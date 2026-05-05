{{-- ============================================================
     LANDING PAGE — Magazine Éditorial
     Style : dark bold, 2-column split hero, typographie massive
     Dép. 60 — Élagage professionnel
     ============================================================ --}}

@php
    $phone    = $phone    ?? setting('company_phone', '06 42 21 41 51');
    $phoneRaw = $phoneRaw ?? setting('company_phone_raw', preg_replace('/\s+/', '', $phone));
    $heroTitle = $heroTitle ?? ($homeConfig['sections']['hero']['title'] ?? 'Élagueur & Abatteur professionnel dans l\'Oise (60)');

    $heroImgRaw = $homeConfig['hero']['background_image'] ?? null;
    $heroImgUrl = $heroImgUrl ?? ($heroImgRaw ? asset(ltrim($heroImgRaw, '/')) : null);

    $svcLimit  = $homeConfig['sections']['services']['limit']  ?? 6;
    $svcTitle  = $homeConfig['sections']['services']['title']  ?? 'Nos Services';
    $svcOn     = $homeConfig['sections']['services']['enabled'] ?? true;

    $aboutOn   = $homeConfig['sections']['about']['enabled'] ?? true;
    $aboutTitle = $homeConfig['sections']['about']['title'] ?? 'À Propos de Nous';
    $aboutText  = $homeConfig['sections']['about']['text']  ?? 'Entreprise familiale spécialisée dans l\'élagage, l\'abattage et l\'entretien des espaces verts dans l\'Oise depuis plus de 15 ans.';
    $aboutImg   = $homeConfig['about']['image'] ?? null;
    $aboutImgUrl = $aboutImg ? asset(ltrim($aboutImg, '/')) : null;

    $reviewsOn    = $homeConfig['sections']['reviews']['enabled'] ?? true;
    $reviewsTitle = $homeConfig['sections']['reviews']['title']   ?? 'Ce que disent nos clients';
    $reviewsLimit = $homeConfig['sections']['reviews']['limit']   ?? 6;

    $ctaOn    = $homeConfig['sections']['cta']['enabled'] ?? true;
    $ctaTitle = $homeConfig['sections']['cta']['title']   ?? 'Besoin d\'un élagueur professionnel ?';

    $companyName = setting('company_name', 'Élagueur Professionnel Oise');
    $companyLogo = setting('company_logo');
@endphp

{{-- ═══════════════════════════════════════════════════════════════
     STYLES MAGAZINE
     ═══════════════════════════════════════════════════════════════ --}}
