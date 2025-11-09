@extends('layouts.admin')

@section('title', 'Créer un Devis')
@section('page_title', 'Créer un Devis')

@section('content')
<div class="p-4 md:p-6">
    <div class="mb-6">
        <a href="{{ route('admin.devis.index') }}" class="text-blue-600 hover:text-blue-900">
            <i class="fas fa-arrow-left mr-2"></i>Retour à la liste
        </a>
    </div>

    <form action="{{ route('admin.devis.store') }}" method="POST" id="devisForm">
        @csrf
        
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Informations Client</h2>
            
            @if($selectedClient)
            <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-blue-900">{{ $selectedClient->nom_complet }}</p>
                        <p class="text-sm text-blue-700">{{ $selectedClient->email }}</p>
                        @if($selectedClient->telephone)
                        <p class="text-sm text-blue-700">{{ $selectedClient->telephone }}</p>
                        @endif
                        @if($selectedClient->adresse_complete)
                        <p class="text-sm text-blue-700 mt-1">{{ $selectedClient->adresse_complete }}</p>
                        @endif
                    </div>
                    <button type="button" onclick="document.getElementById('client_id').value = ''; this.closest('.bg-blue-50').remove();" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            @endif
            
            <div class="mb-4">
                <label for="client_id" class="block text-sm font-medium mb-2">Client *</label>
                <select id="client_id" name="client_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">Sélectionner un client</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ (isset($selectedClientId) && $selectedClientId == $client->id) ? 'selected' : '' }}>
                        {{ $client->nom_complet }} - {{ $client->email }}
                    </option>
                    @endforeach
                </select>
                <a href="{{ route('admin.clients.index') }}" class="text-sm text-blue-600 hover:underline mt-1 inline-block">
                    <i class="fas fa-plus mr-1"></i>Créer un nouveau client
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Description du Projet</h2>
            
            <div class="mb-4">
                <label for="description_globale" class="block text-sm font-medium mb-2">Description globale des travaux *</label>
                <textarea id="description_globale" 
                          name="description_globale" 
                          rows="4"
                          required
                          placeholder="Ex: Rénovation complète d'une toiture de 150m²..."
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="superficie_totale" class="block text-sm font-medium mb-2">Superficie totale</label>
                    <input type="text" 
                           id="superficie_totale" 
                           name="superficie_totale"
                           placeholder="Ex: 150 m²"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label for="prix_final_estime" class="block text-sm font-medium mb-2">Prix final estimé (€)</label>
                    <input type="number" 
                           id="prix_final_estime" 
                           name="prix_final_estime"
                           step="0.01"
                           placeholder="22500"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
            </div>

            <button type="button" 
                    onclick="generateLinesWithAI()" 
                    class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition">
                <i class="fas fa-magic mr-2"></i>Générer les lignes avec l'IA
            </button>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Lignes de Devis</h2>
            <div id="lignes-container" class="space-y-4">
                <!-- Les lignes seront ajoutées ici dynamiquement -->
            </div>
            <div class="mt-4 flex gap-2">
                <button type="button" 
                        onclick="addLigne()" 
                        class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                    <i class="fas fa-plus mr-2"></i>Ajouter une ligne
                </button>
                <button type="button" 
                        onclick="addStandardLines()" 
                        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700"
                        title="Ajouter les lignes standard (nettoyage, évacuation, assurance)">
                    <i class="fas fa-magic mr-2"></i>Ajouter lignes standard
                </button>
            </div>
            <p class="text-xs text-gray-500 mt-2">
                <i class="fas fa-info-circle mr-1"></i>
                Les lignes standard (nettoyage, évacuation, assurance) sont automatiquement ajoutées lors de la génération IA.
            </p>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Paramètres</h2>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="statut" class="block text-sm font-medium mb-2">Statut</label>
                    <select id="statut" name="statut" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="Brouillon">Brouillon</option>
                        <option value="En Attente">En Attente</option>
                    </select>
                </div>
                <div>
                    <label for="taux_tva" class="block text-sm font-medium mb-2">Taux TVA (%)</label>
                    <input type="number" 
                           id="taux_tva" 
                           name="taux_tva"
                           value="20.00"
                           step="0.01"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
            </div>

            <div class="mb-4">
                <label for="date_validite" class="block text-sm font-medium mb-2">Date de validité</label>
                <input type="date" 
                       id="date_validite" 
                       name="date_validite"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>

            <!-- Acompte -->
            <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="text-md font-semibold mb-3 text-blue-800">💳 Acompte</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="acompte_pourcentage" class="block text-sm font-medium mb-2">Pourcentage d'acompte (%)</label>
                        <input type="number" 
                               id="acompte_pourcentage" 
                               name="acompte_pourcentage"
                               value=""
                               step="0.01"
                               min="0"
                               max="100"
                               onchange="calculateAcompte()"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <p class="text-xs text-gray-500 mt-1">Ex: 30 pour 30%</p>
                    </div>
                    <div>
                        <label for="acompte_montant" class="block text-sm font-medium mb-2">Montant acompte (€)</label>
                        <input type="number" 
                               id="acompte_montant" 
                               name="acompte_montant"
                               value=""
                               step="0.01"
                               min="0"
                               readonly
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                        <p class="text-xs text-gray-500 mt-1">Calculé automatiquement</p>
                    </div>
                    <div>
                        <label for="reste_a_payer" class="block text-sm font-medium mb-2">Reste à payer (€)</label>
                        <input type="number" 
                               id="reste_a_payer" 
                               name="reste_a_payer"
                               value=""
                               step="0.01"
                               min="0"
                               readonly
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                        <p class="text-xs text-gray-500 mt-1">Calculé automatiquement</p>
                    </div>
                </div>
            </div>

            <div>
                <label for="conditions_particulieres" class="block text-sm font-medium mb-2">Conditions particulières</label>
                <textarea id="conditions_particulieres" 
                          name="conditions_particulieres" 
                          rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i>Enregistrer le devis
            </button>
            <a href="{{ route('admin.devis.index') }}" class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400">
                Annuler
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
let ligneIndex = 0;

