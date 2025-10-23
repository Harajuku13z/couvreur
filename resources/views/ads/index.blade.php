@extends('layouts.app')

@section('title', 'Nos Annonces')

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
                            <span class="mr-4">📅 {{ $ad->published_at->format('d/m/Y') }}</span>
                        </div>
                        
                        <h2 class="text-xl font-bold text-gray-900 mb-3">
                            <a href="{{ route('ads.show', $ad) }}" class="hover:text-blue-600">
                                {{ $ad->title }}
                            </a>
                        </h2>
                        
                        @if($ad->meta_description)
                            <p class="text-gray-600 mb-4">{{ Str::limit($ad->meta_description, 120) }}</p>
                        @endif
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">{{ $ad->keyword }}</span>
                            <a href="{{ route('ads.show', $ad) }}" class="text-blue-600 hover:text-blue-800 font-semibold">
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
