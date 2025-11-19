@extends('layouts.admin')

@section('title', 'Indexation Google')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2 text-gray-800">
                <i class="fas fa-search mr-2"></i>Indexation Google
            </h1>
            <p class="text-muted">Vérifiez et indexez vos pages dans Google</p>
        </div>
    </div>

    <!-- Messages -->
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
            <div class="card border-left-primary shadow py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">URLs Sitemap</div>
                    <div class="h5 mb-0 font-weight-bold">{{ number_format($stats['total_sitemap'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-success shadow py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Indexées ✅</div>
                    <div class="h5 mb-0 font-weight-bold">{{ number_format($stats['indexed'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-warning shadow py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Non Indexées ⚠️</div>
                    <div class="h5 mb-0 font-weight-bold">{{ number_format($stats['not_indexed'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-info shadow py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Taux Indexation</div>
                    <div class="h5 mb-0 font-weight-bold">
                        {{ $stats['total_tracked'] > 0 ? round($stats['indexed'] / $stats['total_tracked'] * 100, 1) : 0 }}%
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Rapides -->
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-header bg-gradient-primary text-white py-3">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-bolt mr-2"></i>Actions Rapides
            </h6>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-4 mb-3">
                    <button onclick="verifierUrls()" class="btn btn-info btn-block btn-lg shadow-sm" id="btn-verify">
                        <i class="fas fa-search-plus fa-2x mb-2 d-block"></i>
                        <span class="d-block">Vérifier 50 URLs</span>
                    </button>
                    <small class="text-muted d-block mt-2">Interroge Google Search Console</small>
                </div>

                <div class="col-md-4 mb-3">
                    <button onclick="indexerUrls()" class="btn btn-success btn-block btn-lg shadow-sm" id="btn-index">
                        <i class="fas fa-rocket fa-2x mb-2 d-block"></i>
                        <span class="d-block">Indexer 150 URLs</span>
                    </button>
                    <small class="text-muted d-block mt-2">Envoie à Google Indexing API</small>
                </div>

                <div class="col-md-4 mb-3">
                    <button onclick="window.location.reload()" class="btn btn-primary btn-block btn-lg shadow-sm">
                        <i class="fas fa-sync-alt fa-2x mb-2 d-block"></i>
                        <span class="d-block">Actualiser Stats</span>
                    </button>
                    <small class="text-muted d-block mt-2">Recharge les données</small>
                </div>
            </div>

            <!-- Zone résultats -->
            <div id="results-zone" class="mt-4" style="display:none;">
                <hr>
                <div id="results-content"></div>
            </div>
        </div>
    </div>

    <!-- Liste des Sitemaps -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">🗺️ Sitemaps du Site</h6>
            <button onclick="regenererSitemap()" class="btn btn-warning btn-sm" id="btn-sitemap">
                <i class="fas fa-sync mr-2"></i>Régénérer Tous
            </button>
        </div>
        <div class="card-body">
            <?php
            $sitemapFiles = glob(public_path('sitemap*.xml'));
            $sitemapFiles = array_filter($sitemapFiles, function($file) {
                return basename($file) !== 'sitemap_index.xml';
            });
            ?>
            
            @if(!empty($sitemapFiles))
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Fichier</th>
                                <th class="text-center">URLs</th>
                                <th class="text-center">Taille</th>
                                <th class="text-center">Modifié</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sitemapFiles as $file)
                            <?php
                            $filename = basename($file);
                            $urlCount = 0;
                            try {
                                $xml = simplexml_load_file($file);
                                if ($xml && isset($xml->url)) {
                                    $urlCount = count($xml->url);
                                }
                            } catch (\Exception $e) {
                                $urlCount = 0;
                            }
                            ?>
                            <tr>
                                <td><strong>{{ $filename }}</strong></td>
                                <td class="text-center">
                                    <span class="badge badge-info badge-pill">{{ number_format($urlCount) }}</span>
                                </td>
                                <td class="text-center">{{ number_format(filesize($file) / 1024, 1) }} KB</td>
                                <td class="text-center text-muted small">{{ date('d/m/Y H:i', filemtime($file)) }}</td>
                                <td class="text-center">
                                    <a href="{{ url($filename) }}" target="_blank" class="btn btn-sm btn-primary mr-1">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                    @if($isGoogleConfigured)
                                    <button onclick="soumettreGoogle('{{ $filename }}')" class="btn btn-sm btn-success">
                                        <i class="fas fa-upload"></i> Soumettre
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="alert alert-info mt-3 mb-0">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Info :</strong> Le sitemap principal est <code>sitemap.xml</code>. 
                    Si vous avez beaucoup d'URLs (> 2000), plusieurs fichiers sont créés automatiquement.
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Aucun sitemap généré. Cliquez sur "Régénérer Tous" pour créer le sitemap.
                </div>
            @endif
        </div>
    </div>

    <!-- Configuration -->
    <div class="card shadow mb-4">
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
            <form method="POST" action="{{ route('admin.indexation.update') }}">
                @csrf
                
                <div class="form-group">
                    <label>URL du site</label>
                    <input type="url" class="form-control" name="site_url" value="{{ $siteUrl }}" required>
                    <small class="form-text text-muted">Ex: https://couvreur-chevigny-saint-sauveur.fr</small>
                </div>

                <div class="form-group">
                    <label>Credentials JSON Google Search Console</label>
                    <textarea class="form-control font-monospace small" name="google_search_console_credentials" rows="8" placeholder='{"type": "service_account", "project_id": "...", ...}'>{{ $googleCredentials }}</textarea>
                    <small class="form-text text-muted">Collez le JSON de votre compte de service</small>
                </div>

                <div class="custom-control custom-switch mb-3">
                    <input type="checkbox" class="custom-control-input" id="daily_indexing" 
                           name="daily_indexing_enabled" value="1" {{ $dailyIndexingEnabled ? 'checked' : '' }}>
                    <label class="custom-control-label" for="daily_indexing">
                        Activer indexation quotidienne automatique (150 URLs/jour à 02h00)
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i>Sauvegarder Configuration
                </button>
            </form>
        </div>
    </div>

    <!-- Instructions CLI -->
    <div class="card shadow bg-light">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">💡 Alternative : Utiliser CLI (100% fiable)</h6>
        </div>
        <div class="card-body">
            <p><strong>Si les boutons ne fonctionnent pas, utilisez ces commandes :</strong></p>
            
            <div class="bg-dark text-white p-3 rounded mb-3">
                <code class="text-white">
                    # Voir statistiques<br>
                    php artisan indexation:simple stats<br><br>
                    
                    # Vérifier 100 URLs<br>
                    php artisan indexation:simple verify --limit=100<br><br>
                    
                    # Indexer 150 URLs non indexées<br>
                    php artisan indexation:simple index --limit=150<br><br>
                    
                    # Vérifier 1 URL spécifique<br>
                    php artisan indexation:simple verify --url="https://..."<br><br>
                    
                    # Indexer 1 URL spécifique<br>
                    php artisan indexation:simple index --url="https://..."
                </code>
            </div>

            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>Guide complet :</strong> Consultez le fichier <code>INDEXATION_REFONTE_COMPLETE.md</code> 
                dans votre projet pour toutes les instructions détaillées.
            </div>
        </div>
    </div>
</div>

<script>
// Fonction vérifier URLs
function verifierUrls() {
    const btn = document.getElementById('btn-verify');
    const resultsZone = document.getElementById('results-zone');
    const resultsContent = document.getElementById('results-content');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Vérification...';
    
    resultsZone.style.display = 'block';
    resultsContent.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin mr-2"></i>Vérification de 50 URLs en cours... Cela peut prendre 2-3 minutes.</div>';
    
    fetch('{{ route("admin.indexation.verify-urls") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ limit: 50 })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const stats = data.stats || {};
            let html = '<div class="alert alert-success">';
            html += '<h5><i class="fas fa-check-circle mr-2"></i>Vérification terminée !</h5>';
            html += `<p><strong>${stats.verified || 0} URLs vérifiées</strong></p>`;
            html += '<ul>';
            html += `<li>✅ Indexées : ${stats.indexed || 0}</li>`;
            html += `<li>⚠️ Non indexées : ${stats.not_indexed || 0}</li>`;
            html += `<li>❌ Erreurs : ${stats.errors || 0}</li>`;
            if (stats.remaining > 0) {
                html += `<li>🔄 Restantes : ${stats.remaining} (cliquez à nouveau pour continuer)</li>`;
            }
            html += '</ul>';
            html += '</div>';
            resultsContent.innerHTML = html;
        } else {
            resultsContent.innerHTML = `<div class="alert alert-danger"><i class="fas fa-times mr-2"></i>${data.message || 'Erreur'}</div>`;
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        resultsContent.innerHTML = `<div class="alert alert-danger">
            <i class="fas fa-times mr-2"></i>Erreur réseau. 
            <p class="mb-0 mt-2">Utilisez CLI : <code>php artisan indexation:simple verify --limit=50</code></p>
        </div>`;
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-search mr-2"></i>Vérifier 50 URLs';
    });
}

// Fonction indexer URLs
function indexerUrls() {
    if (!confirm('Indexer 150 URLs non indexées ?\n\nCela peut prendre 1-2 minutes.')) return;
    
    const btn = document.getElementById('btn-index');
    const resultsZone = document.getElementById('results-zone');
    const resultsContent = document.getElementById('results-content');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Indexation...';
    
    resultsZone.style.display = 'block';
    resultsContent.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin mr-2"></i>Indexation en cours...</div>';
    
    fetch('{{ route("admin.indexation.index-urls") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ limit: 150 })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let html = '<div class="alert alert-success">';
            html += '<h5><i class="fas fa-check-circle mr-2"></i>Indexation terminée !</h5>';
            html += `<p><strong>${data.success_count || 0} URLs envoyées à Google</strong></p>`;
            if (data.failed_count > 0) {
                html += `<p class="text-warning">${data.failed_count} URLs échouées</p>`;
            }
            html += '<p class="mb-0 small">Les pages seront indexées dans 3-7 jours.</p>';
            html += '</div>';
            resultsContent.innerHTML = html;
        } else {
            resultsContent.innerHTML = `<div class="alert alert-danger"><i class="fas fa-times mr-2"></i>${data.message || 'Erreur'}</div>`;
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        resultsContent.innerHTML = `<div class="alert alert-danger">
            <i class="fas fa-times mr-2"></i>Erreur réseau.
            <p class="mb-0 mt-2">Utilisez CLI : <code>php artisan indexation:simple index --limit=150</code></p>
        </div>`;
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Indexer 150 URLs';
    });
}

// Fonction régénérer sitemap
function regenererSitemap() {
    if (!confirm('Régénérer le sitemap ?')) return;
    
    const btn = document.getElementById('btn-sitemap');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Génération...';
    
    fetch('{{ route("admin.indexation.update-sitemap") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
        btn.innerHTML = '<i class="fas fa-sync mr-2"></i>Régénérer Sitemap';
    });
}

// Fonction soumettre sitemap
function soumettreGoogle(filename) {
    if (!confirm(`Soumettre "${filename}" à Google ?\n\nCela va indexer jusqu'à 200 URLs.\nDurée : 1-2 minutes.`)) return;
    
    const btn = event.target;
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    fetch('{{ route("admin.indexation.submit-sitemap") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ filename: filename })
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
@endsection

