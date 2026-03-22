    <!-- Why Choose Us Section -->
    @if($homeConfig['sections']['why_choose_us']['enabled'] ?? true)
    <section class="py-20 bg-white">
        <div class="site-shell">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">
                    {{ $homeConfig['sections']['why_choose_us']['title'] ?? 'Pourquoi Nous Choisir ?' }}
                </h2>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary text-white rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                        <i class="fas fa-award"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Expertise Reconnue</h3>
                    <p class="text-gray-600">Plus de {{ setting('company_experience', '15') }} ans d'expérience dans le domaine</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary text-white rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Garantie Qualité</h3>
                    <p class="text-gray-600">Tous nos travaux sont garantis et assurés</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary text-white rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Intervention Rapide</h3>
                    <p class="text-gray-600">Devis gratuit sous 24h, intervention sous 48h</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary text-white rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Satisfaction Client</h3>
                    <p class="text-gray-600">{{ $averageRating > 0 ? number_format($averageRating, 1) : '98' }}/5 de satisfaction client</p>
                </div>
            </div>
        </div>
    </section>
    @endif
