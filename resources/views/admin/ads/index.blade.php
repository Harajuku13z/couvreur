@extends('layouts.admin')

@section('title', 'Annonces')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- En-tête avec statistiques -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
            <div>
                <p class="text-gray-600 mt-2">Gérez vos annonces locales et générez du contenu automatiquement</p>
            </div>
            <div class="flex items-center justify-around md:justify-end gap-4 md:space-x-4">
                <div class="text-center md:text-right">
                    <div class="text-xl md:text-2xl font-bold text-blue-600">{{ $totalAds }}</div>
                    <div class="text-xs md:text-sm text-gray-500">Annonces totales</div>
                </div>
                <div class="text-center md:text-right">
                    <div class="text-xl md:text-2xl font-bold text-green-600">{{ $publishedAds }}</div>
                    <div class="text-xs md:text-sm text-gray-500">Publiées</div>
                </div>
                <div class="text-center md:text-right">
                    <div class="text-xl md:text-2xl font-bold text-orange-600">{{ $draftAds }}</div>
                    <div class="text-xs md:text-sm text-gray-500">Brouillons</div>
                </div>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <a href="{{ route('admin.ads.templates.index') }}" class="bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white p-6 rounded-xl shadow-lg transition-all duration-200 transform hover:scale-105">
                <div class="flex items-center">
                    <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                        <i class="fas fa-file-alt text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="font-semibold text-lg">Templates</h3>
                        <p class="text-purple-100 text-sm">Gérer les templates IA</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.ads.manual') }}" class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white p-6 rounded-xl shadow-lg transition-all duration-200 transform hover:scale-105">
                <div class="flex items-center">
                    <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                        <i class="fas fa-edit text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="font-semibold text-lg">Créer manuellement</h3>
                        <p class="text-green-100 text-sm">Annonce personnalisée</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Actions rapides -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 bg-white rounded-xl shadow-sm p-4 border">
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:space-x-4">
                <span class="text-sm text-gray-600">Actions rapides :</span>
                <button onclick="deleteAllAds()" class="bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2 rounded-lg border border-red-200 transition-colors duration-200 flex items-center justify-center w-full sm:w-auto">
                    <i class="fas fa-trash mr-2"></i>
                    Supprimer toutes les annonces
                </button>
            </div>
            <div class="text-xs sm:text-sm text-gray-500 text-center sm:text-right">
                Dernière mise à jour : {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center">
            <i class="fas fa-check-circle mr-3"></i>
            {{ session('success') }}
        </div>
    @endif

    <!-- Tableau des annonces -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="px-4 md:px-6 py-4 border-b bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">Liste des annonces</h2>
        </div>
        
        <!-- Vue mobile : Cartes -->
        <div class="md:hidden divide-y divide-gray-200">
            @forelse($ads as $ad)
            <div class="p-4 hover:bg-gray-50">
                <!-- Ligne 1: Titre -->
                <div class="mb-2">
                    <h3 class="text-base font-semibold text-gray-900">{{ $ad->title }}</h3>
                    @if($ad->keyword)
                    <p class="text-xs text-gray-500 mt-1">{{ $ad->keyword }}</p>
                    @endif
                </div>
                <!-- Ligne 2: Ville et Statut -->
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    @if($ad->city)
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-map-marker-alt mr-1 text-gray-400"></i>
                        <span>{{ $ad->city->name }}@if($ad->city->postal_code) ({{ $ad->city->postal_code }})@endif</span>
                    </div>
                    @endif
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ match($ad->status) {
                            'published' => 'bg-green-100 text-green-800',
                            'draft' => 'bg-yellow-100 text-yellow-800',
                            'archived' => 'bg-gray-100 text-gray-800'
                        } }}">
                        <i class="fas fa-circle mr-1 text-xs
                            {{ match($ad->status) {
                                'published' => 'text-green-400',
                                'draft' => 'text-yellow-400',
                                'archived' => 'text-gray-400'
                            } }}"></i>
                        {{ ucfirst($ad->status) }}
                    </span>
                </div>
                <!-- Ligne 3: Slug -->
                <div class="mb-3">
                    <code class="text-xs bg-gray-100 px-2 py-1 rounded break-all">{{ $ad->slug }}</code>
                </div>
                <!-- Ligne 4: Actions -->
                <div class="flex flex-wrap gap-2">
                    @if($ad->status !== 'published')
                    <form method="POST" action="{{ route('admin.ads.publish', $ad) }}" class="inline">
                        @csrf
                        <button class="text-blue-600 hover:text-blue-800 transition-colors duration-150 text-sm px-3 py-1 rounded hover:bg-blue-50">
                            <i class="fas fa-eye mr-1"></i>Publier
                        </button>
                    </form>
                    @endif
                    
                    @if($ad->status !== 'archived')
                    <form method="POST" action="{{ route('admin.ads.archive', $ad) }}" class="inline">
                        @csrf
                        <button class="text-gray-600 hover:text-gray-800 transition-colors duration-150 text-sm px-3 py-1 rounded hover:bg-gray-50">
                            <i class="fas fa-archive mr-1"></i>Archiver
                        </button>
                    </form>
                    @endif
                    
                    <form method="POST" action="{{ route('admin.ads.destroy', $ad) }}" class="inline" onsubmit="return confirm('Supprimer cette annonce ?')">
                        @csrf
                        @method('DELETE')
                        <button class="text-red-600 hover:text-red-800 transition-colors duration-150 text-sm px-3 py-1 rounded hover:bg-red-50">
                            <i class="fas fa-trash mr-1"></i>Supprimer
                        </button>
                    </form>
                    
                    @if($ad->status === 'published')
                    <a class="text-green-600 hover:text-green-800 transition-colors duration-150 text-sm px-3 py-1 rounded hover:bg-green-50 inline-flex items-center" target="_blank" href="{{ route('ads.show', $ad->slug) }}">
                        <i class="fas fa-external-link-alt mr-1"></i>Voir
                    </a>
                    @endif
                </div>
            </div>
            @empty
            <div class="px-4 md:px-6 py-12 text-center">
                <div class="text-gray-400">
                    <i class="fas fa-inbox text-4xl mb-4"></i>
                    <p class="text-lg font-medium">Aucune annonce</p>
                    <p class="text-sm">Commencez par créer votre première annonce</p>
                </div>
            </div>
            @endforelse
        </div>
        
        <!-- Vue desktop : Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Titre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ville</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($ads as $ad)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $ad->title }}</div>
                            <div class="text-sm text-gray-500">{{ $ad->keyword }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ optional($ad->city)->name }}</div>
                            <div class="text-sm text-gray-500">{{ optional($ad->city)->postal_code }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ match($ad->status) {
                                    'published' => 'bg-green-100 text-green-800',
                                    'draft' => 'bg-yellow-100 text-yellow-800',
                                    'archived' => 'bg-gray-100 text-gray-800'
                                } }}">
                                <i class="fas fa-circle mr-1 text-xs
                                    {{ match($ad->status) {
                                        'published' => 'text-green-400',
                                        'draft' => 'text-yellow-400',
                                        'archived' => 'text-gray-400'
                                    } }}"></i>
                                {{ ucfirst($ad->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $ad->slug }}</code>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                @if($ad->status !== 'published')
                                <form method="POST" action="{{ route('admin.ads.publish', $ad) }}" class="inline">
                                    @csrf
                                    <button class="text-blue-600 hover:text-blue-800 transition-colors duration-150">
                                        <i class="fas fa-eye mr-1"></i>Publier
                                    </button>
                                </form>
                                @endif
                                
                                @if($ad->status !== 'archived')
                                <form method="POST" action="{{ route('admin.ads.archive', $ad) }}" class="inline">
                                    @csrf
                                    <button class="text-gray-600 hover:text-gray-800 transition-colors duration-150">
                                        <i class="fas fa-archive mr-1"></i>Archiver
                                    </button>
                                </form>
                                @endif
                                
                                <form method="POST" action="{{ route('admin.ads.destroy', $ad) }}" class="inline" onsubmit="return confirm('Supprimer cette annonce ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:text-red-800 transition-colors duration-150">
                                        <i class="fas fa-trash mr-1"></i>Supprimer
                                    </button>
                                </form>
                                
                                @if($ad->status === 'published')
                                <a class="text-green-600 hover:text-green-800 transition-colors duration-150" target="_blank" href="{{ route('ads.show', $ad->slug) }}">
                                    <i class="fas fa-external-link-alt mr-1"></i>Voir
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="text-gray-400">
                                <i class="fas fa-inbox text-4xl mb-4"></i>
                                <p class="text-lg font-medium">Aucune annonce</p>
                                <p class="text-sm">Commencez par créer votre première annonce</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($ads->hasPages())
    <div class="mt-6">
        {{ $ads->links() }}
    </div>
    @endif
    </div>
</div>

<script>
function deleteAllAds() {
    if (confirm('Êtes-vous sûr de vouloir supprimer TOUTES les annonces ? Cette action est irréversible !')) {
        if (confirm('ATTENTION : Cette action va supprimer définitivement toutes les annonces. Continuer ?')) {
            // Afficher un indicateur de chargement
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Suppression...';
            button.disabled = true;
            
            fetch('{{ route("admin.ads.delete-all") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Toutes les annonces ont été supprimées avec succès !');
                    location.reload();
                } else {
                    alert('Erreur lors de la suppression : ' + (data.message || 'Erreur inconnue'));
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de la suppression des annonces');
            })
            .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }
    }
}
</script>

@endsection