    <!-- À propos — Louis Hoffmann dans l'Oise -->
    <section class="py-20 bg-gray-50 overflow-hidden">
        <div class="site-shell">
            <div class="grid lg:grid-cols-2 gap-14 lg:gap-20 items-center">

                {{-- ── Colonne image / visuel ──────────────────── --}}
                <div class="relative order-2 lg:order-1">
                    @if(!empty($homeConfig['about']['image']))
                    <div class="relative rounded-3xl overflow-hidden shadow-2xl aspect-[4/3]">
                        <img src="{{ asset($homeConfig['about']['image']) }}"
                             alt="Louis Hoffmann — Élagueur dans l'Oise"
                             class="w-full h-full object-cover"
                             loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                    </div>
                    @else
                    {{-- Visuel déco si pas d'image --}}
                    <div class="relative rounded-3xl overflow-hidden shadow-2xl aspect-[4/3]"
                         style="background: linear-gradient(135deg, #0c2340 0%, #0f3d2e 100%);">
                        <div class="absolute inset-0 flex flex-col items-center justify-center p-8 text-center">
                            <i class="fas fa-tree text-7xl text-emerald-400 mb-5 opacity-80"></i>
                            <h3 class="text-2xl font-extrabold text-white">Louis Hoffmann</h3>
                            <p class="text-emerald-300 font-semibold mt-1">Élagage & Abattage</p>
                            <div class="mt-6 flex flex-wrap gap-2 justify-center">
                                @foreach(['Compiègne','Beauvais','Senlis','Chantilly','Creil','Verberie','Noyon','Clermont'] as $v)
                                <span class="bg-white/10 text-white/80 text-xs px-3 py-1 rounded-full">{{ $v }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Badge expérience flottant --}}
                    <div class="absolute -bottom-5 -right-5 bg-white rounded-2xl shadow-xl p-5 text-center border border-gray-100 hidden sm:block">
                        <div class="text-3xl font-extrabold" style="color:var(--primary-color);">15+</div>
                        <div class="text-gray-600 text-xs font-semibold mt-0.5">ans d'expérience<br>dans l'Oise</div>
                    </div>

                    {{-- Badge certification flottant --}}
                    <div class="absolute -top-5 -left-5 bg-emerald-500 rounded-2xl shadow-xl px-5 py-4 hidden sm:block">
                        <div class="flex items-center gap-2 text-white">
                            <i class="fas fa-shield-alt text-white/80"></i>
                            <div>
                                <p class="font-bold text-sm leading-none">Assuré &amp; certifié</p>
                                <p class="text-white/70 text-xs mt-0.5">Responsabilité civile</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Colonne texte ────────────────────────────── --}}
                <div class="order-1 lg:order-2">
                    <p class="text-sm font-bold uppercase tracking-widest mb-3" style="color:var(--primary-color);">
                        Qui sommes-nous ?
                    </p>
                    <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-6 leading-tight">
                        {{ $homeConfig['about']['title'] ?? 'Votre élagueur de confiance dans le département 60' }}
                    </h2>

                    @if(!empty($homeConfig['about']['content']))
                    <div class="prose prose-lg text-gray-600 leading-relaxed mb-8">
                        {!! nl2br(e($homeConfig['about']['content'])) !!}
                    </div>
                    @else
                    <div class="space-y-4 text-gray-600 leading-relaxed mb-8">
                        <p>
                            <strong class="text-gray-900">Louis Hoffmann – Élagage & Abattage</strong> est une entreprise
                            artisanale spécialisée dans l'entretien et la gestion des arbres dans tout le
                            <strong class="text-gray-900">département de l'Oise (60)</strong>. Basé à proximité de
                            Compiègne, nous couvrons l'intégralité du territoire picard, de Beauvais à Senlis,
                            de Chantilly à Noyon.
                        </p>
                        <p>
                            Avec plus de <strong class="text-gray-900">15 ans d'expérience</strong> dans la gestion
                            arboricole, notre équipe maîtrise toutes les techniques d'élagage, d'abattage dirigé,
                            de taille de haies et de broyage de souches. Chaque intervention est réalisée dans
                            le respect strict des normes de sécurité et de l'environnement.
                        </p>
                        <p>
                            Particuliers, collectivités ou entreprises de l'Oise : nous étudions chaque projet
                            sur mesure et vous proposons un <strong class="text-gray-900">devis gratuit sous 24h</strong>,
                            avec une intervention planifiée selon vos disponibilités.
                        </p>
                    </div>
                    @endif

                    {{-- Points forts --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach([
                            ['icon'=>'fas fa-shield-alt',      'color'=>'text-blue-600',   'bg'=>'bg-blue-50',   'text'=>'Assuré responsabilité civile'],
                            ['icon'=>'fas fa-hard-hat',        'color'=>'text-amber-600',  'bg'=>'bg-amber-50',  'text'=>'Équipement professionnel'],
                            ['icon'=>'fas fa-map-marker-alt',  'color'=>'text-violet-600', 'bg'=>'bg-violet-50', 'text'=>'Tout le département Oise (60)'],
                            ['icon'=>'fas fa-leaf',            'color'=>'text-emerald-600','bg'=>'bg-emerald-50','text'=>'Respect de l\'environnement'],
                            ['icon'=>'fas fa-file-invoice',    'color'=>'text-indigo-600', 'bg'=>'bg-indigo-50', 'text'=>'Devis gratuit en 24h'],
                            ['icon'=>'fas fa-users',           'color'=>'text-rose-600',   'bg'=>'bg-rose-50',   'text'=>'Artisans qualifiés'],
                        ] as $pt)
                        <div class="flex items-center gap-3 bg-white rounded-xl border border-gray-100 px-4 py-3 shadow-sm">
                            <div class="w-8 h-8 rounded-lg {{ $pt['bg'] }} flex items-center justify-center flex-shrink-0">
                                <i class="{{ $pt['icon'] }} {{ $pt['color'] }} text-sm"></i>
                            </div>
                            <span class="text-gray-800 text-sm font-semibold">{{ $pt['text'] }}</span>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-8 flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('form.step', 'propertyType') }}"
                           class="inline-flex items-center gap-2 text-white font-bold px-6 py-3.5 rounded-xl transition"
                           style="background:var(--primary-color);"
                           onmouseover="this.style.background='var(--secondary-color)';"
                           onmouseout="this.style.background='var(--primary-color)';">
                            <i class="fas fa-calculator"></i> Demander un devis
                        </a>
                        <a href="{{ route('contact') }}"
                           class="inline-flex items-center gap-2 font-bold px-6 py-3.5 rounded-xl border-2 transition"
                           style="border-color:var(--primary-color); color:var(--primary-color);"
                           onmouseover="this.style.background='var(--primary-color)'; this.style.color='#fff';"
                           onmouseout="this.style.background='transparent'; this.style.color='var(--primary-color)';">
                            <i class="fas fa-phone"></i> Nous contacter
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>
