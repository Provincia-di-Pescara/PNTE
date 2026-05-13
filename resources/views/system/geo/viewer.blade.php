@extends('layouts.system')

@push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
@endpush

@section('content')
<div class="px-6 py-6 space-y-6"
     x-data="geoViewer()"
     x-init="init()">

    <div>
        <div class="text-[10.5px] tracking-widest text-ink-3 uppercase font-semibold">Dataset geo</div>
        <h1 class="text-[22px] font-semibold mt-1">GeoJSON viewer</h1>
        <p class="text-[12.5px] text-ink-3 mt-1 max-w-3xl leading-relaxed">
            Carica un file GeoJSON (RFC 7946) per validare struttura, contare le feature, calcolare il bbox e
            visualizzarlo su mappa. Il file viene anche analizzato server-side via
            <code class="mono text-[11px]">POST /api/v1/system/test/geojson</code>.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[380px_1fr] gap-4">
        {{-- Side panel --}}
        <div class="space-y-4">
            <div class="card p-4">
                <label class="text-[10.5px] tracking-widest text-ink-3 uppercase font-semibold block mb-2">Carica file</label>
                <div class="border-2 border-dashed border-line-2 rounded-lg p-6 text-center cursor-pointer hover:bg-surface-2 transition-colors"
                     @click="$refs.fileInput.click()"
                     @dragover.prevent
                     @drop.prevent="onDrop($event)">
                    <x-icon name="map" size="20" class="mx-auto text-ink-3" />
                    <div class="mt-2 text-[12.5px] text-ink-2">Trascina qui un .geojson · .json</div>
                    <div class="text-[11px] text-ink-3 mt-1" x-text="filename || 'oppure clicca per selezionare'"></div>
                    <input type="file" x-ref="fileInput" accept=".geojson,.json,application/geo+json,application/json"
                           class="hidden" @change="onFile($event.target.files[0])">
                </div>

                <div class="mt-3 space-y-1">
                    <button @click="loadSample" class="btn btn-sm w-full">
                        Carica sample (Pescara)
                    </button>
                </div>
            </div>

            <div x-show="meta" x-cloak class="card p-4">
                <div class="text-[10.5px] tracking-widest text-ink-3 uppercase font-semibold mb-2">Metadati</div>
                <dl class="space-y-1.5 text-[12px]">
                    <div class="flex justify-between">
                        <dt class="text-ink-3">Type</dt>
                        <dd class="mono" x-text="meta?.type"></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-ink-3">Feature count</dt>
                        <dd class="mono num" x-text="meta?.feature_count"></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-ink-3">Size</dt>
                        <dd class="mono num" x-text="meta ? Math.round(meta.size_bytes/1024) + ' KB' : ''"></dd>
                    </div>
                    <div x-show="meta?.bbox" class="border-t border-line pt-2 mt-2">
                        <div class="text-ink-3 text-[10.5px] uppercase tracking-wider font-semibold mb-1">Bounding box</div>
                        <div class="mono text-[10.5px] leading-relaxed">
                            <div>SW <span x-text="meta?.bbox?.minLng + ', ' + meta?.bbox?.minLat"></span></div>
                            <div>NE <span x-text="meta?.bbox?.maxLng + ', ' + meta?.bbox?.maxLat"></span></div>
                        </div>
                    </div>
                </dl>
            </div>

            <div x-show="error" x-cloak class="chip chip-danger w-full justify-center" x-text="error"></div>
        </div>

        {{-- Map --}}
        <div class="card overflow-hidden">
            <div id="geo-map" style="height: 560px;"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function geoViewer() {
    return {
        map: null,
        layer: null,
        meta: null,
        error: null,
        filename: null,

        init() {
            this.map = L.map('geo-map').setView([42.4647, 14.2156], 9);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap',
                maxZoom: 18,
            }).addTo(this.map);
        },

        async onDrop(e) {
            const file = e.dataTransfer.files[0];
            if (file) await this.onFile(file);
        },

        async onFile(file) {
            if (!file) return;
            this.filename = file.name;
            this.error = null;
            const text = await file.text();
            await this.handleText(text, file);
        },

        async handleText(text, file) {
            // Validate client-side
            let parsed;
            try { parsed = JSON.parse(text); }
            catch (e) { this.error = 'JSON non valido: ' + e.message; return; }

            // Render on map
            if (this.layer) this.map.removeLayer(this.layer);
            try {
                this.layer = L.geoJSON(parsed, {
                    style: { color: 'oklch(0.45 0.16 255)', weight: 2, fillOpacity: 0.15 },
                    onEachFeature: (f, l) => {
                        if (f.properties) {
                            const html = Object.entries(f.properties).slice(0, 8)
                                .map(([k, v]) => `<div class="text-[11px] mono"><b>${k}</b>: ${typeof v === 'object' ? JSON.stringify(v) : v}</div>`)
                                .join('');
                            l.bindPopup('<div class="space-y-0.5">' + html + '</div>');
                        }
                    }
                }).addTo(this.map);
                this.map.fitBounds(this.layer.getBounds(), { padding: [20, 20] });
            } catch (e) {
                this.error = 'Errore rendering: ' + e.message;
                return;
            }

            // Server-side validation
            if (file) {
                const fd = new FormData();
                fd.append('file', file);
                const csrf = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
                try {
                    const res = await fetch('{{ $apiTestEndpoint }}', {
                        method: 'POST', body: fd, credentials: 'same-origin',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf ?? '' },
                    });
                    this.meta = await res.json();
                    if (!res.ok) {
                        this.error = this.meta?.error ?? ('HTTP ' + res.status);
                    }
                } catch (e) { this.error = e.message; }
            }
        },

        async loadSample() {
            const sample = {
                type: 'FeatureCollection',
                features: [{
                    type: 'Feature',
                    properties: { name: 'Pescara sample', source: 'PNTE diagnostics' },
                    geometry: {
                        type: 'LineString',
                        coordinates: [[14.2156, 42.4647], [13.9, 42.4], [13.6, 42.38], [13.3995, 42.3498]],
                    }
                }]
            };
            this.filename = '(sample inline)';
            const blob = new Blob([JSON.stringify(sample)], { type: 'application/geo+json' });
            const file = new File([blob], 'sample.geojson', { type: 'application/geo+json' });
            await this.handleText(JSON.stringify(sample), file);
        }
    };
}
</script>
@endpush
@endsection
