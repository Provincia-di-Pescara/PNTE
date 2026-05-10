@extends('layouts.system')

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-end gap-4">
        <div>
            <div class="text-[10.5px] tracking-[0.1em] text-ink-3 uppercase">Scheduler</div>
            <h1 class="text-[22px] font-semibold tracking-tight mt-1">Scheduler · job in cron</h1>
            <p class="text-xs text-ink-3 mt-0.5">Job batch della piattaforma. Sincronizzazioni Once-Only e listener asincroni.</p>
        </div>
        <div class="flex-1"></div>
        <button class="btn btn-primary"><x-icon name="refresh" size="12" /> Esegui ora</button>
    </div>

    {{-- Jobs table --}}
    <div class="card overflow-hidden">
        <div class="grid text-[10.5px] text-ink-3 uppercase tracking-[0.08em] font-medium bg-surface-2 border-b border-line"
             style="grid-template-columns: 1.6fr 130px 1fr 100px 110px 50px; padding: 10px 16px;">
            <div>Job</div>
            <div>Cron</div>
            <div>Ult. esecuzione</div>
            <div>Durata</div>
            <div>Stato</div>
            <div></div>
        </div>
        @foreach($jobs as $j)
        <div class="row-hover grid items-center text-[12.5px] border-b border-line last:border-0"
             style="grid-template-columns: 1.6fr 130px 1fr 100px 110px 50px; padding: 10px 16px;">
            <div class="mono font-medium">{{ $j[0] }}</div>
            <div class="mono text-[11.5px] text-ink-3">{{ $j[1] }}</div>
            <div>{{ $j[2] }}</div>
            <div class="mono num text-ink-3">{{ $j[3] }}</div>
            <div>
                <x-chip tone="{{ $j[4] === 'ok' ? 'success' : ($j[4] === 'warn' ? 'amber' : 'default') }}">
                    {{ $j[4] === 'ok' ? 'ok' : ($j[4] === 'warn' ? 'ritardo' : 'disattivo') }}
                </x-chip>
            </div>
            <button class="btn btn-sm btn-ghost w-[26px] p-0"><x-icon name="more" size="12" /></button>
        </div>
        @endforeach
    </div>

    <p class="text-xs text-ink-3 px-1">
        I job sono definiti in <code class="bg-surface-2 px-1 rounded mono text-[11px]">app/Console/Kernel.php</code>.
        Per l'esecuzione manuale usa <code class="bg-surface-2 px-1 rounded mono text-[11px]">php artisan &lt;job&gt;</code>
        via Portainer exec o pannello CLI.
    </p>

</div>
@endsection
