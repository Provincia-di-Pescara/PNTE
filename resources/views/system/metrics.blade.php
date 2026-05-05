@extends('layouts.system-sidebar')

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-end gap-4">
        <div>
            <div class="text-[10.5px] tracking-[0.1em] text-ink-3 uppercase">Telemetria</div>
            <h1 class="text-[22px] font-semibold tracking-tight mt-1">Telemetria aggregata</h1>
            <p class="text-xs text-ink-3 mt-0.5">Solo metriche aggregate e anonime. Nessun dato personale, P.IVA, targa o PDF è raggiungibile da qui.</p>
        </div>
        <div class="flex-1"></div>
        <button class="btn"><x-icon name="download" size="12" /> Esporta CSV</button>
    </div>

    {{-- Stats grid --}}
    <div class="grid grid-cols-3 gap-3">
        @foreach([
            ['Login SPID/CIE (24h)', $metrics['logins_24h']],
            ['Pratiche create (24h)', $metrics['applications_24h']],
            ['IUV PagoPA (24h)', $metrics['iuv_24h']],
            ['PEC out (24h)', $metrics['pec_out_24h']],
            ['PEC in (24h)', $metrics['pec_in_24h']],
            ['PDF generati (24h)', $metrics['pdf_24h']],
        ] as [$label, $value])
        <div class="card p-4">
            <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase font-medium">{{ $label }}</div>
            <div class="num text-[26px] font-semibold mt-1">{{ number_format($value) }}</div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-3 gap-3">
        @foreach([
            ['Utenti totali',     $metrics['total_users']],
            ['Enti territoriali', $metrics['total_entities']],
            ['Veicoli',           $metrics['total_vehicles']],
        ] as [$label, $value])
        <div class="card p-4">
            <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase font-medium">{{ $label }}</div>
            <div class="num text-[26px] font-semibold mt-1">{{ number_format($value) }}</div>
        </div>
        @endforeach
    </div>

    {{-- Utenti per ruolo --}}
    <div class="card overflow-hidden">
        <div class="px-4 py-2.5 border-b border-line">
            <div class="text-[13px] font-semibold">Utenti per ruolo</div>
        </div>
        <div class="grid text-[10.5px] text-ink-3 uppercase tracking-[0.08em] font-medium bg-surface-2 border-b border-line"
             style="grid-template-columns: 1fr 100px; padding: 10px 16px;">
            <div>Ruolo</div>
            <div class="text-right">Utenti</div>
        </div>
        @foreach($metrics['users_by_role'] as $role => $count)
        <div class="grid items-center text-[12.5px] border-b border-line last:border-0 row-hover"
             style="grid-template-columns: 1fr 100px; padding: 10px 16px;">
            <div class="mono">{{ $role }}</div>
            <div class="num text-right font-semibold">{{ number_format($count) }}</div>
        </div>
        @endforeach
    </div>

    {{-- Sparkline bar chart (pure CSS) --}}
    <div class="card p-5">
        <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase font-medium mb-4">
            Carico applicativo simulato · ultime 17 ore
        </div>
        @php
        $bars = [12, 18, 15, 22, 28, 24, 32, 29, 35, 40, 38, 44, 42, 48, 52, 46, 50];
        $max  = max($bars);
        @endphp
        <div class="flex items-end gap-1.5" style="height: 110px;">
            @foreach($bars as $h)
            <div class="flex-1 rounded-t-[3px] opacity-85"
                 style="height: {{ round($h / $max * 100) }}%; background: var(--accent);">
            </div>
            @endforeach
        </div>
        <div class="flex justify-between text-[10.5px] text-ink-3 mt-2 mono">
            <span>00:00</span>
            <span>06:00</span>
            <span>12:00</span>
            <span>17:00</span>
        </div>
    </div>

</div>
@endsection
