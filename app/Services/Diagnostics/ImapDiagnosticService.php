<?php

declare(strict_types=1);

namespace App\Services\Diagnostics;

use App\Models\Setting;
use App\Services\Diagnostics\Contracts\DiagnosticInterface;
use Throwable;

final class ImapDiagnosticService implements DiagnosticInterface
{
    public function key(): string
    {
        return 'imap';
    }

    public function label(): string
    {
        return 'PEC / IMAP listener';
    }

    public function run(): DiagnosticResult
    {
        $start = hrtime(true);

        $host = (string) (Setting::get('pec_host') ?? '');
        $port = (int) (Setting::get('pec_port') ?? 993);

        if ($host === '') {
            return DiagnosticResult::fail(
                service: $this->key(),
                error: 'PEC host non configurato (Setting `pec_host`)',
                latencyMs: $this->latencyMs($start),
            );
        }

        try {
            $errno = 0;
            $errstr = '';
            $useTls = $port === 993;
            $remote = ($useTls ? 'tls://' : 'tcp://').$host.':'.$port;

            $socket = @stream_socket_client($remote, $errno, $errstr, 5.0);

            if ($socket === false) {
                return DiagnosticResult::fail(
                    service: $this->key(),
                    error: sprintf('Connessione %s fallita (%d %s)', $remote, $errno, $errstr),
                    latencyMs: $this->latencyMs($start),
                );
            }

            stream_set_timeout($socket, 5);
            $banner = (string) fgets($socket, 512);

            @fclose($socket);

            $isImap = str_contains($banner, '* OK') || str_contains($banner, 'IMAP');

            if (! $isImap) {
                return DiagnosticResult::fail(
                    service: $this->key(),
                    error: 'Banner IMAP non valido: '.trim($banner),
                    latencyMs: $this->latencyMs($start),
                    details: ['host' => $host, 'port' => $port],
                );
            }

            return DiagnosticResult::ok(
                service: $this->key(),
                latencyMs: $this->latencyMs($start),
                version: trim($banner),
                details: [
                    'host' => $host,
                    'port' => $port,
                    'tls' => $useTls,
                    'username_configured' => ! empty(Setting::get('pec_username')),
                ],
            );
        } catch (Throwable $e) {
            return DiagnosticResult::fail(
                service: $this->key(),
                error: $e->getMessage(),
                latencyMs: $this->latencyMs($start),
                details: ['host' => $host, 'port' => $port],
            );
        }
    }

    private function latencyMs(int $start): int
    {
        return (int) round((hrtime(true) - $start) / 1_000_000);
    }
}
