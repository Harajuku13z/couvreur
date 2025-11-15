@extends('layouts.app')

@section('title', 'Demande envoyée - Simulateur de Travaux')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-green-50 to-blue-50 py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Success Message -->
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden mb-8">
                <!-- Header -->
                <div class="bg-gradient-to-r from-green-500 to-green-600 text-white text-center py-12 px-6">
                    <div class="mb-6">
                        <div class="inline-block bg-white rounded-full p-4">
                            <svg class="w-16 h-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </div>
                    <h1 class="text-4xl font-bold mb-4">✅ Demande Envoyée avec Succès !</h1>
                    <p class="text-xl text-green-100">
                        Votre demande de devis a bien été enregistrée
                    </p>
                </div>

                <!-- URGENT CALL BOX -->
                <div class="bg-gradient-to-r from-red-500 to-red-600 text-white p-8 text-center">
                    <div class="max-w-2xl mx-auto">
                        <div class="flex items-center justify-center mb-4">
                            <svg class="w-12 h-12 animate-bounce" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path>
                            </svg>
                        </div>
                        <h2 class="text-3xl font-bold mb-4">🚨 VOUS AVEZ UNE URGENCE ?</h2>
                        <p class="text-xl mb-6">Appelez-nous immédiatement !</p>
                        <a href="tel:{{ company('phone_raw', '0123456789') }}" 
                           class="inline-block bg-white text-red-600 text-4xl font-bold py-6 px-12 rounded-2xl hover:bg-red-50 transition transform hover:scale-105 shadow-2xl">
                            📞 {{ company('phone', '01 23 45 67 89') }}
                        </a>
                        <p class="mt-6 text-red-100">
                            {{ company('hours', 'Du lundi au vendredi, de 9h à 18h') }}
                        </p>
                        <p class="mt-3 text-sm text-red-100">
                            Notre équipe est disponible pour répondre à toutes vos questions
                        </p>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-8">
                    <!-- Thank You Message -->
                    <div class="text-center mb-8">
                        <h3 class="text-2xl font-bold text-gray-800 mb-4">
                            Merci {{ $submission->first_name }} !
                        </h3>
                        <p class="text-gray-600 text-lg">
                            Nous avons bien reçu votre demande de devis et nous vous remercions de votre confiance.
                        </p>
                    </div>

                    <!-- Info Cards -->
                    <div class="grid md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-blue-50 rounded-xl p-6 text-center">
                            <div class="text-4xl mb-3">📧</div>
                            <h4 class="font-semibold text-gray-800 mb-2">Email de Confirmation</h4>
                            <p class="text-sm text-gray-600">
                                Un email récapitulatif vous a été envoyé à<br>
                                <strong>{{ $submission->email }}</strong>
                            </p>
                        </div>

                        <div class="bg-green-50 rounded-xl p-6 text-center">
                            <div class="text-4xl mb-3">⏱️</div>
                            <h4 class="font-semibold text-gray-800 mb-2">Réponse Rapide</h4>
                            <p class="text-sm text-gray-600">
                                Notre équipe vous contactera sous <strong>24 heures</strong> pour affiner votre projet
                            </p>
                        </div>

                        <div class="bg-purple-50 rounded-xl p-6 text-center">
                            <div class="text-4xl mb-3">💯</div>
                            <h4 class="font-semibold text-gray-800 mb-2">100% Gratuit</h4>
                            <p class="text-sm text-gray-600">
                                Votre devis est <strong>sans engagement</strong> et totalement gratuit
                            </p>
                        </div>
                    </div>

                    <!-- Next Steps -->
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-8 mb-8">
                        <h3 class="text-2xl font-bold text-gray-800 mb-6 text-center">
                            📋 Les Prochaines Étapes
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 bg-blue-500 text-white w-10 h-10 rounded-full flex items-center justify-center font-bold mr-4">1</div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-800 mb-1">Analyse de votre demande</h4>
                                    <p class="text-gray-600 text-sm">Notre équipe étudie en détail votre projet et vos besoins spécifiques</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="flex-shrink-0 bg-blue-500 text-white w-10 h-10 rounded-full flex items-center justify-center font-bold mr-4">2</div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-800 mb-1">Contact personnalisé (sous 24h)</h4>
                                    <p class="text-gray-600 text-sm">Un conseiller vous appelle pour discuter de votre projet et répondre à vos questions</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="flex-shrink-0 bg-blue-500 text-white w-10 h-10 rounded-full flex items-center justify-center font-bold mr-4">3</div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-800 mb-1">Réception de votre devis détaillé</h4>
                                    <p class="text-gray-600 text-sm">Vous recevez un devis personnalisé et complet par email</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="flex-shrink-0 bg-green-500 text-white w-10 h-10 rounded-full flex items-center justify-center font-bold mr-4">4</div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-800 mb-1">Planification des travaux</h4>
                                    <p class="text-gray-600 text-sm">Si notre offre vous convient, nous organisons ensemble la réalisation de vos travaux</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recap -->
                    <div class="bg-gray-50 rounded-xl p-6 mb-8">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">
                            📝 Récapitulatif de Votre Demande
                        </h3>
                        <div class="grid md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Type de bien :</span>
                                <strong class="ml-2">{{ ucfirst($submission->property_type) }}</strong>
                            </div>
                            <div>
                                <span class="text-gray-600">Surface :</span>
                                <strong class="ml-2">{{ $submission->surface }} m²</strong>
                            </div>
                            <div>
                                <span class="text-gray-600">Téléphone :</span>
                                <strong class="ml-2">{{ $submission->phone }}</strong>
                            </div>
                            <div>
                                <span class="text-gray-600">Email :</span>
                                <strong class="ml-2">{{ $submission->email }}</strong>
                            </div>
                            <div>
                                <span class="text-gray-600">Localisation :</span>
                                <strong class="ml-2">{{ $submission->postal_code }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="text-center space-y-4">
                        <a href="{{ url('/') }}" 
                           class="inline-block bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-home mr-2"></i>
                            Retour à l'accueil
                        </a>
                        
                        <button onclick="window.print()" 
                                class="inline-block bg-gray-600 text-white px-8 py-3 rounded-lg hover:bg-gray-700 transition ml-4">
                            <i class="fas fa-print mr-2"></i>
                            Imprimer
                        </button>
                    </div>
                </div>
            </div>

            <!-- Company Info -->
            <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                @if(setting('company_logo'))
                    <img src="{{ asset(setting('company_logo')) }}" alt="{{ company('name') }}" class="h-16 mx-auto mb-4">
                @endif
                <h4 class="text-xl font-bold text-gray-800 mb-2">{{ company('name', 'Rénovation Expert') }}</h4>
                <p class="text-gray-600 mb-4">{{ company('description', 'Votre partenaire de confiance pour tous vos travaux de rénovation') }}</p>
                <div class="flex justify-center space-x-6 text-gray-600">
                    @if(company('phone'))
                    <div>
                        <i class="fas fa-phone mr-2"></i>
                        {{ company('phone') }}
                    </div>
                    @endif
                    @if(company('email'))
                    <div>
                        <i class="fas fa-envelope mr-2"></i>
                        {{ company('email') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-scroll to top
window.scrollTo(0, 0);

// Track completion with Google Analytics
if (typeof gtag !== 'undefined') {
    gtag('event', 'form_completion', {
        'event_category': 'engagement',
        'event_label': 'simulator_completion'
    });
}

// Google Ads Conversion Tracking
@if(setting('google_ads_conversion_id') && setting('google_ads_conversion_label'))
(function() {
    var script = document.createElement('script');
    script.async = true;
    script.src = 'https://www.googletagmanager.com/gtag/js?id={{ setting('google_ads_conversion_id') }}';
    document.head.appendChild(script);
    
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '{{ setting('google_ads_conversion_id') }}');
    
    gtag('event', 'conversion', {
        'send_to': '{{ setting('google_ads_conversion_id') }}/{{ setting('google_ads_conversion_label') }}',
        'transaction_id': '{{ $submission->id ?? uniqid() }}'
    });
    
    console.log('Google Ads Conversion tracked!');
})();
@endif

console.log('✅ Page de succès chargée - Formulaire terminé !');
</script>

<style>
@media print {
    .no-print {
        display: none !important;
    }
}
</style>
@endsection







