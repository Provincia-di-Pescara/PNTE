<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-palette="istituzionale">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'GTE Abruzzo') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=DM+Mono:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-bg text-ink font-sans h-screen overflow-hidden">
<div class="h-screen flex overflow-hidden">
    <aside class="w-[264px] shrink-0 border-r border-line bg-surface flex flex-col">
        <div class="px-5 py-4 border-b border-line">
            <div class="text-[10.5px] tracking-[0.14em] text-ink-3 uppercase font-medium">Pannello /system</div>
            <h1 class="text-[18px] font-semibold mt-1">Infrastruttura</h1>
            <p class="text-[11.5px] text-ink-3 mt-1 leading-relaxed">Nessun accesso a pratiche, P.IVA, targhe o PDF.</p>
        </div>

        <nav class="flex-1 overflow-y-auto px-2 py-3 space-y-3">
            @php
                $groups = [
                    [
                        'title' => 'Piattaforma',
                        'items' => [
                            ['label' => 'Overview', 'route' => 'system.dashboard', 'pattern' => 'system.dashboard', 'icon' => 'layers'],
                            ['label' => 'Tenant', 'route' => 'system.tenants', 'pattern' => 'system.tenants*', 'icon' => 'flag'],
                            ['label' => 'Telemetria', 'route' => 'system.metrics', 'pattern' => 'system.metrics', 'icon' => 'doc'],
                        ],
                    ],
                    [
                        'title' => 'Vault & connettori',
                        'items' => [
                            ['label' => 'Vault connettori', 'route' => 'system.connectors', 'pattern' => 'system.connectors', 'icon' => 'layers'],
                            ['label' => 'SMTP/IMAP madre', 'route' => 'system.smtp', 'pattern' => 'system.smtp*', 'icon' => 'bell'],
                            ['label' => 'Scheduler', 'route' => 'system.scheduler', 'pattern' => 'system.scheduler', 'icon' => 'clock'],
                        ],
                    ],
                    [
                        'title' => 'Dataset geo',
                        'items' => [
                            ['label' => 'Geo dataset', 'route' => 'system.geo', 'pattern' => 'system.geo', 'icon' => 'map'],
                        ],
                    ],
                    [
                        'title' => 'Sistema',
                        'items' => [
                            ['label' => 'Audit infra', 'route' => 'system.audit', 'pattern' => 'system.audit', 'icon' => 'doc'],
                            ['label' => 'Release & migrazioni', 'route' => 'system.release', 'pattern' => 'system.release', 'icon' => 'refresh'],
                            ['label' => 'Utenti sistema', 'route' => 'system.users.index', 'pattern' => 'system.users.*', 'icon' => 'user'],
                        ],
                    ],
                ];
            @endphp

            @foreach($groups as $group)
                <div>
                    <div class="text-[10px] text-ink-3 tracking-[0.12em] uppercase font-semibold px-3 py-1">{{ $group['title'] }}</div>
                    @foreach($group['items'] as $item)
                        @php
                            $isActive = request()->routeIs($item['pattern']);
                        @endphp
                        <a href="{{ route($item['route']) }}" class="mx-1 px-2.5 py-1.5 rounded-md text-[13px] flex items-center gap-2 transition-colors {{ $isActive ? 'bg-surface-2 text-ink font-semibold' : 'text-ink-2 hover:bg-surface-2 hover:text-ink font-medium' }}">
                            <x-icon name="{{ $item['icon'] }}" size="14" class="{{ $isActive ? 'text-accent' : '' }}" />
                            <span class="truncate">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>

        <div class="border-t border-line p-3">
            @auth
                <div class="flex items-center gap-2.5 mb-2">
                    <x-avatar :name="auth()->user()->name" tone="info" />
                    <div class="min-w-0 flex-1">
                        <div class="text-[12px] font-semibold truncate">{{ auth()->user()->name }}</div>
                        <div class="text-[10.5px] text-ink-3 truncate">system-admin</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full btn btn-sm btn-ghost">Esci</button>
                </form>
            @endauth
        </div>
    </aside>

    <main class="flex-1 overflow-hidden bg-bg flex flex-col min-w-0">
        <x-impersonation-banner />
        <div class="flex-1 overflow-auto p-6">
            @if(session('success'))
                <x-alert tone="success" class="mb-4">{{ session('success') }}</x-alert>
            @endif
            @if(session('error'))
                <x-alert tone="danger" class="mb-4">{{ session('error') }}</x-alert>
            @endif

            @yield('content')
        </div>
    </main>
</div>
@stack('scripts')
</body>
</html>
