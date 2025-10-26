@extends('layouts.admin')

@section('title', 'Configuration SEO par Page')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Configuration SEO par Page</h1>
        <div class="space-x-4">
            <a href="{{ route('admin.seo.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition">
                <i class="fas fa-arrow-left mr-2"></i>Retour à la Configuration Générale
            </a>
        </div>
    </div>
    
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ session('error') }}
    </div>
    @endif

    <!-- Navigation des pages -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4 border-b">
            <h2 class="text-lg font-semibold">Pages du Site</h2>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <button onclick="showPage('home')" class="page-tab bg-blue-100 text-blue-800 px-4 py-2 rounded-lg font-medium hover:bg-blue-200 transition">
                    🏠 Accueil
                </button>
                <button onclick="showPage('services')" class="page-tab bg-gray-100 text-gray-800 px-4 py-2 rounded-lg font-medium hover:bg-gray-200 transition">
                    🔧 Services
                </button>
                <button onclick="showPage('portfolio')" class="page-tab bg-gray-100 text-gray-800 px-4 py-2 rounded-lg font-medium hover:bg-gray-200 transition">
                    🏗️ Réalisations
                </button>
                <button onclick="showPage('blog')" class="page-tab bg-gray-100 text-gray-800 px-4 py-2 rounded-lg font-medium hover:bg-gray-200 transition">
                    📝 Blog
                </button>
                <button onclick="showPage('ads')" class="page-tab bg-gray-100 text-gray-800 px-4 py-2 rounded-lg font-medium hover:bg-gray-200 transition">
                    📢 Annonces
                </button>
                <button onclick="showPage('reviews')" class="page-tab bg-gray-100 text-gray-800 px-4 py-2 rounded-lg font-medium hover:bg-gray-200 transition">
                    ⭐ Avis
                </button>
                <button onclick="showPage('contact')" class="page-tab bg-gray-100 text-gray-800 px-4 py-2 rounded-lg font-medium hover:bg-gray-200 transition">
                    📞 Contact
                </button>
                <button onclick="showPage('mentions-legales')" class="page-tab bg-gray-100 text-gray-800 px-4 py-2 rounded-lg font-medium hover:bg-gray-200 transition">
                    ⚖️ Mentions Légales
                </button>
                <button onclick="showPage('politique-confidentialite')" class="page-tab bg-gray-100 text-gray-800 px-4 py-2 rounded-lg font-medium hover:bg-gray-200 transition">
                    🔒 Politique de Confidentialité
                </button>
                <button onclick="showPage('cgv')" class="page-tab bg-gray-100 text-gray-800 px-4 py-2 rounded-lg font-medium hover:bg-gray-200 transition">
                    📋 CGV
                </button>
            </div>
        </div>
    </div>

    <!-- Formulaire de configuration par page -->
    <form action="{{ route('admin.seo.update-pages') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <!-- Page Accueil -->
        <div id="page-home" class="page-content">
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4">🏠 Page d'Accueil</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="home_meta_title" class="block text-sm font-medium mb-2">Titre Meta</label>
                        <input type="text" id="home_meta_title" name="home_meta_title" 
                               value="{{ $seoPages['home']['meta_title'] ?? '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="home_meta_description" class="block text-sm font-medium mb-2">Description Meta</label>
                        <textarea id="home_meta_description" name="home_meta_description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ $seoPages['home']['meta_description'] ?? '' }}</textarea>
                    </div>
                    
                    <div>
                        <label for="home_og_title" class="block text-sm font-medium mb-2">Titre Open Graph</label>
                        <input type="text" id="home_og_title" name="home_og_title" 
                               value="{{ $seoPages['home']['og_title'] ?? '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="home_og_description" class="block text-sm font-medium mb-2">Description Open Graph</label>
                        <textarea id="home_og_description" name="home_og_description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ $seoPages['home']['og_description'] ?? '' }}</textarea>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="home_og_image" class="block text-sm font-medium mb-2">Image Open Graph</label>
                        <input type="file" id="home_og_image" name="home_og_image" accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        @if(!empty($seoPages['home']['og_image']))
                        <img src="{{ asset($seoPages['home']['og_image']) }}" alt="Image OG Accueil" class="mt-2 w-32 h-20 object-cover rounded">
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Services -->
        <div id="page-services" class="page-content hidden">
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4">🔧 Page Services</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="services_meta_title" class="block text-sm font-medium mb-2">Titre Meta</label>
                        <input type="text" id="services_meta_title" name="services_meta_title" 
                               value="{{ $seoPages['services']['meta_title'] ?? '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="services_meta_description" class="block text-sm font-medium mb-2">Description Meta</label>
                        <textarea id="services_meta_description" name="services_meta_description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ $seoPages['services']['meta_description'] ?? '' }}</textarea>
                    </div>
                    
                    <div>
                        <label for="services_og_title" class="block text-sm font-medium mb-2">Titre Open Graph</label>
                        <input type="text" id="services_og_title" name="services_og_title" 
                               value="{{ $seoPages['services']['og_title'] ?? '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="services_og_description" class="block text-sm font-medium mb-2">Description Open Graph</label>
                        <textarea id="services_og_description" name="services_og_description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ $seoPages['services']['og_description'] ?? '' }}</textarea>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="services_og_image" class="block text-sm font-medium mb-2">Image Open Graph</label>
                        <input type="file" id="services_og_image" name="services_og_image" accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        @if(!empty($seoPages['services']['og_image']))
                        <img src="{{ asset($seoPages['services']['og_image']) }}" alt="Image OG Services" class="mt-2 w-32 h-20 object-cover rounded">
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Réalisations -->
        <div id="page-portfolio" class="page-content hidden">
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4">🏗️ Page Réalisations</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="portfolio_meta_title" class="block text-sm font-medium mb-2">Titre Meta</label>
                        <input type="text" id="portfolio_meta_title" name="portfolio_meta_title" 
                               value="{{ $seoPages['portfolio']['meta_title'] ?? '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="portfolio_meta_description" class="block text-sm font-medium mb-2">Description Meta</label>
                        <textarea id="portfolio_meta_description" name="portfolio_meta_description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ $seoPages['portfolio']['meta_description'] ?? '' }}</textarea>
                    </div>
                    
                    <div>
                        <label for="portfolio_og_title" class="block text-sm font-medium mb-2">Titre Open Graph</label>
                        <input type="text" id="portfolio_og_title" name="portfolio_og_title" 
                               value="{{ $seoPages['portfolio']['og_title'] ?? '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="portfolio_og_description" class="block text-sm font-medium mb-2">Description Open Graph</label>
                        <textarea id="portfolio_og_description" name="portfolio_og_description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ $seoPages['portfolio']['og_description'] ?? '' }}</textarea>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="portfolio_og_image" class="block text-sm font-medium mb-2">Image Open Graph</label>
                        <input type="file" id="portfolio_og_image" name="portfolio_og_image" accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        @if(!empty($seoPages['portfolio']['og_image']))
                        <img src="{{ asset($seoPages['portfolio']['og_image']) }}" alt="Image OG Réalisations" class="mt-2 w-32 h-20 object-cover rounded">
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Blog -->
        <div id="page-blog" class="page-content hidden">
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4">📝 Page Blog</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="blog_meta_title" class="block text-sm font-medium mb-2">Titre Meta</label>
                        <input type="text" id="blog_meta_title" name="blog_meta_title" 
                               value="{{ $seoPages['blog']['meta_title'] ?? '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="blog_meta_description" class="block text-sm font-medium mb-2">Description Meta</label>
                        <textarea id="blog_meta_description" name="blog_meta_description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ $seoPages['blog']['meta_description'] ?? '' }}</textarea>
                    </div>
                    
                    <div>
                        <label for="blog_og_title" class="block text-sm font-medium mb-2">Titre Open Graph</label>
                        <input type="text" id="blog_og_title" name="blog_og_title" 
                               value="{{ $seoPages['blog']['og_title'] ?? '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="blog_og_description" class="block text-sm font-medium mb-2">Description Open Graph</label>
                        <textarea id="blog_og_description" name="blog_og_description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ $seoPages['blog']['og_description'] ?? '' }}</textarea>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="blog_og_image" class="block text-sm font-medium mb-2">Image Open Graph</label>
                        <input type="file" id="blog_og_image" name="blog_og_image" accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        @if(!empty($seoPages['blog']['og_image']))
                        <img src="{{ asset($seoPages['blog']['og_image']) }}" alt="Image OG Blog" class="mt-2 w-32 h-20 object-cover rounded">
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Annonces -->
        <div id="page-ads" class="page-content hidden">
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4">📢 Page Annonces</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="ads_meta_title" class="block text-sm font-medium mb-2">Titre Meta</label>
                        <input type="text" id="ads_meta_title" name="ads_meta_title" 
                               value="{{ $seoPages['ads']['meta_title'] ?? '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="ads_meta_description" class="block text-sm font-medium mb-2">Description Meta</label>
                        <textarea id="ads_meta_description" name="ads_meta_description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ $seoPages['ads']['meta_description'] ?? '' }}</textarea>
                    </div>
                    
                    <div>
                        <label for="ads_og_title" class="block text-sm font-medium mb-2">Titre Open Graph</label>
                        <input type="text" id="ads_og_title" name="ads_og_title" 
                               value="{{ $seoPages['ads']['og_title'] ?? '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="ads_og_description" class="block text-sm font-medium mb-2">Description Open Graph</label>
                        <textarea id="ads_og_description" name="ads_og_description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ $seoPages['ads']['og_description'] ?? '' }}</textarea>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="ads_og_image" class="block text-sm font-medium mb-2">Image Open Graph</label>
                        <input type="file" id="ads_og_image" name="ads_og_image" accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        @if(!empty($seoPages['ads']['og_image']))
                        <img src="{{ asset($seoPages['ads']['og_image']) }}" alt="Image OG Annonces" class="mt-2 w-32 h-20 object-cover rounded">
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Avis -->
        <div id="page-reviews" class="page-content hidden">
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4">⭐ Page Avis</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="reviews_meta_title" class="block text-sm font-medium mb-2">Titre Meta</label>
                        <input type="text" id="reviews_meta_title" name="reviews_meta_title" 
                               value="{{ $seoPages['reviews']['meta_title'] ?? '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="reviews_meta_description" class="block text-sm font-medium mb-2">Description Meta</label>
                        <textarea id="reviews_meta_description" name="reviews_meta_description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ $seoPages['reviews']['meta_description'] ?? '' }}</textarea>
                    </div>
                    
                    <div>
                        <label for="reviews_og_title" class="block text-sm font-medium mb-2">Titre Open Graph</label>
                        <input type="text" id="reviews_og_title" name="reviews_og_title" 
                               value="{{ $seoPages['reviews']['og_title'] ?? '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="reviews_og_description" class="block text-sm font-medium mb-2">Description Open Graph</label>
                        <textarea id="reviews_og_description" name="reviews_og_description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ $seoPages['reviews']['og_description'] ?? '' }}</textarea>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="reviews_og_image" class="block text-sm font-medium mb-2">Image Open Graph</label>
                        <input type="file" id="reviews_og_image" name="reviews_og_image" accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        @if(!empty($seoPages['reviews']['og_image']))
                        <img src="{{ asset($seoPages['reviews']['og_image']) }}" alt="Image OG Avis" class="mt-2 w-32 h-20 object-cover rounded">
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Contact -->
        <div id="page-contact" class="page-content hidden">
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4">📞 Page Contact</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="contact_meta_title" class="block text-sm font-medium mb-2">Titre Meta</label>
                        <input type="text" id="contact_meta_title" name="contact_meta_title" 
                               value="{{ $seoPages['contact']['meta_title'] ?? '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="contact_meta_description" class="block text-sm font-medium mb-2">Description Meta</label>
                        <textarea id="contact_meta_description" name="contact_meta_description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ $seoPages['contact']['meta_description'] ?? '' }}</textarea>
                    </div>
                    
                    <div>
                        <label for="contact_og_title" class="block text-sm font-medium mb-2">Titre Open Graph</label>
                        <input type="text" id="contact_og_title" name="contact_og_title" 
                               value="{{ $seoPages['contact']['og_title'] ?? '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="contact_og_description" class="block text-sm font-medium mb-2">Description Open Graph</label>
                        <textarea id="contact_og_description" name="contact_og_description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ $seoPages['contact']['og_description'] ?? '' }}</textarea>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="contact_og_image" class="block text-sm font-medium mb-2">Image Open Graph</label>
                        <input type="file" id="contact_og_image" name="contact_og_image" accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        @if(!empty($seoPages['contact']['og_image']))
                        <img src="{{ asset($seoPages['contact']['og_image']) }}" alt="Image OG Contact" class="mt-2 w-32 h-20 object-cover rounded">
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Mentions Légales -->
        <div id="page-mentions-legales" class="page-content hidden">
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4">⚖️ Page Mentions Légales</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="mentions-legales_meta_title" class="block text-sm font-medium mb-2">Titre Meta</label>
                        <input type="text" id="mentions-legales_meta_title" name="mentions-legales_meta_title" 
                               value="{{ $seoPages['mentions-legales']['meta_title'] ?? '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="mentions-legales_meta_description" class="block text-sm font-medium mb-2">Description Meta</label>
                        <textarea id="mentions-legales_meta_description" name="mentions-legales_meta_description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ $seoPages['mentions-legales']['meta_description'] ?? '' }}</textarea>
                    </div>
                    
                    <div>
                        <label for="mentions-legales_og_title" class="block text-sm font-medium mb-2">Titre Open Graph</label>
                        <input type="text" id="mentions-legales_og_title" name="mentions-legales_og_title" 
                               value="{{ $seoPages['mentions-legales']['og_title'] ?? '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="mentions-legales_og_description" class="block text-sm font-medium mb-2">Description Open Graph</label>
                        <textarea id="mentions-legales_og_description" name="mentions-legales_og_description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ $seoPages['mentions-legales']['og_description'] ?? '' }}</textarea>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="mentions-legales_og_image" class="block text-sm font-medium mb-2">Image Open Graph</label>
                        <input type="file" id="mentions-legales_og_image" name="mentions-legales_og_image" accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        @if(!empty($seoPages['mentions-legales']['og_image']))
                        <img src="{{ asset($seoPages['mentions-legales']['og_image']) }}" alt="Image OG Mentions Légales" class="mt-2 w-32 h-20 object-cover rounded">
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Politique de Confidentialité -->
        <div id="page-politique-confidentialite" class="page-content hidden">
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4">🔒 Page Politique de Confidentialité</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="politique-confidentialite_meta_title" class="block text-sm font-medium mb-2">Titre Meta</label>
                        <input type="text" id="politique-confidentialite_meta_title" name="politique-confidentialite_meta_title" 
                               value="{{ $seoPages['politique-confidentialite']['meta_title'] ?? '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="politique-confidentialite_meta_description" class="block text-sm font-medium mb-2">Description Meta</label>
                        <textarea id="politique-confidentialite_meta_description" name="politique-confidentialite_meta_description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ $seoPages['politique-confidentialite']['meta_description'] ?? '' }}</textarea>
                    </div>
                    
                    <div>
                        <label for="politique-confidentialite_og_title" class="block text-sm font-medium mb-2">Titre Open Graph</label>
                        <input type="text" id="politique-confidentialite_og_title" name="politique-confidentialite_og_title" 
                               value="{{ $seoPages['politique-confidentialite']['og_title'] ?? '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="politique-confidentialite_og_description" class="block text-sm font-medium mb-2">Description Open Graph</label>
                        <textarea id="politique-confidentialite_og_description" name="politique-confidentialite_og_description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ $seoPages['politique-confidentialite']['og_description'] ?? '' }}</textarea>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="politique-confidentialite_og_image" class="block text-sm font-medium mb-2">Image Open Graph</label>
                        <input type="file" id="politique-confidentialite_og_image" name="politique-confidentialite_og_image" accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        @if(!empty($seoPages['politique-confidentialite']['og_image']))
                        <img src="{{ asset($seoPages['politique-confidentialite']['og_image']) }}" alt="Image OG Politique de Confidentialité" class="mt-2 w-32 h-20 object-cover rounded">
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Page CGV -->
        <div id="page-cgv" class="page-content hidden">
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4">📋 Page CGV</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="cgv_meta_title" class="block text-sm font-medium mb-2">Titre Meta</label>
                        <input type="text" id="cgv_meta_title" name="cgv_meta_title" 
                               value="{{ $seoPages['cgv']['meta_title'] ?? '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="cgv_meta_description" class="block text-sm font-medium mb-2">Description Meta</label>
                        <textarea id="cgv_meta_description" name="cgv_meta_description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ $seoPages['cgv']['meta_description'] ?? '' }}</textarea>
                    </div>
                    
                    <div>
                        <label for="cgv_og_title" class="block text-sm font-medium mb-2">Titre Open Graph</label>
                        <input type="text" id="cgv_og_title" name="cgv_og_title" 
                               value="{{ $seoPages['cgv']['og_title'] ?? '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="cgv_og_description" class="block text-sm font-medium mb-2">Description Open Graph</label>
                        <textarea id="cgv_og_description" name="cgv_og_description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ $seoPages['cgv']['og_description'] ?? '' }}</textarea>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="cgv_og_image" class="block text-sm font-medium mb-2">Image Open Graph</label>
                        <input type="file" id="cgv_og_image" name="cgv_og_image" accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        @if(!empty($seoPages['cgv']['og_image']))
                        <img src="{{ asset($seoPages['cgv']['og_image']) }}" alt="Image OG CGV" class="mt-2 w-32 h-20 object-cover rounded">
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg">
                Sauvegarder la Configuration SEO
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function showPage(pageName) {
    // Masquer toutes les pages
    document.querySelectorAll('.page-content').forEach(page => {
        page.classList.add('hidden');
    });
    
    // Désactiver tous les onglets
    document.querySelectorAll('.page-tab').forEach(tab => {
        tab.classList.remove('bg-blue-100', 'text-blue-800');
        tab.classList.add('bg-gray-100', 'text-gray-800');
    });
    
    // Afficher la page sélectionnée
    document.getElementById('page-' + pageName).classList.remove('hidden');
    
    // Activer l'onglet sélectionné
    event.target.classList.remove('bg-gray-100', 'text-gray-800');
    event.target.classList.add('bg-blue-100', 'text-blue-800');
}
</script>
@endpush
