<!DOCTYPE html>
<html lang="it" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — GTE Abruzzo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased">

<div class="min-h-full">
    {{-- Nav --}}
    <nav class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex h-14 items-center justify-between">
                <div class="flex items-center gap-6">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-blue-600 flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-slate-900">GTE Abruzzo</span>
                    </a>
                    <div class="hidden sm:flex items-center gap-1">
                        <a href="{{ route('admin.companies.index') }}"
                           class="px-3 py-1.5 rounded-md text-sm font-medium {{ request()->routeIs('admin.companies*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }} transition-colors">
                            Aziende
                        </a>
                        <a href="{{ route('admin.entities.index') }}"
                           class="px-3 py-1.5 rounded-md text-sm font-medium {{ request()->routeIs('admin.entities*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }} transition-colors">
                            Enti
                        </a>
                        @if(auth()->user()->hasRole(\App\Enums\UserRole::SuperAdmin->value) || auth()->user()->hasRole(\App\Enums\UserRole::Operator->value))
                        <a href="{{ route('admin.tariffs.index') }}"
                           class="px-3 py-1.5 rounded-md text-sm font-medium {{ request()->routeIs('admin.tariffs*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }} transition-colors">
                            Tariffario
                        </a>
                        @endif
                        @if(auth()->user()->hasRole(\App\Enums\UserRole::SuperAdmin->value))
                        <a href="{{ route('admin.settings.mail') }}"
                           class="px-3 py-1.5 rounded-md text-sm font-medium {{ request()->routeIs('admin.settings*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }} transition-colors">
                            Impostazioni
                        </a>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-slate-500">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-slate-500 hover:text-slate-700 transition-colors">Esci</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="bg-green-50 border-b border-green-200 px-4 py-2.5">
        <p class="text-sm text-green-800 max-w-7xl mx-auto">{{ session('success') }}</p>
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border-b border-red-200 px-4 py-2.5">
        <p class="text-sm text-red-800 max-w-7xl mx-auto">{{ session('error') }}</p>
    </div>
    @endif

    {{-- Content --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>
</div>

</body>
</html>
