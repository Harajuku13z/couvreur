@extends('layouts.app')

@section('title', $ad->meta_title ?? $ad->title)
@section('description', $ad->meta_description ?? Str::limit(strip_tags($ad->content_html ?? ''), 150))

@push('head')
<link rel="canonical" href="{{ url()->current() }}" />
@if($ad->content_json && isset($ad->content_json['og_title']))
<meta property="og:title" content="{{ $ad->content_json['og_title'] }}">
@endif
@if($ad->content_json && isset($ad->content_json['og_description']))
<meta property="og:description" content="{{ $ad->content_json['og_description'] }}">
@endif
@if($ad->content_json && isset($ad->content_json['service_featured_image']))
<meta property="og:image" content="{{ asset($ad->content_json['service_featured_image']) }}">
@endif
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <section class="relative py-20 text-white overflow-hidden">
        @php
            $serviceImage = $ad->content_json['service_featured_image'] ?? null;
        @endphp
        
        @if($serviceImage)
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" 
             style="background-image: url('{{ asset($serviceImage) }}'); filter: blur(2px); transform: scale(1.1);"></div>
        <div class="absolute inset-0 bg-black bg-opacity-50"></div>
        @else
        <div class="absolute inset-0 bg-gradient-to-br from-blue-600 to-blue-800"></div>
        @endif
        
        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-6">
                    <i class="fas fa-map-marker-alt mr-4"></i>
                    {{ $ad->title }}
                </h1>
                @if($city)
                <p class="text-xl md:text-2xl mb-8 leading-relaxed">
                    Intervention professionnelle à {{ $city->name }} ({{ $city->postal_code }})
                </p>
                @endif
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('form.step', 'propertyType') }}" 
                       class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-4 px-8 rounded-lg text-lg transition-colors shadow-lg">
                        <i class="fas fa-calculator mr-2"></i>
                        Devis Gratuit
                    </a>
                    <a href="tel:{{ setting('company_phone_raw') }}" 
                       class="bg-green-500 hover:bg-green-600 text-white font-bold py-4 px-8 rounded-lg text-lg transition-colors shadow-lg">
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

                <div class="mt-12 bg-gradient-to-r from-blue-600 to-blue-800 rounded-2xl p-8 text-white text-center">
                    <h3 class="text-2xl font-bold mb-4">Prêt à Démarrer Votre Projet à {{ $city->name ?? 'Notre Région' }} ?</h3>
                    <p class="text-lg mb-6">Contactez-nous dès aujourd'hui pour un devis gratuit et personnalisé</p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ route('form.step', 'propertyType') }}" 
                           class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-4 px-8 rounded-lg text-lg transition-colors shadow-lg">
                            <i class="fas fa-calculator mr-2"></i>
                            Demander un Devis Gratuit
                        </a>
                        <a href="tel:{{ setting('company_phone_raw') }}" 
                           class="bg-green-500 hover:bg-green-600 text-white font-bold py-4 px-8 rounded-lg text-lg transition-colors shadow-lg">
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
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Nos Réalisations</h2>
                    <p class="text-lg text-gray-600">Découvrez quelques-unes de nos réalisations récentes</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @php
                        $portfolioItems = \App\Models\Setting::get('portfolio_items', []);
                        $relatedPortfolio = collect($portfolioItems)->take(3);
                    @endphp
                    
                    @foreach($relatedPortfolio as $item)
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                        @if(isset($item['images']) && count($item['images']) > 0)
                        <div class="relative h-48 overflow-hidden">
                            <img src="{{ asset($item['images'][0]) }}" 
                                 alt="{{ $item['title'] }}" 
                                 class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-30 transition-all duration-300 flex items-center justify-center">
                                <a href="{{ route('portfolio.show', $item['id'] ?? $loop->index) }}" 
                                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold opacity-0 hover:opacity-100 transition-all duration-300 transform hover:scale-105">
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
                    @endforeach
                </div>
                
                <div class="text-center mt-8">
                    <a href="{{ route('portfolio.index') }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg transition-colors">
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
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
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
                       class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg transition-colors">
                        Voir Tous les Avis
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection






