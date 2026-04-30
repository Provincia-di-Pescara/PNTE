<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-palette="istituzionale">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'GTE Abruzzo') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-bg text-ink font-sans h-screen flex flex-col overflow-hidden selection:bg-accent selection:text-white">

<header class="h-14 border-b border-line bg-surface flex items-center justify-between px-4 shrink-0">
    <div class="flex items-center gap-3">
        <div class="w-7 h-7 bg-ink text-bg flex items-center justify-center rounded font-bold text-sm tracking-tight">GTE</div>
        <div class="text-sm">
            <span class="font-semibold">GTE Abruzzo</span>
            <span class="text-ink-2 mx-1.5">·</span>
            <span class="text-ink-2">Provincia di Pescara</span>
            <span class="text-ink-3"> · Ente capofila</span>
        </div>
        <div class="w-px h-4 bg-line mx-2"></div>
        <x-chip dot="true" tone="success">Ambiente: Produzione</x-chip>
    </div>
    
    <div class="flex items-center gap-2">
        <button class="flex items-center justify-between w-48 px-2.5 h-8 border border-line-2 rounded bg-surface-2 text-ink-3 text-xs hover:bg-line-2 transition-colors">
            <div class="flex items-center gap-1.5"><x-icon name="search" size="14" /> Cerca...</div>
            <div class="kbd">⌘K</div>
        </button>
        <button class="w-8 h-8 flex items-center justify-center rounded text-ink-2 hover:bg-surface-2 transition-colors">
            <x-icon name="bell" size="16" />
        </button>
        <div class="h-4 w-px bg-line mx-1"></div>
        @auth
        <div class="flex flex-col pl-1 cursor-pointer items-end justify-center">
            <div class="flex items-center gap-2">
                <div class="text-right">
                    <div class="text-xs font-semibold leading-tight">{{ auth()->user()->name }}</div>
                    <div class="text-[10px] text-ink-3 uppercase tracking-wider">{{ auth()->user()->getRoleNames()->first() ?? 'Utente' }}</div>
                </div>
                <x-avatar :name="auth()->user()->name" tone="amber" />
            </div>
        </div>
        @else
        <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Accedi</a>
        @endauth
    </div>
</header>

<div class="flex flex-1 overflow-hidden">
    <!-- SideNav -->
    <aside class="w-[220px] bg-surface border-r border-line flex flex-col shrink-0">
        <div class="px-4 py-3 text-[10px] font-semibold text-ink-3 uppercase tracking-wider">Navigazione</div>
        <nav class="flex-1 px-2 space-y-0.5 overflow-y-auto">
            @yield('nav-items')
            
            @auth
            <div class="my-2 border-t border-line mx-2"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-md text-[13px] font-medium text-ink-2 hover:bg-surface-2 hover:text-ink transition-colors">
                    <x-icon name="arrow" size="14" />
                    <span>Esci</span>
                </button>
            </form>
            @endauth
        </nav>
        <div class="p-4 border-t border-line text-[10px] text-ink-3 leading-tight space-y-1">
            <div class="flex items-center gap-1.5">
                <div class="w-1.5 h-1.5 rounded-full bg-accent"></div>
                v0.5.x
            </div>
            <div>EUPL-1.2 · <a href="#" class="hover:text-ink transition-colors">Riuso</a> · <a href="#" class="hover:text-ink transition-colors">Developers Italia</a></div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 bg-bg overflow-y-auto flex flex-col">
        <x-impersonation-banner />
        <div class="p-6 flex-1">
            @yield('content')
        </div>
    </main>
</div>

@stack('scripts')
</body>
</html>
