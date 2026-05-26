@extends('layouts.admin')

@section('title', 'Couvreur Ads Pilot')
@section('page_title', 'Couvreur Ads Pilot')

@section('content')
<div class="max-w-7xl mx-auto p-4 md:py-8">
    <div class="bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900 rounded-3xl p-6 md:p-10 text-white shadow-xl">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="inline-flex items-center rounded-full bg-emerald-400/15 px-4 py-2 text-sm font-semibold text-emerald-100 border border-emerald-300/30">
                    <i class="fas fa-shield-alt mr-2"></i>Intégré au site Laravel - mode validation humaine
                </div>
                <h1 class="mt-5 text-3xl md:text-5xl font-black leading-tight">Couvreur Ads Pilot</h1>
                <p class="mt-4 max-w-3xl text-blue-100 text-lg">
                    Pilote Google Ads pour couvreurs: génération de campagne locale, analyse des termes de recherche,
                    recommandations IA, mots-clés négatifs et rapports. Aucune action financière risquée n'est appliquée automatiquement.
                </p>
            </div>
            <div class="grid grid-cols-2 gap-3 min-w-[280px]">
                <div class="rounded-2xl bg-white/10 border border-white/10 p-4">
                    <div class="text-xs text-blue-100">Campagnes</div>
                    <div class="text-2xl font-black">{{ $stats['campaigns'] }}</div>
                </div>
                <div class="rounded-2xl bg-white/10 border border-white/10 p-4">
                    <div class="text-xs text-blue-100">Prochain scan</div>
                    <div class="text-xl font-black">{{ $stats['nextScan'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <div class="bg-white rounded-2xl p-5 shadow border border-gray-100">
            <i class="fas fa-link text-blue-600"></i>
            <div class="mt-3 text-2xl font-black text-gray-900">{{ $stats['accounts'] }}</div>
            <div class="text-sm text-gray-500">Comptes Google Ads</div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow border border-gray-100">
            <i class="fas fa-euro-sign text-blue-600"></i>
            <div class="mt-3 text-2xl font-black text-gray-900">{{ $stats['spentToday'] }}</div>
            <div class="text-sm text-gray-500">Dépensé aujourd'hui</div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow border border-gray-100">
            <i class="fas fa-phone text-emerald-600"></i>
            <div class="mt-3 text-2xl font-black text-gray-900">{{ $stats['leads'] }}</div>
            <div class="text-sm text-gray-500">Leads estimés</div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow border border-gray-100">
            <i class="fas fa-mouse-pointer text-blue-600"></i>
            <div class="mt-3 text-2xl font-black text-gray-900">{{ $stats['costPerLead'] }}</div>
            <div class="text-sm text-gray-500">Coût par lead</div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow border border-gray-100">
            <i class="fas fa-heartbeat text-emerald-600"></i>
            <div class="mt-3 text-2xl font-black text-gray-900">{{ $campaign['healthScore'] }}/100</div>
            <div class="text-sm text-gray-500">Score santé</div>
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_0.9fr]">
        <div class="bg-white rounded-3xl shadow border border-gray-100 p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black text-gray-900">Assistant création campagne couvreur</h2>
                    <p class="mt-1 text-gray-600">Remplissez le brief puis générez une structure Google Ads modifiable.</p>
                </div>
                <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">Dry-run</span>
            </div>

            <form id="campaignForm" class="mt-6 grid gap-4 md:grid-cols-2">
                @csrf
                <input name="business_name" class="rounded-xl border border-gray-300 px-4 py-3" value="{{ setting('company_name', 'Entreprise Couverture') }}" placeholder="Nom entreprise">
                <input name="website_url" class="rounded-xl border border-gray-300 px-4 py-3" value="{{ config('app.url') }}" placeholder="Site web">
                <input name="phone" class="rounded-xl border border-gray-300 px-4 py-3" value="{{ setting('company_phone', '0600000000') }}" placeholder="Téléphone">
                <input name="daily_budget" type="number" min="1" max="500" class="rounded-xl border border-gray-300 px-4 py-3" value="15" placeholder="Budget journalier max">
                <input name="service_areas" class="rounded-xl border border-gray-300 px-4 py-3 md:col-span-2" value="{{ setting('company_city', 'Chalon-sur-Saône') }}" placeholder="Zones, séparées par virgules">
                <input name="offer" class="rounded-xl border border-gray-300 px-4 py-3" value="-30% sur vos travaux" placeholder="Offre commerciale">
                <select name="objective" class="rounded-xl border border-gray-300 px-4 py-3">
                    <option>Appels</option>
                    <option>Formulaires</option>
                    <option>Devis</option>
                    <option>Trafic site</option>
                </select>

                <div class="md:col-span-2">
                    <div class="text-sm font-semibold text-gray-700 mb-2">Services proposés</div>
                    <div class="grid gap-2 md:grid-cols-3">
                        @foreach(['Couverture','Réparation toiture','Fuite toiture','Zinguerie','Nettoyage toiture','Démoussage toiture','Isolation toiture','Rénovation toiture','Façade'] as $service)
                            <label class="flex items-center gap-2 rounded-xl border border-gray-200 p-3 text-sm">
                                <input type="checkbox" name="services[]" value="{{ $service }}" @checked(in_array($service, ['Couverture','Fuite toiture','Zinguerie','Rénovation toiture']))>
                                {{ $service }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="md:col-span-2 inline-flex items-center justify-center px-5 py-3 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700">
                    <i class="fas fa-magic mr-2"></i>Générer la campagne Ads
                </button>
            </form>
        </div>

        <div class="bg-white rounded-3xl shadow border border-gray-100 p-6">
            <h2 class="text-xl font-black text-gray-900">Campagne démo supervisée</h2>
            <div class="mt-5 rounded-2xl bg-gray-50 border p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-bold text-gray-900">{{ $campaign['name'] }}</div>
                        <div class="text-sm text-gray-500">Budget {{ $campaign['dailyBudget'] }} - dernier scan {{ $campaign['lastScan'] }}</div>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">{{ $campaign['status'] }}</span>
                </div>
            </div>

            <div class="mt-5 grid gap-3">
                <button onclick="runScan()" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-slate-900 text-white font-bold hover:bg-slate-800">
                    <i class="fas fa-search-dollar mr-2"></i>Lancer un scan IA
                </button>
                <button onclick="generateReport()" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-emerald-600 text-white font-bold hover:bg-emerald-700">
                    <i class="fas fa-file-alt mr-2"></i>Générer rapport
                </button>
            </div>

            <div class="mt-5 rounded-2xl bg-orange-50 border border-orange-200 p-4 text-orange-900 text-sm">
                <strong>Sécurité:</strong> hausse budget, publication campagne et modifications massives nécessitent toujours une validation humaine.
            </div>
        </div>
    </div>

    <div class="mt-8 bg-white rounded-3xl shadow border border-gray-100 overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-xl font-black text-gray-900">Termes de recherche</h2>
            <p class="text-gray-600">Les termes inutiles sont proposés en exclusion, pas appliqués sans validation.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-5 py-3 text-left">Terme</th>
                        <th class="px-5 py-3 text-left">Clics</th>
                        <th class="px-5 py-3 text-left">Coût</th>
                        <th class="px-5 py-3 text-left">Conversions</th>
                        <th class="px-5 py-3 text-left">Décision IA</th>
                    </tr>
                </thead>
                <tbody id="termsTable" class="divide-y">
                    @foreach($searchTerms as $term)
                        <tr>
                            <td class="px-5 py-3 font-semibold text-gray-900">{{ $term['term'] }}</td>
                            <td class="px-5 py-3">{{ $term['clicks'] }}</td>
                            <td class="px-5 py-3">{{ $term['cost'] }}</td>
                            <td class="px-5 py-3">{{ $term['conversions'] }}</td>
                            <td class="px-5 py-3"><span class="px-2 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-bold">En attente scan</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="bg-white rounded-3xl shadow border border-gray-100 p-6">
            <h2 class="text-xl font-black text-gray-900">Résultat génération / scan</h2>
            <pre id="resultBox" class="mt-4 min-h-[220px] max-h-[480px] overflow-auto rounded-2xl bg-slate-950 text-slate-100 p-4 text-xs">Aucun résultat pour le moment.</pre>
        </div>

        <div class="bg-white rounded-3xl shadow border border-gray-100 p-6">
            <h2 class="text-xl font-black text-gray-900">Mots-clés négatifs globaux</h2>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach($negativeKeywords as $keyword)
                    <span class="px-3 py-1 rounded-full bg-red-50 text-red-700 text-xs font-bold border border-red-100">{{ $keyword }}</span>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const resultBox = document.getElementById('resultBox');

function showResult(data) {
    resultBox.textContent = JSON.stringify(data, null, 2);
}

document.getElementById('campaignForm').addEventListener('submit', function (event) {
    event.preventDefault();
    resultBox.textContent = 'Génération en cours...';

    const formData = new FormData(event.target);

    fetch('{{ route("admin.ads-pilot.generate-campaign") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: formData
    })
    .then(response => response.json())
    .then(showResult)
    .catch(error => showResult({ error: error.message }));
});

function runScan() {
    resultBox.textContent = 'Scan en cours...';

    fetch('{{ route("admin.ads-pilot.scan") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        showResult(data);
        if (data.searchTerms) {
            const rows = data.searchTerms.map(term => {
                const badge = term.decision === 'exclude'
                    ? '<span class="px-2 py-1 rounded-full bg-red-100 text-red-700 text-xs font-bold">Exclure</span>'
                    : '<span class="px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold">Garder</span>';

                return `<tr>
                    <td class="px-5 py-3 font-semibold text-gray-900">${term.term}</td>
                    <td class="px-5 py-3">${term.clicks}</td>
                    <td class="px-5 py-3">${term.cost}</td>
                    <td class="px-5 py-3">${term.conversions}</td>
                    <td class="px-5 py-3">${badge}<div class="text-xs text-gray-500 mt-1">${term.reason}</div></td>
                </tr>`;
            }).join('');

            document.getElementById('termsTable').innerHTML = rows;
        }
    })
    .catch(error => showResult({ error: error.message }));
}

function generateReport() {
    resultBox.textContent = 'Rapport en cours...';

    fetch('{{ route("admin.ads-pilot.report") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(showResult)
    .catch(error => showResult({ error: error.message }));
}
</script>
@endsection
