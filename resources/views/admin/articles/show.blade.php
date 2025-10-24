@extends('layouts.admin')

@section('title', $article->title)

@section('content')
<div class="max-w-6xl mx-auto py-10">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $article->title }}</h1>
            <div class="flex items-center space-x-4 mt-2">
                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                    @if($article->status === 'published') bg-green-100 text-green-800
                    @elseif($article->status === 'draft') bg-yellow-100 text-yellow-800
                    @else bg-gray-100 text-gray-800 @endif">
                    {{ ucfirst($article->status) }}
                </span>
                <span class="text-sm text-gray-500">Créé le {{ $article->created_at->format('d/m/Y à H:i') }}</span>
                @if($article->published_at)
                    <span class="text-sm text-gray-500">Publié le {{ $article->published_at->format('d/m/Y à H:i') }}</span>
                @endif
            </div>
        </div>
        
        <div class="flex space-x-3">
            <a href="{{ route('admin.articles.edit', $article) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-edit mr-2"></i>Modifier
            </a>
            <a href="{{ route('blog.show', $article) }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700" target="_blank">
                <i class="fas fa-external-link-alt mr-2"></i>Voir
            </a>
            <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                    <i class="fas fa-trash mr-2"></i>Supprimer
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 text-green-700 rounded">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Contenu de l'Article</h3>
        </div>
        <div class="p-6">
            <div class="prose prose-lg max-w-none">
                {!! $article->content_html !!}
            </div>
        </div>
    </div>

    @if($article->meta_title || $article->meta_description || $article->meta_keywords)
    <div class="mt-6 bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Métadonnées SEO</h3>
        </div>
        <div class="p-6 space-y-4">
            @if($article->meta_title)
            <div>
                <label class="block text-sm font-medium text-gray-700">Meta Title</label>
                <p class="text-gray-900">{{ $article->meta_title }}</p>
            </div>
            @endif

            @if($article->meta_description)
            <div>
                <label class="block text-sm font-medium text-gray-700">Meta Description</label>
                <p class="text-gray-900">{{ $article->meta_description }}</p>
            </div>
            @endif

            @if($article->meta_keywords)
            <div>
                <label class="block text-sm font-medium text-gray-700">Meta Keywords</label>
                <p class="text-gray-900">{{ $article->meta_keywords }}</p>
            </div>
            @endif

            @if($article->featured_image)
            <div>
                <label class="block text-sm font-medium text-gray-700">Image mise en avant</label>
                <img src="{{ asset($article->featured_image) }}" alt="Image mise en avant" class="mt-2 w-32 h-20 object-cover rounded">
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
