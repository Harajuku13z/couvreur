@extends('layouts.admin')

@section('title', 'Automatisation indexation')

@section('content')
<div class="p-6 space-y-6">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                <i class="fas fa-rocket mr-3 text-blue-600"></i>Automatisation indexation
            </h1>
            <p class="text-gray-600 max-w-3xl">
                Page simplifiée pour mettre à jour les sitemaps, régénérer <code>robots.txt</code> et envoyer les pages importantes à l'indexation.
            </p>
        </div>

        <div class="rounded-2xl bg-slate-900 text-white px-5 py-4 min-w-[260px]">
            <div class="text-xs uppercase tracking-[0.2em] text-slate-300 mb-2">Version</div>
            <div class="font-bold">{{ $siteReleaseName }}</div>
            <div class="text-sm text-slate-300">Release: {{ $siteVersion }}</div>
        </div>
    </div>

    @if(session('success'))
    <div class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-green-800">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-red-800">
        <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="text-xs uppercase tracking-wider text-gray-500 mb-2">URLs sitemap</div>
            <div class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_sitemap'] ?? 0) }}</div>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="text-xs uppercase tracking-wider text-gray-500 mb-2">Indexées suivies</div>
            <div class="text-3xl font-bold text-green-600">{{ number_format($stats['indexed'] ?? 0) }}</div>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="text-xs uppercase tracking-wider text-gray-500 mb-2">Annonces indexables</div>
            <div class="text-3xl font-bold text-blue-600">{{ number_format($sitemapCoverage['ads_total_indexable'] ?? 0) }}</div>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="text-xs uppercase tracking-wider text-gray-500 mb-2">API indexation</div>
            <div class="text-lg font-bold {{ $isGoogleConfigured ? 'text-green-600' : 'text-red-600' }}">
                {{ $isGoogleConfigured ? 'Configurée' : 'À configurer' }}
            </div>
        </div>
    </div>

    <div class="rounded-3xl border border-blue-100 bg-gradient-to-br from-blue-50 via-white to-emerald-50 p-6 shadow-sm">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
            <button type="button"
                    onclick="runAutomation()"
                    id="btn-automation"
                    class="rounded-3xl bg-slate-950 p-6 text-left text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-800">
                <i class="fas fa-wand-magic-sparkles text-3xl mb-4 block text-blue-300"></i>
                <span class="block text-xl font-bold">Automatisation complète</span>
                <span class="mt-2 block text-sm text-slate-300">Génère sitemap + robots.txt puis lance l'indexation.</span>
            </button>

            <button type="button"
                    onclick="runIndexation()"
                    id="btn-index"
                    class="rounded-3xl bg-emerald-600 p-6 text-left text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-700">
                <i class="fas fa-paper-plane text-3xl mb-4 block"></i>
                <span class="block text-xl font-bold">Indexation via SerpAPI</span>
                <span class="mt-2 block text-sm text-emerald-100">Envoie jusqu'à 150 URLs à l'API d'indexation configurée.</span>
            </button>

            <button type="button"
                    onclick="generateSitemapRobots()"
                    id="btn-sitemap"
                    class="rounded-3xl bg-violet-600 p-6 text-left text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-violet-700">
                <i class="fas fa-sitemap text-3xl mb-4 block"></i>
                <span class="block text-xl font-bold">Sitemap + robots.txt</span>
                <span class="mt-2 block text-sm text-violet-100">Réécrit les fichiers SEO avec le bon domaine.</span>
            </button>
        </div>

        <div id="results-zone" class="mt-6 hidden">
            <div id="results-content"></div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-bold text-gray-900 mb-4">
                <i class="fas fa-link mr-2 text-violet-600"></i>Liens à contrôler
            </h2>
            <div class="space-y-3 text-sm">
                <div>
                    <div class="font-semibold text-gray-700 mb-1">Sitemap à soumettre dans Google Search Console</div>
                    <a href="{{ $sitemapIndexUrl }}" target="_blank" class="block break-all rounded-xl border border-violet-200 bg-violet-50 px-4 py-3 text-violet-700 hover:underline">
                        {{ $sitemapIndexUrl }}
                    </a>
                </div>
                <div>
                    <div class="font-semibold text-gray-700 mb-1">Robots.txt</div>
                    <a href="{{ rtrim($siteUrl, '/') }}/robots.txt" target="_blank" class="block break-all rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-700 hover:underline">
                        {{ rtrim($siteUrl, '/') }}/robots.txt
                    </a>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-bold text-gray-900 mb-4">
                <i class="fas fa-chart-simple mr-2 text-blue-600"></i>Résumé sitemap
            </h2>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div class="rounded-xl bg-gray-50 border border-gray-200 p-4">
                    <div class="text-gray-500">Pages principales</div>
                    <div class="text-2xl font-bold text-gray-900">{{ number_format(($sitemapCoverage['primary_urls_total'] ?? 0) - ($sitemapCoverage['ads_total_indexable'] ?? 0)) }}</div>
                </div>
                <div class="rounded-xl bg-green-50 border border-green-200 p-4">
                    <div class="text-green-700">Annonces dans sitemap</div>
                    <div class="text-2xl font-bold text-green-700">{{ number_format($sitemapCoverage['ads_total_indexable'] ?? 0) }}</div>
                </div>
                <div class="rounded-xl bg-amber-50 border border-amber-200 p-4">
                    <div class="text-amber-700">Annonces exclues SEO</div>
                    <div class="text-2xl font-bold text-amber-700">{{ number_format($sitemapCoverage['ads_total_excluded'] ?? 0) }}</div>
                </div>
                <div class="rounded-xl bg-blue-50 border border-blue-200 p-4">
                    <div class="text-blue-700">Total sitemap SEO</div>
                    <div class="text-2xl font-bold text-blue-700">{{ number_format($sitemapCoverage['primary_urls_total'] ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
        <div class="font-bold mb-1"><i class="fas fa-circle-info mr-2"></i>Important</div>
        <p>
            SerpAPI ne force pas directement l'indexation Google. Le bouton utilise l'API d'indexation configurée côté site
            et garde le libellé SerpAPI pour votre workflow. Après l'envoi, contrôlez quelques URLs dans Google Search Console.
        </p>
    </div>
</div>

<script>
function showResult(html) {
    const zone = document.getElementById('results-zone');
    const content = document.getElementById('results-content');
    zone.classList.remove('hidden');
    content.innerHTML = html;
}

function buttonLoading(button, title, subtitle) {
    button.disabled = true;
    button.dataset.originalHtml = button.innerHTML;
    button.innerHTML = `<i class="fas fa-spinner fa-spin text-3xl mb-4 block"></i><span class="block text-xl font-bold">${title}</span><span class="mt-2 block text-sm opacity-80">${subtitle}</span>`;
}

function buttonRestore(button) {
    button.disabled = false;
    button.innerHTML = button.dataset.originalHtml;
}

function resultCard(type, title, lines) {
    const colors = {
        success: 'bg-green-50 border-green-200 text-green-800',
        error: 'bg-red-50 border-red-200 text-red-800',
        info: 'bg-blue-50 border-blue-200 text-blue-800'
    };
    const icon = type === 'error' ? 'fa-times-circle' : (type === 'success' ? 'fa-check-circle' : 'fa-spinner fa-spin');
    const body = lines.map(line => `<div>${line}</div>`).join('');
    return `<div class="rounded-2xl border px-5 py-4 ${colors[type]}"><div class="font-bold mb-2"><i class="fas ${icon} mr-2"></i>${title}</div><div class="text-sm space-y-1">${body}</div></div>`;
}

function postJson(url, payload = {}) {
    return fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(payload)
    }).then(async response => {
        const data = await response.json();
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Erreur inconnue');
        }
        return data;
    });
}

