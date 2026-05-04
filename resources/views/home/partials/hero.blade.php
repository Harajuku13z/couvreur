@php
    $hl = $homeLayout ?? ($homeConfig['layout'] ?? 'classic');
    if (!in_array($hl, ['classic', 'showcase', 'magazine', 'conversion'], true)) {
        $hl = 'classic';
    }
    $phoneMain = setting('company_phone', '06 42 21 41 51');
    $phone2    = setting('company_phone_2', '');
    $bgImg     = $homeConfig['hero']['background_image'] ?? null;
@endphp

@push('head')
<style>
/* ── Hero ─────────────────────────────────────────────────────────── */
.hero-wrap {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    overflow: hidden;
}
.hero-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #0c2340 0%, #0f3d2e 55%, #1a5c20 100%);
    z-index: 0;
}
.hero-bg-photo {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    z-index: 0;
}
.hero-bg-photo::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(100deg, rgba(5,25,15,.88) 0%, rgba(5,25,15,.65) 55%, rgba(5,25,15,.30) 100%);
}
/* Cercles déco */
.hero-orb {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
    z-index: 0;
}
/* Puce verte animée */
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }
.blink { animation: blink 2s ease-in-out infinite; }
/* Badge pill */
.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    background: rgba(255,255,255,.12);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,.2);
    border-radius: 999px;
    padding: .4rem 1rem;
    color: #fff;
    font-size: .82rem;
    font-weight: 600;
}
/* Stat mini-card */
.hero-stat {
    background: rgba(255,255,255,.10);
    backdrop-filter: blur(6px);
    border: 1px solid rgba(255,255,255,.14);
    border-radius: .875rem;
    padding: .9rem 1rem;
    text-align: center;
    flex: 1;
    min-width: 0;
}
/* Bouton principal */
.btn-hero-primary {
    display: inline-flex;
    align-items: center;
    gap: .55rem;
    background: #fff;
    color: #0c2340;
    font-weight: 800;
    font-size: 1.05rem;
    padding: .95rem 2rem;
    border-radius: .875rem;
    box-shadow: 0 8px 32px rgba(0,0,0,.25);
    text-decoration: none;
    transition: transform .2s, box-shadow .2s;
}
.btn-hero-primary:hover { transform: translateY(-2px); box-shadow: 0 14px 40px rgba(0,0,0,.3); color: #0c2340; }
.btn-hero-primary i { color: var(--primary-color); }
/* Bouton secondaire */
.btn-hero-sec {
    display: inline-flex;
    align-items: center;
    gap: .55rem;
    border: 2px solid rgba(255,255,255,.65);
    color: #fff;
    font-weight: 700;
    font-size: 1rem;
    padding: .9rem 1.75rem;
    border-radius: .875rem;
    text-decoration: none;
    transition: background .2s;
    background: transparent;
}
.btn-hero-sec:hover { background: rgba(255,255,255,.12); color: #fff; }
/* Vague bas */
.hero-wave { position: absolute; bottom: 0; left: 0; right: 0; z-index: 2; line-height: 0; }

@media(max-width:768px) {
    .hero-wrap { min-height: 100svh; padding-top: 80px; padding-bottom: 2rem; }
}
</style>
@endpush

<section class="hero-wrap pt-20 pb-16 md:pb-0">

    {{-- Fond photo ou dégradé vert forêt --}}
    @if($bgImg)
    <div class="hero-bg-photo" style="background-image:url('{{ asset($bgImg) }}');"></div>
    @else
    <div class="hero-bg">
        {{-- Orbes déco --}}
        <div class="hero-orb" style="width:700px;height:700px;top:-200px;right:-180px;background:radial-gradient(circle,rgba(34,197,94,.18) 0%,transparent 70%);"></div>
        <div class="hero-orb" style="width:500px;height:500px;bottom:-150px;left:-100px;background:radial-gradient(circle,rgba(16,185,129,.12) 0%,transparent 70%);"></div>
        <div class="hero-orb" style="width:300px;height:300px;top:40%;left:38%;background:radial-gradient(circle,rgba(255,255,255,.04) 0%,transparent 70%);"></div>
    </div>
    @endif

    <div class="site-shell relative z-10 py-12 md:py-16 lg:py-0 lg:min-h-[calc(100vh-80px)] lg:flex lg:items-center">
        <div class="w-full grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">

            {{-- ── Colonne texte ────────────────────────────────── --}}
            <div>
                {{-- Badge pré-titre --}}
                <div class="mb-5">
                    <span class="hero-badge">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 blink"></span>
                        Élagueur professionnel — Département 60 (Oise)
                    </span>
                </div>

                {{-- Trust pills --}}
                @php
                    $hasDec  = $homeConfig['trust_badges']['garantie_decennale'] ?? false;
                    $hasRge  = $homeConfig['trust_badges']['certifie_rge'] ?? false;
                    $showRat = ($homeConfig['trust_badges']['show_rating'] ?? false) && $averageRating > 0;
                @endphp
                @if($hasDec || $hasRge || $showRat)
                <div class="flex flex-wrap gap-2 mb-5">
                    @if($hasDec)
                    <span class="hero-badge"><i class="fas fa-shield-alt text-amber-300 text-xs"></i> Garantie décennale</span>
                    @endif
                    @if($hasRge)
                    <span class="hero-badge"><i class="fas fa-leaf text-emerald-300 text-xs"></i> Certifié RGE</span>
                    @endif
                    @if($showRat)
                    <span class="hero-badge"><i class="fas fa-star text-yellow-300 text-xs"></i> {{ number_format($averageRating,1) }}/5 · {{ $totalReviews }} avis</span>
                    @endif
                </div>
                @endif

                {{-- Titre H1 --}}
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-[1.05] mb-5 drop-shadow-xl">
                    {{ $homeConfig['hero']['title'] ?? 'Élagage & Abattage dans l\'Oise' }}
                </h1>

                {{-- Sous-titre --}}
                <p class="text-lg md:text-xl text-white/85 mb-8 leading-relaxed max-w-xl">
                    {{ $homeConfig['hero']['subtitle'] ?? 'Louis Hoffmann intervient dans tout le département 60 — Compiègne, Beauvais, Senlis, Chantilly et alentours. Devis gratuit, réponse sous 24h.' }}
                </p>

                {{-- CTA --}}
                <div class="flex flex-col sm:flex-row gap-3 mb-10">
                    <a href="{{ route('form.step', 'propertyType') }}" class="btn-hero-primary">
                        <i class="fas fa-calculator"></i>
                        {{ $homeConfig['hero']['cta_text'] ?? 'Devis gratuit en ligne' }}
                    </a>
                    @if($phoneMain)
                    <a href="tel:{{ setting('company_phone_raw', $phoneMain) }}" class="btn-hero-sec"
                       onclick="if(typeof trackPhoneCall==='function')trackPhoneCall('{{ setting('company_phone_raw',$phoneMain) }}','hero')">
                        <i class="fas fa-phone text-emerald-300"></i>
                        {{ $phoneMain }}
                    </a>
                    @endif
                    @if($phone2)
                    <a href="tel:{{ setting('company_phone_2_raw',$phone2) }}" class="btn-hero-sec" style="border-color:rgba(255,255,255,.35);">
                        <i class="fas fa-phone-alt text-sm text-white/70"></i> {{ $phone2 }}
                    </a>
                    @endif
                </div>

                {{-- Mini stats --}}
                <div class="flex flex-wrap gap-3">
                    @php $heroStats = [
                        ['val'=>'60+',  'label'=>'Communes de l\'Oise', 'icon'=>'fas fa-map-marker-alt'],
                        ['val'=>'500+', 'label'=>'Chantiers réalisés',  'icon'=>'fas fa-tree'],
                        ['val'=>'48h',  'label'=>'Délai d\'intervention','icon'=>'fas fa-bolt'],
                        ['val'=>$averageRating>0 ? number_format($averageRating,1).'/5' : '4.9/5', 'label'=>'Note Google', 'icon'=>'fas fa-star'],
                    ]; @endphp
                    @foreach($heroStats as $hs)
                    <div class="hero-stat">
                        <i class="{{ $hs['icon'] }} text-emerald-300 text-xs mb-1 block"></i>
                        <div class="text-white font-extrabold text-lg leading-none">{{ $hs['val'] }}</div>
                        <div class="text-white/55 text-xs mt-0.5 leading-tight">{{ $hs['label'] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- ── Colonne visuel ───────────────────────────────── --}}
            <div class="hidden lg:flex items-center justify-center">
                <div class="relative w-full max-w-md">
                    {{-- Card flottante principale --}}
                    <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-white/15 aspect-[4/5]"
                         style="background: linear-gradient(145deg, rgba(255,255,255,.08) 0%, rgba(255,255,255,.02) 100%); backdrop-filter:blur(4px);">
                        @if(!empty($homeConfig['hero']['magazine_side_image']))
                        <img src="{{ asset($homeConfig['hero']['magazine_side_image']) }}"
                             alt="Élagage Oise - Louis Hoffmann"
                             class="absolute inset-0 w-full h-full object-cover"
                             loading="eager" fetchpriority="high">
                        @else
                        <div class="absolute inset-0 flex flex-col items-center justify-center gap-4 p-8">
                            <div class="w-32 h-32 rounded-full flex items-center justify-center"
                                 style="background:rgba(34,197,94,.2); border:2px solid rgba(34,197,94,.3);">
                                <i class="fas fa-tree text-6xl text-emerald-300"></i>
                            </div>
                            <div class="text-center text-white">
                                <p class="font-extrabold text-2xl">Louis Hoffmann</p>
                                <p class="text-white/70 text-sm mt-1">Élagage · Abattage · Oise (60)</p>
                            </div>
                            {{-- Zones --}}
                            <div class="w-full mt-4 space-y-2">
                                @foreach(['Compiègne','Beauvais','Senlis','Chantilly','Creil','Noyon'] as $z)
                                <div class="flex items-center gap-2 bg-white/8 rounded-lg px-3 py-2">
                                    <i class="fas fa-check-circle text-emerald-400 text-xs"></i>
                                    <span class="text-white/85 text-sm font-medium">{{ $z }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Badge urgence flottant --}}
                    <div class="absolute -bottom-4 -left-4 bg-emerald-500 text-white rounded-2xl shadow-xl px-5 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide opacity-80">Devis gratuit</p>
                        <p class="text-lg font-extrabold leading-none">Réponse en 24h</p>
                    </div>

                    {{-- Badge note flottant --}}
                    @if($averageRating > 0)
                    <div class="absolute -top-4 -right-4 bg-white rounded-2xl shadow-xl px-4 py-3 text-center">
                        <p class="text-amber-500 font-extrabold text-xl leading-none">{{ number_format($averageRating,1) }}</p>
                        <div class="flex gap-0.5 mt-1 justify-center">
                            @for($i=1;$i<=5;$i++)<i class="fas fa-star text-amber-400 text-xs"></i>@endfor
                        </div>
                        <p class="text-gray-500 text-xs mt-0.5">{{ $totalReviews }} avis</p>
                    </div>
                    @endif
                </div>
            </div>

        </div>{{-- /grid --}}
    </div>

    {{-- Vague SVG bas --}}
    <div class="hero-wave">
        <svg viewBox="0 0 1440 70" preserveAspectRatio="none" class="w-full h-10 md:h-16" aria-hidden="true">
            <path d="M0,70 C480,0 960,70 1440,30 L1440,70 Z" fill="#f9fafb"/>
        </svg>
    </div>
</section>
