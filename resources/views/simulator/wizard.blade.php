@extends('layouts.app')

@section('title', $simulatorConfig['title'] ?? 'Simulateur de Coûts')
@section('meta_description', $simulatorConfig['description'] ?? 'Estimez rapidement le coût de vos travaux')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50 py-12">
    <div class="container mx-auto px-4 max-w-4xl">
        <!-- En-tête -->
        <div class="text-center mb-10">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                {{ $simulatorConfig['title'] ?? 'Simulateur de Coûts' }}
            </h1>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                {{ $simulatorConfig['description'] ?? 'Estimez rapidement le coût de vos travaux' }}
            </p>
        </div>

        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-600">Étape <span id="current-step">1</span> sur <span id="total-steps">8</span></span>
                <span class="text-sm font-medium text-gray-600"><span id="progress-percent">12</span>%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" id="progress-bar" style="width: 12%"></div>
            </div>
        </div>

        <!-- Formulaire multi-étapes -->
        <div class="bg-white rounded-2xl shadow-2xl p-8 md:p-12">
            <form id="simulator-wizard-form">
                <!-- Étape 1 : Type de propriété -->
                <div class="step" data-step="1">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">
                        Quel type de bien souhaitez-vous rénover ?
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="property_type" value="house" class="sr-only peer" required>
                            <div class="property-option border-3 border-gray-300 rounded-xl p-8 text-center hover:border-blue-500 hover:shadow-xl transition peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                <img src="{{ asset('icons2/Maison.webp') }}" alt="Maison" class="w-32 h-32 mx-auto mb-4 object-contain">
                                <h3 class="text-2xl font-bold text-gray-800 mb-2">Maison</h3>
                                <p class="text-gray-600">Maison individuelle ou mitoyenne</p>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="property_type" value="apartment" class="sr-only peer" required>
                            <div class="property-option border-3 border-gray-300 rounded-xl p-8 text-center hover:border-blue-500 hover:shadow-xl transition peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                <img src="{{ asset('icons2/Appartement.webp') }}" alt="Appartement" class="w-32 h-32 mx-auto mb-4 object-contain">
                                <h3 class="text-2xl font-bold text-gray-800 mb-2">Appartement</h3>
                                <p class="text-gray-600">Appartement en immeuble</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Étape 2 : Informations personnelles -->
                <div class="step hidden" data-step="2">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">
                        Vos informations
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="first_name" class="block text-sm font-semibold text-gray-700 mb-2">Prénom *</label>
                            <input type="text" id="first_name" name="first_name" required
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                        </div>
                        <div>
                            <label for="last_name" class="block text-sm font-semibold text-gray-700 mb-2">Nom *</label>
                            <input type="text" id="last_name" name="last_name" required
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                            <input type="email" id="email" name="email" required
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">Téléphone *</label>
                            <input type="tel" id="phone" name="phone" required
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                        </div>
                    </div>
                </div>

                <!-- Étape 3 : Type de service -->
                <div class="step hidden" data-step="3">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">
                        Quel type de travaux souhaitez-vous réaliser ?
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="services-list">
                        @foreach($simulatorConfig['services'] ?? [] as $service)
                        <label class="relative flex items-start p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all duration-200">
                            <input type="radio" name="service_type" value="{{ $service['id'] }}" class="sr-only peer" required>
                            <div class="peer-checked:border-blue-600 peer-checked:bg-blue-50 absolute inset-0 border-2 rounded-xl pointer-events-none"></div>
                            <div class="relative flex-1">
                                <div class="flex items-center mb-2">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                        </svg>
                                    </div>
                                    <span class="font-semibold text-gray-900">{{ $service['name'] }}</span>
                                </div>
                                <p class="text-sm text-gray-600 ml-13">{{ $service['description'] }}</p>
                                <p class="text-xs text-blue-600 font-medium mt-2 ml-13">À partir de {{ $service['base_cost_per_sqm'] }}€/m²</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Étape 4 : Surface -->
                <div class="step hidden" data-step="4">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">
                        Surface à traiter (en m²)
                    </h2>
                    <div class="max-w-md mx-auto">
                        <div class="relative mb-4">
                            <input type="number" id="surface" name="surface" min="1" max="10000" step="1" value="100"
                                   class="w-full px-6 py-4 text-2xl text-center border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                                   required>
                            <span class="absolute right-6 top-1/2 transform -translate-y-1/2 text-gray-500 font-medium">m²</span>
                        </div>
                        <div class="mt-4">
                            <input type="range" id="surface-range" min="1" max="500" value="100" 
                                   class="w-full h-2 bg-blue-200 rounded-lg appearance-none cursor-pointer">
                        </div>
                        <p class="text-center text-gray-600 mt-4 text-sm">Glissez le curseur ou saisissez directement la surface</p>
                    </div>
                </div>

                <!-- Étape 5 : Qualité -->
                <div class="step hidden" data-step="5">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">
                        Niveau de qualité souhaité
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="relative flex flex-col p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all">
                            <input type="radio" name="quality_level" value="standard" class="sr-only peer" required>
                            <div class="peer-checked:border-blue-600 peer-checked:bg-blue-50 absolute inset-0 border-2 rounded-xl pointer-events-none"></div>
                            <div class="relative text-center">
                                <span class="inline-block px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-semibold mb-2">×1.0</span>
                                <h3 class="text-lg font-bold text-gray-900 mb-1">Standard</h3>
                                <p class="text-sm text-gray-600">Matériaux de qualité, garantie 10 ans</p>
                            </div>
                        </label>
                        <label class="relative flex flex-col p-5 border-2 border-blue-300 rounded-xl cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all">
                            <input type="radio" name="quality_level" value="premium" class="sr-only peer" checked>
                            <div class="peer-checked:border-blue-600 peer-checked:bg-blue-50 absolute inset-0 border-2 rounded-xl pointer-events-none"></div>
                            <div class="relative text-center">
                                <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold mb-2">×1.4</span>
                                <span class="ml-2 px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs font-semibold">Recommandé</span>
                                <h3 class="text-lg font-bold text-gray-900 mb-1 mt-2">Premium</h3>
                                <p class="text-sm text-gray-600">Matériaux haut de gamme, finitions soignées</p>
                            </div>
                        </label>
                        <label class="relative flex flex-col p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all">
                            <input type="radio" name="quality_level" value="luxury" class="sr-only peer">
                            <div class="peer-checked:border-blue-600 peer-checked:bg-blue-50 absolute inset-0 border-2 rounded-xl pointer-events-none"></div>
                            <div class="relative text-center">
                                <span class="inline-block px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold mb-2">×2.0</span>
                                <h3 class="text-lg font-bold text-gray-900 mb-1">Luxe</h3>
                                <p class="text-sm text-gray-600">Excellence absolue, matériaux d'exception</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Étape 6 : Urgence -->
                <div class="step hidden" data-step="6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">
                        Délai d'intervention souhaité
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="relative flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all">
                            <input type="radio" name="urgency" value="normal" class="sr-only peer" checked>
                            <div class="peer-checked:border-blue-600 peer-checked:bg-blue-50 absolute inset-0 border-2 rounded-xl pointer-events-none"></div>
                            <div class="relative flex-1 text-center">
                                <span class="text-sm font-medium text-gray-900">Normal (2-4 semaines)</span>
                            </div>
                        </label>
                        <label class="relative flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all">
                            <input type="radio" name="urgency" value="urgent" class="sr-only peer">
                            <div class="peer-checked:border-blue-600 peer-checked:bg-blue-50 absolute inset-0 border-2 rounded-xl pointer-events-none"></div>
                            <div class="relative flex-1 text-center">
                                <span class="text-sm font-medium text-gray-900">Urgent (sous 1 semaine) <span class="text-orange-600">+25%</span></span>
                            </div>
                        </label>
                        <label class="relative flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all">
                            <input type="radio" name="urgency" value="emergency" class="sr-only peer">
                            <div class="peer-checked:border-blue-600 peer-checked:bg-blue-50 absolute inset-0 border-2 rounded-xl pointer-events-none"></div>
                            <div class="relative flex-1 text-center">
                                <span class="text-sm font-medium text-gray-900">Urgence (48h) <span class="text-red-600">+60%</span></span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Étape 7 : Options additionnelles -->
                <div class="step hidden" data-step="7" id="options-step">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">
                        Options additionnelles (facultatif)
                    </h2>
                    <div class="space-y-3" id="additional-options-list">
                        <!-- Rempli dynamiquement -->
                    </div>
                </div>

                <!-- Étape 8 : Résultats -->
                <div class="step hidden" data-step="8" id="results-step">
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-bold text-gray-900 mb-2">Estimation de votre projet</h2>
                        <p class="text-gray-600">Résultat instantané basé sur vos critères</p>
                    </div>
                    
                    <!-- Coût principal -->
                    <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl p-8 text-white text-center mb-8">
                        <p class="text-lg mb-2 opacity-90">Coût estimé</p>
                        <div class="text-6xl font-bold mb-4" id="estimated-cost">-</div>
                        <div class="text-sm opacity-80">
                            <span id="cost-range">-</span>
                        </div>
                    </div>
                    
                    <!-- Détails -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="bg-gray-50 rounded-xl p-6">
                            <h3 class="font-semibold text-gray-900 mb-4">Détails du projet</h3>
                            <div class="space-y-3 text-sm" id="project-details"></div>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-6">
                            <h3 class="font-semibold text-gray-900 mb-4">Décomposition des coûts</h3>
                            <div class="space-y-3 text-sm" id="cost-breakdown"></div>
                        </div>
                    </div>
                    
                    <!-- Disclaimers -->
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg mb-8">
                        <h3 class="font-semibold text-yellow-900 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            Important à savoir
                        </h3>
                        <ul class="space-y-2 text-sm text-yellow-800">
                            @foreach($simulatorConfig['disclaimers'] ?? [] as $disclaimer)
                            <li class="flex items-start">
                                <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>{{ $disclaimer }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    
                    <!-- CTA Devis -->
                    <div class="text-center">
                        <a href="{{ route('form.step', 'propertyType') }}" 
                           class="inline-flex items-center px-8 py-4 bg-green-600 text-white text-lg font-bold rounded-xl hover:bg-green-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl">
                            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Obtenir un devis personnalisé gratuit
                        </a>
                        <p class="text-sm text-gray-500 mt-3">Réponse sous 24h • Sans engagement • Devis détaillé</p>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="flex justify-between mt-8 pt-6 border-t border-gray-200">
                    <button type="button" id="prev-btn" class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition hidden">
                        <i class="fas fa-arrow-left mr-2"></i>Retour
                    </button>
                    <button type="button" id="next-btn" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition ml-auto">
                        Suivant<i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
.step {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

#surface-range::-webkit-slider-thumb {
    appearance: none;
    width: 24px;
    height: 24px;
    background: #2563eb;
    cursor: pointer;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}

#surface-range::-moz-range-thumb {
    width: 24px;
    height: 24px;
    background: #2563eb;
    cursor: pointer;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}
