@extends('layouts.system-sidebar')

@push('scripts')
    @vite('resources/js/geo-map.js')
@endpush

@section('content')
<div class="space-y-5">
    <div>
        <div class="text-[10.5px] tracking-[0.1em] text-ink-3 uppercase">Geo dataset</div>
        <h1 class="text-[22px] font-semibold tracking-tight mt-1">Geo dataset · stato nazionale</h1>
        <p class="text-xs text-ink-3 mt-0.5">Struttura e freschezza dei layer nazionali.</p>
    </div>

    @if(session('success'))
        <x-alert tone="success">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert tone="danger">{{ session('error') }}</x-alert>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-4 gap-3">
        <div class="card p-4">
            <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase font-medium">Entità con geometria</div>
            <div class="num text-[22px] font-semibold mt-1">{{ number_format($entitiesWithGeom) }}/{{ number_format($totalEntities) }}</div>
        </div>
        <div class="card p-4">
            <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase font-medium">Comuni importati</div>
            <div class="num text-[22px] font-semibold mt-1">{{ number_format($comuniCount) }}</div>
        </div>
        <div class="card p-4">
            <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase font-medium">Province importate</div>
            <div class="num text-[22px] font-semibold mt-1">{{ number_format($provinceCount) }}</div>
        </div>
        <div class="card p-4">
            <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase font-medium">Coverage</div>
            <div class="num text-[22px] font-semibold mt-1">{{ number_format($coverage, 1, ',', '.') }}%</div>
        </div>
    </div>

    {{-- Mappa --}}
    <div class="card overflow-hidden">
        <div class="px-4 pt-4 pb-2 flex items-center justify-between">
            <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase font-medium">Mappa confini amministrativi · Italia</div>
            @if($entitiesWithGeom === 0)
                <span class="text-[11px] text-ink-3">Nessuna geometria — importa prima i confini.</span>
            @endif
        </div>
        <div id="geo-map" style="height: 420px;"></div>
    </div>

    {{-- Importazione (Alpine.js — non-blocking) --}}
    <div class="card p-5"
         x-data="geoImport()"
         x-init="init()"
         @import-geo-start.window="onImportStart($event.detail)">

        <div class="text-[13px] font-semibold mb-1">Importa confini nazionali</div>
        <p class="text-xs text-ink-3 mb-4">Download da sorgente ufficiale o carica file manuale. L'operazione avviene in background — la pagina resta responsive.</p>

        {{-- Status banner --}}
        <div x-show="status.status !== 'idle'" class="mb-4">
            <div class="rounded-lg border border-line bg-surface-2 p-3 space-y-2">
                <div class="flex items-center gap-2">
                    {{-- Icon --}}
                    <span x-show="isRunning" class="inline-block w-3 h-3 rounded-full bg-blue-500 animate-pulse"></span>
                    <span x-show="status.status === 'completed'" class="text-green-600 text-[13px]">✓</span>
                    <span x-show="status.status === 'failed'" class="text-red-600 text-[13px]">✗</span>

                    <span class="text-[12.5px] font-medium" x-text="statusLabel"></span>
                    <span class="ml-auto text-[11px] text-ink-3 mono" x-text="status.tipo ? '[' + status.tipo + ']' : ''"></span>
                </div>

                {{-- Step text --}}
                <div class="text-[12px] text-ink-2" x-text="status.step"></div>

                {{-- Progress bar (visible while running) --}}
                <div x-show="isRunning" class="w-full bg-line rounded-full h-1 overflow-hidden">
                    <div class="h-1 bg-blue-500 rounded-full animate-pulse" style="width: 60%"></div>
                </div>

                {{-- Result --}}
                <div x-show="status.result" class="text-[11px] text-ink-3 mono" x-text="resultText"></div>

                {{-- Error --}}
                <div x-show="status.error" class="text-[12px] text-red-600" x-text="status.error"></div>
            </div>
        </div>

        {{-- Download buttons --}}
        <div class="space-y-3">
            <div class="text-[11px] text-ink-3 uppercase tracking-[0.08em] font-medium">Scarica da sorgente</div>
            <div class="flex gap-2">
                <button @click="fetchBoundaries('comuni')"
                        :disabled="isRunning"
                        class="btn btn-secondary flex-1"
                        :class="{'opacity-50 cursor-not-allowed': isRunning}">
                    Comuni (8.091)
                </button>
                <button @click="fetchBoundaries('province')"
                        :disabled="isRunning"
                        class="btn btn-secondary flex-1"
                        :class="{'opacity-50 cursor-not-allowed': isRunning}">
                    Province (107)
                </button>
                <button @click="fetchBoundaries('tutti')"
                        :disabled="isRunning"
                        class="btn btn-primary flex-1"
                        :class="{'opacity-50 cursor-not-allowed': isRunning}">
                    Tutto
                </button>
            </div>

            {{-- Upload manuale --}}
            <div class="pt-2 border-t border-line">
                <div class="text-[11px] text-ink-3 uppercase tracking-[0.08em] font-medium mb-2">Oppure carica file manuale</div>
                <form @submit.prevent="uploadFile($event)"
                      enctype="multipart/form-data"
                      class="flex gap-2 items-start">
                    @csrf
                    <div class="flex-1">
                        <input id="geo-file" name="file" type="file" accept=".json,.geojson"
                               class="input" required :disabled="isRunning">
                        <p class="text-[11px] text-ink-3 mt-0.5">FeatureCollection GeoJSON · max 50 MB</p>
                    </div>
                    <button type="submit"
                            :disabled="isRunning"
                            class="btn btn-secondary whitespace-nowrap"
                            :class="{'opacity-50 cursor-not-allowed': isRunning}">
                        Importa file
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Sorgenti configurabili --}}
    <div class="card p-5">
        <div class="text-[13px] font-semibold mb-1">Sorgenti dati</div>
        <p class="text-xs text-ink-3 mb-4">URL dei dataset GeoJSON. Default: openpolis/geojson-italy (GitHub). Puoi puntare a mirror o sorgenti interne.</p>

        <form method="POST" action="{{ route('system.geo.sources') }}" class="space-y-3">
            @csrf
            @method('PUT')

            <div>
                <label for="source_comuni_url" class="label">URL Comuni</label>
                <input id="source_comuni_url" name="source_comuni_url" type="url"
                       class="input mono text-[12px]"
                       value="{{ $geoSources['comuni_url'] }}" required maxlength="512">
            </div>
            <div>
                <label for="source_province_url" class="label">URL Province</label>
                <input id="source_province_url" name="source_province_url" type="url"
                       class="input mono text-[12px]"
                       value="{{ $geoSources['province_url'] }}" required maxlength="512">
            </div>
            <button type="submit" class="btn btn-secondary">
                Salva sorgenti
            </button>
        </form>
    </div>

    {{-- Layer table --}}
    <div class="card overflow-hidden">
        <div class="grid text-[10.5px] text-ink-3 uppercase tracking-[0.08em] font-medium bg-surface-2 border-b border-line"
             style="grid-template-columns: 1.3fr 1fr 140px 120px; padding: 10px 16px;">
            <div>Layer</div>
            <div>Provider</div>
            <div>Feature</div>
            <div>Stato</div>
        </div>

        @foreach($layers as $layer)
            <div class="grid items-center text-[12.5px] border-b border-line last:border-0 row-hover"
                 style="grid-template-columns: 1.3fr 1fr 140px 120px; padding: 10px 16px;">
                <div class="font-medium">{{ $layer['name'] }}</div>
                <div class="text-ink-2">{{ $layer['provider'] }}</div>
                <div class="mono">{{ $layer['features'] }}</div>
                <div>
                    <x-chip tone="{{ $layer['status'] === 'ok' ? 'success' : ($layer['status'] === 'warn' ? 'amber' : 'default') }}" dot="true">
                        {{ $layer['status'] === 'ok' ? 'fresco' : ($layer['status'] === 'warn' ? 'attenzione' : ($layer['status'] === 'off' ? 'offline' : 'stale')) }}
                    </x-chip>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
