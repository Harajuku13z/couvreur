@extends('layouts.app')

@php
    // Définir les variables pour le SEO centralisé
    $currentPage = $currentPage ?? 'blog';
    $pageType = 'website';
    
    // Récupérer les meta tags depuis SeoHelper
    $seoData = \App\Helpers\SeoHelper::generateMetaTags('blog', [
        'title' => 'Blog et Astuces',
        'description' => 'Découvrez nos articles et conseils d\'experts en rénovation et couverture',
        'image' => file_exists(public_path('images/og-blog.jpg')) ? asset('images/og-blog.jpg') : null,
    ]);
    
    $pageTitle = $seoData['title'];
    $pageDescription = $seoData['description'];
    $pageImage = $seoData['og:image'];
@endphp

@push('head')
<style>
    /* Variables de couleurs de branding */
    :root {
        --primary-color: {{ setting('primary_color', '#3b82f6') }};
        --secondary-color: {{ setting('secondary_color', '#1e40af') }};
        --accent-color: {{ setting('accent_color', '#f59e0b') }};
    }
</style>
@endpush

@section('head')
<!-- SEO Meta Tags -->
<meta name="robots" content="index, follow">
<meta name="author" content="{{ setting('company_name') }}">
<meta name="publisher" content="{{ setting('company_name') }}">

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
@endsection

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="text-white py-16" style="background: linear-gradient(90deg, var(--primary-color) 0%, var(--secondary-color) 100%);">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">Blog et Astuces</h1>
                <p class="text-xl text-blue-100 max-w-2xl mx-auto">
                    Découvrez nos conseils d'experts pour tous vos projets de rénovation
                </p>
            </div>
        </div>
    </div>

    <!-- Articles Grid -->
    <div class="max-w-6xl mx-auto px-4 py-12">
        @if($articles->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($articles as $article)
                    <article class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                        @if($article->featured_image)
                            <div class="aspect-w-16 aspect-h-9">
                                <img src="{{ asset($article->featured_image) }}" alt="{{ $article->title }}" 
                                     class="w-full h-48 object-cover">
                            </div>
                        @endif
                        
                        <div class="p-6">
                            <div class="flex items-center text-sm text-gray-500 mb-3">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold mr-3"
                                      style="background-color: rgba(var(--primary-color-rgb, 59, 130, 246), 0.1); color: var(--primary-color);">
                                    Article
                                </span>
                                <span>{{ $article->published_at->format('d/m/Y') }}</span>
                            </div>
                            
                            <h2 class="text-xl font-bold text-gray-900 mb-3 line-clamp-2">
                            <a href="{{ route('blog.show', $article) }}" class="transition-colors"
                               style="--hover-color: var(--primary-color);"
                               onmouseover="this.style.color='var(--primary-color)';"
                               onmouseout="this.style.color='rgb(17 24 39)';">
                                {{ $article->title }}
                            </a>
                            </h2>
                            
                            @if($article->meta_description)
                                <p class="text-gray-600 mb-4 line-clamp-3">{{ $article->meta_description }}</p>
                            @endif
                            
                            <a href="{{ route('blog.show', $article) }}" 
                               class="inline-flex items-center font-semibold transition-colors"
                               style="color: var(--primary-color);"
                               onmouseover="this.style.color='var(--secondary-color)';"
                               onmouseout="this.style.color='var(--primary-color)';">
                                Lire la suite
                                <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            @if($articles->hasPages())
                <div class="mt-12">
                    {{ $articles->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-12">
                <div class="text-gray-400 text-6xl mb-4">
                    <i class="fas fa-newspaper"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">Aucun article disponible</h3>
                <p class="text-gray-500">Les articles seront bientôt disponibles.</p>
            </div>
        @endif
    </div>
</div>
@endsection
