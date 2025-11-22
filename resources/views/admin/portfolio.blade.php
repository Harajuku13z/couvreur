@extends('layouts.admin')

@section('title', 'Gestion du Portfolio - Réalisations')

<style>
/* Styles pour corriger l'affichage du portfolio */
.portfolio-card {
    transition: all 0.3s ease;
    min-height: 400px;
}

.portfolio-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.portfolio-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    transition: opacity 0.3s ease;
}

.portfolio-image:hover {
    opacity: 0.9;
}

.portfolio-actions {
    opacity: 0;
    transition: opacity 0.3s ease;
}

.portfolio-card:hover .portfolio-actions {
    opacity: 1;
}

/* Corriger les boutons */
.btn-edit, .btn-delete {
    padding: 8px 12px;
    border-radius: 6px;
    transition: all 0.2s ease;
    cursor: pointer;
    border: none;
    background: none;
}

.btn-edit:hover {
    background-color: rgba(59, 130, 246, 0.1);
    color: #2563eb;
}

.btn-delete:hover {
    background-color: rgba(239, 68, 68, 0.1);
    color: #dc2626;
}

/* Corriger la grille */
.portfolio-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

@media (max-width: 768px) {
    .portfolio-grid {
        grid-template-columns: 1fr;
    }
}
</style>

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">📸 Gestion du Portfolio</h1>
                    <p class="mt-2 text-gray-600">Gérez vos réalisations et photos de travaux</p>
                </div>
                <button onclick="openAddModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center">
                    <i class="fas fa-plus mr-2"></i>Ajouter une Réalisation
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <i class="fas fa-images text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Réalisations</p>
                        <p class="text-2xl font-bold text-gray-900" id="total-items">{{ count($portfolioItems) }}</p>
                    </div>
                </div>
            </div>
            @php
                $servicesData = setting('services', []);
                $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
                $serviceStats = [];
                foreach($services as $service) {
                    $serviceStats[$service['slug']] = [
                        'name' => $service['name'],
                        'icon' => $service['icon'],
                        'count' => collect($portfolioItems)->where('work_type', $service['slug'])->count()
                    ];
                }
            @endphp
            
            @foreach($serviceStats as $slug => $service)
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <i class="{{ $service['icon'] }} text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">{{ ucfirst($service['name']) }}</p>
                        <p class="text-2xl font-bold text-gray-900" id="{{ $slug }}-count">{{ $service['count'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>


        <!-- Portfolio Grid -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold">Vos Réalisations</h2>
                <p class="text-gray-600">Gérez et organisez vos photos de travaux</p>
            </div>
            
            <div id="portfolio-grid" class="p-6">
                @if(count($portfolioItems) > 0)
                    <div class="portfolio-grid">
                        @foreach($portfolioItems as $index => $item)
                            <div class="portfolio-card bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                                @if(isset($item['images']) && count($item['images']) > 0)
                                    <img src="{{ url($item['images'][0]) }}" alt="{{ $item['title'] ?? 'Réalisation' }}" class="portfolio-image">
                                @else
                                    <div class="portfolio-image bg-gray-200 flex items-center justify-center">
                                        <i class="fas fa-image text-4xl text-gray-400"></i>
                                    </div>
                                @endif
                                
                                <div class="p-4">
                                    <h3 class="font-semibold text-lg mb-2">{{ $item['title'] ?? 'Sans titre' }}</h3>
                                    <p class="text-gray-600 text-sm mb-3">{{ $item['description'] ?? 'Aucune description' }}</p>
                                    
                                    <!-- Informations du projet -->
                                    <div class="mb-3">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if(($item['work_type'] ?? '') == 'couverture') bg-green-100 text-green-800
                                                @elseif(($item['work_type'] ?? '') == 'renovation-toiture') bg-yellow-100 text-yellow-800
                                                @elseif(($item['work_type'] ?? '') == 'demoussage') bg-blue-100 text-blue-800
                                                @elseif(($item['work_type'] ?? '') == 'hydrofuge') bg-purple-100 text-purple-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                @if(($item['work_type'] ?? '') == 'couverture') Couverture
                                                @elseif(($item['work_type'] ?? '') == 'renovation-toiture') Rénovation
                                                @elseif(($item['work_type'] ?? '') == 'demoussage') Demoussage
                                                @elseif(($item['work_type'] ?? '') == 'hydrofuge') Hydrofuge
                                                @else {{ ucfirst($item['work_type'] ?? 'Autre') }}
                                                @endif
                                            </span>
                                            
                                            <!-- Nombre de photos -->
                                            @if(isset($item['images']) && count($item['images']) > 0)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-images mr-1"></i>
                                                {{ count($item['images']) }} photo{{ count($item['images']) > 1 ? 's' : '' }}
                                            </span>
                                            @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                                <i class="fas fa-image mr-1"></i>
                                                Aucune photo
                                            </span>
                                            @endif
                                        </div>
                                        
                                        <!-- Informations supplémentaires -->
                                        <div class="flex items-center space-x-4 text-xs text-gray-500">
                                            @if(isset($item['location']) && !empty($item['location']))
                                            <span class="flex items-center">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                {{ $item['location'] }}
                                            </span>
                                            @endif
                                            
                                            @if(isset($item['year']) && !empty($item['year']))
                                            <span class="flex items-center">
                                                <i class="fas fa-calendar mr-1"></i>
                                                {{ $item['year'] }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Actions -->
                                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                                        <a href="{{ route('portfolio.show', $item['id']) }}" 
                                           class="inline-flex items-center px-3 py-1.5 bg-primary text-white text-xs font-medium rounded-md hover:bg-secondary transition-colors"
                                           target="_blank">
                                            <i class="fas fa-eye mr-1"></i>
                                            Voir
                                        </a>
                                        
                                        <div class="portfolio-actions flex space-x-2">
                                            <a href="{{ route('portfolio.admin.edit', $item['id']) }}" class="btn-edit text-blue-600">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="deleteItem({{ $index }})" class="btn-delete text-red-600">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-gray-500 py-12">
                        <i class="fas fa-images text-6xl mb-4"></i>
                        <h3 class="text-xl font-medium mb-2">Aucune réalisation</h3>
                        <p class="text-gray-600 mb-4">Commencez par ajouter vos premières réalisations</p>
                        <button onclick="openAddModal()" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
                            <i class="fas fa-plus mr-2"></i>Ajouter une Réalisation
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="portfolio-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold" id="modal-title">Ajouter une Réalisation</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <form id="portfolio-form" enctype="multipart/form-data">
                @csrf
                <div class="p-6 space-y-6">
                    <!-- Images Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Photos de la réalisation *</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors">
                            <input type="file" id="portfolio-images" name="images[]" multiple accept="image/*" class="hidden">
                            <div id="upload-area" class="cursor-pointer">
                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                                <p class="text-lg font-medium text-gray-700">Cliquez pour sélectionner des photos</p>
                                <p class="text-sm text-gray-500 mt-2">ou glissez-déposez vos images ici</p>
                                <p class="text-xs text-gray-400 mt-1">Formats acceptés: JPG, PNG, WebP - Max 5 Mo par image</p>
                            </div>
                        </div>
                        <div id="image-preview" class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-4 hidden">
                            <!-- Image previews will be shown here -->
                        </div>
                    </div>

                    <!-- Project Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Titre du projet *</label>
                            <input type="text" name="title" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Ex: Rénovation toiture - Maison familiale">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Type de travaux / Service *</label>
                            <select name="work_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Sélectionnez un type</option>
                                @php
                                    $servicesData = setting('services', '[]');
                                    $allServices = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
                                @endphp
                                @foreach($allServices as $service)
                                    <option value="{{ $service['slug'] }}">{{ $service['name'] }}</option>
                                @endforeach
                                <option value="other">Autre</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Sélectionnez le service correspondant à cette réalisation</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description du projet</label>
                        <textarea name="description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Décrivez les travaux réalisés, les matériaux utilisés, les défis relevés..."></textarea>
                    </div>


                    <!-- Visibility -->
                    <div class="flex items-center">
                        <input type="checkbox" name="is_visible" id="is_visible" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="is_visible" class="ml-2 text-sm text-gray-700">Afficher cette réalisation sur le site</label>
                    </div>
                </div>

                <div class="p-6 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                        Annuler
                    </button>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Portfolio Management JavaScript
let portfolioItems = @json($portfolioItems);
let currentEditId = null;

// Initialize portfolio on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Portfolio items on load:', portfolioItems);
    updateStats();
    // renderPortfolio() supprimé - utilise le HTML Blade directement
});

// Load portfolio data
async function loadPortfolio() {
    try {
        const response = await fetch('/admin/portfolio/data');
        const data = await response.json();
        portfolioItems = data.items || [];
        updateStats();
        renderPortfolio();
    } catch (error) {
        console.error('Error loading portfolio:', error);
    }
}

// Update statistics
function updateStats() {
    const total = portfolioItems.length;
    
    // Mettre à jour le total
    document.getElementById('total-items').textContent = total;
    
    // Mettre à jour les statistiques par service
    const services = @json($services ?? []);
    services.forEach(service => {
        const count = portfolioItems.filter(item => item.work_type === service.slug).length;
        const countElement = document.getElementById(service.slug + '-count');
        if (countElement) {
            countElement.textContent = count;
        }
    });
}

// Fonction renderPortfolio supprimée - utilise le HTML Blade directement

// Fonctions helper supprimées - utilise le HTML Blade directement

// Modal functions
function openAddModal() {
    // Réinitialiser les variables
    window.currentEditId = null;
    currentEditId = null;
    
    // Réinitialiser le formulaire
    resetForm();
    
    // Ouvrir le modal
    const portfolioModal = document.getElementById('portfolio-modal');
    if (portfolioModal) {
        portfolioModal.classList.remove('hidden');
    }
}

function closeModal() {
    document.getElementById('portfolio-modal').classList.add('hidden');
    // Réinitialiser le formulaire et les variables
    resetForm();
}

function resetForm() {
    // Réinitialiser les champs du formulaire (avec vérifications)
    const titleEl = document.getElementById('title');
    const workTypeEl = document.getElementById('work_type');
    const descriptionEl = document.getElementById('description');
    const isVisibleEl = document.getElementById('is_visible');
    
    if (titleEl) titleEl.value = '';
    if (workTypeEl) workTypeEl.value = '';
    if (descriptionEl) descriptionEl.value = '';
    if (isVisibleEl) isVisibleEl.checked = true;
    
    // Réinitialiser les variables globales
    window.currentEditId = null;
    currentEditId = null;
    
    // Réinitialiser le titre du modal (avec vérification)
    const modalTitle = document.querySelector('#portfolio-modal h3');
    const submitButton = document.querySelector('#portfolio-modal button[type="submit"]');
    
    if (modalTitle) modalTitle.textContent = 'Ajouter une Réalisation';
    if (submitButton) submitButton.textContent = 'Ajouter';
    
    // Vider les images sélectionnées
    const imageContainer = document.getElementById('image-preview');
    if (imageContainer) {
        imageContainer.innerHTML = '';
    }
}

// Fonction editItem supprimée - maintenant redirection vers page dédiée

// Form submission (avec vérification)
const portfolioForm = document.getElementById('portfolio-form');
if (portfolioForm) {
    portfolioForm.addEventListener('submit', async function(e) {
        e.preventDefault();
    
    const formData = new FormData(this);
    let url;
    
    // Déterminer l'URL selon le mode (ajout ou modification)
    console.log('Form submission - currentEditId:', window.currentEditId);
    console.log('Form submission - currentEditId type:', typeof window.currentEditId);
    
    if (window.currentEditId !== null && window.currentEditId !== undefined) {
        url = `/admin/portfolio/update/${window.currentEditId}`;
        console.log('Using UPDATE URL:', url);
    } else {
        url = '/admin/portfolio';
        console.log('Using ADD URL:', url);
    }
    
    try {
        // Debug: log form data
        console.log('Form data being sent:');
        for (let [key, value] of formData.entries()) {
            console.log(key, value);
        }
        
        const response = await fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (response.ok && result.success) {
            alert(result.message || 'Opération réussie !');
            closeModal();
            location.reload(); // Recharger la page pour voir les changements
        } else {
            alert('Erreur: ' + (result.message || 'Erreur lors de l\'enregistrement'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Erreur lors de l\'enregistrement: ' + error.message);
    }
    });
}

// Image upload handling (avec vérifications)
const uploadArea = document.getElementById('upload-area');
const portfolioImages = document.getElementById('portfolio-images');

if (uploadArea && portfolioImages) {
    uploadArea.addEventListener('click', () => {
        portfolioImages.click();
    });

    portfolioImages.addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    const preview = document.getElementById('image-preview');
    
    preview.innerHTML = '';
    preview.classList.remove('hidden');
    
    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'relative';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Preview" class="w-full h-32 object-cover rounded-lg">
                <button type="button" onclick="removeImage(this)" class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs">
                    <i class="fas fa-times"></i>
                </button>
            `;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
    });
}

function removeImage(button) {
    button.parentElement.remove();
}

// Delete item
async function deleteItem(index) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette réalisation ?')) {
        try {
            // Récupérer l'ID de l'élément depuis les données
            const item = portfolioItems[index];
            if (!item) {
                alert('Erreur : Élément non trouvé');
                return;
            }
            
            // Si l'élément n'a pas d'ID, on utilise l'index comme ID temporaire
            const itemId = item.id || `temp_${index}`;
            
            if (!item.id) {
                alert('Attention : Cet élément n\'a pas d\'ID valide. Suppression impossible.');
                return;
            }
            
            const response = await fetch(`/admin/portfolio/delete/${item.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });
            
            const result = await response.json();
            
            if (response.ok && result.success) {
                alert(result.message || 'Réalisation supprimée avec succès !');
                location.reload(); // Recharger la page pour voir les changements
            } else {
                alert('Erreur : ' + (result.message || 'Erreur lors de la suppression'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Erreur lors de la suppression : ' + error.message);
        }
    }
}


// Initialize - Les données sont déjà chargées via @json($portfolioItems)
</script>
@endsection











