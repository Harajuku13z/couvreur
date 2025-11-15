@extends('layouts.admin')

@section('title', 'Villes')

@section('content')
<div class="max-w-5xl mx-auto p-4 md:py-10">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h1 class="text-xl md:text-3xl font-bold">Gestion des villes</h1>
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:space-x-4 w-full sm:w-auto">
            <span class="text-sm text-gray-600">
                <span id="favorites-count">{{ $favoritesCount ?? 0 }}</span> favoris
            </span>
            <a href="{{ route('admin.cities.index', ['favorites' => '1']) }}" 
               class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded text-sm w-full sm:w-auto text-center">
                Voir les favoris
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 text-green-700 rounded">{{ session('success') }}</div>
    @endif

    <!-- Filtres -->
    <div class="bg-white rounded shadow p-4 mb-6">
        <h3 class="text-lg font-semibold mb-4">Filtres</h3>
        <form method="GET" action="{{ route('admin.cities.index') }}" class="grid md:grid-cols-4 gap-4">
            <div>
                <label class="text-sm font-medium">Recherche</label>
                <input name="search" value="{{ request('search') }}" 
                       class="border rounded px-3 py-2 w-full" 
                       placeholder="Nom de la ville">
            </div>
            <div>
                <label class="text-sm font-medium">Région</label>
                <select name="region" class="border rounded px-3 py-2 w-full" id="region-filter">
                    <option value="">Toutes les régions</option>
                    @foreach($regions as $region)
                        <option value="{{ $region }}" {{ request('region') == $region ? 'selected' : '' }}>
                            {{ $region }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium">Département</label>
                <select name="department" class="border rounded px-3 py-2 w-full" id="department-filter">
                    <option value="">Tous les départements</option>
                    @if(request('region'))
                        @php
                            $departmentsInRegion = \App\Models\City::where('region', request('region'))
                                ->distinct()
                                ->pluck('department')
                                ->filter()
                                ->sort()
                                ->values();
                        @endphp
                        @foreach($departmentsInRegion as $dept)
                            <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                                {{ $dept }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded mr-2">Filtrer</button>
                <a href="{{ route('admin.cities.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded">Reset</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded shadow p-4 mb-8">
        <h2 class="text-lg font-semibold mb-4">Importer des villes (IA)</h2>
        <div class="grid md:grid-cols-3 gap-4">
            <!-- Par département -->
            <form method="POST" action="{{ route('admin.cities.import.department') }}" class="space-y-2">
                @csrf
                <label class="text-sm font-medium">Département</label>
                <select name="department" class="border rounded px-3 py-2 w-full" required>
                    <option value="">Sélectionner</option>
                    @foreach($departments as $dep)
                        <option value="{{ $dep }}">{{ $dep }}</option>
                    @endforeach
                </select>
                <button class="bg-blue-600 text-white rounded px-4 py-2 w-full">Importer</button>
            </form>
            
            <!-- Par région -->
            <form method="POST" action="{{ route('admin.cities.import.region') }}" class="space-y-2">
                @csrf
                <label class="text-sm font-medium">Région</label>
                <select name="region" class="border rounded px-3 py-2 w-full" required>
                    <option value="">Sélectionner</option>
                    @foreach($regions as $reg)
                        <option value="{{ $reg }}">{{ $reg }}</option>
                    @endforeach
                </select>
                <button class="bg-blue-600 text-white rounded px-4 py-2 w-full">Importer</button>
            </form>
            
            <!-- Par rayon autour d'une adresse -->
            <form method="POST" action="{{ route('admin.cities.import.radius') }}" class="space-y-2">
                @csrf
                <label class="text-sm font-medium">Adresse + Rayon (km)</label>
                <input name="address" class="border rounded px-3 py-2 w-full" placeholder="12 Rue Exemple, 75000 Paris" required>
                <input name="radius_km" type="number" min="1" max="200" class="border rounded px-3 py-2 w-full" placeholder="Rayon en km" required>
                <button class="bg-blue-600 text-white rounded px-4 py-2 w-full">Importer</button>
            </form>
        </div>
        <p class="text-xs text-gray-500 mt-2">Inclut villes, communes et villages.</p>

    </div>

    <form method="POST" action="{{ route('admin.cities.store') }}" class="mb-8 grid grid-cols-1 md:grid-cols-5 gap-3">
        @csrf
        <input name="name" class="border rounded px-3 py-2" placeholder="Nom" required>
        <input name="postal_code" class="border rounded px-3 py-2" placeholder="Code postal" required>
        <input name="department" class="border rounded px-3 py-2" placeholder="Département">
        <input name="region" class="border rounded px-3 py-2" placeholder="Région">
        <button class="bg-blue-600 text-white rounded px-4">Ajouter</button>
    </form>

    <div class="flex items-center justify-between mb-2">
        <div class="text-sm text-gray-500">Total: {{ $cities->total() }}</div>
        <form method="POST" action="{{ route('admin.cities.destroy.all') }}" onsubmit="return confirm('Supprimer TOUTES les villes ? Cette action est irréversible.')">
            @csrf
            @method('DELETE')
            <button class="px-4 py-2 bg-red-600 text-white rounded">Supprimer toutes les villes</button>
        </form>
    </div>

    <!-- Vue mobile : Cartes -->
    <div class="md:hidden space-y-4">
        @foreach($cities as $city)
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex justify-between items-start mb-3">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">
                        {{ $city->name }}
                    </h3>
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <span>{{ $city->postal_code }}</span>
                        @if($city->department)
                            <span>•</span>
                            <span>{{ $city->department }}</span>
                        @endif
                        @if($city->region)
                            <span>•</span>
                            <span>{{ $city->region }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <form method="POST" action="{{ route('admin.cities.destroy', $city->id) }}" onsubmit="return confirm('Supprimer ?')" class="inline">
                        @csrf
                        @method('DELETE')
                        <button class="text-red-600 hover:text-red-900 p-2" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="flex flex-wrap gap-2">
                <form method="POST" action="{{ route('admin.cities.update', $city->id) }}" class="inline">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="active" value="{{ $city->active ? 0 : 1 }}">
                    <button class="px-3 py-1 rounded text-xs {{ $city->active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ $city->active ? 'Actif' : 'Inactif' }}
                    </button>
                </form>
                <button onclick="toggleFavorite({{ $city->id }})" 
                        class="favorite-btn px-3 py-1 rounded text-xs {{ $city->is_favorite ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600' }}"
                        data-city-id="{{ $city->id }}"
                        data-is-favorite="{{ $city->is_favorite ? '1' : '0' }}">
                    <i class="fas fa-star mr-1"></i>
                    {{ $city->is_favorite ? 'Favori' : 'Ajouter' }}
                </button>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Vue desktop : Table -->
    <div class="hidden md:block bg-white rounded shadow">
        <table class="min-w-full">
            <thead>
                <tr class="text-left text-sm text-gray-600">
                    <th class="p-3">Nom</th>
                    <th class="p-3">CP</th>
                    <th class="p-3">Département</th>
                    <th class="p-3">Région</th>
                    <th class="p-3">Actif</th>
                    <th class="p-3">Favori</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cities as $city)
                <tr class="border-t">
                    <td class="p-3">{{ $city->name }}</td>
                    <td class="p-3">{{ $city->postal_code }}</td>
                    <td class="p-3">{{ $city->department }}</td>
                    <td class="p-3">{{ $city->region }}</td>
                    <td class="p-3">
                        <form method="POST" action="{{ route('admin.cities.update', $city->id) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="active" value="{{ $city->active ? 0 : 1 }}">
                            <button class="px-3 py-1 rounded {{ $city->active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $city->active ? 'Actif' : 'Inactif' }}
                            </button>
                        </form>
                    </td>
                    <td class="p-3">
                        <button onclick="toggleFavorite({{ $city->id }})" 
                                class="favorite-btn px-3 py-1 rounded text-sm {{ $city->is_favorite ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600' }}"
                                data-city-id="{{ $city->id }}"
                                data-is-favorite="{{ $city->is_favorite ? '1' : '0' }}">
                            <i class="fas fa-star mr-1"></i>
                            {{ $city->is_favorite ? 'Favori' : 'Ajouter' }}
                        </button>
                    </td>
                    <td class="p-3">
                        <form method="POST" action="{{ route('admin.cities.destroy', $city->id) }}" onsubmit="return confirm('Supprimer ?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-600">Supprimer</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $cities->links() }}</div>
