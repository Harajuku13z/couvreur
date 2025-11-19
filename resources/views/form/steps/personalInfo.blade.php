@extends('layouts.app')

@section('title', 'Informations personnelles - Simulateur')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="mb-8">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-600">Étape 8 sur 11</span>
                    <span class="text-sm font-medium text-gray-600">73%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: 73%"></div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">
                    Quelques informations vous concernant
                </h2>
                
                <form method="POST" action="{{ route('form.submit', 'personalInfo') }}" id="personalInfoForm">
                    @csrf
                    <div class="max-w-md mx-auto space-y-6">
                        <!-- Civilité -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Civilité *</label>
                            <div class="grid grid-cols-2 gap-4">
                                <label for="gender_m" class="cursor-pointer">
                                    <input type="radio" name="gender" value="M" id="gender_m" class="hidden" required>
                                    <div class="gender-option border-2 border-gray-300 rounded-lg p-4 text-center hover:border-blue-500 transition">
                                        <i class="fas fa-male text-3xl mb-2 text-gray-600"></i>
                                        <p class="font-semibold">Monsieur</p>
                                    </div>
                                </label>
                                <label for="gender_mme" class="cursor-pointer">
                                    <input type="radio" name="gender" value="Mme" id="gender_mme" class="hidden" required>
                                    <div class="gender-option border-2 border-gray-300 rounded-lg p-4 text-center hover:border-blue-500 transition">
                                        <i class="fas fa-female text-3xl mb-2 text-gray-600"></i>
                                        <p class="font-semibold">Madame</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Prénom -->
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">Prénom *</label>
                            <input type="text" 
                                   id="first_name" 
                                   name="first_name" 
                                   value="{{ old('first_name', $submission->first_name ?? '') }}"
                                   class="w-full px-4 py-3 text-lg border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                                   placeholder="Jean"
                                   required>
                        </div>

                        <!-- Nom -->
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
                            <input type="text" 
                                   id="last_name" 
                                   name="last_name" 
                                   value="{{ old('last_name', $submission->last_name ?? '') }}"
                                   class="w-full px-4 py-3 text-lg border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                                   placeholder="Dupont"
                                   required>
                        </div>

                        <!-- Aide -->
                        <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                            <p class="text-gray-700 text-sm flex items-start">
                                <i class="fas fa-lock mr-2 mt-1 text-green-600"></i>
                                <span>Vos informations personnelles sont sécurisées et ne seront utilisées que pour votre devis.</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-between mt-8">
                        <a href="{{ route('form.step', 'propertyType') }}" class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition">
                            <i class="fas fa-arrow-left mr-2"></i> Précédent
                        </a>
                        <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                            Suivant <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Fonction pour mettre à jour la sélection visuelle
function updateGenderSelection(radio) {
    // Retirer la sélection de toutes les options
    document.querySelectorAll('.gender-option').forEach(opt => {
        opt.classList.remove('border-blue-500', 'bg-blue-50');
        opt.classList.add('border-gray-300');
    });
    
    // Ajouter la sélection à l'option cliquée
    const option = radio.closest('label').querySelector('.gender-option');
    option.classList.remove('border-gray-300');
    option.classList.add('border-blue-500', 'bg-blue-50');
}

// Écouteur sur chaque label (pour que toute la carte soit cliquable)
document.querySelectorAll('label[for^="gender_"]').forEach(function(label) {
    label.addEventListener('click', function(e) {
        const radio = this.querySelector('input[type="radio"]');
        radio.checked = true;
        updateGenderSelection(radio);
    });
});

// Écouteur aussi sur les radios pour compatibilité
document.querySelectorAll('input[name="gender"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        updateGenderSelection(this);
    });
});

// Pré-sélection si une valeur existe
const currentGender = '{{ old('gender', $submission->gender ?? '') }}';
if (currentGender) {
    const radio = document.getElementById('gender_' + (currentGender === 'M' ? 'm' : 'mme'));
    if (radio) {
        radio.checked = true;
        updateGenderSelection(radio);
    }
}

console.log('✅ Étape 8 - Informations Personnelles (VERSION SIMPLE)');
</script>
@endsection







