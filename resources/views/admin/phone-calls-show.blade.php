@extends('layouts.admin')

@section('title', 'Détails de l\'Appel Téléphonique')

@section('content')
<div class="p-4 md:p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl md:text-3xl font-bold text-gray-800">📞 Détails de l'Appel Téléphonique</h1>
                <p class="text-gray-600 mt-1">Informations complètes sur l'appel #{{ $phoneCall->id }}</p>
            </div>
            <a href="{{ route('admin.phone-calls') }}" 
               class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                <i class="fas fa-arrow-left mr-2"></i>Retour à la liste
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informations principales -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Carte principale -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-phone text-blue-600 mr-2"></i>
                    Informations de l'Appel
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Numéro de téléphone</label>
                        <div class="mt-1">
                            <a href="tel:{{ $phoneCall->phone_number }}" 
                               class="text-lg font-semibold text-blue-600 hover:text-blue-800">
                                <i class="fas fa-phone mr-2"></i>{{ $phoneCall->phone_number }}
                            </a>
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Date et heure</label>
                        <div class="mt-1 text-gray-900">
                            <i class="fas fa-calendar mr-2 text-gray-400"></i>
                            {{ $phoneCall->clicked_at->format('d/m/Y') }}
                            <i class="fas fa-clock ml-4 mr-2 text-gray-400"></i>
                            {{ $phoneCall->clicked_at->format('H:i:s') }}
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Page source</label>
                        <div class="mt-1">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-file-alt mr-2"></i>{{ $phoneCall->source_page }}
                            </span>
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Session ID</label>
                        <div class="mt-1">
                            <code class="text-sm bg-gray-100 px-2 py-1 rounded text-gray-800">
                                {{ $phoneCall->session_id ?? 'N/A' }}
                            </code>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Localisation -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-map-marker-alt text-green-600 mr-2"></i>
                    Localisation
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Ville</label>
                        <div class="mt-1 text-gray-900">
                            @if($phoneCall->city)
                                <i class="fas fa-city mr-2 text-gray-400"></i>{{ $phoneCall->city }}
                            @else
                                <span class="text-gray-400 italic">Non renseigné</span>
                            @endif
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Pays</label>
                        <div class="mt-1 text-gray-900">
                            @if($phoneCall->country)
                                <i class="fas fa-globe mr-2 text-gray-400"></i>{{ $phoneCall->country }}
                            @else
                                <span class="text-gray-400 italic">Non renseigné</span>
                            @endif
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Code pays</label>
                        <div class="mt-1">
                            @if($phoneCall->country_code)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    {{ $phoneCall->country_code }}
                                </span>
                            @else
                                <span class="text-gray-400 italic">Non renseigné</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations techniques -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-server text-purple-600 mr-2"></i>
                    Informations Techniques
                </h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Adresse IP</label>
                        <div class="mt-1">
                            <code class="text-sm bg-gray-100 px-2 py-1 rounded text-gray-800">
                                {{ $phoneCall->ip_address ?? 'N/A' }}
                            </code>
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">User Agent</label>
                        <div class="mt-1">
                            <code class="text-xs bg-gray-100 px-2 py-1 rounded text-gray-800 break-all">
                                {{ $phoneCall->user_agent ?? 'N/A' }}
                            </code>
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">URL de référence</label>
                        <div class="mt-1">
                            @if($phoneCall->referrer_url)
                                <a href="{{ $phoneCall->referrer_url }}" 
                                   target="_blank"
                                   class="text-blue-600 hover:text-blue-800 break-all">
                                    <i class="fas fa-external-link-alt mr-2"></i>{{ $phoneCall->referrer_url }}
                                </a>
                            @else
                                <span class="text-gray-400 italic">Aucune URL de référence</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Soumission liée -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-file-alt text-orange-600 mr-2"></i>
                    Soumission Liée
                </h2>
                
                @if($phoneCall->submission)
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-500">ID Soumission</label>
                            <div class="mt-1">
                                <a href="{{ route('admin.submission.show', $phoneCall->submission_id) }}" 
                                   class="text-blue-600 hover:text-blue-800 font-semibold">
                                    <i class="fas fa-link mr-2"></i>Soumission #{{ $phoneCall->submission_id }}
                                </a>
                            </div>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-500">Date de création</label>
                            <div class="mt-1 text-gray-900">
                                {{ $phoneCall->submission->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-times-circle text-gray-300 text-4xl mb-2"></i>
                        <p class="text-gray-500 text-sm">Aucune soumission liée</p>
                    </div>
                @endif
            </div>

            <!-- Métadonnées -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-info-circle text-indigo-600 mr-2"></i>
                    Métadonnées
                </h2>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">ID</span>
                        <span class="font-medium text-gray-900">#{{ $phoneCall->id }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-500">Créé le</span>
                        <span class="font-medium text-gray-900">
                            {{ $phoneCall->created_at->format('d/m/Y H:i') }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-500">Modifié le</span>
                        <span class="font-medium text-gray-900">
                            {{ $phoneCall->updated_at->format('d/m/Y H:i') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-cog text-gray-600 mr-2"></i>
                    Actions
                </h2>
                
                <div class="space-y-2">
                    <button onclick="showEditCityModal({{ $phoneCall->id }}, '{{ $phoneCall->city ?? '' }}')" 
                            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-left">
                        <i class="fas fa-edit mr-2"></i>Corriger la ville
                    </button>
                    
                    <a href="tel:{{ $phoneCall->phone_number }}" 
                       class="block w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-center">
                        <i class="fas fa-phone mr-2"></i>Appeler
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour éditer la ville (réutilisé depuis phone-calls.blade.php) -->
<div id="editCityModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Corriger la ville</h3>
            <form id="editCityForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                        Ville
                    </label>
                    <input type="text" 
                           id="city" 
                           name="city" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           required>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" 
                            onclick="closeEditCityModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showEditCityModal(callId, currentCity) {
    document.getElementById('city').value = currentCity || '';
    document.getElementById('editCityForm').action = '{{ route("admin.phone-calls.update-city", ":id") }}'.replace(':id', callId);
    document.getElementById('editCityModal').classList.remove('hidden');
}

function closeEditCityModal() {
    document.getElementById('editCityModal').classList.add('hidden');
}

// Fermer le modal en cliquant en dehors
document.getElementById('editCityModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditCityModal();
    }
});
</script>
@endpush
@endsection

