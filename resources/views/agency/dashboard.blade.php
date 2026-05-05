@extends('layouts.agency')

@section('content')
<div class="space-y-5"
     x-data="{ activeClient: '{{ $company?->id ?? '' }}' }">

    {{-- Agency identity strip + context switcher --}}
    <div class="card p-4 flex items-center gap-5">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-ink text-bg flex items-center justify-center font-bold text-[11.5px] tracking-wide shrink-0">
                {{ mb_strtoupper(mb_substr($company?->ragione_sociale ?? 'AG', 0, 2)) }}
            </div>
            <div>
                <div class="text-[14px] font-semibold leading-tight">{{ $company?->ragione_sociale ?? 'Agenzia' }}</div>
                <div class="flex items-center gap-2 mt-1">
                    <x-chip tone="amber">ATECO 82.99.11</x-chip>
                    <x-chip>L. 264/1991</x-chip>
                    @if($company?->piva)
                    <span class="mono text-[10.5px] text-ink-3">P.IVA {{ $company->partita_iva }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="h-8 w-px bg-line"></div>

        <div class="flex items-center gap-3 flex-1">
            <span class="text-[11px] text-ink-3 uppercase tracking-[0.06em] font-medium shrink-0">Sto operando per</span>
            @if($clients->isNotEmpty())
            <select class="h-9 px-3 border border-line-2 rounded-lg bg-surface text-ink text-[13px] font-semibold outline-none cursor-pointer min-w-[280px]"
                    style="font-family: inherit;">
                @foreach($clients as $client)
                <option value="{{ $client->id }}">{{ $client->ragione_sociale }} — {{ $client->comune ?? '' }}</option>
                @endforeach
            </select>
            <x-chip dot="true" tone="success">mandato attivo</x-chip>
            @else
            <span class="text-[13px] text-ink-3">Nessun cliente configurato</span>
            @endif
        </div>

        <a href="{{ route('agency.partners') }}" class="btn btn-primary">
            <x-icon name="plus" size="11" /> Nuova pratica per cliente
        </a>
    </div>

    {{-- KPI Grid --}}
    <div class="grid grid-cols-4 gap-3">
        @foreach([
            ['label' => 'Clienti attivi',      'value' => $clientCount,     'sub' => 'mandati attivi',        'tone' => null],
            ['label' => 'Pratiche aperte',     'value' => $openAppsCount,   'sub' => 'convogli in lavorazione','tone' => null],
            ['label' => 'Pratiche (30gg)',      'value' => $apps30Count,     'sub' => 'tutte le aziende',      'tone' => null],
            ['label' => 'Mandati in scadenza', 'value' => $expiringCount,   'sub' => 'da rinnovare',          'tone' => $expiringCount > 0 ? 'amber' : null],
        ] as $k)
        <div class="card p-4">
            <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase font-medium">{{ $k['label'] }}</div>
            <div class="num text-[26px] font-semibold mt-1 {{ $k['tone'] === 'amber' ? 'text-accent-ink' : 'text-ink' }}">{{ $k['value'] }}</div>
            <div class="text-[11.5px] text-ink-3 mt-0.5">{{ $k['sub'] }}</div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-[1.4fr_1fr] gap-4">

        {{-- Partner/mandate table --}}
        <div class="card overflow-hidden">
            <div class="px-4 py-2.5 border-b border-line flex items-center gap-2">
                <div class="text-[13px] font-semibold">Partner · mandati attivi</div>
                <x-chip>{{ $clientCount }}</x-chip>
                <div class="flex-1"></div>
                <a href="{{ route('agency.partners') }}" class="btn btn-sm">
                    <x-icon name="users" size="12" /> Gestisci
                </a>
            </div>

            @if($clients->isEmpty())
            <div class="py-12 flex flex-col items-center justify-center text-center">
                <div class="w-10 h-10 bg-surface-2 rounded-full flex items-center justify-center text-ink-3 mb-3">
                    <x-icon name="users" size="20" stroke="1.5" />
                </div>
                <p class="text-sm font-semibold">Nessun cliente associato</p>
                <p class="text-xs text-ink-2 mt-1">Aggiungi un cliente tramite Scenario A (click) o Scenario B (P7M).</p>
            </div>
            @else
            <div class="overflow-auto">
                @foreach($clients as $i => $client)
                <div class="flex items-center gap-3 px-4 py-3 border-b border-line last:border-0 row-hover">
                    <x-avatar :name="$client->ragione_sociale" tone="info" />
                    <div class="flex-1 min-w-0">
                        <div class="text-[13px] font-semibold truncate">{{ $client->ragione_sociale }}</div>
                        <div class="text-[11px] text-ink-3 flex items-center gap-2 mt-0.5">
                            @if($client->partita_iva)
                            <span class="mono">P.IVA {{ $client->partita_iva }}</span>
                            @endif
                        </div>
                    </div>
                    <x-chip tone="success">attivo</x-chip>
                    <button class="btn btn-sm">Apri</button>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Compliance + alerts --}}
        <div class="space-y-3">
            <div class="card p-4">
                <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase font-medium mb-3">
                    Compliance & classificazione
                </div>
                <div class="grid grid-cols-2 gap-2">
                    @foreach([
                        ['ATECO',          '82.99.11',   'Assistenza registrazione autoveicoli'],
                        ['Legge 264/1991', 'verificata', 'keyword in descrizione attività'],
                        ['Ult. sync PDND', 'Infocamere', 'Registro Imprese'],
                        ['is_agency',      'TRUE',       'auto-detection passata'],
                    ] as [$l, $v, $sub])
                    <div class="p-3 border border-line rounded-lg bg-surface">
                        <div class="text-[10px] text-ink-3 uppercase tracking-[0.08em] font-medium">{{ $l }}</div>
                        <div class="mono text-[12.5px] font-semibold mt-1">{{ $v }}</div>
                        <div class="text-[11px] text-ink-3 mt-0.5 leading-tight">{{ $sub }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="card p-4">
                <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase font-medium mb-3">Avvisi</div>
                @if($expiringCount > 0)
                <div class="p-3 border border-accent/30 rounded-lg bg-accent-bg">
                    <div class="text-[12px] font-semibold text-accent-ink flex items-center gap-1.5">
                        <x-icon name="alert" size="12" />
                        {{ $expiringCount }} {{ $expiringCount === 1 ? 'mandato in scadenza' : 'mandati in scadenza' }}
                    </div>
                    <div class="text-[11.5px] text-accent-ink mt-1.5 leading-snug">
                        Verifica la lista partner e procedi al rinnovo semplificato.
                    </div>
                    <a href="{{ route('agency.partners') }}" class="btn btn-sm mt-2">Gestisci mandati</a>
                </div>
                @else
                <div class="py-6 text-center text-[12px] text-ink-3">
                    <x-icon name="check" size="20" class="mx-auto mb-2 text-success" stroke="2" />
                    Nessun avviso attivo.
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Audit note --}}
    <div class="text-[11.5px] text-ink-3 px-1 border border-dashed border-line-2 rounded-lg p-3 bg-surface-2 leading-relaxed">
        Ogni pratica creata da questa Agenzia registra in audit i campi
        <code class="mono text-ink-2 text-[11px]">agency_mandate_id</code> e
        <code class="mono text-ink-2 text-[11px]">created_by_agency_id</code>
        per attribuzione di responsabilità e KPI per Agenzia.
    </div>

</div>
@endsection
