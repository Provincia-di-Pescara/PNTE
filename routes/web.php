<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\CompanyLookupController;
use App\Http\Controllers\Admin\EntityController;
use App\Http\Controllers\Admin\ImpersonateController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TariffController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Agency\DashboardController as AgencyDashboardController;
use App\Http\Controllers\Api\ArsOverlayController;
use App\Http\Controllers\Api\EntityGeoJsonController;
use App\Http\Controllers\Api\RoutingController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OidcController;
use App\Http\Controllers\Citizen\ApplicationController;
use App\Http\Controllers\Citizen\DelegationController;
use App\Http\Controllers\Citizen\RouteBuilderController;
use App\Http\Controllers\Citizen\TripController;
use App\Http\Controllers\Citizen\VehicleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LawEnforcement\RadarController;
use App\Http\Controllers\Setup\SetupController;
use App\Http\Controllers\System\DashboardController as SystemDashboardController;
use App\Http\Controllers\ThirdParty\ClearanceController;
use App\Http\Controllers\ThirdParty\RoadworkController;
use App\Http\Controllers\ThirdParty\StandardRouteController;
use Illuminate\Support\Facades\Route;

// ── Setup wizard (exempt from EnsureSetupComplete via middleware logic) ──────
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

// ── Authentication ────────────────────────────────────────────────────────────
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/auth/redirect', [OidcController::class, 'redirect'])->name('auth.oidc.redirect');
Route::get('/auth/callback', [OidcController::class, 'callback'])->name('auth.oidc.callback');

// ── Public GIS API ────────────────────────────────────────────────────────────
Route::get('/api/entities/geojson', [EntityGeoJsonController::class, 'index'])->name('api.entities.geojson');

