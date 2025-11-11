@extends('layouts.admin')

@section('title', 'Gestion de l\'Indexation')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">🔍 Gestion de l'Indexation</h1>
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

    @if($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.indexation.update') }}" method="POST">
        @csrf
        
        <!-- Robots Meta Tags -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">🤖 Robots Meta Tags</h2>
            <p class="text-sm text-gray-600 mb-4">Contrôlez comment les moteurs de recherche indexent votre site</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center">
                    <input type="checkbox" name="robots_index" id="robots_index" value="1" 
                           {{ ($indexationConfig['robots_index'] ?? true) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="robots_index" class="ml-2 text-sm font-medium text-gray-700">
                        Indexer les pages (index)
                    </label>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="robots_follow" id="robots_follow" value="1" 
                           {{ ($indexationConfig['robots_follow'] ?? true) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="robots_follow" class="ml-2 text-sm font-medium text-gray-700">
                        Suivre les liens (follow)
                    </label>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="robots_archive" id="robots_archive" value="1" 
                           {{ ($indexationConfig['robots_archive'] ?? true) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="robots_archive" class="ml-2 text-sm font-medium text-gray-700">
                        Archiver les pages (archive)
                    </label>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="robots_snippet" id="robots_snippet" value="1" 
                           {{ ($indexationConfig['robots_snippet'] ?? true) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="robots_snippet" class="ml-2 text-sm font-medium text-gray-700">
                        Afficher les extraits (snippet)
                    </label>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="robots_imageindex" id="robots_imageindex" value="1" 
                           {{ ($indexationConfig['robots_imageindex'] ?? true) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="robots_imageindex" class="ml-2 text-sm font-medium text-gray-700">
                        Indexer les images (imageindex)
                    </label>
                </div>
            </div>
        </div>

        <!-- Sitemap XML -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">🗺️ Sitemap XML</h2>
            <p class="text-sm text-gray-600 mb-4">Configurez le sitemap pour aider les moteurs de recherche à explorer votre site</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="sitemap_enabled" value="1" 
                               {{ ($indexationConfig['sitemap_enabled'] ?? true) ? 'checked' : '' }}
                               class="mr-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <span class="text-sm font-medium text-gray-700">Activer le sitemap XML</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1">Le sitemap sera accessible à /sitemap.xml</p>
                </div>
                
                <div class="mb-4">
                    <label for="sitemap_priority" class="block text-sm font-medium mb-2">Priorité par défaut</label>
                    <select name="sitemap_priority" id="sitemap_priority" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="0.1" {{ ($indexationConfig['sitemap_priority'] ?? 0.8) == 0.1 ? 'selected' : '' }}>0.1 (Très faible)</option>
                        <option value="0.3" {{ ($indexationConfig['sitemap_priority'] ?? 0.8) == 0.3 ? 'selected' : '' }}>0.3 (Faible)</option>
                        <option value="0.5" {{ ($indexationConfig['sitemap_priority'] ?? 0.8) == 0.5 ? 'selected' : '' }}>0.5 (Moyenne)</option>
                        <option value="0.7" {{ ($indexationConfig['sitemap_priority'] ?? 0.8) == 0.7 ? 'selected' : '' }}>0.7 (Élevée)</option>
                        <option value="0.8" {{ ($indexationConfig['sitemap_priority'] ?? 0.8) == 0.8 ? 'selected' : '' }}>0.8 (Très élevée)</option>
                        <option value="1.0" {{ ($indexationConfig['sitemap_priority'] ?? 0.8) == 1.0 ? 'selected' : '' }}>1.0 (Maximum)</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="sitemap_changefreq" class="block text-sm font-medium mb-2">Fréquence de mise à jour</label>
                    <select name="sitemap_changefreq" id="sitemap_changefreq" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="always" {{ ($indexationConfig['sitemap_changefreq'] ?? 'weekly') == 'always' ? 'selected' : '' }}>Toujours</option>
                        <option value="hourly" {{ ($indexationConfig['sitemap_changefreq'] ?? 'weekly') == 'hourly' ? 'selected' : '' }}>Horaire</option>
                        <option value="daily" {{ ($indexationConfig['sitemap_changefreq'] ?? 'weekly') == 'daily' ? 'selected' : '' }}>Quotidienne</option>
                        <option value="weekly" {{ ($indexationConfig['sitemap_changefreq'] ?? 'weekly') == 'weekly' ? 'selected' : '' }}>Hebdomadaire</option>
                        <option value="monthly" {{ ($indexationConfig['sitemap_changefreq'] ?? 'weekly') == 'monthly' ? 'selected' : '' }}>Mensuelle</option>
                        <option value="yearly" {{ ($indexationConfig['sitemap_changefreq'] ?? 'weekly') == 'yearly' ? 'selected' : '' }}>Annuelle</option>
                        <option value="never" {{ ($indexationConfig['sitemap_changefreq'] ?? 'weekly') == 'never' ? 'selected' : '' }}>Jamais</option>
                    </select>
                </div>
            </div>
            
            <div class="flex items-center space-x-4 flex-wrap">
                <a href="{{ url('/sitemap.xml') }}" target="_blank" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-external-link-alt mr-2"></i>Voir le sitemap
                </a>
                
                <button type="button" onclick="updateSitemap()" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-sync-alt mr-2"></i>Mettre à jour le sitemap
                </button>
                
                <span class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Le sitemap est actuellement 
                    <span class="font-medium {{ ($indexationConfig['sitemap_enabled'] ?? true) ? 'text-green-600' : 'text-red-600' }}">
                        {{ ($indexationConfig['sitemap_enabled'] ?? true) ? 'activé' : 'désactivé' }}
                    </span>
                </span>
            </div>
        </div>

        <!-- Google Search Console API -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">🔐 Google Search Console API</h2>
            <p class="text-sm text-gray-600 mb-4">Configurez l'API Google Search Console pour indexer automatiquement vos pages</p>
            
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
                          rows="10"
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

            <!-- Test d'indexation d'une seule URL -->
            @if($isGoogleConfigured)
            <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h4 class="text-sm font-semibold text-blue-800 mb-2">
                    <i class="fas fa-flask mr-1"></i>Test d'indexation d'une URL
                </h4>
                <p class="text-xs text-blue-600 mb-3">
                    Testez l'indexation d'une seule URL pour vérifier que tout fonctionne correctement
                </p>
                <div class="flex items-center space-x-2">
                    <input type="url" 
                           id="test-url-input" 
                           placeholder="https://normesrenovationbretagne.fr/exemple"
                           value="{{ rtrim(setting('site_url', request()->getSchemeAndHttpHost()), '/') }}/"
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm">
                    <button type="button" 
                            onclick="testSingleUrl()" 
                            id="test-single-url-btn"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-paper-plane mr-2"></i>Tester
                    </button>
                </div>
            </div>
            @endif
            
            <!-- Aide Favicon Google -->
            <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h4 class="text-sm font-semibold text-blue-800 mb-2">
                    <i class="fas fa-image mr-1"></i>ℹ️ Favicon dans Google Search
                </h4>
                <div class="text-xs text-blue-700 space-y-2">
                    <p><strong>Pourquoi mon favicon n'apparaît pas dans Google ?</strong></p>
                    <ul class="list-disc list-inside space-y-1 ml-2">
                        <li><strong>Délai d'indexation</strong> : Google peut prendre plusieurs jours/semaines pour afficher votre favicon après modification</li>
                        <li><strong>Taille minimale</strong> : Le favicon doit faire au minimum 48x48 pixels (recommandé : 192x192px)</li>
                        <li><strong>Format</strong> : Formats acceptés : PNG, SVG, GIF, ICO</li>
                        <li><strong>Accessibilité</strong> : Le favicon doit être accessible publiquement (pas bloqué par robots.txt)</li>
                        <li><strong>Manifest.json</strong> : Le fichier manifest.json aide Google à trouver vos icônes</li>
                    </ul>
                    <p class="mt-2"><strong>💡 Astuce</strong> : Utilisez l'outil d'inspection d'URL de Google Search Console pour forcer le recrawl de votre page d'accueil.</p>
                </div>
            </div>

            <!-- Aide et diagnostic -->
            <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <h4 class="text-sm font-semibold text-yellow-800 mb-2">
                    <i class="fas fa-info-circle mr-1"></i>💡 Aide : Résolution des problèmes d'indexation
                </h4>
                <div class="text-xs text-yellow-700 space-y-2">
                    <p><strong>Si toutes les URLs échouent, vérifiez :</strong></p>
                    <ol class="list-decimal list-inside space-y-1 ml-2">
                        <li><strong>Le compte de service est propriétaire du site</strong> : Dans Google Search Console, allez dans Paramètres → Utilisateurs et propriétaires, et ajoutez l'email du compte de service (ex: searchconsole-service@search-api-477513.iam.gserviceaccount.com) comme propriétaire.</li>
                        <li><strong>L'URL du site correspond</strong> : L'URL configurée doit correspondre exactement à celle dans Google Search Console (avec ou sans www, avec https://).</li>
                        <li><strong>L'API Indexing est activée</strong> : Dans Google Cloud Console, activez l'API "Indexing API" pour votre projet.</li>
                        <li><strong>Les credentials JSON sont valides</strong> : Vérifiez que le JSON du compte de service est complet et correct.</li>
                        <li><strong>Les URLs appartiennent au domaine</strong> : Toutes les URLs doivent appartenir au domaine configuré.</li>
                    </ol>
                    <p class="mt-2"><strong>Consultez les logs Laravel</strong> pour voir les détails des erreurs spécifiques.</p>
                </div>
            </div>
            
            <!-- Envoyer tous les liens à Google -->
            @if($isGoogleConfigured)
            <div class="border-t pt-4 mt-4">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-md font-semibold text-gray-800">📤 Envoyer tous les liens à Google</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            <span id="total-urls-count">{{ $totalUrlsInSitemap }}</span> URLs disponibles dans le sitemap
                        </p>
                    </div>
                    <button type="button" onclick="submitAllUrlsToGoogle()" id="submit-all-btn"
                            class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg text-sm font-medium transition shadow-lg">
                        <i class="fas fa-paper-plane mr-2"></i>Envoyer tous les liens à Google
                    </button>
                </div>
                
                <!-- Historique des envois -->
                @if(!empty($submissionHistory))
                <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">📊 Historique des envois</h4>
                    <div class="space-y-2 max-h-40 overflow-y-auto">
                        @foreach(array_reverse(array_slice($submissionHistory, -10)) as $submission)
                        <div class="flex items-center justify-between text-xs bg-white p-2 rounded">
                            <div>
                                <span class="font-medium">{{ date('d/m/Y H:i', strtotime($submission['date'])) }}</span>
                                <span class="text-gray-600 ml-2">{{ $submission['total'] }} URLs envoyées</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-green-600 font-medium">{{ $submission['success'] }} réussies</span>
                                @if($submission['failed'] > 0)
                                <span class="text-red-600 font-medium">{{ $submission['failed'] }} échouées</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endif

            <!-- Indexation quotidienne automatique -->
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
                            {{ $indexedCount }} liens déjà indexés sur {{ $totalUrlsInSitemap }} total (150 liens/jour)
                        </p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button type="button" 
                                onclick="toggleDailyIndexing({{ $dailyIndexingEnabled ? 'false' : 'true' }})" 
                                id="toggle-daily-btn"
                                class="text-white px-4 py-2 rounded-lg text-sm font-medium transition {{ $dailyIndexingEnabled ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-green-600 hover:bg-green-700' }}">
                            <i class="fas fa-{{ $dailyIndexingEnabled ? 'pause' : 'play' }} mr-2"></i>
                            {{ $dailyIndexingEnabled ? 'Désactiver' : 'Activer' }}
                        </button>
                        <button type="button" 
                                onclick="runDailyIndexing()" 
                                id="run-daily-btn"
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
                            <div>
                                <span class="font-medium">{{ date('d/m/Y', strtotime($date)) }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-green-600 font-medium">{{ $stat['success'] ?? 0 }} réussies</span>
                                @if(($stat['failed'] ?? 0) > 0)
                                <span class="text-red-600 font-medium">{{ $stat['failed'] ?? 0 }} échouées</span>
                                @endif
                                <span class="text-gray-500">({{ $stat['total'] ?? 0 }} traitées)</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Actions -->
                <div class="mt-4 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <button type="button" 
                                onclick="resetIndexedUrls()" 
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                            <i class="fas fa-redo mr-2"></i>Réinitialiser la liste des URLs indexées
                        </button>
                        <p class="text-xs text-gray-500">
                            💡 La tâche s'exécute automatiquement chaque jour à 2h du matin si activée (150 liens/jour)
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sitemaps générés -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">📋 Sitemaps générés</h2>
            <p class="text-sm text-gray-600 mb-4">Liste de tous les sitemaps créés</p>
            
            @if(!empty($sitemapInfo))
            <div class="space-y-2">
                @foreach($sitemapInfo as $sitemap)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex-1">
                        <p class="font-medium">{{ $sitemap['filename'] }}</p>
                        <p class="text-sm text-gray-600">
                            {{ number_format($sitemap['size'] / 1024, 2) }} KB - 
                            @if(isset($sitemap['urls_count']))
                                <span class="font-semibold text-blue-600">{{ $sitemap['urls_count'] }} URLs</span> - 
                            @endif
                            Modifié le {{ date('d/m/Y H:i', $sitemap['last_modified']) }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Maximum 2000 URLs par sitemap (limite Google)
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
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition"
                                id="submit-sitemap-{{ $loop->index }}">
                            <i class="fas fa-paper-plane mr-2"></i>Envoyer les liens à Google
                        </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-500">Aucun sitemap généré pour le moment</p>
            @endif
        </div>

        <!-- Statuts d'indexation réels -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h2 class="text-lg font-semibold">📊 Statuts d'Indexation Réels</h2>
                    <p class="text-sm text-gray-600">Vérification réelle via l'API URL Inspection de Google</p>
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
        </div>

        <!-- Liste des URLs -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h2 class="text-lg font-semibold">🔗 Toutes les URLs du sitemap</h2>
                    <p class="text-sm text-gray-600">Liste complète des URLs indexées dans vos sitemaps</p>
                </div>
                <button type="button" onclick="loadUrls()" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-sync-alt mr-2"></i>Charger les URLs
                </button>
            </div>
            
            <div id="urls-container" class="hidden">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <span id="urls-total" class="text-sm font-medium text-gray-700"></span>
                        <span id="urls-info" class="text-sm text-gray-500 ml-2"></span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button type="button" onclick="previousPage()" id="prev-btn" 
                                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span id="page-info" class="text-sm text-gray-700"></span>
                        <button type="button" onclick="nextPage()" id="next-btn" 
                                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                
                <div class="mb-4">
                    <button type="button" onclick="indexSelectedUrls()" id="index-btn" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-spider mr-2"></i>Indexer les URLs sélectionnées via Google
                    </button>
                    <button type="button" onclick="selectAllUrls()" 
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition ml-2">
                        <i class="fas fa-check-square mr-2"></i>Tout sélectionner
                    </button>
                </div>
                
                <div id="urls-list" class="space-y-2 max-h-96 overflow-y-auto">
                    <!-- URLs chargées dynamiquement -->
                </div>
            </div>
            
            <div id="urls-loading" class="hidden text-center py-8">
                <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                <p class="text-sm text-gray-500">Chargement des URLs...</p>
            </div>
        </div>

        <!-- Robots.txt -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">🤖 Robots.txt</h2>
            <p class="text-sm text-gray-600 mb-4">Fichier de configuration pour les robots des moteurs de recherche</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium">Fichier robots.txt</p>
                                <p class="text-sm text-gray-600">Configuration des robots</p>
                            </div>
                            <a href="{{ url('/robots.txt') }}" target="_blank" 
                               class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                                Voir robots.txt
                            </a>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium">Soumettre à Google</p>
                                <p class="text-sm text-gray-600">URL pour Google Search Console</p>
                            </div>
                            <button type="button" onclick="copyToClipboard('{{ url('/robots.txt') }}')" 
                                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                                Copier l'URL
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <h4 class="font-medium text-yellow-800 mb-2">💡 Instructions pour Google Search Console</h4>
                <ol class="text-sm text-yellow-700 space-y-1">
                    <li>1. Connectez-vous à <a href="https://search.google.com/search-console" target="_blank" class="text-blue-600 hover:underline">Google Search Console</a></li>
                    <li>2. Sélectionnez votre propriété</li>
                    <li>3. Allez dans "Sitemaps" et ajoutez : <code class="bg-yellow-100 px-1 rounded">{{ url('/sitemap.xml') }}</code></li>
                    <li>4. Vérifiez que votre site respecte le fichier robots.txt</li>
                </ol>
            </div>
        </div>

        <!-- IndexJump API -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">🚀 IndexJump API</h2>
            <p class="text-sm text-gray-600 mb-4">Service d'indexation alternatif pour GoogleBot, OpenAI Bot et BingBot</p>
            
            <div class="mb-4">
                <label for="indexjump_token" class="block text-sm font-medium mb-2">Token API IndexJump</label>
                <input type="text" id="indexjump_token" name="indexjump_token" 
                       value="{{ $indexJumpToken }}"
                       placeholder="3d93dd2657466b97a401e540aaf9c72e"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <p class="text-xs text-gray-500 mt-1">Token d'authentification pour l'API IndexJump</p>
            </div>
            
            <div class="flex items-center space-x-4 mb-4">
                <button type="button" onclick="testIndexJumpConnection()" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-plug mr-2"></i>Tester la connexion
                </button>
                <button type="button" onclick="saveIndexJumpToken()" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-save mr-2"></i>Sauvegarder le token
                </button>
                @if($isIndexJumpConfigured)
                <span class="text-sm text-green-600">
                    <i class="fas fa-check-circle mr-1"></i>API configurée
                </span>
                @if($indexJumpBalance !== null)
                <div class="flex items-center space-x-2 px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg">
                    <i class="fas fa-link text-blue-600"></i>
                    <span class="text-sm font-semibold text-blue-800" id="indexjump-balance-display">
                        {{ number_format($indexJumpBalance, 0, ',', ' ') }} liens restants
                    </span>
                    <button type="button" onclick="refreshIndexJumpBalance()" 
                            class="ml-2 text-blue-600 hover:text-blue-800"
                            title="Rafraîchir le solde">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                @else
                <button type="button" onclick="refreshIndexJumpBalance()" 
                        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-sync-alt mr-2"></i>Vérifier le solde
                </button>
                @endif
                @else
                <span class="text-sm text-gray-500">
                    <i class="fas fa-exclamation-circle mr-1"></i>API non configurée
                </span>
                @endif
            </div>

            <!-- Test d'indexation d'une seule URL -->
            @if($isIndexJumpConfigured)
            <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h4 class="text-sm font-semibold text-blue-800 mb-2">
                    <i class="fas fa-flask mr-1"></i>Test d'indexation d'une URL
                </h4>
                <p class="text-xs text-blue-600 mb-3">
                    Testez l'indexation d'une seule URL via IndexJump
                </p>
                <div class="flex items-center space-x-2 mb-2">
                    <input type="url" 
                           id="test-indexjump-url-input" 
                           placeholder="https://normesrenovationbretagne.fr/exemple"
                           value="{{ rtrim(setting('site_url', request()->getSchemeAndHttpHost()), '/') }}/"
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm">
                    <select id="test-indexjump-bot" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                        <option value="0">GoogleBot</option>
                        <option value="1">OpenAI Bot</option>
                        <option value="2">BingBot</option>
                    </select>
                    <button type="button" 
                            onclick="testIndexJumpUrl()" 
                            id="test-indexjump-url-btn"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-paper-plane mr-2"></i>Tester
                    </button>
                </div>
            </div>
            @endif

            <!-- Envoyer les sitemaps à IndexJump -->
            @if($isIndexJumpConfigured && !empty($sitemapInfo))
            <div class="border-t pt-4 mt-4">
                <h3 class="text-md font-semibold text-gray-800 mb-3">📤 Envoyer les sitemaps à IndexJump</h3>
                <div class="space-y-3">
                    @foreach($sitemapInfo as $sitemap)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-sm">{{ $sitemap['filename'] }}</p>
                            <p class="text-xs text-gray-600">{{ number_format($sitemap['size'] / 1024, 2) }} KB</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <select id="indexjump-bot-{{ $loop->index }}" class="px-2 py-1 border border-gray-300 rounded text-xs">
                                <option value="0">GoogleBot</option>
                                <option value="1">OpenAI Bot</option>
                                <option value="2">BingBot</option>
                            </select>
                            <button type="button" 
                                    onclick="submitSitemapToIndexJump('{{ $sitemap['filename'] }}', {{ $loop->index }})" 
                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition"
                                    id="submit-indexjump-sitemap-{{ $loop->index }}">
                                <i class="fas fa-paper-plane mr-2"></i>Envoyer à IndexJump
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="text-center">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg">
                Sauvegarder la Configuration d'Indexation
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Copié !';
        button.classList.remove('bg-green-500', 'hover:bg-green-600');
        button.classList.add('bg-green-600');
        
        setTimeout(() => {
            button.textContent = originalText;
            button.classList.remove('bg-green-600');
            button.classList.add('bg-green-500', 'hover:bg-green-600');
        }, 2000);
    }).catch(function(err) {
        console.error('Erreur lors de la copie: ', err);
        alert('Erreur lors de la copie. Veuillez copier manuellement: ' + text);
    });
}

function updateSitemap() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mise à jour...';
    button.disabled = true;
    button.classList.add('opacity-75');
    
    fetch('{{ route("admin.indexation.update-sitemap") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 401 || response.status === 403) {
                throw new Error('Non autorisé. Veuillez vous reconnecter.');
            } else if (response.status === 404) {
                throw new Error('Route non trouvée. Vérifiez la configuration.');
            } else {
                throw new Error('Erreur serveur: ' + response.status);
            }
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('Sitemap mis à jour avec succès !', 'success');
        } else {
            showNotification('Erreur lors de la mise à jour du sitemap: ' + (data.message || 'Erreur inconnue'), 'error');
        }
    })
    .catch(error => {
        console.error('Erreur détaillée:', error);
        if (error.message.includes('Non autorisé') || error.message.includes('401')) {
            showNotification('Session expirée. Veuillez vous reconnecter.', 'error');
            setTimeout(() => {
                window.location.href = '{{ route("admin.login") }}';
            }, 2000);
        } else {
            showNotification('Erreur: ' + error.message, 'error');
        }
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
        button.classList.remove('opacity-75');
    });
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white font-medium z-50 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Variables pour la pagination des URLs
let currentPage = 1;
let totalUrls = 0;
let lastPage = 1;
let allUrls = [];

function loadUrls(page = 1) {
    const container = document.getElementById('urls-container');
    const loading = document.getElementById('urls-loading');
    
    container.classList.add('hidden');
    loading.classList.remove('hidden');
    
    fetch(`{{ route('admin.indexation.urls') }}?page=${page}&per_page=100`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allUrls = data.urls;
                totalUrls = data.total;
                currentPage = data.page;
                lastPage = data.last_page;
                
                displayUrls();
                updatePagination();
                
                container.classList.remove('hidden');
            } else {
                showNotification('Erreur lors du chargement des URLs: ' + (data.message || 'Erreur inconnue'), 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showNotification('Erreur lors du chargement des URLs', 'error');
        })
        .finally(() => {
            loading.classList.add('hidden');
        });
}

function displayUrls() {
    const container = document.getElementById('urls-list');
    const totalSpan = document.getElementById('urls-total');
    const infoSpan = document.getElementById('urls-info');
    
    totalSpan.textContent = `${totalUrls} URLs au total`;
    infoSpan.textContent = `Page ${currentPage} sur ${lastPage}`;
    
    container.innerHTML = '';
    
    allUrls.forEach((urlData, index) => {
        const div = document.createElement('div');
        div.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition';
        div.innerHTML = `
            <div class="flex items-center flex-1">
                <input type="checkbox" class="url-checkbox mr-3" value="${urlData.url}" data-url="${urlData.url}">
                <div class="flex-1">
                    <a href="${urlData.url}" target="_blank" class="text-blue-600 hover:underline font-medium">
                        ${urlData.url}
                    </a>
                    <div class="text-xs text-gray-500 mt-1">
                        ${urlData.sitemap} | ${urlData.priority || 'N/A'} | ${urlData.changefreq || 'N/A'}
                    </div>
                </div>
            </div>
        `;
        container.appendChild(div);
    });
}

function updatePagination() {
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const pageInfo = document.getElementById('page-info');
    
    prevBtn.disabled = currentPage === 1;
    nextBtn.disabled = currentPage === lastPage;
    
    pageInfo.textContent = `${currentPage} / ${lastPage}`;
}

function previousPage() {
    if (currentPage > 1) {
        loadUrls(currentPage - 1);
    }
}

function nextPage() {
    if (currentPage < lastPage) {
        loadUrls(currentPage + 1);
    }
}

function selectAllUrls() {
    const checkboxes = document.querySelectorAll('.url-checkbox');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(cb => {
        cb.checked = !allChecked;
    });
}

function indexSelectedUrls() {
    const selectedUrls = Array.from(document.querySelectorAll('.url-checkbox:checked'))
        .map(cb => cb.value);
    
    if (selectedUrls.length === 0) {
        showNotification('Veuillez sélectionner au moins une URL', 'error');
        return;
    }
    
    const button = document.getElementById('index-btn');
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Indexation en cours...';
    button.disabled = true;
    
    fetch('{{ route("admin.indexation.index-urls") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ urls: selectedUrls })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(
                `Indexation terminée: ${data.success_count} réussies, ${data.failed_count} échouées`,
                data.failed_count === 0 ? 'success' : 'error'
            );
        } else {
            showNotification('Erreur lors de l\'indexation: ' + (data.message || 'Erreur inconnue'), 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors de l\'indexation', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function testGoogleConnection() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Test en cours...';
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
            let message = '✅ Connexion réussie !';
            if (data.site_found === false) {
                message += '\n⚠️ ' + (data.warning || 'Le site n\'est pas trouvé dans Google Search Console.');
            }
            if (data.indexing_test) {
                if (data.indexing_test.success) {
                    message += '\n✅ Test d\'indexation réussi !';
                } else {
                    message += '\n❌ Test d\'indexation échoué: ' + (data.indexing_test.message || 'Erreur inconnue');
                    if (data.indexing_test.error_code) {
                        message += ' (Code: ' + data.indexing_test.error_code + ')';
                    }
                }
            }
            showNotification(message.replace(/\n/g, '<br>'), data.warning ? 'warning' : 'success');
        } else {
            let errorMsg = '❌ Erreur de connexion: ' + (data.message || 'Erreur inconnue');
            if (data.error_code) {
                errorMsg += ' (Code: ' + data.error_code + ')';
            }
            if (data.error_details && data.error_details.length > 0) {
                errorMsg += '\nDétails: ' + JSON.stringify(data.error_details[0]);
            }
            // Afficher les instructions si présentes dans le message
            if (data.message && data.message.includes('Solution')) {
                errorMsg = '❌ ' + data.message;
            }
            showNotification(errorMsg.replace(/\n/g, '<br>'), 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors du test de connexion', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function submitAllUrlsToGoogle() {
    if (!confirm('Êtes-vous sûr de vouloir envoyer toutes les URLs du sitemap à Google ? Cette opération peut prendre plusieurs minutes.')) {
        return;
    }
    
    const button = document.getElementById('submit-all-btn');
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Envoi en cours...';
    button.disabled = true;
    button.classList.add('opacity-75');
    
    fetch('{{ route("admin.indexation.submit-all-to-google") }}', {
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
            showNotification(
                `✅ ${data.success_count} URLs envoyées avec succès${data.failed_count > 0 ? `, ${data.failed_count} échouées` : ''}`,
                data.failed_count === 0 ? 'success' : 'error'
            );
            
            // Recharger la page après 2 secondes pour voir l'historique mis à jour
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showNotification('Erreur lors de l\'envoi: ' + (data.message || 'Erreur inconnue'), 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors de l\'envoi des URLs à Google', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
        button.classList.remove('opacity-75');
    });
}

function submitSitemapToGoogle(filename) {
    if (!confirm(`Êtes-vous sûr de vouloir envoyer toutes les URLs du sitemap "${filename}" à Google ? Cette opération peut prendre plusieurs minutes.`)) {
        return;
    }
    
    // Trouver le bouton correspondant
    const buttons = document.querySelectorAll('[id^="submit-sitemap-"]');
    let button = null;
    buttons.forEach(btn => {
        if (btn.onclick && btn.onclick.toString().includes(filename)) {
            button = btn;
        }
    });
    
    if (!button) {
        // Fallback: trouver par le texte
        buttons.forEach(btn => {
            if (btn.textContent.includes('Envoyer les liens')) {
                button = btn;
            }
        });
    }
    
    const originalText = button ? button.innerHTML : '';
    
    if (button) {
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Envoi en cours...';
        button.disabled = true;
        button.classList.add('opacity-75');
    }
    
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
            let message = `✅ Sitemap "${filename}": ${data.success_count} URLs envoyées avec succès`;
            if (data.failed_count > 0) {
                message += `, ${data.failed_count} échouées`;
                message += '\n⚠️ Consultez les logs pour plus de détails sur les erreurs.';
                message += '\n💡 Vérifiez que le compte de service est propriétaire du site dans Google Search Console.';
            }
            showNotification(message, data.failed_count === 0 ? 'success' : 'error');
            
            // Recharger la page après 3 secondes pour voir l'historique mis à jour
            setTimeout(() => {
                window.location.reload();
            }, 3000);
        } else {
            let errorMsg = 'Erreur lors de l\'envoi: ' + (data.message || 'Erreur inconnue');
            showNotification(errorMsg, 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors de l\'envoi du sitemap à Google', 'error');
    })
    .finally(() => {
        if (button) {
            button.innerHTML = originalText;
            button.disabled = false;
            button.classList.remove('opacity-75');
        }
    });
}

function toggleDailyIndexing(enabled) {
    if (!confirm(enabled ? 'Activer l\'indexation quotidienne automatique ?' : 'Désactiver l\'indexation quotidienne automatique ?')) {
        return;
    }
    
    const button = document.getElementById('toggle-daily-btn');
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Chargement...';
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
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification('Erreur: ' + (data.message || 'Erreur inconnue'), 'error');
            button.innerHTML = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors de la modification', 'error');
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function runDailyIndexing() {
    if (!confirm('Exécuter l\'indexation quotidienne maintenant ? (150 URLs maximum)')) {
        return;
    }
    
    const button = document.getElementById('run-daily-btn');
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
            let message = data.message || 'Indexation quotidienne exécutée avec succès !';
            if (data.success_count > 0) {
                message += ` (${data.success_count} URLs indexées)`;
            }
            if (data.failed_count > 0) {
                message += `, ${data.failed_count} échouées`;
            }
            if (data.indexed_count !== undefined) {
                message += ` - Total: ${data.indexed_count} URLs indexées`;
            }
            showNotification(message, data.failed_count > 0 ? 'warning' : 'success');
            
            // Attendre un peu pour que les statistiques soient bien sauvegardées
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            let errorMsg = data.message || 'Erreur inconnue';
            if (data.output) {
                // Afficher les détails de l'erreur depuis la sortie
                const lines = data.output.split('\n');
                const errorLine = lines.find(line => line.includes('❌') || line.includes('Erreur') || line.includes('error') || line.includes('Permission denied'));
                if (errorLine) {
                    errorMsg += ': ' + errorLine.trim();
                }
            }
            // Afficher le message d'erreur complet (peut contenir des instructions)
            showNotification(errorMsg.replace(/\n/g, '<br>'), 'error');
            button.innerHTML = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors de l\'exécution', 'error');
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function resetIndexedUrls() {
    if (!confirm('⚠️ Êtes-vous sûr de vouloir réinitialiser la liste des URLs indexées ?\n\nCela permettra de réindexer toutes les URLs depuis le début.')) {
        return;
    }
    
    fetch('{{ route("admin.indexation.reset-indexed-urls") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur HTTP: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('✅ ' + (data.message || 'Liste des URLs indexées réinitialisée'), 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification('❌ Erreur: ' + (data.message || 'Erreur inconnue'), 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('❌ Erreur lors de la réinitialisation: ' + error.message, 'error');
    })
    .finally(() => {
        // S'assurer que le bouton est réactivé même en cas d'erreur
        const button = document.querySelector('[onclick="resetIndexedUrls()"]');
        if (button) {
            button.disabled = false;
        }
    });
}

function testSingleUrl() {
    const urlInput = document.getElementById('test-url-input');
    const url = urlInput.value.trim();
    
    if (!url) {
        showNotification('Veuillez entrer une URL à tester', 'error');
        urlInput.focus();
        return;
    }
    
    // Validation basique de l'URL
    try {
        new URL(url);
    } catch (e) {
        showNotification('URL invalide. Veuillez entrer une URL complète (ex: https://example.com/page)', 'error');
        urlInput.focus();
        return;
    }
    
    const button = document.getElementById('test-single-url-btn');
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Test en cours...';
    button.disabled = true;
    
    fetch('{{ route("admin.indexation.test-single-url") }}', {
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
            showNotification('✅ ' + data.message, 'success');
        } else {
            let errorMsg = '❌ ' + (data.message || 'Erreur inconnue');
            if (data.error_code) {
                errorMsg += ' (Code: ' + data.error_code + ')';
            }
            if (data.error_details && data.error_details.length > 0) {
                const firstError = data.error_details[0];
                if (firstError.reason) {
                    errorMsg += '\nRaison: ' + firstError.reason;
                }
            }
            // Afficher les instructions si présentes dans le message
            if (data.message && data.message.includes('Solution')) {
                errorMsg = '❌ ' + data.message;
            }
            showNotification(errorMsg.replace(/\n/g, '<br>'), 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors du test', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// Fonctions IndexJump
function testIndexJumpConnection() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Test en cours...';
    button.disabled = true;
    
    fetch('{{ route("admin.indexation.test-indexjump") }}', {
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
            showNotification('Connexion IndexJump réussie !', 'success');
        } else {
            showNotification('Erreur de connexion: ' + (data.message || 'Erreur inconnue'), 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors du test de connexion', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function saveIndexJumpToken() {
    const token = document.getElementById('indexjump_token').value;
    
    if (!token) {
        showNotification('Veuillez entrer un token', 'error');
        return;
    }
    
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sauvegarde...';
    button.disabled = true;
    
    fetch('{{ route("admin.indexation.update-indexjump-token") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ indexjump_token: token })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Token sauvegardé avec succès', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification('Erreur: ' + (data.message || 'Erreur inconnue'), 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors de la sauvegarde', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function refreshIndexJumpBalance() {
    const button = event?.target || document.querySelector('[onclick="refreshIndexJumpBalance()"]');
    const balanceDisplay = document.getElementById('indexjump-balance-display');
    
    if (balanceDisplay) {
        balanceDisplay.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }
    
    if (button && button.classList.contains('fa-sync-alt')) {
        button.classList.add('fa-spin');
    } else if (button) {
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Chargement...';
        button.disabled = true;
    }
    
    fetch('{{ route("admin.indexation.indexjump-balance") }}', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const balance = data.balance || 0;
            if (balanceDisplay) {
                balanceDisplay.textContent = new Intl.NumberFormat('fr-FR').format(balance) + ' liens restants';
            } else {
                // Si l'affichage n'existe pas, créer un nouvel élément
                const parent = document.querySelector('.flex.items-center.space-x-4.mb-4');
                if (parent) {
                    const balanceDiv = document.createElement('div');
                    balanceDiv.className = 'flex items-center space-x-2 px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg';
                    balanceDiv.innerHTML = `
                        <i class="fas fa-link text-blue-600"></i>
                        <span class="text-sm font-semibold text-blue-800" id="indexjump-balance-display">
                            ${new Intl.NumberFormat('fr-FR').format(balance)} liens restants
                        </span>
                        <button type="button" onclick="refreshIndexJumpBalance()" 
                                class="ml-2 text-blue-600 hover:text-blue-800"
                                title="Rafraîchir le solde">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    `;
                    parent.appendChild(balanceDiv);
                }
            }
            showNotification('Solde mis à jour: ' + new Intl.NumberFormat('fr-FR').format(balance) + ' liens restants', 'success');
        } else {
            showNotification('Erreur: ' + (data.message || 'Impossible de récupérer le solde'), 'error');
            if (balanceDisplay) {
                balanceDisplay.textContent = 'Erreur';
            }
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors de la récupération du solde', 'error');
        if (balanceDisplay) {
            balanceDisplay.textContent = 'Erreur';
        }
    })
    .finally(() => {
        if (button && button.classList.contains('fa-sync-alt')) {
            button.classList.remove('fa-spin');
        } else if (button) {
            button.disabled = false;
            if (button.querySelector('.fa-spinner')) {
                button.innerHTML = '<i class="fas fa-sync-alt mr-2"></i>Vérifier le solde';
            }
        }
    });
}

function testIndexJumpUrl() {
    const urlInput = document.getElementById('test-indexjump-url-input');
    const botSelect = document.getElementById('test-indexjump-bot');
    const url = urlInput.value.trim();
    const bot = botSelect ? botSelect.value : 0;
    
    if (!url) {
        showNotification('Veuillez entrer une URL à tester', 'error');
        urlInput.focus();
        return;
    }
    
    const button = document.getElementById('test-indexjump-url-btn');
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Test en cours...';
    button.disabled = true;
    
    fetch('{{ route("admin.indexation.test-indexjump-url") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ url: url, bot: bot })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('✅ ' + data.message, 'success');
        } else {
            showNotification('❌ ' + (data.message || 'Erreur inconnue'), 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors du test', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function submitSitemapToIndexJump(filename, index) {
    const botSelect = document.getElementById('indexjump-bot-' + index);
    const bot = botSelect ? botSelect.value : 0;
    const button = document.getElementById('submit-indexjump-sitemap-' + index);
    
    if (!confirm(`Êtes-vous sûr de vouloir envoyer le sitemap "${filename}" à IndexJump ? Cette opération peut prendre plusieurs minutes.`)) {
        return;
    }
    
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Envoi en cours...';
    button.disabled = true;
    
    fetch('{{ route("admin.indexation.submit-sitemap-to-indexjump") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ filename: filename, bot: bot })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(
                `✅ ${data.success_count} URLs envoyées avec succès${data.failed_count > 0 ? `, ${data.failed_count} échouées` : ''}`,
                data.failed_count === 0 ? 'success' : 'error'
            );
        } else {
            showNotification('Erreur lors de l\'envoi: ' + (data.message || 'Erreur inconnue'), 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors de l\'envoi du sitemap à IndexJump', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// Permettre d'appuyer sur Entrée dans le champ URL
document.addEventListener('DOMContentLoaded', function() {
    const urlInput = document.getElementById('test-url-input');
    if (urlInput) {
        urlInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                testSingleUrl();
            }
        });
    }
});
</script>
@endpush

