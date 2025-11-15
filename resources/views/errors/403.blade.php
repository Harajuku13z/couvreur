@extends('layouts.app')

@section('title', 'Accès interdit - ' . setting('company_name', 'JD RENOVATION SERVICE'))
@section('description', 'Vous n\'avez pas l\'autorisation d\'accéder à cette page. Retournez à l\'accueil ou contactez-nous.')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 to-pink-50 flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto text-center">
        <!-- Logo/Image 403 -->
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-32 h-32 bg-white rounded-full shadow-lg mb-6">
                <i class="fas fa-lock text-6xl text-purple-600"></i>
            </div>
        </div>

        <!-- Titre principal -->
        <h1 class="text-6xl font-bold text-gray-900 mb-4">
            <span class="text-purple-600">4</span><span class="text-pink-600">0</span><span class="text-purple-600">3</span>
        </h1>
        
        <h2 class="text-3xl font-bold text-gray-800 mb-6">
            Accès non autorisé
        </h2>
        
        <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
            Vous n'avez pas l'autorisation d'accéder à cette page. 
            Mais nos services de rénovation sont toujours disponibles pour vous !
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
               onclick="trackFormClick('403-page')">
                <i class="fas fa-calculator mr-3"></i>
                Demander un devis
            </a>
        </div>

        <!-- Section Contact -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
            <h3 class="text-2xl font-bold text-gray-800 mb-6">
                <i class="fas fa-phone text-green-600 mr-3"></i>
                Contactez-nous pour vos projets
            </h3>
            
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Téléphone -->
                <div class="text-center">
                    <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-phone text-2xl text-green-600"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-800 mb-2">Appelez-nous</h4>
                    <p class="text-gray-600 mb-4">Conseil personnalisé</p>
                    <a href="tel:{{ setting('company_phone_raw') }}" 
                       class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg"
                       onclick="trackPhoneCall('403-page', 'phone')">
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
                    <p class="text-gray-600 mb-4">Réponse rapide</p>
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
                <i class="fas fa-tools text-blue-600 mr-3"></i>
                Nos services vous attendent
            </h3>
            
            <div class="grid md:grid-cols-3 gap-6 text-center">
                <div class="p-4">
                    <i class="fas fa-home text-4xl text-blue-600 mb-3"></i>
                    <h4 class="font-semibold text-gray-800 mb-2">Rénovation complète</h4>
                    <p class="text-gray-600 text-sm">Toiture, façade, isolation</p>
                </div>
                <div class="p-4">
                    <i class="fas fa-shield-alt text-4xl text-green-600 mb-3"></i>
                    <h4 class="font-semibold text-gray-800 mb-2">Garantie décennale</h4>
                    <p class="text-gray-600 text-sm">Assurance qualité</p>
                </div>
                <div class="p-4">
                    <i class="fas fa-star text-4xl text-yellow-600 mb-3"></i>
                    <h4 class="font-semibold text-gray-800 mb-2">Satisfaction client</h4>
                    <p class="text-gray-600 text-sm">Plus de 10 ans d'expérience</p>
                </div>
            </div>
        </div>

        <!-- Message final -->
        <div class="mt-8 text-center">
            <p class="text-gray-600 text-lg">
                <i class="fas fa-handshake text-green-600 mr-2"></i>
                <strong>Devis gratuit</strong> • 
                <strong>Intervention rapide</strong> • 
                <strong>Professionnels qualifiés</strong>
            </p>
        </div>
    </div>
</div>

<!-- Floating Call Button sera affiché automatiquement par le layout -->
@endsection

@section('scripts')
<script>
// Tracking spécifique pour la page 403
document.addEventListener('DOMContentLoaded', function() {
    // Track 403 page view
    if (typeof gtag !== 'undefined') {
        gtag('event', 'page_view', {
            'page_title': '403 - Accès interdit',
            'page_location': window.location.href
        });
    }
    
    // Track 403 error
    if (typeof fbq !== 'undefined') {
        fbq('track', 'PageView', {
            'content_name': '403 Error Page'
        });
    }
});

// Fonction pour tracker les clics sur le formulaire depuis la page 403
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

// Fonction pour tracker les appels depuis la page 403
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
