<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $themeDefaultSetting = setting('theme_default', 'light');
        if (!in_array($themeDefaultSetting, ['light', 'dark', 'system'], true)) {
            $themeDefaultSetting = 'light';
        }
    @endphp
    <script>
        (function () {
            var def = @json($themeDefaultSetting);
            var stored = null;
            try { stored = localStorage.getItem('site-theme'); } catch (e) {}
            function resolve() {
                if (stored === 'light' || stored === 'dark') return stored;
                if (def === 'dark') return 'dark';
                if (def === 'system' && window.matchMedia) {
                    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                }
                return 'light';
            }
            function applyHtmlTheme(theme) {
                document.documentElement.setAttribute('data-theme', theme);
                document.documentElement.classList.toggle('dark', theme === 'dark');
            }
            applyHtmlTheme(resolve());
            if ((!stored || stored === '') && def === 'system' && window.matchMedia) {
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
                    try {
                        if (localStorage.getItem('site-theme')) return;
                    } catch (err) {}
                    applyHtmlTheme(window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                });
            }
        })();
    </script>
    @php
        try {
            $currentPage = $currentPage ?? 'home';
            
            // Si des métadonnées spécifiques sont passées (pour les annonces, articles, etc.), les utiliser directement
            if (isset($ogTitle) || isset($twitterTitle) || isset($pageKeywords) || isset($pageTitle)) {
            // Utiliser les métadonnées spécifiques pour les annonces et articles
            $finalTitle = trim($pageTitle ?? '');
            $finalDescription = trim($pageDescription ?? '');
            $finalKeywords = trim($pageKeywords ?? '');
            $finalOgTitle = trim($ogTitle ?? '') ?: $finalTitle;
            $finalOgDescription = trim($ogDescription ?? '') ?: $finalDescription;
            $finalTwitterTitle = trim($twitterTitle ?? '') ?: $finalOgTitle ?: $finalTitle;
            $finalTwitterDescription = trim($twitterDescription ?? '') ?: $finalOgDescription ?: $finalDescription;
            $finalImage = trim($pageImage ?? '');
            
            // Si des valeurs sont vides, utiliser SeoHelper comme fallback
            if (empty($finalTitle) || empty($finalDescription) || empty($finalImage)) {
                $seoData = \App\Helpers\SeoHelper::generateMetaTags($currentPage, [
                    'title' => $finalTitle ?: ($pageTitle ?? ''),
                    'description' => $finalDescription ?: ($pageDescription ?? ''),
                    'image' => $finalImage ?: ($pageImage ?? ''),
                    'type' => $pageType ?? 'website'
                ]);
                
                $finalTitle = $finalTitle ?: $seoData['title'];
                $finalDescription = $finalDescription ?: $seoData['description'];
                $finalOgTitle = $finalOgTitle ?: $seoData['og:title'];
                $finalOgDescription = $finalOgDescription ?: $seoData['og:description'];
                $finalTwitterTitle = $finalTwitterTitle ?: $seoData['twitter:title'];
                $finalTwitterDescription = $finalTwitterDescription ?: $seoData['twitter:description'];
                $finalImage = $finalImage ?: $seoData['og:image'];
            }
        } else {
            // Sinon, utiliser SeoHelper
            $seoData = \App\Helpers\SeoHelper::generateMetaTags($currentPage, [
                'title' => $pageTitle ?? '',
                'description' => $pageDescription ?? '',
                'image' => $pageImage ?? '',
                'type' => $pageType ?? 'website'
            ]);
            $finalTitle = $seoData['title'];
            $finalDescription = $seoData['description'];
            $finalKeywords = '';
            $finalOgTitle = $seoData['og:title'];
            $finalOgDescription = $seoData['og:description'];
            $finalTwitterTitle = $seoData['twitter:title'];
            $finalTwitterDescription = $seoData['twitter:description'];
            $finalImage = $seoData['og:image'];
        }
        
        // Vérifier les sections @section('title') et @section('description') si elles existent
        $sectionTitle = view()->yieldContent('title', '');
        $sectionDescription = view()->yieldContent('description', '');
        
        // Si des sections existent, les utiliser en priorité
        if (!empty($sectionTitle)) {
            $finalTitle = trim($sectionTitle);
        }
        if (!empty($sectionDescription)) {
            $finalDescription = trim($sectionDescription);
        }
        
        // Validation finale - GARANTIR qu'aucune valeur n'est vide
        $companyName = setting('company_name', 'Votre Entreprise');
        $companySpecialization = setting('company_specialization', 'Travaux de Rénovation');
        
        if (empty($finalTitle)) {
            $finalTitle = $companyName . ' - ' . $companySpecialization;
        }
        if (empty($finalDescription)) {
            $finalDescription = setting('company_description', 'Expert en ' . $companySpecialization . '. Devis gratuit, intervention rapide, qualité garantie.');
        }
        if (empty($finalOgTitle)) {
            $finalOgTitle = $finalTitle;
        }
        if (empty($finalOgDescription)) {
            $finalOgDescription = $finalDescription;
        }
        if (empty($finalTwitterTitle)) {
            $finalTwitterTitle = $finalOgTitle;
        }
        if (empty($finalTwitterDescription)) {
            $finalTwitterDescription = $finalOgDescription;
        }
        if (empty($finalImage)) {
            $companyLogo = setting('company_logo');
            if ($companyLogo) {
                // S'assurer que l'URL est complète (HTTPS)
                $finalImage = strpos($companyLogo, 'http') === 0 ? $companyLogo : url($companyLogo);
            } else {
                $finalImage = url('logo/logo.png');
            }
        }
        
        // S'assurer que l'image est en HTTPS et accessible
        if (!empty($finalImage) && strpos($finalImage, 'http://') === 0) {
            $finalImage = str_replace('http://', 'https://', $finalImage);
        }
        
        // NE PAS tronquer les titres et descriptions - les afficher en entier
        // Les titres et descriptions sont déjà optimisés par GPT pour être complets
        
        // Récupérer la configuration SEO pour les tags de tracking
        $seoConfigData = \App\Models\Setting::get('seo_config', '[]');
        $seoConfig = is_string($seoConfigData) ? json_decode($seoConfigData, true) : ($seoConfigData ?? []);
        } catch (\Exception $e) {
            // En cas d'erreur (ex: base de données inaccessible), utiliser des valeurs par défaut
            \Log::warning('Erreur lors du chargement des settings dans app.blade.php: ' . $e->getMessage());
            $finalTitle = $finalTitle ?? 'Votre Entreprise';
            $finalDescription = $finalDescription ?? 'Expert en travaux de rénovation';
            $finalOgTitle = $finalOgTitle ?? $finalTitle;
            $finalOgDescription = $finalOgDescription ?? $finalDescription;
            $finalTwitterTitle = $finalTwitterTitle ?? $finalOgTitle;
            $finalTwitterDescription = $finalTwitterDescription ?? $finalOgDescription;
            $finalImage = $finalImage ?? url('logo/logo.png');
            $seoConfig = [];
        }
    @endphp
    
    @php
        // Décoder les entités HTML pour éviter le double encodage
        $decodedTitle = html_entity_decode($finalTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Encoder une seule fois pour l'affichage
        $safeTitle = htmlspecialchars($decodedTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
    @endphp
    <title>{{ $safeTitle }}</title>
    <meta name="description" content="{{ e($finalDescription) }}">
    @php
        try {
            $keywordsValue = $finalKeywords ?? '';
            if (empty($keywordsValue)) {
                // Utiliser view()->yieldContent() au lieu de @yield() dans un bloc PHP
                $yieldKeywords = view()->yieldContent('keywords', '');
                $keywordsValue = !empty($yieldKeywords) ? $yieldKeywords : @setting('meta_keywords', 'travaux, rénovation, toiture, façade');
            }
            // S'assurer que les keywords ne sont jamais vides
            if (empty($keywordsValue)) {
                $companySpecialization = @setting('company_specialization', 'Travaux de Rénovation');
                $keywordsValue = strtolower($companySpecialization) . ', travaux, rénovation, devis gratuit';
            }
        } catch (\Exception $e) {
            $keywordsValue = 'travaux, rénovation, toiture, façade';
        }
    @endphp
    <meta name="keywords" content="{{ e($keywordsValue) }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Canonical URL (uniquement sur les pages indexables) -->
    @if($shouldEmitCanonical ?? true)
    <link rel="canonical" href="{{ $canonicalUrl ?? \App\Helpers\SeoHelper::getCanonicalUrl() }}">
    @endif
    
    <!-- Open Graph Meta Tags (améliorés pour Google) -->
    <meta property="og:title" content="{{ e($finalOgTitle) }}">
    <meta property="og:description" content="{{ e($finalOgDescription) }}">
    <meta property="og:image" content="{{ e($finalImage) }}">
    <meta property="og:image:secure_url" content="{{ e($finalImage) }}">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:alt" content="{{ e($finalOgTitle) }}">
    <meta property="og:url" content="{{ $canonicalUrl ?? \App\Helpers\SeoHelper::getCanonicalUrl() }}">
    <meta property="og:type" content="{{ $pageType ?? 'website' }}">
    <meta property="og:site_name" content="{{ e(@setting('company_name', 'Votre Entreprise')) }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="fr_FR">
    
    <!-- Meta robots (éviter désindexation : garder index, follow sauf si $robotsMeta défini) -->
    <meta name="robots" content="{{ $robotsMeta ?? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' }}">
    <meta name="googlebot" content="{{ $robotsMeta ?? 'index, follow' }}">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ e($finalTwitterTitle) }}">
    <meta name="twitter:description" content="{{ e($finalTwitterDescription) }}">
    <meta name="twitter:image" content="{{ e($finalImage) }}">
    @if(@setting('twitter_site'))
    <meta name="twitter:site" content="{{ e(@setting('twitter_site')) }}">
    @endif
    
    <!-- Favicon - Optimisé pour Google Search Results -->
    @php
        $faviconUrl = null;
        $faviconPathForVersion = null;
        
        // Récupérer la config SEO pour vérifier aussi le favicon
        $seoConfigData = \App\Models\Setting::get('seo_config', '[]');
        $seoConfig = is_string($seoConfigData) ? json_decode($seoConfigData, true) : ($seoConfigData ?? []);
        
        // Priorité 1: Favicon 192x192 (optimal pour Google - recommandé)
        $favicon192 = $seoConfig['favicon_192x192'] ?? 'favicons/favicon-192x192.png';
        if (file_exists(public_path($favicon192))) {
            $faviconUrl = asset($favicon192);
            $faviconPathForVersion = $favicon192;
        }
        
        // Priorité 2: Favicon 96x96 (recommandé par Google)
        if (!$faviconUrl) {
            $favicon96 = $seoConfig['favicon_96x96'] ?? 'favicons/favicon-96x96.png';
            if (file_exists(public_path($favicon96))) {
                $faviconUrl = asset($favicon96);
                $faviconPathForVersion = $favicon96;
            }
        }
        
        // Priorité 3: site_favicon (ConfigController)
        if (!$faviconUrl) {
        $faviconPath = setting('site_favicon');
        if ($faviconPath) {
            // Si le chemin commence par uploads/, c'est un chemin relatif depuis public
            if (strpos($faviconPath, 'uploads/') === 0 || strpos($faviconPath, '/') === 0) {
                $fullPath = public_path($faviconPath);
            } else {
                // Sinon, c'est directement dans public/
                $fullPath = public_path($faviconPath);
            }
            
            if (file_exists($fullPath)) {
            $faviconUrl = asset($faviconPath);
                $faviconPathForVersion = $faviconPath;
                }
            }
        }
        
        // Priorité 4: seo_config favicon (SeoController)
        if (!$faviconUrl && !empty($seoConfig['favicon'])) {
            $seoFaviconPath = $seoConfig['favicon'];
            $fullPath = public_path($seoFaviconPath);
            
            if (file_exists($fullPath)) {
                $faviconUrl = asset($seoFaviconPath);
                $faviconPathForVersion = $seoFaviconPath;
            }
        }
        
            // Fallback: chercher un favicon dans le dossier public
        if (!$faviconUrl) {
            $faviconFiles = glob(public_path('favicon*'));
            if (!empty($faviconFiles)) {
                $faviconUrl = asset(basename($faviconFiles[0]));
                $faviconPathForVersion = basename($faviconFiles[0]);
            }
        }
        
        // Déterminer le type MIME et générer un cache-busting basé sur la date de modification
        $faviconType = 'image/x-icon';
        $faviconVersion = '';
        if ($faviconUrl && $faviconPathForVersion) {
            $extension = strtolower(pathinfo(parse_url($faviconUrl, PHP_URL_PATH), PATHINFO_EXTENSION));
            if ($extension === 'png') {
                $faviconType = 'image/png';
            } elseif ($extension === 'jpg' || $extension === 'jpeg') {
                $faviconType = 'image/jpeg';
            } elseif ($extension === 'svg') {
                $faviconType = 'image/svg+xml';
            }
            
            // Générer un version basé sur la date de modification du fichier pour le cache-busting
            $fullPathForVersion = public_path($faviconPathForVersion);
            if (file_exists($fullPathForVersion)) {
                $faviconVersion = '?v=' . filemtime($fullPathForVersion);
            }
        }
    @endphp
    
    @php
        // Vérifier les favicons générés
        $seoConfigData = \App\Models\Setting::get('seo_config', '[]');
        $seoConfig = is_string($seoConfigData) ? json_decode($seoConfigData, true) : ($seoConfigData ?? []);
        
        // SVG favicon
        $svgFavicon = $seoConfig['favicon_svg'] ?? null;
        if ($svgFavicon && file_exists(public_path($svgFavicon))) {
            $svgFaviconUrl = asset($svgFavicon);
        }
        
        // Favicons générés
        $favicon16 = $seoConfig['favicon_16x16'] ?? 'favicons/favicon-16x16.png';
        $favicon32 = $seoConfig['favicon_32x32'] ?? 'favicons/favicon-32x32.png';
        $favicon48 = $seoConfig['favicon_48x48'] ?? 'favicons/favicon-48x48.png';
        $favicon96 = $seoConfig['favicon_96x96'] ?? 'favicons/favicon-96x96.png';
        $favicon192 = $seoConfig['favicon_192x192'] ?? 'favicons/favicon-192x192.png'; // Optimal pour Google
        
        // Apple Touch Icon
        $appleIcon = $seoConfig['apple_touch_icon'] ?? 'favicons/apple-touch-icon.png';
    @endphp
    
    @if($svgFaviconUrl ?? false)
    <!-- SVG Favicon (pour navigateurs modernes) -->
    <link rel="icon" type="image/svg+xml" href="{{ $svgFaviconUrl }}">
    @endif
    
    @if($faviconUrl)
    <!-- Favicon standard (obligatoire pour Google - doit être accessible en HTTPS) -->
    <link rel="icon" type="{{ $faviconType }}" href="{{ $faviconUrl }}{{ $faviconVersion }}">
    @endif
    
    <!-- Favicons générés avec tailles spécifiques (requis par Google) -->
    @if(file_exists(public_path($favicon16)))
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset($favicon16) }}">
    @endif
    @if(file_exists(public_path($favicon32)))
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset($favicon32) }}">
    @endif
    @if(file_exists(public_path($favicon48)))
    <link rel="icon" type="image/png" sizes="48x48" href="{{ asset($favicon48) }}">
    @endif
    @if(file_exists(public_path($favicon96)))
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset($favicon96) }}">
    @endif
    @if(file_exists(public_path($favicon192)))
    <!-- Favicon 192x192 (optimal pour Google Search Results) -->
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset($favicon192) }}">
    @endif
    
    <!-- Apple Touch Icon (pour iOS - 180x180px) -->
    @if(file_exists(public_path($appleIcon)))
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset($appleIcon) }}">
    @elseif($faviconUrl)
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $faviconUrl }}{{ $faviconVersion }}">
    @endif
    
    <!-- Favicon ICO (fallback pour anciens navigateurs) - UN SEUL -->
    @if(file_exists(public_path('favicon.ico')))
    <link rel="icon" type="image/x-icon" href="{{ url('favicon.ico') }}">
    @endif
    
    <!-- Apple Touch Icon (fallback si configuré séparément) -->
    @if(setting('apple_touch_icon'))
    <link rel="apple-touch-icon" href="{{ asset(setting('apple_touch_icon')) }}">
    @endif
    
    <!-- Manifest pour PWA (aide Google à trouver les icônes) -->
    <link rel="manifest" href="{{ url('/manifest.json') }}">
    
    <!-- Meta pour Web App -->
    <meta name="application-name" content="{{ @setting('company_name', 'Votre Entreprise') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ Str::limit(@setting('company_name', 'Votre Entreprise'), 12) }}">
    
    @yield('head')
    
    {{-- Schema.org Structured Data (inclus dans toutes les pages) --}}
    @include('partials.schema-org')
    
    <!-- Articles CSS (critique, chargé en premier) -->
    <link rel="stylesheet" href="{{ asset('css/articles.css') }}">
    
    <!-- Font Awesome (non-bloquant) -->
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"></noscript>

    <!-- Tailwind CSS (différé — non bloquant) -->
    <script>
        // Config disponible avant chargement Tailwind
        window.tailwindConfig = { darkMode: 'class' };
    </script>
    <script defer src="https://cdn.tailwindcss.com" onload="try{tailwind.config=window.tailwindConfig;}catch(e){}"></script>
    
    <style>
        :root,
        html[data-theme="light"] {
            --primary-color: {{ @setting('primary_color', '#3b82f6') }};
            --secondary-color: {{ @setting('secondary_color', '#1e40af') }};
            --accent-color: {{ @setting('accent_color', '#f59e0b') }};
            --page-bg: #f9fafb;
            --page-text: #111827;
            --header-bg: #ffffff;
            --header-text: #374151;
            --header-muted: #4b5563;
            --header-border: #e5e7eb;
            --dropdown-bg: #ffffff;
            --footer-bg: #111827;
            --footer-text: #ffffff;
            --footer-muted: #d1d5db;
        }

        html[data-theme="dark"] {
            --primary-color: {{ @setting('dark_primary_color', '#60a5fa') }};
            --secondary-color: {{ @setting('dark_secondary_color', '#34d399') }};
            --accent-color: {{ @setting('dark_accent_color', '#fbbf24') }};
            --page-bg: #0f172a;
            --page-text: #f1f5f9;
            --header-bg: #1e293b;
            --header-text: #e2e8f0;
            --header-muted: #94a3b8;
            --header-border: #334155;
            --dropdown-bg: #1e293b;
            --footer-bg: #020617;
            --footer-text: #f8fafc;
            --footer-muted: #cbd5e1;
        }

        html {
            color-scheme: light dark;
        }

        .site-body {
            background-color: var(--page-bg);
            color: var(--page-text);
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .site-nav-link {
            color: var(--header-text);
            transition: color 0.15s ease;
        }
        .site-nav-link:hover {
            color: var(--primary-color);
        }
        .site-nav-muted {
            color: var(--header-muted);
        }
        .site-nav-muted:hover {
            color: var(--primary-color);
        }
        .site-dropdown-link {
            color: var(--header-text);
        }
        .site-dropdown-link:hover {
            background-color: rgba(148, 163, 184, 0.15);
        }
        html[data-theme="light"] .site-dropdown-link:hover {
            background-color: #f3f4f6;
        }

        /* Harmonise les blocs courants en mode sombre (contenu principal) */
        html[data-theme="dark"] main .bg-gray-50 {
            background-color: var(--page-bg) !important;
        }
        html[data-theme="dark"] main .bg-white:not(.no-theme) {
            background-color: #1e293b !important;
        }
        html[data-theme="dark"] main .text-gray-900 {
            color: var(--page-text) !important;
        }
        html[data-theme="dark"] main .text-gray-800 {
            color: #e2e8f0 !important;
        }
        html[data-theme="dark"] main .text-gray-700 {
            color: #cbd5e1 !important;
        }
        html[data-theme="dark"] main .text-gray-600 {
            color: #94a3b8 !important;
        }
        html[data-theme="dark"] main .border-gray-200 {
            border-color: #334155 !important;
        }
        html[data-theme="dark"] main .bg-gray-100 {
            background-color: #1e293b !important;
        }
        html[data-theme="dark"] main .text-gray-500 {
            color: #94a3b8 !important;
        }
        /* Typographie (annonces / contenus HTML) */
        html[data-theme="dark"] main .prose {
            color: #cbd5e1 !important;
        }
        html[data-theme="dark"] main .prose :where(h1, h2, h3, h4, h5, h6) {
            color: #f8fafc !important;
        }
        html[data-theme="dark"] main .prose :where(p, li, td, th, strong) {
            color: #cbd5e1 !important;
        }
        html[data-theme="dark"] main .prose a {
            color: var(--primary-color) !important;
        }
        html[data-theme="dark"] main .prose blockquote {
            color: #94a3b8 !important;
            border-color: #475569 !important;
        }
        /* Cartes contenu pleine largeur (HTML injecté sans .prose) */
        html[data-theme="dark"] main .bg-white.rounded-2xl:not(.no-theme) {
            background-color: #1e293b !important;
            color: #e2e8f0 !important;
        }
        html[data-theme="dark"] main .bg-white.rounded-2xl:not(.no-theme) :where(p, li, span, td, th, div):not([class*="text-white"]):not([class*="bg-"]) {
            color: inherit;
        }
        html[data-theme="dark"] main .min-h-screen.bg-gray-50 {
            background-color: var(--page-bg) !important;
        }
        html[data-theme="dark"] main .text-blue-600,
        html[data-theme="dark"] main a.text-blue-600 {
            color: var(--primary-color) !important;
        }
        html[data-theme="dark"] main .hover\:text-blue-600:hover,
        html[data-theme="dark"] main a:hover.text-blue-800 {
            color: #93c5fd !important;
        }

        /*
         * Services / contenus IA : blocs bg-*-50 (bleu, vert, jaune, gris).
         * Sans ça, en mode sombre le texte reste clair mais les fonds restent pastel → illisible.
         */
        html[data-theme="dark"] main .prose .bg-blue-50,
        html[data-theme="dark"] main .prose .bg-green-50,
        html[data-theme="dark"] main .prose .bg-yellow-50,
        html[data-theme="dark"] main .prose .bg-red-50,
        html[data-theme="dark"] main .prose .bg-gray-50,
        html[data-theme="dark"] main .service-page-content .bg-blue-50,
        html[data-theme="dark"] main .service-page-content .bg-green-50,
        html[data-theme="dark"] main .service-page-content .bg-yellow-50,
        html[data-theme="dark"] main .service-page-content .bg-red-50,
        html[data-theme="dark"] main .service-page-content .bg-gray-50 {
            background-color: #1e293b !important;
            color: #e2e8f0 !important;
        }
        html[data-theme="dark"] main .prose .bg-gradient-to-r.from-blue-50.to-green-50,
        html[data-theme="dark"] main .service-page-content .bg-gradient-to-r.from-blue-50.to-green-50 {
            background-image: linear-gradient(to right, #1e293b, #1e293b) !important;
            color: #e2e8f0 !important;
        }
        html[data-theme="dark"] main .service-page-content .text-green-600,
        html[data-theme="dark"] main .prose .text-green-600 {
            color: #4ade80 !important;
        }
        html[data-theme="dark"] main .service-page-content p.leading-relaxed,
        html[data-theme="dark"] main .service-page-content .leading-relaxed {
            color: #cbd5e1 !important;
        }
        /*
         * Les annonces générées doivent rester lisibles même si le thème est sombre
         * ou si Safari conserve un text fill clair injecté par le HTML IA.
         */
        html[data-theme="dark"] main .sp-prose,
        html[data-theme="dark"] main .sp-prose :where(p, li, span, strong, em, small, blockquote, td, th, div, section, article, h2, h3, h4, h5, h6),
        html[data-theme="light"] main .sp-prose,
        html[data-theme="light"] main .sp-prose :where(p, li, span, strong, em, small, blockquote, td, th, div, section, article, h2, h3, h4, h5, h6) {
            color: #111827 !important;
            -webkit-text-fill-color: #111827 !important;
            opacity: 1 !important;
            text-shadow: none !important;
            filter: none !important;
        }
        html[data-theme="dark"] main .sp-prose :where(.bg-white, .bg-gray-50, .bg-slate-50, .bg-blue-600, .bg-blue-700, .bg-green-500, .bg-green-600, .bg-gray-600, .bg-gray-700, [class*="from-blue-50"], [class*="to-green-50"]),
        html[data-theme="dark"] main .sp-prose :where(.bg-white, .bg-gray-50, .bg-slate-50, .bg-blue-600, .bg-blue-700, .bg-green-500, .bg-green-600, .bg-gray-600, .bg-gray-700, [class*="from-blue-50"], [class*="to-green-50"]) :where(p, li, span, strong, em, small, summary, h2, h3, h4, h5, h6, div) {
            color: #f8fafc !important;
            -webkit-text-fill-color: #f8fafc !important;
        }
        html[data-theme="dark"] main .sp-prose :where(.bg-white, .bg-gray-50, .bg-slate-50, [class*="from-blue-50"], [class*="to-green-50"]) {
            background-color: #1e293b !important;
            border-color: #334155 !important;
        }
        html[data-theme="dark"] main .sp-prose :where(.bg-blue-600, .bg-blue-700, .bg-green-500, .bg-green-600, .bg-gray-600, .bg-gray-700),
        html[data-theme="dark"] main .sp-prose :where(.bg-blue-600, .bg-blue-700, .bg-green-500, .bg-green-600, .bg-gray-600, .bg-gray-700) * {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }
        main .sp-prose :where(.bg-blue-600, .bg-blue-700, .bg-green-500, .bg-green-600, .bg-gray-600, .bg-gray-700),
        main .sp-prose :where(.bg-blue-600, .bg-blue-700, .bg-green-500, .bg-green-600, .bg-gray-600, .bg-gray-700) * {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }

        .btn-primary:hover {
            filter: brightness(1.1);
        }

        .floating-phone {
            background-color: var(--secondary-color) !important;
            will-change: transform;
        }

        @media (min-width: 768px) {
            .floating-phone {
                animation: pulse-phone 2s infinite;
            }
        }

        @keyframes pulse-phone {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.05);
                opacity: 0.9;
            }
        }

        /* Bandeau appel mobile : léger relief */
        .mobile-call-bar {
            box-shadow: 0 -4px 24px rgba(0, 0, 0, 0.18);
        }

        .site-theme-toggle {
            background: var(--header-bg);
            color: var(--header-text);
            border: 1px solid var(--header-border);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
        }
        .site-theme-toggle:hover {
            filter: brightness(1.05);
        }

        .site-footer {
            background-color: var(--footer-bg);
            color: var(--footer-text);
        }
        .site-footer-link {
            color: var(--footer-muted);
            transition: color 0.15s ease;
        }
        .site-footer-link:hover {
            color: var(--footer-text);
        }
        .site-footer-border {
            border-color: rgba(255, 255, 255, 0.12);
        }

        /* Conteneur unique (aligné header / footer / page d’accueil) : max-w-7xl + px-4 sm:px-6 lg:px-8 */
        .site-shell {
            width: 100%;
            max-width: 80rem;
            margin-left: auto;
            margin-right: auto;
            padding-left: 1rem;
            padding-right: 1rem;
            box-sizing: border-box;
        }
        @media (min-width: 640px) {
            .site-shell {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
        }
        @media (min-width: 1024px) {
            .site-shell {
                padding-left: 2rem;
                padding-right: 2rem;
            }
        }

        /* Simulateur multi-étapes : texte saisi lisible (noir sur blanc), même si le thème sombre colore les inputs */
        .simulator-step input[type="text"],
        .simulator-step input[type="email"],
        .simulator-step input[type="tel"],
        .simulator-step input[type="number"],
        .simulator-step input[type="search"],
        .simulator-step input[type="url"],
        .simulator-step textarea,
        .simulator-step select {
            color: #111827 !important;
            background-color: #ffffff !important;
            -webkit-text-fill-color: #111827;
        }
        /* Cartes de choix simulateur : fond sombre + texte blanc ; sélectionné = fond blanc + texte couleur primaire */
        .simulator-step .work-option {
            background-color: #1e293b !important;
            border: 2px solid #475569 !important;
            color: #fff !important;
        }
        .simulator-step .work-option p,
        .simulator-step .work-option h3 {
            color: #fff !important;
            -webkit-text-fill-color: #fff;
        }
        .simulator-step .work-option:hover:not(.is-selected) {
            border-color: #64748b !important;
        }
        .simulator-step .work-option.is-selected {
            background-color: #ffffff !important;
            border-color: var(--primary-color) !important;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.12);
        }
        .simulator-step .work-option.is-selected p,
        .simulator-step .work-option.is-selected h3 {
            color: var(--primary-color) !important;
            -webkit-text-fill-color: var(--primary-color);
        }
        .simulator-step .gender-option,
        .simulator-step .property-option {
            color: #111827 !important;
            -webkit-text-fill-color: #111827;
        }
        /*
         * Thème clair : forcer texte foncé sur le simulateur (évite dark:text-white sur fond blanc
         * si désynchronisation data-theme / classe .dark ou préférences navigateur).
         */
        html[data-theme="light"] main .simulator-step {
            color: #111827 !important;
            -webkit-text-fill-color: #111827;
        }
        html[data-theme="light"] main .simulator-step h1,
        html[data-theme="light"] main .simulator-step h2:not(.simulator-step-section-title),
        html[data-theme="light"] main .simulator-step h3 {
            color: #0f172a !important;
            -webkit-text-fill-color: #0f172a;
        }
        html[data-theme="light"] main .simulator-step p.text-slate-600,
        html[data-theme="light"] main .simulator-step .text-slate-600 {
            color: #475569 !important;
            -webkit-text-fill-color: #475569;
        }
        html[data-theme="light"] main .simulator-step label,
        html[data-theme="light"] main .simulator-step .text-slate-900,
        html[data-theme="light"] main .simulator-step .text-slate-800,
        html[data-theme="light"] main .simulator-step .text-slate-700,
        html[data-theme="light"] main .simulator-step .text-slate-100 {
            color: #0f172a !important;
            -webkit-text-fill-color: #0f172a;
        }
        html[data-theme="light"] main .simulator-step .text-slate-400 {
            color: #64748b !important;
            -webkit-text-fill-color: #64748b;
        }
        html[data-theme="light"] main .simulator-step .bg-white.rounded-2xl.no-theme,
        html[data-theme="light"] main .simulator-step .bg-white.dark\:bg-slate-800 {
            color: #111827 !important;
        }
        /* Annule dark:text-white sur fond clair */
        html[data-theme="light"] main .simulator-step .dark\:text-white {
            color: #0f172a !important;
            -webkit-text-fill-color: #0f172a;
        }
        /*
         * Ré-appliquer en dernier : bandeaux simulateur (intro + étapes) = blanc pur,
         * priorité sur la carte .no-theme et sur .dark:text-white ci-dessus.
         */
        html[data-theme="light"] main .simulator-step .simulator-step-section-head,
        html[data-theme="light"] main .simulator-step .simulator-step-section-head *,
        html[data-theme="dark"] main .simulator-step .simulator-step-section-head,
        html[data-theme="dark"] main .simulator-step .simulator-step-section-head *,
        html[data-theme="light"] main .simulator-step .simulator-step-intro,
        html[data-theme="light"] main .simulator-step .simulator-step-intro *,
        html[data-theme="dark"] main .simulator-step .simulator-step-intro,
        html[data-theme="dark"] main .simulator-step .simulator-step-intro * {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }
    </style>
    
    @stack('head')
    
    <!-- Google Analytics -->
    @if(!empty($seoConfig['google_analytics']))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $seoConfig['google_analytics'] }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $seoConfig['google_analytics'] }}');
    </script>
    @endif
    
    <!-- Google Tag Manager -->
    @if(@setting('google_tag_manager_id'))
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','{{ @setting('google_tag_manager_id') }}');</script>
    @endif
    
    <!-- Facebook Pixel -->
    @if(!empty($seoConfig['facebook_pixel']))
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ $seoConfig['facebook_pixel'] }}');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id={{ $seoConfig['facebook_pixel'] }}&ev=PageView&noscript=1"
    /></noscript>
    @endif
    
    <!-- Google Search Console -->
    @if(!empty($seoConfig['google_search_console']))
    {!! $seoConfig['google_search_console'] !!}
    @endif
    
    <!-- Bing Webmaster Tools -->
    @if(!empty($seoConfig['bing_webmaster']))
    {!! $seoConfig['bing_webmaster'] !!}
    @endif
    
    <!-- Google Ads Conversion Tracking -->
    @if(!empty($seoConfig['google_ads']))
    <script>
        gtag('event', 'conversion', {
            'send_to': '{{ $seoConfig['google_ads'] }}'
        });
    </script>
    @endif
