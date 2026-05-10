<?php

declare(strict_types=1);

namespace App\Services\Diagnostics;

use App\Services\Diagnostics\Contracts\DiagnosticInterface;
use Illuminate\Http\Client\Factory as HttpFactory;
use Throwable;

final class OidcDiagnosticService implements DiagnosticInterface
{
    public function __construct(private readonly HttpFactory $http) {}

    public function key(): string
    {
        return 'oidc';
    }

    public function label(): string
    {
        return 'OIDC SPID/CIE';
    }

    public function run(): DiagnosticResult
    {
        $start = hrtime(true);

        $base = (string) config('services.oidc.base_url', '');
        $clientId = (string) config('services.oidc.client_id', '');

        if ($base === '' || $clientId === '') {
            return DiagnosticResult::fail(
                service: $this->key(),
                error: 'OIDC non configurato (services.oidc.base_url + client_id)',
                latencyMs: $this->latencyMs($start),
            );
        }

        $discoveryUrl = rtrim($base, '/').'/.well-known/openid-configuration';

        try {
            $response = $this->http
                ->timeout(5)
                ->acceptJson()
                ->get($discoveryUrl);

            if (! $response->successful()) {
                return DiagnosticResult::fail(
                    service: $this->key(),
                    error: 'Discovery HTTP '.$response->status(),
                    latencyMs: $this->latencyMs($start),
                    details: ['discovery_url' => $discoveryUrl],
                );
            }

            $payload = (array) $response->json();
            $required = ['issuer', 'authorization_endpoint', 'token_endpoint', 'jwks_uri'];
            $missing = array_filter($required, fn (string $k) => empty($payload[$k]));

            if (! empty($missing)) {
                return DiagnosticResult::fail(
                    service: $this->key(),
                    error: 'Discovery incompleto: mancano '.implode(', ', $missing),
                    latencyMs: $this->latencyMs($start),
                    details: ['discovery_url' => $discoveryUrl],
                );
            }

            return DiagnosticResult::ok(
                service: $this->key(),
                latencyMs: $this->latencyMs($start),
                version: 'OpenID Connect Discovery 1.0',
                details: [
                    'issuer' => (string) $payload['issuer'],
                    'authorization_endpoint' => (string) $payload['authorization_endpoint'],
                    'token_endpoint' => (string) $payload['token_endpoint'],
                    'jwks_uri' => (string) $payload['jwks_uri'],
                    'client_id' => $clientId,
                ],
            );
        } catch (Throwable $e) {
            return DiagnosticResult::fail(
                service: $this->key(),
                error: $e->getMessage(),
                latencyMs: $this->latencyMs($start),
                details: ['discovery_url' => $discoveryUrl],
            );
        }
    }

    private function latencyMs(int $start): int
    {
        return (int) round((hrtime(true) - $start) / 1_000_000);
    }
}
