@extends('layouts.admin')

@section('title', 'Génération d\'annonces par Mots-clés et Villes')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-key mr-2 text-purple-600"></i>
                Génération d'annonces par Mots-clés et Villes
            </h1>
            <a href="{{ route('admin.ads.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                <i class="fas fa-arrow-left mr-2"></i>Retour
            </a>
        </div>

        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>Limitation :</strong> Vous pouvez sélectionner un maximum de <strong>2 villes</strong> pour la génération d'annonces.
                    </p>
                </div>
            </div>
        </div>

        <form id="keywordCitiesForm" class="space-y-6">
            @csrf
            
            <!-- Mot-clé -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Mot-clé principal</label>
                <div class="space-y-3">
                    <!-- Mots-clés prédéfinis -->
                    <div>
                        <label class="block text-xs text-gray-500 mb-2">Mots-clés prédéfinis :</label>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-2">
                            @foreach($keywords as $keyword)
                                <button type="button" class="keyword-btn bg-gray-100 hover:bg-purple-100 text-gray-700 hover:text-purple-700 px-3 py-2 rounded text-sm border border-gray-300 hover:border-purple-300 transition-colors" data-keyword="{{ $keyword }}">
                                    {{ $keyword }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Champ mot-clé personnalisé -->
                    <div>
                        <label class="block text-xs text-gray-500 mb-2">Ou saisir un mot-clé personnalisé :</label>
                        <input type="text" name="keyword" id="keywordInput" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Ex: couvreur, toiture, façade..." required>
                    </div>
                </div>
            </div>

            <!-- Sélection des villes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Villes <span class="text-red-500">*</span>
                    <span class="text-sm text-gray-500">(Maximum 2 villes)</span>
                </label>
                
                <!-- Filtres -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Région</label>
                        <select id="regionFilter" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                            <option value="">Toutes les régions</option>
                            @foreach($cities->groupBy('region') as $region => $regionCities)
                                @if($region)
                                    <option value="{{ $region }}">{{ $region }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Département</label>
                        <select id="departmentFilter" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                            <option value="">Tous les départements</option>
                            @foreach($cities->groupBy('department') as $department => $deptCities)
                                @if($department)
                                    <option value="{{ $department }}">{{ $department }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Recherche</label>
                        <input type="text" id="citySearch" class="w-full border border-gray-300 rounded px-2 py-1 text-sm" placeholder="Rechercher une ville...">
                    </div>
                </div>

                <!-- Liste des villes -->
                <div class="border border-gray-300 rounded-md max-h-60 overflow-y-auto">
                    <div class="p-3 bg-gray-50 border-b">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">
                                Villes sélectionnées : <span id="selectedCount" class="text-purple-600 font-bold">0</span>/2
                            </span>
                            <button type="button" id="clearSelection" class="text-xs text-red-600 hover:text-red-800">Tout désélectionner</button>
                        </div>
                    </div>
                    <div class="p-2">
                        @foreach($cities as $city)
                            <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer city-option" data-region="{{ $city->region }}" data-department="{{ $city->department }}" data-name="{{ strtolower($city->name) }}">
                                <input type="checkbox" name="city_ids[]" value="{{ $city->id }}" class="city-checkbox mr-3 text-purple-600 focus:ring-purple-500">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">{{ $city->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $city->postal_code }} - {{ $city->department }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Prompt IA personnalisé -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Prompt IA personnalisé (optionnel)</label>
                <textarea name="ai_prompt" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Instructions spécifiques pour l'IA..."></textarea>
                <p class="text-sm text-gray-500 mt-1">Laissez vide pour utiliser le prompt par défaut</p>
            </div>

            <!-- Taille de lot -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Taille de lot</label>
                <input type="number" name="batch_size" value="20" min="1" max="50" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                <p class="text-sm text-gray-500 mt-1">Nombre d'annonces à traiter par lot (1-50)</p>
            </div>

            <!-- Bouton de génération -->
            <div class="flex justify-end">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-lg transition-colors duration-200 flex items-center">
                    <i class="fas fa-magic mr-2"></i>
                    Générer les annonces
                </button>
            </div>
        </form>

        <!-- Zone de résultats -->
        <div id="results" class="mt-8 hidden">
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Résultats de la génération</h3>
                <div id="resultsContent" class="text-center">
                    <!-- Le contenu sera rempli par JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const keywordInput = document.getElementById('keywordInput');
    const keywordBtns = document.querySelectorAll('.keyword-btn');
    const cityCheckboxes = document.querySelectorAll('.city-checkbox');
    const selectedCount = document.getElementById('selectedCount');
    const clearSelection = document.getElementById('clearSelection');
    const regionFilter = document.getElementById('regionFilter');
    const departmentFilter = document.getElementById('departmentFilter');
    const citySearch = document.getElementById('citySearch');
    const cityOptions = document.querySelectorAll('.city-option');
    const form = document.getElementById('keywordCitiesForm');
    const results = document.getElementById('results');
    const resultsContent = document.getElementById('resultsContent');

    // Gestion des mots-clés prédéfinis
    keywordBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const keyword = this.dataset.keyword;
            keywordInput.value = keyword;
            
            // Mise à jour visuelle
            keywordBtns.forEach(b => b.classList.remove('bg-purple-100', 'text-purple-700', 'border-purple-300'));
            this.classList.add('bg-purple-100', 'text-purple-700', 'border-purple-300');
        });
    });

    // Gestion de la sélection des villes
    function updateSelectedCount() {
        const selected = document.querySelectorAll('.city-checkbox:checked').length;
        selectedCount.textContent = selected;
        
        // Désactiver les checkboxes si 2 villes sont sélectionnées
        cityCheckboxes.forEach(checkbox => {
            if (selected >= 2 && !checkbox.checked) {
                checkbox.disabled = true;
                checkbox.closest('.city-option').classList.add('opacity-50');
            } else {
                checkbox.disabled = false;
                checkbox.closest('.city-option').classList.remove('opacity-50');
            }
        });
    }

    cityCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });

    // Tout désélectionner
    clearSelection.addEventListener('click', function() {
        cityCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        updateSelectedCount();
    });

    // Filtres des villes
    function filterCities() {
        const region = regionFilter.value.toLowerCase();
        const department = departmentFilter.value.toLowerCase();
        const search = citySearch.value.toLowerCase();

        cityOptions.forEach(option => {
            const optionRegion = (option.dataset.region || '').toLowerCase();
            const optionDepartment = (option.dataset.department || '').toLowerCase();
            const optionName = option.dataset.name;

            const matchesRegion = !region || optionRegion.includes(region);
            const matchesDepartment = !department || optionDepartment.includes(department);
            const matchesSearch = !search || optionName.includes(search);

            if (matchesRegion && matchesDepartment && matchesSearch) {
                option.style.display = 'flex';
            } else {
                option.style.display = 'none';
            }
        });
    }

    regionFilter.addEventListener('change', filterCities);
    departmentFilter.addEventListener('change', filterCities);
    citySearch.addEventListener('input', filterCities);

    // Soumission du formulaire
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const selectedCities = Array.from(cityCheckboxes).filter(cb => cb.checked);
        
        // Validation
        if (!keywordInput.value.trim()) {
            alert('Veuillez saisir un mot-clé');
            return;
        }
        
        if (selectedCities.length === 0) {
            alert('Veuillez sélectionner au moins une ville');
            return;
        }
        
        if (selectedCities.length > 2) {
            alert('Maximum 2 villes autorisées');
            return;
        }

        // Afficher les résultats
        results.classList.remove('hidden');
        resultsContent.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin text-2xl text-purple-600"></i><p class="mt-2">Génération des annonces en cours...</p></div>';
        
        // Envoyer la requête
        try {
            const response = await fetch('{{ route("admin.ads.generate.keyword-cities") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            // Si la réponse est une redirection (status 302), suivre la redirection
            if (response.redirected || response.status === 302) {
                window.location.href = response.url || '{{ route("admin.ads.index") }}';
                return;
            }
            
            // Si c'est une réponse JSON (erreur), l'afficher
            const data = await response.json();
            
            if (data.success) {
                resultsContent.innerHTML = `
                    <div class="text-green-600">
                        <i class="fas fa-check-circle text-2xl mb-2"></i>
                        <p class="font-semibold">Annonces générées avec succès !</p>
                        <p class="text-sm mt-1">${data.message}</p>
                        <p class="text-sm">Annonces créées : ${data.created || 0}</p>
                        <p class="text-sm">Annonces ignorées : ${data.skipped || 0}</p>
                    </div>
                `;
            } else {
                resultsContent.innerHTML = `
                    <div class="text-red-600">
                        <i class="fas fa-exclamation-circle text-2xl mb-2"></i>
                        <p class="font-semibold">Erreur lors de la génération</p>
                        <p class="text-sm mt-1">${data.message || 'Une erreur inattendue s\'est produite'}</p>
                    </div>
                `;
            }
        } catch (error) {
            resultsContent.innerHTML = `
                <div class="text-red-600">
                    <i class="fas fa-exclamation-circle text-2xl mb-2"></i>
                    <p class="font-semibold">Erreur de connexion</p>
                    <p class="text-sm mt-1">${error.message}</p>
                </div>
            `;
        }
    });

    // Initialisation
    updateSelectedCount();
});
</script>
@endsection