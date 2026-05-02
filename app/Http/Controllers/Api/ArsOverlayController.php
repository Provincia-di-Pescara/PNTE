<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StandardRouteOverlayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ArsOverlayController extends Controller
{
    public function __construct(private readonly StandardRouteOverlayService $overlay) {}

    /**
     * Analyse ARS (standard-route) coverage for a given route WKT.
     *
     * POST /api/routing/ars-overlay
     *
     * Body: { "wkt": "LINESTRING(...)" }
     *
     * Response:
     * {
     *   "matched_routes": [...],
     *   "coverage_geojson": { GeoJSON FeatureCollection }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'wkt' => ['required', 'string'],
        ]);

        $matchedRoutes = $this->overlay->analyze($validated['wkt']);
        $coverageGeojson = $this->overlay->segmentCoverage($validated['wkt']);

        return response()->json([
            'matched_routes' => $matchedRoutes,
            'coverage_geojson' => $coverageGeojson,
        ]);
    }
}
