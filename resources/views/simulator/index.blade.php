@extends('layouts.app')

@section('title', $simulatorConfig['title'] ?? 'Simulateur de Coûts')
@section('meta_description', $simulatorConfig['description'] ?? 'Estimez rapidement le coût de vos travaux de rénovation')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50 py-12">
    <div class="container mx-auto px-4 max-w-4xl">
        <!-- En-tête -->
        <div class="text-center mb-10">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                {{ $simulatorConfig['title'] ?? 'Simulateur de Coûts' }}
            </h1>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                {{ $simulatorConfig['description'] ?? 'Estimez rapidement le coût de vos travaux de rénovation' }}
            </p>
        </div>
        
        <!-- Formulaire du simulateur -->
        <div class="bg-white rounded-2xl shadow-2xl p-8 md:p-12">
            <form id="cost-simulator-form">
                <!-- Étape 1 : Type de service -->
                <div class="mb-8">
                    <label class="block text-lg font-semibold text-gray-900 mb-4">
                        1. Quel type de travaux souhaitez-vous réaliser ?
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                
                <!-- Étape 2 : Type de propriété -->
                <div class="mb-8">
                    <label class="block text-lg font-semibold text-gray-900 mb-4">
                        2. Type de propriété
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <label class="relative flex flex-col items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all">
                            <input type="radio" name="property_type" value="house" class="sr-only peer" required>
                            <div class="peer-checked:border-blue-600 peer-checked:bg-blue-50 absolute inset-0 border-2 rounded-xl pointer-events-none"></div>
                            <svg class="w-8 h-8 text-gray-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-900">Maison</span>
                        </label>
                        <label class="relative flex flex-col items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all">
                            <input type="radio" name="property_type" value="apartment" class="sr-only peer">
                            <div class="peer-checked:border-blue-600 peer-checked:bg-blue-50 absolute inset-0 border-2 rounded-xl pointer-events-none"></div>
                            <svg class="w-8 h-8 text-gray-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-900">Appartement</span>
                        </label>
                        <label class="relative flex flex-col items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all">
                            <input type="radio" name="property_type" value="commercial" class="sr-only peer">
                            <div class="peer-checked:border-blue-600 peer-checked:bg-blue-50 absolute inset-0 border-2 rounded-xl pointer-events-none"></div>
                            <svg class="w-8 h-8 text-gray-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-900">Commerce</span>
                        </label>
                        <label class="relative flex flex-col items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all">
                            <input type="radio" name="property_type" value="industrial" class="sr-only peer">
                            <div class="peer-checked:border-blue-600 peer-checked:bg-blue-50 absolute inset-0 border-2 rounded-xl pointer-events-none"></div>
                            <svg class="w-8 h-8 text-gray-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-900">Industriel</span>
                        </label>
                    </div>
                </div>
                
                <!-- Étape 3 : Surface -->
                <div class="mb-8">
                    <label for="surface" class="block text-lg font-semibold text-gray-900 mb-4">
                        3. Surface à traiter (en m²)
                    </label>
                    <div class="relative">
                        <input 
                            type="number" 
                            id="surface" 
                            name="surface" 
                            min="1" 
                            max="10000" 
                            step="1" 
                            value="100"
                            class="w-full px-6 py-4 text-lg border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                            required
                        >
                        <span class="absolute right-6 top-1/2 transform -translate-y-1/2 text-gray-500 font-medium">m²</span>
                    </div>
                    <div class="mt-3">
                        <input 
                            type="range" 
                            id="surface-range" 
                            min="1" 
                            max="500" 
                            value="100" 
                            class="w-full h-2 bg-blue-200 rounded-lg appearance-none cursor-pointer"
                        >
                    </div>
                </div>
                
                <!-- Étape 4 : Niveau de qualité -->
                <div class="mb-8">
                    <label class="block text-lg font-semibold text-gray-900 mb-4">
                        4. Niveau de qualité souhaité
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="relative flex flex-col p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all">
                            <input type="radio" name="quality_level" value="standard" class="sr-only peer" required>
                            <div class="peer-checked:border-blue-600 peer-checked:bg-blue-50 absolute inset-0 border-2 rounded-xl pointer-events-none"></div>
                            <div class="relative">
                                <div class="text-center mb-2">
                                    <span class="inline-block px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-semibold mb-2">×1.0</span>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-1 text-center">Standard</h3>
                                <p class="text-sm text-gray-600 text-center">Matériaux de qualité, garantie 10 ans</p>
                            </div>
                        </label>
                        <label class="relative flex flex-col p-5 border-2 border-blue-300 rounded-xl cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all">
                            <input type="radio" name="quality_level" value="premium" class="sr-only peer" checked>
                            <div class="peer-checked:border-blue-600 peer-checked:bg-blue-50 absolute inset-0 border-2 rounded-xl pointer-events-none"></div>
                            <div class="relative">
                                <div class="text-center mb-2">
                                    <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold mb-2">×1.4</span>
                                    <span class="ml-2 px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs font-semibold">Recommandé</span>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-1 text-center">Premium</h3>
                                <p class="text-sm text-gray-600 text-center">Matériaux haut de gamme, finitions soignées</p>
                            </div>
                        </label>
                        <label class="relative flex flex-col p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all">
                            <input type="radio" name="quality_level" value="luxury" class="sr-only peer">
                            <div class="peer-checked:border-blue-600 peer-checked:bg-blue-50 absolute inset-0 border-2 rounded-xl pointer-events-none"></div>
                            <div class="relative">
                                <div class="text-center mb-2">
                                    <span class="inline-block px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold mb-2">×2.0</span>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-1 text-center">Luxe</h3>
                                <p class="text-sm text-gray-600 text-center">Excellence absolue, matériaux d'exception</p>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Étape 5 : Urgence -->
                <div class="mb-8">
                    <label class="block text-lg font-semibold text-gray-900 mb-4">
                        5. Délai d'intervention souhaité
                    </label>
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
                
                <!-- Étape 6 : Options additionnelles -->
                <div class="mb-8" id="additional-options-container" style="display: none;">
                    <label class="block text-lg font-semibold text-gray-900 mb-4">
                        6. Options additionnelles (facultatif)
                    </label>
                    <div class="space-y-3" id="additional-options-list">
                        <!-- Les options seront ajoutées dynamiquement via JS -->
                    </div>
                </div>
                
                <!-- Bouton de calcul -->
                <div class="text-center mt-10">
                    <button 
                        type="submit" 
                        class="inline-flex items-center px-10 py-5 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-lg font-bold rounded-xl hover:from-blue-700 hover:to-blue-800 transform hover:scale-105 transition-all duration-200 shadow-xl hover:shadow-2xl"
                    >
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Calculer le coût estimé
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Résultats (caché par défaut) -->
        <div id="results-container" class="mt-8 bg-white rounded-2xl shadow-2xl p-8 md:p-12" style="display: none;">
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
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Service</span>
                            <span class="font-medium text-gray-900" id="result-service">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Surface</span>
                            <span class="font-medium text-gray-900" id="result-surface">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Qualité</span>
                            <span class="font-medium text-gray-900" id="result-quality">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Délai</span>
                            <span class="font-medium text-gray-900" id="result-urgency">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Type</span>
                            <span class="font-medium text-gray-900" id="result-property">-</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 rounded-xl p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Décomposition des coûts</h3>
                    <div class="space-y-3 text-sm" id="cost-breakdown">
                        <!-- Rempli dynamiquement -->
                    </div>
                </div>
            </div>
            
            <!-- Options sélectionnées -->
            <div id="selected-options-container" class="mb-8" style="display: none;">
                <h3 class="font-semibold text-gray-900 mb-4">Options sélectionnées</h3>
                <div class="space-y-2" id="selected-options-list">
                    <!-- Rempli dynamiquement -->
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
                <a href="{{ route('form.step', 'propertyType') }}" class="inline-flex items-center px-8 py-4 bg-green-600 text-white text-lg font-bold rounded-xl hover:bg-green-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Obtenir un devis personnalisé gratuit
                </a>
                <p class="text-sm text-gray-500 mt-3">Réponse sous 24h • Sans engagement • Devis détaillé</p>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Slider personnalisé */
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

