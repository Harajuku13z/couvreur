@extends('layouts.app')

@section('title', 'Travaux d\'isolation - Simulateur')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <div class="mb-8">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-600">Étape 6 sur 11</span>
                    <span class="text-sm font-medium text-gray-600">55%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: 55%"></div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <h2 class="text-3xl font-bold text-center mb-4 text-gray-800">
                    Quels travaux d'isolation souhaitez-vous réaliser ?
                </h2>
                <p class="text-center text-gray-600 mb-8">Sélectionnez une ou plusieurs options</p>
                
                <form method="POST" action="{{ route('form.submit', 'isolationWorkType') }}">
                    @csrf
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        @php
                        $isolationWorks = [
                            'murs' => ['label' => 'Isolation des Murs', 'icon' => 'Isolation_des_murs.webp'],
                            'sols' => ['label' => 'Isolation des Sols', 'icon' => 'Isolation_des_sols.webp'],
                            'combles' => ['label' => 'Isolation des Combles', 'icon' => 'Isolation_des_combles.webp'],
                            'toitures' => ['label' => 'Isolation des Toitures', 'icon' => 'Isolation_des_toitures.webp'],
                        ];
                        @endphp

                        @foreach($isolationWorks as $value => $work)
                        <label for="isolation_{{ $value }}" class="cursor-pointer">
                            <input type="checkbox" name="isolation_work_type[]" value="{{ $value }}" id="isolation_{{ $value }}" class="hidden work-checkbox">
                            <div class="work-option border-2 border-gray-300 rounded-lg p-6 text-center hover:border-blue-500 transition">
                                <img src="{{ asset('icons2/Isolation/' . $work['icon']) }}" alt="{{ $work['label'] }}" class="w-20 h-20 mx-auto mb-3 object-contain">
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

function validateForm() {
    const checked = Array.from(checkboxes).filter(cb => cb.checked).length;
    submitBtn.disabled = checked === 0;
}

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
document.querySelectorAll('label[for^="isolation_"]').forEach(function(label) {
    label.addEventListener('click', function(e) {
        const checkbox = this.querySelector('input[type="checkbox"]');
        setTimeout(() => toggleOption(checkbox), 10);
    });
});

// Écouteur sur chaque checkbox
checkboxes.forEach(cb => {
    cb.addEventListener('change', function() {
        toggleOption(this);
    });
});

// Pré-sélection
@if(old('isolation_work_type', $submission->isolation_work_types ?? []))
const existingValues = @json(old('isolation_work_type', $submission->isolation_work_types ?? []));
if (Array.isArray(existingValues)) {
    existingValues.forEach(value => {
        const checkbox = document.getElementById('isolation_' + value);
        if (checkbox) {
            checkbox.checked = true;
            toggleOption(checkbox);
        }
    });
}
@endif

validateForm();
console.log('✅ Étape 6 - Travaux Isolation (VERSION SIMPLE)');
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







