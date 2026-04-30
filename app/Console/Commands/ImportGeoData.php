<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Entity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class ImportGeoData extends Command
{
    protected $signature = 'gte:import-geo {file : Percorso al file GeoJSON dei confini territoriali}';

    protected $description = 'Importa confini geografici (GeoJSON) nella colonna geom delle entities';

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
        $notFound = 0;

        foreach ($data['features'] as $feature) {
            $props = $feature['properties'] ?? [];
            $istatCode = $props['cod_istat']
                ?? $props['codice_istat']
                ?? $props['PRO_COM_T']
                ?? $props['COD_ISTAT']
                ?? null;

            if ($istatCode === null) {
                $this->warn('Feature senza codice ISTAT: saltata.');
                $notFound++;

                continue;
            }

            $istatCode = (string) $istatCode;
            $entity = Entity::where('codice_istat', $istatCode)->first();

            if ($entity === null) {
                $this->warn("[{$istatCode}] Entity non trovata: saltata.");
                $notFound++;

                continue;
            }

            DB::statement(
                'UPDATE entities SET geom = ST_GeomFromGeoJSON(?) WHERE id = ?',
                [json_encode($feature['geometry']), $entity->id]
            );
            $updated++;
        }

        $this->info("Completato — Aggiornate: {$updated} / Non trovate: {$notFound}");

        return self::SUCCESS;
    }
}
