@extends('layouts.app')

@section('title', $article->meta_title ?: $article->title)
@section('description', $article->meta_description)
@section('keywords', $article->meta_keywords)

@section('head')
<!-- SEO Meta Tags -->
<meta name="robots" content="index, follow">
<meta name="author" content="{{ setting('company_name') }}">
<meta name="publisher" content="{{ setting('company_name') }}">
<meta name="copyright" content="{{ setting('company_name') }}">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="article">
<meta property="og:url" content="{{ request()->url() }}">
<meta property="og:title" content="{{ $article->meta_title ?: $article->title }}">
<meta property="og:description" content="{{ $article->meta_description }}">
<meta property="og:site_name" content="{{ setting('company_name') }}">
@if($article->featured_image)
<meta property="og:image" content="{{ asset($article->featured_image) }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
@endif
<meta property="og:locale" content="fr_FR">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="{{ request()->url() }}">
<meta name="twitter:title" content="{{ $article->meta_title ?: $article->title }}">
<meta name="twitter:description" content="{{ $article->meta_description }}">
@if($article->featured_image)
<meta name="twitter:image" content="{{ asset($article->featured_image) }}">
@endif

<!-- Article specific meta -->
@if($article->published_at)
<meta property="article:published_time" content="{{ $article->published_at->toISOString() }}">
@endif
<meta property="article:author" content="{{ setting('company_name') }}">
<meta property="article:section" content="Rénovation">
<meta property="article:tag" content="{{ $article->meta_keywords }}">

<!-- Bing Meta Tags -->
<meta name="msvalidate.01" content="{{ setting('bing_verification') }}">
<meta name="msapplication-TileColor" content="#3b82f6">
<meta name="theme-color" content="#3b82f6">

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

<!-- Facebook Pixel -->
@if(setting('facebook_pixel_id'))
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{{ setting('facebook_pixel_id') }}');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id={{ setting('facebook_pixel_id') }}&ev=PageView&noscript=1" /></noscript>
@endif

<!-- Google Ads -->
@if(setting('google_ads_id'))
<script async src="https://www.googletagmanager.com/gtag/js?id={{ setting('google_ads_id') }}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{{ setting('google_ads_id') }}');
</script>
@endif

<!-- Google Search Console -->
@if(setting('google_search_console'))
<meta name="google-site-verification" content="{{ setting('google_search_console') }}">
@endif

<!-- Bing Webmaster Tools -->
@if(setting('bing_webmaster_tools'))
<meta name="msvalidate.01" content="{{ setting('bing_webmaster_tools') }}">
@endif

<!-- Structured Data -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "{{ $article->title }}",
  "description": "{{ $article->meta_description }}",
  "image": "{{ $article->featured_image ? asset($article->featured_image) : asset('images/default-article.jpg') }}",
  "author": {
    "@type": "Organization",
    "name": "{{ setting('company_name') }}"
  },
  "publisher": {
    "@type": "Organization",
    "name": "{{ setting('company_name') }}",
    "logo": {
      "@type": "ImageObject",
      "url": "{{ asset('images/logo.png') }}"
    }
  },
  "datePublished": "{{ $article->published_at ? $article->published_at->toISOString() : now()->toISOString() }}",
  "dateModified": "{{ $article->updated_at->toISOString() }}",
  "mainEntityOfPage": {
    "@type": "WebPage",
    "@id": "{{ request()->url() }}"
  }
}
</script>
@endsection

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">{{ $article->title }}</h1>
                <div class="flex items-center justify-center text-blue-100 space-x-4">
                    @if($article->published_at)
                    <span class="bg-blue-700 px-3 py-1 rounded-full text-sm">
                        <i class="fas fa-calendar mr-1"></i>{{ $article->published_at->format('d/m/Y') }}
                    </span>
                    @endif
                    <span class="bg-blue-700 px-3 py-1 rounded-full text-sm">
                        <i class="fas fa-clock mr-1"></i>Lecture
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-4 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Article Content -->
            <div class="lg:col-span-3">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    @if($article->featured_image)
                        <div class="aspect-w-16 aspect-h-9">
                            <img src="{{ asset($article->featured_image) }}" alt="{{ $article->title }}" 
                                 class="w-full h-64 object-cover">
                        </div>
                    @endif
                    
                    <div class="p-8">
                        <!-- Article Content - HTML tel quel de ChatGPT -->
                        <div class="prose prose-lg max-w-none">
                            {!! $article->content_html !!}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="space-y-6">
                    <!-- Contact Card -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Besoin d'aide ?</h3>
                        <div class="space-y-4">
                            <a href="tel:{{ setting('company_phone_raw') }}" 
                               class="flex items-center text-green-600 hover:text-green-800 font-semibold">
                                <i class="fas fa-phone mr-3"></i>
                                {{ setting('company_phone') }}
                            </a>
                            <a href="{{ route('form.step', 'propertyType') }}" 
                               class="flex items-center text-blue-600 hover:text-blue-800 font-semibold">
                                <i class="fas fa-calculator mr-3"></i>
                                Devis gratuit
                            </a>
                        </div>
                    </div>

                    <!-- Company Info -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Notre Entreprise</h3>
                        <div class="space-y-3 text-sm text-gray-600">
                            <p><strong>{{ setting('company_name') }}</strong></p>
                            <p>{{ setting('company_address') }}</p>
                            <p>{{ setting('company_phone') }}</p>
                            <p>{{ setting('company_email') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Realizations Section -->
        <div class="mt-12">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Nos Réalisations</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="col-span-3 text-center py-8">
                        <p class="text-gray-500">Nos réalisations seront bientôt disponibles.</p>
                    </div>
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

        <!-- CTA Section -->
        <div class="mt-12">
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white rounded-lg p-8 text-center">
                <h2 class="text-2xl font-bold mb-4">Prêt à commencer votre projet ?</h2>
                <p class="text-blue-100 mb-6">Contactez-nous pour un devis gratuit et personnalisé</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="tel:{{ setting('company_phone_raw') }}" 
                       class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                        <i class="fas fa-phone mr-2"></i>Appeler maintenant
                    </a>
                    <a href="{{ route('form.step', 'propertyType') }}" 
                       class="bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-800 transition-colors">
                        <i class="fas fa-calculator mr-2"></i>Devis gratuit
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection