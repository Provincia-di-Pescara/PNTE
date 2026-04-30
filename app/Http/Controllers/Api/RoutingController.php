<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\OsrmServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class RoutingController extends Controller
{
    public function __construct(private readonly OsrmServiceInterface $osrm) {}

    public function snap(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'waypoints' => ['required', 'array', 'min:2'],
            'waypoints.*.lat' => ['required', 'numeric'],
            'waypoints.*.lng' => ['required', 'numeric'],
        ]);

        $result = $this->osrm->snapToRoad($validated['waypoints']);
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

        $routes = $this->osrm->alternatives($validated['waypoints']);

        $result = array_map(function (array $r): array {
            $geojson = json_decode(
                (string) DB::scalar('SELECT ST_AsGeoJSON(ST_GeomFromText(?))', [$r['geometry']]),
                true
            );

            return ['wkt' => $r['geometry'], 'geojson' => $geojson, 'distance_km' => $r['distance_km']];
        }, $routes);

        return response()->json($result);
    }
}
