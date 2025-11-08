@extends('layouts.app')

@section('title', $pageTitle ?? 'Contact')
@section('description', $pageDescription ?? 'Contactez-nous')

@php
    $pageType = 'website';
    $contactHeroImage = setting('contact_hero_image');
@endphp

@push('head')
<style>
    .contact-hero {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        min-height: 500px;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    
    .contact-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.3;
    }
    
    .contact-hero h1,
    .contact-hero p,
    .contact-hero a:not(.bg-white) {
        color: white !important;
    }
    
    .contact-hero .fas,
    .contact-hero .fab,
    .contact-hero i {
        color: white !important;
    }
    
    @if($contactHeroImage)
    .contact-hero {
        background-image: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%), url('{{ asset($contactHeroImage) }}');
        background-size: cover;
        background-position: center;
        background-blend-mode: overlay;
    }
    @endif
    
    .contact-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(0,0,0,0.05);
    }
    
    .contact-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 60px rgba(0,0,0,0.12);
        border-color: var(--primary-color);
    }
    
    .contact-card .icon-wrapper {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        transition: all 0.3s ease;
    }
    
    .contact-card:hover .icon-wrapper {
        transform: scale(1.1) rotate(5deg);
    }
    
    .form-input {
        transition: all 0.3s ease;
    }
    
    .form-input:focus {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .gradient-cta {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        position: relative;
        overflow: hidden;
    }
    
    .gradient-cta::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s ease;
    }
    
    .gradient-cta:hover::before {
        left: 100%;
    }
    
    .faq-item {
        transition: all 0.3s ease;
    }
    
    .faq-item:hover {
        transform: translateX(8px);
        border-color: var(--primary-color);
    }
    
    .pulse-dot {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }
    
    .floating {
        animation: floating 3s ease-in-out infinite;
    }
    
    @keyframes floating {
        0%, 100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-10px);
        }
    }
    
    .map-container {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    }
    
    .section-divider {
        height: 4px;
        width: 80px;
        background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        margin: 0 auto 2rem;
        border-radius: 2px;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50">
    <!-- Hero Section -->
    <section class="contact-hero text-white py-24 md:py-32">
        <div class="container mx-auto px-4 text-center relative z-10">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur-sm rounded-full mb-6 floating">
                <i class="fas fa-envelope-open-text text-4xl" style="color: white;"></i>
            </div>
            <h1 class="text-5xl md:text-7xl font-bold mb-6" style="color: white;">
                Parlons de votre projet
            </h1>
            <p class="text-xl md:text-2xl max-w-3xl mx-auto mb-12 opacity-95" style="color: white;">
                Notre équipe d'experts est disponible pour répondre à toutes vos questions et vous accompagner dans la réalisation de vos objectifs
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <a href="{{ route('form.step', 'propertyType') }}" 
                   class="group bg-white text-gray-900 px-8 py-4 rounded-full text-lg font-semibold hover:bg-gray-100 transition-all duration-300 transform hover:scale-105 shadow-xl inline-flex items-center">
                    <i class="fas fa-calculator mr-2 group-hover:rotate-12 transition-transform" style="color: #1f2937;"></i>
                    Devis gratuit en 2 min
                </a>
                <a href="tel:{{ $companySettings['phone_raw'] ?? $companySettings['phone'] }}" 
                   class="group bg-white/20 backdrop-blur-sm px-8 py-4 rounded-full text-lg font-semibold hover:bg-white/30 transition-all duration-300 transform hover:scale-105 shadow-xl inline-flex items-center"
                   style="color: white;"
                   onclick="trackPhoneCall('{{ $companySettings['phone_raw'] ?? $companySettings['phone'] }}', 'contact')">
                    <span class="relative flex h-3 w-3 mr-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-white"></span>
                    </span>
                    <i class="fas fa-phone mr-2 group-hover:rotate-12 transition-transform" style="color: white;"></i>
                    {{ $companySettings['phone'] }}
                </a>
            </div>
        </div>
    </section>

    @if(session('success'))
    <div class="container mx-auto px-4 pt-8">
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 px-6 py-4 rounded-lg shadow-md">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 text-2xl mr-3"></i>
                <span class="text-green-800 font-medium">{{ session('success') }}</span>
            </div>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="container mx-auto px-4 pt-8">
        <div class="bg-gradient-to-r from-red-50 to-rose-50 border-l-4 border-red-500 px-6 py-4 rounded-lg shadow-md">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 text-2xl mr-3"></i>
                <span class="text-red-800 font-medium">{{ session('error') }}</span>
            </div>
        </div>
    </div>
    @endif

    <div class="container mx-auto px-4 py-20">
        <div class="grid lg:grid-cols-5 gap-12 mb-20">
            <!-- Informations de contact - 2 colonnes -->
            <div class="lg:col-span-2">
                <div class="sticky top-8">
                    <h2 class="text-4xl font-bold text-gray-800 mb-3">
                        <i class="fas fa-address-card mr-3 text-transparent bg-clip-text bg-gradient-to-r from-primary to-secondary"></i>
                        Coordonnées
                    </h2>
                    <div class="section-divider ml-0"></div>
                    <p class="text-gray-600 mb-8 text-lg">
                        Plusieurs moyens de nous contacter, choisissez celui qui vous convient
                    </p>
                    
                    <div class="space-y-6">
                        <!-- Adresse -->
                        <div class="contact-card contact-info-card flex items-start bg-white p-6 rounded-2xl shadow-md">
                            <div class="icon-wrapper w-16 h-16 text-white rounded-2xl flex items-center justify-center mr-5 flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-2xl" style="color: white;"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-800 mb-2 text-lg">Notre adresse</h3>
                                <p class="text-gray-600 leading-relaxed">
                                    @if($companySettings['address'])
                                        {{ $companySettings['address'] }}<br>
                                    @endif
                                    @if($companySettings['postal_code'] || $companySettings['city'])
                                        {{ $companySettings['postal_code'] }} {{ $companySettings['city'] }}<br>
                                    @endif
                                    {{ $companySettings['country'] }}
                                </p>
                            </div>
                        </div>
                        
                        <!-- Téléphone -->
                        @if($companySettings['phone'])
                        <div class="contact-card contact-info-card flex items-start bg-white p-6 rounded-2xl shadow-md">
                            <div class="icon-wrapper w-16 h-16 text-white rounded-2xl flex items-center justify-center mr-5 flex-shrink-0">
                                <i class="fas fa-phone-alt text-2xl" style="color: white;"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-800 mb-2 text-lg">Appelez-nous</h3>
                                <a href="tel:{{ $companySettings['phone_raw'] ?? $companySettings['phone'] }}" 
                                   class="text-primary hover:text-secondary transition-colors text-xl font-bold inline-flex items-center group"
                                   onclick="trackPhoneCall('{{ $companySettings['phone_raw'] ?? $companySettings['phone'] }}', 'contact')">
                                    {{ $companySettings['phone'] }}
                                    <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                                </a>
                                <p class="text-sm text-gray-500 mt-1">
                                    <i class="far fa-clock mr-1"></i>Lun - Ven : 9h - 18h
                                </p>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Email -->
                        @if($companySettings['email'])
                        <div class="contact-card contact-info-card flex items-start bg-white p-6 rounded-2xl shadow-md">
                            <div class="icon-wrapper w-16 h-16 text-white rounded-2xl flex items-center justify-center mr-5 flex-shrink-0">
                                <i class="fas fa-envelope text-2xl" style="color: white;"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-800 mb-2 text-lg">Écrivez-nous</h3>
                                <a href="mailto:{{ $companySettings['email'] }}" 
                                   class="text-primary hover:text-secondary transition-colors text-lg font-semibold inline-flex items-center group break-all">
                                    {{ $companySettings['email'] }}
                                    <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform flex-shrink-0"></i>
                                </a>
                                <p class="text-sm text-gray-500 mt-1">
                                    <i class="fas fa-reply mr-1"></i>Réponse sous 24h
                                </p>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <!-- CTA vers simulateur -->
                    <div class="mt-8 p-8 gradient-cta rounded-2xl shadow-xl relative">
                        <div class="relative z-10">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl mb-4">
                                <i class="fas fa-rocket text-3xl" style="color: white;"></i>
                            </div>
                            <h3 class="text-2xl font-bold mb-3" style="color: white;">
                                Besoin d'un devis rapide ?
                            </h3>
                            <p class="mb-6 opacity-95 text-lg" style="color: white;">
                                Utilisez notre simulateur intelligent pour obtenir une estimation personnalisée en moins de 2 minutes
                            </p>
                            <a href="{{ route('form.step', 'propertyType') }}" 
                               class="inline-flex items-center bg-white text-gray-900 px-6 py-3 rounded-xl font-bold hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg">
                                <i class="fas fa-calculator mr-2"></i>
                                Lancer le simulateur
                                <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Formulaire de contact - 3 colonnes -->
            <div class="lg:col-span-3 form-section">
                <div class="bg-white rounded-3xl shadow-2xl p-8 md:p-12" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);">
                    <h2 class="text-4xl font-bold text-gray-800 mb-3">
                        <i class="fas fa-paper-plane mr-3 text-transparent bg-clip-text bg-gradient-to-r from-primary to-secondary"></i>
                        Envoyez un message
                    </h2>
                    <div class="section-divider ml-0"></div>
                    <p class="text-gray-600 mb-8 text-lg">
                        Remplissez le formulaire ci-dessous et nous vous recontacterons rapidement
                    </p>
                    
                    <form action="{{ route('contact.send') }}" method="POST" id="contactForm" class="space-y-6">
                        @csrf
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-bold text-gray-700 mb-2">
                                    <i class="fas fa-user mr-2 text-primary"></i>Nom complet *
                                </label>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       required
                                       placeholder="Jean Dupont"
                                       class="form-input w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-bold text-gray-700 mb-2">
                                    <i class="fas fa-envelope mr-2 text-primary"></i>Email *
                                </label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       required
                                       placeholder="jean.dupont@exemple.fr"
                                       class="form-input w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                            </div>
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label for="phone" class="block text-sm font-bold text-gray-700 mb-2">
                                    <i class="fas fa-phone mr-2 text-primary"></i>Téléphone
                                </label>
                                <input type="tel" 
                                       id="phone" 
                                       name="phone"
                                       placeholder="06 12 34 56 78"
                                       class="form-input w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                            </div>
                            
                            <div>
                                <label for="postal_code" class="block text-sm font-bold text-gray-700 mb-2">
                                    <i class="fas fa-map-pin mr-2 text-primary"></i>Code postal
                                </label>
                                <input type="text" 
                                       id="postal_code" 
                                       name="postal_code"
                                       placeholder="22540"
                                       maxlength="10"
                                       class="form-input w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                            </div>
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label for="city" class="block text-sm font-bold text-gray-700 mb-2">
                                    <i class="fas fa-city mr-2 text-primary"></i>Ville
                                </label>
                                <input type="text" 
                                       id="city" 
                                       name="city"
                                       placeholder="Pédernec"
                                       class="form-input w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                            </div>
                            
                            <div>
                                <label for="callback_time" class="block text-sm font-bold text-gray-700 mb-2">
                                    <i class="fas fa-clock mr-2 text-primary"></i>Quand vous rappeler ?
                                </label>
                                <select id="callback_time" 
                                        name="callback_time"
                                        class="form-input w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                                    <option value="">Sélectionnez un créneau</option>
                                    <option value="matin">🌅 Matin (9h - 12h)</option>
                                    <option value="apres-midi">☀️ Après-midi (14h - 17h)</option>
                                    <option value="soir">🌆 Soir (17h - 19h)</option>
                                    <option value="flexible">🔄 Flexible</option>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label for="service_interest" class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="fas fa-briefcase mr-2 text-primary"></i>Service qui vous intéresse
                            </label>
                            <select id="service_interest" 
                                    name="service_interest"
                                    class="form-input w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                                <option value="">Sélectionnez un service (optionnel)</option>
                                @php
                                    $servicesData = \App\Models\Setting::get('services', '[]');
                                    $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
                                    if (!is_array($services)) {
                                        $services = [];
                                    }
                                    $visibleServices = array_filter($services, function($service) {
                                        return is_array($service) && ($service['is_visible'] ?? true);
                                    });
                                @endphp
                                @foreach($visibleServices as $service)
                                    @if(is_array($service) && isset($service['name']))
                                    <option value="{{ $service['name'] }}">{{ $service['name'] }}</option>
                                    @endif
                                @endforeach
                                <option value="Autre">Autre</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-2 flex items-center">
                                <i class="fas fa-info-circle mr-1"></i>
                                Ou <a href="{{ route('form.step', 'propertyType') }}" class="text-primary hover:underline font-semibold mx-1">utilisez notre simulateur</a> pour un devis personnalisé
                            </p>
                        </div>
                        
                        <div>
                            <label for="subject" class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="fas fa-tag mr-2 text-primary"></i>Sujet *
                            </label>
                            <input type="text" 
                                   id="subject" 
                                   name="subject" 
                                   required
                                   placeholder="Résumé de votre demande"
                                   class="form-input w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                        </div>
                        
                        <div>
                            <label for="message" class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="fas fa-comment-alt mr-2 text-primary"></i>Message *
                            </label>
                            <textarea id="message" 
                                      name="message" 
                                      rows="6"
                                      required
                                      placeholder="Décrivez votre projet ou votre demande en détail..."
                                      class="form-input w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-all resize-none"></textarea>
                        </div>
                        
                        {{-- reCAPTCHA --}}
                        @if(setting('recaptcha_site_key'))
                        <div id="recaptcha-container"></div>
                        <input type="hidden" name="recaptcha_token" id="recaptcha_token">
                        @endif
                        
                        <button type="submit" 
                                id="submitBtn"
                                class="w-full text-white px-6 py-5 rounded-xl font-bold hover:shadow-2xl transition-all duration-300 transform hover:scale-105 text-lg"
                                style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Envoyer le message
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                        
                        <p class="text-xs text-gray-500 text-center mt-4">
                            <i class="fas fa-lock mr-1"></i>
                            Vos données sont protégées et ne seront jamais partagées
                        </p>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Google Maps -->
        @php
            $address = $companySettings['address'] ?? '';
            $city = $companySettings['city'] ?? '';
            $postalCode = $companySettings['postal_code'] ?? '';
            $country = $companySettings['country'] ?? 'France';
            $fullAddress = trim(implode(' ', array_filter([$address, $postalCode, $city, $country])));
            $mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($fullAddress);
        @endphp
        
        @if($fullAddress)
        <div class="mt-24">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-800 mb-3">
                    <i class="fas fa-map-marked-alt mr-3 text-transparent bg-clip-text bg-gradient-to-r from-primary to-secondary"></i>
                    Nous trouver
                </h2>
                <div class="section-divider"></div>
                <p class="text-xl text-gray-600">
                    Venez nous rendre visite à notre bureau
                </p>
            </div>
            
            <div class="map-container">
                <div class="w-full" style="height: 500px;">
                    <iframe 
                        width="100%" 
                        height="100%" 
                        style="border:0" 
                        loading="lazy" 
                        allowfullscreen
                        referrerpolicy="no-referrer-when-downgrade"
                        src="https://www.google.com/maps?q={{ urlencode($fullAddress) }}&output=embed">
                    </iframe>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Section FAQ -->
        @if(count($faqs) > 0)
        <div class="mt-24">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-800 mb-3">
                    <i class="fas fa-question-circle mr-3 text-transparent bg-clip-text bg-gradient-to-r from-primary to-secondary"></i>
                    Questions Fréquentes
                </h2>
                <div class="section-divider"></div>
                <p class="text-xl text-gray-600">
                    Les réponses aux questions les plus posées
                </p>
            </div>
            
            {{-- Barre de recherche FAQ --}}
            <div class="max-w-2xl mx-auto mb-12">
                <div class="relative">
                    <input type="text" 
                           id="faqSearch" 
                           placeholder="Rechercher une question..." 
                           class="w-full px-6 py-5 pl-14 border-2 border-gray-200 rounded-2xl focus:ring-2 focus:ring-primary focus:border-primary transition-all text-lg shadow-md">
                    <i class="fas fa-search absolute left-5 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl"></i>
                </div>
            </div>
            
            {{-- Liste des FAQ --}}
            <div class="max-w-4xl mx-auto space-y-4" id="faqList">
                @foreach($faqs as $index => $faq)
                <div class="faq-item bg-white border-2 border-gray-100 rounded-2xl shadow-md hover:shadow-xl transition-all" 
                     data-question="{{ strtolower($faq['question'] ?? '') }}" 
                     data-answer="{{ strtolower($faq['answer'] ?? '') }}">
                    <button class="w-full px-8 py-6 text-left flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-primary rounded-2xl group" 
                            onclick="toggleFaq({{ $index }})">
                        <span class="font-bold text-gray-800 pr-4 text-lg group-hover:text-primary transition-colors">
                            <i class="fas fa-comment-dots mr-3 text-primary"></i>
                            {{ $faq['question'] ?? '' }}
                        </span>
                        <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);">
                            <i class="fas fa-chevron-down text-white faq-icon-{{ $index }} transition-transform duration-300"></i>
                        </div>
                    </button>
                    <div class="faq-answer-{{ $index }} hidden px-8 pb-6" style="display: none !important; opacity: 0;">
                        <div class="text-gray-700 leading-relaxed pl-10 text-lg border-l-4 ml-2" style="border-color: var(--primary-color);">
                            <i class="fas fa-reply mr-2" style="color: var(--primary-color);"></i>
                            {!! nl2br(e($faq['answer'] ?? '')) !!}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <div class="text-center mt-12">
                <p class="text-gray-600 text-lg mb-4">
                    <i class="fas fa-lightbulb mr-2 text-yellow-500"></i>
                    Vous n'avez pas trouvé la réponse à votre question ?
                </p>
                <a href="#contactForm" 
                   class="inline-flex items-center text-primary hover:text-secondary font-bold text-lg transition-colors">
                    Contactez-nous directement
                    <i class="fas fa-arrow-down ml-2"></i>
                </a>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
{{-- reCAPTCHA --}}
@if(setting('recaptcha_site_key'))
@include('form.partials.recaptcha')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof grecaptcha !== 'undefined') {
        grecaptcha.ready(function() {
            grecaptcha.execute('{{ setting('recaptcha_site_key') }}', {action: 'contact'}).then(function(token) {
                document.getElementById('recaptcha_token').value = token;
            });
        });
    }
    
    // Recharger le token avant la soumission
    document.getElementById('contactForm').addEventListener('submit', function(e) {
        e.preventDefault(); // Empêcher la soumission immédiate
        
        const submitBtn = document.getElementById('submitBtn');
        const form = this;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Envoi en cours... <i class="fas fa-hourglass-half ml-2"></i>';
        
        // Générer le token reCAPTCHA si nécessaire
        if (typeof grecaptcha !== 'undefined' && '{{ setting('recaptcha_site_key') }}') {
            grecaptcha.ready(function() {
                grecaptcha.execute('{{ setting('recaptcha_site_key') }}', {action: 'contact'}).then(function(token) {
                    document.getElementById('recaptcha_token').value = token;
                    // Soumettre le formulaire une fois le token obtenu
                    form.submit();
                }).catch(function(error) {
                    console.error('reCAPTCHA error:', error);
                    // Soumettre quand même si reCAPTCHA échoue (ne pas bloquer l'utilisateur)
                    form.submit();
                });
            });
        } else {
            // Pas de reCAPTCHA, soumettre directement
            form.submit();
        }
    });
});
</script>
@endif

