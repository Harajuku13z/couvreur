    <!-- About Section -->
    @if(($homeConfig['about']['enabled'] ?? false) && !empty($homeConfig['about']['content']))
    <section class="py-20 bg-gray-100">
        <div class="site-shell">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Colonne 1: Texte justifié -->
                <div class="space-y-6">
                    <h2 class="text-4xl font-bold text-gray-800 mb-6">
                        {{ $homeConfig['about']['title'] ?? 'Qui Sommes-Nous ?' }}
                    </h2>
                    <div class="prose prose-lg text-gray-600 leading-relaxed text-justify">
                        {!! nl2br(e($homeConfig['about']['content'])) !!}
                    </div>
                    
                    <!-- Points forts -->
                    <div class="grid grid-cols-2 gap-4 mt-8">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-white text-sm"></i>
                            </div>
                            <span class="text-gray-700 font-medium">Expertise reconnue</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-white text-sm"></i>
                            </div>
                            <span class="text-gray-700 font-medium">Qualité garantie</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-white text-sm"></i>
                            </div>
                            <span class="text-gray-700 font-medium">Service client</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-white text-sm"></i>
                            </div>
                            <span class="text-gray-700 font-medium">Respect délais</span>
                        </div>
                    </div>
                </div>
                
                <!-- Colonne 2: Image configurable -->
                <div class="relative">
                    @if(!empty($homeConfig['about']['image']))
                        <div class="aspect-square rounded-2xl overflow-hidden shadow-2xl">
                            <img src="{{ asset($homeConfig['about']['image']) }}" 
                                 alt="{{ $homeConfig['about']['title'] ?? 'Qui Sommes-Nous' }}" 
                                 class="w-full h-full object-cover object-center mobile-responsive-img about-image-mobile"
                                 style="image-rendering: -webkit-optimize-contrast; image-rendering: crisp-edges; max-width: 100%; height: auto; display: block; width: 100%;"
                                 loading="lazy"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <div class="w-full h-full bg-gradient-to-br from-primary to-secondary rounded-2xl flex items-center justify-center" style="display: none;">
                                <div class="text-center text-white p-8">
                                    <i class="fas fa-building text-6xl mb-4"></i>
                                    <h3 class="text-2xl font-bold mb-2">{{ setting('company_name', 'Votre Entreprise') }}</h3>
                                    <p class="text-white/90">{{ setting('company_specialization', 'Travaux de Rénovation') }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="aspect-square bg-gradient-to-br from-primary to-secondary rounded-2xl flex items-center justify-center">
                            <div class="text-center text-white p-8">
                                <i class="fas fa-building text-6xl mb-4"></i>
                                <h3 class="text-2xl font-bold mb-2">{{ setting('company_name', 'Votre Entreprise') }}</h3>
                                <p class="text-white/90">{{ setting('company_specialization', 'Travaux de Rénovation') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
    @endif
