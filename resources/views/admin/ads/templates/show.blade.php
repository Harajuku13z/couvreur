@extends('layouts.admin')

@section('title', 'Template: ' . $template->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- En-tête -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <div class="flex items-center mb-2">
                <a href="{{ route('admin.ads.templates.index') }}" class="text-blue-600 hover:text-blue-800 mr-4">
                    <i class="fas fa-arrow-left"></i> Retour aux templates
                </a>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $template->name }}</h1>
            <p class="text-gray-600 mt-2">{{ $template->short_description }}</p>
        </div>
        <div class="flex space-x-4">
            <a href="{{ route('admin.ads.templates.edit', $template->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                <i class="fas fa-edit"></i>
                <span>Personnaliser</span>
            </a>
            <button onclick="generateAdsFromTemplate({{ $template->id }})" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                <i class="fas fa-plus-circle"></i>
                <span>Générer des Annonces</span>
            </button>
            @if($template->is_active)
                <button onclick="toggleTemplateStatus({{ $template->id }}, false)" class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-3 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                    <i class="fas fa-pause"></i>
                    <span>Désactiver</span>
                </button>
            @else
                <button onclick="toggleTemplateStatus({{ $template->id }}, true)" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                    <i class="fas fa-play"></i>
                    <span>Activer</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Informations du template -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Informations générales -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations du Template</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Service</label>
                        <p class="text-gray-900">{{ $template->service_name }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Icône</label>
                        <p class="text-gray-900">
                            <i class="{{ $template->icon }} mr-2"></i>
                            {{ $template->icon }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Statut</label>
                        <p>
                            @if($template->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Actif
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i>
                                    Inactif
                                </span>
                            @endif
                        </p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Utilisation</label>
                        <p class="text-gray-900">{{ $template->usage_count }} fois</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Annonces créées</label>
                        <p class="text-gray-900">{{ $template->ads->count() }} annonces</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Créé le</label>
                        <p class="text-gray-900">{{ $template->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aperçu du contenu -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Aperçu du Contenu</h3>
                </div>
                <div class="p-6">
                    <div class="prose max-w-none">
                        {!! $template->content_html !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Métadonnées SEO -->
    <div class="bg-white rounded-lg shadow-sm border mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Métadonnées SEO</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Titre SEO</label>
                    <p class="text-gray-900 bg-gray-50 p-3 rounded border">{{ $template->meta_title }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description SEO</label>
                    <p class="text-gray-900 bg-gray-50 p-3 rounded border">{{ $template->meta_description }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mots-clés</label>
                    <p class="text-gray-900 bg-gray-50 p-3 rounded border">{{ $template->meta_keywords }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Titre Open Graph</label>
                    <p class="text-gray-900 bg-gray-50 p-3 rounded border">{{ $template->og_title }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Annonces créées avec ce template -->
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Annonces Créées avec ce Template</h3>
        </div>
        
        @if($template->ads->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Titre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ville</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Créé le</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($template->ads as $ad)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $ad->title }}</div>
                                    <div class="text-sm text-gray-500">{{ $ad->slug }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $ad->city->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($ad->status === 'published')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Publié
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>
                                            Brouillon
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $ad->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('ads.show', $ad->slug) }}" target="_blank" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <i class="fas fa-bullhorn text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune annonce créée</h3>
                <p class="text-gray-500 mb-6">Ce template n'a pas encore été utilisé pour créer des annonces.</p>
                <button onclick="generateAdsFromTemplate({{ $template->id }})" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition-colors duration-200">
                    Générer des Annonces
                </button>
            </div>
        @endif
    </div>
</div>

<!-- Modal Génération Annonces -->
<div id="generateAdsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Générer des Annonces</h3>
                <button onclick="hideGenerateAdsModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div id="citySelectionContainer">
                <!-- Le contenu sera chargé dynamiquement -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentTemplateId = {{ $template->id }};

function generateAdsFromTemplate(templateId) {
    currentTemplateId = templateId;
    document.getElementById('generateAdsModal').classList.remove('hidden');
    loadCitiesForTemplate(templateId);
}

function hideGenerateAdsModal() {
    document.getElementById('generateAdsModal').classList.add('hidden');
}

function loadCitiesForTemplate(templateId) {
    // Charger les villes disponibles
    fetch('/admin/ads/templates/cities')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayCitySelection(data.cities);
            } else {
                alert('Erreur lors du chargement des villes');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors du chargement des villes');
        });
}

function displayCitySelection(cities) {
    const container = document.getElementById('citySelectionContainer');
    
    let html = `
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Sélectionner les villes</label>
            <div class="max-h-60 overflow-y-auto border border-gray-300 rounded-md p-3">
                <div class="mb-2">
                    <label class="flex items-center">
                        <input type="checkbox" id="selectAllCities" onchange="toggleAllCities(this)" class="mr-2">
                        <span class="font-medium">Sélectionner toutes les villes</span>
                    </label>
                </div>
                <div class="grid grid-cols-2 gap-2" id="citiesList">
    `;
    
    cities.forEach(city => {
        html += `
            <label class="flex items-center text-sm">
                <input type="checkbox" name="city_ids[]" value="${city.id}" class="mr-2 city-checkbox">
                <span>${city.name}</span>
            </label>
        `;
    });
    
    html += `
                </div>
            </div>
        </div>
        
        <div class="flex justify-end space-x-3">
            <button type="button" onclick="hideGenerateAdsModal()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors duration-200">
                Annuler
            </button>
            <button type="button" onclick="generateAdsFromSelectedCities()" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200">
                Générer les Annonces
            </button>
        </div>
    `;
    
    container.innerHTML = html;
}

function toggleAllCities(selectAllCheckbox) {
    const cityCheckboxes = document.querySelectorAll('.city-checkbox');
    cityCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
}

function generateAdsFromSelectedCities() {
    const selectedCities = Array.from(document.querySelectorAll('.city-checkbox:checked'))
        .map(checkbox => checkbox.value);
    
    if (selectedCities.length === 0) {
        alert('Veuillez sélectionner au moins une ville');
        return;
    }
    
    const formData = new FormData();
    formData.append('template_id', currentTemplateId);
    selectedCities.forEach(cityId => {
        formData.append('city_ids[]', cityId);
    });
    
    // Afficher un indicateur de chargement
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Génération en cours...';
    button.disabled = true;
    
    fetch('/admin/ads/templates/generate-ads', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Génération terminée : ${data.created} annonces créées, ${data.skipped} ignorées`);
            location.reload(); // Recharger la page pour voir les nouvelles annonces
        } else {
            alert('Erreur lors de la génération : ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de la génération des annonces');
    })
    .finally(() => {
        button.textContent = originalText;
        button.disabled = false;
    });
}

function toggleTemplateStatus(templateId, newStatus) {
    if (confirm(`Êtes-vous sûr de vouloir ${newStatus ? 'activer' : 'désactiver'} ce template ?`)) {
        fetch(`/admin/ads/templates/${templateId}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ is_active: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de la modification du statut');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de la modification du statut');
        });
    }
}
</script>
@endpush
