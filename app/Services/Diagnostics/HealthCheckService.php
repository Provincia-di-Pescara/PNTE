<?php

declare(strict_types=1);

namespace App\Services\Diagnostics;

use App\Models\SystemAuditLog;
use App\Services\Diagnostics\Contracts\DiagnosticInterface;
use Illuminate\Support\Facades\Auth;
use Throwable;

final class HealthCheckService
{
    /**
     * @param  array<string, DiagnosticInterface>  $diagnostics
     *                                                           keyed by DiagnosticInterface::key()
     */
    public function __construct(private readonly array $diagnostics) {}

    /**
     * @return array<string, DiagnosticInterface>
     */
    public function diagnostics(): array
    {
        return $this->diagnostics;
    }

    public function get(string $key): DiagnosticInterface
    {
        if (! isset($this->diagnostics[$key])) {
            throw new \InvalidArgumentException("Unknown diagnostic service: {$key}");
        }

        return $this->diagnostics[$key];
    }

    /**
     * Run a single diagnostic by key. Logs result to system_audit_logs.
     */
    public function runOne(string $key, bool $audit = true): DiagnosticResult
    {
        $diagnostic = $this->get($key);

        try {
            $result = $diagnostic->run();
        } catch (Throwable $e) {
            $result = DiagnosticResult::fail($diagnostic->key(), $e->getMessage());
        }

        if ($audit) {
            $this->audit($result);
        }

        return $result;
    }

    /**
     * Run all registered diagnostics sequentially.
     *
     * @return array<string, DiagnosticResult>
     */
    public function runAll(bool $audit = true): array
    {
        $results = [];

        foreach ($this->diagnostics as $key => $_) {
            $results[$key] = $this->runOne($key, audit: $audit);
        }

        return $results;
    }

    /**
     * @param  array<string, DiagnosticResult>  $results
     */
    public function summarize(array $results): array
    {
        $allOk = true;
        $services = [];

        foreach ($results as $key => $result) {
            $allOk = $allOk && $result->ok;
            $services[$key] = $result->toArray();
        }

        return [
            'ok' => $allOk,
            'checked_at' => now()->toImmutable()->toIso8601String(),
            'service_count' => count($results),
            'services' => $services,
        ];
    }

    private function audit(DiagnosticResult $result): void
    {
        $actor = Auth::user();

        SystemAuditLog::query()->create([
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name ?? 'sistema',
            'action' => 'diagnostic.run.'.$result->service,
            'detail' => $result->ok
                ? sprintf('OK · %d ms%s', $result->latencyMs, $result->version ? ' · '.$result->version : '')
                : sprintf('FAIL · %s', $result->error ?? 'errore sconosciuto'),
            'created_at' => now(),
        ]);
    }
}
