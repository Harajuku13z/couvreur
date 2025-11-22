@extends('layouts.app')

@section('title', 'Email - Simulateur de Travaux')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-600">Étape 11 sur 11</span>
                    <span class="text-sm font-medium text-gray-600">100%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-600 h-2 rounded-full" style="width: 100%"></div>
                </div>
            </div>

            <!-- Question -->
            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <h2 class="text-3xl font-bold text-center mb-4 text-gray-800">
                    Dernière étape !
                </h2>
                <p class="text-center text-gray-600 mb-8">
                    Indiquez votre adresse email pour recevoir votre devis personnalisé
                </p>
                
                <!-- FORMULAIRE SIMPLE (POST classique) -->
                <form method="POST" action="{{ route('form.submit', 'email') }}" id="emailForm">
                    @csrf
                    <input type="hidden" name="recaptcha_token" id="recaptcha_token">
                    
                    <div class="max-w-md mx-auto">
                        <!-- Email -->
                        <div class="mb-6">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Adresse email *
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $submission->email ?? '') }}"
                                   class="w-full px-4 py-3 text-lg border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                                   placeholder="exemple@email.com"
                                   required
                                   autofocus>
                            <p class="text-xs text-gray-500 mt-1">Format : exemple@email.com</p>
                            @error('email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            @error('recaptcha')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Aide -->
                        <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                            <p class="text-gray-700 text-sm flex items-start">
                                <i class="fas fa-check-circle mr-2 mt-1 text-green-600"></i>
                                <span>
                                    <strong>Félicitations !</strong> Vous recevrez votre devis personnalisé par email sous 24h.
                                    Nous vous contacterons également par téléphone pour affiner votre projet.
                                </span>
                            </p>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div class="flex justify-between mt-8">
                        <a href="{{ route('form.previous', 'email') }}" 
                           class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Précédent
                        </a>
                        
                        <button type="submit" 
                                id="submitBtn"
                                class="bg-green-600 text-white px-8 py-3 rounded-lg hover:bg-green-700 transition font-semibold">
                            <i class="fas fa-check mr-2"></i>
                            Terminer ma demande
                        </button>
                    </div>
                </form>
            </div>

            <!-- Avantages -->
            <div class="grid md:grid-cols-3 gap-4 mt-8">
                <div class="bg-white rounded-lg p-4 text-center">
                    <div class="text-blue-600 text-3xl mb-2">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <p class="text-sm font-semibold">100% Gratuit</p>
                </div>
                <div class="bg-white rounded-lg p-4 text-center">
                    <div class="text-green-600 text-3xl mb-2">
                        <i class="fas fa-lock"></i>
                    </div>
                    <p class="text-sm font-semibold">Sans Engagement</p>
                </div>
                <div class="bg-white rounded-lg p-4 text-center">
                    <div class="text-orange-600 text-3xl mb-2">
                        <i class="fas fa-clock"></i>
                    </div>
                    <p class="text-sm font-semibold">Réponse sous 24h</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validation email en temps réel
const emailInput = document.getElementById('email');

emailInput.addEventListener('input', function() {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (emailRegex.test(this.value)) {
        this.classList.remove('border-red-500');
        this.classList.add('border-green-500');
    } else if (this.value.length > 0) {
        this.classList.remove('border-green-500');
        this.classList.add('border-red-500');
    } else {
        this.classList.remove('border-red-500', 'border-green-500');
    }
});

console.log('✅ Page chargée : Étape 11 - Email (VERSION SIMPLE) - Dernière étape !');
</script>

<style>
.border-green-500 {
    border-color: #10b981 !important;
}

.border-red-500 {
    border-color: #ef4444 !important;
}
</style>

@include('form.partials.recaptcha')
@endsection