<style>
/* ─── Reset ─────────────────────────────────────────────────────── */
.lp *, .lp *::before, .lp *::after { box-sizing: border-box; margin: 0; padding: 0; }
.lp { font-family: inherit; color: #e8ede9; background: #0d1110; }
.lp a { text-decoration: none; }
.lp img { display: block; max-width: 100%; }

/* ─── Shell ──────────────────────────────────────────────────────── */
.mag-shell {
    max-width: 1320px;
    margin: 0 auto;
    padding: 0 1.25rem;
}
@media (min-width: 768px)  { .mag-shell { padding: 0 2rem; } }
@media (min-width: 1280px) { .mag-shell { padding: 0 3rem; } }

/* ─── Section Label ──────────────────────────────────────────────── */
.mag-label {
    display: inline-flex;
    align-items: center;
    gap: .6rem;
    font-size: .68rem;
    font-weight: 900;
    letter-spacing: .22em;
    text-transform: uppercase;
    color: var(--primary-color);
    margin-bottom: 1.25rem;
}
.mag-label::before {
    content: '';
    display: block;
    width: 2rem;
    height: 2px;
    background: var(--primary-color);
    flex-shrink: 0;
}

/* ═══════════════════════════════════
   §1 HERO — 2-column split
   ═══════════════════════════════════ */
.mag-hero {
    min-height: 90vh;
    background: #0d1110;
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
}
.mag-hero-grid {
    display: grid;
    grid-template-columns: 55% 45%;
    min-height: 90vh;
}
.mag-hero-left {
    background: #111716;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 5rem 3.5rem 5rem 4rem;
    position: relative;
    z-index: 1;
}
.mag-hero-tag {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    font-size: .65rem;
    font-weight: 900;
    letter-spacing: .25em;
    text-transform: uppercase;
    color: var(--primary-color);
    border: 1.5px solid var(--primary-color);
    padding: .35rem .9rem;
    border-radius: 2px;
    margin-bottom: 2rem;
    width: fit-content;
}
.mag-hero-h1 {
    font-size: clamp(3.5rem, 8vw, 7rem);
    font-weight: 950;
    line-height: 0.88;
    letter-spacing: -.03em;
    text-transform: uppercase;
    color: #f0f5f1;
    margin-bottom: 2rem;
}
.mag-hero-h1 .accent {
    color: var(--primary-color);
    display: block;
}
.mag-hero-sub {
    font-size: 1.05rem;
    line-height: 1.65;
    color: #8fa896;
    max-width: 32rem;
    margin-bottom: 2.75rem;
}
.mag-hero-ctas {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 3rem;
}
.mag-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: .6rem;
    background: var(--primary-color);
    color: #fff;
    font-size: .85rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    padding: .9rem 2rem;
    border: 2px solid var(--primary-color);
    border-radius: 2px;
    cursor: pointer;
    transition: background .2s, color .2s, transform .15s;
}
.mag-btn-primary:hover { background: transparent; color: var(--primary-color); transform: translateY(-2px); }
.mag-btn-outline {
    display: inline-flex;
    align-items: center;
    gap: .6rem;
    background: transparent;
    color: #f0f5f1;
    font-size: .85rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    padding: .9rem 2rem;
    border: 2px solid rgba(240,245,241,.35);
    border-radius: 2px;
    transition: border-color .2s, color .2s, transform .15s;
}
.mag-btn-outline:hover { border-color: #f0f5f1; color: #fff; transform: translateY(-2px); }
.mag-hero-trust {
    display: flex;
    flex-wrap: wrap;
    gap: 1.75rem;
    padding-top: 2rem;
    border-top: 1px solid rgba(255,255,255,.1);
}
.mag-hero-trust-item {
    display: flex;
    flex-direction: column;
    gap: .15rem;
}
.mag-hero-trust-item .t-val {
    font-size: 1.3rem;
    font-weight: 900;
    color: var(--primary-color);
    line-height: 1;
}
.mag-hero-trust-item .t-lbl {
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: #6b8070;
}
/* Right photo column */
.mag-hero-right {
    position: relative;
    overflow: hidden;
    background: #0a1209;
}
.mag-hero-right img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center 25%;
    display: block;
}
.mag-hero-right-fallback {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(160deg, #132018 0%, #0a1209 60%, #0d1110 100%);
}
.mag-hero-right-fallback i {
    font-size: 6rem;
    color: var(--primary-color);
    opacity: .35;
}
.mag-hero-overlay-stats {
    position: absolute;
    bottom: 2rem;
    left: 1.5rem;
    right: 1.5rem;
    display: flex;
    gap: .75rem;
    flex-wrap: wrap;
}
.mag-hero-stat-badge {
    background: rgba(10,18,9,.82);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(var(--primary-rgb, 46,160,67),.35);
    border-radius: 3px;
    padding: .6rem 1rem;
    text-align: center;
    flex: 1;
    min-width: 7rem;
}
.mag-hero-stat-badge .sv { font-size: 1.4rem; font-weight: 900; color: var(--primary-color); line-height: 1; }
.mag-hero-stat-badge .sl { font-size: .58rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: #8fa896; margin-top: .2rem; }

@media (max-width: 900px) {
    .mag-hero-grid {
        grid-template-columns: 1fr;
        min-height: auto;
    }
    .mag-hero-right {
        order: -1;
        height: 60vw;
        min-height: 240px;
    }
    .mag-hero-left {
        padding: 2.5rem 1.5rem 3rem;
    }
    .mag-hero-h1 { font-size: clamp(2.5rem, 10vw, 4rem); }
    .mag-hero-overlay-stats { bottom: 1rem; left: 1rem; right: 1rem; }
}

/* ═══════════════════════════════════
   §2 MARQUEE TICKER
   ═══════════════════════════════════ */
.mag-ticker {
    background: #070e08;
    border-top: 1px solid rgba(var(--primary-rgb,46,160,67),.2);
    border-bottom: 1px solid rgba(var(--primary-rgb,46,160,67),.2);
    padding: .7rem 0;
    overflow: hidden;
    white-space: nowrap;
}
.mag-ticker-track {
    display: inline-flex;
    animation: mag-ticker-scroll 32s linear infinite;
}
.mag-ticker-track:hover { animation-play-state: paused; }
.mag-ticker-item {
    display: inline-flex;
    align-items: center;
    gap: .7rem;
    font-size: .72rem;
    font-weight: 800;
    letter-spacing: .18em;
    text-transform: uppercase;
    color: #7aab85;
    padding: 0 2.5rem;
}
.mag-ticker-item i { color: var(--primary-color); font-size: .75rem; }
@keyframes mag-ticker-scroll {
    0%   { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}

/* ═══════════════════════════════════
   §3 SERVICES
   ═══════════════════════════════════ */
.mag-services {
    background: #111716;
    padding: 6rem 0 5rem;
}
.mag-services-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 2rem;
    margin-bottom: 3.5rem;
    flex-wrap: wrap;
}
.mag-services-h2 {
    font-size: clamp(2.2rem, 5vw, 3.8rem);
    font-weight: 950;
    line-height: .9;
    letter-spacing: -.03em;
    text-transform: uppercase;
    color: #f0f5f1;
}
.mag-svc-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
}
@media (max-width: 960px) { .mag-svc-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px) { .mag-svc-grid { grid-template-columns: 1fr; } }

.mag-svc-card {
    background: #0d1110;
    border-bottom: 3px solid var(--primary-color);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: transform .25s, box-shadow .25s;
    cursor: pointer;
}
.mag-svc-card:hover { transform: translateY(-4px); box-shadow: 0 20px 50px rgba(0,0,0,.45); }
.mag-svc-card-img {
    height: 220px;
    overflow: hidden;
    position: relative;
    background: #0a1209;
}
.mag-svc-card-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform .5s;
}
.mag-svc-card:hover .mag-svc-card-img img { transform: scale(1.06); }
.mag-svc-card-img-fallback {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #132018, #0a1209);
}
.mag-svc-card-img-fallback i { font-size: 3.5rem; color: var(--primary-color); opacity: .55; }
.mag-svc-card-body { padding: 1.5rem 1.75rem 2rem; flex: 1; display: flex; flex-direction: column; }
.mag-svc-card-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.2rem;
    height: 2.2rem;
    border: 1.5px solid rgba(var(--primary-rgb,46,160,67),.4);
    border-radius: 2px;
    margin-bottom: 1rem;
}
.mag-svc-card-icon i { font-size: .85rem; color: var(--primary-color); }
.mag-svc-card-title {
    font-size: 1.2rem;
    font-weight: 900;
    letter-spacing: -.01em;
    text-transform: uppercase;
    color: #f0f5f1;
    margin-bottom: .65rem;
    line-height: 1.15;
}
.mag-svc-card-desc {
    font-size: .85rem;
    line-height: 1.7;
    color: #7a9483;
    flex: 1;
    margin-bottom: 1.25rem;
}
.mag-svc-card-link {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    font-size: .7rem;
    font-weight: 900;
    letter-spacing: .18em;
    text-transform: uppercase;
    color: var(--primary-color);
    transition: gap .2s;
}
.mag-svc-card:hover .mag-svc-card-link { gap: .8rem; }

/* ═══════════════════════════════════
   §4 À PROPOS
   ═══════════════════════════════════ */
