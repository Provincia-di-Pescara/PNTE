<!DOCTYPE html>
<html lang="it" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard — GTE Abruzzo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased">

<nav class="bg-white border-b border-slate-200 px-6 py-3 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
            </svg>
        </div>
        <span class="text-sm font-semibold text-slate-900">GTE Abruzzo</span>
    </div>

    <div class="flex items-center gap-4">
        <span class="text-sm text-slate-500">{{ auth()->user()->name }}</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-slate-500 hover:text-slate-700 transition-colors">
                Esci
            </button>
        </form>
    </div>
</nav>

<main class="max-w-4xl mx-auto px-6 py-12">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Benvenuto, {{ auth()->user()->name }}</h1>
        <p class="text-slate-500 mt-1">Il sistema GTE Abruzzo è operativo.</p>
    </div>

    {{-- Admin quick links --}}
    @if(auth()->user()->hasAnyRole(['super-admin', 'operator']))
    <div class="mb-8 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <a href="{{ route('admin.companies.index') }}"
           class="flex items-center gap-4 p-5 bg-white rounded-xl border border-slate-200 hover:border-blue-300 hover:shadow-sm transition-all group">
            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                <svg class="w-5 h-5 text-blue-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
            </div>
            <div>
                <p class="font-semibold text-slate-900 text-sm">Aziende</p>
                <p class="text-xs text-slate-500 mt-0.5">Gestione aziende e deleghe operative</p>
            </div>
        </a>
        <a href="{{ route('admin.entities.index') }}"
           class="flex items-center gap-4 p-5 bg-white rounded-xl border border-slate-200 hover:border-blue-300 hover:shadow-sm transition-all group">
            <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                <svg class="w-5 h-5 text-purple-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21 12 3l9.75 18H2.25Zm9.75-3v-5.25" />
                </svg>
            </div>
            <div>
                <p class="font-semibold text-slate-900 text-sm">Enti territoriali</p>
                <p class="text-xs text-slate-500 mt-0.5">Comuni, Province, ANAS, Autostrade</p>
            </div>
        </a>
    </div>
    @endif

    {{-- Citizen quick links --}}
    @if(auth()->user()->isCitizen())
    <div class="mb-8 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <a href="{{ route('my.delegations.index') }}"
           class="flex items-center gap-4 p-5 bg-white rounded-xl border border-slate-200 hover:border-blue-300 hover:shadow-sm transition-all group">
            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                <svg class="w-5 h-5 text-blue-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
            </div>
            <div>
                <p class="font-semibold text-slate-900 text-sm">Mie Deleghe</p>
                <p class="text-xs text-slate-500 mt-0.5">Gestione deleghe aziendali</p>
            </div>
        </a>
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach([
            ['title' => 'Identità & RBAC', 'desc' => 'SPID/CIE, ruoli e aziende', 'milestone' => 'v0.2.x', 'color' => 'amber'],
            ['title' => 'Garage Virtuale', 'desc' => 'Veicoli, assi e tariffario usura', 'milestone' => 'v0.3.x', 'color' => 'orange'],
            ['title' => 'WebGIS & Routing', 'desc' => 'Leaflet, OSRM, intersezione spaziale', 'milestone' => 'v0.4.x', 'color' => 'purple'],
            ['title' => 'State Machine', 'desc' => 'Wizard domanda, pareri, PEC', 'milestone' => 'v0.5.x', 'color' => 'blue'],
            ['title' => 'PagoPA & PDF', 'desc' => 'Pagamenti, firma PAdES, protocollo', 'milestone' => 'v0.6.x', 'color' => 'green'],
            ['title' => 'AINOP/PDND', 'desc' => 'Integrazione infrastrutture nazionali', 'milestone' => 'v1.0.0', 'color' => 'red'],
        ] as $item)
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">{{ $item['milestone'] }}</span>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500">In sviluppo</span>
            </div>
            <h3 class="font-semibold text-slate-900 text-sm">{{ $item['title'] }}</h3>
            <p class="text-xs text-slate-500 mt-1">{{ $item['desc'] }}</p>
        </div>
        @endforeach
    </div>
</main>

</body>
</html>
