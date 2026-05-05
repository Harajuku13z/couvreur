{{-- ============================================================
     LANDING PAGE "SHOWCASE" — Louis Hoffmann Élagage · Dép. 60
     Design : dark bold editorial, services & portfolio en avant
     ============================================================ --}}

@php
    $phone    = setting('company_phone', '06 42 21 41 51');
    $phoneRaw = preg_replace('/\D/', '', $phone);
    $heroTitle = $homeConfig['sections']['hero']['title'] ?? 'Élagueur & Abatteur professionnel dans l\'Oise (60)';
    $heroImg    = $homeConfig['hero']['background_image'] ?? null;
    $heroImgUrl = $heroImg ? asset(ltrim($heroImg, '/')) : null;

    $svcEnabled  = $homeConfig['sections']['services']['enabled'] ?? true;
    $svcTitle    = $homeConfig['sections']['services']['title']   ?? 'Nos services';
    $svcLimit    = $homeConfig['sections']['services']['limit']   ?? 6;

    $aboutEnabled = $homeConfig['sections']['about']['enabled'] ?? true;
    $aboutTitle   = $homeConfig['sections']['about']['title']   ?? 'À propos';
    $aboutText    = $homeConfig['sections']['about']['text']    ?? '';
    $aboutImg     = $homeConfig['about']['image']               ?? null;
    $aboutImgUrl  = $aboutImg ? asset(ltrim($aboutImg, '/')) : null;

    $revEnabled = $homeConfig['sections']['reviews']['enabled'] ?? true;
    $revTitle   = $homeConfig['sections']['reviews']['title']   ?? 'Témoignages clients';
    $revLimit   = $homeConfig['sections']['reviews']['limit']   ?? 4;

    $ctaEnabled = $homeConfig['sections']['cta']['enabled'] ?? true;
    $ctaTitle   = $homeConfig['sections']['cta']['title']   ?? 'Prêt à démarrer votre projet ?';

    $portEnabled = $homeConfig['sections']['portfolio']['enabled'] ?? false;

    $displayedSvcs = array_slice(is_array($svcList) ? $svcList : [], 0, (int)$svcLimit);

    $firstReview = isset($reviews) && $reviews->count() ? $reviews->first() : null;
    $avgRating   = $averageRating ?? 4.9;
    $totalRev    = $totalReviews  ?? 0;
@endphp

{{-- ═══════════════════════════════════════════════════
     STYLES
     ═══════════════════════════════════════════════════ --}}
<style>
/* ── Reset ──────────────────────────────────────────── */
.lp *, .lp *::before, .lp *::after { box-sizing: border-box; margin: 0; padding: 0; }
.lp { font-family: inherit; background: #0d1110; color: #fff; }

/* ── Shell ──────────────────────────────────────────── */
.sc-shell {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 1.25rem;
}
@media (min-width: 768px)  { .sc-shell { padding: 0 2rem; } }
@media (min-width: 1280px) { .sc-shell { padding: 0 3rem; } }

/* ── Label ──────────────────────────────────────────── */
.sc-label {
    display: inline-flex;
    align-items: center;
    gap: .55rem;
    font-size: .68rem;
    font-weight: 800;
    letter-spacing: .2em;
    text-transform: uppercase;
    color: var(--primary-color);
    margin-bottom: .85rem;
}
.sc-label::before {
    content: '';
    display: inline-block;
    width: 2rem;
    height: 2px;
    background: var(--primary-color);
    flex-shrink: 0;
}

/* ── Animations ─────────────────────────────────────── */
@keyframes sc-fadeup {
    from { opacity: 0; transform: translateY(28px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes sc-pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: .55; transform: scale(.82); }
}
.sc-reveal   { animation: sc-fadeup .65s ease both; }
.sc-reveal-2 { animation-delay: .1s; }
.sc-reveal-3 { animation-delay: .2s; }
.sc-reveal-4 { animation-delay: .32s; }

/* ── Buttons ────────────────────────────────────────── */
.sc-btn-solid {
    display: inline-flex;
    align-items: center;
    gap: .65rem;
    font-size: .925rem;
    font-weight: 800;
    padding: .9rem 1.9rem;
    border-radius: 14px;
    background: var(--primary-color);
    color: #fff;
    text-decoration: none;
    transition: opacity .2s, transform .2s;
    border: none;
    cursor: pointer;
}
.sc-btn-solid:hover { opacity: .88; transform: translateY(-2px); }

.sc-btn-outline {
    display: inline-flex;
    align-items: center;
    gap: .65rem;
    font-size: .925rem;
    font-weight: 700;
    padding: .9rem 1.9rem;
    border-radius: 14px;
    background: transparent;
    color: #fff;
    text-decoration: none;
    border: 1.5px solid rgba(255,255,255,.22);
    transition: background .2s, border-color .2s;
    cursor: pointer;
}
.sc-btn-outline:hover {
    background: rgba(255,255,255,.07);
    border-color: rgba(255,255,255,.4);
}

.sc-btn-green-outline {
    display: inline-flex;
    align-items: center;
    gap: .55rem;
    font-size: .85rem;
    font-weight: 700;
    padding: .7rem 1.5rem;
    border-radius: 10px;
    background: transparent;
    color: var(--primary-color);
    text-decoration: none;
    border: 1.5px solid var(--primary-color);
    transition: background .2s, color .2s;
}
.sc-btn-green-outline:hover {
    background: var(--primary-color);
    color: #fff;
}

/* ── HERO ───────────────────────────────────────────── */
.sc-hero {
    position: relative;
    height: 100svh;
    min-height: 600px;
    background: #0d1110;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}

.sc-hero-bg {
    position: absolute;
    inset: 0;
    z-index: 0;
}
.sc-hero-bg img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center 25%;
    display: block;
}
.sc-hero-bg-gradient {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        90deg,
        rgba(8, 17, 12, .97) 0%,
        rgba(8, 17, 12, .82) 42%,
        rgba(8, 17, 12, .45) 72%,
        rgba(8, 17, 12, .25) 100%
    );
}
.sc-hero-bg-bottom {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 45%;
    background: linear-gradient(to top, rgba(8,17,12,.98) 0%, transparent 100%);
}

.sc-hero-inner {
    position: relative;
    z-index: 10;
    width: 100%;
    padding-bottom: 0;
}

.sc-hero-content {
    padding-top: 8rem;
    padding-bottom: 2.5rem;
}

/* ── Hero tag ───────────────────────────────────────── */
.sc-hero-tag {
    display: inline-flex;
    align-items: center;
    gap: .75rem;
    margin-bottom: 1.75rem;
}
.sc-hero-tag-line {
    width: 2.5rem;
    height: 2px;
    background: var(--primary-color);
    flex-shrink: 0;
}
.sc-hero-tag-text {
    font-size: .7rem;
    font-weight: 800;
    letter-spacing: .2em;
    text-transform: uppercase;
    color: var(--primary-color);
}

/* ── Hero H1 ────────────────────────────────────────── */
.sc-hero-h1 {
    font-size: clamp(3rem, 7vw, 5.5rem);
    font-weight: 950;
    line-height: .92;
    letter-spacing: -.025em;
    text-transform: uppercase;
    color: #fff;
    margin-bottom: 1.5rem;
    max-width: 700px;
}
.sc-hero-h1 span {
    color: var(--primary-color);
    display: block;
}

.sc-hero-subtitle {
    font-size: clamp(.9rem, 1.8vw, 1.1rem);
    color: rgba(255,255,255,.5);
    line-height: 1.7;
    max-width: 500px;
    margin-bottom: 2.25rem;
}

/* ── Hero CTA row ───────────────────────────────────── */
.sc-hero-ctas {
    display: flex;
    flex-wrap: wrap;
    gap: .875rem;
    margin-bottom: 3rem;
}

/* ── Feature boxes ──────────────────────────────────── */
.sc-feat-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: .75rem;
    padding-bottom: 1.5rem;
}
@media (min-width: 640px) {
    .sc-feat-row { grid-template-columns: repeat(4, 1fr); }
}

