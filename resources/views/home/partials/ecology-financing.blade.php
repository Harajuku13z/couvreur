    <!-- Sections Écologie et Aide Financière -->
    @php
        $ecoVisible = ($homeConfig['ecology']['enabled'] ?? false) && !empty(trim((string) ($homeConfig['ecology']['content'] ?? '')));
        $finVisible = ($homeConfig['financing']['enabled'] ?? false) && !empty(trim((string) ($homeConfig['financing']['content'] ?? '')));
        $ecoFinBoth = $ecoVisible && $finVisible;
        $soloFullWidth = ($ecoVisible || $finVisible) && !$ecoFinBoth;
    @endphp
    @if($ecoVisible || $finVisible)
    <section class="py-20 bg-gray-50 dark:bg-slate-950">
        <div class="site-shell">
            <div @class([
                'grid gap-8',
                'lg:grid-cols-2' => $ecoFinBoth,
                'grid-cols-1' => !$ecoFinBoth,
            ])>
                <!-- Section Écologie -->
                @if($ecoVisible)
                <div @class([
                    'group relative overflow-hidden bg-gradient-to-br from-green-600 to-emerald-700 rounded-3xl p-8 text-white shadow-2xl',
                    'lg:p-12 xl:p-14' => $soloFullWidth,
                ])>
                    <!-- Effet de brillance -->
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-green-400 to-emerald-300"></div>

                    <div class="relative z-10">
                        <div @class([
                            'flex items-center mb-6',
                            'flex-col sm:flex-row sm:items-start gap-6 sm:gap-8' => $soloFullWidth,
                        ])>
                            <div @class([
                                'w-20 h-20 bg-white/20 rounded-2xl flex items-center justify-center shadow-lg shrink-0',
                                'sm:w-24 sm:h-24 sm:mr-0' => $soloFullWidth,
                                'mr-6' => !$soloFullWidth,
                            ])>
                                <i class="fas fa-leaf text-white text-3xl {{ $soloFullWidth ? 'sm:text-4xl' : '' }}"></i>
                            </div>
                            <div class="min-w-0 {{ $soloFullWidth ? 'text-center sm:text-left flex-1' : '' }}">
                                <h3 @class([
                                    'font-bold mb-2',
                                    'text-3xl lg:text-4xl' => $soloFullWidth,
                                    'text-3xl' => !$soloFullWidth,
                                ]) style="text-shadow: 2px 2px 4px rgba(0,0,0,0.8);">
                                    {{ $homeConfig['ecology']['title'] ?? 'Notre Engagement Écologique' }}
                                </h3>
                                <div class="w-16 h-1 bg-green-300 rounded-full {{ $soloFullWidth ? 'mx-auto sm:mx-0' : '' }}"></div>
                            </div>
                        </div>

                        <div @class([
                            'text-white/95 mb-8 text-lg leading-relaxed font-medium',
                            'lg:text-xl lg:leading-relaxed max-w-4xl' => $soloFullWidth,
                        ]) style="text-shadow: 1px 1px 3px rgba(0,0,0,0.7);">
                            {!! nl2br(e($homeConfig['ecology']['content'])) !!}
                        </div>
                        @php
                            $ecoBadges = $homeConfig['ecology']['badges'] ?? [];
                            $showEcoMat = (bool) ($ecoBadges['materiaux_recycles'] ?? true);
                            $showEcoEn = (bool) ($ecoBadges['energies_vertes'] ?? true);
                            $ecoN = (int) $showEcoMat + (int) $showEcoEn;
                            if ($ecoN <= 1) {
                                $ecoGrid = 'grid-cols-1 ' . ($soloFullWidth ? 'max-w-md' : 'max-w-sm') . ' mx-auto sm:mx-0';
                            } else {
                                $ecoGrid = $soloFullWidth
                                    ? 'grid-cols-1 sm:grid-cols-2 xl:grid-cols-2 max-w-3xl xl:max-w-4xl'
                                    : 'grid-cols-2';
                            }
                        @endphp
                        @if($ecoN > 0)
                        <div class="grid {{ $ecoGrid }} gap-4">
                            @if($showEcoMat)
                            <div class="bg-white/25 backdrop-blur-sm rounded-xl p-6 text-center shadow-lg hover:bg-white/35 transition-all duration-300">
                                <div class="text-4xl font-bold mb-3" aria-hidden="true">♻️</div>
                                <div class="text-sm font-bold" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">Matériaux recyclés</div>
                            </div>
                            @endif
                            @if($showEcoEn)
                            <div class="bg-white/25 backdrop-blur-sm rounded-xl p-6 text-center shadow-lg hover:bg-white/35 transition-all duration-300">
                                <div class="text-4xl font-bold mb-3" aria-hidden="true">🌱</div>
                                <div class="text-sm font-bold" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">Énergies vertes</div>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>

                    <!-- Motif décoratif -->
                    <div class="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                    <div class="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                </div>
                @endif

                <!-- Section Aide Financière -->
                @if($finVisible)
                <div @class([
                    'group relative overflow-hidden bg-gradient-to-br from-yellow-600 to-orange-600 rounded-3xl p-8 text-white shadow-2xl',
                    'lg:p-12 xl:p-14' => $soloFullWidth,
                ])>
                    <!-- Effet de brillance -->
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-yellow-400 to-orange-300"></div>

                    <div class="relative z-10">
                        <div @class([
                            'flex items-center mb-6',
                            'flex-col sm:flex-row sm:items-start gap-6 sm:gap-8' => $soloFullWidth,
                        ])>
                            <div @class([
                                'w-20 h-20 bg-white/20 rounded-2xl flex items-center justify-center shadow-lg shrink-0',
                                'sm:w-24 sm:h-24 sm:mr-0' => $soloFullWidth,
                                'mr-6' => !$soloFullWidth,
                            ])>
                                <i class="fas fa-euro-sign text-white text-3xl {{ $soloFullWidth ? 'sm:text-4xl' : '' }}"></i>
                            </div>
                            <div class="min-w-0 {{ $soloFullWidth ? 'text-center sm:text-left flex-1' : '' }}">
                                <h3 @class([
                                    'font-bold mb-2',
                                    'text-3xl lg:text-4xl' => $soloFullWidth,
                                    'text-3xl' => !$soloFullWidth,
                                ]) style="text-shadow: 2px 2px 4px rgba(0,0,0,0.8);">
                                    {{ $homeConfig['financing']['title'] ?? 'Aides et Financements Disponibles' }}
                                </h3>
                                <div class="w-16 h-1 bg-yellow-300 rounded-full {{ $soloFullWidth ? 'mx-auto sm:mx-0' : '' }}"></div>
                            </div>
                        </div>

                        <div @class([
                            'text-white/95 mb-8 text-lg leading-relaxed font-medium',
                            'lg:text-xl lg:leading-relaxed max-w-4xl' => $soloFullWidth,
                        ]) style="text-shadow: 1px 1px 3px rgba(0,0,0,0.7);">
                            {!! nl2br(e($homeConfig['financing']['content'])) !!}
                        </div>
                        @php
                            $finBadges = $homeConfig['financing']['badges'] ?? [];
                            $showMpr = (bool) ($finBadges['maprimerenov'] ?? true);
                            $showCee = (bool) ($finBadges['certificats_cee'] ?? true);
                            $finN = (int) $showMpr + (int) $showCee;
                            if ($finN <= 1) {
                                $finGrid = 'grid-cols-1 ' . ($soloFullWidth ? 'max-w-md' : 'max-w-sm') . ' mx-auto sm:mx-0';
                            } else {
                                $finGrid = $soloFullWidth
                                    ? 'grid-cols-1 sm:grid-cols-2 xl:grid-cols-2 max-w-3xl xl:max-w-4xl'
                                    : 'grid-cols-2';
                            }
                        @endphp
                        @if($finN > 0)
                        <div class="grid {{ $finGrid }} gap-4">
                            @if($showMpr)
                            <div class="bg-white/25 backdrop-blur-sm rounded-xl p-6 text-center shadow-lg hover:bg-white/35 transition-all duration-300">
                                <div class="text-4xl font-bold mb-3" aria-hidden="true">🏠</div>
                                <div class="text-sm font-bold" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">MaPrimeRénov'</div>
                            </div>
                            @endif
                            @if($showCee)
                            <div class="bg-white/25 backdrop-blur-sm rounded-xl p-6 text-center shadow-lg hover:bg-white/35 transition-all duration-300">
                                <div class="text-4xl font-bold mb-3" aria-hidden="true">💰</div>
                                <div class="text-sm font-bold" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">Certificats CEE</div>
                            </div>
                            @endif
                        </div>
                        @endif
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
