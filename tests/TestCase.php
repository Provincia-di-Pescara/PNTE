<?php

namespace Tests;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    protected function refreshApplication(): void
    {
        // Force the test database before the app boots so dotenv (immutable) keeps this value.
        $_ENV['DB_DATABASE'] = 'pnte_test';
        $_SERVER['DB_DATABASE'] = 'pnte_test';
        putenv('DB_DATABASE=pnte_test');

        parent::refreshApplication();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([ValidateCsrfToken::class, PreventRequestForgery::class]);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
