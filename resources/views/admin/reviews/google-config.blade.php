@extends('layouts.admin')

@section('title', 'Configuration Google Reviews')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fab fa-google text-blue-600 mr-2"></i>Configuration Google Reviews
            </h1>
            <p class="text-gray-600 mt-1">Configurez l'import automatique des avis Google</p>
        </div>
        <a href="{{ route('admin.reviews.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>Retour aux Avis
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fab fa-google mr-2 text-blue-600"></i>Configuration Google
                    </h3>
                </div>
                <div class="p-6">
                    <form action="{{ route('admin.reviews.google.config.save') }}" method="POST">
                        @csrf
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-blue-600 mr-3 mt-1"></i>
                                <div>
                                    <h4 class="font-semibold text-blue-900 mb-1">Configuration requise</h4>
                                    <p class="text-blue-800 text-sm">Vous devez avoir un compte Google Business et une clé API Google avec accès Places API.</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label for="google_place_id" class="block text-sm font-medium text-gray-700 mb-2">Google Place ID *</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('google_place_id') border-red-500 @enderror" 
                                   id="google_place_id" name="google_place_id" 
                                   value="{{ old('google_place_id', $googlePlaceId) }}" 
                                   placeholder="ChIJ..." required>
                            @error('google_place_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-sm text-gray-500 mt-1">
                                <a href="https://developers.google.com/maps/documentation/places/web-service/place-id" 
                                   target="_blank" class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-external-link-alt mr-1"></i>Comment trouver mon Place ID ?
                                </a>
                            </p>
                        </div>

                        <div class="mb-6">
                            <label for="google_api_key" class="block text-sm font-medium text-gray-700 mb-2">Google API Key *</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('google_api_key') border-red-500 @enderror" 
                                   id="google_api_key" name="google_api_key" 
                                   value="{{ old('google_api_key', $googleApiKey) }}" 
                                   placeholder="AIza..." required>
                            @error('google_api_key')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-sm text-gray-500 mt-1">Clé API Google avec accès Places API activé</p>
                        </div>


                        <div class="mb-6">
                            <div class="flex items-start">
                                <input type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1" 
                                       id="auto_approve_google" name="auto_approve_google" value="1" 
                                       {{ old('auto_approve_google', $autoApprove) ? 'checked' : '' }}>
                                <div class="ml-3">
                                    <label for="auto_approve_google" class="block text-sm font-medium text-gray-700">
                                        Approuver automatiquement les avis Google
                                    </label>
                                    <p class="text-sm text-gray-500 mt-1">Les avis importés depuis Google seront automatiquement visibles sur le site</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between">
                            <a href="{{ route('admin.reviews.index') }}" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition flex items-center">
                                <i class="fas fa-times mr-2"></i>Annuler
                            </a>
                            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition flex items-center">
                                <i class="fas fa-save mr-2"></i>Sauvegarder la Configuration
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
                        <i class="fab fa-google mr-2 text-blue-600"></i>Import Google
                    </h3>
                </div>
                <div class="p-6">
                    @if($googlePlaceId && $googleApiKey)
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                            <div class="flex items-start">
                                <i class="fas fa-check-circle text-green-600 mr-3 mt-1"></i>
                                <div>
                                    <h4 class="font-semibold text-green-900 mb-1">Configuration valide</h4>
                                    <p class="text-green-800 text-sm">Vous pouvez importer les avis Google</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            <form action="{{ route('admin.reviews.google.import-auto') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition flex items-center justify-center">
                                    <i class="fas fa-magic mr-2"></i>Import Automatique (Tous les avis)
                                </button>
                            </form>
                            
                            <form action="{{ route('admin.reviews.google.import') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition flex items-center justify-center">
                                    <i class="fab fa-google mr-2"></i>Import Standard (5 avis)
                                </button>
                            </form>
                        </div>
                        
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                            <div class="flex items-start">
                                <i class="fas fa-magic text-green-600 mr-3 mt-1"></i>
                                <div>
                                    <h4 class="font-semibold text-green-900 mb-1">Import Automatique Ultra-Simple</h4>
                                    <p class="text-green-800 text-sm mb-2">
                                        <strong>Nouveau :</strong> L'import automatique essaie plusieurs méthodes pour récupérer le maximum d'avis possible.
                                        Il teste différentes langues et paramètres pour optimiser les résultats.
                                    </p>
                                    <p class="text-green-800 text-sm">
                                        <strong>Simple :</strong> Juste le Place ID et l'API Key, et le système fait tout automatiquement !
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <i class="fas fa-exclamation-triangle text-yellow-600 mr-3 mt-1"></i>
                                <div>
                                    <h4 class="font-semibold text-yellow-900 mb-1">Configuration incomplète</h4>
                                    <p class="text-yellow-800 text-sm">Veuillez configurer le Place ID et l'API Key</p>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <div class="mt-6">
                        <h4 class="font-semibold text-gray-900 mb-3">Étapes pour configurer :</h4>
                        <ol class="text-sm text-gray-600 space-y-2">
                            <li class="flex items-start">
                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded-full mr-3 mt-0.5">1</span>
                                Créer un projet Google Cloud
                            </li>
                            <li class="flex items-start">
                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded-full mr-3 mt-0.5">2</span>
                                Activer Places API
                            </li>
                            <li class="flex items-start">
                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded-full mr-3 mt-0.5">3</span>
                                Créer une clé API
                            </li>
                            <li class="flex items-start">
                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded-full mr-3 mt-0.5">4</span>
                                Trouver votre Place ID
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md mt-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-question-circle mr-2 text-blue-600"></i>Aide
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <a href="https://developers.google.com/maps/documentation/places/web-service/place-id" 
                           target="_blank" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                            <i class="fas fa-external-link-alt text-blue-600 mr-3"></i>
                            <span class="text-sm font-medium text-gray-700">Trouver mon Place ID</span>
                        </a>
                        <a href="https://console.cloud.google.com/" 
                           target="_blank" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                            <i class="fas fa-external-link-alt text-blue-600 mr-3"></i>
                            <span class="text-sm font-medium text-gray-700">Google Cloud Console</span>
                        </a>
                        <a href="https://developers.google.com/maps/documentation/places/web-service" 
                           target="_blank" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                            <i class="fas fa-external-link-alt text-blue-600 mr-3"></i>
                            <span class="text-sm font-medium text-gray-700">Documentation Places API</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection








