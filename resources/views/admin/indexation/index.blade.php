@extends('layouts.admin')

@section('title', 'Indexation Google')

@section('content')
<div class="p-6 space-y-8">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                <i class="fas fa-search mr-3"></i>Indexation Google
            </h1>
            <p class="text-gray-600">
                Pilotez l’indexation selon la nouvelle logique SEO: canonical propre, annonces faibles en <code>noindex, follow</code>,
                audit qualité et sitemaps thématiques.
            </p>
        </div>

        <div class="bg-slate-900 text-white rounded-2xl px-5 py-4 shadow-sm min-w-[280px]">
            <div class="text-xs uppercase tracking-[0.2em] text-slate-300 mb-2">Version du site</div>
            <div class="text-lg font-bold">{{ $siteReleaseName }}</div>
            <div class="text-sm text-slate-200 mt-1">Release: {{ $siteVersion }}</div>
            <div class="text-xs text-slate-400 mt-3">
                Mettez à jour <code class="text-slate-200">APP_RELEASE_NAME</code> et <code class="text-slate-200">APP_VERSION</code> dans le <code class="text-slate-200">.env</code> après chaque déploiement.
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-5 py-4 rounded-xl">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl">
        <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-5">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs uppercase tracking-wider text-gray-500 mb-2">URLs dans les sitemaps</div>
            <div class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_sitemap'] ?? 0) }}</div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs uppercase tracking-wider text-gray-500 mb-2">URLs indexées</div>
            <div class="text-3xl font-bold text-green-600">{{ number_format($stats['indexed'] ?? 0) }}</div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs uppercase tracking-wider text-gray-500 mb-2">Non indexées</div>
            <div class="text-3xl font-bold text-amber-600">{{ number_format($stats['not_indexed'] ?? 0) }}</div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs uppercase tracking-wider text-gray-500 mb-2">Taux d’indexation</div>
            <div class="text-3xl font-bold text-indigo-600">
                {{ $stats['total_tracked'] > 0 ? round($stats['indexed'] / $stats['total_tracked'] * 100, 1) : 0 }}%
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs uppercase tracking-wider text-gray-500 mb-2">Noindex auto annonces faibles</div>
            <div class="text-lg font-bold {{ $adsAutoNoindexLowQuality ? 'text-green-600' : 'text-red-600' }}">
                {{ $adsAutoNoindexLowQuality ? 'Activé' : 'Désactivé' }}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        <div class="xl:col-span-2 space-y-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">
                            <i class="fas fa-bolt mr-2 text-blue-600"></i>Actions rapides
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">Vérifiez, indexez et régénérez les sitemaps sans quitter cette page.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <button onclick="verifierUrls()"
                            class="rounded-2xl bg-blue-600 hover:bg-blue-700 text-white p-6 text-left shadow-sm transition"
                            id="btn-verify">
                        <i class="fas fa-search-plus text-3xl mb-3 block"></i>
                        <span class="font-semibold text-lg block">Vérifier 50 URLs</span>
                        <span class="text-blue-100 text-sm">Inspection via Google</span>
                    </button>

                    <button onclick="indexerUrls()"
                            class="rounded-2xl bg-green-600 hover:bg-green-700 text-white p-6 text-left shadow-sm transition"
                            id="btn-index">
                        <i class="fas fa-rocket text-3xl mb-3 block"></i>
                        <span class="font-semibold text-lg block">Indexer 150 URLs</span>
                        <span class="text-green-100 text-sm">Demande via API</span>
                    </button>

                    <button onclick="regenererSitemap()"
                            class="rounded-2xl bg-violet-600 hover:bg-violet-700 text-white p-6 text-left shadow-sm transition"
                            id="btn-sitemap">
                        <i class="fas fa-sitemap text-3xl mb-3 block"></i>
                        <span class="font-semibold text-lg block">Régénérer les sitemaps</span>
                        <span class="text-violet-100 text-sm">Index + sous-sitemaps</span>
                    </button>
                </div>

                <div id="results-zone" class="mt-6 hidden">
                    <div id="results-content"></div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">
                            <i class="fas fa-sitemap mr-2 text-violet-600"></i>Architecture des sitemaps
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">
                            Le sitemap principal est désormais un index SEO propre. Un sitemap d’inventaire complet regroupe aussi toutes les pages publiées pour contrôle interne.
                        </p>
                    </div>
                    <a href="{{ $sitemapIndexUrl }}" target="_blank"
                       class="inline-flex items-center px-4 py-2 bg-violet-50 text-violet-700 rounded-xl text-sm font-semibold border border-violet-200">
                        <i class="fas fa-external-link-alt mr-2"></i>Ouvrir l’index
                    </a>
                </div>

                <div class="bg-violet-50 border border-violet-200 rounded-2xl p-4 mb-5">
                    <div class="text-sm text-violet-900">
                        <div class="font-semibold mb-2">URL canonique du sitemap à soumettre dans Search Console</div>
                        <code class="block bg-white border border-violet-200 rounded-lg px-3 py-2 text-sm break-all">{{ $sitemapIndexUrl }}</code>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4">
                        <div class="text-xs uppercase tracking-wider text-gray-500 mb-1">Services publiés</div>
                        <div class="text-2xl font-bold text-gray-900">{{ number_format($sitemapCoverage['services_total'] ?? 0) }}</div>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4">
                        <div class="text-xs uppercase tracking-wider text-gray-500 mb-1">Annonces publiées</div>
                        <div class="text-2xl font-bold text-gray-900">{{ number_format($sitemapCoverage['ads_total_published'] ?? 0) }}</div>
                    </div>
                    <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-4">
                        <div class="text-xs uppercase tracking-wider text-green-700 mb-1">Annonces SEO incluses</div>
                        <div class="text-2xl font-bold text-green-700">{{ number_format($sitemapCoverage['ads_total_indexable'] ?? 0) }}</div>
                    </div>
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4">
                        <div class="text-xs uppercase tracking-wider text-amber-700 mb-1">Annonces exclues du sitemap SEO</div>
                        <div class="text-2xl font-bold text-amber-700">{{ number_format($sitemapCoverage['ads_total_excluded'] ?? 0) }}</div>
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4 mb-5 text-sm text-slate-700">
                    <div class="font-semibold text-slate-900 mb-2">Lecture rapide</div>
                    <div>Le sitemap SEO principal contient {{ number_format($sitemapCoverage['primary_urls_total'] ?? 0) }} URLs.</div>
                    <div>Le sitemap d’inventaire complet contient {{ number_format($sitemapCoverage['inventory_urls_total'] ?? 0) }} URLs publiées.</div>
                    <div class="mt-2">
                        Soumettez à Google uniquement <code>{{ $sitemapIndexUrl }}</code>. Le fichier d’inventaire sert surtout à vérifier que toutes les pages existent bien côté site.
                    </div>
                    <div class="mt-2">
                        Sitemap d’inventaire : <a href="{{ $inventorySitemapUrl }}" target="_blank" class="text-blue-600 hover:underline">{{ $inventorySitemapUrl }}</a>
                    </div>
                </div>

                @if(!empty($sitemapFiles))
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500 uppercase text-xs tracking-wider">
                                <th class="text-left py-3 pr-4">Fichier</th>
                                <th class="text-left py-3 pr-4">Segment</th>
                                <th class="text-center py-3 pr-4">URLs</th>
                                <th class="text-center py-3 pr-4">Taille</th>
                                <th class="text-center py-3 pr-4">Modifié</th>
                                <th class="text-right py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sitemapFiles as $file)
                            <tr class="border-b border-gray-100">
                                <td class="py-4 pr-4 font-medium text-gray-900">{{ $file['filename'] }}</td>
                                <td class="py-4 pr-4 text-gray-600">
                                    {{ $file['category'] }}
                                    @if($file['is_inventory'] ?? false)
                                    <div class="mt-1">
                                        <span class="inline-flex px-2 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold">Inventaire complet</span>
                                    </div>
                                    @endif
                                </td>
                                <td class="py-4 pr-4 text-center">
                                    <span class="inline-flex px-3 py-1 rounded-full bg-blue-50 text-blue-700 font-semibold">{{ number_format($file['url_count']) }}</span>
                                </td>
                                <td class="py-4 pr-4 text-center text-gray-600">{{ $file['size_kb'] }} KB</td>
                                <td class="py-4 pr-4 text-center text-gray-600">{{ $file['modified_at'] }}</td>
                                <td class="py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ $file['public_url'] }}" target="_blank"
                                           class="px-3 py-2 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50">
                                            Voir
                                        </a>
                                        @if($isGoogleConfigured)
                                        <button onclick="soumettreGoogle('{{ $file['relative_path'] }}', this)"
                                                class="px-3 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white">
                                            Soumettre
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 px-5 py-4">
                    Aucun sous-sitemap détecté. Lancez une régénération pour créer `public/sitemap/*.xml`.
                </div>
                @endif
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="rounded-2xl bg-slate-50 border border-slate-200 px-4 py-4 mb-5">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-code-branch text-slate-700 mt-1"></i>
                        <div class="text-sm text-slate-700">
                            <div class="font-semibold text-slate-900">Mise à jour actuellement affichée</div>
                            <div class="mt-1">{{ $siteReleaseName }} <span class="text-slate-500">({{ $siteVersion }})</span></div>
                        </div>
                    </div>
                </div>

                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-sliders-h mr-2 text-emerald-600"></i>Configuration SEO et Search Console
                </h2>

                <form method="POST" action="{{ route('admin.indexation.update') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">URL du site</label>
                        <input type="url" name="site_url" value="{{ $siteUrl }}" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-2">Cette URL sert de base aux canonicals et aux sitemaps.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Credentials JSON Google Search Console</label>
                        <textarea name="google_search_console_credentials" rows="8"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl font-mono text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder='{"type":"service_account", ...}'>{{ $googleCredentials }}</textarea>
                        <p class="text-xs text-gray-500 mt-2">Compte de service utilisé pour l’inspection d’URL et les demandes d’indexation.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="flex items-start gap-3 p-4 rounded-2xl border border-gray-200 bg-gray-50">
                            <input type="checkbox" name="daily_indexing_enabled" value="1" class="mt-1"
                                   {{ $dailyIndexingEnabled ? 'checked' : '' }}>
                            <span>
                                <span class="block font-semibold text-gray-800">Indexation quotidienne automatique</span>
                                <span class="block text-sm text-gray-600">150 URLs/jour via cron à 02h00.</span>
                            </span>
                        </label>

                        <label class="flex items-start gap-3 p-4 rounded-2xl border border-gray-200 bg-gray-50">
                            <input type="checkbox" name="ads_auto_noindex_low_quality" value="1" class="mt-1"
                                   {{ $adsAutoNoindexLowQuality ? 'checked' : '' }}>
                            <span>
                                <span class="block font-semibold text-gray-800">Noindex auto pour annonces faibles</span>
                                <span class="block text-sm text-gray-600">Laisse crawlable, retire du sitemap et garde le maillage.</span>
                            </span>
                        </label>
                    </div>

                    <button type="submit"
                            class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-sm">
                        <i class="fas fa-save mr-2"></i>Sauvegarder
                    </button>
                </form>
            </div>
        </div>

        <div class="space-y-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-check-double mr-2 text-slate-700"></i>Règles actives
                </h2>
                <ul class="space-y-3 text-sm text-gray-700">
                    <li class="flex gap-3">
                        <i class="fas fa-link text-blue-600 mt-1"></i>
                        <span>Canonical affichée uniquement sur les annonces indexables.</span>
                    </li>
                    <li class="flex gap-3">
                        <i class="fas fa-ban text-amber-600 mt-1"></i>
                        <span>Annonces faibles en <code>noindex, follow</code>, sans blocage dans <code>robots.txt</code>.</span>
                    </li>
                    <li class="flex gap-3">
                        <i class="fas fa-code text-violet-600 mt-1"></i>
                        <span>Schema FAQ retiré du JSON-LD. Le contenu FAQ reste visible sur la page.</span>
                    </li>
                    <li class="flex gap-3">
                        <i class="fas fa-map-signs text-emerald-600 mt-1"></i>
                        <span>JSON-LD local: <code>RoofingContractor</code> / <code>ProfessionalService</code> + <code>Service</code> + breadcrumbs.</span>
                    </li>
                </ul>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-file-medical-alt mr-2 text-amber-600"></i>Audit SEO annonces
                </h2>
                <div class="space-y-4 text-sm text-gray-700">
                    <div class="rounded-xl bg-gray-900 text-gray-100 p-4 font-mono text-xs overflow-x-auto">
                        php artisan seo:audit-ads --sample=500<br>
                        php artisan seo:audit-ads --all
                    </div>
                    <p>Rapports générés dans <code>storage/app/seo-audits/</code>. Utilisez-les pour réécrire, fusionner ou laisser en noindex les pages faibles.</p>

                    @if(!empty($latestAuditReports))
                    <div>
                        <div class="font-semibold text-gray-900 mb-2">Derniers rapports détectés</div>
                        <div class="space-y-2">
                            @foreach($latestAuditReports as $report)
                            <div class="rounded-xl border border-gray-200 px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $report['filename'] }}</div>
                                <div class="text-xs text-gray-500 mt-1">{{ $report['modified_at'] }} • {{ $report['size_kb'] }} KB</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-terminal mr-2 text-gray-700"></i>Commandes utiles
                </h2>
                <div class="rounded-xl bg-gray-900 text-gray-100 p-4 font-mono text-xs overflow-x-auto space-y-3">
                    <div>php artisan indexation:simple stats</div>
                    <div>php artisan indexation:simple verify --limit=100</div>
                    <div>php artisan indexation:simple index --limit=150</div>
                    <div>php artisan sitemap:generate-daily</div>
                    <div>php artisan seo:audit-ads --sample=1000</div>
                </div>
                <p class="text-xs text-gray-500 mt-3">
                    Après déploiement, inspectez quelques URLs dans Search Console pour vérifier canonical, couverture et prise en compte des nouveaux schémas.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function showResult(html) {
    const zone = document.getElementById('results-zone');
    const content = document.getElementById('results-content');
    zone.classList.remove('hidden');
    content.innerHTML = html;
}

