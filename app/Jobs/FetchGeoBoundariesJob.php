<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\SystemAuditLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class FetchGeoBoundariesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public int $tries = 3;

    public function __construct(
        public readonly string $tipo,
        public readonly string $sourceUrl,
        public readonly int $actorId,
        public readonly string $actorName,
    ) {}

    public function handle(): void
    {
        Cache::put('geo_import_status', [
            'status' => 'downloading',
            'tipo' => $this->tipo,
            'step' => 'Scaricando confini '.($this->tipo === 'comuni' ? 'comunali' : 'provinciali').'...',
            'started_at' => now()->toIso8601String(),
            'completed_at' => null,
            'error' => null,
            'result' => null,
        ], 3600);

        $filename = 'geo-imports/'.Str::uuid().'.geojson';
        $fullPath = Storage::disk('local')->path($filename);

        Storage::disk('local')->makeDirectory('geo-imports');

        $downloaded = false;

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                $response = Http::withOptions(['sink' => $fullPath])
                    ->timeout(300)
                    ->get($this->sourceUrl);

                if ($response->successful()) {
                    $downloaded = true;
                    break;
                }
            } catch (\Throwable) {
                // retry
            }
        }

        if (! $downloaded) {
            Storage::disk('local')->delete($filename);
            $this->markFailed('Download fallito dopo 3 tentativi da: '.$this->sourceUrl);

            return;
        }

        Cache::put('geo_import_status', [
            'status' => 'importing',
            'tipo' => $this->tipo,
            'step' => 'Importando nel database...',
            'started_at' => Cache::get('geo_import_status.started_at') ?? now()->toIso8601String(),
            'completed_at' => null,
            'error' => null,
            'result' => null,
        ], 3600);

        $exitCode = Artisan::call('pnte:import-geo', ['file' => $fullPath]);

        Storage::disk('local')->delete($filename);

        if ($exitCode !== 0) {
            $this->markFailed('Importazione fallita (exit '.$exitCode.').');

            return;
        }

        $output = Artisan::output();
        $result = $this->parseImportOutput($output);

        Cache::put('geo_import_status', [
            'status' => 'completed',
            'tipo' => $this->tipo,
            'step' => 'Completato.',
            'started_at' => now()->toIso8601String(),
            'completed_at' => now()->toIso8601String(),
            'error' => null,
            'result' => $result,
        ], 3600);

        SystemAuditLog::query()->create([
            'actor_id' => $this->actorId,
            'actor_name' => $this->actorName,
            'action' => 'geo.fetch-istat',
            'detail' => sprintf(
                'Import %s completato — Aggiornate: %d / Create: %d / Saltate: %d',
                $this->tipo,
                $result['updated'],
                $result['created'],
                $result['skipped'],
            ),
            'created_at' => now(),
        ]);
    }

    public function failed(\Throwable $e): void
    {
        $this->markFailed($e->getMessage());
    }

    private function markFailed(string $error): void
    {
        Cache::put('geo_import_status', [
            'status' => 'failed',
            'tipo' => $this->tipo,
            'step' => 'Importazione fallita.',
            'started_at' => null,
            'completed_at' => now()->toIso8601String(),
            'error' => $error,
            'result' => null,
        ], 3600);

        SystemAuditLog::query()->create([
            'actor_id' => $this->actorId,
            'actor_name' => $this->actorName,
            'action' => 'geo.fetch-istat',
            'detail' => 'Import '.$this->tipo.' fallito: '.$error,
            'created_at' => now(),
        ]);
    }

    /** @return array{updated: int, created: int, skipped: int} */
    private function parseImportOutput(string $output): array
    {
        preg_match('/Aggiornate:\s*(\d+).*Create:\s*(\d+).*Saltate:\s*(\d+)/s', $output, $m);

        return [
            'updated' => (int) ($m[1] ?? 0),
            'created' => (int) ($m[2] ?? 0),
            'skipped' => (int) ($m[3] ?? 0),
        ];
    }
}
