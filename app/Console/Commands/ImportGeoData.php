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
        ini_set('memory_limit', '512M');

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

            // Rileva codice ISTAT e tipo dal GeoJSON sorgente.
            // Priorità: openpolis (com_istat_code / prov_istat_code) poi formati legacy.
            // com_istat_code va prima di prov_istat_code: entrambi presenti nei comuni.
            $tipo = null;
            $istatCode = null;

            if (isset($props['com_istat_code'])) {
                // openpolis comuni: '001001' (6 cifre, già padded)
                $istatCode = str_pad((string) $props['com_istat_code'], 6, '0', STR_PAD_LEFT);
                $tipo = EntityType::Comune;
            } elseif (isset($props['prov_istat_code'])) {
                // openpolis province: '001' (3 cifre, già padded)
                $istatCode = str_pad((string) $props['prov_istat_code'], 3, '0', STR_PAD_LEFT);
                $tipo = EntityType::Provincia;
            } elseif (isset($props['cod_istat'])) {
                $istatCode = str_pad((string) $props['cod_istat'], 6, '0', STR_PAD_LEFT);
                $tipo = EntityType::Comune;
            } elseif (isset($props['cod_prov'])) {
                $istatCode = str_pad((string) $props['cod_prov'], 3, '0', STR_PAD_LEFT);
                $tipo = EntityType::Provincia;
            } elseif (isset($props['PRO_COM_T'])) {
                $istatCode = str_pad((string) $props['PRO_COM_T'], 6, '0', STR_PAD_LEFT);
                $tipo = EntityType::Comune;
            } elseif (isset($props['codice_istat'], $props['COD_ISTAT'])) {
                $raw = (string) ($props['codice_istat'] ?? $props['COD_ISTAT']);
                $istatCode = $raw;
                $tipo = match (strlen($raw)) {
                    3 => EntityType::Provincia,
                    6 => EntityType::Comune,
                    default => null,
                };
            }

            if ($istatCode === null) {
                $this->warn('Feature senza codice ISTAT: saltata.');
                $skipped++;

                continue;
            }

            if ($tipo === null) {
                $this->warn("[{$istatCode}] Tipo non determinabile: saltata.");
                $skipped++;

                continue;
            }

            $nome = (string) ($props['name'] ?? $props['prov_name'] ?? $props['nome'] ?? $istatCode);

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
