<?php

declare(strict_types=1);

namespace App\Services\Diagnostics;

use App\Models\Setting;
use App\Services\Diagnostics\Contracts\DiagnosticInterface;
use Illuminate\Http\Client\Factory as HttpFactory;
use Throwable;

final class PagoPaDiagnosticService implements DiagnosticInterface
{
    public function __construct(private readonly HttpFactory $http) {}

    public function key(): string
    {
        return 'pagopa';
    }

    public function label(): string
    {
        return 'PagoPA';
    }

    public function run(): DiagnosticResult
    {
        $start = hrtime(true);

        $apiKey = (string) (Setting::get('pagopa_api_key') ?? '');
        $stationId = (string) (Setting::get('pagopa_station_id') ?? '');
        $baseUrl = (string) (Setting::get('pagopa_base_url') ?? '');

        if ($apiKey === '' && $stationId === '') {
            return DiagnosticResult::fail(
                service: $this->key(),
                error: 'PagoPA non configurato (api_key o station_id)',
                latencyMs: $this->latencyMs($start),
            );
        }

        if ($baseUrl === '') {
            return DiagnosticResult::ok(
                service: $this->key(),
                latencyMs: $this->latencyMs($start),
                version: 'PagoPA cred. presenti (no ping)',
                details: [
                    'api_key_format_ok' => $this->validApiKeyShape($apiKey),
                    'station_id_present' => $stationId !== '',
                    'base_url_configured' => false,
                ],
            );
        }

        try {
            $response = $this->http
                ->timeout(5)
                ->withHeaders(['Ocp-Apim-Subscription-Key' => $apiKey])
                ->acceptJson()
                ->get(rtrim($baseUrl, '/').'/info');

            $ok = $response->status() < 500;

            return new DiagnosticResult(
                service: $this->key(),
                ok: $ok,
                latencyMs: $this->latencyMs($start),
                version: $ok ? 'PagoPA HTTP '.$response->status() : null,
                details: [
                    'base_url' => $baseUrl,
                    'http_status' => $response->status(),
                    'api_key_format_ok' => $this->validApiKeyShape($apiKey),
                    'station_id_present' => $stationId !== '',
                ],
                error: $ok ? null : 'HTTP '.$response->status(),
                checkedAt: now()->toImmutable(),
            );
        } catch (Throwable $e) {
            return DiagnosticResult::fail(
                service: $this->key(),
                error: $e->getMessage(),
                latencyMs: $this->latencyMs($start),
                details: ['base_url' => $baseUrl],
            );
        }
    }

    private function validApiKeyShape(string $key): bool
    {
        return $key !== '' && preg_match('/^[A-Za-z0-9_-]{16,}$/', $key) === 1;
    }

    private function latencyMs(int $start): int
    {
        return (int) round((hrtime(true) - $start) / 1_000_000);
    }
}
