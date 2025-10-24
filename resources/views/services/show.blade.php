@extends('layouts.app')

@section('title', $service['meta_title'] ?? $service['name'] . ' - ' . setting('company_name', 'Votre Entreprise'))

@section('description', $service['meta_description'] ?? $service['short_description'])

@section('keywords', $service['meta_keywords'] ?? '')

@push('head')
<style>
    /* Variables de couleurs de branding */
    :root {
        --primary-color: {{ setting('primary_color', '#3b82f6') }};
        --secondary-color: {{ setting('secondary_color', '#1e40af') }};
        --accent-color: {{ setting('accent_color', '#f59e0b') }};
    }
</style>
<!-- Open Graph pour les réseaux sociaux -->
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $service['og_title'] ?? $service['meta_title'] ?? $service['name'] }}">
<meta property="og:description" content="{{ $service['og_description'] ?? $service['meta_description'] ?? $service['short_description'] }}">
<meta property="og:url" content="{{ request()->url() }}">
@if($service['og_image'] ?? $service['featured_image'])
<meta property="og:image" content="{{ asset($service['og_image'] ?? $service['featured_image']) }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
@endif
<meta property="og:site_name" content="{{ setting('company_name', 'Votre Entreprise') }}">

<!-- Twitter Cards -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $service['og_title'] ?? $service['meta_title'] ?? $service['name'] }}">
<meta name="twitter:description" content="{{ $service['og_description'] ?? $service['meta_description'] ?? $service['short_description'] }}">
@if($service['og_image'] ?? $service['featured_image'])
<meta name="twitter:image" content="{{ asset($service['og_image'] ?? $service['featured_image']) }}">
@endif
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <section class="relative py-20 text-white overflow-hidden">
        @if($service['featured_image'])
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" 
             style="background-image: url('{{ asset($service['featured_image']) }}'); filter: blur(2px); transform: scale(1.1);"></div>
        <div class="absolute inset-0 bg-black bg-opacity-50"></div>
        @else
        <div class="absolute inset-0" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);"></div>
        @endif
        
        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-6">
                    <i class="{{ $service['icon'] ?? 'fas fa-tools' }} mr-4"></i>
                    {{ $service['name'] }}
                </h1>
                <p class="text-xl md:text-2xl mb-8 leading-relaxed">
                    {{ $service['short_description'] }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('form.step', 'propertyType') }}" 
                       class="text-white font-bold py-4 px-8 rounded-lg text-lg transition-colors shadow-lg"
                       style="background-color: var(--accent-color);"
                       onmouseover="this.style.backgroundColor='var(--secondary-color)';"
                       onmouseout="this.style.backgroundColor='var(--accent-color)';">
                        <i class="fas fa-calculator mr-2"></i>
                        Devis Gratuit
                    </a>
                    <a href="tel:{{ setting('company_phone_raw') }}" 
                       class="text-white font-bold py-4 px-8 rounded-lg text-lg transition-colors shadow-lg"
                       style="background-color: var(--primary-color);"
                       onmouseover="this.style.backgroundColor='var(--secondary-color)';"
                       onmouseout="this.style.backgroundColor='var(--primary-color)';">
                        <i class="fas fa-phone mr-2"></i>
                        {{ setting('company_phone') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Contenu du service -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="bg-white rounded-2xl shadow-lg p-8 md:p-12">
                    {!! $service['description'] !!}
                </div>

                <div class="mt-12 rounded-2xl p-8 text-white text-center" style="background: linear-gradient(90deg, var(--primary-color) 0%, var(--secondary-color) 100%);">
                    <h3 class="text-2xl font-bold mb-4">Prêt à Démarrer Votre Projet {{ $service['name'] }} ?</h3>
                    <p class="text-lg mb-6">Contactez-nous dès aujourd'hui pour un devis gratuit et personnalisé</p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ route('form.step', 'propertyType') }}" 
                           class="text-white font-bold py-4 px-8 rounded-lg text-lg transition-colors shadow-lg"
                           style="background-color: var(--accent-color);"
                           onmouseover="this.style.backgroundColor='var(--secondary-color)';"
                           onmouseout="this.style.backgroundColor='var(--accent-color)';">
                            <i class="fas fa-calculator mr-2"></i>
                            Demander un Devis Gratuit
                        </a>
                        <a href="tel:{{ setting('company_phone_raw') }}" 
                           class="text-white font-bold py-4 px-8 rounded-lg text-lg transition-colors shadow-lg"
                           style="background-color: var(--primary-color);"
                           onmouseover="this.style.backgroundColor='var(--secondary-color)';"
                           onmouseout="this.style.backgroundColor='var(--primary-color)';">
                            <i class="fas fa-phone mr-2"></i>
                            Appeler Maintenant
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Réalisations -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Nos Réalisations {{ $service['name'] }}</h2>
                    <p class="text-lg text-gray-600">Découvrez quelques-unes de nos réalisations récentes</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @php
                        $portfolioItems = \App\Models\Setting::get('portfolio_items', []);
                        // Ensure we have a valid array and filter out any invalid items
                        if (!is_array($portfolioItems)) {
                            $portfolioItems = [];
                        }
                        $relatedPortfolio = collect($portfolioItems)
                            ->filter(function($item) {
                                return is_array($item) && isset($item['title']);
                            })
                            ->take(3);
                    @endphp
                    
                    @foreach($relatedPortfolio as $item)
                        @if(is_array($item) && isset($item['title']))
                        <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                            @if(isset($item['images']) && is_array($item['images']) && count($item['images']) > 0)
                            <div class="relative h-48 overflow-hidden">
                                <img src="{{ url($item['images'][0]) }}" 
                                     alt="{{ $item['title'] }}" 
                                     class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-30 transition-all duration-300 flex items-center justify-center">
                                    <a href="{{ route('portfolio.show', $item['id'] ?? $loop->index) }}" 
                                       class="text-white px-4 py-2 rounded-lg font-semibold opacity-0 hover:opacity-100 transition-all duration-300 transform hover:scale-105"
                                       style="background-color: var(--primary-color);"
                                       onmouseover="this.style.backgroundColor='var(--secondary-color)';"
                                       onmouseout="this.style.backgroundColor='var(--primary-color)';">
                                        <i class="fas fa-eye mr-2"></i>
                                        Voir la réalisation
                                    </a>
                                </div>
                            </div>
                            @endif
                            
                            <div class="p-6">
                                <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $item['title'] }}</h3>
                                @if(isset($item['description']) && $item['description'])
                                <p class="text-gray-600 text-sm">{{ Str::limit($item['description'], 100) }}</p>
                                @endif
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
                
                <div class="text-center mt-8">
                    <a href="{{ route('portfolio.index') }}" 
                       class="text-white font-bold py-3 px-8 rounded-lg transition-colors"
                       style="background-color: var(--primary-color);"
                       onmouseover="this.style.backgroundColor='var(--secondary-color)';"
                       onmouseout="this.style.backgroundColor='var(--primary-color)';">
                        Voir Toutes Nos Réalisations
                    </a>
                </div>
            </div>
        </div>
    </section>

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
                                {{ ucfirst($review->source) }}
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
@endsection



