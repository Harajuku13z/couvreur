@extends('layouts.admin')

@section('title', 'Gestion des Factures')
@section('page_title', 'Gestion des Factures')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Factures</h1>
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
        <form method="GET" action="{{ route('admin.factures.index') }}" class="flex gap-4 items-end">
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
            <button type="submit" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700">
                <i class="fas fa-search mr-2"></i>Filtrer
            </button>
        </form>
    </div>

    <!-- Liste des factures -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
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
                        <a href="{{ route('admin.factures.show', $facture->id) }}" 
                           class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($facture->statut === 'Impayée')
                        <form action="{{ route('admin.factures.mark-paid', $facture->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-green-600 hover:text-green-900" onclick="return confirm('Marquer cette facture comme payée ?')">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                        @endif
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
@endsection

