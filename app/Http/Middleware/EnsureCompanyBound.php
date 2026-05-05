<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure the authenticated user has at least one approved company binding.
 * Required for admin-azienda and company-bound operators.
 */
final class EnsureCompanyBound
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->companies()->wherePivotNotNull('approved_at')->exists()) {
            abort(403, 'Utente non associato a nessuna azienda approvata.');
        }

        return $next($request);
    }
}
