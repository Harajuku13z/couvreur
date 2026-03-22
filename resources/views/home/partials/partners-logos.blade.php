    <!-- Section Nos Partenaires : bloc intro + grille logos -->
    @php
        $p = $homeConfig['partners'] ?? [];
        $intro = $p['intro'] ?? [];
        $showIntro = !empty($intro['enabled']) && (!empty($intro['title']) || !empty($intro['body']) || !empty($intro['image']));
        $partnersEnabled = $p['enabled'] ?? false;
        $partners = $p['logos'] ?? [];
        $partnersTitle = $p['title'] ?? 'Nos Partenaires';
        $showLogos = $partnersEnabled && !empty($partners);
        $showPartnersSection = $showIntro || $showLogos;
    @endphp
    @if($showPartnersSection)
    <section class="py-14 md:py-20 bg-gray-50 dark:bg-gray-900/40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10">
            <div class="bg-white dark:bg-gray-800 rounded-2xl md:rounded-3xl shadow-lg border border-gray-100/90 dark:border-gray-700 px-5 py-10 md:px-10 md:py-12 lg:px-12">
                <!-- Titre commun -->
                <div class="text-center mb-10 md:mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-800 dark:text-gray-100 mb-2">{{ $partnersTitle }}</h2>
                    <div class="w-24 h-1 mx-auto rounded-full" style="background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));"></div>
                </div>

                @if($showIntro)
                <!-- Bloc spécial : texte à gauche, visuel à droite -->
                <div class="grid md:grid-cols-2 gap-10 lg:gap-14 items-center mb-0">
                    <div class="order-2 md:order-1 text-center md:text-left">
                        @if(!empty($intro['title']))
                        <h3 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-gray-100 mb-4">{{ $intro['title'] }}</h3>
                        @endif
                        @if(!empty($intro['body']))
                        <div class="text-gray-600 dark:text-gray-300 leading-relaxed whitespace-pre-line mb-6">{{ $intro['body'] }}</div>
                        @endif
                        @if(!empty($intro['link_url']))
                        <a href="{{ $intro['link_url'] }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl text-white font-semibold shadow-lg transition hover:opacity-95"
                           style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                            {{ $intro['link_label'] ?? 'En savoir plus' }}
                            <i class="fas fa-external-link-alt text-sm" aria-hidden="true"></i>
                        </a>
                        @endif
                    </div>
                    <div class="order-1 md:order-2 rounded-2xl overflow-hidden shadow-xl bg-gradient-to-br from-gray-100 to-gray-50 dark:from-gray-700 dark:to-gray-800 min-h-[200px] md:min-h-[260px] flex items-center justify-center p-6 md:p-8 ring-1 ring-gray-200/80 dark:ring-gray-600">
                        @if(!empty($intro['image']))
                        <img src="{{ asset($intro['image']) }}"
                             alt="{{ strip_tags($intro['title'] ?? 'Partenaires') }}"
                             class="w-full h-full max-h-[320px] md:max-h-[400px] object-contain object-center"
                             loading="lazy">
                        @else
                        <div class="text-gray-400 dark:text-gray-500 text-center text-sm">
                            <i class="fas fa-image text-5xl mb-3 block opacity-50"></i>
                            Ajoutez une image ou un logo dans l’admin (section Nos Partenaires)
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                @if($showLogos)
                <!-- Zone logos : grille alignée à gauche (plus de flex centré) -->
                <div class="@if($showIntro) mt-12 pt-10 border-t border-gray-200 dark:border-gray-600 @endif">
                    @if($showIntro)
                    <div class="mb-8 text-center md:text-left">
                        <p class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                            Ils nous font confiance
                        </p>
                        <p class="text-base text-gray-600 dark:text-gray-300 max-w-2xl">
                            Découvrez les organisations et marques avec lesquelles nous travaillons.
                        </p>
                    </div>
                    @endif

                    {{-- Grille responsive : colonnes égales, logos alignés en haut à gauche comme une galerie --}}
                    <ul class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-5 list-none p-0 m-0 w-full"
                        role="list"
                        style="justify-items: stretch;">
                        @foreach($partners as $partner)
                            @if(!empty($partner['logo']))
                            <li class="min-w-0">
                                <div class="partner-logo-container group h-full min-h-[7.5rem] sm:min-h-[8.5rem] md:min-h-[9.5rem] flex flex-col items-center justify-center rounded-2xl border border-gray-100 dark:border-gray-600 bg-gradient-to-b from-white to-gray-50/90 dark:from-gray-800 dark:to-gray-800/90 p-4 sm:p-5 shadow-sm transition-all duration-300 hover:shadow-md hover:border-[color:var(--primary-color)]/25 hover:-translate-y-0.5">
                                    @if(!empty($partner['url']))
                                    <a href="{{ $partner['url'] }}" target="_blank" rel="noopener noreferrer" class="flex flex-1 w-full flex-col items-center justify-center gap-2 rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[color:var(--primary-color)]">
                                        <span class="flex flex-1 w-full items-center justify-center min-h-[4rem] sm:min-h-[5rem]">
                                            <img
                                                src="{{ asset($partner['logo']) }}"
                                                alt="{{ $partner['name'] ?? 'Partenaire' }}"
                                                class="max-w-[85%] max-h-[4.5rem] sm:max-h-[5.5rem] md:max-h-24 w-auto object-contain object-center transition-all duration-300 opacity-95 group-hover:opacity-100 group-hover:scale-[1.02]"
                                                loading="lazy"
                                                onerror="this.style.display='none';">
                                        </span>
                                        @if(!empty($partner['name']))
                                        <span class="text-[11px] sm:text-xs font-medium text-center text-gray-500 dark:text-gray-400 leading-tight px-1 group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors line-clamp-2">{{ $partner['name'] }}</span>
                                        @endif
                                    </a>
                                    @else
                                    <div class="flex flex-1 w-full flex-col items-center justify-center gap-2">
                                        <span class="flex flex-1 w-full items-center justify-center min-h-[4rem] sm:min-h-[5rem]">
                                            <img
                                                src="{{ asset($partner['logo']) }}"
                                                alt="{{ $partner['name'] ?? 'Partenaire' }}"
                                                class="max-w-[85%] max-h-[4.5rem] sm:max-h-[5.5rem] md:max-h-24 w-auto object-contain object-center"
                                                loading="lazy"
                                                onerror="this.style.display='none';">
                                        </span>
                                        @if(!empty($partner['name']))
                                        <span class="text-[11px] sm:text-xs font-medium text-center text-gray-500 dark:text-gray-400 line-clamp-2 leading-tight px-1">{{ $partner['name'] }}</span>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>
    </section>
    @endif
