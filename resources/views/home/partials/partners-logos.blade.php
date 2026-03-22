    <!-- Section Nos Partenaires : bloc intro (texte gauche / visuel droite) + grille logos -->
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
            <div class="bg-white dark:bg-gray-800 rounded-2xl md:rounded-3xl shadow-lg border border-gray-100/90 dark:border-gray-700 px-5 py-10 md:px-12 md:py-14">
                <!-- Titre commun -->
                <div class="text-center mb-10 md:mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-800 dark:text-gray-100 mb-2">{{ $partnersTitle }}</h2>
                    <div class="w-24 h-1 mx-auto rounded-full" style="background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));"></div>
                </div>

                @if($showIntro)
                <!-- Bloc spécial : texte à gauche, logo ou image à droite -->
                <div class="grid md:grid-cols-2 gap-10 lg:gap-14 items-center mb-12 md:mb-14">
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
                    <div class="order-1 md:order-2 rounded-2xl overflow-hidden shadow-xl bg-gray-100 dark:bg-gray-700 min-h-[200px] md:min-h-[260px] flex items-center justify-center p-6 md:p-8">
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
                <!-- Grille de logos (tailles augmentées) -->
                <div class="flex flex-wrap justify-center items-center gap-6 md:gap-12 lg:gap-16">
                    @foreach($partners as $partner)
                        @if(!empty($partner['logo']))
                        <div class="partner-logo-container w-[190px] sm:w-[240px] md:w-[300px] lg:w-[340px] h-28 md:h-40 lg:h-44 flex items-center justify-center p-4 md:p-6 bg-gray-50 dark:bg-gray-700/50 md:bg-white dark:md:bg-gray-700 rounded-xl md:rounded-2xl shadow-sm hover:shadow-md transition-shadow flex-shrink-0 border border-gray-100 dark:border-gray-600">
                            @if(!empty($partner['url']))
                            <a href="{{ $partner['url'] }}" target="_blank" rel="noopener noreferrer" class="w-full h-full flex items-center justify-center">
                                <img
                                    src="{{ asset($partner['logo']) }}"
                                    alt="{{ $partner['name'] ?? 'Partenaire' }}"
                                    class="max-w-full max-h-[6rem] md:max-h-[8.5rem] lg:max-h-[10rem] w-auto object-contain transition-all duration-300 opacity-100 hover:opacity-90"
                                    loading="lazy"
                                    onerror="this.style.display='none';">
                            </a>
                            @else
                            <img
                                src="{{ asset($partner['logo']) }}"
                                alt="{{ $partner['name'] ?? 'Partenaire' }}"
                                class="max-w-full max-h-[6rem] md:max-h-[8.5rem] lg:max-h-[10rem] w-auto object-contain opacity-100"
                                loading="lazy"
                                onerror="this.style.display='none';">
                            @endif
                        </div>
                        @endif
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </section>
    @endif
