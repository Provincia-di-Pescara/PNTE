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
    routeLayer = L.geoJSON(data.geojson).addTo(map);
    map.fitBounds(routeLayer.getBounds());

    document.getElementById('input-waypoints').value = JSON.stringify(waypoints);
    document.getElementById('input-geometry').value = data.wkt;
    document.getElementById('input-distance-km').value = data.distance_km;
}

document.getElementById('btn-clear')?.addEventListener('click', () => {
    waypointMarkers.forEach(m => map.removeLayer(m));
    waypointMarkers.length = 0;
    waypoints.length = 0;
    if (routeLayer) { map.removeLayer(routeLayer); routeLayer = null; }
    document.getElementById('input-waypoints').value = '';
    document.getElementById('input-geometry').value = '';
    document.getElementById('input-distance-km').value = '';
});
