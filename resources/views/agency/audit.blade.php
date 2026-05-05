@extends('layouts.agency')

@section('content')
<div class="space-y-5">
    <div class="flex items-end gap-4">
        <div>
            <div class="text-[10.5px] tracking-[0.1em] text-ink-3 uppercase">Agenzia · Audit</div>
            <h1 class="text-[22px] font-semibold tracking-tight mt-1">Audit & responsabilità</h1>
            <p class="text-xs text-ink-3 mt-0.5">Operazioni sensibili tracciate con contesto partner per attribuzione di responsabilità.</p>
        </div>
        <div class="flex-1"></div>
        <button class="btn"><x-icon name="download" size="12" /> Esporta CSV</button>
    </div>

    <div class="card overflow-hidden">
        <div class="grid text-[10.5px] text-ink-3 uppercase tracking-[0.08em] font-medium bg-surface-2 border-b border-line"
             style="grid-template-columns: 150px 140px 1fr 130px; padding: 10px 16px;">
            <div>Quando</div>
            <div>Operatore</div>
            <div>Operazione</div>
            <div>Mandato</div>
        </div>
        <div class="p-8 text-center text-ink-3">
            <x-icon name="shield" size="24" class="mx-auto mb-3" stroke="1.4" />
            <p class="text-sm font-semibold">Log audit — disponibile a partire da v0.6.x</p>
            <p class="text-xs mt-1">Gli eventi sensibili con <span class="mono text-ink-2">agency_mandate_id</span> appariranno qui.</p>
        </div>
    </div>
</div>
@endsection
