@extends('layouts.admin')

@section('title', 'Gestion SEO')

@section('content')
<div class="p-6">
        <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Gestion SEO</h1>
        <div class="space-x-4">
            <button type="button" onclick="validateSeoForGoogle()" 
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition">
                <i class="fas fa-check-circle mr-2"></i>Valider pour Google
            </button>
            <a href="{{ route('admin.seo.pages') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition">
                <i class="fas fa-cog mr-2"></i>Configuration par Page
            </a>
        </div>
    </div>
    
    <!-- Zone d'affichage des résultats de validation -->
    <div id="validationResults" class="hidden mb-6"></div>
    
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <i class="fas fa-exclamation-circle mr-2"></i>
        {{ session('error') }}
    </div>
    @endif

    @if(session('bimi_error'))
    <div class="bg-orange-100 border border-orange-400 text-orange-800 px-4 py-3 rounded mb-4">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <strong>Erreur upload logo BIMI :</strong> {{ session('bimi_error') }}
        <p class="text-sm mt-2">
            <i class="fas fa-info-circle mr-1"></i>
            Vérifiez les logs Laravel pour plus de détails : <code>storage/logs/laravel.log</code>
        </p>
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

    <form action="{{ route('admin.seo.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Meta Tags</h2>
                <button type="button" onclick="generateSeoWithAI()" 
                        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-magic mr-2"></i>Générer avec l'IA
                </button>
            </div>
            
            <div class="mb-4">
                <label for="meta_title" class="block text-sm font-medium mb-2">Titre Meta</label>
                <input type="text" id="meta_title" name="meta_title" 
                       value="{{ $seoConfig['meta_title'] ?? '' }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            
            <div class="mb-4">
                <label for="meta_description" class="block text-sm font-medium mb-2">Description Meta</label>
                <textarea id="meta_description" name="meta_description" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ $seoConfig['meta_description'] ?? '' }}</textarea>
            </div>
            
            <div class="mb-4">
                <label for="meta_keywords" class="block text-sm font-medium mb-2">Mots-clés</label>
                <input type="text" id="meta_keywords" name="meta_keywords" 
                       value="{{ $seoConfig['meta_keywords'] ?? '' }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Réseaux Sociaux</h2>
            
            <div class="mb-4">
                <label for="og_title" class="block text-sm font-medium mb-2">Titre Open Graph</label>
                <input type="text" id="og_title" name="og_title" 
                       value="{{ $seoConfig['og_title'] ?? '' }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            
            <div class="mb-4">
                <label for="og_description" class="block text-sm font-medium mb-2">Description Open Graph</label>
                <textarea id="og_description" name="og_description" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ $seoConfig['og_description'] ?? '' }}</textarea>
            </div>
            
            <div class="mb-4">
                <label for="og_image" class="block text-sm font-medium mb-2">Image Open Graph</label>
                <input type="file" id="og_image" name="og_image" accept="image/*"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
                @if(!empty($seoConfig['og_image']))
                <img src="{{ asset($seoConfig['og_image']) }}" alt="Image OG" class="mt-2 w-32 h-20 object-cover rounded">
                @endif
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">🌐 Favicons et Icônes</h2>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Génération automatique :</strong> En uploadant une image source, toutes les tailles nécessaires seront générées automatiquement (16x16, 32x32, 48x48, 96x96, 192x192, 512x512, Apple Touch Icon 180x180).
                </p>
            </div>
            
            <div class="mb-4">
                <label for="favicon" class="block text-sm font-medium mb-2">
                    Favicon Source (PNG, JPG, GIF)
                    <span class="text-xs text-gray-500">- Toutes les tailles seront générées automatiquement</span>
                </label>
                <input type="file" id="favicon" name="favicon" accept="image/png,image/jpeg,image/jpg,image/gif"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <p class="text-xs text-gray-500 mt-1">Format recommandé : PNG carré, minimum 512x512px</p>
                @if(!empty($seoConfig['favicon']))
                <div class="mt-3 flex items-center gap-4">
                    <img src="{{ asset($seoConfig['favicon']) }}" alt="Favicon source" class="w-16 h-16 object-cover rounded border border-gray-200">
                    <div class="text-sm text-gray-600">
                        <p class="font-medium">Favicon source actuel</p>
                        <p class="text-xs text-gray-500">Toutes les tailles sont générées depuis cette image</p>
                    </div>
                </div>
                @endif
            </div>
            
            <div class="mb-4">
                <label for="favicon_svg" class="block text-sm font-medium mb-2">
                    Favicon SVG (optionnel)
                    <span class="text-xs text-gray-500">- Pour les navigateurs modernes</span>
                </label>
                <input type="file" id="favicon_svg" name="favicon_svg" accept="image/svg+xml"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <p class="text-xs text-gray-500 mt-1">Format SVG pour une meilleure qualité sur tous les écrans</p>
                @if(!empty($seoConfig['favicon_svg']))
                <div class="mt-3">
                    <img src="{{ asset($seoConfig['favicon_svg']) }}" alt="Favicon SVG" class="w-16 h-16 object-cover rounded border border-gray-200">
                    <p class="text-xs text-gray-500 mt-1">Favicon SVG actuel</p>
                </div>
                @endif
            </div>
            
            <div class="mb-4">
                <label for="apple_touch_icon" class="block text-sm font-medium mb-2">
                    Apple Touch Icon (optionnel - 180x180px)
                    <span class="text-xs text-gray-500">- Généré automatiquement depuis le favicon source</span>
                </label>
                <input type="file" id="apple_touch_icon" name="apple_touch_icon" accept="image/png,image/jpeg"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <p class="text-xs text-gray-500 mt-1">Si non fourni, sera généré automatiquement en 180x180px</p>
                @php
                    $appleIcon = $seoConfig['apple_touch_icon'] ?? 'favicons/apple-touch-icon.png';
                @endphp
                @if(file_exists(public_path($appleIcon)))
                <div class="mt-3">
                    <img src="{{ asset($appleIcon) }}" alt="Apple Touch Icon" class="w-20 h-20 object-cover rounded border border-gray-200">
                    <p class="text-xs text-gray-500 mt-1">Apple Touch Icon actuel (180x180px)</p>
                </div>
                @endif
            </div>
            
            <!-- Aperçu des favicons générés -->
            @php
                $generatedSizes = ['16x16', '32x32', '48x48', '96x96', '192x192', '512x512'];
            @endphp
            @if(!empty($seoConfig['favicon_96x96']) || file_exists(public_path('favicons/favicon-96x96.png')))
            <div class="mt-4 pt-4 border-t border-gray-200">
                <h3 class="text-sm font-semibold mb-3">Aperçu des favicons générés :</h3>
                <div class="grid grid-cols-3 md:grid-cols-6 gap-3">
                    @foreach($generatedSizes as $size)
                        @php
                            $iconPath = $seoConfig["favicon_{$size}"] ?? "favicons/favicon-{$size}.png";
                        @endphp
                        @if(file_exists(public_path($iconPath)))
                        <div class="text-center">
                            <img src="{{ asset($iconPath) }}" alt="Favicon {{ $size }}" 
                                 class="w-full h-auto border border-gray-200 rounded mx-auto mb-1"
                                 style="max-width: {{ explode('x', $size)[0] }}px;">
                            <p class="text-xs text-gray-500">{{ $size }}</p>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">📧 Logo BIMI (Email)</h2>
            
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-purple-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Logo BIMI :</strong> Le logo affiché à côté de vos emails dans Gmail et autres clients de messagerie. 
                    Le logo doit être en format SVG et respecter les contraintes BIMI.
                </p>
            </div>
            
            <div class="mb-4">
                <label for="bimi_logo" class="block text-sm font-medium mb-2">
                    Logo SVG BIMI
                    <span class="text-xs text-gray-500">- Format SVG requis (200x200px recommandé)</span>
                </label>
                <input type="file" id="bimi_logo" name="bimi_logo" accept="image/svg+xml"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <p class="text-xs text-gray-500 mt-1">
                    Format SVG 1.1 ou SVG Tiny 1.2. Pas de scripts, animations ou références externes.
                </p>
                @php
                    $bimiLogoPath = 'logo/logo.svg';
                @endphp
                @if(file_exists(public_path($bimiLogoPath)))
                @php
                    $logoFilemtime = filemtime(public_path($bimiLogoPath));
                    $logoUrl = asset($bimiLogoPath) . '?v=' . $logoFilemtime;
                @endphp
                <div class="mt-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0">
                            <img src="{{ $logoUrl }}" alt="Logo BIMI" class="w-20 h-20 object-contain rounded border border-gray-300 bg-white p-2" onerror="this.src='{{ asset($bimiLogoPath) }}?v=' + Date.now()">
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-sm text-gray-700">Logo BIMI actuel</p>
                            <p class="text-xs text-gray-500 mt-1">Chemin : <code class="bg-gray-100 px-1 rounded">public/{{ $bimiLogoPath }}</code></p>
                            <p class="text-xs text-gray-500 mt-1">Taille : {{ number_format(filesize(public_path($bimiLogoPath))) }} octets</p>
                            <p class="text-xs text-gray-500 mt-1">Modifié : {{ date('d/m/Y H:i:s', $logoFilemtime) }}</p>
                            <p class="text-xs text-gray-500 mt-1">URL : <a href="{{ $logoUrl }}" target="_blank" class="text-blue-600 hover:underline" onclick="this.href += '&refresh=' + Date.now(); return true;">{{ asset($bimiLogoPath) }}</a></p>
                            <button type="button" onclick="location.reload()" class="mt-2 text-xs bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700">
                                <i class="fas fa-sync-alt mr-1"></i>Actualiser l'aperçu
                            </button>
                        </div>
                    </div>
                </div>
                @else
                <div class="mt-3 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                    <p class="text-sm text-yellow-800">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Aucun logo BIMI trouvé. Le logo doit être placé dans <code class="bg-yellow-100 px-1 rounded">public/logo/logo.svg</code>
                    </p>
                </div>
                @endif
            </div>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-link mr-2"></i>
                    <strong>Configuration DNS BIMI requise :</strong> Pour que le logo s'affiche dans Gmail, vous devez configurer un enregistrement DNS TXT :
                    <code class="block mt-2 bg-blue-100 px-2 py-1 rounded text-xs">default._bimi.normesrenovationbretagne.fr. TXT "v=BIMI1; l=https://normesrenovationbretagne.fr/logo/logo.svg;"</code>
                    <a href="{{ url('/BIMI_SETUP.md') }}" target="_blank" class="text-blue-600 hover:underline mt-2 inline-block">
                        Voir la documentation complète →
                    </a>
                </p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Analytics & Tracking</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="google_analytics" class="block text-sm font-medium mb-2">Google Analytics ID</label>
                    <input type="text" id="google_analytics" name="google_analytics" 
                           value="{{ $seoConfig['google_analytics'] ?? '' }}"
                           placeholder="G-XXXXXXXXXX"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label for="facebook_pixel" class="block text-sm font-medium mb-2">Facebook Pixel ID</label>
                    <input type="text" id="facebook_pixel" name="facebook_pixel" 
                           value="{{ $seoConfig['facebook_pixel'] ?? '' }}"
                           placeholder="123456789012345"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label for="google_ads" class="block text-sm font-medium mb-2">Google Ads ID</label>
                    <input type="text" id="google_ads" name="google_ads" 
                           value="{{ $seoConfig['google_ads'] ?? '' }}"
                           placeholder="AW-XXXXXXXXX"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Moteurs de Recherche</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="google_search_console" class="block text-sm font-medium mb-2">Google Search Console</label>
                    <input type="text" id="google_search_console" name="google_search_console" 
                           value="{{ $seoConfig['google_search_console'] ?? '' }}"
                           placeholder="Code de vérification Google"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label for="bing_webmaster" class="block text-sm font-medium mb-2">Bing Webmaster Tools</label>
                    <input type="text" id="bing_webmaster" name="bing_webmaster" 
                           value="{{ $seoConfig['bing_webmaster'] ?? '' }}"
                           placeholder="Code de vérification Bing"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Données Structurées</h2>
            
            <div class="mb-4">
                <label for="schema_markup" class="block text-sm font-medium mb-2">JSON-LD Schema Markup</label>
                <textarea id="schema_markup" name="schema_markup" rows="5"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md font-mono text-sm">{{ $seoConfig['schema_markup'] ?? '' }}</textarea>
            </div>
        </div>
        
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-blue-800">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>Note :</strong> Les paramètres d'indexation (Sitemap, Robots.txt, Robots Meta Tags) ont été déplacés dans la section 
                <a href="{{ route('admin.indexation.index') }}" class="text-blue-600 hover:underline font-semibold">Indexation</a>.
            </p>
        </div>

        <div class="text-center">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg">
                Sauvegarder la Configuration SEO
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function validateSeoForGoogle() {
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Validation en cours...';
    button.disabled = true;
    
    const resultsDiv = document.getElementById('validationResults');
    resultsDiv.classList.remove('hidden');
    resultsDiv.innerHTML = '<div class="bg-blue-50 border border-blue-200 rounded-lg p-4"><i class="fas fa-spinner fa-spin mr-2"></i>Validation en cours...</div>';
    
    fetch('{{ route("admin.seo.validate") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            url: window.location.origin
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayValidationResults(data.validation, data.recommendations);
        } else {
            resultsDiv.innerHTML = '<div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">Erreur: ' + (data.error || 'Erreur inconnue') + '</div>';
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        resultsDiv.innerHTML = '<div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">Erreur lors de la validation</div>';
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function displayValidationResults(validation, recommendations) {
    const resultsDiv = document.getElementById('validationResults');
    let html = '<div class="bg-white rounded-lg shadow p-6">';
    html += '<h3 class="text-lg font-semibold mb-4"><i class="fas fa-search mr-2"></i>Résultats de validation Google</h3>';
    
    // Favicon
    html += '<div class="mb-4 p-4 border rounded-lg ' + (validation.favicon.valid ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200') + '">';
    html += '<h4 class="font-semibold mb-2">📌 Favicon</h4>';
    html += '<p class="text-sm mb-2">' + (validation.favicon.valid ? '<span class="text-green-600">✅ Valide</span>' : '<span class="text-red-600">❌ Non conforme</span>') + '</p>';
    
    if (validation.favicon.errors && validation.favicon.errors.length > 0) {
        html += '<ul class="list-disc list-inside text-sm text-red-700 mb-2">';
        validation.favicon.errors.forEach(error => {
            html += '<li>' + error + '</li>';
        });
        html += '</ul>';
    }
    
    if (validation.favicon.warnings && validation.favicon.warnings.length > 0) {
        html += '<ul class="list-disc list-inside text-sm text-yellow-700 mb-2">';
        validation.favicon.warnings.forEach(warning => {
            html += '<li>⚠️ ' + warning + '</li>';
        });
        html += '</ul>';
    }
    
    if (validation.favicon.info && validation.favicon.info.length > 0) {
        html += '<ul class="list-disc list-inside text-sm text-green-700 mb-2">';
        validation.favicon.info.forEach(info => {
            html += '<li>' + info + '</li>';
        });
        html += '</ul>';
    }
    
    if (validation.favicon.favicon_url) {
        html += '<p class="text-xs text-gray-600 mt-2">URL: <a href="' + validation.favicon.favicon_url + '" target="_blank" class="text-blue-600 underline">' + validation.favicon.favicon_url + '</a></p>';
    }
    html += '</div>';
    
    // Image OG
    html += '<div class="mb-4 p-4 border rounded-lg ' + (validation.og_image.valid ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200') + '">';
    html += '<h4 class="font-semibold mb-2">🖼️ Image Open Graph</h4>';
    html += '<p class="text-sm mb-2">' + (validation.og_image.valid ? '<span class="text-green-600">✅ Valide</span>' : '<span class="text-red-600">❌ Non conforme</span>') + '</p>';
    
    if (validation.og_image.errors && validation.og_image.errors.length > 0) {
        html += '<ul class="list-disc list-inside text-sm text-red-700 mb-2">';
        validation.og_image.errors.forEach(error => {
            html += '<li>' + error + '</li>';
        });
        html += '</ul>';
    }
    
    if (validation.og_image.warnings && validation.og_image.warnings.length > 0) {
        html += '<ul class="list-disc list-inside text-sm text-yellow-700 mb-2">';
        validation.og_image.warnings.forEach(warning => {
            html += '<li>⚠️ ' + warning + '</li>';
        });
        html += '</ul>';
    }
    
    if (validation.og_image.info && validation.og_image.info.length > 0) {
        html += '<ul class="list-disc list-inside text-sm text-green-700 mb-2">';
        validation.og_image.info.forEach(info => {
            html += '<li>' + info + '</li>';
        });
        html += '</ul>';
    }
    
    if (validation.og_image.image_url) {
        html += '<p class="text-xs text-gray-600 mt-2">URL: <a href="' + validation.og_image.image_url + '" target="_blank" class="text-blue-600 underline">' + validation.og_image.image_url + '</a></p>';
        html += '<img src="' + validation.og_image.image_url + '" alt="Image OG" class="mt-2 max-w-xs rounded border">';
    }
    html += '</div>';
    
    // Recommandations
    if (recommendations && recommendations.length > 0) {
        html += '<div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">';
        html += '<h4 class="font-semibold mb-2">💡 Recommandations</h4>';
        html += '<ul class="list-disc list-inside text-sm">';
        recommendations.forEach(rec => {
            html += '<li class="mb-1"><strong>' + rec.title + ':</strong> ' + rec.message + '</li>';
        });
        html += '</ul>';
        html += '</div>';
    }
    
    html += '<div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm">';
    html += '<p class="font-semibold mb-2">🔗 Outils de test Google:</p>';
    html += '<ul class="list-disc list-inside space-y-1">';
    html += '<li><a href="https://search.google.com/test/mobile-friendly" target="_blank" class="text-blue-600 underline">Mobile-Friendly Test</a> - Vérifier le favicon</li>';
    html += '<li><a href="https://search.google.com/test/rich-results" target="_blank" class="text-blue-600 underline">Rich Results Test</a> - Vérifier les données structurées</li>';
    html += '<li><a href="https://realfavicongenerator.net/favicon_checker" target="_blank" class="text-blue-600 underline">Favicon Checker</a> - Vérifier le favicon</li>';
    html += '</ul>';
    html += '</div>';
    
    html += '</div>';
    resultsDiv.innerHTML = html;
}

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

function generateSeoWithAI() {
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Génération en cours...';
    button.disabled = true;
    button.classList.add('opacity-75');
    
    fetch('{{ route("admin.seo.generate-ai") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remplir les champs avec les données générées
            if (data.content.meta_title) {
                document.getElementById('meta_title').value = data.content.meta_title;
            }
            if (data.content.meta_description) {
                document.getElementById('meta_description').value = data.content.meta_description;
            }
            if (data.content.meta_keywords) {
                document.getElementById('meta_keywords').value = data.content.meta_keywords;
            }
            if (data.content.og_title) {
                document.getElementById('og_title').value = data.content.og_title;
            }
            if (data.content.og_description) {
                document.getElementById('og_description').value = data.content.og_description;
            }
            
            showNotification('Contenu SEO généré avec succès !', 'success');
        } else {
            showNotification('Erreur lors de la génération: ' + (data.message || 'Erreur inconnue'), 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors de la génération du contenu SEO', 'error');
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