function verifierUrls() {
    const btn = document.getElementById('btn-verify');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin text-3xl mb-3 block"></i><span class="font-semibold text-lg block">Vérification...</span><span class="text-blue-100 text-sm">Inspection via Google</span>';

    showResult('<div class="rounded-2xl bg-blue-50 border border-blue-200 text-blue-800 px-5 py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Vérification de 50 URLs en cours...</div>');

    fetch('{{ route("admin.indexation.verify-urls") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ limit: 50 })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || 'Erreur inconnue');
        }

        const stats = data.stats || {};
        showResult(`
            <div class="rounded-2xl bg-green-50 border border-green-200 text-green-800 px-5 py-4">
                <div class="font-semibold mb-2"><i class="fas fa-check-circle mr-2"></i>Vérification terminée</div>
                <div class="text-sm space-y-1">
                    <div>Vérifiées: ${stats.verified || 0}</div>
                    <div>Indexées: ${stats.indexed || 0}</div>
                    <div>Non indexées: ${stats.not_indexed || 0}</div>
                    <div>Erreurs: ${stats.errors || 0}</div>
                    <div>Restantes: ${stats.remaining || 0}</div>
                </div>
            </div>
        `);
    })
    .catch(error => {
        showResult(`
            <div class="rounded-2xl bg-red-50 border border-red-200 text-red-800 px-5 py-4">
                <div class="font-semibold"><i class="fas fa-times-circle mr-2"></i>${error.message}</div>
                <div class="text-sm mt-2">Fallback CLI: <code>php artisan indexation:simple verify --limit=50</code></div>
            </div>
        `);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-search-plus text-3xl mb-3 block"></i><span class="font-semibold text-lg block">Vérifier 50 URLs</span><span class="text-blue-100 text-sm">Inspection via Google</span>';
    });
}

