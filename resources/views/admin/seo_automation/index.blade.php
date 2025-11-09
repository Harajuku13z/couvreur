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
        <button id="testConnectionsBtn" 
                class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 flex items-center">
            <i class="fas fa-vial mr-2"></i>
            Tester les connexions
        </button>
    </div>

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

    <!-- Formulaire de lancement -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-play-circle mr-2 text-blue-600"></i>Lancer la génération d'articles
        </h2>
        
        <form action="{{ route('admin.seo-automation.run') }}" method="POST" class="space-y-4">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Nombre d'articles -->
                <div>
                    <label for="number_of_articles" class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre d'articles à créer <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           id="number_of_articles" 
                           name="number_of_articles" 
                           value="{{ old('number_of_articles', 1) }}"
                           min="1" 
                           max="50" 
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Entre 1 et 50 articles par ville</p>
                </div>

                <!-- Sélection de service -->
                <div>
                    <label for="service_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Service (optionnel)
                    </label>
                    <select id="service_id" 
                            name="service_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Aucun service --</option>
                        @foreach($services as $service)
                            <option value="{{ $service['id'] ?? '' }}" {{ old('service_id') == ($service['id'] ?? '') ? 'selected' : '' }}>
                                {{ $service['name'] ?? 'Service sans nom' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Sélectionner un service pour utiliser son nom comme mot-clé</p>
                </div>
            </div>

            <!-- Mot-clé personnalisé -->
            <div>
                <label for="keyword" class="block text-sm font-medium text-gray-700 mb-2">
                    Mot-clé personnalisé (optionnel)
                </label>
                <input type="text" 
                       id="keyword" 
                       name="keyword" 
                       value="{{ old('keyword') }}"
                       placeholder="Ex: couvreur, toiture, rénovation..."
                       maxlength="255"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">Si rempli, ce mot-clé sera utilisé au lieu des tendances. Priorité sur le service.</p>
            </div>

            <!-- Sélection des villes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Villes à cibler
                </label>
                <div class="border border-gray-300 rounded-lg p-3 max-h-48 overflow-y-auto bg-gray-50">
                    @if($favoriteCities->isEmpty())
                        <p class="text-sm text-gray-500 italic">Aucune ville favorite configurée. Allez dans <strong>Villes</strong> pour en marquer comme favorites.</p>
                    @else
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       id="select_all_cities" 
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                       onchange="document.querySelectorAll('input[name=\"city_ids[]\"]').forEach(cb => cb.checked = this.checked)">
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
                <p class="text-xs text-gray-500 mt-1">Si aucune ville n'est sélectionnée, toutes les villes favorites seront utilisées</p>
            </div>

            <!-- Bouton de soumission -->
            <div class="flex justify-end">
                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 flex items-center">
                    <i class="fas fa-rocket mr-2"></i>
                    Lancer la génération
                </button>
            </div>
        </form>
    </div>

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
                        @if($log->article_url)
                            <a href="{{ $log->article_url }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">
                                <i class="fas fa-external-link-alt mr-1"></i> Voir
                            </a>
                        @else
                            <span class="text-gray-400 text-sm">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $log->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        @if($log->status === 'failed')
                            <form action="{{ route('admin.seo-automation.retry', $log) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-redo mr-1"></i> Relancer
                                </button>
                            </form>
                        @endif
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
            
            @if($log->article_url)
                <a href="{{ $log->article_url }}" target="_blank" class="inline-block text-blue-600 hover:text-blue-800 text-sm mb-2">
                    <i class="fas fa-external-link-alt mr-1"></i> Voir l'article
                </a>
            @endif
            
            @if($log->status === 'failed')
                <form action="{{ route('admin.seo-automation.retry', $log) }}" method="POST" class="mt-2">
                    @csrf
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
                        <i class="fas fa-redo mr-1"></i> Relancer
                    </button>
                </form>
            @endif
            
            @if($log->error_message)
                <div class="mt-2 text-xs text-red-600 bg-red-50 p-2 rounded">
                    <i class="fas fa-exclamation-triangle mr-1"></i> {{ $log->error_message }}
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
    
    // Fonction pour tester une API individuelle
    function testApi(apiName, button) {
        const resultDiv = document.getElementById(apiName + '_result');
        const originalText = button.innerHTML;
        
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Test...';
        resultDiv.innerHTML = '<div class="text-blue-600"><i class="fas fa-spinner fa-spin mr-1"></i>Test en cours...</div>';
        
        fetch('{{ route("admin.seo-automation.test-api") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ api: apiName })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || 'Erreur HTTP ' + response.status);
                });
            }
            return response.json();
        })
        .then(data => {
            button.disabled = false;
            button.innerHTML = originalText;
            
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
            html += '<span>' + data.message + '</span>';
            if (data.data) {
                html += '<div class="mt-1 text-xs opacity-75">';
                if (Array.isArray(data.data)) {
                    html += data.data.join(', ');
                } else {
                    html += JSON.stringify(data.data);
                }
                html += '</div>';
            }
            html += '</div>';
            
            resultDiv.innerHTML = html;
        })
        .catch(error => {
            button.disabled = false;
            button.innerHTML = originalText;
            console.error('Erreur test API:', error);
            resultDiv.innerHTML = '<div class="bg-red-50 border border-red-400 text-red-700 rounded-lg p-2 mt-2"><i class="fas fa-times-circle mr-1"></i>Erreur: ' + (error.message || 'Erreur inconnue') + '</div>';
        });
    }
});
</script>
@endsection

