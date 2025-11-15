@extends('layouts.app')

@section('title', 'Tous nos avis clients - ' . setting('company_name'))
@section('description', 'Découvrez les avis de nos clients satisfaits. Plus de ' . $stats['total'] . ' avis avec une note moyenne de ' . $stats['average'] . '/5. Témoignages authentiques sur nos services de couverture et rénovation.')

@push('head')
@endpush

@php
    // Définir les variables pour le SEO centralisé
    $currentPage = 'reviews';
    $pageTitle = 'Avis Clients - ' . setting('company_name', 'Sauser Couverture');
    $pageDescription = 'Découvrez les avis de nos clients satisfaits. Plus de ' . $stats['total'] . ' avis avec une note moyenne de ' . $stats['average'] . '/5. Témoignages authentiques sur nos services de couverture et rénovation.';
    // $pageImage supprimé pour utiliser l'image par défaut du SeoHelper
    $pageType = 'website';
@endphp

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16">
        <div class="container mx-auto px-4">
            <div class="text-center">
                <a href="{{ url('/') }}" class="text-blue-200 hover:text-white mb-6 inline-block transition">
                    <i class="fas fa-arrow-left mr-2"></i>Retour à l'accueil
                </a>
                <h1 class="text-5xl md:text-6xl font-bold mb-6">
                    Nos Avis Clients
                </h1>
                <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                    Découvrez ce que nos clients pensent de nos services
                </p>
                
                <!-- Bouton Ajouter Avis -->
                <a href="{{ route('reviews.create') }}" class="inline-block bg-white text-blue-600 px-8 py-4 rounded-full font-semibold text-lg hover:bg-blue-50 transition-all duration-300 shadow-lg hover:shadow-xl">
                    <i class="fas fa-plus mr-2"></i>Ajouter un Avis
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-6xl mx-auto">
                <div class="text-center p-6 bg-blue-50 rounded-2xl">
                    <div class="text-4xl font-bold text-blue-600 mb-2">{{ $stats['total'] }}</div>
                    <div class="text-gray-600 font-medium">Avis Total</div>
                </div>
                <div class="text-center p-6 bg-yellow-50 rounded-2xl">
                    <div class="text-4xl font-bold text-yellow-600 mb-2">{{ $stats['average'] }}</div>
                    <div class="text-gray-600 font-medium mb-2">Note Moyenne</div>
                    <div class="flex justify-center">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star {{ $i <= $stats['average'] ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                        @endfor
                    </div>
                </div>
                <div class="text-center p-6 bg-green-50 rounded-2xl">
                    <div class="text-4xl font-bold text-green-600 mb-2">{{ $stats['five_stars'] }}</div>
                    <div class="text-gray-600 font-medium">5 Étoiles</div>
                </div>
                <div class="text-center p-6 bg-purple-50 rounded-2xl">
                    <div class="text-4xl font-bold text-purple-600 mb-2">{{ $stats['four_stars'] }}</div>
                    <div class="text-gray-600 font-medium">4 Étoiles</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews Section -->
    <div class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @forelse($reviews as $review)
                        <div class="bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden group">
                            <!-- Header avec note et source -->
                            <div class="p-6 pb-4">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center space-x-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }} text-lg"></i>
                                        @endfor
                                    </div>
                                    @if($review->source)
                                        <span class="text-xs bg-gray-100 text-gray-600 px-3 py-1 rounded-full font-medium">
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
                                                <i class="fas fa-star mr-1"></i>{{ $review->source }}
                                            @endif
                                        </span>
                                    @endif
                                </div>

                                <!-- Review Text -->
                                <p class="text-gray-700 mb-6 leading-relaxed">{{ $review->review_text }}</p>

                                <!-- Système de photos supprimé -->
                            </div>

                            <!-- Author Section -->
                            <div class="px-6 pb-6 pt-0">
                                <div class="flex items-center">
                                    @if($review->author_photo)
                                        <img src="{{ $review->author_photo }}" alt="{{ $review->author_name }}" class="w-12 h-12 rounded-full mr-4 object-cover border-2 border-gray-200">
                                    @elseif($review->author_photo_url)
                                        <img src="{{ $review->author_photo_url }}" alt="{{ $review->author_name }}" class="w-12 h-12 rounded-full mr-4 object-cover border-2 border-gray-200">
                                    @else
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center font-bold mr-4 text-lg">
                                            {{ $review->author_initials }}
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        @if($review->author_link)
                                            <a href="{{ $review->author_link }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-gray-900 hover:text-blue-600 transition-colors">
                                                {{ $review->author_name }}
                                                <i class="fas fa-external-link-alt text-xs ml-1 text-gray-400"></i>
                                            </a>
                                        @else
                                            <p class="font-semibold text-gray-900">{{ $review->author_name }}</p>
                                        @endif
                                        <p class="text-sm text-gray-500">{{ $review->review_date ? $review->review_date->format('d/m/Y') : '' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-16">
                            <div class="bg-white rounded-2xl shadow-lg p-12">
                                <i class="fas fa-star text-6xl text-gray-300 mb-6"></i>
                                <h3 class="text-2xl font-bold text-gray-800 mb-4">Aucun avis pour le moment</h3>
                                <p class="text-gray-600 mb-8">Soyez le premier à partager votre expérience !</p>
                                <a href="{{ route('reviews.create') }}" class="inline-block bg-blue-600 text-white px-8 py-4 rounded-full font-semibold hover:bg-blue-700 transition">
                                    <i class="fas fa-plus mr-2"></i>Ajouter un Avis
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($reviews->hasPages())
                <div class="flex justify-center mt-12">
                    <div class="bg-white rounded-2xl shadow-lg p-4">
                        {{ $reviews->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>

    <!-- CTA Section -->
    <div class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center">
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-3xl p-12 text-white max-w-4xl mx-auto">
                    <h2 class="text-4xl font-bold mb-6">
                        Vous aussi, faites-nous confiance !
                    </h2>
                    <p class="text-xl text-blue-100 mb-8">
                        Obtenez votre devis personnalisé gratuitement
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ url('/') }}" class="inline-block bg-white text-blue-600 px-8 py-4 rounded-full font-semibold text-lg hover:bg-blue-50 transition-all duration-300 shadow-lg hover:shadow-xl">
                            <i class="fas fa-calculator mr-2"></i>Démarrer mon projet
                        </a>
                        <a href="{{ route('reviews.create') }}" class="inline-block bg-blue-500 text-white px-8 py-4 rounded-full font-semibold text-lg hover:bg-blue-400 transition-all duration-300 shadow-lg hover:shadow-xl">
                            <i class="fas fa-star mr-2"></i>Laisser un avis
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

        <!-- Image Modal supprimé -->
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Système de photos supprimé
});
</script>
@endsection







