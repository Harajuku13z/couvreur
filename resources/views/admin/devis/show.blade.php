@extends('layouts.admin')

@section('title', 'Devis ' . $devis->numero)
@section('page_title', 'Devis ' . $devis->numero)

@section('content')
<div class="p-4 md:p-6">
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <a href="{{ route('admin.devis.index') }}" class="text-blue-600 hover:text-blue-900">
            <i class="fas fa-arrow-left mr-2"></i>Retour à la liste
        </a>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            @if($devis->statut === 'En Attente')
            <form action="{{ route('admin.devis.validate', $devis->id) }}" method="POST" class="inline w-full sm:w-auto">
                @csrf
                <button type="submit" 
                        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 w-full sm:w-auto text-center"
                        onclick="return confirm('Valider ce devis et créer la facture ?')">
                    <i class="fas fa-check mr-2"></i>Valider le devis
                </button>
            </form>
            @endif
            <a href="{{ route('admin.devis.edit', $devis->id) }}" 
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-center w-full sm:w-auto">
                <i class="fas fa-edit mr-2"></i>Modifier
            </a>
        </div>
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
    <div class="bg-white rounded-lg shadow p-4 md:p-6 mb-6">
        <h3 class="font-semibold mb-4">Actions</h3>
        <div class="flex flex-col sm:flex-row flex-wrap gap-3">
            <a href="{{ route('admin.devis.pdf', $devis->id) }}" 
               target="_blank"
               class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-center w-full sm:w-auto">
                <i class="fas fa-file-pdf mr-2"></i>Voir le PDF
            </a>
            <a href="{{ route('admin.devis.download-pdf', $devis->id) }}" 
               class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition text-center w-full sm:w-auto">
                <i class="fas fa-download mr-2"></i>Télécharger le PDF
            </a>
            @if($devis->client->email)
            <form action="{{ route('admin.devis.send-email', $devis->id) }}" method="POST" class="inline w-full sm:w-auto">
                @csrf
                <button type="submit" 
                        class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition w-full sm:w-auto"
                        onclick="return confirm('Envoyer le devis par email à {{ $devis->client->email }} ?')">
                    <i class="fas fa-envelope mr-2"></i>Envoyer par email
                </button>
            </form>
            @php
                $publicPdfUrl = $devis->getPublicPdfUrl();
                $emailBody = 'Bonjour,' . "\n\n" . 
                             'Veuillez trouver ci-joint notre devis ' . $devis->numero . '.' . "\n\n" . 
                             'Vous pouvez le consulter en ligne : ' . $publicPdfUrl . "\n\n" . 
                             'Cordialement';
                $emailBodyEncoded = rawurlencode($emailBody);
                $subjectEncoded = rawurlencode('Devis ' . $devis->numero);
            @endphp
            <a href="mailto:{{ $devis->client->email }}?subject={{ $subjectEncoded }}&body={{ $emailBodyEncoded }}" 
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-center w-full sm:w-auto">
                <i class="fas fa-envelope-open-text mr-2"></i>Écrire un email
            </a>
            @endif
            @if($devis->client->telephone)
            @php
                // Nettoyer le numéro de téléphone (garder uniquement les chiffres)
                $phoneNumber = preg_replace('/[^0-9+]/', '', $devis->client->telephone);
                
                // Retirer le + si présent
                $phoneNumber = str_replace('+', '', $phoneNumber);
                
                // Si le numéro commence par 0, le remplacer par 33 (code pays France)
                if (substr($phoneNumber, 0, 1) === '0') {
                    $phoneNumber = '33' . substr($phoneNumber, 1);
                }
                // Si le numéro commence déjà par 33, on le garde tel quel
                // Sinon, on suppose que c'est un numéro français et on ajoute 33
                elseif (substr($phoneNumber, 0, 2) !== '33' && strlen($phoneNumber) === 9) {
                    $phoneNumber = '33' . $phoneNumber;
                }
                
                // Message avec lien public vers le devis PDF (avec token)
                $pdfUrl = $devis->getPublicPdfUrl();
                $whatsappMessage = 'Bonjour, voici votre devis ' . $devis->numero . ' : ' . $pdfUrl;
                $whatsappUrl = 'https://wa.me/' . $phoneNumber . '?text=' . urlencode($whatsappMessage);
            @endphp
            <a href="{{ $whatsappUrl }}" 
               target="_blank"
               rel="noopener noreferrer"
               class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition text-center w-full sm:w-auto">
                <i class="fab fa-whatsapp mr-2"></i>Envoyer sur WhatsApp
            </a>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4 md:p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
    <div class="bg-white rounded-lg shadow p-4 md:p-6 mb-6">
        <h3 class="font-semibold mb-2">Description du projet</h3>
        <p class="text-gray-700">{{ $devis->description_globale }}</p>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow p-4 md:p-6 mb-6">
        <h3 class="font-semibold mb-4">Lignes de devis</h3>
        
        <!-- Vue mobile : Cartes -->
        <div class="md:hidden space-y-4">
            @foreach($devis->lignesDevis as $ligne)
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="mb-2">
                    <h4 class="font-medium text-gray-900">{{ $ligne->description }}</h4>
                </div>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Quantité :</span>
                        <span class="font-medium">{{ $ligne->quantite }} {{ $ligne->unite }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Prix unitaire :</span>
                        <span class="font-medium">{{ number_format($ligne->prix_unitaire, 2, ',', ' ') }} €</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-gray-200">
                        <span class="font-semibold text-gray-900">Total :</span>
                        <span class="font-bold text-lg">{{ number_format($ligne->total_ligne, 2, ',', ' ') }} €</span>
                    </div>
                </div>
            </div>
            @endforeach
            
            <!-- Totaux mobile -->
            <div class="border-t-2 border-gray-300 pt-4 mt-4 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="font-semibold">Total HT :</span>
                    <span class="font-semibold">{{ number_format($devis->total_ht, 2, ',', ' ') }} €</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span>TVA ({{ $devis->taux_tva }}%) :</span>
                    <span>{{ number_format($devis->total_ttc - $devis->total_ht, 2, ',', ' ') }} €</span>
                </div>
                <div class="flex justify-between pt-2 border-t border-gray-200">
                    <span class="font-bold text-lg">Total TTC :</span>
                    <span class="font-bold text-lg">{{ number_format($devis->total_ttc, 2, ',', ' ') }} €</span>
                </div>
                @if($devis->acompte_pourcentage && $devis->acompte_pourcentage > 0)
                <div class="bg-blue-50 rounded-lg p-3 mt-3 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="font-semibold text-blue-800">Acompte ({{ $devis->acompte_pourcentage }}%) :</span>
                        <span class="font-semibold text-blue-800">{{ number_format($devis->acompte_montant ?? 0, 2, ',', ' ') }} €</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-blue-200">
                        <span class="font-bold text-blue-900">Reste à payer :</span>
                        <span class="font-bold text-blue-900">{{ number_format($devis->reste_a_payer ?? $devis->total_ttc, 2, ',', ' ') }} €</span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Vue desktop : Table -->
        <div class="hidden md:block overflow-x-auto table-responsive">
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
                    @if($devis->acompte_pourcentage && $devis->acompte_pourcentage > 0)
                    <tr class="bg-blue-50">
                        <td colspan="3" class="px-4 py-3 text-right font-semibold text-blue-800">Acompte ({{ $devis->acompte_pourcentage }}%)</td>
                        <td class="px-4 py-3 text-right font-semibold text-blue-800">{{ number_format($devis->acompte_montant ?? 0, 2, ',', ' ') }} €</td>
                    </tr>
                    <tr class="bg-blue-50">
                        <td colspan="3" class="px-4 py-3 text-right font-bold text-blue-900">Reste à payer</td>
                        <td class="px-4 py-3 text-right font-bold text-blue-900">{{ number_format($devis->reste_a_payer ?? $devis->total_ttc, 2, ',', ' ') }} €</td>
                    </tr>
                    @endif
                </tfoot>
            </table>
        </div>
    </div>

    @if($devis->conditions_particulieres)
    <div class="bg-white rounded-lg shadow p-4 md:p-6">
        <h3 class="font-semibold mb-2">Conditions particulières</h3>
        <p class="text-gray-700 whitespace-pre-line">{{ $devis->conditions_particulieres }}</p>
    </div>
    @endif
</div>
@endsection

