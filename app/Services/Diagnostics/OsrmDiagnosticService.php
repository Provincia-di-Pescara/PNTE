<?php

declare(strict_types=1);

namespace App\Services\Diagnostics;

use App\Services\Diagnostics\Contracts\DiagnosticInterface;
use Illuminate\Http\Client\Factory as HttpFactory;
use Throwable;

final class OsrmDiagnosticService implements DiagnosticInterface
{
    /**
     * Pescara → L'Aquila sample (well within Italian OSRM tile coverage).
     * Lng,lat order per OSRM convention.
     */
    private const SAMPLE_COORDS = '14.2156,42.4647;13.3995,42.3498';

    public function __construct(private readonly HttpFactory $http) {}

    public function key(): string
    {
        return 'osrm';
    }

    public function label(): string
    {
        return 'OSRM routing engine';
    }

    public function run(): DiagnosticResult
    {
        $start = hrtime(true);
        $baseUrl = rtrim((string) config('services.osrm.base_url'), '/');

        if ($baseUrl === '') {
            return DiagnosticResult::fail(
                service: $this->key(),
                error: 'services.osrm.base_url non configurato',
                latencyMs: $this->latencyMs($start),
            );
        }

        try {
            $response = $this->http
                ->timeout((int) config('services.osrm.timeout', 5))
                ->get("{$baseUrl}/route/v1/driving/".self::SAMPLE_COORDS, [
                    'overview' => 'false',
                    'steps' => 'false',
                ]);

            if (! $response->successful()) {
                return DiagnosticResult::fail(
                    service: $this->key(),
                    error: 'HTTP '.$response->status(),
                    latencyMs: $this->latencyMs($start),
                    details: ['base_url' => $baseUrl],
                );
            }

            $body = (array) $response->json();
            $code = (string) ($body['code'] ?? 'unknown');
            $route = $body['routes'][0] ?? null;

            if ($code !== 'Ok' || $route === null) {
                return DiagnosticResult::fail(
                    service: $this->key(),
                    error: 'OSRM response code='.$code,
                    latencyMs: $this->latencyMs($start),
                    details: ['base_url' => $baseUrl, 'response' => $body],
                );
            }

            return DiagnosticResult::ok(
                service: $this->key(),
                latencyMs: $this->latencyMs($start),
                version: 'OSRM HTTP API v1',
                details: [
                    'base_url' => $baseUrl,
                    'sample_distance_km' => round((float) ($route['distance'] ?? 0) / 1000, 2),
                    'sample_duration_s' => (int) round((float) ($route['duration'] ?? 0)),
                ],
            );
        } catch (Throwable $e) {
            return DiagnosticResult::fail(
                service: $this->key(),
                error: $e->getMessage(),
                latencyMs: $this->latencyMs($start),
                details: ['base_url' => $baseUrl],
            );
        }
    }

    private function latencyMs(int $start): int
    {
        return (int) round((hrtime(true) - $start) / 1_000_000);
    }
}
