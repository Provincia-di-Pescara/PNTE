<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Entity;
use App\Models\ImpersonationLog;
use App\Models\Roadwork;
use App\Models\Route;
use App\Models\StandardRoute;
use App\Models\Tariff;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        if ($user->hasRole(['super-admin', 'operator'])) {
            $usersByRole = [];
            foreach (UserRole::cases() as $role) {
                $usersByRole[$role->value] = User::role($role->value)->count();
            }

            return view('dashboard', [
                'entityCount' => Entity::query()->count(),
                'tariffCount' => Tariff::query()->count(),
                'companyCount' => Company::query()->count(),
                'routeCount' => Route::query()->count(),
                'roadworkCount' => Roadwork::query()->count(),
                'userCount' => User::query()->count(),
                'entitiesWithoutGeom' => Entity::query()->whereNull('geom')->count(),
                'usersByRole' => $usersByRole,
                'recentImpersonations' => ImpersonationLog::query()
                    ->with(['impersonator', 'impersonated'])
                    ->latest('started_at')
                    ->limit(5)
                    ->get(),
                'allUsers' => User::query()
                    ->with('roles')
                    ->orderBy('name')
                    ->get(),
            ]);
        }

        if ($user->hasRole('citizen')) {
            $delegationCount = $user->companies()->count();
            $companyIds = $user->companies()->pluck('companies.id');
            $vehicleCount = Vehicle::query()->whereIn('company_id', $companyIds)->count();

            return view('citizen.dashboard', [
                'vehicleCount' => $vehicleCount,
                'routeCount' => Route::query()->where('user_id', $user->id)->count(),
                'delegationCount' => $delegationCount,
                'recentRoutes' => Route::query()
                    ->where('user_id', $user->id)
                    ->latest()
                    ->limit(3)
                    ->get(),
            ]);
        }

        if ($user->hasRole('third-party')) {
            $entity = $user->entity;
            $activeRoadworks = Roadwork::query()
                ->where('entity_id', $user->entity_id)
                ->where('status', 'active')
                ->latest('valid_from')
                ->limit(5)
                ->get();

            return view('third-party.dashboard', [
                'roadworkCount' => Roadwork::query()->where('entity_id', $user->entity_id)->count(),
                'activeRoadworkCount' => Roadwork::query()->where('entity_id', $user->entity_id)->where('status', 'active')->count(),
                'standardRouteCount' => $entity ? StandardRoute::query()->where('entity_id', $entity->id)->count() : 0,
                'entity' => $entity,
                'activeRoadworks' => $activeRoadworks,
            ]);
        }

        if ($user->hasRole('law-enforcement')) {
            $activeRoadworks = Roadwork::query()
                ->where('status', 'active')
                ->latest('valid_from')
                ->limit(5)
                ->get();

            return view('law-enforcement.dashboard', [
                'activeRoadworkCount' => Roadwork::query()->where('status', 'active')->count(),
                'arsRouteCount' => StandardRoute::query()->count(),
                'activeRoadworks' => $activeRoadworks,
            ]);
        }

        return view('dashboard');
    }
}
