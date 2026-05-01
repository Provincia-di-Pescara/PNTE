import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const map = L.map('map').setView([42.1, 13.7], 9); // Abruzzo center

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19,
}).addTo(map);

const waypointMarkers = [];
const waypoints = [];
let routeLayer = null;
let arsLayer = null;
let entitiesLayer = null;
let arsVisible = true;
let entitiesVisible = true;
const selectedEntityIds = new Set();

async function loadEntitiesLayer() {
    const res = await fetch('/api/entities/geojson');
    if (!res.ok) return;
    const data = await res.json();

    entitiesLayer = L.geoJSON(data, {
        style: (feature) => entityStyle(feature, false),
        onEachFeature(feature, layer) {
            const id = feature.properties.id;
            layer.on('click', () => {
                if (selectedEntityIds.has(id)) {
                    selectedEntityIds.delete(id);
                    layer.setStyle(entityStyle(feature, false));
                } else {
                    selectedEntityIds.add(id);
                    layer.setStyle(entityStyle(feature, true));
                }
                document.getElementById('input-entity-ids').value =
                    JSON.stringify([...selectedEntityIds]);
            });
            layer.bindTooltip(feature.properties.nome, { sticky: true, opacity: 0.8 });
        },
    }).addTo(map);
}

function entityStyle(feature, selected) {
    return {
        color: '#2563eb',
        weight: 1.5,
        dashArray: '4 3',
        fillOpacity: selected ? 0.18 : 0,
        fillColor: '#2563eb',
    };
}

// ── ARS overlay (green / red / orange) ──────────────────────────────────────

const ARS_COLORS = {
    0: '#ef4444', // red — no coverage
    1: '#22c55e', // green — single ARS
    2: '#f97316', // orange — multiple / conflicting
};

async function fetchArsOverlay(wkt) {
    const statusEl = document.getElementById('ars-status');
    if (statusEl) { statusEl.classList.remove('hidden'); }

    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    try {
        const res = await fetch('/api/routing/ars-overlay', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ wkt }),
        });

        if (!res.ok) return;
        const data = await res.json();

        if (arsLayer) map.removeLayer(arsLayer);

        arsLayer = L.geoJSON(data.coverage_geojson, {
            style() {
                // coverage_count is always 1 per feature (one ARS intersection per feature)
                // Overlapping features visually indicate multiple coverage
                return {
                    color: '#22c55e', // green — ARS covered
                    weight: 6,
                    opacity: 0.8,
                    fillOpacity: 0.2,
                    fillColor: '#22c55e',
                };
            },
            onEachFeature(feature, layer) {
                const nome = feature.properties.standard_route_nome;
                layer.bindTooltip(`ARS: ${nome}`, { sticky: true, opacity: 0.9 });
            },
        });

        if (arsVisible) arsLayer.addTo(map);
    } finally {
        if (statusEl) { statusEl.classList.add('hidden'); }
    }
}

// ── Route snapping ───────────────────────────────────────────────────────────

map.on('click', async (e) => {
    const { lat, lng } = e.latlng;
    waypoints.push({ lat, lng });
    const marker = L.marker([lat, lng]).addTo(map);
    waypointMarkers.push(marker);

    if (waypoints.length >= 2) {
        await fetchAndDisplayRoute();
    }
});

async function fetchAndDisplayRoute() {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const res = await fetch('/api/routing/snap', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ waypoints }),
    });
    if (!res.ok) {
        document.getElementById('input-geometry').value = '';
        document.getElementById('input-waypoints').value = '';
        document.getElementById('input-distance-km').value = '';
        return;
    }
    const data = await res.json();

    if (routeLayer) map.removeLayer(routeLayer);
    routeLayer = L.geoJSON(data.geojson, {
        style: { color: '#ef4444', weight: 4, opacity: 0.9 }, // red = no ARS coverage by default
    }).addTo(map);
    map.fitBounds(routeLayer.getBounds());

    document.getElementById('input-waypoints').value = JSON.stringify(waypoints);
    document.getElementById('input-geometry').value = data.wkt;
    document.getElementById('input-distance-km').value = data.distance_km;

    // Analyse ARS coverage for the new route
    await fetchArsOverlay(data.wkt);
}

// ── Controls ─────────────────────────────────────────────────────────────────

document.getElementById('btn-clear')?.addEventListener('click', () => {
    waypointMarkers.forEach(m => map.removeLayer(m));
    waypointMarkers.length = 0;
    waypoints.length = 0;

    if (routeLayer) { map.removeLayer(routeLayer); routeLayer = null; }
    if (arsLayer) { map.removeLayer(arsLayer); arsLayer = null; }

    selectedEntityIds.clear();
    document.getElementById('input-waypoints').value = '';
    document.getElementById('input-geometry').value = '';
    document.getElementById('input-distance-km').value = '';
    document.getElementById('input-entity-ids').value = '';
});

document.getElementById('btn-toggle-ars')?.addEventListener('click', () => {
    arsVisible = !arsVisible;
    if (arsLayer) {
        arsVisible ? arsLayer.addTo(map) : map.removeLayer(arsLayer);
    }
});

document.getElementById('btn-toggle-entities')?.addEventListener('click', () => {
    entitiesVisible = !entitiesVisible;
    if (entitiesLayer) {
        entitiesVisible ? entitiesLayer.addTo(map) : map.removeLayer(entitiesLayer);
    }
});

// ── Initialise ────────────────────────────────────────────────────────────────

loadEntitiesLayer();

