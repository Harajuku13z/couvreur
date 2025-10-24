@extends('layouts.admin')

@section('title', 'Mot-clé + Villes')

@section('content')
<div class="max-w-7xl mx-auto py-10">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Mot-clé + Villes</h1>
            <p class="text-gray-600 mt-2">Générez des annonces basées sur des mots-clés spécifiques dans plusieurs villes</p>
        </div>
        <div class="flex space-x-4">
            <a href="/admin/ads" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                Retour aux annonces
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800">{{ session('success') }}</h3>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293-1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">{{ session('error') }}</h3>
                </div>
            </div>
        </div>
    @endif

    @if(session('errors'))
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <h3 class="text-sm font-medium text-yellow-800 mb-2">Erreurs rencontrées :</h3>
            <ul class="text-sm text-yellow-700 list-disc list-inside">
                @foreach(session('errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Villes</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $totalCities }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Villes Favorites</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $favoriteCount }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Annonces</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $totalAds }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Mots-clés</p>
                    <p class="text-2xl font-semibold text-gray-900">SEO</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulaire de génération -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Configuration Mot-clé + Villes</h2>
        </div>
        
        <form method="POST" action="{{ route('admin.ads.keyword-cities.generate') }}" class="p-6">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Colonne gauche -->
                <div class="space-y-6">
                    <!-- Mots-clés -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mots-clés SEO</label>
                        <input type="text" name="keywords" id="keywords-input" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex: couvreur, toiture, réparation, devis" required>
                        <p class="text-sm text-gray-500 mt-1">Séparez les mots-clés par des virgules</p>
                    </div>

                    <!-- Nombre d'annonces par ville -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Annonces par ville</label>
                        <input type="number" name="count_per_city" value="1" min="1" max="10" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-sm text-gray-500 mt-1">Nombre d'annonces à générer pour chaque ville sélectionnée</p>
                    </div>
                </div>

                <!-- Colonne droite -->
                <div class="space-y-6">
                    <!-- Filtrage des villes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filtrer les villes</label>
                        <div class="space-y-3">
                            <div class="flex space-x-4">
                                <button type="button" id="show-favorites" class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
                                    Villes favorites
                                </button>
                                <button type="button" id="show-all" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                                    Toutes les villes
                                </button>
                            </div>
                            
                            <div>
                                <select id="region-filter" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Toutes les régions</option>
                                    @foreach($regions as $region)
                                        <option value="{{ $region }}">{{ $region }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Sélection des villes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Villes sélectionnées</label>
                        <div id="cities-container" class="border border-gray-300 rounded-md p-4 max-h-64 overflow-y-auto">
                            <p class="text-gray-500 text-center py-4">Sélectionnez des villes à droite</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="mt-8 flex justify-end space-x-4">
                <button type="submit" id="generate-btn" class="px-6 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span id="generate-text">Générer les annonces</span>
                    <span id="generate-loading" style="display: none;">Génération en cours...</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const keywordsInput = document.getElementById('keywords-input');
    const regionFilter = document.getElementById('region-filter');
    const citiesContainer = document.getElementById('cities-container');
    const selectedCitiesInput = document.getElementById('selected-cities');
    const showFavoritesBtn = document.getElementById('show-favorites');
    const showAllBtn = document.getElementById('show-all');
    const generateBtn = document.getElementById('generate-btn');
    const generateText = document.getElementById('generate-text');
    const generateLoading = document.getElementById('generate-loading');
    
    let allCities = [];
    let filteredCities = [];
    let selectedCities = [];
    
    // Charger les villes favorites
    showFavoritesBtn.addEventListener('click', function() {
        loadFavoriteCities();
    });
    
    // Charger toutes les villes
    showAllBtn.addEventListener('click', function() {
        loadAllCities();
    });
    
    // Filtrage par région
    regionFilter.addEventListener('change', function() {
        if (this.value) {
            loadCitiesByRegion(this.value);
        } else {
            displayCities(allCities);
        }
    });
    
    // Fonction pour charger les villes favorites
    function loadFavoriteCities() {
        fetch('/admin/ads/keyword-cities/favorite-cities')
            .then(response => response.json())
            .then(data => {
                allCities = data.cities;
                filteredCities = data.cities;
                displayCities(data.cities);
            })
            .catch(error => {
                console.error('Error loading favorite cities:', error);
            });
    }
    
    // Fonction pour charger toutes les villes
    function loadAllCities() {
        // Pour l'instant, on charge les favorites par défaut
        // TODO: Implémenter le chargement de toutes les villes
        loadFavoriteCities();
    }
    
    // Fonction pour charger les villes par région
    function loadCitiesByRegion(region) {
        fetch(`/admin/ads/keyword-cities/cities-by-region?region=${encodeURIComponent(region)}`)
            .then(response => response.json())
            .then(data => {
                filteredCities = data.cities;
                displayCities(data.cities);
            })
            .catch(error => {
                console.error('Error loading cities by region:', error);
            });
    }
    
    // Fonction pour afficher les villes
    function displayCities(cities) {
        citiesContainer.innerHTML = '';
        
        if (cities.length === 0) {
            citiesContainer.innerHTML = '<p class="text-gray-500 text-center py-4">Aucune ville trouvée</p>';
            return;
        }
        
        cities.forEach(city => {
            const cityDiv = document.createElement('div');
            cityDiv.className = 'flex items-center justify-between p-2 border border-gray-200 rounded mb-2';
            cityDiv.innerHTML = `
                <div class="flex items-center">
                    <input type="checkbox" id="city-${city.id}" value="${city.id}" class="city-checkbox mr-2">
                    <label for="city-${city.id}" class="text-sm">
                        <span class="font-medium">${city.name}</span>
                        <span class="text-gray-500">(${city.postal_code})</span>
                        ${city.is_favorite ? '<span class="ml-2 text-yellow-500">⭐</span>' : ''}
                    </label>
                </div>
            `;
            
            citiesContainer.appendChild(cityDiv);
        });
        
        // Ajouter les event listeners pour les checkboxes
        document.querySelectorAll('.city-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedCities);
        });
    }
    
    // Fonction pour mettre à jour les villes sélectionnées
    function updateSelectedCities() {
        const checkboxes = document.querySelectorAll('.city-checkbox:checked');
        selectedCities = Array.from(checkboxes).map(cb => cb.value);
        
        // Mettre à jour le bouton de génération
        generateBtn.disabled = selectedCities.length === 0;
    }
    
    // Gestion du formulaire
    document.querySelector('form').addEventListener('submit', function(e) {
        // Mettre à jour les villes sélectionnées avant la soumission
        updateSelectedCities();
        
        if (selectedCities.length === 0) {
            e.preventDefault();
            alert('Veuillez sélectionner au moins une ville');
            return;
        }
        
        if (!keywordsInput.value.trim()) {
            e.preventDefault();
            alert('Veuillez saisir des mots-clés');
            return;
        }
        
        // Créer les inputs cachés pour les villes sélectionnées
        selectedCities.forEach(cityId => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'cities[]';
            input.value = cityId;
            this.appendChild(input);
        });
        
        // Afficher le loading
        generateBtn.disabled = true;
        generateText.style.display = 'none';
        generateLoading.style.display = 'inline';
    });
    
    // Charger les villes favorites par défaut
    loadFavoriteCities();
});
</script>
@endsection
