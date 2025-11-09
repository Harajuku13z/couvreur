@extends('layouts.admin')

@section('title', 'Tableau de Bord Devis & Facturation')

@section('content')
<div class="p-4 md:p-6">
    <!-- Menu rapide mobile -->
    <div class="md:hidden mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Accès rapide</h2>
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('admin.devis.create') }}" 
               class="bg-white rounded-lg shadow p-4 flex flex-col items-center justify-center hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-plus text-white text-xl"></i>
                </div>
                <span class="text-sm font-medium text-gray-700 text-center">Créer un devis</span>
            </a>
            
            <a href="{{ route('admin.factures.index') }}" 
               class="bg-white rounded-lg shadow p-4 flex flex-col items-center justify-center hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-receipt text-white text-xl"></i>
                </div>
                <span class="text-sm font-medium text-gray-700 text-center">Factures</span>
            </a>
            
            <a href="{{ route('admin.clients.index') }}" 
               class="bg-white rounded-lg shadow p-4 flex flex-col items-center justify-center hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-users text-white text-xl"></i>
                </div>
                <span class="text-sm font-medium text-gray-700 text-center">Clients</span>
            </a>
            
            <a href="{{ route('admin.submissions') }}" 
               class="bg-white rounded-lg shadow p-4 flex flex-col items-center justify-center hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-indigo-500 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-file-alt text-white text-xl"></i>
                </div>
                <span class="text-sm font-medium text-gray-700 text-center">Soumissions</span>
            </a>
        </div>
    </div>

    <!-- Statistiques principales -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- CA Total -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-euro-sign text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-5">
                    <p class="text-sm font-medium text-gray-500">Chiffre d'Affaire Total</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalCA, 2, ',', ' ') }} €</p>
                </div>
            </div>
        </div>

        <!-- CA Potentiel -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-5">
                    <p class="text-sm font-medium text-gray-500">CA Potentiel</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($caPotentiel, 2, ',', ' ') }} €</p>
                </div>
            </div>
        </div>

        <!-- Taux de conversion -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-percentage text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-5">
                    <p class="text-sm font-medium text-gray-500">Taux de Conversion</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($tauxConversion, 1) }}%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques par statut -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Stats Devis -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Statistiques Devis</h2>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Brouillon</span>
                    <span class="font-bold text-gray-900">{{ $statsDevis['Brouillon'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">En Attente</span>
                    <span class="font-bold text-yellow-600">{{ $statsDevis['En Attente'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Accepté</span>
                    <span class="font-bold text-green-600">{{ $statsDevis['Accepté'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Refusé</span>
                    <span class="font-bold text-red-600">{{ $statsDevis['Refusé'] ?? 0 }}</span>
                </div>
            </div>
        </div>

        <!-- Stats Factures -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Statistiques Factures</h2>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Impayée</span>
                    <span class="font-bold text-red-600">{{ $statsFactures['Impayée'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Payée</span>
                    <span class="font-bold text-green-600">{{ $statsFactures['Payée'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Annulée</span>
                    <span class="font-bold text-gray-600">{{ $statsFactures['Annulée'] ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Factures en attente -->
    @if($facturesEnAttente->count() > 0)
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-lg font-semibold mb-4">Factures en Attente</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Numéro</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Échéance</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($facturesEnAttente as $facture)
                    <tr>
                        <td class="px-4 py-3 text-sm">{{ $facture->numero }}</td>
                        <td class="px-4 py-3 text-sm">{{ $facture->client->nom_complet ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if($facture->date_echeance)
                                {{ $facture->date_echeance->format('d/m/Y') }}
                                @if($facture->isOverdue())
                                    <span class="text-red-600 font-semibold">(En retard)</span>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm font-medium">{{ number_format($facture->prix_total_ttc, 2, ',', ' ') }} €</td>
                        <td class="px-4 py-3 text-sm">
                            <a href="{{ route('admin.factures.show', $facture->id) }}" class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Derniers devis et factures -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Derniers devis -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Derniers Devis</h2>
                <a href="{{ route('admin.devis.index') }}" class="text-blue-600 hover:text-blue-900 text-sm">
                    Voir tout →
                </a>
            </div>
            <div class="space-y-3">
                @forelse($derniersDevis as $devis)
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                    <div>
                        <p class="font-medium text-sm">{{ $devis->numero }}</p>
                        <p class="text-xs text-gray-500">{{ $devis->client->nom_complet ?? '-' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium">{{ number_format($devis->total_ttc, 2, ',', ' ') }} €</p>
                        <span class="text-xs px-2 py-1 rounded {{ $devis->statut === 'Accepté' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $devis->statut }}
                        </span>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-sm">Aucun devis</p>
                @endforelse
            </div>
        </div>

        <!-- Dernières factures -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Dernières Factures</h2>
                <a href="{{ route('admin.factures.index') }}" class="text-blue-600 hover:text-blue-900 text-sm">
                    Voir tout →
                </a>
            </div>
            <div class="space-y-3">
                @forelse($dernieresFactures as $facture)
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                    <div>
                        <p class="font-medium text-sm">{{ $facture->numero }}</p>
                        <p class="text-xs text-gray-500">{{ $facture->client->nom_complet ?? '-' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium">{{ number_format($facture->prix_total_ttc, 2, ',', ' ') }} €</p>
                        <span class="text-xs px-2 py-1 rounded {{ $facture->statut === 'Payée' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $facture->statut }}
                        </span>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-sm">Aucune facture</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

