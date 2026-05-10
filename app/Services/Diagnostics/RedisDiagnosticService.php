<?php

declare(strict_types=1);

namespace App\Services\Diagnostics;

use App\Services\Diagnostics\Contracts\DiagnosticInterface;
use Illuminate\Support\Facades\Redis;
use Throwable;

final class RedisDiagnosticService implements DiagnosticInterface
{
    public function key(): string
    {
        return 'redis';
    }

    public function label(): string
    {
        return 'Redis';
    }

    public function run(): DiagnosticResult
    {
        $start = hrtime(true);

        try {
            $connection = Redis::connection();

            $pong = (string) $connection->command('ping');
            if (mb_strtolower($pong) !== 'pong' && $pong !== '+PONG' && $pong !== '1') {
                return DiagnosticResult::fail(
                    service: $this->key(),
                    error: 'PING returned unexpected response: '.$pong,
                    latencyMs: $this->latencyMs($start),
                );
            }

            $info = (array) $connection->command('info', ['server']);
            $version = $info['redis_version'] ?? null;

            $memory = (array) $connection->command('info', ['memory']);

            return DiagnosticResult::ok(
                service: $this->key(),
                latencyMs: $this->latencyMs($start),
                version: $version ? 'Redis '.$version : null,
                details: [
                    'used_memory_human' => $memory['used_memory_human'] ?? null,
                    'connected_clients' => $info['connected_clients'] ?? null,
                    'uptime_seconds' => $info['uptime_in_seconds'] ?? null,
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
