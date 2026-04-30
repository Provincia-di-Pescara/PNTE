@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-xl font-bold tracking-tight">Dashboard Operativa</h1>
        <p class="text-sm text-ink-2 mt-1">Riepilogo sistema e stato elaborazioni</p>
    </div>

    <!-- KPI Grid -->
    <div class="grid grid-cols-4 gap-4">
        <div class="card p-4">
            <div class="text-xs text-ink-2 font-medium">Enti registrati</div>
            <div class="text-2xl font-bold mt-1 num">{{ $entityCount ?? 0 }}</div>
        </div>
        <div class="card p-4">
            <div class="text-xs text-ink-2 font-medium">Tariffe attive</div>
            <div class="text-2xl font-bold mt-1 num">{{ $tariffCount ?? 0 }}</div>
        </div>
        <div class="card p-4">
            <div class="text-xs text-ink-2 font-medium">Aziende</div>
            <div class="text-2xl font-bold mt-1 num">{{ \App\Models\Company::count() }}</div>
        </div>
        <div class="card p-4">
            <div class="text-xs text-ink-2 font-medium">Pratiche in attesa</div>
            <div class="text-2xl font-bold mt-1 num">0</div>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="card p-6 border border-dashed border-line-2 bg-surface flex flex-col items-center justify-center text-center py-16">
                <div class="w-12 h-12 bg-surface-2 rounded-full flex items-center justify-center text-ink-3 mb-4">
                    <x-icon name="truck" size="24" stroke="1.5" />
                </div>
                <h3 class="text-sm font-semibold">Pratiche · In arrivo con v0.5.x</h3>
                <p class="text-xs text-ink-2 mt-1 max-w-sm">La gestione del flusso documentale e la state machine delle pratiche saranno disponibili a breve.</p>
            </div>
        </div>
        
        <div class="col-span-1 space-y-6">
            <div class="card p-5">
                <h3 class="text-sm font-semibold mb-4">Collegamenti rapidi</h3>
                <div class="space-y-2">
                    <a href="{{ route('admin.entities.index') }}" class="flex items-center gap-3 p-2.5 rounded-lg border border-line hover:border-accent transition-colors bg-surface-2 group">
                        <div class="w-8 h-8 rounded bg-surface flex items-center justify-center text-ink-2 group-hover:text-accent">
                            <x-icon name="bridge" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold">Enti territoriali</div>
                            <div class="text-[10px] text-ink-2">Gestione Comuni e Province</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.tariffs.index') }}" class="flex items-center gap-3 p-2.5 rounded-lg border border-line hover:border-accent transition-colors bg-surface-2 group">
                        <div class="w-8 h-8 rounded bg-surface flex items-center justify-center text-ink-2 group-hover:text-accent">
                            <x-icon name="euro" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold">Tariffario</div>
                            <div class="text-[10px] text-ink-2">Coefficienti di usura</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
