<?php

declare(strict_types=1);

namespace App\Console\Commands\Diagnostics;

use App\Services\Diagnostics\HealthCheckService;
use Illuminate\Console\Command;

/**
 * Run a single diagnostic service by key.
 */
final class RunSingleDiagnosticCommand extends Command
{
    protected $signature = 'gte:diag:run
        {service : Diagnostic key (db, postgis, redis, queue, storage, osrm, smtp, imap, oidc, pdnd, pagopa, ainop, routing)}
        {--json : Output as JSON}
        {--no-audit : Skip writing system_audit_logs entry}';

    protected $description = 'Esegue un singolo diagnostic service.';

    public function handle(HealthCheckService $health): int
    {
        $key = (string) $this->argument('service');

        try {
            $result = $health->runOne($key, audit: ! $this->option('no-audit'));
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());

            $available = array_keys($health->diagnostics());
            $this->line('Disponibili: '.implode(', ', $available));

            return 2;
        }

        if ($this->option('json')) {
            $this->line((string) json_encode($result->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return $result->ok ? self::SUCCESS : self::FAILURE;
        }

        $this->line(sprintf(
            '%s · %s · %d ms · %s%s',
            $result->ok ? '<info>OK</info>' : '<error>FAIL</error>',
            $result->service,
            $result->latencyMs,
            $result->version ?? '',
            $result->error ? "\n  ".$result->error : ''
        ));

        return $result->ok ? self::SUCCESS : self::FAILURE;
    }
}
