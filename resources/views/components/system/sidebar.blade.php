@php
    /** @var array<int, array{title: string, items: array<int, array{label: string, route: string, pattern?: string, icon: string, badge?: string}>}> $groups */
    $groups = [
        [
            'title' => 'Piattaforma',
            'items' => [
                ['label' => 'Overview', 'route' => 'system.dashboard', 'pattern' => 'system.dashboard', 'icon' => 'layers'],
                ['label' => 'Telemetria', 'route' => 'system.metrics', 'pattern' => 'system.metrics', 'icon' => 'bolt'],
                ['label' => 'Audit infra', 'route' => 'system.audit', 'pattern' => 'system.audit', 'icon' => 'doc'],
                ['label' => 'Release', 'route' => 'system.release', 'pattern' => 'system.release', 'icon' => 'refresh'],
            ],
        ],
        [
            'title' => 'Tenant & utenti',
            'items' => [
                ['label' => 'Tenant', 'route' => 'system.tenants', 'pattern' => 'system.tenants*', 'icon' => 'flag'],
                ['label' => 'Utenti sistema', 'route' => 'system.users.index', 'pattern' => 'system.users.*', 'icon' => 'user'],
            ],
        ],
        [
            'title' => 'Connettori & API',
            'items' => [
                ['label' => 'SPID/CIE OIDC', 'route' => 'system.integrations.show', 'route_args' => ['service' => 'oidc'], 'pattern' => 'system.integrations.*:oidc', 'icon' => 'qr'],
                ['label' => 'PDND', 'route' => 'system.integrations.show', 'route_args' => ['service' => 'pdnd'], 'pattern' => 'system.integrations.*:pdnd', 'icon' => 'qr'],
                ['label' => 'PagoPA', 'route' => 'system.integrations.show', 'route_args' => ['service' => 'pagopa'], 'pattern' => 'system.integrations.*:pagopa', 'icon' => 'qr'],
                ['label' => 'SMTP outbound', 'route' => 'system.integrations.show', 'route_args' => ['service' => 'smtp'], 'pattern' => 'system.integrations.*:smtp', 'icon' => 'bell'],
                ['label' => 'PEC / IMAP', 'route' => 'system.integrations.show', 'route_args' => ['service' => 'pec'], 'pattern' => 'system.integrations.*:pec', 'icon' => 'bell'],
                ['label' => 'AINOP', 'route' => 'system.integrations.show', 'route_args' => ['service' => 'ainop'], 'pattern' => 'system.integrations.*:ainop', 'icon' => 'qr'],
            ],
        ],
        [
            'title' => 'Dataset geo',
            'items' => [
                ['label' => 'Geo dataset', 'route' => 'system.geo', 'pattern' => 'system.geo', 'icon' => 'map'],
                ['label' => 'OSRM tester', 'route' => 'system.geo.osrm', 'pattern' => 'system.geo.osrm', 'icon' => 'map'],
                ['label' => 'GeoJSON viewer', 'route' => 'system.geo.viewer', 'pattern' => 'system.geo.viewer', 'icon' => 'map'],
                ['label' => 'Simulatore rotte', 'route' => 'system.routes', 'pattern' => 'system.routes*', 'icon' => 'map'],
            ],
        ],
        [
            'title' => 'Diagnostica',
            'items' => [
                ['label' => 'Health globale', 'route' => 'system.diagnostics.index', 'pattern' => 'system.diagnostics.index', 'icon' => 'bolt'],
                ['label' => 'API tester', 'route' => 'system.diagnostics.api-tester', 'pattern' => 'system.diagnostics.api-tester', 'icon' => 'qr'],
                ['label' => 'Cache & queue', 'route' => 'system.diagnostics.cache-queue', 'pattern' => 'system.diagnostics.cache-queue', 'icon' => 'clock'],
                ['label' => 'DB & PostGIS', 'route' => 'system.diagnostics.database', 'pattern' => 'system.diagnostics.database', 'icon' => 'doc'],
                ['label' => 'Scheduler', 'route' => 'system.scheduler', 'pattern' => 'system.scheduler', 'icon' => 'clock'],
            ],
        ],
        [
            'title' => 'Sistema',
            'items' => [
                ['label' => 'Branding piattaforma', 'route' => 'system.settings.branding', 'pattern' => 'system.settings.branding', 'icon' => 'flag'],
                ['label' => 'App behaviour', 'route' => 'system.settings.app-behaviour', 'pattern' => 'system.settings.app-behaviour', 'icon' => 'bolt'],
            ],
        ],
    ];

    $isItemActive = function (array $item): bool {
        $pattern = $item['pattern'] ?? $item['route'];
        // Custom pattern with `:` indicates route+param check
        if (str_contains($pattern, ':')) {
            [$routePattern, $paramValue] = explode(':', $pattern, 2);
            if (! request()->routeIs($routePattern)) {
                return false;
            }
            $current = request()->route()?->parameter('service');
            return $current === $paramValue;
        }
        return request()->routeIs($pattern);
    };

    $routeExists = function (string $name): bool {
        return \Illuminate\Support\Facades\Route::has($name);
    };
@endphp

<aside class="w-[244px] shrink-0 border-r border-line bg-surface flex flex-col overflow-hidden">
    <nav class="flex-1 overflow-y-auto px-2 py-3 space-y-3">
        @foreach($groups as $group)
            <div>
                <div class="text-[10px] text-ink-3 tracking-[0.12em] uppercase font-semibold px-3 py-1.5">
                    {{ $group['title'] }}
                </div>
                @foreach($group['items'] as $item)
                    @continue(! $routeExists($item['route']))
                    @php
                        $args = $item['route_args'] ?? [];
                        $href = route($item['route'], $args);
                        $isActive = $isItemActive($item);
                    @endphp
                    <a href="{{ $href }}"
                       class="mx-1 px-2.5 py-1.5 rounded-md text-[12.5px] flex items-center gap-2 transition-colors no-underline {{ $isActive ? 'bg-accent-bg text-accent-ink font-semibold' : 'text-ink-2 hover:bg-surface-2 hover:text-ink font-medium' }}">
                        <x-icon name="{{ $item['icon'] }}" size="13" class="{{ $isActive ? 'text-accent' : 'text-ink-3' }}" />
                        <span class="truncate">{{ $item['label'] }}</span>
                        @if(! empty($item['badge']))
                            <span class="ml-auto w-1.5 h-1.5 rounded-full bg-accent"></span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endforeach
    </nav>

    <div class="border-t border-line p-3 text-[10.5px] text-ink-3 leading-relaxed">
        <span class="mono">v{{ config('app.version', '0.5.x') }}</span> · EUPL-1.2<br>
        Riuso · Developers Italia
    </div>
</aside>
