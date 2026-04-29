<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Route as RouteModel;
use Illuminate\Support\Facades\DB;

final class RouteIntersectionService
{
    /**
     * Returns entity_id → km breakdown for the given route.
     *
     * MariaDB 11.4 ST_Length on SRID 4326 geometry returns degrees (not meters).
     * ST_Length(g, 'kilometre') is NOT supported by MariaDB (ERROR 1582).
     * Conversion: 1° ≈ 111.32 km for Abruzzo latitude (error < 2%).
     *
     * @return array<int, float> entity_id → km
     */
    public function breakdown(RouteModel $route): array
    {
        $wkt = $route->getRawGeometry();
        $rows = DB::select(
            'SELECT e.id,
                    ST_Length(ST_Intersection(ST_GeomFromText(?, 4326), e.geom)) * 111.32 AS km
             FROM entities e
             WHERE e.geom IS NOT NULL
               AND ST_Intersects(ST_GeomFromText(?, 4326), e.geom)',
            [$wkt, $wkt]
        );

        return collect($rows)
            ->mapWithKeys(fn ($r) => [(int) $r->id => round((float) $r->km, 3)])
            ->all();
    }
}
