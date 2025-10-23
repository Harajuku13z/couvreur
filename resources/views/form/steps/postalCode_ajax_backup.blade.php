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
                    <div class="bg-blue-600 h-2 rounded-full progress-bar" style="width: 82%"></div>
                </div>
            </div>

            <!-- Question -->
            <div class="bg-white rounded-lg shadow-lg p-8 mb-8 fade-in">
                <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">
                    Quel est le code postal où seront réalisés les travaux ?
                </h2>
                
                <form id="postalCodeForm" method="POST">
                    @csrf
                    <div class="max-w-md mx-auto">
                        <div class="space-y-4">
                            <div>
                                <label for="postalCode" class="block text-sm font-medium text-gray-700 mb-2">
                                    Code postal *
                                </label>
                                <input type="text" 
                                       id="postalCode" 
                                       name="postal_code_number" 
                                       value="{{ explode(',', $submission->postal_code ?? '')[0] ?? '' }}"
                                       class="w-full px-4 py-3 text-lg border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none transition"
                                       placeholder="75001"
                                       maxlength="5"
                                       pattern="[0-9]{5}"
                                       required
                                       autocomplete="postal-code">
                                <p class="text-xs text-gray-500 mt-1">5 chiffres requis</p>
                            </div>
                            
                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                                    Ville *
                                </label>
                                <input type="text" 
                                       id="city" 
                                       name="city" 
                                       value="{{ trim(explode(',', $submission->postal_code ?? ',')[1] ?? '') }}"
                                       class="w-full px-4 py-3 text-lg border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none transition"
                                       placeholder="Paris"
                                       required
                                       autocomplete="address-level2">
                                <p class="text-xs text-gray-500 mt-1">Nom de votre ville</p>
                            </div>
                        </div>
                        
                        <!-- Aide -->
                        <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                            <p class="text-gray-700 text-sm flex items-start">
                                <i class="fas fa-info-circle mr-2 mt-1 text-blue-600"></i>
                                <span>Ces informations nous aident à estimer les coûts de déplacement et à vous proposer des artisans dans votre région.</span>
                            </p>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Navigation -->
            <div class="flex justify-between">
                <button type="button" 
                        onclick="goToPreviousStep()" 
                        class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Précédent
                </button>
                
                <button type="button"
                        id="nextBtn" 
                        onclick="submitForm()"
                        class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                    Suivant
                    <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
console.log('🚀 Étape 9 - Code Postal - Chargée');

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('postalCodeForm');
    const postalCodeInput = document.getElementById('postalCode');
    const cityInput = document.getElementById('city');
    const nextBtn = document.getElementById('nextBtn');

    console.log('✅ Éléments trouvés:', {
        form: !!form,
        postalCode: !!postalCodeInput,
        city: !!cityInput,
        nextBtn: !!nextBtn
    });

    // Validation en temps réel
    function validateForm() {
        const postalCode = postalCodeInput.value.trim();
        const city = cityInput.value.trim();
        
        console.log('🔍 Validation:', {
            postalCodeLength: postalCode.length,
            cityLength: city.length,
            postalCodeValid: postalCode.length === 5,
            cityValid: city.length > 0
        });
        
        const isValid = postalCode.length === 5 && city.length > 0;
        
        nextBtn.disabled = !isValid;
        
        if (isValid) {
            nextBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            postalCodeInput.classList.remove('border-red-500', 'border-gray-300');
            postalCodeInput.classList.add('border-green-500');
            cityInput.classList.remove('border-red-500', 'border-gray-300');
            cityInput.classList.add('border-green-500');
            console.log('✅ Formulaire valide - Bouton activé');
        } else {
            nextBtn.classList.add('opacity-50', 'cursor-not-allowed');
            
            if (postalCode.length > 0 && postalCode.length !== 5) {
                postalCodeInput.classList.add('border-red-500');
                postalCodeInput.classList.remove('border-green-500', 'border-gray-300');
            } else {
                postalCodeInput.classList.remove('border-red-500', 'border-green-500');
                postalCodeInput.classList.add('border-gray-300');
            }
            
            console.log('❌ Formulaire invalide - Bouton désactivé');
        }
        
        return isValid;
    }

    // Validation initiale
    setTimeout(validateForm, 100);

    // Limiter le code postal aux chiffres seulement
    postalCodeInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
        validateForm();
    });

    // Validation sur changement de ville
    cityInput.addEventListener('input', validateForm);

    // Enter sur code postal = focus ville
    postalCodeInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            cityInput.focus();
        }
    });

    // Enter sur ville = submit
    cityInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && validateForm()) {
            e.preventDefault();
            submitForm();
        }
    });
});

