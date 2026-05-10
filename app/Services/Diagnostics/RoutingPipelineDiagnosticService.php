<?php

declare(strict_types=1);

namespace App\Services\Diagnostics;

use App\Contracts\OsrmServiceInterface;
use App\Services\Diagnostics\Contracts\DiagnosticInterface;
use App\Services\RouteIntersectionService;
use Throwable;

/**
 * End-to-end smoke test of the routing pipeline:
 *   waypoints → OsrmService::snapToRoad → WKT LINESTRING
 *             → RouteIntersectionService::breakdownFromWkt → entity km map
 *
 * Confirms the full chain is wired (HTTP client, OSRM, PostGIS, entities geom).
 */
final class RoutingPipelineDiagnosticService implements DiagnosticInterface
{
    /**
     * Pescara → L'Aquila — sample within Abruzzo coverage.
     * Total approx 110 km, crosses ≥3 entity polygons in nominal data.
     */
    private const SAMPLE_WAYPOINTS = [
        ['lat' => 42.4647, 'lng' => 14.2156],
        ['lat' => 42.3498, 'lng' => 13.3995],
    ];

    public function __construct(
        private readonly OsrmServiceInterface $osrm,
        private readonly RouteIntersectionService $intersection,
    ) {}

    public function key(): string
    {
        return 'routing';
    }

    public function label(): string
    {
        return 'Routing pipeline E2E';
    }

    public function run(): DiagnosticResult
    {
        $start = hrtime(true);

        try {
            $snap = $this->osrm->snapToRoad(self::SAMPLE_WAYPOINTS);

            $wkt = (string) $snap['geometry'];
            $km = (float) $snap['distance_km'];

            if (! str_starts_with($wkt, 'LINESTRING')) {
                return DiagnosticResult::fail(
                    service: $this->key(),
                    error: 'OSRM ha restituito geometry non LINESTRING',
                    latencyMs: $this->latencyMs($start),
                    details: ['km' => $km],
                );
            }

            if ($km <= 0.0) {
                return DiagnosticResult::fail(
                    service: $this->key(),
                    error: 'distance_km non valida ('.$km.')',
                    latencyMs: $this->latencyMs($start),
                );
            }

            $breakdown = $this->intersection->breakdownFromWkt($wkt);

            return DiagnosticResult::ok(
                service: $this->key(),
                latencyMs: $this->latencyMs($start),
                version: 'snap+breakdown E2E',
                details: [
                    'sample_km' => $km,
                    'entities_intersected' => count($breakdown),
                    'top_entities' => array_slice(array_map(
                        fn (array $b) => [
                            'entity_id' => $b['entity_id'],
                            'nome' => $b['nome'],
                            'km' => $b['km'],
                        ],
                        $breakdown
                    ), 0, 5),
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
