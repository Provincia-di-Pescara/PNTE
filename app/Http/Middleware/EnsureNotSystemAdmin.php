<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Block system-admin from accessing any business-facing area.
 * Prevents accidental exposure of PII or business data to infra operators.
 */
final class EnsureNotSystemAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->hasRole(UserRole::SystemAdmin->value)) {
            abort(403, 'Il pannello di sistema non consente accesso alle aree operative.');
        }

        return $next($request);
    }
}
