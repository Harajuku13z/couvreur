{{-- ============================================================
     LANDING PAGE v2 — Louis Hoffmann Élagage · Département 60
     Design : éditorial bold, dark sections, bento grid, zero cliché
     ============================================================ --}}

@php
    $servicesRaw  = \App\Models\Setting::get('services', '[]');
    $allServices  = is_string($servicesRaw) ? json_decode($servicesRaw, true) : ($servicesRaw ?? []);
    if(!is_array($allServices)) $allServices = [];
    $svcList = array_values(array_filter($allServices, fn($s) => is_array($s) && ($s['is_visible'] ?? true)));

    $phone    = setting('company_phone', '06 42 21 41 51');
    $phoneRaw = setting('company_phone_raw', $phone);
    $heroTitle = $homeConfig['sections']['hero']['title'] ?? 'Élagueur & Abatteur professionnel dans l\'Oise (60)';

    // Image hero depuis la config DB
    $heroImg    = $homeConfig['hero']['background_image'] ?? null;
    $heroImgUrl = $heroImg ? asset(ltrim($heroImg, '/')) : null;
@endphp

<style>
/* ── Global page ── */
.lp { font-family: inherit; }
.lp-shell { max-width: 1280px; margin: 0 auto; padding: 0 1.5rem; }
@media(min-width:768px){ .lp-shell{ padding: 0 2.5rem; } }
@media(min-width:1280px){ .lp-shell{ padding: 0 3rem; } }

/* ── Marquee ── */
@keyframes marquee { from{ transform: translateX(0); } to{ transform: translateX(-50%); } }
.marquee-track { display:flex; width: max-content; animation: marquee 22s linear infinite; }
.marquee-track:hover { animation-play-state: paused; }

/* ── Counters ── */
.lp-counter { font-size: clamp(3.5rem, 8vw, 7rem); font-weight: 900; line-height: 1; }

/* ── Service bento card hover ── */
.svc-card { transition: transform .3s ease, box-shadow .3s ease; }
.svc-card:hover { transform: translateY(-6px); }
.svc-card-img { transition: transform .6s ease; }
.svc-card:hover .svc-card-img { transform: scale(1.06); }

/* ── Reveal animation ── */
@keyframes fadeUp { from{ opacity:0; transform:translateY(28px); } to{ opacity:1; transform:translateY(0); } }
.reveal { animation: fadeUp .65s ease both; }
.reveal-2 { animation-delay: .1s; }
.reveal-3 { animation-delay: .2s; }
.reveal-4 { animation-delay: .3s; }

/* ── Diagonal separator ── */
.clip-diag { clip-path: polygon(0 0, 100% 0, 100% 88%, 0 100%); }
.clip-diag-rev { clip-path: polygon(0 6%, 100% 0, 100% 100%, 0 100%); }

/* ── Big number accent ── */
.bg-number {
    position:absolute; font-size: clamp(8rem, 20vw, 18rem);
    font-weight: 900; line-height: 1; opacity: .04;
    pointer-events: none; user-select: none;
    top: 50%; transform: translateY(-50%); right: -2rem;
    color: currentColor;
}

/* ── Checklist ── */
.lp-check li { display:flex; gap:.75rem; align-items:flex-start; padding:.5rem 0; }
.lp-check li::before {
    content:''; display:inline-flex; flex-shrink:0;
    width:1.25rem; height:1.25rem; border-radius:.375rem; margin-top:.1rem;
    background: var(--primary-color);
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 12 9' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 4l3.5 3.5L11 1' stroke='white' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-size: 60% 60%; background-repeat: no-repeat; background-position: center;
}

/* ── FAQ ── */
.faq-item details[open] summary { color: var(--primary-color); }
.faq-item details summary { cursor: pointer; list-style: none; }
.faq-item details summary::-webkit-details-marker { display:none; }
.faq-icon { transition: transform .25s ease; }
.faq-item details[open] .faq-icon { transform: rotate(45deg); }
</style>

<div class="lp">

{{-- ╔══════════════════════════════════════════════════════
     § 1. HERO — split sombre / lumineux, typographie massive
     ══════════════════════════════════════════════════════ --}}
