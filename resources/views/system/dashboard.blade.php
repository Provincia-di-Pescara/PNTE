@extends('layouts.system')

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-end gap-4">
        <div>
            <div class="text-[10.5px] tracking-[0.1em] text-ink-3 uppercase">Pannello /system</div>
            <h1 class="text-[22px] font-semibold tracking-tight mt-1">Infrastruttura</h1>
            <p class="text-xs text-ink-3 mt-0.5">Nessun accesso a pratiche, P.IVA, targhe o PDF.</p>
        </div>
        <div class="flex-1"></div>
        <button class="btn"><x-icon name="refresh" size="12" /> Refresh</button>
        <a href="#" class="btn btn-primary"><x-icon name="doc" size="12" /> Apri runbook</a>
    </div>

    {{-- KPI Grid --}}
    <div class="grid grid-cols-4 gap-3">
        @foreach([
            ['label' => 'Tenant attivi',   'value' => $kpi['tenant_count'],  'sub'  => 'enti abilitati',                       'tone' => null],
            ['label' => 'Job in coda',     'value' => $kpi['queue_size'],    'sub'  => 'redis · '.$kpi['failed_jobs'].' fail', 'tone' => null],
            ['label' => 'Storage usato',   'value' => $kpi['storage_used'],  'sub'  => 'filesystem · storage/app',             'tone' => null],
            ['label' => 'SLA piattaforma', 'value' => $kpi['sla_30d'],       'sub'  => 'ult. 30 gg',                            'tone' => 'success'],
        ] as $k)
        <div class="card p-4">
            <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase font-medium">{{ $k['label'] }}</div>
            <div class="num text-[26px] font-semibold mt-1 {{ $k['tone'] === 'success' ? 'text-success' : 'text-ink' }}">{{ is_numeric($k['value']) ? number_format((float) $k['value']) : $k['value'] }}</div>
            <div class="text-[11.5px] text-ink-3 mt-0.5">{{ $k['sub'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Services table --}}
    <div class="card overflow-hidden">
        <div class="px-4 py-2.5 border-b border-line flex items-center gap-2 bg-surface">
            <div class="text-[13px] font-semibold">Servizi</div>
        </div>
        <div class="grid text-[10.5px] text-ink-3 uppercase tracking-[0.08em] font-medium bg-surface-2 border-b border-line"
             style="grid-template-columns: 1.4fr 1fr 100px 110px 90px 50px; padding: 10px 16px;">
            <div>Servizio</div>
            <div>Build</div>
            <div>Stato</div>
            <div>Uptime 30gg</div>
            <div>Lat. p95</div>
            <div></div>
        </div>
        @php
        $services = [
            ['app · Laravel 13',      'v0.5.x',          'ok',   '99,98%', '84 ms'],
            ['db · PostgreSQL 16',    'PostGIS 3.4',      'ok',   '100%',   '3 ms'],
            ['redis · queue',         '7.2',              'ok',   '99,99%', '1 ms'],
            ['osrm · routing',        'Italia 26.03',     'ok',   '99,71%', '184 ms'],
            ['browsershot · PDF',     'Chromium',         'warn', '98,1%',  '1.4 s'],
            ['imap · listener PEC',   'scheduler',        'ok',   '99,9%',  '—'],
        ];
        @endphp
        @foreach($services as $i => $s)
        <div class="row-hover grid items-center text-[12.5px] border-b border-line last:border-0"
             style="grid-template-columns: 1.4fr 1fr 100px 110px 90px 50px; padding: 12px 16px;">
            <div class="mono font-medium">{{ $s[0] }}</div>
            <div class="mono text-[11.5px] text-ink-3">{{ $s[1] }}</div>
            <div>
                <x-chip tone="{{ $s[2] === 'ok' ? 'success' : ($s[2] === 'warn' ? 'amber' : 'danger') }}">
                    {{ $s[2] === 'ok' ? 'operativo' : ($s[2] === 'warn' ? 'attenzione' : 'errore') }}
                </x-chip>
            </div>
            <div class="num">{{ $s[3] }}</div>
            <div class="num text-ink-3">{{ $s[4] }}</div>
            <button class="btn btn-sm btn-ghost w-[26px] p-0"><x-icon name="more" size="12" /></button>
        </div>
        @endforeach
    </div>

    {{-- Counts (collapsible) --}}
    <details class="card overflow-hidden">
        <summary class="px-5 py-3 cursor-pointer text-[13px] font-semibold flex items-center gap-2 select-none list-none">
            <x-icon name="layers" size="14" />
            Riepilogo sistema
            <x-chip class="ml-2">{{ $kpi['users'] }} utenti</x-chip>
        </summary>
        <div class="border-t border-line grid grid-cols-3 sm:grid-cols-6 gap-0">
            @foreach([
                ['Utenti',   $kpi['users']],
                ['Enti',     $kpi['entities']],
                ['Veicoli',  $kpi['vehicles']],
            ] as $i => [$label, $value])
            <div class="p-4 {{ $i < 2 ? 'border-r border-line' : '' }}">
                <div class="text-xs text-ink-2 font-medium">{{ $label }}</div>
                <div class="text-2xl font-bold mt-1 num">{{ number_format($value) }}</div>
            </div>
            @endforeach
        </div>
    </details>

</div>
@endsection
