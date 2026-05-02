@extends('layouts.law-enforcement')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="text-center py-6">
        <h1 class="text-2xl font-bold tracking-tight">Verifica Transiti</h1>
        <p class="text-sm text-ink-2 mt-1">Controllo autorizzazioni in tempo reale</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 gap-4">
        <div class="card p-4 text-center">
            <div class="text-xs text-ink-2 font-medium">Cantieri attivi</div>
            <div class="text-2xl font-bold mt-1 num text-amber-500">{{ $activeRoadworkCount ?? 0 }}</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-xs text-ink-2 font-medium">Strade ARS censite</div>
            <div class="text-2xl font-bold mt-1 num">{{ $arsRouteCount ?? 0 }}</div>
        </div>
    </div>

    <div class="card p-6 border-accent bg-surface shadow-sm">
        <label class="block text-xs font-semibold text-ink-2 mb-2">Targa del veicolo</label>
        <div class="flex gap-3">
            <input type="text" placeholder="Es. AB 123 CD" class="flex-1 h-12 px-4 rounded-lg border border-line bg-bg text-xl font-mono uppercase tracking-widest focus:border-accent focus:ring-1 focus:ring-accent outline-none transition-all">
            <button class="btn btn-primary h-12 px-8 text-base">
                <x-icon name="search" size="18" /> Cerca
            </button>
        </div>
    </div>

    @if(($activeRoadworks ?? collect())->isNotEmpty())
    <div class="card p-5">
        <h3 class="text-sm font-semibold mb-3">Cantieri attivi sul territorio</h3>
        <div class="divide-y divide-line">
            @foreach($activeRoadworks as $rw)
            <div class="flex items-center gap-3 py-2.5">
                <div class="w-2 h-2 rounded-full bg-amber-400 shrink-0"></div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-medium truncate">{{ $rw->title ?? 'Cantiere #'.$rw->id }}</div>
                    <div class="text-[10px] text-ink-3">
                        {{ $rw->valid_from?->format('d/m/Y') }} – {{ $rw->valid_to?->format('d/m/Y') ?? 'indefinito' }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="card p-8 border border-dashed border-line-2 bg-surface flex flex-col items-center justify-center text-center py-16">
        <div class="w-16 h-16 bg-surface-2 rounded-full flex items-center justify-center text-ink-3 mb-4">
            <x-icon name="qr" size="32" stroke="1.5" />
        </div>
        <h3 class="text-base font-semibold">Dati reali in arrivo con v0.5.x</h3>
        <p class="text-sm text-ink-2 mt-2 max-w-md">La verifica restituirà lo stato dell'autorizzazione, la validità, i limiti di peso e le prescrizioni specifiche per il transito in corso.</p>
    </div>
</div>
@endsection
