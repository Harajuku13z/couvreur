@extends('layouts.admin')

@section('title', 'Soumissions Abandonnées')

@section('content')
<div class="p-4 md:p-6">
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-xl md:text-3xl font-bold text-gray-800">Soumissions Abandonnées</h1>
            <p class="text-gray-600 mt-2">Liste des formulaires non complétés</p>
        </div>
        <div class="flex gap-3 w-full sm:w-auto">
            <a href="{{ route('admin.export.abandoned-submissions') }}" 
               class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition w-full sm:w-auto text-center">
                <i class="fas fa-download mr-2"></i>Export CSV
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('admin.abandoned-submissions') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Étape d'abandon</label>
                <select name="step" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Toutes les étapes</option>
                    <option value="propertyType" {{ request('step') == 'propertyType' ? 'selected' : '' }}>Type de bien</option>
                    <option value="surface" {{ request('step') == 'surface' ? 'selected' : '' }}>Surface</option>
                    <option value="workType" {{ request('step') == 'workType' ? 'selected' : '' }}>Type de travaux</option>
                    <option value="ownershipStatus" {{ request('step') == 'ownershipStatus' ? 'selected' : '' }}>Statut propriétaire</option>
                    <option value="personalInfo" {{ request('step') == 'personalInfo' ? 'selected' : '' }}>Informations personnelles</option>
                    <option value="phone" {{ request('step') == 'phone' ? 'selected' : '' }}>Téléphone</option>
                    <option value="email" {{ request('step') == 'email' ? 'selected' : '' }}>Email</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date début</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date fin</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-filter mr-2"></i>Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-red-500 rounded-md flex items-center justify-center">
                        <i class="fas fa-times-circle text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-5">
                    <p class="text-sm font-medium text-gray-500">Total Abandons</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $abandonedSubmissions->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-yellow-500 rounded-md flex items-center justify-center">
                        <i class="fas fa-clock text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-5">
                    <p class="text-sm font-medium text-gray-500">Aujourd'hui</p>
                    <p class="text-2xl font-bold text-gray-900">{{ \App\Models\Submission::abandoned()->whereDate('created_at', today())->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-purple-500 rounded-md flex items-center justify-center">
                        <i class="fas fa-chart-line text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-5">
                    <p class="text-sm font-medium text-gray-500">Cette semaine</p>
                    <p class="text-2xl font-bold text-gray-900">{{ \App\Models\Submission::abandoned()->where('created_at', '>=', now()->startOfWeek())->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Vue mobile : Cartes -->
    <div class="md:hidden space-y-4">
        @forelse($abandonedSubmissions as $abandoned)
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex justify-between items-start mb-3">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-sm font-medium text-gray-500">#{{ substr($abandoned->session_id, 0, 8) }}</span>
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                            {{ ucfirst($abandoned->current_step ?? 'N/A') }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.abandoned-submission.show', $abandoned->id) }}" 
                       class="text-blue-600 hover:text-blue-900 p-2"
                       title="Voir détails">
                        <i class="fas fa-eye"></i>
                    </a>
                </div>
            </div>
            
            <div class="space-y-2 text-sm">
                @if($abandoned->created_at && $abandoned->abandoned_at)
                <div class="flex items-center text-gray-600">
                    <i class="fas fa-clock w-5 text-gray-400"></i>
                    <span>{{ $abandoned->created_at->diffForHumans($abandoned->abandoned_at) }}</span>
                </div>
                @endif
                <div class="flex items-center text-gray-600">
                    <i class="fas fa-calendar w-5 text-gray-400"></i>
                    <span>{{ $abandoned->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">
            <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
            <p class="text-lg">Aucune soumission abandonnée</p>
            <p class="text-sm">Les formulaires incomplets apparaîtront ici</p>
        </div>
        @endforelse
    </div>

    <!-- Vue desktop : Table -->
    <div class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            ID Session
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Étape d'abandon
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Temps passé
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($abandonedSubmissions as $abandoned)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                #{{ substr($abandoned->session_id, 0, 8) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    {{ ucfirst($abandoned->current_step ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($abandoned->created_at && $abandoned->abandoned_at)
                                    <i class="fas fa-clock text-gray-400 mr-1"></i>
                                    {{ $abandoned->created_at->diffForHumans($abandoned->abandoned_at) }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div>{{ $abandoned->created_at->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-400">{{ $abandoned->created_at->format('H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('admin.abandoned-submission.show', $abandoned->id) }}" 
                                   class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye mr-1"></i>Voir détails
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                                <p class="text-lg">Aucune soumission abandonnée</p>
                                <p class="text-sm">Les formulaires incomplets apparaîtront ici</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($abandonedSubmissions->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $abandonedSubmissions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection






