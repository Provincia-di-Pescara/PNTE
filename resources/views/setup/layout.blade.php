<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-palette="istituzionale">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Configurazione iniziale — GTE Abruzzo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-bg text-ink font-sans min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 selection:bg-accent selection:text-white">

<div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-ink text-bg mb-4 font-bold text-2xl tracking-tight">
            GTE
        </div>
        <h1 class="text-2xl font-bold tracking-tight">GTE Abruzzo</h1>
        <p class="text-[13px] text-ink-2 mt-1">Gestionale Trasporti Eccezionali</p>
    </div>

    {{-- Step indicator --}}
    @isset($currentStep)
    <div class="sm:mx-auto sm:w-full sm:max-w-lg mb-8">
        <div class="flex items-center justify-between px-4">
            @foreach([
                ['num' => 1, 'label' => 'Account'],
                ['num' => 2, 'label' => 'Applicazione'],
                ['num' => 3, 'label' => 'Posta'],
                ['num' => 4, 'label' => 'Riepilogo'],
            ] as $step)
            <div class="flex flex-col items-center flex-1">
                <div class="flex items-center w-full">
                    @if (!$loop->first)
                    <div class="flex-1 h-0.5 {{ $currentStep > $step['num'] - 1 ? 'bg-accent' : 'bg-line-2' }}"></div>
                    @endif
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold shrink-0
                        {{ $currentStep === $step['num'] ? 'bg-accent text-white ring-4 ring-accent/20' : ($currentStep > $step['num'] ? 'bg-accent text-white' : 'bg-surface-2 text-ink-3') }}">
                        @if($currentStep > $step['num'])
                        <x-icon name="check" size="14" stroke="2.5" />
                        @else
                        {{ $step['num'] }}
                        @endif
                    </div>
                    @if (!$loop->last)
                    <div class="flex-1 h-0.5 {{ $currentStep > $step['num'] ? 'bg-accent' : 'bg-line-2' }}"></div>
                    @endif
                </div>
                <span class="mt-2 text-[11px] font-semibold uppercase tracking-wider {{ $currentStep === $step['num'] ? 'text-accent' : 'text-ink-3' }}">{{ $step['label'] }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endisset

    {{-- Card --}}
    <div class="sm:mx-auto sm:w-full sm:max-w-lg">
        <div class="card p-8 sm:px-10">
            @yield('content')
        </div>
        <p class="mt-8 text-center text-[11px] text-ink-3">
            Provincia di Pescara — EUPL-1.2 — <a href="https://github.com/provincia-di-pescara/gte-abruzzo" class="hover:text-ink transition-colors">GitHub</a>
        </p>
    </div>
</div>

</body>
</html>
