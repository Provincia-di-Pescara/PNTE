<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restrict access to the /system panel to system-admin only.
 * All other authenticated users receive 403.
 */
final class EnsureSystemAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->hasRole(UserRole::SystemAdmin->value)) {
            abort(403, 'Accesso riservato agli amministratori di sistema.');
        }

        return $next($request);
    }
}
