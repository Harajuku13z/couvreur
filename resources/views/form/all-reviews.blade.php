@extends('layouts.app')

@section('title', 'Tous nos avis clients - ' . setting('company_name'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12">
    <div class="container mx-auto px-4">
        <!-- Header -->
        <div class="text-center mb-12">
            <a href="{{ route('home') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
                <i class="fas fa-arrow-left mr-2"></i>Retour à l'accueil
            </a>
            <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                Nos Avis Clients
            </h1>
            <p class="text-xl text-gray-600">
                Ce que nos clients disent de nous
            </p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-12 max-w-4xl mx-auto">
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="text-3xl font-bold text-blue-600">{{ $stats['total'] }}</div>
                <div class="text-sm text-gray-600 mt-1">Avis total</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="text-3xl font-bold text-yellow-500">{{ $stats['average'] }}</div>
                <div class="text-sm text-gray-600 mt-1">Note moyenne</div>
                <div class="flex justify-center mt-1">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star {{ $i <= $stats['average'] ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                    @endfor
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="text-3xl font-bold text-green-600">{{ $stats['five_stars'] }}</div>
                <div class="text-sm text-gray-600 mt-1">5 étoiles</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="text-3xl font-bold text-indigo-600">{{ $stats['four_stars'] }}</div>
                <div class="text-sm text-gray-600 mt-1">4 étoiles</div>
            </div>
        </div>

        <!-- Reviews Grid -->
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                @forelse($reviews as $review)
                    <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition">
                        <!-- Rating -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                @endfor
                            </div>
                            @if($review->source === 'google')
                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                                    <i class="fab fa-google mr-1"></i>Google
                                </span>
                            @endif
                        </div>

                        <!-- Review Text -->
                        <p class="text-gray-700 mb-4 line-clamp-4">{{ $review->review_text }}</p>

                        <!-- Author -->
                        <div class="flex items-center mt-4 pt-4 border-t border-gray-200">
                            @if($review->author_photo_url)
                                <img src="{{ $review->author_photo_url }}" alt="{{ $review->author_name }}" class="w-10 h-10 rounded-full mr-3">
                            @else
                                <div class="w-10 h-10 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold mr-3">
                                    {{ strtoupper(substr($review->author_name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <p class="font-semibold text-gray-900">{{ $review->author_name }}</p>
                                <p class="text-sm text-gray-500">{{ $review->review_date ? $review->review_date->format('d/m/Y') : '' }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-12">
                        <i class="fas fa-star text-6xl text-gray-300 mb-4"></i>
                        <p class="text-xl text-gray-600">Aucun avis pour le moment</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($reviews->hasPages())
            <div class="flex justify-center">
                {{ $reviews->links() }}
            </div>
            @endif
        </div>

        <!-- CTA -->
        <div class="text-center mt-12">
            <div class="bg-white rounded-lg shadow-lg p-8 max-w-2xl mx-auto">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">
                    Vous aussi, faites-nous confiance !
                </h2>
                <p class="text-gray-600 mb-6">
                    Obtenez votre devis personnalisé gratuitement
                </p>
                <a href="{{ route('home') }}" class="inline-block bg-blue-600 text-white px-8 py-4 rounded-lg hover:bg-blue-700 transition text-lg font-semibold">
                    <i class="fas fa-calculator mr-2"></i>Démarrer mon projet
                </a>
            </div>
        </div>
    </div>
</div>
@endsection







