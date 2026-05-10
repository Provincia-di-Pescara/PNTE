<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Diagnostics;

use App\Models\SystemAuditLog;
use App\Services\Diagnostics\Contracts\DiagnosticInterface;
use App\Services\Diagnostics\DiagnosticResult;
use App\Services\Diagnostics\HealthCheckService;
use Tests\TestCase;

final class HealthCheckServiceTest extends TestCase
{
    public function test_run_one_returns_result_and_writes_audit(): void
    {
        $diag = $this->fakeDiagnostic('redis', ok: true, latency: 5, version: '7.2.0');
        $service = new HealthCheckService(['redis' => $diag]);

        $result = $service->runOne('redis');

        $this->assertTrue($result->ok);
        $this->assertSame('redis', $result->service);
        $this->assertDatabaseHas('system_audit_logs', [
            'action' => 'diagnostic.run.redis',
        ]);
    }

    public function test_run_one_audit_skipped_when_disabled(): void
    {
        SystemAuditLog::query()->delete();

        $diag = $this->fakeDiagnostic('osrm', ok: true);
        $service = new HealthCheckService(['osrm' => $diag]);

        $service->runOne('osrm', audit: false);

        $this->assertSame(0, SystemAuditLog::query()->count());
    }

    public function test_run_one_swallows_exception_and_returns_failure(): void
    {
        $diag = new class implements DiagnosticInterface
        {
            public function key(): string
            {
                return 'broken';
            }

            public function label(): string
            {
                return 'Broken';
            }

            public function run(): DiagnosticResult
            {
                throw new \RuntimeException('boom');
            }
        };

        $service = new HealthCheckService(['broken' => $diag]);
        $result = $service->runOne('broken');

        $this->assertFalse($result->ok);
        $this->assertSame('boom', $result->error);
    }

    public function test_run_all_returns_keyed_results(): void
    {
        $service = new HealthCheckService([
            'a' => $this->fakeDiagnostic('a', ok: true),
            'b' => $this->fakeDiagnostic('b', ok: false, error: 'down'),
        ]);

        $results = $service->runAll(audit: false);

        $this->assertCount(2, $results);
        $this->assertTrue($results['a']->ok);
        $this->assertFalse($results['b']->ok);
        $this->assertSame('down', $results['b']->error);
    }

    public function test_summarize_aggregates_results(): void
    {
        $service = new HealthCheckService([]);

        $summary = $service->summarize([
            'a' => DiagnosticResult::ok('a', 1),
            'b' => DiagnosticResult::fail('b', 'nope'),
        ]);

        $this->assertFalse($summary['ok']);
        $this->assertSame(2, $summary['service_count']);
        $this->assertArrayHasKey('a', $summary['services']);
        $this->assertArrayHasKey('b', $summary['services']);
    }

    public function test_get_throws_for_unknown_service(): void
    {
        $service = new HealthCheckService([]);

        $this->expectException(\InvalidArgumentException::class);
        $service->get('nope');
    }

    private function fakeDiagnostic(
        string $key,
        bool $ok = true,
        int $latency = 1,
        ?string $version = null,
        ?string $error = null,
    ): DiagnosticInterface {
        return new class($key, $ok, $latency, $version, $error) implements DiagnosticInterface
        {
            public function __construct(
                private readonly string $key,
                private readonly bool $ok,
                private readonly int $latency,
                private readonly ?string $version,
                private readonly ?string $error,
            ) {}

            public function key(): string
            {
                return $this->key;
            }

            public function label(): string
            {
                return ucfirst($this->key);
            }

            public function run(): DiagnosticResult
            {
                return $this->ok
                    ? DiagnosticResult::ok($this->key, $this->latency, $this->version)
                    : DiagnosticResult::fail($this->key, $this->error ?? 'fail');
            }
        };
    }
}
