@extends('layouts.admin')

@section('title', 'Génération d\'annonces par Service et Villes')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-magic mr-2 text-blue-600"></i>
                Génération d'annonces par Service et Villes
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

        <form id="serviceCitiesForm" class="space-y-6">
            @csrf
            
            <!-- Sélection du service -->
                    <div>
                <label for="service_id" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-tools mr-1"></i>Service
                </label>
                <select id="service_id" name="service_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Sélectionner un service</option>
                            @foreach($services as $service)
                        <option value="{{ $service->id }}" data-slug="{{ $service->slug }}">
                            {{ $service->title }}
                        </option>
                            @endforeach
                        </select>
                    </div>

            <!-- Sélection des villes (limité à 2) -->
                    <div>
                <label for="cities" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-map-marker-alt mr-1"></i>Villes (Maximum 2)
                </label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($cities as $city)
                        <label class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="cities[]" value="{{ $city->id }}" class="city-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" data-city-name="{{ $city->name }}">
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">{{ $city->name }}</div>
                                <div class="text-xs text-gray-500">{{ $city->postal_code }} - {{ $city->department }}</div>
                            </div>
                        </label>
                                    @endforeach
                            </div>
                <div class="mt-2 text-sm text-gray-600">
                    <span id="selected-count">0</span> ville(s) sélectionnée(s) sur 2 maximum
                        </div>
                    </div>

            <!-- Options de génération -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-3">
                    <i class="fas fa-cog mr-2"></i>Options de génération
                </h3>
                
                <div class="space-y-3">
                    <label class="flex items-center">
                        <input type="checkbox" name="generate_meta" value="1" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Générer les métadonnées SEO</span>
                    </label>
                    
                    <label class="flex items-center">
                        <input type="checkbox" name="generate_content" value="1" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Générer le contenu HTML</span>
                    </label>
                    
                    <label class="flex items-center">
                        <input type="checkbox" name="publish_immediately" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Publier immédiatement</span>
                    </label>
                </div>
            </div>

            <!-- Bouton de génération -->
            <div class="flex justify-end">
                <button type="submit" id="generateBtn" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-magic mr-2"></i>
                    Générer les annonces
                </button>
            </div>
        </form>

        <!-- Zone de résultats -->
        <div id="results" class="mt-8 hidden">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-check-circle mr-2 text-green-600"></i>Résultats
            </h3>
            <div id="results-content" class="bg-gray-50 p-4 rounded-lg"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cityCheckboxes = document.querySelectorAll('.city-checkbox');
    const selectedCountSpan = document.getElementById('selected-count');
    const generateBtn = document.getElementById('generateBtn');
    const form = document.getElementById('serviceCitiesForm');
    const results = document.getElementById('results');
    const resultsContent = document.getElementById('results-content');

    // Limiter la sélection à 2 villes maximum
    cityCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checkedBoxes = document.querySelectorAll('.city-checkbox:checked');
            
            if (checkedBoxes.length > 2) {
                this.checked = false;
                alert('Vous ne pouvez sélectionner que 2 villes maximum.');
                return;
            }
            
            updateSelectedCount();
        });
    });

    function updateSelectedCount() {
        const checkedBoxes = document.querySelectorAll('.city-checkbox:checked');
        selectedCountSpan.textContent = checkedBoxes.length;
        
        // Activer/désactiver le bouton selon le nombre de sélections
        generateBtn.disabled = checkedBoxes.length === 0;
    }

    // Gestion du formulaire
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const checkedBoxes = document.querySelectorAll('.city-checkbox:checked');
        
        if (checkedBoxes.length === 0) {
            alert('Veuillez sélectionner au moins une ville.');
            return;
        }
        
        if (checkedBoxes.length > 2) {
            alert('Vous ne pouvez sélectionner que 2 villes maximum.');
            return;
        }
        
        // Désactiver le bouton pendant la génération
        generateBtn.disabled = true;
        generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Génération en cours...';
        
        // Afficher les résultats
        results.classList.remove('hidden');
        resultsContent.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin text-2xl text-blue-600"></i><p class="mt-2">Génération des annonces en cours...</p></div>';
        
        // Envoyer la requête
        fetch('{{ route("admin.ads.generate.service-cities") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultsContent.innerHTML = `
                    <div class="text-green-600">
                        <i class="fas fa-check-circle text-2xl mb-2"></i>
                        <p class="font-medium">${data.message}</p>
                        <p class="text-sm mt-1">${data.count} annonce(s) générée(s) avec succès.</p>
                    </div>
                `;
            } else {
                resultsContent.innerHTML = `
                    <div class="text-red-600">
                        <i class="fas fa-exclamation-circle text-2xl mb-2"></i>
                        <p class="font-medium">Erreur lors de la génération</p>
                        <p class="text-sm mt-1">${data.message}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            resultsContent.innerHTML = `
                <div class="text-red-600">
                    <i class="fas fa-exclamation-circle text-2xl mb-2"></i>
                    <p class="font-medium">Erreur lors de la génération</p>
                    <p class="text-sm mt-1">Une erreur est survenue. Veuillez réessayer.</p>
                </div>
            `;
        })
        .finally(() => {
            // Réactiver le bouton
            generateBtn.disabled = false;
            generateBtn.innerHTML = '<i class="fas fa-magic mr-2"></i>Générer les annonces';
        });
    });
});
</script>
@endsection