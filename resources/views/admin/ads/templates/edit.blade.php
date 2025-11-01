@extends('layouts.admin')

@section('title', 'Personnaliser le Template: ' . $template->name)

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
            <h1 class="text-3xl font-bold text-gray-900">Personnaliser le Template</h1>
            <p class="text-gray-600 mt-2">{{ $template->service_name }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('warning'))
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline">{{ session('warning') }}</span>
        </div>
    @endif

    <!-- Formulaire d'édition -->
    <form action="{{ route('admin.ads.templates.update', $template->id) }}" method="POST" id="templateEditForm">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Contenu Principal</h2>
            
            <div class="space-y-6">
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
                    >{{ old('short_description', $template->short_description) }}</textarea>
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
                        rows="5" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                        maxlength="2000"
                    >{{ old('long_description', $template->long_description) }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Maximum 2000 caractères</p>
                </div>

                <!-- Contenu HTML -->
                <div>
                    <label for="content_html" class="block text-sm font-medium text-gray-700 mb-2">
                        Contenu HTML <span class="text-red-500">*</span>
                        <span class="text-xs text-gray-500 font-normal ml-2">(Utilisez [VILLE] et [RÉGION] comme placeholders)</span>
                    </label>
                    <textarea 
                        id="content_html" 
                        name="content_html" 
                        rows="15" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm"
                        required
                    >{{ old('content_html', $template->content_html) }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Le contenu sera utilisé pour générer les annonces avec remplacement automatique de [VILLE] et [RÉGION]</p>
                </div>
            </div>
        </div>

        <!-- SEO -->
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Référencement (SEO)</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Meta Title -->
                <div>
                    <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-2">
                        Meta Title <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="meta_title" 
                        name="meta_title" 
                        value="{{ old('meta_title', $template->meta_title) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                        maxlength="160"
                    >
                    <p class="text-xs text-gray-500 mt-1">Maximum 160 caractères</p>
                </div>

                <!-- Meta Description -->
                <div>
                    <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-2">
                        Meta Description <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        id="meta_description" 
                        name="meta_description" 
                        rows="3" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                        maxlength="500"
                    >{{ old('meta_description', $template->meta_description) }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Maximum 500 caractères</p>
                </div>

                <!-- Meta Keywords -->
                <div>
                    <label for="meta_keywords" class="block text-sm font-medium text-gray-700 mb-2">
                        Meta Keywords
                    </label>
                    <input 
                        type="text" 
                        id="meta_keywords" 
                        name="meta_keywords" 
                        value="{{ old('meta_keywords', $template->meta_keywords) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        maxlength="500"
                    >
                    <p class="text-xs text-gray-500 mt-1">Mots-clés séparés par des virgules</p>
                </div>

                <!-- Icon -->
                <div>
                    <label for="icon" class="block text-sm font-medium text-gray-700 mb-2">
                        Icône Font Awesome
                    </label>
                    <input 
                        type="text" 
                        id="icon" 
                        name="icon" 
                        value="{{ old('icon', $template->icon) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="fas fa-tools"
                        maxlength="50"
                    >
                    <p class="text-xs text-gray-500 mt-1">Ex: fas fa-tools, fas fa-home</p>
                </div>
            </div>
        </div>

        <!-- Open Graph & Twitter -->
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Réseaux Sociaux (Open Graph & Twitter)</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- OG Title -->
                <div>
                    <label for="og_title" class="block text-sm font-medium text-gray-700 mb-2">
                        OG Title
                    </label>
                    <input 
                        type="text" 
                        id="og_title" 
                        name="og_title" 
                        value="{{ old('og_title', $template->og_title) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        maxlength="160"
                    >
                </div>

                <!-- OG Description -->
                <div>
                    <label for="og_description" class="block text-sm font-medium text-gray-700 mb-2">
                        OG Description
                    </label>
                    <textarea 
                        id="og_description" 
                        name="og_description" 
                        rows="3" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        maxlength="500"
                    >{{ old('og_description', $template->og_description) }}</textarea>
                </div>

                <!-- Twitter Title -->
                <div>
                    <label for="twitter_title" class="block text-sm font-medium text-gray-700 mb-2">
                        Twitter Title
                    </label>
                    <input 
                        type="text" 
                        id="twitter_title" 
                        name="twitter_title" 
                        value="{{ old('twitter_title', $template->twitter_title) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        maxlength="160"
                    >
                </div>

                <!-- Twitter Description -->
                <div>
                    <label for="twitter_description" class="block text-sm font-medium text-gray-700 mb-2">
                        Twitter Description
                    </label>
                    <textarea 
                        id="twitter_description" 
                        name="twitter_description" 
                        rows="3" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        maxlength="500"
                    >{{ old('twitter_description', $template->twitter_description) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Aperçu -->
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Aperçu du Contenu</h2>
            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                <div class="prose max-w-none" id="contentPreview">
                    {!! str_replace(['[VILLE]', '[RÉGION]'], ['Paris', 'Île-de-France'], old('content_html', $template->content_html)) !!}
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-between items-center">
            <a href="{{ route('admin.ads.templates.index') }}" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-times mr-2"></i>Annuler
            </a>
            
            <div class="flex space-x-4">
                <button 
                    type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors duration-200 flex items-center space-x-2"
                >
                    <i class="fas fa-save"></i>
                    <span>Sauvegarder et Continuer</span>
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Mise à jour de l'aperçu en temps réel
    document.getElementById('content_html').addEventListener('input', function() {
        const content = this.value;
        const preview = document.getElementById('contentPreview');
        preview.innerHTML = content.replace(/\[VILLE\]/g, 'Paris').replace(/\[RÉGION\]/g, 'Île-de-France');
    });
</script>
@endpush
@endsection

