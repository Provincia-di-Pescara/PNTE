<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Models\Roadwork;
use App\Models\Route;
use App\Models\Tariff;
use App\Models\Vehicle;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        if ($user->hasRole(['super-admin', 'operator'])) {
            return view('dashboard', [
                'entityCount' => Entity::query()->count(),
                'tariffCount' => Tariff::query()->count(),
            ]);
        }

        if ($user->hasRole('citizen')) {
            return view('citizen.dashboard', [
                'vehicleCount' => $user->vehicles()->count(),
                'routeCount'   => Route::query()->where('user_id', $user->id)->count(),
            ]);
        }

        if ($user->hasRole('third-party')) {
            return view('third-party.dashboard', [
                'roadworkCount' => Roadwork::query()->where('entity_id', $user->entity_id)->count(),
            ]);
        }

        if ($user->hasRole('law-enforcement')) {
            return view('law-enforcement.dashboard');
        }

        return view('dashboard');
    }
}
