@extends('layouts.admin')

@section('title', 'Gestion des Mots-clés SEO')

@section('content')
<div class="container mx-auto px-4 py-6 md:py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Gestion des Mots-clés SEO</h1>
            <p class="text-gray-600 mt-1">Ajoutez et modifiez les mots-clés utilisés pour la génération automatique d'articles</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.seo-automation.index') }}" 
               class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Retour à l'automatisation
            </a>
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

    <!-- Section 1: Gestion des mots-clés -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-tags mr-2 text-blue-600"></i>Liste des Mots-clés
        </h2>
        <p class="text-sm text-gray-600 mb-4">
            Ces mots-clés seront utilisés pour générer automatiquement des articles SEO. Un mot-clé par ligne.
        </p>
        
        <form action="{{ route('admin.keywords.save') }}" method="POST" id="keywordsForm">
            @csrf
            <div class="space-y-4">
                <!-- Bouton génération IA -->
                <div class="flex items-center gap-3 mb-4">
                    <button type="button" 
                            id="generateKeywordsBtn"
                            class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 flex items-center">
                        <i class="fas fa-magic mr-2"></i>
                        Générer des mots-clés (IA)
                    </button>
                    <span class="text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Génère automatiquement 20-30 mots-clés pertinents
                    </span>
                </div>
                
                <!-- Zone de texte pour les mots-clés -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Mots-clés (un par ligne)
                    </label>
                    <textarea 
                        name="keywords_text" 
                        id="keywordsTextarea"
                        rows="15"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono"
                        placeholder="Exemple:&#10;rénovation de toiture&#10;couvreur Paris&#10;réparation toiture urgente&#10;démoussage toiture&#10;isolation des combles">@foreach($customKeywords as $keyword){{ $keyword }}
@endforeach</textarea>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        Un mot-clé par ligne. Les doublons seront automatiquement supprimés.
                    </p>
                </div>
                
                <!-- Bouton sauvegarder -->
                <div class="flex items-center gap-3">
                    <button type="submit" 
                            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 flex items-center">
                        <i class="fas fa-save mr-2"></i>
                        Sauvegarder les mots-clés
                    </button>
                    <span id="keywordsCount" class="text-sm text-gray-600">
                        {{ count($customKeywords) }} mot(s)-clé(s) configuré(s)
                    </span>
                </div>
            </div>
        </form>
    </div>

    <!-- Section 2: Gestion des images par mot-clé -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-images mr-2 text-indigo-600"></i>Images par Mot-clé
        </h2>
        <p class="text-sm text-gray-600 mb-4">
            Associez une image à chaque mot-clé. Cette image sera utilisée dans les articles générés automatiquement.
        </p>
        
        <div class="space-y-6">
            <!-- Formulaire d'ajout d'image -->
            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Ajouter une image</h3>
                <form action="{{ route('admin.keywords.image.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mot-clé</label>
                            <input type="text" 
                                   name="keyword" 
                                   required
                                   list="keywordsList"
                                   placeholder="Ex: rénovation de toiture"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <datalist id="keywordsList">
                                @foreach($customKeywords as $keyword)
                                    <option value="{{ $keyword }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Image (JPG, PNG, WebP - Max 5MB)</label>
                            <input type="file" 
                                   name="image" 
                                   accept="image/jpeg,image/png,image/jpg,image/webp"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Titre (optionnel)</label>
                        <input type="text" 
                               name="title" 
                               placeholder="Ex: Toiture rénovée"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm">
                        <i class="fas fa-plus mr-1"></i>Ajouter l'image
                    </button>
                </form>
            </div>
            
            <!-- Liste des images configurées -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-3">Images configurées ({{ $keywordImages->count() }})</h3>
                @if($keywordImages->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($keywordImages as $keywordImage)
                            <div class="border border-gray-200 rounded-lg p-4 bg-white hover:shadow-md transition-shadow">
                                <div class="mb-3">
                                    <img src="{{ asset($keywordImage->image_path) }}" 
                                         alt="{{ $keywordImage->title ?? $keywordImage->keyword }}"
                                         class="w-full h-40 object-cover rounded-lg">
                                </div>
                                <div class="space-y-2">
                                    <div>
                                        <p class="font-medium text-gray-900 text-sm">{{ $keywordImage->keyword }}</p>
                                        @if($keywordImage->title)
                                            <p class="text-gray-600 text-xs mt-1">{{ $keywordImage->title }}</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs px-2 py-1 rounded {{ $keywordImage->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $keywordImage->is_active ? 'Actif' : 'Inactif' }}
                                        </span>
                                        <div class="flex items-center gap-2">
                                            <form action="{{ route('admin.keywords.image.destroy', $keywordImage) }}" 
                                                  method="POST" 
                                                  class="inline"
                                                  onsubmit="return confirm('Supprimer cette image ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12 text-gray-500 bg-gray-50 rounded-lg">
                        <i class="fas fa-images text-5xl mb-3 text-gray-400"></i>
                        <p class="text-sm">Aucune image configurée. Ajoutez une image pour commencer.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Compter les mots-clés dans le textarea
document.getElementById('keywordsTextarea')?.addEventListener('input', function() {
    const lines = this.value.split('\n').filter(line => line.trim().length > 0);
    document.getElementById('keywordsCount').textContent = lines.length + ' mot(s)-clé(s)';
});

// Génération de mots-clés via IA
document.getElementById('generateKeywordsBtn')?.addEventListener('click', function() {
    const btn = this;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Génération en cours...';
    
    fetch('{{ route("admin.keywords.generate") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        
        if (data.status === 'success' && data.keywords) {
            // Remplacer le contenu du textarea
            const textarea = document.getElementById('keywordsTextarea');
            const existingKeywords = textarea.value.split('\n').filter(line => line.trim().length > 0);
            const allKeywords = [...new Set([...existingKeywords, ...data.keywords])];
            textarea.value = allKeywords.join('\n');
            
            // Mettre à jour le compteur
            document.getElementById('keywordsCount').textContent = allKeywords.length + ' mot(s)-clé(s)';
            
            // Afficher un message de succès
            showNotification(data.message || 'Mots-clés générés avec succès', 'success');
        } else {
            showNotification(data.message || 'Erreur lors de la génération', 'error');
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        showNotification('Erreur lors de la génération : ' + error.message, 'error');
    });
});

// Fonction de notification
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white font-medium z-50 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>
@endpush
@endsection