</div>

<script>
// Fonction pour basculer le statut favori
function toggleFavorite(cityId) {
    const button = document.querySelector(`[data-city-id="${cityId}"]`);
    const isFavorite = button.dataset.isFavorite === '1';
    
    fetch(`/admin/cities/${cityId}/toggle-favorite`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            is_favorite: !isFavorite
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mettre à jour le bouton
            if (data.is_favorite) {
                button.className = 'favorite-btn px-3 py-1 rounded text-sm bg-yellow-100 text-yellow-700';
                button.innerHTML = '<i class="fas fa-star mr-1"></i>Favori';
                button.dataset.isFavorite = '1';
            } else {
                button.className = 'favorite-btn px-3 py-1 rounded text-sm bg-gray-100 text-gray-600';
                button.innerHTML = '<i class="fas fa-star mr-1"></i>Ajouter';
                button.dataset.isFavorite = '0';
            }
            
            // Mettre à jour le compteur
            document.getElementById('favorites-count').textContent = data.favorites_count;
            
            // Afficher une notification
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Erreur lors de la mise à jour', 'error');
    });
}

// Fonction pour afficher les notifications
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Filtrage AJAX des départements par région
document.getElementById('region-filter').addEventListener('change', function() {
    const region = this.value;
    const departmentSelect = document.getElementById('department-filter');
    
    if (region) {
        fetch(`/admin/cities/departments?region=${encodeURIComponent(region)}`)
            .then(response => response.json())
            .then(data => {
                departmentSelect.innerHTML = '<option value="">Tous les départements</option>';
                data.departments.forEach(dept => {
                    const option = document.createElement('option');
                    option.value = dept;
                    option.textContent = dept;
                    departmentSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error loading departments:', error);
            });
    } else {
        departmentSelect.innerHTML = '<option value="">Tous les départements</option>';
    }
});
</script>
@endsection