function geoImport() {
    const csrf = document.head.querySelector('meta[name="csrf-token"]')?.content ?? '';

    return {
        status: @json($importStatus),
        pollInterval: null,

        get isRunning() {
            return ['downloading', 'importing'].includes(this.status.status);
        },

        get statusLabel() {
            const map = {
                idle: 'Inattivo',
                downloading: 'Download in corso...',
                importing: 'Importazione in corso...',
                completed: 'Completato',
                failed: 'Fallito',
            };
            return map[this.status.status] ?? this.status.status;
        },

        get resultText() {
            const r = this.status.result;
            if (!r) return '';
            return `Aggiornate: ${r.updated} · Create: ${r.created} · Saltate: ${r.skipped}`;
        },

        init() {
            if (this.isRunning) {
                this.startPoll();
            }
        },

        startPoll() {
            if (this.pollInterval) return;
            this.pollInterval = setInterval(() => this.refreshStatus(), 2000);
        },

        stopPoll() {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
        },

        async refreshStatus() {
            try {
                const r = await fetch('{{ route('system.geo.status') }}');
                this.status = await r.json();
                if (!this.isRunning) {
                    this.stopPoll();
                    window.location.reload();
                }
            } catch (e) {
                // silent — retry on next tick
            }
        },

        async fetchBoundaries(tipo) {
            if (this.isRunning) return;
            this.status = { status: 'downloading', tipo, step: 'Job avviato...', started_at: null, completed_at: null, error: null, result: null };
            try {
                await fetch('{{ route('system.geo.fetch') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ tipo }),
                });
            } catch (e) {
                this.status = { status: 'failed', tipo, step: '', error: 'Errore di rete.', result: null };
                return;
            }
            await this.refreshStatus();
            this.startPoll();
        },

        async uploadFile(event) {
            if (this.isRunning) return;
            const form = event.target;
            const fileInput = form.querySelector('input[type="file"]');
            if (!fileInput?.files?.length) return;

            const data = new FormData();
            data.append('file', fileInput.files[0]);
            data.append('_token', csrf);

            this.status = { status: 'importing', tipo: 'upload', step: 'Caricamento file...', started_at: null, completed_at: null, error: null, result: null };

            try {
                const r = await fetch('{{ route('system.geo.import') }}', { method: 'POST', body: data });
                if (!r.ok) {
                    const err = await r.json().catch(() => ({ message: 'Errore sconosciuto.' }));
                    this.status = { status: 'failed', tipo: 'upload', step: '', error: err.message ?? 'Errore upload.', result: null };
                    return;
                }
            } catch (e) {
                this.status = { status: 'failed', tipo: 'upload', step: '', error: 'Errore di rete.', result: null };
                return;
            }

            fileInput.value = '';
            await this.refreshStatus();
            this.startPoll();
        },

        onImportStart(detail) {
            this.status = detail;
            this.startPoll();
        },
    };
}
</script>
@endsection