// ── Authenticated area ────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Leave impersonation — accessible to impersonated users (not system-admin restricted)
    Route::delete('/impersonate', [ImpersonateController::class, 'leave'])->name('impersonate.leave');

    // ── /system — system-admin only (infra panel, zero business data) ─────────
    Route::prefix('system')->name('system.')->middleware('system-admin')->group(function () {
        Route::get('/', [SystemDashboardController::class, 'index'])->name('dashboard');
        Route::get('/tenants', [SystemDashboardController::class, 'tenants'])->name('tenants');
        Route::post('/tenants/{entity}/toggle', [SystemDashboardController::class, 'toggleTenant'])->name('tenants.toggle');
        Route::get('/connectors', [SystemDashboardController::class, 'connectors'])->name('connectors');
        Route::get('/smtp', [SystemDashboardController::class, 'smtp'])->name('smtp');
        Route::post('/smtp/test', [SystemDashboardController::class, 'testSmtp'])->name('smtp.test');
        Route::get('/scheduler', [SystemDashboardController::class, 'scheduler'])->name('scheduler');
        Route::get('/metrics', [SystemDashboardController::class, 'metrics'])->name('metrics');
        Route::get('/geo', [SystemDashboardController::class, 'geo'])->name('geo');
        Route::post('/geo/fetch', [SystemDashboardController::class, 'fetchGeo'])->name('geo.fetch');
        Route::post('/geo/import', [SystemDashboardController::class, 'importGeo'])->name('geo.import');
        Route::get('/audit', [SystemDashboardController::class, 'auditInfra'])->name('audit');
        Route::get('/release', [SystemDashboardController::class, 'release'])->name('release');
        Route::get('/users', [SystemDashboardController::class, 'users'])->name('users.index');
        Route::post('/users', [SystemDashboardController::class, 'storeUser'])->name('users.store');
        Route::patch('/users/{user}/disable', [SystemDashboardController::class, 'disableUser'])->name('users.disable');
        Route::patch('/users/{user}/reset-password', [SystemDashboardController::class, 'resetPassword'])->name('users.reset-password');

        // Impersonation — only system-admin can START impersonation; leave is in general auth group above
        Route::post('/users/{user}/impersonate', [ImpersonateController::class, 'take'])->name('users.impersonate');
    });

    // ── /admin — admin-capofila, admin-ente, operator (entity-bound) ──────────
    Route::prefix('admin')->name('admin.')->middleware(['not-system-admin', 'role:admin-capofila|admin-ente|operator'])->group(function () {
        Route::resource('companies', CompanyController::class);
        Route::post('companies/{company}/delegations/{user}/action', [CompanyController::class, 'approveDelegation'])
            ->name('companies.delegation.action');

        Route::resource('entities', EntityController::class);

        Route::resource('tariffs', TariffController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        // Settings (mail, general, gis, oidc, pec, pdnd) — admin-capofila or admin-ente only
        Route::prefix('settings')->name('settings.')->middleware('role:admin-capofila|admin-ente')->group(function () {
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

    // ── /my — admin-azienda, citizen, operator (company-bound) ───────────────
    Route::prefix('my')->name('my.')->middleware('not-system-admin')->group(function () {
        Route::post('delegations/lookup', [DelegationController::class, 'lookup'])->name('delegations.lookup');
        Route::resource('delegations', DelegationController::class)->only(['index', 'create', 'store']);

        Route::resource('vehicles', VehicleController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

        Route::resource('routes', RouteBuilderController::class)->only(['create', 'store', 'show']);

        Route::resource('applications', ApplicationController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update']);

        Route::post('applications/{application}/trips', [TripController::class, 'store'])
            ->name('applications.trips.store');
        Route::patch('trips/{trip}/end', [TripController::class, 'end'])
            ->name('trips.end');
    });

    // ── /api — authenticated routing endpoints ────────────────────────────────
    Route::prefix('api')->name('api.')->middleware('not-system-admin')->group(function () {
        Route::post('routing/snap', [RoutingController::class, 'snap'])->name('routing.snap');
        Route::post('routing/alternatives', [RoutingController::class, 'alternatives'])->name('routing.alternatives');
        Route::post('routing/ars-overlay', [ArsOverlayController::class, 'index'])->name('routing.ars-overlay');
        Route::post('admin/companies/lookup', CompanyLookupController::class)->name('admin.companies.lookup');
    });

    // ── /third-party — third-party entity operators ───────────────────────────
    Route::prefix('third-party')->name('third-party.')->middleware(['not-system-admin', 'role:third-party', 'entity-bound'])->group(function () {
        Route::resource('roadworks', RoadworkController::class);
        Route::resource('standard-routes', StandardRouteController::class);

        Route::resource('clearances', ClearanceController::class)->only(['index', 'show']);
        Route::post('clearances/{clearance}/approve', [ClearanceController::class, 'approve'])
            ->name('clearances.approve');
        Route::post('clearances/{clearance}/reject', [ClearanceController::class, 'reject'])
            ->name('clearances.reject');
    });

    // ── /agency — Agenzie di pratiche auto (ATECO 82.99.11 · L. 264/1991) ─────
    Route::prefix('agency')->name('agency.')->middleware(['not-system-admin', 'role:agency'])->group(function () {
        Route::get('/', [AgencyDashboardController::class, 'index'])->name('dashboard');
        Route::get('/partners', [AgencyDashboardController::class, 'partners'])->name('partners');
        Route::get('/applications', [AgencyDashboardController::class, 'applications'])->name('applications');
        Route::get('/audit', [AgencyDashboardController::class, 'audit'])->name('audit');
    });

    // ── /law-enforcement — Forze dell'Ordine (read-only) ─────────────────────
    Route::prefix('law-enforcement')->name('law-enforcement.')->middleware(['not-system-admin', 'role:law-enforcement'])->group(function () {
        Route::get('radar', [RadarController::class, 'index'])->name('radar.index');
        Route::get('radar/{application}', [RadarController::class, 'show'])->name('radar.show');
    });
});