</style>
@endpush

@push('scripts')
<script>
const simulatorConfig = @json($simulatorConfig);
let currentStep = 1;
const totalSteps = 8;

// Synchroniser slider et input surface
const surfaceInput = document.getElementById('surface');
const surfaceRange = document.getElementById('surface-range');
if (surfaceInput && surfaceRange) {
    surfaceRange.addEventListener('input', () => surfaceInput.value = surfaceRange.value);
    surfaceInput.addEventListener('input', () => {
        if (surfaceInput.value <= 500) surfaceRange.value = surfaceInput.value;
    });
}

// Gestion des étapes
function showStep(step) {
    document.querySelectorAll('.step').forEach(s => s.classList.add('hidden'));
    document.querySelector(`.step[data-step="${step}"]`)?.classList.remove('hidden');
    
    currentStep = step;
    updateProgress();
    updateNavigation();
    
    // Afficher les options si nécessaire
    if (step === 7) {
        updateAdditionalOptions();
    }
}

function updateProgress() {
    const percent = Math.round((currentStep / totalSteps) * 100);
    document.getElementById('progress-percent').textContent = percent;
    document.getElementById('progress-bar').style.width = percent + '%';
    document.getElementById('current-step').textContent = currentStep;
}

function updateNavigation() {
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    
    prevBtn.classList.toggle('hidden', currentStep === 1);
    
    if (currentStep === totalSteps) {
        nextBtn.style.display = 'none';
    } else {
        nextBtn.style.display = 'block';
        nextBtn.innerHTML = currentStep === totalSteps - 1 ? 'Voir les résultats' : 'Suivant<i class="fas fa-arrow-right ml-2"></i>';
    }
}

