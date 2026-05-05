<?php

use App\Http\Middleware\EnsureCompanyBound;
use App\Http\Middleware\EnsureEntityBound;
use App\Http\Middleware\EnsureNotSystemAdmin;
use App\Http\Middleware\EnsureSetupComplete;
use App\Http\Middleware\EnsureSystemAdmin;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            EnsureSetupComplete::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            '/auth/callback',
        ]);
        $middleware->prependToPriorityList(
            AuthenticatesRequests::class,
            EnsureSetupComplete::class,
        );
        $middleware->alias([
            'system-admin' => EnsureSystemAdmin::class,
            'not-system-admin' => EnsureNotSystemAdmin::class,
            'entity-bound' => EnsureEntityBound::class,
            'company-bound' => EnsureCompanyBound::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