.sc-feat-box {
    background: rgba(255,255,255,.04);
    border-left: 3px solid var(--primary-color);
    border-radius: 0 12px 12px 0;
    padding: .9rem 1rem;
    backdrop-filter: blur(8px);
}
.sc-feat-box-icon {
    font-size: 1.1rem;
    color: var(--primary-color);
    margin-bottom: .35rem;
}
.sc-feat-box-title {
    font-size: .82rem;
    font-weight: 800;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: .06em;
    margin-bottom: .15rem;
}
.sc-feat-box-sub {
    font-size: .7rem;
    color: rgba(255,255,255,.38);
}

/* ── Floating review card ───────────────────────────── */
.sc-review-float {
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.1);
    backdrop-filter: blur(16px);
    border-radius: 18px;
    padding: 1.25rem 1.5rem;
    width: 300px;
}
.sc-review-float-stars {
    display: flex;
    gap: 3px;
    margin-bottom: .7rem;
}
.sc-review-float-quote {
    font-size: .82rem;
    color: rgba(255,255,255,.72);
    line-height: 1.65;
    font-style: italic;
    margin-bottom: .75rem;
}
.sc-review-float-author {
    display: flex;
    align-items: center;
    gap: .65rem;
}
.sc-review-float-avatar {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--primary-color);
}
.sc-review-float-name {
    font-size: .8rem;
    font-weight: 700;
    color: #fff;
}
.sc-review-float-source {
    font-size: .68rem;
    color: rgba(255,255,255,.35);
}

/* Desktop: absolute bottom-right */
.sc-review-float-wrap-desktop {
    display: none;
}
@media (min-width: 1024px) {
    .sc-review-float-wrap-desktop {
        display: block;
        position: absolute;
        right: 7%;
        bottom: 80px;
        z-index: 20;
    }
}

/* Mobile: inline below features */
.sc-review-float-wrap-mobile {
    display: block;
    margin-top: 1.5rem;
    margin-bottom: 2rem;
}
@media (min-width: 1024px) {
    .sc-review-float-wrap-mobile { display: none; }
}

/* ── SERVICES section ───────────────────────────────── */
.sc-services {
    background: #111716;
    padding: 6rem 0;
}

.sc-sec-head {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 3rem;
}
.sc-sec-h2 {
    font-size: clamp(1.9rem, 3.5vw, 2.8rem);
    font-weight: 900;
    line-height: 1.05;
    letter-spacing: -.02em;
    color: #fff;
}