<section class="relative overflow-hidden"
         style="background:#071c0e; min-height:100svh;">

    {{-- Photo de fond --}}
    @if($heroImgUrl)
    <div style="position:absolute;inset:0;z-index:0;">
        <img src="{{ $heroImgUrl }}" alt="Élagage dans l'Oise"
             style="width:100%;height:100%;object-fit:cover;object-position:center 30%;display:block;">
        <div style="position:absolute;inset:0;background:linear-gradient(160deg, rgba(5,18,8,.85) 0%, rgba(5,18,8,.65) 50%, rgba(5,18,8,.5) 100%);"></div>
    </div>
    @else
    {{-- Fallback sans photo --}}
    <div class="absolute inset-0 opacity-[.015] pointer-events-none"
         style="background-image:url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22400%22><filter id=%22n%22><feTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22/><feColorMatrix type=%22saturate%22 values=%220%22/></filter><rect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23n)%22 opacity=%221%22/></svg>');"></div>
    <div class="absolute top-0 right-0 w-[700px] h-[700px] rounded-full pointer-events-none"
         style="background:radial-gradient(circle, rgba(34,197,94,.18) 0%, transparent 65%); transform:translate(30%, -30%);"></div>
    @endif

    <div class="lp-shell relative z-10 flex flex-col justify-center" style="min-height:100svh; padding-top:7rem; padding-bottom:5rem; z-index:10;">
        <div class="grid lg:grid-cols-[1fr_420px] gap-12 xl:gap-20 items-center">

            {{-- Texte principal --}}
            <div>
                {{-- Tag --}}
                <div class="flex items-center gap-3 mb-8">
                    <div class="flex items-center gap-1.5">
                        <span class="block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="block w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                        <span class="block w-1 h-1 rounded-full bg-emerald-800"></span>
                    </div>
                    <span class="text-emerald-400 text-xs font-bold uppercase tracking-[.18em]">Élagueur certifié · Département 60 · Oise</span>
                </div>

                {{-- H1 massif --}}
                <h1 class="reveal text-white font-black leading-[.95] tracking-tight mb-8"
                    style="font-size: clamp(2.8rem, 6.5vw, 5.5rem);">
                    {!! str_replace(['(', ')'], ['<span style="color:var(--primary-color);">(', ')</span>'], e($heroTitle)) !!}
                </h1>

                <p class="reveal reveal-2 text-white/55 text-lg md:text-xl leading-relaxed mb-10 max-w-xl">
                    Élagage, abattage, taille de haies et broyage de souches —
                    <span class="text-white/80">Compiègne, Beauvais, Senlis, Chantilly, Creil, Noyon</span>
                    et toutes les communes de l'Oise.
                </p>

                {{-- CTAs --}}
                <div class="reveal reveal-3 flex flex-wrap gap-4 mb-14">
                    <a href="{{ route('form.step', 'propertyType') }}"
                       class="group relative inline-flex items-center gap-3 font-black text-lg px-9 py-5 rounded-2xl overflow-hidden transition-all hover:scale-[1.03] shadow-2xl"
                       style="background: var(--primary-color); color:#fff;">
                        <span class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity"
                              style="background:linear-gradient(135deg, var(--secondary-color), var(--primary-color));"></span>
                        <i class="fas fa-calculator relative z-10"></i>
                        <span class="relative z-10">Devis gratuit</span>
                        <i class="fas fa-arrow-right relative z-10 text-sm group-hover:translate-x-1 transition-transform"></i>
                    </a>
                    <a href="tel:{{ $phoneRaw }}"
                       class="inline-flex items-center gap-3 font-bold text-lg px-9 py-5 rounded-2xl border border-white/15 text-white hover:bg-white/08 hover:border-white/30 transition-all"
                       onclick="if(typeof trackPhoneCall==='function')trackPhoneCall('{{ $phoneRaw }}','hero')">
                        <span class="relative flex">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-20"></span>
                            <i class="fas fa-phone relative text-emerald-400"></i>
                        </span>
                        {{ $phone }}
                    </a>
                </div>

                {{-- Mini preuves sociales --}}
                <div class="reveal reveal-4 flex flex-wrap items-center gap-6 text-sm text-white/40 font-medium">
                    <div class="flex items-center gap-2">
                        <div class="flex">@for($i=0;$i<5;$i++)<i class="fas fa-star text-amber-400 text-xs"></i>@endfor</div>
                        <span class="text-white/70 font-bold">4.9</span>
                        <span>· 120+ avis</span>
                    </div>
                    <div class="w-px h-4 bg-white/15"></div>
                    <span><span class="text-white/70 font-bold">15+</span> ans d'expérience</span>
                    <div class="w-px h-4 bg-white/15"></div>
                    <span><span class="text-white/70 font-bold">60+</span> communes Oise</span>
                </div>
            </div>

            {{-- Panel flottant droit --}}
            <div class="hidden lg:block">
                <div class="rounded-3xl overflow-hidden border border-white/10"
                     style="background:rgba(255,255,255,.04); backdrop-filter:blur(16px);">
                    {{-- Top colored strip --}}
                    <div class="h-1.5" style="background:linear-gradient(90deg, var(--primary-color), var(--secondary-color), var(--accent-color, #10b981));"></div>

                    <div class="p-7">
                        <div class="text-white/50 text-xs font-bold uppercase tracking-widest mb-5">Zone d'intervention · Oise (60)</div>

                        <div class="flex flex-wrap gap-2 mb-5">
                            @foreach(['Compiègne','Beauvais','Senlis','Chantilly','Creil','Noyon','Verberie','Pont-Sainte-Maxence'] as $city)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold"
                                  style="background:rgba(var(--primary-color-rgb,34,197,94),.12);color:rgba(255,255,255,.8);border:1px solid rgba(var(--primary-color-rgb,34,197,94),.2);">
                                <i class="fas fa-map-marker-alt text-[9px]" style="color:var(--primary-color);"></i>
                                {{ $city }}
                            </span>
                            @endforeach
                        </div>

                        <div class="text-white/30 text-xs text-center mb-5">+ 60 autres communes du département</div>

                        <a href="{{ route('form.step', 'propertyType') }}"
                           class="block w-full text-center font-black py-4 rounded-xl text-sm transition-all hover:opacity-90"
                           style="background:var(--primary-color); color:#fff;">
                            <i class="fas fa-calculator mr-2"></i>Obtenir un devis gratuit
                        </a>
                    </div>
                </div>

                {{-- Badge dévis --}}
                <div class="mt-4 rounded-2xl p-4 border border-white/10 flex items-center gap-3"
                     style="background:rgba(255,255,255,.04);">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white flex-shrink-0"
                         style="background:rgba(34,197,94,.2);">
                        <i class="fas fa-check text-emerald-400 text-sm"></i>
                    </div>
                    <div>
                        <div class="text-white/90 font-bold text-sm">Réponse garantie sous 24h</div>
                        <div class="text-white/40 text-xs">Devis gratuit · Sans engagement</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Scroll indicator --}}
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 opacity-30">
        <span class="text-white text-xs font-medium uppercase tracking-widest">Scroll</span>
        <div class="w-px h-10 bg-white/40"></div>
    </div>
