<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Diagnostics;

use App\Services\Diagnostics\DiagnosticResult;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

final class DiagnosticResultTest extends TestCase
{
    public function test_ok_factory_builds_successful_result(): void
    {
        $result = DiagnosticResult::ok(
            service: 'postgis',
            latencyMs: 12,
            version: 'PostGIS 3.4.0',
            details: ['extension' => 'postgis'],
        );

        $this->assertTrue($result->ok);
        $this->assertSame('postgis', $result->service);
        $this->assertSame(12, $result->latencyMs);
        $this->assertSame('PostGIS 3.4.0', $result->version);
        $this->assertSame(['extension' => 'postgis'], $result->details);
        $this->assertNull($result->error);
        $this->assertInstanceOf(CarbonImmutable::class, $result->checkedAt);
    }

    public function test_fail_factory_builds_failed_result(): void
    {
        $result = DiagnosticResult::fail(
            service: 'osrm',
            error: 'connection refused',
            latencyMs: 3000,
            details: ['url' => 'http://osrm:5000'],
        );

        $this->assertFalse($result->ok);
        $this->assertSame('osrm', $result->service);
        $this->assertSame(3000, $result->latencyMs);
        $this->assertNull($result->version);
        $this->assertSame('connection refused', $result->error);
    }

    public function test_to_array_returns_serializable_payload(): void
    {
        $result = DiagnosticResult::ok(
            service: 'redis',
            latencyMs: 1,
            version: '7.2.0',
            details: ['memory' => '1.2 MB'],
            checkedAt: CarbonImmutable::parse('2026-05-10T10:00:00Z'),
        );

        $payload = $result->toArray();

        $this->assertSame([
            'service' => 'redis',
            'ok' => true,
            'latency_ms' => 1,
            'version' => '7.2.0',
            'details' => ['memory' => '1.2 MB'],
            'error' => null,
            'checked_at' => '2026-05-10T10:00:00+00:00',
        ], $payload);
    }
}
