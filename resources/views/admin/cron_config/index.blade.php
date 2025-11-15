@extends('layouts.admin')

@section('title', 'Configuration du Cron')

@section('content')
<div class="container mx-auto px-4 py-6 md:py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Configuration du Cron</h1>
            <p class="text-gray-600 mt-1">Instructions pour configurer le cron dans Hostinger</p>
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

    <!-- Méthode 1: Script Shell Direct (Recommandé) -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-terminal mr-2 text-green-600"></i>Méthode 1 : Script Shell Direct (Recommandé)
        </h2>
        <p class="text-sm text-gray-600 mb-4">
            Cette méthode exécute directement la commande Artisan. Plus simple, plus rapide et plus fiable.
        </p>
        
        <div class="space-y-4">
            <!-- Instructions -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-green-900 mb-3">📋 Instructions pour Hostinger</h3>
                <ol class="list-decimal list-inside space-y-2 text-sm text-green-800">
                    <li>Connectez-vous à votre <strong>hPanel Hostinger</strong></li>
                    <li>Allez dans <strong>Avancé → Cron Jobs</strong></li>
                    <li>Créez une nouvelle tâche cron avec les paramètres suivants :</li>
                </ol>
            </div>
            
            <!-- Configuration du cron -->
            <div class="bg-gray-50 border border-gray-300 rounded-lg p-4">
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type :</label>
                        <code class="bg-white px-3 py-1 rounded border border-gray-300 text-sm">Personnalisé</code>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Commande à exécuter :</label>
                        <div class="flex items-center gap-2">
                            <code id="cronCommandDirect" class="flex-1 bg-white px-3 py-2 rounded border border-gray-300 text-xs font-mono break-all">{{ $cronCommand['direct']['command'] }}</code>
                            <button onclick="copyToClipboard('{{ $cronCommand['direct']['command'] }}')" 
                                    class="bg-gray-600 text-white px-3 py-2 rounded text-sm hover:bg-gray-700">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fréquence (selon l'intervalle configuré : {{ $cronInterval }} min) :</label>
                        <div class="flex items-center gap-2">
                            <code id="cronFrequency" class="bg-white px-3 py-2 rounded border border-gray-300 text-sm font-mono">{{ $cronCommand['direct']['frequency'] }}</code>
                            <button onclick="copyToClipboard('{{ $cronCommand['direct']['frequency'] }}')" 
                                    class="bg-gray-600 text-white px-3 py-2 rounded text-sm hover:bg-gray-700">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Configurez les champs : minute, heure, jour, mois, weekDay selon cette fréquence
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Exemple complet -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-blue-900 mb-2">💡 Exemple de configuration complète :</h4>
                <div class="space-y-2 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="font-medium w-20">minute:</span>
                        <code class="bg-white px-2 py-1 rounded">{{ $cronInterval === 1 ? '*' : "*/{$cronInterval}" }}</code>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-medium w-20">heure:</span>
                        <code class="bg-white px-2 py-1 rounded">*</code>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-medium w-20">jour:</span>
                        <code class="bg-white px-2 py-1 rounded">*</code>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-medium w-20">mois:</span>
                        <code class="bg-white px-2 py-1 rounded">*</code>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-medium w-20">weekDay:</span>
                        <code class="bg-white px-2 py-1 rounded">*</code>
                    </div>
                </div>
            </div>
            
            <!-- Note importante -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                <p class="text-sm text-yellow-800">
                    <i class="fas fa-info-circle mr-1"></i>
                    <strong>Note :</strong> La commande <code>seo:run-automations</code> vérifie automatiquement les conditions (heure configurée: <strong>{{ $automationTime }}</strong>, activation, villes favorites) avant d'exécuter. Le cron peut s'exécuter toutes les {{ $cronInterval }} minute(s), le système ne générera les articles que lorsque toutes les conditions sont remplies.
                </p>
            </div>
        </div>
    </div>

    <!-- Méthode 2: Route HTTP (Alternative) -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-globe mr-2 text-blue-600"></i>Méthode 2 : Route HTTP (Alternative)
        </h2>
        <p class="text-sm text-gray-600 mb-4">
            Si vous préférez utiliser une route HTTP protégée par token (nécessite curl).
        </p>
        
        <div class="space-y-4">
            <!-- Gestion du token -->
            <div class="flex items-center gap-3 flex-wrap">
                <button type="button" 
                        id="getTokenBtn"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 flex items-center">
                    <i class="fas fa-key mr-2"></i>
                    Afficher le token et l'URL
                </button>
                <button type="button" 
                        id="regenerateTokenBtn"
                        class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 flex items-center">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Régénérer le token
                </button>
                <button type="button" 
                        id="testHttpBtn"
                        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 flex items-center">
                    <i class="fas fa-vial mr-2"></i>
                    Tester la route HTTP
                </button>
            </div>
            
            <div id="tokenResult" class="hidden"></div>
            
            <!-- Configuration HTTP -->
            @if($scheduleUrl)
            <div class="bg-gray-50 border border-gray-300 rounded-lg p-4">
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Commande à exécuter :</label>
                        <div class="flex items-center gap-2">
                            <code id="cronCommandHttp" class="flex-1 bg-white px-3 py-2 rounded border border-gray-300 text-xs font-mono break-all">{{ $cronCommand['http']['command'] ?? '' }}</code>
                            <button onclick="copyToClipboard('{{ $cronCommand['http']['command'] ?? '' }}')" 
                                    class="bg-gray-600 text-white px-3 py-2 rounded text-sm hover:bg-gray-700">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fréquence :</label>
                        <code class="bg-white px-3 py-2 rounded border border-gray-300 text-sm font-mono">{{ $cronCommand['http']['frequency'] ?? $cronCommand['direct']['frequency'] }}</code>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Alternative : Service externe -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-cloud mr-2 text-purple-600"></i>Alternative : Service Externe
        </h2>
        <p class="text-sm text-gray-600 mb-4">
            Si le cron Hostinger ne fonctionne pas, vous pouvez utiliser un service externe.
        </p>
        
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
            <h4 class="text-sm font-semibold text-purple-900 mb-2">Services recommandés :</h4>
            <ul class="list-disc list-inside space-y-1 text-sm text-purple-800">
                <li><a href="https://cron-job.org" target="_blank" class="underline font-semibold">cron-job.org</a> (gratuit, fiable)</li>
                <li><a href="https://uptimerobot.com" target="_blank" class="underline font-semibold">UptimeRobot</a> (gratuit jusqu'à 50 monitors)</li>
                <li><a href="https://www.easycron.com" target="_blank" class="underline font-semibold">EasyCron</a> (gratuit jusqu'à 2 jobs)</li>
            </ul>
            <p class="text-xs text-purple-700 mt-3">
                <i class="fas fa-info-circle mr-1"></i>
                Configurez l'URL avec le token (méthode HTTP) et la fréquence selon l'intervalle configuré.
            </p>
        </div>
    </div>

    <!-- Logs -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-file-alt mr-2 text-gray-600"></i>Logs
        </h2>
        <p class="text-sm text-gray-600 mb-4">
            Les logs sont disponibles dans les fichiers suivants :
        </p>
        <div class="space-y-2">
            <div class="flex items-center gap-2 p-2 bg-gray-50 rounded">
                <code class="text-xs">storage/logs/seo-automation-cron.log</code>
                <span class="text-xs text-gray-500">(si vous utilisez le script shell)</span>
            </div>
            <div class="flex items-center gap-2 p-2 bg-gray-50 rounded">
                <code class="text-xs">storage/logs/laravel.log</code>
                <span class="text-xs text-gray-500">(pour la commande directe)</span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showNotification('Commande copiée dans le presse-papiers', 'success');
    }, function(err) {
        showNotification('Erreur lors de la copie', 'error');
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

// Gestion du token
document.getElementById('getTokenBtn')?.addEventListener('click', function() {
    const btn = this;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Chargement...';
    
    const resultDiv = document.getElementById('tokenResult');
    resultDiv.classList.remove('hidden');
    resultDiv.innerHTML = '<div class="bg-blue-50 border border-blue-200 rounded-lg p-4"><i class="fas fa-spinner fa-spin mr-2"></i>Récupération du token...</div>';
    
    fetch('{{ route("admin.cron-config.token") }}', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        
        if (data.status === 'success') {
            // Mettre à jour la commande HTTP
            const httpCommand = `curl -s "${data.url}" > /dev/null 2>&1`;
            const httpCommandEl = document.getElementById('cronCommandHttp');
            if (httpCommandEl) {
                httpCommandEl.textContent = httpCommand;
            }
            
            let html = '<div class="bg-white border border-gray-200 rounded-lg p-4">';
            html += '<h3 class="font-bold text-gray-900 mb-3"><i class="fas fa-link mr-2 text-blue-600"></i>URL de la route HTTP</h3>';
            html += '<div class="space-y-3">';
            html += '<div>';
            html += '<label class="block text-sm font-medium text-gray-700 mb-1">URL complète :</label>';
            html += '<div class="flex items-center gap-2">';
            html += '<input type="text" value="' + data.url + '" readonly class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 font-mono text-sm">';
            html += '<button onclick="copyToClipboard(\'' + data.url + '\')" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 flex items-center">';
            html += '<i class="fas fa-copy mr-2"></i>Copier';
            html += '</button>';
            html += '</div>';
            html += '</div>';
            html += '<div>';
            html += '<label class="block text-sm font-medium text-gray-700 mb-1">Token (gardez-le secret) :</label>';
            html += '<div class="flex items-center gap-2">';
            html += '<input type="text" value="' + data.token + '" readonly class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 font-mono text-sm">';
            html += '<button onclick="copyToClipboard(\'' + data.token + '\')" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 flex items-center">';
            html += '<i class="fas fa-copy mr-2"></i>Copier';
            html += '</button>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            resultDiv.innerHTML = html;
        } else {
            resultDiv.innerHTML = '<div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4"><i class="fas fa-exclamation-circle mr-2"></i>' + (data.message || 'Erreur lors de la récupération') + '</div>';
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        document.getElementById('tokenResult').innerHTML = '<div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4"><i class="fas fa-times-circle mr-2"></i>Erreur: ' + error.message + '</div>';
    });
});

document.getElementById('regenerateTokenBtn')?.addEventListener('click', function() {
    if (!confirm('⚠️ Êtes-vous sûr de vouloir régénérer le token ? Vous devrez mettre à jour votre cron avec le nouveau token.')) {
        return;
    }
    
    const btn = this;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Régénération...';
    
    fetch('{{ route("admin.cron-config.regenerate-token") }}', {
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
        
        if (data.status === 'success') {
            showNotification(data.message, 'success');
            // Mettre à jour la commande HTTP
            const httpCommand = `curl -s "${data.url}" > /dev/null 2>&1`;
            const httpCommandEl = document.getElementById('cronCommandHttp');
            if (httpCommandEl) {
                httpCommandEl.textContent = httpCommand;
            }
            // Recharger la page après 2 secondes
            setTimeout(() => window.location.reload(), 2000);
        } else {
            showNotification(data.message || 'Erreur lors de la régénération', 'error');
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        showNotification('Erreur: ' + error.message, 'error');
    });
});

document.getElementById('testHttpBtn')?.addEventListener('click', function() {
    const btn = this;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Test en cours...';
    
    fetch('{{ route("admin.cron-config.test-http") }}', {
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
        
        if (data.status === 'success') {
            showNotification('Route HTTP accessible : ' + (data.response?.message || 'OK'), 'success');
        } else {
            showNotification('Erreur : ' + (data.message || 'Erreur inconnue'), 'error');
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        showNotification('Erreur: ' + error.message, 'error');
    });
});
</script>
@endpush
@endsection

