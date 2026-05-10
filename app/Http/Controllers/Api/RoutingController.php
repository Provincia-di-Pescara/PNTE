<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\OsrmServiceInterface;
use App\Exceptions\OsrmNoRouteException;
use App\Http\Controllers\Controller;
use App\Services\RouteIntersectionService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class RoutingController extends Controller
{
    public function __construct(
        private readonly OsrmServiceInterface $osrm,
        private readonly RouteIntersectionService $intersections,
    ) {}

    public function snap(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'waypoints' => ['required', 'array', 'min:2'],
            'waypoints.*.lat' => ['required', 'numeric'],
            'waypoints.*.lng' => ['required', 'numeric'],
        ]);

        try {
            $result = $this->osrm->snapToRoad($validated['waypoints']);
        } catch (ConnectionException) {
            return response()->json(['error' => 'Motore OSRM non raggiungibile.'], 503);
        } catch (OsrmNoRouteException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        $geojson = json_decode(
            (string) DB::scalar('SELECT ST_AsGeoJSON(ST_GeomFromText(?))', [$result['geometry']]),
            true
        );

        return response()->json([
            'wkt' => $result['geometry'],
            'geojson' => $geojson,
            'distance_km' => $result['distance_km'],
        ]);
    }

    public function alternatives(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'waypoints' => ['required', 'array', 'min:2'],
            'waypoints.*.lat' => ['required', 'numeric'],
            'waypoints.*.lng' => ['required', 'numeric'],
        ]);

        try {
            $routes = $this->osrm->alternatives($validated['waypoints']);
        } catch (ConnectionException) {
            return response()->json(['error' => 'Motore OSRM non raggiungibile.'], 503);
        }

        $result = array_map(static function (array $r): array {
            $geojson = json_decode(
                (string) DB::scalar('SELECT ST_AsGeoJSON(ST_GeomFromText(?))', [$r['geometry']]),
                true
            );

            return ['wkt' => $r['geometry'], 'geojson' => $geojson, 'distance_km' => $r['distance_km']];
        }, $routes);

        return response()->json($result);
    }

    public function breakdown(Request $request): JsonResponse
    {
        $validated = $request->validate(['wkt' => ['required', 'string']]);
        $rows = $this->intersections->breakdownFromWkt($validated['wkt']);

        return response()->json([
            'breakdown' => $rows,
            'total_km'  => round(array_sum(array_column($rows, 'km')), 3),
        ]);
    }
}
