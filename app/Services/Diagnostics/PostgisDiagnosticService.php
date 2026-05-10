<?php

declare(strict_types=1);

namespace App\Services\Diagnostics;

use App\Services\Diagnostics\Contracts\DiagnosticInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

final class PostgisDiagnosticService implements DiagnosticInterface
{
    public function key(): string
    {
        return 'postgis';
    }

    public function label(): string
    {
        return 'PostGIS';
    }

    public function run(): DiagnosticResult
    {
        $start = hrtime(true);

        try {
            $version = (string) DB::scalar('SELECT PostGIS_Full_Version()');

            $sample = DB::scalar(
                "SELECT ST_AsText(ST_Buffer(ST_GeomFromText('POINT(14.21 42.46)', 4326), 0.01))"
            );

            $latencyMs = $this->latencyMs($start);

            return DiagnosticResult::ok(
                service: $this->key(),
                latencyMs: $latencyMs,
                version: $this->shortVersion($version),
                details: [
                    'srid_test' => 4326,
                    'sample_buffer_geometry_type' => str_starts_with((string) $sample, 'POLYGON') ? 'POLYGON' : 'unknown',
                    'full_version' => $version,
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

    private function shortVersion(string $full): string
    {
        if (preg_match('/POSTGIS="([\d.]+)/i', $full, $m)) {
            return 'PostGIS '.$m[1];
        }

        return mb_substr($full, 0, 40);
    }
}
