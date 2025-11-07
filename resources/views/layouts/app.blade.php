<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $currentPage = $currentPage ?? 'home';
        
        // Si des métadonnées spécifiques sont passées (pour les annonces, articles, etc.), les utiliser directement
        if (isset($ogTitle) || isset($twitterTitle) || isset($pageKeywords) || isset($pageTitle)) {
            // Utiliser les métadonnées spécifiques pour les annonces et articles
            $finalTitle = $pageTitle ?? '';
            $finalDescription = $pageDescription ?? '';
            $finalKeywords = $pageKeywords ?? '';
            $finalOgTitle = $ogTitle ?? $finalTitle;
            $finalOgDescription = $ogDescription ?? $finalDescription;
            $finalTwitterTitle = $twitterTitle ?? $finalOgTitle ?? $finalTitle;
            $finalTwitterDescription = $twitterDescription ?? $finalOgDescription ?? $finalDescription;
            $finalImage = $pageImage ?? '';
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
        
        // Récupérer la configuration SEO pour les tags de tracking
        $seoConfigData = \App\Models\Setting::get('seo_config', '[]');
        $seoConfig = is_string($seoConfigData) ? json_decode($seoConfigData, true) : ($seoConfigData ?? []);
    @endphp
    
    <title>{{ $finalTitle }}</title>
    <meta name="description" content="{{ $finalDescription }}">
    @php
        $keywordsValue = $finalKeywords ?? '';
        if (empty($keywordsValue)) {
            // Utiliser view()->yieldContent() au lieu de @yield() dans un bloc PHP
            $yieldKeywords = view()->yieldContent('keywords', '');
            $keywordsValue = !empty($yieldKeywords) ? $yieldKeywords : setting('meta_keywords', 'travaux, rénovation, toiture, façade');
        }
    @endphp
    <meta name="keywords" content="{{ $keywordsValue }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="{{ $finalOgTitle }}">
    <meta property="og:description" content="{{ $finalOgDescription }}">
    <meta property="og:image" content="{{ $finalImage }}">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta property="og:type" content="{{ $pageType ?? 'website' }}">
    <meta property="og:site_name" content="{{ setting('company_name', 'Sauser Couverture') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $finalTwitterTitle }}">
    <meta name="twitter:description" content="{{ $finalTwitterDescription }}">
    <meta name="twitter:image" content="{{ $finalImage }}">
    
    <!-- Favicon -->
    @php
        $faviconUrl = null;
        $faviconPathForVersion = null;
        
        // Récupérer la config SEO pour vérifier aussi le favicon
        $seoConfigData = \App\Models\Setting::get('seo_config', '[]');
        $seoConfig = is_string($seoConfigData) ? json_decode($seoConfigData, true) : ($seoConfigData ?? []);
        
        // Vérifier d'abord dans site_favicon (ConfigController)
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
        
        // Si pas trouvé, vérifier dans seo_config (SeoController)
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
    
    @if($faviconUrl)
    <link rel="icon" type="{{ $faviconType }}" href="{{ $faviconUrl }}{{ $faviconVersion }}">
    <link rel="shortcut icon" type="{{ $faviconType }}" href="{{ $faviconUrl }}{{ $faviconVersion }}">
    @endif
    
    <!-- Apple Touch Icon -->
    @if(setting('apple_touch_icon'))
    <link rel="apple-touch-icon" href="{{ asset(setting('apple_touch_icon')) }}">
    @endif
    
    @yield('head')
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Articles CSS -->
    <link rel="stylesheet" href="{{ asset('css/articles.css') }}">
    
    <style>
        :root {
            --primary-color: {{ setting('primary_color', '#3b82f6') }};
            --secondary-color: {{ setting('secondary_color', '#1e40af') }};
            --accent-color: {{ setting('accent_color', '#f59e0b') }};
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
        }
        
        @keyframes pulse-phone {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 var(--secondary-color); }
            50% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
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
    @if(setting('google_tag_manager_id'))
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','{{ setting('google_tag_manager_id') }}');</script>
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
    @if(setting('company_phone_raw'))
    <a href="tel:{{ setting('company_phone_raw') }}" 
       id="floatingCallBtn"
       class="floating-phone fixed bottom-6 right-6 text-white w-16 h-16 rounded-full flex items-center justify-center shadow-2xl transition z-50"
       style="background-color: var(--primary-color);"
       onclick="trackPhoneCall()">
        <i class="fas fa-phone text-2xl"></i>
    </a>
    
    <!-- Call Info Tooltip -->
    <div class="fixed bottom-24 right-6 bg-white rounded-lg shadow-xl p-4 z-40 hidden" id="callTooltip">
        <div class="text-center">
            <p class="text-sm font-semibold text-gray-800">Appelez-nous !</p>
            <p class="text-xs text-gray-600">{{ setting('company_phone') }}</p>
        </div>
    </div>
    @endif
    
    <script>
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}'
        };
        
        function trackPhoneCall(source = null, type = null) {
            // Éviter les appels multiples
            if (window.trackingInProgress) return;
            window.trackingInProgress = true;
            
            const payload = {
                source_page: window.location.pathname,
                phone_number: '{{ setting("company_phone_raw") }}'
            };
            
            // Ajouter les paramètres si fournis
            if (source) payload.source = source;
            if (type) payload.type = type;
            
            fetch('{{ route("track.phone") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.Laravel.csrfToken
                },
                body: JSON.stringify(payload)
            }).catch(err => console.log('Tracking error:', err))
            .finally(() => {
                window.trackingInProgress = false;
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Track all phone links
            document.querySelectorAll('a[href^="tel:"]').forEach(link => {
                link.addEventListener('click', function(e) {
                    trackPhoneCall();
                });
            });
            
            // Floating call button tooltip
            const floatingBtn = document.getElementById('floatingCallBtn');
            const tooltip = document.getElementById('callTooltip');
            
            if (floatingBtn && tooltip) {
                floatingBtn.addEventListener('mouseenter', function() {
                    tooltip.classList.remove('hidden');
                });
                
                floatingBtn.addEventListener('mouseleave', function() {
                    tooltip.classList.add('hidden');
                });
            }
        });
    </script>
    
    @yield('scripts')
</body>
</html>