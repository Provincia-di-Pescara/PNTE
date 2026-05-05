<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure the authenticated user has an active entity binding.
 * Required for admin-ente, admin-capofila, operator (entity-bound), and third-party.
 */
final class EnsureEntityBound
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $user->entity_id === null) {
            abort(403, 'Utente non associato a nessun ente.');
        }

        return $next($request);
    }
}
