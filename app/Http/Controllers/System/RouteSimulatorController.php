<?php

declare(strict_types=1);

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class RouteSimulatorController extends Controller
{
    public function index(): View
    {
        return view('system.routes');
    }
}
