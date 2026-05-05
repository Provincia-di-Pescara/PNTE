<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\EntityType;
use App\Models\Entity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class ImportGeoData extends Command
{
    protected $signature = 'gte:import-geo {file : Percorso al file GeoJSON dei confini territoriali}';

    protected $description = 'Importa confini geografici (GeoJSON) nella colonna geom delle entities, creando le entity mancanti (upsert)';

    public function handle(): int
    {
        $path = (string) $this->argument('file');

        if (! file_exists($path)) {
            $this->error("File non trovato: {$path}");

            return self::FAILURE;
        }

        $data = json_decode((string) file_get_contents($path), true);

        if (! isset($data['features']) || ! is_array($data['features'])) {
            $this->error('GeoJSON non valido: manca la chiave "features".');

            return self::FAILURE;
        }

        $updated = 0;
        $created = 0;
        $skipped = 0;

        foreach ($data['features'] as $feature) {
            $props = $feature['properties'] ?? [];

            // openpolis: comuni → cod_istat (6 cifre), province → cod_prov (3 cifre)
            $istatCode = $props['cod_istat']
                ?? $props['cod_prov']
                ?? $props['codice_istat']
                ?? $props['PRO_COM_T']
                ?? $props['COD_ISTAT']
                ?? null;

            if ($istatCode === null) {
                $this->warn('Feature senza codice ISTAT: saltata.');
                $skipped++;

                continue;
            }

            $istatCode = (string) $istatCode;

            // Infer tipo from ISTAT code length
            $tipo = match (strlen($istatCode)) {
                3 => EntityType::Provincia,
                6 => EntityType::Comune,
                default => null,
            };

            if ($tipo === null) {
                $this->warn("[{$istatCode}] Lunghezza codice non riconosciuta: saltata.");
                $skipped++;

                continue;
            }

            $nome = (string) ($props['name'] ?? $props['nome'] ?? $istatCode);

            $existed = Entity::query()->where('codice_istat', $istatCode)->exists();

            $entity = Entity::query()->updateOrCreate(
                ['codice_istat' => $istatCode],
                ['nome' => $nome, 'tipo' => $tipo->value],
            );

            DB::statement(
                'UPDATE entities SET geom = ST_GeomFromGeoJSON(?) WHERE id = ?',
                [json_encode($feature['geometry']), $entity->id],
            );

            if ($existed) {
                $updated++;
            } else {
                $created++;
            }
        }

        $this->info("Completato — Aggiornate: {$updated} / Create: {$created} / Saltate: {$skipped}");

        return self::SUCCESS;
    }
}

