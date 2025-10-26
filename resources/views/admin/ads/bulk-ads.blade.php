@extends('layouts.admin')

@section('title', 'Génération d\'Annonces en Masse')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- En-tête -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Génération d'Annonces en Masse</h1>
        <p class="text-gray-600">Créez automatiquement des annonces pour un service sur toutes les villes avec un template personnalisé</p>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-tools text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Services Disponibles</p>
                    <p class="text-2xl font-bold text-gray-900">{{ count($services) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-map-marker-alt text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Villes Total</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $cities->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-star text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Villes Favorites</p>
                    <p class="text-2xl font-bold text-gray-900">{{ count($favoriteCities) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulaire de génération -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Configuration de la Génération en Masse</h2>
            <p class="text-sm text-gray-600 mt-1">Ce système crée automatiquement des annonces pour toutes les villes avec un template personnalisé</p>
        </div>
        
        <form id="bulk-ads-form" class="p-6">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Colonne gauche -->
                <div class="space-y-6">
                    <!-- Service -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Service</label>
                        <select name="service_slug" id="service-select" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Sélectionner un service</option>
                            @foreach($services as $service)
                                <option value="{{ $service['slug'] }}">{{ $service['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Scope des villes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Portée des Villes</label>
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="radio" name="city_scope" value="favorites" checked class="mr-3 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-gray-700">Villes favorites uniquement ({{ count($favoriteCities) }} villes)</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="city_scope" value="all" class="mr-3 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-gray-700">Toutes les villes ({{ $cities->count() }} villes)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Taille du batch -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Taille du Batch</label>
                        <select name="batch_size" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="5">5 annonces par batch</option>
                            <option value="10" selected>10 annonces par batch</option>
                            <option value="20">20 annonces par batch</option>
                            <option value="50">50 annonces par batch</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Plus le batch est petit, plus la génération est stable</p>
                    </div>
                </div>

                <!-- Colonne droite -->
                <div class="space-y-6">
                    <!-- Prompt IA personnalisé -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Instructions IA Personnalisées (Optionnel)</label>
                        <textarea name="ai_prompt" rows="4" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ajoutez des instructions spécifiques pour personnaliser le contenu généré..."></textarea>
                        <p class="text-xs text-gray-500 mt-1">Ces instructions seront appliquées au template de base</p>
                    </div>

                    <!-- Aperçu du template -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Aperçu du Template</label>
                        <div class="bg-gray-50 border border-gray-200 rounded-md p-4 text-sm">
                            <p class="font-medium text-gray-700 mb-2">Structure du template :</p>
                            <ul class="space-y-1 text-gray-600">
                                <li>• Introduction personnalisée par ville</li>
                                <li>• Section "Notre Engagement Qualité"</li>
                                <li>• 8 prestations avec icônes Font Awesome</li>
                                <li>• Section "Pourquoi Choisir Notre Entreprise"</li>
                                <li>• Section "Notre Expertise Locale"</li>
                                <li>• Section "Informations Pratiques"</li>
                            </ul>
                            <p class="text-xs text-gray-500 mt-2">Le template s'adapte automatiquement à chaque ville</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bouton de génération -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        <p id="estimated-count">Sélectionnez un service pour voir le nombre d'annonces à créer</p>
                    </div>
                    <button type="submit" id="generate-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-3 rounded-lg transition-colors duration-200 flex items-center">
                        <i class="fas fa-magic mr-2"></i>
                        Créer Annonces en Masse
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Zone de progression -->
    <div id="progress-section" class="mt-8 hidden">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Progression de la Génération</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Progression</span>
                    <span id="progress-text" class="text-sm font-medium text-gray-900">0%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
                <div id="progress-details" class="text-sm text-gray-600">
                    <p>Préparation de la génération...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Résultats -->
    <div id="results-section" class="mt-8 hidden">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Résultats de la Génération</h3>
            <div id="results-content" class="space-y-2">
                <!-- Les résultats seront affichés ici -->
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('bulk-ads-form');
    const serviceSelect = document.getElementById('service-select');
    const cityScopeRadios = document.querySelectorAll('input[name="city_scope"]');
    const estimatedCount = document.getElementById('estimated-count');
    const generateBtn = document.getElementById('generate-btn');
    const progressSection = document.getElementById('progress-section');
    const resultsSection = document.getElementById('results-section');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const progressDetails = document.getElementById('progress-details');
    const resultsContent = document.getElementById('results-content');

    // Mettre à jour le nombre estimé d'annonces
    function updateEstimatedCount() {
        const serviceSlug = serviceSelect.value;
        const cityScope = document.querySelector('input[name="city_scope"]:checked').value;
        
        if (!serviceSlug) {
            estimatedCount.textContent = 'Sélectionnez un service pour voir le nombre d\'annonces à créer';
            return;
        }

        const cityCount = cityScope === 'all' ? {{ $cities->count() }} : {{ count($favoriteCities) }};
        estimatedCount.textContent = `Environ ${cityCount} annonces seront créées pour ce service`;
    }

    // Événements
    serviceSelect.addEventListener('change', updateEstimatedCount);
    cityScopeRadios.forEach(radio => {
        radio.addEventListener('change', updateEstimatedCount);
    });

    // Soumission du formulaire
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const serviceSlug = formData.get('service_slug');
        const cityScope = formData.get('city_scope');
        const batchSize = formData.get('batch_size');
        const aiPrompt = formData.get('ai_prompt');

        if (!serviceSlug) {
            alert('Veuillez sélectionner un service');
            return;
        }

        // Afficher la progression
        progressSection.classList.remove('hidden');
        resultsSection.classList.add('hidden');
        generateBtn.disabled = true;
        generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Génération en cours...';

        // Simuler la progression
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += Math.random() * 10;
            if (progress > 90) progress = 90;
            
            progressBar.style.width = progress + '%';
            progressText.textContent = Math.round(progress) + '%';
            progressDetails.textContent = `Génération des annonces... ${Math.round(progress)}%`;
        }, 500);

        // Appel AJAX
        fetch('{{ route("admin.ads.bulk-ads.generate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                service_slug: serviceSlug,
                include_all_cities: cityScope === 'all',
                batch_size: parseInt(batchSize),
                ai_prompt: aiPrompt
            })
        })
        .then(response => response.json())
        .then(data => {
            clearInterval(progressInterval);
            progressBar.style.width = '100%';
            progressText.textContent = '100%';
            
            if (data.success) {
                progressDetails.textContent = 'Génération terminée avec succès !';
                
                // Afficher les résultats
                resultsSection.classList.remove('hidden');
                resultsContent.innerHTML = `
                    <div class="bg-green-50 border border-green-200 rounded-md p-4">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-600 mr-3"></i>
                            <div>
                                <h4 class="font-medium text-green-800">Génération réussie !</h4>
                                <p class="text-sm text-green-700 mt-1">${data.message}</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                            <div class="flex items-center">
                                <i class="fas fa-plus-circle text-blue-600 mr-3"></i>
                                <div>
                                    <p class="font-medium text-blue-800">Annonces créées</p>
                                    <p class="text-2xl font-bold text-blue-900">${data.data.created_ads}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                            <div class="flex items-center">
                                <i class="fas fa-skip-forward text-yellow-600 mr-3"></i>
                                <div>
                                    <p class="font-medium text-yellow-800">Annonces ignorées</p>
                                    <p class="text-2xl font-bold text-yellow-900">${data.data.skipped_ads}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-red-50 border border-red-200 rounded-md p-4">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-red-600 mr-3"></i>
                                <div>
                                    <p class="font-medium text-red-800">Erreurs</p>
                                    <p class="text-2xl font-bold text-red-900">${data.data.errors_count}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                progressDetails.textContent = 'Erreur lors de la génération';
                resultsSection.classList.remove('hidden');
                resultsContent.innerHTML = `
                    <div class="bg-red-50 border border-red-200 rounded-md p-4">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-600 mr-3"></i>
                            <div>
                                <h4 class="font-medium text-red-800">Erreur</h4>
                                <p class="text-sm text-red-700 mt-1">${data.message}</p>
                            </div>
                        </div>
                    </div>
                `;
            }
        })
        .catch(error => {
            clearInterval(progressInterval);
            progressDetails.textContent = 'Erreur lors de la génération';
            resultsSection.classList.remove('hidden');
            resultsContent.innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-600 mr-3"></i>
                        <div>
                            <h4 class="font-medium text-red-800">Erreur</h4>
                            <p class="text-sm text-red-700 mt-1">Une erreur est survenue lors de la génération</p>
                        </div>
                    </div>
                </div>
            `;
        })
        .finally(() => {
            generateBtn.disabled = false;
            generateBtn.innerHTML = '<i class="fas fa-magic mr-2"></i>Créer Annonces en Masse';
        });
    });
});
</script>
@endsection