function indexerUrls() {
    if (!confirm('Indexer 150 URLs non indexées ?')) return;

    const btn = document.getElementById('btn-index');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin text-3xl mb-3 block"></i><span class="font-semibold text-lg block">Indexation...</span><span class="text-green-100 text-sm">Demande via API</span>';

    showResult('<div class="rounded-2xl bg-green-50 border border-green-200 text-green-800 px-5 py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Envoi des demandes d’indexation en cours...</div>');

    fetch('{{ route("admin.indexation.index-urls") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ limit: 150 })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || 'Erreur inconnue');
        }

        showResult(`
            <div class="rounded-2xl bg-green-50 border border-green-200 text-green-800 px-5 py-4">
                <div class="font-semibold mb-2"><i class="fas fa-check-circle mr-2"></i>Indexation terminée</div>
                <div class="text-sm space-y-1">
                    <div>Demandes envoyées: ${data.success_count || 0}</div>
                    <div>Échecs: ${data.failed_count || 0}</div>
                    <div>Total traité: ${data.total || 0}</div>
                </div>
            </div>
        `);
    })
    .catch(error => {
        showResult(`
            <div class="rounded-2xl bg-red-50 border border-red-200 text-red-800 px-5 py-4">
                <div class="font-semibold"><i class="fas fa-times-circle mr-2"></i>${error.message}</div>
                <div class="text-sm mt-2">Fallback CLI: <code>php artisan indexation:simple index --limit=150</code></div>
            </div>
        `);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-rocket text-3xl mb-3 block"></i><span class="font-semibold text-lg block">Indexer 150 URLs</span><span class="text-green-100 text-sm">Demande via API</span>';
    });
}