function addLigne(ligne = null) {
    const container = document.getElementById('lignes-container');
    const index = ligneIndex++;
    
    const ligneHtml = `
        <div class="border border-gray-200 rounded-lg p-4 ligne-item" data-index="${index}">
            <!-- Ligne 1: Description -->
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Description *</label>
                <textarea name="lignes[${index}][description]" 
                          required
                          rows="2"
                          class="w-full px-3 py-2 border border-gray-300 rounded resize-y">${ligne ? ligne.description : ''}</textarea>
            </div>
            
            <!-- Ligne 2: Quantité, Unité et Prix unitaire -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
                <div>
                    <label class="block text-sm font-medium mb-1">Quantité *</label>
                    <input type="number" 
                           name="lignes[${index}][quantite]" 
                           value="${ligne ? ligne.quantite : ''}"
                           step="0.01"
                           required
                           oninput="calculateLigneTotal(${index})"
                           class="w-full px-3 py-2 border border-gray-300 rounded">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Unité *</label>
                    <input type="text" 
                           name="lignes[${index}][unite]" 
                           value="${ligne ? ligne.unite : 'unité'}"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Prix unitaire (€) *</label>
                    <input type="number" 
                           name="lignes[${index}][prix_unitaire]" 
                           value="${ligne ? ligne.prix_unitaire : ''}"
                           step="0.01"
                           required
                           oninput="calculateLigneTotal(${index})"
                           class="w-full px-3 py-2 border border-gray-300 rounded">
                </div>
            </div>
            
            <!-- Ligne 3: Total -->
            <div class="flex justify-between items-center">
                <div class="flex-1">
                    <label class="block text-sm font-medium mb-1">Total (€)</label>
                    <input type="text" 
                           name="lignes[${index}][total]" 
                           id="ligne-total-${index}"
                           readonly
                           value="0.00"
                           class="w-full px-3 py-2 border border-gray-300 rounded bg-gray-100 font-semibold">
                </div>
                <div class="ml-4 flex items-end">
                    <button type="button" 
                            onclick="removeLigne(${index})" 
                            class="text-red-600 hover:text-red-900 p-2"
                            title="Supprimer cette ligne">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', ligneHtml);
    
    // Calculer le total initial si des valeurs sont présentes (après insertion dans le DOM)
    setTimeout(() => {
        if (ligne) {
            calculateLigneTotal(index);
        }
    }, 0);
    
    // Ajouter les listeners pour recalculer l'acompte
    updateAcompteOnLigneChange();
}

// Calculer le total d'une ligne
function calculateLigneTotal(index) {
    const ligneItem = document.querySelector(`.ligne-item[data-index="${index}"]`);
    if (!ligneItem) return;
    
    const quantite = parseFloat(ligneItem.querySelector('input[name*="[quantite]"]').value) || 0;
    const prixUnitaire = parseFloat(ligneItem.querySelector('input[name*="[prix_unitaire]"]').value) || 0;
    const total = quantite * prixUnitaire;
    
    const totalInput = document.getElementById(`ligne-total-${index}`);
    if (totalInput) {
        totalInput.value = total.toFixed(2);
    }
    
    // Recalculer l'acompte
    calculateAcompte();
}

function removeLigne(index) {
    document.querySelector(`.ligne-item[data-index="${index}"]`).remove();
    // Recalculer l'acompte après suppression
    calculateAcompte();
}

async function generateLinesWithAI() {
    const description = document.getElementById('description_globale').value;
    const superficie = document.getElementById('superficie_totale').value;
    const prixEstime = document.getElementById('prix_final_estime').value;
    
    if (!description) {
        alert('Veuillez saisir une description des travaux');
        return;
    }
    
    const button = event.target;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Génération en cours...';
    
    try {
        const response = await fetch('{{ route("admin.devis.generate-lines") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                description_globale: description,
                superficie_totale: superficie,
                prix_final_estime: prixEstime ? parseFloat(prixEstime) : null
            })
        });
        
        const data = await response.json();
        
        if (data.success && data.lines) {
            // Vider les lignes existantes
            document.getElementById('lignes-container').innerHTML = '';
            ligneIndex = 0;
            
            // Ajouter les nouvelles lignes
            data.lines.forEach(ligne => {
                addLigne(ligne);
            });
            
            alert('Lignes générées avec succès !');
        } else {
            alert('Erreur : ' + (data.message || 'Erreur inconnue'));
        }
    } catch (error) {
        alert('Erreur : ' + error.message);
    } finally {
        button.disabled = false;
        button.innerHTML = originalText;
    }
}

// Ajouter les lignes standard
function addStandardLines() {
    const standardLines = [
        {
            description: 'Nettoyage et remise en état du chantier - Chantier rendu propre',
            quantite: 1,
            unite: 'lot',
            prix_unitaire: 150.00
        },
        {
            description: 'Évacuation des déchets et gravats vers déchetterie agréée',
            quantite: 1,
            unite: 'lot',
            prix_unitaire: 200.00
        },
        {
            description: 'Assurance décennale et garantie de parfait achèvement',
            quantite: 1,
            unite: 'lot',
            prix_unitaire: 0.00
        }
    ];
    
    standardLines.forEach(ligne => {
        addLigne(ligne);
    });
}

// Ajouter une ligne vide au chargement pour permettre la saisie manuelle
// Calculer l'acompte et le reste à payer
function calculateAcompte() {
    const pourcentage = parseFloat(document.getElementById('acompte_pourcentage').value) || 0;
    
    // Calculer le total TTC depuis les lignes
    let totalHT = 0;
    const lignes = document.querySelectorAll('.ligne-item');
    lignes.forEach(ligne => {
        const quantite = parseFloat(ligne.querySelector('input[name*="[quantite]"]').value) || 0;
        const prixUnitaire = parseFloat(ligne.querySelector('input[name*="[prix_unitaire]"]').value) || 0;
        totalHT += quantite * prixUnitaire;
    });
    
    const tauxTVA = parseFloat(document.getElementById('taux_tva').value) || 20;
    const totalTTC = totalHT * (1 + tauxTVA / 100);
    
    if (pourcentage > 0 && totalTTC > 0) {
        const acompteMontant = totalTTC * (pourcentage / 100);
        const resteAPayer = totalTTC - acompteMontant;
        
        document.getElementById('acompte_montant').value = acompteMontant.toFixed(2);
        document.getElementById('reste_a_payer').value = resteAPayer.toFixed(2);
    } else {
        document.getElementById('acompte_montant').value = '';
        document.getElementById('reste_a_payer').value = totalTTC > 0 ? totalTTC.toFixed(2) : '';
    }
}

// Recalculer l'acompte quand les lignes changent
function updateAcompteOnLigneChange() {
    // Les événements oninput sont déjà gérés directement dans le HTML
    // Cette fonction est conservée pour compatibilité mais n'est plus nécessaire
    // car oninput est directement dans les inputs
}

document.addEventListener('DOMContentLoaded', function() {
    // Ajouter une ligne vide pour permettre la saisie manuelle
    // Si l'utilisateur utilise l'IA, les lignes seront remplacées
    if (document.getElementById('lignes-container').children.length === 0) {
        addLigne();
    }
});
</script>
@endpush
@endsection

