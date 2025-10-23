@extends('layouts.admin')

@section('title', 'Gestion SEO')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Gestion SEO</h1>
    
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif

    <form action="{{ route('admin.seo.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Meta Tags</h2>
            
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
            <h2 class="text-lg font-semibold mb-4">Configuration Technique</h2>
            
            <div class="mb-4">
                <label for="favicon" class="block text-sm font-medium mb-2">Favicon</label>
                <input type="file" id="favicon" name="favicon" accept="image/*"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
                @if(!empty($seoConfig['favicon']))
                <img src="{{ asset($seoConfig['favicon']) }}" alt="Favicon" class="mt-2 w-8 h-8 object-cover rounded">
                @endif
            </div>
            
            <div class="mb-4">
                <label for="apple_touch_icon" class="block text-sm font-medium mb-2">Apple Touch Icon</label>
                <input type="file" id="apple_touch_icon" name="apple_touch_icon" accept="image/*"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
                @if(!empty($seoConfig['apple_touch_icon']))
                <img src="{{ asset($seoConfig['apple_touch_icon']) }}" alt="Apple Touch Icon" class="mt-2 w-12 h-12 object-cover rounded">
                @endif
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

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Sitemap & Robots</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-md font-medium mb-3 text-gray-700">Sitemap XML</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium">Sitemap automatique</p>
                                <p class="text-sm text-gray-600">Généré automatiquement</p>
                            </div>
                            <a href="{{ url('/sitemap.xml') }}" target="_blank" 
                               class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                                Voir le sitemap
                            </a>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium">Soumettre à Google</p>
                                <p class="text-sm text-gray-600">URL pour Google Search Console</p>
                            </div>
                            <button type="button" onclick="copyToClipboard('{{ url('/sitemap.xml') }}')" 
                                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                                Copier l'URL
                            </button>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-md font-medium mb-3 text-gray-700">Robots.txt</h3>
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
                Sauvegarder la Configuration SEO
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Afficher une notification de succès
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
</script>
@endpush

