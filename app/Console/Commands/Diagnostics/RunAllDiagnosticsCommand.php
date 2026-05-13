<?php

declare(strict_types=1);

namespace App\Console\Commands\Diagnostics;

use App\Services\Diagnostics\DiagnosticResult;
use App\Services\Diagnostics\HealthCheckService;
use Illuminate\Console\Command;

/**
 * Run all diagnostic services and report aggregated status.
 *
 * Exit codes:
 *   0 — all services ok
 *   1 — at least one service failed
 *   2 — internal error
 *
 * Suitable for Docker HEALTHCHECK and CI smoke tests.
 */
final class RunAllDiagnosticsCommand extends Command
{
    protected $signature = 'pnte:diag
        {--json : Output as JSON snapshot}
        {--quiet-ok : Suppress per-service stdout when all services succeed}
        {--no-audit : Skip writing system_audit_logs entries}';

    protected $description = 'Esegue tutti i diagnostic services e ritorna lo stato aggregato.';

    public function handle(HealthCheckService $health): int
    {
        try {
            $audit = ! $this->option('no-audit');
            $results = $health->runAll(audit: $audit);
            $summary = $health->summarize($results);

            if ($this->option('json')) {
                $this->line((string) json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

                return $summary['ok'] ? self::SUCCESS : self::FAILURE;
            }

            if (! ($this->option('quiet-ok') && $summary['ok'])) {
                $this->renderTable($results);
            }

            $this->newLine();
            $this->line($summary['ok']
                ? "<info>✓ Tutti i {$summary['service_count']} servizi sono operativi.</info>"
                : '<error>✗ Almeno un servizio è in errore.</error>'
            );

            return $summary['ok'] ? self::SUCCESS : self::FAILURE;
        } catch (\Throwable $e) {
            $this->error('Errore esecuzione diagnostica: '.$e->getMessage());

            return 2;
        }
    }

    /**
     * @param  array<string, DiagnosticResult>  $results
     */
    private function renderTable(array $results): void
    {
        $rows = [];
        foreach ($results as $key => $r) {
            $rows[] = [
                $key,
                $r->ok ? '<info>OK</info>' : '<error>FAIL</error>',
                $r->latencyMs.' ms',
                $r->version ?? '—',
                $r->error ?? '—',
            ];
        }

        $this->table(['Service', 'Status', 'Latency', 'Version', 'Error'], $rows);
    }
}
