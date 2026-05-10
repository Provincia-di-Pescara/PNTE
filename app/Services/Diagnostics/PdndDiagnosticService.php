<?php

declare(strict_types=1);

namespace App\Services\Diagnostics;

use App\Models\Setting;
use App\Services\Diagnostics\Contracts\DiagnosticInterface;
use App\Services\PdndTokenService;
use Throwable;

final class PdndDiagnosticService implements DiagnosticInterface
{
    public function __construct(private readonly PdndTokenService $pdnd) {}

    public function key(): string
    {
        return 'pdnd';
    }

    public function label(): string
    {
        return 'PDND voucher';
    }

    public function run(): DiagnosticResult
    {
        $start = hrtime(true);

        if (Setting::get('pdnd_enabled', '0') !== '1') {
            return DiagnosticResult::fail(
                service: $this->key(),
                error: 'PDND non abilitato (Setting `pdnd_enabled` ≠ 1)',
                latencyMs: $this->latencyMs($start),
            );
        }

        $tokenEndpoint = (string) (Setting::get('pdnd_token_endpoint') ?? '');
        $clientId = (string) (Setting::get('pdnd_client_id') ?? '');

        if ($tokenEndpoint === '' || $clientId === '') {
            return DiagnosticResult::fail(
                service: $this->key(),
                error: 'PDND credenziali incomplete (token_endpoint + client_id)',
                latencyMs: $this->latencyMs($start),
            );
        }

        try {
            $bundle = $this->pdnd->getToken('GET', $tokenEndpoint);

            return DiagnosticResult::ok(
                service: $this->key(),
                latencyMs: $this->latencyMs($start),
                version: 'PDND DPoP RFC 9449',
                details: [
                    'token_endpoint' => $tokenEndpoint,
                    'client_id' => $clientId,
                    'access_token_present' => ! empty($bundle['access_token']),
                    'dpop_proof_present' => ! empty($bundle['dpop_proof']),
                ],
            );
        } catch (Throwable $e) {
            return DiagnosticResult::fail(
                service: $this->key(),
                error: $e->getMessage(),
                latencyMs: $this->latencyMs($start),
                details: ['token_endpoint' => $tokenEndpoint],
            );
        }
    }

    private function latencyMs(int $start): int
    {
        return (int) round((hrtime(true) - $start) / 1_000_000);
    }
}