.showcase-svc-grid {
    display: grid;
    gap: 1.25rem;
    grid-template-columns: 1fr;
}
@media (min-width: 640px) {
    .showcase-svc-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 1024px) {
    .showcase-svc-grid { grid-template-columns: repeat(3, 1fr); }
}

.sc-svc-card {
    position: relative;
    display: block;
    height: 300px;
    border-radius: 18px;
    overflow: hidden;
    text-decoration: none;
    border: 1.5px solid var(--primary-color);
    transition: transform .3s ease, box-shadow .35s ease;
}
.sc-svc-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 60px rgba(0,0,0,.4), 0 0 0 1px var(--primary-color);
}

.sc-svc-card-img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform .6s ease;
}
.sc-svc-card:hover .sc-svc-card-img {
    transform: scale(1.07);
}

.sc-svc-card-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        to top,
        rgba(6, 20, 10, .97) 0%,
        rgba(6, 20, 10, .6)  45%,
        rgba(6, 20, 10, .1)  100%
    );
}

.sc-svc-card-body {
    position: absolute;
    inset: 0;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}

.sc-svc-card-icon {
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 10px;
    background: rgba(var(--primary-color-rgb, 34,197,94), .18);
    border: 1px solid rgba(var(--primary-color-rgb, 34,197,94), .3);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-color);
    font-size: 1rem;
    margin-bottom: .75rem;
}

.sc-svc-card-title {
    font-size: 1.1rem;
    font-weight: 800;
    color: #fff;
    margin-bottom: .4rem;
    line-height: 1.2;
    text-transform: uppercase;
    letter-spacing: .03em;
}

.sc-svc-card-desc {
    font-size: .78rem;
    color: rgba(255,255,255,.52);
    line-height: 1.55;
    margin-bottom: .85rem;
}

.sc-svc-card-link {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    font-size: .75rem;
    font-weight: 700;
    color: var(--primary-color);
    text-transform: uppercase;
    letter-spacing: .1em;
}

/* No-image fallback */
.sc-svc-card-fallback {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #0f2318 0%, #1a3d2a 100%);
}

/* ── RÉALISATIONS section ───────────────────────────── */
.sc-portfolio {
    background: #0d1110;
    padding: 6rem 0;
}

.sc-port-grid {
    display: grid;
    gap: 1.25rem;
    grid-template-columns: 1fr;
    margin-top: 2.5rem;
}
@media (min-width: 640px) {
    .sc-port-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 1024px) {
    .sc-port-grid { grid-template-columns: repeat(3, 1fr); }
}

.sc-port-card {
    position: relative;
    height: 260px;
    border-radius: 16px;
    overflow: hidden;
    display: block;
    text-decoration: none;
    background: #111716;
    transition: transform .3s ease;
}
.sc-port-card:hover { transform: translateY(-5px); }
.sc-port-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform .6s ease;
}
.sc-port-card:hover img { transform: scale(1.06); }
.sc-port-card-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(6,20,10,.85) 0%, transparent 55%);
}
.sc-port-card-label {
    position: absolute;
    bottom: 1.25rem;
    left: 1.25rem;
    right: 1.25rem;
    font-size: .82rem;
    font-weight: 700;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: .05em;
}

.sc-port-cta-block {
    margin-top: 2.5rem;
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 20px;
    padding: 3.5rem 2.5rem;
    text-align: center;
}
.sc-port-cta-block p {
    font-size: clamp(.9rem, 1.5vw, 1.05rem);
    color: rgba(255,255,255,.45);
    max-width: 440px;
    margin: .75rem auto 2rem;
    line-height: 1.7;
}

/* ── ABOUT section ──────────────────────────────────── */
.sc-about {
    background: #0f1f13;
    padding: 6rem 0;
}

.sc-about-grid {
    display: grid;
    gap: 3.5rem;
    align-items: start;
}
@media (min-width: 1024px) {
    .sc-about-grid { grid-template-columns: 1fr 1fr; align-items: center; }
}

.sc-about-photo-wrap {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    height: 480px;
}
.sc-about-photo-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.sc-about-photo-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(160deg, #0f2318 0%, #1a4526 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}
.sc-about-photo-placeholder i {
    font-size: 4rem;
    color: rgba(255,255,255,.12);
}

.sc-about-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: .75rem;
    margin-top: 1.5rem;
}
@media (min-width: 480px) {
    .sc-about-stats { grid-template-columns: repeat(4, 1fr); }
}
@media (min-width: 1024px) {
    .sc-about-stats { grid-template-columns: repeat(2, 1fr); }
}

.sc-stat-box {
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 14px;
    padding: 1.1rem .85rem;
    text-align: center;
}
.sc-stat-val {
    font-size: 1.75rem;
    font-weight: 900;
    color: var(--primary-color);
    line-height: 1;
    margin-bottom: .3rem;
}
.sc-stat-lbl {
    font-size: .7rem;
    color: rgba(255,255,255,.4);
    text-transform: uppercase;
    letter-spacing: .1em;
    font-weight: 700;
}

.sc-about-right h2 {
    font-size: clamp(1.8rem, 3vw, 2.6rem);
    font-weight: 900;
    line-height: 1.1;
    color: #fff;
    letter-spacing: -.02em;
    margin-bottom: 1.25rem;
}
.sc-about-right p {
    font-size: .95rem;
    color: rgba(255,255,255,.55);
    line-height: 1.75;
    margin-bottom: 1.5rem;
}

