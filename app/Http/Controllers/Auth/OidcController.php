<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\AuthProvider;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

final class OidcController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('oidc')->redirect();
    }

    public function callback(): RedirectResponse
    {
        $oidcUser = Socialite::driver('oidc')->user();

        /** @var array<string, mixed> $raw */
        $raw = $oidcUser->getRaw();

        $cf = strtoupper(trim((string) ($raw['fiscalNumber'] ?? $raw['fiscal_number'] ?? '')));

        $user = User::firstOrCreate(
            ['codice_fiscale' => $cf ?: null, 'auth_provider' => AuthProvider::Spid->value],
            [
                'name' => $oidcUser->getName() ?: $cf,
                'email' => $oidcUser->getEmail(),
                'nome_verificato' => (string) ($raw['given_name'] ?? ''),
                'cognome_verificato' => (string) ($raw['family_name'] ?? ''),
                'provider_id' => $oidcUser->getId(),
                'auth_provider' => AuthProvider::Spid,
            ]
        );

        if (! $user->hasRole(UserRole::Citizen->value)) {
            $user->assignRole(UserRole::Citizen->value);
        }

        Auth::login($user);

        return redirect()->intended(route('dashboard'));
    }
}
