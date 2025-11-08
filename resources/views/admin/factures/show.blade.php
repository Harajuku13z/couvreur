@extends('layouts.admin')

@section('title', 'Facture ' . $facture->numero)
@section('page_title', 'Facture ' . $facture->numero)

@section('content')
<div class="p-6">
    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('admin.factures.index') }}" class="text-blue-600 hover:text-blue-900">
            <i class="fas fa-arrow-left mr-2"></i>Retour à la liste
        </a>
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

    <!-- Actions PDF et Envoi -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="font-semibold mb-4">Actions</h3>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.factures.pdf', $facture->id) }}" 
               target="_blank"
               class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                <i class="fas fa-file-pdf mr-2"></i>Voir le PDF
            </a>
            <a href="{{ route('admin.factures.download-pdf', $facture->id) }}" 
               class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                <i class="fas fa-download mr-2"></i>Télécharger le PDF
            </a>
            @if($facture->client->email)
            <form action="{{ route('admin.factures.send-email', $facture->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" 
                        class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition"
                        onclick="return confirm('Envoyer la facture par email à {{ $facture->client->email }} ?')">
                    <i class="fas fa-envelope mr-2"></i>Envoyer par email
                </button>
            </form>
            <a href="mailto:{{ $facture->client->email }}?subject=Facture {{ $facture->numero }}&body=Bonjour,%0D%0A%0D%0AVeuillez trouver ci-joint notre facture {{ $facture->numero }}.%0D%0A%0D%0ACordialement" 
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-envelope-open-text mr-2"></i>Écrire un email
            </a>
            @if($facture->statut !== 'Payée')
            <form action="{{ route('admin.factures.send-reminder', $facture->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" 
                        class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition"
                        onclick="return confirm('Envoyer une relance pour cette facture impayée ?')">
                    <i class="fas fa-bell mr-2"></i>Envoyer une relance
                </button>
            </form>
            @endif
            @endif
            @if($facture->client->telephone)
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $facture->client->telephone) }}?text={{ urlencode('Bonjour, voici votre facture ' . $facture->numero . ' : ' . url(route('admin.factures.pdf', $facture->id))) }}" 
               target="_blank"
               class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                <i class="fab fa-whatsapp mr-2"></i>Envoyer sur WhatsApp
            </a>
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
            @if($facture->montant_paye > 0)
            <div class="flex justify-between pt-2 border-t border-gray-200">
                <span>Montant payé</span>
                <span class="font-medium text-green-600">{{ number_format($facture->montant_paye, 2, ',', ' ') }} €</span>
            </div>
            <div class="flex justify-between pt-2 border-t-2 border-gray-300">
                <span class="font-bold">Reste à payer</span>
                <span class="font-bold text-lg text-red-600">{{ number_format($facture->montant_restant, 2, ',', ' ') }} €</span>
            </div>
            @endif
        </div>
        
        @if($facture->statut !== 'Payée')
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h4 class="font-semibold mb-3">Enregistrer un paiement</h4>
            <form action="{{ route('admin.factures.record-payment', $facture->id) }}" method="POST" class="flex gap-3">
                @csrf
                <input type="number" 
                       name="montant" 
                       step="0.01" 
                       min="0.01" 
                       max="{{ $facture->montant_restant }}" 
                       value="{{ $facture->montant_restant }}"
                       class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Montant">
                <button type="submit" 
                        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-money-bill-wave mr-2"></i>Enregistrer le paiement
                </button>
            </form>
            <p class="text-sm text-gray-500 mt-2">Reste à payer : <strong>{{ number_format($facture->montant_restant, 2, ',', ' ') }} €</strong></p>
        </div>
        @endif
        
        @if($facture->statut === 'Impayée' || $facture->statut === 'Partiellement payée')
        <div class="mt-6 pt-6 border-t border-gray-200">
            <form action="{{ route('admin.factures.mark-paid', $facture->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" 
                        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition"
                        onclick="return confirm('Marquer cette facture comme payée ?')">
                    <i class="fas fa-check mr-2"></i>Marquer comme payée
                </button>
            </form>
        </div>
        @endif
        
        @if($facture->nombre_relances > 0)
        <div class="mt-4 pt-4 border-t border-gray-200">
            <p class="text-sm text-gray-600">
                <i class="fas fa-bell mr-2"></i>
                <strong>{{ $facture->nombre_relances }}</strong> relance(s) envoyée(s)
                @if($facture->derniere_relance)
                - Dernière relance : {{ $facture->derniere_relance->format('d/m/Y') }}
                @endif
            </p>
        </div>
        @endif
    </div>

    @if($facture->notes)
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-semibold mb-2">Notes</h3>
        <p class="text-gray-700 whitespace-pre-line">{{ $facture->notes }}</p>
    </div>
    @endif
</div>
@endsection

