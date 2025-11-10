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
        line-height: 1.1;
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
    
    /* Layout principal */
    .article-container {
        max-width: 1400px;
        margin: -100px auto 0;
        padding: 0;
        position: relative;
        z-index: 10;
        width: 100%;
        box-sizing: border-box;
    }
    
    /* Conteneur avec border pour tout le contenu */
    .article-content-container {
        background: var(--bg-white);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 2.5rem;
        margin: 0 2rem 5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    
    .article-grid {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    
    /* S'assurer que la grille fonctionne correctement */
    .article-grid > * {
        min-width: 0;
        box-sizing: border-box;
    }
    
    /* Card Article Principale */
    .article-card {
        background: transparent;
        border-radius: 0;
        box-shadow: none;
        overflow: visible;
        transition: none;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        position: relative;
        z-index: 1;
    }
    
    
    .article-content-wrapper {
        padding: 2.5rem;
    }
    
    /* Styles du contenu enrichis */
    .article-content {
        font-size: 1.0625rem;
        line-height: 1.5;
        color: var(--text-medium);
        max-width: 100%;
        overflow-x: auto; /* Permet le scroll horizontal si nécessaire */
        word-wrap: break-word;
        overflow-wrap: break-word;
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
        margin: 1.25rem 0;
        padding-left: 1.5rem;
    }
    
    .article-content li {
        margin: 0.5rem 0;
        padding-left: 0.5rem;
        line-height: 1.6;
    }
    
    .article-content ul li::marker {
        color: var(--primary);
        font-size: 1.2em;
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
    
    /* Sidebar moderne */
    .article-sidebar {
        position: sticky;
        top: 20px;
        display: flex;
        flex-direction: column;
        gap: 1.75rem;
        align-self: start;
        z-index: 5;
        max-height: calc(100vh - 40px);
        overflow-y: auto;
    }
    
    /* Style pour le scroll de la sidebar */
    .article-sidebar::-webkit-scrollbar {
        width: 6px;
    }
    
    .article-sidebar::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .article-sidebar::-webkit-scrollbar-thumb {
        background: var(--primary-color);
        border-radius: 10px;
    }
    
    .article-sidebar::-webkit-scrollbar-thumb:hover {
        background: var(--secondary-color);
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
    
    /* CTA Section Ultra-Moderne */
    .cta-section {
        margin-top: 4rem;
        padding-top: 4rem;
        border-top: 2px solid var(--border);
    }
    
    .cta-hero {
        position: relative;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border-radius: 24px;
        padding: 4rem 3rem;
        color: #fff;
        margin-bottom: 2.5rem;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(37, 99, 235, 0.25);
    }
    
    .cta-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.12) 0%, transparent 60%);
        border-radius: 50%;
        animation: pulse 8s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.1);
            opacity: 0.8;
        }
    }
    
    .cta-hero-content {
        position: relative;
        z-index: 1;
        max-width: 800px;
        margin: 0 auto;
        text-align: center;
    }
    
    .cta-title {
        font-size: 2.25rem;
        font-weight: 800;
        margin: 0 0 1.25rem 0;
        letter-spacing: -0.01em;
    }
    
    .cta-subtitle {
        font-size: 1.125rem;
        margin: 0 0 2.5rem 0;
        opacity: 0.95;
        line-height: 1.6;
    }
    
    .cta-benefits-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin: 2.5rem 0;
        text-align: left;
    }
    
    .cta-benefit-card {
        display: flex;
        gap: 1rem;
        align-items: flex-start;
        padding: 1.25rem;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.15);
        transition: all 0.3s;
    }
    
    .cta-benefit-card:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-2px);
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
        gap: 1.25rem;
        justify-content: center;
        flex-wrap: wrap;
        margin: 2.5rem 0 2rem 0;
    }
    
    .cta-footer {
        font-size: 0.9375rem;
        opacity: 0.9;
        margin: 0;
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
    
    /* Responsive Design */
    @media (max-width: 1024px) {
        .related-articles-grid {
            grid-template-columns: 1fr;
        }
        
        .company-address-card {
            padding: 2rem 1.5rem;
        }
        
        .article-container {
            margin-top: -60px;
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
        }
        
        .hero-meta {
            gap: 1rem;
            font-size: 0.875rem;
        }
        
        .article-container {
            padding: 0 1.5rem 3rem;
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
            padding: 2.5rem 1.5rem;
        }
        
        .cta-title {
            font-size: 1.75rem;
        }
        
        .cta-benefits-grid {
            grid-template-columns: 1fr;
        }
        
        .cta-buttons {
            flex-direction: column;
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
            
            <h1 class="hero-title">{{ $article->title }}</h1>
            
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
            <!-- Article -->
            <article class="article-card">
                <div class="article-content-wrapper">
                    <div class="article-content">
                        @php
                            $content = $article->content_html;
                            
                            if (strpos($content, '&lt;') !== false && strpos($content, '<') === false) {
                                $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                            }
                            
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
                    
                    <!-- CTA Section -->
                    <div class="cta-section">
                        <div class="cta-hero">
                            <div class="cta-hero-content">
                                <h2 class="cta-title">🚀 Lancez Votre Projet en Toute Confiance</h2>
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
                                            <span>RGE et assurés décennale</span>
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
                                        <span>Demander mon devis gratuit</span>
                                    </a>
                                    @if(setting('company_phone_raw'))
                                    <a href="tel:{{ setting('company_phone_raw') }}" class="btn-modern btn-outline" style="color: #fff; border-color: #fff;">
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
                        
                        <div class="cta-summary-box">
                            <h3 class="cta-summary-title">En Résumé : Votre Référence Complète</h3>
                            
                            <p class="cta-summary-text">
                                Vous l'avez découvert dans ce guide expert, les bénéfices d'un devis clair et détaillé ne sont pas négligeables. Il vous offre non seulement une vision précise des coûts, mais aussi une assurance contre les imprévus.
                            </p>
                            
                            <p class="cta-summary-text">
                                Faire appel à <strong>{{ setting('company_name') }}</strong> pour votre projet, c'est choisir :
                            </p>
                            
                            <ul class="cta-summary-list">
                                <li>Une expertise reconnue et des matériaux de qualité supérieure</li>
                                <li>Un accompagnement personnalisé du début à la fin du projet</li>
                                <li>Une transparence totale et des tarifs compétitifs</li>
                            </ul>
                            
                            <p class="cta-summary-text">
                                <strong>Ne laissez pas votre projet en suspens.</strong> Que vous soyez en phase de réflexion ou prêt à vous lancer, notre équipe d'experts certifiés est là pour vous conseiller sans engagement. Obtenez votre devis personnalisé gratuit et découvrez comment transformer votre vision en réalité.
                            </p>
                            
                            <p class="cta-summary-footer">
                                🏆 <strong>{{ setting('company_name') }}</strong>, votre expert de confiance{{ setting('company_city') ? ' à ' . setting('company_city') : '' }}
                            </p>
                        </div>
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
                        <div class="review-stars">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= ($review->rating ?? 5) ? 'active' : '' }}"></i>
                            @endfor
                        </div>
                        @if($review->author_name)
                        <p class="review-author">{{ $review->author_name }}</p>
                        @endif
                    </div>
                    @if($review->content)
                    <p class="review-content">{{ Str::limit($review->content, 150) }}</p>
                    @endif
                    @if($review->created_at)
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
</div>
@endsection