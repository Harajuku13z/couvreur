@extends('layouts.app')

@section('title', 'Travaux de toiture - Simulateur')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <div class="mb-8">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-600">Étape 4 sur 11</span>
                    <span class="text-sm font-medium text-gray-600">36%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: 36%"></div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <h2 class="text-3xl font-bold text-center mb-4 text-gray-800">
                    Quels travaux de toiture souhaitez-vous réaliser ?
                </h2>
                <p class="text-center text-gray-600 mb-8">Sélectionnez une ou plusieurs options</p>
                
                <form method="POST" action="{{ route('form.submit', 'roofWorkType') }}">
                    @csrf
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @php
                        $roofWorks = [
                            'peinture' => ['label' => 'Peinture', 'icon' => 'Peinture_Toiture.webp'],
                            'hydrofuge' => ['label' => 'Hydrofuge', 'icon' => 'Hydrofuge_toiture.webp'],
                            'refection' => ['label' => 'Réfection', 'icon' => 'Refection_de_toiture.webp'],
                            'accessoires' => ['label' => 'Accessoires', 'icon' => 'Accessoires_et_finitions.webp'],
                            'remplacement' => ['label' => 'Remplacement', 'icon' => 'Remplacement_de_couverture.webp'],
                            'traitement' => ['label' => 'Traitement Bois', 'icon' => 'Traitement_de_bois_de_charpente.webp'],
                            'nettoyage' => ['label' => 'Nettoyage', 'icon' => 'Nettoyage_toiture_amiante.webp'],
                        ];
                        @endphp

                        @foreach($roofWorks as $value => $work)
                        <label for="roof_{{ $value }}" class="cursor-pointer">
                            <input type="checkbox" name="roof_work_type[]" value="{{ $value }}" id="roof_{{ $value }}" class="hidden work-checkbox">
                            <div class="work-option border-2 border-gray-300 rounded-lg p-4 text-center hover:border-blue-500 transition">
                                <img src="{{ asset('icons2/Toiture/' . $work['icon']) }}" alt="{{ $work['label'] }}" class="w-16 h-16 mx-auto mb-2 object-contain">
                                <p class="text-sm font-semibold text-gray-800">{{ $work['label'] }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>

                    <div class="flex justify-between mt-8">
                        <a href="{{ route('form.step', 'propertyType') }}" class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition">
                            <i class="fas fa-arrow-left mr-2"></i> Précédent
                        </a>
                        <button type="submit" id="submitBtn" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition" disabled>
                            Suivant <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const checkboxes = document.querySelectorAll('.work-checkbox');
const submitBtn = document.getElementById('submitBtn');

// Validation : au moins 1 case cochée
function validateForm() {
    const checked = Array.from(checkboxes).filter(cb => cb.checked).length;
    submitBtn.disabled = checked === 0;
}

// Fonction pour basculer la sélection
function toggleOption(checkbox) {
    const option = checkbox.closest('label').querySelector('.work-option');
    
    if (checkbox.checked) {
        option.classList.remove('border-gray-300');
        option.classList.add('border-blue-500', 'bg-blue-50');
    } else {
        option.classList.remove('border-blue-500', 'bg-blue-50');
        option.classList.add('border-gray-300');
    }
    
    validateForm();
}

// Écouteur sur chaque label pour gérer le clic sur toute la carte
document.querySelectorAll('label[for^="roof_"]').forEach(function(label) {
    label.addEventListener('click', function(e) {
        const checkbox = this.querySelector('input[type="checkbox"]');
        // Le navigateur gère déjà le checked, on met à jour le visuel après un petit délai
        setTimeout(() => {
            toggleOption(checkbox);
        }, 10);
    });
});

// Écouteur sur chaque checkbox aussi (pour compatibilité)
checkboxes.forEach(cb => {
    cb.addEventListener('change', function() {
        toggleOption(this);
    });
});

// Pré-sélection des valeurs existantes
@if(old('roof_work_type', $submission->roof_work_types ?? []))
const existingValues = @json(old('roof_work_type', $submission->roof_work_types ?? []));
if (Array.isArray(existingValues)) {
    existingValues.forEach(value => {
        const checkbox = document.getElementById('roof_' + value);
        if (checkbox) {
            checkbox.checked = true;
            toggleOption(checkbox);
        }
    });
}
@endif

validateForm();
console.log('✅ Étape 4 - Travaux Toiture (VERSION SIMPLE)');
</script>

<style>
.work-option {
    transition: all 0.3s ease;
}

.work-option:hover {
    transform: translateY(-3px);
}
</style>
@endsection







