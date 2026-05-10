<?php

declare(strict_types=1);

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

final class RouteSimulatorController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('system.geo.simulator');
    }
}
