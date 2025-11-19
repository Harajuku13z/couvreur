@extends('layouts.admin')

@section('title', 'Automatisation SEO')

@section('content')
<div class="container mx-auto px-4 py-6 md:py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Automatisation SEO</h1>
            <p class="text-gray-600 mt-1">Gestion des articles SEO générés automatiquement</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.keywords.index') }}" 
               class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 flex items-center">
                <i class="fas fa-tags mr-2"></i>
                Gérer les mots-clés
            </a>
            <form action="{{ route('admin.seo-automation.reset-all') }}" method="POST" class="inline" onsubmit="return confirm('⚠️ ATTENTION : Cela supprimera TOUS les logs d\'automation et les jobs en attente. Êtes-vous sûr ?');">
                @csrf
                <button type="submit" 
                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 flex items-center">
                    <i class="fas fa-trash-alt mr-2"></i>
                    Réinitialiser tout
                </button>
            </form>
        </div>
    </div>
    
    <!-- Résultat du test scheduler -->
    <div id="schedulerTestResult" class="hidden mb-4"></div>
    
    <!-- Liste des horaires planifiés -->
    @if(isset($scheduledTimes) && count($scheduledTimes) > 0)
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>Horaires Planifiés pour Aujourd'hui
        </h2>
        <p class="text-sm text-gray-600 mb-4">
            Liste des créneaux horaires prévus pour la création des articles aujourd'hui ({{ count($scheduledTimes) }} article(s) planifié(s))
        </p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($scheduledTimes as $schedule)
            <div class="p-4 rounded-xl border-2 {{ $schedule['is_past'] ? ($schedule['article_created'] ?? false ? 'bg-green-50 border-green-300' : 'bg-red-50 border-red-300') : 'bg-blue-50 border-blue-200' }} shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <i class="fas fa-clock {{ $schedule['is_past'] ? 'text-gray-500' : 'text-blue-600' }}"></i>
                            <span class="font-bold text-lg text-gray-900">{{ $schedule['time'] }}</span>
                            @if(isset($schedule['article_created']) && $schedule['article_created'])
                                <span class="px-2 py-0.5 bg-green-500 text-white text-xs font-semibold rounded-full">
                                    <i class="fas fa-check mr-1"></i>Créé
                                </span>
                            @elseif($schedule['is_past'])
                                <span class="px-2 py-0.5 bg-red-500 text-white text-xs font-semibold rounded-full">
                                    <i class="fas fa-times mr-1"></i>Manqué
                                </span>
                            @endif
                        </div>
                        <div class="text-sm text-gray-700 mb-2">
                            <span class="font-medium">Article #{{ $schedule['article_number'] }}</span>
                        </div>
                        @if(isset($schedule['city']))
                        <div class="flex items-center space-x-2 text-sm">
                            <i class="fas fa-map-marker-alt text-green-600"></i>
                            <span class="font-semibold text-gray-800">{{ $schedule['city']['name'] }}</span>
                            @if($schedule['city']['postal_code'])
                                <span class="text-gray-500">({{ $schedule['city']['postal_code'] }})</span>
                            @endif
                        </div>
                        @endif
                        @if($schedule['is_past'] && !($schedule['article_created'] ?? false))
                        <div class="mt-2 text-xs text-red-600">
                            <div class="flex items-center mb-1">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                <span>Aucun article généré à cette heure</span>
                            </div>
                            @if(isset($schedule['error_message']) && !empty($schedule['error_message']))
                            <div class="mt-1 p-2 bg-red-100 rounded border border-red-300">
                                <div class="font-semibold text-red-800 mb-1">Erreur :</div>
                                <div class="text-red-700 break-words">{{ $schedule['error_message'] }}</div>
                            </div>
                            @else
                            <div class="mt-1 text-red-500 italic">
                                Raison possible : Le cron Hostinger n'a pas été exécuté à temps ou a échoué silencieusement.
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                    @if(!$schedule['is_past'])
                    <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse ml-2"></div>
                    @endif
                </div>
            </div>
            @endforeach
            </div>
            
        @if(isset($scheduleStats))
        <div class="mt-4 p-3 bg-gray-50 rounded-lg">
            <div class="text-xs text-gray-600 space-y-1">
                <div><strong>Heure de début :</strong> {{ $scheduleStats['start_time'] ?? 'N/A' }}</div>
                <div><strong>Intervalle entre articles :</strong> {{ $scheduleStats['interval_minutes'] ?? 0 }} minutes</div>
                <div><strong>Prochain créneau :</strong> {{ $scheduleStats['next_scheduled_time'] ?? 'N/A' }}</div>
                <div><strong>Articles créés aujourd'hui :</strong> {{ $scheduleStats['articles_today'] ?? 0 }}/{{ $scheduleStats['total_articles_per_day'] ?? 0 }}</div>
        </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Lien vers la configuration du cron -->
    <div class="relative overflow-hidden bg-gradient-to-r from-blue-500 via-blue-600 to-indigo-600 rounded-xl shadow-lg p-6 mb-6 transform transition-all hover:shadow-xl hover:scale-[1.01]">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full"></div>
        <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-32 h-32 bg-white opacity-5 rounded-full"></div>
        <div class="relative flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center mb-2">
                    <div class="bg-white bg-opacity-20 rounded-lg p-2 mr-3">
                        <i class="fas fa-clock text-white text-xl"></i>
            </div>
                    <h3 class="text-xl font-bold text-white">
                        Configuration du Cron
                    </h3>
                </div>
                <p class="text-blue-100 text-sm leading-relaxed">
                    Instructions détaillées pour configurer le cron dans Hostinger. Deux méthodes disponibles : Script shell direct (recommandé) ou Route HTTP.
                </p>
            </div>
            <a href="{{ route('admin.cron-config.index') }}" 
               class="group bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-600 flex items-center whitespace-nowrap transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                <span>Voir les instructions</span>
                <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
            </a>
        </div>
    </div>
    
    <!-- Statut automatisation -->
    @php
        $automationTime = \App\Models\Setting::where('key', 'seo_automation_time')->value('value') ?? '04:00';
    @endphp
    @if($automationEnabled)
        <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-600 mr-2"></i>
                    <div>
                        <p class="text-sm font-medium text-green-900">Automatisation activée</p>
                        @if(isset($pendingJobs) && count($pendingJobs) > 0)
                            <p class="text-xs text-blue-700 mt-1">
                                <i class="fas fa-clock mr-1"></i><strong>{{ count($pendingJobs) }} job(s)</strong> en attente dans la queue
                            </p>
                        @endif
                    </div>
                </div>
                <form action="{{ route('admin.seo-automation.save-time') }}" method="POST" class="flex items-center gap-2 flex-wrap">
                    @csrf
                    <div class="flex items-center gap-2">
                        <label for="automation_time" class="text-xs text-gray-600 whitespace-nowrap">Heure:</label>
                        <input type="time" 
                               id="automation_time" 
                               name="time" 
                               value="{{ $automationTime }}"
                               class="px-2 py-1 border border-gray-300 rounded text-sm">
                    </div>
                    <div class="flex items-center gap-2">
                        <label for="cron_interval" class="text-xs text-gray-600 whitespace-nowrap">Intervalle cron (min):</label>
                        <input type="number" 
                               id="cron_interval" 
                               name="cron_interval" 
                               value="{{ \App\Models\Setting::where('key', 'seo_automation_cron_interval')->value('value') ?? 1 }}"
                               min="1" 
                               max="60"
                               class="px-2 py-1 border border-gray-300 rounded text-sm w-16"
                               title="Fréquence de vérification du cron (en minutes). Le système vérifie toutes les X minutes si l'heure configurée est arrivée.">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="number" 
                               id="articles_per_day" 
                               name="articles_per_day" 
                               value="{{ \App\Models\Setting::where('key', 'seo_automation_articles_per_day')->value('value') ?? 5 }}"
                               min="1" 
                               max="50"
                               class="px-2 py-1 border border-gray-300 rounded text-sm w-16"
                               title="Nombre d'articles par jour par ville (répartis sur la journée)">
                        <label for="articles_per_day" class="text-xs text-gray-600 whitespace-nowrap">article(s)/jour/ville</label>
                    </div>
                    @php
                        $citiesCount = \App\Models\City::where('is_favorite', true)->count();
                        $articlesPerDay = (int)(\App\Models\Setting::where('key', 'seo_automation_articles_per_day')->value('value') ?? 5);
                        $totalArticlesPerDay = $articlesPerDay * $citiesCount;
                    @endphp
                    @if($citiesCount > 0)
                    <div class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        Total : <strong>{{ $totalArticlesPerDay }} articles/jour</strong> pour {{ $citiesCount }} ville(s) favorite(s)
                    </div>
                    @endif
                            <div class="flex items-center gap-2">
                                <label for="direct_execution" class="text-xs text-gray-600 flex items-center gap-1">
                                    <input type="checkbox" 
                                           id="direct_execution" 
                                           name="direct_execution" 
                                           value="1"
                                           {{ \App\Models\Setting::where('key', 'seo_automation_direct_execution')->value('value') ? 'checked' : '' }}
                                           class="rounded">
                                    <span>Exécution directe (sans queue)</span>
                                </label>
                            </div>
                            <div class="flex items-center gap-2">
                                <label for="ignore_quota" class="text-xs text-gray-600 flex items-center gap-1">
                                    <input type="checkbox" 
                                           id="ignore_quota" 
                                           name="ignore_quota" 
                                           value="1"
                                           {{ \App\Models\Setting::where('key', 'seo_automation_ignore_quota')->value('value') ? 'checked' : '' }}
                                           class="rounded">
                                    <span>Ignorer le quota journalier (mode test)</span>
                                </label>
                            </div>
                    <button type="submit" 
                            class="px-3 py-1 bg-green-600 text-white rounded text-xs hover:bg-green-700">
                        <i class="fas fa-save mr-1"></i>Enregistrer
                    </button>
                </form>
                        <div class="mt-2 text-xs text-gray-600 space-y-1">
                            <div>
                                <i class="fas fa-info-circle mr-1"></i>
                                <strong>Exécution directe :</strong> Les articles sont générés immédiatement sans passer par la queue (plus fiable, pas besoin de worker).
                            </div>
                            <div>
                                <i class="fas fa-flask mr-1 text-yellow-600"></i>
                                <strong>Ignorer le quota :</strong> En mode test, ignore la limite d'articles par jour pour permettre des tests sans restriction.
                            </div>
                        </div>
            </div>
        </div>
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
            <div class="flex items-center">
                <i class="fas fa-pause-circle text-yellow-600 mr-2"></i>
                <div>
                    <p class="text-sm font-medium text-yellow-900">Automatisation en pause</p>
                    <p class="text-xs text-yellow-700 mt-1">Les générations automatiques sont désactivées. Vous pouvez toujours générer des articles manuellement.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de test des connexions -->
    <div id="testModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">
                    <i class="fas fa-vial mr-2 text-green-600"></i>Test des connexions
                </h3>
                <button id="closeTestModal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div id="testResults" class="space-y-4">
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-3xl text-blue-600 mb-4"></i>
                    <p class="text-gray-600">Test en cours...</p>
                </div>
            </div>
        </div>
    </div>

    @if(session('scheduler_output'))
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
            <div class="flex items-start">
                <i class="fas fa-terminal mr-2 mt-0.5 text-blue-600"></i>
                <div class="flex-1">
                    <p class="font-semibold text-blue-900 mb-2">Sortie du scheduler :</p>
                    <pre class="bg-white p-3 rounded text-xs overflow-x-auto border border-blue-200">{{ session('scheduler_output') }}</pre>
                </div>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <div class="flex items-start">
                <i class="fas fa-check-circle mr-2 mt-0.5"></i>
                <div class="flex-1">
                    <p class="font-semibold whitespace-pre-line">{{ session('success') }}</p>
                    @if(session('seo_results'))
                        <div class="mt-3 space-y-4">
                            @foreach(session('seo_results') as $resultIndex => $result)
                                <div class="bg-white rounded-lg p-4 border-2 {{ $result['status'] === 'success' ? 'border-green-300' : 'border-red-300' }}">
                                    <!-- En-tête -->
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex-1">
                                            <div class="font-semibold text-lg text-gray-900">
                                                <i class="fas fa-map-marker-alt mr-2 text-blue-600"></i>{{ $result['city'] }}
                                            </div>
                                            @if(isset($result['keyword']))
                                                <div class="text-sm text-gray-600 mt-1">
                                                    <i class="fas fa-tag mr-1"></i>Mot-clé: <strong>{{ $result['keyword'] }}</strong>
                                                </div>
                                            @endif
                                        </div>
                                        @if($result['status'] === 'success')
                                            <div class="ml-2 flex flex-col items-end gap-1">
                                                <span class="px-3 py-1 bg-green-200 text-green-800 text-xs font-semibold rounded">
                                                    <i class="fas fa-check mr-1"></i>Publié
                                                </span>
                                                @if(isset($result['indexed']) && $result['indexed'])
                                                    <span class="px-3 py-1 bg-blue-200 text-blue-800 text-xs font-semibold rounded">
                                                        <i class="fab fa-google mr-1"></i>Indexé
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="ml-2 px-3 py-1 bg-red-200 text-red-800 text-xs font-semibold rounded">
                                                <i class="fas fa-times mr-1"></i>Échec
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <!-- Étapes du processus -->
                                    @if(isset($result['steps']) && is_array($result['steps']) && count($result['steps']) > 0)
                                        <div class="mt-4 border-t border-gray-200 pt-4">
                                            <h4 class="text-sm font-semibold text-gray-700 mb-3">
                                                <i class="fas fa-list-ol mr-1"></i>Détails du processus:
                                            </h4>
                                            <div class="space-y-3">
                                                @foreach($result['steps'] as $stepIndex => $step)
                                                    <div class="flex items-start gap-3 p-3 rounded-lg border {{ 
                                                        $step['status'] === 'success' ? 'bg-green-50 border-green-200' : 
                                                        ($step['status'] === 'failed' ? 'bg-red-50 border-red-200' : 
                                                        'bg-blue-50 border-blue-200') 
                                                    }}">
                                                        <div class="flex-shrink-0 mt-0.5">
                                                            @if($step['status'] === 'success')
                                                                <i class="fas fa-check-circle text-green-600 text-lg"></i>
                                                            @elseif($step['status'] === 'failed')
                                                                <i class="fas fa-times-circle text-red-600 text-lg"></i>
                                                            @elseif($step['status'] === 'warning')
                                                                <i class="fas fa-exclamation-triangle text-yellow-600 text-lg"></i>
                                                            @else
                                                                <i class="fas fa-spinner fa-spin text-blue-600 text-lg"></i>
                                                            @endif
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <div class="font-medium text-sm text-gray-900">
                                                                {{ $step['title'] ?? 'Étape ' . ($stepIndex + 1) }}
                                                            </div>
                                                            @php
                                                                // Masquer les messages d'indexation intermédiaires lors de l'automatisation
                                                                $stepMessage = $step['message'] ?? '';
                                                                $isIndexationStep = ($step['step'] ?? '') === 'google_indexing';
                                                                $hideIndexationMessages = $isIndexationStep && (
                                                                    strpos($stepMessage, 'Demande d\'indexation envoyée') !== false ||
                                                                    strpos($stepMessage, 'En attente d\'indexation') !== false ||
                                                                    strpos($stepMessage, 'vérification en cours') !== false
                                                                );
                                                            @endphp
                                                            @if(!$hideIndexationMessages)
                                                            <div class="text-xs text-gray-600 mt-1">
                                                                {{ $stepMessage }}
                                                            </div>
                                                            @endif
                                                            @if(isset($step['data']) && is_array($step['data']) && !empty($step['data']))
                                                                <div class="mt-2 text-xs">
                                                                    @if(isset($step['data']['keywords']) && is_array($step['data']['keywords']))
                                                                        <div class="text-gray-600">
                                                                            <strong>Mots-clés:</strong> {{ implode(', ', array_slice($step['data']['keywords'], 0, 3)) }}
                                                                            @if(isset($step['data']['total']) && $step['data']['total'] > 3)
                                                                                (+ {{ $step['data']['total'] - 3 }} autres)
                                                                            @endif
                                                                        </div>
                                                                    @endif
                                                                    @if(isset($step['data']['related_queries']) && is_array($step['data']['related_queries']) && !empty($step['data']['related_queries']))
                                                                        <div class="text-gray-600 mt-2">
                                                                            <strong>Requêtes associées ({{ count($step['data']['related_queries']) }}):</strong>
                                                                            <ul class="list-disc list-inside mt-1 space-y-1">
                                                                                @foreach($step['data']['related_queries'] as $query)
                                                                                    <li class="text-sm">{{ $query }}</li>
                                                                                @endforeach
                                                                            </ul>
                                                                        </div>
                                                                    @endif
                                                                    @if(isset($step['data']['competitors']) && is_array($step['data']['competitors']) && !empty($step['data']['competitors']))
                                                                        <div class="text-gray-600 mt-2">
                                                                            <strong>Concurrents analysés ({{ count($step['data']['competitors']) }}):</strong>
                                                                            <ul class="list-none mt-2 space-y-2">
                                                                                @foreach($step['data']['competitors'] as $competitor)
                                                                                    <li class="text-sm border-l-2 border-blue-300 pl-2">
                                                                                        <div class="font-medium">{{ $competitor['title'] ?? 'N/A' }}</div>
                                                                                        @if(!empty($competitor['link']))
                                                                                            <a href="{{ $competitor['link'] }}" target="_blank" class="text-blue-600 hover:underline text-xs break-all">
                                                                                                <i class="fas fa-external-link-alt mr-1"></i>{{ $competitor['link'] }}
                                                                                            </a>
                                                                                        @endif
                                                                                        @if(!empty($competitor['snippet']))
                                                                                            <div class="text-xs text-gray-500 mt-1 italic">{{ Str::limit($competitor['snippet'], 100) }}</div>
                                                                                        @endif
                                                                                    </li>
                                                                                @endforeach
                                                                            </ul>
                                                                        </div>
                                                                    @elseif(isset($step['data']['competitors_count']))
                                                                        <div class="text-gray-600 mt-1">
                                                                            <strong>Concurrents analysés:</strong> {{ $step['data']['competitors_count'] }} résultats
                                                                        </div>
                                                                    @endif
                                                                    @if(isset($step['data']['title']))
                                                                        <div class="text-gray-600 mt-2 p-2 bg-blue-50 border border-blue-200 rounded">
                                                                            <strong class="text-blue-900">Titre choisi par ChatGPT:</strong>
                                                                            <div class="text-blue-800 font-medium mt-1">{{ $step['data']['title'] }}</div>
                                                                        </div>
                                                                    @endif
                                                                    @if(isset($step['data']['url']))
                                                                        <div class="text-gray-600 mt-1">
                                                                            <strong>URL:</strong> <a href="{{ $step['data']['url'] }}" target="_blank" class="text-blue-600 hover:underline break-all">{{ $step['data']['url'] }}</a>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <!-- Lien vers l'article -->
                                    @if($result['status'] === 'success' && isset($result['url']))
                                        <div class="mt-4 pt-4 border-t border-gray-200">
                                            <a href="{{ $result['url'] }}" target="_blank" 
                                               class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                <i class="fas fa-external-link-alt mr-2"></i>
                                                Voir l'article publié
                                            </a>
                                            @if(isset($result['indexed']))
                                                <div class="text-xs mt-2 {{ $result['indexed'] ? 'text-green-600' : 'text-yellow-600' }}">
                                                    <i class="fas {{ $result['indexed'] ? 'fa-check-circle' : 'fa-clock' }} mr-1"></i>
                                                    {{ $result['indexed'] ? 'Indexé par Google' : 'En attente d\'indexation' }}
                                                </div>
                                            @endif
                                        </div>
                                    @elseif($result['status'] === 'failed' || $result['status'] === 'error')
                                        <div class="mt-4 pt-4 border-t border-gray-200">
                                            <div class="text-sm text-red-600 bg-red-50 p-2 rounded">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                <strong>Erreur:</strong> {{ $result['error'] ?? 'Erreur lors de la génération' }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <i class="fas fa-exclamation-circle mr-2"></i>
            {{ session('error') }}
        </div>
    @endif

            <!-- Configuration des APIs -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">
                    <i class="fas fa-cog mr-2 text-gray-600"></i>Configuration des APIs
                </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- SerpAPI -->
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fab fa-google mr-2 text-blue-600"></i>SerpAPI
                        @if(!empty($apiConfig['serpapi_key']))
                            <span class="ml-2 text-xs text-green-600">
                                <i class="fas fa-check-circle"></i> Configuré
                            </span>
                        @else
                            <span class="ml-2 text-xs text-gray-500">
                                <i class="fas fa-exclamation-circle"></i> Non configuré
                            </span>
                        @endif
                    </h3>
                    <button onclick="testApi('serpapi', this)" 
                            class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                        <i class="fas fa-vial mr-1"></i>Test
                    </button>
                </div>
                <form action="{{ route('admin.seo-automation.save-config') }}" method="POST" class="space-y-3">
                    @csrf
                    
                    <!-- Toggle pour activer/désactiver SerpAPI dans l'automatisation -->
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="flex-1">
                            <label class="text-sm font-medium text-gray-700 block">
                                Utiliser SerpAPI dans l'automatisation
                            </label>
                            <p class="text-xs text-gray-500 mt-1">
                                Si désactivé, ChatGPT gère tout (mots-clés, concurrents, etc.)
                            </p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer ml-4">
                            <input type="checkbox" 
                                   name="seo_automation_serpapi_enabled" 
                                   value="1"
                                   {{ \App\Models\Setting::where('key', 'seo_automation_serpapi_enabled')->value('value') ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    
                    <div>
                        <input type="password" 
                               name="serpapi_key" 
                               value=""
                               placeholder="{{ !empty($apiConfig['serpapi_key']) ? 'Laisser vide pour conserver la clé actuelle' : 'Entrez votre clé API SerpAPI' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        @if(!empty($apiConfig['serpapi_key']))
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-info-circle mr-1"></i>Clé configurée ({{ strlen($apiConfig['serpapi_key']) }} caractères)
                            </p>
                        @endif
                    </div>
                    <button type="submit" class="w-full bg-gray-600 text-white px-3 py-2 rounded text-sm hover:bg-gray-700">
                        <i class="fas fa-save mr-1"></i>Sauvegarder
                    </button>
                </form>
                <div id="serpapi_result" class="mt-2 text-sm"></div>
            </div>

            <!-- ChatGPT -->
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-robot mr-2 text-green-600"></i>ChatGPT
                        @if(!empty($apiConfig['chatgpt_api_key']))
                            <span class="ml-2 text-xs text-green-600">
                                <i class="fas fa-check-circle"></i> Configuré
                            </span>
                        @else
                            <span class="ml-2 text-xs text-gray-500">
                                <i class="fas fa-exclamation-circle"></i> Non configuré
                            </span>
                        @endif
                    </h3>
                    <button onclick="testApi('gpt', this)" 
                            class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                        <i class="fas fa-vial mr-1"></i>Test
                    </button>
                </div>
                <form action="{{ route('admin.seo-automation.save-config') }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="flex items-center">
                        <input type="checkbox" name="chatgpt_enabled" value="1" {{ $apiConfig['chatgpt_enabled'] ? 'checked' : '' }} class="rounded">
                        <label class="ml-2 text-sm text-gray-700">Activer</label>
                    </div>
                    <div>
                        <input type="password" 
                               name="chatgpt_api_key" 
                               value=""
                               placeholder="{{ !empty($apiConfig['chatgpt_api_key']) ? 'Laisser vide pour conserver la clé actuelle' : 'Entrez votre clé API OpenAI' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        @if(!empty($apiConfig['chatgpt_api_key']))
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-info-circle mr-1"></i>Clé configurée ({{ strlen($apiConfig['chatgpt_api_key']) }} caractères)
                            </p>
                        @endif
                    </div>
                    <select name="chatgpt_model" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="gpt-3.5-turbo" {{ $apiConfig['chatgpt_model'] == 'gpt-3.5-turbo' ? 'selected' : '' }}>GPT-3.5 Turbo</option>
                        <option value="gpt-4" {{ $apiConfig['chatgpt_model'] == 'gpt-4' ? 'selected' : '' }}>GPT-4</option>
                        <option value="gpt-4-turbo" {{ $apiConfig['chatgpt_model'] == 'gpt-4-turbo' ? 'selected' : '' }}>GPT-4 Turbo</option>
                        <option value="gpt-4o" {{ $apiConfig['chatgpt_model'] == 'gpt-4o' ? 'selected' : '' }}>GPT-4o</option>
                    </select>
                    <button type="submit" class="w-full bg-gray-600 text-white px-3 py-2 rounded text-sm hover:bg-gray-700">
                        <i class="fas fa-save mr-1"></i>Sauvegarder
                    </button>
                </form>
                <div id="gpt_result" class="mt-2 text-sm"></div>
            </div>

            <!-- Google Indexing -->
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fab fa-google mr-2 text-red-600"></i>Google Indexing
                    </h3>
                    <button onclick="testApi('google_indexing', this)" 
                            class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">
                        <i class="fas fa-vial mr-1"></i>Test
                    </button>
                </div>
                <form action="{{ route('admin.seo-automation.save-config') }}" method="POST" class="space-y-3">
                    @csrf
                    <textarea name="google_credentials" 
                              rows="4"
                              placeholder="{{ $apiConfig['google_credentials'] ? 'Laisser vide pour conserver' : 'Credentials JSON' }}"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-mono">{{ old('google_credentials', $apiConfig['google_credentials']) }}</textarea>
                    <button type="submit" class="w-full bg-gray-600 text-white px-3 py-2 rounded text-sm hover:bg-gray-700">
                        <i class="fas fa-save mr-1"></i>Sauvegarder
                    </button>
                </form>
                <div id="google_indexing_result" class="mt-2 text-sm"></div>
            </div>
        </div>
    </div>

    <!-- Lien vers la gestion des mots-clés -->
    <div class="relative overflow-hidden bg-gradient-to-r from-indigo-500 via-purple-600 to-pink-600 rounded-xl shadow-lg p-6 mb-6 transform transition-all hover:shadow-xl hover:scale-[1.01]">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full"></div>
        <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-32 h-32 bg-white opacity-5 rounded-full"></div>
        <div class="relative flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                                <div class="flex-1">
                <div class="flex items-center mb-2">
                    <div class="bg-white bg-opacity-20 rounded-lg p-2 mr-3">
                        <i class="fas fa-tags text-white text-xl"></i>
                                </div>
                    <h3 class="text-xl font-bold text-white">
                        Gestion des Mots-clés
                    </h3>
                    @if(count($customKeywords) > 0)
                        <span class="ml-3 bg-white bg-opacity-20 text-white text-xs font-semibold px-3 py-1 rounded-full">
                            {{ count($customKeywords) }} configuré(s)
                        </span>
                    @endif
                    </div>
                <p class="text-indigo-100 text-sm leading-relaxed">
                    Gérez vos mots-clés SEO et leurs images associées sur une page dédiée pour une meilleure organisation.
                </p>
            </div>
            <a href="{{ route('admin.keywords.index') }}" 
               class="group bg-white text-indigo-600 px-6 py-3 rounded-lg font-semibold hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-600 flex items-center whitespace-nowrap transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                <span>Gérer les mots-clés</span>
                <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
            </a>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Total</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
        </div>
        <div class="bg-yellow-50 rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">En attente</div>
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</div>
        </div>
        <div class="bg-blue-50 rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Publiés</div>
            <div class="text-2xl font-bold text-blue-600">{{ $stats['published'] }}</div>
        </div>
        <div class="bg-green-50 rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Indexés</div>
            <div class="text-2xl font-bold text-green-600">{{ $stats['indexed'] }}</div>
        </div>
        <div class="bg-red-50 rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Échoués</div>
            <div class="text-2xl font-bold text-red-600">{{ $stats['failed'] }}</div>
        </div>
    </div>

    <!-- Bouton de relance manuelle -->
    @if($stats['failed'] > 0)
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-redo-alt text-red-600 mr-2"></i>
                <div>
                    <p class="text-sm font-medium text-red-900">
                        Articles en échec
                    </p>
                    <p class="text-xs text-red-700 mt-1">
                        {{ $stats['failed'] }} article(s) échoué(s)
                    </p>
                </div>
            </div>
            <form action="{{ route('admin.seo-automation.retry-pending-failed') }}" method="POST" class="inline" 
                  onsubmit="return confirm('⚠️ Êtes-vous sûr de vouloir relancer {{ $stats['failed'] }} article(s) en échec ?');">
                @csrf
                <button type="submit" 
                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 flex items-center">
                    <i class="fas fa-play-circle mr-2"></i>
                    Relancer tous les articles en échec
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- Formulaire de lancement - Design Premium -->
    <div class="relative overflow-hidden bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 rounded-2xl shadow-2xl border-2 border-blue-200 mb-8">
        <!-- Effet de brillance animé -->
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full animate-shimmer"></div>
        
        <div class="relative p-8">
            <!-- Header avec icône animée -->
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl blur-xl opacity-50 animate-pulse"></div>
                        <div class="relative bg-gradient-to-br from-blue-500 via-indigo-600 to-purple-600 rounded-2xl p-4 shadow-xl transform hover:scale-105 transition-transform duration-300">
                            <i class="fas fa-rocket text-white text-3xl"></i>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-3xl font-extrabold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">
                            🚀 Génération d'Articles SEO
        </h2>
                        <p class="text-sm text-gray-600 mt-1 font-medium">Créez des articles optimisés automatiquement avec l'IA</p>
                    </div>
                </div>
            </div>
        
            <form action="{{ route('admin.seo-automation.run') }}" method="POST" class="space-y-6">
            @csrf
            
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nombre d'articles -->
                <div class="bg-white/80 backdrop-blur-sm rounded-xl p-4 border border-blue-100 shadow-sm">
                    <label for="number_of_articles" class="block text-sm font-semibold text-gray-800 mb-2 flex items-center">
                        <i class="fas fa-file-alt text-blue-600 mr-2"></i>
                        Nombre d'articles à créer <span class="text-red-500 ml-1">*</span>
                    </label>
                    <input type="number" 
                           id="number_of_articles" 
                           name="number_of_articles" 
                           value="{{ old('number_of_articles', 1) }}"
                           min="1" 
                           max="50" 
                           required
                           class="w-full px-4 py-3 border-2 border-blue-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all bg-white">
                    <p class="text-xs text-gray-600 mt-2 flex items-center">
                        <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                        Entre 1 et 50 articles par ville
                    </p>
                </div>

                <!-- Sélection de service -->
                <div class="bg-white/80 backdrop-blur-sm rounded-xl p-4 border border-blue-100 shadow-sm">
                    <label for="service_id" class="block text-sm font-semibold text-gray-800 mb-2 flex items-center">
                        <i class="fas fa-briefcase text-indigo-600 mr-2"></i>
                        Service (optionnel)
                    </label>
                    <select id="service_id" 
                            name="service_id" 
                            class="w-full px-4 py-3 border-2 border-blue-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all bg-white">
                        <option value="">-- Aucun service --</option>
                        @foreach($services as $service)
                            <option value="{{ $service['id'] ?? '' }}" {{ old('service_id') == ($service['id'] ?? '') ? 'selected' : '' }}>
                                {{ $service['name'] ?? 'Service sans nom' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-600 mt-2 flex items-center">
                        <i class="fas fa-info-circle text-indigo-500 mr-1"></i>
                        Sélectionner un service pour utiliser son nom comme mot-clé
                    </p>
                </div>
            </div>

            <!-- Mot-clé personnalisé -->
            <div class="bg-white/80 backdrop-blur-sm rounded-xl p-4 border border-blue-100 shadow-sm">
                <label for="keyword" class="block text-sm font-semibold text-gray-800 mb-2 flex items-center">
                    <i class="fas fa-keyboard text-purple-600 mr-2"></i>
                    Mot-clé personnalisé (optionnel)
                </label>
                <input type="text" 
                       id="keyword" 
                       name="keyword" 
                       value="{{ old('keyword') }}"
                       placeholder="Ex: couvreur, toiture, rénovation..."
                       maxlength="255"
                       class="w-full px-4 py-3 border-2 border-blue-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all bg-white">
                <p class="text-xs text-gray-600 mt-2 flex items-center">
                    <i class="fas fa-info-circle text-purple-500 mr-1"></i>
                    Si rempli, ce mot-clé sera utilisé au lieu des tendances. Priorité sur le service.
                </p>
            </div>

            <!-- Sélection des villes -->
            <div class="bg-white/80 backdrop-blur-sm rounded-xl p-4 border border-blue-100 shadow-sm">
                <label class="block text-sm font-semibold text-gray-800 mb-2 flex items-center">
                    <i class="fas fa-map-marker-alt text-green-600 mr-2"></i>
                    Villes à cibler
                </label>
                <div class="border-2 border-blue-200 rounded-xl p-4 max-h-48 overflow-y-auto bg-white shadow-inner">
                    @if($favoriteCities->isEmpty())
                        <p class="text-sm text-gray-500 italic">Aucune ville favorite configurée. Allez dans <strong>Villes</strong> pour en marquer comme favorites.</p>
                    @else
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       id="select_all_cities" 
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm font-medium text-gray-700">Sélectionner toutes ({{ $favoriteCities->count() }} villes favorites)</span>
                            </label>
                            <hr class="my-2">
                            @foreach($favoriteCities as $city)
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           name="city_ids[]" 
                                           value="{{ $city->id }}"
                                           {{ old('city_ids') && in_array($city->id, old('city_ids')) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">{{ $city->name }}</span>
                                    @if($city->postal_code)
                                        <span class="ml-2 text-xs text-gray-500">({{ $city->postal_code }})</span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>
                <p class="text-xs text-gray-600 mt-2 flex items-center">
                    <i class="fas fa-info-circle text-green-500 mr-1"></i>
                    Si aucune ville n'est sélectionnée, toutes les villes favorites seront utilisées
                </p>
            </div>

                <!-- Bouton de soumission Premium -->
                <div class="md:col-span-2 flex justify-end pt-4 border-t border-blue-200">
                <button type="submit" 
                            class="group relative overflow-hidden bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white px-8 py-4 rounded-xl font-bold text-lg shadow-2xl hover:shadow-blue-500/50 transform hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-blue-300 focus:ring-offset-2 flex items-center space-x-3">
                        <!-- Effet de brillance au survol -->
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                        
                        <!-- Contenu du bouton -->
                        <span class="relative z-10 flex items-center space-x-3">
                            <i class="fas fa-rocket text-xl animate-bounce group-hover:animate-none"></i>
                            <span>Lancer la Génération</span>
                            <i class="fas fa-arrow-right text-lg transform group-hover:translate-x-1 transition-transform"></i>
                        </span>
                        
                        <!-- Particules animées -->
                        <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity">
                            <div class="absolute top-2 left-4 w-2 h-2 bg-white rounded-full animate-ping"></div>
                            <div class="absolute bottom-2 right-8 w-2 h-2 bg-white rounded-full animate-ping" style="animation-delay: 0.2s"></div>
                            <div class="absolute top-1/2 right-4 w-1.5 h-1.5 bg-white rounded-full animate-ping" style="animation-delay: 0.4s"></div>
                        </div>
                </button>
            </div>
        </form>
        </div>
    </div>
    
    <style>
        @keyframes shimmer {
            0% {
                transform: translateX(-100%);
            }
            100% {
                transform: translateX(100%);
            }
        }
        .animate-shimmer {
            animation: shimmer 3s infinite;
        }
    </style>

    <!-- Table Desktop -->
    <div class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ville</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mot-clé</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Article</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($logs as $log)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $log->city->name ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $log->keyword ?? '-' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'generated' => 'bg-blue-100 text-blue-800',
                                'published' => 'bg-blue-100 text-blue-800',
                                'indexed' => 'bg-green-100 text-green-800',
                                'failed' => 'bg-red-100 text-red-800',
                            ];
                            $statusLabels = [
                                'pending' => 'En attente',
                                'generated' => 'Généré',
                                'published' => 'Publié',
                                'indexed' => 'Indexé',
                                'failed' => 'Échoué',
                            ];
                        @endphp
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$log->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $statusLabels[$log->status] ?? $log->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($log->article_id)
                            @php
                                $article = \App\Models\Article::find($log->article_id);
                                $articleUrl = $article ? url('/blog/' . $article->slug) : null;
                                $metadata = is_array($log->metadata) ? $log->metadata : json_decode($log->metadata, true);
                                $seoAnalysis = $metadata['seo_analysis'] ?? null;
                                $indexRequested = $metadata['index_requested'] ?? false;
                                $isIndexed = $metadata['indexed'] ?? false;
                            @endphp
                            @if($articleUrl)
                                <a href="{{ $articleUrl }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm mr-3">
                                    <i class="fas fa-external-link-alt mr-1"></i> Voir
                                </a>
                            @endif
                            
                            <div class="mt-2 space-y-2">
                                {{-- Statut indexation --}}
                                @if($isIndexed)
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>Indexé ✅
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            {{ isset($metadata['index_requested_at']) ? 'Le ' . \Carbon\Carbon::parse($metadata['index_requested_at'])->format('d/m/Y H:i') : '' }}
                                        </span>
                                    </div>
                                @elseif($indexRequested)
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                            <i class="fas fa-clock mr-1"></i>Demande envoyée
                                        </span>
                                        <span class="text-xs text-gray-500">En attente Google (3-7j)</span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>Non indexé
                                        </span>
                                        @if($articleUrl)
                                        <button onclick="indexerArticle('{{ $articleUrl }}', this)" 
                                                class="inline-flex items-center px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-lg transition">
                                            <i class="fas fa-paper-plane mr-1"></i>Indexer maintenant
                                        </button>
                                        @endif
                                    </div>
                                @endif
                            @if($seoAnalysis)
                                    <div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        @if($seoAnalysis['percentage'] >= 75) bg-green-100 text-green-800
                                        @elseif($seoAnalysis['percentage'] >= 60) bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        <i class="fas fa-star mr-1"></i>{{ $seoAnalysis['grade'] }} ({{ $seoAnalysis['percentage'] }}%)
                                    </span>
                                </div>
                            @endif
                            </div>
                        @else
                            <span class="text-gray-400 text-sm">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $log->created_at->format('d/m/Y H:i') }}
                        @if($log->status === 'failed' && $log->error_message)
                            <div class="mt-1 text-xs text-red-600">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                <span class="font-semibold">Erreur:</span> {{ Str::limit($log->error_message, 80) }}
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center gap-2">
                            @if($log->status === 'pending')
                                <form action="{{ route('admin.seo-automation.retry', $log) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-blue-600 hover:text-blue-900" title="Régénérer">
                                        <i class="fas fa-redo mr-1"></i> Régénérer
                                    </button>
                                </form>
                            @endif
                            
                            @if($log->status === 'failed' && !$log->article_id)
                                <form action="{{ route('admin.seo-automation.destroy', $log) }}" method="POST" class="inline" 
                                      onsubmit="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer ce log d\'échec ? Cette action est irréversible.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Supprimer">
                                        <i class="fas fa-trash mr-1"></i> Supprimer
                                    </button>
                                </form>
                            @endif
                            
                            @if($log->article_id)
                                <form action="{{ route('admin.seo-automation.destroy', $log) }}" method="POST" class="inline" 
                                      onsubmit="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer cet article et son log ? Cette action est irréversible.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Supprimer">
                                        <i class="fas fa-trash mr-1"></i> Supprimer
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        Aucune automation enregistrée
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Cards Mobile -->
    <div class="md:hidden space-y-4">
        @forelse($logs as $log)
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <div class="font-semibold text-gray-900">{{ $log->city->name ?? 'N/A' }}</div>
                    <div class="text-sm text-gray-600 mt-1">{{ $log->keyword ?? '-' }}</div>
                </div>
                @php
                    $statusColors = [
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'generated' => 'bg-blue-100 text-blue-800',
                        'published' => 'bg-blue-100 text-blue-800',
                        'indexed' => 'bg-green-100 text-green-800',
                        'failed' => 'bg-red-100 text-red-800',
                    ];
                    $statusLabels = [
                        'pending' => 'En attente',
                        'generated' => 'Généré',
                        'published' => 'Publié',
                        'indexed' => 'Indexé',
                        'failed' => 'Échoué',
                    ];
                @endphp
                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$log->status] ?? 'bg-gray-100 text-gray-800' }}">
                    {{ $statusLabels[$log->status] ?? $log->status }}
                </span>
            </div>
            
            <div class="text-sm text-gray-500 mb-3">
                <i class="far fa-calendar mr-1"></i> {{ $log->created_at->format('d/m/Y H:i') }}
            </div>
            
            @if($log->article_id)
                @php
                    $article = \App\Models\Article::find($log->article_id);
                    $articleUrl = $article ? url('/blog/' . $article->slug) : null;
                    $metadata = is_array($log->metadata) ? $log->metadata : json_decode($log->metadata, true);
                    $seoAnalysis = $metadata['seo_analysis'] ?? null;
                    $indexRequested = $metadata['index_requested'] ?? false;
                    $isIndexed = $metadata['indexed'] ?? false;
                @endphp
                @if($articleUrl)
                    <a href="{{ $articleUrl }}" target="_blank" class="inline-block text-blue-600 hover:text-blue-800 text-sm mb-3">
                        <i class="fas fa-external-link-alt mr-1"></i> Voir l'article
                    </a>
                @endif
                
                <div class="mb-3 space-y-2">
                    {{-- Statut indexation détaillé --}}
                    @if($isIndexed)
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-green-50 border border-green-200">
                            <i class="fas fa-check-circle text-green-600 text-lg"></i>
                            <div>
                                <div class="text-sm font-semibold text-green-800">Indexé dans Google ✅</div>
                                <div class="text-xs text-green-600">
                                    {{ isset($metadata['index_requested_at']) ? \Carbon\Carbon::parse($metadata['index_requested_at'])->format('d/m/Y H:i') : '' }}
                                </div>
                            </div>
                        </div>
                    @elseif($indexRequested)
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-50 border border-blue-200">
                            <i class="fas fa-hourglass-half text-blue-600 text-lg"></i>
                            <div>
                                <div class="text-sm font-semibold text-blue-800">Demande d'indexation envoyée</div>
                                <div class="text-xs text-blue-600">En attente Google (3-7 jours)</div>
                            </div>
                        </div>
                    @else
                        <div class="space-y-2">
                            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-yellow-50 border border-yellow-200">
                                <i class="fas fa-exclamation-triangle text-yellow-600 text-lg"></i>
                                <div class="text-sm font-semibold text-yellow-800">Pas encore indexé</div>
                            </div>
                            @if($articleUrl)
                            <div>
                                <button onclick="indexerArticle('{{ $articleUrl }}', this)" 
                                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg shadow hover:shadow-lg transition">
                                    <i class="fas fa-paper-plane mr-2"></i>Indexer maintenant
                                </button>
                            </div>
                            @endif
                        </div>
                    @endif
                </div>
                @if($seoAnalysis)
                    <div class="mt-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="inline-flex items-center px-3 py-1 rounded text-sm font-medium
                                @if($seoAnalysis['percentage'] >= 75) bg-green-100 text-green-800
                                @elseif($seoAnalysis['percentage'] >= 60) bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif">
                                <i class="fas fa-star mr-1"></i>{{ $seoAnalysis['grade'] }} ({{ $seoAnalysis['percentage'] }}%)
                            </span>
                        </div>
                        @if(!empty($seoAnalysis['strengths']))
                            <div class="text-xs text-green-700 mb-2">
                                <strong>Points forts :</strong>
                                <ul class="list-disc list-inside mt-1">
                                    @foreach(array_slice($seoAnalysis['strengths'], 0, 3) as $strength)
                                        <li>{{ $strength }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if(!empty($seoAnalysis['issues']))
                            <div class="text-xs text-red-700">
                                <strong>Points à améliorer :</strong>
                                <ul class="list-disc list-inside mt-1">
                                    @foreach(array_slice($seoAnalysis['issues'], 0, 3) as $issue)
                                        <li>{{ $issue }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endif
            @endif
            
            <div class="flex gap-2 mt-3">
                @if($log->status === 'pending')
                    <form action="{{ route('admin.seo-automation.retry', $log) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
                            <i class="fas fa-redo mr-1"></i> Régénérer
                        </button>
                    </form>
                @endif
                
                @if($log->status === 'failed' && !$log->article_id)
                    <form action="{{ route('admin.seo-automation.destroy', $log) }}" method="POST" class="flex-1"
                          onsubmit="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer ce log d\'échec ? Cette action est irréversible.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-sm">
                            <i class="fas fa-trash mr-1"></i> Supprimer
                        </button>
                    </form>
                @endif
                
                @if($log->article_id)
                    <form action="{{ route('admin.seo-automation.destroy', $log) }}" method="POST" class="flex-1"
                          onsubmit="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer cet article et son log ? Cette action est irréversible.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-sm">
                            <i class="fas fa-trash mr-1"></i> Supprimer
                        </button>
                    </form>
                @endif
            </div>
            
            @if($log->error_message)
                <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-red-600 mr-2 mt-0.5"></i>
                        <div class="flex-1">
                            <div class="font-semibold text-red-800 text-sm mb-1">Raison de l'échec :</div>
                            <div class="text-red-700 text-sm">{{ $log->error_message }}</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">
            Aucune automation enregistrée
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $logs->links() }}
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestionnaire pour "Sélectionner toutes"
    const selectAllCheckbox = document.getElementById('select_all_cities');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const cityCheckboxes = document.querySelectorAll('input[name="city_ids[]"]');
            cityCheckboxes.forEach(function(cb) {
                cb.checked = this.checked;
            }.bind(this));
        });
    }
    
    const testBtn = document.getElementById('testConnectionsBtn');
    const testModal = document.getElementById('testModal');
    const closeModal = document.getElementById('closeTestModal');
    const testResults = document.getElementById('testResults');

    testBtn.addEventListener('click', function() {
        testModal.classList.remove('hidden');
        testResults.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-3xl text-blue-600 mb-4"></i>
                <p class="text-gray-600">Test en cours...</p>
            </div>
        `;

        fetch('{{ route("admin.seo-automation.test") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            let html = '';
            
            // Déterminer les classes CSS
            let summaryBgClass = 'bg-green-50';
            let summaryBorderClass = 'border-green-400';
            let summaryTextClass = 'text-green-700';
            let summaryIconClass = 'check-circle';
            let summaryTitle = '✅ Toutes les connexions sont OK';
            
            if (data.has_error) {
                summaryBgClass = 'bg-red-50';
                summaryBorderClass = 'border-red-400';
                summaryTextClass = 'text-red-700';
                summaryIconClass = 'exclamation-circle';
                summaryTitle = '❌ Certaines connexions ont échoué';
            } else if (!data.success) {
                summaryBgClass = 'bg-yellow-50';
                summaryBorderClass = 'border-yellow-400';
                summaryTextClass = 'text-yellow-700';
                summaryIconClass = 'exclamation-triangle';
                summaryTitle = '⚠️ Certaines connexions ont des avertissements';
            }
            
            // Résumé
            html += '<div class="' + summaryBgClass + ' border ' + summaryBorderClass + ' rounded-lg p-4 mb-4">';
            html += '<div class="flex items-center">';
            html += '<i class="fas fa-' + summaryIconClass + ' ' + summaryTextClass + ' mr-2"></i>';
            html += '<div>';
            html += '<p class="font-semibold ' + summaryTextClass + '">' + summaryTitle + '</p>';
            html += '<p class="text-sm ' + summaryTextClass + ' mt-1">';
            html += data.summary.success + ' réussie(s), ' + data.summary.warning + ' avertissement(s), ' + data.summary.error + ' erreur(s)';
            html += '</p>';
            html += '</div>';
            html += '</div>';
            html += '</div>';

            // Détails par service
            const serviceNames = {
                'serpapi': 'SerpAPI',
                'gpt': 'GPT (ChatGPT/Groq)',
                'google_indexing': 'Google Indexing'
            };
            
            const statusColors = {
                'success': { bg: 'bg-green-50', border: 'border-green-400', text: 'text-green-700', icon: 'check-circle' },
                'warning': { bg: 'bg-yellow-50', border: 'border-yellow-400', text: 'text-yellow-700', icon: 'exclamation-triangle' },
                'error': { bg: 'bg-red-50', border: 'border-red-400', text: 'text-red-700', icon: 'times-circle' },
                'pending': { bg: 'bg-gray-50', border: 'border-gray-400', text: 'text-gray-700', icon: 'clock' }
            };
            
            Object.keys(data.results).forEach(service => {
                const result = data.results[service];
                const colors = statusColors[result.status] || statusColors.pending;
                
                html += '<div class="' + colors.bg + ' border ' + colors.border + ' rounded-lg p-4">';
                html += '<div class="flex items-start">';
                html += '<i class="fas fa-' + colors.icon + ' ' + colors.text + ' mr-3 mt-1"></i>';
                html += '<div class="flex-1">';
                html += '<h4 class="font-semibold ' + colors.text + ' mb-1">' + (serviceNames[service] || service) + '</h4>';
                html += '<p class="text-sm ' + colors.text + '">' + result.message + '</p>';
                if (result.data) {
                    html += '<div class="mt-2 text-xs ' + colors.text + ' opacity-75">';
                    if (Array.isArray(result.data)) {
                        html += result.data.join(', ');
                    } else {
                        html += JSON.stringify(result.data);
                    }
                    html += '</div>';
                }
                html += '</div>';
                html += '</div>';
                html += '</div>';
            });

            testResults.innerHTML = html;
        })
        .catch(error => {
            testResults.innerHTML = `
                <div class="bg-red-50 border border-red-400 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-times-circle text-red-600 mr-2"></i>
                        <p class="text-red-700">Erreur lors du test: ${error.message}</p>
                    </div>
                </div>
            `;
        });
    });

    closeModal.addEventListener('click', function() {
        testModal.classList.add('hidden');
    });

    // Fermer en cliquant en dehors
    testModal.addEventListener('click', function(e) {
        if (e.target === testModal) {
            testModal.classList.add('hidden');
        }
    });
});

// Fonction pour tester une API individuelle (doit être globale pour onclick)
function testApi(apiName, button) {
        console.log('testApi appelé avec:', apiName);
        
        const resultDivId = apiName + '_result';
        const resultDiv = document.getElementById(resultDivId);
        
        if (!resultDiv) {
            console.error('Élément resultDiv non trouvé:', resultDivId);
            alert('Erreur: élément de résultat non trouvé pour ' + apiName);
            return;
        }
        
        const originalText = button.innerHTML;
        
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Test...';
        resultDiv.innerHTML = '<div class="text-blue-600"><i class="fas fa-spinner fa-spin mr-1"></i>Test en cours...</div>';
        
        const url = '{{ route("admin.seo-automation.test-api") }}';
        console.log('URL de test:', url);
        
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ api: apiName }),
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('Réponse reçue:', response.status, response.statusText);
            
            if (!response.ok) {
                // Essayer de lire le JSON même en cas d'erreur
                return response.text().then(text => {
                    console.error('Erreur HTTP:', response.status, text);
                    let errorData;
                    try {
                        errorData = JSON.parse(text);
                    } catch (e) {
                        errorData = { message: text || 'Erreur HTTP ' + response.status };
                    }
                    throw new Error(errorData.message || 'Erreur HTTP ' + response.status);
                });
            }
            
            return response.text().then(text => {
                console.log('Réponse texte:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Erreur parsing JSON:', e, text);
                    throw new Error('Réponse invalide du serveur: ' + text.substring(0, 100));
                }
            });
        })
        .then(data => {
            console.log('Données reçues:', data);
            
            button.disabled = false;
            button.innerHTML = originalText;
            
            if (!data || !data.status) {
                throw new Error('Réponse invalide: ' + JSON.stringify(data));
            }
            
            let bgClass = 'bg-green-50 border-green-400 text-green-700';
            let icon = 'check-circle';
            
            if (data.status === 'error') {
                bgClass = 'bg-red-50 border-red-400 text-red-700';
                icon = 'times-circle';
            } else if (data.status === 'warning') {
                bgClass = 'bg-yellow-50 border-yellow-400 text-yellow-700';
                icon = 'exclamation-triangle';
            }
            
            let html = '<div class="' + bgClass + ' border rounded-lg p-2 mt-2">';
            html += '<i class="fas fa-' + icon + ' mr-1"></i>';
            html += '<span>' + (data.message || 'Aucun message') + '</span>';
            if (data.data) {
                html += '<div class="mt-2 text-xs">';
                
                // Afficher les informations de connexion
                if (data.data.sites_count !== undefined || data.data.site_url || data.data.site_found !== undefined) {
                    html += '<div class="mb-3 p-2 bg-blue-50 border border-blue-200 rounded">';
                    html += '<div class="font-semibold mb-1">Informations de connexion:</div>';
                    if (data.data.sites_count !== undefined) {
                        html += '<div>Sites trouvés: ' + data.data.sites_count + '</div>';
                    }
                    if (data.data.site_url) {
                        html += '<div>URL du site: ' + data.data.site_url + '</div>';
                    }
                    if (data.data.site_found !== undefined) {
                        html += '<div>Site trouvé: ' + (data.data.site_found ? 'Oui' : 'Non') + '</div>';
                    }
                    if (data.data.site_permission) {
                        html += '<div>Permission: ' + data.data.site_permission + '</div>';
                    }
                    html += '</div>';
                }
                
                // Afficher les tests d'URL
                console.log('url_tests:', data.data.url_tests);
                if (data.data.url_tests && Array.isArray(data.data.url_tests) && data.data.url_tests.length > 0) {
                    html += '<div class="mt-3">';
                    html += '<div class="font-semibold mb-2 text-sm">Tests d\'indexation par protocole (' + data.data.url_tests.length + ' tests):</div>';
                    data.data.url_tests.forEach(function(test, index) {
                        console.log('Test URL:', test);
                        const testBgClass = test.success ? 'bg-green-50 text-green-800 border-green-300' : 'bg-red-50 text-red-800 border-red-300';
                        const testIcon = test.success ? 'check-circle' : 'times-circle';
                        html += '<div class="' + testBgClass + ' border-2 rounded-lg p-3 mb-2">';
                        html += '<div class="flex items-start">';
                        html += '<i class="fas fa-' + testIcon + ' mr-2 mt-0.5 text-lg"></i>';
                        html += '<div class="flex-1">';
                        html += '<div class="font-mono text-sm font-bold mb-1 break-all">' + test.url + '</div>';
                        html += '<div class="text-xs mt-1">' + test.message + '</div>';
                        if (test.error_code) {
                            html += '<div class="text-xs mt-1 opacity-75 font-semibold">Code d\'erreur: ' + test.error_code + '</div>';
                        }
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                    });
                    html += '</div>';
                } else if (typeof data.data === 'object') {
                    // Pour les autres types de données
                    html += '<div class="mt-1 opacity-75">';
                    if (Array.isArray(data.data)) {
                        html += data.data.join(', ');
                    } else {
                        html += JSON.stringify(data.data, null, 2);
                    }
                    html += '</div>';
                } else if (data.data) {
                    html += '<div class="mt-1 opacity-75">' + data.data + '</div>';
                }
                
                html += '</div>';
            }
            html += '</div>';
            
            resultDiv.innerHTML = html;
        })
        .catch(error => {
            console.error('Erreur complète test API:', error);
            button.disabled = false;
            button.innerHTML = originalText;
            
            const errorMessage = error.message || 'Erreur inconnue';
            resultDiv.innerHTML = '<div class="bg-red-50 border border-red-400 text-red-700 rounded-lg p-2 mt-2"><i class="fas fa-times-circle mr-1"></i>Erreur: ' + errorMessage + '</div>';
        });
        }

        // Gestion des mots-clés personnalisés
        let customKeywords = @json($customKeywords ?? []);
        
        document.getElementById('generateKeywordsBtn')?.addEventListener('click', function() {
            const btn = this;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Génération en cours...';
            
            fetch('{{ route("admin.seo-automation.generate-keywords") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                
                if (data.status === 'success' && data.keywords) {
                    customKeywords = data.keywords;
                    displayKeywords();
                    document.getElementById('keywordsResult').innerHTML = '<div class="text-green-600"><i class="fas fa-check-circle mr-1"></i>' + data.message + '</div>';
                } else {
                    document.getElementById('keywordsResult').innerHTML = '<div class="text-red-600"><i class="fas fa-times-circle mr-1"></i>' + (data.message || 'Erreur lors de la génération') + '</div>';
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                document.getElementById('keywordsResult').innerHTML = '<div class="text-red-600"><i class="fas fa-times-circle mr-1"></i>Erreur: ' + error.message + '</div>';
            });
        });
        
        function displayKeywords() {
            const container = document.getElementById('keywordsContainer');
            if (customKeywords.length === 0) {
                container.innerHTML = '<p class="text-sm text-gray-500 italic">Aucun mot-clé configuré. Cliquez sur "Générer les mots-clés via ChatGPT" pour en créer depuis la description de l\'entreprise.</p>';
                document.getElementById('saveKeywordsBtn').disabled = true;
                document.getElementById('saveKeywordsBtn').classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                let html = '';
                customKeywords.forEach((keyword, index) => {
                    html += `<div class="flex items-center gap-3 p-2 bg-white rounded border border-gray-200">
                        <div class="flex-1">
                            <input type="text" 
                                   name="keywords[]" 
                                   value="${keyword}"
                                   class="w-full px-2 py-1 border border-gray-300 rounded text-sm"
                                   placeholder="Mot-clé">
                        </div>
                        <div class="w-32">
                            <input type="file" 
                                   name="keyword_images[${index}]"
                                   accept="image/jpeg,image/png,image/jpg,image/webp"
                                   class="w-full text-xs">
                        </div>
                        <button type="button" 
                                onclick="removeKeywordItem(this)"
                                class="text-red-600 hover:text-red-800 px-2">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>`;
                });
                container.innerHTML = html;
                document.getElementById('saveKeywordsBtn').disabled = false;
                document.getElementById('saveKeywordsBtn').classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
        
        window.removeKeywordItem = function(button) {
            const item = button.closest('.flex.items-center');
            if (item) {
                item.remove();
                // Mettre à jour customKeywords
                const inputs = document.querySelectorAll('#keywordsContainer input[name="keywords[]"]');
                customKeywords = Array.from(inputs).map(input => input.value).filter(k => k.trim() !== '');
                if (customKeywords.length === 0) {
                    displayKeywords();
                }
            }
        };
        
        document.getElementById('saveKeywordsBtn')?.addEventListener('click', function() {
            const btn = this;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sauvegarde...';
            
            const form = document.getElementById('keywordsForm');
            if (!form) {
                console.error('Formulaire keywordsForm introuvable');
                btn.disabled = false;
                btn.innerHTML = originalText;
                document.getElementById('keywordsResult').innerHTML = '<div class="text-red-600"><i class="fas fa-times-circle mr-1"></i>Erreur: Formulaire introuvable</div>';
                return;
            }
            
            const formData = new FormData(form);
            
            // Vérifier qu'il y a au moins un mot-clé
            const keywordInputs = form.querySelectorAll('input[name="keywords[]"]');
            const hasKeywords = Array.from(keywordInputs).some(input => input.value.trim() !== '');
            
            if (!hasKeywords) {
                btn.disabled = false;
                btn.innerHTML = originalText;
                document.getElementById('keywordsResult').innerHTML = '<div class="text-red-600"><i class="fas fa-times-circle mr-1"></i>Veuillez ajouter au moins un mot-clé</div>';
                return;
            }
            
            // Ajouter le token CSRF
            formData.append('_token', '{{ csrf_token() }}');
            
            fetch('{{ route("admin.seo-automation.save-keywords") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                // Vérifier si la réponse est OK
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Erreur HTTP ' + response.status);
                    });
                }
                return response.json();
            })
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                
                if (data.status === 'success') {
                    document.getElementById('keywordsResult').innerHTML = '<div class="text-green-600"><i class="fas fa-check-circle mr-1"></i>' + data.message + '</div>';
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    document.getElementById('keywordsResult').innerHTML = '<div class="text-red-600"><i class="fas fa-times-circle mr-1"></i>' + (data.message || 'Erreur lors de la sauvegarde') + '</div>';
                }
            })
            .catch(error => {
                console.error('Erreur sauvegarde mots-clés:', error);
                btn.disabled = false;
                btn.innerHTML = originalText;
                document.getElementById('keywordsResult').innerHTML = '<div class="text-red-600"><i class="fas fa-times-circle mr-1"></i>Erreur: ' + (error.message || 'Erreur inconnue') + '</div>';
            });
        });
        
        // Tester le scheduler
        document.getElementById('testSchedulerBtn')?.addEventListener('click', function() {
            const btn = this;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Test en cours...';
            
            const resultDiv = document.getElementById('schedulerTestResult');
            resultDiv.classList.remove('hidden');
            resultDiv.innerHTML = '<div class="bg-blue-50 border border-blue-200 rounded-lg p-4"><i class="fas fa-spinner fa-spin mr-2"></i>Test du scheduler en cours...</div>';
            
            fetch('{{ route("admin.seo-automation.test-scheduler") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                
                if (data.status === 'success') {
                    const info = data.info;
                    let html = '<div class="bg-white border border-gray-200 rounded-lg p-4">';
                    html += '<h3 class="font-bold text-gray-900 mb-3"><i class="fas fa-info-circle mr-2 text-blue-600"></i>Résultat du test scheduler</h3>';
                    html += '<div class="space-y-2 text-sm">';
                    html += `<p><strong>Heure actuelle :</strong> ${info.current_time} (${info.timezone})</p>`;
                    html += `<p><strong>Heure configurée :</strong> ${info.automation_time}</p>`;
                    html += `<p><strong>Scheduler exécuté :</strong> <span class="${info.scheduler_executed ? 'text-green-600' : 'text-red-600'}">${info.scheduler_executed ? '✅ Oui' : '❌ Non'}</span></p>`;
                    html += `<p><strong>Déclenchement prévu :</strong> <span class="${info.will_trigger ? 'text-green-600 font-bold' : 'text-gray-600'}">${info.will_trigger ? '✅ Oui, maintenant' : '❌ Non, attendre ' + info.automation_time}</span></p>`;
                    if (info.explanation) {
                        html += `<p class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded text-xs"><strong>ℹ️ Explication :</strong> ${info.explanation}</p>`;
                    }
                    if (info.output) {
                        html += '<details class="mt-3"><summary class="cursor-pointer text-blue-600 hover:underline">Voir la sortie complète</summary>';
                        html += '<pre class="mt-2 p-2 bg-gray-100 rounded text-xs overflow-auto max-h-40">' + escapeHtml(info.output) + '</pre>';
                        html += '</details>';
                    }
                    html += '</div>';
                    
                    // Avertissement si le cron n'est pas configuré
                    if (!info.cron_configured) {
                        html += '<div class="mt-4 p-4 bg-red-50 border border-red-200 rounded text-sm">';
                        html += '<p class="font-semibold text-red-800 mb-2"><i class="fas fa-exclamation-triangle mr-2"></i>⚠️ CRON NON CONFIGURÉ</p>';
                        html += '<p class="text-red-700 mb-3">Le scheduler Laravel ne s\'exécutera pas automatiquement tant que le cron n\'est pas configuré sur votre serveur.</p>';
                        html += '<div class="bg-white p-3 rounded border border-red-300">';
                        html += '<p class="font-semibold mb-2">📋 Instructions pour configurer le cron :</p>';
                        html += '<ol class="list-decimal list-inside space-y-2 text-xs text-gray-700">';
                        html += '<li>Connectez-vous à votre serveur via SSH</li>';
                        html += '<li>Exécutez : <code class="bg-gray-200 px-1 rounded">crontab -e</code></li>';
                        html += '<li>Ajoutez cette ligne (remplacez le chemin par votre chemin réel) :</li>';
                        html += '</ol>';
                        html += '<div class="mt-2 p-2 bg-gray-100 rounded font-mono text-xs overflow-x-auto">';
                        html += '* * * * * cd ' + window.location.pathname.split('/admin')[0] + ' && php artisan schedule:run >> /dev/null 2>&1';
                        html += '</div>';
                        html += '<p class="text-xs text-gray-600 mt-2">💡 <strong>Pour Hostinger :</strong> Le chemin est généralement <code>/home/u570136219/public_html</code></p>';
                        html += '<p class="text-xs text-gray-600">💡 <strong>Pour trouver le chemin PHP :</strong> <code>which php</code> ou <code>whereis php</code></p>';
                        html += '</div>';
                        html += '</div>';
                    } else {
                        html += '<div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded text-xs">';
                        html += '<p class="font-semibold mb-2">💡 Si le scheduler ne s\'exécute pas automatiquement :</p>';
                        html += '<ol class="list-decimal list-inside space-y-1">';
                        html += '<li>Vérifiez que le cron Laravel est configuré : <code class="bg-gray-200 px-1 rounded">crontab -l</code></li>';
                        html += '<li>Si absent, ajoutez : <code class="bg-gray-200 px-1 rounded">* * * * * cd /chemin-projet && php artisan schedule:run</code></li>';
                        html += '<li>Vérifiez les logs : <code class="bg-gray-200 px-1 rounded">tail -f storage/logs/laravel.log</code></li>';
                        html += '</ol>';
                        html += '</div>';
                    }
                    html += '</div>';
                    resultDiv.innerHTML = html;
                } else {
                    resultDiv.innerHTML = '<div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4"><i class="fas fa-exclamation-circle mr-2"></i>' + (data.message || 'Erreur lors du test') + '</div>';
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                resultDiv.innerHTML = '<div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4"><i class="fas fa-times-circle mr-2"></i>Erreur: ' + error.message + '</div>';
            });
        });
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Fonction pour indexer un article manuellement
        function indexerArticle(articleUrl, btn) {
            console.log('Indexation demandée pour :', articleUrl);
            
            if (!confirm(`Indexer cet article dans Google ?\n\nURL : ${articleUrl}\n\nLa demande sera envoyée à Google Indexing API.`)) {
                return;
            }
            
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Envoi...';
            
            // Nettoyer l'URL (enlever espaces, etc.)
            const cleanUrl = articleUrl.trim();
            
            console.log('URL nettoyée :', cleanUrl);
            const submitUrl = '/admin/seo-automation/google-index-url';
            console.log('Envoi requête à', submitUrl);
            
            fetch(submitUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ url: cleanUrl })
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response ok:', response.ok);
                return response.json();
            })
            .then(data => {
                console.log('Réponse serveur:', data);
                
                if (data.success) {
                    alert(`✅ Demande d'indexation envoyée avec succès !\n\nL'article sera indexé par Google dans 3-7 jours.\n\nURL : ${cleanUrl}`);
                    window.location.reload();
                } else {
                    const errorMsg = data.message || 'Erreur inconnue';
                    alert(`❌ Erreur : ${errorMsg}\n\nConsultez les logs Laravel pour plus de détails.`);
                    console.error('Erreur serveur:', data);
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            })
            .catch(error => {
                console.error('Erreur réseau ou parsing:', error);
                alert(`❌ Erreur : ${error.message}\n\nVérifications :\n1. Google Search Console configuré dans /admin/indexation\n2. Credentials JSON valides\n3. Logs Laravel : tail -f storage/logs/laravel.log`);
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
        }
        </script>
        
    <!-- Image Open Graph par défaut (Blog) -->
    @php
        $currentOgImage = \App\Models\Setting::where('key', 'default_blog_og_image')->value('value') ?? 'images/og-blog.jpg';
    @endphp
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-image mr-2 text-purple-600"></i>Image Open Graph par défaut (Blog)
        </h2>
        <p class="text-sm text-gray-600 mb-4">
            Cette image sera utilisée comme image de partage (og:image) pour tous les articles qui n'ont pas d'image mise en avant. Format recommandé : 1200x630px (ratio 1.91:1).
        </p>
        
        <div class="space-y-4">
            <!-- Upload d'image -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Uploader une nouvelle image
                </label>
                <form action="{{ route('admin.seo-automation.upload-og-image') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div>
                        <input type="file" 
                               name="og_image" 
                               id="og_image_input"
                               accept="image/jpeg,image/png,image/jpg,image/webp"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <p class="text-xs text-gray-500 mt-1">
                            Formats acceptés : JPG, PNG, WebP. L'image sera automatiquement redimensionnée à 1200x630px si nécessaire.
                        </p>
                    </div>
                    <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 text-sm">
                        <i class="fas fa-upload mr-1"></i>Uploader et remplacer l'image
                    </button>
                </form>
            </div>
            
            <!-- Chemin manuel (optionnel) -->
            <div class="border-t border-gray-200 pt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Ou spécifier un chemin manuel (depuis public/)
                </label>
                <form action="{{ route('admin.seo-automation.save-og-image') }}" method="POST" class="flex items-end gap-3">
                    @csrf
                    <div class="flex-1">
                        <input type="text" 
                               name="image_path" 
                               value="{{ $currentOgImage }}"
                               placeholder="images/og-blog.jpg"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <p class="text-xs text-gray-500 mt-1">
                            Exemple: <code>images/og-blog.jpg</code> ou <code>images/articles/default.jpg</code>
                        </p>
                    </div>
                    <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 text-sm">
                        <i class="fas fa-save mr-1"></i>Enregistrer le chemin
                    </button>
                </form>
            </div>
            
            <!-- Aperçu de l'image actuelle -->
            @if(file_exists(public_path($currentOgImage)))
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                    <p class="text-sm font-medium text-gray-700 mb-2">Image actuelle :</p>
                    <div class="relative inline-block">
                        <img src="{{ asset($currentOgImage) }}" 
                             alt="Image OG Blog" 
                             id="og_image_preview"
                             class="max-w-full h-auto rounded-lg shadow-md"
                             style="max-width: 600px; max-height: 315px; object-fit: contain;">
                        <div class="mt-2 text-xs text-gray-500">
                            <code>{{ $currentOgImage }}</code>
                            @php
                                $imagePath = public_path($currentOgImage);
                                if (file_exists($imagePath)) {
                                    $imageSize = getimagesize($imagePath);
                                    if ($imageSize) {
                                        echo " - {$imageSize[0]}x{$imageSize[1]}px";
                                    }
                                }
                            @endphp
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    <p class="text-sm text-yellow-800">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        L'image <code>{{ $currentOgImage }}</code> n'existe pas. Veuillez uploader une nouvelle image.
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Lien vers la gestion des mots-clés -->
    <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 mb-6">
        <div class="flex items-center justify-between">
                        <div>
                <h3 class="text-lg font-semibold text-indigo-900 mb-1">
                    <i class="fas fa-tags mr-2"></i>Gestion des Mots-clés
                </h3>
                <p class="text-sm text-indigo-700">
                    Gérez vos mots-clés SEO et leurs images associées sur une page dédiée pour une meilleure organisation.
                </p>
                        </div>
            <a href="{{ route('admin.keywords.index') }}" 
               class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 flex items-center whitespace-nowrap">
                <i class="fas fa-arrow-right mr-2"></i>
                Aller à la gestion des mots-clés
            </a>
        </div>
    </div>

@endsection

