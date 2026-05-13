<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-palette="istituzionale">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? trim(View::yieldContent('title-prefix').' Pannello /system') }} — {{ \App\Models\Setting::get('branding.platform_name', config('app.name', 'PNTE')) }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=DM+Mono:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-bg text-ink font-sans h-screen overflow-hidden">
<div class="h-screen flex flex-col overflow-hidden">
    <x-system.topbar />

    <div class="flex-1 flex overflow-hidden min-h-0">
        <x-system.sidebar />

        <main class="flex-1 overflow-hidden bg-bg flex flex-col min-w-0">
            <x-impersonation-banner />

            @hasSection('tabs')
                @yield('tabs')
            @endif

            <div class="flex-1 overflow-auto">
                @if(session('success'))
                    <div class="px-6 pt-6">
                        <x-alert tone="success">{{ session('success') }}</x-alert>
                    </div>
                @endif
                @if(session('error'))
                    <div class="px-6 pt-6">
                        <x-alert tone="danger">{{ session('error') }}</x-alert>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
