@extends('layouts.agency')

@section('content')
<div class="space-y-5">
    <div class="flex items-end gap-4">
        <div>
            <div class="text-[10.5px] tracking-[0.1em] text-ink-3 uppercase">Agenzia · Partner</div>
            <h1 class="text-[22px] font-semibold tracking-tight mt-1">Gestione Partner</h1>
            <p class="text-xs text-ink-3 mt-0.5">
                Mandati <span class="mono">agency_mandates</span>: durata, scope, kill-switch e rinnovo.
            </p>
        </div>
        <div class="flex-1"></div>
        <button class="btn"><x-icon name="download" size="12" /> Esporta</button>
        <button class="btn btn-primary"><x-icon name="plus" size="12" /> Acquisisci nuovo cliente</button>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="card p-4">
            <x-chip tone="success">Scenario A</x-chip>
            <h3 class="text-[14px] font-semibold mt-2 mb-1">Ditta già digitale · click di approvazione</h3>
            <p class="text-[12px] text-ink-3 leading-relaxed">
                Richiedi il mandato in piattaforma. Il legale rappresentante riceve notifica e approva con un click.
                Il record <span class="mono text-ink-2">agency_mandate</span> diventa attivo immediatamente.
            </p>
            <button class="btn btn-sm btn-primary mt-3">Avvia richiesta</button>
        </div>
        <div class="card p-4">
            <x-chip tone="amber">Scenario B</x-chip>
            <h3 class="text-[14px] font-semibold mt-2 mb-1">Ditta analogica · Procura Speciale firmata .p7m</h3>
            <p class="text-[12px] text-ink-3 leading-relaxed">
                Scegli la data di validità · genera il PDF Procura Speciale precompilato · il LR firma offline ·
                carica il <span class="mono text-ink-2">.p7m</span>: il sistema verifica integrità, certificato e CF
                firmatario su Registro Imprese.
            </p>
            <div class="flex gap-2 mt-3">
                <button class="btn btn-sm btn-primary">Genera PDF</button>
                <button class="btn btn-sm">Carica .p7m</button>
            </div>
        </div>
    </div>

    <div class="card p-8 text-center text-ink-3">
        <x-icon name="users" size="28" class="mx-auto mb-3" stroke="1.4" />
        <p class="text-sm font-semibold">Gestione mandati disponibile a partire da v0.6.x</p>
        <p class="text-xs mt-1">Il modulo <span class="mono">agency_mandates</span> è pianificato nel prossimo milestone.</p>
    </div>
</div>
@endsection