function regenererSitemap() {
    if (!confirm('Régénérer l’index sitemap et tous les sous-sitemaps ?')) return;

    const btn = document.getElementById('btn-sitemap');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin text-3xl mb-3 block"></i><span class="font-semibold text-lg block">Génération...</span><span class="text-violet-100 text-sm">Index + sous-sitemaps</span>';

    fetch('{{ route("admin.indexation.update-sitemap") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || 'Erreur inconnue');
        }
        window.location.reload();
    })
    .catch(error => {
        showResult(`
            <div class="rounded-2xl bg-red-50 border border-red-200 text-red-800 px-5 py-4">
                <div class="font-semibold"><i class="fas fa-times-circle mr-2"></i>${error.message}</div>
            </div>
        `);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}

function soumettreGoogle(filename, buttonEl) {
    if (!confirm(`Soumettre ${filename} à Google ?`)) return;

    const btn = buttonEl;
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Envoi...';

    fetch('{{ route("admin.indexation.submit-sitemap") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ filename })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || 'Erreur inconnue');
        }

        showResult(`
            <div class="rounded-2xl bg-green-50 border border-green-200 text-green-800 px-5 py-4">
                <div class="font-semibold"><i class="fas fa-check-circle mr-2"></i>${data.message}</div>
                <div class="text-sm mt-2">URLs envoyées: ${data.success_count || 0} • Échecs: ${data.failed_count || 0}</div>
            </div>
        `);
    })
    .catch(error => {
        showResult(`
            <div class="rounded-2xl bg-red-50 border border-red-200 text-red-800 px-5 py-4">
                <div class="font-semibold"><i class="fas fa-times-circle mr-2"></i>${error.message}</div>
            </div>
        `);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = original;
    });
}
</script>
@endsection
