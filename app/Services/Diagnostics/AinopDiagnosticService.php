<?php

declare(strict_types=1);

namespace App\Services\Diagnostics;

use App\Models\Setting;
use App\Services\Diagnostics\Contracts\DiagnosticInterface;
use Carbon\CarbonImmutable;
use Throwable;

final class AinopDiagnosticService implements DiagnosticInterface
{
    public function key(): string
    {
        return 'ainop';
    }

    public function label(): string
    {
        return 'AINOP X.509';
    }

    public function run(): DiagnosticResult
    {
        $start = hrtime(true);

        $clientId = (string) (Setting::get('ainop_client_id') ?? '');
        $cert = (string) (Setting::get('ainop_certificate') ?? '');
        $fingerprint = (string) (Setting::get('ainop_cert_fingerprint') ?? '');

        if ($clientId === '' && $cert === '' && $fingerprint === '') {
            return DiagnosticResult::fail(
                service: $this->key(),
                error: 'AINOP non configurato (client_id / certificate / fingerprint)',
                latencyMs: $this->latencyMs($start),
            );
        }

        try {
            $details = [
                'client_id_present' => $clientId !== '',
                'certificate_present' => $cert !== '',
                'fingerprint_present' => $fingerprint !== '',
            ];

            if ($cert !== '') {
                $info = @openssl_x509_parse($cert);
                if ($info === false) {
                    return DiagnosticResult::fail(
                        service: $this->key(),
                        error: 'Certificato AINOP non parseable',
                        latencyMs: $this->latencyMs($start),
                        details: $details,
                    );
                }

                $expires = isset($info['validTo_time_t']) ? CarbonImmutable::createFromTimestamp((int) $info['validTo_time_t']) : null;
                $details['cert_subject'] = $info['name'] ?? null;
                $details['cert_expires_at'] = $expires?->toIso8601String();
                $details['cert_days_to_expiry'] = $expires ? (int) now()->diffInDays($expires, false) : null;

                if ($expires && $expires->isPast()) {
                    return DiagnosticResult::fail(
                        service: $this->key(),
                        error: 'Certificato AINOP scaduto il '.$expires->toDateString(),
                        latencyMs: $this->latencyMs($start),
                        details: $details,
                    );
                }
            }

            return DiagnosticResult::ok(
                service: $this->key(),
                latencyMs: $this->latencyMs($start),
                version: 'AINOP X.509',
                details: $details,
            );
        } catch (Throwable $e) {
            return DiagnosticResult::fail(
                service: $this->key(),
                error: $e->getMessage(),
                latencyMs: $this->latencyMs($start),
            );
        }
    }

    private function latencyMs(int $start): int
    {
        return (int) round((hrtime(true) - $start) / 1_000_000);
    }
}