function updateAdditionalOptions() {
    const serviceId = document.querySelector('input[name="service_type"]:checked')?.value;
    const service = simulatorConfig.services?.find(s => s.id === serviceId);
    const container = document.getElementById('additional-options-list');
    
    if (!service?.additional_options?.length) {
        showStep(8);
        calculateCost();
        return;
    }
    
    container.innerHTML = service.additional_options.map(opt => `
        <label class="relative flex items-start p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all">
            <input type="checkbox" name="additional_options[]" value="${opt.id}" class="sr-only peer">
            <div class="peer-checked:border-blue-600 peer-checked:bg-blue-50 absolute inset-0 border-2 rounded-xl pointer-events-none"></div>
            <div class="relative flex items-center flex-1">
                <div class="w-5 h-5 border-2 border-gray-300 rounded peer-checked:bg-blue-600 peer-checked:border-blue-600 flex items-center justify-center mr-3">
                    <svg class="w-3 h-3 text-white hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <span class="font-medium text-gray-900">${opt.name}</span>
                    <span class="ml-2 text-sm text-blue-600 font-semibold">+${opt.cost_per_sqm}€/m²</span>
                </div>
            </div>
        </label>
    `).join('');
}

// Navigation
document.getElementById('next-btn').addEventListener('click', () => {
    const currentStepEl = document.querySelector(`.step[data-step="${currentStep}"]`);
    const form = currentStepEl?.querySelector('input[required], select[required]');
    
    if (form && !form.closest('form').checkValidity()) {
        form.closest('form').reportValidity();
        return;
    }
    
    if (currentStep === 7) {
        calculateCost();
        showStep(8);
    } else {
        showStep(currentStep + 1);
    }
});

