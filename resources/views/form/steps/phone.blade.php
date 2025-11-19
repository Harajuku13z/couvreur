@extends('layouts.app')

@section('title', 'Téléphone - Simulateur de Travaux')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-600">Étape 10 sur 11</span>
                    <span class="text-sm font-medium text-gray-600">91%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: 91%"></div>
                </div>
            </div>

            <!-- Question -->
            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">
                    Quel est votre numéro de téléphone ?
                </h2>
                
                <!-- FORMULAIRE SIMPLE (POST classique) -->
                <form method="POST" action="{{ route('form.submit', 'phone') }}" id="phoneForm">
                    @csrf
                    <input type="hidden" name="recaptcha_token" id="recaptcha_token">
                    
                    <div class="max-w-md mx-auto">
                        <!-- Téléphone -->
                        <div class="mb-6">
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                Numéro de téléphone * <span class="text-xs text-gray-500">(10 chiffres)</span>
                            </label>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone', $submission->phone ?? '') }}"
                                   class="w-full px-4 py-3 text-lg border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                                   placeholder="06 12 34 56 78"
                                   maxlength="14"
                                   required
                                   autofocus>
                            <p class="text-xs text-gray-500 mt-1">Format : 06 12 34 56 78</p>
                            @error('phone')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            @error('recaptcha')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Aide -->
                        <div class="p-4 bg-blue-50 rounded-lg">
                            <p class="text-gray-700 text-sm flex items-start">
                                <i class="fas fa-info-circle mr-2 mt-1 text-blue-600"></i>
                                <span>Votre numéro nous permet de vous contacter pour affiner votre projet et vous proposer un devis personnalisé.</span>
                            </p>
                        </div>
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
                                class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                            Suivant
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('form.partials.recaptcha')

<script>
// Formatage automatique du numéro de téléphone
const phoneInput = document.getElementById('phone');

phoneInput.addEventListener('input', function(e) {
    let value = this.value.replace(/\D/g, ''); // Garder que les chiffres
    
    // Limiter à 10 chiffres
    if (value.length > 10) {
        value = value.slice(0, 10);
    }
    
    // Formater : 06 12 34 56 78
    if (value.length >= 2) {
        value = value.match(/.{1,2}/g).join(' ');
    }
    
    this.value = value;
    
    // Feedback visuel
    const digitsOnly = value.replace(/\s/g, '');
    if (digitsOnly.length === 10) {
        this.classList.remove('border-red-500');
        this.classList.add('border-green-500');
    } else if (digitsOnly.length > 0) {
        this.classList.remove('border-green-500');
        this.classList.add('border-red-500');
    } else {
        this.classList.remove('border-red-500', 'border-green-500');
    }
});
</script>

<style>
.border-green-500 {
    border-color: #10b981 !important;
}

.border-red-500 {
    border-color: #ef4444 !important;
}
</style>
@endsection
