<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

final class EnsureSetupComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('setup.*') || $request->routeIs('logout')) {
            return $next($request);
        }

        try {
            if (! Schema::hasTable('settings') || ! Setting::isSetupComplete()) {
                return redirect()->route('setup.index');
            }
        } catch (\Exception) {
            return redirect()->route('setup.index');
        }

        return $next($request);
    }
}
