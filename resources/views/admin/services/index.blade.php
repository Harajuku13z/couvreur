@extends('layouts.admin')

@section('title', 'Gestion des Services')

<style>
/* Styles pour la gestion des services */
.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
}

.service-card {
    transition: all 0.3s ease;
    min-height: 300px;
}

.service-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.service-icon {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-size: 24px;
    margin: 0 auto 1rem;
}

.service-actions {
    opacity: 0;
    transition: opacity 0.3s ease;
}

.service-card:hover .service-actions {
    opacity: 1;
}

.btn-service {
    padding: 8px 16px;
    border-radius: 6px;
    transition: all 0.2s ease;
    cursor: pointer;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-edit {
    background-color: rgba(59, 130, 246, 0.1);
    color: #2563eb;
}

.btn-edit:hover {
    background-color: rgba(59, 130, 246, 0.2);
}

.btn-delete {
    background-color: rgba(239, 68, 68, 0.1);
    color: #dc2626;
}

.btn-delete:hover {
    background-color: rgba(239, 68, 68, 0.2);
}

.btn-view {
    background-color: rgba(16, 185, 129, 0.1);
    color: #059669;
}

.btn-view:hover {
    background-color: rgba(16, 185, 129, 0.2);
}

.btn-regenerate {
    background-color: rgba(147, 51, 234, 0.1);
    color: #7c3aed;
}

.btn-regenerate:hover {
    background-color: rgba(147, 51, 234, 0.2);
}

.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-featured {
    background-color: #fef3c7;
    color: #92400e;
}

.status-menu {
    background-color: #dbeafe;
    color: #1e40af;
}

.status-inactive {
    background-color: #f3f4f6;
    color: #6b7280;
}
</style>

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">🛠️ Gestion des Services</h1>
                    <p class="mt-2 text-gray-600">Créez et gérez vos pages de services avec génération automatique de contenu</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('services.admin.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center">
                        <i class="fas fa-plus mr-2"></i>Nouveau Service
                    </a>
                    <form action="{{ route('services.admin.regenerate.all') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center" 
                                onclick="return confirm('Êtes-vous sûr de vouloir régénérer le contenu de tous les services ? Cette action peut prendre quelques minutes.')">
                            <i class="fas fa-magic mr-2"></i>Régénérer Tout
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-tools text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Services</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ count($services) }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-star text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Services Vedettes</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ collect($services)->where('is_featured', true)->count() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-bars text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Dans le Menu</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ collect($services)->where('is_menu', true)->count() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-eye text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Pages Visibles</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ collect($services)->where('is_featured', true)->count() + collect($services)->where('is_menu', true)->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des services -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold">Vos Services</h2>
                <p class="text-gray-600">Gérez vos pages de services et leur visibilité</p>
            </div>
            
            <div class="p-6">
                @if(count($services) > 0)
                    <div class="services-grid">
                        @foreach($services as $service)
                            <div class="service-card bg-white border border-gray-200 rounded-lg p-6">
                                <div class="text-center mb-4">
                                    <div class="service-icon">
                                        <i class="{{ $service['icon'] ?? 'fas fa-tools' }}"></i>
                                    </div>
                                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $service['name'] }}</h3>
                                    <p class="text-gray-600 text-sm mb-4">{{ Str::limit($service['short_description'], 100) }}</p>
                                    
                                    <!-- Statuts -->
                                    <div class="flex justify-center gap-2 mb-4">
                                        @if(($service['is_featured'] ?? false))
                                            <span class="status-badge status-featured">Vedette</span>
                                        @endif
                                        @if(($service['is_menu'] ?? false))
                                            <span class="status-badge status-menu">Menu</span>
                                        @endif
                                        @if(!($service['is_featured'] ?? false) && !($service['is_menu'] ?? false))
                                            <span class="status-badge status-inactive">Masqué</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="service-actions flex flex-col gap-2">
                                    <div class="flex justify-center gap-2">
                                        <a href="{{ route('services.show', $service['slug']) }}" target="_blank" class="btn-service btn-view">
                                            <i class="fas fa-eye"></i>Voir
                                        </a>
                                        <a href="{{ route('services.admin.edit', $service['id'] ?? $loop->index) }}" class="btn-service btn-edit">
                                            <i class="fas fa-edit"></i>Modifier
                                        </a>
                                    </div>
                                    <div class="flex justify-center gap-2">
                                        <button onclick="regenerateService({{ $service['id'] ?? $loop->index }}, '{{ $service['name'] }}')" class="btn-service btn-regenerate">
                                            <i class="fas fa-sync-alt"></i>Régénérer IA
                                        </button>
                                        <button onclick="deleteService({{ $service['id'] ?? $loop->index }}, '{{ $service['name'] }}')" class="btn-service btn-delete">
                                            <i class="fas fa-trash"></i>Supprimer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-gray-500 py-12">
                        <i class="fas fa-tools text-6xl mb-4"></i>
                        <h3 class="text-xl font-medium mb-2">Aucun service</h3>
                        <p class="text-gray-600 mb-4">Commencez par créer votre premier service</p>
                        <a href="{{ route('services.admin.create') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
                            <i class="fas fa-plus mr-2"></i>Créer un Service
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function regenerateService(id, name) {
    if (confirm(`Voulez-vous régénérer le contenu du service "${name}" avec l'IA ?\n\nCela va :\n✅ Créer une nouvelle description courte\n✅ Créer une nouvelle description longue HTML\n✅ Générer de nouveaux meta tags SEO\n✅ Choisir une nouvelle icône\n\nLe brief actuel sera utilisé pour la régénération.`)) {
        // Afficher un loader
        const button = event.target.closest('button');
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération...';
        button.disabled = true;
        
        // Créer un formulaire pour la régénération
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/services/${id}/regenerate`;
        
        // Ajouter le token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        // Soumettre le formulaire
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteService(id, name) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le service "${name}" ?`)) {
        // Créer un formulaire pour la suppression
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/services/${id}`;
        
        // Ajouter le token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        // Ajouter la méthode DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        // Soumettre le formulaire
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection








