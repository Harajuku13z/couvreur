    <!-- Sections Écologie et Aide Financière -->
    @if(($homeConfig['ecology']['enabled'] ?? false) || ($homeConfig['financing']['enabled'] ?? false))
    <section class="py-20 bg-gray-50">
        <div class="site-shell">
            <div class="grid lg:grid-cols-2 gap-8">
                <!-- Section Écologie (Gauche) -->
                @if(($homeConfig['ecology']['enabled'] ?? false) && !empty($homeConfig['ecology']['content']))
                <div class="group relative overflow-hidden bg-gradient-to-br from-green-600 to-emerald-700 rounded-3xl p-8 text-white shadow-2xl">
                    <!-- Effet de brillance -->
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-green-400 to-emerald-300"></div>
                    
                    <div class="relative z-10">
                        <div class="flex items-center mb-6">
                            <div class="w-20 h-20 bg-white/20 rounded-2xl flex items-center justify-center mr-6 shadow-lg">
                                <i class="fas fa-leaf text-white text-3xl"></i>
                            </div>
                            <div>
                                <h3 class="text-3xl font-bold mb-2" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.8);">
                                    {{ $homeConfig['ecology']['title'] ?? 'Notre Engagement Écologique' }}
                                </h3>
                                <div class="w-16 h-1 bg-green-300 rounded-full"></div>
                            </div>
                        </div>
                        
                        <div class="text-white/95 mb-8 text-lg leading-relaxed font-medium" style="text-shadow: 1px 1px 3px rgba(0,0,0,0.7);">
                            {!! nl2br(e($homeConfig['ecology']['content'])) !!}
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white/25 backdrop-blur-sm rounded-xl p-6 text-center shadow-lg hover:bg-white/35 transition-all duration-300">
                                <div class="text-4xl font-bold mb-3">♻️</div>
                                <div class="text-sm font-bold" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">Matériaux recyclés</div>
                            </div>
                            <div class="bg-white/25 backdrop-blur-sm rounded-xl p-6 text-center shadow-lg hover:bg-white/35 transition-all duration-300">
                                <div class="text-4xl font-bold mb-3">🌱</div>
                                <div class="text-sm font-bold" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">Énergies vertes</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Motif décoratif -->
                    <div class="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                    <div class="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                </div>
                @endif
                
                <!-- Section Aide Financière (Droite) -->
                @if(($homeConfig['financing']['enabled'] ?? false) && !empty($homeConfig['financing']['content']))
                <div class="group relative overflow-hidden bg-gradient-to-br from-yellow-600 to-orange-600 rounded-3xl p-8 text-white shadow-2xl">
                    <!-- Effet de brillance -->
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-yellow-400 to-orange-300"></div>
                    
                    <div class="relative z-10">
                        <div class="flex items-center mb-6">
                            <div class="w-20 h-20 bg-white/20 rounded-2xl flex items-center justify-center mr-6 shadow-lg">
                                <i class="fas fa-euro-sign text-white text-3xl"></i>
                            </div>
                            <div>
                                <h3 class="text-3xl font-bold mb-2" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.8);">
                                    {{ $homeConfig['financing']['title'] ?? 'Aides et Financements Disponibles' }}
                                </h3>
                                <div class="w-16 h-1 bg-yellow-300 rounded-full"></div>
                            </div>
                        </div>
                        
                        <div class="text-white/95 mb-8 text-lg leading-relaxed font-medium" style="text-shadow: 1px 1px 3px rgba(0,0,0,0.7);">
                            {!! nl2br(e($homeConfig['financing']['content'])) !!}
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white/25 backdrop-blur-sm rounded-xl p-6 text-center shadow-lg hover:bg-white/35 transition-all duration-300">
                                <div class="text-4xl font-bold mb-3">🏠</div>
                                <div class="text-sm font-bold" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">MaPrimeRénov'</div>
                            </div>
                            <div class="bg-white/25 backdrop-blur-sm rounded-xl p-6 text-center shadow-lg hover:bg-white/35 transition-all duration-300">
                                <div class="text-4xl font-bold mb-3">💰</div>
                                <div class="text-sm font-bold" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">Certificats CEE</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Motif décoratif -->
                    <div class="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                    <div class="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                </div>
                @endif
            </div>
        </div>
    </section>
    @endif
