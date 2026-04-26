<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\EntityController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OidcController;
use App\Http\Controllers\Setup\SetupController;
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
});

// Authentication — local (operators/admins)
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Authentication — SPID/CIE via external OIDC proxy
Route::get('/auth/redirect', [OidcController::class, 'redirect'])->name('auth.oidc.redirect');
Route::get('/auth/callback', [OidcController::class, 'callback'])->name('auth.oidc.callback');

// Protected area
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Admin: companies
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('companies', CompanyController::class);
        Route::post('companies/{company}/delegations/{user}/action', [CompanyController::class, 'approveDelegation'])
            ->name('companies.delegation.action');

        Route::resource('entities', EntityController::class);
    });
});
