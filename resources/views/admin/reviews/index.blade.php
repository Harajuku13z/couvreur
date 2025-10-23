@extends('layouts.admin')

@section('title', 'Gestion des Avis')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-star text-yellow-500 mr-2"></i>Gestion des Avis
            </h1>
            <p class="text-gray-600 mt-1">Gérez les avis clients et importez depuis Google</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.reviews.google.config') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition flex items-center">
                <i class="fab fa-google mr-2"></i>Config Google
            </a>
            <a href="{{ route('admin.reviews.create') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition flex items-center">
                <i class="fas fa-plus mr-2"></i>Ajouter un Avis
            </a>
        </div>
    </div>

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
                    <i class="fas fa-clock text-2xl text-gray-300"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="text-sm font-medium text-purple-600 uppercase tracking-wide">Note Moyenne</div>
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['avg_rating'], 1) }}/5</div>
                </div>
                <div class="ml-4">
                    <i class="fas fa-star text-2xl text-gray-300"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des avis -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-list mr-2 text-blue-600"></i>Tous les Avis
            </h3>
        </div>
        <div class="p-6">
            @if($reviews->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Note</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avis</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($reviews as $review)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                                                <i class="fas fa-user text-white text-sm"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $review->author_name }}</div>
                                            @if($review->author_location)
                                                <div class="text-sm text-gray-500">{{ $review->author_location }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $review->rating)
                                                <i class="fas fa-star text-yellow-400"></i>
                                            @else
                                                <i class="far fa-star text-yellow-400"></i>
                                            @endif
                                        @endfor
                                        <span class="ml-2 text-sm font-medium text-gray-900">{{ $review->rating }}/5</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 max-w-xs truncate">{{ Str::limit($review->review_text, 100) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($review->source === 'google')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fab fa-google mr-1"></i>Google
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-edit mr-1"></i>Manuel
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($review->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check mr-1"></i>Actif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>Inactif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>{{ $review->created_at->format('d/m/Y') }}</div>
                                    <div class="text-xs">{{ $review->created_at->format('H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.reviews.show', $review) }}" class="text-blue-600 hover:text-blue-900" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.reviews.edit', $review) }}" class="text-yellow-600 hover:text-yellow-900" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.reviews.toggle', $review) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-{{ $review->is_active ? 'gray' : 'green' }}-600 hover:text-{{ $review->is_active ? 'gray' : 'green' }}-900" title="{{ $review->is_active ? 'Désactiver' : 'Activer' }}">
                                                <i class="fas fa-{{ $review->is_active ? 'ban' : 'check' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet avis ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="flex justify-center mt-6">
                    {{ $reviews->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-star text-6xl text-gray-300 mb-4"></i>
                    <h4 class="text-xl font-semibold text-gray-600 mb-2">Aucun avis pour le moment</h4>
                    <p class="text-gray-500 mb-6">Commencez par ajouter un avis manuellement ou importez depuis Google</p>
                    <div class="flex justify-center space-x-4">
                        <a href="{{ route('admin.reviews.create') }}" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition flex items-center">
                            <i class="fas fa-plus mr-2"></i>Ajouter un Avis
                        </a>
                        <a href="{{ route('admin.reviews.google.config') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition flex items-center">
                            <i class="fab fa-google mr-2"></i>Configurer Google
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection








