@extends('layouts.app')

@section('title', setting('company_name', 'Votre Entreprise') . ' - ' . setting('company_specialization', 'Travaux de Rénovation'))
@section('description', setting('company_description', 'Expert en travaux de rénovation'))

@push('head')
<style>
    :root {
        --primary-color: {{ setting('primary_color', '#3b82f6') }};
        --secondary-color: {{ setting('secondary_color', '#1e40af') }};
        --accent-color: {{ setting('accent_color', '#f59e0b') }};
    }
    
    .bg-primary { background-color: var(--primary-color); }
    .text-primary { color: var(--primary-color); }
    .border-primary { border-color: var(--primary-color); }
    .bg-secondary { background-color: var(--secondary-color); }
    .text-secondary { color: var(--secondary-color); }
    .bg-accent { background-color: var(--accent-color); }
    .text-accent { color: var(--accent-color); }
    
    .timeline-item {
        position: relative;
        padding-left: 3rem;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 2px;
        height: 100%;
        background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
    }
    
    .timeline-item::after {
        content: '';
        position: absolute;
        left: -8px;
        top: 1rem;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: var(--primary-color);
        border: 3px solid white;
        box-shadow: 0 0 0 3px var(--primary-color);
    }
    
    .service-card {
        transition: all 0.3s ease;
    }
    
    .service-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }
    
    /* Animation pour les partenaires */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-fadeIn {
        animation: fadeIn 0.3s ease-in-out;
    }
    
    /* Styles pour les logos partenaires */
    .partner-logo-container {
        transition: all 0.3s ease;
    }
    
    .partner-logo-container:hover {
        transform: scale(1.05);
    }
    
    /* Styles spécifiques pour mobile */
    @media (max-width: 768px) {
        /* Hero section mobile */
        .hero-mobile {
            min-height: 100vh !important;
            background-attachment: scroll !important;
            background-size: cover !important;
            background-position: center center !important;
            background-repeat: no-repeat !important;
            background-color: var(--secondary-color) !important;
        }
        
        /* Force background image display on mobile */
        .hero-mobile[style*="background-image"] {
            background-image: var(--hero-bg) !important;
            background-size: cover !important;
            background-position: center center !important;
            background-repeat: no-repeat !important;
            background-attachment: scroll !important;
        }
        
        /* Images responsive */
        .mobile-responsive-img {
            max-width: 100% !important;
            width: 100% !important;
            height: auto !important;
            display: block !important;
            object-fit: cover !important;
        }
        
        /* About section mobile */
        .about-image-mobile {
            width: 100% !important;
            height: auto !important;
            max-height: 400px !important;
            object-fit: cover !important;
            display: block !important;
        }
        
        /* Portfolio images mobile */
        .portfolio-image-mobile {
            width: 100% !important;
            height: 200px !important;
            object-fit: cover !important;
            display: block !important;
        }
        
        /* Service images mobile */
        .service-image-mobile {
            width: 100% !important;
            height: 200px !important;
            background-size: cover !important;
            background-position: center center !important;
            background-repeat: no-repeat !important;
            display: block !important;
        }
        
        /* Force service images to show on mobile */
        .service-image-mobile img {
            display: block !important;
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
        }
        
        /* Force image display */
        img {
            max-width: 100% !important;
            height: auto !important;
            display: block !important;
        }
    }
</style>
@endpush

