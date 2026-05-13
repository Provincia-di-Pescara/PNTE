@extends('layouts.settings')

@section('settings-content')
<div class="mb-6">
    <h1 class="text-xl font-bold tracking-tight">GIS / Mappe</h1>
    <p class="text-sm text-ink-2 mt-0.5">Configurazione OSRM, importazione confini ISTAT e gestione geometrie.</p>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-2.5 rounded-md bg-success/10 text-success text-[13px] font-medium">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-2.5 rounded-md bg-danger/10 text-danger text-[13px] font-medium">{{ session('error') }}</div>
@endif

<div class="space-y-6 max-w-2xl">
    <!-- OSRM settings -->
    <div class="card p-6">
        <h2 class="text-sm font-semibold mb-4">Routing engine (OSRM)</h2>
        <form method="POST" action="{{ route('admin.settings.gis.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="osrm_base_url" class="block text-xs font-semibold text-ink-2 mb-1.5">URL base OSRM</label>
                <input type="url" id="osrm_base_url" name="osrm_base_url"
                       value="{{ old('osrm_base_url', $settings['osrm_base_url']) }}"
                       placeholder="http://osrm:5000"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors @error('osrm_base_url') border-danger @enderror">
                <p class="mt-1 text-[11px] text-ink-3">Indirizzo del servizio OSRM (snap to road, calcolo percorsi).</p>
                @error('osrm_base_url')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
            </div>

            <div class="pt-4 border-t border-line">
                <button type="submit" class="btn btn-primary">Salva</button>
            </div>
        </form>
    </div>

    <!-- ISTAT boundary import -->
    <div class="card p-6">
        <h2 class="text-sm font-semibold mb-1">Importazione confini ISTAT</h2>
        <p class="text-xs text-ink-2 mb-4">
            Scarica i confini amministrativi da
            <a href="https://github.com/openpolis/geojson-italy" target="_blank" rel="noopener" class="text-accent underline">openpolis/geojson-italy</a>
            (licenza MIT, fonte ISTAT) e aggiorna la geometria degli enti registrati.
        </p>

        <div class="grid grid-cols-2 gap-4">
            <form method="POST" action="{{ route('admin.settings.gis.fetch-boundaries') }}">
                @csrf
                <input type="hidden" name="tipo" value="comuni">
                <button type="submit" class="btn btn-secondary w-full">
                    <x-icon name="download" size="14" />
                    Importa Comuni
                </button>
                <p class="text-[11px] text-ink-3 mt-1.5 text-center">~8.000 comuni italiani</p>
            </form>

            <form method="POST" action="{{ route('admin.settings.gis.fetch-boundaries') }}">
                @csrf
                <input type="hidden" name="tipo" value="province">
                <button type="submit" class="btn btn-secondary w-full">
                    <x-icon name="download" size="14" />
                    Importa Province
                </button>
                <p class="text-[11px] text-ink-3 mt-1.5 text-center">~107 province italiane</p>
            </form>
        </div>

        <div class="mt-4 p-3 rounded-md bg-amber-50 dark:bg-amber-900/20 border border-amber-200 text-[11px] text-amber-700 dark:text-amber-400">
            <strong>Attenzione:</strong> l'importazione aggiorna solo gli enti con codice ISTAT corrispondente già registrati nel sistema.
            L'operazione può richiedere alcuni minuti per i comuni.
        </div>
    </div>

    <!-- Geo import manual -->
    <div class="card p-6">
        <h2 class="text-sm font-semibold mb-1">Importazione manuale GeoJSON</h2>
        <p class="text-xs text-ink-2 mb-4">
            Per importare un file GeoJSON personalizzato, usa il comando Artisan da terminale:
        </p>
        <pre class="bg-surface-2 rounded-md px-4 py-3 text-[11px] font-mono text-ink overflow-x-auto">php artisan PNTE:import-geo /percorso/al/file.geojson</pre>
        <p class="text-[11px] text-ink-3 mt-2">Il file deve essere una FeatureCollection con proprietà <code>codice_istat</code>.</p>
    </div>
</div>
@endsection
