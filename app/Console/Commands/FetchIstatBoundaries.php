<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

final class FetchIstatBoundaries extends Command
{
    protected $signature = 'gte:fetch-istat-boundaries {tipo : comuni o province}';

    protected $description = 'Scarica i confini ISTAT da openpolis/geojson-italy e importa nelle entities';

    private const URLS = [
        'comuni' => 'https://raw.githubusercontent.com/openpolis/geojson-italy/master/geojson/limits_IT_municipalities.geojson',
        'province' => 'https://raw.githubusercontent.com/openpolis/geojson-italy/master/geojson/limits_IT_provinces.geojson',
    ];

    public function handle(): int
    {
        $tipo = (string) $this->argument('tipo');

        if (! array_key_exists($tipo, self::URLS)) {
            $this->error('Tipo non valido. Usa: comuni o province');

            return self::FAILURE;
        }

        $url = self::URLS[$tipo];

        $this->info("Download da: {$url}");

        $response = Http::timeout(120)->get($url);

        if (! $response->successful()) {
            $this->error('Download fallito: HTTP '.$response->status());

            return self::FAILURE;
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'istat_'.$tipo.'_').'.geojson';

        if ($tmpFile === false) {
            $this->error('Impossibile creare file temporaneo.');

            return self::FAILURE;
        }

        file_put_contents($tmpFile, $response->body());

        $this->info('File scaricato. Avvio importazione...');

        try {
            Artisan::call('gte:import-geo', ['file' => $tmpFile], $this->getOutput());
        } finally {
            @unlink($tmpFile);
        }

        $this->info('Importazione '.$tipo.' completata.');

        return self::SUCCESS;
    }
}
