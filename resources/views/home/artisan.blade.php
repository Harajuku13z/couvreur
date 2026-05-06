{{-- ============================================================
     PAGE ACCUEIL — Design Artisan Editorial
     Converti depuis le prototype React/JSX (Site web artisan-2.zip)
     Police : Fraunces (display) + Manrope (corps)
     Palette : fond chaud #FAF7F2 · encre #1F1A14 · primary depuis settings
     ============================================================ --}}

@php
    $phone    = $companySettings['phone'] ?? setting('company_phone', '06 42 21 41 51');
    $phoneRaw = preg_replace('/\s+/', '', $phone);
    $city     = $companySettings['city'] ?? setting('company_city', 'Paris');
    $name     = $companySettings['name'] ?? setting('company_name', 'Votre Entreprise');

    $heroTitle = $homeConfig['sections']['hero']['title']
              ?? $homeConfig['hero']['title']
              ?? ($name . ' — ' . $city);
    $heroSub   = $homeConfig['sections']['hero']['subtitle']
              ?? $homeConfig['hero']['subtitle']
              ?? 'Réparation, rénovation ou neuf — on intervient vite, on vous explique tout, et on garantit le travail.';

    $heroImg    = $homeConfig['hero']['background_image'] ?? null;
    $heroImgUrl = $heroImg ? asset(ltrim($heroImg, '/')) : null;

    $svcList = array_values(array_filter(
        is_array($services) ? $services : [],
        fn($s) => is_array($s) && ($s['is_visible'] ?? true)
    ));

    $ratingVal = round((float)($averageRating ?? 5), 1);
    $reviewCount = (int)($totalReviews ?? 0);

    $displayedReviews = $reviews->take(3);
    $displayedPortfolio = array_slice($portfolioItems ?? [], 0, 3);
@endphp

{{-- Fonts Fraunces + Manrope si pas déjà chargées --}}
@push('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght,SOFT@9..144,500,30;9..144,700,30;9..144,800,30&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
/* ────────────────────────────────────────────────────────────
   ARTISAN DESIGN SYSTEM — Editorial Warm
   ──────────────────────────────────────────────────────────── */
.art-root {
    --primary:      var(--primary-color, #B7472A);
    --primary-dark: color-mix(in srgb, var(--primary) 75%, #000);
    --primary-soft: color-mix(in srgb, var(--primary) 10%, transparent);
    --primary-fg:   #fff;

    --bg:       #FAF7F2;
    --bg-soft:  #F2EDE4;
    --bg-card:  #FFFFFF;
    --ink:      #1F1A14;
    --ink-2:    #4A3F32;
    --ink-3:    #7A6E5F;
    --line:     rgba(31,26,20,.10);
    --line-s:   rgba(31,26,20,.18);

    --sh-sm: 0 1px 2px rgba(31,26,20,.05), 0 1px 3px rgba(31,26,20,.08);
    --sh-md: 0 4px 12px rgba(31,26,20,.06), 0 2px 4px rgba(31,26,20,.04);
    --sh-lg: 0 12px 40px rgba(31,26,20,.10), 0 4px 12px rgba(31,26,20,.06);

    --r:    14px;
    --r-sm: 10px;
    --r-lg: 22px;

    --fd: "Fraunces", ui-serif, Georgia, serif;
    --fb: "Manrope", ui-sans-serif, system-ui, sans-serif;
    --cw: 1200px;

    font-family: var(--fb);
    background: var(--bg);
    color: var(--ink);
    -webkit-font-smoothing: antialiased;
}
.art-root *, .art-root *::before, .art-root *::after {
    box-sizing: border-box;
}
.art-root h1, .art-root h2, .art-root h3, .art-root h4 {
    font-family: var(--fd);
    font-weight: 700;
    letter-spacing: -.025em;
    color: var(--ink);
    margin: 0;
}
.art-root h1 { font-size: clamp(40px,5.2vw,68px); line-height: 1.04; }
.art-root h2 { font-size: clamp(28px,3.2vw,42px); line-height: 1.1; }
.art-root h3 { font-size: clamp(18px,1.5vw,22px); line-height: 1.25; }
.art-root p  { margin: 0; }
.art-root a  { color: inherit; text-decoration: none; }

/* Container */
.art-w {
    width: 100%;
    max-width: var(--cw);
    margin: 0 auto;
    padding: 0 24px;
}

/* Sections */
.art-sec { padding: 88px 0; }
.art-sec.tight { padding: 56px 0; }

/* Section header */
.art-sh { margin-bottom: 48px; max-width: 720px; }
.art-sh p { color: var(--ink-3); font-size: 17px; margin-top: 12px; }
.art-eyebrow {
    display: inline-flex; align-items: center; gap: 8px;
    font-size: 12px; font-weight: 700; letter-spacing: .12em;
    text-transform: uppercase; color: var(--primary);
    margin-bottom: 14px;
}
.art-eyebrow::before {
    content: ''; display: block; width: 24px; height: 2px;
    background: var(--primary); border-radius: 2px;
}

/* Buttons */
.art-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    height: 52px; padding: 0 24px; border: 0; border-radius: 999px;
    font-family: var(--fb); font-weight: 600; font-size: 15px;
    cursor: pointer; transition: all .15s; text-decoration: none;
}
.art-btn-primary {
    background: var(--primary); color: var(--primary-fg);
}
.art-btn-primary:hover { filter: brightness(.9); transform: translateY(-1px); }
.art-btn-ghost {
    background: var(--bg-card); color: var(--ink);
    border: 1.5px solid var(--line-s);
}
.art-btn-ghost:hover { background: var(--bg-soft); }
.art-btn-outline {
    background: transparent; color: var(--ink);
    border: 1.5px solid var(--line-s);
}
.art-btn-lg { height: 60px; padding: 0 32px; font-size: 16px; }
.art-btn-sm { height: 40px; padding: 0 16px; font-size: 14px; }

/* Cards */
.art-card {
    background: var(--bg-card);
    border-radius: var(--r);
    box-shadow: var(--sh-sm);
    border: 1px solid var(--line);
}

/* Photo helper */
.art-photo {
    position: relative; overflow: hidden; background: var(--bg-soft);
    border-radius: var(--r) var(--r) 0 0;
    background-size: cover; background-position: center;
}

/* Star */
.art-stars { color: #FFD166; display: flex; gap: 2px; }
.art-stars-google { color: #F5A623; }

/* ── §1 HERO ─────────────────────────────────────────────── */
.art-hero {
    position: relative; min-height: min(720px, 88vh);
    display: flex; align-items: stretch; overflow: hidden;
}
.art-hero-bg {
    position: absolute; inset: 0;
    background-size: cover; background-position: center;
}
.art-hero-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(180deg,rgba(15,12,8,.55) 0%,rgba(15,12,8,.45) 50%,rgba(15,12,8,.85) 100%);
}
.art-hero-overlay2 {
    position: absolute; inset: 0;
    background: linear-gradient(90deg,rgba(15,12,8,.7) 0%,rgba(15,12,8,.2) 70%,transparent 100%);
}
.art-hero-inner {
    position: relative; z-index: 2;
    padding-top: 80px; padding-bottom: 100px;
    color: #fff; display: flex; align-items: center;
    width: 100%; max-width: var(--cw); margin: 0 auto; padding-left: 24px; padding-right: 24px;
}
.art-hero-content { max-width: 760px; }
.art-hero-badge {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 6px 14px; border-radius: 999px;
    background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2);
    backdrop-filter: blur(8px);
    font-size: 13px; font-weight: 600; letter-spacing: .3px;
    margin-bottom: 24px; color: #fff;
}
.art-hero-badge-dot {
    width: 8px; height: 8px; border-radius: 50%; background: #6FCF97;
    animation: art-pulse 2s infinite;
}
@keyframes art-pulse {
    0%,100% { opacity: 1; transform: scale(1); }
    50%      { opacity: .6; transform: scale(1.2); }
}
.art-hero-ctas { display: flex; gap: 12px; margin-top: 36px; flex-wrap: wrap; }
.art-hero-cta-tel {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,.12); color: #fff;
    border: 1px solid rgba(255,255,255,.3); backdrop-filter: blur(8px);
}
.art-hero-trust { display: flex; align-items: center; gap: 28px; margin-top: 44px; flex-wrap: wrap; }
.art-hero-trust-divider { width: 1px; height: 36px; background: rgba(255,255,255,.2); }
.art-hero-avatars { display: flex; }
.art-hero-avatar {
    width: 38px; height: 38px; border-radius: 50%;
    border: 2px solid rgba(255,255,255,.95);
    font-weight: 700; font-size: 14px; color: #fff;
    display: flex; align-items: center; justify-content: center;
}
.art-hero-logosbar {
    position: absolute; bottom: 0; left: 0; right: 0; z-index: 3;
    padding: 14px 24px;
    background: rgba(0,0,0,.4); backdrop-filter: blur(12px);
    border-top: 1px solid rgba(255,255,255,.1);
    display: flex; justify-content: center; gap: 40px; flex-wrap: wrap;
    font-size: 12px; color: rgba(255,255,255,.75);
    font-weight: 600; letter-spacing: .3px; text-transform: uppercase;
}
.art-hero-logosbar span { display: inline-flex; align-items: center; gap: 6px; }

/* ── §2 STATS ───────────────────────────────────────────── */
.art-stats { padding: 56px 0; background: var(--ink); color: var(--bg); }
.art-stats-grid {
    display: grid; grid-template-columns: repeat(4,1fr); gap: 24px; text-align: center;
}
.art-stat-n {
    font-family: var(--fd); font-weight: 700;
    font-size: clamp(36px,4vw,52px); letter-spacing: -.02em;
    line-height: 1; color: var(--primary);
}
.art-stat-l { font-size: 14px; color: rgba(250,247,242,.65); margin-top: 8px; letter-spacing: .3px; }

/* ── §3 SERVICES ─────────────────────────────────────────── */
.art-svc-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 48px; gap: 24px; flex-wrap: wrap; }
.art-svc-grid   { display: grid; grid-template-columns: repeat(auto-fill,minmax(300px,1fr)); gap: 20px; }
.art-svc-card   { overflow: hidden; display: flex; flex-direction: column; cursor: pointer; transition: transform .15s, box-shadow .15s; }
.art-svc-card:hover { transform: translateY(-3px); box-shadow: var(--sh-md); }
.art-svc-icon {
    position: absolute; top: 12px; left: 12px;
    width: 40px; height: 40px; border-radius: 10px;
    background: rgba(255,255,255,.95); color: var(--primary);
    display: flex; align-items: center; justify-content: center;
}
.art-svc-body { padding: 24px; display: flex; flex-direction: column; gap: 10px; flex: 1; }
.art-svc-more { display: inline-flex; align-items: center; gap: 6px; color: var(--primary); font-weight: 600; font-size: 14px; margin-top: auto; }

