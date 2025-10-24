@extends('layouts.admin')

@section('title', 'Modifier le Service')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Modifier le Service</h1>
        <p class="text-gray-600 mt-2">Modifiez les informations de votre service</p>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <h4 class="font-bold">Erreurs de validation :</h4>
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('services.admin.update', $service['id'] ?? $loop->index ?? 0) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nom du service -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nom du Service *
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $service['name'] ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description courte -->
                <div class="md:col-span-2">
                    <label for="short_description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description Courte *
                    </label>
                    <textarea id="short_description" 
                              name="short_description" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                              required>{{ old('short_description', $service['short_description'] ?? '') }}</textarea>
                    @error('short_description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description complète -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description Complète *
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="8"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                              required>{{ old('description', $service['description'] ?? '') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Icône -->
                <div>
                    <label for="icon" class="block text-sm font-medium text-gray-700 mb-2">
                        Icône Font Awesome
                    </label>
                    <input type="text" 
                           id="icon" 
                           name="icon" 
                           value="{{ old('icon', $service['icon'] ?? 'fas fa-tools') }}"
                           placeholder="fas fa-tools"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('icon')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Image mise en avant -->
                <div>
                    <label for="featured_image" class="block text-sm font-medium text-gray-700 mb-2">
                        Image Mise en Avant
                    </label>
                    <input type="file" 
                           id="featured_image" 
                           name="featured_image" 
                           accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @if(!empty($service['featured_image']))
                    <div class="mt-2">
                        <img src="{{ asset($service['featured_image']) }}" alt="Image actuelle" class="w-32 h-20 object-cover rounded">
                        <p class="text-sm text-gray-500 mt-1">Image actuelle</p>
                    </div>
                    @endif
                    @error('featured_image')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Options -->
                <div class="md:col-span-2">
                    <div class="flex flex-wrap gap-4">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="is_featured" 
                                   value="1"
                                   {{ old('is_featured', $service['is_featured'] ?? false) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Service mis en avant</span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="is_menu" 
                                   value="1"
                                   {{ old('is_menu', $service['is_menu'] ?? false) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Afficher dans le menu</span>
                        </label>
                    </div>
                </div>

                <!-- SEO -->
                <div class="md:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Optimisation SEO</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-2">
                                Titre SEO
                            </label>
                            <input type="text" 
                                   id="meta_title" 
                                   name="meta_title" 
                                   value="{{ old('meta_title', $service['meta_title'] ?? '') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-sm text-gray-500 mt-1">Titre optimisé pour les moteurs de recherche (max 60 caractères)</p>
                            @error('meta_title')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description SEO
                            </label>
                            <textarea id="meta_description" 
                                      name="meta_description" 
                                      rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('meta_description', $service['meta_description'] ?? '') }}</textarea>
                            <p class="text-sm text-gray-500 mt-1">Description pour les moteurs de recherche (max 160 caractères)</p>
                            @error('meta_description')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="meta_keywords" class="block text-sm font-medium text-gray-700 mb-2">
                            Mots-clés SEO
                        </label>
                        <input type="text" 
                               id="meta_keywords" 
                               name="meta_keywords" 
                               value="{{ old('meta_keywords', $service['meta_keywords'] ?? '') }}"
                               placeholder="demoussage, toiture, rénovation, devis gratuit, professionnel..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-sm text-gray-500 mt-1">Mots-clés séparés par des virgules (générés automatiquement par l'IA)</p>
                        @error('meta_keywords')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Open Graph -->
                <div class="md:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Réseaux Sociaux (Open Graph)</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="og_title" class="block text-sm font-medium text-gray-700 mb-2">
                                Titre Open Graph
                            </label>
                            <input type="text" 
                                   id="og_title" 
                                   name="og_title" 
                                   value="{{ old('og_title', $service['og_title'] ?? '') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-sm text-gray-500 mt-1">Titre optimisé pour Facebook/LinkedIn</p>
                            @error('og_title')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="og_description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description Open Graph
                            </label>
                            <textarea id="og_description" 
                                      name="og_description" 
                                      rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('og_description', $service['og_description'] ?? '') }}</textarea>
                            <p class="text-sm text-gray-500 mt-1">Description pour les réseaux sociaux</p>
                            @error('og_description')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="og_image" class="block text-sm font-medium text-gray-700 mb-2">
                            Image Open Graph
                        </label>
                        <input type="file" 
                               id="og_image" 
                               name="og_image" 
                               accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @if(!empty($service['og_image']))
                        <div class="mt-2">
                            <img src="{{ asset($service['og_image']) }}" alt="Image OG actuelle" class="w-32 h-20 object-cover rounded">
                            <p class="text-sm text-gray-500 mt-1">Image Open Graph actuelle</p>
                        </div>
                        @endif
                        <p class="text-sm text-gray-500 mt-1">Image pour le partage sur les réseaux sociaux (1200x630px recommandé)</p>
                        @error('og_image')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Twitter Cards -->
                <div class="md:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Twitter Cards</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="twitter_title" class="block text-sm font-medium text-gray-700 mb-2">
                                Titre Twitter
                            </label>
                            <input type="text" 
                                   id="twitter_title" 
                                   name="twitter_title" 
                                   value="{{ old('twitter_title', $service['twitter_title'] ?? '') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-sm text-gray-500 mt-1">Titre pour Twitter (optionnel, utilise Open Graph si vide)</p>
                            @error('twitter_title')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="twitter_description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description Twitter
                            </label>
                            <textarea id="twitter_description" 
                                      name="twitter_description" 
                                      rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('twitter_description', $service['twitter_description'] ?? '') }}</textarea>
                            <p class="text-sm text-gray-500 mt-1">Description pour Twitter (optionnel, utilise Open Graph si vide)</p>
                            @error('twitter_description')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('services.admin.index') }}" 
                   class="px-4 py-2 text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors">
                    Annuler
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    Mettre à jour
                </button>
            </div>
        </div>
    </form>
</div>
@endsection