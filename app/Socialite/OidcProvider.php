<?php

declare(strict_types=1);

namespace App\Socialite;

use Illuminate\Support\Arr;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User;

/**
 * Generic OIDC provider for Laravel Socialite.
 *
 * Talks to any standards-compliant OIDC authorization server (e.g. IAM Proxy
 * Italia) using the authorization-code flow with a confidential client.
 * Endpoints follow the issuer convention; override via OIDC_* env vars if
 * the proxy exposes them at non-standard paths.
 */
final class OidcProvider extends AbstractProvider
{
    protected $scopes = ['openid', 'profile', 'email'];

    protected $scopeSeparator = ' ';

    private function issuer(): string
    {
        return rtrim((string) config('services.oidc.base_url'), '/');
    }

    protected function getAuthUrl($state): string
    {
        $endpoint = config('services.oidc.authorization_endpoint')
            ?? $this->issuer().'/authorization';

        return $this->buildAuthUrlFromBase((string) $endpoint, $state);
    }

    protected function getTokenUrl(): string
    {
        return (string) (config('services.oidc.token_endpoint')
            ?? $this->issuer().'/token');
    }

    /** @return array<string, mixed> */
    protected function getUserByToken($token): array
    {
        $endpoint = (string) (config('services.oidc.userinfo_endpoint')
            ?? $this->issuer().'/userinfo');

        $response = $this->getHttpClient()->get($endpoint, [
            'headers' => ['Authorization' => 'Bearer '.$token],
        ]);

        return (array) json_decode((string) $response->getBody(), true);
    }

    /** @param array<string, mixed> $user */
    protected function mapUserToObject(array $user): User
    {
        $givenName = (string) Arr::get($user, 'given_name', '');
        $familyName = (string) Arr::get($user, 'family_name', '');
        $name = trim($givenName.' '.$familyName) ?: Arr::get($user, 'sub', '');

        return (new User)->setRaw($user)->map([
            'id' => Arr::get($user, 'sub'),
            'name' => $name,
            'email' => Arr::get($user, 'email'),
        ]);
    }
}
