<?php

declare(strict_types=1);

namespace App\Services\Diagnostics;

use App\Services\Diagnostics\Contracts\DiagnosticInterface;
use Throwable;

final class SmtpDiagnosticService implements DiagnosticInterface
{
    public function key(): string
    {
        return 'smtp';
    }

    public function label(): string
    {
        return 'SMTP outbound';
    }

    public function run(): DiagnosticResult
    {
        $start = hrtime(true);

        $host = (string) config('mail.mailers.smtp.host', '');
        $port = (int) config('mail.mailers.smtp.port', 0);

        if ($host === '' || $port === 0) {
            return DiagnosticResult::fail(
                service: $this->key(),
                error: 'mail.mailers.smtp.host/port non configurati',
                latencyMs: $this->latencyMs($start),
            );
        }

        try {
            $errno = 0;
            $errstr = '';
            $socket = @fsockopen($host, $port, $errno, $errstr, 5.0);

            if ($socket === false) {
                return DiagnosticResult::fail(
                    service: $this->key(),
                    error: sprintf('TCP %s:%d non raggiungibile (%d %s)', $host, $port, $errno, $errstr),
                    latencyMs: $this->latencyMs($start),
                );
            }

            stream_set_timeout($socket, 5);
            $banner = (string) fgets($socket, 512);

            $isSmtp = str_starts_with($banner, '220');

            @fwrite($socket, "QUIT\r\n");
            @fclose($socket);

            if (! $isSmtp) {
                return DiagnosticResult::fail(
                    service: $this->key(),
                    error: 'Banner SMTP non valido: '.trim($banner),
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
                    'username_configured' => ! empty(config('mail.mailers.smtp.username')),
                    'mailer' => config('mail.default'),
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
