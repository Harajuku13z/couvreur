@extends('layouts.admin')

@section('title', 'Indexation Google - Simplifié')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2 text-gray-800">
                <i class="fas fa-search mr-2"></i>
                Indexation Google - Interface Simplifiée
            </h1>
            <p class="text-muted">Vérifiez et indexez vos pages facilement</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    @endif

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">URLs Sitemap</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_sitemap'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-sitemap fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Indexées ✅</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['indexed'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Non Indexées ⚠️</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['not_indexed'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Taux Indexation</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_tracked'] > 0 ? round($stats['indexed'] / $stats['total_tracked'] * 100, 1) : 0 }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Rapides -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">⚡ Actions Rapides</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <button onclick="runCommand('verify')" class="btn btn-info btn-block btn-lg" id="verify-btn">
                                <i class="fas fa-search mr-2"></i>
                                Vérifier 50 URLs
                            </button>
                            <small class="text-muted d-block mt-2">Vérifie le statut d'indexation via Google</small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <button onclick="runCommand('index')" class="btn btn-success btn-block btn-lg" id="index-btn">
                                <i class="fas fa-paper-plane mr-2"></i>
                                Indexer 150 URLs
                            </button>
                            <small class="text-muted d-block mt-2">Envoie demande d'indexation à Google</small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <button onclick="refreshStats()" class="btn btn-primary btn-block btn-lg">
                                <i class="fas fa-sync-alt mr-2"></i>
                                Actualiser Stats
                            </button>
                            <small class="text-muted d-block mt-2">Recharge les statistiques</small>
                        </div>
                    </div>

                    <!-- Zone de résultats -->
                    <div id="command-results" class="mt-4" style="display:none;">
                        <div class="alert alert-info">
                            <div id="results-content">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Traitement en cours...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sitemap -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">🗺️ Sitemap</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>URL Sitemap :</strong> 
                        <a href="{{ url('/sitemap.xml') }}" target="_blank" class="text-primary">
                            {{ url('/sitemap.xml') }}
                            <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>

                    <div class="d-flex gap-2">
                        <button onclick="regenerateSitemap()" class="btn btn-warning">
                            <i class="fas fa-sync mr-2"></i>Régénérer Sitemap
                        </button>
                        
                        @if($isGoogleConfigured)
                        <button onclick="submitSitemap()" class="btn btn-success">
                            <i class="fas fa-upload mr-2"></i>Soumettre à Google
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration Google -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        🔐 Configuration Google Search Console
                        @if($isGoogleConfigured)
                            <span class="badge badge-success ml-2">Configuré ✅</span>
                        @else
                            <span class="badge badge-danger ml-2">Non configuré ❌</span>
                        @endif
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.indexation.simple.config') }}">
                        @csrf
                        
                        <div class="form-group">
                            <label for="site_url">URL du site</label>
                            <input type="url" class="form-control" id="site_url" name="site_url" 
                                   value="{{ setting('site_url', request()->getSchemeAndHttpHost()) }}"
                                   required>
                            <small class="form-text text-muted">Votre domaine principal (ex: https://couvreur-chevigny-saint-sauveur.fr)</small>
                        </div>

                        <div class="form-group">
                            <label for="google_credentials">Credentials JSON Google</label>
                            <textarea class="form-control font-monospace small" id="google_credentials" 
                                      name="google_search_console_credentials" rows="6"
                                      placeholder='{"type": "service_account", ...}'>{{ $googleCredentials ?? '' }}</textarea>
                            <small class="form-text text-muted">Collez le JSON de votre compte de service Google</small>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="daily_indexing" 
                                       name="daily_indexing_enabled" value="1" 
                                       {{ $dailyIndexingEnabled ? 'checked' : '' }}>
                                <label class="custom-control-label" for="daily_indexing">
                                    Activer indexation quotidienne automatique (150 URLs/jour à 02h00)
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Sauvegarder Configuration
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Instructions -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow bg-light">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">💡 Comment utiliser</h6>
                </div>
                <div class="card-body">
                    <h6 class="font-weight-bold">Méthode CLI (Recommandée - 100% fiable) :</h6>
                    <div class="bg-dark text-white p-3 rounded mb-3">
                        <code class="text-white">
                            # Voir statistiques<br>
                            php artisan indexation:simple stats<br><br>
                            # Vérifier 100 URLs<br>
                            php artisan indexation:simple verify --limit=100<br><br>
                            # Indexer 150 URLs non indexées<br>
                            php artisan indexation:simple index --limit=150
                        </code>
                    </div>

                    <h6 class="font-weight-bold mt-4">Workflow recommandé :</h6>
                    <ol class="mb-0">
                        <li class="mb-2">
                            <strong>Vérifier URLs</strong> : Cliquez "Vérifier 50 URLs" plusieurs fois (ou CLI)
                        </li>
                        <li class="mb-2">
                            <strong>Indexer importantes</strong> : Cliquez "Indexer 150 URLs" (ou CLI)
                        </li>
                        <li class="mb-2">
                            <strong>Automatiser</strong> : Activez "Indexation quotidienne" dans configuration
                        </li>
                        <li class="mb-2">
                            <strong>Surveiller</strong> : Consultez Google Search Console dans 3-7 jours
                        </li>
                    </ol>

                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Note :</strong> Si les boutons ne fonctionnent pas, utilisez la CLI qui est 100% fiable.
                        Les commandes sont dans le guide <code>INDEXATION_REFONTE_COMPLETE.md</code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Fonction pour exécuter commandes via AJAX
function runCommand(action) {
    const resultsDiv = document.getElementById('command-results');
    const contentDiv = document.getElementById('results-content');
    
    // Afficher zone résultats
    resultsDiv.style.display = 'block';
    contentDiv.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Traitement en cours...';
    
    // Désactiver bouton
    const btn = event.target;
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Traitement...';
    
    let url, method, body;
    
    if (action === 'verify') {
        url = '{{ route("admin.indexation.simple.verify") }}';
        method = 'POST';
        body = JSON.stringify({ limit: 50 });
    } else if (action === 'index') {
        url = '{{ route("admin.indexation.simple.index") }}';
        method = 'POST';
        body = JSON.stringify({});
    } else {
        return;
    }
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: body
    })
    .then(response => response.json())
    .then(data => {
        console.log('Résultat:', data);
        
        if (data.success) {
            let html = '<div class="alert alert-success mb-0">';
            html += '<h5 class="alert-heading"><i class="fas fa-check-circle mr-2"></i>Succès !</h5>';
            
            if (action === 'verify') {
                const stats = data.stats || {};
                html += `<p class="mb-2"><strong>${stats.verified_now || 0} URLs vérifiées</strong></p>`;
                html += `<ul class="mb-0">`;
                html += `<li>✅ Indexées : ${stats.indexed || 0}</li>`;
                html += `<li>⚠️ Non indexées : ${stats.not_indexed || 0}</li>`;
                html += `<li>❌ Erreurs : ${stats.errors || 0}</li>`;
                if (stats.remaining > 0) {
                    html += `<li>🔄 Restantes : ${stats.remaining} (cliquez à nouveau pour continuer)</li>`;
                }
                html += `</ul>`;
            } else if (action === 'index') {
                html += `<p class="mb-0">${data.message || 'Indexation effectuée'}</p>`;
                if (data.success_count) {
                    html += `<p class="mb-0 mt-2">✅ ${data.success_count} URLs envoyées à Google</p>`;
                }
            }
            
            html += '</div>';
            contentDiv.innerHTML = html;
        } else {
            contentDiv.innerHTML = `<div class="alert alert-danger mb-0">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <strong>Erreur :</strong> ${data.message || 'Une erreur est survenue'}
            </div>`;
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        contentDiv.innerHTML = `<div class="alert alert-danger mb-0">
            <i class="fas fa-times-circle mr-2"></i>
            <strong>Erreur réseau :</strong> ${error.message}
            <p class="mb-0 mt-2 small">Utilisez la CLI à la place : <code>php artisan indexation:simple ${action}</code></p>
        </div>`;
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}

function refreshStats() {
    window.location.reload();
}

function regenerateSitemap() {
    if (!confirm('Régénérer le sitemap ?')) return;
    
    const btn = event.target;
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Génération...';
    
    fetch('{{ route("admin.indexation.update-sitemap") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Sitemap régénéré avec succès !');
            window.location.reload();
        } else {
            alert('❌ Erreur : ' + (data.message || 'Erreur inconnue'));
        }
    })
    .catch(error => {
        alert('❌ Erreur : ' + error.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}

function submitSitemap() {
    if (!confirm('Soumettre le sitemap à Google ?\n\nCela peut prendre plusieurs minutes.')) return;
    
    const btn = event.target;
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Envoi...';
    
    fetch('{{ route("admin.indexation.submit-sitemap-to-google") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ filename: 'sitemap.xml' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`✅ Sitemap soumis !\n\n${data.success_count || 0} URLs envoyées à Google`);
            window.location.reload();
        } else {
            alert('❌ Erreur : ' + (data.message || 'Erreur inconnue'));
        }
    })
    .catch(error => {
        alert('❌ Erreur : ' + error.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}
</script>
@endpush
@endsection

