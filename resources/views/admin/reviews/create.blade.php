@extends('layouts.admin')

@section('title', 'Ajouter un Avis')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-plus text-green-600 mr-2"></i>Ajouter un Avis
            </h1>
            <p class="text-gray-600 mt-1">Créer un nouvel avis client</p>
        </div>
        <a href="{{ route('admin.reviews.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>Retour
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-star mr-2 text-blue-600"></i>Informations de l'Avis
                    </h3>
                </div>
                <div class="p-6">
                    <form action="{{ route('admin.reviews.store') }}" method="POST">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="author_name" class="block text-sm font-medium text-gray-700 mb-2">Nom du client *</label>
                                <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('author_name') border-red-500 @enderror" 
                                       id="author_name" name="author_name" value="{{ old('author_name') }}" required>
                                @error('author_name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="author_location" class="block text-sm font-medium text-gray-700 mb-2">Localisation</label>
                                <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('author_location') border-red-500 @enderror" 
                                       id="author_location" name="author_location" value="{{ old('author_location') }}">
                                @error('author_location')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Note *</label>
                            <div class="flex space-x-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <input type="radio" id="rating{{ $i }}" name="rating" value="{{ $i }}" 
                                           {{ old('rating', 5) == $i ? 'checked' : '' }} class="hidden">
                                    <label for="rating{{ $i }}" class="cursor-pointer text-3xl text-gray-300 hover:text-yellow-400 transition-colors">
                                        <i class="fas fa-star"></i>
                                    </label>
                                @endfor
                            </div>
                            @error('rating')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="review_text" class="block text-sm font-medium text-gray-700 mb-2">Avis *</label>
                            <textarea class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('review_text') border-red-500 @enderror" 
                                      id="review_text" name="review_text" rows="5" required 
                                      placeholder="Écrivez l'avis du client...">{{ old('review_text') }}</textarea>
                            @error('review_text')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-sm text-gray-500 mt-1">Maximum 1000 caractères</p>
                        </div>

                        <!-- Nouveau champ pour la plateforme -->
                        <div class="mb-6">
                            <label for="source" class="block text-sm font-medium text-gray-700 mb-2">Plateforme</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('source') border-red-500 @enderror" 
                                    id="source" name="source">
                                <option value="manual" {{ old('source', 'manual') == 'manual' ? 'selected' : '' }}>Manuel</option>
                                <option value="google" {{ old('source') == 'google' ? 'selected' : '' }}>Google</option>
                                <option value="facebook" {{ old('source') == 'facebook' ? 'selected' : '' }}>Facebook</option>
                                <option value="travaux.com" {{ old('source') == 'travaux.com' ? 'selected' : '' }}>Travaux.com</option>
                                <option value="pages-jaunes" {{ old('source') == 'pages-jaunes' ? 'selected' : '' }}>Pages Jaunes</option>
                                <option value="trustpilot" {{ old('source') == 'trustpilot' ? 'selected' : '' }}>Trustpilot</option>
                                <option value="site-web" {{ old('source') == 'site-web' ? 'selected' : '' }}>Site Web</option>
                                <option value="autre" {{ old('source') == 'autre' ? 'selected' : '' }}>Autre</option>
                            </select>
                            @error('source')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-sm text-gray-500 mt-1">Sélectionnez la plateforme d'origine de l'avis</p>
                        </div>

                        <div class="mb-6">
                            <div class="flex items-start">
                                <input type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1" 
                                       id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <div class="ml-3">
                                    <label for="is_active" class="block text-sm font-medium text-gray-700">
                                        Activer immédiatement
                                    </label>
                                    <p class="text-sm text-gray-500 mt-1">L'avis sera visible sur le site</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <div class="flex items-start">
                                <input type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1" 
                                       id="is_verified" name="is_verified" value="1" {{ old('is_verified') ? 'checked' : '' }}>
                                <div class="ml-3">
                                    <label for="is_verified" class="block text-sm font-medium text-gray-700">
                                        Avis vérifié
                                    </label>
                                    <p class="text-sm text-gray-500 mt-1">Marquer comme vérifié</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between">
                            <a href="{{ route('admin.reviews.index') }}" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition flex items-center">
                                <i class="fas fa-times mr-2"></i>Annuler
                            </a>
                            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition flex items-center">
                                <i class="fas fa-save mr-2"></i>Créer l'Avis
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-lightbulb mr-2 text-yellow-600"></i>Conseils
                    </h3>
                </div>
                <div class="p-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-600 mr-3 mt-1"></i>
                            <div>
                                <h4 class="font-semibold text-blue-900 mb-1">Conseils pour un bon avis :</h4>
                            </div>
                        </div>
                    </div>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-600 mr-3 mt-1"></i>
                            <span class="text-sm text-gray-700">Soyez authentique et détaillé</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-600 mr-3 mt-1"></i>
                            <span class="text-sm text-gray-700">Mentionnez des aspects spécifiques du service</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-600 mr-3 mt-1"></i>
                            <span class="text-sm text-gray-700">Évitez les avis trop courts ou génériques</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-600 mr-3 mt-1"></i>
                            <span class="text-sm text-gray-700">Utilisez un ton professionnel mais naturel</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ratingInputs = document.querySelectorAll('input[name="rating"]');
    const ratingLabels = document.querySelectorAll('label[for^="rating"]');
    
    ratingInputs.forEach((input, index) => {
        input.addEventListener('change', function() {
            updateStars(parseInt(this.value));
        });
    });
    
    ratingLabels.forEach((label, index) => {
        label.addEventListener('mouseenter', function() {
            updateStars(index + 1);
        });
        
        label.addEventListener('mouseleave', function() {
            const checkedInput = document.querySelector('input[name="rating"]:checked');
            if (checkedInput) {
                updateStars(parseInt(checkedInput.value));
            } else {
                updateStars(0);
            }
        });
    });
    
    function updateStars(rating) {
        ratingLabels.forEach((label, index) => {
            if (index < rating) {
                label.classList.remove('text-gray-300');
                label.classList.add('text-yellow-400');
            } else {
                label.classList.remove('text-yellow-400');
                label.classList.add('text-gray-300');
            }
        });
    }
    
    // Initialiser avec la valeur par défaut
    const defaultInput = document.querySelector('input[name="rating"]:checked');
    if (defaultInput) {
        updateStars(parseInt(defaultInput.value));
    }
});
</script>

@endsection








