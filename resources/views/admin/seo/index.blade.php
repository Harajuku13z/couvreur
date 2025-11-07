@extends('layouts.admin')

@section('title', 'Gestion SEO')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Gestion SEO</h1>
        <div class="space-x-4">
            <a href="{{ route('admin.seo.pages') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition">
                <i class="fas fa-cog mr-2"></i>Configuration par Page
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

