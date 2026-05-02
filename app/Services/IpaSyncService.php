<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Entity;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Synchronises the PEC addresses of entities by querying the AgID IPA API
 * (RICERCA_ENTE o AOO o UO-IPA_40 v4) via PDND Interoperabilità.
 *
 * Requires PDND credentials and the IPA e-service URL configured in settings.
 */
final class IpaSyncService
{
    public function __construct(
        private readonly PdndTokenService $pdnd,
    ) {}

    /**
     * @return array{updated: int, skipped: int, errors: int, log: array<string>}
     */
    public function syncAll(bool $dryRun = false): array
    {
        $baseUrl = rtrim((string) Setting::get('pdnd_ipa_url', ''), '/');

        if (empty($baseUrl)) {
            throw new RuntimeException('URL e-service IPA non configurato nel pannello PDND.');
        }

        $entities = Entity::query()
            ->whereNotNull('codice_istat')
            ->whereNotNull('tipo')
            ->get();

        $updated = 0;
        $skipped = 0;
        $errors = 0;
        $log = [];

        foreach ($entities as $entity) {
            try {
                $pec = $this->fetchPec($baseUrl, $entity->codice_istat);

                if ($pec === null) {
                    $skipped++;
                    $log[] = "[SKIP] {$entity->nome} ({$entity->codice_istat}) — nessun dato PEC";

                    continue;
                }

                if ($entity->pec === $pec) {
                    $skipped++;
                    $log[] = "[SKIP] {$entity->nome} — PEC invariata ({$pec})";

                    continue;
                }

                $oldPec = $entity->pec ?? '(vuota)';

                if (! $dryRun) {
                    $entity->update(['pec' => $pec]);
                }

                $updated++;
                $log[] = "[OK] {$entity->nome} — PEC ".($dryRun ? 'sarebbe aggiornata' : 'aggiornata').": {$oldPec} → {$pec}";
            } catch (RuntimeException $e) {
                $errors++;
                $log[] = "[ERR] {$entity->nome} ({$entity->codice_istat}): {$e->getMessage()}";
                Log::warning('IpaSyncService: errore su '.$entity->nome, ['error' => $e->getMessage()]);
            }
        }

        return compact('updated', 'skipped', 'errors', 'log');
    }

    private function fetchPec(string $baseUrl, string $codiceIstat): ?string
    {
        $url = "{$baseUrl}/enti";

        ['access_token' => $token, 'dpop_proof' => $dpop] = $this->pdnd->getToken('GET', $url);

        $response = Http::withHeaders([
            'Authorization' => "DPoP {$token}",
            'DPoP' => $dpop,
            'Accept' => 'application/json',
        ])->get($url, [
            'cod_istat' => $codiceIstat,
            'limit' => 5,
        ]);

        if ($response->status() === 401) {
            // Token expired mid-sync — clear cache and retry once
            $this->pdnd->forgetToken();
            ['access_token' => $token, 'dpop_proof' => $dpop] = $this->pdnd->getToken('GET', $url);

            $response = Http::withHeaders([
                'Authorization' => "DPoP {$token}",
                'DPoP' => $dpop,
                'Accept' => 'application/json',
            ])->get($url, [
                'cod_istat' => $codiceIstat,
                'limit' => 5,
            ]);
        }

        if (! $response->successful()) {
            throw new RuntimeException('HTTP '.$response->status());
        }

        $data = $response->json();

        // IPA API returns `data` array with ente objects containing `Pec_Ep` or `pec`
        $items = $data['data'] ?? $data['results'] ?? (is_array($data) && isset($data[0]) ? $data : []);

        foreach ($items as $item) {
            $pec = $item['Pec_Ep'] ?? $item['pec'] ?? $item['PEC'] ?? null;

            if (! empty($pec) && str_contains((string) $pec, '@')) {
                return strtolower((string) $pec);
            }
        }

        return null;
    }
}
