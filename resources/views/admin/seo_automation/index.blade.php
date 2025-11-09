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
@endsection