/* ── §4 HOW IT WORKS ─────────────────────────────────────── */
.art-how-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; }
.art-how-n { font-family: var(--fd); font-weight: 700; font-size: 14px; color: var(--primary); letter-spacing: .5px; margin-bottom: 14px; }
.art-how-icon { width: 48px; height: 48px; border-radius: 12px; background: var(--primary-soft); color: var(--primary); display: flex; align-items: center; justify-content: center; margin-bottom: 16px; }

/* ── §5 WHY US ───────────────────────────────────────────── */
.art-why-grid { display: grid; grid-template-columns: 1fr 1.3fr; gap: 64px; align-items: center; }
.art-why-img  { border-radius: var(--r-lg); overflow: hidden; aspect-ratio: 4/5; background-size: cover; background-position: center; position: relative; }
.art-why-quote {
    position: absolute; bottom: 20px; left: 20px; right: 20px; padding: 18px;
    background: rgba(255,255,255,.95); border-radius: var(--r-sm); backdrop-filter: blur(8px);
}
.art-why-benefits { display: grid; grid-template-columns: repeat(2,1fr); gap: 16px; margin-top: 28px; }
.art-why-benefit  { display: flex; gap: 12px; align-items: flex-start; }
.art-why-ico { width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0; background: var(--primary-soft); color: var(--primary); display: flex; align-items: center; justify-content: center; }

/* ── §6 RÉALISATIONS ─────────────────────────────────────── */
.art-real-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 20px; }
.art-real-card { overflow: hidden; cursor: pointer; }
.art-real-place { position: absolute; top: 12px; left: 12px; background: rgba(255,255,255,.95); color: var(--ink); padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }

/* ── §7 AVIS ─────────────────────────────────────────────── */
.art-avis-grid { display: grid; grid-template-columns: repeat(auto-fill,minmax(300px,1fr)); gap: 16px; }
.art-avis-card { padding: 28px; display: flex; flex-direction: column; gap: 16px; height: 100%; }
.art-avis-footer { display: flex; align-items: center; gap: 12px; padding-top: 12px; border-top: 1px solid var(--line); }
.art-avis-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--bg-soft); color: var(--ink-2); display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; }

/* ── §8 VILLES ───────────────────────────────────────────── */
.art-city-pill {
    padding: 10px 16px; background: var(--bg-card); border: 1px solid var(--line);
    border-radius: 999px; font-size: 14px; font-weight: 500;
    display: inline-flex; align-items: center; gap: 6px;
    cursor: pointer; color: var(--ink); text-decoration: none;
    transition: border-color .15s, color .15s;
}
.art-city-pill:hover { border-color: var(--primary); color: var(--primary); }
.art-city-pills { display: flex; flex-wrap: wrap; gap: 8px; }

