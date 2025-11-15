@extends('layouts.app')

@php
    // Passer les métadonnées SEO au layout
    $currentPage = 'form';
    if (isset($pageTitle)) {
        // Les variables sont déjà définies depuis FormControllerSimple::showStep()
    } else {
        $pageTitle = 'Simulateur de devis gratuit - ' . setting('company_name', 'Notre Entreprise');
        $pageDescription = 'Obtenez votre devis gratuit en quelques clics pour vos travaux de rénovation. Estimation rapide et gratuite.';
        $pageKeywords = 'devis gratuit, simulateur devis, estimation travaux, devis en ligne';
    }
@endphp

@section('title', $pageTitle ?? 'Simulateur de devis gratuit')

@section('description', $pageDescription ?? 'Obtenez votre devis gratuit en quelques clics.')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-600">Étape 1 sur 11</span>
                    <span class="text-sm font-medium text-gray-600">9%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: 9%"></div>
                </div>
            </div>

            <!-- Question -->
            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">
                    Quel type de bien souhaitez-vous rénover ?
                </h2>
                
                @error('recaptcha')
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                </div>
                @enderror
                
                <form method="POST" action="{{ route('form.submit', 'propertyType') }}" id="propertyForm">
                    @csrf
                    <input type="hidden" name="recaptcha_token" id="recaptcha_token">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Maison -->
                        <label for="property_maison" class="cursor-pointer">
                            <input type="radio" name="property_type" value="maison" id="property_maison" class="hidden" required>
                            <div class="property-option border-3 border-gray-300 rounded-xl p-8 text-center hover:border-blue-500 hover:shadow-xl transition">
                                <img src="{{ asset('icons2/Maison.webp') }}" alt="Maison" class="w-32 h-32 mx-auto mb-4 object-contain">
                                <h3 class="text-2xl font-bold text-gray-800 mb-2">Maison</h3>
                                <p class="text-gray-600">Maison individuelle ou mitoyenne</p>
                            </div>
                        </label>

                        <!-- Appartement -->
                        <label for="property_appartement" class="cursor-pointer">
                            <input type="radio" name="property_type" value="appartement" id="property_appartement" class="hidden" required>
                            <div class="property-option border-3 border-gray-300 rounded-xl p-8 text-center hover:border-blue-500 hover:shadow-xl transition">
                                <img src="{{ asset('icons2/Appartement.webp') }}" alt="Appartement" class="w-32 h-32 mx-auto mb-4 object-contain">
                                <h3 class="text-2xl font-bold text-gray-800 mb-2">Appartement</h3>
                                <p class="text-gray-600">Appartement en immeuble</p>
                            </div>
                        </label>
                    </div>
                </form>
            </div>

            <!-- Navigation -->
            <div class="flex justify-between">
                <a href="{{ url('/') }}" 
                   class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Retour
                </a>
                
                <button type="submit" form="propertyForm" id="submitBtn"
                        class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                    Suivant
                    <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Fonction pour mettre à jour la sélection visuelle
function updateSelection(radio) {
    // Retirer la sélection de toutes les options
    document.querySelectorAll('.property-option').forEach(opt => {
        opt.classList.remove('border-blue-500', 'bg-blue-50');
        opt.classList.add('border-gray-300');
    });
    
    // Ajouter la sélection à l'option cliquée
    const option = radio.closest('label').querySelector('.property-option');
    option.classList.remove('border-gray-300');
    option.classList.add('border-blue-500', 'bg-blue-50');
}

// Fonction pour soumettre le formulaire avec reCAPTCHA
function submitPropertyForm() {
    const form = document.getElementById('propertyForm');
    const recaptchaTokenInput = document.getElementById('recaptcha_token');
    const submitBtn = document.getElementById('submitBtn');
    
    // Vérifier si reCAPTCHA est configuré
    const recaptchaSiteKey = '{{ setting("recaptcha_site_key") }}';
    
    if (recaptchaSiteKey) {
        // Attendre que grecaptcha soit chargé (important sur mobile)
        if (typeof grecaptcha === 'undefined') {
            // Si grecaptcha n'est pas encore chargé, attendre un peu
            setTimeout(function() {
                if (typeof grecaptcha !== 'undefined') {
                    submitPropertyForm();
                } else {
                    // Si après 3 secondes grecaptcha n'est toujours pas chargé, soumettre sans
                    console.warn('reCAPTCHA non chargé, soumission sans vérification');
                    form.submit();
                }
            }, 1000);
            return;
        }
        
        // Désactiver le bouton pendant la vérification
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Vérification...';
        }
        
        grecaptcha.ready(function() {
            grecaptcha.execute(recaptchaSiteKey, {action: 'submit'}).then(function(token) {
                if (token) {
                    recaptchaTokenInput.value = token;
                    form.submit();
                } else {
                    throw new Error('Token reCAPTCHA vide');
                }
            }).catch(function(error) {
                console.error('reCAPTCHA error:', error);
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Suivant <i class="fas fa-arrow-right ml-2"></i>';
                }
                // Sur mobile, parfois reCAPTCHA peut échouer, permettre quand même la soumission
                // mais avec un message d'avertissement
                if (confirm('Erreur de vérification anti-robot. Souhaitez-vous réessayer ?')) {
                    setTimeout(function() {
                        submitPropertyForm();
                    }, 500);
                } else {
                    // Permettre la soumission même sans token (le serveur gérera)
                    form.submit();
                }
            });
        });
    } else {
        // Si reCAPTCHA n'est pas configuré, soumettre directement
        form.submit();
    }
}

// Écouteur sur chaque option (label + div)
document.querySelectorAll('label[for^="property_"]').forEach(function(label) {
    label.addEventListener('click', function(e) {
        const radio = this.querySelector('input[type="radio"]');
        radio.checked = true;
        updateSelection(radio);
        
        // Auto-submit après un court délai (UX) avec reCAPTCHA
        setTimeout(function() {
            submitPropertyForm();
        }, 300);
    });
});

// Écouteur aussi sur les radios pour compatibilité
document.querySelectorAll('input[name="property_type"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        updateSelection(this);
        
        // Auto-submit après un court délai (UX) avec reCAPTCHA
        setTimeout(function() {
            submitPropertyForm();
        }, 300);
    });
});

// Écouteur sur le bouton de soumission
document.getElementById('propertyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitPropertyForm();
});

// Pré-sélectionner si une valeur existe
const currentValue = '{{ old('property_type', $submission->property_type ?? '') }}';
if (currentValue) {
    const radio = document.getElementById('property_' + currentValue);
    if (radio) {
        radio.checked = true;
        updateSelection(radio);
    }
}

console.log('✅ Étape 1 - Type de Bien (VERSION SIMPLE)');
</script>

@include('form.partials.recaptcha')

<style>
.property-option {
    transition: all 0.3s ease;
}

.property-option:hover {
    transform: translateY(-5px);
}
</style>
@endsection














