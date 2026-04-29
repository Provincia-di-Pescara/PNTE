<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\RoadworkStatus;
use App\Models\Roadwork;
use App\Models\Route as RouteModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

final class RoadworkConflictService
{
    /**
     * Returns active roadworks that spatially intersect the given route on the given date.
     *
     * @return Collection<int, Roadwork>
     */
    public function conflicts(RouteModel $route, Carbon $date): Collection
    {
        return Roadwork::query()
            ->whereRaw('ST_Intersects(geometry, ST_GeomFromText(?, 4326))', [$route->getRawGeometry()])
            ->where('status', '!=', RoadworkStatus::Closed->value)
            ->where('valid_from', '<=', $date->toDateString())
            ->where(fn ($q) => $q->whereNull('valid_to')
                ->orWhere('valid_to', '>=', $date->toDateString()))
            ->get();
    }
}