.mag-about {
    background: #f5f7f5;
    overflow: hidden;
}
.mag-about-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    min-height: 600px;
}
.mag-about-photo {
    position: relative;
    overflow: hidden;
    background: #132018;
    min-height: 500px;
}
.mag-about-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
}
.mag-about-photo-fallback {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(160deg, #132018, #0a1209);
    min-height: 500px;
}
.mag-about-photo-fallback i { font-size: 5rem; color: var(--primary-color); opacity: .4; }
.mag-about-text {
    background: #f5f7f5;
    padding: 5rem 4rem 5rem 4.5rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
    color: #0f1f13;
}
.mag-about-h2 {
    font-size: clamp(2rem, 4vw, 3.2rem);
    font-weight: 950;
    line-height: .92;
    letter-spacing: -.03em;
    text-transform: uppercase;
    color: #0f1f13;
    margin-bottom: 1.5rem;
}
.mag-about-text .mag-label { color: var(--primary-color); }
.mag-about-p {
    font-size: 1rem;
    line-height: 1.8;
    color: #374840;
    margin-bottom: 2rem;
    max-width: 36rem;
}
.mag-about-checklist {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: .75rem;
    margin-bottom: 2.5rem;
}
.mag-about-checklist li {
    display: flex;
    align-items: center;
    gap: .75rem;
    font-size: .88rem;
    font-weight: 700;
    color: #213d2a;
}
.mag-about-checklist li i { color: var(--primary-color); font-size: .9rem; flex-shrink: 0; }
.mag-about-ctas { display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 3rem; }
.mag-btn-dark {
    display: inline-flex;
    align-items: center;
    gap: .6rem;
    background: #0f1f13;
    color: #fff;
    font-size: .82rem;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
    padding: .85rem 1.75rem;
    border: 2px solid #0f1f13;
    border-radius: 2px;
    transition: background .2s, transform .15s;
}
.mag-btn-dark:hover { background: transparent; color: #0f1f13; transform: translateY(-2px); }
.mag-btn-green {
    display: inline-flex;
    align-items: center;
    gap: .6rem;
    background: var(--primary-color);
    color: #fff;
    font-size: .82rem;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
    padding: .85rem 1.75rem;
    border: 2px solid var(--primary-color);
    border-radius: 2px;
    transition: background .2s, color .2s, transform .15s;
}
.mag-btn-green:hover { background: transparent; color: var(--primary-color); transform: translateY(-2px); }
/* Stats bar */
.mag-about-statsbar {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    border-top: 1px solid rgba(15,31,19,.12);
    padding-top: 2rem;
}
.mag-about-stat { text-align: center; padding: .5rem; }
.mag-about-stat .asv {
    font-size: 2rem;
    font-weight: 950;
    letter-spacing: -.04em;
    color: var(--primary-color);
    line-height: 1;
}
.mag-about-stat .asl {
    font-size: .6rem;
    font-weight: 800;
    letter-spacing: .16em;
    text-transform: uppercase;
    color: #5a7565;
    margin-top: .3rem;
}

@media (max-width: 900px) {
    .mag-about-grid { grid-template-columns: 1fr; }
    .mag-about-photo { min-height: 300px; order: -1; }
    .mag-about-text { padding: 2.5rem 1.5rem; }
    .mag-about-statsbar { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
}

/* ═══════════════════════════════════
   §5 PROCESSUS — Editorial Timeline
   ═══════════════════════════════════ */
.mag-process {
    background: #0d1110;
    padding: 6rem 0;
    border-top: 1px solid rgba(255,255,255,.05);
}
.mag-process-h2 {
    font-size: clamp(2rem, 4.5vw, 3.5rem);
    font-weight: 950;
    line-height: .9;
    letter-spacing: -.03em;
    text-transform: uppercase;
    color: #f0f5f1;
    margin-bottom: 3.5rem;
}
.mag-process-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0;
    position: relative;
}
.mag-process-grid::after {
    content: '';
    position: absolute;
    top: 3.5rem;
    left: 3rem;
    right: 3rem;
    height: 1px;
    background: linear-gradient(90deg, var(--primary-color) 0%, rgba(var(--primary-rgb,46,160,67),.2) 100%);
    z-index: 0;
}
.mag-process-step {
    position: relative;
    z-index: 1;
    padding: 0 1.5rem 0 0;
    padding-top: 0;
}
.mag-process-num {
    font-size: 5rem;
    font-weight: 950;
    line-height: 1;
    letter-spacing: -.06em;
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    display: block;
    background: #0d1110;
    width: fit-content;
    padding-right: .75rem;
}
.mag-process-step-title {
    font-size: 1rem;
    font-weight: 900;
    letter-spacing: .04em;
    text-transform: uppercase;
    color: #f0f5f1;
    margin-bottom: .6rem;
}
.mag-process-step-text {
    font-size: .83rem;
    line-height: 1.75;
    color: #6b8070;
}

@media (max-width: 768px) {
    .mag-process-grid {
        grid-template-columns: 1fr;
        gap: 2.5rem;
    }
    .mag-process-grid::after { display: none; }
    .mag-process-step {
        display: flex;
        gap: 1.5rem;
        padding: 0;
        border-left: 2px solid var(--primary-color);
        padding-left: 1.5rem;
    }
    .mag-process-num { font-size: 3rem; margin-bottom: 0; padding-right: 0; flex-shrink: 0; line-height: 1.1; }
}

/* ═══════════════════════════════════
   §6 TÉMOIGNAGES — Pull-Quote Style
   ═══════════════════════════════════ */
.mag-reviews {
    background: #111716;
    padding: 6rem 0;
    border-top: 1px solid rgba(255,255,255,.05);
}
.mag-reviews-header { margin-bottom: 4rem; }
.mag-reviews-h2 {
    font-size: clamp(2rem, 4.5vw, 3.5rem);
    font-weight: 950;
    line-height: .92;
    letter-spacing: -.03em;
    text-transform: uppercase;
    color: #f0f5f1;
    margin-bottom: 1rem;
}
.mag-reviews-stars-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}
.mag-reviews-stars-row .stars i { color: #f59e0b; font-size: .9rem; }
.mag-reviews-stars-row .avg { font-size: 1.5rem; font-weight: 900; color: #f0f5f1; }
.mag-reviews-stars-row .total { font-size: .8rem; color: #6b8070; }
/* Featured pull-quote */
.mag-pull-quote {
    position: relative;
    background: #0d1110;
    border-left: 4px solid var(--primary-color);
    padding: 3rem 3.5rem 3rem 4rem;
    margin-bottom: 3rem;
    overflow: hidden;
}
.mag-pull-quote::before {
    content: '\201C';
    position: absolute;
    top: -1rem;
    left: 1.5rem;
    font-size: 10rem;
    font-weight: 900;
    line-height: 1;
    color: var(--primary-color);
    opacity: .12;
    font-family: Georgia, serif;
}
.mag-pull-text {
    font-size: clamp(1.1rem, 2.5vw, 1.5rem);
    font-weight: 700;
    line-height: 1.55;
    color: #d8ede0;
    font-style: italic;
    margin-bottom: 1.75rem;
    position: relative;
    z-index: 1;
}
.mag-pull-author {
    display: flex;
    align-items: center;
    gap: 1rem;
    position: relative;
    z-index: 1;
}
.mag-pull-author-avatar {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    background: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: 1rem;
    color: #fff;
}
.mag-pull-author-avatar img { width: 100%; height: 100%; object-fit: cover; }
.mag-pull-author-name { font-size: .9rem; font-weight: 800; color: #f0f5f1; }
.mag-pull-author-meta { font-size: .72rem; color: #6b8070; margin-top: .1rem; }

/* Review grid */
.mag-reviews-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
    margin-bottom: 2.5rem;
}
@media (max-width: 960px) { .mag-reviews-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px) { .mag-reviews-grid { grid-template-columns: 1fr; } }

.mag-review-card {
    background: #0d1110;
    border: 1px solid rgba(255,255,255,.07);
    padding: 1.75rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    transition: border-color .2s;
}
.mag-review-card:hover { border-color: rgba(var(--primary-rgb,46,160,67),.35); }
.mag-review-stars i { color: #f59e0b; font-size: .8rem; }
.mag-review-text {
    font-size: .84rem;
    line-height: 1.75;
    color: #8fa896;
    font-style: italic;
    flex: 1;
}
.mag-review-author {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding-top: 1rem;
    border-top: 1px solid rgba(255,255,255,.06);
}
.mag-review-avatar {
    width: 2.4rem;
    height: 2.4rem;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    background: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: .85rem;
    color: #fff;
}
.mag-review-avatar img { width: 100%; height: 100%; object-fit: cover; }
.mag-review-name { font-size: .82rem; font-weight: 800; color: #d8ede0; }
.mag-review-date { font-size: .68rem; color: #5a7565; }
.mag-reviews-cta-row { text-align: center; margin-top: 1rem; }

@media (max-width: 768px) {
    .mag-pull-quote { padding: 2rem 1.5rem 2rem 2rem; }
}

/* ═══════════════════════════════════
   §7 ZONES — City Pills
   ═══════════════════════════════════ */
.mag-zones {
    background: #0f1f13;
    padding: 5rem 0;
    border-top: 1px solid rgba(255,255,255,.05);
}
.mag-zones-h2 {
    font-size: clamp(1.8rem, 3.5vw, 2.8rem);
    font-weight: 950;
    line-height: .95;
    letter-spacing: -.03em;
    text-transform: uppercase;
    color: #f0f5f1;
    margin-bottom: .75rem;
}
.mag-zones-sub {
    font-size: .9rem;
    color: #6b8070;
    margin-bottom: 2.5rem;
}
.mag-zones-pills {
    display: flex;
    flex-wrap: wrap;
    gap: .6rem;
}
.mag-zone-pill {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    background: rgba(var(--primary-rgb,46,160,67),.08);
    border: 1px solid rgba(var(--primary-rgb,46,160,67),.25);
    color: #8fd4a0;
    font-size: .72rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    padding: .45rem 1rem;
    border-radius: 2px;
    transition: background .2s, color .2s;
}
.mag-zone-pill:hover {
    background: var(--primary-color);
    color: #fff;
    border-color: var(--primary-color);
}
.mag-zone-pill i { font-size: .6rem; }

/* ═══════════════════════════════════
   §8 FAQ — 2-column Accordion
   ═══════════════════════════════════ */
.mag-faq {
    background: #0d1110;
    padding: 6rem 0;
    border-top: 1px solid rgba(255,255,255,.05);
}
.mag-faq-h2 {
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 950;
    line-height: .9;
    letter-spacing: -.03em;
    text-transform: uppercase;
    color: #f0f5f1;
    margin-bottom: 3rem;
}
.mag-faq-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0 3rem;
}
@media (max-width: 768px) { .mag-faq-grid { grid-template-columns: 1fr; } }

.mag-faq-item {
    border-bottom: 1px solid rgba(255,255,255,.08);
}
.mag-faq-question {
    width: 100%;
    background: none;
    border: none;
    color: #d8ede0;
    font-size: .92rem;
    font-weight: 800;
    letter-spacing: .02em;
    text-align: left;
    padding: 1.25rem 0;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    transition: color .2s;
}
.mag-faq-question:hover { color: var(--primary-color); }
.mag-faq-question i { flex-shrink: 0; font-size: .8rem; color: var(--primary-color); transition: transform .3s; }
.mag-faq-answer {
    display: none;
    font-size: .85rem;
    line-height: 1.8;
    color: #6b8070;
    padding-bottom: 1.25rem;
}
.mag-faq-item.open .mag-faq-question i { transform: rotate(45deg); }
.mag-faq-item.open .mag-faq-answer { display: block; }

/* ═══════════════════════════════════
   §9 CTA FINAL
   ═══════════════════════════════════ */
.mag-cta-final {
    background: linear-gradient(135deg, var(--primary-color) 0%, #1a5c24 55%, #0f2e15 100%);
    padding: 7rem 0;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.mag-cta-final::before {
    content: '';
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
}
.mag-cta-inner { position: relative; z-index: 1; max-width: 720px; margin: 0 auto; }
.mag-cta-final-tag {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    font-size: .65rem;
    font-weight: 900;
    letter-spacing: .22em;
    text-transform: uppercase;
    color: rgba(255,255,255,.7);
    border: 1px solid rgba(255,255,255,.3);
    padding: .3rem .9rem;
    border-radius: 2px;
    margin-bottom: 1.75rem;
}
.mag-cta-final-h2 {
    font-size: clamp(2.5rem, 6vw, 5rem);
    font-weight: 950;
    line-height: .88;
    letter-spacing: -.04em;
    text-transform: uppercase;
    color: #fff;
    margin-bottom: 1.5rem;
}
.mag-cta-final-sub {
    font-size: 1rem;
    color: rgba(255,255,255,.75);
    margin-bottom: 3rem;
    line-height: 1.65;
}
.mag-cta-final-btns { display: flex; flex-wrap: wrap; justify-content: center; gap: 1.25rem; }
.mag-btn-white {
    display: inline-flex;
    align-items: center;
    gap: .6rem;
    background: #fff;
    color: #0f2e15;
    font-size: .85rem;
    font-weight: 900;
    letter-spacing: .1em;
    text-transform: uppercase;
    padding: 1rem 2.25rem;
    border: 2px solid #fff;
    border-radius: 2px;
    transition: background .2s, color .2s, transform .15s;
}
.mag-btn-white:hover { background: transparent; color: #fff; transform: translateY(-2px); }
.mag-btn-outline-white {
    display: inline-flex;
    align-items: center;
    gap: .6rem;
    background: transparent;
    color: #fff;
    font-size: .85rem;
    font-weight: 900;
    letter-spacing: .1em;
    text-transform: uppercase;
    padding: 1rem 2.25rem;
    border: 2px solid rgba(255,255,255,.5);
    border-radius: 2px;
    transition: border-color .2s, transform .15s;
}
.mag-btn-outline-white:hover { border-color: #fff; transform: translateY(-2px); }
</style>

{{-- ═══════════════════════════════════════════════════════════════
     MARKUP
     ═══════════════════════════════════════════════════════════════ --}}
<div class="lp">

    {{-- ═══════ §1 HERO MAGAZINE ═══════ --}}
    <section class="mag-hero">
        <div class="mag-hero-grid">

            {{-- Left Text Column --}}
            <div class="mag-hero-left">
                <span class="mag-hero-tag">
                    <i class="fas fa-certificate" style="font-size:.6rem;"></i>
                    Élagueur certifié &middot; Dép. 60
                </span>

                <h1 class="mag-hero-h1">
                    @php
                        $words = explode(' ', $heroTitle);
                        $mid   = max(1, (int)round(count($words) / 2));
                        $line1 = implode(' ', array_slice($words, 0, $mid));
                        $line2 = implode(' ', array_slice($words, $mid));
                    @endphp
                    {{ $line1 }}
                    <span class="accent">{{ $line2 }}</span>
                </h1>

                <p class="mag-hero-sub">
                    Intervention rapide dans tout le département de l'Oise.
                    Devis gratuit sous 24h, travaux soignés et assurés.
                </p>

                <div class="mag-hero-ctas">
                    <a href="{{ route('form.step', 'propertyType') }}"
                       class="mag-btn-primary"
                       onclick="trackFormClick('hero_magazine')">
                        <i class="fas fa-clipboard-list"></i>
                        Devis gratuit
                    </a>
                    <a href="tel:{{ $phoneRaw }}" class="mag-btn-outline">
                        <i class="fas fa-phone"></i>
                        {{ $phone }}
                    </a>
                </div>

                <div class="mag-hero-trust">
                    <div class="mag-hero-trust-item">
                        <span class="t-val">4.9<i class="fas fa-star" style="font-size:.85rem;margin-left:.2rem;color:#f59e0b;"></i></span>
                        <span class="t-lbl">Note clients</span>
                    </div>
                    <div class="mag-hero-trust-item">
                        <span class="t-val">15+</span>
                        <span class="t-lbl">Ans d'expérience</span>
                    </div>
                    <div class="mag-hero-trust-item">
                        <span class="t-val">60+</span>
                        <span class="t-lbl">Communes</span>
                    </div>
                    <div class="mag-hero-trust-item">
                        <span class="t-val">500+</span>
                        <span class="t-lbl">Chantiers</span>
                    </div>
                </div>
            </div>

            {{-- Right Photo Column --}}
            <div class="mag-hero-right">
                @if($heroImgUrl)
                    <img src="{{ $heroImgUrl }}"
                         alt="Élagueur professionnel dans l'Oise"
                         width="900" height="1200"
                         loading="eager"
                         fetchpriority="high">
                @else
                    <div class="mag-hero-right-fallback">
                        <i class="fas fa-tree"></i>
                    </div>
                @endif

                {{-- Overlay stats --}}
                <div class="mag-hero-overlay-stats">
                    <div class="mag-hero-stat-badge">
                        <div class="sv">500+</div>
                        <div class="sl">Chantiers</div>
                    </div>
                    <div class="mag-hero-stat-badge">
                        <div class="sv">Assuré</div>
                        <div class="sl">RC Pro</div>
                    </div>
                    <div class="mag-hero-stat-badge">
                        <div class="sv">24h</div>
                        <div class="sl">Délai devis</div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- ═══════ §2 MARQUEE TICKER ═══════ --}}
    <div class="mag-ticker" aria-hidden="true">
        <div class="mag-ticker-track">
            @php
                $tickerItems = [
                    ['icon' => 'fa-check-circle', 'text' => 'Devis gratuit & sans engagement'],
                    ['icon' => 'fa-shield-alt',   'text' => 'Assurance RC Professionnelle'],
                    ['icon' => 'fa-certificate',  'text' => 'Certifié CS Élagage'],
                    ['icon' => 'fa-map-marker-alt','text' => 'Tout le département Oise (60)'],
                    ['icon' => 'fa-star',          'text' => 'Note 4.9/5 Google'],
                    ['icon' => 'fa-clock',         'text' => 'Réponse sous 24h'],
                    ['icon' => 'fa-leaf',          'text' => 'Élagage & Abattage'],
                    ['icon' => 'fa-recycle',       'text' => 'Broyage & Évacuation déchets'],
                    ['icon' => 'fa-check-circle', 'text' => 'Devis gratuit & sans engagement'],
                    ['icon' => 'fa-shield-alt',   'text' => 'Assurance RC Professionnelle'],
                    ['icon' => 'fa-certificate',  'text' => 'Certifié CS Élagage'],
                    ['icon' => 'fa-map-marker-alt','text' => 'Tout le département Oise (60)'],
                    ['icon' => 'fa-star',          'text' => 'Note 4.9/5 Google'],
                    ['icon' => 'fa-clock',         'text' => 'Réponse sous 24h'],
                    ['icon' => 'fa-leaf',          'text' => 'Élagage & Abattage'],
                    ['icon' => 'fa-recycle',       'text' => 'Broyage & Évacuation déchets'],
                ];
            @endphp
            @foreach($tickerItems as $ti)
                <span class="mag-ticker-item">
                    <i class="fas {{ $ti['icon'] }}"></i>
                    {{ $ti['text'] }}
                </span>
            @endforeach
        </div>
    </div>

    {{-- ═══════ §3 SERVICES ═══════ --}}
    @if($svcOn && !empty($svcList))
    <section class="mag-services" id="services">
        <div class="mag-shell">
            <div class="mag-services-header">
                <div>
                    <span class="mag-label"><i class="fas fa-tools"></i>Prestations</span>
                    <h2 class="mag-services-h2">{{ strtoupper($svcTitle) }}</h2>
                </div>
                <a href="{{ route('services.index') }}"
                   class="mag-btn-outline"
                   style="white-space:nowrap;">
                    Voir tout
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="mag-svc-grid">
                @foreach(collect($svcList)->take($svcLimit) as $svc)
                <article class="mag-svc-card">
                    <div class="mag-svc-card-img">
                        @if(!empty($svc['featured_image']))
                            <img src="{{ url($svc['featured_image']) }}"
                                 alt="{{ $svc['name'] }}"
                                 width="600" height="400"
                                 loading="lazy">
                        @else
                            <div class="mag-svc-card-img-fallback">
                                <i class="{{ $svc['icon'] ?? 'fas fa-tree' }}"></i>
                            </div>
                        @endif
                    </div>
                    <div class="mag-svc-card-body">
                        <div class="mag-svc-card-icon">
                            <i class="{{ $svc['icon'] ?? 'fas fa-tools' }}"></i>
                        </div>
                        <h3 class="mag-svc-card-title">{{ $svc['name'] }}</h3>
                        <p class="mag-svc-card-desc">
                            {{ $svc['short_description'] ?? Str::limit($svc['description'] ?? '', 110) }}
                        </p>
                        <a href="{{ route('services.show', $svc['slug']) }}"
                           class="mag-svc-card-link"
                           onclick="trackServiceClick('{{ $svc['name'] }}', '{{ request()->url() }}')">
                            Découvrir
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ═══════ §4 À PROPOS ═══════ --}}
    @if($aboutOn)
    <section class="mag-about" id="a-propos">
        <div class="mag-about-grid">
            {{-- Photo left --}}
            <div class="mag-about-photo">
                @if($aboutImgUrl)
                    <img src="{{ $aboutImgUrl }}"
                         alt="{{ $aboutTitle }}"
                         width="800" height="900"
                         loading="lazy">
                @else
                    <div class="mag-about-photo-fallback">
                        <i class="fas fa-hard-hat"></i>
                    </div>
                @endif
            </div>

            {{-- Text right --}}
            <div class="mag-about-text">
                <span class="mag-label"><i class="fas fa-building"></i>Notre Histoire</span>
                <h2 class="mag-about-h2">{{ strtoupper($aboutTitle) }}</h2>
                <p class="mag-about-p">{{ $aboutText }}</p>

                <ul class="mag-about-checklist">
                    <li><i class="fas fa-check-circle"></i>Certifié CS Élagage — formation continue</li>
                    <li><i class="fas fa-check-circle"></i>Assurance Responsabilité Civile Professionnelle</li>
                    <li><i class="fas fa-check-circle"></i>Matériel professionnel entretenu régulièrement</li>
                    <li><i class="fas fa-check-circle"></i>Intervention propre : broyage & évacuation des déchets</li>
                    <li><i class="fas fa-check-circle"></i>Devis détaillé gratuit et sans engagement</li>
                </ul>

                <div class="mag-about-ctas">
                    <a href="{{ route('form.step', 'propertyType') }}"
                       class="mag-btn-green"
                       onclick="trackFormClick('about_magazine')">
                        <i class="fas fa-clipboard-list"></i>
                        Demander un devis
                    </a>
                    <a href="{{ route('contact') }}" class="mag-btn-dark">
                        <i class="fas fa-phone"></i>
                        Nous contacter
                    </a>
                </div>

                <div class="mag-about-statsbar">
                    <div class="mag-about-stat">
                        <div class="asv">15+</div>
                        <div class="asl">Années</div>
                    </div>
                    <div class="mag-about-stat">
                        <div class="asv">500+</div>
                        <div class="asl">Chantiers</div>
                    </div>
                    <div class="mag-about-stat">
                        <div class="asv">60+</div>
                        <div class="asl">Communes</div>
                    </div>
                    <div class="mag-about-stat">
                        <div class="asv">4.9</div>
                        <div class="asl">Note /5</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- ═══════ §5 PROCESSUS ═══════ --}}
    <section class="mag-process" id="processus">
        <div class="mag-shell">
            <span class="mag-label"><i class="fas fa-route"></i>Comment ça marche</span>
            <h2 class="mag-process-h2">NOTRE PROCESSUS</h2>

            <div class="mag-process-grid">
                <div class="mag-process-step">
                    <span class="mag-process-num">01</span>
                    <div>
                        <h3 class="mag-process-step-title">Prise de contact</h3>
                        <p class="mag-process-step-text">
                            Appelez-nous ou remplissez le formulaire en ligne. Nous vous répondons sous 24h pour organiser une visite.
                        </p>
                    </div>
                </div>
                <div class="mag-process-step">
                    <span class="mag-process-num">02</span>
                    <div>
                        <h3 class="mag-process-step-title">Devis gratuit</h3>
                        <p class="mag-process-step-text">
                            Visite sur site gratuite et sans engagement. Nous évaluons les travaux et vous remettons un devis détaillé.
                        </p>
                    </div>
                </div>
                <div class="mag-process-step">
                    <span class="mag-process-num">03</span>
                    <div>
                        <h3 class="mag-process-step-title">Planification</h3>
                        <p class="mag-process-step-text">
                            Nous convenons ensemble d'une date d'intervention. Respect des délais garanti.
                        </p>
                    </div>
                </div>
                <div class="mag-process-step">
                    <span class="mag-process-num">04</span>
                    <div>
                        <h3 class="mag-process-step-title">Intervention & nettoyage</h3>
                        <p class="mag-process-step-text">
                            Travaux réalisés dans les règles de l'art. Broyage et évacuation des déchets verts inclus.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════ §6 TÉMOIGNAGES ═══════ --}}
    @if($reviewsOn && !empty($reviews) && $reviews->count())
    <section class="mag-reviews" id="temoignages">
        <div class="mag-shell">
            <div class="mag-reviews-header">
                <span class="mag-label"><i class="fas fa-quote-left"></i>Témoignages</span>
                <h2 class="mag-reviews-h2">{{ strtoupper($reviewsTitle) }}</h2>
                @if(isset($averageRating) && $averageRating > 0)
                <div class="mag-reviews-stars-row">
                    <div class="stars">
                        @for($i=1;$i<=5;$i++)
                            <i class="fas fa-star{{ $i > round($averageRating) ? '-o' : '' }}"></i>
                        @endfor
                    </div>
                    <span class="avg">{{ number_format($averageRating, 1) }}/5</span>
                    @if(isset($totalReviews))
                    <span class="total">({{ $totalReviews }} avis vérifiés)</span>
                    @endif
                </div>
                @endif
            </div>

            {{-- Featured pull-quote: first review --}}
            @php $featuredReview = $reviews->first(); @endphp
            @if($featuredReview)
            <div class="mag-pull-quote">
                <p class="mag-pull-text">"{{ Str::limit($featuredReview->review_text ?? 'Excellent travail, très professionnel et soigné. Je recommande vivement.', 240) }}"</p>
                <div class="mag-pull-author">
                    <div class="mag-pull-author-avatar">
                        @if($featuredReview->author_photo_url)
                            <img src="{{ $featuredReview->author_photo_url }}"
                                 alt="{{ $featuredReview->author_name }}"
                                 width="48" height="48">
                        @else
                            {{ strtoupper(substr($featuredReview->author_name ?? 'C', 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <div class="mag-pull-author-name">{{ $featuredReview->author_name ?? 'Client vérifié' }}</div>
                        <div class="mag-pull-author-meta">
                            @if($featuredReview->review_date)
                                {{ \Carbon\Carbon::parse($featuredReview->review_date)->translatedFormat('F Y') }}
                            @endif
                            @if($featuredReview->source && str_contains($featuredReview->source, 'Google'))
                                &middot; <i class="fab fa-google"></i> Google
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Reviews grid: skip first (featured above) --}}
            <div class="mag-reviews-grid">
                @foreach($reviews->skip(1)->take($reviewsLimit - 1) as $review)
                <div class="mag-review-card">
                    <div class="mag-review-stars">
                        @for($i=1;$i<=5;$i++)
                            <i class="fas fa-star{{ $i > ($review->rating ?? 5) ? '-o' : '' }}"></i>
                        @endfor
                    </div>
                    <p class="mag-review-text">"{{ Str::limit($review->review_text ?? 'Très bon travail, je recommande.', 150) }}"</p>
                    <div class="mag-review-author">
                        <div class="mag-review-avatar">
                            @if($review->author_photo_url)
                                <img src="{{ $review->author_photo_url }}"
                                     alt="{{ $review->author_name }}"
                                     width="40" height="40">
                            @else
                                {{ strtoupper(substr($review->author_name ?? 'C', 0, 1)) }}
                            @endif
                        </div>
                        <div>
                            <div class="mag-review-name">{{ $review->author_name ?? 'Client vérifié' }}</div>
                            <div class="mag-review-date">
                                {{ $review->review_date ? \Carbon\Carbon::parse($review->review_date)->translatedFormat('M Y') : '' }}
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mag-reviews-cta-row">
                <a href="{{ route('reviews.all') }}" class="mag-btn-outline">
                    Voir tous les avis
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>
    @endif

    {{-- ═══════ §7 ZONES D'INTERVENTION ═══════ --}}
    @if(isset($favoriteCities) && $favoriteCities->count())
    <section class="mag-zones" id="zones">
        <div class="mag-shell">
            <span class="mag-label"><i class="fas fa-map-marker-alt"></i>Zones d'intervention</span>
            <h2 class="mag-zones-h2">NOUS INTERVENONS<br>DANS TOUT LE 60</h2>
            <p class="mag-zones-sub">Élagage, abattage et entretien des espaces verts dans tout le département de l'Oise.</p>
            <div class="mag-zones-pills">
                @foreach($favoriteCities as $city)
                <a href="{{ route('services.index') }}?ville={{ $city->slug ?? Str::slug($city->name ?? '') }}"
                   class="mag-zone-pill">
                    <i class="fas fa-map-pin"></i>
                    {{ $city->name ?? 'Ville' }}
                    @if(isset($city->postal_code))
                        <span style="opacity:.65;">({{ $city->postal_code }})</span>
                    @endif
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ═══════ §8 FAQ ═══════ --}}
    <section class="mag-faq" id="faq">
        <div class="mag-shell">
            <span class="mag-label"><i class="fas fa-question-circle"></i>Questions fréquentes</span>
            <h2 class="mag-faq-h2">FAQ</h2>

            @php
                $faqs = [
                    ['q' => 'Le devis est-il vraiment gratuit ?',
                     'a' => 'Oui, notre devis est entièrement gratuit et sans engagement. Nous nous déplaçons sur votre propriété pour évaluer les travaux et vous remettons un document détaillé sous 24 à 48h.'],
                    ['q' => 'Intervenez-vous en urgence ?',
                     'a' => 'Oui, nous pouvons intervenir rapidement en cas d\'arbre dangereux, chute ou tempête. Contactez-nous directement par téléphone pour une prise en charge prioritaire.'],
                    ['q' => 'Êtes-vous assurés pour les travaux en hauteur ?',
                     'a' => 'Absolument. Nous disposons d\'une assurance Responsabilité Civile Professionnelle couvrant tous les travaux d\'élagage et d\'abattage, y compris les interventions en hauteur.'],
                    ['q' => 'Qu\'est-ce qui est inclus dans la prestation ?',
                     'a' => 'Nos prestations incluent les travaux demandés, le broyage des branches et déchets verts sur place, et l\'évacuation des souches si nécessaire. Tout est précisé dans le devis.'],
                    ['q' => 'Quelles communes du département 60 couvrez-vous ?',
                     'a' => 'Nous intervenons dans tout le département de l\'Oise (60) : Beauvais, Compiègne, Creil, Senlis, Noyon, Chantilly, Clermont, Méru et leurs environs. Contactez-nous pour confirmer votre commune.'],
                    ['q' => 'Quelle est la meilleure période pour élaguer ?',
                     'a' => 'L\'élagage peut s\'effectuer tout au long de l\'année selon les espèces. En général, la période de dormance (automne-hiver) est idéale pour les arbres à feuilles caduques. Nous vous conseillons lors du devis.'],
                    ['q' => 'Avez-vous des certifications professionnelles ?',
                     'a' => 'Oui, nous sommes titulaires du Certificat de Spécialisation Élagage (CS Élagage) et maintenons nos compétences à jour grâce à des formations continues dans le domaine de l\'arboriculture.'],
                    ['q' => 'Comment préparer mon terrain avant l\'intervention ?',
                     'a' => 'Nous vous conseillons de dégager l\'accès au terrain et de signaler tout obstacle (câbles, canalisations) que vous connaissez. Nous nous chargeons du reste et nettoyons entièrement le chantier.'],
                ];
            @endphp

            <div class="mag-faq-grid">
                @foreach($faqs as $idx => $faq)
                <div class="mag-faq-item{{ $idx === 0 ? ' open' : '' }}">
                    <button class="mag-faq-question" aria-expanded="{{ $idx === 0 ? 'true' : 'false' }}">
                        {{ $faq['q'] }}
                        <i class="fas fa-plus"></i>
                    </button>
                    <div class="mag-faq-answer" {{ $idx === 0 ? '' : 'aria-hidden="true"' }}>
                        {{ $faq['a'] }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════ §9 CTA FINAL ═══════ --}}
    @if($ctaOn)
    <section class="mag-cta-final" id="contact">
        <div class="mag-shell">
            <div class="mag-cta-inner">
                <span class="mag-cta-final-tag">
                    <i class="fas fa-leaf" style="font-size:.6rem;"></i>
                    Élagueur certifié · Oise (60)
                </span>
                <h2 class="mag-cta-final-h2">{{ strtoupper($ctaTitle) }}</h2>
                <p class="mag-cta-final-sub">
                    Devis gratuit et sans engagement sous 24h. Intervention dans tout le département de l'Oise.
                    Appelez-nous ou remplissez le formulaire en ligne.
                </p>
                <div class="mag-cta-final-btns">
                    <a href="{{ route('form.step', 'propertyType') }}"
                       class="mag-btn-white"
                       onclick="trackFormClick('cta_final_magazine')">
                        <i class="fas fa-clipboard-list"></i>
                        Devis gratuit en ligne
                    </a>
                    <a href="tel:{{ $phoneRaw }}" class="mag-btn-outline-white">
                        <i class="fas fa-phone"></i>
                        {{ $phone }}
                    </a>
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- ═══════ PARTIALS ═══════ --}}
    @include('home.partials.ecology-financing')
    @include('home.partials.featured-partner')
    @include('home.partials.partners-logos')
    @include('home.partials.scripts')

</div>{{-- /.lp --}}

{{-- ═══════════════════════════════════════════════════════════════
     TRACKING + FAQ JS
     ═══════════════════════════════════════════════════════════════ --}}
<script>
/* Tracking */
function trackServiceClick(n, p) {
    fetch('/api/track-service-click?service=' + encodeURIComponent(n), { method: 'GET' }).catch(function () {});
}
function trackFormClick(p) {
    fetch('/api/track-form-click', { method: 'GET' }).catch(function () {});
}

/* FAQ Accordion */
document.addEventListener('DOMContentLoaded', function () {
    var items = document.querySelectorAll('.lp .mag-faq-item');
    items.forEach(function (item) {
        var btn    = item.querySelector('.mag-faq-question');
        var answer = item.querySelector('.mag-faq-answer');
        if (!btn || !answer) return;
        btn.addEventListener('click', function () {
            var isOpen = item.classList.contains('open');
            /* close all in same faq grid */
            var grid = item.closest('.mag-faq-grid');
            if (grid) {
                grid.querySelectorAll('.mag-faq-item.open').forEach(function (openItem) {
                    openItem.classList.remove('open');
                    openItem.querySelector('.mag-faq-question').setAttribute('aria-expanded', 'false');
                    openItem.querySelector('.mag-faq-answer').setAttribute('aria-hidden', 'true');
                });
            }
            if (!isOpen) {
                item.classList.add('open');
                btn.setAttribute('aria-expanded', 'true');
                answer.removeAttribute('aria-hidden');
            }
        });
    });
});
</script>
