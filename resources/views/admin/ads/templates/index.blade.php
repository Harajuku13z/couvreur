@extends('layouts.admin')

@section('title', 'Templates d\'Annonces')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Templates d'Annonces</h1>
            <p class="text-gray-600 mt-2">Gérez les templates générés par l'IA pour créer des annonces personnalisées</p>
        </div>
        <div class="flex space-x-4">
            <button onclick="showCreateTemplateModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                <i class="fas fa-plus"></i>
                <span>Créer un Template</span>
            </button>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-sm border">
            <div class="flex items-center">
                <div class="bg-blue-100 p-3 rounded-lg">
                    <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Templates</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $templates->total() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-sm border">
            <div class="flex items-center">
                <div class="bg-green-100 p-3 rounded-lg">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Templates Actifs</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $templates->where('is_active', true)->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-sm border">
            <div class="flex items-center">
                <div class="bg-purple-100 p-3 rounded-lg">
                    <i class="fas fa-bullhorn text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Annonces Créées</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $templates->sum('ads_count') }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-sm border">
            <div class="flex items-center">
                <div class="bg-orange-100 p-3 rounded-lg">
                    <i class="fas fa-chart-line text-orange-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Utilisation Moyenne</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $templates->avg('usage_count') ? round($templates->avg('usage_count'), 1) : 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulaire de création de template -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Créer un Template</h2>
        
        <!-- Onglets -->
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button onclick="switchTab('service')" id="service-tab" class="tab-button active py-2 px-1 border-b-2 font-medium text-sm">
                        <i class="fas fa-cog mr-2"></i>Par Service
                    </button>
                    <button onclick="switchTab('keyword')" id="keyword-tab" class="tab-button py-2 px-1 border-b-2 font-medium text-sm">
                        <i class="fas fa-key mr-2"></i>Par Mot-clé
                    </button>
                </nav>
            </div>
        </div>
        
        <!-- Formulaire par service -->
        <form id="createTemplateForm" class="tab-content" data-tab="service">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="service_slug" class="block text-sm font-medium text-gray-700 mb-2">Service</label>
                    <select id="service_slug" name="service_slug" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Sélectionner un service</option>
                        @foreach($services as $service)
                            <option value="{{ $service['slug'] }}">{{ $service['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="ai_prompt" class="block text-sm font-medium text-gray-700 mb-2">Instructions personnalisées (optionnel)</label>
                    <textarea id="ai_prompt" name="ai_prompt" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ajoutez des instructions spécifiques pour la génération du contenu..."></textarea>
                </div>
            </div>
            
            <div class="mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                    <i class="fas fa-magic"></i>
                    <span>Créer le Template</span>
                </button>
            </div>
        </form>
        
        <!-- Formulaire par mot-clé -->
        <form id="createKeywordTemplateForm" class="tab-content hidden" data-tab="keyword">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="keyword" class="block text-sm font-medium text-gray-700 mb-2">Mot-clé</label>
                    <input type="text" id="keyword" name="keyword" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex: zinguerie, isolation, peinture..." required>
                </div>
                
                <div>
                    <label for="keyword_ai_prompt" class="block text-sm font-medium text-gray-700 mb-2">Instructions personnalisées (optionnel)</label>
                    <textarea id="keyword_ai_prompt" name="ai_prompt" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ajoutez des instructions spécifiques pour la génération du contenu..."></textarea>
                </div>
            </div>
            
            <div class="mt-6">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                    <i class="fas fa-key"></i>
                    <span>Créer le Template Mot-clé</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Liste des templates -->
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Liste des Templates</h2>
        </div>
        
        @if($templates->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Template</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Créé le</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($templates as $template)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                            <i class="{{ $template->icon }} text-blue-600"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $template->name }}</div>
                                            <div class="text-sm text-gray-500">{{ Str::limit($template->short_description, 60) }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $template->service_name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
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
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center">
                                        <i class="fas fa-bullhorn text-gray-400 mr-2"></i>
                                        {{ $template->ads_count }} annonces
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $template->usage_count }} utilisations
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $template->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('ads.templates.show', $template) }}" class="text-blue-600 hover:text-blue-900" title="Voir le template">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button onclick="generateAdsFromTemplate({{ $template->id }})" class="text-green-600 hover:text-green-900" title="Générer des annonces">
                                            <i class="fas fa-plus-circle"></i>
                                        </button>
                                        <button onclick="toggleTemplateStatus({{ $template->id }}, {{ $template->is_active ? 'false' : 'true' }})" class="text-orange-600 hover:text-orange-900" title="{{ $template->is_active ? 'Désactiver' : 'Activer' }}">
                                            <i class="fas fa-{{ $template->is_active ? 'pause' : 'play' }}"></i>
                                        </button>
                                        <button onclick="deleteTemplate({{ $template->id }})" class="text-red-600 hover:text-red-900" title="Supprimer le template">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $templates->links() }}
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <i class="fas fa-file-alt text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun template créé</h3>
                <p class="text-gray-500 mb-6">Créez votre premier template d'annonce pour commencer à générer du contenu personnalisé.</p>
                <button onclick="showCreateTemplateModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors duration-200">
                    Créer un Template
                </button>
            </div>
        @endif
    </div>