function runAutomation() {
    if (!confirm('Lancer automatisation complète : sitemap + robots.txt + indexation ?')) return;

    const btn = document.getElementById('btn-automation');
    buttonLoading(btn, 'Automatisation...', 'Traitement en cours');
    showResult(resultCard('info', 'Automatisation en cours', ['Génération du sitemap et de robots.txt...', 'Envoi des URLs à l’indexation...']));

    postJson('{{ route("admin.indexation.automation") }}', { limit: 200 })
        .then(data => {
            const sitemap = data.sitemap || {};
            const indexation = data.indexation || {};
            showResult(resultCard('success', 'Automatisation terminée', [
                `Sitemap: ${sitemap.index_url || '{{ $sitemapIndexUrl }}'}`,
                `Robots.txt: ${sitemap.robots_url || '{{ rtrim($siteUrl, '/') }}/robots.txt'}`,
                `URLs envoyées: ${indexation.success_count || 0}`,
                `Échecs: ${indexation.failed_count || 0}`,
                `Total traité: ${indexation.total || 0}`
            ]));
        })
        .catch(error => showResult(resultCard('error', 'Automatisation impossible', [error.message])))
        .finally(() => buttonRestore(btn));
}

function runIndexation() {
    if (!confirm('Lancer l’indexation via SerpAPI/API configurée ?')) return;

    const btn = document.getElementById('btn-index');
    buttonLoading(btn, 'Indexation...', 'Envoi des URLs');
    showResult(resultCard('info', 'Indexation en cours', ['Envoi de 150 URLs maximum à l’API configurée...']));

    postJson('{{ route("admin.indexation.index-urls") }}', { limit: 150 })
        .then(data => {
            showResult(resultCard('success', 'Indexation terminée', [
                `Demandes envoyées: ${data.success_count || 0}`,
                `Échecs: ${data.failed_count || 0}`,
                `Total traité: ${data.total || 0}`
            ]));
        })
        .catch(error => showResult(resultCard('error', 'Indexation impossible', [error.message])))
        .finally(() => buttonRestore(btn));
}

function generateSitemapRobots() {
    if (!confirm('Générer le sitemap et robots.txt maintenant ?')) return;

    const btn = document.getElementById('btn-sitemap');
    buttonLoading(btn, 'Génération...', 'Sitemap + robots.txt');
    showResult(resultCard('info', 'Génération en cours', ['Création de sitemap.xml et des sous-sitemaps...', 'Mise à jour de robots.txt avec le bon domaine...']));

    postJson('{{ route("admin.indexation.update-sitemap") }}')
        .then(data => {
            showResult(resultCard('success', 'Sitemap et robots.txt générés', [
                `Sitemap: ${data.index_url || '{{ $sitemapIndexUrl }}'}`,
                `Robots.txt: ${data.robots_url || '{{ rtrim($siteUrl, '/') }}/robots.txt'}`,
                `URLs sitemap: ${data.total_urls || 0}`
            ]));
        })
        .catch(error => showResult(resultCard('error', 'Génération impossible', [error.message])))
        .finally(() => buttonRestore(btn));
}
</script>
@endsection
