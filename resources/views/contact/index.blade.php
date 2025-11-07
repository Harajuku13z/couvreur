@extends('layouts.app')

@section('title', $pageTitle ?? 'Contact')
@section('description', $pageDescription ?? 'Contactez-nous')

@php
    $pageType = 'website';
@endphp

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Contactez-nous</h1>
            <p class="text-xl md:text-2xl max-w-2xl mx-auto">
                Une question ? Un projet ? Notre équipe est à votre écoute
            </p>
        </div>
    </section>

    <div class="container mx-auto px-4 py-16">
        <div class="grid md:grid-cols-2 gap-12">
            <!-- Informations de contact -->
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-8">
                    <i class="fas fa-address-card mr-3 text-primary"></i>Nos Coordonnées
                </h2>
                
                <div class="space-y-6">
                    <!-- Adresse -->
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800 mb-1">Adresse</h3>
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
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800 mb-1">Téléphone</h3>
                            <a href="tel:{{ $companySettings['phone_raw'] ?? $companySettings['phone'] }}" 
                               class="text-primary hover:text-secondary transition-colors"
                               onclick="trackPhoneCall('{{ $companySettings['phone_raw'] ?? $companySettings['phone'] }}', 'contact')">
                                {{ $companySettings['phone'] }}
                            </a>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Email -->
                    @if($companySettings['email'])
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800 mb-1">Email</h3>
                            <a href="mailto:{{ $companySettings['email'] }}" 
                               class="text-primary hover:text-secondary transition-colors">
                                {{ $companySettings['email'] }}
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
                
                <!-- CTA -->
                <div class="mt-8">
                    <a href="{{ route('form.step', 'propertyType') }}" 
                       class="inline-flex items-center bg-primary text-white px-8 py-4 rounded-lg font-semibold hover:bg-secondary transition-colors">
                        <i class="fas fa-calculator mr-2"></i>
                        Demander un devis gratuit
                    </a>
                </div>
            </div>
            
            <!-- Formulaire de contact -->
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-8">
                    <i class="fas fa-paper-plane mr-3 text-primary"></i>Envoyez-nous un message
                </h2>
                
                <form action="{{ route('contact.send') }}" method="POST" class="space-y-6">
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
                    
                    <button type="submit" 
                            class="w-full bg-primary text-white px-6 py-4 rounded-lg font-semibold hover:bg-secondary transition-colors">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Envoyer le message
                    </button>
                </form>
            </div>
        </div>
        
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

