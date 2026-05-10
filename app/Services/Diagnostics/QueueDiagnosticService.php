<?php

declare(strict_types=1);

namespace App\Services\Diagnostics;

use App\Services\Diagnostics\Contracts\DiagnosticInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class QueueDiagnosticService implements DiagnosticInterface
{
    public function key(): string
    {
        return 'queue';
    }

    public function label(): string
    {
        return 'Queue';
    }

    public function run(): DiagnosticResult
    {
        $start = hrtime(true);

        try {
            $size = Queue::size();
            $failed = Schema::hasTable('failed_jobs')
                ? DB::table('failed_jobs')->count()
                : 0;

            $latestFailure = null;
            if (Schema::hasTable('failed_jobs') && $failed > 0) {
                $row = DB::table('failed_jobs')
                    ->orderByDesc('failed_at')
                    ->first(['failed_at', 'connection', 'queue']);
                $latestFailure = $row ? [
                    'failed_at' => $row->failed_at,
                    'connection' => $row->connection,
                    'queue' => $row->queue,
                ] : null;
            }

            return DiagnosticResult::ok(
                service: $this->key(),
                latencyMs: $this->latencyMs($start),
                version: 'driver: '.config('queue.default'),
                details: [
                    'pending' => $size,
                    'failed' => $failed,
                    'connection' => config('queue.default'),
                    'latest_failure' => $latestFailure,
                ],
            );
        } catch (Throwable $e) {
            return DiagnosticResult::fail(
                service: $this->key(),
                error: $e->getMessage(),
                latencyMs: $this->latencyMs($start),
            );
        }
    }

    private function latencyMs(int $start): int
    {
        return (int) round((hrtime(true) - $start) / 1_000_000);
    }
}
