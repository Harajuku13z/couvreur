{{-- Carte France : Leaflet + GeoJSON (public/geo/departements.geojson) --}}
@if(!empty($departmentsMap['show']))
@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
@endpush
<section class="py-16 md:py-20 bg-white dark:bg-slate-900 border-y border-gray-100 dark:border-slate-800" aria-labelledby="fr-map-title">
    <div class="site-shell w-full max-w-full">
        <div class="text-center mb-8 md:mb-10 px-1">
            <h2 id="fr-map-title" class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white tracking-tight">
                {{ $departmentsMap['title'] }}
            </h2>
            <div class="w-24 h-1.5 mx-auto mt-4 rounded-full" style="background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));"></div>
            @if(!empty($departmentsMap['subtitle']))
                <p class="mt-4 text-lg text-gray-600 dark:text-slate-400 max-w-3xl mx-auto">
                    {{ $departmentsMap['subtitle'] }}
                </p>
            @endif
        </div>

        <div class="grid lg:grid-cols-3 gap-8 items-start">
            <div class="lg:col-span-2 rounded-2xl overflow-hidden border border-gray-200 dark:border-slate-700 shadow-lg bg-slate-50 dark:bg-slate-800/50">
                <div id="france-departments-leaflet-map" class="w-full min-h-[320px] md:min-h-[480px] z-0" role="img" aria-label="Carte des départements français"></div>
            </div>
            <div class="space-y-3">
                <p class="text-sm font-semibold text-gray-800 dark:text-slate-200 uppercase tracking-wide">Départements mis en avant</p>
                <ul class="space-y-2">
                    @foreach($departmentsMap['items'] as $row)
                        <li>
                            <a href="{{ $row['url'] }}"
                               class="flex items-center justify-between gap-2 rounded-xl border border-gray-200 dark:border-slate-600 bg-gray-50 dark:bg-slate-800/80 px-4 py-3 text-sm font-medium text-gray-900 dark:text-white hover:border-[color:var(--primary-color)] hover:shadow-md transition">
                                <span>
                                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-white text-xs font-bold mr-2" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">{{ $row['code'] }}</span>
                                    {{ $row['name'] }}
                                </span>
                                <i class="fas fa-chevron-right text-gray-400 text-xs" aria-hidden="true"></i>
                            </a>
                        </li>
                    @endforeach
                </ul>
                <p class="text-xs text-gray-500 dark:text-slate-500 leading-relaxed pt-2">
                    Carte vectorielle (GeoJSON) + bibliothèque <a href="https://leafletjs.com/" class="underline hover:text-gray-700 dark:hover:text-slate-300" target="_blank" rel="noopener noreferrer">Leaflet</a>.
                </p>
            </div>
        </div>
    </div>
</section>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
(function () {
    const items = @json($departmentsMap['items'] ?? []);
    const geoUrl = @json($departmentsMap['geoJsonUrl'] ?? '');
    const primary = getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim() || '#2563eb';
    const secondary = getComputedStyle(document.documentElement).getPropertyValue('--secondary-color').trim() || '#10b981';

    const highlight = new Set(items.map(function (it) { return String(it.code).toUpperCase(); }));

    function normalizeCode(c) {
        c = String(c || '').trim().toUpperCase();
        if (c === '2A' || c === '2B') return c;
        if (/^\d+$/.test(c)) return c.padStart(2, '0');
        return c;
    }

    const urlByCode = {};
    items.forEach(function (it) {
        urlByCode[normalizeCode(it.code)] = it.url;
    });

    const map = L.map('france-departments-leaflet-map', {
        scrollWheelZoom: false,
        zoomControl: true,
        attributionControl: true
    });

    fetch(geoUrl, { credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (geojson) {
            const layer = L.geoJSON(geojson, {
                style: function (feat) {
                    const code = normalizeCode(feat.properties && feat.properties.code);
                    const on = highlight.has(code);
                    return {
                        color: on ? primary : '#94a3b8',
                        weight: on ? 2 : 0.6,
                        fillColor: on ? secondary : '#e2e8f0',
                        fillOpacity: on ? 0.55 : 0.35
                    };
                },
                onEachFeature: function (feature, lyr) {
                    const nom = (feature.properties && feature.properties.nom) ? feature.properties.nom : '';
                    const code = normalizeCode(feature.properties && feature.properties.code);
                    lyr.bindTooltip(nom + (code ? ' (' + code + ')' : ''), { sticky: true });
                    lyr.on('click', function () {
                        if (highlight.has(code) && urlByCode[code]) {
                            window.location.href = urlByCode[code];
                        }
                    });
                }
            });
            layer.addTo(map);
            try {
                map.fitBounds(layer.getBounds(), { padding: [24, 24] });
            } catch (e) {}
        })
        .catch(function () {
            document.getElementById('france-departments-leaflet-map').innerHTML =
                '<div class="p-8 text-center text-red-600 text-sm">Impossible de charger la carte. Vérifiez que le fichier <code>public/geo/departements.geojson</code> est présent.</div>';
        });
})();
</script>
@endif
