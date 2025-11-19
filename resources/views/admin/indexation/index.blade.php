@extends('layouts.admin')

@section('title', 'Indexation Google')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
            <i class="fas fa-search mr-3"></i>Indexation Google
        </h1>
        <p class="text-gray-600">Vérifiez et indexez vos pages dans Google</p>
    </div>

    <!-- Messages -->
    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg shadow">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-3 text-2xl"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg shadow">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle mr-3 text-2xl"></i>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    </div>
    @endif

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- URLs Sitemap -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-medium text-gray-500 uppercase mb-1">URLs Sitemap</div>
                    <div class="text-3xl font-bold text-gray-800">{{ number_format($stats['total_sitemap'] ?? 0) }}</div>
                </div>
                <div class="text-blue-500">
                    <i class="fas fa-sitemap text-4xl opacity-20"></i>
                </div>
            </div>
        </div>

        <!-- Indexées -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-medium text-gray-500 uppercase mb-1">Indexées ✅</div>
                    <div class="text-3xl font-bold text-green-600">{{ number_format($stats['indexed'] ?? 0) }}</div>
                </div>
                <div class="text-green-500">
                    <i class="fas fa-check-circle text-4xl opacity-20"></i>
                </div>
            </div>
        </div>

        <!-- Non Indexées -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-yellow-500 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-medium text-gray-500 uppercase mb-1">Non Indexées ⚠️</div>
                    <div class="text-3xl font-bold text-yellow-600">{{ number_format($stats['not_indexed'] ?? 0) }}</div>
                </div>
                <div class="text-yellow-500">
                    <i class="fas fa-exclamation-triangle text-4xl opacity-20"></i>
                </div>
            </div>
        </div>

        <!-- Taux -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-medium text-gray-500 uppercase mb-1">Taux Indexation</div>
                    <div class="text-3xl font-bold text-purple-600">
                        {{ $stats['total_tracked'] > 0 ? round($stats['indexed'] / $stats['total_tracked'] * 100, 1) : 0 }}%
                    </div>
                </div>
                <div class="text-purple-500">
                    <i class="fas fa-chart-pie text-4xl opacity-20"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Rapides -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-4 -m-6 mb-6 rounded-t-xl">
            <h2 class="text-xl font-bold flex items-center">
                <i class="fas fa-bolt mr-3"></i>Actions Rapides
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center">
                <button onclick="verifierUrls()" 
                        class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-8 px-6 rounded-xl shadow-lg hover:shadow-2xl transform hover:scale-105 transition-all duration-200"
                        id="btn-verify">
                    <i class="fas fa-search-plus text-5xl mb-3 block"></i>
                    <span class="text-xl block">Vérifier 50 URLs</span>
                </button>
                <p class="text-gray-600 text-sm mt-3">
                    <i class="fas fa-info-circle mr-1"></i>Interroge Google (2-3 min)
                </p>
            </div>

            <div class="text-center">
                <button onclick="indexerUrls()" 
                        class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-8 px-6 rounded-xl shadow-lg hover:shadow-2xl transform hover:scale-105 transition-all duration-200"
                        id="btn-index">
                    <i class="fas fa-rocket text-5xl mb-3 block"></i>
                    <span class="text-xl block">Indexer 150 URLs</span>
                </button>
                <p class="text-gray-600 text-sm mt-3">
                    <i class="fas fa-paper-plane mr-1"></i>Envoie à Google API
                </p>
            </div>

            <div class="text-center">
                <button onclick="window.location.reload()" 
                        class="w-full bg-purple-500 hover:bg-purple-600 text-white font-bold py-8 px-6 rounded-xl shadow-lg hover:shadow-2xl transform hover:scale-105 transition-all duration-200">
                    <i class="fas fa-sync-alt text-5xl mb-3 block"></i>
                    <span class="text-xl block">Actualiser</span>
                </button>
                <p class="text-gray-600 text-sm mt-3">
                    <i class="fas fa-chart-line mr-1"></i>Recharge stats
                </p>
            </div>
        </div>

        <!-- Zone résultats -->
        <div id="results-zone" class="mt-6 hidden">
            <hr class="my-4 border-gray-300">
            <div id="results-content"></div>
        </div>
    </div>

    <!-- Liste des Sitemaps -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800">
                <i class="fas fa-sitemap mr-3"></i>Sitemaps du Site
            </h2>
            <button onclick="regenererSitemap()" 
                    class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-6 rounded-lg shadow hover:shadow-lg transition"
                    id="btn-sitemap">
                <i class="fas fa-sync mr-2"></i>Régénérer Tous
            </button>
        </div>

        <?php
        $sitemapFiles = glob(public_path('sitemap*.xml'));
        $sitemapFiles = array_filter($sitemapFiles, function($file) {
            return basename($file) !== 'sitemap_index.xml';
        });
        ?>

        @if(!empty($sitemapFiles))
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fichier</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">URLs</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Taille</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Modifié</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($sitemapFiles as $file)
                        <?php
                        $filename = basename($file);
                        $urlCount = 0;
                        try {
                            $xml = simplexml_load_file($file);
                            if ($xml && isset($xml->url)) {
                                $urlCount = count($xml->url);
                            }
                        } catch (\Exception $e) {
                            $urlCount = 0;
                        }
                        ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <i class="fas fa-file-code text-blue-500 mr-3"></i>
                                    <span class="text-sm font-medium text-gray-900">{{ $filename }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ number_format($urlCount) }} URLs
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                                {{ number_format(filesize($file) / 1024, 1) }} KB
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                                {{ date('d/m/Y H:i', filemtime($file)) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <a href="{{ url($filename) }}" target="_blank" 
                                   class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-lg mr-2 transition">
                                    <i class="fas fa-eye mr-2"></i>Voir
                                </a>
                                @if($isGoogleConfigured)
                                <button onclick="soumettreGoogle('{{ $filename }}')" 
                                        class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-lg transition">
                                    <i class="fas fa-upload mr-2"></i>Soumettre
                                </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-500 text-xl mr-3 mt-1"></i>
                    <div class="text-sm text-blue-800">
                        <p class="font-semibold mb-1">Info Sitemaps</p>
                        <p>Le sitemap principal est <code class="bg-blue-200 px-2 py-1 rounded">sitemap.xml</code>. 
                        Si vous avez beaucoup d'URLs (> 2000), plusieurs fichiers sont créés automatiquement.</p>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-6 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-yellow-500 text-3xl mr-4"></i>
                    <div>
                        <p class="text-yellow-800 font-semibold mb-1">Aucun sitemap généré</p>
                        <p class="text-yellow-700 text-sm">Cliquez sur "Régénérer Tous" pour créer le sitemap.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Configuration -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <div class="flex items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800">
                <i class="fas fa-cog mr-3"></i>Configuration Google Search Console
            </h2>
            @if($isGoogleConfigured)
                <span class="ml-auto px-4 py-2 bg-green-100 text-green-800 text-sm font-semibold rounded-full">
                    <i class="fas fa-check-circle mr-2"></i>Configuré ✅
                </span>
            @else
                <span class="ml-auto px-4 py-2 bg-red-100 text-red-800 text-sm font-semibold rounded-full">
                    <i class="fas fa-times-circle mr-2"></i>Non configuré ❌
                </span>
            @endif
        </div>

        <form method="POST" action="{{ route('admin.indexation.update') }}" class="space-y-6">
            @csrf
            
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">URL du site</label>
                <input type="url" name="site_url" value="{{ $siteUrl }}" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                <p class="mt-2 text-sm text-gray-600">
                    <i class="fas fa-lightbulb mr-1"></i>Ex: https://couvreur-chevigny-saint-sauveur.fr
                </p>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Credentials JSON Google Search Console</label>
                <textarea name="google_search_console_credentials" rows="8" 
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg font-mono text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                          placeholder='{"type": "service_account", "project_id": "...", ...}'>{{ $googleCredentials }}</textarea>
                <p class="mt-2 text-sm text-gray-600">
                    <i class="fas fa-key mr-1"></i>Collez le JSON de votre compte de service
                </p>
            </div>

            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
                <div>
                    <label class="text-sm font-bold text-gray-700">Indexation quotidienne automatique</label>
                    <p class="text-xs text-gray-600 mt-1">150 URLs indexées chaque jour à 02h00</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="daily_indexing_enabled" value="1" class="sr-only peer" 
                           {{ $dailyIndexingEnabled ? 'checked' : '' }}>
                    <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>

            <div class="pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg hover:shadow-xl transition transform hover:scale-105">
                    <i class="fas fa-save mr-2"></i>Sauvegarder Configuration
                </button>
            </div>
        </form>
    </div>

    <!-- Instructions CLI -->
    <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">
            <i class="fas fa-terminal mr-3 text-gray-600"></i>Alternative : CLI (100% fiable)
        </h2>
        <p class="text-gray-700 mb-4 font-medium">Si les boutons ne fonctionnent pas, utilisez ces commandes :</p>
        
        <div class="bg-gray-900 text-green-400 p-6 rounded-lg font-mono text-sm overflow-x-auto">
            <div class="mb-3"><span class="text-gray-500"># Voir statistiques</span><br><span class="text-white">php artisan indexation:simple stats</span></div>
            <div class="mb-3"><span class="text-gray-500"># Vérifier 100 URLs</span><br><span class="text-white">php artisan indexation:simple verify --limit=100</span></div>
            <div class="mb-3"><span class="text-gray-500"># Indexer 150 URLs non indexées</span><br><span class="text-white">php artisan indexation:simple index --limit=150</span></div>
            <div class="mb-3"><span class="text-gray-500"># Vérifier 1 URL spécifique</span><br><span class="text-white">php artisan indexation:simple verify --url="https://..."</span></div>
            <div><span class="text-gray-500"># Indexer 1 URL spécifique</span><br><span class="text-white">php artisan indexation:simple index --url="https://..."</span></div>
        </div>

        <div class="mt-4 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
            <div class="flex items-start">
                <i class="fas fa-book text-blue-500 text-xl mr-3 mt-1"></i>
                <div class="text-sm text-blue-800">
                    <p class="font-semibold">Guide complet disponible</p>
                    <p class="mt-1">Consultez <code class="bg-blue-200 px-2 py-1 rounded">INDEXATION_REFONTE_COMPLETE.md</code> pour toutes les instructions</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Fonction vérifier URLs
function verifierUrls() {
    const btn = document.getElementById('btn-verify');
    const resultsZone = document.getElementById('results-zone');
    const resultsContent = document.getElementById('results-content');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin text-4xl mb-3 block"></i><span class="text-xl">Vérification...</span>';
    
    resultsZone.classList.remove('hidden');
    resultsContent.innerHTML = '<div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg"><i class="fas fa-spinner fa-spin mr-2"></i>Vérification de 50 URLs en cours... Cela peut prendre 2-3 minutes.</div>';
    
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
        if (data.success) {
            const stats = data.stats || {};
            let html = '<div class="bg-green-50 border-l-4 border-green-500 p-6 rounded-lg">';
            html += '<h3 class="text-lg font-bold text-green-800 mb-3"><i class="fas fa-check-circle mr-2"></i>Vérification terminée !</h3>';
            html += `<p class="text-green-800 font-semibold mb-3">${stats.verified || 0} URLs vérifiées</p>`;
            html += '<ul class="space-y-2 text-green-700">';
            html += `<li><i class="fas fa-check text-green-600 mr-2"></i>Indexées : ${stats.indexed || 0}</li>`;
            html += `<li><i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>Non indexées : ${stats.not_indexed || 0}</li>`;
            html += `<li><i class="fas fa-times text-red-600 mr-2"></i>Erreurs : ${stats.errors || 0}</li>`;
            if (stats.remaining > 0) {
                html += `<li><i class="fas fa-redo text-blue-600 mr-2"></i>Restantes : ${stats.remaining} (cliquez à nouveau pour continuer)</li>`;
            }
            html += '</ul></div>';
            resultsContent.innerHTML = html;
        } else {
            resultsContent.innerHTML = `<div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg"><i class="fas fa-times mr-2"></i><span class="text-red-800 font-semibold">${data.message || 'Erreur'}</span></div>`;
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        resultsContent.innerHTML = `<div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
            <p class="text-red-800 font-semibold"><i class="fas fa-times mr-2"></i>Erreur réseau</p>
            <p class="text-red-700 text-sm mt-2">Utilisez CLI : <code class="bg-red-200 px-2 py-1 rounded">php artisan indexation:simple verify --limit=50</code></p>
        </div>`;
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-search-plus text-5xl mb-3 block"></i><span class="text-xl block">Vérifier 50 URLs</span>';
    });
}

// Fonction indexer URLs
function indexerUrls() {
    if (!confirm('Indexer 150 URLs non indexées ?\n\nCela peut prendre 1-2 minutes.')) return;
    
    const btn = document.getElementById('btn-index');
    const resultsZone = document.getElementById('results-zone');
    const resultsContent = document.getElementById('results-content');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin text-4xl mb-3 block"></i><span class="text-xl">Indexation...</span>';
    
    resultsZone.classList.remove('hidden');
    resultsContent.innerHTML = '<div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg"><i class="fas fa-spinner fa-spin mr-2"></i>Indexation en cours...</div>';
    
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
        if (data.success) {
            let html = '<div class="bg-green-50 border-l-4 border-green-500 p-6 rounded-lg">';
            html += '<h3 class="text-lg font-bold text-green-800 mb-3"><i class="fas fa-check-circle mr-2"></i>Indexation terminée !</h3>';
            html += `<p class="text-green-800 font-semibold mb-2">${data.success_count || 0} URLs envoyées à Google</p>`;
            if (data.failed_count > 0) {
                html += `<p class="text-yellow-700"><i class="fas fa-exclamation-triangle mr-2"></i>${data.failed_count} URLs échouées</p>`;
            }
            html += '<p class="text-green-700 text-sm mt-3"><i class="fas fa-clock mr-2"></i>Les pages seront indexées dans 3-7 jours.</p>';
            html += '</div>';
            resultsContent.innerHTML = html;
        } else {
            resultsContent.innerHTML = `<div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg"><i class="fas fa-times mr-2"></i><span class="text-red-800 font-semibold">${data.message || 'Erreur'}</span></div>`;
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        resultsContent.innerHTML = `<div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
            <p class="text-red-800 font-semibold"><i class="fas fa-times mr-2"></i>Erreur réseau</p>
            <p class="text-red-700 text-sm mt-2">Utilisez CLI : <code class="bg-red-200 px-2 py-1 rounded">php artisan indexation:simple index --limit=150</code></p>
        </div>`;
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-rocket text-5xl mb-3 block"></i><span class="text-xl block">Indexer 150 URLs</span>';
    });
}

// Fonction régénérer sitemap
function regenererSitemap() {
    if (!confirm('Régénérer le sitemap ?')) return;
    
    const btn = document.getElementById('btn-sitemap');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Génération...';
    
    fetch('{{ route("admin.indexation.update-sitemap") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Sitemap régénéré avec succès !');
            window.location.reload();
        } else {
            alert('❌ Erreur : ' + (data.message || 'Erreur inconnue'));
        }
    })
    .catch(error => {
        alert('❌ Erreur : ' + error.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}

// Fonction soumettre sitemap
function soumettreGoogle(filename) {
    if (!confirm(`Soumettre "${filename}" à Google ?\n\nIndexera jusqu'à 200 URLs.\nDurée : 1-2 minutes.`)) return;
    
    const btn = event.target;
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Envoi...';
    
    fetch('{{ route("admin.indexation.submit-sitemap") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ filename: filename })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`✅ Sitemap soumis !\n\n${data.success_count || 0} URLs envoyées à Google`);
            window.location.reload();
        } else {
            alert('❌ Erreur : ' + (data.message || 'Erreur inconnue'));
        }
    })
    .catch(error => {
        alert('❌ Erreur : ' + error.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}
</script>
@endsection
