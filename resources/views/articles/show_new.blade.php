@extends('layouts.app')

@section('title', $article->meta_title ?: $article->title)
@section('description', $article->meta_description)
@section('keywords', $article->meta_keywords)

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
