<?php

declare(strict_types=1);

namespace App\Providers;

use App\Socialite\OidcProvider;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Socialite::extend('oidc', function (): OidcProvider {
            /** @var array<string, mixed> $config */
            $config = config('services.oidc');

            return Socialite::buildProvider(OidcProvider::class, $config);
        });
    }
}