</section>

{{-- ╔══════════════════════════════════════════════════════
     § 2. MARQUEE CONFIANCE — ticker horizontal
     ══════════════════════════════════════════════════════ --}}
<div class="py-5 border-y overflow-hidden"
     style="background:#06150d; border-color:rgba(255,255,255,.06);">
    <div class="marquee-track">
        @php $items = [
            ['fas fa-shield-alt','Artisan assuré RC Pro'],
            ['fas fa-star','4.9★ sur Google'],
            ['fas fa-tree','500+ chantiers Oise'],
            ['fas fa-clock','Réponse sous 24h'],
            ['fas fa-map-marker-alt','60+ communes couvertes'],
            ['fas fa-award','15+ ans d\'expérience'],
            ['fas fa-hand-holding-usd','Devis 100% gratuit'],
            ['fas fa-recycle','Éco-responsable'],
            ['fas fa-shield-alt','Artisan assuré RC Pro'],
            ['fas fa-star','4.9★ sur Google'],
            ['fas fa-tree','500+ chantiers Oise'],
            ['fas fa-clock','Réponse sous 24h'],
            ['fas fa-map-marker-alt','60+ communes couvertes'],
            ['fas fa-award','15+ ans d\'expérience'],
            ['fas fa-hand-holding-usd','Devis 100% gratuit'],
            ['fas fa-recycle','Éco-responsable'],
        ]; @endphp
        @foreach($items as $item)
        <div class="flex items-center gap-2.5 px-8 flex-shrink-0">
            <i class="{{ $item[0] }} text-xs" style="color:var(--primary-color);"></i>
            <span class="text-white/50 text-sm font-semibold whitespace-nowrap">{{ $item[1] }}</span>
        </div>
        <div class="text-white/15 flex-shrink-0">·</div>
        @endforeach
    </div>
</div>

{{-- ╔══════════════════════════════════════════════════════
     § 3. SERVICES — Bento Grid éditorial
     ══════════════════════════════════════════════════════ --}}
