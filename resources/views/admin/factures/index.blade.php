@extends('layouts.admin')

@section('title', 'Gestion des Factures')
@section('page_title', 'Gestion des Factures')

@section('content')
<div class="p-4 md:p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h1 class="text-xl md:text-2xl font-bold">Factures</h1>
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

    <!-- Filtres -->
    <div class="mb-6 bg-white p-4 rounded-lg shadow">
        <form method="GET" action="{{ route('admin.factures.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium mb-1">Recherche</label>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Numéro, client..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Statut</label>
                <select name="statut" class="px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">Tous</option>
                    <option value="Impayée" {{ request('statut') == 'Impayée' ? 'selected' : '' }}>Impayée</option>
                    <option value="Payée" {{ request('statut') == 'Payée' ? 'selected' : '' }}>Payée</option>
                    <option value="Annulée" {{ request('statut') == 'Annulée' ? 'selected' : '' }}>Annulée</option>
                </select>
            </div>
            <button type="submit" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 w-full md:w-auto text-center">
                <i class="fas fa-search mr-2"></i>Filtrer
            </button>
        </form>
    </div>

    <!-- Vue mobile : Cartes -->
    <div class="md:hidden space-y-4">
        @forelse($factures as $facture)
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex justify-between items-start mb-3">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-sm font-medium text-gray-500">{{ $facture->numero }}</span>
                        @php
                            $statusColors = [
                                'Impayée' => 'bg-red-100 text-red-800',
                                'Payée' => 'bg-green-100 text-green-800',
                                'Annulée' => 'bg-gray-100 text-gray-800',
                            ];
                        @endphp
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$facture->statut] ?? 'bg-gray-100' }}">
                            {{ $facture->statut }}
                        </span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ $facture->client->nom_complet ?? 'Sans client' }}
                    </h3>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.factures.show', $facture->id) }}" 
                       class="text-blue-600 hover:text-blue-900 p-2"
                       title="Voir">
                        <i class="fas fa-eye"></i>
                    </a>
                    @if($facture->statut === 'Impayée')
                    <form action="{{ route('admin.factures.mark-paid', $facture->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-green-600 hover:text-green-900 p-2" onclick="return confirm('Marquer cette facture comme payée ?')" title="Marquer comme payée">
                            <i class="fas fa-check"></i>
                        </button>
                    </form>
                    @endif
                    @php
                        $requiresPassword = in_array($facture->statut, ['Payée', 'Partiellement payée']);
                    @endphp
                    <button onclick="showDeleteFactureModal({{ $facture->id }}, '{{ $facture->numero }}', {{ $requiresPassword ? 'true' : 'false' }})" 
                            class="text-red-600 hover:text-red-900 p-2"
                            title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            
            <div class="space-y-2 text-sm">
                <div class="flex items-center text-gray-600">
                    <i class="fas fa-calendar w-5 text-gray-400"></i>
                    <span>{{ $facture->date_emission->format('d/m/Y') }}</span>
                </div>
                <div class="flex items-center text-gray-600">
                    <i class="fas fa-euro-sign w-5 text-gray-400"></i>
                    <span class="font-semibold text-gray-900">{{ number_format($facture->prix_total_ttc, 2, ',', ' ') }} €</span>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">
            Aucune facture trouvée
        </div>
        @endforelse
    </div>

    <!-- Vue desktop : Table -->
    <div class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Numéro</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total TTC</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($factures as $facture)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $facture->numero }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $facture->client->nom_complet ?? '-' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $facture->date_emission->format('d/m/Y') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $statusColors = [
                                'Impayée' => 'bg-red-100 text-red-800',
                                'Payée' => 'bg-green-100 text-green-800',
                                'Annulée' => 'bg-gray-100 text-gray-800',
                            ];
                        @endphp
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$facture->statut] ?? 'bg-gray-100' }}">
                            {{ $facture->statut }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ number_format($facture->prix_total_ttc, 2, ',', ' ') }} €</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.factures.show', $facture->id) }}" 
                               class="text-blue-600 hover:text-blue-900"
                               title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($facture->statut === 'Impayée')
                            <form action="{{ route('admin.factures.mark-paid', $facture->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-900" onclick="return confirm('Marquer cette facture comme payée ?')" title="Marquer comme payée">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            @endif
                            @php
                                $requiresPassword = in_array($facture->statut, ['Payée', 'Partiellement payée']);
                            @endphp
                            <button onclick="showDeleteFactureModal({{ $facture->id }}, '{{ $facture->numero }}', {{ $requiresPassword ? 'true' : 'false' }})" 
                                    class="text-red-600 hover:text-red-900"
                                    title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        Aucune facture trouvée
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $factures->links() }}
    </div>
</div>

<!-- Modal suppression facture -->
<div id="deleteFactureModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 md:top-20 mx-auto p-5 border w-11/12 md:w-96 shadow-lg rounded-md bg-white modal-responsive">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                    Supprimer la facture
                </h3>
                <button onclick="hideDeleteFactureModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="mb-4">
                <p class="text-sm text-gray-700 mb-4">
                    Cette action est <strong>irréversible</strong>. La facture <strong id="factureNumeroToDelete"></strong> sera définitivement supprimée.
                </p>
                <div id="passwordRequiredMessageFacture" class="hidden">
                    <p class="text-sm font-semibold text-red-600 mb-2">
                        Mot de passe requis
                    </p>
                </div>
            </div>
            
            <form id="deleteFactureForm" method="POST">
                @csrf
                @method('DELETE')
                <div id="passwordFieldContainerFacture" class="mb-4 hidden">
                    <input type="password" 
                           name="password" 
                           id="deleteFacturePassword" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500" 
                           placeholder="Entrez le mot de passe"
                           autocomplete="off">
                    <p class="text-xs text-gray-500 mt-1">
                        Mot de passe requis pour confirmer cette action
                    </p>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="hideDeleteFactureModal()" 
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
function showDeleteFactureModal(factureId, factureNumero, requiresPassword) {
    document.getElementById('factureNumeroToDelete').textContent = factureNumero;
    document.getElementById('deleteFactureForm').action = '{{ route("admin.factures.destroy", ":id") }}'.replace(':id', factureId);
    
    const passwordContainer = document.getElementById('passwordFieldContainerFacture');
    const passwordMessage = document.getElementById('passwordRequiredMessageFacture');
    const passwordInput = document.getElementById('deleteFacturePassword');
    
    if (requiresPassword) {
        passwordContainer.classList.remove('hidden');
        passwordMessage.classList.remove('hidden');
        passwordInput.required = true;
        passwordInput.focus();
    } else {
        passwordContainer.classList.add('hidden');
        passwordMessage.classList.add('hidden');
        passwordInput.required = false;
        passwordInput.value = '';
    }
    
    document.getElementById('deleteFactureModal').classList.remove('hidden');
}

function hideDeleteFactureModal() {
    document.getElementById('deleteFactureModal').classList.add('hidden');
    document.getElementById('deleteFactureForm').reset();
}
</script>
@endpush
@endsection

