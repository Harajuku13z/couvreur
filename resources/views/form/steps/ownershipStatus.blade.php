@extends('layouts.app')

@section('title', 'Statut - Simulateur')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="mb-8">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-600">Étape 7 sur 11</span>
                    <span class="text-sm font-medium text-gray-600">64%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: 64%"></div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">
                    Êtes-vous propriétaire ou locataire ?
                </h2>
                
                <form method="POST" action="{{ route('form.submit', 'ownershipStatus') }}" id="ownershipForm">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl mx-auto">
                        <!-- Propriétaire -->
                        <label for="owner" class="cursor-pointer">
                            <input type="radio" name="ownership_status" value="owner" id="owner" class="hidden" required>
                            <div class="property-option border-3 border-gray-300 rounded-xl p-8 text-center hover:border-blue-500 hover:shadow-xl transition">
                                <img src="{{ asset('icons2/Proprietaire.webp') }}" alt="Propriétaire" class="w-32 h-32 mx-auto mb-4 object-contain">
                                <h3 class="text-2xl font-bold text-gray-800">Propriétaire</h3>
                            </div>
                        </label>

                        <!-- Locataire -->
                        <label for="tenant" class="cursor-pointer">
                            <input type="radio" name="ownership_status" value="tenant" id="tenant" class="hidden" required>
                            <div class="property-option border-3 border-gray-300 rounded-xl p-8 text-center hover:border-blue-500 hover:shadow-xl transition">
                                <img src="{{ asset('icons2/Locataire.webp') }}" alt="Locataire" class="w-32 h-32 mx-auto mb-4 object-contain">
                                <h3 class="text-2xl font-bold text-gray-800">Locataire</h3>
                            </div>
                        </label>
                    </div>
                </form>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('form.step', 'propertyType') }}" class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Précédent
                </a>
                <button type="submit" form="ownershipForm" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                    Suivant <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Fonction pour mettre à jour la sélection visuelle
function updateSelection(radio) {
    document.querySelectorAll('.property-option').forEach(opt => {
        opt.classList.remove('border-blue-500', 'bg-blue-50');
    });
    const option = radio.closest('label').querySelector('.property-option');
    option.classList.add('border-blue-500', 'bg-blue-50');
}

// Écouteur sur chaque label
document.querySelectorAll('label[for^="owner"], label[for^="tenant"]').forEach(function(label) {
    label.addEventListener('click', function(e) {
        const radio = this.querySelector('input[type="radio"]');
        radio.checked = true;
        updateSelection(radio);
        
        setTimeout(() => document.getElementById('ownershipForm').submit(), 300);
    });
});

// Écouteur sur chaque radio aussi
document.querySelectorAll('input[name="ownership_status"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        updateSelection(this);
        setTimeout(() => document.getElementById('ownershipForm').submit(), 300);
    });
});

const currentValue = '{{ old('ownership_status', $submission->ownership_status ?? '') }}';
if (currentValue) {
    const radio = document.getElementById(currentValue === 'owner' ? 'owner' : 'tenant');
    if (radio) {
        radio.checked = true;
        updateSelection(radio);
    }
}

console.log('✅ Étape 7 - Statut Propriété (VERSION SIMPLE)');
</script>
@endsection