</div>

<!-- Modal Création Template -->
<div id="createTemplateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Créer un Template</h3>
                <button onclick="hideCreateTemplateModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="createTemplateForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Service</label>
                    <select id="serviceSelect" name="service_slug" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Sélectionner un service</option>
                        @php
                            $servicesData = setting('services', '[]');
                            $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
                        @endphp
                        @foreach($services as $service)
                            <option value="{{ $service['slug'] }}">{{ $service['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Instructions personnalisées (optionnel)</label>
                    <textarea id="aiPrompt" name="ai_prompt" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ajoutez des instructions spécifiques pour personnaliser le template..."></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideCreateTemplateModal()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors duration-200">
                        Annuler
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200">
                        Créer le Template
                    </button>
                </div>
            </form>
        </div>
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
let currentTemplateId = null;

function showCreateTemplateModal() {
    document.getElementById('createTemplateModal').classList.remove('hidden');
}

function hideCreateTemplateModal() {
    document.getElementById('createTemplateModal').classList.add('hidden');
    document.getElementById('createTemplateForm').reset();
}

function showGenerateAdsModal(templateId) {
    currentTemplateId = templateId;
    document.getElementById('generateAdsModal').classList.remove('hidden');
    loadCitiesForTemplate(templateId);
}

function hideGenerateAdsModal() {
    document.getElementById('generateAdsModal').classList.add('hidden');
    currentTemplateId = null;
}

function generateAdsFromTemplate(templateId) {
    showGenerateAdsModal(templateId);
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

// Gestion du formulaire de création de template
document.getElementById('createTemplateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    const button = this.querySelector('button[type="submit"]');
    const originalText = button.textContent;
    button.textContent = 'Création en cours...';
    button.disabled = true;
    
    fetch('/admin/ads/templates/create-from-service', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Template créé avec succès !');
            location.reload();
        } else {
            alert('Erreur lors de la création : ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de la création du template');
    })
    .finally(() => {
        button.textContent = originalText;
        button.disabled = false;
    });
});

// Fonction pour supprimer un template
function deleteTemplate(templateId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce template ? Cette action est irréversible.')) {
        fetch(`/admin/ads/templates/${templateId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Template supprimé avec succès !');
                location.reload();
            } else {
                alert('Erreur lors de la suppression : ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de la suppression du template');
        });
    }
}

// Gestion des onglets
function switchTab(tabName) {
    // Masquer tous les contenus d'onglets
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Désactiver tous les boutons d'onglets
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active', 'border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Afficher le contenu de l'onglet sélectionné
    document.querySelector(`[data-tab="${tabName}"]`).classList.remove('hidden');
    
    // Activer le bouton de l'onglet sélectionné
    const activeButton = document.getElementById(`${tabName}-tab`);
    activeButton.classList.add('active', 'border-blue-500', 'text-blue-600');
    activeButton.classList.remove('border-transparent', 'text-gray-500');
}

// Gestion du formulaire de création par mot-clé
document.getElementById('createKeywordTemplateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    const button = this.querySelector('button[type="submit"]');
    const originalText = button.textContent;
    button.textContent = 'Création en cours...';
    button.disabled = true;
    
    fetch('/admin/ads/templates/create-from-keyword', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Template créé avec succès !');
            location.reload();
        } else {
            if (data.existing_templates && data.existing_templates.length > 0) {
                let message = data.message + '\n\nTemplates existants :\n';
                data.existing_templates.forEach(template => {
                    message += `- ${template.name} (${template.ads_count} annonces) - ${template.created_at}\n`;
                });
                message += '\nVoulez-vous créer un nouveau template quand même ?';
                
                if (confirm(message)) {
                    formData.append('force_create', 'true');
                    // Relancer la requête avec force_create
                    fetch('/admin/ads/templates/create-from-keyword', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Template créé avec succès !');
                            location.reload();
                        } else {
                            alert('Erreur lors de la création : ' + data.message);
                        }
                    });
                }
            } else {
                alert('Erreur lors de la création : ' + data.message);
            }
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de la création du template');
    })
    .finally(() => {
        button.textContent = originalText;
        button.disabled = false;
    });
});
</script>
@endpush
