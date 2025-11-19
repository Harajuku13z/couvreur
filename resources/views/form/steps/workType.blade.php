@extends('layouts.app')

@section('title', 'Type de travaux - Simulateur de Travaux')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-600">Étape 3 sur 11</span>
                    <span class="text-sm font-medium text-gray-600">27%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: 27%"></div>
                </div>
            </div>

            <!-- Question -->
            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <h2 class="text-3xl font-bold text-center mb-4 text-gray-800">
                    Quel(s) type(s) de travaux souhaitez-vous réaliser ?
                </h2>
                <p class="text-center text-gray-600 mb-8">Sélectionnez une ou plusieurs options</p>
                
                <form method="POST" action="{{ route('form.submit', 'workType') }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Toiture -->
                        <label for="work_roof" class="cursor-pointer">
                            <input type="checkbox" name="work_type[]" value="roof" id="work_roof" class="hidden work-checkbox">
                            <div class="work-option border-3 border-gray-300 rounded-xl p-6 text-center hover:border-blue-500 hover:shadow-xl transition">
                                <img src="{{ asset('icons2/Toiture/Toiture.webp') }}" alt="Toiture" class="w-24 h-24 mx-auto mb-4 object-contain">
                                <h3 class="text-xl font-bold text-gray-800">Toiture</h3>
                            </div>
                        </label>

                        <!-- Façade -->
                        <label for="work_facade" class="cursor-pointer">
                            <input type="checkbox" name="work_type[]" value="facade" id="work_facade" class="hidden work-checkbox">
                            <div class="work-option border-3 border-gray-300 rounded-xl p-6 text-center hover:border-blue-500 hover:shadow-xl transition">
                                <img src="{{ asset('icons2/Facade.webp') }}" alt="Façade" class="w-24 h-24 mx-auto mb-4 object-contain">
                                <h3 class="text-xl font-bold text-gray-800">Façade</h3>
                            </div>
                        </label>

                        <!-- Isolation -->
                        <label for="work_isolation" class="cursor-pointer">
                            <input type="checkbox" name="work_type[]" value="isolation" id="work_isolation" class="hidden work-checkbox">
                            <div class="work-option border-3 border-gray-300 rounded-xl p-6 text-center hover:border-blue-500 hover:shadow-xl transition">
                                <img src="{{ asset('icons2/Isolation.webp') }}" alt="Isolation" class="w-24 h-24 mx-auto mb-4 object-contain">
                                <h3 class="text-xl font-bold text-gray-800">Isolation</h3>
                            </div>
                        </label>
                    </div>

                    <!-- Navigation -->
                    <div class="flex justify-between mt-8">
                        <a href="{{ route('form.step', 'propertyType') }}" 
                           class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Précédent
                        </a>
                        
                        <button type="submit" 
                                id="submitBtn"
                                class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                disabled>
                            Suivant
                            <i class="fas fa-arrow-right ml-2"></i>
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
    const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
    submitBtn.disabled = checkedCount === 0;
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

// Écouteur sur chaque label
document.querySelectorAll('label[for^="work_"]').forEach(function(label) {
    label.addEventListener('click', function(e) {
        const checkbox = this.querySelector('input[type="checkbox"]');
        // Le navigateur gère déjà le checked, on met juste à jour le visuel
        setTimeout(() => {
            toggleOption(checkbox);
        }, 10);
    });
});

// Écouteur sur chaque checkbox aussi
checkboxes.forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        toggleOption(this);
    });
});

// Pré-sélection des valeurs existantes
@if(old('work_type', $submission->work_types ?? []))
const existingValues = @json(old('work_type', $submission->work_types ?? []));
if (Array.isArray(existingValues)) {
    existingValues.forEach(value => {
        const checkbox = document.getElementById('work_' + value);
        if (checkbox) {
            checkbox.checked = true;
            toggleOption(checkbox);
        }
    });
}
@endif

validateForm();
console.log('✅ Étape 3 - Type de Travaux (VERSION SIMPLE)');
</script>

<style>
.work-option {
    transition: all 0.3s ease;
}

.work-option:hover {
    transform: translateY(-5px);
}
</style>
@endsection














