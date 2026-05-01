<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class ImportStandardRoutes extends Command
{
    protected $signature = 'gte:import-standard-routes
                            {file : Percorso al file GeoJSON delle strade standard}
                            {entity_id : ID dell\'entità amministrativa a cui associare le strade}';

    protected $description = 'Importa strade standard ARS (GeoJSON) nella tabella standard_routes';

    public function handle(): int
    {
        $path = (string) $this->argument('file');
        $entityId = (int) $this->argument('entity_id');

        if (! file_exists($path)) {
            $this->error("File non trovato: {$path}");

            return self::FAILURE;
        }

        $data = json_decode((string) file_get_contents($path), true);

        if (! isset($data['features']) || ! is_array($data['features'])) {
            $this->error('GeoJSON non valido: manca la chiave "features".');

            return self::FAILURE;
        }

        // Verify the entity exists
        $entityExists = DB::selectOne('SELECT id FROM entities WHERE id = ?', [$entityId]);
        if ($entityExists === null) {
            $this->error("Entity con id={$entityId} non trovata nel database.");

            return self::FAILURE;
        }

        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($data['features'] as $feature) {
            $geometry = $feature['geometry'] ?? null;
            $props = $feature['properties'] ?? [];

            if ($geometry === null) {
                $this->warn('Feature senza geometry: saltata.');
                $skipped++;

                continue;
            }

            $geometryType = strtoupper((string) ($geometry['type'] ?? ''));
            if (! in_array($geometryType, ['LINESTRING', 'MULTILINESTRING'], true)) {
                $this->warn("Geometry di tipo '{$geometryType}' non supportata (atteso LINESTRING): saltata.");
                $skipped++;

                continue;
            }

            $nome = (string) ($props['nome'] ?? $props['name'] ?? $props['NOME'] ?? $props['NAME'] ?? '');
            if ($nome === '') {
                $this->warn('Feature senza nome: saltata.');
                $skipped++;

                continue;
            }

            $vehicleTypes = $props['vehicle_types'] ?? $props['tipi_veicolo'] ?? null;
            if (! is_array($vehicleTypes)) {
                $vehicleTypes = [];
            }

            $maxMassaKg = isset($props['max_massa_kg']) ? (int) $props['max_massa_kg'] : null;
            $maxLunghezzaMm = isset($props['max_lunghezza_mm']) ? (int) $props['max_lunghezza_mm'] : null;
            $maxLarghezzaMm = isset($props['max_larghezza_mm']) ? (int) $props['max_larghezza_mm'] : null;
            $maxAltezzaMm = isset($props['max_altezza_mm']) ? (int) $props['max_altezza_mm'] : null;
            $active = isset($props['active']) ? (bool) $props['active'] : true;
            $note = isset($props['note']) ? (string) $props['note'] : null;

            $geometryJson = json_encode($geometry);

            // Check for existing record (idempotent: match on entity_id + nome)
            $existing = DB::selectOne(
                'SELECT id FROM standard_routes WHERE entity_id = ? AND nome = ?',
                [$entityId, $nome]
            );

            if ($existing !== null) {
                DB::statement(
                    'UPDATE standard_routes
                     SET geometry = ST_GeomFromGeoJSON(?),
                         vehicle_types = ?,
                         max_massa_kg = ?,
                         max_lunghezza_mm = ?,
                         max_larghezza_mm = ?,
                         max_altezza_mm = ?,
                         active = ?,
                         note = ?,
                         updated_at = NOW()
                     WHERE id = ?',
                    [
                        $geometryJson,
                        json_encode($vehicleTypes),
                        $maxMassaKg,
                        $maxLunghezzaMm,
                        $maxLarghezzaMm,
                        $maxAltezzaMm,
                        $active ? 1 : 0,
                        $note,
                        (int) $existing->id,
                    ]
                );
                $updated++;
            } else {
                DB::statement(
                    'INSERT INTO standard_routes
                         (entity_id, nome, geometry, vehicle_types,
                          max_massa_kg, max_lunghezza_mm, max_larghezza_mm, max_altezza_mm,
                          active, note, created_at, updated_at)
                     VALUES (?, ?, ST_GeomFromGeoJSON(?), ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
                    [
                        $entityId,
                        $nome,
                        $geometryJson,
                        json_encode($vehicleTypes),
                        $maxMassaKg,
                        $maxLunghezzaMm,
                        $maxLarghezzaMm,
                        $maxAltezzaMm,
                        $active ? 1 : 0,
                        $note,
                    ]
                );
                $inserted++;
            }
        }

        $this->info("Completato — Inserite: {$inserted} / Aggiornate: {$updated} / Saltate: {$skipped}");

        return self::SUCCESS;
    }
}
