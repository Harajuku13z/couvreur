@extends('layouts.admin')

@section('title', 'Ajouter un Avis Manuel')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Ajouter un Avis Manuel</h1>
            <p class="text-gray-600 mt-2">Ajoutez un avis depuis n'importe quelle plateforme</p>
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
            <form action="{{ route('admin.reviews.store') }}" method="POST">
                @csrf
                
                <div class="space-y-6">
                    <div>
                        <label for="author_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nom de l'auteur *
                        </label>
                        <input type="text" 
                               id="author_name" 
                               name="author_name" 
                               value="{{ old('author_name') }}"
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
                               value="{{ old('author_photo') }}"
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
                               value="{{ old('author_link') }}"
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
                            <option value="1" {{ old('rating') == '1' ? 'selected' : '' }}>⭐ (1 étoile)</option>
                            <option value="2" {{ old('rating') == '2' ? 'selected' : '' }}>⭐⭐ (2 étoiles)</option>
                            <option value="3" {{ old('rating') == '3' ? 'selected' : '' }}>⭐⭐⭐ (3 étoiles)</option>
                            <option value="4" {{ old('rating') == '4' ? 'selected' : '' }}>⭐⭐⭐⭐ (4 étoiles)</option>
                            <option value="5" {{ old('rating') == '5' ? 'selected' : '' }}>⭐⭐⭐⭐⭐ (5 étoiles)</option>
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
                                  required>{{ old('review_text') }}</textarea>
                    </div>

                    <div>
                        <label for="video_url" class="block text-sm font-medium text-gray-700 mb-2">
                            URL Vidéo (YouTube ou Vimeo)
                        </label>
                        <input type="url" 
                               id="video_url" 
                               name="video_url" 
                               value="{{ old('video_url') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="https://www.youtube.com/watch?v=... ou https://vimeo.com/...">
                        <p class="text-sm text-gray-500 mt-1">Collez l'URL complète d'une vidéo YouTube ou Vimeo (optionnel)</p>
                    </div>

                    <div>
                        <label for="review_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Date de l'avis *
                        </label>
                        <input type="date" 
                               id="review_date" 
                               name="review_date" 
                               value="{{ old('review_date', date('Y-m-d')) }}"
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
                            <option value="Google Maps" {{ old('source') == 'Google Maps' ? 'selected' : '' }}>Google Maps</option>
                            <option value="Travaux.com" {{ old('source') == 'Travaux.com' ? 'selected' : '' }}>Travaux.com</option>
                            <option value="LeBonCoin" {{ old('source') == 'LeBonCoin' ? 'selected' : '' }}>LeBonCoin</option>
                            <option value="Trustpilot" {{ old('source') == 'Trustpilot' ? 'selected' : '' }}>Trustpilot</option>
                            <option value="Yelp" {{ old('source') == 'Yelp' ? 'selected' : '' }}>Yelp</option>
                            <option value="Facebook" {{ old('source') == 'Facebook' ? 'selected' : '' }}>Facebook</option>
                            <option value="Autre" {{ old('source') == 'Autre' ? 'selected' : '' }}>Autre</option>
                        </select>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-700">
                            Activer cet avis (visible sur le site)
                        </label>
                    </div>

                    <div class="flex space-x-4">
                        <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-save mr-2"></i>Ajouter l'Avis
                        </button>
                        <a href="{{ route('admin.reviews.index') }}" class="flex-1 bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition text-center">
                            <i class="fas fa-times mr-2"></i>Annuler
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Aide -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-600 mr-3 mt-1"></i>
                <div>
                    <h3 class="text-sm font-medium text-blue-800">Plateformes supportées</h3>
                    <ul class="text-sm text-blue-700 mt-2 space-y-1">
                        <li>• <strong>Google Maps</strong> : Avis Google officiels</li>
                        <li>• <strong>Travaux.com</strong> : Plateforme française de travaux</li>
                        <li>• <strong>LeBonCoin</strong> : Avis clients LeBonCoin</li>
                        <li>• <strong>Trustpilot</strong> : Plateforme internationale d'avis</li>
                        <li>• <strong>Yelp</strong> : Avis et recommandations</li>
                        <li>• <strong>Facebook</strong> : Avis Facebook Business</li>
                        <li>• <strong>Autre</strong> : Toute autre plateforme</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection