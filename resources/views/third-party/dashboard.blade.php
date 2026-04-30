@extends('layouts.third-party')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight">Cruscotto Ente</h1>
            <p class="text-sm text-ink-2 mt-1">Gestione pareri e cantieri stradali</p>
        </div>
        <a href="{{ route('third-party.roadworks.create') }}" class="btn btn-primary">
            <x-icon name="plus" size="14" /> Nuovo cantiere
        </a>
    </div>

    <!-- KPI Grid -->
    <div class="grid grid-cols-4 gap-4">
        <div class="card p-4">
            <div class="text-xs text-ink-2 font-medium">Cantieri attivi</div>
            <div class="text-2xl font-bold mt-1 num">{{ $roadworkCount ?? 0 }}</div>
        </div>
        <div class="card p-4">
            <div class="text-xs text-ink-2 font-medium">Pareri da rilasciare</div>
            <div class="text-2xl font-bold mt-1 num">0</div>
        </div>
        <div class="card p-4">
            <div class="text-xs text-ink-2 font-medium">Pareri rilasciati</div>
            <div class="text-2xl font-bold mt-1 num">0</div>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="card p-6 border border-dashed border-line-2 bg-surface flex flex-col items-center justify-center text-center py-16">
                <div class="w-12 h-12 bg-surface-2 rounded-full flex items-center justify-center text-ink-3 mb-4">
                    <x-icon name="doc" size="24" stroke="1.5" />
                </div>
                <h3 class="text-sm font-semibold">Pareri (Nulla Osta) · In arrivo con v0.5.x</h3>
                <p class="text-xs text-ink-2 mt-1 max-w-sm">Le richieste di nulla osta per i transiti sul tuo territorio appariranno qui.</p>
            </div>
        </div>
        
        <div class="col-span-1 space-y-6">
            <div class="card p-5">
                <h3 class="text-sm font-semibold mb-4">Collegamenti rapidi</h3>
                <div class="space-y-2">
                    <a href="{{ route('third-party.roadworks.index') }}" class="flex items-center gap-3 p-2.5 rounded-lg border border-line hover:border-accent transition-colors bg-surface-2 group">
                        <div class="w-8 h-8 rounded bg-surface flex items-center justify-center text-ink-2 group-hover:text-accent">
                            <x-icon name="cone" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold">I tuoi cantieri</div>
                            <div class="text-[10px] text-ink-2">Gestione chiusure stradali</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