</head>
<body class="site-body min-h-screen antialiased @if(@setting('company_phone_raw')) pb-24 md:pb-0 @endif">
    @include('partials.header')
    
    <main>
        @yield('content')
    </main>
    
    @include('partials.footer')

    @php
        $showThemeToggle = filter_var(setting('theme_show_toggle', true), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($showThemeToggle === null) {
            $showThemeToggle = (bool) setting('theme_show_toggle', true);
        }
    @endphp
    @if($showThemeToggle)
    <button type="button"
            id="siteThemeToggle"
            class="site-theme-toggle fixed z-50 w-12 h-12 rounded-full flex items-center justify-center transition bottom-[5.25rem] left-4 md:bottom-6 md:left-6"
            aria-label="Basculer le thème clair ou sombre"
            title="Thème clair / sombre">
        <span class="theme-icon-light" aria-hidden="true"><i class="fas fa-sun text-lg"></i></span>
        <span class="theme-icon-dark hidden" aria-hidden="true"><i class="fas fa-moon text-lg"></i></span>
    </button>
    <script>
        (function () {
            function syncThemeIcons() {
                var dark = document.documentElement.getAttribute('data-theme') === 'dark';
                var sunEl = document.querySelector('.theme-icon-light');
                var moonEl = document.querySelector('.theme-icon-dark');
                if (sunEl && moonEl) {
                    sunEl.classList.toggle('hidden', !dark);
                    moonEl.classList.toggle('hidden', dark);
                }
            }
            var btn = document.getElementById('siteThemeToggle');
            if (btn) {
                btn.addEventListener('click', function () {
                    var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                    document.documentElement.setAttribute('data-theme', next);
                    document.documentElement.classList.toggle('dark', next === 'dark');
                    try { localStorage.setItem('site-theme', next); } catch (e) {}
                    syncThemeIcons();
                });
                syncThemeIcons();
            }
        })();
    </script>
    @endif
    
    <!-- Floating Call Button -->
    @if(@setting('company_phone_raw'))
    @php
        try {
            // Formater le numéro pour tel: (supprimer les espaces, garder les chiffres)
            $phoneRaw = preg_replace('/[^0-9+]/', '', @setting('company_phone_raw', ''));
            $phoneForTracking = @setting('company_phone_raw', '');
            $companyPhone = @setting('company_phone', @setting('company_phone_raw', ''));
            // Si le numéro commence par 0, le remplacer par +33 pour les appels internationaux
            if (strpos($phoneRaw, '0') === 0 && strlen($phoneRaw) == 10) {
                $phoneRaw = '+33' . substr($phoneRaw, 1);
            }
            $currentPageForTracking = $currentPage ?? 'home';
        } catch (\Exception $e) {
            $phoneRaw = '';
            $phoneForTracking = '';
            $companyPhone = '';
        }
    @endphp
    @if(!empty($phoneRaw))
    <a href="tel:{{ $phoneRaw }}" 
       id="floatingCallBtn"
       class="floating-phone fixed z-50 text-white shadow-2xl transition
              left-0 right-0 bottom-0 rounded-t-2xl mobile-call-bar flex items-center justify-center gap-2 sm:gap-3 px-4 py-3.5 min-h-[3.5rem] font-semibold text-base
              pb-[max(0.875rem,env(safe-area-inset-bottom))]
              md:left-auto md:right-6 md:bottom-6 md:w-16 md:h-16 md:min-h-0 md:rounded-full md:gap-0 md:px-0 md:py-0 md:pb-0 md:font-normal"
       style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);"
       aria-label="Appelez maintenant — {{ $companyPhone ?: $phoneForTracking }}"
       title="Appeler {{ $companyPhone ?: $phoneForTracking }}">
        <i class="fas fa-phone text-xl md:text-2xl shrink-0" aria-hidden="true"></i>
        <span class="md:hidden leading-tight whitespace-nowrap">Appelez maintenant</span>
        <span class="md:hidden text-sm font-medium opacity-95 truncate min-w-0 flex-1 text-right">{{ $companyPhone ?: $phoneForTracking }}</span>
    </a>
    @endif
    @endif
    
    <script>
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}',
            defaultPhone: '{{ @setting("company_phone_raw", "") }}'
            };
    </script>
    <script src="{{ asset('js/phone-tracking.js') }}?v={{ time() }}"></script>
    
    @yield('scripts')
</body>
</html>
