@extends('layouts.citizen')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight">Le mie pratiche</h1>
            <p class="text-sm text-ink-2 mt-1">Gestione veicoli e domande di transito</p>
        </div>
        <button class="btn btn-primary">
            <x-icon name="plus" size="14" /> Nuova domanda
        </button>
    </div>

    <!-- KPI Grid -->
    <div class="grid grid-cols-4 gap-4">
        <div class="card p-4">
            <div class="text-xs text-ink-2 font-medium">Veicoli nel garage</div>
            <div class="text-2xl font-bold mt-1 num">{{ $vehicleCount ?? 0 }}</div>
        </div>
        <div class="card p-4">
            <div class="text-xs text-ink-2 font-medium">Percorsi salvati</div>
            <div class="text-2xl font-bold mt-1 num">{{ $routeCount ?? 0 }}</div>
        </div>
        <div class="card p-4">
            <div class="text-xs text-ink-2 font-medium">Domande in corso</div>
            <div class="text-2xl font-bold mt-1 num">0</div>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="card p-6 border border-dashed border-line-2 bg-surface flex flex-col items-center justify-center text-center py-16">
                <div class="w-12 h-12 bg-surface-2 rounded-full flex items-center justify-center text-ink-3 mb-4">
                    <x-icon name="doc" size="24" stroke="1.5" />
                </div>
                <h3 class="text-sm font-semibold">Le mie pratiche · In arrivo con v0.5.x</h3>
                <p class="text-xs text-ink-2 mt-1 max-w-sm">Lo storico delle pratiche inviate e lo stato di avanzamento saranno visualizzati qui.</p>
            </div>
        </div>
        
        <div class="col-span-1 space-y-6">
            <div class="card p-5">
                <h3 class="text-sm font-semibold mb-4">Collegamenti rapidi</h3>
                <div class="space-y-2">
                    <a href="{{ route('my.vehicles.index') }}" class="flex items-center gap-3 p-2.5 rounded-lg border border-line hover:border-accent transition-colors bg-surface-2 group">
                        <div class="w-8 h-8 rounded bg-surface flex items-center justify-center text-ink-2 group-hover:text-accent">
                            <x-icon name="axles" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold">Garage Virtuale</div>
                            <div class="text-[10px] text-ink-2">Gestione flotta veicoli</div>
                        </div>
                    </a>
                    <a href="{{ route('my.delegations.index') }}" class="flex items-center gap-3 p-2.5 rounded-lg border border-line hover:border-accent transition-colors bg-surface-2 group">
                        <div class="w-8 h-8 rounded bg-surface flex items-center justify-center text-ink-2 group-hover:text-accent">
                            <x-icon name="user" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold">Deleghe</div>
                            <div class="text-[10px] text-ink-2">Aziende rappresentate</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
