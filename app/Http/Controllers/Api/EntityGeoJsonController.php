<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\EntityType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'tipo' => ['sometimes', 'string', new Enum(EntityType::class)],
        ]);

        $query = 'SELECT id, nome, tipo, codice_istat,
                         ST_AsGeoJSON(geom, 6) AS geojson
                  FROM entities
                  WHERE geom IS NOT NULL';
        $bindings = [];

        if (isset($validated['tipo'])) {
            $query .= ' AND tipo = ?';
            $bindings[] = $validated['tipo'];
        }

        $rows = DB::select($query, $bindings);

        // Build FeatureCollection by concatenating raw GeoJSON strings from PostGIS.
        // Avoids json_decode → PHP array → json_encode round-trip which OOMs on 7 k+ comuni.
        $features = array_map(static function (object $row): string {
            $props = json_encode([
                'id' => (int) $row->id,
                'nome' => (string) $row->nome,
                'tipo' => (string) $row->tipo,
                'codice_istat' => $row->codice_istat,
            ]);

            return '{"type":"Feature","geometry":'.$row->geojson.',"properties":'.$props.'}';
        }, $rows);

        $body = '{"type":"FeatureCollection","features":['.implode(',', $features).']}';

        return response($body, 200, ['Content-Type' => 'application/json']);
    }
}
