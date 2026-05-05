@extends('layouts.system-sidebar')

@push('scripts')
    @vite('resources/js/geo-map.js')
@endpush

@section('content')
<div class="space-y-5">
    <div class="flex items-end gap-4">
        <div>
            <div class="text-[10.5px] tracking-[0.1em] text-ink-3 uppercase">Geo dataset</div>
            <h1 class="text-[22px] font-semibold tracking-tight mt-1">Geo dataset · stato nazionale</h1>
            <p class="text-xs text-ink-3 mt-0.5">Struttura e freschezza dei layer nazionali, senza dati applicativi o percorsi utente.</p>
        </div>
    </div>

    @if(session('success'))
        <x-alert tone="success">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert tone="danger">{{ session('error') }}</x-alert>
    @endif

    <div class="grid grid-cols-3 gap-3">
        <div class="card p-4">
            <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase font-medium">Entità con geometria</div>
            <div class="num text-[24px] font-semibold mt-1">{{ number_format($entitiesWithGeom) }}/{{ number_format($totalEntities) }}</div>
        </div>
        <div class="card p-4">
            <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase font-medium">Coverage</div>
            <div class="num text-[24px] font-semibold mt-1">{{ number_format($coverage, 1, ',', '.') }}%</div>
        </div>
        <div class="card p-4">
            <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase font-medium">Layer monitorati</div>
            <div class="num text-[24px] font-semibold mt-1">{{ number_format(count($layers)) }}</div>
        </div>
    </div>

    {{-- Mappa confini amministrativi --}}
    <div class="card overflow-hidden">
        <div class="px-4 pt-4 pb-2">
            <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase font-medium">Mappa confini amministrativi</div>
        </div>
        <div id="geo-map" style="height: 420px;"></div>
    </div>

    {{-- Aggiorna dataset --}}
    <div class="grid grid-cols-2 gap-4">
        {{-- Sinistra: fetch da ISTAT (fonte primaria) --}}
        <div class="card p-5 space-y-4">
            <div>
                <div class="text-[13px] font-semibold">Fetch da ISTAT / openpolis</div>
                <p class="text-xs text-ink-3 mt-0.5">Scarica i confini aggiornati direttamente dal repository ufficiale (geojson-italy). Operazione sincrona, può richiedere qualche minuto.</p>
            </div>

            <form method="POST" action="{{ route('system.geo.fetch') }}" class="space-y-3">
                @csrf
                <div>
                    <label for="tipo" class="label">Dataset</label>
                    <select id="tipo" name="tipo" class="input">
                        <option value="tutti">Comuni + Province (tutto)</option>
                        <option value="comuni">Solo Comuni</option>
                        <option value="province">Solo Province</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-full">
                    Fetch da ISTAT
                </button>
            </form>
        </div>

        {{-- Destra: import file (fallback) --}}
        <div class="card p-5 space-y-4">
            <div>
                <div class="text-[13px] font-semibold">Importa file GeoJSON</div>
                <p class="text-xs text-ink-3 mt-0.5">Fallback manuale: carica un file <code>.geojson</code> o <code>.json</code> (FeatureCollection, max 50 MB). Le geometrie sovrascrivono quelle esistenti per <code>codice_istat</code>.</p>
            </div>

            <form method="POST" action="{{ route('system.geo.import') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div>
                    <label for="file" class="label">File GeoJSON</label>
                    <input id="file" name="file" type="file" accept=".json,.geojson" class="input" required>
                    @error('file')
                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="btn btn-secondary w-full">
                    Importa file
                </button>
            </form>
        </div>
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
                        {{ $layer['status'] === 'ok' ? 'fresco' : ($layer['status'] === 'warn' ? 'attenzione' : 'stale') }}
                    </x-chip>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

