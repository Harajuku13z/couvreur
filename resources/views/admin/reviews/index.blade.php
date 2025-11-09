@extends('layouts.admin')

@section('title', 'Gestion des Avis')

@section('content')
<div class="container mx-auto px-4 md:px-6 py-4 md:py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 md:mb-8">
        <div>
            <h1 class="text-xl md:text-3xl font-bold text-gray-900">Gestion des Avis</h1>
            <p class="text-gray-600 mt-2 text-sm md:text-base">Gérez les avis clients de votre entreprise</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
            <a href="{{ route('admin.reviews.serp.config') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-center w-full sm:w-auto">
                <i class="fas fa-cog mr-2"></i>Configuration SerpAPI
            </a>
            
            <a href="{{ route('admin.reviews.create') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition text-center w-full sm:w-auto">
                <i class="fas fa-plus mr-2"></i>Ajouter un Avis Manuel
            </a>
        </div>
    </div>

    <!-- Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="text-sm font-medium text-blue-600 uppercase tracking-wide">Total Avis</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
                </div>
                <div class="ml-4">
                    <i class="fas fa-comments text-2xl text-gray-300"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="text-sm font-medium text-green-600 uppercase tracking-wide">Avis Actifs</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['active'] }}</div>
                </div>
                <div class="ml-4">
                    <i class="fas fa-check-circle text-2xl text-gray-300"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="text-sm font-medium text-yellow-600 uppercase tracking-wide">Avis Inactifs</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['inactive'] }}</div>
                </div>
                <div class="ml-4">
                    <i class="fas fa-pause-circle text-2xl text-gray-300"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="text-sm font-medium text-purple-600 uppercase tracking-wide">Note Moyenne</div>
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['average_rating'], 1) }}/5</div>
                </div>
                <div class="ml-4">
                    <i class="fas fa-star text-2xl text-gray-300"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="bg-white rounded-lg shadow-md p-4 md:p-6 mb-6 md:mb-8">
        <div class="flex flex-wrap gap-4">
            <form action="{{ route('admin.reviews.delete-all') }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer tous les avis ?')" class="w-full sm:w-auto">
                @csrf
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition w-full sm:w-auto">
                    <i class="fas fa-trash mr-2"></i>Supprimer Tous
                </button>
            </form>
        </div>
    </div>

    <!-- Liste des avis -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-4 md:px-6 py-4 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                <h3 class="text-base md:text-lg font-medium text-gray-900">Liste des Avis</h3>
                <div class="flex items-center text-xs sm:text-sm text-gray-500">
                    <i class="fas fa-sort-amount-down mr-2"></i>
                    <span>Triés par date (plus récent en premier)</span>
                </div>
            </div>
        </div>

        @if($reviews->count() > 0)
            <div class="divide-y divide-gray-200">
                @foreach($reviews as $review)
                    <div class="p-4 md:p-6 hover:bg-gray-50">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-3 mb-2">
                                    <div class="flex-shrink-0">
                                        @if($review->author_photo)
                                            <img src="{{ $review->author_photo }}" 
                                                 alt="{{ $review->author_name }}" 
                                                 class="w-10 h-10 rounded-full object-cover border-2 border-gray-200"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold" style="display: none;">
                                                {{ $review->author_initials }}
                                            </div>
                                        @else
                                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold">
                                                {{ $review->author_initials }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        @if($review->author_link)
                                            <a href="{{ $review->author_link }}" 
                                               target="_blank" 
                                               rel="noopener noreferrer"
                                               class="text-base md:text-lg font-semibold text-blue-600 hover:text-blue-800 hover:underline break-words">
                                                {{ $review->author_name }}
                                                <i class="fas fa-external-link-alt text-xs ml-1"></i>
                                            </a>
                                        @else
                                            <h4 class="text-base md:text-lg font-semibold text-gray-900 break-words">{{ $review->author_name }}</h4>
                                        @endif
                                        <div class="flex items-center space-x-2 mt-1">
                                            <div class="flex text-yellow-400">
                                                {!! $review->stars_html !!}
                                            </div>
                                            <span class="text-sm text-gray-500">{{ $review->rating }}/5</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <p class="text-gray-700 mb-3 mt-2 break-words">{{ $review->review_text }}</p>
                                
                                <div class="flex flex-wrap items-center gap-3 text-sm">
                                    <div class="flex items-center text-blue-600 font-medium">
                                        <i class="fas fa-calendar mr-2"></i>
                                        <span>{{ $review->review_date ? $review->review_date->format('d/m/Y à H:i') : 'Date inconnue' }}</span>
                                    </div>
                                    <div class="flex items-center text-gray-500">
                                        <i class="fas fa-tag mr-1"></i>
                                        <span>{{ $review->source }}</span>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $review->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $review->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 md:ml-4 md:flex-shrink-0">
                                <form action="{{ route('admin.reviews.toggle-status', $review->id) }}" method="POST" class="inline w-full sm:w-auto">
                                    @csrf
                                    <button type="submit" class="text-sm px-3 py-1 rounded w-full sm:w-auto {{ $review->is_active ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200' : 'bg-green-100 text-green-800 hover:bg-green-200' }} transition">
                                        {{ $review->is_active ? 'Désactiver' : 'Activer' }}
                                    </button>
                                </form>
                                
                                <form action="{{ route('admin.reviews.delete', $review->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet avis ?')" class="inline w-full sm:w-auto">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm px-3 py-1 bg-red-100 text-red-800 rounded hover:bg-red-200 transition w-full sm:w-auto">
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $reviews->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <i class="fas fa-comments text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun avis trouvé</h3>
                <p class="text-gray-500 mb-6">Commencez par importer des avis depuis Google.</p>
                <a href="{{ route('admin.reviews.serp.config') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-download mr-2"></i>Importer des Avis
                </a>
            </div>
        @endif
    </div>
</div>
@endsection