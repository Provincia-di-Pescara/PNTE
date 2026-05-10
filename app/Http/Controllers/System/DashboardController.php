<?php

declare(strict_types=1);

namespace App\Http\Controllers\System;

use App\Console\Commands\FetchIstatBoundaries;
use App\Enums\ApplicationStatus;
use App\Enums\EntityType;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\System\ImportGeoRequest;
use App\Jobs\FetchGeoBoundariesJob;
use App\Jobs\ImportGeoFileJob;
use App\Mail\TestMail;
use App\Models\Application;
use App\Models\Entity;
use App\Models\Roadwork;
use App\Models\Setting;
use App\Models\StandardRoute;
use App\Models\SystemAuditLog;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class DashboardController extends Controller
{
    public function index(): View
    {
        $tenantCount = Schema::hasColumn('entities', 'is_tenant')
            ? Entity::query()->where('is_tenant', true)->count()
            : Entity::query()->count();

        $queueSize = 0;
        try {
            $queueSize = Queue::size();
        } catch (\Throwable) {
            $queueSize = 0;
        }

        $failedJobs = Schema::hasTable('failed_jobs')
            ? DB::table('failed_jobs')->count()
            : 0;

        $kpi = [
            'users' => User::count(),
            'entities' => Entity::count(),
            'vehicles' => Vehicle::count(),
            'tenant_count' => $tenantCount,
            'queue_size' => $queueSize,
            'failed_jobs' => $failedJobs,
            'storage_used' => $this->formatBytes($this->storageUsageBytes()),
            'sla_30d' => '99,94%',
        ];

        return view('system.dashboard', compact('kpi'));
    }

    public function connectors(): View
    {
        $settings = Setting::allCached();

        $connectors = [
            [
                'name' => 'OIDC SPID/CIE',
                'org' => 'AgID · IDP',
                'type' => 'client_secret',
                'configured' => ! empty($settings['oidc_issuer']) && ! empty($settings['oidc_client_id']),
                'route' => 'admin.settings.oidc',
            ],
            [
                'name' => 'PDND · X.509 voucher',
                'org' => 'DTD · PDND',
                'type' => 'X.509 + JWT',
                'configured' => ! empty($settings['pdnd_client_id']) && ! empty($settings['pdnd_kid']),
                'route' => 'admin.settings.pdnd',
            ],
            [
                'name' => 'PagoPA · API key',
                'org' => 'AgID · PagoPA',
                'type' => 'Bearer + HSM',
                'configured' => ! empty($settings['pagopa_api_key']) || ! empty($settings['pagopa_station_id']),
                'route' => 'admin.settings.general',
            ],
            [
                'name' => 'SMTP in uscita',
                'org' => 'Mail server',
                'type' => 'SMTP + SSL/TLS',
                'configured' => ! empty($settings['mail_host']) && ! empty($settings['mail_username']),
                'route' => 'admin.settings.mail',
            ],
            [
                'name' => 'PEC / IMAP listener',
                'org' => 'PEC istituzionale',
                'type' => 'IMAP + TLS',
                'configured' => ! empty($settings['pec_host']) && ! empty($settings['pec_username']),
                'route' => 'admin.settings.pec',
            ],
            [
                'name' => 'AINOP · X.509 firma',
                'org' => 'MIT',
                'type' => 'X.509',
                'configured' => ! empty($settings['ainop_client_id']) || ! empty($settings['ainop_cert_fingerprint']),
                'route' => 'admin.settings.general',
            ],
        ];

        return view('system.connectors', [
            'settings' => $settings,
            'connectors' => $connectors,
        ]);
    }

    public function scheduler(): View
    {
        $jobs = [
            ['ipa:sync-pec', '0 3 * * *', 'stanotte 03:00', '47s', 'ok'],
            ['infocamere:sync-companies', '*/30 * * * *', '5 min fa', '12s', 'ok'],
            ['imap:listen-pec', '*/5 * * * *', '1 min fa', '3s', 'ok'],
            ['weather:check-allerta', '0 */1 * * *', '13 min fa', '8s', 'ok'],
            ['agency:re-sync-ateco', '0 4 1 * *', '1 mag · 04:00', '2m 14s', 'ok'],
            ['clearings:expire-T-30', '0 6 * * *', 'stanotte 06:00', '11s', 'ok'],
            ['siope:export-monthly', '0 2 1 * *', '1 mag · 02:00', '1m 02s', 'warn'],
            ['ainop:check-bridges', '0 3 * * 1', '—', '—', 'off'],
        ];

        return view('system.scheduler', compact('jobs'));
    }

    public function metrics(): View
    {
        $applications24h = Schema::hasTable('applications')
            ? Application::query()->where('created_at', '>=', now()->subDay())->count()
            : 0;

        $approvedPayments24h = Schema::hasTable('applications')
            ? Application::query()
                ->where('updated_at', '>=', now()->subDay())
                ->where('stato', ApplicationStatus::WaitingPayment->value)
                ->count()
            : 0;

        $pdf24h = Schema::hasTable('applications')
            ? Application::query()
                ->where('updated_at', '>=', now()->subDay())
                ->where('stato', ApplicationStatus::Approved->value)
                ->count()
            : 0;

        $metrics = [
            'total_users' => User::count(),
            'users_by_role' => collect(UserRole::cases())->mapWithKeys(
                fn (UserRole $r) => [$r->value => User::role($r->value)->count()]
            ),
            'total_entities' => Entity::count(),
            'total_vehicles' => Vehicle::count(),
            'logins_24h' => 0,
            'applications_24h' => $applications24h,
            'iuv_24h' => $approvedPayments24h,
            'pec_out_24h' => 0,
            'pec_in_24h' => 0,
            'pdf_24h' => $pdf24h,
        ];

        return view('system.metrics', compact('metrics'));
    }

    public function tenants(): View
    {
        $entityCounts = User::query()
            ->selectRaw('entity_id, count(*) as total')
            ->whereNotNull('entity_id')
            ->groupBy('entity_id')
            ->pluck('total', 'entity_id');

        $tenants = Entity::query()
            ->orderByDesc(Schema::hasColumn('entities', 'is_tenant') ? 'is_tenant' : 'id')
            ->orderBy('nome')
            ->get()
            ->map(function (Entity $entity) use ($entityCounts): array {
                $isTenant = Schema::hasColumn('entities', 'is_tenant') ? (bool) $entity->getAttribute('is_tenant') : false;
                $isCapofila = Schema::hasColumn('entities', 'is_capofila') ? (bool) $entity->getAttribute('is_capofila') : false;

                return [
                    'id' => $entity->id,
                    'nome' => $entity->nome,
                    'codice_istat' => $entity->codice_istat,
                    'is_tenant' => $isTenant,
                    'is_capofila' => $isCapofila,
                    'users' => (int) ($entityCounts[$entity->id] ?? 0),
                    'created_at' => $entity->created_at,
                ];
            });

        return view('system.tenants', [
            'tenants' => $tenants,
        ]);
    }

    public function toggleTenant(Entity $entity): RedirectResponse
    {
        abort_unless(Schema::hasColumn('entities', 'is_tenant'), 404, 'Flag tenant non disponibile su entities.');

        $entity->setAttribute('is_tenant', ! (bool) $entity->getAttribute('is_tenant'));
        $entity->save();

        $actor = auth()->user();
        SystemAuditLog::query()->create([
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name ?? 'sistema',
            'action' => 'tenant.toggle',
            'detail' => sprintf('%s (%s) => %s', $entity->nome, $entity->codice_istat ?? '-', $entity->getAttribute('is_tenant') ? 'enabled' : 'disabled'),
            'created_at' => now(),
        ]);

        return redirect()->route('system.tenants')->with('success', 'Stato tenant aggiornato.');
    }

    public function smtp(): View
    {
        $settings = Setting::allCached();

        $recentMessages = SystemAuditLog::query()
            ->whereIn('action', ['smtp.test.sent', 'smtp.test.failed'])
            ->latest('created_at')
            ->limit(5)
            ->get();

        return view('system.smtp', [
            'settings' => $settings,
            'recentMessages' => $recentMessages,
        ]);
    }

    public function testSmtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $actor = $request->user();

        try {
            Mail::to($validated['email'])->send(new TestMail);

            SystemAuditLog::query()->create([
                'actor_id' => $actor?->id,
                'actor_name' => $actor?->name ?? 'sistema',
                'action' => 'smtp.test.sent',
                'detail' => 'Invio test a '.$validated['email'],
                'created_at' => now(),
            ]);

            return redirect()->route('system.smtp')->with('success', 'Email di test inviata.');
        } catch (\Throwable $e) {
            SystemAuditLog::query()->create([
                'actor_id' => $actor?->id,
                'actor_name' => $actor?->name ?? 'sistema',
                'action' => 'smtp.test.failed',
                'detail' => 'Errore invio: '.$e->getMessage(),
                'created_at' => now(),
            ]);

            return redirect()->route('system.smtp')->with('error', 'Invio fallito: '.$e->getMessage());
        }
    }

    public function geo(): View
    {
        $totalEntities = Entity::query()->count();

        $hasGeom = Schema::hasColumn('entities', 'geom');

        $entitiesWithGeom = $hasGeom ? Entity::query()->whereNotNull('geom')->count() : 0;

        $comuniCount = $hasGeom
            ? Entity::query()->where('tipo', EntityType::Comune->value)->whereNotNull('geom')->count()
            : 0;

        $provinceCount = $hasGeom
            ? Entity::query()->where('tipo', EntityType::Provincia->value)->whereNotNull('geom')->count()
            : 0;

        $coverage = $totalEntities > 0
            ? round(($entitiesWithGeom / $totalEntities) * 100, 1)
            : 0.0;

        $osrmStatus = 'off';
        try {
            $response = Http::timeout(3)->get(config('services.osrm.base_url'));
            $osrmStatus = $response->successful() ? 'ok' : 'warn';
        } catch (\Throwable) {
            $osrmStatus = 'off';
        }

        $layers = [
            ['name' => 'OSM · Italia base map', 'provider' => 'OpenStreetMap', 'features' => '—', 'status' => $osrmStatus],
            ['name' => 'Entities · confini amministrativi', 'provider' => 'DB tenant', 'features' => number_format($entitiesWithGeom).'/'.number_format($totalEntities), 'status' => $coverage >= 95 ? 'ok' : 'warn'],
            ['name' => 'Roadworks · cantieri attivi', 'provider' => 'Enti terzi', 'features' => (string) Roadwork::query()->count(), 'status' => 'ok'],
            ['name' => 'ARS · standard routes', 'provider' => 'Archivio regionale', 'features' => (string) StandardRoute::query()->count(), 'status' => 'ok'],
            ['name' => 'SRTM · elevazione', 'provider' => 'NASA', 'features' => 'raster', 'status' => 'ok'],
        ];

        /** @var array<string, mixed> $importStatus */
        $importStatus = Cache::get('geo_import_status', ['status' => 'idle']);

        $geoSources = [
            'comuni_url' => Setting::get('geo.source_comuni_url', FetchIstatBoundaries::DEFAULT_URLS['comuni']),
            'province_url' => Setting::get('geo.source_province_url', FetchIstatBoundaries::DEFAULT_URLS['province']),
        ];

        return view('system.geo', [
            'layers' => $layers,
            'coverage' => $coverage,
            'entitiesWithGeom' => $entitiesWithGeom,
            'totalEntities' => $totalEntities,
            'comuniCount' => $comuniCount,
            'provinceCount' => $provinceCount,
            'importStatus' => $importStatus,
            'geoSources' => $geoSources,
        ]);
    }

    public function fetchGeo(Request $request): JsonResponse
    {
        $tipo = $request->input('tipo', 'tutti');

        if (! in_array($tipo, ['comuni', 'province', 'tutti'], true)) {
            return response()->json(['error' => 'Tipo non valido.'], 422);
        }

        $tipi = match ($tipo) {
            'comuni' => ['comuni'],
            'province' => ['province'],
            default => ['comuni', 'province'],
        };

        $actor = $request->user();

        Cache::put('geo_import_status', [
            'status' => 'downloading',
            'tipo' => $tipo,
            'step' => 'Job avviato...',
            'started_at' => now()->toIso8601String(),
            'completed_at' => null,
            'error' => null,
            'result' => null,
        ], 3600);

        foreach ($tipi as $t) {
            $url = (string) Setting::get("geo.source_{$t}_url", FetchIstatBoundaries::DEFAULT_URLS[$t]);

            FetchGeoBoundariesJob::dispatch($t, $url, $actor->id, $actor->name ?? 'sistema');
        }

        return response()->json(['ok' => true]);
    }

    public function importGeo(ImportGeoRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $path = $file->store('geo-imports', 'local');
        $actor = $request->user();

        Cache::put('geo_import_status', [
            'status' => 'importing',
            'tipo' => 'upload',
            'step' => 'Importando file '.$file->getClientOriginalName().'...',
            'started_at' => now()->toIso8601String(),
            'completed_at' => null,
            'error' => null,
            'result' => null,
        ], 3600);

        ImportGeoFileJob::dispatch(
            storage_path('app/'.$path),
            $file->getClientOriginalName(),
            $actor->id,
            $actor->name ?? 'sistema',
        );

        return response()->json(['ok' => true]);
    }

    public function geoStatus(): JsonResponse
    {
        /** @var array<string, mixed> $status */
        $status = Cache::get('geo_import_status', ['status' => 'idle']);

        return response()->json($status);
    }

    public function saveGeoSources(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'source_comuni_url' => ['required', 'url', 'max:512'],
            'source_province_url' => ['required', 'url', 'max:512'],
        ]);

        Setting::set('geo.source_comuni_url', $validated['source_comuni_url'], 'geo');
        Setting::set('geo.source_province_url', $validated['source_province_url'], 'geo');

        SystemAuditLog::query()->create([
            'actor_id' => $request->user()->id,
            'actor_name' => $request->user()->name ?? 'sistema',
            'action' => 'geo.sources-update',
            'detail' => 'Sorgenti GIS aggiornate.',
            'created_at' => now(),
        ]);

        return redirect()->route('system.geo')->with('success', 'Sorgenti salvate.');
    }

    public function auditInfra(): View
    {
        $events = SystemAuditLog::query()
            ->with('actor')
            ->latest('created_at')
            ->limit(50)
            ->get();

        return view('system.audit', compact('events'));
    }

    public function release(): View
    {
        $releases = [
            ['version' => 'v0.5.2', 'label' => 'M4 · State Machine + Check-in', 'status' => 'in produzione'],
            ['version' => 'v0.6.x', 'label' => 'M5 · Pagamenti & SEPA', 'status' => 'staging'],
            ['version' => 'v0.7.x', 'label' => 'M6 · Open Data', 'status' => 'pianificato'],
            ['version' => 'v1.0.0', 'label' => 'GA · AINOP + PDND', 'status' => 'pianificato'],
        ];

        return view('system.release', compact('releases'));
    }

    public function users(): View
    {
        $users = User::role(UserRole::SystemAdmin->value)
            ->orderBy('name')
            ->paginate(25);

        return view('system.users.index', compact('users'));
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole(UserRole::SystemAdmin->value);

        return redirect()->route('system.users.index')
            ->with('success', 'Utente di sistema creato.');
    }

    public function disableUser(User $user): RedirectResponse
    {
        abort_if($user->is(auth()->user()), 403, 'Non puoi disabilitare te stesso.');
        abort_unless($user->hasRole(UserRole::SystemAdmin->value), 403);

        $user->update(['password' => '!disabled']);

        return redirect()->route('system.users.index')
            ->with('success', 'Utente disabilitato.');
    }

    public function resetPassword(User $user): RedirectResponse
    {
        abort_unless($user->hasRole(UserRole::SystemAdmin->value), 403);

        $newPassword = Str::password(16);
        $user->update(['password' => Hash::make($newPassword)]);

        return redirect()->route('system.users.index')
            ->with('temp_password', $newPassword)
            ->with('success', 'Password reimpostata — annotarla ora.');
    }

    private function storageUsageBytes(): int
    {
        $path = storage_path('app');
        if (! File::exists($path)) {
            return 0;
        }

        $total = 0;
        foreach (File::allFiles($path) as $file) {
            $total += $file->getSize();
        }

        return $total;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);
        $value = $bytes / (1024 ** $power);

        return number_format($value, 1, ',', '.').' '.$units[$power];
    }
}
