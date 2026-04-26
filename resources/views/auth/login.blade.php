<!DOCTYPE html>
<html lang="it" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Accesso — GTE Abruzzo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased">

<div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">

    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-blue-600 mb-4">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-slate-900">GTE Abruzzo</h1>
        <p class="text-sm text-slate-500 mt-1">Gestionale Trasporti Eccezionali</p>
    </div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow-sm rounded-2xl border border-slate-100 sm:px-10">

            @if(session('setup_complete'))
            <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 flex items-start gap-3">
                <svg class="w-5 h-5 text-green-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                <div>
                    <p class="text-sm font-semibold text-green-800">Setup completato con successo!</p>
                    <p class="text-sm text-green-700 mt-0.5">Accedi con le credenziali che hai appena creato.</p>
                </div>
            </div>
            @endif

            <div class="mb-6">
                <h2 class="text-lg font-semibold text-slate-900">Accesso operatori</h2>
                <p class="text-sm text-slate-500 mt-1">Riservato a operatori e amministratori.</p>
            </div>

            @if($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4">
                <p class="text-sm text-red-700">{{ $errors->first() }}</p>
            </div>
            @endif

            @if(config('services.oidc.base_url'))
            <div class="mb-6">
                <a href="{{ route('auth.oidc.redirect') }}"
                   class="w-full flex items-center justify-center gap-3 py-2.5 px-4 rounded-lg border border-slate-300 bg-white text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <rect width="24" height="24" rx="4" fill="#0066CC"/>
                        <text x="12" y="16" text-anchor="middle" fill="white" font-size="10" font-family="sans-serif" font-weight="bold">ID</text>
                    </svg>
                    Accedi con SPID / CIE
                </a>
            </div>

            <div class="relative mb-6">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
                <div class="relative flex justify-center text-xs"><span class="bg-white px-3 text-slate-400">oppure</span></div>
            </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">E-mail</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           autocomplete="email" required autofocus
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 @error('email') border-red-400 @enderror">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <input type="password" id="password" name="password"
                           autocomplete="current-password" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="remember" name="remember"
                           class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                    <label for="remember" class="text-sm text-slate-600 cursor-pointer">Ricordami</label>
                </div>

                <button type="submit"
                        class="w-full flex justify-center py-2.5 px-4 rounded-lg bg-blue-600 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    Accedi
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-slate-400">
            Provincia di Pescara — EUPL-1.2 — <a href="https://github.com/provincia-di-pescara/gte-abruzzo" class="underline">GitHub</a>
        </p>
    </div>
</div>

</body>
</html>
