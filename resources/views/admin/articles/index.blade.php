@extends('layouts.admin')

@section('title', 'Articles')

@section('content')
<div class="max-w-6xl mx-auto p-4 md:py-10">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h1 class="text-2xl md:text-3xl font-bold">Articles</h1>
        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
            <a href="{{ route('admin.articles.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-center">
                <i class="fas fa-plus mr-2"></i>Nouvel Article
            </a>
            <a href="{{ route('admin.articles.ai.form') }}" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 text-center">
                <i class="fas fa-robot mr-2"></i>Générer avec IA
            </a>
            <form method="POST" action="{{ route('admin.articles.destroy-all') }}" class="inline w-full sm:w-auto" onsubmit="return confirm('⚠️ ATTENTION: Cette action supprimera TOUS les articles définitivement. Êtes-vous sûr de vouloir continuer ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 w-full sm:w-auto text-center">
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
        <div class="px-4 md:px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Liste des Articles</h3>
        </div>
        <div class="divide-y divide-gray-200">
            @forelse($articles as $article)
                <div class="px-4 md:px-6 py-4 hover:bg-gray-50">
                    <!-- Ligne 1: Titre -->
                    <div class="mb-2">
                        <h4 class="text-base md:text-lg font-medium text-gray-900">{{ $article->title }}</h4>
                    </div>
                    <!-- Ligne 2: Slug et Date de création -->
                    <div class="mb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <p class="text-xs md:text-sm text-gray-500 break-all">{{ $article->slug }}</p>
                        <p class="text-xs md:text-sm text-gray-400">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            Créé le {{ $article->created_at->format('d/m/Y à H:i') }}
                        </p>
                    </div>
                    <!-- Ligne 3: Statut et Actions -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3">
                        <div class="flex items-center">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                @if($article->status === 'published') bg-green-100 text-green-800
                                @elseif($article->status === 'draft') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($article->status) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 sm:gap-0 sm:space-x-2">
                            <a href="{{ route('admin.articles.show', $article) }}" class="text-blue-600 hover:text-blue-900 p-2 rounded hover:bg-blue-50" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.articles.edit', $article) }}" class="text-green-600 hover:text-green-900 p-2 rounded hover:bg-green-50" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 p-2 rounded hover:bg-red-50" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 md:px-6 py-8 text-center">
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
