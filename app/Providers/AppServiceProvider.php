<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Tariff;
use App\Models\Vehicle;
use App\Policies\TariffPolicy;
use App\Policies\VehiclePolicy;
use App\Socialite\OidcProvider;
use Illuminate\Support\Facades\Gate;
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

        Gate::policy(Vehicle::class, VehiclePolicy::class);
        Gate::policy(Tariff::class, TariffPolicy::class);
    }
}