.sc-checklist {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: .6rem;
    margin-bottom: 2rem;
}
.sc-checklist li {
    display: flex;
    align-items: flex-start;
    gap: .75rem;
    font-size: .9rem;
    color: rgba(255,255,255,.62);
    line-height: 1.5;
}
.sc-check-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.3rem;
    height: 1.3rem;
    border-radius: .4rem;
    background: var(--primary-color);
    flex-shrink: 0;
    margin-top: .1rem;
}
.sc-check-icon svg {
    width: .7rem;
    height: .7rem;
    stroke: #fff;
    stroke-width: 2.5;
    fill: none;
}

/* ── REVIEWS section ────────────────────────────────── */
.sc-reviews {
    background: #0d1110;
    padding: 6rem 0;
}

.sc-reviews-inner {
    display: grid;
    gap: 1.25rem;
    grid-template-columns: 1fr;
    margin-top: 2.5rem;
}
@media (min-width: 1024px) {
    .sc-reviews-inner { grid-template-columns: 2fr 1fr; align-items: start; }
}

.sc-rev-featured {
    background: #111716;
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 22px;
    padding: 2.5rem;
}

.sc-rev-small-col {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.sc-rev-card {
    background: #111716;
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 16px;
    padding: 1.5rem;
}

.sc-rev-stars {
    display: flex;
    gap: 3px;
    margin-bottom: .75rem;
}
.sc-rev-star { color: #fbbf24; font-size: .8rem; }

.sc-rev-text {
    font-size: .88rem;
    color: rgba(255,255,255,.62);
    line-height: 1.7;
    font-style: italic;
    margin-bottom: 1.1rem;
}
.sc-rev-featured .sc-rev-text {
    font-size: 1.05rem;
    color: rgba(255,255,255,.72);
}

.sc-rev-author {
    display: flex;
    align-items: center;
    gap: .75rem;
}
.sc-rev-avatar {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--primary-color);
    flex-shrink: 0;
}
.sc-rev-avatar-placeholder {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    background: rgba(var(--primary-color-rgb,34,197,94),.18);
    border: 2px solid var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.sc-rev-name {
    font-size: .85rem;
    font-weight: 700;
    color: #fff;
}
.sc-rev-date {
    font-size: .72rem;
    color: rgba(255,255,255,.32);
}

.sc-rev-aggregate {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 1.5rem 2.5rem;
    border-top: 1px solid rgba(255,255,255,.07);
    margin-top: .5rem;
}
.sc-rev-agg-num {
    font-size: 3rem;
    font-weight: 900;
    color: #fff;
    line-height: 1;
}
.sc-rev-agg-stars { display: flex; gap: 3px; margin-bottom: .25rem; }
.sc-rev-agg-count { font-size: .75rem; color: rgba(255,255,255,.38); }

/* ── CTA section ────────────────────────────────────── */
.sc-cta {
    background: linear-gradient(135deg, #0e2b17 0%, #1a4d2a 50%, #113520 100%);
    padding: 7rem 0;
    position: relative;
    overflow: hidden;
}
.sc-cta-halo {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 800px;
    height: 800px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(74,222,128,.08) 0%, transparent 65%);
    pointer-events: none;
}
.sc-cta-inner {
    position: relative;
    z-index: 10;
    text-align: center;
}
.sc-cta-inner h2 {
    font-size: clamp(2rem, 4vw, 3.2rem);
    font-weight: 900;
    color: #fff;
    line-height: 1.1;
    letter-spacing: -.02em;
    margin-bottom: 1rem;
    text-transform: uppercase;
}
.sc-cta-inner p {
    font-size: 1rem;
    color: rgba(255,255,255,.52);
    max-width: 480px;
    margin: 0 auto 2.5rem;
    line-height: 1.7;
}
.sc-cta-btns {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    justify-content: center;
    margin-bottom: 2.5rem;
}
.sc-trust-pills {
    display: flex;
    flex-wrap: wrap;
    gap: .75rem;
    justify-content: center;
}
.sc-trust-pill {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    font-size: .72rem;
    font-weight: 700;
    color: rgba(255,255,255,.5);
    padding: .4rem .95rem;
    border-radius: 50px;
    border: 1px solid rgba(255,255,255,.1);
    background: rgba(255,255,255,.04);
}
.sc-trust-pill i { color: var(--primary-color); font-size: .7rem; }

/* ── Divider ────────────────────────────────────────── */
.sc-divider {
    width: 100%;
    height: 1px;
    background: rgba(255,255,255,.07);
}

/* ── Section title spacing ──────────────────────────── */
.sc-sec-title-wrap { margin-bottom: 3rem; }
</style>

{{-- ═══════════════════════════════════════════════════
     MARKUP
     ═══════════════════════════════════════════════════ --}}
<div class="lp">

{{-- ══════════════════════════════════════════════════════════
     §1  HERO — Full screen dark bold
     ══════════════════════════════════════════════════════════ --}}
<section class="sc-hero" aria-label="Section principale">

    {{-- Background image --}}
    <div class="sc-hero-bg">
        @if($heroImgUrl)
            <img src="{{ $heroImgUrl }}"
                 alt="Élagage professionnel dans l'Oise — {{ setting('company_name','Louis Hoffmann Élagage') }}"
                 fetchpriority="high" decoding="async">
        @else
            <div style="position:absolute;inset:0;background:linear-gradient(160deg,#06120a 0%,#0d2d18 55%,#1a4527 100%);"></div>
        @endif
        <div class="sc-hero-bg-gradient"></div>
        <div class="sc-hero-bg-bottom"></div>
    </div>

    {{-- Floating review card — Desktop absolute --}}
    @if($firstReview)
    <div class="sc-review-float-wrap-desktop" aria-hidden="true">
        <div class="sc-review-float">
            <div class="sc-review-float-stars">
                @for($i=1;$i<=5;$i++)
                    <i class="fas fa-star" style="color:#fbbf24;font-size:.75rem;"></i>
                @endfor
            </div>
            <p class="sc-review-float-quote">
                "{{ \Illuminate\Support\Str::limit($firstReview->review_text, 90) }}"
            </p>
            <div class="sc-review-float-author">
                @if($firstReview->author_photo_url)
                    <img src="{{ $firstReview->author_photo_url }}"
                         alt="{{ $firstReview->author_name }}"
                         class="sc-review-float-avatar"
                         loading="lazy">
                @else
                    <div class="sc-review-float-avatar" style="background:rgba(var(--primary-color-rgb,34,197,94),.18);display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-user" style="color:var(--primary-color);font-size:.8rem;"></i>
                    </div>
                @endif
                <div>
                    <div class="sc-review-float-name">{{ $firstReview->author_name }}</div>
                    <div class="sc-review-float-source">{{ $firstReview->source ?? 'Google' }} · Vérifié</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Hero content --}}
    <div class="sc-hero-inner sc-shell">
        <div class="sc-hero-content">

            {{-- Tag --}}
            <div class="sc-hero-tag sc-reveal">
                <span class="sc-hero-tag-line"></span>
                <span class="sc-hero-tag-text">Élagueur certifié &middot; Dép. 60</span>
            </div>

            {{-- H1 --}}
            <h1 class="sc-hero-h1 sc-reveal sc-reveal-2">
                {{ $heroTitle }}
                <span>dans l'Oise</span>
            </h1>

            {{-- Subtitle --}}
            <p class="sc-hero-subtitle sc-reveal sc-reveal-3">
                Élagage, abattage, taille de haies et broyage de souches —
                <span style="color:rgba(255,255,255,.8);">Compiègne, Beauvais, Senlis, Chantilly, Creil, Noyon</span>
                et toutes les communes du département&nbsp;60.
            </p>

            {{-- CTA buttons --}}
            <div class="sc-hero-ctas sc-reveal sc-reveal-4">
                <a href="{{ route('services.index') }}"
                   onclick="if(typeof trackServiceClick==='function')trackServiceClick('all','{{ request()->url() }}')"
                   class="sc-btn-solid">
                    <i class="fas fa-tree"></i>
                    Nos services
                </a>
                <a href="{{ route('form.step', 'propertyType') }}"
                   onclick="if(typeof trackFormClick==='function')trackFormClick('{{ request()->url() }}')"
                   class="sc-btn-outline">
                    <i class="fas fa-file-alt"></i>
                    Devis gratuit
                    <i class="fas fa-arrow-right" style="font-size:.75rem;"></i>
                </a>
            </div>

            {{-- Feature boxes --}}
            <div class="sc-feat-row sc-reveal sc-reveal-4">
                <div class="sc-feat-box">
                    <div class="sc-feat-box-icon"><i class="fas fa-leaf"></i></div>
                    <div class="sc-feat-box-title">Écologique</div>
                    <div class="sc-feat-box-sub">Déchets valorisés</div>
                </div>
                <div class="sc-feat-box">
                    <div class="sc-feat-box-icon"><i class="fas fa-bolt"></i></div>
                    <div class="sc-feat-box-title">Rapide</div>
                    <div class="sc-feat-box-sub">Réponse sous 24h</div>
                </div>
                <div class="sc-feat-box">
                    <div class="sc-feat-box-icon"><i class="fas fa-shield-alt"></i></div>
                    <div class="sc-feat-box-title">Sécurisé</div>
                    <div class="sc-feat-box-sub">RC Pro &amp; assurance</div>
                </div>
                <div class="sc-feat-box">
                    <div class="sc-feat-box-icon"><i class="fas fa-sliders-h"></i></div>
                    <div class="sc-feat-box-title">Sur-mesure</div>
                    <div class="sc-feat-box-sub">Devis personnalisé</div>
                </div>
            </div>

            {{-- Floating review card — Mobile inline --}}
            @if($firstReview)
            <div class="sc-review-float-wrap-mobile">
                <div class="sc-review-float" style="width:100%;">
                    <div class="sc-review-float-stars">
                        @for($i=1;$i<=5;$i++)
                            <i class="fas fa-star" style="color:#fbbf24;font-size:.75rem;"></i>
                        @endfor
                    </div>
                    <p class="sc-review-float-quote">
                        "{{ \Illuminate\Support\Str::limit($firstReview->review_text, 90) }}"
                    </p>
                    <div class="sc-review-float-author">
                        @if($firstReview->author_photo_url)
                            <img src="{{ $firstReview->author_photo_url }}"
                                 alt="{{ $firstReview->author_name }}"
                                 class="sc-review-float-avatar"
                                 loading="lazy">
                        @else
                            <div class="sc-review-float-avatar" style="background:rgba(var(--primary-color-rgb,34,197,94),.18);display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-user" style="color:var(--primary-color);font-size:.8rem;"></i>
                            </div>
                        @endif
                        <div>
                            <div class="sc-review-float-name">{{ $firstReview->author_name }}</div>
                            <div class="sc-review-float-source">{{ $firstReview->source ?? 'Google' }} · Vérifié</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════
     §2  SERVICES — Dark section, right after hero
     ══════════════════════════════════════════════════════════ --}}
