@extends('layouts.app')

@section('title', 'Page non trouvée - ' . setting('company_name', 'JD RENOVATION SERVICE'))
@section('description', 'La page que vous recherchez n\'existe pas. Retournez à l\'accueil ou contactez-nous pour vos travaux de rénovation.')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-green-50 flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto text-center">

        <!-- Titre principal -->
        <h1 class="text-6xl font-bold text-gray-900 mb-4">
            <span class="text-blue-600">4</span><span class="text-green-600">0</span><span class="text-blue-600">4</span>
        </h1>
        
        <h2 class="text-3xl font-bold text-gray-800 mb-6">
            Oups ! Cette page n'existe pas
        </h2>
        
        <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
            La page que vous recherchez semble avoir disparu. Mais ne vous inquiétez pas, 
            nos experts en rénovation sont là pour vous aider !
        </p>

        <!-- Actions principales -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
            <!-- Bouton Accueil -->
            <a href="{{ url('/') }}" 
               class="inline-flex items-center px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg">
                <i class="fas fa-home mr-3"></i>
                Retour à l'accueil
            </a>
        </div>


        <!-- Message rassurant -->
        <div class="mt-8 text-center">
            <p class="text-gray-600 text-lg">
                <i class="fas fa-shield-alt text-green-600 mr-2"></i>
                <strong>Garantie décennale</strong> sur tous nos travaux • 
                <strong>Devis gratuit</strong> • 
                <strong>Intervention rapide</strong>
            </p>
        </div>
    </div>
</div>

<!-- Floating Call Button sera affiché automatiquement par le layout -->
@endsection

@section('scripts')
<script>
// Tracking spécifique pour la page 404
document.addEventListener('DOMContentLoaded', function() {
    // Track 404 page view
    if (typeof gtag !== 'undefined') {
        gtag('event', 'page_view', {
            'page_title': '404 - Page non trouvée',
            'page_location': window.location.href
        });
    }
    
    // Track 404 error
    if (typeof fbq !== 'undefined') {
        fbq('track', 'PageView', {
            'content_name': '404 Error Page'
        });
    }
});

// Fonction pour tracker les clics sur le formulaire depuis la page 404
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

// Fonction pour tracker les appels depuis la page 404
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
