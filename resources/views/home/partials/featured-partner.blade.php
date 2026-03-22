    <!-- Partenaire mis en avant (image gauche / texte droite) -->
    @php
        $pf = $homeConfig['partners']['featured'] ?? [];
        $showFeaturedPartner = !empty($pf['enabled']) && (!empty($pf['title']) || !empty($pf['body']) || !empty($pf['image']));
    @endphp
    @if($showFeaturedPartner)
    <section class="py-14 md:py-20 bg-white">
        <div class="site-shell">
            <div class="grid md:grid-cols-2 gap-10 lg:gap-14 items-center">
                <div class="rounded-2xl overflow-hidden shadow-xl bg-gray-100 min-h-[220px] md:min-h-[280px] flex items-center justify-center order-2 md:order-1">
                    @if(!empty($pf['image']))
                    <img src="{{ asset($pf['image']) }}"
                         alt="{{ strip_tags($pf['title'] ?? 'Partenaire') }}"
                         class="w-full h-full object-cover max-h-[420px] md:max-h-none"
                         loading="lazy">
                    @else
                    <div class="p-8 text-gray-400 text-center text-sm">
                        <i class="fas fa-image text-4xl mb-2 block opacity-50"></i>
                        Ajoutez une image dans la configuration de la page d’accueil
                    </div>
                    @endif
                </div>
                <div class="order-1 md:order-2 text-center md:text-left">
                    @if(!empty($pf['title']))
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-3">{{ $pf['title'] }}</h2>
                    @endif
                    @if(!empty($pf['subtitle']))
                    <p class="text-lg font-semibold mb-4" style="color: var(--primary-color);">{{ $pf['subtitle'] }}</p>
                    @endif
                    @if(!empty($pf['body']))
                    <div class="text-gray-600 leading-relaxed whitespace-pre-line mb-6 text-left">{{ $pf['body'] }}</div>
                    @endif
                    @if(!empty($pf['link_url']))
                    <a href="{{ $pf['link_url'] }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl text-white font-semibold shadow-lg transition hover:opacity-95"
                       style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                        {{ $pf['link_label'] ?? 'En savoir plus' }}
                        <i class="fas fa-external-link-alt text-sm" aria-hidden="true"></i>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </section>
    @endif
