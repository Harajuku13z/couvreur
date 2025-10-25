@extends('layouts.admin')

@section('title', 'Modifier un Avis')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Modifier un Avis</h1>
            <p class="text-gray-600 mt-2">Modifiez les informations de cet avis</p>
        </div>
        <a href="{{ route('admin.reviews.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
            <i class="fas fa-arrow-left mr-2"></i>Retour aux Avis
        </a>
    </div>

    <!-- Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('admin.reviews.update', $review->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="space-y-6">
                    <div>
                        <label for="author_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nom de l'auteur *
                        </label>
                        <input type="text" 
                               id="author_name" 
                               name="author_name" 
                               value="{{ old('author_name', $review->author_name) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Ex: Jean Dupont"
                               required>
                    </div>

                    <div>
                        <label for="author_photo" class="block text-sm font-medium text-gray-700 mb-2">
                            Photo de profil (URL)
                        </label>
                        <input type="url" 
                               id="author_photo" 
                               name="author_photo" 
                               value="{{ old('author_photo', $review->author_photo) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="https://example.com/photo.jpg">
                        <p class="text-sm text-gray-500 mt-1">URL de la photo de profil (optionnel)</p>
                    </div>

                    <div>
                        <label for="author_link" class="block text-sm font-medium text-gray-700 mb-2">
                            Lien du profil
                        </label>
                        <input type="url" 
                               id="author_link" 
                               name="author_link" 
                               value="{{ old('author_link', $review->author_link) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="https://google.com/maps/contrib/...">
                        <p class="text-sm text-gray-500 mt-1">Lien vers le profil de l'auteur (optionnel)</p>
                    </div>

                    <div>
                        <label for="rating" class="block text-sm font-medium text-gray-700 mb-2">
                            Note (1-5 étoiles) *
                        </label>
                        <select id="rating" 
                                name="rating" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                            <option value="">Sélectionnez une note</option>
                            <option value="1" {{ old('rating', $review->rating) == '1' ? 'selected' : '' }}>⭐ (1 étoile)</option>
                            <option value="2" {{ old('rating', $review->rating) == '2' ? 'selected' : '' }}>⭐⭐ (2 étoiles)</option>
                            <option value="3" {{ old('rating', $review->rating) == '3' ? 'selected' : '' }}>⭐⭐⭐ (3 étoiles)</option>
                            <option value="4" {{ old('rating', $review->rating) == '4' ? 'selected' : '' }}>⭐⭐⭐⭐ (4 étoiles)</option>
                            <option value="5" {{ old('rating', $review->rating) == '5' ? 'selected' : '' }}>⭐⭐⭐⭐⭐ (5 étoiles)</option>
                        </select>
                    </div>

                    <div>
                        <label for="review_text" class="block text-sm font-medium text-gray-700 mb-2">
                            Texte de l'avis *
                        </label>
                        <textarea id="review_text" 
                                  name="review_text" 
                                  rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Ex: Excellent service, je recommande vivement !"
                                  required>{{ old('review_text', $review->review_text) }}</textarea>
                    </div>

                    <div>
                        <label for="review_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Date de l'avis *
                        </label>
                        <input type="date" 
                               id="review_date" 
                               name="review_date" 
                               value="{{ old('review_date', $review->review_date->format('Y-m-d')) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               required>
                    </div>

                    <div>
                        <label for="source" class="block text-sm font-medium text-gray-700 mb-2">
                            Plateforme *
                        </label>
                        <select id="source" 
                                name="source" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                            <option value="">Sélectionnez une plateforme</option>
                            <option value="Google Maps" {{ old('source', $review->source) == 'Google Maps' ? 'selected' : '' }}>Google Maps</option>
                            <option value="Travaux.com" {{ old('source', $review->source) == 'Travaux.com' ? 'selected' : '' }}>Travaux.com</option>
                            <option value="LeBonCoin" {{ old('source', $review->source) == 'LeBonCoin' ? 'selected' : '' }}>LeBonCoin</option>
                            <option value="Trustpilot" {{ old('source', $review->source) == 'Trustpilot' ? 'selected' : '' }}>Trustpilot</option>
                            <option value="Yelp" {{ old('source', $review->source) == 'Yelp' ? 'selected' : '' }}>Yelp</option>
                            <option value="Facebook" {{ old('source', $review->source) == 'Facebook' ? 'selected' : '' }}>Facebook</option>
                            <option value="Autre" {{ old('source', $review->source) == 'Autre' ? 'selected' : '' }}>Autre</option>
                        </select>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', $review->is_active) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-700">
                            Activer cet avis (visible sur le site)
                        </label>
                    </div>

                    <div class="flex space-x-4">
                        <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-save mr-2"></i>Mettre à jour l'Avis
                        </button>
                        <a href="{{ route('admin.reviews.index') }}" class="flex-1 bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition text-center">
                            <i class="fas fa-times mr-2"></i>Annuler
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
