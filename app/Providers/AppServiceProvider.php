<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\ApplicationServiceInterface;
use App\Contracts\ClearanceDispatchServiceInterface;
use App\Contracts\InfoCamereServiceInterface;
use App\Contracts\OsrmServiceInterface;
use App\Models\Application;
use App\Models\Clearance;
use App\Models\Roadwork;
use App\Models\Route;
use App\Models\StandardRoute;
use App\Models\Tariff;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Policies\ApplicationPolicy;
use App\Policies\ClearancePolicy;
use App\Policies\RoadworkPolicy;
use App\Policies\RoutePolicy;
use App\Policies\StandardRoutePolicy;
use App\Policies\TariffPolicy;
use App\Policies\TripPolicy;
use App\Policies\VehiclePolicy;
use App\Services\ApplicationService;
use App\Services\ClearanceDispatchService;
use App\Services\InfoCamereService;
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
        $this->app->bind(InfoCamereServiceInterface::class, InfoCamereService::class);
        $this->app->bind(ClearanceDispatchServiceInterface::class, ClearanceDispatchService::class);
        $this->app->bind(ApplicationServiceInterface::class, ApplicationService::class);
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
        Gate::policy(Roadwork::class, RoadworkPolicy::class);
        Gate::policy(StandardRoute::class, StandardRoutePolicy::class);
        Gate::policy(Application::class, ApplicationPolicy::class);
        Gate::policy(Clearance::class, ClearancePolicy::class);
        Gate::policy(Trip::class, TripPolicy::class);
    }
}
