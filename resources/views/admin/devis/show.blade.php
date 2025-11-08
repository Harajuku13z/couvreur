@extends('layouts.admin')

@section('title', 'Devis ' . $devis->numero)
@section('page_title', 'Devis ' . $devis->numero)

@section('content')
<div class="p-6">
    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('admin.devis.index') }}" class="text-blue-600 hover:text-blue-900">
            <i class="fas fa-arrow-left mr-2"></i>Retour à la liste
        </a>
        <div class="flex gap-2">
            @if($devis->statut === 'En Attente')
            <form action="{{ route('admin.devis.validate', $devis->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" 
                        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700"
                        onclick="return confirm('Valider ce devis et créer la facture ?')">
                    <i class="fas fa-check mr-2"></i>Valider le devis
                </button>
            </form>
            @endif
            <a href="{{ route('admin.devis.edit', $devis->id) }}" 
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                <i class="fas fa-edit mr-2"></i>Modifier
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-2 gap-6">
            <div>
                <h3 class="font-semibold mb-2">Client</h3>
                <p>{{ $devis->client->nom_complet }}</p>
                <p class="text-sm text-gray-600">{{ $devis->client->email }}</p>
                <p class="text-sm text-gray-600">{{ $devis->client->telephone }}</p>
            </div>
            <div>
                <h3 class="font-semibold mb-2">Informations</h3>
                <p><strong>Numéro :</strong> {{ $devis->numero }}</p>
                <p><strong>Date d'émission :</strong> {{ $devis->date_emission->format('d/m/Y') }}</p>
                <p><strong>Date de validité :</strong> {{ $devis->date_validite ? $devis->date_validite->format('d/m/Y') : '-' }}</p>
                <p><strong>Statut :</strong> 
                    <span class="px-2 py-1 rounded text-xs font-semibold 
                        {{ $devis->statut === 'Accepté' ? 'bg-green-100 text-green-800' : 
                           ($devis->statut === 'En Attente' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                        {{ $devis->statut }}
                    </span>
                </p>
            </div>
        </div>
    </div>

    @if($devis->description_globale)
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="font-semibold mb-2">Description du projet</h3>
        <p class="text-gray-700">{{ $devis->description_globale }}</p>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="font-semibold mb-4">Lignes de devis</h3>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantité</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prix unitaire</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($devis->lignesDevis as $ligne)
                <tr>
                    <td class="px-4 py-3 text-sm">{{ $ligne->description }}</td>
                    <td class="px-4 py-3 text-sm">{{ $ligne->quantite }} {{ $ligne->unite }}</td>
                    <td class="px-4 py-3 text-sm">{{ number_format($ligne->prix_unitaire, 2, ',', ' ') }} €</td>
                    <td class="px-4 py-3 text-sm text-right font-medium">{{ number_format($ligne->total_ligne, 2, ',', ' ') }} €</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="3" class="px-4 py-3 text-right font-semibold">Total HT</td>
                    <td class="px-4 py-3 text-right font-semibold">{{ number_format($devis->total_ht, 2, ',', ' ') }} €</td>
                </tr>
                <tr>
                    <td colspan="3" class="px-4 py-3 text-right">TVA ({{ $devis->taux_tva }}%)</td>
                    <td class="px-4 py-3 text-right">{{ number_format($devis->total_ttc - $devis->total_ht, 2, ',', ' ') }} €</td>
                </tr>
                <tr>
                    <td colspan="3" class="px-4 py-3 text-right font-bold text-lg">Total TTC</td>
                    <td class="px-4 py-3 text-right font-bold text-lg">{{ number_format($devis->total_ttc, 2, ',', ' ') }} €</td>
                </tr>
            </tfoot>
        </table>
    </div>

    @if($devis->conditions_particulieres)
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-semibold mb-2">Conditions particulières</h3>
        <p class="text-gray-700 whitespace-pre-line">{{ $devis->conditions_particulieres }}</p>
    </div>
    @endif
</div>
@endsection

