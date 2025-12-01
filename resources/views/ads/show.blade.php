@extends('layouts.app')

@php
    // S'assurer que les métadonnées sont disponibles pour le layout
    // Le layout utilise $pageTitle, $pageDescription, etc. en priorité
    // Ces variables sont passées depuis AdPublicController::show()
@endphp

@section('title', $pageTitle ?? 'Service professionnel')

@section('description', $pageDescription ?? 'Service professionnel de qualité. Devis gratuit et intervention rapide.')

@section('keywords', !empty($pageKeywords) ? $pageKeywords . (!empty($extendedKeywords) ? ', ' . implode(', ', $extendedKeywords) : '') : (!empty($extendedKeywords) ? implode(', ', $extendedKeywords) : ''))

@push('head')
<style>
    /* Variables de couleurs de branding */
    :root {
        --primary-color: {{ setting('primary_color', '#3b82f6') }};
        --secondary-color: {{ setting('secondary_color', '#1e40af') }};
        --accent-color: {{ setting('accent_color', '#f59e0b') }};
    }
    
    /* Empêcher le scroll horizontal sur mobile */
    html, body {
        overflow-x: hidden;
        max-width: 100%;
        word-wrap: break-word;
        overflow-wrap: break-word;
        hyphens: none;
    }
    
    /* Assurer que tous les conteneurs respectent la largeur */
    .container, [class*="max-w"] {
        max-width: 100%;
        overflow-x: hidden;
    }
    
    /* Contenus HTML dans les annonces - forcer les retours à la ligne */
    .bg-white.rounded-2xl {
        overflow-x: hidden;
        word-wrap: break-word;
        overflow-wrap: break-word;
        hyphens: none;
    }
    
    /* Forcer les retours à la ligne pour tous les éléments de texte */
    .bg-white.rounded-2xl p,
    .bg-white.rounded-2xl div,
    .bg-white.rounded-2xl span,
    .bg-white.rounded-2xl li,
    .bg-white.rounded-2xl td,
    .bg-white.rounded-2xl th,
    .bg-white.rounded-2xl a,
    .bg-white.rounded-2xl h1,
    .bg-white.rounded-2xl h2,
    .bg-white.rounded-2xl h3,
    .bg-white.rounded-2xl h4,
    .bg-white.rounded-2xl h5,
    .bg-white.rounded-2xl h6 {
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        word-break: break-word !important;
        hyphens: none !important;
        max-width: 100%;
        overflow-x: hidden;
    }
    
    /* URLs et mots longs */
    .bg-white.rounded-2xl a {
        word-break: break-all;
        overflow-wrap: anywhere;
    }
    
    /* Images et médias */
    .bg-white.rounded-2xl img,
    .bg-white.rounded-2xl iframe,
    .bg-white.rounded-2xl video,
    .bg-white.rounded-2xl embed,
    .bg-white.rounded-2xl object {
        max-width: 100% !important;
        height: auto;
        display: block;
    }
    
    /* Tableaux - forcer les retours à la ligne au lieu du scroll */
    .bg-white.rounded-2xl table {
        width: 100% !important;
        max-width: 100% !important;
        table-layout: fixed;
        word-wrap: break-word;
        overflow-wrap: break-word;
        border-collapse: collapse;
    }
    
    .bg-white.rounded-2xl td,
    .bg-white.rounded-2xl th {
        word-wrap: break-word;
        overflow-wrap: break-word;
        word-break: break-word;
        overflow-x: hidden;
        max-width: 0;
    }
    
    /* Code et pre - retour à la ligne */
    .bg-white.rounded-2xl pre,
    .bg-white.rounded-2xl code {
        max-width: 100%;
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        white-space: pre-wrap !important;
        overflow-x: hidden;
    }
    
    /* Listes */
    .bg-white.rounded-2xl ul,
    .bg-white.rounded-2xl ol {
        overflow-x: hidden;
        word-wrap: break-word;
    }
    
    /* Assurer que tous les éléments enfants respectent la largeur */
    .bg-white.rounded-2xl * {
        max-width: 100%;
        box-sizing: border-box;
    }
    
    /* Sections avec overflow */
    section {
        overflow-x: hidden;
        width: 100%;
        word-wrap: break-word;
        hyphens: none;
    }
    
    /* Conteneur de contenu avec overflow hidden */
    .prose {
        overflow-x: hidden;
        word-wrap: break-word;
        overflow-wrap: break-word;
        hyphens: none;
    }
    
    .prose * {
        max-width: 100%;
        word-wrap: break-word;
        overflow-wrap: break-word;
        hyphens: none;
    }
    
    /* Padding responsive pour mobile */
    @media (max-width: 640px) {
        .container {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        .bg-white.rounded-2xl {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 overflow-x-hidden">
    <!-- Hero Section -->
    <section class="relative py-20 text-white overflow-hidden">
        @if(!empty($featuredImage))
        @php
            // Nettoyer le chemin de l'image (enlever le préfixe uploads/ si déjà présent dans asset())
            $imagePath = str_starts_with($featuredImage, 'http') ? $featuredImage : asset($featuredImage);
        @endphp
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" 
             style="background-image: url('{{ $imagePath }}'); filter: blur(2px); transform: scale(1.05);"></div>
        <div class="absolute inset-0 bg-black bg-opacity-60"></div>
        @else
        <div class="absolute inset-0" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);"></div>
        @endif
        
        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-6">
                    <i class="fas fa-tools mr-4"></i>
                    {{ $ad->title ?? 'Service professionnel' }}
                </h1>
                <p class="text-xl md:text-2xl mb-8 leading-relaxed">
                    Service professionnel à {{ $cityModel->name ?? 'votre ville' }} - Devis gratuit et intervention rapide
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('form.step', 'propertyType') }}" 
                       class="text-white font-bold py-4 px-8 rounded-lg text-lg transition-colors shadow-lg"
                       style="background-color: var(--accent-color);"
                       onmouseover="this.style.backgroundColor='var(--secondary-color)';"
                       onmouseout="this.style.backgroundColor='var(--accent-color)';">
                        <i class="fas fa-calculator mr-2"></i>
                        Simulateur de devis
                    </a>
                    <a href="tel:{{ setting('company_phone_raw') }}" 
                       class="text-white font-bold py-4 px-8 rounded-lg text-lg transition-colors shadow-lg"
                       style="background-color: var(--primary-color);"
                       onmouseover="this.style.backgroundColor='var(--secondary-color)';"
                       onmouseout="this.style.backgroundColor='var(--primary-color)';"
                       onclick="console.log('📞 Clic sur bouton appel (ads/show)'); if(typeof window.trackPhoneCall === 'function') { console.log('📞 Appel de trackPhoneCall'); window.trackPhoneCall('{{ setting('company_phone_raw') }}', 'ads/{{ $ad->slug ?? 'unknown' }}'); } else { console.error('❌ trackPhoneCall non disponible', typeof window.trackPhoneCall); } return true;"
                        <i class="fas fa-phone mr-2"></i>
                        {{ setting('company_phone') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Contenu de l'annonce -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="bg-white rounded-2xl shadow-lg p-4 md:p-8 lg:p-12 overflow-hidden relative">
                    <div class="prose prose-sm md:prose-base max-w-none" style="word-wrap: break-word; overflow-wrap: break-word; overflow-x: hidden; word-break: break-word;">
                        {!! $ad->content_html ?? '<p>Contenu en cours de chargement...</p>' !!}
                    </div>
                    
                    @if(!empty($extendedKeywords))
                    <!-- Mots-clés étendus invisibles mais visibles pour Google -->
                    <div style="position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden;" aria-hidden="true">
                        {{ implode(', ', $extendedKeywords) }}
                    </div>
                    @endif
                </div>

                <div class="mt-12 rounded-2xl p-8 text-white text-center" style="background: linear-gradient(90deg, var(--primary-color) 0%, var(--secondary-color) 100%);">
                    <h3 class="text-2xl font-bold mb-4">Prêt à Démarrer Votre Projet à {{ $cityModel->name ?? 'votre ville' }} ?</h3>
                    <p class="text-lg mb-6">Contactez-nous dès aujourd'hui pour un devis gratuit et personnalisé</p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ route('form.step', 'propertyType') }}" 
                           class="text-white font-bold py-4 px-8 rounded-lg text-lg transition-colors shadow-lg"
                           style="background-color: var(--accent-color);"
                           onmouseover="this.style.backgroundColor='var(--secondary-color)';"
                           onmouseout="this.style.backgroundColor='var(--accent-color)';">
                            <i class="fas fa-calculator mr-2"></i>
                            Simulateur de devis
                        </a>
                        <a href="tel:{{ setting('company_phone_raw') }}" 
                           class="text-white font-bold py-4 px-8 rounded-lg text-lg transition-colors shadow-lg"
                           style="background-color: var(--primary-color);"
                           onmouseover="this.style.backgroundColor='var(--secondary-color)';"
                           onmouseout="this.style.backgroundColor='var(--primary-color)';"
                           onclick="console.log('📞 Clic sur bouton appel (ads/show)'); if(typeof window.trackPhoneCall === 'function') { console.log('📞 Appel de trackPhoneCall'); window.trackPhoneCall('{{ setting('company_phone_raw') }}', 'ads/{{ $ad->slug ?? 'unknown' }}'); } else { console.error('❌ trackPhoneCall non disponible', typeof window.trackPhoneCall); } return true;"
                            <i class="fas fa-phone mr-2"></i>
                            Appeler Maintenant
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Nos Réalisations -->
    @if(!empty($portfolioItems) && count($portfolioItems) > 0)
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Nos Réalisations</h2>
                    <p class="text-lg text-gray-600">Découvrez quelques-unes de nos réalisations récentes</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    @foreach(array_slice($portfolioItems, 0, 4) as $portfolioItem)
                    @if(is_array($portfolioItem) && !empty($portfolioItem['images']) && is_array($portfolioItem['images']))
                    @php
                        // Générer le slug pour la page de détails
                        $itemTitle = $portfolioItem['title'] ?? 'Réalisation';
                        $itemSlug = !empty($portfolioItem['slug']) ? $portfolioItem['slug'] : Str::slug($itemTitle);
                    @endphp
                    <a href="{{ route('portfolio.show', $itemSlug) }}" class="block">
                        <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 cursor-pointer">
                            <div class="relative">
                                <img src="{{ asset($portfolioItem['images'][0]) }}" 
                                     alt="{{ ($mainKeyword ?? '') . ' ' . ($portfolioItem['title'] ?? 'Réalisation') . ($cityModel->postal_code ? ' ' . $cityModel->postal_code : '') }}" 
                                     class="w-full h-64 object-cover">
                                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
                                    <div class="opacity-0 hover:opacity-100 transition-opacity duration-300">
                                        <i class="fas fa-search-plus text-white text-2xl"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="p-6">
                                <h3 class="text-xl font-bold text-gray-900 mb-2">
                                    {{ $portfolioItem['title'] ?? 'Réalisation' }}
                                </h3>
                                @if(!empty($portfolioItem['description']))
                                <p class="text-gray-600 text-sm mb-4">
                                    {{ Str::limit($portfolioItem['description'], 100) }}
                                </p>
                                @endif
                                <div class="flex items-center justify-between">
                                    @if(!empty($portfolioItem['work_type']))
                                    <span class="px-3 py-1 rounded-full text-xs font-medium"
                                          style="background-color: rgba(var(--primary-color-rgb, 59, 130, 246), 0.1); color: var(--primary-color);">
                                        @switch($portfolioItem['work_type'])
                                            @case('roof')
                                                <i class="fas fa-home mr-1"></i>Toiture
                                                @break
                                            @case('facade')
                                                <i class="fas fa-building mr-1"></i>Façade
                                                @break
                                            @case('isolation')
                                                <i class="fas fa-thermometer-half mr-1"></i>Isolation
                                                @break
                                            @default
                                                <i class="fas fa-tools mr-1"></i>Mixte
                                        @endswitch
                                    </span>
                                    @endif
                                    @if(count($portfolioItem['images']) > 1)
                                    <span class="text-gray-500 text-sm">
                                        <i class="fas fa-images mr-1"></i>{{ count($portfolioItem['images']) }} photos
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                    @endif
                    @endforeach
                </div>
                
                @if(count($portfolioItems) > 4)
                <div class="text-center mt-8">
                    <a href="{{ route('portfolio.index') }}" 
                       class="text-white font-bold py-3 px-8 rounded-lg transition-colors"
                       style="background-color: var(--primary-color);"
                       onmouseover="this.style.backgroundColor='var(--secondary-color)';"
                       onmouseout="this.style.backgroundColor='var(--primary-color)';">
                        <i class="fas fa-images mr-2"></i>
                        Voir Toutes nos Réalisations
                    </a>
                </div>
                @endif
            </div>
        </div>
    </section>
    @endif

    <!-- Section Annonces Similaires -->
    @if(isset($relatedAds) && $relatedAds->count() > 0)
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Autres Services à {{ $cityModel->name ?? 'votre ville' }}</h2>
                    <p class="text-lg text-gray-600">Découvrez nos autres services disponibles dans votre ville</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($relatedAds as $relatedAd)
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $relatedAd->title }}</h3>
                            <p class="text-gray-600 text-sm mb-4">{{ Str::limit($relatedAd->meta_description, 100) }}</p>
                            <a href="{{ route('ads.show', $relatedAd->slug) }}" 
                               class="inline-block text-white font-semibold px-4 py-2 rounded-lg transition-colors"
                               style="background-color: var(--primary-color);"
                               onmouseover="this.style.backgroundColor='var(--secondary-color)';"
                               onmouseout="this.style.backgroundColor='var(--primary-color)';">
                                Voir le service
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- Section Avis Clients -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Avis de Nos Clients</h2>
                    <p class="text-lg text-gray-600">Ce que disent nos clients sur nos services</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @php
                        $reviews = \App\Models\Review::where('is_active', true)->take(3)->get();
                    @endphp
                    
                    @if($reviews->count() > 0)
                    @foreach($reviews as $review)
                    <div class="bg-gray-50 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 rounded-full overflow-hidden mr-4">
                                @if($review->author_photo_url)
                                <img src="{{ $review->author_photo_url }}" alt="{{ $review->author_name }}" class="w-full h-full object-cover">
                                @else
                                <div class="w-full h-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg">
                                    {{ $review->author_initials }}
                                </div>
                                @endif
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900">{{ $review->author_name }}</h4>
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star text-yellow-400 {{ $i <= $review->rating ? '' : 'opacity-30' }}"></i>
                                    @endfor
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-gray-700 mb-4">
                            @if($review->review_text)
                                <p>{{ Str::limit($review->review_text, 150) }}</p>
                            @else
                                <p class="text-gray-500 italic">Avis sans contenu détaillé</p>
                            @endif
                        </div>
                        
                        <div class="flex items-center justify-between text-sm text-gray-500">
                            <span>{{ $review->review_date ? \Carbon\Carbon::parse($review->review_date)->format('d/m/Y') : '' }}</span>
                            @if($review->source && $review->source !== 'manual')
                            <span class="px-2 py-1 rounded-full text-xs"
                                  style="background-color: rgba(var(--primary-color-rgb, 59, 130, 246), 0.1); color: var(--primary-color);">
                                @if(str_contains($review->source, 'Google'))
                                    <i class="fab fa-google mr-1"></i>Google Maps
                                @elseif(str_contains($review->source, 'Travaux'))
                                    <i class="fas fa-tools mr-1"></i>Travaux.com
                                @elseif(str_contains($review->source, 'LeBonCoin'))
                                    <i class="fas fa-shopping-cart mr-1"></i>LeBonCoin
                                @elseif(str_contains($review->source, 'Trustpilot'))
                                    <i class="fas fa-shield-alt mr-1"></i>Trustpilot
                                @elseif(str_contains($review->source, 'Facebook'))
                                    <i class="fab fa-facebook mr-1"></i>Facebook
                                @else
                                    <i class="fas fa-star mr-1"></i>{{ ucfirst($review->source) }}
                                @endif
                            </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="col-span-full text-center py-8">
                        <p class="text-gray-500">Aucun avis disponible pour le moment.</p>
                    </div>
                    @endif
                </div>
                
                <div class="text-center mt-8">
                    <a href="{{ route('reviews.all') }}" 
                       class="text-white font-bold py-3 px-8 rounded-lg transition-colors"
                       style="background-color: var(--primary-color);"
                       onmouseover="this.style.backgroundColor='var(--secondary-color)';"
                       onmouseout="this.style.backgroundColor='var(--primary-color)';">
                        Voir Tous les Avis
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>

