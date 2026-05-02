<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Generates PDND-compliant DPoP-bound access tokens.
 *
 * The client authenticates via a private_key_jwt client assertion (RS256)
 * and includes a DPoP proof (ES256) with every token request and API call.
 *
 * Spec references:
 *   - RFC 9449 — DPoP: Sender-Constrained Access Tokens
 *   - PDND Interoperability API v3 — securitySchemes.DPoPAuth
 *   - AgID Linee Guida Interoperabilità — Pattern INTEGRITY_REST_02
 */
final class PdndTokenService
{
    private const CACHE_KEY = 'pdnd_access_token';

    private const CLOCK_SKEW_SECONDS = 30;

    /**
     * Returns a valid DPoP-bound access token and a fresh DPoP proof for
     * the given HTTP method and URI.
     *
     * @return array{access_token: string, dpop_proof: string}
     */
    public function getToken(string $httpMethod = 'POST', string $uri = ''): array
    {
        $this->assertEnabled();

        $accessToken = Cache::get(self::CACHE_KEY);

        if (! $accessToken) {
            $accessToken = $this->fetchNewToken();
        }

        return [
            'access_token' => $accessToken,
            'dpop_proof' => $this->buildDpopProof($httpMethod, $uri, $accessToken),
        ];
    }

    /**
     * Clears the cached token (e.g. after a 401 to force refresh).
     */
    public function forgetToken(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function assertEnabled(): void
    {
        if (Setting::get('pdnd_enabled', '0') !== '1') {
            throw new RuntimeException('PDND non configurato. Abilita e configura il pannello PDND / Interoperabilità.');
        }
    }

    private function fetchNewToken(): string
    {
        $tokenEndpoint = $this->require('pdnd_token_endpoint');
        $clientId = $this->require('pdnd_client_id');
        $privateKeyPem = $this->require('pdnd_private_key');

        $now = time();
        $jti = bin2hex(random_bytes(16));

        $assertion = JWT::encode([
            'iss' => $clientId,
            'sub' => $clientId,
            'aud' => $tokenEndpoint,
            'jti' => $jti,
            'iat' => $now,
            'exp' => $now + 600, // 10 minutes — PDND max
        ], $privateKeyPem, 'RS256');

        $dpopForTokenRequest = $this->buildDpopProof('POST', $tokenEndpoint);

        $response = Http::withHeaders(['DPoP' => $dpopForTokenRequest])
            ->asForm()
            ->post($tokenEndpoint, [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
                'client_assertion' => $assertion,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Errore PDND token endpoint: HTTP '.$response->status().' — '.$response->body()
            );
        }

        $data = $response->json();

        if (empty($data['access_token'])) {
            throw new RuntimeException('PDND non ha restituito access_token: '.$response->body());
        }

        $accessToken = (string) $data['access_token'];
        $expiresIn = (int) ($data['expires_in'] ?? 3600);
        $ttl = max(60, $expiresIn - self::CLOCK_SKEW_SECONDS);

        Cache::put(self::CACHE_KEY, $accessToken, $ttl);

        return $accessToken;
    }

    /**
     * Builds a DPoP proof JWT (ES256) for a single request.
     *
     * The `ath` claim (access token hash) is included when an access_token is
     * provided, binding the proof to the specific token (RFC 9449 §4.2).
     */
    private function buildDpopProof(string $httpMethod, string $uri, ?string $accessToken = null): string
    {
        $dpopKeyPem = $this->require('pdnd_dpop_private_key');

        $publicKeyResource = openssl_pkey_get_private($dpopKeyPem);

        if ($publicKeyResource === false) {
            throw new RuntimeException('Chiave privata DPoP EC non valida.');
        }

        $keyDetails = openssl_pkey_get_details($publicKeyResource);

        if ($keyDetails === false || ($keyDetails['type'] ?? null) !== OPENSSL_KEYTYPE_EC) {
            throw new RuntimeException('La chiave DPoP deve essere una chiave EC (P-256).');
        }

        // Build JWK (public part) for the `jwk` header claim
        $publicPem = $keyDetails['key'];
        $pubResource = openssl_pkey_get_public($publicPem);
        $pubDetails = openssl_pkey_get_details($pubResource);
        $ecDetails = $pubDetails['ec'] ?? [];

        $jwk = [
            'kty' => 'EC',
            'crv' => 'P-256',
            'x' => $this->base64UrlEncode($ecDetails['x'] ?? ''),
            'y' => $this->base64UrlEncode($ecDetails['y'] ?? ''),
        ];

        $claims = [
            'jti' => bin2hex(random_bytes(16)),
            'htm' => strtoupper($httpMethod),
            'htu' => $uri,
            'iat' => time(),
        ];

        if ($accessToken !== null) {
            $claims['ath'] = $this->base64UrlEncode(hash('sha256', $accessToken, true));
        }

        return JWT::encode(
            $claims,
            $dpopKeyPem,
            'ES256',
            null,
            ['typ' => 'dpop+jwt', 'alg' => 'ES256', 'jwk' => $jwk]
        );
    }

    private function require(string $key): string
    {
        $value = Setting::get($key);

        if (empty($value)) {
            throw new RuntimeException("Impostazione PDND mancante: {$key}. Configura il pannello PDND / Interoperabilità.");
        }

        return (string) $value;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
