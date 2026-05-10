<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Diagnostics\AinopDiagnosticService;
use App\Services\Diagnostics\DatabaseDiagnosticService;
use App\Services\Diagnostics\HealthCheckService;
use App\Services\Diagnostics\ImapDiagnosticService;
use App\Services\Diagnostics\OidcDiagnosticService;
use App\Services\Diagnostics\OsrmDiagnosticService;
use App\Services\Diagnostics\PagoPaDiagnosticService;
use App\Services\Diagnostics\PdndDiagnosticService;
use App\Services\Diagnostics\PostgisDiagnosticService;
use App\Services\Diagnostics\QueueDiagnosticService;
use App\Services\Diagnostics\RedisDiagnosticService;
use App\Services\Diagnostics\RoutingPipelineDiagnosticService;
use App\Services\Diagnostics\SmtpDiagnosticService;
use App\Services\Diagnostics\StorageDiagnosticService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class DiagnosticsServiceProvider extends ServiceProvider
{
    /**
     * Ordered list of diagnostic services. Order is preserved in API
     * snapshots and UI listings (cheapest/fastest first).
     */
    public const SERVICES = [
        DatabaseDiagnosticService::class,
        PostgisDiagnosticService::class,
        RedisDiagnosticService::class,
        QueueDiagnosticService::class,
        StorageDiagnosticService::class,
        OsrmDiagnosticService::class,
        SmtpDiagnosticService::class,
        ImapDiagnosticService::class,
        OidcDiagnosticService::class,
        PdndDiagnosticService::class,
        PagoPaDiagnosticService::class,
        AinopDiagnosticService::class,
        RoutingPipelineDiagnosticService::class,
    ];

    public function register(): void
    {
        $this->app->singleton(HealthCheckService::class, function (Application $app): HealthCheckService {
            $diagnostics = [];
            foreach (self::SERVICES as $class) {
                $instance = $app->make($class);
                $diagnostics[$instance->key()] = $instance;
            }

            return new HealthCheckService($diagnostics);
        });
    }
}
