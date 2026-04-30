<?php

declare(strict_types=1);

namespace App\Contracts;

interface OsrmServiceInterface
{
    /**
     * Snap waypoints to road network and return route as WKT LINESTRING + km.
     *
     * @param  array<int, array{lat: float, lng: float}>  $waypoints
     * @return array{geometry: string, distance_km: float}
     */
    public function snapToRoad(array $waypoints): array;

    /**
     * Like snapToRoad but returns all available alternative routes.
     *
     * @param  array<int, array{lat: float, lng: float}>  $waypoints
     * @return array<int, array{geometry: string, distance_km: float}>
     */
    public function alternatives(array $waypoints): array;
}
