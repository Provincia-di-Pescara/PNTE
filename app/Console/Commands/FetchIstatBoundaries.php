<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

final class FetchIstatBoundaries extends Command
{
    protected $signature = 'pnte:fetch-istat-boundaries {tipo : comuni o province} {--url= : URL sorgente personalizzato}';

    protected $description = 'Scarica i confini ISTAT da openpolis/geojson-italy e importa nelle entities (stream su disco, retry 3x)';

    public const DEFAULT_URLS = [
        'comuni' => 'https://raw.githubusercontent.com/openpolis/geojson-italy/master/geojson/limits_IT_municipalities.geojson',
        'province' => 'https://raw.githubusercontent.com/openpolis/geojson-italy/master/geojson/limits_IT_provinces.geojson',
    ];

    public function handle(): int
    {
        $tipo = (string) $this->argument('tipo');

        if (! array_key_exists($tipo, self::DEFAULT_URLS)) {
            $this->error('Tipo non valido. Usa: comuni o province');

            return self::FAILURE;
        }

        $url = (string) ($this->option('url') ?? self::DEFAULT_URLS[$tipo]);

        $this->info("Sorgente: {$url}");

        $tmpFile = tempnam(sys_get_temp_dir(), 'istat_'.$tipo.'_').'.geojson';

        if ($tmpFile === false) {
            $this->error('Impossibile creare file temporaneo.');

            return self::FAILURE;
        }

        $downloaded = false;

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $this->info("Download tentativo {$attempt}/3...");

            try {
                $response = Http::withOptions(['sink' => $tmpFile])
                    ->timeout(300)
                    ->get($url);

                if ($response->successful()) {
                    $downloaded = true;
                    break;
                }

                $this->warn("HTTP {$response->status()} — riprovo...");
            } catch (\Throwable $e) {
                $this->warn("Errore rete: {$e->getMessage()} — riprovo...");
            }
        }

        if (! $downloaded) {
            @unlink($tmpFile);
            $this->error('Download fallito dopo 3 tentativi.');

            return self::FAILURE;
        }

        $size = round(filesize($tmpFile) / 1024 / 1024, 1);
        $this->info("File scaricato ({$size} MB). Avvio importazione...");

        try {
            Artisan::call('pnte:import-geo', ['file' => $tmpFile], $this->getOutput());
        } finally {
            @unlink($tmpFile);
        }

        $this->info("Importazione {$tipo} completata.");

        return self::SUCCESS;
    }
}
