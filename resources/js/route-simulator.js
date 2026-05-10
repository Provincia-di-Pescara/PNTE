import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

// Vite breaks Leaflet's default icon auto-detection; set paths explicitly.
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({ iconUrl: markerIcon, iconRetinaUrl: markerIcon2x, shadowUrl: markerShadow });

const map = L.map('route-sim-map').setView([42.1, 13.7], 9);

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19,
}).addTo(map);

// Province layer for reference
fetch('/api/entities/geojson?tipo=provincia')
    .then(r => r.ok ? r.json() : null)
    .then(data => {
        if (!data?.features?.length) { return; }
        L.geoJSON(data, {
            style: { color: '#f97316', weight: 2, fillOpacity: 0.08 },
            onEachFeature: (f, l) => l.bindTooltip(f.properties.nome, { sticky: true, opacity: 0.8 }),
        }).addTo(map);
    });

const waypoints = [];
const markers = [];
let routeLayer = null;

const csrf = () => document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// ── DOM refs ──────────────────────────────────────────────────────────────────

const elClear     = document.getElementById('btn-sim-clear');
const elFit       = document.getElementById('btn-sim-fit');
const elDistance  = document.getElementById('sim-distance');
const elDistVal   = document.getElementById('sim-distance-value');
const elEmpty     = document.getElementById('sim-breakdown-empty');
const elLoading   = document.getElementById('sim-breakdown-loading');
const elTable     = document.getElementById('sim-breakdown-table');
const elBody      = document.getElementById('sim-breakdown-body');
const elTotal     = document.getElementById('sim-total-km');

// ── Snap ──────────────────────────────────────────────────────────────────────

async function snapRoute() {
    const res = await fetch('/api/routing/snap', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
        body: JSON.stringify({ waypoints }),
    });
    if (res.status === 503) {
        elEmpty.textContent = 'Motore OSRM non disponibile. Avvia il container osrm e riprova.';
        elEmpty.classList.remove('hidden');
        return null;
    }
    if (res.status === 422) {
        const body = await res.json();
        elEmpty.textContent = body.error ?? 'Waypoint lontano dalla rete stradale. Clicca più vicino a una strada.';
        elEmpty.classList.remove('hidden');
        return null;
    }
    if (!res.ok) { return null; }
    return res.json();
}

// ── Breakdown ─────────────────────────────────────────────────────────────────

async function fetchBreakdown(wkt) {
    elEmpty.classList.add('hidden');
    elLoading.classList.remove('hidden');
    elTable.classList.add('hidden');

    try {
        const res = await fetch('/api/routing/breakdown', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
            body: JSON.stringify({ wkt }),
        });
        if (!res.ok) { throw new Error(`HTTP ${res.status}`); }
        const data = await res.json();
        renderBreakdown(data.breakdown, data.total_km);
    } catch (e) {
        console.error('route-simulator breakdown error:', e);
        elLoading.classList.add('hidden');
        elEmpty.classList.remove('hidden');
    }
}

function renderBreakdown(rows, totalKm) {
    elLoading.classList.add('hidden');

    if (!rows.length) {
        elEmpty.textContent = 'Nessuna entità attraversata dal percorso.';
        elEmpty.classList.remove('hidden');
        return;
    }

    const province = rows.filter(r => r.tipo === 'provincia');
    const comuni   = rows.filter(r => r.tipo === 'comune');

    const totalProv   = province.reduce((s, r) => s + r.km, 0);
    const totalComuni = comuni.reduce((s, r) => s + r.km, 0);

    const sectionHeader = label => `<tr class="bg-surface-2">
        <td colspan="3" class="py-1 px-1 text-[10.5px] font-semibold tracking-[0.08em] uppercase text-ink-3">${label}</td>
        <td class="py-1 text-right text-[10.5px] text-ink-3">%</td>
    </tr>`;

    const renderRows = (group, subtotal) => group.map(r => {
        const pct = subtotal > 0 ? ((r.km / subtotal) * 100).toFixed(1) : '0.0';
        return `<tr>
            <td class="py-1.5 pr-4">${r.nome}</td>
            <td class="py-1.5 pr-4 text-ink-3"></td>
            <td class="py-1.5 text-right mono">${r.km.toFixed(3)}</td>
            <td class="py-1.5 text-right text-ink-3">${pct}%</td>
        </tr>`;
    }).join('');

    let html = '';
    if (province.length) {
        html += sectionHeader('Province');
        html += renderRows(province, totalProv);
    }
    if (comuni.length) {
        html += sectionHeader('Comuni');
        html += renderRows(comuni, totalComuni);
    }

    elBody.innerHTML = html;
    elTotal.textContent = totalKm.toFixed(3);
    elTable.classList.remove('hidden');
}

// ── Map interaction ───────────────────────────────────────────────────────────

map.on('click', async (e) => {
    const { lat, lng } = e.latlng;
    waypoints.push({ lat, lng });
    markers.push(L.marker([lat, lng]).addTo(map));

    if (waypoints.length < 2) { return; }

    const data = await snapRoute();
    if (!data) { return; }

    if (routeLayer) { map.removeLayer(routeLayer); }
    routeLayer = L.geoJSON(data.geojson, {
        style: { color: '#ef4444', weight: 4, opacity: 0.9 },
        interactive: false,
    }).addTo(map);

    elDistVal.textContent = data.distance_km.toFixed(3);
    elDistance.classList.remove('hidden');
    elFit.classList.remove('hidden');
    elClear.disabled = false;

    await fetchBreakdown(data.wkt);
});

// ── Clear ─────────────────────────────────────────────────────────────────────

elFit?.addEventListener('click', () => {
    if (routeLayer) {
        try { map.fitBounds(routeLayer.getBounds(), { padding: [16, 16] }); } catch { /* empty */ }
    }
});

elClear?.addEventListener('click', () => {
    markers.forEach(m => map.removeLayer(m));
    markers.length = 0;
    waypoints.length = 0;

    if (routeLayer) { map.removeLayer(routeLayer); routeLayer = null; }

    elDistance.classList.add('hidden');
    elFit.classList.add('hidden');
    elClear.disabled = true;
    elTable.classList.add('hidden');
    elLoading.classList.add('hidden');
    elEmpty.textContent = 'Clicca almeno 2 punti sulla mappa per calcolare il ripartitore.';
    elEmpty.classList.remove('hidden');
    elBody.innerHTML = '';
});
