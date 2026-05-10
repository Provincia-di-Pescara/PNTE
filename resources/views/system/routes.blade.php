@extends('layouts.system-sidebar')

@push('scripts')
    @vite('resources/js/route-simulator.js')
@endpush

@section('content')
<div class="space-y-5">
    <div>
        <div class="text-[10.5px] tracking-[0.1em] text-ink-3 uppercase">Simulatore</div>
        <h1 class="text-[22px] font-semibold tracking-tight mt-1">Simulatore rotte · OSRM</h1>
        <p class="text-xs text-ink-3 mt-0.5">Testa il motore di routing e verifica il ripartitore chilometrico per ogni ente. I percorsi non vengono salvati.</p>
    </div>

    {{-- Mappa + controlli --}}
    <div class="card overflow-hidden">
        <div class="px-4 pt-4 pb-2 flex items-center justify-between gap-4">
            <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase font-medium">Mappa interattiva · clicca per aggiungere waypoint</div>
            <div class="flex items-center gap-3">
                <span id="sim-distance" class="text-[12px] text-ink-2 mono hidden">
                    <span id="sim-distance-value"></span> km
                </span>
                <button id="btn-sim-fit"
                        class="btn btn-sm btn-ghost text-[12px] hidden">
                    Centra percorso
                </button>
                <button id="btn-sim-clear"
                        class="btn btn-sm btn-ghost text-[12px]"
                        disabled>
                    Cancella percorso
                </button>
            </div>
        </div>
        <div id="route-sim-map" style="height: 500px;"></div>
    </div>

    {{-- Tabella breakdown --}}
    <div class="card p-5">
        <div class="text-[13px] font-semibold mb-1">Ripartitore chilometrico per ente</div>
        <p class="text-xs text-ink-3 mb-4">Distribuzione dei km del percorso per ogni comune e provincia attraversata.</p>

        <div id="sim-breakdown-empty" class="text-[12.5px] text-ink-3 py-4 text-center">
            Clicca almeno 2 punti sulla mappa per calcolare il ripartitore.
        </div>

        <div id="sim-breakdown-loading" class="hidden text-[12.5px] text-ink-3 py-4 text-center">
            <span class="inline-block w-2 h-2 rounded-full bg-blue-500 animate-pulse mr-1"></span>
            Calcolo in corso…
        </div>

        <table id="sim-breakdown-table" class="hidden w-full text-[12.5px]">
            <thead>
                <tr class="border-b border-line text-[11px] text-ink-3 tracking-[0.08em] uppercase">
                    <th class="text-left pb-2 font-medium" colspan="2">Ente</th>
                    <th class="text-right pb-2 font-medium">Km</th>
                    <th class="text-right pb-2 font-medium">% (per tipo)</th>
                </tr>
            </thead>
            <tbody id="sim-breakdown-body" class="divide-y divide-line">
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-line font-semibold">
                    <td class="pt-2" colspan="2">Totale</td>
                    <td class="pt-2 text-right mono" id="sim-total-km"></td>
                    <td class="pt-2 text-right">100%</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
