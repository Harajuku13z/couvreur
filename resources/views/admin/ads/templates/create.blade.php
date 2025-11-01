@extends('layouts.admin')

@section('title', 'Créer un Template')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- En-tête -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <div class="flex items-center mb-2">
                <a href="{{ route('admin.ads.templates.index') }}" class="text-blue-600 hover:text-blue-800 mr-4">
                    <i class="fas fa-arrow-left"></i> Retour aux templates
                </a>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">Créer un Template</h1>
            <p class="text-gray-600 mt-2">Créez un template manuellement, puis générez des annonces personnalisées via IA</p>
        </div>
    </div>

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Formulaire de création -->
    <form action="{{ route('admin.ads.templates.store') }}" method="POST" enctype="multipart/form-data" id="templateCreateForm">
        @csrf

        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Informations Générales</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nom du template -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nom du Template <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                        maxlength="255"
                        placeholder="Ex: Rénovation de toiture"
                    >
                </div>

                <!-- Nom du service -->
                <div>
                    <label for="service_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nom du Service <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="service_name" 
                        name="service_name" 
                        value="{{ old('service_name') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                        maxlength="255"
                        placeholder="Ex: Rénovation de toiture"
                    >
                </div>

                <!-- Slug du service -->
                <div>
                    <label for="service_slug" class="block text-sm font-medium text-gray-700 mb-2">
                        Slug du Service <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="service_slug" 
                        name="service_slug" 
                        value="{{ old('service_slug') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                        maxlength="255"
                        placeholder="Ex: renovation-toiture"
                    >
                    <p class="text-xs text-gray-500 mt-1">URL-friendly (minuscules, tirets)</p>
                </div>

                <!-- Icône -->
                <div>
                    <label for="icon" class="block text-sm font-medium text-gray-700 mb-2">
                        Icône Font Awesome
                    </label>
                    <input 
                        type="text" 
                        id="icon" 
                        name="icon" 
                        value="{{ old('icon', 'fas fa-tools') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        maxlength="50"
                        placeholder="fas fa-tools"
                    >
                    <p class="text-xs text-gray-500 mt-1">Ex: fas fa-tools, fas fa-home</p>
                </div>

                <!-- Image de mise en avant -->
                <div class="md:col-span-2">
                    <label for="featured_image" class="block text-sm font-medium text-gray-700 mb-2">
                        Image de mise en avant
                    </label>
                    <input 
                        type="file" 
                        id="featured_image" 
                        name="featured_image" 
                        accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                    <p class="text-xs text-gray-500 mt-1">Formats acceptés: JPEG, PNG, GIF, WebP (max 5MB)</p>
                </div>

                <!-- Description courte -->
                <div>
                    <label for="short_description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description courte <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        id="short_description" 
                        name="short_description" 
                        rows="3" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                        maxlength="500"
                        placeholder="Description courte du service..."
                    >{{ old('short_description') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Maximum 500 caractères</p>
                </div>

                <!-- Description longue -->
                <div>
                    <label for="long_description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description longue <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        id="long_description" 
                        name="long_description" 
                        rows="3" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                        maxlength="2000"
                        placeholder="Description détaillée du service..."
                    >{{ old('long_description') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Maximum 2000 caractères</p>
                </div>
            </div>
        </div>

        <!-- Contenu HTML -->
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Contenu HTML</h2>
            <div>
                <label for="content_html" class="block text-sm font-medium text-gray-700 mb-2">
                    Contenu HTML <span class="text-red-500">*</span>
                    <span class="text-xs text-gray-500 font-normal ml-2">(Utilisez [VILLE], [RÉGION], [DÉPARTEMENT] comme placeholders)</span>
                </label>
                <textarea 
                    id="content_html" 
                    name="content_html" 
                    rows="15" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm"
                    required
                    placeholder="Contenu HTML avec placeholders [VILLE], [RÉGION], [DÉPARTEMENT]..."
                >{{ old('content_html') }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Le contenu sera utilisé pour générer des annonces avec remplacement automatique des placeholders</p>
            </div>
        </div>

        <!-- SEO -->
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Métadonnées SEO</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Meta Title -->
                <div>
                    <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-2">
                        Titre SEO <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="meta_title" 
                        name="meta_title" 
                        value="{{ old('meta_title') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                        maxlength="160"
                        placeholder="Titre optimisé SEO"
                    >
                    <p class="text-xs text-gray-500 mt-1">Maximum 160 caractères</p>
                </div>

                <!-- Meta Description -->
                <div>
                    <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description SEO <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        id="meta_description" 
                        name="meta_description" 
                        rows="3" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                        maxlength="500"
                        placeholder="Description optimisée SEO..."
                    >{{ old('meta_description') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Maximum 500 caractères</p>
                </div>

                <!-- Meta Keywords -->
                <div>
                    <label for="meta_keywords" class="block text-sm font-medium text-gray-700 mb-2">
                        Mots-clés SEO
                    </label>
                    <input 
                        type="text" 
                        id="meta_keywords" 
                        name="meta_keywords" 
                        value="{{ old('meta_keywords') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        maxlength="500"
                        placeholder="mot-clé1, mot-clé2, mot-clé3"
                    >
                </div>

                <!-- OG Title -->
                <div>
                    <label for="og_title" class="block text-sm font-medium text-gray-700 mb-2">
                        Titre Open Graph
                    </label>
                    <input 
                        type="text" 
                        id="og_title" 
                        name="og_title" 
                        value="{{ old('og_title') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        maxlength="160"
                        placeholder="Titre pour partage social"
                    >
                </div>

                <!-- OG Description -->
                <div>
                    <label for="og_description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description Open Graph
                    </label>
                    <textarea 
                        id="og_description" 
                        name="og_description" 
                        rows="3" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        maxlength="500"
                        placeholder="Description pour partage social..."
                    >{{ old('og_description') }}</textarea>
                </div>

                <!-- Twitter Title -->
                <div>
                    <label for="twitter_title" class="block text-sm font-medium text-gray-700 mb-2">
                        Titre Twitter
                    </label>
                    <input 
                        type="text" 
                        id="twitter_title" 
                        name="twitter_title" 
                        value="{{ old('twitter_title') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        maxlength="160"
                        placeholder="Titre pour Twitter"
                    >
                </div>

                <!-- Twitter Description -->
                <div>
                    <label for="twitter_description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description Twitter
                    </label>
                    <textarea 
                        id="twitter_description" 
                        name="twitter_description" 
                        rows="3" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        maxlength="500"
                        placeholder="Description pour Twitter..."
                    >{{ old('twitter_description') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-between items-center">
            <a href="{{ route('admin.ads.templates.index') }}" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-times mr-2"></i>Annuler
            </a>
            
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                <i class="fas fa-save"></i>
                <span>Créer le Template</span>
            </button>
        </div>
    </form>
</div>
@endsection