// Synchroniser le slider et l'input
const surfaceInput = document.getElementById('surface');
const surfaceRange = document.getElementById('surface-range');

surfaceRange.addEventListener('input', function() {
    surfaceInput.value = this.value;
});

surfaceInput.addEventListener('input', function() {
    if (this.value <= 500) {
        surfaceRange.value = this.value;
    }
});

// Afficher les options additionnelles selon le service sélectionné
document.querySelectorAll('input[name="service_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        updateAdditionalOptions(this.value);
    });
});

function updateAdditionalOptions(serviceId) {
    const service = simulatorConfig.services.find(s => s.id === serviceId);
    const container = document.getElementById('additional-options-container');
    const list = document.getElementById('additional-options-list');
    
    if (!service || !service.additional_options || service.additional_options.length === 0) {
        container.style.display = 'none';
        return;
    }
    
    container.style.display = 'block';
    list.innerHTML = '';
    
    service.additional_options.forEach(option => {
        const optionHtml = `
            <label class="relative flex items-start p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all">
                <input type="checkbox" name="additional_options[]" value="${option.id}" class="sr-only peer">
                <div class="peer-checked:border-blue-600 peer-checked:bg-blue-50 absolute inset-0 border-2 rounded-xl pointer-events-none"></div>
                <div class="relative flex items-center flex-1">
                    <div class="w-5 h-5 border-2 border-gray-300 rounded peer-checked:bg-blue-600 peer-checked:border-blue-600 flex items-center justify-center mr-3">
                        <svg class="w-3 h-3 text-white hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <span class="font-medium text-gray-900">${option.name}</span>
                        <span class="ml-2 text-sm text-blue-600 font-semibold">+${option.cost_per_sqm}€/m²</span>
                    </div>
                </div>
            </label>
        `;
        list.innerHTML += optionHtml;
    });
}