@if($svcEnabled)
<section class="sc-services" id="services" aria-label="Nos services">
    <div class="sc-shell">

        <div class="sc-sec-head">
            <div>
                <div class="sc-label">{{ $svcTitle }}</div>
                <h2 class="sc-sec-h2">Ce que nous faisons</h2>
            </div>
            <a href="{{ route('services.index') }}" class="sc-btn-green-outline">
                Voir tous les services <i class="fas fa-arrow-right" style="font-size:.7rem;"></i>
            </a>
        </div>

        @if(count($displayedSvcs))
        <div class="showcase-svc-grid">
            @foreach($displayedSvcs as $svc)
            @php
                $slug  = $svc['slug']  ?? \Illuminate\Support\Str::slug($svc['name'] ?? 'service');
                $name  = $svc['name']  ?? 'Service';
                $icon  = $svc['icon']  ?? 'fa-tree';
                $desc  = $svc['short_description'] ?? '';
                $img   = $svc['featured_image'] ?? null;
                $imgUrl = $img ? asset(ltrim($img, '/')) : null;
            @endphp
            <a href="{{ route('services.show', $slug) }}"
               class="sc-svc-card"
               onclick="if(typeof trackServiceClick==='function')trackServiceClick('{{ addslashes($name) }}','{{ route('services.show',$slug) }}')">

                @if($imgUrl)
                    <img src="{{ $imgUrl }}"
                         alt="{{ $name }}"
                         class="sc-svc-card-img"
                         loading="lazy">
                @else
                    <div class="sc-svc-card-fallback"></div>
                @endif

                <div class="sc-svc-card-overlay"></div>

                <div class="sc-svc-card-body">
                    <div class="sc-svc-card-icon">
                        <i class="fas {{ $icon }}"></i>
                    </div>
                    <div class="sc-svc-card-title">{{ $name }}</div>
                    @if($desc)
                    <div class="sc-svc-card-desc">{{ \Illuminate\Support\Str::limit($desc, 70) }}</div>
                    @endif
                    <div class="sc-svc-card-link">
                        En savoir plus <i class="fas fa-arrow-right" style="font-size:.65rem;"></i>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @else
        <div style="text-align:center;padding:4rem 0;color:rgba(255,255,255,.3);">
            <i class="fas fa-tree" style="font-size:3rem;margin-bottom:1rem;display:block;"></i>
            <p>Nos services seront bientôt disponibles.</p>
        </div>
        @endif

    </div>
</section>
@endif

<div class="sc-divider"></div>

{{-- ══════════════════════════════════════════════════════════
     §3  RÉALISATIONS HIGHLIGHT
     ══════════════════════════════════════════════════════════ --}}
<section class="sc-portfolio" id="realisations" aria-label="Nos réalisations">
    <div class="sc-shell">

        <div class="sc-sec-head">
            <div>
                <div class="sc-label">Portfolio</div>
                <h2 class="sc-sec-h2">Nos réalisations</h2>
            </div>
            @if($portEnabled)
            <a href="{{ route('portfolio.index') }}" class="sc-btn-green-outline">
                Voir tout le portfolio <i class="fas fa-arrow-right" style="font-size:.7rem;"></i>
            </a>
            @endif
        </div>

        @if($portEnabled && !empty($homeConfig['sections']['portfolio']['items']))
        <div class="sc-port-grid">
            @foreach(array_slice($homeConfig['sections']['portfolio']['items'], 0, 3) as $portItem)
            @php
                $portImg   = $portItem['image'] ?? null;
                $portImgUrl = $portImg ? asset(ltrim($portImg, '/')) : null;
                $portLabel  = $portItem['title'] ?? $portItem['label'] ?? '';
            @endphp
            <a href="{{ route('portfolio.index') }}" class="sc-port-card">
                @if($portImgUrl)
                    <img src="{{ $portImgUrl }}" alt="{{ $portLabel }}" loading="lazy">
                @else
                    <div style="width:100%;height:100%;background:linear-gradient(135deg,#0f2318 0%,#1a3d2a 100%);display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-images" style="font-size:2.5rem;color:rgba(255,255,255,.12);"></i>
                    </div>
                @endif
                <div class="sc-port-card-overlay"></div>
                @if($portLabel)
                <div class="sc-port-card-label">{{ $portLabel }}</div>
                @endif
            </a>
            @endforeach
        </div>
        @else
        <div class="sc-port-cta-block">
            <div class="sc-label" style="justify-content:center;">Nos chantiers</div>
            <h3 style="font-size:clamp(1.5rem,2.5vw,2rem);font-weight:900;color:#fff;margin-bottom:.75rem;">
                Découvrez nos chantiers en Oise
            </h3>
            <p>
                Élagage, abattage, taille de haies : consultez nos réalisations
                dans tout le département 60.
            </p>
            <a href="{{ route('portfolio.index') }}" class="sc-btn-solid">
                <i class="fas fa-images"></i>
                Voir nos réalisations
            </a>
        </div>
        @endif

    </div>