@section('content')

    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center justify-center overflow-hidden hero-mobile pt-16 pb-16" 
             @if($homeConfig['hero']['background_image'] ?? null)
             style="background-color: var(--secondary-color); background-image: url('{{ asset($homeConfig['hero']['background_image']) }}'); background-attachment: scroll; background-size: cover; background-position: center; background-repeat: no-repeat; --hero-bg: url('{{ asset($homeConfig['hero']['background_image']) }}');"
             @else
             style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));"
             @endif>
        <!-- Overlay sombre pour améliorer la lisibilité du texte -->
        @if($homeConfig['hero']['background_image'] ?? null)
        <div class="absolute inset-0 bg-black/40 z-0"></div>
        @endif
        <div class="container mx-auto px-4 text-center text-white relative z-10 pt-8">
            <!-- Trust Badges -->
            @if(($homeConfig['trust_badges']['garantie_decennale'] ?? false) || ($homeConfig['trust_badges']['certifie_rge'] ?? false) || ($homeConfig['trust_badges']['show_rating'] ?? false))
            <div class="flex justify-center items-center gap-6 mb-8 flex-wrap px-4">
                @if($homeConfig['trust_badges']['garantie_decennale'] ?? false)
                <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full">
                    <i class="fas fa-shield-alt text-yellow-400"></i>
                    <span class="text-sm font-medium">Garantie Décennale</span>
                </div>
                @endif
                
                @if($homeConfig['trust_badges']['certifie_rge'] ?? false)
                <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full">
                    <i class="fas fa-certificate text-green-400"></i>
                    <span class="text-sm font-medium">Certifié RGE</span>
                </div>
                @endif
                
                @if(($homeConfig['trust_badges']['show_rating'] ?? false) && $averageRating > 0)
                <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full">
                    <div class="flex text-yellow-400">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star {{ $i <= $averageRating ? '' : 'text-gray-300' }}"></i>
                        @endfor
                    </div>
                    <span class="text-sm font-medium">{{ number_format($averageRating, 1) }}/5 ({{ $totalReviews }} avis)</span>
                </div>
                @endif
            </div>
            @endif


            <h1 class="text-5xl md:text-7xl font-bold mb-6 leading-tight">
                {{ $homeConfig['hero']['title'] ?? setting('company_name', 'Votre Entreprise') }}
            </h1>
            
            <p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto leading-relaxed">
                {{ $homeConfig['hero']['subtitle'] ?? 'Expert en ' . setting('company_specialization', 'Travaux de Rénovation') }}
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center px-4 mb-8">
                <a href="{{ route('form.step', 'propertyType') }}" 
                   class="bg-primary text-white px-8 py-4 rounded-full text-lg font-semibold hover:bg-secondary transition-all duration-300 transform hover:scale-105 shadow-lg">
                    <i class="fas fa-calculator mr-2"></i>
                    {{ $homeConfig['hero']['cta_text'] ?? 'Demander un Devis Gratuit' }}
                </a>
                
                @if($homeConfig['hero']['show_phone'] ?? true)
                <a href="tel:{{ setting('company_phone') }}" 
                   class="bg-primary text-white px-8 py-4 rounded-full text-lg font-semibold hover:bg-secondary transition-all duration-300 transform hover:scale-105 shadow-lg">
                    <i class="fas fa-phone mr-2"></i>
                    {{ setting('company_phone') }}
                </a>
                @endif
            </div>
        </div>
        
        <!-- Scroll indicator -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 text-white animate-bounce">
            <i class="fas fa-chevron-down text-2xl"></i>
        </div>
    </section>

    <!-- Stats Section -->
    @if(!empty($homeConfig['stats']))
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                @foreach($homeConfig['stats'] as $stat)
                <div class="text-center">
                    <div class="w-20 h-20 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas {{ $stat['icon'] }} text-white text-2xl"></i>
                    </div>
                    <div class="text-4xl font-bold text-gray-900 mb-2">{{ $stat['value'] }}</div>
                    <div class="text-gray-600 font-medium">{{ $stat['label'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- About Section -->
    @if(($homeConfig['about']['enabled'] ?? false) && !empty($homeConfig['about']['content']))
    <section class="py-20 bg-gray-100">
        <div class="container mx-auto px-4">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Colonne 1: Texte justifié -->
                <div class="space-y-6">
                    <h2 class="text-4xl font-bold text-gray-800 mb-6">
                        {{ $homeConfig['about']['title'] ?? 'Qui Sommes-Nous ?' }}
                    </h2>
                    <div class="prose prose-lg text-gray-600 leading-relaxed text-justify">
                        {!! nl2br(e($homeConfig['about']['content'])) !!}
                    </div>
                    
                    <!-- Points forts -->
                    <div class="grid grid-cols-2 gap-4 mt-8">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-white text-sm"></i>
                            </div>
                            <span class="text-gray-700 font-medium">Expertise reconnue</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-white text-sm"></i>
                            </div>
                            <span class="text-gray-700 font-medium">Qualité garantie</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-white text-sm"></i>
                            </div>
                            <span class="text-gray-700 font-medium">Service client</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-white text-sm"></i>
                            </div>
                            <span class="text-gray-700 font-medium">Respect délais</span>
                        </div>
                    </div>
                </div>
                
                <!-- Colonne 2: Image configurable -->
                <div class="relative">
                    @if(!empty($homeConfig['about']['image']))
                        <div class="aspect-square rounded-2xl overflow-hidden shadow-2xl">
                            <img src="{{ asset($homeConfig['about']['image']) }}" 
                                 alt="{{ $homeConfig['about']['title'] ?? 'Qui Sommes-Nous' }}" 
                                 class="w-full h-full object-cover object-center mobile-responsive-img about-image-mobile"
                                 style="image-rendering: -webkit-optimize-contrast; image-rendering: crisp-edges; max-width: 100%; height: auto; display: block; width: 100%;"
                                 loading="lazy"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <div class="w-full h-full bg-gradient-to-br from-primary to-secondary rounded-2xl flex items-center justify-center" style="display: none;">
                                <div class="text-center text-white p-8">
                                    <i class="fas fa-building text-6xl mb-4"></i>
                                    <h3 class="text-2xl font-bold mb-2">{{ setting('company_name', 'Votre Entreprise') }}</h3>
                                    <p class="text-white/90">{{ setting('company_specialization', 'Travaux de Rénovation') }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="aspect-square bg-gradient-to-br from-primary to-secondary rounded-2xl flex items-center justify-center">
                            <div class="text-center text-white p-8">
                                <i class="fas fa-building text-6xl mb-4"></i>
                                <h3 class="text-2xl font-bold mb-2">{{ setting('company_name', 'Votre Entreprise') }}</h3>
                                <p class="text-white/90">{{ setting('company_specialization', 'Travaux de Rénovation') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- Services Section -->
    @if(($homeConfig['sections']['services']['enabled'] ?? true) && !empty($services))
    <section class="py-20">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">
                    {{ $homeConfig['sections']['services']['title'] ?? 'Nos Services' }}
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Des solutions complètes pour tous vos projets de rénovation
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach(collect($services)->take($homeConfig['sections']['services']['limit'] ?? 6) as $service)
                <div class="service-card bg-white rounded-2xl shadow-lg overflow-hidden">
                    @if(!empty($service['featured_image']))
                    <div class="h-48 bg-cover bg-center mobile-responsive-img service-image-mobile" style="background-image: url('{{ url($service['featured_image']) }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">
                        <img src="{{ url($service['featured_image']) }}" 
                             alt="{{ $service['name'] }}" 
                             class="w-full h-full object-cover mobile-responsive-img"
                             style="display: none;"
                             width="667"
                             height="350"
                             loading="lazy">
                    </div>
                    @else
                    <div class="h-48 bg-gradient-to-br from-primary to-secondary flex items-center justify-center">
                        <i class="{{ $service['icon'] ?? 'fas fa-tools' }} text-6xl text-white"></i>
                    </div>
                    @endif
                    
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-3">{{ $service['name'] }}</h3>
                        <p class="text-gray-600 mb-4">{{ $service['short_description'] ?? Str::limit($service['description'], 120) }}</p>
                        <a href="{{ route('services.show', $service['slug']) }}" 
                           class="inline-flex items-center font-semibold transition"
                           style="color: var(--primary-color);"
                           onmouseover="this.style.color='var(--secondary-color)';"
                           onmouseout="this.style.color='var(--primary-color)';"
                           onclick="trackServiceClick('{{ $service['name'] }}', '{{ request()->url() }}')">
                            En savoir plus <i class="fas fa-arrow-right ml-2" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- How It Works Section -->
    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Comment Ça Marche ?</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Un processus simple et transparent en 4 étapes
                </p>
            </div>
            
            <div class="max-w-6xl mx-auto">
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Étape 1 -->
                    <div class="relative">
                        <div class="bg-white p-8 rounded-2xl shadow-lg text-center transform hover:scale-105 transition-all duration-300 border-l-4 border-blue-500 h-full flex flex-col">
                            <div class="w-16 h-16 bg-blue-500 text-white rounded-full flex items-center justify-center text-2xl font-bold mb-6 mx-auto">
                                1
                            </div>
                            <h3 class="text-xl font-bold mb-4 text-gray-800">Demande de Devis</h3>
                            <p class="text-gray-600 leading-relaxed flex-grow">
                                Remplissez notre formulaire en ligne pour recevoir un devis personnalisé et gratuit.
                            </p>
                        </div>
                        <!-- Flèche vers la droite -->
                        <div class="hidden lg:block absolute top-1/2 -right-4 transform -translate-y-1/2">
                            <i class="fas fa-arrow-right text-blue-500 text-2xl"></i>
                        </div>
                    </div>
                    
                    <!-- Étape 2 -->
                    <div class="relative">
                        <div class="bg-white p-8 rounded-2xl shadow-lg text-center transform hover:scale-105 transition-all duration-300 border-l-4 border-green-500 h-full flex flex-col">
                            <div class="w-16 h-16 bg-green-500 text-white rounded-full flex items-center justify-center text-2xl font-bold mb-6 mx-auto">
                                2
                            </div>
                            <h3 class="text-xl font-bold mb-4 text-gray-800">Étude du Projet</h3>
                            <p class="text-gray-600 leading-relaxed flex-grow">
                                Nos experts analysent vos besoins et vous proposent la meilleure solution.
                            </p>
                        </div>
                        <!-- Flèche vers la droite -->
                        <div class="hidden lg:block absolute top-1/2 -right-4 transform -translate-y-1/2">
                            <i class="fas fa-arrow-right text-green-500 text-2xl"></i>
                        </div>
                    </div>
                    
                    <!-- Étape 3 -->
                    <div class="relative">
                        <div class="bg-white p-8 rounded-2xl shadow-lg text-center transform hover:scale-105 transition-all duration-300 border-l-4 border-orange-500 h-full flex flex-col">
                            <div class="w-16 h-16 bg-orange-500 text-white rounded-full flex items-center justify-center text-2xl font-bold mb-6 mx-auto">
                                3
                            </div>
                            <h3 class="text-xl font-bold mb-4 text-gray-800">Planification</h3>
                            <p class="text-gray-600 leading-relaxed flex-grow">
                                Nous planifions les travaux selon vos disponibilités et nos délais d'intervention.
                            </p>
                        </div>
                        <!-- Flèche vers la droite -->
                        <div class="hidden lg:block absolute top-1/2 -right-4 transform -translate-y-1/2">
                            <i class="fas fa-arrow-right text-orange-500 text-2xl"></i>
                        </div>
                    </div>
                    
                    <!-- Étape 4 -->
                    <div class="relative">
                        <div class="bg-white p-8 rounded-2xl shadow-lg text-center transform hover:scale-105 transition-all duration-300 border-l-4 border-purple-500 h-full flex flex-col">
                            <div class="w-16 h-16 bg-purple-500 text-white rounded-full flex items-center justify-center text-2xl font-bold mb-6 mx-auto">
                                4
                            </div>
                            <h3 class="text-xl font-bold mb-4 text-gray-800">Réalisation</h3>
                            <p class="text-gray-600 leading-relaxed flex-grow">
                                Nos équipes qualifiées réalisent vos travaux avec professionnalisme et qualité.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sections Écologie et Aide Financière -->
    @if(($homeConfig['ecology']['enabled'] ?? false) || ($homeConfig['financing']['enabled'] ?? false))
    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="grid lg:grid-cols-2 gap-8">
                <!-- Section Écologie (Gauche) -->
                @if(($homeConfig['ecology']['enabled'] ?? false) && !empty($homeConfig['ecology']['content']))
                <div class="group relative overflow-hidden bg-gradient-to-br from-green-600 to-emerald-700 rounded-3xl p-8 text-white shadow-2xl">
                    <!-- Effet de brillance -->
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-green-400 to-emerald-300"></div>
                    
                    <div class="relative z-10">
                        <div class="flex items-center mb-6">
                            <div class="w-20 h-20 bg-white/20 rounded-2xl flex items-center justify-center mr-6 shadow-lg">
                                <i class="fas fa-leaf text-white text-3xl"></i>
                            </div>
                            <div>
                                <h3 class="text-3xl font-bold mb-2" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.8);">
                                    {{ $homeConfig['ecology']['title'] ?? 'Notre Engagement Écologique' }}
                                </h3>
                                <div class="w-16 h-1 bg-green-300 rounded-full"></div>
                            </div>
                        </div>
                        
                        <div class="text-white/95 mb-8 text-lg leading-relaxed font-medium" style="text-shadow: 1px 1px 3px rgba(0,0,0,0.7);">
                            {!! nl2br(e($homeConfig['ecology']['content'])) !!}
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white/25 backdrop-blur-sm rounded-xl p-6 text-center shadow-lg hover:bg-white/35 transition-all duration-300">
                                <div class="text-4xl font-bold mb-3">♻️</div>
                                <div class="text-sm font-bold" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">Matériaux recyclés</div>
                            </div>
                            <div class="bg-white/25 backdrop-blur-sm rounded-xl p-6 text-center shadow-lg hover:bg-white/35 transition-all duration-300">
                                <div class="text-4xl font-bold mb-3">🌱</div>
                                <div class="text-sm font-bold" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">Énergies vertes</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Motif décoratif -->
                    <div class="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                    <div class="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                </div>
                @endif
                
                <!-- Section Aide Financière (Droite) -->
                @if(($homeConfig['financing']['enabled'] ?? false) && !empty($homeConfig['financing']['content']))
                <div class="group relative overflow-hidden bg-gradient-to-br from-yellow-600 to-orange-600 rounded-3xl p-8 text-white shadow-2xl">
                    <!-- Effet de brillance -->
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-yellow-400 to-orange-300"></div>
                    
                    <div class="relative z-10">
                        <div class="flex items-center mb-6">
                            <div class="w-20 h-20 bg-white/20 rounded-2xl flex items-center justify-center mr-6 shadow-lg">
                                <i class="fas fa-euro-sign text-white text-3xl"></i>
                            </div>
                            <div>
                                <h3 class="text-3xl font-bold mb-2" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.8);">
                                    {{ $homeConfig['financing']['title'] ?? 'Aides et Financements Disponibles' }}
                                </h3>
                                <div class="w-16 h-1 bg-yellow-300 rounded-full"></div>
                            </div>
                        </div>
                        
                        <div class="text-white/95 mb-8 text-lg leading-relaxed font-medium" style="text-shadow: 1px 1px 3px rgba(0,0,0,0.7);">
                            {!! nl2br(e($homeConfig['financing']['content'])) !!}
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white/25 backdrop-blur-sm rounded-xl p-6 text-center shadow-lg hover:bg-white/35 transition-all duration-300">
                                <div class="text-4xl font-bold mb-3">🏠</div>
                                <div class="text-sm font-bold" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">MaPrimeRénov'</div>
                            </div>
                            <div class="bg-white/25 backdrop-blur-sm rounded-xl p-6 text-center shadow-lg hover:bg-white/35 transition-all duration-300">
                                <div class="text-4xl font-bold mb-3">💰</div>
                                <div class="text-sm font-bold" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">Certificats CEE</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Motif décoratif -->
                    <div class="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                    <div class="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                </div>
                @endif
            </div>
        </div>
    </section>
    @endif


    <!-- Portfolio Section -->
    @if(($homeConfig['sections']['portfolio']['enabled'] ?? true) && !empty($portfolioItems))
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">
                    {{ $homeConfig['sections']['portfolio']['title'] ?? 'Nos Réalisations' }}
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Découvrez quelques-unes de nos réalisations récentes
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach(collect($portfolioItems)->take($homeConfig['sections']['portfolio']['limit'] ?? 6) as $item)
                <a href="{{ route('portfolio.show', $item['slug'] ?? \Illuminate\Support\Str::slug($item['title'] ?? 'realisation')) }}" class="block bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    @if(!empty($item['images']))
                        @php $firstImage = is_array($item['images']) ? $item['images'][0] : $item['images']; @endphp
                        <div class="h-64 bg-cover bg-center portfolio-image-mobile" style="background-image: url('{{ asset($firstImage) }}'); background-size: cover; background-position: center; background-repeat: no-repeat;"></div>
                    @else
                        <div class="h-64 bg-gradient-to-br from-primary to-secondary flex items-center justify-center">
                            <i class="fas fa-image text-6xl text-white"></i>
                        </div>
                    @endif
                    
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $item['title'] }}</h3>
                        <p class="text-gray-600 mb-4">{{ Str::limit($item['description'], 100) }}</p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">{{ $item['type'] ?? 'Réalisation' }}</span>
                            <div class="inline-flex items-center text-primary font-semibold">
                                Voir le projet <i class="fas fa-arrow-right ml-1"></i>
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            
            <div class="text-center mt-12">
                <a href="{{ route('portfolio.index') }}" 
                   class="inline-flex items-center bg-primary text-white px-8 py-4 rounded-full text-lg font-semibold hover:bg-secondary transition-all duration-300 transform hover:scale-105 shadow-lg">
                    Voir Toutes Nos Réalisations <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </section>
    @endif

    <!-- Reviews Section -->
    @if(($homeConfig['sections']['reviews']['enabled'] ?? true) && !empty($reviews))
    <section class="py-20 bg-gray-100">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">
                    {{ $homeConfig['sections']['reviews']['title'] ?? 'Avis de Nos Clients' }}
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Ce que nos clients disent de nous
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($reviews->take($homeConfig['sections']['reviews']['limit'] ?? 6) as $review)
                <div class="bg-white p-6 rounded-2xl shadow-lg">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center font-bold">
                            {{ $review->author_initials ?? substr($review->author_name, 0, 1) }}
                        </div>
                        <div class="ml-4">
                            <h4 class="font-semibold text-gray-800">{{ $review->author_name }}</h4>
                            <div class="flex text-yellow-400">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= $review->rating ? '' : 'text-gray-300' }}"></i>
                                @endfor
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">{{ Str::limit($review->review_text, 150) }}</p>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">
                            {{ $review->review_date ? $review->review_date->diffForHumans() : $review->created_at->diffForHumans() }}
                        </span>
                        @if($review->source)
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">{{ $review->source }}</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Bouton "Lire tous les avis" -->
            <div class="text-center mt-12">
                <a href="{{ route('reviews.all') }}" 
                   class="bg-primary text-white px-8 py-4 rounded-lg font-semibold hover:bg-secondary transition-colors text-lg">
                    <i class="fas fa-star mr-2"></i>
                    Lire Tous les Avis
                </a>
            </div>
        </div>
    </section>
    @endif

    <!-- Why Choose Us Section -->
    @if($homeConfig['sections']['why_choose_us']['enabled'] ?? true)
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">
                    {{ $homeConfig['sections']['why_choose_us']['title'] ?? 'Pourquoi Nous Choisir ?' }}
                </h2>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary text-white rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                        <i class="fas fa-award"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Expertise Reconnue</h3>
                    <p class="text-gray-600">Plus de {{ setting('company_experience', '15') }} ans d'expérience dans le domaine</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary text-white rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Garantie Qualité</h3>
                    <p class="text-gray-600">Tous nos travaux sont garantis et assurés</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary text-white rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Intervention Rapide</h3>
                    <p class="text-gray-600">Devis gratuit sous 24h, intervention sous 48h</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary text-white rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Satisfaction Client</h3>
                    <p class="text-gray-600">{{ $averageRating > 0 ? number_format($averageRating, 1) : '98' }}/5 de satisfaction client</p>
                </div>
            </div>
        </div>
    </section>
    @endif


    <!-- Partners Section -->
    @php
        $partnersEnabled = $homeConfig['partners']['enabled'] ?? false;
        $partners = $homeConfig['partners']['logos'] ?? [];
        $partnersTitle = $homeConfig['partners']['title'] ?? 'Nos Partenaires';
        $showPartners = $partnersEnabled && !empty($partners);
    @endphp
    @if($showPartners)
    <section class="py-12 bg-gray-50">
        <div class="container mx-auto px-4">
            <!-- Section Title -->
            <div class="text-center mb-8">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">{{ $partnersTitle }}</h2>
            </div>
            
            <!-- Partners Logos -->
            <div class="flex flex-wrap justify-center items-center gap-4 md:gap-6">
                @foreach($partners as $partner)
                    @if(!empty($partner['logo']))
                    <div class="partner-logo-container w-[120px] sm:w-[140px] md:w-[160px] h-16 md:h-20 flex items-center justify-center p-2 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow flex-shrink-0">
                        @if(!empty($partner['url']))
                        <a href="{{ $partner['url'] }}" target="_blank" rel="noopener noreferrer" class="w-full h-full flex items-center justify-center">
                            <img 
                                src="{{ asset($partner['logo']) }}" 
                                alt="{{ $partner['name'] ?? 'Partenaire' }}"
                                class="max-w-full max-h-full object-contain transition-all duration-300 opacity-100 hover:opacity-90"
                                loading="lazy"
                                onerror="this.style.display='none';">
                        </a>
                        @else
                        <img 
                            src="{{ asset($partner['logo']) }}" 
                            alt="{{ $partner['name'] ?? 'Partenaire' }}"
                            class="max-w-full max-h-full object-contain opacity-100"
                            loading="lazy"
                            onerror="this.style.display='none';">
                        @endif
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- CTA Section -->
    @if($homeConfig['sections']['cta']['enabled'] ?? true)
    <section class="py-20 relative overflow-hidden" style="background-color: var(--primary-color);">
        <!-- Overlay sombre pour améliorer la lisibilité -->
        <div class="absolute inset-0 bg-black/40"></div>
        
        <!-- Motif de fond subtil -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-r from-white/10 to-transparent"></div>
        </div>
        
        <div class="container mx-auto px-4 relative z-10">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-white mb-6 drop-shadow-2xl" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.8);">
                    {{ $homeConfig['sections']['cta']['title'] ?? 'Prêt à Démarrer Votre Projet ?' }}
                </h2>
                <p class="text-xl text-white mb-8 max-w-2xl mx-auto drop-shadow-xl font-medium" style="text-shadow: 1px 1px 3px rgba(0,0,0,0.8);">
                    Contactez-nous dès aujourd'hui pour un devis gratuit et personnalisé
                </p>
            </div>
            
            
            <!-- Boutons d'action -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('contact') }}" 
                   class="text-white px-8 py-4 rounded-full text-lg font-semibold transition-all duration-300 transform hover:scale-105 shadow-lg"
                   style="background-color: var(--primary-color);"
                   onmouseover="this.style.backgroundColor='var(--accent-color)';"
                   onmouseout="this.style.backgroundColor='var(--primary-color)';">
                    <i class="fas fa-envelope mr-2"></i>
                    Contact
                </a>
                <a href="{{ route('form.step', 'propertyType') }}" 
                   class="text-white px-8 py-4 rounded-full text-lg font-semibold transition-all duration-300 transform hover:scale-105 shadow-lg"
                   style="background-color: var(--secondary-color);"
                   onmouseover="this.style.backgroundColor='var(--accent-color)';"
                   onmouseout="this.style.backgroundColor='var(--secondary-color)';"
                   onclick="trackFormClick('{{ request()->url() }}')">
                    <i class="fas fa-calculator mr-2"></i>
                    Simulateur de Devis
                </a>
                <a href="tel:{{ setting('company_phone_raw', setting('company_phone')) }}" 
                   class="text-white px-8 py-4 rounded-full text-lg font-semibold transition-all duration-300 transform hover:scale-105 shadow-lg"
                   style="background-color: var(--accent-color);"
                   onmouseover="this.style.backgroundColor='var(--secondary-color)';"
                   onmouseout="this.style.backgroundColor='var(--accent-color)';"
                   onclick="trackPhoneCall('{{ setting('company_phone_raw', setting('company_phone')) }}', 'home-cta')">
                    <i class="fas fa-phone mr-2"></i>
                    Appeler
                </a>
            </div>
        </div>
    </section>
    @endif


    <!-- JavaScript -->
    <script>
        // trackPhoneCall est géré automatiquement par phone-tracking.js dans layouts/app.blade.php
        // Plus besoin de fonction locale, le script global s'occupe de tout

        function trackFormClick(page) {
            fetch('/api/track-form-click', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    page: page
                })
            }).catch(error => console.log('Tracking error:', error));
        }

        function trackServiceClick(service, page) {
            fetch('/api/track-service-click', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    service: service,
                    page: page
                })
            }).catch(error => console.log('Tracking error:', error));
        }

        // Force image display on mobile
        document.addEventListener('DOMContentLoaded', function() {
            // Check if mobile
            if (window.innerWidth <= 768) {
                // Force hero background image
                const heroSection = document.querySelector('.hero-mobile');
                if (heroSection && heroSection.style.backgroundImage) {
                    heroSection.style.backgroundSize = 'cover';
                    heroSection.style.backgroundPosition = 'center center';
                    heroSection.style.backgroundRepeat = 'no-repeat';
                    heroSection.style.backgroundAttachment = 'scroll';
                }
                
                // Force all images to display
                const images = document.querySelectorAll('img');
                images.forEach(img => {
                    img.style.maxWidth = '100%';
                    img.style.height = 'auto';
                    img.style.display = 'block';
                    img.style.width = '100%';
                });
                
                // Force service images to show on mobile
                const serviceImages = document.querySelectorAll('.service-image-mobile img');
                serviceImages.forEach(img => {
                    img.style.display = 'block';
                    img.style.width = '100%';
                    img.style.height = '100%';
                    img.style.objectFit = 'cover';
                });
                
                // Force background images
                const bgElements = document.querySelectorAll('[style*="background-image"]');
                bgElements.forEach(el => {
                    el.style.backgroundSize = 'cover';
                    el.style.backgroundPosition = 'center center';
                    el.style.backgroundRepeat = 'no-repeat';
                });
            }
        });
    </script>
@endsection







