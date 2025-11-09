@extends('layouts.admin')

@section('title', 'Soumissions')
@section('page_title', 'Toutes les Soumissions')

@section('content')
<div class="p-4 md:p-6">
    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow p-4 md:p-6 mb-6">
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
    <div class="bg-white rounded-lg shadow p-4 md:p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center justify-around md:justify-start md:space-x-6">
                <div class="text-center">
                    <div class="text-xl md:text-2xl font-bold text-blue-600">{{ $submissions->total() }}</div>
                    <div class="text-xs md:text-sm text-gray-500">Total Leads</div>
                </div>
                <div class="text-center">
                    <div class="text-xl md:text-2xl font-bold text-green-600">{{ $submissions->where('status', 'COMPLETED')->count() }}</div>
                    <div class="text-xs md:text-sm text-gray-500">Complétés</div>
                </div>
                <div class="text-center">
                    <div class="text-xl md:text-2xl font-bold text-yellow-600">{{ $submissions->where('status', 'IN_PROGRESS')->count() }}</div>
                    <div class="text-xs md:text-sm text-gray-500">En cours</div>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:space-x-4">
                <a href="{{ route('admin.abandoned-submissions') }}" 
                   class="bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2 rounded-lg border border-red-200 transition-colors duration-200 flex items-center justify-center">
                    <i class="fas fa-times-circle mr-2"></i>
                    Leads Abandonnés
                    <span class="ml-2 bg-red-600 text-white text-xs px-2 py-1 rounded-full">{{ $abandonedCount }}</span>
                </a>
                
                <a href="{{ route('admin.export.submissions') }}" 
                   class="bg-blue-50 hover:bg-blue-100 text-blue-600 px-4 py-2 rounded-lg border border-blue-200 transition-colors duration-200 flex items-center justify-center">
                    <i class="fas fa-download mr-2"></i>
                    Exporter
                </a>
                
                <button onclick="showDeleteAllModal()" 
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center justify-center">
                    <i class="fas fa-trash-alt mr-2"></i>
                    Supprimer tout
                </button>
            </div>
        </div>
    </div>

    <!-- Vue mobile : Cartes -->
    <div class="md:hidden space-y-4">
        @forelse($submissions as $submission)
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex justify-between items-start mb-3">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-sm font-medium text-gray-500">#{{ $submission->id }}</span>
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
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ $submission->first_name }} {{ $submission->last_name }}
                    </h3>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.submission.show', $submission->id) }}" 
                       class="text-blue-600 hover:text-blue-900 p-2"
                       title="Voir les détails">
                        <i class="fas fa-eye"></i>
                    </a>
                    @if($submission->status === 'COMPLETED')
                        <a href="{{ route('admin.submission.create-client', $submission->id) }}" 
                           class="text-green-600 hover:text-green-900 p-2"
                           title="Créer un devis">
                            <i class="fas fa-file-invoice"></i>
                        </a>
                    @endif
                    @if($submission->status === 'IN_PROGRESS')
                        <form method="POST" action="{{ route('admin.submission.mark-abandoned', $submission->id) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-red-600 hover:text-red-900 p-2" 
                                    onclick="return confirm('Marquer comme abandonné ?')"
                                    title="Abandonner">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            
            <div class="space-y-2 text-sm">
                <div class="flex items-center text-gray-600">
                    <i class="fas fa-envelope w-5 text-gray-400"></i>
                    <span>{{ $submission->email }}</span>
                </div>
                <div class="flex items-center text-gray-600">
                    <i class="fas fa-phone w-5 text-gray-400"></i>
                    <span>{{ $submission->phone }}</span>
                </div>
                @if($submission->city)
                <div class="flex items-center text-gray-600">
                    <i class="fas fa-map-marker-alt w-5 text-gray-400"></i>
                    <span>{{ $submission->city }}@if($submission->country), {{ $submission->country }}@endif</span>
                </div>
                @endif
                <div class="flex items-center text-gray-600">
                    <i class="fas fa-calendar w-5 text-gray-400"></i>
                    <span>{{ $submission->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
            
            @php
                $devisCount = $submission->devis_count;
                $facturesPayeesCount = $submission->factures_payees_count;
            @endphp
            @if($devisCount > 0 || $facturesPayeesCount > 0)
            <div class="mt-3 pt-3 border-t border-gray-200">
                <div class="flex flex-wrap gap-2">
                    @if($devisCount > 0)
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-file-invoice mr-1"></i>
                            {{ $devisCount }} devis
                        </span>
                    @endif
                    @if($facturesPayeesCount > 0)
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>
                            {{ $facturesPayeesCount }} facture(s) payée(s)
                        </span>
                    @endif
                </div>
            </div>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">
            Aucune soumission trouvée.
        </div>
        @endforelse
    </div>

    <!-- Vue desktop : Table -->
    <div class="hidden md:block bg-white rounded-lg shadow overflow-hidden table-responsive">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ville</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Suivi</th>
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
                        @if($submission->city)
                            <div class="text-sm font-medium text-gray-900">
                                <i class="fas fa-map-marker-alt mr-1 text-gray-400"></i>{{ $submission->city }}
                            </div>
                            @if($submission->country)
                                <div class="text-xs text-gray-500">{{ $submission->country }}</div>
                            @endif
                        @else
                            <span class="text-sm text-gray-400 italic">Non renseigné</span>
                        @endif
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
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $devisCount = $submission->devis_count;
                            $facturesPayeesCount = $submission->factures_payees_count;
                        @endphp
                        <div class="flex flex-col gap-1">
                            @if($devisCount > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-file-invoice mr-1"></i>
                                    {{ $devisCount }} devis
                                </span>
                            @else
                                <span class="text-xs text-gray-400">Aucun devis</span>
                            @endif
                            @if($facturesPayeesCount > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    {{ $facturesPayeesCount }} facture(s) payée(s)
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $submission->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.submission.show', $submission->id) }}" 
                               class="text-blue-600 hover:text-blue-900"
                               title="Voir les détails">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($submission->status === 'COMPLETED')
                                <a href="{{ route('admin.submission.create-client', $submission->id) }}" 
                                   class="text-green-600 hover:text-green-900"
                                   title="Créer un devis">
                                    <i class="fas fa-file-invoice"></i>
                                </a>
                            @endif
                            @if($submission->status === 'IN_PROGRESS')
                                <form method="POST" action="{{ route('admin.submission.mark-abandoned', $submission->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-900" 
                                            onclick="return confirm('Marquer comme abandonné ?')"
                                            title="Abandonner">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
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

<!-- Modal Supprimer tout -->
<div id="deleteAllModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 md:top-20 mx-auto p-5 border w-11/12 md:w-96 shadow-lg rounded-md bg-white modal-responsive">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                    Supprimer toutes les soumissions
                </h3>
                <button onclick="hideDeleteAllModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="mb-4">
                <p class="text-sm text-gray-700 mb-4">
                    Cette action est <strong>irréversible</strong>. Toutes les soumissions seront définitivement supprimées.
                </p>
                <p class="text-sm font-semibold text-red-600 mb-2">
                    Nombre de soumissions à supprimer : <span id="deleteCount">{{ $submissions->total() }}</span>
                </p>
            </div>
            
            <form id="deleteAllForm" method="POST" action="{{ route('admin.submissions.delete-all') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Mot de passe requis
                    </label>
                    <input type="password" 
                           name="password" 
                           id="deletePassword" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500" 
                           placeholder="Entrez le mot de passe"
                           required
                           autocomplete="off">
                    <p class="text-xs text-gray-500 mt-1">
                        Mot de passe requis pour confirmer cette action
                    </p>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="hideDeleteAllModal()" 
                            class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors duration-200">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200">
                        <i class="fas fa-trash-alt mr-2"></i>
                        Supprimer tout
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showDeleteAllModal() {
    document.getElementById('deleteAllModal').classList.remove('hidden');
    document.getElementById('deletePassword').focus();
}

function hideDeleteAllModal() {
    document.getElementById('deleteAllModal').classList.add('hidden');
    document.getElementById('deleteAllForm').reset();
}

// Gestion de la soumission du formulaire
document.getElementById('deleteAllForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const password = document.getElementById('deletePassword').value;
    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Suppression...';
    
    fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            location.reload();
        } else {
            alert('❌ ' + (data.message || 'Erreur lors de la suppression'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('❌ Erreur lors de la suppression');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});
</script>
@endpush
@endsection