</section>

<div class="sc-divider"></div>

{{-- ══════════════════════════════════════════════════════════
     §4  À PROPOS — Dark green
     ══════════════════════════════════════════════════════════ --}}
@if($aboutEnabled)
<section class="sc-about" id="a-propos" aria-label="À propos">
    <div class="sc-shell">

        <div class="sc-about-grid">

            {{-- Left: photo + stats --}}
            <div>
                <div class="sc-about-photo-wrap">
                    @if($aboutImgUrl)
                        <img src="{{ $aboutImgUrl }}"
                             alt="{{ setting('company_name','Louis Hoffmann Élagage') }}"
                             loading="lazy">
                    @else
                        <div class="sc-about-photo-placeholder">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    @endif
                </div>

                <div class="sc-about-stats">
                    <div class="sc-stat-box">
                        <div class="sc-stat-val">500+</div>
                        <div class="sc-stat-lbl">Chantiers réalisés</div>
                    </div>
                    <div class="sc-stat-box">
                        <div class="sc-stat-val">15+</div>
                        <div class="sc-stat-lbl">Ans d'expérience</div>
                    </div>
                    <div class="sc-stat-box">
                        <div class="sc-stat-val">60+</div>
                        <div class="sc-stat-lbl">Communes Oise</div>
                    </div>
                    <div class="sc-stat-box">
                        <div class="sc-stat-val">4.9★</div>
                        <div class="sc-stat-lbl">Note Google</div>
                    </div>
                </div>
            </div>

            {{-- Right: text + checklist --}}
            <div class="sc-about-right">
                <div class="sc-label">{{ $aboutTitle }}</div>
                <h2>
                    Votre expert en élagage<br>
                    <span style="color:var(--primary-color);">dans l'Oise depuis 15 ans</span>
                </h2>

                @if($aboutText)
                <p>{{ $aboutText }}</p>
                @else
                <p>
                    Artisan élageur certifié, nous intervenons sur l'ensemble
                    du département de l'Oise pour tous vos travaux d'arboriculture.
                    Notre équipe qualifiée assure des interventions soignées,
                    respectueuses de l'environnement et dans le respect des normes de sécurité.
                </p>
                @endif

                <ul class="sc-checklist">
                    <li>
                        <span class="sc-check-icon">
                            <svg viewBox="0 0 12 9"><path d="M1 4l3.5 3.5L11 1" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        Artisan certifié CS Taille &amp; Soins des Arbres
                    </li>
                    <li>
                        <span class="sc-check-icon">
                            <svg viewBox="0 0 12 9"><path d="M1 4l3.5 3.5L11 1" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        Assurance Responsabilité Civile Professionnelle
                    </li>
                    <li>
                        <span class="sc-check-icon">
                            <svg viewBox="0 0 12 9"><path d="M1 4l3.5 3.5L11 1" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        Devis gratuit et réponse sous 24h
                    </li>
                    <li>
                        <span class="sc-check-icon">
                            <svg viewBox="0 0 12 9"><path d="M1 4l3.5 3.5L11 1" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        Valorisation écologique des déchets végétaux
                    </li>
                    <li>
                        <span class="sc-check-icon">
                            <svg viewBox="0 0 12 9"><path d="M1 4l3.5 3.5L11 1" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        Intervention sur tout le département 60 — Oise
                    </li>
                </ul>

                <div style="display:flex;flex-wrap:wrap;gap:.875rem;">
                    <a href="{{ route('form.step', 'propertyType') }}"
                       onclick="if(typeof trackFormClick==='function')trackFormClick('{{ request()->url() }}')"
                       class="sc-btn-solid">
                        <i class="fas fa-calculator"></i>
                        Demander un devis
                    </a>
                    <a href="{{ route('contact') }}" class="sc-btn-outline">
                        <i class="fas fa-envelope"></i>
                        Nous contacter
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>
@endif

<div class="sc-divider"></div>

{{-- ══════════════════════════════════════════════════════════
     §5  TÉMOIGNAGES
     ══════════════════════════════════════════════════════════ --}}
