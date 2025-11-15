@extends('layouts.app')

@section('title', 'Erreur serveur - ' . setting('company_name', 'JD RENOVATION SERVICE'))
@section('description', 'Une erreur technique s\'est produite. Nos équipes sont informées et travaillent à résoudre le problème.')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-red-50 to-orange-50 flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto text-center">
        <!-- Logo/Image 500 -->
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-32 h-32 bg-white rounded-full shadow-lg mb-6">
                <i class="fas fa-tools text-6xl text-red-600"></i>
            </div>
        </div>

        <!-- Titre principal -->
        <h1 class="text-6xl font-bold text-gray-900 mb-4">
            <span class="text-red-600">5</span><span class="text-orange-600">0</span><span class="text-red-600">0</span>
        </h1>
        
        <h2 class="text-3xl font-bold text-gray-800 mb-6">
            Oups ! Une erreur technique s'est produite
        </h2>
        
        <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
            Nos équipes techniques sont en train de réparer le problème. 
            En attendant, nos experts en rénovation sont toujours disponibles pour vous aider !
        </p>

        <!-- Actions principales -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
            <!-- Bouton Accueil -->
            <a href="{{ url('/') }}" 
               class="inline-flex items-center px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg">
                <i class="fas fa-home mr-3"></i>
                Retour à l'accueil
            </a>
            
            <!-- Bouton Devis -->
            <a href="{{ route('form.step', 'propertyType') }}" 
               class="inline-flex items-center px-8 py-4 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg"
               onclick="trackFormClick('500-page')">
                <i class="fas fa-calculator mr-3"></i>
                Demander un devis
            </a>
        </div>

        <!-- Section Contact -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
            <h3 class="text-2xl font-bold text-gray-800 mb-6">
                <i class="fas fa-phone text-green-600 mr-3"></i>
                Contactez-nous directement !
            </h3>
            
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Téléphone -->
                <div class="text-center">
                    <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-phone text-2xl text-green-600"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-800 mb-2">Appelez-nous</h4>
                    <p class="text-gray-600 mb-4">Service client disponible</p>
                    <a href="tel:{{ setting('company_phone_raw') }}" 
                       class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg"
                       onclick="trackPhoneCall('500-page', 'phone')">
                        <i class="fas fa-phone mr-2"></i>
                        {{ setting('company_phone', '01 23 45 67 89') }}
                    </a>
                </div>
                
                <!-- Email -->
                <div class="text-center">
                    <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-envelope text-2xl text-blue-600"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-800 mb-2">Écrivez-nous</h4>
                    <p class="text-gray-600 mb-4">Réponse garantie</p>
                    <a href="mailto:{{ setting('company_email', 'contact@jd-renovation-service.fr') }}" 
                       class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg">
                        <i class="fas fa-envelope mr-2"></i>
                        Nous écrire
                    </a>
                </div>
            </div>
        </div>

        <!-- Message rassurant -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h3 class="text-2xl font-bold text-gray-800 mb-6">
                <i class="fas fa-shield-alt text-green-600 mr-3"></i>
                Nos services continuent normalement
            </h3>
            
            <div class="grid md:grid-cols-3 gap-6 text-center">
                <div class="p-4">
                    <i class="fas fa-tools text-4xl text-blue-600 mb-3"></i>
                    <h4 class="font-semibold text-gray-800 mb-2">Travaux de rénovation</h4>
                    <p class="text-gray-600 text-sm">Toiture, façade, isolation</p>
                </div>
                <div class="p-4">
                    <i class="fas fa-shield-alt text-4xl text-green-600 mb-3"></i>
                    <h4 class="font-semibold text-gray-800 mb-2">Garantie décennale</h4>
                    <p class="text-gray-600 text-sm">Sur tous nos travaux</p>
                </div>
                <div class="p-4">
                    <i class="fas fa-clock text-4xl text-orange-600 mb-3"></i>
                    <h4 class="font-semibold text-gray-800 mb-2">Intervention rapide</h4>
                    <p class="text-gray-600 text-sm">Devis gratuit sous 24h</p>
                </div>
            </div>
        </div>

        <!-- Message technique -->
        <div class="mt-8 text-center">
            <p class="text-gray-600 text-lg">
                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                <strong>Problème technique temporaire</strong> • 
                <strong>Nos équipes sont informées</strong> • 
                <strong>Service client disponible</strong>
            </p>
        </div>
    </div>
</div>

<!-- Floating Call Button sera affiché automatiquement par le layout -->
@endsection

@section('scripts')
<script>
// Tracking spécifique pour la page 500
document.addEventListener('DOMContentLoaded', function() {
    // Track 500 page view
    if (typeof gtag !== 'undefined') {
        gtag('event', 'page_view', {
            'page_title': '500 - Erreur serveur',
            'page_location': window.location.href
        });
    }
    
    // Track 500 error
    if (typeof fbq !== 'undefined') {
        fbq('track', 'PageView', {
            'content_name': '500 Error Page'
        });
    }
});

// Fonction pour tracker les clics sur le formulaire depuis la page 500
function trackFormClick(page) {
    fetch('/api/track-form-click', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            source_page: page,
            error_page: true
        })
    }).catch(error => console.log('Tracking error:', error));
}

// Fonction pour tracker les appels depuis la page 500
function trackPhoneCall(page, type) {
    fetch('/api/track-phone-call', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            source_page: page,
            phone_number: '{{ setting("company_phone_raw") }}',
            error_page: true,
            call_type: type
        })
    }).catch(error => console.log('Tracking error:', error));
}
</script>
@endsection
