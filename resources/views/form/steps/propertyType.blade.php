@extends('layouts.app')

@section('title', 'Type de bien - Simulateur de Travaux')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-600">Étape 1 sur 11</span>
                    <span class="text-sm font-medium text-gray-600">9%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: 9%"></div>
                </div>
            </div>

            <!-- Question -->
            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">
                    Quel type de bien souhaitez-vous rénover ?
                </h2>
                
                <form method="POST" action="{{ route('form.submit', 'propertyType') }}" id="propertyForm">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Maison -->
                        <label for="property_maison" class="cursor-pointer">
                            <input type="radio" name="property_type" value="maison" id="property_maison" class="hidden" required>
                            <div class="property-option border-3 border-gray-300 rounded-xl p-8 text-center hover:border-blue-500 hover:shadow-xl transition">
                                <img src="{{ asset('icons2/Maison.webp') }}" alt="Maison" class="w-32 h-32 mx-auto mb-4 object-contain">
                                <h3 class="text-2xl font-bold text-gray-800 mb-2">Maison</h3>
                                <p class="text-gray-600">Maison individuelle ou mitoyenne</p>
                            </div>
                        </label>

                        <!-- Appartement -->
                        <label for="property_appartement" class="cursor-pointer">
                            <input type="radio" name="property_type" value="appartement" id="property_appartement" class="hidden" required>
                            <div class="property-option border-3 border-gray-300 rounded-xl p-8 text-center hover:border-blue-500 hover:shadow-xl transition">
                                <img src="{{ asset('icons2/Appartement.webp') }}" alt="Appartement" class="w-32 h-32 mx-auto mb-4 object-contain">
                                <h3 class="text-2xl font-bold text-gray-800 mb-2">Appartement</h3>
                                <p class="text-gray-600">Appartement en immeuble</p>
                            </div>
                        </label>
                    </div>
                </form>
            </div>

            <!-- Navigation -->
            <div class="flex justify-between">
                <a href="{{ route('home') }}" 
                   class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Retour
                </a>
                
                <button type="submit" form="propertyForm" 
                        class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                    Suivant
                    <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Fonction pour mettre à jour la sélection visuelle
function updateSelection(radio) {
    // Retirer la sélection de toutes les options
    document.querySelectorAll('.property-option').forEach(opt => {
        opt.classList.remove('border-blue-500', 'bg-blue-50');
        opt.classList.add('border-gray-300');
    });
    
    // Ajouter la sélection à l'option cliquée
    const option = radio.closest('label').querySelector('.property-option');
    option.classList.remove('border-gray-300');
    option.classList.add('border-blue-500', 'bg-blue-50');
}

// Écouteur sur chaque option (label + div)
document.querySelectorAll('label[for^="property_"]').forEach(function(label) {
    label.addEventListener('click', function(e) {
        const radio = this.querySelector('input[type="radio"]');
        radio.checked = true;
        updateSelection(radio);
        
        // Auto-submit après un court délai (UX)
        setTimeout(function() {
            document.getElementById('propertyForm').submit();
        }, 300);
    });
});

// Écouteur aussi sur les radios pour compatibilité
document.querySelectorAll('input[name="property_type"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        updateSelection(this);
        
        // Auto-submit après un court délai (UX)
        setTimeout(function() {
            document.getElementById('propertyForm').submit();
        }, 300);
    });
});

// Pré-sélectionner si une valeur existe
const currentValue = '{{ old('property_type', $submission->property_type ?? '') }}';
if (currentValue) {
    const radio = document.getElementById('property_' + currentValue);
    if (radio) {
        radio.checked = true;
        updateSelection(radio);
    }
}

console.log('✅ Étape 1 - Type de Bien (VERSION SIMPLE)');
</script>

<style>
.property-option {
    transition: all 0.3s ease;
}

.property-option:hover {
    transform: translateY(-5px);
}
</style>
@endsection














