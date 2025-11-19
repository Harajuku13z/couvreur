@extends('layouts.app')

@section('title', 'Code postal - Simulateur de Travaux')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-600">Étape 9 sur 11</span>
                    <span class="text-sm font-medium text-gray-600">82%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: 82%"></div>
                </div>
            </div>

            <!-- Question -->
            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">
                    Quel est le code postal où seront réalisés les travaux ?
                </h2>
                
                <!-- FORMULAIRE ULTRA-SIMPLE (POST classique, pas de AJAX) -->
                <form method="POST" action="{{ route('form.submit', 'postalCode') }}">
                    @csrf
                    
                    <div class="max-w-md mx-auto">
                        <div class="space-y-4">
                            <!-- Code Postal -->
                            <div>
                                <label for="postal_code_number" class="block text-sm font-medium text-gray-700 mb-2">
                                    Code postal * <span class="text-xs text-gray-500">(5 chiffres)</span>
                                </label>
                                <input type="text" 
                                       id="postal_code_number" 
                                       name="postal_code_number" 
                                       value="{{ old('postal_code_number', explode(',', $submission->postal_code ?? '')[0] ?? '') }}"
                                       class="w-full px-4 py-3 text-lg border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                                       placeholder="75001"
                                       maxlength="5"
                                       pattern="[0-9]{5}"
                                       required
                                       autofocus>
                                @error('postal_code_number')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Ville -->
                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                                    Ville *
                                </label>
                                <input type="text" 
                                       id="city" 
                                       name="city" 
                                       value="{{ old('city', trim(explode(',', $submission->postal_code ?? ',')[1] ?? '')) }}"
                                       class="w-full px-4 py-3 text-lg border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                                       placeholder="Paris"
                                       required>
                                @error('city')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Champ caché pour le code postal combiné -->
                            <input type="hidden" name="postal_code" id="postal_code_combined">
                        </div>
                        
                        <!-- Aide -->
                        <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                            <p class="text-gray-700 text-sm flex items-start">
                                <i class="fas fa-info-circle mr-2 mt-1 text-blue-600"></i>
                                <span>Ces informations nous permettent de vous proposer des artisans dans votre région.</span>
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

<script>
// Combiner le code postal et la ville avant soumission
document.querySelector('form').addEventListener('submit', function(e) {
    const postalCodeNumber = document.getElementById('postal_code_number').value.trim();
    const city = document.getElementById('city').value.trim();
    const combined = postalCodeNumber + ', ' + city;
    document.getElementById('postal_code_combined').value = combined;
    
    console.log('✅ Formulaire soumis :', {
        postalCodeNumber: postalCodeNumber,
        city: city,
        combined: combined
    });
});

// Validation en temps réel (optionnel)
document.getElementById('postal_code_number').addEventListener('input', function(e) {
    // Limiter aux chiffres
    this.value = this.value.replace(/[^0-9]/g, '');
    
    // Feedback visuel
    if (this.value.length === 5) {
        this.classList.remove('border-red-500');
        this.classList.add('border-green-500');
    } else if (this.value.length > 0) {
        this.classList.remove('border-green-500');
        this.classList.add('border-red-500');
    } else {
        this.classList.remove('border-red-500', 'border-green-500');
    }
});

document.getElementById('city').addEventListener('input', function(e) {
    if (this.value.trim().length > 0) {
        this.classList.remove('border-red-500');
        this.classList.add('border-green-500');
    } else {
        this.classList.remove('border-green-500');
    }
});

console.log('✅ Page chargée : Étape 9 - Code Postal (VERSION SIMPLE)');
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
