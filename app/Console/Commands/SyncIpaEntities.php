<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\IpaSyncService;
use Illuminate\Console\Command;
use Throwable;

final class SyncIpaEntities extends Command
{
    protected $signature = 'gte:sync-ipa {--dry-run : Simula la sincronizzazione senza salvare}';

    protected $description = 'Sincronizza le PEC degli enti tramite AgID IPA via PDND Interoperabilità';

    public function handle(IpaSyncService $ipa): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('⚡ DRY RUN — nessuna modifica verrà salvata.');
        }

        $this->info('Avvio sincronizzazione IPA...');

        try {
            $result = $ipa->syncAll($dryRun);
        } catch (Throwable $e) {
            $this->error('Sincronizzazione fallita: '.$e->getMessage());

            return self::FAILURE;
        }

        foreach ($result['log'] as $line) {
            if (str_starts_with($line, '[ERR]')) {
                $this->error($line);
            } elseif (str_starts_with($line, '[OK]')) {
                $this->info($line);
            } else {
                $this->line($line);
            }
        }

        $this->newLine();
        $this->table(['Aggiornati', 'Invariati', 'Errori'], [
            [$result['updated'], $result['skipped'], $result['errors']],
        ]);

        if (! $dryRun) {
            Setting::set('ipa.last_sync_at', now()->toIso8601String(), 'ipa');
            Setting::set('ipa.last_sync_result', json_encode([
                'updated' => $result['updated'],
                'skipped' => $result['skipped'],
                'errors' => $result['errors'],
            ]), 'ipa');
        }

        if ($result['errors'] > 0) {
            $this->warn('Sincronizzazione completata con '.$result['errors'].' errori.');
        } else {
            $this->info('Sincronizzazione completata con successo.');
        }

        return self::SUCCESS;
    }
}
