<?php

declare(strict_types=1);

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $company = $user->companies()->first();

        // agency_mandates (client relationships) planned for v0.6.x — empty until then.
        $clients = collect();

        return view('agency.dashboard', [
            'company' => $company,
            'clients' => $clients,
            'clientCount' => $clients->count(),
            'openAppsCount' => 0,
            'apps30Count' => 0,
            'expiringCount' => 0,
        ]);
    }

    public function partners(): View
    {
        return view('agency.partners');
    }

    public function applications(): View
    {
        return view('agency.applications');
    }

    public function audit(): View
    {
        return view('agency.audit');
    }
}
