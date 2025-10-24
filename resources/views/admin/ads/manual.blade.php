@extends('layouts.admin')

@section('title', 'Créer une annonce manuellement')

@section('content')
<div class="max-w-7xl mx-auto py-10">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Créer une annonce manuellement</h1>
            <p class="text-gray-600 mt-2">Créez une annonce personnalisée avec votre propre contenu</p>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Manuel</p>
                    <p class="text-2xl font-semibold text-gray-900">Création</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulaire de création -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Créer une annonce manuellement</h2>
        </div>
        
        <form method="POST" action="{{ route('admin.ads.manual.store') }}" class="p-6">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Colonne gauche -->
                <div class="space-y-6">
                    <!-- Titre -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Titre de l'annonce</label>
                        <input type="text" name="title" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex: Couvreur à Paris - Devis gratuit" required>
                    </div>

                    <!-- Mot-clé -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mot-clé principal</label>
                        <input type="text" name="keyword" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex: couvreur paris" required>
                        <p class="text-sm text-gray-500 mt-1">Mot-clé principal pour le référencement</p>
                    </div>

                    <!-- Contenu -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Contenu (HTML)</label>
                        <textarea name="content" rows="8" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Contenu HTML de l'annonce..." required></textarea>
                        <p class="text-sm text-gray-500 mt-1">Vous pouvez utiliser du HTML (h1, h2, p, ul, li, etc.)</p>
                    </div>

                    <!-- Service (optionnel) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Service (optionnel)</label>
                        <select name="service_slug" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Aucun service</option>
                            @foreach($services as $service)
                                <option value="{{ $service['slug'] }}">{{ $service['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Statut -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                        <select name="status" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="draft">Brouillon</option>
                            <option value="published">Publié</option>
                            <option value="archived">Archivé</option>
                        </select>
                    </div>
                </div>

                <!-- Colonne droite -->
                <div class="space-y-6">
                    <!-- Sélection de la ville -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ville</label>
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ville sélectionnée</label>
                        <div id="cities-container" class="border border-gray-300 rounded-md p-4 max-h-64 overflow-y-auto">
                            <p class="text-gray-500 text-center py-4">Sélectionnez une ville à droite</p>
                        </div>
                        <input type="hidden" name="city_id" id="selected-city" value="">
                    </div>

                    <!-- SEO -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">Optimisation SEO</h3>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Titre SEO</label>
                            <input type="text" name="meta_title" maxlength="60" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Titre pour les moteurs de recherche">
                            <p class="text-sm text-gray-500 mt-1">Maximum 60 caractères</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description SEO</label>
                            <textarea name="meta_description" rows="3" maxlength="160" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Description pour les moteurs de recherche"></textarea>
                            <p class="text-sm text-gray-500 mt-1">Maximum 160 caractères</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mots-clés SEO</label>
                            <input type="text" name="meta_keywords" maxlength="500" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Mots-clés séparés par des virgules">
                            <p class="text-sm text-gray-500 mt-1">Séparez les mots-clés par des virgules</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="mt-8 flex justify-end space-x-4">
                <button type="button" id="preview-btn" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Prévisualiser
                </button>
                <button type="submit" id="create-btn" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    Créer l'annonce
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const regionFilter = document.getElementById('region-filter');
    const citiesContainer = document.getElementById('cities-container');
    const selectedCityInput = document.getElementById('selected-city');
    const showFavoritesBtn = document.getElementById('show-favorites');
    const showAllBtn = document.getElementById('show-all');
    const createBtn = document.getElementById('create-btn');
    
    let allCities = [];
    let filteredCities = [];
    let selectedCity = null;
    
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
        fetch('/admin/ads/manual/favorite-cities')
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
        fetch(`/admin/ads/manual/cities-by-region?region=${encodeURIComponent(region)}`)
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
            cityDiv.className = 'flex items-center justify-between p-2 border border-gray-200 rounded mb-2 cursor-pointer hover:bg-gray-50';
            cityDiv.innerHTML = `
                <div class="flex items-center">
                    <input type="radio" name="city_radio" id="city-${city.id}" value="${city.id}" class="city-radio mr-2">
                    <label for="city-${city.id}" class="text-sm cursor-pointer">
                        <span class="font-medium">${city.name}</span>
                        <span class="text-gray-500">(${city.postal_code})</span>
                        ${city.is_favorite ? '<span class="ml-2 text-yellow-500">⭐</span>' : ''}
                    </label>
                </div>
            `;
            
            citiesContainer.appendChild(cityDiv);
        });
        
        // Ajouter les event listeners pour les radios
        document.querySelectorAll('.city-radio').forEach(radio => {
            radio.addEventListener('change', updateSelectedCity);
        });
    }
    
    // Fonction pour mettre à jour la ville sélectionnée
    function updateSelectedCity() {
        const radio = document.querySelector('.city-radio:checked');
        if (radio) {
            selectedCity = radio.value;
            selectedCityInput.value = selectedCity;
            createBtn.disabled = false;
        } else {
            selectedCity = null;
            selectedCityInput.value = '';
            createBtn.disabled = true;
        }
    }
    
    // Charger les villes favorites par défaut
    loadFavoriteCities();
});
</script>
@endsection
