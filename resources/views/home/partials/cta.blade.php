    <!-- CTA Section -->
    @if($homeConfig['sections']['cta']['enabled'] ?? true)
    <section class="py-20 relative overflow-hidden" style="background-color: var(--primary-color);">
        <!-- Overlay sombre pour améliorer la lisibilité -->
        <div class="absolute inset-0 bg-black/40"></div>
        
        <!-- Motif de fond subtil -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-r from-white/10 to-transparent"></div>
        </div>
        
        <div class="container mx-auto px-4 relative z-10">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-white mb-6 drop-shadow-2xl" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.8);">
                    {{ $homeConfig['sections']['cta']['title'] ?? 'Prêt à Démarrer Votre Projet ?' }}
                </h2>
                <p class="text-xl text-white mb-8 max-w-2xl mx-auto drop-shadow-xl font-medium" style="text-shadow: 1px 1px 3px rgba(0,0,0,0.8);">
                    Contactez-nous dès aujourd'hui pour un devis gratuit et personnalisé
                </p>
            </div>
            
            
            <!-- Boutons d'action -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('contact') }}" 
                   class="text-white px-8 py-4 rounded-full text-lg font-semibold transition-all duration-300 transform hover:scale-105 shadow-lg"
                   style="background-color: var(--primary-color);"
                   onmouseover="this.style.backgroundColor='var(--accent-color)';"
                   onmouseout="this.style.backgroundColor='var(--primary-color)';">
                    <i class="fas fa-envelope mr-2"></i>
                    Contact
                </a>
                <a href="{{ route('form.step', 'propertyType') }}" 
                   class="text-white px-8 py-4 rounded-full text-lg font-semibold transition-all duration-300 transform hover:scale-105 shadow-lg"
                   style="background-color: var(--secondary-color);"
                   onmouseover="this.style.backgroundColor='var(--accent-color)';"
                   onmouseout="this.style.backgroundColor='var(--secondary-color)';"
                   onclick="trackFormClick('{{ request()->url() }}')">
                    <i class="fas fa-calculator mr-2"></i>
                    Simulateur de Devis
                </a>
                <a href="tel:{{ setting('company_phone_raw', setting('company_phone')) }}" 
                   class="text-white px-8 py-4 rounded-full text-lg font-semibold transition-all duration-300 transform hover:scale-105 shadow-lg"
                   style="background-color: var(--accent-color);"
                   onmouseover="this.style.backgroundColor='var(--secondary-color)';"
                   onmouseout="this.style.backgroundColor='var(--accent-color)';"
                   onclick="trackPhoneCall('{{ setting('company_phone_raw', setting('company_phone')) }}', 'home-cta')">
                    <i class="fas fa-phone mr-2"></i>
                    Appeler
                </a>
            </div>
        </div>
    </section>
    @endif