document.getElementById('prev-btn').addEventListener('click', () => {
    if (currentStep > 1) showStep(currentStep - 1);
});

// Auto-submit sur sélection type propriété
document.querySelectorAll('input[name="property_type"]').forEach(radio => {
    radio.addEventListener('change', () => {
        setTimeout(() => showStep(2), 300);
    });
});

// Calcul du coût
function calculateCost() {
    const formData = new FormData(document.getElementById('simulator-wizard-form'));
    const data = {
        service_type: formData.get('service_type'),
        property_type: formData.get('property_type'),
        surface: parseFloat(formData.get('surface')),
        quality_level: formData.get('quality_level'),
        urgency: formData.get('urgency'),
        additional_options: formData.getAll('additional_options[]'),
        // Informations personnelles
        first_name: formData.get('first_name'),
        last_name: formData.get('last_name'),
        email: formData.get('email'),
        phone: formData.get('phone')
    };
    
    // Afficher un loader
    const nextBtn = document.getElementById('next-btn');
    const originalText = nextBtn.innerHTML;
    nextBtn.disabled = true;
    nextBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Calcul en cours...';
    
    fetch('{{ route("simulator.calculate") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        nextBtn.disabled = false;
        nextBtn.innerHTML = originalText;
        
        if (result.success) {
            displayResults(result.result);
        } else {
            alert('Erreur lors du calcul. Veuillez réessayer.');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        nextBtn.disabled = false;
        nextBtn.innerHTML = originalText;
        alert('Erreur lors du calcul. Veuillez réessayer.');
    });
}

function displayResults(result) {
    document.getElementById('estimated-cost').textContent = formatCurrency(result.total_cost);
    document.getElementById('cost-range').textContent = `Fourchette: ${formatCurrency(result.min_cost)} - ${formatCurrency(result.max_cost)}`;
    
    document.getElementById('project-details').innerHTML = `
        <div class="flex justify-between"><span class="text-gray-600">Service</span><span class="font-medium">${result.service_name}</span></div>
        <div class="flex justify-between"><span class="text-gray-600">Surface</span><span class="font-medium">${result.surface} m²</span></div>
        <div class="flex justify-between"><span class="text-gray-600">Qualité</span><span class="font-medium">${result.quality_label}</span></div>
        <div class="flex justify-between"><span class="text-gray-600">Délai</span><span class="font-medium">${result.urgency_label}</span></div>
        <div class="flex justify-between"><span class="text-gray-600">Type</span><span class="font-medium">${result.property_label}</span></div>
    `;
    
    document.getElementById('cost-breakdown').innerHTML = `
        <div class="flex justify-between pb-2 border-b"><span class="text-gray-600">Coût de base</span><span class="font-medium">${formatCurrency(result.breakdown.base)}</span></div>
        <div class="flex justify-between pb-2 border-b"><span class="text-gray-600">Qualité (×${result.breakdown.quality_multiplier})</span><span class="font-medium">+${Math.round((result.breakdown.quality_multiplier - 1) * 100)}%</span></div>
        <div class="flex justify-between pb-2 border-b"><span class="text-gray-600">Urgence (×${result.breakdown.urgency_multiplier})</span><span class="font-medium">+${Math.round((result.breakdown.urgency_multiplier - 1) * 100)}%</span></div>
        ${result.breakdown.options > 0 ? `<div class="flex justify-between pb-2 border-b"><span class="text-gray-600">Options</span><span class="font-medium text-blue-600">+${formatCurrency(result.breakdown.options)}</span></div>` : ''}
        <div class="flex justify-between pt-2 font-bold text-lg"><span>Total</span><span class="text-blue-600">${formatCurrency(result.total_cost)}</span></div>
    `;
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR', minimumFractionDigits: 0 }).format(amount);
}
</script>
@endpush
@endsection

