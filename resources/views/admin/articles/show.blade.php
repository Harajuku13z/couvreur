@extends('layouts.admin')

@section('title', $article->title)

@section('head')
<style>
/* Styles pour le contenu généré par ChatGPT avec Tailwind CSS */
.article-content {
    line-height: 1.7;
    color: #374151;
}

/* S'assurer que le contenu Tailwind s'affiche correctement */
.article-content .max-w-7xl {
    max-width: 80rem;
}

.article-content .text-4xl {
    font-size: 2.25rem;
    line-height: 2.5rem;
}

.article-content .text-2xl {
    font-size: 1.5rem;
    line-height: 2rem;
}

.article-content .text-xl {
    font-size: 1.25rem;
    line-height: 1.75rem;
}

.article-content .bg-white {
    background-color: #ffffff;
}

.article-content .bg-green-50 {
    background-color: #f0fdf4;
}

.article-content .bg-blue-50 {
    background-color: #eff6ff;
}

.article-content .rounded-xl {
    border-radius: 0.75rem;
}

.article-content .shadow {
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

.article-content .hover\:shadow-lg:hover {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.article-content .transition {
    transition-property: color, background-color, border-color, text-decoration-color, fill, stroke;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

.article-content .duration-300 {
    transition-duration: 300ms;
}

.article-content .text-gray-900 {
    color: #111827;
}

.article-content .text-gray-800 {
    color: #1f2937;
}

.article-content .text-gray-700 {
    color: #374151;
}

.article-content .text-blue-500 {
    color: #3b82f6;
}

.article-content .text-white {
    color: #ffffff;
}

.article-content .bg-blue-500 {
    background-color: #3b82f6;
}

.article-content .hover\:bg-blue-600:hover {
    background-color: #2563eb;
}

.article-content .font-bold {
    font-weight: 700;
}

.article-content .font-semibold {
    font-weight: 600;
}

.article-content .mb-2 {
    margin-bottom: 0.5rem;
}

.article-content .mb-4 {
    margin-bottom: 1rem;
}

.article-content .mb-6 {
    margin-bottom: 1.5rem;
}

.article-content .my-4 {
    margin-top: 1rem;
    margin-bottom: 1rem;
}

.article-content .p-4 {
    padding: 1rem;
}

.article-content .p-6 {
    padding: 1.5rem;
}

.article-content .px-6 {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
}

.article-content .py-3 {
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
}

.article-content .rounded-lg {
    border-radius: 0.5rem;
}

.article-content .inline-block {
    display: inline-block;
}

.article-content .text-center {
    text-align: center;
}

.article-content .list-disc {
    list-style-type: disc;
}

.article-content .list-inside {
    list-style-position: inside;
}

.article-content .list-decimal {
    list-style-type: decimal;
}
</style>
@endsection

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
            <div class="article-content">
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
