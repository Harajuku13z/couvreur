@extends('layouts.admin')

@section('title', 'Soumissions')
@section('page_title', 'Toutes les Soumissions')

@section('content')
<div class="p-6">
    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('admin.submissions') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">Tous</option>
                    <option value="COMPLETED" {{ request('status') == 'COMPLETED' ? 'selected' : '' }}>Complétées</option>
                    <option value="IN_PROGRESS" {{ request('status') == 'IN_PROGRESS' ? 'selected' : '' }}>En cours</option>
                    <option value="ABANDONED" {{ request('status') == 'ABANDONED' ? 'selected' : '' }}>Abandonnées</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date début</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date fin</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
                <div class="flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, email..." class="flex-1 px-4 py-2 border border-gray-300 rounded-lg">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Statistiques et liens rapides -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-6">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $submissions->total() }}</div>
                    <div class="text-sm text-gray-500">Total Leads</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $submissions->where('status', 'COMPLETED')->count() }}</div>
                    <div class="text-sm text-gray-500">Complétés</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600">{{ $submissions->where('status', 'IN_PROGRESS')->count() }}</div>
                    <div class="text-sm text-gray-500">En cours</div>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.abandoned-submissions') }}" 
                   class="bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2 rounded-lg border border-red-200 transition-colors duration-200 flex items-center">
                    <i class="fas fa-times-circle mr-2"></i>
                    Leads Abandonnés
                    <span class="ml-2 bg-red-600 text-white text-xs px-2 py-1 rounded-full">{{ $abandonedCount }}</span>
                </a>
                
                <a href="{{ route('admin.export.submissions') }}" 
                   class="bg-blue-50 hover:bg-blue-100 text-blue-600 px-4 py-2 rounded-lg border border-blue-200 transition-colors duration-200 flex items-center">
                    <i class="fas fa-download mr-2"></i>
                    Exporter
                </a>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($submissions as $submission)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        #{{ $submission->id }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            {{ $submission->first_name }} {{ $submission->last_name }}
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ $submission->email }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $submission->phone }}</div>
                        <div class="text-sm text-gray-500">{{ $submission->email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $submission->property_type == 'house' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ $submission->property_type == 'house' ? 'Maison' : 'Appartement' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ match($submission->status) {
                                'COMPLETED' => 'bg-green-100 text-green-800',
                                'IN_PROGRESS' => 'bg-yellow-100 text-yellow-800',
                                'ABANDONED' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-800'
                            } }}">
                            {{ match($submission->status) {
                                'COMPLETED' => 'Complété',
                                'IN_PROGRESS' => 'En cours',
                                'ABANDONED' => 'Abandonné',
                                default => 'Inconnu'
                            } }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $submission->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('admin.submission.show', $submission->id) }}" 
                           class="text-blue-600 hover:text-blue-900 mr-3">
                            Voir
                        </a>
                        @if($submission->status === 'IN_PROGRESS')
                        <form method="POST" action="{{ route('admin.submission.mark-abandoned', $submission->id) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-red-600 hover:text-red-900" 
                                    onclick="return confirm('Marquer comme abandonné ?')">
                                Abandonner
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                        Aucune soumission trouvée.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $submissions->links() }}
    </div>
</div>
@endsection