@if(($homeConfig['sections']['services']['enabled'] ?? true) && count($svcList) > 0)
<section class="py-24 bg-gray-50">
    <div class="lp-shell">

        {{-- Header section --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-14">
            <div>
                <p class="text-xs font-black uppercase tracking-[.2em] mb-4"
                   style="color:var(--primary-color);">Nos prestations</p>
                <h2 class="font-black text-gray-900 leading-tight"
                    style="font-size:clamp(2rem,4.5vw,3.5rem);">
                    {{ $homeConfig['sections']['services']['title'] ?? 'Ce qu\'on fait' }}<br>
                    <span class="text-gray-300">dans l'Oise (60)</span>
                </h2>
            </div>
            <a href="{{ route('services.index') }}"
               class="inline-flex items-center gap-2 font-bold text-sm px-6 py-3 rounded-xl border-2 flex-shrink-0 transition-all hover:scale-[1.02]"
               style="border-color:var(--primary-color); color:var(--primary-color);"
               onmouseover="this.style.background='var(--primary-color)';this.style.color='#fff';"
               onmouseout="this.style.background='transparent';this.style.color='var(--primary-color)';">
                Voir tous les services <i class="fas fa-arrow-right text-xs"></i>
            </a>
        </div>

        {{-- Bento grid --}}
        @php $limit = min(count($svcList), $homeConfig['sections']['services']['limit'] ?? 6); @endphp
        <div class="grid gap-4"
             style="grid-template-columns: repeat(12, 1fr);">

            @foreach($svcList as $i => $svc)
            @if($i >= $limit) @break @endif
            @php
                // Alternance des tailles : grand-petit-petit / petit-grand / etc.
                $patterns = [
                    0 => 'col-span-12 md:col-span-7 row-span-2',
                    1 => 'col-span-12 md:col-span-5',
                    2 => 'col-span-12 md:col-span-5',
                    3 => 'col-span-12 md:col-span-5',
                    4 => 'col-span-12 md:col-span-7',
                    5 => 'col-span-12 md:col-span-12 lg:col-span-12',
                ];
                $colClass = $patterns[$i] ?? 'col-span-12 md:col-span-4';
                $isLarge  = ($i === 0 || $i === 4);
            @endphp
            <a href="{{ route('services.show', $svc['slug']) }}"
               class="svc-card group relative overflow-hidden rounded-3xl {{ $colClass }} block"
               style="min-height: {{ $isLarge ? '380px' : '220px' }};"
               onclick="if(typeof trackServiceClick==='function')trackServiceClick('{{ $svc['name'] }}','{{ request()->url() }}')">

                {{-- Background --}}
                @if(!empty($svc['featured_image']))
                <div style="position:absolute;inset:0;overflow:hidden;">
                    <img src="{{ url($svc['featured_image']) }}" alt="{{ $svc['name'] }}"
                         style="width:100%;height:100%;object-fit:cover;display:block;transition:transform .6s ease;"
                         class="svc-card-img" loading="lazy">
                    <div style="position:absolute;inset:0;background:linear-gradient(to top, rgba(6,21,13,.95) 0%, rgba(6,21,13,.5) 50%, rgba(6,21,13,.15) 100%);"></div>
                </div>
                @else
                <div class="absolute inset-0" style="background:linear-gradient(135deg, #06150d 0%, #0d3b22 60%, #1a5c35 100%);"></div>
                <i class="{{ $svc['icon'] ?? 'fas fa-tree' }} absolute text-[8rem] opacity-[.06] top-1/2 right-4 -translate-y-1/2" style="color:#fff;"></i>
                @endif

                {{-- Hover overlay --}}
                <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-400"
                     style="background:linear-gradient(135deg, rgba(var(--primary-color-rgb,34,197,94),.25) 0%, transparent 70%);"></div>

                {{-- Content --}}
                <div class="absolute inset-0 p-7 flex flex-col justify-end">
                    {{-- Icon badge --}}
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white mb-4 flex-shrink-0 self-start border border-white/20"
                         style="background:rgba(var(--primary-color-rgb,34,197,94),.25); backdrop-filter:blur(8px);">
                        <i class="{{ $svc['icon'] ?? 'fas fa-tree' }} text-sm"></i>
                    </div>
                    <h3 class="text-white font-black mb-2 leading-tight"
                        style="font-size:{{ $isLarge ? 'clamp(1.4rem,2.5vw,2rem)' : '1.1rem' }};">
                        {{ $svc['name'] }}
                    </h3>
                    @if($isLarge)
                    <p class="text-white/65 text-sm leading-relaxed mb-4 max-w-sm">
                        {{ \Illuminate\Support\Str::limit($svc['short_description'] ?? '', 120) }}
                    </p>
                    @endif
                    <div class="flex items-center gap-2 text-sm font-bold opacity-0 group-hover:opacity-100 translate-y-2 group-hover:translate-y-0 transition-all duration-300"
                         style="color:var(--primary-color);">
                        En savoir plus <i class="fas fa-arrow-right text-xs"></i>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

    </div>
</section>
@endif

{{-- ╔══════════════════════════════════════════════════════
     § 4. ABOUT — Dark éditorial, chiffres massifs
     ══════════════════════════════════════════════════════ --}}
@if($homeConfig['sections']['about']['enabled'] ?? true)
<section class="relative py-28 overflow-hidden" style="background:#06150d;">
    {{-- Halo --}}
    <div class="absolute top-0 left-0 w-[600px] h-[600px] rounded-full pointer-events-none"
         style="background:radial-gradient(circle, rgba(34,197,94,.12) 0%, transparent 65%); transform:translate(-40%,-40%);"></div>

    <div class="lp-shell relative z-10">
        <div class="grid lg:grid-cols-2 gap-16 xl:gap-24" style="align-items:stretch;">

            {{-- Photo + chiffres en dessous --}}
            <div class="relative flex flex-col">
                @php $aboutPhoto = $homeConfig['about']['image'] ?? null; @endphp
                @if($aboutPhoto)
                {{-- Photo principale — s'étire pour remplir la hauteur du texte --}}
                <div class="relative rounded-2xl overflow-hidden flex-1 mb-4" style="min-height:280px;">
                    <img src="{{ asset(ltrim($aboutPhoto, '/')) }}"
                         alt="{{ $homeConfig['sections']['about']['title'] ?? 'À propos' }}"
                         style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center;">
                    {{-- Badge overlay --}}
                    @if(setting('company_logo'))
                    <div class="absolute bottom-4 right-4 w-14 h-14 rounded-xl overflow-hidden border-2 shadow-xl flex items-center justify-center"
                         style="border-color:rgba(255,255,255,.2); background:#fff;">
                        <img src="{{ asset(setting('company_logo')) }}" alt="{{ setting('company_name') }}" class="w-10 h-auto object-contain">
                    </div>
                    @endif
                </div>
                @endif

                {{-- Chiffres compacts sous la photo --}}
                <div class="grid grid-cols-4 gap-3 flex-shrink-0">
                    @foreach([
                        ['500+','Chantiers','text-emerald-400'],
                        ['15+','Ans exp.','text-blue-400'],
                        ['60+','Communes','text-violet-400'],
                        ['4.9★','Avis Google','text-amber-400'],
                    ] as $stat)
                    <div class="rounded-xl p-3 border border-white/08 text-center"
                         style="background:rgba(255,255,255,.03);">
                        <div class="{{ $stat[2] }} font-black text-lg leading-tight mb-0.5">{{ $stat[0] }}</div>
                        <div class="text-white/40 text-[10px] leading-tight font-medium">{{ $stat[1] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Texte --}}
            <div>
                <p class="text-xs font-black uppercase tracking-[.2em] mb-5 text-emerald-400">À propos</p>
                <h2 class="font-black text-white leading-[1.05] mb-6"
                    style="font-size:clamp(1.8rem,3.5vw,3rem);">
                    {{ $homeConfig['sections']['about']['title'] ?? 'Votre élagueur de confiance dans l\'Oise' }}
                </h2>
                <p class="text-white/55 leading-relaxed mb-6 text-base">
                    {{ $homeConfig['sections']['about']['text'] ?? 'Louis Hoffmann est élagueur professionnel certifié, intervenant dans tout le département 60 (Oise) : Compiègne, Beauvais, Senlis, Chantilly, Creil, Noyon et toutes les communes environnantes. Avec plus de 15 ans d\'expérience, nous garantissons des interventions sécurisées, soignées et respectueuses de l\'environnement.' }}
                </p>

                <ul class="lp-check text-white/70 text-sm mb-8 space-y-1">
                    <li>Artisan certifié, assuré en responsabilité civile professionnelle</li>
                    <li>Équipements professionnels et sécurité NF</li>
                    <li>Valorisation des déchets verts — broyage sur place possible</li>
                    <li>Devis gratuit, transparent, sans engagement</li>
                    <li>Réponse sous 24h pour toute intervention dans le 60</li>
                </ul>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('contact') }}"
                       class="inline-flex items-center gap-2.5 font-bold px-7 py-4 rounded-xl text-white transition-all hover:opacity-90"
                       style="background:var(--primary-color);">
                        <i class="fas fa-envelope"></i> Nous contacter
                    </a>
                    <a href="{{ route('form.step', 'propertyType') }}"
                       class="inline-flex items-center gap-2.5 font-bold px-7 py-4 rounded-xl border border-white/20 text-white hover:bg-white/08 transition-all">
                        <i class="fas fa-calculator"></i> Devis gratuit
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>
@endif

{{-- ╔══════════════════════════════════════════════════════
     § 5. PROCESSUS — Numéros massifs, horizontal
     ══════════════════════════════════════════════════════ --}}
<section class="py-24 bg-white">
    <div class="lp-shell">

        <div class="text-center mb-16">
            <p class="text-xs font-black uppercase tracking-[.2em] mb-4" style="color:var(--primary-color);">Simple & rapide</p>
            <h2 class="font-black text-gray-900 leading-tight" style="font-size:clamp(1.8rem,4vw,3rem);">
                Comment ça se passe ?
            </h2>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-0 relative">
            {{-- Ligne de connexion desktop --}}
            <div class="hidden lg:block absolute top-[3.5rem] left-[12.5%] right-[12.5%] h-px"
                 style="background:linear-gradient(90deg, var(--primary-color), var(--secondary-color), var(--primary-color));"></div>

            @foreach([
                [1,'fas fa-phone-alt','Prise de contact','Appelez ou remplissez le formulaire. Réponse sous 24h partout dans l\'Oise (60).','emerald'],
                [2,'fas fa-search-location','Visite & devis','Déplacement gratuit (Compiègne, Beauvais, Senlis…) pour évaluer et chiffrer.','sky'],
                [3,'fas fa-calendar-check','Planification','Date calée selon vos dispo et la saison, en respectant les réglementations locales.','violet'],
                [4,'fas fa-hard-hat','Travaux & nettoyage','Intervention sécurisée, chantier nettoyé intégralement, broyage possible.','amber'],
            ] as [$n, $icon, $title, $text, $color])
            @php $colors = ['emerald'=>['bg'=>'bg-emerald-500','light'=>'bg-emerald-50','txt'=>'text-emerald-600'],'sky'=>['bg'=>'bg-sky-500','light'=>'bg-sky-50','txt'=>'text-sky-600'],'violet'=>['bg'=>'bg-violet-500','light'=>'bg-violet-50','txt'=>'text-violet-600'],'amber'=>['bg'=>'bg-amber-500','light'=>'bg-amber-50','txt'=>'text-amber-600']]; $c=$colors[$color]; @endphp
            <div class="relative flex flex-col items-center text-center px-6 py-8 group">
                {{-- Number circle --}}
                <div class="relative mb-7 z-10">
                    <div class="w-16 h-16 rounded-2xl {{ $c['bg'] }} flex items-center justify-center text-white font-black text-2xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                        {{ $n }}
                    </div>
                    <div class="absolute inset-0 rounded-2xl {{ $c['bg'] }} opacity-20 scale-[1.5] group-hover:scale-[1.7] transition-transform duration-300"></div>
                </div>
                <div class="w-10 h-10 rounded-xl {{ $c['light'] }} flex items-center justify-center mb-4">
                    <i class="{{ $icon }} {{ $c['txt'] }}"></i>
                </div>
                <h3 class="font-black text-gray-900 text-base mb-2">{{ $title }}</h3>
                <p class="text-gray-500 text-sm leading-relaxed">{{ $text }}</p>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-14">
            <a href="{{ route('form.step', 'propertyType') }}"
               class="inline-flex items-center gap-2.5 text-white font-black px-10 py-5 rounded-2xl shadow-xl transition-all hover:scale-[1.02]"
               style="background:linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                <i class="fas fa-calculator"></i> Démarrer mon devis gratuit
            </a>
        </div>
    </div>
</section>

{{-- ╔══════════════════════════════════════════════════════
     § 6. ZONES OISE — Fond sombre, pills modernes
     ══════════════════════════════════════════════════════ --}}
<section style="background:#f0faf2; padding:5rem 0; border-top:1px solid #d1ead8; border-bottom:1px solid #d1ead8;">
    <div class="lp-shell">
        {{-- Header section --}}
        <div style="display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:1.5rem;margin-bottom:3rem;">
            <div>
                <span style="display:inline-flex;align-items:center;gap:.5rem;font-size:.72rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--primary-color);margin-bottom:.75rem;">
                    <i class="fas fa-map-marker-alt"></i> Zone d'intervention
                </span>
                <h2 style="font-size:clamp(1.6rem,3vw,2.4rem);font-weight:900;color:#111827;line-height:1.1;margin:0;">
                    Tout le département <span style="color:var(--primary-color);">60 — Oise</span>
                </h2>
                <p style="font-size:.9rem;color:#6b7280;margin-top:.75rem;max-width:480px;line-height:1.7;">
                    Nous intervenons dans l'ensemble des communes de l'Oise et des départements limitrophes (Aisne, Somme, Val-d'Oise…)
                </p>
            </div>
            <a href="{{ route('contact') }}"
               style="display:inline-flex;align-items:center;gap:.6rem;font-size:.875rem;font-weight:700;padding:.75rem 1.5rem;border-radius:12px;border:2px solid var(--primary-color);color:var(--primary-color);text-decoration:none;transition:all .2s;white-space:nowrap;"
               onmouseover="this.style.background='var(--primary-color)';this.style.color='#fff';"
               onmouseout="this.style.background='transparent';this.style.color='var(--primary-color)';">
                Vérifier ma commune <i class="fas fa-arrow-right" style="font-size:.75rem;"></i>
            </a>
        </div>

        {{-- Pills des villes --}}
        @php
            $displayCities = isset($favoriteCities) && $favoriteCities->count() > 0 ? $favoriteCities : collect([]);
            $oiseFallback = [
                ['Compiègne','60200'],['Beauvais','60000'],['Senlis','60300'],
                ['Chantilly','60500'],['Creil','60100'],['Noyon','60400'],
                ['Verberie','60410'],['Clermont','60600'],['Pont-Sainte-Maxence','60700'],
                ['Méru','60110'],['Liancourt','60140'],['Gouvieux','60270'],
                ['Lacroix-Saint-Ouen','60610'],['Margny-lès-Compiègne','60280'],
                ['Ribécourt-Dreslincourt','60170'],['Éstrées-Saint-Denis','60190'],
                ['Bresles','60590'],['Longueil-Annel','60150'],
            ];
        @endphp

        <div style="display:flex;flex-wrap:wrap;gap:.625rem;">
            @if($displayCities->count() > 0)
                @foreach($displayCities as $city)
                <a href="{{ route('ads.index') }}?city={{ $city->slug }}"
                   style="display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 1.1rem;border-radius:50px;border:1px solid #c6e4cf;background:#fff;color:#374151;font-size:.82rem;font-weight:600;text-decoration:none;transition:all .18s;box-shadow:0 1px 3px rgba(0,0,0,.06);"
                   onmouseover="this.style.borderColor='var(--primary-color)';this.style.color='var(--primary-color)';this.style.background='rgba(var(--primary-color-rgb,34,197,94),.06)';"
                   onmouseout="this.style.borderColor='#c6e4cf';this.style.color='#374151';this.style.background='#fff';">
                    <i class="fas fa-map-pin" style="color:var(--primary-color);font-size:.7rem;"></i>
                    {{ $city->name }}
                    @if($city->postal_code)
                    <span style="color:#9ca3af;font-size:.75rem;font-family:monospace;">{{ $city->postal_code }}</span>
                    @endif
                </a>
                @endforeach
            @else
                @foreach($oiseFallback as [$name, $postal])
                <div style="display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 1.1rem;border-radius:50px;border:1px solid #c6e4cf;background:#fff;color:#374151;font-size:.82rem;font-weight:600;box-shadow:0 1px 3px rgba(0,0,0,.06);">
                    <i class="fas fa-map-pin" style="color:var(--primary-color);font-size:.7rem;"></i>
                    {{ $name }}
                    <span style="color:#9ca3af;font-size:.75rem;font-family:monospace;">{{ $postal }}</span>
                </div>
                @endforeach
                <div style="display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 1.1rem;border-radius:50px;border:1px dashed #c6e4cf;background:transparent;color:#9ca3af;font-size:.8rem;font-style:italic;">
                    + 40 autres communes…
                </div>
            @endif
        </div>
    </div>
</section>

{{-- ╔══════════════════════════════════════════════════════
     § 7. TÉMOIGNAGES — Éditorial, grande citation
     ══════════════════════════════════════════════════════ --}}
@if(($homeConfig['sections']['reviews']['enabled'] ?? true) && !empty($reviews) && count($reviews) > 0)
<section class="py-24 bg-gray-50">
    <div class="lp-shell">

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-14">
            <div>
                <p class="text-xs font-black uppercase tracking-[.2em] mb-4" style="color:var(--primary-color);">Témoignages</p>
                <h2 class="font-black text-gray-900 leading-tight" style="font-size:clamp(1.8rem,4vw,3rem);">
                    {{ $homeConfig['sections']['reviews']['title'] ?? 'Ce que disent nos clients' }}
                </h2>
                @if(isset($averageRating) && $averageRating > 0)
                <div class="flex items-center gap-2.5 mt-3">
                    <div class="flex gap-0.5">@for($i=1;$i<=5;$i++)<i class="fas fa-star text-base {{ $i<=round($averageRating)?'text-amber-400':'text-gray-200' }}"></i>@endfor</div>
                    <span class="font-black text-gray-900 text-xl">{{ number_format($averageRating,1) }}/5</span>
                    @if(isset($totalReviews))<span class="text-gray-400 text-sm">({{ $totalReviews }} avis)</span>@endif
                </div>
                @endif
            </div>
            <a href="{{ route('reviews.all') }}"
               class="inline-flex items-center gap-2 font-bold text-sm px-6 py-3 rounded-xl border-2 flex-shrink-0 transition-all"
               style="border-color:var(--primary-color); color:var(--primary-color);"
               onmouseover="this.style.background='var(--primary-color)';this.style.color='#fff';"
               onmouseout="this.style.background='transparent';this.style.color='var(--primary-color)';">
                Lire tous les avis <i class="fas fa-star text-xs"></i>
            </a>
        </div>

        {{-- Avis en 2 styles : 1 grand + N petits (max 5) --}}
        <div class="grid lg:grid-cols-3 gap-5">
            @foreach($reviews->take(min(5, $homeConfig['sections']['reviews']['limit'] ?? 5)) as $ri => $review)
            @if($ri === 0)
            {{-- Grand avis éditorial --}}
            <div class="lg:col-span-2 rounded-3xl p-8 md:p-10 flex flex-col justify-between relative overflow-hidden"
                 style="background:linear-gradient(135deg, #06150d 0%, #0d3b22 60%, #1a5c35 100%);">
                <div class="text-[7rem] font-black leading-none text-white/05 absolute -top-4 -left-2 select-none">"</div>
                <div class="flex gap-1 mb-5 relative z-10">
                    @for($i=1;$i<=5;$i++)<i class="fas fa-star {{ $i<=$review->rating?'text-amber-400':'text-white/20' }} text-base"></i>@endfor
                </div>
                <p class="text-white/85 text-xl md:text-2xl font-semibold leading-relaxed mb-8 relative z-10 italic">
                    "{{ \Illuminate\Support\Str::limit($review->review_text ?? 'Excellent travail, très professionnel.', 220) }}"
                </p>
                <div class="flex items-center gap-3 relative z-10">
                    <div class="w-12 h-12 rounded-full overflow-hidden flex-shrink-0 border-2 border-white/20">
                        @if($review->author_photo_url)
                        <img src="{{ $review->author_photo_url }}" alt="{{ $review->author_name }}" class="w-full h-full object-cover">
                        @else
                        <div class="w-full h-full flex items-center justify-center text-white font-black text-lg" style="background:var(--primary-color);">
                            {{ strtoupper(substr($review->author_name??'C',0,1)) }}
                        </div>
                        @endif
                    </div>
                    <div>
                        <div class="text-white font-bold">{{ $review->author_name }}</div>
                        <div class="text-white/40 text-xs">{{ $review->review_date ? \Carbon\Carbon::parse($review->review_date)->translatedFormat('F Y') : '' }}</div>
                    </div>
                    @if($review->source && str_contains($review->source,'Google'))
                    <div class="ml-auto flex items-center gap-1.5 bg-white/08 px-3 py-1.5 rounded-full">
                        <i class="fab fa-google text-blue-400 text-xs"></i>
                        <span class="text-white/50 text-xs font-semibold">Google</span>
                    </div>
                    @endif
                </div>
            </div>
            @else
            {{-- Petits avis --}}
            <div class="rounded-3xl bg-white border border-gray-100 p-6 flex flex-col shadow-sm hover:shadow-lg transition-all hover:-translate-y-0.5 relative overflow-hidden">
                <div class="absolute top-4 right-5 text-5xl font-black text-gray-50 leading-none select-none">"</div>
                <div class="flex gap-0.5 mb-4">
                    @for($i=1;$i<=5;$i++)<i class="fas fa-star text-xs {{ $i<=$review->rating?'text-amber-400':'text-gray-200' }}"></i>@endfor
                </div>
                <p class="text-gray-600 text-sm italic leading-relaxed flex-1 mb-5">
                    "{{ \Illuminate\Support\Str::limit($review->review_text ?? 'Excellent travail.', 160) }}"
                </p>
                <div class="flex items-center gap-2.5 pt-4 border-t border-gray-100">
                    <div class="w-9 h-9 rounded-full overflow-hidden flex-shrink-0">
                        @if($review->author_photo_url)
                        <img src="{{ $review->author_photo_url }}" alt="{{ $review->author_name }}" class="w-full h-full object-cover">
                        @else
                        <div class="w-full h-full flex items-center justify-center text-white text-xs font-black" style="background:linear-gradient(135deg,var(--primary-color),var(--secondary-color));">
                            {{ strtoupper(substr($review->author_name??'C',0,1)) }}
                        </div>
                        @endif
                    </div>
                    <div>
                        <div class="font-bold text-gray-900 text-xs">{{ $review->author_name }}</div>
                        <div class="text-gray-400 text-[10px]">{{ $review->review_date ? \Carbon\Carbon::parse($review->review_date)->translatedFormat('F Y') : '' }}</div>
                    </div>
                </div>
            </div>
            @endif
            @endforeach
        </div>

    </div>
</section>
@endif

{{-- ╔══════════════════════════════════════════════════════
     § 8. FAQ — Accordéon minimal
     ══════════════════════════════════════════════════════ --}}
<section class="py-24 bg-white">
    <div class="lp-shell">
        <div class="grid lg:grid-cols-[380px_1fr] gap-16 items-start">

            {{-- Texte gauche --}}
            <div class="lg:sticky lg:top-24">
                <p class="text-xs font-black uppercase tracking-[.2em] mb-5" style="color:var(--primary-color);">FAQ</p>
                <h2 class="font-black text-gray-900 leading-tight mb-5"
                    style="font-size:clamp(1.8rem,3.5vw,2.8rem);">
                    Vos questions,<br>nos réponses
                </h2>
                <p class="text-gray-500 text-base leading-relaxed mb-8">
                    Tout ce que vous devez savoir avant de nous contacter pour votre projet d'élagage dans l'Oise.
                </p>
                <a href="{{ route('contact') }}"
                   class="inline-flex items-center gap-2 font-bold text-sm px-6 py-3.5 rounded-xl text-white transition-all hover:opacity-90"
                   style="background:var(--primary-color);">
                    <i class="fas fa-envelope text-xs"></i> Poser une question
                </a>
            </div>

            {{-- Accordéon --}}
            <div class="divide-y divide-gray-100">
                @foreach([
                    ['Intervenez-vous dans toute l\'Oise ?','Oui, nous couvrons l\'ensemble du département 60 : Compiègne, Beauvais, Senlis, Chantilly, Creil, Noyon, Verberie, Clermont, Méru, Liancourt, Pont-Sainte-Maxence et toutes les communes alentour. Nous intervenons également dans les départements limitrophes (Aisne, Somme, Val-d\'Oise…).'],
                    ['Combien coûte un élagage dans l\'Oise ?','Le tarif dépend du type d\'arbre, de sa hauteur, de son accessibilité et des travaux à effectuer. C\'est pourquoi nous proposons un devis gratuit et sans engagement après visite sur place. Comptez en général entre 150€ et 800€ pour un élagage standard.'],
                    ['Avez-vous les certifications nécessaires ?','Absolument. Louis Hoffmann est artisan certifié, assuré en responsabilité civile professionnelle. Nous respectons toutes les normes de sécurité et les réglementations en vigueur dans le département 60.'],
                    ['Que faites-vous des déchets verts ?','Nous broyons les branches sur place si vous le souhaitez (le broyat peut servir de paillis). Les rémanents peuvent aussi être évacués et valorisés en composterie ou énergie verte.'],
                    ['En quelle saison effectuer l\'élagage ?','L\'élagage se pratique idéalement en fin d\'hiver (février-mars) ou en été (juillet-août). Certains arbres ont leurs propres rythmes. L\'abattage peut se faire toute l\'année. Nous vous conseillons lors du devis.'],
                    ['Puis-je obtenir un devis rapidement ?','Oui. Remplissez notre formulaire en ligne ou appelez-nous directement. Nous vous répondons sous 24h et nous déplaçons gratuitement pour établir un devis détaillé.'],
                ] as $fi => $faq)
                <div class="faq-item py-0">
                    <details class="group">
                        <summary class="flex items-center justify-between gap-4 py-5 cursor-pointer">
                            <span class="font-bold text-gray-900 text-base pr-4">{{ $faq[0] }}</span>
                            <div class="faq-icon w-8 h-8 rounded-full border-2 flex items-center justify-center flex-shrink-0"
                                 style="border-color:var(--primary-color); color:var(--primary-color);">
                                <i class="fas fa-plus text-xs"></i>
                            </div>
                        </summary>
                        <div class="pb-5 text-gray-500 text-sm leading-relaxed">{{ $faq[1] }}</div>
                    </details>
                </div>
                @endforeach
            </div>

        </div>
    </div>
</section>

{{-- ╔══════════════════════════════════════════════════════
     § 9. CTA FINAL — Plein-écran, impact max
     ══════════════════════════════════════════════════════ --}}
@if($homeConfig['sections']['cta']['enabled'] ?? true)
{{-- Séparateur visuel avant le CTA --}}
<div style="height:4px; background:linear-gradient(90deg, var(--primary-color), var(--secondary-color, #1a5c35), var(--primary-color));"></div>

<section class="relative overflow-hidden"
         style="background:linear-gradient(160deg, #0d3320 0%, #1a5c35 50%, #0d3320 100%);">
    {{-- Motif discret --}}
    <div class="absolute inset-0 opacity-[.04]"
         style="background-image:radial-gradient(rgba(255,255,255,.6) 1px, transparent 1px); background-size:28px 28px;"></div>
    {{-- Halo --}}
    <div class="absolute top-0 left-1/2 w-[800px] h-[500px] rounded-full pointer-events-none opacity-30"
         style="background:radial-gradient(ellipse, rgba(74,222,128,.25) 0%, transparent 65%); transform:translateX(-50%) translateY(-40%);"></div>

    <div class="lp-shell relative z-10 text-center" style="padding-top:7rem; padding-bottom:7rem;">

        <div class="inline-flex items-center gap-2.5 bg-white/06 border border-white/12 rounded-full px-5 py-2 mb-8">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span class="text-white/70 text-xs font-bold uppercase tracking-widest">Département 60 — Oise · Picardie</span>
        </div>

        <h2 class="font-black text-white leading-[.95] mb-6 tracking-tight"
            style="font-size:clamp(2.5rem,7vw,6rem);">
            {{ $homeConfig['sections']['cta']['title'] ?? 'Besoin d\'un élagueur<br>dans l\'Oise ?' }}
        </h2>

        <p class="text-white/50 text-lg md:text-xl mb-12 max-w-2xl mx-auto leading-relaxed">
            Compiègne, Beauvais, Senlis, Chantilly, Creil, Noyon — devis gratuit, réponse sous 24h, artisan certifié et assuré.
        </p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-14">
            <a href="{{ route('form.step', 'propertyType') }}"
               class="group inline-flex items-center justify-center gap-3 bg-white font-black text-lg px-10 py-5 rounded-2xl shadow-2xl hover:bg-gray-50 hover:scale-[1.03] transition-all"
               style="color:var(--primary-color);"
               onclick="if(typeof trackFormClick==='function')trackFormClick('{{ request()->url() }}')">
                <i class="fas fa-calculator group-hover:rotate-6 transition-transform"></i>
                Devis gratuit en ligne
            </a>
            <a href="tel:{{ $phoneRaw }}"
               class="inline-flex items-center justify-center gap-3 border-2 border-white/20 text-white font-bold text-lg px-10 py-5 rounded-2xl hover:bg-white/08 hover:border-white/40 transition-all"
               onclick="if(typeof trackPhoneCall==='function')trackPhoneCall('{{ $phoneRaw }}','cta-final')">
                <span class="relative flex">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-25"></span>
                    <i class="fas fa-phone relative text-emerald-400"></i>
                </span>
                {{ $phone }}
            </a>
        </div>

        {{-- Trust pills --}}
        <div class="flex flex-wrap justify-center gap-4">
            @foreach(['Devis 100% gratuit','Sans engagement','Réponse sous 24h','Artisan certifié','Tout le dép. 60'] as $g)
            <div class="flex items-center gap-2 bg-white/05 border border-white/08 px-4 py-2 rounded-full text-white/50 text-xs font-semibold">
                <i class="fas fa-check text-emerald-400 text-[10px]"></i> {{ $g }}
            </div>
            @endforeach
        </div>

    </div>
</section>
@endif

{{-- Sections optionnelles --}}
@include('home.partials.ecology-financing')
@include('home.partials.portfolio')
@include('home.partials.featured-partner')
@include('home.partials.partners-logos')
@include('home.partials.scripts')

<script>
function trackServiceClick(n,p){ fetch('/api/track-service-click?service='+encodeURIComponent(n),{method:'GET'}).catch(()=>{}); }
function trackFormClick(p){ fetch('/api/track-form-click',{method:'GET'}).catch(()=>{}); }
</script>
</div>
