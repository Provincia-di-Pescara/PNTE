<?php

declare(strict_types=1);

namespace App\Services\Diagnostics;

use App\Services\Diagnostics\Contracts\DiagnosticInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

final class DatabaseDiagnosticService implements DiagnosticInterface
{
    public function key(): string
    {
        return 'db';
    }

    public function label(): string
    {
        return 'PostgreSQL';
    }

    public function run(): DiagnosticResult
    {
        $start = hrtime(true);

        try {
            $version = (string) DB::scalar('SELECT version()');
            $database = (string) DB::scalar('SELECT current_database()');
            $latencyMs = $this->latencyMs($start);

            return DiagnosticResult::ok(
                service: $this->key(),
                latencyMs: $latencyMs,
                version: $this->shortVersion($version),
                details: [
                    'connection' => config('database.default'),
                    'database' => $database,
                    'driver' => DB::connection()->getDriverName(),
                    'full_version' => $version,
                ],
            );
        } catch (Throwable $e) {
            return DiagnosticResult::fail(
                service: $this->key(),
                error: $e->getMessage(),
                latencyMs: $this->latencyMs($start),
                details: ['connection' => config('database.default')],
            );
        }
    }

    private function latencyMs(int $start): int
    {
        return (int) round((hrtime(true) - $start) / 1_000_000);
    }

    private function shortVersion(string $full): string
    {
        if (preg_match('/PostgreSQL\s+([\d.]+)/i', $full, $m)) {
            return 'PostgreSQL '.$m[1];
        }

        return mb_substr($full, 0, 40);
    }
}
