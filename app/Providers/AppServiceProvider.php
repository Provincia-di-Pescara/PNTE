<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\OsrmServiceInterface;
use App\Models\Route;
use App\Models\Tariff;
use App\Models\Vehicle;
use App\Policies\RoutePolicy;
use App\Policies\TariffPolicy;
use App\Policies\VehiclePolicy;
use App\Services\OsrmService;
use App\Socialite\OidcProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OsrmServiceInterface::class, OsrmService::class);
    }

    public function boot(): void
    {
        Socialite::extend('oidc', function (): OidcProvider {
            /** @var array<string, mixed> $config */
            $config = config('services.oidc');

            return Socialite::buildProvider(OidcProvider::class, $config);
        });

        Gate::policy(Vehicle::class, VehiclePolicy::class);
        Gate::policy(Tariff::class, TariffPolicy::class);
        Gate::policy(Route::class, RoutePolicy::class);
    }
}