// Fonction de soumission du formulaire
function submitForm() {
    console.log('📤 submitForm() appelée');
    
    const postalCodeInput = document.getElementById('postalCode');
    const cityInput = document.getElementById('city');
    const nextBtn = document.getElementById('nextBtn');
    
    const postalCode = postalCodeInput.value.trim();
    const city = cityInput.value.trim();
    
    console.log('📋 Données à envoyer:', {
        postalCode: postalCode,
        city: city
    });
    
    // Validation finale
    if (postalCode.length !== 5) {
        console.error('❌ Code postal invalide:', postalCode.length);
        showNotification('Veuillez entrer un code postal valide (5 chiffres)', 'error');
        return;
    }
    
    if (city.length === 0) {
        console.error('❌ Ville manquante');
        showNotification('Veuillez entrer le nom de votre ville', 'error');
        return;
    }
    
    // Combiner le code postal et la ville
    const postalCodeCombined = postalCode + ', ' + city;
    console.log('📦 Postal code combiné:', postalCodeCombined);
    
    // Désactiver le bouton
    nextBtn.disabled = true;
    nextBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Envoi en cours...';
    
    console.log('📡 Envoi de la requête AJAX...');
    
    // Envoyer les données via AJAX
    fetch('{{ route("form.next", "postalCode") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            postal_code: postalCodeCombined
        })
    })
    .then(response => {
        console.log('📥 Réponse reçue, status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('✅ Données reçues:', data);
        
        if (data.success) {
            console.log('🎉 Succès ! Redirection vers:', data.redirect);
            showNotification('Code postal enregistré !', 'success');
            
            // Redirection après un court délai
            setTimeout(function() {
                window.location.href = data.redirect;
            }, 500);
        } else {
            console.error('❌ Erreur dans la réponse:', data);
            nextBtn.disabled = false;
            nextBtn.innerHTML = 'Suivant <i class="fas fa-arrow-right ml-2"></i>';
            showNotification(data.message || 'Une erreur est survenue', 'error');
        }
    })
    .catch(error => {
        console.error('❌ Erreur fetch:', error);
        nextBtn.disabled = false;
        nextBtn.innerHTML = 'Suivant <i class="fas fa-arrow-right ml-2"></i>';
        showNotification('Erreur de connexion. Veuillez réessayer.', 'error');
    });
}

// Fonction pour revenir à l'étape précédente
function goToPreviousStep() {
    console.log('⬅️ Retour à l\'étape précédente');
    
    fetch('{{ route("form.previous", "postalCode") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('✅ Redirection vers:', data.redirect);
            window.location.href = data.redirect;
        } else {
            console.error('❌ Erreur:', data);
            showNotification('Erreur lors du retour', 'error');
        }
    })
    .catch(error => {
        console.error('❌ Erreur:', error);
        showNotification('Erreur de connexion', 'error');
    });
}

// Test au chargement
console.log('🧪 Test des fonctions globales:', {
    showNotification: typeof showNotification,
    makeRequest: typeof makeRequest
});
</script>

<style>
.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.progress-bar {
    transition: width 0.3s ease;
}

input:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.border-green-500 {
    border-color: #10b981 !important;
}

.border-red-500 {
    border-color: #ef4444 !important;
}
</style>
@endsection
