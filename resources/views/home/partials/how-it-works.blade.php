    <!-- Comment ça marche — adapté élagage Oise -->
    <section class="py-20 bg-gray-50 border-y border-gray-100">
        <div class="site-shell">

            <div class="text-center mb-14">
                <p class="text-sm font-bold uppercase tracking-widest mb-3" style="color:var(--primary-color);">Simple & rapide</p>
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4">
                    Comment se déroule votre intervention ?
                </h2>
                <p class="text-gray-500 max-w-xl mx-auto text-lg">
                    Du premier contact à la fin du chantier, tout est transparent et organisé
                </p>
            </div>

            {{-- Ligne de progression desktop --}}
            <div class="relative">
                <div class="hidden lg:block absolute top-[3.25rem] left-[12.5%] right-[12.5%] h-0.5 bg-gradient-to-r pointer-events-none z-0"
                     style="background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));"></div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 relative z-10">
                    @php $steps = [
                        [
                            'n'     => 1,
                            'icon'  => 'fas fa-phone-alt',
                            'title' => 'Prise de contact',
                            'text'  => 'Appelez-nous ou remplissez le formulaire en ligne. Nous vous répondons sous 24h pour toute intervention dans l\'Oise (60).',
                            'color' => 'emerald',
                        ],
                        [
                            'n'     => 2,
                            'icon'  => 'fas fa-search-location',
                            'title' => 'Visite & devis gratuit',
                            'text'  => 'Nous nous déplaçons sur votre site (Compiègne, Beauvais, Senlis…) pour évaluer les arbres et établir un devis détaillé, gratuit et sans engagement.',
                            'color' => 'blue',
                        ],
                        [
                            'n'     => 3,
                            'icon'  => 'fas fa-calendar-check',
                            'title' => 'Planification',
                            'text'  => 'Nous convenons ensemble d\'une date d\'intervention adaptée à vos disponibilités et à la saison, en respectant les réglementations locales de l\'Oise.',
                            'color' => 'violet',
                        ],
                        [
                            'n'     => 4,
                            'icon'  => 'fas fa-hard-hat',
                            'title' => 'Intervention & nettoyage',
                            'text'  => 'Notre équipe réalise les travaux d\'élagage ou d\'abattage en toute sécurité, puis nettoie intégralement le chantier. Broyage des rémanents possible.',
                            'color' => 'amber',
                        ],
                    ];
                    $stepColors = [
                        'emerald' => ['bg'=>'bg-emerald-500','light'=>'bg-emerald-50','text'=>'text-emerald-600'],
                        'blue'    => ['bg'=>'bg-blue-500',   'light'=>'bg-blue-50',   'text'=>'text-blue-600'],
                        'violet'  => ['bg'=>'bg-violet-500', 'light'=>'bg-violet-50', 'text'=>'text-violet-600'],
                        'amber'   => ['bg'=>'bg-amber-500',  'light'=>'bg-amber-50',  'text'=>'text-amber-600'],
                    ]; @endphp

                    @foreach($steps as $step)
                    @php $sc = $stepColors[$step['color']]; @endphp
                    <div class="flex flex-col items-center text-center group">
                        {{-- Numéro rond --}}
                        <div class="relative mb-6">
                            <div class="w-14 h-14 rounded-full {{ $sc['bg'] }} flex items-center justify-center text-white font-extrabold text-xl shadow-lg group-hover:scale-110 transition-transform duration-300 z-10 relative">
                                {{ $step['n'] }}
                            </div>
                            {{-- Halo --}}
                            <div class="absolute inset-0 rounded-full {{ $sc['bg'] }} opacity-20 scale-150 group-hover:scale-[1.7] transition-transform duration-300"></div>
                        </div>

                        {{-- Carte --}}
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md p-6 w-full group-hover:-translate-y-1 transition-all duration-300 flex-1">
                            <div class="w-10 h-10 rounded-xl {{ $sc['light'] }} flex items-center justify-center mx-auto mb-4">
                                <i class="{{ $step['icon'] }} {{ $sc['text'] }}"></i>
                            </div>
                            <h3 class="font-extrabold text-gray-900 mb-2 text-base">{{ $step['title'] }}</h3>
                            <p class="text-gray-500 text-sm leading-relaxed">{{ $step['text'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- CTA bas --}}
            <div class="text-center mt-12">
                <a href="{{ route('form.step', 'propertyType') }}"
                   class="inline-flex items-center gap-2 text-white font-bold px-8 py-4 rounded-2xl shadow-lg transition-all hover:scale-[1.02]"
                   style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                    <i class="fas fa-calculator"></i>
                    Démarrer mon devis gratuit
                </a>
            </div>

        </div>
    </section>
