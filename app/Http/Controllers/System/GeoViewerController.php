<?php

declare(strict_types=1);

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use App\Models\Roadwork;
use App\Models\StandardRoute;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

final class GeoViewerController extends Controller
{
    public function viewer(): View
    {
        return view('system.geo.viewer', [
            'apiTestEndpoint' => route('system.api.test.geojson'),
        ]);
    }

    public function osrm(): View
    {
        return view('system.geo.osrm', [
            'baseUrl' => (string) config('services.osrm.base_url'),
            'healthEndpoint' => route('system.api.health.single', ['service' => 'osrm']),
            'routingEndpoint' => route('system.api.test.routing'),
        ]);
    }

    public function simulator(): View
    {
        $entitiesWithGeom = Schema::hasColumn('entities', 'geom')
            ? Entity::query()->whereNotNull('geom')->count()
            : 0;
        $totalEntities = Entity::query()->count();

        return view('system.geo.route-simulator', [
            'routingEndpoint' => route('system.api.test.routing'),
            'entitiesWithGeom' => $entitiesWithGeom,
            'totalEntities' => $totalEntities,
            'roadworks' => Roadwork::query()->count(),
            'standardRoutes' => StandardRoute::query()->count(),
        ]);
    }
}
