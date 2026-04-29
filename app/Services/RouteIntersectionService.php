<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Route as RouteModel;
use Illuminate\Support\Facades\DB;

final class RouteIntersectionService
{
    /**
     * Returns entity_id → km breakdown for the given route.
     * ST_Length on SRID 4326 returns degrees; 1° ≈ 111.32 km for Abruzzo (error < 2%).
     *
     * @return array<int, float> entity_id → km
     */
    public function breakdown(RouteModel $route): array
    {
        $rows = DB::select(
            'SELECT e.id,
                    ST_Length(ST_Intersection(ST_GeomFromText(?), e.geom)) * 111.32 AS km
             FROM entities e
             WHERE e.geom IS NOT NULL
               AND ST_Intersects(ST_GeomFromText(?), e.geom)',
            [$route->getRawGeometry(), $route->getRawGeometry()]
        );

        return collect($rows)
            ->mapWithKeys(fn ($r) => [(int) $r->id => round((float) $r->km, 3)])
            ->all();
    }
}
