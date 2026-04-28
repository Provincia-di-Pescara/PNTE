<?php

use App\Http\Middleware\EnsureSetupComplete;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
