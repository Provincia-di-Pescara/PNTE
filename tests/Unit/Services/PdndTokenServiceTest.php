<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Setting;
use App\Services\PdndTokenService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

final class PdndTokenServiceTest extends TestCase
{
    private function configureSettings(): void
    {
        Setting::set('pdnd_enabled', '1', 'pdnd');
        Setting::set('pdnd_client_id', 'test-client-id', 'pdnd');
        Setting::set('pdnd_token_endpoint', 'https://auth.interop.pagopa.it/token', 'pdnd');

        // Generate a real RSA key for signing in tests
        $rsaKey = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($rsaKey, $rsaPem);
        Setting::set('pdnd_private_key', $rsaPem, 'pdnd');

        // Generate a real EC key for DPoP in tests
        $ecKey = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
        openssl_pkey_export($ecKey, $ecPem);
        Setting::set('pdnd_dpop_private_key', $ecPem, 'pdnd');
    }

    public function test_throws_when_pdnd_disabled(): void
    {
        Setting::set('pdnd_enabled', '0', 'pdnd');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/PDND non configurato/');

        $service = new PdndTokenService;
        $service->getToken();
    }

    public function test_fetches_token_and_caches_it(): void
    {
        $this->configureSettings();
        Cache::forget('pdnd_access_token');

        Http::fake([
            'https://auth.interop.pagopa.it/token' => Http::response([
                'access_token' => 'fake-access-token',
                'token_type' => 'DPoP',
                'expires_in' => 3600,
            ], 200),
        ]);

        $service = new PdndTokenService;
        $result = $service->getToken('GET', 'https://api.example.com/test');

        $this->assertArrayHasKey('access_token', $result);
        $this->assertArrayHasKey('dpop_proof', $result);
        $this->assertSame('fake-access-token', $result['access_token']);
        $this->assertNotEmpty($result['dpop_proof']);

        // Verify cached
        $this->assertSame('fake-access-token', Cache::get('pdnd_access_token'));
    }

    public function test_uses_cached_token_without_http_call(): void
    {
        $this->configureSettings();
        Cache::put('pdnd_access_token', 'cached-token', 3600);

        Http::fake(); // Should NOT be called

        $service = new PdndTokenService;
        $result = $service->getToken('GET', 'https://api.example.com/test');

        $this->assertSame('cached-token', $result['access_token']);
        Http::assertNothingSent();
    }

    public function test_throws_when_token_endpoint_returns_error(): void
    {
        $this->configureSettings();
        Cache::forget('pdnd_access_token');

        Http::fake([
            'https://auth.interop.pagopa.it/token' => Http::response(['error' => 'invalid_client'], 401),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Errore PDND token endpoint/');

        $service = new PdndTokenService;
        $service->getToken();
    }

    public function test_dpop_proof_contains_required_jwt_header_structure(): void
    {
        $this->configureSettings();
        Cache::put('pdnd_access_token', 'existing-token', 3600);

        $service = new PdndTokenService;
        $result = $service->getToken('GET', 'https://api.example.com/resource');

        $dpop = $result['dpop_proof'];
        $this->assertNotEmpty($dpop);

        // JWT has 3 dot-separated parts
        $parts = explode('.', $dpop);
        $this->assertCount(3, $parts);

        // Decode the header (first part)
        $header = json_decode(base64_decode(str_pad(strtr($parts[0], '-_', '+/'), strlen($parts[0]) % 4, '=')), true);
        $this->assertSame('dpop+jwt', $header['typ']);
        $this->assertSame('ES256', $header['alg']);
        $this->assertArrayHasKey('jwk', $header);
        $this->assertSame('EC', $header['jwk']['kty']);
        $this->assertSame('P-256', $header['jwk']['crv']);

        // Decode the payload (second part)
        $payload = json_decode(base64_decode(str_pad(strtr($parts[1], '-_', '+/'), strlen($parts[1]) % 4, '=')), true);
        $this->assertSame('GET', $payload['htm']);
        $this->assertSame('https://api.example.com/resource', $payload['htu']);
        $this->assertArrayHasKey('jti', $payload);
        $this->assertArrayHasKey('iat', $payload);
        $this->assertArrayHasKey('ath', $payload); // access token hash
    }

    public function test_forget_token_clears_cache(): void
    {
        Cache::put('pdnd_access_token', 'some-token', 3600);

        $service = new PdndTokenService;
        $service->forgetToken();

        $this->assertNull(Cache::get('pdnd_access_token'));
    }
}