// Gestion de la soumission du formulaire
document.getElementById('cost-simulator-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {
        service_type: formData.get('service_type'),
        property_type: formData.get('property_type'),
        surface: parseFloat(formData.get('surface')),
        quality_level: formData.get('quality_level'),
        urgency: formData.get('urgency'),
        additional_options: formData.getAll('additional_options[]')
    };
    
    // Afficher un loader
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-3 inline" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Calcul en cours...';
    
    // Envoyer la requête
    fetch('/simulateur/calculate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        
        if (result.success) {
            displayResults(result.result);
            
            // Scroll vers les résultats
            document.getElementById('results-container').scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start' 
            });
        } else {
            alert('Erreur lors du calcul. Veuillez réessayer.');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        alert('Erreur lors du calcul. Veuillez réessayer.');
    });
});

function displayResults(result) {
    const container = document.getElementById('results-container');
    
    // Afficher le conteneur
    container.style.display = 'block';
    
    // Coût principal
    document.getElementById('estimated-cost').textContent = formatCurrency(result.total_cost);
    document.getElementById('cost-range').textContent = `Fourchette: ${formatCurrency(result.min_cost)} - ${formatCurrency(result.max_cost)}`;
    
    // Détails
    document.getElementById('result-service').textContent = result.service_name;
    document.getElementById('result-surface').textContent = result.surface + ' m² (soit ' + formatCurrency(result.cost_per_sqm) + '/m²)';
    document.getElementById('result-quality').textContent = result.quality_label;
    document.getElementById('result-urgency').textContent = result.urgency_label;
    document.getElementById('result-property').textContent = result.property_label;
    
    // Décomposition
    const breakdown = document.getElementById('cost-breakdown');
    breakdown.innerHTML = `
        <div class="flex justify-between pb-2 border-b border-gray-200">
            <span class="text-gray-600">Coût de base</span>
            <span class="font-medium text-gray-900">${formatCurrency(result.breakdown.base)}</span>
        </div>
        <div class="flex justify-between pb-2 border-b border-gray-200">
            <span class="text-gray-600">Qualité (×${result.breakdown.quality_multiplier})</span>
            <span class="font-medium text-gray-900">${result.breakdown.quality_multiplier > 1 ? '+' : ''}${Math.round((result.breakdown.quality_multiplier - 1) * 100)}%</span>
        </div>
        <div class="flex justify-between pb-2 border-b border-gray-200">
            <span class="text-gray-600">Urgence (×${result.breakdown.urgency_multiplier})</span>
            <span class="font-medium text-gray-900">${result.breakdown.urgency_multiplier > 1 ? '+' : ''}${Math.round((result.breakdown.urgency_multiplier - 1) * 100)}%</span>
        </div>
        ${result.breakdown.options > 0 ? `
        <div class="flex justify-between pb-2 border-b border-gray-200">
            <span class="text-gray-600">Options</span>
            <span class="font-medium text-blue-600">+${formatCurrency(result.breakdown.options)}</span>
        </div>
        ` : ''}
        <div class="flex justify-between pt-2 font-bold text-lg">
            <span class="text-gray-900">Total</span>
            <span class="text-blue-600">${formatCurrency(result.total_cost)}</span>
        </div>
    `;
    
    // Options sélectionnées
    const optionsContainer = document.getElementById('selected-options-container');
    const optionsList = document.getElementById('selected-options-list');
    
    if (result.selected_options && result.selected_options.length > 0) {
        optionsContainer.style.display = 'block';
        optionsList.innerHTML = result.selected_options.map(opt => `
            <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                <span class="text-gray-900">${opt.name}</span>
                <span class="font-semibold text-blue-600">${formatCurrency(opt.cost)}</span>
            </div>
        `).join('');
    } else {
        optionsContainer.style.display = 'none';
    }
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
}
</script>
@endpush
@endsection

