@extends('layouts.admin')

@section('title', 'Gestion des Clients')
@section('page_title', 'Gestion des Clients')

@section('content')
<div class="p-4 md:p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h1 class="text-xl md:text-2xl font-bold">Clients</h1>
        <button onclick="showCreateClientModal()" 
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition w-full sm:w-auto text-center">
            <i class="fas fa-plus mr-2"></i>Nouveau Client
        </button>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ session('error') }}
    </div>
    @endif

    <!-- Recherche -->
    <div class="mb-6">
        <form method="GET" action="{{ route('admin.clients.index') }}" class="flex flex-col sm:flex-row gap-4">
            <input type="text" 
                   name="search" 
                   value="{{ request('search') }}"
                   placeholder="Rechercher un client..." 
                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg">
            <button type="submit" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 w-full sm:w-auto text-center">
                <i class="fas fa-search mr-2"></i>Rechercher
            </button>
        </form>
    </div>

    <!-- Vue mobile : Cartes -->
    <div class="md:hidden space-y-4">
        @forelse($clients as $client)
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex justify-between items-start mb-3">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        {{ $client->nom_complet }}
                    </h3>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.devis.create', ['client_id' => $client->id]) }}" 
                       class="text-blue-600 hover:text-blue-900 p-2"
                       title="Créer un devis">
                        <i class="fas fa-file-invoice"></i>
                    </a>
                    <button onclick="showDeleteClientModal({{ $client->id }}, '{{ $client->nom_complet }}')" 
                            class="text-red-600 hover:text-red-900 p-2"
                            title="Supprimer le client">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            
            <div class="space-y-2 text-sm">
                <div class="flex items-center text-gray-600">
                    <i class="fas fa-envelope w-5 text-gray-400"></i>
                    <span>{{ $client->email }}</span>
                </div>
                @if($client->telephone)
                <div class="flex items-center text-gray-600">
                    <i class="fas fa-phone w-5 text-gray-400"></i>
                    <span>{{ $client->telephone }}</span>
                </div>
                @endif
                @if($client->adresse || $client->code_postal || $client->ville)
                <div class="flex items-center text-gray-600">
                    <i class="fas fa-map-marker-alt w-5 text-gray-400"></i>
                    <div>
                        @if($client->adresse)
                            <div>{{ $client->adresse }}</div>
                        @endif
                        @if($client->code_postal || $client->ville)
                            <div class="text-gray-500">
                                {{ trim(($client->code_postal ?? '') . ' ' . ($client->ville ?? '')) }}
                            </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">
            Aucun client trouvé
        </div>
        @endforelse
    </div>

    <!-- Vue desktop : Table -->
    <div class="hidden md:block bg-white rounded-lg shadow overflow-hidden table-responsive">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Téléphone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Adresse</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($clients as $client)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $client->nom_complet }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $client->email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $client->telephone ?? '-' }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900">
                            @if($client->adresse || $client->code_postal || $client->ville)
                                @if($client->adresse)
                                    <div>{{ $client->adresse }}</div>
                                @endif
                                @if($client->code_postal || $client->ville)
                                    <div class="text-gray-600">
                                        {{ trim(($client->code_postal ?? '') . ' ' . ($client->ville ?? '')) }}
                                    </div>
                                @endif
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.devis.create', ['client_id' => $client->id]) }}" 
                               class="text-blue-600 hover:text-blue-900"
                               title="Créer un devis">
                                <i class="fas fa-file-invoice"></i>
                            </a>
                            <button onclick="showDeleteClientModal({{ $client->id }}, '{{ $client->nom_complet }}')" 
                                    class="text-red-600 hover:text-red-900"
                                    title="Supprimer le client">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                        Aucun client trouvé
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $clients->links() }}
    </div>
</div>

<!-- Modal création client -->
<div id="createClientModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 md:top-20 mx-auto p-5 border w-11/12 md:w-96 shadow-lg rounded-md bg-white modal-responsive">
        <div class="mt-3">
            <h3 class="text-lg font-bold mb-4">Nouveau Client</h3>
            <form id="createClientForm" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium mb-1">Nom *</label>
                    <input type="text" name="nom" required class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Prénom</label>
                    <input type="text" name="prenom" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Email *</label>
                    <input type="email" name="email" required class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Téléphone</label>
                    <input type="tel" name="telephone" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Adresse</label>
                    <input type="text" name="adresse" class="w-full px-3 py-2 border rounded-lg" placeholder="Numéro et nom de rue">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm font-medium mb-1">Code postal</label>
                        <input type="text" name="code_postal" class="w-full px-3 py-2 border rounded-lg" placeholder="35000">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Ville</label>
                        <input type="text" name="ville" class="w-full px-3 py-2 border rounded-lg" placeholder="Rennes">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Pays</label>
                    <input type="text" name="pays" class="w-full px-3 py-2 border rounded-lg" placeholder="France" value="France">
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        Créer
                    </button>
                    <button type="button" onclick="hideCreateClientModal()" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal suppression client -->
<div id="deleteClientModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 md:top-20 mx-auto p-5 border w-11/12 md:w-96 shadow-lg rounded-md bg-white modal-responsive">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                    Supprimer le client
                </h3>
                <button onclick="hideDeleteClientModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="mb-4">
                <p class="text-sm text-gray-700 mb-4">
                    Cette action est <strong>irréversible</strong>. Le client <strong id="clientNameToDelete"></strong> sera définitivement supprimé.
                </p>
                <p class="text-sm font-semibold text-red-600 mb-2">
                    Mot de passe requis
                </p>
            </div>
            
            <form id="deleteClientForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="mb-4">
                    <input type="password" 
                           name="password" 
                           id="deleteClientPassword" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500" 
                           placeholder="Entrez le mot de passe"
                           required
                           autocomplete="off">
                    <p class="text-xs text-gray-500 mt-1">
                        Mot de passe requis pour confirmer cette action
                    </p>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="hideDeleteClientModal()" 
                            class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors duration-200">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200">
                        <i class="fas fa-trash-alt mr-2"></i>
                        Supprimer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showCreateClientModal() {
    document.getElementById('createClientModal').classList.remove('hidden');
}

function hideCreateClientModal() {
    document.getElementById('createClientModal').classList.add('hidden');
}

function showDeleteClientModal(clientId, clientName) {
    document.getElementById('clientNameToDelete').textContent = clientName;
    document.getElementById('deleteClientForm').action = '{{ route("admin.clients.destroy", ":id") }}'.replace(':id', clientId);
    document.getElementById('deleteClientModal').classList.remove('hidden');
    document.getElementById('deleteClientPassword').focus();
}

function hideDeleteClientModal() {
    document.getElementById('deleteClientModal').classList.add('hidden');
    document.getElementById('deleteClientForm').reset();
}

document.getElementById('createClientForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    try {
        const response = await fetch('{{ route("admin.clients.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur : ' + (data.message || 'Erreur inconnue'));
        }
    } catch (error) {
        alert('Erreur : ' + error.message);
    }
});
</script>
@endpush
@endsection

