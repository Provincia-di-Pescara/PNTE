import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const el = document.getElementById('geo-map');

if (el) {
    const map = L.map('geo-map').setView([42.35, 13.9], 8);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 18,
    }).addTo(map);

    const colors = {
        comune: '#3b82f6',
        provincia: '#f97316',
        anas: '#ef4444',
        autostrada: '#8b5cf6',
    };

    fetch('/api/entities/geojson')
        .then(r => r.json())
        .then(data => {
            if (!data.features?.length) {
                return;
            }

            const layer = L.geoJSON(data, {
                style: feature => ({
                    color: colors[feature.properties?.tipo] ?? '#6b7280',
                    weight: feature.properties?.tipo === 'provincia' ? 2 : 1,
                    fillOpacity: 0.15,
                    fillColor: colors[feature.properties?.tipo] ?? '#6b7280',
                }),
                onEachFeature: (feature, featureLayer) => {
                    const p = feature.properties;
                    const label = [
                        `<strong>${p.nome ?? '—'}</strong>`,
                        p.tipo,
                        p.codice_istat ? `· ${p.codice_istat}` : null,
                    ].filter(Boolean).join(' ');
                    featureLayer.bindPopup(label);
                },
            }).addTo(map);

            map.fitBounds(layer.getBounds(), { padding: [16, 16] });
        })
        .catch(e => console.error('geo-map error:', e));
}
