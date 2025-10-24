<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', setting('meta_title', setting('company_name', 'Sausser Couverture')))</title>
    <meta name="description" content="@yield('description', setting('meta_description', 'Expert en travaux de rénovation'))">
    <meta name="keywords" content="@yield('keywords', setting('meta_keywords', 'travaux, rénovation, toiture, façade'))">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="@yield('og_title', setting('meta_title', setting('company_name')))">
    <meta property="og:description" content="@yield('og_description', setting('meta_description', 'Expert en travaux de rénovation'))">
    <meta property="og:image" content="@yield('og_image', setting('og_image', setting('company_logo', asset('logo/logo.png'))))">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ setting('company_name', 'Sausser Couverture') }}">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('twitter_title', setting('meta_title', setting('company_name')))">
    <meta name="twitter:description" content="@yield('twitter_description', setting('meta_description', 'Expert en travaux de rénovation'))">
    <meta name="twitter:image" content="@yield('twitter_image', setting('og_image', setting('company_logo', asset('logo/logo.png'))))">
    
    <!-- Favicon -->
    @if(setting('site_favicon'))
    <link rel="icon" type="image/x-icon" href="{{ asset(setting('site_favicon')) }}">
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
    @if(setting('google_analytics_id'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ setting('google_analytics_id') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ setting('google_analytics_id') }}');
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
    @if(setting('facebook_pixel_id'))
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ setting('facebook_pixel_id') }}');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id={{ setting('facebook_pixel_id') }}&ev=PageView&noscript=1"
    /></noscript>
    @endif
    
    <!-- Google Search Console -->
    @if(setting('google_search_console'))
    {!! setting('google_search_console') !!}
    @endif
    
    <!-- Bing Webmaster Tools -->
    @if(setting('bing_webmaster_tools'))
    {!! setting('bing_webmaster_tools') !!}
    @endif
    
    <!-- Google Ads Conversion Tracking -->
    @if(setting('google_ads_conversion_id'))
    <script>
        gtag('event', 'conversion', {
            'send_to': '{{ setting('google_ads_conversion_id') }}'
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