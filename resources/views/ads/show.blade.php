@extends('layouts.app')

@section('title', $pageTitle)

@section('description', $pageDescription)

@push('head')
<style>
    /* Variables de couleurs de branding */
    :root {
        --primary-color: {{ setting('primary_color', '#3b82f6') }};
        --secondary-color: {{ setting('secondary_color', '#1e40af') }};
        --accent-color: {{ setting('accent_color', '#f59e0b') }};
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <section class="relative py-20 text-white overflow-hidden">
        <div class="absolute inset-0" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);"></div>
        
        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-6">
                    <i class="fas fa-tools mr-4"></i>
                    {{ $ad->title }}
                </h1>
                <p class="text-xl md:text-2xl mb-8 leading-relaxed">
                    Service professionnel à {{ $cityModel->name }} - Devis gratuit et intervention rapide
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

    <!-- Contenu de l'annonce -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="bg-white rounded-2xl shadow-lg p-8 md:p-12">
                    {!! $ad->content_html !!}
                </div>

                <div class="mt-12 rounded-2xl p-8 text-white text-center" style="background: linear-gradient(90deg, var(--primary-color) 0%, var(--secondary-color) 100%);">
                    <h3 class="text-2xl font-bold mb-4">Prêt à Démarrer Votre Projet à {{ $cityModel->name }} ?</h3>
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

    <!-- Section Annonces Similaires -->
    @if($relatedAds->count() > 0)
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Autres Services à {{ $cityModel->name }}</h2>
                    <p class="text-lg text-gray-600">Découvrez nos autres services disponibles dans votre ville</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($relatedAds as $relatedAd)
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $relatedAd->title }}</h3>
                            <p class="text-gray-600 text-sm mb-4">{{ Str::limit($relatedAd->meta_description, 100) }}</p>
                            <a href="{{ route('ads.show', ['service' => explode('-', $relatedAd->slug)[0], 'city' => implode('-', array_slice(explode('-', $relatedAd->slug), 1))]) }}" 
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