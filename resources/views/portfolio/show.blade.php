@extends('layouts.app')

@php
    // Les variables SEO sont déjà passées par le contrôleur
    // Le layout app.blade.php les gère automatiquement
@endphp

@push('head')

<style>
    /* Styles spécifiques pour mobile */
    @media (max-width: 768px) {
        /* Images responsive */
        .mobile-responsive-img {
            max-width: 100%;
            height: auto;
            display: block;
            object-fit: cover;
        }
        
        /* Hero section mobile */
        .hero-mobile {
            min-height: 100vh;
            background-attachment: scroll !important;
            background-size: cover !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
        }
        
        /* Galerie photo mobile - réduction des marges internes */
        .gallery-mobile {
            padding: 1rem !important;
        }
        
        .gallery-mobile h2 {
            font-size: 1.5rem !important;
            margin-bottom: 1rem !important;
        }
        
        .gallery-mobile .grid {
            gap: 0.75rem !important;
        }
        
        .gallery-mobile img {
            height: 200px !important;
        }
        
        /* Contenu principal mobile */
        .content-mobile {
            padding: 1rem !important;
        }
        
        .content-mobile h2 {
            font-size: 1.5rem !important;
            margin-bottom: 1rem !important;
        }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-white">
    <!-- Hero Section avec image principale en plein écran -->
    <section class="relative h-screen overflow-hidden">
        @if(!empty($portfolioItem['images']))
            @php 
                $firstImage = is_array($portfolioItem['images']) ? $portfolioItem['images'][0] : $portfolioItem['images'];
            @endphp
            <img src="{{ url($firstImage) }}" 
                 alt="{{ $portfolioItem['title'] }}" 
                 class="w-full h-full object-cover mobile-responsive-img cursor-pointer"
                 style="max-width: 100%; height: auto; display: block;"
                 loading="lazy"
                 onclick="openMobileSlider(0)">
        @else
            <div class="w-full h-full bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center">
                <i class="fas fa-image text-white text-8xl"></i>
            </div>
        @endif
        
        <!-- Overlay sombre avec dégradé -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
        
        <!-- Contenu du hero -->
        <div class="absolute inset-0 flex items-end">
            <div class="container mx-auto px-6 pb-20">
                <div class="max-w-4xl">
                    <h1 class="text-7xl font-bold text-white mb-6 drop-shadow-2xl">{{ $portfolioItem['title'] }}</h1>
                    @if(!empty($portfolioItem['service_type']))
                    <div class="inline-flex items-center bg-white/20 backdrop-blur-sm text-white px-6 py-3 rounded-full mb-6 border border-white/30">
                        <i class="fas fa-tools mr-3"></i>
                        <span class="font-semibold text-lg">{{ $portfolioItem['service_type'] }}</span>
                    </div>
                    @endif
                    @if(!empty($portfolioItem['location']))
                    <div class="flex items-center text-white/90 text-xl">
                        <i class="fas fa-map-marker-alt mr-3"></i>
                        <span>{{ $portfolioItem['location'] }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Bouton retour -->
        <div class="absolute top-6 left-6">
            <a href="{{ route('portfolio.index') }}" 
               class="bg-white/20 backdrop-blur-sm text-white px-6 py-3 rounded-full hover:bg-white/30 transition-all duration-300 border border-white/30">
                <i class="fas fa-arrow-left mr-2"></i>
                Retour aux réalisations
            </a>
        </div>
    </section>

    <!-- Contenu principal -->
    <section class="py-20">
        <div class="container mx-auto px-6">
            <div class="grid lg:grid-cols-3 gap-16">
                <!-- Contenu principal -->
                <div class="lg:col-span-2">
                    <!-- Description -->
                    @if(!empty($portfolioItem['description']))
                    <div class="bg-white rounded-3xl p-10 shadow-2xl mb-12 content-mobile">
                        <h2 class="text-4xl font-bold text-gray-800 mb-8">À propos de ce projet</h2>
                        <div class="prose prose-xl text-gray-600 leading-relaxed">
                            {!! nl2br(e($portfolioItem['description'])) !!}
                        </div>
                    </div>
                    @endif

                    <!-- Image unique mise en valeur -->
                    @if(!empty($portfolioItem['images']) && (!is_array($portfolioItem['images']) || count($portfolioItem['images']) == 1))
                    <div class="bg-white rounded-3xl p-10 shadow-2xl mb-12 content-mobile">
                        <h2 class="text-4xl font-bold text-gray-800 mb-8">Réalisation</h2>
                        @php 
                            $singleImage = is_array($portfolioItem['images']) ? $portfolioItem['images'][0] : $portfolioItem['images'];
                        @endphp
                        <img src="{{ url($singleImage) }}" 
                             alt="{{ $portfolioItem['title'] }}" 
                             class="w-full h-[800px] object-cover rounded-2xl shadow-2xl mobile-responsive-img cursor-pointer"
                             style="max-width: 100%; height: auto; display: block;"
                             loading="lazy"
                             onclick="openMobileSlider(0)">
                    </div>
                    @endif

                    <!-- Galerie de photos (plusieurs images) -->
                    @if(!empty($portfolioItem['images']) && is_array($portfolioItem['images']) && count($portfolioItem['images']) > 1)
                    <div class="bg-white rounded-3xl p-10 shadow-2xl gallery-mobile">
                        <h2 class="text-4xl font-bold text-gray-800 mb-8">Galerie photos</h2>
                        
                        <!-- Grid responsive (mobile et desktop) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-8" id="photo-gallery">
                            @foreach($portfolioItem['images'] as $index => $image)
                            <div class="relative">
                                <img src="{{ asset($image) }}" 
                                     alt="{{ $portfolioItem['title'] }} - Photo {{ $index + 1 }}" 
                                     class="w-full h-64 sm:h-96 object-cover rounded-2xl shadow-xl cursor-pointer hover:shadow-2xl transition-shadow"
                                     onclick="openMobileSlider({{ $index }})">
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <!-- CTA -->
                    <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-3xl p-8 text-white text-center shadow-2xl sticky top-8">
                        <h3 class="text-2xl font-bold mb-4 text-white">Vous aimez ce projet ?</h3>
                        <p class="text-white/90 mb-8 text-lg">Contactez-nous pour discuter de votre projet similaire</p>
                        
                        <div class="space-y-4">
                            <a href="tel:{{ setting('company_phone') }}" 
                               class="block w-full bg-yellow-500 text-white px-8 py-4 rounded-2xl font-bold hover:bg-yellow-600 transition-colors shadow-xl text-lg">
                                <i class="fas fa-phone mr-3"></i>
                                {{ setting('company_phone') }}
                            </a>
                            <a href="{{ route('form.step', 'propertyType') }}" 
                               class="block w-full bg-green-500 text-white px-8 py-4 rounded-2xl font-bold hover:bg-green-600 transition-colors shadow-xl text-lg">
                                <i class="fas fa-calculator mr-3"></i>
                                Demander un devis
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Autres projets -->
    @if($otherItems->count() > 0)
    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-5xl font-bold text-gray-800 mb-6">Autres réalisations</h2>
                <p class="text-gray-600 text-xl">Découvrez d'autres projets qui pourraient vous intéresser</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                @foreach($otherItems as $item)
                <div class="bg-white rounded-3xl shadow-2xl overflow-hidden hover:shadow-3xl transition-all duration-500 transform hover:-translate-y-4">
                    <!-- Image -->
                    <div class="relative h-64 overflow-hidden">
                        @if(!empty($item['images']))
                            @php 
                                $firstImage = is_array($item['images']) ? $item['images'][0] : $item['images'];
                            @endphp
                            <img src="{{ asset($firstImage) }}" 
                                 alt="{{ $item['title'] }}" 
                                 class="w-full h-full object-cover transition-transform duration-500 hover:scale-110">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center">
                                <i class="fas fa-image text-white text-5xl"></i>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Contenu -->
                    <div class="p-8">
                        <h3 class="text-2xl font-bold text-gray-800 mb-4">{{ $item['title'] }}</h3>
                        @if(!empty($item['description']))
                        <p class="text-gray-600 mb-6 text-lg">{{ Str::limit($item['description'], 120) }}</p>
                        @endif
                        <a href="{{ route('portfolio.show', \Illuminate\Support\Str::slug($item['title'])) }}" 
                           class="inline-flex items-center text-blue-600 font-bold hover:text-blue-800 transition-colors text-lg">
                            Voir le projet <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            
            <div class="text-center mt-16">
                <a href="{{ route('portfolio.index') }}" 
                   class="inline-flex items-center bg-blue-600 text-white px-10 py-5 rounded-2xl font-bold hover:bg-blue-700 transition-colors shadow-2xl hover:shadow-3xl text-xl">
                    <i class="fas fa-images mr-3"></i>
                    Voir toutes nos réalisations
                </a>
            </div>
        </div>
    </section>
    @endif
</div>

<!-- Modal plein écran pour le slider mobile -->
<div id="mobile-slider-modal" class="fixed inset-0 bg-black z-50 hidden">
    <div class="relative w-full h-full flex items-center justify-center">
        <!-- Bouton fermer -->
        <button onclick="closeMobileSlider()" class="absolute top-4 right-4 z-10 bg-black/50 text-white p-3 rounded-full hover:bg-black/70 transition-colors">
            <i class="fas fa-times text-xl"></i>
        </button>
        
        <!-- Slider plein écran -->
        <div class="relative w-full h-full">
            <div class="overflow-hidden h-full">
                <div class="flex h-full transition-transform duration-300 ease-in-out" id="fullscreen-slider">
                    @if(!empty($portfolioItem['images']) && is_array($portfolioItem['images']))
                        @foreach($portfolioItem['images'] as $index => $image)
                        <div class="w-full h-full flex-shrink-0 flex items-center justify-center">
                            <img src="{{ asset($image) }}" 
                                 alt="{{ $portfolioItem['title'] }} - Photo {{ $index + 1 }}" 
                                 class="max-w-full max-h-full object-contain">
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
            
            <!-- Navigation plein écran -->
            @if(!empty($portfolioItem['images']) && is_array($portfolioItem['images']) && count($portfolioItem['images']) > 1)
            <button onclick="prevFullscreenSlide()" class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-black/50 text-white p-4 rounded-full hover:bg-black/70 transition-colors">
                <i class="fas fa-chevron-left text-xl"></i>
            </button>
            <button onclick="nextFullscreenSlide()" class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-black/50 text-white p-4 rounded-full hover:bg-black/70 transition-colors">
                <i class="fas fa-chevron-right text-xl"></i>
            </button>
            
            <!-- Compteur d'images -->
            <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black/50 text-white px-4 py-2 rounded-full">
                <span id="image-counter">1</span> / {{ count($portfolioItem['images']) }}
            </div>
            @endif
        </div>
    </div>
</div>

<script>
let currentFullscreenSlide = 0;
const totalSlides = {{ !empty($portfolioItem['images']) && is_array($portfolioItem['images']) ? count($portfolioItem['images']) : 0 }};

// Modal plein écran
function openMobileSlider(index = 0) {
    currentFullscreenSlide = index;
    document.getElementById('mobile-slider-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    updateFullscreenSlider();
    updateImageCounter();
}

function closeMobileSlider() {
    document.getElementById('mobile-slider-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function nextFullscreenSlide() {
    if (currentFullscreenSlide < totalSlides - 1) {
        currentFullscreenSlide++;
    } else {
        currentFullscreenSlide = 0;
    }
    updateFullscreenSlider();
    updateImageCounter();
}

function prevFullscreenSlide() {
    if (currentFullscreenSlide > 0) {
        currentFullscreenSlide--;
    } else {
        currentFullscreenSlide = totalSlides - 1;
    }
    updateFullscreenSlider();
    updateImageCounter();
}

function updateFullscreenSlider() {
    const slider = document.getElementById('fullscreen-slider');
    if (slider) {
        slider.style.transform = `translateX(-${currentFullscreenSlide * 100}%)`;
    }
}

function updateImageCounter() {
    const counter = document.getElementById('image-counter');
    if (counter) {
        counter.textContent = currentFullscreenSlide + 1;
    }
}

// Navigation au clavier
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('mobile-slider-modal');
    if (!modal.classList.contains('hidden')) {
        if (e.key === 'Escape') {
            closeMobileSlider();
        } else if (e.key === 'ArrowLeft') {
            prevFullscreenSlide();
        } else if (e.key === 'ArrowRight') {
            nextFullscreenSlide();
        }
    }
});

// Swipe gestures pour mobile (modal plein écran uniquement)
let startX = 0;
let startY = 0;

document.addEventListener('touchstart', function(e) {
    startX = e.touches[0].clientX;
    startY = e.touches[0].clientY;
});

document.addEventListener('touchend', function(e) {
    if (!startX || !startY) return;
    
    const endX = e.changedTouches[0].clientX;
    const endY = e.changedTouches[0].clientY;
    
    const diffX = startX - endX;
    const diffY = startY - endY;
    
    // Vérifier si c'est un swipe horizontal (pas vertical)
    if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
        const modal = document.getElementById('mobile-slider-modal');
        if (!modal.classList.contains('hidden')) {
            if (diffX > 0) {
                nextFullscreenSlide();
            } else {
                prevFullscreenSlide();
            }
        }
    }
    
    startX = 0;
    startY = 0;
});
</script>

@endsection