<script>
function toggleFaq(index) {
    const answer = document.querySelector(`.faq-answer-${index}`);
    const icon = document.querySelector(`.faq-icon-${index}`);
    
    if (!answer || !icon) return;
    
    // Vérifier si l'élément est actuellement caché (plus robuste)
    const isHidden = answer.classList.contains('hidden') || 
                     answer.style.display === 'none' || 
                     getComputedStyle(answer).display === 'none';
    
    if (isHidden) {
        // Fermer toutes les autres FAQ
        document.querySelectorAll('[class*="faq-answer-"]').forEach(el => {
            if (el !== answer) {
                el.style.opacity = '0';
                el.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    el.style.display = 'none';
                    el.classList.add('hidden');
                }, 200);
            }
        });
        document.querySelectorAll('[class*="faq-icon-"]').forEach(el => {
            if (el !== icon) {
                el.classList.remove('rotate-180');
            }
        });
        
        // Ouvrir celle-ci
        answer.classList.remove('hidden');
        answer.style.display = 'block';
        answer.style.opacity = '0';
        answer.style.transform = 'translateY(-10px)';
        icon.classList.add('rotate-180');
        
        // Forcer le reflow pour que l'animation fonctionne
        void answer.offsetHeight;
        
        // Animation d'apparition
        setTimeout(() => {
            answer.style.transition = 'all 0.3s ease';
            answer.style.opacity = '1';
            answer.style.transform = 'translateY(0)';
        }, 10);
        
        // Scroll smooth vers l'élément
        setTimeout(() => {
            answer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 100);
    } else {
        // Fermer
        answer.style.transition = 'all 0.3s ease';
        answer.style.opacity = '0';
        answer.style.transform = 'translateY(-10px)';
        icon.classList.remove('rotate-180');
        
        setTimeout(() => {
            answer.style.display = 'none';
            answer.classList.add('hidden');
        }, 300);
    }
}

