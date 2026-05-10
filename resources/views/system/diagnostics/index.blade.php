@extends('layouts.system')

@section('content')
<div class="px-6 py-6 space-y-6"
     x-data="{
        loading: false,
        snapshot: null,
        async runAll() {
            this.loading = true;
            try {
                const res = await fetch('{{ route('system.api.health') }}', {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                this.snapshot = await res.json();
            } finally { this.loading = false; }
        }
     }">

    {{-- Heading --}}
    <div class="flex items-end gap-4">
        <div class="flex-1">
            <div class="text-[10.5px] tracking-widest text-ink-3 uppercase font-semibold">Diagnostica</div>
            <h1 class="text-[22px] font-semibold mt-1">Health globale dei servizi</h1>
            <p class="text-[12.5px] text-ink-3 mt-1 max-w-3xl leading-relaxed">
                Esegue tutte le verifiche di connettività verso DB, PostGIS, Redis, queue, storage,
                OSRM, SMTP/IMAP, OIDC, PDND, PagoPA, AINOP e la pipeline di routing. Ogni esecuzione
                viene tracciata in <code class="mono text-[11px]">system_audit_logs</code>.
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('system.diagnostics.api-tester') }}" class="btn">
                <x-icon name="qr" size="11" /> API tester
            </a>
            <button class="btn btn-primary" :disabled="loading"
                    @click="runAll" :class="loading ? 'opacity-60 cursor-wait' : ''">
                <x-icon name="bolt" size="12" />
                <span x-show="!loading">Esegui diagnostica completa</span>
                <span x-show="loading">In corso…</span>
            </button>
        </div>
    </div>

    {{-- Aggregate snapshot --}}
    <div x-show="snapshot" x-cloak class="card p-4">
        <div class="flex items-center gap-3">
            <template x-if="snapshot?.ok">
                <span class="chip chip-success">Tutti i servizi OK</span>
            </template>
            <template x-if="snapshot && !snapshot.ok">
                <span class="chip chip-danger">Almeno un servizio in errore</span>
            </template>
            <span class="text-[11.5px] text-ink-3 mono" x-text="snapshot ? 'snapshot ' + snapshot.checked_at : ''"></span>
            <span class="text-[11.5px] text-ink-3" x-text="snapshot ? snapshot.service_count + ' servizi' : ''"></span>
        </div>
    </div>

    {{-- Service grid: each card auto-runs its own check --}}
    <div>
        <div class="flex items-center gap-3 mb-3">
            <div class="text-[11px] text-ink-3 tracking-widest uppercase font-semibold">Per servizio</div>
            <div class="flex-1 h-px bg-line"></div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($catalog as $entry)
                <x-system.health-card
                    :service="$entry['key']"
                    :label="$entry['label']"
                    :icon="$entry['icon']" />
            @endforeach
        </div>
    </div>

    {{-- Recent runs --}}
    @if($recentRuns->isNotEmpty())
        <div>
            <div class="flex items-center gap-3 mb-3">
                <div class="text-[11px] text-ink-3 tracking-widest uppercase font-semibold">Ultimi 20 run</div>
                <div class="flex-1 h-px bg-line"></div>
            </div>
            <div class="card overflow-hidden">
                @foreach($recentRuns as $run)
                    <div class="grid grid-cols-[150px_180px_120px_1fr] px-4 py-2.5 items-center text-[12.5px] {{ ! $loop->last ? 'border-b border-line' : '' }}">
                        <span class="mono text-ink-3 text-[11.5px]">{{ $run->created_at->format('d M · H:i:s') }}</span>
                        <span class="mono text-[11.5px]">{{ $run->action }}</span>
                        <span>{{ $run->actor_name }}</span>
                        <span class="text-ink-3 truncate">{{ $run->detail }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
