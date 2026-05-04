    <!-- Pourquoi nous choisir — Oise (60) -->
    @if($homeConfig['sections']['why_choose_us']['enabled'] ?? true)
    <section class="py-20 bg-white">
        <div class="site-shell">

            <div class="text-center mb-14">
                <p class="text-sm font-bold uppercase tracking-widest mb-3" style="color:var(--primary-color);">Nos atouts</p>
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4">
                    {{ $homeConfig['sections']['why_choose_us']['title'] ?? 'Pourquoi choisir Louis Hoffmann dans l\'Oise ?' }}
                </h2>
                <p class="text-gray-500 max-w-2xl mx-auto text-lg">
                    De Compiègne à Beauvais, nous sommes l'élagueur de référence du département 60
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @php $cards = [
                    [
                        'icon'  => 'fas fa-map-marked-alt',
                        'color' => 'emerald',
                        'title' => 'Présence dans tout le 60',
                        'text'  => 'Compiègne, Beauvais, Senlis, Chantilly, Creil, Noyon, Verberie, Clermont… Nous intervenons dans plus de 60 communes de l\'Oise sans frais de déplacement supplémentaires.',
                    ],
                    [
                        'icon'  => 'fas fa-bolt',
                        'color' => 'amber',
                        'title' => 'Intervention rapide',
                        'text'  => 'Devis gratuit sous 24h, intervention planifiée selon vos disponibilités. En cas d\'urgence (arbre dangereux, chute de branche), nous nous déplaçons en priorité.',
                    ],
                    [
                        'icon'  => 'fas fa-shield-alt',
                        'color' => 'blue',
                        'title' => 'Garanties & assurances',
                        'text'  => 'Responsabilité civile professionnelle, matériel aux normes, équipements de protection. Tous nos travaux d\'élagage et d\'abattage sont couverts et sécurisés.',
                    ],
                    [
                        'icon'  => 'fas fa-tree',
                        'color' => 'green',
                        'title' => 'Expertise arboricole',
                        'text'  => 'Élagage, abattage dirigé, taille de haies, broyage de souches, démontage en hauteur… Notre expertise couvre toutes les essences d\'arbres de la région Hauts-de-France.',
                    ],
                    [
                        'icon'  => 'fas fa-leaf',
                        'color' => 'teal',
                        'title' => 'Respect de l\'environnement',
                        'text'  => 'Nous valorisons les déchets verts (broyage, compostage), préservons la faune et la flore locale, et intervenons dans le respect des réglementations environnementales de l\'Oise.',
                    ],
                    [
                        'icon'  => 'fas fa-euro-sign',
                        'color' => 'rose',
                        'title' => 'Tarifs transparents',
                        'text'  => 'Devis détaillé et gratuit avant chaque intervention. Pas de mauvaise surprise : le prix annoncé est le prix payé. Facilités de paiement disponibles.',
                    ],
                ]; @endphp

                @php $gradients = [
                    'emerald' => 'from-emerald-50 to-teal-50',
                    'amber'   => 'from-amber-50 to-yellow-50',
                    'blue'    => 'from-blue-50 to-indigo-50',
                    'green'   => 'from-green-50 to-emerald-50',
                    'teal'    => 'from-teal-50 to-cyan-50',
                    'rose'    => 'from-rose-50 to-pink-50',
                ];
                $icons = [
                    'emerald' => 'text-emerald-600',
                    'amber'   => 'text-amber-600',
                    'blue'    => 'text-blue-600',
                    'green'   => 'text-green-600',
                    'teal'    => 'text-teal-600',
                    'rose'    => 'text-rose-600',
                ];
                $bars = [
                    'emerald' => 'from-emerald-400 to-teal-500',
                    'amber'   => 'from-amber-400 to-yellow-500',
                    'blue'    => 'from-blue-400 to-indigo-500',
                    'green'   => 'from-green-400 to-emerald-500',
                    'teal'    => 'from-teal-400 to-cyan-500',
                    'rose'    => 'from-rose-400 to-pink-500',
                ]; @endphp

                @foreach($cards as $card)
                @php $g = $gradients[$card['color']]; $ic = $icons[$card['color']]; $b = $bars[$card['color']]; @endphp
                <div class="group relative bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 overflow-hidden flex flex-col">
                    {{-- Barre couleur top --}}
                    <div class="h-1 w-full bg-gradient-to-r {{ $b }}"></div>
                    <div class="p-7 flex flex-col flex-1">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br {{ $g }} flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
                            <i class="{{ $card['icon'] }} {{ $ic }} text-lg"></i>
                        </div>
                        <h3 class="text-lg font-extrabold text-gray-900 mb-3">{{ $card['title'] }}</h3>
                        <p class="text-gray-500 text-sm leading-relaxed flex-1">{{ $card['text'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>

        </div>
    </section>
    @endif
