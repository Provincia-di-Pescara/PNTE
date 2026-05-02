<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\EntityType;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;

final class EntityGeoJsonController extends Controller
{
    /**
     * Return entities with geometry as a GeoJSON FeatureCollection.
     *
     * GET /api/entities/geojson
     *
     * Query params:
     *   tipo (optional) — filter by EntityType value (e.g. "comune", "provincia")
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tipo' => ['sometimes', 'string', new Enum(EntityType::class)],
        ]);

        $query = 'SELECT id, nome, tipo, codice_istat, ST_AsGeoJSON(geom) AS geojson
                  FROM entities
                  WHERE geom IS NOT NULL';
        $bindings = [];

        if (isset($validated['tipo'])) {
            $query .= ' AND tipo = ?';
            $bindings[] = $validated['tipo'];
        }

        $rows = DB::select($query, $bindings);

        $features = array_map(static function (object $row): array {
            return [
                'type' => 'Feature',
                'geometry' => json_decode((string) $row->geojson, true),
                'properties' => [
                    'id' => (int) $row->id,
                    'nome' => (string) $row->nome,
                    'tipo' => (string) $row->tipo,
                    'codice_istat' => $row->codice_istat,
                ],
            ];
        }, $rows);

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }
}