@if($revEnabled && isset($reviews) && $reviews->count())
<section class="sc-reviews" id="temoignages" aria-label="Témoignages clients">
    <div class="sc-shell">

        <div class="sc-sec-head">
            <div>
                <div class="sc-label">{{ $revTitle }}</div>
                <h2 class="sc-sec-h2">Ce que disent nos clients</h2>
            </div>
            <a href="{{ route('reviews.all') }}" class="sc-btn-green-outline">
                Tous les avis <i class="fas fa-arrow-right" style="font-size:.7rem;"></i>
            </a>
        </div>

        @php
            $displayedReviews = $reviews->take((int)$revLimit);
            $featuredRev = $displayedReviews->first();
            $smallRevs   = $displayedReviews->skip(1)->take(3);
        @endphp

        <div class="sc-reviews-inner">

            {{-- Featured review --}}
            @if($featuredRev)
            <div>
                <div class="sc-rev-featured">
                    <div class="sc-rev-stars">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star sc-rev-star"></i>
                        @endfor
                    </div>
                    <p class="sc-rev-text">
                        "{{ \Illuminate\Support\Str::limit($featuredRev->review_text, 300) }}"
                    </p>
                    <div class="sc-rev-author">
                        @if($featuredRev->author_photo_url)
                            <img src="{{ $featuredRev->author_photo_url }}"
                                 alt="{{ $featuredRev->author_name }}"
                                 class="sc-rev-avatar"
                                 loading="lazy">
                        @else
                            <div class="sc-rev-avatar-placeholder">
                                <i class="fas fa-user" style="color:var(--primary-color);font-size:.8rem;"></i>
                            </div>
                        @endif
                        <div>
                            <div class="sc-rev-name">{{ $featuredRev->author_name }}</div>
                            <div class="sc-rev-date">
                                {{ $featuredRev->source ?? 'Google' }}
                                @if($featuredRev->review_date)
                                    &middot; {{ \Carbon\Carbon::parse($featuredRev->review_date)->translatedFormat('F Y') }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if($totalRev || $avgRating)
                <div class="sc-rev-aggregate" style="background:#111716;border-radius:0 0 22px 22px;border:1px solid rgba(255,255,255,.08);border-top:none;">
                    <div class="sc-rev-agg-num">{{ number_format((float)$avgRating, 1) }}</div>
                    <div>
                        <div class="sc-rev-agg-stars">
                            @for($i=1;$i<=5;$i++)
                                <i class="fas fa-star" style="color:#fbbf24;font-size:.8rem;"></i>
                            @endfor
                        </div>
                        <div class="sc-rev-agg-count">
                            {{ $totalRev ? $totalRev.' avis vérifiés' : 'Note moyenne' }} · Google
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- Small reviews --}}
            @if($smallRevs->count())
            <div class="sc-rev-small-col">
                @foreach($smallRevs as $rev)
                <div class="sc-rev-card">
                    <div class="sc-rev-stars">
                        @for($i=1;$i<=5;$i++)
                            <i class="fas fa-star sc-rev-star"></i>
                        @endfor
                    </div>
                    <p class="sc-rev-text">
                        "{{ \Illuminate\Support\Str::limit($rev->review_text, 130) }}"
                    </p>
                    <div class="sc-rev-author">
                        @if($rev->author_photo_url)
                            <img src="{{ $rev->author_photo_url }}"
                                 alt="{{ $rev->author_name }}"
                                 class="sc-rev-avatar"
                                 loading="lazy">
                        @else
                            <div class="sc-rev-avatar-placeholder">
                                <i class="fas fa-user" style="color:var(--primary-color);font-size:.75rem;"></i>
                            </div>
                        @endif
                        <div>
                            <div class="sc-rev-name">{{ $rev->author_name }}</div>
                            <div class="sc-rev-date">
                                {{ $rev->source ?? 'Google' }}
                                @if($rev->review_date)
                                    &middot; {{ \Carbon\Carbon::parse($rev->review_date)->translatedFormat('M Y') }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

        </div>

    </div>
</section>

<div class="sc-divider"></div>
@endif

{{-- ══════════════════════════════════════════════════════════
     §6  CTA FINAL — Green gradient
     ══════════════════════════════════════════════════════════ --}}
@if($ctaEnabled)
<section class="sc-cta" id="cta" aria-label="Appel à l'action">
    <div class="sc-cta-halo"></div>
    <div class="sc-shell sc-cta-inner">

        <div class="sc-label" style="justify-content:center;margin-bottom:1.25rem;">
            Action
        </div>

        <h2>{{ $ctaTitle }}</h2>

        <p>
            Contactez-nous dès aujourd'hui pour un devis gratuit et
            sans engagement. Réponse garantie sous 24h.
        </p>

        <div class="sc-cta-btns">
            <a href="{{ route('form.step', 'propertyType') }}"
               onclick="if(typeof trackFormClick==='function')trackFormClick('{{ request()->url() }}')"
               class="sc-btn-solid"
               style="font-size:1.05rem;padding:1.1rem 2.25rem;">
                <i class="fas fa-file-alt"></i>
                Obtenir mon devis gratuit
                <i class="fas fa-arrow-right" style="font-size:.8rem;"></i>
            </a>
            <a href="tel:{{ $phoneRaw }}" class="sc-btn-outline" style="font-size:1.05rem;padding:1.1rem 2.25rem;">
                <i class="fas fa-phone" style="color:var(--primary-color);"></i>
                {{ $phone }}
            </a>
        </div>

        <div class="sc-trust-pills">
            <span class="sc-trust-pill"><i class="fas fa-check-circle"></i>Devis 100% gratuit</span>
            <span class="sc-trust-pill"><i class="fas fa-check-circle"></i>Sans engagement</span>
            <span class="sc-trust-pill"><i class="fas fa-check-circle"></i>Réponse sous 24h</span>
            <span class="sc-trust-pill"><i class="fas fa-check-circle"></i>Artisan certifié</span>
            <span class="sc-trust-pill"><i class="fas fa-check-circle"></i>RC Pro assurée</span>
        </div>

    </div>
</section>
@endif

</div>{{-- /.lp --}}

@include('home.partials.ecology-financing')
@include('home.partials.featured-partner')
@include('home.partials.partners-logos')
@include('home.partials.scripts')

<script>
function trackServiceClick(n, p) {
    fetch('/api/track-service-click?service=' + encodeURIComponent(n), { method: 'GET' }).catch(function(){});
}
function trackFormClick(p) {
    fetch('/api/track-form-click', { method: 'GET' }).catch(function(){});
}
</script>
