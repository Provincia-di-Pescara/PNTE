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
use Illuminate\Support\Facades\Storage;

final class ImportGeoFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public int $tries = 3;

    public function __construct(
        public readonly string $storagePath,
        public readonly string $originalName,
        public readonly int $actorId,
        public readonly string $actorName,
    ) {}

    public function handle(): void
    {
        Cache::put('geo_import_status', [
            'status' => 'importing',
            'tipo' => 'upload',
            'step' => 'Importando file '.$this->originalName.'...',
            'started_at' => now()->toIso8601String(),
            'completed_at' => null,
            'error' => null,
            'result' => null,
        ], 3600);

        $exitCode = Artisan::call('pnte:import-geo', ['file' => $this->storagePath]);

        Storage::disk('local')->delete(
            str_replace(Storage::disk('local')->path(''), '', $this->storagePath)
        );

        if ($exitCode !== 0) {
            Cache::put('geo_import_status', [
                'status' => 'failed',
                'tipo' => 'upload',
                'step' => 'Importazione fallita.',
                'started_at' => null,
                'completed_at' => now()->toIso8601String(),
                'error' => 'Importazione fallita (exit '.$exitCode.').',
                'result' => null,
            ], 3600);

            SystemAuditLog::query()->create([
                'actor_id' => $this->actorId,
                'actor_name' => $this->actorName,
                'action' => 'geo.import-file',
                'detail' => 'Import fallito: '.$this->originalName,
                'created_at' => now(),
            ]);

            return;
        }

        $output = Artisan::output();
        $result = $this->parseImportOutput($output);

        Cache::put('geo_import_status', [
            'status' => 'completed',
            'tipo' => 'upload',
            'step' => 'Completato.',
            'started_at' => now()->toIso8601String(),
            'completed_at' => now()->toIso8601String(),
            'error' => null,
            'result' => $result,
        ], 3600);

        SystemAuditLog::query()->create([
            'actor_id' => $this->actorId,
            'actor_name' => $this->actorName,
            'action' => 'geo.import-file',
            'detail' => sprintf(
                'Import %s completato — Aggiornate: %d / Create: %d / Saltate: %d',
                $this->originalName,
                $result['updated'],
                $result['created'],
                $result['skipped'],
            ),
            'created_at' => now(),
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Storage::disk('local')->delete(
            str_replace(Storage::disk('local')->path(''), '', $this->storagePath)
        );

        Cache::put('geo_import_status', [
            'status' => 'failed',
            'tipo' => 'upload',
            'step' => 'Importazione fallita.',
            'started_at' => null,
            'completed_at' => now()->toIso8601String(),
            'error' => $e->getMessage(),
            'result' => null,
        ], 3600);

        SystemAuditLog::query()->create([
            'actor_id' => $this->actorId,
            'actor_name' => $this->actorName,
            'action' => 'geo.import-file',
            'detail' => 'Import '.$this->originalName.' fallito: '.$e->getMessage(),
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