/* ── §9 CONTACT ──────────────────────────────────────────── */
.art-contact-grid { display: grid; grid-template-columns: 1fr 1.3fr; gap: 56px; align-items: flex-start; }
.art-contact-item {
    display: flex; align-items: center; gap: 14px; padding: 18px;
    background: var(--bg-card); border: 1px solid var(--line); border-radius: var(--r);
    text-decoration: none; color: var(--ink);
}
.art-contact-ico { width: 44px; height: 44px; border-radius: 12px; background: var(--primary-soft); color: var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.art-contact-label { font-size: 12px; color: var(--ink-3); text-transform: uppercase; letter-spacing: .5px; font-weight: 600; }
.art-contact-val   { font-weight: 700; font-size: 17px; }

/* Form */
.art-form { display: flex; flex-direction: column; gap: 14px; }
.art-field { display: flex; flex-direction: column; gap: 6px; }
.art-field label { font-size: 13px; font-weight: 500; color: var(--ink-2); }
.art-field input, .art-field select, .art-field textarea {
    width: 100%; padding: 0 14px; height: 48px;
    border: 1px solid var(--line-s); border-radius: 10px;
    font-family: var(--fb); font-size: 15px; background: var(--bg-card);
    color: var(--ink); outline: none; transition: border-color .15s;
}
.art-field input:focus, .art-field select:focus, .art-field textarea:focus {
    border-color: var(--primary);
}
.art-field textarea { height: 96px; padding-top: 12px; resize: vertical; }
.art-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.art-submit {
    width: 100%; height: 54px; margin-top: 8px;
    background: var(--primary); color: var(--primary-fg);
    border: 0; border-radius: 999px; font-family: var(--fb);
    font-size: 16px; font-weight: 600; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: filter .15s, transform .15s;
}
.art-submit:hover { filter: brightness(.9); transform: translateY(-1px); }

/* CTA strip */
.art-cta-strip { padding: 64px 0; background: var(--ink); color: var(--bg); }
.art-cta-inner { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 32px; }

/* Inline SVG icons */
.art-ico { display: inline-flex; align-items: center; justify-content: center; }
.art-ico svg { stroke: currentColor; fill: none; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; }

/* ── RESPONSIVE ───────────────────────────────────────────── */
@media (max-width: 900px) {
    .art-stats-grid  { grid-template-columns: repeat(2,1fr); gap: 32px; }
    .art-how-grid    { grid-template-columns: repeat(2,1fr); }
    .art-why-grid    { grid-template-columns: 1fr; gap: 32px; }
    .art-real-grid   { grid-template-columns: 1fr; }
    .art-contact-grid{ grid-template-columns: 1fr; gap: 32px; }
    .art-hero-logosbar { gap: 20px; font-size: 11px; }
    .art-hero-trust-divider { display: none; }
}
@media (max-width: 640px) {
    .art-how-grid  { grid-template-columns: 1fr; }
    .art-svc-grid  { grid-template-columns: 1fr; }
    .art-avis-grid { grid-template-columns: 1fr; }
    .art-form-row  { grid-template-columns: 1fr; }
    .art-why-benefits { grid-template-columns: 1fr; }
    .art-sec { padding: 56px 0; }
}
</style>
@endpush

<div class="art-root">

{{-- ═══════════════════════════════════════════════
     §1  HERO FULL-BLEED
═══════════════════════════════════════════════ --}}
<section class="art-hero" style="padding: 0;">
    {{-- Background image --}}
    <div class="art-hero-bg"
         style="{{ $heroImgUrl ? 'background-image:url(' . e($heroImgUrl) . ');' : 'background:#1F1A14;' }}">
    </div>
    <div class="art-hero-overlay"></div>
    <div class="art-hero-overlay2"></div>

    <div class="art-hero-inner">
        <div class="art-hero-content">

            {{-- Badge disponible --}}
            <div class="art-hero-badge">
                <span class="art-hero-badge-dot"></span>
                Disponible aujourd'hui · {{ $city }}
            </div>

            {{-- Titre --}}
            <h1 style="color:#fff;">{{ $heroTitle }}</h1>

            {{-- Sous-titre --}}
            <p style="font-size:clamp(17px,1.4vw,21px); color:rgba(255,255,255,.88); margin-top:24px; max-width:620px; line-height:1.55;">
                {{ $heroSub }}
            </p>

            {{-- CTAs --}}
            <div class="art-hero-ctas">
                <a href="#contact-form"
                   class="art-btn art-btn-primary art-btn-lg"
                   style="box-shadow:0 8px 30px rgba(0,0,0,.35);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    Devis gratuit en 1 min
                </a>
                <a href="tel:{{ $phoneRaw }}"
                   class="art-btn art-btn-lg art-hero-cta-tel">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
                    {{ $phone }}
                </a>
            </div>

            {{-- Trust badges --}}
            <div class="art-hero-trust">
                @if($reviewCount > 0)
                <div style="display:flex; align-items:center; gap:12px;">
                    <div class="art-hero-avatars">
                        @foreach($displayedReviews->take(4) as $idx => $rev)
                            <div class="art-hero-avatar"
                                 style="background:{{ ['#D9C7A0','#C2B59B','#A89B7E','#8C7B5E'][$idx % 4] }}; margin-left:{{ $idx === 0 ? '0' : '-10px' }};">
                                {{ mb_strtoupper(mb_substr($rev->author_name ?? 'C', 0, 1)) }}
                            </div>
                        @endforeach
                    </div>
                    <div>
                        <div style="display:flex; align-items:center; gap:8px; color:#FFD166;">
                            @for($s=1;$s<=5;$s++)<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>@endfor
                            <span style="color:#fff; margin-left:6px; font-weight:700; font-size:14px;">{{ number_format($ratingVal, 1, ',', '') }} / 5</span>
                        </div>
                        <div style="font-size:13px; color:rgba(255,255,255,.7);">{{ $reviewCount }} avis Google vérifiés</div>
                    </div>
                </div>
                <div class="art-hero-trust-divider"></div>
                @endif
                <div style="display:flex; align-items:center; gap:10px;">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>
                    <div>
                        <div style="font-weight:700; font-size:14px; color:#fff;">Garantie décennale</div>
                        <div style="font-size:13px; color:rgba(255,255,255,.7);">Artisan certifié</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Logos bar --}}
    <div class="art-hero-logosbar">
        <span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/></svg> Garantie décennale</span>
        <span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="m15.5 12.5 2.5 9-6-3-6 3 2.5-9"/></svg> Artisan certifié</span>
        <span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg> Devis gratuit</span>
        @if($reviewCount > 0)
        <span><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg> {{ number_format($ratingVal, 1, ',', '') }}/5 Google</span>
        @endif
        <span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76Z"/></svg> +de 500 chantiers</span>
    </div>
