<?php

declare(strict_types=1);

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\SystemAuditLog;
use App\Services\Diagnostics\HealthCheckService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

final class DiagnosticsController extends Controller
{
    public function __construct(private readonly HealthCheckService $health) {}

    public function index(): View
    {
        $catalog = collect($this->health->diagnostics())
            ->map(fn ($d, string $key) => [
                'key' => $key,
                'label' => $d->label(),
                'icon' => $this->iconFor($key),
            ])
            ->values()
            ->all();

        $recentRuns = SystemAuditLog::query()
            ->where('action', 'like', 'diagnostic.%')
            ->latest('created_at')
            ->limit(20)
            ->get();

        return view('system.diagnostics.index', [
            'catalog' => $catalog,
            'recentRuns' => $recentRuns,
        ]);
    }

    public function apiTester(): View
    {
        $endpoints = [
            ['method' => 'GET',  'path' => '/api/v1/system/health',                    'name' => 'Snapshot completo health'],
            ['method' => 'GET',  'path' => '/api/v1/system/health/db',                  'name' => 'Singolo: PostgreSQL'],
            ['method' => 'GET',  'path' => '/api/v1/system/health/postgis',             'name' => 'Singolo: PostGIS'],
            ['method' => 'GET',  'path' => '/api/v1/system/health/redis',               'name' => 'Singolo: Redis'],
            ['method' => 'GET',  'path' => '/api/v1/system/health/queue',               'name' => 'Singolo: Queue'],
            ['method' => 'GET',  'path' => '/api/v1/system/health/storage',             'name' => 'Singolo: Storage'],
            ['method' => 'GET',  'path' => '/api/v1/system/health/osrm',                'name' => 'Singolo: OSRM'],
            ['method' => 'GET',  'path' => '/api/v1/system/health/smtp',                'name' => 'Singolo: SMTP'],
            ['method' => 'GET',  'path' => '/api/v1/system/health/imap',                'name' => 'Singolo: PEC/IMAP'],
            ['method' => 'GET',  'path' => '/api/v1/system/health/oidc',                'name' => 'Singolo: OIDC SPID/CIE'],
            ['method' => 'GET',  'path' => '/api/v1/system/health/pdnd',                'name' => 'Singolo: PDND'],
            ['method' => 'GET',  'path' => '/api/v1/system/health/pagopa',              'name' => 'Singolo: PagoPA'],
            ['method' => 'GET',  'path' => '/api/v1/system/health/ainop',               'name' => 'Singolo: AINOP'],
            ['method' => 'GET',  'path' => '/api/v1/system/health/routing',             'name' => 'Singolo: Routing pipeline'],
            ['method' => 'POST', 'path' => '/api/v1/system/test/mail',                  'name' => 'Test invio mail'],
            ['method' => 'POST', 'path' => '/api/v1/system/test/routing',               'name' => 'Test routing snap+breakdown'],
            ['method' => 'POST', 'path' => '/api/routing/snap',                         'name' => 'Routing pubblico: snap'],
            ['method' => 'POST', 'path' => '/api/routing/breakdown',                    'name' => 'Routing pubblico: breakdown'],
        ];

        $samples = [
            '/api/v1/system/test/mail' => ['to' => 'admin@example.test'],
            '/api/v1/system/test/routing' => ['from' => ['lat' => 42.4647, 'lng' => 14.2156], 'to' => ['lat' => 42.3498, 'lng' => 13.3995]],
            '/api/routing/snap' => ['waypoints' => [['lat' => 42.4647, 'lng' => 14.2156], ['lat' => 42.3498, 'lng' => 13.3995]]],
            '/api/routing/breakdown' => ['waypoints' => [['lat' => 42.4647, 'lng' => 14.2156], ['lat' => 42.3498, 'lng' => 13.3995]]],
        ];

        return view('system.diagnostics.api-tester', [
            'endpoints' => $endpoints,
            'samples' => $samples,
        ]);
    }

    public function cacheQueue(): View
    {
        $queueSize = 0;
        try {
            $queueSize = Queue::size();
        } catch (\Throwable) {
            $queueSize = 0;
        }

        $failed = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;
        $failedRows = Schema::hasTable('failed_jobs')
            ? DB::table('failed_jobs')->orderByDesc('failed_at')->limit(10)->get()
            : collect();

        return view('system.diagnostics.cache-queue', [
            'queueDriver' => config('queue.default'),
            'cacheDriver' => config('cache.default'),
            'queueSize' => $queueSize,
            'failed' => $failed,
            'failedRows' => $failedRows,
        ]);
    }

    public function database(): View
    {
        return view('system.diagnostics.database', [
            'connection' => config('database.default'),
            'driver' => DB::connection()->getDriverName(),
        ]);
    }

    private function iconFor(string $key): string
    {
        return match ($key) {
            'db', 'postgis' => 'doc',
            'redis', 'queue' => 'clock',
            'storage' => 'layers',
            'osrm', 'routing' => 'map',
            'smtp', 'imap' => 'bell',
            'oidc', 'pdnd', 'pagopa', 'ainop' => 'qr',
            default => 'layers',
        };
    }
}
