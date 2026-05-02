<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\InfoCamereServiceInterface;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Queries the Unioncamere "Servizi consultazione Registro Imprese" e-service
 * via PDND Interoperabilità to retrieve company data by P.IVA.
 *
 * Requires PDND credentials and the InfoCamere e-service URL configured in
 * settings. Note: the Unioncamere agreement requires manual approval on PDND.
 */
final class InfoCamereService implements InfoCamereServiceInterface
{
    public function __construct(
        private readonly PdndTokenService $pdnd,
        private readonly Setting $settingModel,
    ) {}

    /**
     * Retrieves company data from the Registro Imprese.
     *
     * @return array{ragione_sociale: string, codice_fiscale: string|null, indirizzo: string|null, comune: string|null, cap: string|null, provincia: string|null, email: string|null, pec: string|null}
     *
     * @throws RuntimeException when PDND is not configured, company not found, or API error.
     */
    public function getByPiva(string $piva): array
    {
        $baseUrl = rtrim((string) Setting::get('pdnd_infocamere_url', ''), '/');

        if (empty($baseUrl)) {
            throw new RuntimeException('URL e-service InfoCamere non configurato nel pannello PDND.');
        }

        $url = "{$baseUrl}/imprese/{$piva}";

        ['access_token' => $token, 'dpop_proof' => $dpop] = $this->pdnd->getToken('GET', $url);

        $response = Http::withHeaders([
            'Authorization' => "DPoP {$token}",
            'DPoP' => $dpop,
            'Accept' => 'application/json',
        ])->get($url);

        if ($response->status() === 401) {
            $this->pdnd->forgetToken();
            ['access_token' => $token, 'dpop_proof' => $dpop] = $this->pdnd->getToken('GET', $url);

            $response = Http::withHeaders([
                'Authorization' => "DPoP {$token}",
                'DPoP' => $dpop,
                'Accept' => 'application/json',
            ])->get($url);
        }

        if ($response->status() === 404) {
            throw new RuntimeException("Impresa con P.IVA {$piva} non trovata nel Registro Imprese.");
        }

        if (! $response->successful()) {
            throw new RuntimeException('Errore Registro Imprese: HTTP '.$response->status().' — '.$response->body());
        }

        return $this->mapResponse($response->json());
    }

    /**
     * Maps the API response to the Company model fields.
     * Tolerates different field naming conventions across Unioncamere API versions.
     *
     * @param  array<string, mixed>  $data
     * @return array{ragione_sociale: string, codice_fiscale: string|null, indirizzo: string|null, comune: string|null, cap: string|null, provincia: string|null, email: string|null, pec: string|null}
     */
    private function mapResponse(array $data): array
    {
        // Flatten nested structures (some endpoints wrap in `data` or `impresa`)
        $item = $data['data'] ?? $data['impresa'] ?? $data;

        $ragioneSociale = $item['denominazione'] ?? $item['ragione_sociale'] ?? $item['nome'] ?? '';

        if (empty($ragioneSociale)) {
            throw new RuntimeException('Risposta InfoCamere non contiene denominazione azienda.');
        }

        $sede = $item['sede_legale'] ?? $item['sede'] ?? $item ?? [];

        return [
            'ragione_sociale' => (string) $ragioneSociale,
            'codice_fiscale' => $this->nullable($item['codice_fiscale'] ?? $item['cf'] ?? null),
            'indirizzo' => $this->nullable(
                isset($sede['indirizzo'])
                    ? $sede['indirizzo']
                    : (isset($sede['via']) ? trim(($sede['via'] ?? '').' '.($sede['civico'] ?? '')) : null)
            ),
            'comune' => $this->nullable($sede['comune'] ?? $sede['localita'] ?? null),
            'cap' => $this->nullable($sede['cap'] ?? null),
            'provincia' => $this->nullable($sede['provincia'] ?? $sede['sigla_provincia'] ?? null),
            'email' => $this->nullable($item['email'] ?? $item['mail'] ?? null),
            'pec' => $this->nullable($item['pec'] ?? $item['email_pec'] ?? null),
        ];
    }

    private function nullable(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }
}
