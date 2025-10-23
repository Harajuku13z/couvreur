@extends('layouts.admin')

@section('title', 'Créer un Article')

@section('content')
<div class="max-w-4xl mx-auto py-10">
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Créer un Article</h1>
        <p class="text-gray-600 mt-2">Créez un nouvel article avec le contenu HTML de ChatGPT</p>
    </div>

    <form method="POST" action="{{ route('admin.articles.store') }}" class="space-y-6">
        @csrf
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Titre de l'article</label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    @error('title')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                    <select id="status" name="status" 
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Brouillon</option>
                        <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Publié</option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <label for="content_html" class="block text-sm font-medium text-gray-700 mb-2">Contenu HTML (ChatGPT)</label>
                <textarea id="content_html" name="content_html" rows="20" 
                          class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm" 
                          placeholder="Collez ici le contenu HTML généré par ChatGPT..." required>{{ old('content_html') }}</textarea>
                <p class="text-sm text-gray-500 mt-1">Le contenu HTML sera enregistré tel quel, sans modification.</p>
                @error('content_html')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-2">Meta Title (optionnel)</label>
                    <input type="text" id="meta_title" name="meta_title" value="{{ old('meta_title') }}" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('meta_title')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="featured_image" class="block text-sm font-medium text-gray-700 mb-2">Image mise en avant (URL)</label>
                    <input type="url" id="featured_image" name="featured_image" value="{{ old('featured_image') }}" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('featured_image')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-2">Meta Description (optionnel)</label>
                <textarea id="meta_description" name="meta_description" rows="3" 
                          class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('meta_description') }}</textarea>
                @error('meta_description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6">
                <label for="meta_keywords" class="block text-sm font-medium text-gray-700 mb-2">Meta Keywords (optionnel)</label>
                <input type="text" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords') }}" 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="mot-clé1, mot-clé2, mot-clé3">
                @error('meta_keywords')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex justify-end space-x-4">
            <a href="{{ route('admin.articles.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                Annuler
            </a>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Créer l'Article
            </button>
        </div>
    </form>
</div>
@endsection
