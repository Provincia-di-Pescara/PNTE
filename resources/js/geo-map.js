import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const el = document.getElementById('geo-map');

if (el) {
    const map = L.map('geo-map').setView([41.87, 12.56], 6);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 18,
    }).addTo(map);

    const style = {
        comune:     { color: '#3b82f6', weight: 0.5, fillOpacity: 0.1 },
        provincia:  { color: '#f97316', weight: 2,   fillOpacity: 0.15 },
        anas:       { color: '#ef4444', weight: 1,   fillOpacity: 0.1 },
        autostrada: { color: '#8b5cf6', weight: 1,   fillOpacity: 0.1 },
    };

    function popup(p) {
        return [
            `<strong>${p.nome ?? '—'}</strong>`,
            p.tipo,
            p.codice_istat ? `· ${p.codice_istat}` : null,
        ].filter(Boolean).join(' ');
    }

    function loadLayer(url, layerGroup) {
        return fetch(url)
            .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
            .then(data => {
                if (!data.features?.length) return null;
                const layer = L.geoJSON(data, {
                    style: f => style[f.properties?.tipo] ?? { color: '#6b7280', weight: 1, fillOpacity: 0.1 },
                    onEachFeature: (f, l) => l.bindPopup(popup(f.properties)),
                });
                layer.addTo(layerGroup);
                return layer;
            });
    }

    // Province always visible; comuni loaded once on demand when zoomed in (zoom >= 9).
    const provinceGroup = L.layerGroup().addTo(map);
    const comuniGroup   = L.layerGroup().addTo(map);

    let comuniLoaded = false;

    loadLayer('/api/entities/geojson?tipo=provincia', provinceGroup)
        .then(layer => { if (layer) map.fitBounds(layer.getBounds(), { padding: [16, 16] }); })
        .catch(e => console.error('geo-map province error:', e));

    map.on('zoomend', () => {
        if (map.getZoom() >= 9 && !comuniLoaded) {
            comuniLoaded = true;
            loadLayer('/api/entities/geojson?tipo=comune', comuniGroup)
                .catch(e => console.error('geo-map comuni error:', e));
        }
    });
}
