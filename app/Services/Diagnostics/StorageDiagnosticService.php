<?php

declare(strict_types=1);

namespace App\Services\Diagnostics;

use App\Services\Diagnostics\Contracts\DiagnosticInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

final class StorageDiagnosticService implements DiagnosticInterface
{
    public function key(): string
    {
        return 'storage';
    }

    public function label(): string
    {
        return 'Storage filesystem';
    }

    public function run(): DiagnosticResult
    {
        $start = hrtime(true);
        $disk = config('filesystems.default', 'local');
        $probe = 'diagnostics/'.Str::random(16).'.txt';
        $payload = 'pnte-diag '.now()->toIso8601String();

        try {
            $fs = Storage::disk($disk);

            $fs->put($probe, $payload);
            $read = $fs->get($probe);
            $fs->delete($probe);

            if ($read !== $payload) {
                return DiagnosticResult::fail(
                    service: $this->key(),
                    error: 'Round-trip mismatch on disk "'.$disk.'"',
                    latencyMs: $this->latencyMs($start),
                );
            }

            $rootBytes = @disk_free_space(storage_path('app')) ?: null;

            return DiagnosticResult::ok(
                service: $this->key(),
                latencyMs: $this->latencyMs($start),
                version: 'disk: '.$disk,
                details: [
                    'disk' => $disk,
                    'free_bytes' => $rootBytes !== null ? (int) $rootBytes : null,
                    'free_human' => $rootBytes !== null ? $this->humanBytes((int) $rootBytes) : null,
                ],
            );
        } catch (Throwable $e) {
            return DiagnosticResult::fail(
                service: $this->key(),
                error: $e->getMessage(),
                latencyMs: $this->latencyMs($start),
                details: ['disk' => $disk],
            );
        }
    }

    private function latencyMs(int $start): int
    {
        return (int) round((hrtime(true) - $start) / 1_000_000);
    }

    private function humanBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = min((int) floor(log(max($bytes, 1), 1024)), count($units) - 1);
        $value = $bytes / (1024 ** $power);

        return number_format($value, 1, '.', '').' '.$units[$power];
    }
}
