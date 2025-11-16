@extends('layouts.app')

@php
    $pageTitle = $article->meta_title ?: $article->title;
    $pageDescription = $article->meta_description;
    
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
    
    $pageImage = $article->featured_image ? asset($article->featured_image) : asset(setting('default_blog_og_image', 'images/og-blog.jpg'));
    $pageType = 'article';
    $currentPage = 'article';
    
    $ogTitle = $ogTitle ?? $pageTitle;
    $ogDescription = $ogDescription ?? $pageDescription;
    $twitterTitle = $twitterTitle ?? $ogTitle;
    $twitterDescription = $twitterDescription ?? $ogDescription;
@endphp

@section('title', $pageTitle)
@section('description', $pageDescription)
@section('keywords', $metaKeywords)

@push('head')
<meta property="article:published_time" content="{{ $article->created_at->toISOString() }}">
<meta property="article:author" content="{{ setting('company_name', 'Sauser Couverture') }}">
<meta property="article:section" content="Blog">
<meta property="article:tag" content="{{ $article->focus_keyword ?? 'Rénovation' }}">

<style>
    :root {
        --primary: {{ setting('primary_color', '#2563eb') }};
        --primary-dark: {{ setting('secondary_color', '#1e40af') }};
        --accent: {{ setting('accent_color', '#f59e0b') }};
        --text-dark: #0f172a;
        --text-medium: #334155;
        --text-light: #64748b;
        --bg-white: #ffffff;
        --bg-light: #f8fafc;
        --bg-lighter: #f1f5f9;
        --border: #e2e8f0;
    }
    
    * {
        box-sizing: border-box;
    }
    
    /* Container principal */
    .article-page {
        background: linear-gradient(180deg, var(--bg-light) 0%, var(--bg-white) 50%);
        min-height: 100vh;
    }
    
    /* Hero Section Ultra-Moderne */
    .article-hero {
        position: relative;
        min-height: 550px;
        display: flex;
        align-items: center;
        overflow: hidden;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    }
    
    .article-hero.has-image {
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }
    
    /* Overlay pour l'image - utiliser la couleur principale */
    .article-hero.has-image::before {
        background: linear-gradient(135deg, 
            var(--primary) 0%, 
            var(--primary-dark) 100%);
        opacity: 0.7;
    }
    
    .article-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, 
            rgba(0, 0, 0, 0.6) 0%, 
            rgba(0, 0, 0, 0.4) 100%);
        z-index: 1;
    }
    
    /* Si l'image n'est pas présente, utiliser un overlay avec la couleur principale */
    .article-hero:not(.has-image)::before {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        opacity: 1;
    }
    
    .article-hero::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 200px;
        background: linear-gradient(to top, var(--bg-light), transparent);
        z-index: 2;
    }
    
    .hero-content {
        position: relative;
        z-index: 3;
        max-width: 900px;
        margin: 0 auto;
        padding: 4rem 2rem;
        text-align: center;
        animation: fadeInUp 0.8s ease-out;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .article-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1.25rem;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-radius: 50px;
        color: #fff;
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 2rem;
        border: 1px solid rgba(255, 255, 255, 0.25);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: all 0.3s;
    }
    
    .article-badge:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
    }
    
    .hero-title {
        font-size: clamp(2.25rem, 6vw, 4rem);
        font-weight: 800;
        line-height: 1.2;
        word-wrap: break-word;
        overflow-wrap: break-word;
        hyphens: auto;
        max-width: 100%;
        color: #fff;
        margin: 0 0 1.5rem 0;
        text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        letter-spacing: -0.02em;
    }
    
    .hero-meta {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 2rem;
        flex-wrap: wrap;
        color: rgba(255, 255, 255, 0.95);
        font-size: 0.9375rem;
        font-weight: 500;
    }
    
    .hero-meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(8px);
        border-radius: 50px;
        transition: all 0.3s;
    }
    
    .hero-meta-item:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-1px);
    }
    
    .hero-meta-item i {
        opacity: 0.9;
    }
    
    /* Layout principal - Style WordPress */
    .article-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 3rem 1.5rem;
        position: relative;
        z-index: 10;
        width: 100%;
        box-sizing: border-box;
    }
    
    /* Grille WordPress - Contenu + Sidebar */
    .article-content-container {
        background: transparent;
        border: none;
        border-radius: 0;
        padding: 0;
        margin: 0;
        box-shadow: none;
        max-width: 100%;
    }
    
    .article-grid {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 3rem;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
    
    /* Forcer le layout en colonnes sur desktop (sidebar à gauche) */
    @media (min-width: 1025px) {
        .article-grid {
            grid-template-columns: 350px 1fr !important;
            display: grid !important;
        }
        
        /* Ordre d'affichage desktop : sidebar d'abord, puis article */
        .article-sidebar {
            order: 1;
        }
        
        .article-card {
            order: 2;
        }
    }
    
    /* Card Article Principale */
    .article-card {
        background: var(--bg-white);
        border-radius: 0;
        box-shadow: none;
        overflow: visible;
        transition: none;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        position: relative;
        z-index: 1;
        padding: 0;
        min-width: 0; /* Permet au contenu de se rétrécir dans le grid */
    }
    
    
    .article-content-wrapper {
        padding: 0;
        min-width: 0; /* Permet au contenu de se rétrécir dans le grid */
    }
    
    /* Styles du contenu enrichis - Style WordPress */
    .article-content {
        font-size: 1.125rem;
        line-height: 1.8;
        color: #1e293b;
        max-width: 100%;
        overflow-x: auto;
        word-wrap: break-word;
        overflow-wrap: break-word;
        padding: 0;
    }
    
    /* ISOLATION COMPLÈTE du contenu HTML généré */
    .article-content-wrapper {
        position: relative;
        overflow: hidden;
        width: 100%;
        box-sizing: border-box;
    }
    
    .article-content {
        isolation: isolate !important;
        contain: layout style paint !important;
        position: relative !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        overflow-x: hidden !important;
    }
    
    /* FORCER tous les éléments du contenu à rester dans le conteneur */
    .article-content * {
        max-width: 100% !important;
        box-sizing: border-box !important;
        position: relative !important;
    }
    
    /* Empêcher TOUS les positionnements absolus/fixed */
    .article-content [style*="position"],
    .article-content [style*="Position"] {
        position: relative !important;
    }
    
    /* Empêcher les largeurs qui dépassent */
    .article-content [style*="width"],
    .article-content [style*="Width"] {
        max-width: 100% !important;
    }
    
    /* Empêcher les marges négatives */
    .article-content [style*="margin"],
    .article-content [style*="Margin"] {
        margin-left: auto !important;
        margin-right: auto !important;
    }
    
    /* S'assurer que les tableaux et images ne débordent pas */
    .article-content table {
        max-width: 100% !important;
        width: 100% !important;
        display: block !important;
        overflow-x: auto !important;
        box-sizing: border-box !important;
    }
    
    .article-content img {
        max-width: 100% !important;
        width: auto !important;
        height: auto !important;
        display: block !important;
        box-sizing: border-box !important;
        margin: 1.5rem auto !important;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    /* Corriger les images avec des URLs relatives */
    .article-content img[src^="/uploads/"],
    .article-content img[src^="uploads/"] {
        /* Les URLs seront corrigées par PHP */
    }
    
    /* Empêcher les divs et sections de sortir du conteneur */
    .article-content div,
    .article-content section,
    .article-content article,
    .article-content aside,
    .article-content nav {
        max-width: 100% !important;
        overflow-x: hidden !important;
        box-sizing: border-box !important;
        position: relative !important;
    }
    
    /* Empêcher les floats de casser la mise en page */
    .article-content [style*="float"] {
        float: none !important;
        display: block !important;
    }
    
    .article-content > *:first-child {
        margin-top: 0;
    }
    
    .article-content h2 {
        font-size: 1.875rem;
        font-weight: 700;
        color: var(--text-dark);
        margin: 2rem 0 0.75rem 0;
        padding-bottom: 0.5rem;
        position: relative;
        letter-spacing: -0.01em;
    }
    
    .article-content h2::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 80px;
        height: 4px;
        background: linear-gradient(90deg, var(--primary) 0%, var(--accent) 100%);
        border-radius: 2px;
    }
    
    .article-content h3 {
        font-size: 1.375rem;
        font-weight: 700;
        color: var(--text-dark);
        margin: 1.5rem 0 0.75rem 0;
        padding-left: 0.75rem;
        border-left: 4px solid var(--primary);
        transition: all 0.3s;
    }
    
    .article-content h3:hover {
        padding-left: 1.5rem;
        border-left-color: var(--accent);
    }
    
    .article-content h4 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-dark);
        margin: 2rem 0 1rem 0;
    }
    
    .article-content p {
        margin-bottom: 1rem;
        color: var(--text-medium);
        line-height: 1.5;
    }
    
    .article-content a {
        color: var(--primary);
        text-decoration: none;
        border-bottom: 2px solid transparent;
        transition: all 0.2s;
        font-weight: 500;
    }
    
    .article-content a:hover {
        color: var(--primary-dark);
        border-bottom-color: var(--primary);
    }
    
    .article-content ul,
    .article-content ol {
        margin: 1.5rem 0;
        padding-left: 0;
        list-style: none;
    }
    
    .article-content ul li,
    .article-content ol li {
        margin-bottom: 0.75rem;
        line-height: 1.7;
        padding-left: 2rem;
        position: relative;
        color: var(--text-medium);
    }
    
    /* Icônes arrondies pour les listes à puces */
    .article-content ul li::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0.5rem;
        width: 8px;
        height: 8px;
        background: var(--primary);
        border-radius: 50%;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }
    
    /* Icônes numérotées arrondies pour les listes ordonnées */
    .article-content ol {
        counter-reset: list-counter;
    }
    
    .article-content ol li {
        counter-increment: list-counter;
    }
    
    .article-content ol li::before {
        content: counter(list-counter);
        position: absolute;
        left: 0;
        top: 0;
        width: 24px;
        height: 24px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .article-content blockquote {
        position: relative;
        margin: 2.5rem 0;
        padding: 2rem 2rem 2rem 3.5rem;
        background: linear-gradient(135deg, var(--bg-lighter) 0%, var(--bg-light) 100%);
        border-left: 5px solid var(--primary);
        border-radius: 12px;
        font-style: italic;
        color: var(--text-medium);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    
    .article-content blockquote::before {
        content: '"';
        position: absolute;
        left: 1.25rem;
        top: 1.5rem;
        font-size: 4rem;
        color: var(--primary);
        opacity: 0.15;
        font-family: Georgia, serif;
        line-height: 1;
    }
    
    .article-content img {
        max-width: 100%;
        height: auto;
        border-radius: 12px;
        margin: 2.5rem 0;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    }
    
    .article-content table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin: 2.5rem 0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    
    .article-content th,
    .article-content td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid var(--border);
    }
    
    .article-content th {
        background: linear-gradient(135deg, var(--bg-lighter) 0%, var(--bg-light) 100%);
        font-weight: 700;
        color: var(--text-dark);
    }
    
    .article-content tr:last-child td {
        border-bottom: none;
    }
    
    /* FAQ Section améliorée */
    .article-content #faq {
        margin: 4rem 0;
        padding: 3rem;
        background: linear-gradient(135deg, var(--bg-lighter) 0%, var(--bg-light) 100%);
        border-radius: 16px;
        border: 1px solid var(--border);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
    }
    
    .article-content #faq h2 {
        margin-top: 0;
        padding-bottom: 1.5rem;
        border-bottom: 3px solid var(--primary);
    }
    
    .article-content #faq h2::after {
        display: none;
    }
    
    .article-content #faq > div[itemscope] {
        margin: 1.5rem 0;
        padding: 1.75rem;
        background: var(--bg-white);
        border-radius: 12px;
        border-left: 4px solid var(--primary);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: all 0.3s;
    }
    
    .article-content #faq > div[itemscope]:hover {
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        transform: translateX(4px);
        border-left-color: var(--accent);
    }
    
    .article-content #faq h3 {
        font-size: 1.125rem;
        margin: 0 0 1rem 0;
        padding-left: 0;
        border-left: none;
        color: var(--text-dark);
    }
    
    /* Sidebar WordPress */
    .article-sidebar {
        display: flex;
        flex-direction: column;
        gap: 2rem;
        align-self: start;
        padding-right: 1rem;
        margin-right: 0;
    }
    
    .sidebar-card {
        background: var(--bg-white);
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        border: 1px solid var(--border);
        transition: all 0.3s;
    }
    
    .sidebar-card:hover {
        box-shadow: 0 12px 36px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }
    
    .sidebar-card-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0 0 1.25rem 0;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--border);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .sidebar-card-title i {
        color: var(--primary);
    }
    
    .sidebar-card-body {
        font-size: 0.9375rem;
        color: var(--text-medium);
        line-height: 1.7;
    }
    
    .sidebar-card-body p {
        margin-bottom: 1rem;
    }
    
    .sidebar-card.gradient {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: #fff;
        border: none;
    }
    
    .sidebar-card.gradient .sidebar-card-title {
        color: #fff;
        border-bottom-color: rgba(255, 255, 255, 0.2);
    }
    
    .sidebar-card.gradient .sidebar-card-body {
        color: rgba(255, 255, 255, 0.95);
    }
    
    /* Boutons modernes */
    .btn-modern {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.625rem;
        padding: 1rem 2rem;
        font-weight: 600;
        font-size: 0.9375rem;
        border-radius: 12px;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        cursor: pointer;
        white-space: nowrap;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: #fff;
        box-shadow: 0 4px 16px rgba(37, 99, 235, 0.3);
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(37, 99, 235, 0.4);
    }
    
    .btn-outline {
        background: transparent;
        color: var(--primary);
        border: 2px solid var(--primary);
    }
    
    .btn-outline:hover {
        background: var(--primary);
        color: #fff;
        transform: translateY(-2px);
    }
    
    .btn-white {
        background: #fff;
        color: var(--primary);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    }
    
    .btn-white:hover {
        background: var(--bg-lighter);
        transform: translateY(-2px);
    }
    
    .btn-block {
        display: flex;
        width: 100%;
    }
    
    /* CTA Section Ultra-Moderne avec Image */
    .cta-section {
        margin-top: 0;
        padding-top: 0;
        border-top: none;
    }
    
    /* CTA dans la sidebar - Style WordPress */
    .cta-hero {
        position: relative;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border-radius: 8px;
        padding: 0;
        color: #fff;
        margin-bottom: 0;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        display: flex;
        flex-direction: column;
        align-items: stretch;
        min-height: auto;
        border: none;
    }
    
    .cta-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 700px;
        height: 700px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
        border-radius: 50%;
        animation: pulse 10s ease-in-out infinite;
    }
    
    .cta-hero::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -10%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(147, 197, 253, 0.2) 0%, transparent 60%);
        border-radius: 50%;
        animation: pulse 12s ease-in-out infinite reverse;
    }
    
    @keyframes pulse {
        0%, 100% {
            transform: scale(1) rotate(0deg);
            opacity: 1;
        }
        50% {
            transform: scale(1.15) rotate(5deg);
            opacity: 0.9;
        }
    }
    
    .cta-hero-image {
        flex: 0 0 auto;
        width: 100%;
        aspect-ratio: 1 / 1; /* Format carré */
        position: relative;
        overflow: hidden;
        border-radius: 8px 8px 0 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--bg-lighter);
    }
    
    .cta-hero-image img {
        width: 100%;
        height: 100%;
        object-fit: contain; /* Afficher l'image à 100% de sa taille réelle */
        object-position: center;
    }
    
    .cta-hero-content {
        position: relative;
        z-index: 1;
        flex: 1;
        padding: 1.5rem;
        text-align: left;
    }
    
    .cta-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0 0 1rem 0;
        letter-spacing: -0.01em;
        line-height: 1.3;
        color: #ffffff;
    }
    
    .cta-subtitle {
        font-size: 0.9375rem;
        margin: 0 0 1.5rem 0;
        opacity: 0.95;
        line-height: 1.6;
        text-align: left;
        font-weight: 400;
        color: rgba(255, 255, 255, 0.95);
    }
    
    .cta-benefits-grid {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin: 1.5rem 0;
        text-align: left;
    }
    
    .cta-benefit-card {
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
        padding: 0.75rem;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 6px;
        border: 1px solid rgba(255, 255, 255, 0.15);
        transition: all 0.3s;
    }
    
    .cta-benefit-card:hover {
        background: rgba(255, 255, 255, 0.15);
    }
    
    .cta-benefit-icon {
        font-size: 1.75rem;
        flex-shrink: 0;
    }
    
    .cta-benefit-text strong {
        display: block;
        margin-bottom: 0.375rem;
        font-weight: 600;
        font-size: 1.0625rem;
    }
    
    .cta-benefit-text span {
        font-size: 0.875rem;
        opacity: 0.9;
        line-height: 1.5;
    }
    
    .cta-buttons {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        justify-content: flex-start;
        margin: 1.5rem 0 1rem 0;
    }
    
    .cta-buttons .btn-modern {
        padding: 0.875rem 1.25rem;
        font-size: 0.9375rem;
        font-weight: 600;
        border-radius: 6px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: all 0.2s;
        position: relative;
        overflow: hidden;
        width: 100%;
        text-align: center;
    }
    
    .cta-buttons .btn-modern.btn-white {
        background: #ffffff;
        color: var(--primary);
        border: 1px solid #ffffff;
    }
    
    .cta-buttons .btn-modern.btn-white:hover {
        background: #f8fafc;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
    
    .cta-buttons .btn-modern.btn-outline {
        background: transparent;
        border: 1px solid rgba(255, 255, 255, 0.8);
        color: #ffffff;
    }
    
    .cta-buttons .btn-modern.btn-outline:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: #ffffff;
    }
    
    .cta-footer {
        font-size: 0.8125rem;
        opacity: 0.9;
        margin: 0;
        text-align: center;
        padding-top: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
        color: rgba(255, 255, 255, 0.9);
    }
    
    /* Summary Box Premium */
    .cta-summary-box {
        background: linear-gradient(135deg, var(--bg-lighter) 0%, var(--bg-light) 100%);
        border-radius: 20px;
        padding: 3rem;
        border-left: 6px solid var(--primary);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
    }
    
    .cta-summary-title {
        font-size: 1.875rem;
        font-weight: 800;
        margin: 0 0 1.75rem 0;
        color: var(--text-dark);
        letter-spacing: -0.01em;
    }
    
    .cta-summary-text {
        font-size: 1.0625rem;
        line-height: 1.8;
        color: var(--text-medium);
        margin-bottom: 1.75rem;
    }
    
    .cta-summary-list {
        list-style: none;
        padding: 0;
        margin: 2rem 0;
    }
    
    .cta-summary-list li {
        display: flex;
        gap: 1rem;
        margin: 1rem 0;
        color: var(--text-medium);
        line-height: 1.7;
    }
    
    .cta-summary-list li::before {
        content: '✓';
        color: var(--primary);
        font-weight: 700;
        font-size: 1.5rem;
        flex-shrink: 0;
        line-height: 1;
    }
    
    .cta-summary-footer {
        font-size: 1rem;
        line-height: 1.8;
        color: var(--text-light);
        font-style: italic;
        margin: 2rem 0 0 0;
        padding-top: 2rem;
        border-top: 1px solid var(--border);
    }
    
    /* Section Nos Réalisations */
    .portfolio-section {
        margin-top: 3rem;
        padding-top: 3rem;
        border-top: 1px solid var(--border);
    }
    
    .portfolio-section-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .portfolio-section-title i {
        color: var(--primary);
    }
    
    .portfolio-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
    }
    
    .portfolio-card {
        background: var(--bg-white);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        transition: all 0.3s;
    }
    
    .portfolio-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }
    
    .portfolio-image {
        width: 100%;
        height: 200px;
        overflow: hidden;
    }
    
    .portfolio-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }
    
    .portfolio-card:hover .portfolio-image img {
        transform: scale(1.05);
    }
    
    .portfolio-content {
        padding: 1.25rem;
    }
    
    .portfolio-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
    }
    
    .portfolio-type {
        font-size: 0.9rem;
        color: var(--text-medium);
        margin-bottom: 0.75rem;
    }
    
    .portfolio-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: gap 0.2s;
    }
    
    .portfolio-link:hover {
        gap: 0.75rem;
        color: var(--primary-dark);
    }
    
    /* Section Avis Clients */
    .reviews-section {
        margin-top: 3rem;
        padding-top: 3rem;
        border-top: 1px solid var(--border);
    }
    
    .reviews-section-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .reviews-section-title i {
        color: var(--accent);
    }
    
    .reviews-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
    }
    
    .review-card {
        background: var(--bg-white);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        border-left: 3px solid var(--accent);
    }
    
    .review-header {
        margin-bottom: 1rem;
    }
    
    .review-author-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .review-author-avatar {
        flex-shrink: 0;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid var(--border);
    }
    
    .review-avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    
    .review-avatar-placeholder {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.125rem;
        text-transform: uppercase;
    }
    
    .review-author-details {
        flex: 1;
        min-width: 0;
    }
    
    .review-stars {
        display: flex;
        gap: 0.25rem;
        margin-top: 0.25rem;
    }
    
    .review-stars i {
        color: #d1d5db;
        font-size: 0.875rem;
    }
    
    .review-stars i.active {
        color: var(--accent);
    }
    
    .review-author {
        font-weight: 600;
        color: var(--text-dark);
        font-size: 0.95rem;
        margin: 0 0 0.25rem 0;
    }
    
    .review-content {
        font-size: 0.95rem;
        color: var(--text-medium);
        line-height: 1.6;
        margin-bottom: 0.75rem;
    }
    
    .review-date {
        font-size: 0.85rem;
        color: var(--text-light);
        margin: 0;
    }
    
    .reviews-more-wrapper {
        text-align: center;
        margin-top: 2rem;
    }
    
    .reviews-more-button {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.875rem 2rem;
        background: var(--primary);
        color: #ffffff;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .reviews-more-button:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        gap: 0.75rem;
    }
    
    /* Section Lire aussi */
    .related-articles-section {
        margin-top: 3rem;
        padding-top: 3rem;
        border-top: 1px solid var(--border);
    }
    
    .related-articles-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .related-articles-title i {
        color: var(--primary);
    }
    
    .related-articles-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
    }
    
    .related-article-card {
        background: var(--bg-white);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s;
    }
    
    .related-article-card:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        transform: translateY(-4px);
    }
    
    .related-article-image {
        width: 100%;
        height: 200px;
        overflow: hidden;
    }
    
    .related-article-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }
    
    .related-article-card:hover .related-article-image img {
        transform: scale(1.05);
    }
    
    .related-article-content {
        padding: 1.25rem;
    }
    
    .related-article-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }
    
    .related-article-title a {
        color: var(--text-dark);
        text-decoration: none;
        transition: color 0.2s;
    }
    
    .related-article-title a:hover {
        color: var(--primary);
    }
    
    .related-article-excerpt {
        font-size: 0.9rem;
        color: var(--text-medium);
        line-height: 1.5;
        margin-bottom: 0.75rem;
    }
    
    .related-article-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
        transition: gap 0.2s;
    }
    
    .related-article-link:hover {
        gap: 0.75rem;
        color: var(--primary-dark);
    }
    
    /* Section Adresse complète */
    .company-address-section {
        margin-top: 3rem;
        padding-top: 3rem;
        border-top: 1px solid var(--border);
    }
    
    .company-address-card {
        background: var(--bg-light);
        border-radius: 12px;
        padding: 2rem;
        border-left: 4px solid var(--primary);
    }
    
    .company-address-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .company-address-title i {
        color: var(--primary);
    }
    
    .company-address-content {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    
    .company-address-line {
        font-size: 1.0625rem;
        color: var(--text-medium);
        line-height: 1.8;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }
    
    .company-address-line i {
        color: var(--primary);
        margin-top: 0.25rem;
        flex-shrink: 0;
    }
    
    .company-address-line a {
        color: var(--primary);
        text-decoration: none;
        transition: color 0.2s;
    }
    
    .company-address-line a:hover {
        color: var(--primary-dark);
        text-decoration: underline;
    }
    
    /* Responsive Design - WordPress Style */
    @media (max-width: 1024px) {
        .article-grid {
            grid-template-columns: 1fr !important;
            gap: 2rem;
        }
        
        /* Sur mobile : sidebar après le contenu */
        .article-sidebar {
            order: 2; /* Après le contenu */
            position: static;
            max-height: none;
            padding-right: 0;
            margin-right: 0;
        }
        
        .article-card {
            order: 1; /* Avant la sidebar */
        }
        
        .related-articles-grid {
            grid-template-columns: 1fr;
        }
        
        .company-address-card {
            padding: 2rem 1.5rem;
        }
        
        .article-container {
            padding: 2rem 1rem;
        }
        
        .article-content-container {
            padding: 0;
        }
    }
    
    @media (max-width: 768px) {
        .article-hero {
            min-height: 400px;
        }
        
        .hero-content {
            padding: 3rem 1.5rem;
        }
        
        .hero-title {
            font-size: 2rem;
            word-wrap: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
            max-width: 100%;
        }
        
        .hero-meta {
            gap: 1rem;
            font-size: 0.875rem;
        }
        
        .article-container {
            padding: 0 0.75rem;
        }
        
        .article-content-container {
            padding: 1.5rem;
            margin: 0 auto 2rem;
            border-radius: 8px;
        }
        
        .article-content-wrapper {
            padding: 2rem 1.5rem;
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
        
        .cta-hero {
            flex-direction: column;
            min-height: auto;
        }
        
        .cta-hero-image {
            flex: 0 0 auto;
            width: 100%;
            aspect-ratio: 1 / 1; /* Format carré sur mobile aussi */
            border-radius: 24px 24px 0 0;
        }
        
        .cta-hero-content {
            padding: 2.5rem 1.5rem;
            text-align: center;
        }
        
        .cta-title {
            font-size: 1.75rem;
        }
        
        .cta-subtitle {
            text-align: center;
        }
        
        .cta-benefits-grid {
            grid-template-columns: 1fr;
        }
        
        .cta-buttons {
            flex-direction: column;
            justify-content: center;
        }
        
        .btn-modern {
            width: 100%;
        }
        
        .cta-summary-box {
            padding: 2rem 1.5rem;
        }
    }
</style>
@endpush

@section('content')
<div class="article-page">
    <!-- Hero Section -->
    <div class="article-hero @if($article->featured_image) has-image @endif"
         @if($article->featured_image)
         style="background-image: url('{{ asset($article->featured_image) }}');"
         @endif>
        <div class="hero-content">
            <div class="article-badge">
                <i class="fas fa-tag"></i>
                <span>{{ $article->focus_keyword ?? 'Rénovation' }}</span>
            </div>
            
            <h1 class="hero-title" style="word-wrap: break-word; overflow-wrap: break-word; hyphens: auto; max-width: 100%;">{{ $article->title }}</h1>
            
            <div class="hero-meta">
                @if($article->published_at)
                <div class="hero-meta-item">
                    <i class="fas fa-calendar"></i>
                    <span>{{ $article->published_at->format('d M Y') }}</span>
                </div>
                @endif
                <div class="hero-meta-item">
                    <i class="fas fa-clock"></i>
                    <span>Lecture rapide</span>
                </div>
                <div class="hero-meta-item">
                    <i class="fas fa-user"></i>
                    <span>{{ setting('company_name') }}</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Contenu principal -->
    <div class="article-container">
        <div class="article-content-container">
        <div class="article-grid">
            <!-- Sidebar WordPress (à gauche sur desktop, après contenu sur mobile) -->
            <aside class="article-sidebar">
                <!-- CTA Simulateur -->
                <div class="sidebar-card cta-card">
                    <div class="cta-hero">
                        @php
                            $simulatorImage = setting('simulator_image');
                            $hasSimulatorImage = $simulatorImage && file_exists(public_path($simulatorImage));
                        @endphp
                        @if($hasSimulatorImage)
                        <div class="cta-hero-image">
                            <img src="{{ asset($simulatorImage) }}" alt="Simulateur de coût de travaux" loading="lazy">
                        </div>
                        @endif
                        <div class="cta-hero-content">
                            <h2 class="cta-title">💰 Simulateur de Coût de Travaux</h2>
                            <p class="cta-subtitle">
                                <strong>{{ setting('company_name') }}</strong>, votre partenaire expert local. Nous vous garantissons :
                            </p>
                            
                            <div class="cta-benefits-grid">
                                <div class="cta-benefit-card">
                                    <span class="cta-benefit-icon">✅</span>
                                    <div class="cta-benefit-text">
                                        <strong>Devis détaillé gratuit</strong>
                                        <span>Sous 24h, sans engagement</span>
                                    </div>
                                </div>
                                <div class="cta-benefit-card">
                                    <span class="cta-benefit-icon">✅</span>
                                    <div class="cta-benefit-text">
                                        <strong>Artisans certifiés</strong>
                                        <span>et assurés décennale</span>
                                    </div>
                                </div>
                                <div class="cta-benefit-card">
                                    <span class="cta-benefit-icon">✅</span>
                                    <div class="cta-benefit-text">
                                        <strong>Matériaux premium</strong>
                                        <span>Sélection rigoureuse</span>
                                    </div>
                                </div>
                                <div class="cta-benefit-card">
                                    <span class="cta-benefit-icon">✅</span>
                                    <div class="cta-benefit-text">
                                        <strong>Respect des délais</strong>
                                        <span>Transparence totale</span>
                                    </div>
                                </div>
                                <div class="cta-benefit-card">
                                    <span class="cta-benefit-icon">✅</span>
                                    <div class="cta-benefit-text">
                                        <strong>Service après-vente</strong>
                                        <span>Suivi personnalisé</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="cta-buttons">
                                <a href="{{ route('form.step', 'propertyType') }}" class="btn-modern btn-white">
                                    <i class="fas fa-calculator"></i>
                                    <span>Simulateur</span>
                                </a>
                                @if(setting('company_phone_raw'))
                                <a href="tel:{{ setting('company_phone_raw') }}" class="btn-modern btn-outline">
                                    <i class="fas fa-phone"></i>
                                    <span>{{ setting('company_phone') }}</span>
                                </a>
                                @endif
                            </div>
                            
                            <p class="cta-footer">
                                🔒 Vos données sont protégées · 500+ clients satisfaits nous font confiance
                            </p>
                        </div>
                    </div>
                </div>
                
            </aside>
            
            <!-- Article -->
            <article class="article-card">
                <div class="article-content-wrapper">
                    <div class="article-content">
                        @php
                            $content = $article->content_html;
                            
                            // Nettoyer les entités HTML multiples (&amp;amp; -> &)
                            $content = preg_replace('/&amp;(amp;)+/', '&', $content);
                            
                            // Debug: logger le contenu brut pour voir comment les images sont stockées
                            \Log::info('Article content_html brut', [
                                'article_id' => $article->id,
                                'content_length' => strlen($content),
                                'has_img_tags' => strpos($content, '<img') !== false,
                                'img_count' => substr_count($content, '<img'),
                                'sample' => substr($content, 0, 500),
                            ]);
                            
                            if (strpos($content, '&lt;') !== false && strpos($content, '<') === false) {
                                $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                            }
                            
                            // Extraire toutes les URLs d'images pour debug
                            preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $imgMatches);
                            if (!empty($imgMatches[1])) {
                                \Log::info('URLs d\'images trouvées dans le contenu', [
                                    'article_id' => $article->id,
                                    'urls' => $imgMatches[1],
                                ]);
                            }
                            
                            // Corriger les URLs d'images relatives en URLs absolues
                            // Les images uploadées via Quill ont des URLs comme /uploads/articles/... ou uploads/articles/...
                            // Aussi gérer les cas où l'URL est stockée comme texte au lieu d'une balise <img>
                            
                            // D'abord, améliorer le regex pour capturer toutes les balises img
                            $content = preg_replace_callback(
                                '/<img\s+([^>]*?)>/i',
                                function($matches) {
                                    $attributes = $matches[1];
                                    
                                    // Extraire le src avec un regex plus robuste
                                    if (preg_match('/src=["\']([^"\']+)["\']/i', $attributes, $srcMatch)) {
                                        $src = $srcMatch[1];
                                    } else {
                                        // Si pas de src trouvé, retourner tel quel
                                        return $matches[0];
                                    }
                                    
                                    // Si c'est déjà une URL absolue (http:// ou https://), la garder telle quelle
                                    if (preg_match('/^https?:\/\//', $src)) {
                                        $finalSrc = $src;
                                    }
                                    // Si c'est une URL relative (commence par /uploads/ ou uploads/)
                                    elseif (preg_match('/^\/?uploads\//', $src)) {
                                        // Convertir en URL absolue
                                        $finalSrc = asset(ltrim($src, '/'));
                                    }
                                    // Si c'est une URL relative sans slash initial (uploads/articles/...)
                                    elseif (!preg_match('/^(\/|https?:\/\/)/', $src)) {
                                        // Essayer de construire l'URL complète
                                        $cleanSrc = ltrim($src, '/');
                                        // Vérifier si le fichier existe
                                        if (file_exists(public_path($cleanSrc))) {
                                            $finalSrc = asset($cleanSrc);
                                        } else {
                                            // Essayer quand même avec asset()
                                            $finalSrc = asset($cleanSrc);
                                        }
                                    }
                                    // Si c'est une URL absolue avec le domaine (commence par /)
                                    elseif (preg_match('/^\//', $src) && !preg_match('/^\/\//', $src)) {
                                        // C'est déjà une URL absolue relative au domaine
                                        $finalSrc = asset(ltrim($src, '/'));
                                    } else {
                                        $finalSrc = $src;
                                    }
                                    
                                    // Remplacer le src dans les attributs
                                    $newAttributes = preg_replace('/src=["\'][^"\']+["\']/i', 'src="' . htmlspecialchars($finalSrc, ENT_QUOTES, 'UTF-8') . '"', $attributes);
                                    
                                    // S'assurer que l'image a un alt text si manquant
                                    if (!preg_match('/alt=["\']/', $newAttributes)) {
                                        $newAttributes .= ' alt="Image article"';
                                    }
                                    
                                    // Ajouter loading lazy pour améliorer les performances
                                    if (!preg_match('/loading=["\']/', $newAttributes)) {
                                        $newAttributes .= ' loading="lazy"';
                                    }
                                    
                                    // Reconstruire la balise img avec l'URL corrigée
                                    return '<img ' . $newAttributes . '>';
                                },
                                $content
                            );
                            
                            // Debug: logger les URLs après traitement
                            preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $imgMatchesAfter);
                            if (!empty($imgMatchesAfter[1])) {
                                \Log::info('URLs d\'images après traitement', [
                                    'article_id' => $article->id,
                                    'urls' => $imgMatchesAfter[1],
                                ]);
                            }
                            
                            // ÉTAPE 0: SOLUTION ULTIME - Traiter d'abord les cas avec URL dupliquée AVANT nettoyage
                            // Pattern exact: https://domain.comhttps://domain.com/uploads/...image.jpg" alt="..."
                            // Ce pattern DOIT être traité en premier car il contient l'URL dupliquée
                            $content = preg_replace_callback(
                                '/(https?:\/\/[^\/\s<>"\']+)(\1)+\/uploads\/([^\s<>"\']+\.(jpg|jpeg|png|gif|webp|svg))("\s+alt=["\']([^"\']+)["\'][^>]*>)/i',
                                function($matches) {
                                    // Nettoyer l'URL dupliquée
                                    $cleanDomain = $matches[1];
                                    $filename = $matches[3];
                                    $src = $cleanDomain . '/uploads/' . $filename;
                                    $alt = isset($matches[6]) ? html_entity_decode($matches[6], ENT_QUOTES | ENT_HTML5, 'UTF-8') : 'Image article';
                                    // Nettoyer les entités HTML multiples (&amp;amp; -> &)
                                    $alt = preg_replace('/&amp;(amp;)+/', '&', $alt);
                                    $alt = html_entity_decode($alt, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                    \Log::info('SOLUTION ULTIME: URL dupliquée nettoyée et balise réparée', ['src' => $src, 'alt' => $alt]);
                                    return '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '" class="article-image" loading="lazy">';
                                },
                                $content
                            );
                            
                            // ÉTAPE 0a: Nettoyer toutes les autres URLs dupliquées restantes
                            $content = preg_replace('/(https?:\/\/[^\/\s<>"\']+)(\1)+\/uploads\//i', '$1/uploads/', $content);
                            $content = preg_replace('/(https?:\/\/[^\/\s<>"\']+)(\1)+/i', '$1', $content);
                            
                            // ÉTAPE 0b: Réparer les balises <img> cassées avec URL propre et &amp; dans l'alt
                            // Pattern: https://domain.com/uploads/...image.jpg" alt="...&amp;amp;..." loading="lazy">
                            $content = preg_replace_callback(
                                '/(https?:\/\/[^\s<>"\']+\/uploads\/[^\s<>"\']+\.(jpg|jpeg|png|gif|webp|svg))("\s+alt=["\']([^"\']+)["\'][^>]*>)/i',
                                function($matches) {
                                    $src = $matches[1];
                                    $alt = isset($matches[4]) ? html_entity_decode($matches[4], ENT_QUOTES | ENT_HTML5, 'UTF-8') : 'Image article';
                                    // Nettoyer les entités HTML multiples (&amp;amp; -> &)
                                    $alt = preg_replace('/&amp;(amp;)+/', '&', $alt);
                                    $alt = html_entity_decode($alt, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                    \Log::info('SOLUTION ULTIME: Balise img cassée réparée (URL directe)', ['src' => $src, 'alt' => $alt]);
                                    return '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '" class="article-image" loading="lazy">';
                                },
                                $content
                            );
                            
                            // ÉTAPE 0b: Réparer aussi les URLs relatives qui commencent directement
                            $content = preg_replace_callback(
                                '/(\/?uploads\/[^\s<>"\']+\.(jpg|jpeg|png|gif|webp|svg))("\s+alt=["\']([^"\']+)["\'][^>]*>)/i',
                                function($matches) {
                                    $src = asset(ltrim($matches[1], '/'));
                                    $alt = isset($matches[4]) ? html_entity_decode($matches[4], ENT_QUOTES | ENT_HTML5, 'UTF-8') : 'Image article';
                                    // Nettoyer les entités HTML multiples
                                    $alt = preg_replace('/&amp;(amp;)+/', '&', $alt);
                                    $alt = html_entity_decode($alt, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                    \Log::info('SOLUTION ULTIME: Balise img cassée réparée (URL relative)', ['src' => $src, 'alt' => $alt]);
                                    return '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '" class="article-image" loading="lazy">';
                                },
                                $content
                            );
                            
                            // ÉTAPE 1: Protéger toutes les balises <img> valides existantes AVANT tout traitement
                            $validImages = [];
                            $content = preg_replace_callback(
                                '/<img\s+[^>]*src=["\']([^"\']+)["\'][^>]*>/i',
                                function($matches) use (&$validImages) {
                                    $placeholder = '<!--IMG_VALID_' . count($validImages) . '-->';
                                    $validImages[] = $matches[0];
                                    \Log::info('Image protégée', [
                                        'placeholder' => $placeholder,
                                        'img_tag' => $matches[0],
                                        'src' => $matches[1]
                                    ]);
                                    return $placeholder;
                                },
                                $content
                            );
                            
                            \Log::info('Images protégées', [
                                'count' => count($validImages),
                                'content_length_after_protection' => strlen($content)
                            ]);
                            
                            // ÉTAPE 2: Nettoyer les URLs dupliquées (domaine répété plusieurs fois) - seulement en dehors des placeholders
                            $content = preg_replace('/(https?:\/\/[^\/\s<>"\']+)(\1)+/i', '$1', $content);
                            
                            // ÉTAPE 3: Réparer les balises <img> cassées avec URLs dupliquées et attributs multiples
                            // Mais seulement si ce n'est pas un placeholder
                            $content = preg_replace_callback(
                                '/(https?:\/\/[^\s<>"\']+\/uploads\/[^\s<>"\']+\.(jpg|jpeg|png|gif|webp|svg))("(?:\s+[^>]*?)?>)+/i',
                                function($matches) {
                                    // Vérifier que ce n'est pas dans un placeholder
                                    if (strpos($matches[0], '<!--IMG_VALID_') !== false) {
                                        return $matches[0];
                                    }
                                    $src = $matches[1];
                                    $alt = 'Image article';
                                    if (preg_match('/alt=["\']([^"\']+)["\']/', $matches[0], $altMatch)) {
                                        $alt = $altMatch[1];
                                    }
                                    \Log::info('Image cassée réparée (URL complète)', ['src' => $src]);
                                    return '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '" class="article-image" loading="lazy">';
                                },
                                $content
                            );
                            
                            // ÉTAPE 4: Réparer les URLs relatives avec attributs dupliqués
                            $content = preg_replace_callback(
                                '/(\/?uploads\/[^\s<>"\']+\.(jpg|jpeg|png|gif|webp|svg))("(?:\s+[^>]*?)?>)+/i',
                                function($matches) {
                                    // Vérifier que ce n'est pas dans un placeholder
                                    if (strpos($matches[0], '<!--IMG_VALID_') !== false) {
                                        return $matches[0];
                                    }
                                    $src = asset(ltrim($matches[1], '/'));
                                    $alt = 'Image article';
                                    if (preg_match('/alt=["\']([^"\']+)["\']/', $matches[0], $altMatch)) {
                                        $alt = $altMatch[1];
                                    }
                                    \Log::info('Image cassée réparée (URL relative)', ['src' => $src]);
                                    return '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '" class="article-image" loading="lazy">';
                                },
                                $content
                            );
                            
                            // ÉTAPE 5: Réparer les balises <img> cassées qui commencent directement par une URL
                            // Pattern: https://domain.com/uploads/...image.jpg" alt="..." loading="lazy">
                            // (sans <img src= au début) - le guillemet peut être collé ou avec espace
                            // Gérer aussi les cas avec backslash échappé
                            $content = preg_replace_callback(
                                '/(https?:\/\/[^\s<>"\']+\/uploads\/[^\s<>"\']+\.(jpg|jpeg|png|gif|webp|svg))("(?:\s+)?alt=["\']([^"\']+)["\'][^>]*>)/i',
                                function($matches) {
                                    // Vérifier que ce n'est pas dans un placeholder
                                    $pos = strpos($content, $matches[0]);
                                    if ($pos !== false) {
                                        $before = substr($content, 0, $pos);
                                        if (strpos($before, '<!--IMG_VALID_') !== false && strpos($before, '-->') > strrpos($before, '<!--IMG_VALID_')) {
                                            return $matches[0]; // Garder si dans un placeholder
                                        }
                                    }
                                    $src = $matches[1];
                                    $alt = isset($matches[4]) ? $matches[4] : 'Image article';
                                    \Log::info('Balise img cassée réparée (URL directe complète)', ['src' => $src, 'alt' => $alt, 'match' => $matches[0]]);
                                    return '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '" class="article-image" loading="lazy">';
                                },
                                $content
                            );
                            
                            // ÉTAPE 5a: Réparer aussi les cas avec backslash échappé dans l'URL
                            // Pattern: https://domain.com/uploads/...article\_1762875356\_691357dc893a7.jpeg" alt="..."
                            $content = preg_replace_callback(
                                '/(https?:\/\/[^\s<>"\']+\/uploads\/[^\s<>"\']+(?:\\\\_|[^"\'])+\.(jpg|jpeg|png|gif|webp|svg))("(?:\s+)?alt=["\']([^"\']+)["\'][^>]*>)/i',
                                function($matches) {
                                    // Vérifier que ce n'est pas dans un placeholder
                                    $pos = strpos($content, $matches[0]);
                                    if ($pos !== false) {
                                        $before = substr($content, 0, $pos);
                                        if (strpos($before, '<!--IMG_VALID_') !== false && strpos($before, '-->') > strrpos($before, '<!--IMG_VALID_')) {
                                            return $matches[0];
                                        }
                                    }
                                    $src = str_replace('\\_', '_', $matches[1]); // Déséchapper les underscores
                                    $alt = isset($matches[4]) ? $matches[4] : 'Image article';
                                    \Log::info('Balise img cassée réparée (URL avec backslash)', ['src' => $src, 'alt' => $alt]);
                                    return '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '" class="article-image" loading="lazy">';
                                },
                                $content
                            );
                            
                            // ÉTAPE 5b: Réparer les URLs relatives qui commencent directement
                            // Pattern: /uploads/articles/...image.jpg" alt="..." loading="lazy">
                            $content = preg_replace_callback(
                                '/(\/?uploads\/[^\s<>"\']+\.(jpg|jpeg|png|gif|webp|svg))("(?:\s+)?alt=["\']([^"\']+)["\'][^>]*>)/i',
                                function($matches) {
                                    // Vérifier que ce n'est pas dans un placeholder
                                    $pos = strpos($content, $matches[0]);
                                    if ($pos !== false) {
                                        $before = substr($content, 0, $pos);
                                        if (strpos($before, '<!--IMG_VALID_') !== false && strpos($before, '-->') > strrpos($before, '<!--IMG_VALID_')) {
                                            return $matches[0]; // Garder si dans un placeholder
                                        }
                                    }
                                    $src = asset(ltrim($matches[1], '/'));
                                    $alt = isset($matches[4]) ? $matches[4] : 'Image article';
                                    \Log::info('Balise img cassée réparée (URL relative directe)', ['src' => $src, 'alt' => $alt]);
                                    return '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '" class="article-image" loading="lazy">';
                                },
                                $content
                            );
                            
                            // ÉTAPE 5c: Réparer aussi les cas où l'URL est seule (sans attributs après)
                            // Pattern: https://domain.com/uploads/...image.jpg
                            $content = preg_replace_callback(
                                '/(https?:\/\/[^\s<>"\']+\/uploads\/[^\s<>"\']+\.(jpg|jpeg|png|gif|webp|svg))(?![^<]*>)/i',
                                function($matches) {
                                    // Vérifier que ce n'est pas dans un placeholder ou déjà dans une balise img
                                    $pos = strpos($content, $matches[0]);
                                    if ($pos !== false) {
                                        $before = substr($content, 0, $pos);
                                        // Si c'est déjà dans une balise img, ne pas toucher
                                        if (preg_match('/<img[^>]*src=["\'][^"\']*$/i', $before)) {
                                            return $matches[0];
                                        }
                                        // Si c'est dans un placeholder, ne pas toucher
                                        if (strpos($before, '<!--IMG_VALID_') !== false && strpos($before, '-->') > strrpos($before, '<!--IMG_VALID_')) {
                                            return $matches[0];
                                        }
                                    }
                                    $src = $matches[1];
                                    \Log::info('URL seule convertie en balise img (URL complète)', ['src' => $src]);
                                    return '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="Image article" class="article-image" loading="lazy">';
                                },
                                $content
                            );
                            
                            // ÉTAPE 5d: Réparer les URLs relatives seules
                            $content = preg_replace_callback(
                                '/(\/?uploads\/[^\s<>"\']+\.(jpg|jpeg|png|gif|webp|svg))(?![^<]*>)/i',
                                function($matches) {
                                    // Vérifier que ce n'est pas dans un placeholder ou déjà dans une balise img
                                    $pos = strpos($content, $matches[0]);
                                    if ($pos !== false) {
                                        $before = substr($content, 0, $pos);
                                        // Si c'est déjà dans une balise img, ne pas toucher
                                        if (preg_match('/<img[^>]*src=["\'][^"\']*$/i', $before)) {
                                            return $matches[0];
                                        }
                                        // Si c'est dans un placeholder, ne pas toucher
                                        if (strpos($before, '<!--IMG_VALID_') !== false && strpos($before, '-->') > strrpos($before, '<!--IMG_VALID_')) {
                                            return $matches[0];
                                        }
                                    }
                                    $src = asset(ltrim($matches[1], '/'));
                                    \Log::info('URL seule convertie en balise img (URL relative)', ['src' => $src]);
                                    return '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="Image article" class="article-image" loading="lazy">';
                                },
                                $content
                            );
                            
                            // ÉTAPE 5c: Supprimer les fragments de balises <img> cassées restantes (qui ne commencent pas par <img)
                            // Mais seulement si ce n'est pas dans un placeholder
                            $content = preg_replace_callback(
                                '/"\s+alt=["\'][^"\']+["\'][^>]*>/i',
                                function($matches) {
                                    // Vérifier que ce n'est pas dans un placeholder
                                    $pos = strpos($content, $matches[0]);
                                    if ($pos !== false) {
                                        $before = substr($content, 0, $pos);
                                        if (strpos($before, '<!--IMG_VALID_') !== false && strpos($before, '-->') > strrpos($before, '<!--IMG_VALID_')) {
                                            return $matches[0]; // Garder si dans un placeholder
                                        }
                                    }
                                    return ''; // Supprimer sinon
                                },
                                $content
                            );
                            
                            // ÉTAPE 6: Convertir les URLs d'images restantes en balises <img>
                            // Mais seulement si ce n'est pas dans un placeholder
                            // URLs complètes avec domaine
                            $content = preg_replace_callback(
                                '/(https?:\/\/[^\s<>"\']+\/uploads\/[^\s<>"\']+\.(jpg|jpeg|png|gif|webp|svg))/i',
                                function($matches) {
                                    // Vérifier que ce n'est pas dans un placeholder
                                    $pos = strpos($content, $matches[0]);
                                    if ($pos !== false) {
                                        $before = substr($content, 0, $pos);
                                        if (strpos($before, '<!--IMG_VALID_') !== false && strpos($before, '-->') > strrpos($before, '<!--IMG_VALID_')) {
                                            return $matches[0]; // Garder si dans un placeholder
                                        }
                                    }
                                    \Log::info('URL convertie en balise img (URL complète)', ['url' => $matches[1]]);
                                    return '<img src="' . htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8') . '" alt="Image article" class="article-image" loading="lazy">';
                                },
                                $content
                            );
                            
                            // URLs relatives
                            $content = preg_replace_callback(
                                '/(\/?uploads\/[^\s<>"\']+\.(jpg|jpeg|png|gif|webp|svg))/i',
                                function($matches) {
                                    // Vérifier que ce n'est pas dans un placeholder
                                    $pos = strpos($content, $matches[0]);
                                    if ($pos !== false) {
                                        $before = substr($content, 0, $pos);
                                        if (strpos($before, '<!--IMG_VALID_') !== false && strpos($before, '-->') > strrpos($before, '<!--IMG_VALID_')) {
                                            return $matches[0]; // Garder si dans un placeholder
                                        }
                                    }
                                    $src = asset(ltrim($matches[1], '/'));
                                    \Log::info('URL convertie en balise img (URL relative)', ['url' => $matches[1], 'src' => $src]);
                                    return '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="Image article" class="article-image" loading="lazy">';
                                },
                                $content
                            );
                            
                            // ÉTAPE 7: Restaurer les balises <img> valides protégées
                            foreach ($validImages as $index => $imgTag) {
                                $content = str_replace('<!--IMG_VALID_' . $index . '-->', $imgTag, $content);
                            }
                            
                            \Log::info('Images restaurées', [
                                'count' => count($validImages),
                                'content_length_after_restore' => strlen($content)
                            ]);
                            
                            // ÉTAPE 8: Nettoyer les balises <img> sans src valide (seulement celles vraiment cassées)
                            $content = preg_replace('/<img(?![^>]*src=["\'][^"\']+["\'])[^>]*>/i', '', $content);
                            
                            // Log final pour debug
                            preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $finalImgMatches);
                            \Log::info('Images finales dans le contenu', [
                                'count' => count($finalImgMatches[1]),
                                'urls' => $finalImgMatches[1]
                            ]);
                            
                            // Supprimer les sections CTA finales générées par GPT
                            $content = preg_replace('/<div[^>]*class="cta-final"[^>]*>.*?<\/div>/is', '', $content);
                            $content = preg_replace('/<section[^>]*class="article-conclusion"[^>]*>.*?<\/section>/is', '', $content);
                            $content = preg_replace('/🚀\s*Lancez\s+Votre\s+Projet[^<]*<.*?🔒[^<]*<.*?<\/div>/is', '', $content);
                            $content = preg_replace('/En\s+Résumé\s*:.*?🏆[^<]*<.*?<\/section>/is', '', $content);
                            
                            // Nettoyer les styles inline qui peuvent casser la mise en page
                            $content = preg_replace('/style="[^"]*position\s*:\s*absolute[^"]*"/i', '', $content);
                            $content = preg_replace('/style="[^"]*position\s*:\s*fixed[^"]*"/i', '', $content);
                            $content = preg_replace('/style="[^"]*width\s*:\s*100%[^"]*"/i', 'style="max-width: 100%"', $content);
                            
                            // S'assurer que les balises sont bien fermées (approche simple)
                            // Compter les balises ouvrantes et fermantes pour les divs et sections
                            $openDivs = substr_count($content, '<div');
                            $closeDivs = substr_count($content, '</div>');
                            if ($openDivs > $closeDivs) {
                                $content .= str_repeat('</div>', $openDivs - $closeDivs);
                            }
                            
                            $openSections = substr_count($content, '<section');
                            $closeSections = substr_count($content, '</section>');
                            if ($openSections > $closeSections) {
                                $content .= str_repeat('</section>', $openSections - $closeSections);
                            }
                            
                            if (class_exists('\App\Helpers\InternalLinkingHelper')) {
                                try {
                                    $content = \App\Helpers\InternalLinkingHelper::generateInternalLinks($content, 'article');
                                } catch (\Exception $e) {}
                            }
                            
                            $content = preg_replace_callback(
                                '/(?<!href=["\'])(?<!>)(https?:\/\/[^\s<>"\'\)]+)(?![^<]*<\/a>)/',
                                function($matches) {
                                    return '<a href="' . htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener noreferrer">' . htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8') . '</a>';
                                },
                                $content
                            );
                        @endphp
                        {!! $content !!}
                    </div>
                </div>
            </article>
        </div>
        
        <!-- Section Nos Réalisations -->
        @php
            $portfolioItems = \App\Models\Setting::get('portfolio_items', []);
            if (!is_array($portfolioItems)) {
                $portfolioItems = [];
            }
            $displayedRealizations = collect($portfolioItems)
                ->filter(function($item) {
                    return is_array($item) && isset($item['title']);
                })
                ->shuffle()
                ->take(2);
        @endphp
        
        @if($displayedRealizations->count() > 0)
        <div class="portfolio-section">
            <h2 class="portfolio-section-title">
                <i class="fas fa-images"></i>
                Nos Réalisations
            </h2>
            <div class="portfolio-grid">
                @foreach($displayedRealizations as $item)
                <div class="portfolio-card">
                    @if(!empty($item['images']))
                        @php
                            $firstImage = is_array($item['images']) ? $item['images'][0] : $item['images'];
                        @endphp
                        <div class="portfolio-image">
                            <img src="{{ asset($firstImage) }}" alt="{{ $item['title'] ?? 'Réalisation' }}">
                        </div>
                    @endif
                    <div class="portfolio-content">
                        <h3 class="portfolio-title">{{ $item['title'] ?? 'Réalisation' }}</h3>
                        @if(!empty($item['work_type']))
                        <p class="portfolio-type">{{ $item['work_type'] }}</p>
                        @endif
                        @if(!empty($item['slug']))
                        <a href="{{ route('portfolio.show', $item['slug']) }}" class="portfolio-link">
                            Voir la réalisation <i class="fas fa-arrow-right"></i>
                        </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        
        <!-- Section Avis Clients -->
        @if(isset($reviews) && $reviews->count() > 0)
        <div class="reviews-section">
            <h2 class="reviews-section-title">
                <i class="fas fa-star"></i>
                Avis de Nos Clients
            </h2>
            <div class="reviews-grid">
                @foreach($reviews->take(3) as $review)
                <div class="review-card">
                    <div class="review-header">
                        <div class="review-author-info">
                            <div class="review-author-avatar">
                                @if($review->author_photo_url)
                                    <img src="{{ $review->author_photo_url }}" alt="{{ $review->author_name ?? 'Auteur' }}" class="review-avatar-img">
                                @elseif($review->author_photo)
                                    <img src="{{ asset($review->author_photo) }}" alt="{{ $review->author_name ?? 'Auteur' }}" class="review-avatar-img">
                                @else
                                    <div class="review-avatar-placeholder">
                                        {{ $review->author_initials ?? '?' }}
                                    </div>
                                @endif
                            </div>
                            <div class="review-author-details">
                                @if($review->author_name)
                                <p class="review-author">{{ $review->author_name }}</p>
                                @endif
                                <div class="review-stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= ($review->rating ?? 5) ? 'active' : '' }}"></i>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($review->review_text)
                    <p class="review-content">{{ Str::limit($review->review_text, 150) }}</p>
                    @elseif($review->content)
                    <p class="review-content">{{ Str::limit($review->content, 150) }}</p>
                    @endif
                    @if($review->review_date)
                    <p class="review-date">{{ $review->review_date->format('d/m/Y') }}</p>
                    @elseif($review->created_at)
                    <p class="review-date">{{ $review->created_at->format('d/m/Y') }}</p>
                    @endif
                </div>
                @endforeach
            </div>
            @if($reviews->count() > 3)
            <div class="reviews-more-wrapper">
                <a href="{{ route('reviews.all') ?? '/avis' }}" class="reviews-more-button">
                    Voir plus d'avis <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            @endif
        </div>
        @endif
        
        <!-- Section Lire aussi -->
        @php
            $relatedArticles = \App\Models\Article::where('status', 'published')
                ->where('id', '!=', $article->id)
                ->orderBy('published_at', 'desc')
                ->limit(3)
                ->get();
        @endphp
        
        @if($relatedArticles->count() > 0)
        <div class="related-articles-section">
            <h2 class="related-articles-title">
                <i class="fas fa-book-reader"></i>
                Lire aussi
            </h2>
            <div class="related-articles-grid">
                @foreach($relatedArticles as $relatedArticle)
                <article class="related-article-card">
                    @if($relatedArticle->featured_image)
                    <div class="related-article-image">
                        <img src="{{ asset($relatedArticle->featured_image) }}" alt="{{ $relatedArticle->title }}">
                    </div>
                    @endif
                    <div class="related-article-content">
                        <h3 class="related-article-title">
                            <a href="{{ route('blog.show', $relatedArticle) }}">{{ $relatedArticle->title }}</a>
                        </h3>
                        @if($relatedArticle->meta_description)
                        <p class="related-article-excerpt">{{ Str::limit(strip_tags($relatedArticle->meta_description), 120) }}</p>
                        @endif
                        <a href="{{ route('blog.show', $relatedArticle) }}" class="related-article-link">
                            Lire la suite <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </article>
                @endforeach
            </div>
        </div>
        @endif
        
        <!-- Section Adresse complète -->
        <div class="company-address-section">
            <div class="company-address-card">
                <h3 class="company-address-title">
                    <i class="fas fa-map-marker-alt"></i>
                    {{ setting('company_name') }}
                </h3>
                <div class="company-address-content">
                    <p class="company-address-line">
                        <i class="fas fa-map-pin"></i>
                        <strong>Adresse complète :</strong> 
                        {{ setting('company_address') }}
                        @if(setting('company_postal_code') || setting('company_city'))
                        ,
                        @endif
                        @if(setting('company_postal_code'))
                        {{ setting('company_postal_code') }}
                        @endif
                        @if(setting('company_postal_code') && setting('company_city'))
                        &nbsp;
                        @endif
                        @if(setting('company_city'))
                        {{ setting('company_city') }}
                        @endif
                    </p>
                    @if(setting('company_phone'))
                    <p class="company-address-line">
                        <i class="fas fa-phone"></i>
                        <strong>Téléphone :</strong> 
                        <a href="tel:{{ setting('company_phone_raw') }}">{{ setting('company_phone') }}</a>
                    </p>
                    @endif
                    @if(setting('company_email'))
                    <p class="company-address-line">
                        <i class="fas fa-envelope"></i>
                        <strong>Email :</strong> 
                        <a href="mailto:{{ setting('company_email') }}">{{ setting('company_email') }}</a>
                    </p>
                    @endif
                </div>
            </div>
        </div>
        </div>
    </div>
</div><br><br>
@endsection