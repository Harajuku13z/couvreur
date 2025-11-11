@extends('layouts.app')

@php
    // Utiliser les titres et descriptions complets (sans troncature)
    $pageTitle = $article->meta_title ?: $article->title;
    $pageDescription = $article->meta_description;
    
    // Filtrer les mots-clés pour enlever les mots vides
    $metaKeywords = $article->meta_keywords;
    if ($metaKeywords) {
        $keywordsArray = array_map('trim', explode(',', $metaKeywords));
        $stopWords = ['votre', 'notre', 'mieux', 'bien', 'bon', 'meilleur', 'orange', 'le', 'la', 'les'];
        $filteredKeywords = array_filter($keywordsArray, function($kw) use ($stopWords) {
            $kwLower = strtolower(trim($kw));
            return !empty($kw) && strlen($kw) >= 3 && !in_array($kwLower, $stopWords);
        });
        $metaKeywords = !empty($filteredKeywords) ? implode(', ', $filteredKeywords) : null;
    }
@endphp
@section('title', $pageTitle)
@section('description', $pageDescription)
@section('keywords', $metaKeywords)

@php
    // Passer les métadonnées spécifiques à l'article au layout principal
    // IMPORTANT: Ne pas tronquer les titres - utiliser le titre complet
    $pageTitle = $article->meta_title ?: $article->title;
    $pageDescription = $article->meta_description;
    $pageKeywords = $article->meta_keywords;
    $pageImage = $article->featured_image ? asset($article->featured_image) : asset(setting('default_blog_og_image', 'images/og-blog.jpg'));
    $pageType = 'article';
    $currentPage = 'article';
    
    // S'assurer que les titres Open Graph et Twitter utilisent le titre complet (sans troncature)
    $ogTitle = $pageTitle;
    $twitterTitle = $pageTitle;
    $ogDescription = $pageDescription;
    $twitterDescription = $pageDescription;
@endphp

@push('head')
<style>
    :root {
        --primary-color: {{ setting('primary_color', '#3b82f6') }};
        --secondary-color: {{ setting('secondary_color', '#1e40af') }};
        --accent-color: {{ setting('accent_color', '#f59e0b') }};
    }
    
    /* S'assurer que le titre de l'article ne soit jamais tronqué */
    .article-hero h1 {
        word-wrap: break-word;
        overflow-wrap: break-word;
        hyphens: auto;
        white-space: normal;
        text-overflow: unset;
        overflow: visible;
        max-width: 100%;
    }
</style>
<!-- Métadonnées spécifiques aux articles -->
<meta property="article:published_time" content="{{ $article->created_at->toISOString() }}">
<meta property="article:author" content="{{ setting('company_name', 'Sauser Couverture') }}">
<meta property="article:section" content="Blog">
<meta property="article:tag" content="{{ $article->focus_keyword ?? 'Rénovation' }}">

<style>
/* Styles pour le contenu généré par ChatGPT avec Tailwind CSS */
.article-content {
    line-height: 1.8;
    color: #374151;
    font-size: 1.1rem;
}

.article-content .text-link {
    color: var(--primary-color, #3b82f6);
    text-decoration: underline;
    word-break: break-all;
}

.article-content .text-link:hover {
    color: var(--secondary-color, #1e40af);
    text-decoration: none;
}

/* S'assurer que le contenu Tailwind s'affiche correctement */
.article-content .max-w-7xl {
    max-width: 80rem;
}

.article-content .text-4xl {
    font-size: 2.25rem;
    line-height: 2.5rem;
}

.article-content .text-2xl {
    font-size: 1.5rem;
    line-height: 2rem;
}

.article-content .text-xl {
    font-size: 1.25rem;
    line-height: 1.75rem;
}

.article-content .bg-white {
    background-color: #ffffff;
}

.article-content .bg-green-50 {
    background-color: #f0fdf4;
}

.article-content .bg-blue-50 {
    background-color: #eff6ff;
}

.article-content .rounded-xl {
    border-radius: 0.75rem;
}

.article-content .shadow {
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

.article-content .hover\:shadow-lg:hover {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.article-content .transition {
    transition-property: color, background-color, border-color, text-decoration-color, fill, stroke;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

.article-content .duration-300 {
    transition-duration: 300ms;
}

.article-content .text-gray-900 {
    color: #111827;
}

.article-content .text-gray-800 {
    color: #1f2937;
}

.article-content .text-gray-700 {
    color: #374151;
}

.article-content .text-blue-500 {
    color: #3b82f6;
}

.article-content .text-white {
    color: #ffffff;
}

.article-content .bg-blue-500 {
    background-color: #3b82f6;
}

.article-content .hover\:bg-blue-600:hover {
    background-color: #2563eb;
}

.article-content .font-bold {
    font-weight: 700;
}

.article-content .font-semibold {
    font-weight: 600;
}

.article-content .mb-2 {
    margin-bottom: 0.5rem;
}

.article-content .mb-4 {
    margin-bottom: 1rem;
}

.article-content .mb-6 {
    margin-bottom: 1.5rem;
}

.article-content .my-4 {
    margin-top: 1rem;
    margin-bottom: 1rem;
}

.article-content .p-4 {
    padding: 1rem;
}

.article-content .p-6 {
    padding: 1.5rem;
}

.article-content .px-6 {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
}

.article-content .py-3 {
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
}

.article-content .rounded-lg {
    border-radius: 0.5rem;
}

.article-content .inline-block {
    display: inline-block;
}

.article-content .text-center {
    text-align: center;
}

.article-content .list-disc {
    list-style-type: disc;
}

.article-content .list-inside {
    list-style-position: inside;
}

/* Styles pour les titres et éléments HTML de base */
.article-content h1 {
    font-size: 2.25rem;
    line-height: 2.5rem;
    font-weight: 700;
    color: #111827;
    margin-top: 1.5rem;
    margin-bottom: 1rem;
}

.article-content h2 {
    font-size: 2rem;
    line-height: 2.5rem;
    font-weight: 700;
    color: #111827;
    margin-top: 2.5rem;
    margin-bottom: 1.25rem;
    padding-bottom: 0.75rem;
    border-bottom: 3px solid var(--primary-color, #3b82f6);
}

.article-content h3 {
    font-size: 1.5rem;
    line-height: 2rem;
    font-weight: 600;
    color: #1f2937;
    margin-top: 2rem;
    margin-bottom: 1rem;
    padding-left: 0.75rem;
    border-left: 4px solid var(--accent-color, #f59e0b);
}

.article-content h4 {
    font-size: 1.25rem;
    line-height: 1.75rem;
    font-weight: 600;
    color: #374151;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
}

.article-content p {
    margin-bottom: 1.25rem;
    line-height: 1.8;
    color: #374151;
    text-align: justify;
}

.article-content br {
    display: block;
    content: "";
    margin-top: 0.5rem;
}

.article-content ul,
.article-content ol {
    margin-bottom: 1rem;
    padding-left: 1.5rem;
}

.article-content ul.list-icon {
    list-style: none;
    padding-left: 0;
    margin: 1.5rem 0;
}

.article-content ul.list-icon li {
    position: relative;
    padding-left: 2rem;
    margin-bottom: 0.75rem;
    line-height: 1.75;
}

.article-content ul.list-icon li::before {
    content: "✓";
    position: absolute;
    left: 0;
    color: var(--primary-color, #3b82f6);
    font-weight: bold;
    font-size: 1.2rem;
}

.article-content ul:not(.list-icon) {
    list-style-type: disc;
    margin: 1.5rem 0;
    padding-left: 2rem;
}

.article-content ol {
    list-style-type: decimal;
    margin: 1.5rem 0;
    padding-left: 2rem;
}

.article-content li {
    margin-bottom: 0.75rem;
    line-height: 1.8;
}

.article-content strong {
    font-weight: 700;
    color: #111827;
}

.article-content em {
    font-style: italic;
}

.article-content a {
    color: #3b82f6;
    text-decoration: underline;
}

.article-content a:hover {
    color: #2563eb;
}

.article-content img {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
    margin: 1.5rem 0;
}

.article-content blockquote {
    border-left: 4px solid #3b82f6;
    padding-left: 1rem;
    margin: 1.5rem 0;
    font-style: italic;
    color: #6b7280;
}

.article-content code {
    background-color: #f3f4f6;
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
    font-family: monospace;
    font-size: 0.875em;
}

.article-content pre {
    background-color: #1f2937;
    color: #f9fafb;
    padding: 1rem;
    border-radius: 0.5rem;
    overflow-x: auto;
    margin: 1.5rem 0;
}

.article-content pre code {
    background-color: transparent;
    padding: 0;
    color: inherit;
}

.article-content hr {
    border: none;
    border-top: 2px solid #e5e7eb;
    margin: 2rem 0;
}

/* Styles pour la FAQ */
.article-content #faq {
    margin-top: 3rem;
    margin-bottom: 3rem;
    padding: 2rem;
    background-color: #f9fafb;
    border-radius: 0.75rem;
    border: 1px solid #e5e7eb;
}

.article-content #faq h2 {
    font-size: 1.875rem;
    font-weight: 700;
    color: #111827;
    margin-top: 0;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 3px solid var(--primary-color, #3b82f6);
}

.article-content #faq > div[itemscope] {
    margin-bottom: 2rem;
    padding: 1.5rem;
    background-color: #ffffff;
    border-radius: 0.5rem;
    border-left: 4px solid var(--primary-color, #3b82f6);
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.article-content #faq h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    margin-top: 0;
    margin-bottom: 1rem;
    padding-left: 0;
    border-left: none;
}

.article-content #faq div[itemscope][itemprop="acceptedAnswer"] {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.article-content #faq div[itemscope][itemprop="acceptedAnswer"] p {
    margin-bottom: 0;
    color: #374151;
    line-height: 1.75;
}

/* Styles pour le CTA final */
.article-content .cta-final {
    margin-top: 3rem;
    margin-bottom: 3rem;
    padding: 2.5rem;
    background: linear-gradient(135deg, var(--primary-color, #3b82f6) 0%, var(--secondary-color, #1e40af) 100%);
    border-radius: 1rem;
    color: #ffffff;
    text-align: center;
}

.article-content .cta-final h3 {
    font-size: 1.875rem;
    font-weight: 700;
    color: #ffffff;
    margin-top: 0;
    margin-bottom: 1.5rem;
    padding-left: 0;
    border-left: none;
}

.article-content .cta-final p {
    color: #ffffff;
    margin-bottom: 1.5rem;
}

.article-content .cta-final ul {
    text-align: left;
    display: inline-block;
    margin-bottom: 2rem;
    color: #ffffff;
}

.article-content .cta-final li {
    margin-bottom: 0.75rem;
    color: #ffffff;
}

.article-content .cta-buttons {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    align-items: center;
}

@media (min-width: 640px) {
    .article-content .cta-buttons {
        flex-direction: row;
        justify-content: center;
    }
}

.article-content .cta-buttons a {
    display: inline-block;
    padding: 1rem 2rem;
    border-radius: 0.5rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.article-content .cta-buttons .btn-primary {
    background-color: #ffffff;
    color: var(--primary-color, #3b82f6);
}

.article-content .cta-buttons .btn-primary:hover {
    background-color: #f3f4f6;
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.article-content .cta-buttons .btn-secondary {
    background-color: rgba(255, 255, 255, 0.2);
    color: #ffffff;
    border: 2px solid #ffffff;
}

.article-content .cta-buttons .btn-secondary:hover {
    background-color: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Styles pour la conclusion */
.article-content .article-conclusion {
    margin-top: 3rem;
    margin-bottom: 2rem;
    padding: 2rem;
    background-color: #f0f9ff;
    border-radius: 0.75rem;
    border-left: 4px solid var(--accent-color, #f59e0b);
}

.article-content .article-conclusion h2 {
    font-size: 1.75rem;
    font-weight: 700;
    color: #111827;
    margin-top: 0;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid var(--accent-color, #f59e0b);
}

.article-content .article-conclusion p {
    color: #374151;
    line-height: 1.8;
}

.article-content table {
    width: 100%;
    border-collapse: collapse;
    margin: 1.5rem 0;
}

.article-content th,
.article-content td {
    padding: 0.75rem;
    border: 1px solid #e5e7eb;
    text-align: left;
}

.article-content th {
    background-color: #f9fafb;
    font-weight: 600;
    color: #111827;
}

.article-content .list-decimal {
    list-style-type: decimal;
}

/* Responsive */
@media (max-width: 768px) {
    .article-content .max-w-7xl {
        max-width: 100%;
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .article-content .text-4xl {
        font-size: 1.875rem;
        line-height: 2.25rem;
    }
    
    .article-content .text-2xl {
        font-size: 1.25rem;
        line-height: 1.75rem;
    }
    }
</style>

<!-- Google Analytics -->
@if(setting('google_analytics_id'))
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id={{ setting('google_analytics_id') }}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{{ setting('google_analytics_id') }}');
  
  // Track article view
  gtag('event', 'article_view', {
    'article_title': '{{ $article->title }}',
    'article_category': '{{ $article->category }}',
    'page_location': '{{ request()->url() }}'
  });
</script>
@endif

<!-- JavaScript pour les boutons de partage -->
<script>
function copyToClipboard(text) {
    // Utiliser l'API moderne Clipboard si disponible
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(function() {
            showCopyMessage();
        }).catch(function(err) {
            console.error('Erreur lors de la copie: ', err);
            fallbackCopyTextToClipboard(text);
        });
    } else {
        // Fallback pour les navigateurs plus anciens
        fallbackCopyTextToClipboard(text);
    }
}

function fallbackCopyTextToClipboard(text) {
    var textArea = document.createElement("textarea");
    textArea.value = text;
    
    // Éviter le défilement vers le bas sur iOS
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";
    textArea.style.opacity = "0";
    
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        var successful = document.execCommand('copy');
        if (successful) {
            showCopyMessage();
        } else {
            console.error('Fallback: Impossible de copier le texte');
        }
    } catch (err) {
        console.error('Fallback: Erreur lors de la copie ', err);
    }
    
    document.body.removeChild(textArea);
}

function showCopyMessage() {
    var message = document.getElementById('copy-message');
    message.classList.remove('hidden');
    
    // Masquer le message après 3 secondes
    setTimeout(function() {
        message.classList.add('hidden');
    }, 3000);
}

// Fonction pour basculer l'affichage des boutons de partage flottants
function toggleShareButtons() {
    var shareButtons = document.getElementById('share-buttons');
    var mainButton = document.querySelector('button[onclick="toggleShareButtons()"]');
    
    if (shareButtons.classList.contains('hidden')) {
        shareButtons.classList.remove('hidden');
        mainButton.style.transform = 'rotate(45deg)';
        mainButton.style.backgroundColor = '#dc2626'; // Rouge pour indiquer la fermeture
    } else {
        shareButtons.classList.add('hidden');
        mainButton.style.transform = 'rotate(0deg)';
        mainButton.style.backgroundColor = '#2563eb'; // Bleu par défaut
    }
}

// Améliorer l'expérience utilisateur sur mobile
document.addEventListener('DOMContentLoaded', function() {
    // Ajouter des effets hover sur les boutons de partage
    var shareButtons = document.querySelectorAll('.group');
    shareButtons.forEach(function(button) {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    });
    
    // Fermer les boutons flottants quand on clique ailleurs
    document.addEventListener('click', function(event) {
        var shareContainer = document.querySelector('.fixed.bottom-4.right-4');
        var shareButtons = document.getElementById('share-buttons');
        
        if (shareContainer && !shareContainer.contains(event.target) && !shareButtons.classList.contains('hidden')) {
            toggleShareButtons();
        }
    });
    
    // Ajouter une animation d'apparition progressive pour les boutons flottants
    var floatingButtons = document.querySelectorAll('#share-buttons a, #share-buttons button');
    floatingButtons.forEach(function(button, index) {
        button.style.transitionDelay = (index * 0.1) + 's';
    });
});
</script>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section avec image mise en avant -->
    <div class="relative min-h-[60vh] flex items-center justify-center overflow-hidden"
         @if($article->featured_image)
         style="background-image: url('{{ asset($article->featured_image) }}'); background-size: cover; background-position: center; background-attachment: scroll;"
         @else
         style="background: linear-gradient(135deg, var(--primary-color, #3b82f6), var(--secondary-color, #1e40af));"
         @endif>
        <!-- Overlay sombre pour améliorer la lisibilité -->
        @if($article->featured_image)
        <div class="absolute inset-0 bg-black/50 z-0"></div>
        @else
        <div class="absolute inset-0 bg-gradient-to-r from-black/30 to-black/50 z-0"></div>
        @endif
        
        <div class="max-w-6xl mx-auto px-4 text-center text-white relative z-10 py-16 article-hero">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 drop-shadow-lg">{{ $article->title }}</h1>
            <div class="flex items-center justify-center space-x-4 flex-wrap gap-2">
                @if($article->published_at)
                <span class="flex items-center bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full text-sm">
                    <i class="fas fa-calendar mr-2"></i>Publié le {{ $article->published_at->format('d/m/Y') }}
                </span>
                @endif
                <span class="flex items-center bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full text-sm">
                    <i class="fas fa-clock mr-2"></i>Lecture
                </span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-4 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Article Content -->
            <div class="lg:col-span-3">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="p-8">
                        <!-- Article Content - HTML généré directement par ChatGPT -->
                        <div class="article-content prose prose-lg max-w-none">
                            @php
                                // Le contenu est déjà en HTML depuis ChatGPT
                                $content = \App\Helpers\InternalLinkingHelper::generateInternalLinks($article->content_html, 'article');
                                
                                // Convertir les URLs en liens cliquables (pour les URLs qui ne sont pas déjà dans des balises <a>)
                                // On évite de modifier les liens existants
                                $content = preg_replace_callback(
                                    '/(?<!href=["\'])(?<!>)(https?:\/\/[^\s<>"\'\)]+)(?![^<]*<\/a>)/',
                                    function($matches) {
                                        return '<a href="' . $matches[1] . '" target="_blank" rel="noopener noreferrer" class="text-link">' . $matches[1] . '</a>';
                                    },
                                    $content
                                );
                            @endphp
                            {!! $content !!}
                        </div>
                        
                        {{-- Liens suggérés --}}
                        @php
                            $suggestedLinks = \App\Helpers\InternalLinkingHelper::getSuggestedLinks('article', 5);
                        @endphp
                        @if(count($suggestedLinks) > 0)
                        <div class="mt-12 p-6 rounded-lg border" style="background-color: rgba(var(--primary-color-rgb, 59, 130, 246), 0.1); border-color: var(--primary-color, #3b82f6);">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">
                                <i class="fas fa-link mr-2 text-primary"></i>Articles et Services Connexes
                            </h3>
                            <div class="grid md:grid-cols-2 gap-4">
                                @foreach($suggestedLinks as $link)
                                <a href="{{ $link['url'] }}" class="flex items-center p-3 bg-white rounded-lg hover:shadow-md transition-shadow">
                                    <i class="fas {{ $link['type'] === 'service' ? 'fa-tools' : 'fa-newspaper' }} mr-3" style="color: var(--primary-color, #3b82f6);"></i>
                                    <span class="text-gray-800 hover:underline" style="--hover-color: var(--primary-color, #3b82f6);">{{ $link['title'] }}</span>
                                    <i class="fas fa-arrow-right ml-auto text-gray-400"></i>
                                </a>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        
                        <!-- Boutons de partage social -->
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                <div class="flex items-center">
                                    <span class="text-sm font-medium text-gray-700 mr-3">
                                        <i class="fas fa-share-alt mr-2"></i>Partager cet article :
                                    </span>
                                </div>
                                
                                <div class="flex items-center space-x-3">
                                    <!-- Facebook -->
                                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}&quote={{ urlencode($article->title) }}" 
                                       target="_blank" 
                                       rel="noopener noreferrer"
                                       class="text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center space-x-2 group" style="background-color: var(--primary-color, #3b82f6);" onmouseover="this.style.backgroundColor='var(--secondary-color, #1e40af)'" onmouseout="this.style.backgroundColor='var(--primary-color, #3b82f6)'">
                                        <i class="fab fa-facebook-f"></i>
                                        <span class="hidden sm:inline">Facebook</span>
                                    </a>
                                    
                                    <!-- WhatsApp -->
                                    <a href="https://wa.me/?text={{ urlencode($article->title . ' - ' . request()->url()) }}" 
                                       target="_blank" 
                                       rel="noopener noreferrer"
                                       class="text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center space-x-2 group" style="background-color: #25D366;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                                        <i class="fab fa-whatsapp"></i>
                                        <span class="hidden sm:inline">WhatsApp</span>
                                    </a>
                                    
                                    <!-- Email -->
                                    <a href="mailto:?subject={{ urlencode($article->title) }}&body={{ urlencode('Je vous partage cet article intéressant : ' . request()->url()) }}" 
                                       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center space-x-2 group">
                                        <i class="fas fa-envelope"></i>
                                        <span class="hidden sm:inline">Email</span>
                                    </a>
                                    
                                    <!-- Copier le lien -->
                                    <button onclick="copyToClipboard('{{ request()->url() }}')" 
                                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center space-x-2 group"
                                            title="Copier le lien">
                                        <i class="fas fa-copy"></i>
                                        <span class="hidden sm:inline">Copier</span>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Message de confirmation pour la copie -->
                            <div id="copy-message" class="hidden mt-3 p-3 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                                <i class="fas fa-check-circle mr-2"></i>Lien copié dans le presse-papiers !
                            </div>
                        </div>
                        
                        <!-- Informations supplémentaires -->
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <div class="flex flex-wrap items-center justify-between text-sm text-gray-500">
                                <div class="flex items-center space-x-4">
                                    @if($article->published_at)
                                    <span class="flex items-center">
                                        <i class="fas fa-calendar mr-2"></i>
                                        Publié le {{ $article->published_at->format('d/m/Y') }}
                                    </span>
                                    @endif
                                    <span class="flex items-center">
                                        <i class="fas fa-clock mr-2"></i>
                                        {{ $article->estimated_reading_time ?? '5' }} min de lecture
                                    </span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                        {{ $article->focus_keyword ?? 'Rénovation' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="space-y-6">

                    <!-- Company Info -->
                    <div class="rounded-xl shadow-xl p-6 text-white" style="background: linear-gradient(135deg, var(--primary-color, #3b82f6), var(--secondary-color, #1e40af));">
                        <div class="text-center mb-6">
                            <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-building text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-bold mb-2">{{ setting('company_name') }}</h3>
                            <p class="text-sm opacity-90">Votre partenaire rénovation</p>
                        </div>
                        
                        <div class="space-y-4 mb-6">
                            <div class="flex items-center">
                                <i class="fas fa-map-marker-alt mr-3 opacity-80"></i>
                                <span class="text-sm">
                                    @php
                                        $address = setting('company_address', '');
                                        $city = setting('company_city', '');
                                        $postalCode = setting('company_postal_code', '');
                                        $country = setting('company_country', 'France');
                                        
                                        $fullAddress = [];
                                        if ($address) $fullAddress[] = $address;
                                        if ($postalCode && $city) {
                                            $fullAddress[] = $postalCode . ' ' . $city;
                                        } elseif ($city) {
                                            $fullAddress[] = $city;
                                        } elseif ($postalCode) {
                                            $fullAddress[] = $postalCode;
                                        }
                                        if ($country) $fullAddress[] = $country;
                                        
                                        $fullAddressString = implode(', ', $fullAddress);
                                    @endphp
                                    {{ $fullAddressString ?: setting('company_address') }}
                                </span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-phone mr-3 opacity-80"></i>
                                <a href="tel:{{ setting('company_phone_raw') }}" class="text-sm hover:opacity-80 transition-opacity">
                                    {{ setting('company_phone') }}
                                </a>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-envelope mr-3 opacity-80"></i>
                                <a href="mailto:{{ setting('company_email') }}" class="text-sm hover:opacity-80 transition-opacity">
                                    {{ setting('company_email') }}
                                </a>
                            </div>
                        </div>
                        
                        <!-- Boutons d'action -->
                        <div class="space-y-3">
                            <a href="{{ route('form.step', 'propertyType') }}" 
                               class="w-full bg-white py-3 px-4 rounded-lg font-bold hover:bg-gray-100 transition-colors flex items-center justify-center" style="color: var(--primary-color, #3b82f6);">
                                <i class="fas fa-calculator mr-2"></i>
                                Devis Gratuit
                            </a>
                            <a href="tel:{{ setting('company_phone_raw') }}" 
                               class="w-full text-white py-3 px-4 rounded-lg font-bold transition-opacity flex items-center justify-center" style="background-color: var(--accent-color, #f59e0b);" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                                <i class="fas fa-phone mr-2"></i>
                                Appeler Maintenant
                            </a>
                        </div>
                        
                        <!-- Trust badges -->
                        <div class="mt-6 pt-4 border-t border-white border-opacity-20">
                            <div class="flex justify-center space-x-4 text-xs">
                                <div class="flex items-center">
                                    <i class="fas fa-shield-alt mr-1 opacity-80"></i>
                                    <span class="opacity-90">Garantie</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-certificate mr-1 opacity-80"></i>
                                    <span class="opacity-90">Certifié</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-star mr-1 opacity-80"></i>
                                    <span class="opacity-90">5★</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Contact Section -->
        <div class="mt-8 lg:hidden">
            <div class="grid grid-cols-1 gap-6">
                <!-- Company Info -->
                <div class="bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl shadow-xl p-6 text-white">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-building text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-2">{{ setting('company_name') }}</h3>
                        <p class="text-blue-100 text-sm">Votre partenaire rénovation</p>
                    </div>
                    
                    <div class="space-y-4 mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt text-blue-200 mr-3"></i>
                            <span class="text-sm">
                                @php
                                    $address = setting('company_address', '');
                                    $city = setting('company_city', '');
                                    $postalCode = setting('company_postal_code', '');
                                    $country = setting('company_country', 'France');
                                    
                                    $fullAddress = [];
                                    if ($address) $fullAddress[] = $address;
                                    if ($postalCode && $city) {
                                        $fullAddress[] = $postalCode . ' ' . $city;
                                    } elseif ($city) {
                                        $fullAddress[] = $city;
                                    } elseif ($postalCode) {
                                        $fullAddress[] = $postalCode;
                                    }
                                    if ($country) $fullAddress[] = $country;
                                    
                                    $fullAddressString = implode(', ', $fullAddress);
                                @endphp
                                {{ $fullAddressString ?: setting('company_address') }}
                            </span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-phone text-green-300 mr-3"></i>
                            <a href="tel:{{ setting('company_phone_raw') }}" class="text-sm hover:text-green-200 transition-colors">
                                {{ setting('company_phone') }}
                            </a>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-envelope text-blue-200 mr-3"></i>
                            <a href="mailto:{{ setting('company_email') }}" class="text-sm hover:text-blue-200 transition-colors">
                                {{ setting('company_email') }}
                            </a>
                        </div>
                    </div>
                    
                    <!-- Boutons d'action -->
                    <div class="space-y-3">
                        <a href="{{ route('form.step', 'propertyType') }}" 
                           class="w-full bg-white text-blue-600 py-3 px-4 rounded-lg font-bold hover:bg-gray-100 transition-colors flex items-center justify-center">
                            <i class="fas fa-calculator mr-2"></i>
                            Devis Gratuit
                        </a>
                        <a href="tel:{{ setting('company_phone_raw') }}" 
                           class="w-full bg-green-500 text-white py-3 px-4 rounded-lg font-bold hover:bg-green-600 transition-colors flex items-center justify-center">
                            <i class="fas fa-phone mr-2"></i>
                            Appeler Maintenant
                        </a>
                    </div>
                    
                    <!-- Trust badges -->
                    <div class="mt-6 pt-4 border-t border-white border-opacity-20">
                        <div class="flex justify-center space-x-4 text-xs">
                            <div class="flex items-center">
                                <i class="fas fa-shield-alt text-yellow-300 mr-1"></i>
                                <span class="text-blue-100">Garantie</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-certificate text-yellow-300 mr-1"></i>
                                <span class="text-blue-100">Certifié</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-star text-yellow-300 mr-1"></i>
                                <span class="text-blue-100">5★</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Boutons de partage flottants pour mobile -->
        <div class="fixed bottom-4 right-4 z-50 lg:hidden">
            <div class="flex flex-col space-y-3">
                <!-- Bouton principal de partage -->
                <button onclick="toggleShareButtons()" 
                        class="bg-blue-600 hover:bg-blue-700 text-white w-14 h-14 rounded-full shadow-lg flex items-center justify-center transition-all duration-300 transform hover:scale-110">
                    <i class="fas fa-share-alt text-xl"></i>
                </button>
                
                <!-- Boutons de partage individuels (masqués par défaut) -->
                <div id="share-buttons" class="hidden flex flex-col space-y-2">
                    <!-- Facebook -->
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}&quote={{ urlencode($article->title) }}" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="bg-blue-600 hover:bg-blue-700 text-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center transition-all duration-300 transform hover:scale-110">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    
                    <!-- WhatsApp -->
                    <a href="https://wa.me/?text={{ urlencode($article->title . ' - ' . request()->url()) }}" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="bg-green-500 hover:bg-green-600 text-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center transition-all duration-300 transform hover:scale-110">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    
                    <!-- Email -->
                    <a href="mailto:?subject={{ urlencode($article->title) }}&body={{ urlencode('Je vous partage cet article intéressant : ' . request()->url()) }}" 
                       class="bg-gray-600 hover:bg-gray-700 text-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center transition-all duration-300 transform hover:scale-110">
                        <i class="fas fa-envelope"></i>
                    </a>
                    
                    <!-- Copier le lien -->
                    <button onclick="copyToClipboard('{{ request()->url() }}')" 
                            class="bg-gray-500 hover:bg-gray-600 text-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center transition-all duration-300 transform hover:scale-110">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        @if(isset($reviews) && count($reviews) > 0)
        <div class="mt-12">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Avis Clients</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($reviews as $review)
                        <div class="bg-gray-50 rounded-lg p-6">
                            <div class="flex items-center mb-4">
                                <div class="flex text-yellow-400">
                                    @for($i = 0; $i < 5; $i++)
                                        <i class="fas fa-star"></i>
                                    @endfor
                                </div>
                            </div>
                            <p class="text-gray-700 mb-4">"{{ $review->review_text }}"</p>
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold">
                                    {{ substr($review->author_name, 0, 1) }}
                                </div>
                                <div class="ml-3">
                                    <p class="font-semibold text-gray-900">{{ $review->author_name }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
