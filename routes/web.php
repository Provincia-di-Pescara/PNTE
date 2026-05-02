<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\CompanyLookupController;
use App\Http\Controllers\Admin\EntityController;
use App\Http\Controllers\Admin\ImpersonateController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TariffController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Api\ArsOverlayController;
use App\Http\Controllers\Api\EntityGeoJsonController;
use App\Http\Controllers\Api\RoutingController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OidcController;
use App\Http\Controllers\Citizen\DelegationController;
use App\Http\Controllers\Citizen\RouteBuilderController;
use App\Http\Controllers\Citizen\VehicleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Setup\SetupController;
use App\Http\Controllers\ThirdParty\RoadworkController;
use App\Http\Controllers\ThirdParty\StandardRouteController;
use Illuminate\Support\Facades\Route;

// Setup wizard (exempt from EnsureSetupComplete via middleware logic)
Route::prefix('setup')->name('setup.')->group(function () {
    Route::get('/', [SetupController::class, 'index'])->name('index');
    Route::get('/1', [SetupController::class, 'showStep1'])->name('step1');
    Route::post('/1', [SetupController::class, 'storeStep1'])->name('step1.store');
    Route::get('/2', [SetupController::class, 'showStep2'])->name('step2');
    Route::post('/2', [SetupController::class, 'storeStep2'])->name('step2.store');
    Route::get('/3', [SetupController::class, 'showStep3'])->name('step3');
    Route::post('/3', [SetupController::class, 'storeStep3'])->name('step3.store');
    Route::get('/riepilogo', [SetupController::class, 'showRiepilogo'])->name('riepilogo');
    Route::post('/complete', [SetupController::class, 'complete'])->name('complete');
    Route::post('/test-email', [SetupController::class, 'testEmail'])->name('test-email');
});

// Authentication — local (operators/admins)
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Authentication — SPID/CIE via external OIDC proxy
Route::get('/auth/redirect', [OidcController::class, 'redirect'])->name('auth.oidc.redirect');
Route::get('/auth/callback', [OidcController::class, 'callback'])->name('auth.oidc.callback');

// Public-geometry API (no auth required — geographic boundary data)
Route::get('/api/entities/geojson', [EntityGeoJsonController::class, 'index'])->name('api.entities.geojson');

// Protected area
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Citizen: deleghe aziendali
    Route::prefix('my')->name('my.')->group(function () {
        Route::post('delegations/lookup', [DelegationController::class, 'lookup'])->name('delegations.lookup');
        Route::resource('delegations', DelegationController::class)->only(['index', 'create', 'store']);

        Route::resource('vehicles', VehicleController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

        Route::resource('routes', RouteBuilderController::class)->only(['create', 'store', 'show']);
    });

    // API
    Route::prefix('api')->name('api.')->group(function () {
        Route::post('routing/snap', [RoutingController::class, 'snap'])->name('routing.snap');
        Route::post('routing/alternatives', [RoutingController::class, 'alternatives'])->name('routing.alternatives');
        Route::post('routing/ars-overlay', [ArsOverlayController::class, 'index'])->name('routing.ars-overlay');
        Route::post('admin/companies/lookup', CompanyLookupController::class)->name('admin.companies.lookup');
    });

    // Third-party: roadworks management
    Route::prefix('third-party')->name('third-party.')->group(function () {
        Route::resource('roadworks', RoadworkController::class);
        Route::resource('standard-routes', StandardRouteController::class);
    });

    // Admin
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('companies', CompanyController::class);
        Route::post('companies/{company}/delegations/{user}/action', [CompanyController::class, 'approveDelegation'])
            ->name('companies.delegation.action');

        Route::resource('entities', EntityController::class);

        Route::resource('tariffs', TariffController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        // Impersonazione
        Route::post('users/{user}/impersonate', [ImpersonateController::class, 'take'])->name('users.impersonate');
        Route::delete('impersonate', [ImpersonateController::class, 'leave'])->name('impersonate.leave');

        // Impostazioni
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');

            Route::get('mail', [SettingController::class, 'showMail'])->name('mail');
            Route::put('mail', [SettingController::class, 'updateMail'])->name('mail.update');
            Route::post('mail/test', [SettingController::class, 'testMail'])->name('mail.test');

            Route::get('general', [SettingController::class, 'showGeneral'])->name('general');
            Route::put('general', [SettingController::class, 'updateGeneral'])->name('general.update');

            Route::get('branding', [SettingController::class, 'showBranding'])->name('branding');
            Route::put('branding', [SettingController::class, 'updateBranding'])->name('branding.update');

            Route::get('gis', [SettingController::class, 'showGis'])->name('gis');
            Route::put('gis', [SettingController::class, 'updateGis'])->name('gis.update');
            Route::post('gis/fetch-boundaries', [SettingController::class, 'fetchBoundaries'])->name('gis.fetch-boundaries');

            Route::get('oidc', [SettingController::class, 'showOidc'])->name('oidc');
            Route::put('oidc', [SettingController::class, 'updateOidc'])->name('oidc.update');

            Route::get('pec', [SettingController::class, 'showPec'])->name('pec');
            Route::put('pec', [SettingController::class, 'updatePec'])->name('pec.update');
            Route::post('pec/test', [SettingController::class, 'testPec'])->name('pec.test');

            Route::get('pdnd', [SettingController::class, 'showPdnd'])->name('pdnd');
            Route::put('pdnd', [SettingController::class, 'updatePdnd'])->name('pdnd.update');
            Route::post('pdnd/generate-dpop-key', [SettingController::class, 'generateDpopKey'])->name('pdnd.generate-dpop-key');
            Route::post('pdnd/sync-ipa', [SettingController::class, 'syncIpa'])->name('pdnd.sync-ipa');

            Route::prefix('users')->name('users.')->group(function () {
                Route::get('/', [UserController::class, 'index'])->name('index');
                Route::get('{user}', [UserController::class, 'show'])->name('show');
                Route::patch('{user}/role', [UserController::class, 'updateRole'])->name('role');
                Route::patch('{user}/entity', [UserController::class, 'updateEntity'])->name('entity');
            });
        });
    });
});
