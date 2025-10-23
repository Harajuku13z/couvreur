@extends('layouts.admin')

@section('title', 'Articles')

@section('content')
<div class="max-w-6xl mx-auto py-10">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Articles</h1>
        <div class="flex space-x-3">
            <a href="{{ route('admin.articles.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>Nouvel Article
            </a>
            <a href="{{ route('admin.articles.generate') }}" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                <i class="fas fa-robot mr-2"></i>Générer avec IA
            </a>
            <form method="POST" action="{{ route('admin.articles.destroy-all') }}" class="inline" onsubmit="return confirm('⚠️ ATTENTION: Cette action supprimera TOUS les articles définitivement. Êtes-vous sûr de vouloir continuer ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                    <i class="fas fa-trash-alt mr-2"></i>Supprimer tout
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 text-green-700 rounded">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-3 bg-red-50 text-red-700 rounded">{{ session('error') }}</div>
    @endif

    @if(session('info'))
        <div class="mb-4 p-3 bg-blue-50 text-blue-700 rounded">{{ session('info') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Liste des Articles</h3>
        </div>
        <div class="divide-y divide-gray-200">
            @forelse($articles as $article)
                <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50">
                    <div class="flex-1">
                        <h4 class="text-lg font-medium text-gray-900">{{ $article->title }}</h4>
                        <p class="text-sm text-gray-500">{{ $article->slug }}</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                            @if($article->status === 'published') bg-green-100 text-green-800
                            @elseif($article->status === 'draft') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($article->status) }}
                        </span>
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.articles.show', $article) }}" class="text-blue-600 hover:text-blue-900 p-2" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.articles.edit', $article) }}" class="text-green-600 hover:text-green-900 p-2" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 p-2" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center">
                    <p class="text-gray-500">Aucun article trouvé.</p>
                    <a href="{{ route('admin.articles.create') }}" class="text-blue-600 hover:text-blue-800">Créer le premier article</a>
                </div>
            @endforelse
        </div>
    </div>

    @if($articles->hasPages())
        <div class="mt-6">
            {{ $articles->links() }}
        </div>
    @endif
</div>
@endsection
