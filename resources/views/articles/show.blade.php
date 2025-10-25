@extends('layouts.app')

@section('title', $article->meta_title ?: $article->title)
@section('description', $article->meta_description)
@section('keywords', $article->meta_keywords)

@push('head')
<!-- Open Graph pour les réseaux sociaux -->
<meta property="og:type" content="article">
<meta property="og:title" content="{{ $article->meta_title ?: $article->title }}">
<meta property="og:description" content="{{ $article->meta_description }}">
<meta property="og:url" content="{{ request()->url() }}">
@if($article->featured_image)
<meta property="og:image" content="{{ asset($article->featured_image) }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{{ $article->title }}">
@else
<meta property="og:image" content="{{ asset('images/og-blog.jpg') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{{ $article->title }}">
@endif
<meta property="og:site_name" content="{{ setting('company_name', 'Sauser Couverture') }}">
<meta property="article:published_time" content="{{ $article->created_at->toISOString() }}">
<meta property="article:author" content="{{ setting('company_name', 'Sauser Couverture') }}">

<!-- Twitter Cards -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $article->meta_title ?: $article->title }}">
<meta name="twitter:description" content="{{ $article->meta_description }}">
@if($article->featured_image)
<meta name="twitter:image" content="{{ asset($article->featured_image) }}">
@else
<meta name="twitter:image" content="{{ asset('images/og-blog.jpg') }}">
@endif

<style>
/* Styles pour le contenu généré par ChatGPT avec Tailwind CSS */
.article-content {
    line-height: 1.7;
    color: #374151;
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
@endpush

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
                        <div class="w-full">
                            <img src="{{ asset($article->featured_image) }}" alt="{{ $article->title }}" 
                                 class="w-full aspect-video object-cover rounded-lg shadow-lg">
                        </div>
                    @endif
                    
                    <div class="p-8">
                        <!-- Article Content - HTML généré par ChatGPT avec Tailwind CSS -->
                        <div class="article-content">
                            {!! $article->content_html !!}
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
            <div class="hidden lg:block lg:col-span-1">
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

        <!-- Mobile Contact Section -->
        <div class="mt-8 lg:hidden">
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
