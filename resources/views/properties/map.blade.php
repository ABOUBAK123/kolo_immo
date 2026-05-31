@extends('layouts.app')
@section('title', 'Carte des logements')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
#map { height: calc(100vh - 64px); width: 100%; }
.leaflet-popup-content-wrapper { border-radius: 12px; padding: 0; overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,.15); }
.leaflet-popup-content { margin: 0; width: 220px !important; }
.map-popup img { width: 100%; height: 110px; object-fit: cover; }
.map-popup .body { padding: 10px 12px; }
.map-popup .price { font-weight: 800; color: #1B4F72; font-size: 15px; }
.map-popup .title { font-size: 13px; font-weight: 600; color: #111; margin: 2px 0; line-height: 1.3; }
.map-popup .city  { font-size: 11px; color: #9ca3af; }
.map-popup a.btn  { display: block; background: #F59E0B; color: #fff; text-align: center; padding: 8px; font-size: 12px; font-weight: 700; text-decoration: none; }
.price-marker { background: #fff; border: 2px solid #1B4F72; border-radius: 20px; padding: 3px 8px; font-weight: 800; font-size: 11px; color: #1B4F72; white-space: nowrap; box-shadow: 0 2px 6px rgba(0,0,0,.2); cursor: pointer; }
.price-marker:hover, .price-marker.active { background: #1B4F72; color: #fff; }
#panel { position: absolute; top: 80px; left: 12px; z-index: 1000; background: white; border-radius: 16px; padding: 16px; box-shadow: 0 4px 20px rgba(0,0,0,.15); width: 260px; }
#panel h2 { font-size: 14px; font-weight: 700; margin: 0 0 12px; color: #111; }
#panel label { font-size: 11px; font-weight: 600; color: #6b7280; display: block; margin-bottom: 4px; text-transform: uppercase; }
#panel input, #panel select { width: 100%; padding: 7px 10px; border: 1.5px solid #e5e7eb; border-radius: 8px; font-size: 13px; margin-bottom: 10px; box-sizing: border-box; }
#panel button { width: 100%; background: #1B4F72; color: #fff; border: none; padding: 9px; border-radius: 8px; font-weight: 700; font-size: 13px; cursor: pointer; }
#panel button:hover { background: #154460; }
#locate-btn { position: absolute; top: 80px; right: 12px; z-index: 1000; background: white; border: none; border-radius: 10px; padding: 10px 12px; box-shadow: 0 2px 8px rgba(0,0,0,.15); cursor: pointer; font-size: 18px; }
#result-count { font-size: 11px; color: #9ca3af; margin-top: 4px; }
</style>
@endpush

@section('content')
<div style="position:relative">

    {{-- Filter panel --}}
    <div id="panel">
        <h2>🗺 Filtres carte</h2>

        <label>Ville</label>
        <select id="f-city">
            <option value="">Toutes les villes</option>
            @foreach($cities as $c)
            <option>{{ $c }}</option>
            @endforeach
        </select>

        <label>Type</label>
        <select id="f-type">
            <option value="">Tous</option>
            <option value="appartement">Appartement</option>
            <option value="studio">Studio</option>
            <option value="villa">Villa</option>
            <option value="chambre">Chambre</option>
        </select>

        <label>Prix max (FCFA/nuit)</label>
        <input type="number" id="f-price" placeholder="ex: 50000" min="0" step="1000">

        <label>Rayon (km) autour du centre</label>
        <input type="range" id="f-radius" min="1" max="30" value="5" oninput="document.getElementById('radius-val').textContent = this.value + ' km'">
        <p id="radius-val" style="font-size:12px;color:#374151;margin:-6px 0 8px;text-align:right">5 km</p>

        <button onclick="applyFilters()">Appliquer</button>
        <p id="result-count"></p>
    </div>

    {{-- Geolocation button --}}
    <button id="locate-btn" onclick="locateMe()" title="Ma position">📍</button>

    <div id="map"></div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const map = L.map('map').setView([5.35, -4.0], 12); // Abidjan default

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 19
}).addTo(map);

let markers = [];
let centerMarker = null;
let radiusCircle = null;
let filterCenter = null;

function createIcon(price) {
    return L.divIcon({
        className: '',
        html: `<div class="price-marker">${Number(price).toLocaleString('fr-FR')} F</div>`,
        iconAnchor: [0, 0],
    });
}

function loadMarkers(params = {}) {
    const url = new URL('{{ route("properties.map-data") }}', window.location.origin);
    Object.entries(params).forEach(([k, v]) => { if (v) url.searchParams.set(k, v); });

    fetch(url)
        .then(r => r.json())
        .then(data => {
            markers.forEach(m => map.removeLayer(m));
            markers = [];
            const list = data.markers || [];
            document.getElementById('result-count').textContent = list.length + ' logement(s) trouvé(s)';

            list.forEach(p => {
                const icon = createIcon(p.price);
                const m = L.marker([p.lat, p.lng], {icon})
                    .bindPopup(`
                        <div class="map-popup">
                            ${p.photo ? `<img src="${p.photo}" onerror="this.style.display='none'">` : ''}
                            <div class="body">
                                <p class="price">${Number(p.price).toLocaleString('fr-FR')} FCFA <span style="font-weight:400;font-size:11px;color:#9ca3af">/nuit</span></p>
                                <p class="title">${p.title}</p>
                                <p class="city">📍 ${p.district ? p.district + ', ' : ''}${p.city}${p.distance ? ' — ' + p.distance + ' km' : ''}</p>
                            </div>
                            <a href="${p.url}" class="btn">Voir ce logement →</a>
                        </div>
                    `)
                    .addTo(map);
                markers.push(m);
            });

            if (list.length > 0 && !filterCenter) {
                const bounds = L.latLngBounds(list.map(p => [p.lat, p.lng]));
                map.fitBounds(bounds, {padding: [40, 40]});
            }
        });
}

function applyFilters() {
    const params = {
        city:      document.getElementById('f-city').value,
        type:      document.getElementById('f-type').value,
        price_max: document.getElementById('f-price').value,
    };
    if (filterCenter) {
        params.lat    = filterCenter[0];
        params.lng    = filterCenter[1];
        params.radius = document.getElementById('f-radius').value;
    }
    loadMarkers(params);
}

function locateMe() {
    if (!navigator.geolocation) return alert('Géolocalisation non disponible.');
    navigator.geolocation.getCurrentPosition(pos => {
        const {latitude: lat, longitude: lng} = pos.coords;
        filterCenter = [lat, lng];
        map.setView([lat, lng], 14);

        if (centerMarker) map.removeLayer(centerMarker);
        centerMarker = L.circleMarker([lat, lng], {
            radius: 8, fillColor: '#3b82f6', color: '#fff', weight: 2, fillOpacity: 1
        }).bindPopup('Vous êtes ici').addTo(map);

        updateRadiusCircle();
        applyFilters();
    }, () => alert('Impossible d\'obtenir votre position.'));
}

function updateRadiusCircle() {
    if (!filterCenter) return;
    const km = parseFloat(document.getElementById('f-radius').value);
    if (radiusCircle) map.removeLayer(radiusCircle);
    radiusCircle = L.circle(filterCenter, {
        radius: km * 1000,
        color: '#3b82f6', fillColor: '#93c5fd', fillOpacity: 0.1, weight: 1.5, dashArray: '6'
    }).addTo(map);
}

document.getElementById('f-radius').addEventListener('input', updateRadiusCircle);

// Initial load
loadMarkers();
</script>
@endpush
