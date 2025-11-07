@extends('layouts.app')

@php
    // Définir les variables pour le SEO centralisé
    $currentPage = $currentPage ?? 'ads';
    $pageType = 'website';
    
    // Récupérer les meta tags depuis SeoHelper
    $seoData = \App\Helpers\SeoHelper::generateMetaTags('ads', [
        'title' => 'Nos Annonces',
        'description' => 'Découvrez nos services par ville. Solutions professionnelles de couverture et rénovation dans toute la région.',
        'image' => file_exists(public_path('images/og-services.jpg')) ? asset('images/og-services.jpg') : null,
    ]);
    
    $pageTitle = $seoData['title'];
    $pageDescription = $seoData['description'];
    $pageImage = $seoData['og:image'];
@endphp

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
<div class="max-w-6xl mx-auto py-10">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Nos Annonces</h1>
        <p class="text-xl text-gray-600">Découvrez nos services par ville</p>
    </div>

    @if($ads->count() > 0)
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($ads as $ad)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="p-6">
                        <div class="flex items-center text-sm text-gray-500 mb-3">
                            <span class="mr-4">📍 {{ $ad->city->name }}</span>
                            <span class="mr-4">📅 {{ $ad->formatted_publication_date }}</span>
                        </div>
                        
                        <h2 class="text-xl font-bold text-gray-900 mb-3">
                            <a href="{{ route('ads.show', $ad->slug) }}" class="hover:text-blue-600">
                                {{ $ad->title }}
                            </a>
                        </h2>
                        
                        @if($ad->meta_description)
                            <p class="text-gray-600 mb-4">{{ Str::limit($ad->meta_description, 120) }}</p>
                        @endif
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">{{ $ad->keyword }}</span>
                            <a href="{{ route('ads.show', $ad->slug) }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                                Voir l'annonce →
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($ads->hasPages())
            <div class="mt-12">
                {{ $ads->links() }}
            </div>
        @endif
    @else
        <div class="text-center py-12">
            <div class="text-gray-500 text-lg">
                <i class="fas fa-map-marker-alt text-4xl mb-4"></i>
                <p>Aucune annonce disponible pour le moment.</p>
            </div>
        </div>
    @endif
</div>
@endsection
