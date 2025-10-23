@extends('layouts.app')

@section('title', $article->meta_title ?: $article->title)
@section('description', $article->meta_description)
@section('keywords', $article->meta_keywords)

@section('head')
<style>
.article-content {
    line-height: 1.7;
    color: #374151;
}

.article-content h1 {
    font-size: 2.25rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1rem;
    margin-top: 2rem;
}

.article-content h2 {
    font-size: 1.875rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.75rem;
    margin-top: 1.5rem;
}

.article-content h3 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.5rem;
    margin-top: 1.25rem;
}

.article-content p {
    margin-bottom: 1rem;
    font-size: 1.125rem;
}

.article-content article {
    max-width: none;
}

.article-content header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.article-content section {
    margin-bottom: 2rem;
}

.article-content .meta {
    color: #6b7280;
    font-size: 0.875rem;
    margin-bottom: 1rem;
}

.article-content .introduction {
    background-color: #f9fafb;
    padding: 1.5rem;
    border-radius: 0.5rem;
    border-left: 4px solid #3b82f6;
}

.article-content .faq {
    background-color: #f0f9ff;
    padding: 1.5rem;
    border-radius: 0.5rem;
    border: 1px solid #e0f2fe;
}

.article-content .conclusion {
    background-color: #f0fdf4;
    padding: 1.5rem;
    border-radius: 0.5rem;
    border-left: 4px solid #10b981;
}

.article-content footer {
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}
</style>
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
                        <div class="w-full">
                            <img src="{{ url($article->featured_image) }}" alt="{{ $article->title }}" 
                                 class="w-full aspect-video object-cover rounded-lg shadow-lg">
                        </div>
                    @endif
                    
                    <div class="p-8">
                        <!-- Article Content - HTML tel quel de ChatGPT -->
                        <div class="article-content">
                            {!! $article->content_html !!}
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
