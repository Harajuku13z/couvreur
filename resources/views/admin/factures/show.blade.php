@extends('layouts.admin')

@section('title', 'Facture ' . $facture->numero)
@section('page_title', 'Facture ' . $facture->numero)

@section('content')
<div class="p-6">
    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('admin.factures.index') }}" class="text-blue-600 hover:text-blue-900">
            <i class="fas fa-arrow-left mr-2"></i>Retour à la liste
        </a>
        <div class="flex gap-2">
            @if($facture->statut === 'Impayée')
            <form action="{{ route('admin.factures.mark-paid', $facture->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" 
                        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700"
                        onclick="return confirm('Marquer cette facture comme payée ?')">
                    <i class="fas fa-check mr-2"></i>Marquer comme payée
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-2 gap-6">
            <div>
                <h3 class="font-semibold mb-2">Client</h3>
                <p>{{ $facture->client->nom_complet }}</p>
                <p class="text-sm text-gray-600">{{ $facture->client->email }}</p>
                <p class="text-sm text-gray-600">{{ $facture->client->telephone }}</p>
            </div>
            <div>
                <h3 class="font-semibold mb-2">Informations</h3>
                <p><strong>Numéro :</strong> {{ $facture->numero }}</p>
                <p><strong>Date d'émission :</strong> {{ $facture->date_emission->format('d/m/Y') }}</p>
                <p><strong>Date d'échéance :</strong> {{ $facture->date_echeance ? $facture->date_echeance->format('d/m/Y') : '-' }}</p>
                @if($facture->date_paiement)
                <p><strong>Date de paiement :</strong> {{ $facture->date_paiement->format('d/m/Y') }}</p>
                @endif
                <p><strong>Statut :</strong> 
                    <span class="px-2 py-1 rounded text-xs font-semibold 
                        {{ $facture->statut === 'Payée' ? 'bg-green-100 text-green-800' : 
                           ($facture->statut === 'Impayée' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                        {{ $facture->statut }}
                    </span>
                    @if($facture->isOverdue())
                    <span class="ml-2 text-red-600 font-semibold">(En retard)</span>
                    @endif
                </p>
                @if($facture->devis)
                <p><strong>Devis associé :</strong> 
                    <a href="{{ route('admin.devis.show', $facture->devis_id) }}" class="text-blue-600 hover:underline">
                        {{ $facture->devis->numero }}
                    </a>
                </p>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="font-semibold mb-4">Détails de la facture</h3>
        <div class="space-y-2">
            <div class="flex justify-between">
                <span>Total HT</span>
                <span class="font-medium">{{ number_format($facture->prix_total_ht, 2, ',', ' ') }} €</span>
            </div>
            <div class="flex justify-between">
                <span>TVA ({{ $facture->taux_tva }}%)</span>
                <span class="font-medium">{{ number_format($facture->prix_total_ttc - $facture->prix_total_ht, 2, ',', ' ') }} €</span>
            </div>
            <div class="flex justify-between pt-2 border-t border-gray-200">
                <span class="font-bold text-lg">Total TTC</span>
                <span class="font-bold text-lg">{{ number_format($facture->prix_total_ttc, 2, ',', ' ') }} €</span>
            </div>
        </div>
    </div>

    @if($facture->notes)
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-semibold mb-2">Notes</h3>
        <p class="text-gray-700 whitespace-pre-line">{{ $facture->notes }}</p>
    </div>
    @endif
</div>
@endsection

