<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Spatial analysis of a route against ARS standard routes.
 *
 * Uses ST_Buffer with 0.0001° (~11 m at 41–42°N) to account for road-snap
 * misalignment between the user's snapped route and stored standard routes.
 *
 * @phpstan-type MatchedRoute array{
 *     id: int,
 *     nome: string,
 *     entity_id: int,
 *     vehicle_types: mixed,
 *     max_massa_kg: int|null,
 *     max_lunghezza_mm: int|null,
 *     max_larghezza_mm: int|null,
 *     max_altezza_mm: int|null,
 *     geojson: array<string, mixed>
 * }
 */
final class StandardRouteOverlayService
{
    /**
     * Returns all active standard routes whose geometry overlaps the given route WKT
     * (within a ~11 m buffer).
     *
     * @param  string  $wkt  WKT LINESTRING of the user's route (SRID 4326)
     * @return list<MatchedRoute>
     */
    public function analyze(string $wkt): array
    {
        $rows = DB::select(
            'SELECT sr.id,
                    sr.nome,
                    sr.entity_id,
                    sr.vehicle_types,
                    sr.max_massa_kg,
                    sr.max_lunghezza_mm,
                    sr.max_larghezza_mm,
                    sr.max_altezza_mm,
                    ST_AsGeoJSON(sr.geometry) AS geojson
             FROM standard_routes sr
                         WHERE sr.active = true
               AND ST_Intersects(ST_Buffer(ST_GeomFromText(?, 4326), 0.0001), sr.geometry)',
            [$wkt]
        );

        return array_map(function (object $row): array {
            return [
                'id' => (int) $row->id,
                'nome' => (string) $row->nome,
                'entity_id' => (int) $row->entity_id,
                'vehicle_types' => json_decode((string) $row->vehicle_types, true),
                'max_massa_kg' => $row->max_massa_kg !== null ? (int) $row->max_massa_kg : null,
                'max_lunghezza_mm' => $row->max_lunghezza_mm !== null ? (int) $row->max_lunghezza_mm : null,
                'max_larghezza_mm' => $row->max_larghezza_mm !== null ? (int) $row->max_larghezza_mm : null,
                'max_altezza_mm' => $row->max_altezza_mm !== null ? (int) $row->max_altezza_mm : null,
                'geojson' => json_decode((string) $row->geojson, true),
            ];
        }, $rows);
    }

    /**
     * Returns a GeoJSON FeatureCollection of ARS-covered segments along the route.
     *
     * Each feature is the intersection of the buffered route with one active standard
     * route, annotated with coverage_count=1. Features with coverage_count=0 are not
     * emitted: the frontend renders the base route line in red and paints green ARS
     * segments on top, achieving the same visual effect without aggregate SQL.
     *
     * Note: coverage is communicated via presence/absence of features. This keeps
     * the payload compact and avoids expensive aggregate geometry operations.
     *
     * @param  string  $wkt  WKT LINESTRING of the user's route (SRID 4326)
     * @return array<string, mixed> GeoJSON FeatureCollection
     */
    public function segmentCoverage(string $wkt): array
    {
        $rows = DB::select(
            'SELECT sr.id AS sr_id,
                    sr.nome AS sr_nome,
                    ST_AsGeoJSON(
                        ST_Intersection(
                            ST_Buffer(ST_GeomFromText(?, 4326), 0.0001),
                            sr.geometry
                        )
                    ) AS intersection_geojson
             FROM standard_routes sr
                         WHERE sr.active = true
               AND ST_Intersects(ST_Buffer(ST_GeomFromText(?, 4326), 0.0001), sr.geometry)
               AND NOT ST_IsEmpty(
                       ST_Intersection(
                           ST_Buffer(ST_GeomFromText(?, 4326), 0.0001),
                           sr.geometry
                       )
                   )',
            [$wkt, $wkt, $wkt]
        );

        $features = [];
        foreach ($rows as $row) {
            $geometry = json_decode((string) $row->intersection_geojson, true);
            if ($geometry === null || $this->isEmptyGeometry($geometry)) {
                continue;
            }
            $features[] = [
                'type' => 'Feature',
                'geometry' => $geometry,
                'properties' => [
                    'standard_route_id' => (int) $row->sr_id,
                    'standard_route_nome' => (string) $row->sr_nome,
                    'coverage_count' => 1,
                ],
            ];
        }

        return [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];
    }

    /**
     * Checks whether a decoded GeoJSON geometry is effectively empty
     * (null type, empty coordinates, or GeometryCollection with no geometries).
     *
     * @param  array<string, mixed>  $geometry
     */
    private function isEmptyGeometry(array $geometry): bool
    {
        if (empty($geometry['type'])) {
            return true;
        }

        if (($geometry['type'] ?? '') === 'GeometryCollection') {
            return empty($geometry['geometries']);
        }

        return empty($geometry['coordinates']);
    }
}