</section>


{{-- ═══════════════════════════════════════════════
     §2  STATS — bande sombre
═══════════════════════════════════════════════ --}}
<section class="art-stats">
    <div class="art-w">
        <div class="art-stats-grid">
            <div>
                <div class="art-stat-n">500+</div>
                <div class="art-stat-l">Chantiers réalisés</div>
            </div>
            <div>
                <div class="art-stat-n">{{ $reviewCount > 0 ? number_format($ratingVal, 1, ',', '') . '/5' : '5/5' }}</div>
                <div class="art-stat-l">Note Google {{ $reviewCount > 0 ? '· ' . $reviewCount . ' avis' : '' }}</div>
            </div>
            <div>
                <div class="art-stat-n">24h</div>
                <div class="art-stat-l">Délai de réponse moyen</div>
            </div>
            <div>
                <div class="art-stat-n">10 ans</div>
                <div class="art-stat-l">Garantie décennale</div>
            </div>
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════
     §3  SERVICES avec photos
═══════════════════════════════════════════════ --}}
@if(!empty($svcList))
<section class="art-sec">
    <div class="art-w">
        <div class="art-svc-header">
            <div class="art-sh" style="margin-bottom:0;">
                <div class="art-eyebrow">Nos services</div>
                <h2>Tout ce qu'on peut faire pour vous</h2>
                <p>Un seul interlocuteur, un travail soigné, un devis clair.</p>
            </div>
            <a href="{{ route('services.index') }}" class="art-btn art-btn-ghost">
                Voir tous les services
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            </a>
        </div>
        <div class="art-svc-grid">
            @foreach(array_slice($svcList, 0, 6) as $svc)
            @php
                $svcImg = null;
                if (!empty($svc['image'])) {
                    $svcImg = strpos($svc['image'], 'http') === 0 ? $svc['image'] : asset(ltrim($svc['image'], '/'));
                }
                $svcName = $svc['name'] ?? $svc['title'] ?? 'Service';
                $svcDesc = $svc['description'] ?? $svc['desc'] ?? '';
                $svcSlug = $svc['slug'] ?? \Illuminate\Support\Str::slug($svcName);
            @endphp
            <a href="{{ route('services.show', $svcSlug) }}" class="art-card art-svc-card">
                <div class="art-photo" style="height:200px; {{ $svcImg ? 'background-image:url(' . e($svcImg) . ');' : 'background:linear-gradient(135deg, var(--primary-soft), var(--bg-soft));' }}">
                    <div class="art-svc-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    </div>
                </div>
                <div class="art-svc-body">
                    <h3>{{ $svcName }}</h3>
                    <p style="color:var(--ink-3); font-size:14.5px; line-height:1.55; flex:1;">{{ Str::limit($svcDesc, 120) }}</p>
                    <span class="art-svc-more">
                        En savoir plus
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif


