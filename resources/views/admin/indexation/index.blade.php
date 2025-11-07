@extends('layouts.admin')

@section('title', 'Gestion de l\'Indexation')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">🔍 Gestion de l'Indexation</h1>
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

    @if($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.indexation.update') }}" method="POST">
        @csrf
        
        <!-- Robots Meta Tags -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">🤖 Robots Meta Tags</h2>
            <p class="text-sm text-gray-600 mb-4">Contrôlez comment les moteurs de recherche indexent votre site</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center">
                    <input type="checkbox" name="robots_index" id="robots_index" value="1" 
                           {{ ($indexationConfig['robots_index'] ?? true) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="robots_index" class="ml-2 text-sm font-medium text-gray-700">
                        Indexer les pages (index)
                    </label>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="robots_follow" id="robots_follow" value="1" 
                           {{ ($indexationConfig['robots_follow'] ?? true) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="robots_follow" class="ml-2 text-sm font-medium text-gray-700">
                        Suivre les liens (follow)
                    </label>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="robots_archive" id="robots_archive" value="1" 
                           {{ ($indexationConfig['robots_archive'] ?? true) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="robots_archive" class="ml-2 text-sm font-medium text-gray-700">
                        Archiver les pages (archive)
                    </label>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="robots_snippet" id="robots_snippet" value="1" 
                           {{ ($indexationConfig['robots_snippet'] ?? true) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="robots_snippet" class="ml-2 text-sm font-medium text-gray-700">
                        Afficher les extraits (snippet)
                    </label>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="robots_imageindex" id="robots_imageindex" value="1" 
                           {{ ($indexationConfig['robots_imageindex'] ?? true) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="robots_imageindex" class="ml-2 text-sm font-medium text-gray-700">
                        Indexer les images (imageindex)
                    </label>
                </div>
            </div>
        </div>

        <!-- Sitemap XML -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">🗺️ Sitemap XML</h2>
            <p class="text-sm text-gray-600 mb-4">Configurez le sitemap pour aider les moteurs de recherche à explorer votre site</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="sitemap_enabled" value="1" 
                               {{ ($indexationConfig['sitemap_enabled'] ?? true) ? 'checked' : '' }}
                               class="mr-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <span class="text-sm font-medium text-gray-700">Activer le sitemap XML</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1">Le sitemap sera accessible à /sitemap.xml</p>
                </div>
                
                <div class="mb-4">
                    <label for="sitemap_priority" class="block text-sm font-medium mb-2">Priorité par défaut</label>
                    <select name="sitemap_priority" id="sitemap_priority" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="0.1" {{ ($indexationConfig['sitemap_priority'] ?? 0.8) == 0.1 ? 'selected' : '' }}>0.1 (Très faible)</option>
                        <option value="0.3" {{ ($indexationConfig['sitemap_priority'] ?? 0.8) == 0.3 ? 'selected' : '' }}>0.3 (Faible)</option>
                        <option value="0.5" {{ ($indexationConfig['sitemap_priority'] ?? 0.8) == 0.5 ? 'selected' : '' }}>0.5 (Moyenne)</option>
                        <option value="0.7" {{ ($indexationConfig['sitemap_priority'] ?? 0.8) == 0.7 ? 'selected' : '' }}>0.7 (Élevée)</option>
                        <option value="0.8" {{ ($indexationConfig['sitemap_priority'] ?? 0.8) == 0.8 ? 'selected' : '' }}>0.8 (Très élevée)</option>
                        <option value="1.0" {{ ($indexationConfig['sitemap_priority'] ?? 0.8) == 1.0 ? 'selected' : '' }}>1.0 (Maximum)</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="sitemap_changefreq" class="block text-sm font-medium mb-2">Fréquence de mise à jour</label>
                    <select name="sitemap_changefreq" id="sitemap_changefreq" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="always" {{ ($indexationConfig['sitemap_changefreq'] ?? 'weekly') == 'always' ? 'selected' : '' }}>Toujours</option>
                        <option value="hourly" {{ ($indexationConfig['sitemap_changefreq'] ?? 'weekly') == 'hourly' ? 'selected' : '' }}>Horaire</option>
                        <option value="daily" {{ ($indexationConfig['sitemap_changefreq'] ?? 'weekly') == 'daily' ? 'selected' : '' }}>Quotidienne</option>
                        <option value="weekly" {{ ($indexationConfig['sitemap_changefreq'] ?? 'weekly') == 'weekly' ? 'selected' : '' }}>Hebdomadaire</option>
                        <option value="monthly" {{ ($indexationConfig['sitemap_changefreq'] ?? 'weekly') == 'monthly' ? 'selected' : '' }}>Mensuelle</option>
                        <option value="yearly" {{ ($indexationConfig['sitemap_changefreq'] ?? 'weekly') == 'yearly' ? 'selected' : '' }}>Annuelle</option>
                        <option value="never" {{ ($indexationConfig['sitemap_changefreq'] ?? 'weekly') == 'never' ? 'selected' : '' }}>Jamais</option>
                    </select>
                </div>
            </div>
            
            <div class="flex items-center space-x-4 flex-wrap">
                <a href="{{ url('/sitemap.xml') }}" target="_blank" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-external-link-alt mr-2"></i>Voir le sitemap
                </a>
                
                <button type="button" onclick="updateSitemap()" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-sync-alt mr-2"></i>Mettre à jour le sitemap
                </button>
                
                <span class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Le sitemap est actuellement 
                    <span class="font-medium {{ ($indexationConfig['sitemap_enabled'] ?? true) ? 'text-green-600' : 'text-red-600' }}">
                        {{ ($indexationConfig['sitemap_enabled'] ?? true) ? 'activé' : 'désactivé' }}
                    </span>
                </span>
            </div>
        </div>

        <!-- Robots.txt -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">🤖 Robots.txt</h2>
            <p class="text-sm text-gray-600 mb-4">Fichier de configuration pour les robots des moteurs de recherche</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium">Fichier robots.txt</p>
                                <p class="text-sm text-gray-600">Configuration des robots</p>
                            </div>
                            <a href="{{ url('/robots.txt') }}" target="_blank" 
                               class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                                Voir robots.txt
                            </a>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium">Soumettre à Google</p>
                                <p class="text-sm text-gray-600">URL pour Google Search Console</p>
                            </div>
                            <button type="button" onclick="copyToClipboard('{{ url('/robots.txt') }}')" 
                                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                                Copier l'URL
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <h4 class="font-medium text-yellow-800 mb-2">💡 Instructions pour Google Search Console</h4>
                <ol class="text-sm text-yellow-700 space-y-1">
                    <li>1. Connectez-vous à <a href="https://search.google.com/search-console" target="_blank" class="text-blue-600 hover:underline">Google Search Console</a></li>
                    <li>2. Sélectionnez votre propriété</li>
                    <li>3. Allez dans "Sitemaps" et ajoutez : <code class="bg-yellow-100 px-1 rounded">{{ url('/sitemap.xml') }}</code></li>
                    <li>4. Vérifiez que votre site respecte le fichier robots.txt</li>
                </ol>
            </div>
        </div>

        <div class="text-center">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg">
                Sauvegarder la Configuration d'Indexation
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Copié !';
        button.classList.remove('bg-green-500', 'hover:bg-green-600');
        button.classList.add('bg-green-600');
        
        setTimeout(() => {
            button.textContent = originalText;
            button.classList.remove('bg-green-600');
            button.classList.add('bg-green-500', 'hover:bg-green-600');
        }, 2000);
    }).catch(function(err) {
        console.error('Erreur lors de la copie: ', err);
        alert('Erreur lors de la copie. Veuillez copier manuellement: ' + text);
    });
}

function updateSitemap() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mise à jour...';
    button.disabled = true;
    button.classList.add('opacity-75');
    
    fetch('{{ route("admin.indexation.update-sitemap") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 401 || response.status === 403) {
                throw new Error('Non autorisé. Veuillez vous reconnecter.');
            } else if (response.status === 404) {
                throw new Error('Route non trouvée. Vérifiez la configuration.');
            } else {
                throw new Error('Erreur serveur: ' + response.status);
            }
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('Sitemap mis à jour avec succès !', 'success');
        } else {
            showNotification('Erreur lors de la mise à jour du sitemap: ' + (data.message || 'Erreur inconnue'), 'error');
        }
    })
    .catch(error => {
        console.error('Erreur détaillée:', error);
        if (error.message.includes('Non autorisé') || error.message.includes('401')) {
            showNotification('Session expirée. Veuillez vous reconnecter.', 'error');
            setTimeout(() => {
                window.location.href = '{{ route("admin.login") }}';
            }, 2000);
        } else {
            showNotification('Erreur: ' + error.message, 'error');
        }
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
        button.classList.remove('opacity-75');
    });
}

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

