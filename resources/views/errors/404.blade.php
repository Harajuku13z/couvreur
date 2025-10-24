@extends('layouts.app')

@section('title', 'Page non trouvée - ' . setting('company_name', 'JD RENOVATION SERVICE'))
@section('description', 'La page que vous recherchez n\'existe pas. Retournez à l\'accueil ou contactez-nous pour vos travaux de rénovation.')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-green-50 flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto text-center">
        <!-- Logo/Image 404 -->
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-32 h-32 bg-white rounded-full shadow-lg mb-6">
                <i class="fas fa-home text-6xl text-blue-600"></i>
            </div>
        </div>

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
            <a href="{{ route('home') }}" 
               class="inline-flex items-center px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg">
                <i class="fas fa-home mr-3"></i>
                Retour à l'accueil
            </a>
            
            <!-- Bouton Devis -->
            <a href="{{ route('form.step', 'propertyType') }}" 
               class="inline-flex items-center px-8 py-4 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg"
               onclick="trackFormClick('404-page')">
                <i class="fas fa-calculator mr-3"></i>
                Demander un devis
            </a>
        </div>

        <!-- Section Contact -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
            <h3 class="text-2xl font-bold text-gray-800 mb-6">
                <i class="fas fa-phone text-green-600 mr-3"></i>
                Besoin d'aide ? Contactez-nous !
            </h3>
            
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Téléphone -->
                <div class="text-center">
                    <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-phone text-2xl text-green-600"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-800 mb-2">Appelez-nous</h4>
                    <p class="text-gray-600 mb-4">Réponse immédiate</p>
                    <a href="tel:{{ setting('company_phone_raw') }}" 
                       class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg"
                       onclick="trackPhoneCall('404-page', 'phone')">
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
                    <p class="text-gray-600 mb-4">Réponse sous 24h</p>
                    <a href="mailto:{{ setting('company_email', 'contact@jd-renovation-service.fr') }}" 
                       class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg">
                        <i class="fas fa-envelope mr-2"></i>
                        Nous écrire
                    </a>
                </div>
            </div>
        </div>

        <!-- Services populaires -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h3 class="text-2xl font-bold text-gray-800 mb-6">
                <i class="fas fa-tools text-blue-600 mr-3"></i>
                Découvrez nos services
            </h3>
            
            @php
                $servicesData = \App\Models\Setting::get('services', '[]');
                $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
                
                if (!is_array($services)) {
                    $services = [];
                }
                
                $featuredServices = array_slice(array_filter($services, function($service) {
                    return is_array($service) && (($service['is_featured'] ?? false) || ($service['is_menu'] ?? false));
                }), 0, 4);
            @endphp
            
            @if(count($featuredServices) > 0)
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($featuredServices as $service)
                @if(is_array($service) && isset($service['name']) && isset($service['slug']))
                <a href="{{ route('services.show', $service['slug']) }}" 
                   class="block p-4 bg-gray-50 hover:bg-blue-50 rounded-lg transition-all duration-300 transform hover:scale-105">
                    <div class="text-center">
                        @if(isset($service['icon']) && $service['icon'])
                        <i class="{{ $service['icon'] }} text-3xl text-blue-600 mb-3"></i>
                        @else
                        <i class="fas fa-tools text-3xl text-blue-600 mb-3"></i>
                        @endif
                        <h4 class="font-semibold text-gray-800">{{ $service['name'] }}</h4>
                    </div>
                </a>
                @endif
                @endforeach
            </div>
            @else
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('services.show', 'travaux-de-toiture') }}" 
                   class="block p-4 bg-gray-50 hover:bg-blue-50 rounded-lg transition-all duration-300 transform hover:scale-105">
                    <div class="text-center">
                        <i class="fas fa-home text-3xl text-blue-600 mb-3"></i>
                        <h4 class="font-semibold text-gray-800">Travaux de toiture</h4>
                    </div>
                </a>
                <a href="{{ route('services.show', 'ravalement-facades') }}" 
                   class="block p-4 bg-gray-50 hover:bg-blue-50 rounded-lg transition-all duration-300 transform hover:scale-105">
                    <div class="text-center">
                        <i class="fas fa-paint-brush text-3xl text-blue-600 mb-3"></i>
                        <h4 class="font-semibold text-gray-800">Ravalement façades</h4>
                    </div>
                </a>
                <a href="{{ route('services.show', 'isolation-thermique') }}" 
                   class="block p-4 bg-gray-50 hover:bg-blue-50 rounded-lg transition-all duration-300 transform hover:scale-105">
                    <div class="text-center">
                        <i class="fas fa-thermometer-half text-3xl text-blue-600 mb-3"></i>
                        <h4 class="font-semibold text-gray-800">Isolation thermique</h4>
                    </div>
                </a>
                <a href="{{ route('services.show', 'gouttieres-evacuations') }}" 
                   class="block p-4 bg-gray-50 hover:bg-blue-50 rounded-lg transition-all duration-300 transform hover:scale-105">
                    <div class="text-center">
                        <i class="fas fa-tint text-3xl text-blue-600 mb-3"></i>
                        <h4 class="font-semibold text-gray-800">Gouttières</h4>
                    </div>
                </a>
            </div>
            @endif
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
