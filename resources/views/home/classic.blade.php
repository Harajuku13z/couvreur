{{-- ============================================================
     LANDING PAGE — Louis Hoffmann Élagage · Département 60
     Design global : un seul fichier cohérent, conversion first
     ============================================================ --}}

@php
    /* ─── Data ─── */
    $servicesData = \App\Models\Setting::get('services', '[]');
    $allServices  = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
    if(!is_array($allServices)) $allServices = [];
    $visibleServices = array_filter($allServices, fn($s) => is_array($s) && ($s['is_visible'] ?? true));
    $heroTitle  = $homeConfig['sections']['hero']['title']  ?? setting('hero_title', 'Élagueur professionnel dans l\'Oise (60)');
    $heroSub    = $homeConfig['sections']['hero']['subtitle'] ?? 'Élagage, abattage, taille de haies et broyage de souches — Compiègne, Beauvais, Senlis, Chantilly, Creil, Noyon';
    $phone      = setting('company_phone', '06 42 21 41 51');
    $phoneRaw   = setting('company_phone_raw', $phone);
@endphp

{{-- ╔══════════════════════════════════════════╗
     ║  1. HERO — plein écran, conversion max  ║
     ╚══════════════════════════════════════════╝ --}}
<section id="hero" class="relative min-h-screen flex items-center overflow-hidden"
         style="background: linear-gradient(140deg, #071c10 0%, #0d3b22 40%, #1a5c35 75%, #0f3d1f 100%);">

    {{-- Particules déco --}}
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="absolute -top-32 -right-32 w-[600px] h-[600px] rounded-full opacity-[.07]"
             style="background: radial-gradient(circle, #22c55e 0%, transparent 70%);"></div>
        <div class="absolute bottom-0 -left-48 w-[500px] h-[500px] rounded-full opacity-[.06]"
             style="background: radial-gradient(circle, #10b981 0%, transparent 70%);"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[900px] h-[900px] rounded-full opacity-[.03]"
             style="background: radial-gradient(circle, #86efac 0%, transparent 60%);"></div>
        {{-- Grille subtile --}}
        <div class="absolute inset-0 opacity-[.04]"
             style="background-image: linear-gradient(rgba(255,255,255,.6) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.6) 1px, transparent 1px); background-size: 60px 60px;"></div>
    </div>

    {{-- Image hero si disponible --}}
    @if(setting('hero_image'))
    <div class="absolute inset-0">
        <img src="{{ asset(setting('hero_image')) }}" alt="Élagage Oise" class="w-full h-full object-cover opacity-20">
        <div class="absolute inset-0" style="background: linear-gradient(140deg, rgba(7,28,16,.92) 0%, rgba(13,59,34,.85) 50%, rgba(26,92,53,.75) 100%);"></div>
    </div>
    @endif

    <div class="site-shell relative z-10 py-28 md:py-36 lg:py-44">
        <div class="grid lg:grid-cols-2 gap-16 items-center">

            {{-- Colonne gauche --}}
            <div>
                {{-- Badge top --}}
                <div class="inline-flex items-center gap-2.5 bg-white/10 border border-white/20 backdrop-blur-sm rounded-full px-4 py-2 mb-8">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-white/90 text-sm font-semibold tracking-wide">Élagueur certifié · Département 60 (Oise)</span>
                </div>

                {{-- H1 --}}
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white leading-[1.08] mb-6 tracking-tight">
                    {!! nl2br(e($heroTitle)) !!}
                </h1>

                <p class="text-white/75 text-lg md:text-xl leading-relaxed mb-10 max-w-lg">
                    {{ $heroSub }}
                </p>

                {{-- CTAs --}}
                <div class="flex flex-wrap gap-4 mb-12">
                    <a href="{{ route('form.step', 'propertyType') }}"
                       class="group inline-flex items-center gap-3 text-white font-bold text-lg px-8 py-4 rounded-2xl shadow-2xl hover:scale-[1.03] transition-all duration-200"
                       style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);"
                       onclick="if(typeof trackFormClick==='function')trackFormClick('{{ request()->url() }}')">
                        <i class="fas fa-calculator group-hover:rotate-6 transition-transform"></i>
                        Devis gratuit en ligne
                        <i class="fas fa-arrow-right text-sm group-hover:translate-x-1 transition-transform"></i>
                    </a>
                    <a href="tel:{{ $phoneRaw }}"
                       class="inline-flex items-center gap-3 text-white font-bold text-lg px-8 py-4 rounded-2xl border-2 border-white/30 hover:bg-white/10 hover:border-white/50 transition-all duration-200"
                       onclick="if(typeof trackPhoneCall==='function')trackPhoneCall('{{ $phoneRaw }}','hero')">
                        <i class="fas fa-phone animate-pulse text-emerald-300"></i>
                        {{ $phone }}
                    </a>
                </div>

                {{-- Mini stats --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @foreach([
                        ['n'=>'60+', 'label'=>'Communes Oise'],
                        ['n'=>'500+','label'=>'Chantiers réalisés'],
                        ['n'=>'15+', 'label'=>'Ans d\'expérience'],
                        ['n'=>'4.9★','label'=>'Note clients'],
                    ] as $stat)
                    <div class="bg-white/08 border border-white/12 rounded-xl px-4 py-3 text-center backdrop-blur-sm">
                        <div class="text-2xl font-black text-white mb-0.5">{{ $stat['n'] }}</div>
                        <div class="text-white/55 text-xs font-medium">{{ $stat['label'] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Colonne droite — carte flottante --}}
            <div class="hidden lg:flex justify-end">
                <div class="relative w-[380px]">
                    {{-- Carte principale --}}
                    <div class="bg-white/95 backdrop-blur-md rounded-3xl shadow-2xl p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-white"
                                 style="background: var(--primary-color);">
                                <i class="fas fa-map-marked-alt"></i>
                            </div>
                            <div>
                                <div class="font-extrabold text-gray-900 text-base">Zone d'intervention</div>
                                <div class="text-xs text-gray-500">Département Oise (60)</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2.5 mb-6">
                            @foreach([
                                ['Compiègne','60200','fas fa-tree'],
                                ['Beauvais','60000','fas fa-tree'],
                                ['Senlis','60300','fas fa-tree'],
                                ['Chantilly','60500','fas fa-tree'],
                                ['Creil','60100','fas fa-tree'],
                                ['Noyon','60400','fas fa-tree'],
                            ] as $city)
                            <div class="flex items-center gap-2 bg-gray-50 rounded-xl px-3 py-2.5">
                                <i class="{{ $city[2] }} text-xs" style="color:var(--primary-color);"></i>
                                <div>
                                    <div class="text-sm font-bold text-gray-800 leading-none">{{ $city[0] }}</div>
                                    <div class="text-xs text-gray-400">{{ $city[1] }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <a href="{{ route('form.step', 'propertyType') }}"
                           class="block w-full text-center text-white font-bold py-3.5 rounded-xl transition-all hover:scale-[1.02] text-sm"
                           style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                            <i class="fas fa-calculator mr-2"></i>Obtenir un devis gratuit
                        </a>
                    </div>

                    {{-- Badge flottant --}}
                    <div class="absolute -top-5 -right-5 bg-emerald-500 text-white rounded-2xl px-5 py-3 shadow-xl">
                        <div class="text-center">
                            <div class="font-black text-lg leading-none">GRATUIT</div>
                            <div class="text-xs font-semibold opacity-90">Devis · 24h</div>
                        </div>
                    </div>

                    {{-- Badge étoiles --}}
                    <div class="absolute -bottom-5 -left-5 bg-white rounded-2xl px-5 py-3 shadow-xl border border-gray-100">
                        <div class="flex items-center gap-2">
                            <div class="flex">
                                @for($i=0;$i<5;$i++)<i class="fas fa-star text-amber-400 text-xs"></i>@endfor
                            </div>
                            <div class="text-sm font-black text-gray-900">4.9/5</div>
                        </div>
                        <div class="text-xs text-gray-500 mt-0.5">Avis clients vérifiés</div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Vague bas --}}
    <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" style="display:block;width:100%;height:80px;">
            <path d="M0,80 C360,20 1080,60 1440,0 L1440,80 Z" fill="#f9fafb"/>
        </svg>
    </div>
</section>

{{-- ╔══════════════════════════════════════════╗
     ║  2. BANDE DE CONFIANCE                  ║
     ╚══════════════════════════════════════════╝ --}}
<section class="bg-gray-50 py-10 border-b border-gray-100">
    <div class="site-shell">
        <div class="flex flex-wrap justify-center gap-x-12 gap-y-6 text-center">
            @foreach([
                ['fas fa-shield-alt','Artisan assuré & certifié'],
                ['fas fa-leaf','Déchets valorisés'],
                ['fas fa-clock','Réponse sous 24h'],
                ['fas fa-map-marker-alt','Tout le dép. 60'],
                ['fas fa-star','4.9★ sur Google'],
                ['fas fa-hand-holding-usd','Devis 100% gratuit'],
            ] as $trust)
            <div class="flex items-center gap-2.5 text-gray-600">
                <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0"
                     style="background:rgba(var(--primary-color-rgb,34,197,94),.1);">
                    <i class="{{ $trust[0] }} text-sm" style="color:var(--primary-color);"></i>
                </div>
                <span class="font-semibold text-sm">{{ $trust[1] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ╔══════════════════════════════════════════╗
     ║  3. SERVICES                            ║
     ╚══════════════════════════════════════════╝ --}}
@if(($homeConfig['sections']['services']['enabled'] ?? true) && count($visibleServices) > 0)
<section id="services" class="py-24 bg-gray-50">
    <div class="site-shell">

        <div class="text-center mb-16">
            <span class="inline-block text-xs font-black uppercase tracking-widest px-4 py-1.5 rounded-full mb-4"
                  style="background:rgba(var(--primary-color-rgb,34,197,94),.1); color:var(--primary-color);">
                Nos prestations
            </span>
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-black text-gray-900 mb-4 leading-tight">
                {{ $homeConfig['sections']['services']['title'] ?? 'Nos Services d\'Élagage' }}
            </h2>
            <p class="text-gray-500 text-lg max-w-2xl mx-auto">
                Élagage, abattage, taille de haies et broyage de souches dans tout le département Oise (60) — artisan qualifié, assuré, matériel professionnel.
            </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach(collect($visibleServices)->take($homeConfig['sections']['services']['limit'] ?? 6) as $service)
            <a href="{{ route('services.show', $service['slug']) }}"
               class="group relative bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 hover:shadow-2xl hover:border-transparent hover:-translate-y-1.5 transition-all duration-300 flex flex-col"
               onclick="trackServiceClick('{{ $service['name'] }}', '{{ request()->url() }}')">

                {{-- Image ou dégradé --}}
                @if(!empty($service['featured_image']))
                <div class="relative h-52 overflow-hidden flex-shrink-0">
                    <img src="{{ url($service['featured_image']) }}"
                         alt="{{ $service['name'] }}"
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                         loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>
                    <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                         style="background: linear-gradient(to top, rgba(var(--primary-color-rgb,34,197,94),.4), transparent 60%);"></div>
                </div>
                @else
                <div class="h-44 flex items-center justify-center relative overflow-hidden flex-shrink-0"
                     style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                    <i class="{{ $service['icon'] ?? 'fas fa-tree' }} text-7xl text-white/20"></i>
                    <i class="{{ $service['icon'] ?? 'fas fa-tree' }} text-5xl text-white absolute group-hover:scale-110 transition-transform duration-300"></i>
                </div>
                @endif

                {{-- Contenu --}}
                <div class="p-6 flex flex-col flex-1">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <h3 class="text-lg font-black text-gray-900 group-hover:transition-colors leading-tight" style="--tw-text-opacity:1;">
                            {{ $service['name'] }}
                        </h3>
                        <div class="w-9 h-9 rounded-xl flex-shrink-0 flex items-center justify-center mt-0.5"
                             style="background:rgba(var(--primary-color-rgb,34,197,94),.1);">
                            <i class="{{ $service['icon'] ?? 'fas fa-tree' }} text-sm" style="color:var(--primary-color);"></i>
                        </div>
                    </div>
                    <p class="text-gray-500 text-sm leading-relaxed flex-1 mb-5">
                        {{ $service['short_description'] ?? \Illuminate\Support\Str::limit($service['description'] ?? '', 110) }}
                    </p>
                    <div class="flex items-center gap-2 font-bold text-sm mt-auto" style="color:var(--primary-color);">
                        En savoir plus
                        <i class="fas fa-arrow-right text-xs group-hover:translate-x-1 transition-transform duration-200"></i>
                    </div>
                </div>

                {{-- Bar couleur en haut au hover --}}
                <div class="absolute top-0 left-0 right-0 h-1 origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300 rounded-t-2xl"
                     style="background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));"></div>
            </a>
            @endforeach
        </div>

        @if(count($visibleServices) > ($homeConfig['sections']['services']['limit'] ?? 6))
        <div class="text-center mt-12">
            <a href="{{ route('services.index') }}"
               class="inline-flex items-center gap-2.5 font-bold px-10 py-4 rounded-2xl border-2 transition-all hover:scale-[1.02]"
               style="border-color:var(--primary-color); color:var(--primary-color);"
               onmouseover="this.style.background='var(--primary-color)';this.style.color='#fff';"
               onmouseout="this.style.background='transparent';this.style.color='var(--primary-color)';">
                Voir tous nos services <i class="fas fa-arrow-right text-sm"></i>
            </a>
        </div>
        @endif

    </div>
</section>
@endif

{{-- ╔══════════════════════════════════════════╗
     ║  4. ABOUT — split 2 colonnes            ║
     ╚══════════════════════════════════════════╝ --}}
@if($homeConfig['sections']['about']['enabled'] ?? true)
<section class="py-24 bg-white overflow-hidden">
    <div class="site-shell">
        <div class="grid lg:grid-cols-2 gap-16 items-center">

            {{-- Visuel gauche --}}
            <div class="relative">
                {{-- Image principale --}}
                <div class="rounded-3xl overflow-hidden shadow-2xl"
                     style="background: linear-gradient(135deg, #071c10, #1a5c35);">
                    @if(setting('about_image') || setting('hero_image'))
                    <img src="{{ asset(setting('about_image') ?? setting('hero_image')) }}"
                         alt="Louis Hoffmann élagueur Oise"
                         class="w-full h-[420px] object-cover opacity-80">
                    @else
                    <div class="h-[420px] flex flex-col items-center justify-center gap-6">
                        <div class="text-white/20 text-[120px]"><i class="fas fa-tree"></i></div>
                        <div class="text-center">
                            <div class="text-white/60 font-bold text-xl">Louis Hoffmann</div>
                            <div class="text-white/40 text-sm">Élagueur professionnel · Oise</div>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Badge expérience --}}
                <div class="absolute -bottom-6 -right-6 bg-white rounded-2xl shadow-2xl px-7 py-5 border border-gray-100">
                    <div class="text-4xl font-black mb-1" style="color:var(--primary-color);">15+</div>
                    <div class="text-sm font-semibold text-gray-700 leading-tight">Ans d'expérience<br>dans l'Oise (60)</div>
                </div>

                {{-- Badge certifications --}}
                <div class="absolute -top-5 -left-5 bg-emerald-500 text-white rounded-2xl px-5 py-4 shadow-xl">
                    <i class="fas fa-certificate text-2xl mb-1 block text-center opacity-90"></i>
                    <div class="text-xs font-bold text-center leading-tight">Artisan<br>Certifié</div>
                </div>
            </div>

            {{-- Texte droite --}}
            <div>
                <span class="inline-block text-xs font-black uppercase tracking-widest px-4 py-1.5 rounded-full mb-5"
                      style="background:rgba(var(--primary-color-rgb,34,197,94),.1); color:var(--primary-color);">
                    À propos de nous
                </span>
                <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-5 leading-tight">
                    {{ $homeConfig['sections']['about']['title'] ?? 'Votre élagueur de confiance dans l\'Oise depuis 15 ans' }}
                </h2>
                <p class="text-gray-600 leading-relaxed mb-6">
                    {{ $homeConfig['sections']['about']['text'] ?? 'Louis Hoffmann est élagueur professionnel certifié, intervenant dans tout le département 60 (Oise) : Compiègne, Beauvais, Senlis, Chantilly, Creil, Noyon et toutes les communes environnantes. Avec plus de 15 ans d\'expérience, nous garantissons des interventions sécurisées, soignées et respectueuses de l\'environnement.' }}
                </p>

                {{-- Points forts --}}
                <div class="grid sm:grid-cols-2 gap-4 mb-8">
                    @foreach([
                        ['fas fa-tree','Élagage raisonné','Techniques respectueuses de la santé de l\'arbre'],
                        ['fas fa-hard-hat','Sécurité maximale','Équipements certifiés, équipe formée'],
                        ['fas fa-map-marker-alt','Tout le dép. 60','Oise et départements limitrophes'],
                        ['fas fa-recycle','Éco-responsable','Broyage et valorisation des déchets verts'],
                        ['fas fa-file-invoice','Devis détaillé','Transparent, gratuit et sans engagement'],
                        ['fas fa-phone-alt','Réactivité 24h','Réponse rapide à toute demande d\'urgence'],
                    ] as $pt)
                    <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 border border-gray-100">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"
                             style="background:rgba(var(--primary-color-rgb,34,197,94),.12);">
                            <i class="{{ $pt[0] }} text-sm" style="color:var(--primary-color);"></i>
                        </div>
                        <div>
                            <div class="font-bold text-gray-900 text-sm">{{ $pt[1] }}</div>
                            <div class="text-xs text-gray-500 mt-0.5 leading-relaxed">{{ $pt[2] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <a href="{{ route('contact') }}"
                   class="inline-flex items-center gap-2.5 text-white font-bold px-8 py-4 rounded-2xl shadow-lg hover:scale-[1.02] transition-all"
                   style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                    <i class="fas fa-envelope"></i> Nous contacter
                </a>
            </div>

        </div>
    </div>
</section>
@endif

{{-- ╔══════════════════════════════════════════╗
     ║  5. PROCESSUS — 4 étapes               ║
     ╚══════════════════════════════════════════╝ --}}
<section class="py-24 bg-gray-50 border-y border-gray-100">
    <div class="site-shell">

        <div class="text-center mb-16">
            <span class="inline-block text-xs font-black uppercase tracking-widest px-4 py-1.5 rounded-full mb-4"
                  style="background:rgba(var(--primary-color-rgb,34,197,94),.1); color:var(--primary-color);">
                Simple & rapide
            </span>
            <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-4">Comment ça se passe ?</h2>
            <p class="text-gray-500 text-lg max-w-xl mx-auto">Du premier contact à la fin du chantier, tout est transparent et organisé.</p>
        </div>

        <div class="relative">
            {{-- Ligne desktop --}}
            <div class="hidden lg:block absolute top-14 left-[12.5%] right-[12.5%] h-px"
                 style="background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));"></div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 relative z-10">
                @php $steps = [
                    ['n'=>1,'icon'=>'fas fa-phone-alt','label'=>'Prise de contact',
                     'text'=>'Appelez-nous ou remplissez notre formulaire. Réponse garantie sous 24h pour toute commune de l\'Oise (60).'],
                    ['n'=>2,'icon'=>'fas fa-search-location','label'=>'Visite & devis gratuit',
                     'text'=>'Nous nous déplaçons (Compiègne, Beauvais, Senlis…) pour évaluer et établir un devis détaillé — gratuit, sans engagement.'],
                    ['n'=>3,'icon'=>'fas fa-calendar-check','label'=>'Planification',
                     'text'=>'Date calée selon vos disponibilités et la saison, en respectant les réglementations locales de l\'Oise.'],
                    ['n'=>4,'icon'=>'fas fa-hard-hat','label'=>'Intervention & nettoyage',
                     'text'=>'Travaux sécurisés, nettoyage complet du chantier, broyage des rémanents si souhaité.'],
                ]; @endphp

                @foreach($steps as $i => $step)
                <div class="flex flex-col items-center text-center group">
                    <div class="relative mb-8">
                        <div class="w-28 h-28 rounded-2xl bg-white border border-gray-100 shadow-lg flex items-center justify-center group-hover:shadow-xl group-hover:-translate-y-1 transition-all duration-300">
                            <div class="text-center">
                                <div class="text-3xl font-black mb-1" style="color:var(--primary-color);">{{ $step['n'] }}</div>
                                <i class="{{ $step['icon'] }} text-gray-300 text-xl"></i>
                            </div>
                        </div>
                        <div class="absolute -top-2 -right-2 w-7 h-7 rounded-full border-2 border-white flex items-center justify-center text-white text-xs font-black shadow-md"
                             style="background:var(--primary-color);">
                            <i class="{{ $step['icon'] }} text-[9px]"></i>
                        </div>
                    </div>
                    <h3 class="font-black text-gray-900 mb-3 text-base">{{ $step['label'] }}</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">{{ $step['text'] }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <div class="text-center mt-14">
            <a href="{{ route('form.step', 'propertyType') }}"
               class="inline-flex items-center gap-2.5 text-white font-bold px-10 py-4 rounded-2xl shadow-lg hover:scale-[1.02] transition-all"
               style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                <i class="fas fa-calculator"></i> Démarrer mon devis gratuit
            </a>
        </div>

    </div>
</section>

{{-- ╔══════════════════════════════════════════╗
     ║  6. ZONE OISE — villes                 ║
     ╚══════════════════════════════════════════╝ --}}
<section class="py-24 bg-white">
    <div class="site-shell">

        <div class="text-center mb-14">
            <span class="inline-block text-xs font-black uppercase tracking-widest px-4 py-1.5 rounded-full mb-4"
                  style="background:rgba(var(--primary-color-rgb,34,197,94),.1); color:var(--primary-color);">
                Zone d'intervention
            </span>
            <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-4">
                Élagage & Abattage dans tout le Département 60
            </h2>
            <p class="text-gray-500 max-w-xl mx-auto text-lg">
                Nous intervenons dans l'ensemble des communes de l'Oise et des départements limitrophes.
            </p>
        </div>

        @php
            $displayCities = isset($favoriteCities) && $favoriteCities->count() > 0 ? $favoriteCities : collect([]);
            $oiseFallback = [
                ['Compiègne','60200'],['Beauvais','60000'],['Senlis','60300'],
                ['Chantilly','60500'],['Creil','60100'],['Noyon','60400'],
                ['Verberie','60410'],['Clermont','60600'],['Pont-Sainte-Maxence','60700'],
                ['Méru','60110'],['Liancourt','60140'],['Gouvieux','60270'],
                ['Lacroix-Saint-Ouen','60610'],['Margny-lès-Compiègne','60280'],
                ['Ribécourt-Dreslincourt','60170'],['Éstrées-Saint-Denis','60190'],
            ];
        @endphp

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-3">
            @if($displayCities->count() > 0)
                @foreach($displayCities as $city)
                <a href="{{ route('ads.index') }}?city={{ $city->slug }}"
                   class="group bg-gray-50 border border-gray-100 hover:border-transparent hover:shadow-lg rounded-2xl px-3 py-4 text-center transition-all duration-200 hover:-translate-y-1">
                    <div class="w-8 h-8 rounded-lg mx-auto mb-2 flex items-center justify-center"
                         style="background:rgba(var(--primary-color-rgb,34,197,94),.12);">
                        <i class="fas fa-map-pin text-xs" style="color:var(--primary-color);"></i>
                    </div>
                    <div class="text-xs font-bold text-gray-800 leading-tight">{{ $city->name }}</div>
                    @if($city->postal_code)
                    <div class="text-[10px] text-gray-400 mt-0.5">{{ $city->postal_code }}</div>
                    @endif
                </a>
                @endforeach
            @else
                @foreach($oiseFallback as $city)
                <div class="group bg-gray-50 border border-gray-100 hover:border-transparent hover:shadow-lg rounded-2xl px-3 py-4 text-center transition-all duration-200 hover:-translate-y-1 cursor-default">
                    <div class="w-8 h-8 rounded-lg mx-auto mb-2 flex items-center justify-center"
                         style="background:rgba(var(--primary-color-rgb,34,197,94),.12);">
                        <i class="fas fa-map-pin text-xs" style="color:var(--primary-color);"></i>
                    </div>
                    <div class="text-xs font-bold text-gray-800 leading-tight">{{ $city[0] }}</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">{{ $city[1] }}</div>
                </div>
                @endforeach
            @endif
        </div>

        <div class="mt-10 text-center">
            <p class="text-sm text-gray-500">
                <i class="fas fa-map-marker-alt mr-1" style="color:var(--primary-color);"></i>
                Et dans toutes les autres communes du département Oise (60) —
                <a href="{{ route('contact') }}" class="font-bold underline hover:no-underline" style="color:var(--primary-color);">
                    Contactez-nous
                </a>
                pour vérifier votre zone.
            </p>
        </div>

    </div>
</section>

{{-- ╔══════════════════════════════════════════╗
     ║  7. STATS COMPTEURS                    ║
     ╚══════════════════════════════════════════╝ --}}
<section class="py-20 relative overflow-hidden"
         style="background: linear-gradient(135deg, #071c10 0%, #0d3b22 50%, #1a5c35 100%);">
    <div class="absolute inset-0 opacity-[.04]"
         style="background-image: radial-gradient(rgba(255,255,255,.8) 1px, transparent 1px); background-size: 40px 40px;"></div>
    <div class="site-shell relative z-10">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            @foreach([
                ['500+','Chantiers réalisés','fas fa-hard-hat'],
                ['15+','Ans dans l\'Oise','fas fa-calendar-alt'],
                ['60+','Communes couvertes','fas fa-map-marker-alt'],
                ['4.9★','Note moyenne','fas fa-star'],
            ] as $stat)
            <div class="group">
                <div class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center text-white opacity-30 group-hover:opacity-60 transition-opacity"
                     style="background:rgba(255,255,255,.1);">
                    <i class="{{ $stat[2] }} text-xl"></i>
                </div>
                <div class="text-4xl md:text-5xl font-black text-white mb-2">{{ $stat[0] }}</div>
                <div class="text-white/50 text-sm font-medium">{{ $stat[1] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ╔══════════════════════════════════════════╗
     ║  8. TÉMOIGNAGES                        ║
     ╚══════════════════════════════════════════╝ --}}
@if(($homeConfig['sections']['reviews']['enabled'] ?? true) && !empty($reviews) && count($reviews) > 0)
<section class="py-24 bg-gray-50">
    <div class="site-shell">

        <div class="text-center mb-16">
            <span class="inline-block text-xs font-black uppercase tracking-widest px-4 py-1.5 rounded-full mb-4"
                  style="background:rgba(var(--primary-color-rgb,34,197,94),.1); color:var(--primary-color);">
                Témoignages
            </span>
            <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-4">
                {{ $homeConfig['sections']['reviews']['title'] ?? 'Ce que disent nos clients de l\'Oise' }}
            </h2>
            @if(isset($averageRating) && $averageRating > 0)
            <div class="flex items-center justify-center gap-3">
                <div class="flex gap-1">
                    @for($i=1;$i<=5;$i++)
                    <i class="fas fa-star text-lg {{ $i <= round($averageRating) ? 'text-amber-400' : 'text-gray-200' }}"></i>
                    @endfor
                </div>
                <span class="font-black text-gray-900 text-xl">{{ number_format($averageRating,1) }}/5</span>
                @if(isset($totalReviews))
                <span class="text-gray-400 text-sm">({{ $totalReviews }} avis vérifiés)</span>
                @endif
            </div>
            @endif
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($reviews->take($homeConfig['sections']['reviews']['limit'] ?? 6) as $review)
            <div class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 p-7 flex flex-col relative overflow-hidden">
                {{-- Quote déco --}}
                <div class="absolute top-4 right-4 text-6xl font-black leading-none text-gray-50 select-none">"</div>

                <div class="flex gap-1 mb-4">
                    @for($i=1;$i<=5;$i++)
                    <i class="fas fa-star text-sm {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-200' }}"></i>
                    @endfor
                </div>

                <p class="text-gray-700 leading-relaxed text-sm flex-1 mb-6 italic relative z-10">
                    "{{ \Illuminate\Support\Str::limit($review->review_text ?? 'Excellent travail, très professionnel.', 160) }}"
                </p>

                <div class="flex items-center gap-3 pt-5 border-t border-gray-100">
                    <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0">
                        @if($review->author_photo_url)
                        <img src="{{ $review->author_photo_url }}" alt="{{ $review->author_name }}" class="w-full h-full object-cover">
                        @else
                        <div class="w-full h-full flex items-center justify-center text-white font-black"
                             style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                            {{ strtoupper(substr($review->author_name ?? 'C', 0, 1)) }}
                        </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-gray-900 text-sm truncate">{{ $review->author_name }}</p>
                        <p class="text-gray-400 text-xs">
                            {{ $review->review_date ? \Carbon\Carbon::parse($review->review_date)->translatedFormat('F Y') : '' }}
                            @if($review->source && $review->source !== 'manual')
                            &bull;
                            @if(str_contains($review->source,'Google'))
                            <i class="fab fa-google text-blue-500"></i> Google
                            @else
                            {{ ucfirst($review->source) }}
                            @endif
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-12">
            <a href="{{ route('reviews.all') }}"
               class="inline-flex items-center gap-2.5 font-bold px-10 py-4 rounded-2xl border-2 transition-all hover:scale-[1.02]"
               style="border-color:var(--primary-color); color:var(--primary-color);"
               onmouseover="this.style.background='var(--primary-color)';this.style.color='#fff';"
               onmouseout="this.style.background='transparent';this.style.color='var(--primary-color)';">
                <i class="fas fa-star text-sm"></i> Lire tous les avis
            </a>
        </div>

    </div>
</section>
@endif

{{-- ╔══════════════════════════════════════════╗
     ║  9. FAQ ACCORDÉON                      ║
     ╚══════════════════════════════════════════╝ --}}
<section class="py-24 bg-white">
    <div class="site-shell max-w-3xl">

        <div class="text-center mb-16">
            <span class="inline-block text-xs font-black uppercase tracking-widest px-4 py-1.5 rounded-full mb-4"
                  style="background:rgba(var(--primary-color-rgb,34,197,94),.1); color:var(--primary-color);">
                Questions fréquentes
            </span>
            <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-4">Vos questions, nos réponses</h2>
            <p class="text-gray-500 text-lg">Tout ce que vous devez savoir avant de nous contacter.</p>
        </div>

        @php $faqs = [
            ['Intervenez-vous dans toute l\'Oise ?',
             'Oui, nous couvrons l\'ensemble du département 60 : Compiègne, Beauvais, Senlis, Chantilly, Creil, Noyon, Verberie, Clermont, Méru, Liancourt, Pont-Sainte-Maxence et toutes les communes alentour. Nous intervenons également dans les départements limitrophes (Aisne, Somme, Val-d\'Oise…).'],
            ['Combien coûte un élagage dans l\'Oise ?',
             'Le tarif dépend du type d\'arbre, de sa hauteur, de son accessibilité et des travaux à effectuer. C\'est pourquoi nous proposons un devis gratuit et sans engagement après visite sur place. Comptez en général entre 150€ et 800€ pour un élagage standard.'],
            ['Avez-vous les certifications nécessaires ?',
             'Absolument. Louis Hoffmann est artisan certifié, assuré en responsabilité civile professionnelle. Nous respectons toutes les normes de sécurité et les réglementations en vigueur dans le département 60.'],
            ['Que faites-vous des déchets verts ?',
             'Nous broyons les branches sur place si vous le souhaitez (le broyat peut servir de paillis). Les rémanents peuvent aussi être évacués et valorisés en composterie ou énergie verte.'],
            ['En quelle saison effectuer l\'élagage ?',
             'L\'élagage se pratique idéalement en fin d\'hiver (février-mars) ou en été (juillet-août). Certains arbres ont leurs propres rythmes. L\'abattage peut se faire toute l\'année. Nous vous conseillons lors du devis.'],
            ['Puis-je obtenir un devis rapidement ?',
             'Oui. Remplissez notre formulaire en ligne ou appelez-nous directement. Nous vous répondons sous 24h et nous déplaçons gratuitement pour établir un devis détaillé.'],
        ]; @endphp

        <div class="space-y-3" id="faq-container">
            @foreach($faqs as $i => $faq)
            <div class="border border-gray-100 rounded-2xl overflow-hidden shadow-sm">
                <button class="faq-btn w-full flex items-center justify-between gap-4 px-6 py-5 text-left bg-white hover:bg-gray-50 transition-colors"
                        onclick="toggleFaq({{ $i }})"
                        aria-expanded="false">
                    <span class="font-bold text-gray-900 text-base">{{ $faq[0] }}</span>
                    <div class="flex-shrink-0 w-8 h-8 rounded-full border-2 flex items-center justify-center transition-all duration-300 faq-icon-{{ $i }}"
                         style="border-color:var(--primary-color); color:var(--primary-color);">
                        <i class="fas fa-plus text-xs transition-transform duration-300 faq-plus-{{ $i }}"></i>
                    </div>
                </button>
                <div class="faq-body-{{ $i }} hidden px-6 pb-5">
                    <p class="text-gray-600 leading-relaxed text-sm border-t border-gray-100 pt-4">{{ $faq[1] }}</p>
                </div>
            </div>
            @endforeach
        </div>

    </div>
</section>

{{-- ╔══════════════════════════════════════════╗
     ║  10. CTA FINAL                         ║
     ╚══════════════════════════════════════════╝ --}}
@if($homeConfig['sections']['cta']['enabled'] ?? true)
<section class="relative py-0 overflow-hidden"
         style="background: linear-gradient(140deg, #071c10 0%, #0d3b22 50%, #1a5c35 100%);">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-0 right-0 w-[500px] h-[500px] rounded-full opacity-[.08]"
             style="background: radial-gradient(circle, #22c55e, transparent 70%); transform: translate(30%,-30%);"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] rounded-full opacity-[.07]"
             style="background: radial-gradient(circle, #10b981, transparent 70%); transform: translate(-30%,30%);"></div>
        <div class="absolute inset-0 opacity-[.03]"
             style="background-image: linear-gradient(rgba(255,255,255,.8) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.8) 1px, transparent 1px); background-size: 60px 60px;"></div>
    </div>

    <div class="site-shell relative z-10 py-24">
        <div class="max-w-4xl mx-auto text-center">

            <div class="mb-8">
                <span class="inline-flex items-center gap-2 bg-white/10 border border-white/20 text-white text-sm font-bold px-6 py-2.5 rounded-full">
                    <i class="fas fa-map-marker-alt text-emerald-400 text-xs animate-pulse"></i>
                    Département 60 — Oise · Picardie
                </span>
            </div>

            <h2 class="text-4xl md:text-6xl font-black text-white mb-6 leading-tight drop-shadow-2xl">
                {{ $homeConfig['sections']['cta']['title'] ?? 'Besoin d\'un élagueur dans l\'Oise ?' }}
            </h2>

            <p class="text-xl text-white/70 mb-12 max-w-2xl mx-auto leading-relaxed">
                Compiègne, Beauvais, Senlis, Chantilly, Creil, Noyon… nous intervenons partout dans le 60.
                Devis gratuit, réponse sous 24h, artisan certifié et assuré.
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-14">
                <a href="{{ route('form.step', 'propertyType') }}"
                   class="inline-flex items-center justify-center gap-3 bg-white font-black text-lg px-10 py-5 rounded-2xl shadow-2xl hover:bg-gray-50 hover:scale-[1.03] transition-all"
                   style="color:var(--primary-color);"
                   onclick="if(typeof trackFormClick==='function')trackFormClick('{{ request()->url() }}')">
                    <i class="fas fa-calculator"></i>
                    Devis gratuit en ligne
                </a>
                <a href="tel:{{ $phoneRaw }}"
                   class="inline-flex items-center justify-center gap-3 border-2 border-white/50 text-white font-bold text-lg px-10 py-5 rounded-2xl hover:bg-white/10 hover:border-white/80 transition-all"
                   onclick="if(typeof trackPhoneCall==='function')trackPhoneCall('{{ $phoneRaw }}','cta')">
                    <i class="fas fa-phone text-emerald-300"></i>
                    {{ $phone }}
                </a>
            </div>

            <div class="flex flex-wrap justify-center gap-8 text-white/50 text-sm">
                @foreach(['Devis 100% gratuit','Sans engagement','Réponse sous 24h','Artisan certifié','Tout le dépt. 60'] as $g)
                <span class="flex items-center gap-2">
                    <i class="fas fa-check text-emerald-400 text-xs"></i> {{ $g }}
                </span>
                @endforeach
            </div>

        </div>
    </div>
</section>
@endif

{{-- Sections optionnelles gardées via includes --}}
@include('home.partials.ecology-financing')
@include('home.partials.portfolio')
@include('home.partials.featured-partner')
@include('home.partials.partners-logos')
@include('home.partials.scripts')

{{-- FAQ JS --}}
<script>
function toggleFaq(i) {
    const body = document.querySelector('.faq-body-' + i);
    const icon = document.querySelector('.faq-plus-' + i);
    const iconWrap = document.querySelector('.faq-icon-' + i);
    const btn = iconWrap.closest('button');
    const isOpen = !body.classList.contains('hidden');

    // Ferme tous
    document.querySelectorAll('[class*="faq-body-"]').forEach(b => b.classList.add('hidden'));
    document.querySelectorAll('[class*="faq-plus-"]').forEach(ic => ic.style.transform = '');
    document.querySelectorAll('button[aria-expanded]').forEach(b => b.setAttribute('aria-expanded','false'));

    if(!isOpen) {
        body.classList.remove('hidden');
        icon.style.transform = 'rotate(45deg)';
        btn.setAttribute('aria-expanded', 'true');
    }
}

function trackServiceClick(name, page) {
    fetch('/api/track-service-click?service=' + encodeURIComponent(name), { method: 'GET' }).catch(()=>{});
}
</script>
