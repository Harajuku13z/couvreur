@extends('layouts.admin')

@section('title', 'Modifier le Devis ' . $devis->numero)
@section('page_title', 'Modifier le Devis ' . $devis->numero)

@section('content')
<div class="p-6">
    <div class="mb-6">
        <a href="{{ route('admin.devis.show', $devis->id) }}" class="text-blue-600 hover:text-blue-900">
            <i class="fas fa-arrow-left mr-2"></i>Retour au devis
        </a>
    </div>

    <form action="{{ route('admin.devis.update', $devis->id) }}" method="POST" id="devisForm">
        @csrf
        @method('PUT')
        
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Informations Client</h2>
            
            <div class="mb-4">
                <label for="client_id" class="block text-sm font-medium mb-2">Client *</label>
                <select id="client_id" name="client_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ $devis->client_id == $client->id ? 'selected' : '' }}>
                        {{ $client->nom_complet }} - {{ $client->email }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label for="statut" class="block text-sm font-medium mb-2">Statut *</label>
                <select id="statut" name="statut" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="Brouillon" {{ $devis->statut == 'Brouillon' ? 'selected' : '' }}>Brouillon</option>
                    <option value="En Attente" {{ $devis->statut == 'En Attente' ? 'selected' : '' }}>En Attente</option>
                    <option value="Accepté" {{ $devis->statut == 'Accepté' ? 'selected' : '' }}>Accepté</option>
                    <option value="Refusé" {{ $devis->statut == 'Refusé' ? 'selected' : '' }}>Refusé</option>
                </select>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Description du Projet</h2>
            
            <div class="mb-4">
                <label for="description_globale" class="block text-sm font-medium mb-2">Description globale</label>
                <textarea id="description_globale" 
                          name="description_globale" 
                          rows="4"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ $devis->description_globale }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="taux_tva" class="block text-sm font-medium mb-2">Taux TVA (%)</label>
                    <input type="number" 
                           id="taux_tva" 
                           name="taux_tva"
                           value="{{ $devis->taux_tva }}"
                           step="0.01"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label for="date_validite" class="block text-sm font-medium mb-2">Date de validité</label>
                    <input type="date" 
                           id="date_validite" 
                           name="date_validite"
                           value="{{ $devis->date_validite ? $devis->date_validite->format('Y-m-d') : '' }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
            </div>

            <div>
                <label for="conditions_particulieres" class="block text-sm font-medium mb-2">Conditions particulières</label>
                <textarea id="conditions_particulieres" 
                          name="conditions_particulieres" 
                          rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ $devis->conditions_particulieres }}</textarea>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Lignes de Devis</h2>
            <div id="lignes-container" class="space-y-4">
                @foreach($devis->lignesDevis as $index => $ligne)
                <div class="border border-gray-200 rounded-lg p-4 ligne-item" data-index="{{ $index }}">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-5">
                            <label class="block text-sm font-medium mb-1">Description *</label>
                            <input type="text" 
                                   name="lignes[{{ $index }}][description]" 
                                   value="{{ $ligne->description }}"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium mb-1">Quantité *</label>
                            <input type="number" 
                                   name="lignes[{{ $index }}][quantite]" 
                                   value="{{ $ligne->quantite }}"
                                   step="0.01"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium mb-1">Unité *</label>
                            <input type="text" 
                                   name="lignes[{{ $index }}][unite]" 
                                   value="{{ $ligne->unite }}"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium mb-1">Prix unitaire (€) *</label>
                            <input type="number" 
                                   name="lignes[{{ $index }}][prix_unitaire]" 
                                   value="{{ $ligne->prix_unitaire }}"
                                   step="0.01"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded">
                        </div>
                        <div class="col-span-1 flex items-end">
                            <button type="button" 
                                    onclick="removeLigne({{ $index }})" 
                                    class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <button type="button" 
                    onclick="addLigne()" 
                    class="mt-4 bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                <i class="fas fa-plus mr-2"></i>Ajouter une ligne
            </button>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i>Enregistrer les modifications
            </button>
            <a href="{{ route('admin.devis.show', $devis->id) }}" class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400">
                Annuler
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
let ligneIndex = {{ $devis->lignesDevis->count() }};

function addLigne() {
    const container = document.getElementById('lignes-container');
    const index = ligneIndex++;
    
    const ligneHtml = `
        <div class="border border-gray-200 rounded-lg p-4 ligne-item" data-index="${index}">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-5">
                    <label class="block text-sm font-medium mb-1">Description *</label>
                    <input type="text" 
                           name="lignes[${index}][description]" 
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium mb-1">Quantité *</label>
                    <input type="number" 
                           name="lignes[${index}][quantite]" 
                           step="0.01"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium mb-1">Unité *</label>
                    <input type="text" 
                           name="lignes[${index}][unite]" 
                           value="unité"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium mb-1">Prix unitaire (€) *</label>
                    <input type="number" 
                           name="lignes[${index}][prix_unitaire]" 
                           step="0.01"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded">
                </div>
                <div class="col-span-1 flex items-end">
                    <button type="button" 
                            onclick="removeLigne(${index})" 
                            class="text-red-600 hover:text-red-900">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', ligneHtml);
}

function removeLigne(index) {
    const item = document.querySelector(`.ligne-item[data-index="${index}"]`);
    if (item) {
        item.remove();
    }
}
</script>
@endpush
@endsection