// S'assurer que toutes les FAQ sont fermées au chargement
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[class*="faq-answer-"]').forEach(answer => {
        answer.style.display = 'none';
        answer.classList.add('hidden');
        answer.style.opacity = '0';
    });
});

// Recherche dans les FAQ avec mise en évidence
document.getElementById('faqSearch')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const faqItems = document.querySelectorAll('.faq-item');
    let visibleCount = 0;
    
    faqItems.forEach(item => {
        const question = item.getAttribute('data-question') || '';
        const answer = item.getAttribute('data-answer') || '';
        
        if (searchTerm === '' || question.includes(searchTerm) || answer.includes(searchTerm)) {
            item.style.display = 'block';
            visibleCount++;
            
            // Animation d'apparition
            item.style.opacity = '0';
            item.style.transform = 'translateY(20px)';
            setTimeout(() => {
                item.style.transition = 'all 0.3s ease';
                item.style.opacity = '1';
                item.style.transform = 'translateY(0)';
            }, 50);
        } else {
            item.style.display = 'none';
        }
    });
    
    // Afficher un message si aucun résultat
    let noResultMsg = document.getElementById('noResultMsg');
    if (visibleCount === 0 && searchTerm !== '') {
        if (!noResultMsg) {
            noResultMsg = document.createElement('div');
            noResultMsg.id = 'noResultMsg';
            noResultMsg.className = 'text-center py-12 bg-white rounded-2xl shadow-md';
            noResultMsg.innerHTML = `
                <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-600 text-lg">
                    <i class="fas fa-info-circle mr-2"></i>
                    Aucune question ne correspond à votre recherche
                </p>
                <p class="text-gray-500 mt-2">
                    Essayez avec d'autres mots-clés ou 
                    <a href="#contactForm" class="text-primary hover:underline font-semibold">contactez-nous directement</a>
                </p>
            `;
            document.getElementById('faqList').appendChild(noResultMsg);
        }
        noResultMsg.style.display = 'block';
    } else if (noResultMsg) {
        noResultMsg.style.display = 'none';
    }
});

// Animation au scroll pour les éléments
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observer les cartes de contact
document.querySelectorAll('.contact-card').forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(30px)';
    card.style.transition = 'all 0.6s ease';
    observer.observe(card);
});

// Animation du formulaire
const formInputs = document.querySelectorAll('.form-input');
formInputs.forEach((input, index) => {
    input.addEventListener('focus', function() {
        this.parentElement.querySelector('label')?.classList.add('text-primary');
    });
    
    input.addEventListener('blur', function() {
        if (!this.value) {
            this.parentElement.querySelector('label')?.classList.remove('text-primary');
        }
    });
});

// Smooth scroll pour les liens internes
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Validation temps réel du formulaire
const emailInput = document.getElementById('email');
const phoneInput = document.getElementById('phone');

emailInput?.addEventListener('blur', function() {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (this.value && !emailRegex.test(this.value)) {
        this.classList.add('border-red-500');
        this.classList.remove('border-gray-200');
        
        if (!this.nextElementSibling?.classList.contains('error-message')) {
            const errorMsg = document.createElement('p');
            errorMsg.className = 'error-message text-red-500 text-sm mt-1';
            errorMsg.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i>Email invalide';
            this.parentElement.appendChild(errorMsg);
        }
    } else {
        this.classList.remove('border-red-500');
        this.classList.add('border-gray-200');
        this.nextElementSibling?.remove();
    }
});

phoneInput?.addEventListener('blur', function() {
    const phoneRegex = /^[\d\s\+\-\(\)]{10,}$/;
    if (this.value && !phoneRegex.test(this.value)) {
        this.classList.add('border-red-500');
        this.classList.remove('border-gray-200');
        
        if (!this.nextElementSibling?.classList.contains('error-message')) {
            const errorMsg = document.createElement('p');
            errorMsg.className = 'error-message text-red-500 text-sm mt-1';
            errorMsg.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i>Numéro de téléphone invalide';
            this.parentElement.appendChild(errorMsg);
        }
    } else {
        this.classList.remove('border-red-500');
        this.classList.add('border-gray-200');
        this.nextElementSibling?.remove();
    }
});

// Compteur de caractères pour le message
const messageTextarea = document.getElementById('message');
if (messageTextarea) {
    const counterDiv = document.createElement('div');
    counterDiv.className = 'text-sm text-gray-500 text-right mt-1';
    counterDiv.innerHTML = '<i class="fas fa-align-left mr-1"></i><span id="charCount">0</span> caractères';
    messageTextarea.parentElement.appendChild(counterDiv);
    
    messageTextarea.addEventListener('input', function() {
        document.getElementById('charCount').textContent = this.value.length;
        
        if (this.value.length > 500) {
            counterDiv.classList.add('text-primary', 'font-semibold');
        } else {
            counterDiv.classList.remove('text-primary', 'font-semibold');
        }
    });
}

// Animation des icônes au survol
document.querySelectorAll('.contact-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        const icon = this.querySelector('i');
        if (icon) {
            icon.style.transform = 'scale(1.2) rotate(10deg)';
            icon.style.transition = 'transform 0.3s ease';
        }
    });
    
    card.addEventListener('mouseleave', function() {
        const icon = this.querySelector('i');
        if (icon) {
            icon.style.transform = 'scale(1) rotate(0deg)';
        }
    });
});
</script>
@endpush
@endsection