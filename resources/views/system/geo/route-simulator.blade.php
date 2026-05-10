@extends('layouts.system')

@push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
@endpush

@section('content')
<div class="px-6 py-6 space-y-6"
     x-data="routeSim()"
     x-init="init()">

    <div>
        <div class="text-[10.5px] tracking-widest text-ink-3 uppercase font-semibold">Dataset geo</div>
        <h1 class="text-[22px] font-semibold mt-1">Simulatore rotte</h1>
        <p class="text-[12.5px] text-ink-3 mt-1 max-w-3xl leading-relaxed">
            Clicca due punti sulla mappa per generare una rotta via OSRM e calcolare il breakdown
            per entità (PostGIS). Verifica E2E della pipeline di routing.
        </p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="card p-3">
            <div class="text-[10.5px] text-ink-3 uppercase tracking-wider">Entities con geom</div>
            <div class="text-[20px] font-semibold num mt-1">{{ $entitiesWithGeom }}/{{ $totalEntities }}</div>
        </div>
        <div class="card p-3">
            <div class="text-[10.5px] text-ink-3 uppercase tracking-wider">Roadworks</div>
            <div class="text-[20px] font-semibold num mt-1">{{ $roadworks }}</div>
        </div>
        <div class="card p-3">
            <div class="text-[10.5px] text-ink-3 uppercase tracking-wider">Standard routes</div>
            <div class="text-[20px] font-semibold num mt-1">{{ $standardRoutes }}</div>
        </div>
        <div class="card p-3">
            <div class="text-[10.5px] text-ink-3 uppercase tracking-wider">Stato OSRM</div>
            <div class="text-[14px] font-semibold mt-1">
                <a href="{{ route('system.geo.osrm') }}" class="chip chip-info">
                    Vai al test →
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-4">
        <div class="card overflow-hidden">
            <div id="sim-map" style="height: 580px;"></div>
        </div>

        <div class="space-y-3">
            <div class="card p-4">
                <div class="text-[10.5px] tracking-widest text-ink-3 uppercase font-semibold mb-2">Stato</div>
                <div class="text-[12px] mono leading-relaxed text-ink-2 space-y-1">
                    <div>From: <span x-text="from ? from.lat.toFixed(4) + ', ' + from.lng.toFixed(4) : '— clicca sulla mappa'"></span></div>
                    <div>To: <span x-text="to ? to.lat.toFixed(4) + ', ' + to.lng.toFixed(4) : '— clicca un secondo punto'"></span></div>
                </div>
                <div class="flex gap-2 mt-3">
                    <button @click="reset" class="btn btn-sm">Reset</button>
                    <button @click="run" :disabled="!from || !to || loading" class="btn btn-sm btn-primary"
                            :class="(!from || !to || loading) ? 'opacity-50 cursor-not-allowed' : ''">
                        <span x-show="!loading">Esegui rotta</span>
                        <span x-show="loading">…</span>
                    </button>
                </div>
            </div>

            <div x-show="result" x-cloak class="card p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-[10.5px] tracking-widest text-ink-3 uppercase font-semibold">Distanza</div>
                        <div class="text-[24px] font-semibold num" x-text="result?.distance_km + ' km'"></div>
                    </div>
                    <span class="chip chip-success" x-show="result?.ok">OK · <span x-text="result?.latency_ms"></span> ms</span>
                </div>

                <div x-show="result?.breakdown?.length">
                    <div class="text-[10.5px] tracking-widest text-ink-3 uppercase font-semibold mb-1.5">Breakdown</div>
                    <div class="space-y-1 max-h-[260px] overflow-auto">
                        <template x-for="b in (result?.breakdown || [])" :key="b.entity_id">
                            <div class="flex justify-between text-[11.5px] py-0.5">
                                <span x-text="b.nome" class="truncate"></span>
                                <span class="mono num text-ink-3" x-text="b.km.toFixed(2) + ' km'"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div x-show="error" x-cloak class="chip chip-danger w-full justify-center" x-text="error"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function routeSim() {
    return {
        map: null, fromMarker: null, toMarker: null, routeLayer: null,
        from: null, to: null,
        loading: false, result: null, error: null,

        init() {
            this.map = L.map('sim-map').setView([42.35, 13.7], 9);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap',
                maxZoom: 18,
            }).addTo(this.map);

            this.map.on('click', e => this.onClick(e));
        },

        onClick(e) {
            if (!this.from) {
                this.from = { lat: e.latlng.lat, lng: e.latlng.lng };
                this.fromMarker = L.marker([this.from.lat, this.from.lng], { title: 'From' }).addTo(this.map);
            } else if (!this.to) {
                this.to = { lat: e.latlng.lat, lng: e.latlng.lng };
                this.toMarker = L.marker([this.to.lat, this.to.lng], { title: 'To' }).addTo(this.map);
            }
        },

        reset() {
            if (this.fromMarker) { this.map.removeLayer(this.fromMarker); this.fromMarker = null; }
            if (this.toMarker) { this.map.removeLayer(this.toMarker); this.toMarker = null; }
            if (this.routeLayer) { this.map.removeLayer(this.routeLayer); this.routeLayer = null; }
            this.from = null; this.to = null;
            this.result = null; this.error = null;
        },

        async run() {
            if (!this.from || !this.to) return;
            this.loading = true; this.error = null; this.result = null;
            const csrf = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
            try {
                const res = await fetch('{{ $routingEndpoint }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf ?? '' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ from: this.from, to: this.to }),
                });
                this.result = await res.json();
                if (!res.ok || !this.result.ok) {
                    this.error = this.result?.error ?? ('HTTP ' + res.status);
                    return;
                }
                // Parse WKT LINESTRING and draw
                const coords = this.parseWkt(this.result.wkt);
                if (this.routeLayer) this.map.removeLayer(this.routeLayer);
                this.routeLayer = L.polyline(coords, { color: 'oklch(0.45 0.16 255)', weight: 4 }).addTo(this.map);
                this.map.fitBounds(this.routeLayer.getBounds(), { padding: [40, 40] });
            } catch (e) {
                this.error = e.message;
            } finally {
                this.loading = false;
            }
        },

        parseWkt(wkt) {
            // LINESTRING(lng lat, lng lat, ...)
            const inner = wkt.replace(/^LINESTRING\s*\(/, '').replace(/\)$/, '');
            return inner.split(',').map(p => {
                const [lng, lat] = p.trim().split(/\s+/).map(Number);
                return [lat, lng];
            });
        }
    };
}
</script>
@endpush
@endsection
