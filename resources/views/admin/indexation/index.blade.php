@extends('layouts.admin')

@section('title', 'Gestion de l\'Indexation')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">🗺️ Gestion du Sitemap & Indexation</h1>
    </div>
    
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ session('error') }}
    </div>
    @endif

    <!-- Sitemap XML - Section principale -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-lg font-semibold">🗺️ Sitemap XML</h2>
                <p class="text-sm text-gray-600 mt-1">Génération automatique du sitemap pour les moteurs de recherche</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ url('/sitemap.xml') }}" target="_blank" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-external-link-alt mr-2"></i>Voir le sitemap
                </a>
                <button type="button" onclick="updateSitemap()" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-sync-alt mr-2"></i>Régénérer
                </button>
            </div>
        </div>
        
        <!-- Statistiques sitemap -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="text-2xl font-bold text-blue-600">{{ $totalUrlsInSitemap ?? 0 }}</div>
                <div class="text-sm text-gray-600">URLs dans le sitemap</div>
            </div>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="text-2xl font-bold text-green-600">{{ count($sitemapInfo ?? []) }}</div>
                <div class="text-sm text-gray-600">Fichiers sitemap</div>
            </div>
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <div class="text-2xl font-bold text-purple-600">
                    @if(!empty($sitemapInfo))
                        {{ date('d/m/Y H:i', max(array_column($sitemapInfo, 'last_modified'))) }}
                    @else
                        Jamais
                    @endif
                </div>
                <div class="text-sm text-gray-600">Dernière génération</div>
            </div>
        </div>

        <!-- Liste des sitemaps -->
        @if(!empty($sitemapInfo))
        <div class="space-y-2">
            @foreach($sitemapInfo as $sitemap)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex-1">
                    <p class="font-medium">{{ $sitemap['filename'] }}</p>
                    <p class="text-sm text-gray-600">
                        {{ number_format($sitemap['size'] / 1024, 2) }} KB - 
                        <span class="font-semibold text-blue-600">{{ $sitemap['urls_count'] ?? 0 }} URLs</span> - 
                        Modifié le {{ date('d/m/Y H:i', $sitemap['last_modified']) }}
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ $sitemap['url'] }}" target="_blank" 
                       class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-external-link-alt mr-2"></i>Voir
                    </a>
                    @if($isGoogleConfigured)
                    <button type="button" 
                            onclick="submitSitemapToGoogle('{{ $sitemap['filename'] }}')" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-paper-plane mr-2"></i>Envoyer à Google
                    </button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-8 bg-gray-50 rounded-lg">
            <p class="text-gray-500 mb-4">Aucun sitemap généré</p>
            <button type="button" onclick="updateSitemap()" 
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition">
                <i class="fas fa-plus-circle mr-2"></i>Générer le sitemap
            </button>
        </div>
        @endif

        <!-- Automatisation -->
        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-semibold text-blue-800 mb-1">
                        <i class="fas fa-clock mr-1"></i>Génération automatique
                    </h4>
                    <p class="text-xs text-blue-600">
                        Le sitemap est régénéré automatiquement chaque jour à 2h du matin
                    </p>
                </div>
                <span class="text-sm font-medium text-green-600">
                    <i class="fas fa-check-circle mr-1"></i>Activée
                </span>
            </div>
        </div>
    </div>

    <!-- Google Search Console -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">🔐 Google Search Console</h2>
        <p class="text-sm text-gray-600 mb-4">Configuration de l'API Google pour l'indexation automatique</p>
        
        <form action="{{ route('admin.indexation.update') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label for="site_url" class="block text-sm font-medium mb-2">URL du site</label>
                <input type="url" id="site_url" name="site_url" 
                       value="{{ setting('site_url', request()->getSchemeAndHttpHost()) }}"
                       placeholder="https://votre-site.com"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <p class="text-xs text-gray-500 mt-1">URL de base de votre site (sans slash final)</p>
            </div>
            
            <div class="mb-4">
                <label for="google_search_console_credentials" class="block text-sm font-medium mb-2">
                    Credentials JSON Google Search Console
                </label>
                <textarea id="google_search_console_credentials" name="google_search_console_credentials" 
                          rows="8"
                          placeholder='{"type": "service_account", "project_id": "...", ...}'
                          class="w-full px-3 py-2 border border-gray-300 rounded-md font-mono text-xs">{{ !empty($googleCredentialsArray) ? json_encode($googleCredentialsArray, JSON_PRETTY_PRINT) : '' }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Collez ici le JSON de votre compte de service Google</p>
            </div>
            
            <div class="flex items-center space-x-4 mb-4">
                <button type="button" onclick="testGoogleConnection()" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-plug mr-2"></i>Tester la connexion
                </button>
                @if($isGoogleConfigured)
                <span class="text-sm text-green-600">
                    <i class="fas fa-check-circle mr-1"></i>API configurée
                </span>
                @else
                <span class="text-sm text-gray-500">
                    <i class="fas fa-exclamation-circle mr-1"></i>API non configurée
                </span>
                @endif
            </div>

            <!-- Indexation quotidienne automatique -->
            @if($isGoogleConfigured)
            <div class="border-t pt-4 mt-4">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-md font-semibold text-gray-800">🔄 Indexation Quotidienne Automatique</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Indexe automatiquement 150 URLs par jour pour respecter le quota Google
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            @if($dailyIndexingEnabled)
                                <span class="text-green-600">✅ Activée</span> - 
                            @else
                                <span class="text-gray-500">⏸️ Désactivée</span> - 
                            @endif
                            {{ $indexedCount ?? 0 }} liens déjà indexés sur {{ $totalUrlsInSitemap ?? 0 }} total
                        </p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button type="button" 
                                onclick="toggleDailyIndexing({{ $dailyIndexingEnabled ? 'false' : 'true' }})" 
                                class="text-white px-4 py-2 rounded-lg text-sm font-medium transition {{ $dailyIndexingEnabled ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-green-600 hover:bg-green-700' }}">
                            <i class="fas fa-{{ $dailyIndexingEnabled ? 'pause' : 'play' }} mr-2"></i>
                            {{ $dailyIndexingEnabled ? 'Désactiver' : 'Activer' }}
                        </button>
                        <button type="button" 
                                onclick="runDailyIndexing()" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                            <i class="fas fa-play-circle mr-2"></i>Exécuter maintenant
                        </button>
                    </div>
                </div>

                <!-- Statistiques -->
                @if(!empty($dailyStats))
                <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">📊 Statistiques des 7 derniers jours</h4>
                    <div class="space-y-2 max-h-40 overflow-y-auto">
                        @foreach(array_reverse(array_slice($dailyStats, -7, 7, true)) as $date => $stat)
                        <div class="flex items-center justify-between text-xs bg-white p-2 rounded">
                            <span class="font-medium">{{ date('d/m/Y', strtotime($date)) }}</span>
                            <div class="flex items-center space-x-2">
                                <span class="text-green-600 font-medium">{{ $stat['success'] ?? 0 }} réussies</span>
                                @if(($stat['failed'] ?? 0) > 0)
                                <span class="text-red-600 font-medium">{{ $stat['failed'] ?? 0 }} échouées</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endif

            <div class="mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg">
                    <i class="fas fa-save mr-2"></i>Sauvegarder la configuration
                </button>
            </div>
        </form>
    </div>

    <!-- Vérification des pages indexées -->
    @if($isGoogleConfigured)
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-lg font-semibold">📊 Vérification des Pages Indexées</h2>
                <p class="text-sm text-gray-600 mt-1">Vérifiez le statut d'indexation de vos pages via Google Search Console</p>
            </div>
            <button type="button" onclick="verifyAllStatuses()" 
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                <i class="fas fa-search mr-2"></i>Vérifier les statuts
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="text-2xl font-bold text-blue-600">{{ $indexationStats['total'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">URLs suivies</div>
            </div>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="text-2xl font-bold text-green-600">{{ $indexationStats['indexed'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">Indexées ✅</div>
            </div>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="text-2xl font-bold text-yellow-600">{{ $indexationStats['not_indexed'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">Non indexées ⚠️</div>
            </div>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="text-2xl font-bold text-gray-600">{{ $indexationStats['never_verified'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">Jamais vérifiées</div>
            </div>
        </div>

        <div class="mt-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-md font-semibold">Derniers statuts vérifiés</h3>
                <button type="button" onclick="loadStatuses()" 
                        class="text-sm text-blue-600 hover:text-blue-800">
                    <i class="fas fa-sync-alt mr-1"></i>Actualiser
                </button>
            </div>
            <div id="statuses-container" class="space-y-2">
                <div class="text-center text-gray-500 py-4">
                    <i class="fas fa-spinner fa-spin mr-2"></i>Chargement des statuts...
                </div>
            </div>
        </div>

        <!-- Test d'une URL -->
        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <h4 class="text-sm font-semibold text-blue-800 mb-2">
                <i class="fas fa-flask mr-1"></i>Vérifier une URL spécifique
            </h4>
            <div class="flex items-center space-x-2">
                <input type="url" 
                       id="verify-url-input" 
                       placeholder="https://votredomaine.com/page"
                       value="{{ rtrim(setting('site_url', request()->getSchemeAndHttpHost()), '/') }}/"
                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm">
                <button type="button" 
                        onclick="verifySingleUrl()" 
                        id="verify-url-btn"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-search mr-2"></i>Vérifier
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Robots.txt -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">🤖 Robots.txt</h2>
        <p class="text-sm text-gray-600 mb-4">Fichier de configuration pour les robots des moteurs de recherche</p>
        
        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg mb-4">
            <div>
                <p class="font-medium">Fichier robots.txt</p>
                <p class="text-sm text-gray-600">Configuration automatique des robots</p>
            </div>
            <a href="{{ url('/robots.txt') }}" target="_blank" 
               class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                <i class="fas fa-external-link-alt mr-2"></i>Voir robots.txt
            </a>
        </div>
        
        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <h4 class="font-medium text-yellow-800 mb-2">💡 Instructions pour Google Search Console</h4>
            <ol class="text-sm text-yellow-700 space-y-1 list-decimal list-inside">
                <li>Connectez-vous à <a href="https://search.google.com/search-console" target="_blank" class="text-blue-600 hover:underline">Google Search Console</a></li>
                <li>Sélectionnez votre propriété</li>
                <li>Allez dans "Sitemaps" et ajoutez : <code class="bg-yellow-100 px-1 rounded">{{ url('/sitemap.xml') }}</code></li>
                <li>Vérifiez que votre site respecte le fichier robots.txt</li>
            </ol>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white font-medium z-50 ${
        type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

function updateSitemap() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Génération...';
    button.disabled = true;
    button.classList.add('opacity-75');
    
    fetch('{{ route("admin.indexation.update-sitemap") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('✅ Sitemap régénéré avec succès !', 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showNotification('❌ Erreur: ' + (data.message || 'Erreur inconnue'), 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('❌ Erreur lors de la génération', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
        button.classList.remove('opacity-75');
    });
}

function testGoogleConnection() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Test...';
    button.disabled = true;
    
    fetch('{{ route("admin.indexation.test-google") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('✅ Connexion réussie !', 'success');
        } else {
            showNotification('❌ Erreur: ' + (data.message || 'Erreur inconnue'), 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('❌ Erreur lors du test', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function submitSitemapToGoogle(filename) {
    if (!confirm(`Envoyer le sitemap "${filename}" à Google ?`)) {
        return;
    }
    
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Envoi...';
    button.disabled = true;
    
    fetch('{{ route("admin.indexation.submit-sitemap-to-google") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ filename: filename })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`✅ ${data.success_count} URLs envoyées${data.failed_count > 0 ? `, ${data.failed_count} échouées` : ''}`, 'success');
            setTimeout(() => window.location.reload(), 2000);
        } else {
            showNotification('❌ Erreur: ' + (data.message || 'Erreur inconnue'), 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('❌ Erreur lors de l\'envoi', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function toggleDailyIndexing(enabled) {
    if (!confirm(enabled ? 'Activer l\'indexation quotidienne ?' : 'Désactiver l\'indexation quotidienne ?')) {
        return;
    }
    
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>...';
    button.disabled = true;
    
    fetch('{{ route("admin.indexation.toggle-daily-indexing") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ enabled: enabled })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification('❌ Erreur: ' + (data.message || 'Erreur inconnue'), 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('❌ Erreur', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function runDailyIndexing() {
    if (!confirm('Exécuter l\'indexation quotidienne maintenant ? (150 URLs maximum)')) {
        return;
    }
    
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Exécution...';
    button.disabled = true;
    
    fetch('{{ route("admin.indexation.run-daily-indexing") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let message = data.message || 'Indexation exécutée !';
            if (data.success_count > 0) {
                message += ` (${data.success_count} URLs)`;
            }
            showNotification(message, 'success');
            setTimeout(() => window.location.reload(), 2000);
        } else {
            showNotification('❌ Erreur: ' + (data.message || 'Erreur inconnue'), 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('❌ Erreur lors de l\'exécution', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// Fonctions pour la vérification des statuts
function loadStatuses() {
    const container = document.getElementById('statuses-container');
    if (!container) return;
    
    container.innerHTML = '<div class="text-center text-gray-500 py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Chargement...</div>';
    
    fetch('{{ route("admin.indexation.statuses") }}?per_page=10')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayStatuses(data.statuses.data);
            } else {
                container.innerHTML = '<div class="text-red-500">Erreur: ' + (data.error || 'Erreur inconnue') + '</div>';
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            container.innerHTML = '<div class="text-red-500">Erreur lors du chargement</div>';
        });
}

function displayStatuses(statuses) {
    const container = document.getElementById('statuses-container');
    if (!container) return;
    
    if (statuses.length === 0) {
        container.innerHTML = '<div class="text-center text-gray-500 py-4">Aucun statut vérifié pour le moment</div>';
        return;
    }
    
    container.innerHTML = '';
    
    statuses.forEach(status => {
        const div = document.createElement('div');
        div.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition';
        
        const indexedBadge = status.indexed 
            ? '<span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium">✅ Indexée</span>'
            : '<span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-medium">⚠️ Non indexée</span>';
        
        const lastVerification = status.last_verification_time 
            ? new Date(status.last_verification_time).toLocaleString('fr-FR')
            : 'Jamais vérifiée';
        
        div.innerHTML = `
            <div class="flex items-center flex-1">
                <div class="flex-1">
                    <a href="${status.url}" target="_blank" class="text-blue-600 hover:underline font-medium">
                        ${status.url}
                    </a>
                    <div class="text-xs text-gray-500 mt-1">
                        ${indexedBadge}
                        <span class="ml-2">Dernière vérification: ${lastVerification}</span>
                    </div>
                </div>
                <button type="button" onclick="verifySingleStatus('${status.url}')" 
                        class="ml-2 text-blue-600 hover:text-blue-800 text-sm">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        `;
        container.appendChild(div);
    });
}

function verifySingleStatus(url) {
    const button = event?.target?.closest('button') || event.target;
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;
    
    fetch('{{ route("admin.indexation.verify-status") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ url: url })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(
                data.indexed ? '✅ URL indexée' : '⚠️ URL non indexée',
                data.indexed ? 'success' : 'warning'
            );
            loadStatuses();
        } else {
            showNotification('❌ Erreur: ' + (data.error || 'Erreur inconnue'), 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('❌ Erreur lors de la vérification', 'error');
    })
    .finally(() => {
        button.innerHTML = originalHTML;
        button.disabled = false;
    });
}

function verifySingleUrl() {
    const urlInput = document.getElementById('verify-url-input');
    const url = urlInput.value.trim();
    
    if (!url) {
        showNotification('Veuillez entrer une URL à vérifier', 'error');
        urlInput.focus();
        return;
    }
    
    const button = document.getElementById('verify-url-btn');
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Vérification...';
    button.disabled = true;
    
    fetch('{{ route("admin.indexation.verify-status") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ url: url })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(
                data.indexed ? '✅ URL indexée' : '⚠️ URL non indexée',
                data.indexed ? 'success' : 'warning'
            );
            loadStatuses();
        } else {
            showNotification('❌ Erreur: ' + (data.error || 'Erreur inconnue'), 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('❌ Erreur lors de la vérification', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function verifyAllStatuses() {
    if (!confirm('Vérifier le statut de toutes les URLs récentes ? Cela peut prendre quelques minutes.')) {
        return;
    }
    
    showNotification('Vérification en cours... Cela peut prendre quelques minutes.', 'info');
    
    fetch('{{ route("admin.indexation.statuses") }}?per_page=50')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.statuses.data.length > 0) {
                const urls = data.statuses.data
                    .filter(s => !s.indexed || !s.last_verification_time || 
                        new Date(s.last_verification_time) < new Date(Date.now() - 24*60*60*1000))
                    .map(s => s.url)
                    .slice(0, 20);
                
                if (urls.length === 0) {
                    showNotification('Aucune URL à vérifier', 'info');
                    return;
                }
                
                verifyMultipleStatuses(urls);
            } else {
                showNotification('Aucune URL trouvée', 'info');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showNotification('Erreur lors de la récupération des URLs', 'error');
        });
}

function verifyMultipleStatuses(urls) {
    let completed = 0;
    let indexed = 0;
    let notIndexed = 0;
    
    urls.forEach((url, index) => {
        setTimeout(() => {
            fetch('{{ route("admin.indexation.verify-status") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ url: url })
            })
            .then(response => response.json())
            .then(data => {
                completed++;
                if (data.success) {
                    if (data.indexed) {
                        indexed++;
                    } else {
                        notIndexed++;
                    }
                }
                
                if (completed === urls.length) {
                    showNotification(
                        `Vérification terminée: ${indexed} indexées, ${notIndexed} non indexées`,
                        'success'
                    );
                    loadStatuses();
                }
            })
            .catch(error => {
                completed++;
                if (completed === urls.length) {
                    showNotification('Vérification terminée avec des erreurs', 'warning');
                    loadStatuses();
                }
            });
        }, index * 500);
    });
}

// Charger les statuts au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('statuses-container')) {
        loadStatuses();
    }
});
</script>
@endpush