@push('head')
<!-- Schema.org Structured Data pour SEO -->
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'serviceType' => $mainKeyword ?? 'Service',
    'provider' => [
        '@type' => 'LocalBusiness',
        'name' => setting('company_name', 'Votre Entreprise'),
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => $cityModel->name ?? '',
            'postalCode' => $cityModel->postal_code ?? '',
            'addressCountry' => 'FR'
        ],
        'telephone' => setting('company_phone_raw', ''),
        'url' => url('/')
    ],
    'areaServed' => [
        '@type' => 'City',
        'name' => $cityModel->name ?? '',
        'postalCode' => $cityModel->postal_code ?? ''
    ],
    'description' => strip_tags($pageDescription ?? ''),
    'url' => url()->current()
] + (!empty($extendedKeywords) ? ['keywords' => implode(', ', array_slice($extendedKeywords, 0, 10))] : []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@push('scripts')
<script>
// Script de secours pour le tracking des appels si phone-tracking.js n'est pas encore chargé
(function() {
    // S'assurer que la fonction trackPhoneCall est disponible même si le script externe n'est pas chargé
    if (typeof window.trackPhoneCall === 'undefined') {
        window.trackPhoneCall = function(phoneNumber, sourcePage) {
            console.log('📞 trackPhoneCall (fallback) appelé', { phoneNumber, sourcePage });
            
            // Essayer d'envoyer la requête directement
            const payload = {
                phone_number: phoneNumber || '{{ setting('company_phone_raw') }}',
                source_page: sourcePage || window.location.pathname,
                referrer_url: document.referrer || window.location.href
            };
            
            // Utiliser fetch avec keepalive pour maximiser les chances de succès
            fetch('/api/track-phone-call', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(payload),
                keepalive: true
            }).catch(function(err) {
                console.error('Erreur tracking (fallback):', err);
            });
        };
    }
})();

// Logger pour debug
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Page ads/show chargée');
    console.log('trackPhoneCall disponible:', typeof window.trackPhoneCall);
    
    // Vérifier que tous les liens tel: sont bien détectés
    const telLinks = document.querySelectorAll('a[href^="tel:"]');
    console.log('🔗 Liens tel: trouvés:', telLinks.length);
    telLinks.forEach(function(link, index) {
        console.log('  - Lien', index + 1, ':', link.href, link.onclick ? '(avec onclick)' : '(sans onclick)');
    });
});
</script>
@endpush
@endsection