{{-- ═══════════════════════════════════════════════
     §4  COMMENT ÇA MARCHE
═══════════════════════════════════════════════ --}}
<section class="art-sec" style="background:var(--bg-soft);">
    <div class="art-w">
        <div class="art-sh" style="text-align:center; margin-inline:auto;">
            <div class="art-eyebrow" style="justify-content:center;">Comment ça marche</div>
            <h2>De l'appel au chantier fini, en 4 étapes simples.</h2>
        </div>
        <div class="art-how-grid">
            @php
            $steps = [
                ['n'=>'01','t'=>'Vous nous appelez','d'=>'Premier échange par téléphone, gratuit et sans engagement.','icon'=>'phone'],
                ['n'=>'02','t'=>'Visite chez vous','d'=>'On se déplace pour voir le chantier et vous écouter.','icon'=>'home'],
                ['n'=>'03','t'=>'Devis sous 24h','d'=>'Détaillé, ligne par ligne, prix garanti.','icon'=>'mail'],
                ['n'=>'04','t'=>'On réalise','d'=>'Chantier propre, équipe formée, livraison à la date prévue.','icon'=>'tools'],
            ];
            $stepIcons = [
                'phone'=>'<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92Z"/>',
                'home' =>'<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
                'mail' =>'<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
                'tools'=>'<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76Z"/>',
            ];
            @endphp
            @foreach($steps as $step)
            <div class="art-card" style="padding:28px;">
                <div class="art-how-n">{{ $step['n'] }}</div>
                <div class="art-how-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">{!! $stepIcons[$step['icon']] !!}</svg>
                </div>
                <h3 style="font-size:19px;">{{ $step['t'] }}</h3>
                <p style="color:var(--ink-3); font-size:14px; margin-top:8px; line-height:1.55;">{{ $step['d'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════
     §5  POURQUOI NOUS
═══════════════════════════════════════════════ --}}
<section class="art-sec">
    <div class="art-w">
        <div class="art-why-grid">
            {{-- Image --}}
            @php
                $aboutImg = $homeConfig['about']['image'] ?? null;
                $aboutImgUrl = $aboutImg ? asset(ltrim($aboutImg, '/')) : null;
            @endphp
            <div class="art-why-img"
                 style="{{ $aboutImgUrl ? 'background-image:url(' . e($aboutImgUrl) . ');' : 'background:linear-gradient(135deg,var(--bg-soft),var(--ink));' }}">
                <div class="art-why-quote">
                    <div style="display:flex; gap:8px; align-items:center; margin-bottom:6px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="var(--primary)" stroke="none"><path d="M9.4 7C5.9 7 3 9.9 3 13.4V19h6v-6H6c0-2 1.4-3.4 3.4-3.4V7Zm11.6 0c-3.5 0-6.4 2.9-6.4 6.4V19h6v-6h-3c0-2 1.4-3.4 3.4-3.4V7Z"/></svg>
                        <span style="font-weight:600; font-size:13.5px;">{{ $homeConfig['sections']['about']['title'] ?? 'Votre artisan de confiance' }}</span>
                    </div>
                    <div style="font-size:12.5px; color:var(--ink-3);">— {{ $name }}, {{ $city }}</div>
                </div>
            </div>
            {{-- Texte --}}
            <div>
                <div class="art-eyebrow">Pourquoi nous ?</div>
                <h2>Un artisan, pas une multinationale.</h2>
                <p style="color:var(--ink-2); margin-top:16px; font-size:17px; line-height:1.6;">
                    {{ $homeConfig['sections']['about']['text'] ?? 'On habite le coin, on connaît les maisons d\'ici, et on tient à notre réputation. Pas de sous-traitance, pas de surprise sur le devis, pas de client laissé sans nouvelles.' }}
                </p>
                <div class="art-why-benefits">
                    @php
                    $benefits = [
                        ['icon'=>'<path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>','t'=>'Rapide','d'=>'Devis sous 24h.'],
                        ['icon'=>'<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/>','t'=>'Garanti','d'=>'Décennale incluse.'],
                        ['icon'=>'<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76Z"/>','t'=>'Soigné','d'=>'Chantier propre.'],
                        ['icon'=>'<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>','t'=>'Humain','d'=>'Un seul interlocuteur.'],
                    ];
                    @endphp
                    @foreach($benefits as $b)
                    <div class="art-why-benefit">
                        <div class="art-why-ico">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">{!! $b['icon'] !!}</svg>
                        </div>
                        <div>
                            <div style="font-weight:700; font-size:16px;">{{ $b['t'] }}</div>
                            <div style="color:var(--ink-3); font-size:14px; margin-top:2px;">{{ $b['d'] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════
     §6  RÉALISATIONS
═══════════════════════════════════════════════ --}}
@if(!empty($displayedPortfolio))
<section class="art-sec" style="background:var(--bg-soft);">
    <div class="art-w">
        <div class="art-svc-header">
            <div class="art-sh" style="margin-bottom:0;">
                <div class="art-eyebrow">Réalisations</div>
                <h2>Des chantiers récents, dans votre région</h2>
                <p>Photos prises sur place, jamais de stock.</p>
            </div>
            <a href="{{ route('portfolio.index') }}" class="art-btn art-btn-ghost">
                Toutes nos réalisations
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            </a>
        </div>
        <div class="art-real-grid">
            @foreach($displayedPortfolio as $item)
            @php
                $imgs = $item['images'] ?? [];
                $firstImg = is_array($imgs) && count($imgs) > 0 ? $imgs[0] : null;
                $imgUrl = $firstImg ? (strpos($firstImg, 'http') === 0 ? $firstImg : asset(ltrim($firstImg, '/'))) : null;
                $pSlug = $item['slug'] ?? \Illuminate\Support\Str::slug($item['title'] ?? 'realisation');
            @endphp
            <a href="{{ route('portfolio.show', $pSlug) }}" class="art-card art-real-card">
                <div class="art-photo" style="height:240px; {{ $imgUrl ? 'background-image:url(' . e($imgUrl) . ');' : 'background:linear-gradient(135deg,var(--primary-soft),var(--bg-soft));' }}">
                    @if(!empty($item['city']) || !empty($item['location']))
                    <div class="art-real-place">
                        📍 {{ $item['city'] ?? $item['location'] ?? '' }}
                    </div>
                    @endif
                </div>
                <div style="padding:20px;">
                    <h3 style="font-size:18px; margin-bottom:6px;">{{ $item['title'] ?? 'Réalisation' }}</h3>
                    <p style="color:var(--ink-3); font-size:14px;">{{ Str::limit($item['description'] ?? '', 100) }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif


{{-- ═══════════════════════════════════════════════
     §7  AVIS CLIENTS
═══════════════════════════════════════════════ --}}
@if($displayedReviews->count() > 0)
<section class="art-sec">
    <div class="art-w">
        <div class="art-sh" style="text-align:center; margin-inline:auto;">
            <div class="art-eyebrow" style="justify-content:center;">Ce qu'on en dit</div>
            <h2>{{ $reviewCount }} avis · {{ number_format($ratingVal, 1, ',', '') }} sur 5 sur Google</h2>
            <p style="margin:12px auto 0;">Pas de tri, pas de filtre.</p>
        </div>
        <div class="art-avis-grid">
            @foreach($displayedReviews as $review)
            <div class="art-card art-avis-card">
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <div class="art-stars art-stars-google">
                        @for($s=1;$s<=(int)($review->rating ?? 5);$s++)
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        @endfor
                    </div>
                    {{-- Google G --}}
                    <svg width="20" height="20" viewBox="0 0 24 24"><path d="M22.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.4h5.9c-.3 1.4-1 2.5-2.2 3.3v2.7h3.5c2-1.9 3.3-4.7 3.3-8.1Z" fill="#4285F4"/><path d="M12 23c3 0 5.5-1 7.3-2.7l-3.5-2.7c-1 .7-2.2 1-3.7 1-2.9 0-5.3-1.9-6.2-4.5H2.3v2.8C4.1 20.5 7.8 23 12 23Z" fill="#34A853"/><path d="M5.8 14.1a6.7 6.7 0 0 1 0-4.2V7.1H2.3a11 11 0 0 0 0 9.8l3.5-2.8Z" fill="#FBBC05"/><path d="M12 5.4c1.6 0 3.1.6 4.2 1.7l3.1-3.1A11 11 0 0 0 12 1C7.8 1 4.1 3.5 2.3 7.1l3.5 2.8C6.7 7.3 9.1 5.4 12 5.4Z" fill="#EA4335"/></svg>
                </div>
                <p style="font-size:15px; line-height:1.6; color:var(--ink-2); flex:1;">« {{ $review->comment ?? $review->content ?? '' }} »</p>
                <div class="art-avis-footer">
                    <div class="art-avis-avatar">{{ mb_strtoupper(mb_substr($review->author_name ?? 'C', 0, 1)) }}</div>
                    <div>
                        <div style="font-weight:600; font-size:14px;">{{ $review->author_name ?? 'Client' }}</div>
                        <div style="font-size:12px; color:var(--ink-3);">{{ $review->review_date ? \Carbon\Carbon::parse($review->review_date)->translatedFormat('d M Y') : '' }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif


{{-- ═══════════════════════════════════════════════
     §8  ZONE D'INTERVENTION
═══════════════════════════════════════════════ --}}
@if(isset($favoriteCities) && $favoriteCities->count() > 0)
<section class="art-sec tight" style="background:var(--bg-soft);">
    <div class="art-w">
        <div class="art-sh" style="max-width:720px;">
            <div class="art-eyebrow">Zone d'intervention</div>
            <h2>On intervient à {{ $city }} et dans un rayon de 30 km.</h2>
            <p style="color:var(--ink-3);">Une demande hors zone ? Appelez-nous, on regarde au cas par cas.</p>
        </div>
        <div class="art-city-pills">
            @foreach($favoriteCities as $fc)
            <a href="{{ route('ads.index') . '?city=' . $fc->slug }}" class="art-city-pill">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                {{ $fc->name }}
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif


{{-- ═══════════════════════════════════════════════
     §9  FORMULAIRE CONTACT
═══════════════════════════════════════════════ --}}
<section class="art-sec" id="contact-form" style="padding:88px 0; background:var(--bg);">
    <div class="art-w">
        <div class="art-contact-grid">
            {{-- Infos --}}
            <div>
                <div class="art-eyebrow">Demandez votre devis</div>
                <h2>Parlons de votre projet.</h2>
                <p style="color:var(--ink-3); font-size:17px; margin-top:16px; line-height:1.6;">
                    Remplissez le formulaire ou appelez-nous directement. On vous rappelle dans les 24h, devis détaillé sans engagement.
                </p>
                <div style="display:flex; flex-direction:column; gap:18px; margin-top:32px;">
                    <a href="tel:{{ $phoneRaw }}" class="art-contact-item">
                        <div class="art-contact-ico">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
                        </div>
                        <div>
                            <div class="art-contact-label">Téléphone</div>
                            <div class="art-contact-val">{{ $phone }}</div>
                        </div>
                    </a>
                    <div class="art-contact-item">
                        <div class="art-contact-ico">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        </div>
                        <div>
                            <div class="art-contact-label">Horaires</div>
                            <div class="art-contact-val" style="font-size:15px;">Lun–Sam · 8h – 19h</div>
                        </div>
                    </div>
                    <div class="art-contact-item">
                        <div class="art-contact-ico">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <div>
                            <div class="art-contact-label">Zone</div>
                            <div class="art-contact-val" style="font-size:15px;">{{ $city }} et 30 km autour</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Formulaire --}}
            <div class="art-card" style="padding:32px;">
                <h3 style="font-size:22px; margin-bottom:6px;">Demande de devis gratuit</h3>
                <p style="color:var(--ink-3); font-size:14px; margin-bottom:24px;">Réponse sous 24h · Sans engagement</p>

                <form action="{{ route('contact.send') }}" method="POST" class="art-form">
                    @csrf
                    <div class="art-form-row">
                        <div class="art-field">
                            <label for="art-name">Votre nom *</label>
                            <input type="text" id="art-name" name="name" placeholder="Marie Dupont" required>
                        </div>
                        <div class="art-field">
                            <label for="art-phone">Téléphone *</label>
                            <input type="tel" id="art-phone" name="phone" placeholder="06 12 34 56 78" required>
                        </div>
                    </div>
                    <div class="art-field">
                        <label for="art-email">Email</label>
                        <input type="email" id="art-email" name="email" placeholder="marie@email.fr">
                    </div>
                    @if(!empty($svcList))
                    <div class="art-field">
                        <label for="art-service">Type de besoin</label>
                        <select id="art-service" name="service">
                            <option value="">Sélectionnez…</option>
                            @foreach($svcList as $svc)
                            <option value="{{ $svc['name'] ?? $svc['title'] ?? '' }}">{{ $svc['name'] ?? $svc['title'] ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="art-field">
                        <label for="art-message">Décrivez votre projet</label>
                        <textarea id="art-message" name="message" placeholder="Quelques détails sur ce dont vous avez besoin…"></textarea>
                    </div>
                    <button type="submit" class="art-submit">
                        Envoyer ma demande
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </button>
                    <p style="font-size:12px; color:var(--ink-3); text-align:center; margin-top:4px;">
                        En envoyant, vous acceptez d'être recontacté par {{ $name }}.
                    </p>
                </form>
            </div>
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════
     §10  FAQ (si dispo)
═══════════════════════════════════════════════ --}}
@if(!empty($faqs))
<section class="art-sec" style="background:var(--bg-soft);">
    <div class="art-w">
        <div class="art-sh" style="text-align:center; margin-inline:auto;">
            <div class="art-eyebrow" style="justify-content:center;">Questions fréquentes</div>
            <h2>On répond à tout.</h2>
        </div>
        <div style="max-width:760px; margin:0 auto; display:flex; flex-direction:column; gap:12px;">
            @foreach(array_slice($faqs, 0, 6) as $faq)
            <details class="art-card" style="padding:20px 24px; cursor:pointer;">
                <summary style="font-weight:700; font-size:17px; list-style:none; display:flex; justify-content:space-between; align-items:center; gap:12px; color:var(--ink);">
                    {{ $faq['question'] ?? $faq['q'] ?? '' }}
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="m6 9 6 6 6-6"/></svg>
                </summary>
                <p style="margin-top:12px; color:var(--ink-2); font-size:15px; line-height:1.6;">{{ $faq['answer'] ?? $faq['r'] ?? $faq['a'] ?? '' }}</p>
            </details>
            @endforeach
        </div>
    </div>
</section>
@endif

</div>{{-- .art-root --}}
