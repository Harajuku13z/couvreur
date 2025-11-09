@extends('layouts.admin')

@section('title', 'Automatisation SEO')

@section('content')
<div class="container mx-auto px-4 py-6 md:py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Automatisation SEO</h1>
            <p class="text-gray-600 mt-1">Gestion des articles SEO générés automatiquement</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Statistiques -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Total</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
        </div>
        <div class="bg-yellow-50 rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">En attente</div>
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</div>
        </div>
        <div class="bg-blue-50 rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Publiés</div>
            <div class="text-2xl font-bold text-blue-600">{{ $stats['published'] }}</div>
        </div>
        <div class="bg-green-50 rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Indexés</div>
            <div class="text-2xl font-bold text-green-600">{{ $stats['indexed'] }}</div>
        </div>
        <div class="bg-red-50 rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Échoués</div>
            <div class="text-2xl font-bold text-red-600">{{ $stats['failed'] }}</div>
        </div>
    </div>

    <!-- Table Desktop -->
    <div class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ville</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mot-clé</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Article</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($logs as $log)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $log->city->name ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $log->keyword ?? '-' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'generated' => 'bg-blue-100 text-blue-800',
                                'published' => 'bg-blue-100 text-blue-800',
                                'indexed' => 'bg-green-100 text-green-800',
                                'failed' => 'bg-red-100 text-red-800',
                            ];
                            $statusLabels = [
                                'pending' => 'En attente',
                                'generated' => 'Généré',
                                'published' => 'Publié',
                                'indexed' => 'Indexé',
                                'failed' => 'Échoué',
                            ];
                        @endphp
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$log->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $statusLabels[$log->status] ?? $log->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($log->article_url)
                            <a href="{{ $log->article_url }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">
                                <i class="fas fa-external-link-alt mr-1"></i> Voir
                            </a>
                        @else
                            <span class="text-gray-400 text-sm">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $log->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        @if($log->status === 'failed')
                            <form action="{{ route('admin.seo-automation.retry', $log) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-redo mr-1"></i> Relancer
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        Aucune automation enregistrée
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Cards Mobile -->
    <div class="md:hidden space-y-4">
        @forelse($logs as $log)
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <div class="font-semibold text-gray-900">{{ $log->city->name ?? 'N/A' }}</div>
                    <div class="text-sm text-gray-600 mt-1">{{ $log->keyword ?? '-' }}</div>
                </div>
                @php
                    $statusColors = [
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'generated' => 'bg-blue-100 text-blue-800',
                        'published' => 'bg-blue-100 text-blue-800',
                        'indexed' => 'bg-green-100 text-green-800',
                        'failed' => 'bg-red-100 text-red-800',
                    ];
                    $statusLabels = [
                        'pending' => 'En attente',
                        'generated' => 'Généré',
                        'published' => 'Publié',
                        'indexed' => 'Indexé',
                        'failed' => 'Échoué',
                    ];
                @endphp
                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$log->status] ?? 'bg-gray-100 text-gray-800' }}">
                    {{ $statusLabels[$log->status] ?? $log->status }}
                </span>
            </div>
            
            <div class="text-sm text-gray-500 mb-3">
                <i class="far fa-calendar mr-1"></i> {{ $log->created_at->format('d/m/Y H:i') }}
            </div>
            
            @if($log->article_url)
                <a href="{{ $log->article_url }}" target="_blank" class="inline-block text-blue-600 hover:text-blue-800 text-sm mb-2">
                    <i class="fas fa-external-link-alt mr-1"></i> Voir l'article
                </a>
            @endif
            
            @if($log->status === 'failed')
                <form action="{{ route('admin.seo-automation.retry', $log) }}" method="POST" class="mt-2">
                    @csrf
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
                        <i class="fas fa-redo mr-1"></i> Relancer
                    </button>
                </form>
            @endif
            
            @if($log->error_message)
                <div class="mt-2 text-xs text-red-600 bg-red-50 p-2 rounded">
                    <i class="fas fa-exclamation-triangle mr-1"></i> {{ $log->error_message }}
                </div>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">
            Aucune automation enregistrée
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $logs->links() }}
    </div>
</div>
@endsection

