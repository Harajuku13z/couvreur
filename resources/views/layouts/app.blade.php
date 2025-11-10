<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ request()->url() }}">
    
    <!-- Open Graph Meta Tags (améliorés pour Google) -->
    <meta property="og:title" content="{{ e($finalOgTitle) }}">
    <meta property="og:description" content="{{ e($finalOgDescription) }}">
    <meta property="og:image" content="{{ e($finalImage) }}">
    <meta property="og:image:secure_url" content="{{ e($finalImage) }}">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:alt" content="{{ e($finalOgTitle) }}">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta property="og:type" content="{{ $pageType ?? 'website' }}">
    <meta property="og:site_name" content="{{ e(@setting('company_name', 'Votre Entreprise')) }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="fr_FR">
    
    <!-- Meta tags supplémentaires pour améliorer l'affichage dans Google -->
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="googlebot" content="index, follow">
    
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
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        :root {
            --primary-color: {{ @setting('primary_color', '#3b82f6') }};
            --secondary-color: {{ @setting('secondary_color', '#1e40af') }};
            --accent-color: {{ @setting('accent_color', '#f59e0b') }};
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }
        
        .btn-primary:hover {
            filter: brightness(1.1);
        }
        
        .floating-phone {
            animation: pulse-phone 2s infinite;
            background-color: var(--secondary-color) !important;
            will-change: transform;
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
<body class="bg-gray-50">
    @include('partials.header')
    
    <main>
        @yield('content')
    </main>
    
    @include('partials.footer')
    
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
       class="floating-phone fixed bottom-6 right-6 text-white w-16 h-16 rounded-full flex items-center justify-center shadow-2xl transition z-50"
       style="background-color: var(--primary-color);"
       aria-label="Appeler {{ $companyPhone ?: $phoneForTracking }}"
       title="Appeler {{ $companyPhone ?: $phoneForTracking }}">
        <i class="fas fa-phone text-2xl" aria-hidden="true"></i>
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