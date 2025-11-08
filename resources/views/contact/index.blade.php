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
        min-height: 400px;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
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
        background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('{{ asset($contactHeroImage) }}');
        background-size: cover;
        background-position: center;
    }
    @endif
    
    .contact-card {
        transition: all 0.3s ease;
    }
    
    .contact-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <section class="contact-hero text-white py-20">
        <div class="container mx-auto px-4 text-center relative z-10">
            <h1 class="text-4xl md:text-6xl font-bold mb-4" style="color: white;">Contactez-nous</h1>
            <p class="text-xl md:text-2xl max-w-3xl mx-auto mb-8" style="color: white;">
                Une question ? Un projet ? Notre équipe est à votre écoute pour vous accompagner
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('form.step', 'propertyType') }}" 
                   class="bg-white text-gray-900 px-8 py-4 rounded-full text-lg font-semibold hover:bg-gray-100 transition-all duration-300 transform hover:scale-105 shadow-lg">
                    <i class="fas fa-calculator mr-2"></i>
                    Demander un devis gratuit
                </a>
                <a href="tel:{{ $companySettings['phone_raw'] ?? $companySettings['phone'] }}" 
                   class="bg-white/20 backdrop-blur-sm px-8 py-4 rounded-full text-lg font-semibold hover:bg-white/30 transition-all duration-300 transform hover:scale-105 shadow-lg"
                   style="color: white;"
                   onclick="trackPhoneCall('{{ $companySettings['phone_raw'] ?? $companySettings['phone'] }}', 'contact')">
                    <i class="fas fa-phone mr-2" style="color: white;"></i>
                    {{ $companySettings['phone'] }}
                </a>
            </div>
        </div>
    </section>

    @if(session('success'))
    <div class="container mx-auto px-4 pt-8">
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="container mx-auto px-4 pt-8">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    </div>
    @endif

    <div class="container mx-auto px-4 py-16">
        <div class="grid md:grid-cols-2 gap-12 mb-16">
            <!-- Informations de contact -->
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-8">
                    <i class="fas fa-address-card mr-3 text-primary"></i>Nos Coordonnées
                </h2>
                
                <div class="space-y-6">
                    <!-- Adresse -->
                    <div class="contact-card flex items-start bg-white p-6 rounded-xl shadow-lg">
                        <div class="w-14 h-14 bg-primary text-white rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-map-marker-alt text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800 mb-2 text-lg">Adresse</h3>
                            <p class="text-gray-600">
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
                    <div class="contact-card flex items-start bg-white p-6 rounded-xl shadow-lg">
                        <div class="w-14 h-14 bg-primary text-white rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-phone text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800 mb-2 text-lg">Téléphone</h3>
                            <a href="tel:{{ $companySettings['phone_raw'] ?? $companySettings['phone'] }}" 
                               class="text-primary hover:text-secondary transition-colors text-lg font-semibold"
                               onclick="trackPhoneCall('{{ $companySettings['phone_raw'] ?? $companySettings['phone'] }}', 'contact')">
                                {{ $companySettings['phone'] }}
                            </a>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Email -->
                    @if($companySettings['email'])
                    <div class="contact-card flex items-start bg-white p-6 rounded-xl shadow-lg">
                        <div class="w-14 h-14 bg-primary text-white rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-envelope text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800 mb-2 text-lg">Email</h3>
                            <a href="mailto:{{ $companySettings['email'] }}" 
                               class="text-primary hover:text-secondary transition-colors text-lg font-semibold">
                                {{ $companySettings['email'] }}
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
                
                <!-- CTA vers simulateur -->
                <div class="mt-8 p-6 bg-gradient-to-r from-primary to-secondary rounded-xl">
                    <h3 class="text-xl font-bold mb-3" style="color: white;">
                        <i class="fas fa-calculator mr-2"></i>Besoin d'un devis ?
                    </h3>
                    <p class="mb-4 opacity-90" style="color: white;">
                        Utilisez notre simulateur pour obtenir un devis personnalisé en quelques minutes
                    </p>
                    <a href="{{ route('form.step', 'propertyType') }}" 
                       class="inline-flex items-center bg-white text-gray-900 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                        <i class="fas fa-arrow-right mr-2"></i>
                        Accéder au simulateur
                    </a>
                </div>
            </div>
            
            <!-- Formulaire de contact -->
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-8">
                    <i class="fas fa-paper-plane mr-3 text-primary"></i>Envoyez-nous un message
                </h2>
                
                <form action="{{ route('contact.send') }}" method="POST" id="contactForm" class="space-y-6">
                    @csrf
                    
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nom complet *
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email *
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                    </div>
                    
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                Téléphone
                            </label>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        
                        <div>
                            <label for="callback_time" class="block text-sm font-medium text-gray-700 mb-2">
                                Quand vous rappeler ?
                            </label>
                            <select id="callback_time" 
                                    name="callback_time"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="">Sélectionnez un créneau</option>
                                <option value="matin">Matin (9h - 12h)</option>
                                <option value="apres-midi">Après-midi (14h - 17h)</option>
                                <option value="soir">Soir (17h - 19h)</option>
                                <option value="flexible">Flexible</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label for="service_interest" class="block text-sm font-medium text-gray-700 mb-2">
                            Service qui vous intéresse
                        </label>
                        <select id="service_interest" 
                                name="service_interest"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
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
                        <p class="text-xs text-gray-500 mt-1">
                            Ou <a href="{{ route('form.step', 'propertyType') }}" class="text-primary hover:underline">utilisez notre simulateur</a> pour un devis personnalisé
                        </p>
                    </div>
                    
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                            Sujet *
                        </label>
                        <input type="text" 
                               id="subject" 
                               name="subject" 
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>
                    
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                            Message *
                        </label>
                        <textarea id="message" 
                                  name="message" 
                                  rows="6"
                                  required
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"></textarea>
                    </div>
                    
                    {{-- reCAPTCHA --}}
                    @if(setting('recaptcha_site_key'))
                    <div id="recaptcha-container"></div>
                    <input type="hidden" name="recaptcha_token" id="recaptcha_token">
                    @endif
                    
                    <button type="submit" 
                            id="submitBtn"
                            class="w-full bg-primary text-white px-6 py-4 rounded-lg font-semibold hover:bg-secondary transition-colors">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Envoyer le message
                    </button>
                </form>
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
        <div class="mt-16">
            <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">
                <i class="fas fa-map-marked-alt mr-3 text-primary"></i>Notre Localisation
            </h2>
            
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <!-- Carte Google Maps intégrée -->
                <div class="w-full" style="height: 450px;">
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
        <div class="mt-20">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">
                    Questions Fréquentes
                </h2>
                <p class="text-xl text-gray-600">
                    Trouvez rapidement les réponses à vos questions
                </p>
            </div>
            
            {{-- Barre de recherche FAQ --}}
            <div class="max-w-2xl mx-auto mb-8">
                <div class="relative">
                    <input type="text" 
                           id="faqSearch" 
                           placeholder="Rechercher dans les questions..." 
                           class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
            
            {{-- Liste des FAQ --}}
            <div class="max-w-4xl mx-auto space-y-4" id="faqList">
                @foreach($faqs as $index => $faq)
                <div class="faq-item bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow" 
                     data-question="{{ strtolower($faq['question'] ?? '') }}" 
                     data-answer="{{ strtolower($faq['answer'] ?? '') }}">
                    <button class="w-full px-6 py-4 text-left flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-primary rounded-lg" 
                            onclick="toggleFaq({{ $index }})">
                        <span class="font-semibold text-gray-800 pr-4">{{ $faq['question'] ?? '' }}</span>
                        <i class="fas fa-chevron-down text-primary faq-icon-{{ $index }} transition-transform"></i>
                    </button>
                    <div class="faq-answer-{{ $index }} hidden px-6 pb-4">
                        <div class="text-gray-600 leading-relaxed">
                            {!! nl2br(e($faq['answer'] ?? '')) !!}
                        </div>
                    </div>
                </div>
                @endforeach
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
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Envoi en cours...';
        
        if (typeof grecaptcha !== 'undefined') {
            grecaptcha.ready(function() {
                grecaptcha.execute('{{ setting('recaptcha_site_key') }}', {action: 'contact'}).then(function(token) {
                    document.getElementById('recaptcha_token').value = token;
                    // Le formulaire se soumettra normalement
                });
            });
        }
    });
});
</script>
@endif

<script>
function toggleFaq(index) {
    const answer = document.querySelector(`.faq-answer-${index}`);
    const icon = document.querySelector(`.faq-icon-${index}`);
    
    if (answer.classList.contains('hidden')) {
        // Fermer toutes les autres FAQ
        document.querySelectorAll('[class*="faq-answer-"]').forEach(el => {
            if (!el.classList.contains(`faq-answer-${index}`)) {
                el.classList.add('hidden');
            }
        });
        document.querySelectorAll('[class*="faq-icon-"]').forEach(el => {
            if (!el.classList.contains(`faq-icon-${index}`)) {
                el.classList.remove('rotate-180');
            }
        });
        
        // Ouvrir celle-ci
        answer.classList.remove('hidden');
        icon.classList.add('rotate-180');
    } else {
        answer.classList.add('hidden');
        icon.classList.remove('rotate-180');
    }
}

// Recherche dans les FAQ
document.getElementById('faqSearch')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.getAttribute('data-question') || '';
        const answer = item.getAttribute('data-answer') || '';
        
        if (question.includes(searchTerm) || answer.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});
</script>
@endpush
@endsection
