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
    
    // Passer les métadonnées spécifiques à l'article au layout principal
    $pageImage = $article->featured_image ? asset($article->featured_image) : asset(setting('default_blog_og_image', 'images/og-blog.jpg'));
    $pageType = 'article';
    $currentPage = 'article';
    
    // Open Graph et Twitter
    $ogTitle = $ogTitle ?? $pageTitle;
    $ogDescription = $ogDescription ?? $pageDescription;
    $twitterTitle = $twitterTitle ?? $ogTitle;
    $twitterDescription = $twitterDescription ?? $ogDescription;
@endphp

@section('title', $pageTitle)
@section('description', $pageDescription)
@section('keywords', $metaKeywords)

@push('head')
<!-- Métadonnées spécifiques aux articles -->
<meta property="article:published_time" content="{{ $article->created_at->toISOString() }}">
<meta property="article:author" content="{{ setting('company_name', 'Sauser Couverture') }}">
<meta property="article:section" content="Blog">
<meta property="article:tag" content="{{ $article->focus_keyword ?? 'Rénovation' }}">

<style>
    :root {
        --primary-color: {{ setting('primary_color', '#3b82f6') }};
        --secondary-color: {{ setting('secondary_color', '#1e40af') }};
        --accent-color: {{ setting('accent_color', '#f59e0b') }};
    }
    
    /* Design WordPress moderne */
    .wp-article-container {
        background: #ffffff;
        min-height: 100vh;
    }
    
    .wp-article-header {
        position: relative;
        padding: 4rem 0 3rem;
        margin-bottom: 0;
        min-height: 400px;
        display: flex;
        align-items: center;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    }
    
    .wp-article-header.has-image {
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }
    
    .wp-article-header-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0.4) 100%);
        z-index: 1;
    }
    
    .wp-article-header-content {
        position: relative;
        z-index: 2;
        width: 100%;
    }
    
    .wp-article-featured-image {
        width: 100%;
        max-height: 500px;
        object-fit: cover;
        margin: 0;
        display: block;
    }
    
    .wp-article-wrapper {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .wp-article-content-area {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 50px;
        margin-top: -80px;
        position: relative;
        z-index: 10;
        align-items: start;
    }
    
    .wp-article-main {
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 2px 20px rgba(0,0,0,0.08);
        padding: 4rem;
        margin-bottom: 2rem;
        min-width: 0;
    }
    
    .wp-article-title {
        font-size: 2.5rem;
        font-weight: 700;
        line-height: 1.2;
        color: #1a1a1a;
        margin: 0 0 1.5rem 0;
    }
    
    .wp-article-meta {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        font-size: 0.9rem;
        color: #6b7280;
    }
    
    .wp-article-meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .wp-article-meta-item i {
        color: var(--primary-color);
    }
    
    /* Styles pour le contenu généré par ChatGPT */
    .article-content {
        line-height: 1.85;
        color: #374151;
        font-size: 1.125rem;
        max-width: 100%;
    }
    
    .article-content p {
        margin-bottom: 1.75rem;
    }
    
    .article-content h1 {
        font-size: 2.25rem;
        line-height: 1.3;
        font-weight: 700;
        color: #111827;
        margin-top: 2rem;
        margin-bottom: 1rem;
    }
    
    .article-content h2 {
        font-size: 1.875rem;
        line-height: 1.4;
        font-weight: 700;
        color: #111827;
        margin-top: 2.5rem;
        margin-bottom: 1.25rem;
        padding-bottom: 0.75rem;
        border-bottom: 3px solid var(--primary-color);
    }
    
    .article-content h3 {
        font-size: 1.5rem;
        line-height: 1.5;
        font-weight: 600;
        color: #1f2937;
        margin-top: 2rem;
        margin-bottom: 1rem;
        padding-left: 0.75rem;
        border-left: 4px solid var(--accent-color);
    }
    
    .article-content h4 {
        font-size: 1.25rem;
        line-height: 1.6;
        font-weight: 600;
        color: #374151;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
    }
    
    .article-content p {
        margin-bottom: 1.5rem;
        line-height: 1.8;
        color: #374151;
    }
    
    .article-content ul,
    .article-content ol {
        margin-bottom: 1.5rem;
        padding-left: 2rem;
    }
    
    .article-content li {
        margin-bottom: 0.75rem;
        line-height: 1.8;
    }
    
    .article-content a {
        color: var(--primary-color);
        text-decoration: underline;
        transition: color 0.2s;
    }
    
    .article-content a:hover {
        color: var(--secondary-color);
    }
    
    .article-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 2rem 0;
    }
    
    .article-content blockquote {
        border-left: 4px solid var(--primary-color);
        padding-left: 1.5rem;
        margin: 2rem 0;
        font-style: italic;
        color: #6b7280;
        background: #f9fafb;
        padding: 1.5rem;
        border-radius: 4px;
    }
    
    .article-content table {
        width: 100%;
        border-collapse: collapse;
        margin: 2rem 0;
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
    
    /* Styles FAQ */
    .article-content #faq {
        margin-top: 3rem;
        margin-bottom: 3rem;
        padding: 2rem;
        background-color: #f9fafb;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }
    
    .article-content #faq h2 {
        font-size: 1.875rem;
        font-weight: 700;
        color: #111827;
        margin-top: 0;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 3px solid var(--primary-color);
    }
    
    .article-content #faq > div[itemscope] {
        margin-bottom: 2rem;
        padding: 1.5rem;
        background-color: #ffffff;
        border-radius: 8px;
        border-left: 4px solid var(--primary-color);
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
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
        margin-bottom: 0.5rem;
        color: #374151;
        line-height: 1.75;
    }
    
    /* Sidebar WordPress style */
    .wp-sidebar {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        position: sticky;
        top: 100px;
        align-self: start;
        max-height: calc(100vh - 120px);
        overflow-y: auto;
    }
    
    .wp-widget {
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        padding: 2rem;
    }
    
    .wp-widget-title {
        font-size: 1.375rem;
        font-weight: 700;
        color: #111827;
        margin: 0 0 1.25rem 0;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--primary-color);
    }
    
    .wp-widget-content {
        font-size: 1rem;
        color: #374151;
        line-height: 1.7;
    }
    
    .wp-widget-content a {
        color: var(--primary-color);
        text-decoration: none;
        transition: color 0.2s;
    }
    
    .wp-widget-content a:hover {
        color: var(--secondary-color);
        text-decoration: underline;
    }
    
    .wp-cta-button {
        display: inline-block;
        padding: 0.875rem 1.75rem;
        background: var(--primary-color);
        color: #ffffff;
        border-radius: 6px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s;
        margin-top: 0.5rem;
    }
    
    .wp-cta-button:hover {
        background: var(--secondary-color);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .wp-cta-button-secondary {
        background: transparent;
        border: 2px solid var(--primary-color);
        color: var(--primary-color);
    }
    
    .wp-cta-button-secondary:hover {
        background: var(--primary-color);
        color: #ffffff;
    }
    
    /* Section CTA Finale */
    .wp-cta-section {
        margin-top: 3rem;
        padding-top: 3rem;
        border-top: 2px solid #e5e7eb;
    }
    
    .wp-cta-main {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        border-radius: 12px;
        padding: 3rem;
        color: #ffffff;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    
    .wp-cta-benefits {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2.5rem;
        text-align: left;
    }
    
    .wp-cta-benefit-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }
    
    .wp-cta-benefit-icon {
        color: #10b981;
        font-size: 1.5rem;
        flex-shrink: 0;
    }
    
    .wp-cta-buttons-wrapper {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: center;
        margin-bottom: 1.5rem;
    }
    
    .wp-cta-button-primary {
        display: inline-flex;
        align-items: center;
        padding: 1rem 2rem;
        background: #ffffff;
        color: var(--primary-color);
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .wp-cta-button-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        background: #f9fafb;
    }
    
    .wp-cta-button-secondary {
        display: inline-flex;
        align-items: center;
        padding: 1rem 2rem;
        background: rgba(255,255,255,0.2);
        color: #ffffff;
        border: 2px solid #ffffff;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s;
    }
    
    .wp-cta-button-secondary:hover {
        background: rgba(255,255,255,0.3);
        transform: translateY(-2px);
    }
    
    .wp-cta-summary {
        background: #f9fafb;
        border-radius: 12px;
        padding: 2.5rem;
        border-left: 4px solid var(--primary-color);
    }
    
    .wp-cta-summary-list {
        list-style: none;
        padding: 0;
        margin: 0 0 1.5rem 0;
    }
    
    .wp-cta-summary-list li {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 1rem;
        color: #374151;
    }
    
    .wp-cta-summary-list li::before {
        content: '•';
        color: var(--primary-color);
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    
    /* Responsive */
    @media (max-width: 1024px) {
        .wp-article-wrapper {
            max-width: 100%;
        }
        
        .wp-article-content-area {
            grid-template-columns: 1fr;
            gap: 2rem;
            margin-top: 0;
        }
        
        .wp-article-main {
            margin-top: 2rem;
            padding: 2.5rem;
        }
        
        .wp-sidebar {
            order: -1;
            position: static;
            max-height: none;
            overflow-y: visible;
        }
        
        .wp-cta-benefits {
            grid-template-columns: 1fr;
        }
    }
    
    @media (max-width: 768px) {
        .wp-article-header {
            padding: 2rem 0 1.5rem;
            min-height: 300px;
        }
        
        .wp-article-main {
            padding: 1.5rem;
        }
        
        .wp-article-title {
            font-size: 1.875rem;
        }
        
        .wp-article-meta {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }
        
        .article-content {
            font-size: 1rem;
        }
        
        .article-content h2 {
            font-size: 1.5rem;
        }
        
        .article-content h3 {
            font-size: 1.25rem;
        }
    }
</style>
@endpush

@section('content')
<div class="wp-article-container">
    <!-- Header avec titre et image en background -->
    <div class="wp-article-header @if($article->featured_image) has-image @endif" 
         @if($article->featured_image)
         style="background-image: url('{{ asset($article->featured_image) }}');"
         @endif>
        <div class="wp-article-header-overlay"></div>
        <div class="wp-article-wrapper wp-article-header-content">
            <h1 class="wp-article-title" style="color: #ffffff;">{{ $article->title }}</h1>
            <div class="wp-article-meta" style="color: rgba(255,255,255,0.9);">
                @if($article->published_at)
                <div class="wp-article-meta-item">
                    <i class="fas fa-calendar"></i>
                    <span>{{ $article->published_at->format('d F Y') }}</span>
                </div>
                @endif
                <div class="wp-article-meta-item">
                    <i class="fas fa-clock"></i>
                    <span>Temps de lecture</span>
                </div>
                <div class="wp-article-meta-item">
                    <i class="fas fa-tag"></i>
                    <span>{{ $article->focus_keyword ?? 'Rénovation' }}</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Contenu principal -->
    <div class="wp-article-wrapper">
        <div class="wp-article-content-area">
            <!-- Article principal -->
            <article class="wp-article-main">
                <div class="article-content">
                    @php
                        // Le contenu est déjà en HTML depuis ChatGPT
                        $content = $article->content_html;
                        
                        // Vérifier si le contenu contient des entités HTML échappées
                        if (strpos($content, '&lt;') !== false && strpos($content, '<') === false) {
                            $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        }
                        
                        // Supprimer les sections CTA finales générées par GPT (on les remplace par notre section fixe)
                        // Supprimer les sections "Lancez Votre Projet", "En Résumé", "Conclusion" avec CTA
                        $content = preg_replace('/<div[^>]*class="cta-final"[^>]*>.*?<\/div>/is', '', $content);
                        $content = preg_replace('/<section[^>]*class="article-conclusion"[^>]*>.*?<\/section>/is', '', $content);
                        $content = preg_replace('/🚀\s*Lancez\s+Votre\s+Projet[^<]*<.*?🔒[^<]*<.*?<\/div>/is', '', $content);
                        $content = preg_replace('/En\s+Résumé\s*:.*?🏆[^<]*<.*?<\/section>/is', '', $content);
                        
                        // Générer les liens internes si le helper existe
                        if (class_exists('\App\Helpers\InternalLinkingHelper')) {
                            try {
                                $content = \App\Helpers\InternalLinkingHelper::generateInternalLinks($content, 'article');
                            } catch (\Exception $e) {
                                // Si le helper échoue, on continue avec le contenu original
                            }
                        }
                        
                        // Convertir les URLs en liens cliquables
                        $content = preg_replace_callback(
                            '/(?<!href=["\'])(?<!>)(https?:\/\/[^\s<>"\'\)]+)(?![^<]*<\/a>)/',
                            function($matches) {
                                return '<a href="' . htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener noreferrer" class="text-link">' . htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8') . '</a>';
                            },
                            $content
                        );
                    @endphp
                    {!! $content !!}
                </div>
                
                <!-- Section CTA Finale Fixe (remplace celle générée par GPT) -->
                @php
                    $companyName = setting('company_name', 'Votre Entreprise');
                    $companyPhone = setting('company_phone', '');
                    $companyPhoneRaw = setting('company_phone_raw', '');
                    $companyCity = setting('company_city', '');
                    $focusKeyword = $article->focus_keyword ?? 'votre projet';
                    $articleTitle = $article->title;
                    
                    // Extraire le mot-clé principal de l'article pour personnaliser
                    $keywordForCTA = $focusKeyword;
                    if (empty($keywordForCTA)) {
                        // Essayer d'extraire depuis le titre
                        $titleWords = explode(' ', $articleTitle);
                        $keywordForCTA = $titleWords[0] ?? 'votre projet';
                    }
                @endphp
                
                <!-- Section CTA Finale Fixe (remplace celle générée par GPT) -->
                <div class="wp-cta-section">
                    <!-- Section principale CTA -->
                    <div class="wp-cta-main">
                        <div style="text-align: center; max-width: 800px; margin: 0 auto;">
                            <h2 style="font-size: 2rem; font-weight: 700; margin: 0 0 1rem 0; color: #ffffff;">
                                🚀 Lancez Votre Projet en Toute Confiance
                            </h2>
                            <p style="font-size: 1.125rem; margin: 0 0 2rem 0; color: rgba(255,255,255,0.95); line-height: 1.6;">
                                <strong>{{ $companyName }}</strong>, votre partenaire expert local. Nous vous garantissons :
                            </p>
                            
                            <div class="wp-cta-benefits">
                                <div class="wp-cta-benefit-item">
                                    <span class="wp-cta-benefit-icon">✅</span>
                                    <div>
                                        <strong style="display: block; margin-bottom: 0.25rem; color: #ffffff;">Devis détaillé gratuit</strong>
                                        <span style="font-size: 0.9rem; color: rgba(255,255,255,0.9);">Sous 24h, sans engagement</span>
                                    </div>
                                </div>
                                <div class="wp-cta-benefit-item">
                                    <span class="wp-cta-benefit-icon">✅</span>
                                    <div>
                                        <strong style="display: block; margin-bottom: 0.25rem; color: #ffffff;">Artisans certifiés</strong>
                                        <span style="font-size: 0.9rem; color: rgba(255,255,255,0.9);">RGE et assurés (garantie décennale)</span>
                                    </div>
                                </div>
                                <div class="wp-cta-benefit-item">
                                    <span class="wp-cta-benefit-icon">✅</span>
                                    <div>
                                        <strong style="display: block; margin-bottom: 0.25rem; color: #ffffff;">Matériaux premium</strong>
                                        <span style="font-size: 0.9rem; color: rgba(255,255,255,0.9);">Sélectionnés pour leur durabilité</span>
                                    </div>
                                </div>
                                <div class="wp-cta-benefit-item">
                                    <span class="wp-cta-benefit-icon">✅</span>
                                    <div>
                                        <strong style="display: block; margin-bottom: 0.25rem; color: #ffffff;">Respect des délais</strong>
                                        <span style="font-size: 0.9rem; color: rgba(255,255,255,0.9);">Transparence totale sur les coûts</span>
                                    </div>
                                </div>
                                <div class="wp-cta-benefit-item">
                                    <span class="wp-cta-benefit-icon">✅</span>
                                    <div>
                                        <strong style="display: block; margin-bottom: 0.25rem; color: #ffffff;">Service après-vente</strong>
                                        <span style="font-size: 0.9rem; color: rgba(255,255,255,0.9);">Réactif et suivi personnalisé</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="wp-cta-buttons-wrapper">
                                <a href="{{ route('form.step', 'propertyType') }}" class="wp-cta-button-primary">
                                    <i class="fas fa-calculator mr-2"></i>Demander mon devis gratuit
                                </a>
                                @if($companyPhoneRaw)
                                <a href="tel:{{ $companyPhoneRaw }}" class="wp-cta-button-secondary">
                                    <i class="fas fa-phone mr-2"></i>{{ $companyPhone }}
                                </a>
                                @endif
                            </div>
                            
                            <p style="font-size: 0.9rem; color: rgba(255,255,255,0.85); margin: 0;">
                                🔒 Vos données sont protégées. 500+ clients satisfaits nous font confiance.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Section Résumé -->
                    <div class="wp-cta-summary">
                        <h3 style="font-size: 1.75rem; font-weight: 700; margin: 0 0 1.5rem 0; color: #111827;">
                            En Résumé : Votre Référence Complète
                        </h3>
                        <p style="font-size: 1.1rem; line-height: 1.8; color: #374151; margin-bottom: 1.5rem;">
                            Vous l'avez découvert dans ce guide expert, les bénéfices d'un devis clair et détaillé ne sont pas négligeables. Il vous offre non seulement une vision précise des coûts, mais aussi une assurance contre les imprévus.
                        </p>
                        <p style="font-size: 1.1rem; line-height: 1.8; color: #374151; margin-bottom: 1.5rem;">
                            Faire appel à <strong>{{ $companyName }}</strong> pour votre projet, c'est choisir :
                        </p>
                        <ul class="wp-cta-summary-list">
                            <li>Une expertise reconnue et des matériaux de qualité.</li>
                            <li>Un accompagnement personnalisé du début à la fin du projet.</li>
                            <li>Une transparence totale et des tarifs compétitifs.</li>
                        </ul>
                        <p style="font-size: 1.1rem; line-height: 1.8; color: #374151; margin-bottom: 1.5rem;">
                            <strong>Ne laissez pas votre projet en suspens.</strong> Que vous soyez en phase de réflexion ou prêt à vous lancer, notre équipe d'experts certifiés est là pour vous conseiller sans engagement. Obtenez votre devis personnalisé gratuit et découvrez comment transformer votre vision en réalité.
                        </p>
                        <p style="font-size: 1rem; line-height: 1.8; color: #6b7280; font-style: italic; margin: 0;">
                            🏆 <strong>{{ $companyName }}</strong>, votre expert de confiance{{ $companyCity ? ' à ' . $companyCity : '' }} depuis de nombreuses années.
                        </p>
                    </div>
                </div>
            </article>
            
            <!-- Sidebar -->
            <aside class="wp-sidebar">
                <!-- Widget Contact -->
                <div class="wp-widget">
                    <h3 class="wp-widget-title">Besoin d'aide ?</h3>
                    <div class="wp-widget-content">
                        <p style="margin-bottom: 1rem;">Nos experts sont à votre disposition pour répondre à toutes vos questions.</p>
                        <a href="tel:{{ setting('company_phone_raw') }}" class="wp-cta-button" style="display: block; text-align: center;">
                            <i class="fas fa-phone mr-2"></i>{{ setting('company_phone') }}
                        </a>
                        <a href="{{ route('form.step', 'propertyType') }}" class="wp-cta-button wp-cta-button-secondary" style="display: block; text-align: center; margin-top: 0.75rem;">
                            <i class="fas fa-calculator mr-2"></i>Devis gratuit
                        </a>
                    </div>
                </div>
                
                <!-- Widget Entreprise -->
                <div class="wp-widget">
                    <h3 class="wp-widget-title">Notre Entreprise</h3>
                    <div class="wp-widget-content">
                        <p><strong>{{ setting('company_name') }}</strong></p>
                        <p style="margin-top: 0.5rem;">{{ setting('company_address') }}</p>
                        <p style="margin-top: 0.5rem;">
                            <a href="tel:{{ setting('company_phone_raw') }}">{{ setting('company_phone') }}</a>
                        </p>
                        <p style="margin-top: 0.5rem;">
                            <a href="mailto:{{ setting('company_email') }}">{{ setting('company_email') }}</a>
                        </p>
                    </div>
                </div>
                
                <!-- Widget CTA -->
                <div class="wp-widget" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); color: #ffffff;">
                    <h3 class="wp-widget-title" style="color: #ffffff; border-bottom-color: rgba(255,255,255,0.3);">Prêt à commencer ?</h3>
                    <div class="wp-widget-content" style="color: rgba(255,255,255,0.95);">
                        <p style="margin-bottom: 1rem;">Contactez-nous pour un devis gratuit et personnalisé.</p>
                        <a href="{{ route('form.step', 'propertyType') }}" class="wp-cta-button" style="background: #ffffff; color: var(--primary-color); display: block; text-align: center;">
                            <i class="fas fa-calculator mr-2"></i>Demander un devis
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection
