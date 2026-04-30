<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-palette="istituzionale">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Accesso — GTE Abruzzo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-bg text-ink font-sans min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 selection:bg-accent selection:text-white">

<div class="sm:mx-auto sm:w-full sm:max-w-md text-center mb-8">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-ink text-bg mb-4 font-bold text-2xl tracking-tight">
        GTE
    </div>
    <h1 class="text-2xl font-bold tracking-tight">GTE Abruzzo</h1>
    <p class="text-[13px] text-ink-2 mt-1">Gestionale Trasporti Eccezionali</p>
</div>

<div class="sm:mx-auto sm:w-full sm:max-w-md">
    <div class="card p-8 sm:px-10">

        @if(session('setup_complete'))
        <div class="mb-6 rounded-lg bg-success-bg border border-success/30 p-4 flex items-start gap-3">
            <x-icon name="check" size="20" class="text-success shrink-0 mt-0.5" />
            <div>
                <p class="text-sm font-semibold text-success">Setup completato con successo!</p>
                <p class="text-[13px] text-success mt-0.5">Accedi con le credenziali che hai appena creato.</p>
            </div>
        </div>
        @endif

        <div class="mb-6">
            <h2 class="text-lg font-semibold">Accesso operatori</h2>
            <p class="text-[13px] text-ink-2 mt-1">Riservato a operatori e amministratori.</p>
        </div>

        @if($errors->any())
        <div class="mb-4 rounded-lg bg-danger-bg border border-danger/30 p-4">
            <p class="text-sm text-danger">{{ $errors->first() }}</p>
        </div>
        @endif

        @if(config('services.oidc.base_url'))
        <div class="mb-6">
            <a href="{{ route('auth.oidc.redirect') }}"
               class="w-full flex items-center justify-center gap-3 py-2.5 px-4 rounded-md border border-line bg-surface text-[13px] font-semibold hover:bg-surface-2 focus:outline-none transition-colors">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <rect width="24" height="24" rx="4" fill="var(--ink)"/>
                    <text x="12" y="16" text-anchor="middle" fill="var(--bg)" font-size="10" font-family="sans-serif" font-weight="bold">ID</text>
                </svg>
                Accedi con SPID / CIE
            </a>
        </div>

        <div class="relative mb-6">
            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-line"></div></div>
            <div class="relative flex justify-center text-[11px] uppercase tracking-wider font-semibold">
                <span class="bg-surface px-3 text-ink-3">oppure</span>
            </div>
        </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-xs font-semibold text-ink-2 mb-1.5">E-mail</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors @error('email') border-danger @enderror">
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-ink-2 mb-1.5">Password</label>
                <input type="password" id="password" name="password" autocomplete="current-password" required
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" id="remember" name="remember" class="w-4 h-4 rounded border-line text-accent focus:ring-accent">
                <label for="remember" class="text-[13px] text-ink-2 cursor-pointer">Ricordami</label>
            </div>

            <button type="submit" class="btn btn-primary w-full justify-center text-sm py-2 h-10">
                Accedi
            </button>
        </form>
    </div>

    <p class="mt-8 text-center text-[11px] text-ink-3">
        Provincia di Pescara — EUPL-1.2 — <a href="https://github.com/provincia-di-pescara/gte-abruzzo" class="hover:text-ink transition-colors">GitHub</a>
    </p>
</div>

</body>
</html>
