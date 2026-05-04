@extends('layouts.admin')

@section('title', 'Gestion des Services')

<style>
/* Styles pour la gestion des services */
.services-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}

@media (min-width: 768px) {
    .services-grid {
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    }
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


.btn-delete {
    background-color: rgba(239, 68, 68, 0.1);
    color: #dc2626;
}


.btn-view {
    background-color: rgba(16, 185, 129, 0.1);
    color: #059669;
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
<div class="min-h-screen bg-gray-50 py-4 md:py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 md:mb-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-3xl font-bold text-gray-900">🛠️ Gestion des Services</h1>
                    <p class="mt-2 text-gray-600 text-sm md:text-base">Créez et gérez vos pages de services avec génération automatique de contenu</p>
                </div>
                <div class="flex gap-3 w-full sm:w-auto">
                    <a href="{{ route('services.admin.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center justify-center w-full sm:w-auto">
                        <i class="fas fa-plus mr-2"></i>Nouveau Service
                    </a>
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
                                    @if(!empty($service['featured_image']))
                                        <div class="mb-4">
                                            <img src="{{ asset($service['featured_image']) }}" alt="{{ $service['name'] }}" 
                                                 class="w-full h-32 object-cover rounded-lg mx-auto">
                                        </div>
                                    @else
                                        <div class="service-icon">
                                            <i class="{{ $service['icon'] ?? 'fas fa-tools' }}"></i>
                                        </div>
                                    @endif
                                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $service['name'] }}</h3>
                                    <p class="text-gray-600 text-sm mb-2">{{ Str::limit($service['short_description'], 100) }}</p>
                                    <p class="text-xs text-gray-400 mb-4">ID: {{ $service['id'] ?? 'N/A' }}</p>
                                    
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
                                    <div class="flex flex-wrap justify-center gap-2">
                                        <a href="{{ route('services.show', $service['slug']) }}" target="_blank" class="btn-service btn-view flex-1 sm:flex-none">
                                            <i class="fas fa-eye"></i>Voir
                                        </a>
                                        <a href="{{ route('services.admin.edit', $service['id'] ?? $loop->index) }}" class="btn-service btn-edit flex-1 sm:flex-none">
                                            <i class="fas fa-edit"></i>Modifier
                                        </a>
                                    </div>
                                    <div class="flex flex-wrap justify-center gap-2">
                                        <button onclick="regenerateService('{{ $service['id'] ?? $loop->index }}', '{{ addslashes($service['name']) }}')"
                                                class="btn-service flex-1 sm:flex-none"
                                                style="background:rgba(139,92,246,.12); color:#7c3aed;"
                                                id="regen-btn-{{ $service['id'] ?? $loop->index }}">
                                            <i class="fas fa-magic"></i>Régénérer IA
                                        </button>
                                        <button onclick="deleteService('{{ $service['id'] ?? $loop->index }}', '{{ addslashes($service['name']) }}')" class="btn-service btn-delete flex-1 sm:flex-none">
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

async function regenerateService(id, name) {
    if (!confirm(`Régénérer le contenu IA pour "${name}" ?\n\nCela remplacera la description actuelle avec une nouvelle version générée par l'IA. Cette action peut prendre 30-60 secondes.`)) return;

    const btn = document.getElementById('regen-btn-' + id);
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération en cours…';
        btn.style.opacity = '0.7';
    }

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const response = await fetch(`/admin/services/${id}/regenerate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
        });

        const data = await response.json();

        if (data.success) {
            if (btn) {
                btn.innerHTML = '<i class="fas fa-check"></i> Régénéré !';
                btn.style.background = 'rgba(16,185,129,.15)';
                btn.style.color = '#059669';
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-magic"></i>Régénérer IA';
                    btn.style.background = 'rgba(139,92,246,.12)';
                    btn.style.color = '#7c3aed';
                    btn.style.opacity = '1';
                }, 3000);
            }
            alert(`✅ Le contenu de "${name}" a été régénéré avec succès par l'IA !`);
        } else {
            throw new Error(data.message || 'Erreur inconnue');
        }
    } catch (err) {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-magic"></i>Régénérer IA';
            btn.style.opacity = '1';
        }
        alert('❌ Erreur lors de la régénération : ' + err.message);
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








