<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\OsrmServiceInterface;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\DB;

final class OsrmService implements OsrmServiceInterface
{
    public function __construct(private readonly HttpFactory $http) {}

    /**
     * Snap waypoints to road network and return route as WKT LINESTRING + km.
     *
     * @param  array<int, array{lat: float, lng: float}>  $waypoints
     * @return array{geometry: string, distance_km: float}
     */
    public function snapToRoad(array $waypoints): array
    {
        $routes = $this->fetchRoutes($waypoints, alternatives: false);

        return $this->parseRoute($routes[0]);
    }

    /**
     * Like snapToRoad but returns all available alternative routes.
     *
     * @param  array<int, array{lat: float, lng: float}>  $waypoints
     * @return array<int, array{geometry: string, distance_km: float}>
     */
    public function alternatives(array $waypoints): array
    {
        $routes = $this->fetchRoutes($waypoints, alternatives: true);

        return array_map(fn ($r) => $this->parseRoute($r), $routes);
    }

    /** @return array<int, mixed> */
    private function fetchRoutes(array $waypoints, bool $alternatives): array
    {
        $coords = implode(';', array_map(fn ($w) => $w['lng'].','.$w['lat'], $waypoints));
        $baseUrl = rtrim((string) config('services.osrm.base_url'), '/');

        $response = $this->http
            ->timeout((int) config('services.osrm.timeout', 10))
            ->get("{$baseUrl}/route/v1/driving/{$coords}", [
                'overview' => 'full',
                'geometries' => 'geojson',
                'steps' => 'false',
                'alternatives' => $alternatives ? 'true' : 'false',
            ])
            ->throw()
            ->json();

        return (array) ($response['routes'] ?? []);
    }

    /** @param  array<string, mixed>  $route
     *  @return array{geometry: string, distance_km: float} */
    private function parseRoute(array $route): array
    {
        $geojson = json_encode($route['geometry']);
        $wkt = (string) DB::scalar('SELECT ST_AsText(ST_GeomFromGeoJSON(?))', [$geojson]);

        return [
            'geometry' => $wkt,
            'distance_km' => round((float) $route['distance'] / 1000, 3),
        ];
    }
}
