{{-- ============================================================
     LANDING PAGE "Classique" — Dark Bold Editorial
     Design inspiré référence HTML/CSS professionnelle
     Ordre : Hero → Marquee → Services → À propos → Processus
             → Zones → Témoignages → FAQ → CTA final
     ============================================================ --}}

@php
    $servicesRaw  = \App\Models\Setting::get('services', '[]');
    $allServices  = is_string($servicesRaw) ? json_decode($servicesRaw, true) : ($servicesRaw ?? []);
    if(!is_array($allServices)) $allServices = [];
    $svcList = array_values(array_filter($allServices, fn($s) => is_array($s) && ($s['is_visible'] ?? true)));

    $phone    = setting('company_phone', '06 42 21 41 51');
    $phoneRaw = setting('company_phone_raw', $phone);
    $heroTitle = $homeConfig['sections']['hero']['title'] ?? 'Élagueur & Abatteur professionnel dans l\'Oise (60)';

    $heroImg    = $homeConfig['hero']['background_image'] ?? null;
    $heroImgUrl = $heroImg ? asset(ltrim($heroImg, '/')) : null;
@endphp

<style>
/* ═══════════════════════════════════════════════════════════════
   BASE & RESET
═══════════════════════════════════════════════════════════════ */
.lp *, .lp *::before, .lp *::after {
    box-sizing: border-box;
}
.lp {
    font-family: inherit;
    background: #0d1110;
    color: #fff;
}

/* ═══════════════════════════════════════════════════════════════
   SHELL
═══════════════════════════════════════════════════════════════ */
.cl-shell {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 7%;
}

/* ═══════════════════════════════════════════════════════════════
   SECTION LABEL
═══════════════════════════════════════════════════════════════ */
.cl-label {
    display: inline-flex;
    align-items: center;
    gap: .625rem;
    font-size: .72rem;
    font-weight: 800;
    letter-spacing: .18em;
    text-transform: uppercase;
    color: var(--primary-color);
    margin-bottom: 1rem;
}
.cl-label::before {
    content: '';
    display: inline-block;
    width: 2rem;
    height: 2px;
    background: var(--primary-color);
    border-radius: 2px;
}

/* ═══════════════════════════════════════════════════════════════
   §1 HERO
═══════════════════════════════════════════════════════════════ */
.cl-hero {
    position: relative;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    overflow: hidden;
}
.cl-hero-bg {
    position: absolute;
    inset: 0;
    z-index: 0;
}
.cl-hero-bg img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
}
.cl-hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, rgba(6,8,8,.96) 0%, rgba(8,10,8,.85) 40%, rgba(8,10,8,.2) 100%);
}
.cl-hero-no-img {
    position: absolute;
    inset: 0;
    background: #0d1110;
}
.cl-hero-glow {
    position: absolute;
    top: -200px;
    left: -200px;
    width: 800px;
    height: 800px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(108,163,34,.18) 0%, transparent 65%);
    pointer-events: none;
    z-index: 1;
}
.cl-hero-inner {
    position: relative;
    z-index: 10;
    padding: 160px 7% 80px;
}
.cl-hero-grid {
    display: grid;
    gap: 3rem;
    align-items: flex-end;
    position: relative;
}

/* Hero tag */
.cl-hero-tag {
    display: inline-flex;
    align-items: center;
    gap: .75rem;
    margin-bottom: 1.75rem;
}
.cl-hero-tag-line {
    width: 2.5rem;
    height: 2px;
    background: var(--primary-color);
    border-radius: 2px;
}
.cl-hero-tag-text {
    font-size: .72rem;
    font-weight: 800;
    letter-spacing: .18em;
    text-transform: uppercase;
    color: var(--primary-color);
}

/* Hero H1 */
.cl-h1 {
    font-size: clamp(3.2rem, 7vw, 6rem);
    font-weight: 950;
    line-height: 0.95;
    letter-spacing: -2px;
    color: #fff;
    margin: 0 0 1.5rem;
}
.cl-h1 .hl {
    color: var(--primary-color);
}

/* Hero subtitle */
.cl-hero-sub {
    font-size: clamp(1rem, 1.8vw, 1.15rem);
    color: rgba(255,255,255,.65);
    line-height: 1.75;
    max-width: 560px;
    margin: 0 0 2.5rem;
}

/* Hero CTAs */
.cl-hero-ctas {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 3rem;
}
.cl-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: .7rem;
    background: var(--primary-color);
    color: #fff;
    font-size: 1rem;
    font-weight: 900;
    padding: 1rem 2rem;
    border-radius: 14px;
    text-decoration: none;
    transition: opacity .2s, transform .2s;
    box-shadow: 0 8px 28px rgba(108,163,34,.35);
}
.cl-btn-primary:hover {
    opacity: .9;
    transform: translateY(-2px);
}
.cl-btn-phone {
    display: inline-flex;
    align-items: center;
    gap: .7rem;
    border: 1.5px solid rgba(255,255,255,.22);
    color: #fff;
    font-size: 1rem;
    font-weight: 700;
    padding: 1rem 2rem;
    border-radius: 14px;
    text-decoration: none;
    transition: background .2s, border-color .2s;
}
.cl-btn-phone:hover {
    background: rgba(255,255,255,.07);
    border-color: rgba(255,255,255,.4);
}
.cl-phone-dot {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.cl-phone-dot-ring {
    position: absolute;
    inset: -4px;
    border-radius: 50%;
    background: var(--primary-color);
    opacity: .25;
    animation: cl-pulse 2s ease-in-out infinite;
}
@keyframes cl-pulse {
    0%, 100% { opacity: .25; transform: scale(1); }
    50% { opacity: .1; transform: scale(1.4); }
}

/* Features grid at bottom of hero */
.cl-features {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 28px;
    margin-top: 65px;
}
.cl-feature-item {
    border-left: 1px solid rgba(255,255,255,.15);
    padding-left: 22px;
}
.cl-feature-title {
    font-size: 14px;
    font-weight: 800;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--primary-color);
    margin-bottom: .35rem;
}
.cl-feature-desc {
    font-size: .82rem;
    color: #d1d1d1;
    line-height: 1.65;
}

/* Floating review card */
.cl-review-card {
    position: absolute;
    right: 7%;
    bottom: 80px;
    width: 330px;
    padding: 28px;
    background: rgba(10,12,11,.92);
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,.08);
    backdrop-filter: blur(12px);
    z-index: 20;
}
.cl-review-stars {
    display: flex;
    gap: 3px;
    margin-bottom: .75rem;
}
.cl-review-star {
    color: #fbbf24;
    font-size: .8rem;
}
.cl-review-text {
    color: rgba(255,255,255,.78);
    font-size: .875rem;
    font-style: italic;
    line-height: 1.7;
    margin: 0 0 1rem;
}
.cl-review-author {
    display: flex;
    align-items: center;
    gap: .75rem;
}
.cl-review-avatar {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    border: 1.5px solid rgba(255,255,255,.15);
}
.cl-review-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.cl-review-avatar-initials {
    width: 100%;
    height: 100%;
    background: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 900;
    font-size: .9rem;
}
.cl-review-name {
    font-weight: 700;
    font-size: .85rem;
    color: #fff;
}
.cl-review-date {
    font-size: .72rem;
    color: rgba(255,255,255,.35);
}

/* ═══════════════════════════════════════════════════════════════
   §2 MARQUEE
═══════════════════════════════════════════════════════════════ */
.cl-marquee-strip {
    background: #0d1110;
    border-top: 1px solid rgba(255,255,255,.06);
    border-bottom: 1px solid rgba(255,255,255,.06);
    padding: .85rem 0;
    overflow: hidden;
}
.cl-marquee-track {
    display: flex;
    width: max-content;
    animation: cl-marquee 28s linear infinite;
}
.cl-marquee-track:hover {
    animation-play-state: paused;
}
@keyframes cl-marquee {
    from { transform: translateX(0); }
    to   { transform: translateX(-50%); }
}
.cl-marquee-item {
    display: flex;
    align-items: center;
    gap: .625rem;
    padding: 0 2rem;
    flex-shrink: 0;
}
.cl-marquee-dot {
    color: rgba(255,255,255,.12);
    flex-shrink: 0;
}

/* ═══════════════════════════════════════════════════════════════
   §3 SERVICES
═══════════════════════════════════════════════════════════════ */
.cl-services {
    background: #111716;
    padding: 6rem 0;
}
.cl-svc-header {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1.5rem;
    margin-bottom: 3.5rem;
}
.cl-h2 {
    font-size: clamp(1.9rem, 4.5vw, 3.2rem);
    font-weight: 900;
    line-height: 1.05;
    margin: 0;
    color: #fff;
}
.cl-h2 .dim {
    color: rgba(255,255,255,.32);
}
.cl-svc-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
}
.cl-svc-card {
    position: relative;
    height: 280px;
    border-radius: 16px;
    overflow: hidden;
    display: block;
    text-decoration: none;
    transition: transform .3s ease, box-shadow .3s ease;
    border-bottom: 3px solid var(--primary-color);
}
.cl-svc-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 50px rgba(0,0,0,.4);
}
.cl-svc-card-img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform .6s ease;
}
.cl-svc-card:hover .cl-svc-card-img {
    transform: scale(1.06);
}
.cl-svc-card-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(6,10,8,.95) 0%, rgba(6,10,8,.5) 50%, rgba(6,10,8,.1) 100%);
}
.cl-svc-card-fallback {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #0a2e15 0%, #1a5c35 100%);
}
.cl-svc-card-body {
    position: absolute;
    inset: 0;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}
.cl-svc-card-title {
    color: #fff;
    font-weight: 900;
    font-size: 1.05rem;
    line-height: 1.2;
    margin: 0 0 .35rem;
}
.cl-svc-card-desc {
    color: rgba(255,255,255,.55);
    font-size: .78rem;
    line-height: 1.6;
    margin: 0;
}
.cl-btn-ghost {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    font-weight: 700;
    font-size: .85rem;
    padding: .7rem 1.4rem;
    border-radius: 10px;
    border: 1.5px solid rgba(255,255,255,.15);
    color: rgba(255,255,255,.7);
    text-decoration: none;
    transition: border-color .2s, color .2s;
}
.cl-btn-ghost:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
}

/* ═══════════════════════════════════════════════════════════════
   §4 À PROPOS
═══════════════════════════════════════════════════════════════ */
.cl-about {
    background: #0f1f13;
    padding: 6rem 0;
    position: relative;
    overflow: hidden;
}
.cl-about-halo {
    position: absolute;
    top: 0;
    left: 0;
    width: 600px;
    height: 600px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(108,163,34,.1) 0%, transparent 65%);
    transform: translate(-40%, -40%);
    pointer-events: none;
    z-index: 0;
}
.cl-about-grid {
    display: grid;
    gap: 4rem;
    align-items: stretch;
    position: relative;
    z-index: 1;
}
.cl-about-photo-col {
    display: flex;
    flex-direction: column;
}
.cl-about-photo {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    flex: 1;
    min-height: 360px;
}
.cl-about-photo img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.cl-about-photo-fallback {
    position: absolute;
    inset: 0;
    background: linear-gradient(145deg, #0d3b22, #1a5c35);
    display: flex;
    align-items: center;
    justify-content: center;
}
.cl-about-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: .75rem;
    margin-top: 1.25rem;
}
.cl-about-stat {
    border-radius: 12px;
    padding: .875rem .5rem;
    border: 1px solid rgba(255,255,255,.07);
    background: rgba(255,255,255,.03);
    text-align: center;
}
.cl-about-stat-num {
    font-weight: 900;
    font-size: 1.05rem;
    line-height: 1.2;
    margin-bottom: .2rem;
}
.cl-about-stat-lbl {
    color: rgba(255,255,255,.38);
    font-size: .68rem;
    font-weight: 600;
    line-height: 1.3;
}
.cl-about-text-col {
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.cl-about-body {
    color: rgba(255,255,255,.52);
    font-size: .95rem;
    line-height: 1.8;
    margin: 0 0 1.75rem;
}
.cl-checklist {
    list-style: none;
    margin: 0 0 2rem;
    padding: 0;
}
.cl-checklist li {
    display: flex;
    align-items: flex-start;
    gap: .75rem;
    padding: .45rem 0;
    font-size: .88rem;
    color: rgba(255,255,255,.62);
    line-height: 1.6;
}
.cl-checklist li::before {
    content: '';
    display: inline-flex;
    flex-shrink: 0;
    width: 1.2rem;
    height: 1.2rem;
    margin-top: .1rem;
    border-radius: .3rem;
    background-color: var(--primary-color);
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 12 9' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 4l3.5 3.5L11 1' stroke='white' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-size: 62% 62%;
    background-repeat: no-repeat;
    background-position: center;
}
.cl-about-ctas {
    display: flex;
    flex-wrap: wrap;
    gap: .875rem;
}
.cl-btn-green {
    display: inline-flex;
    align-items: center;
    gap: .6rem;
    font-weight: 800;
    font-size: .9rem;
    padding: .875rem 1.75rem;
    border-radius: 12px;
    background: var(--primary-color);
    color: #fff;
    text-decoration: none;
    transition: opacity .2s, transform .2s;
}
.cl-btn-green:hover {
    opacity: .9;
    transform: translateY(-1px);
}
.cl-btn-outline {
    display: inline-flex;
    align-items: center;
    gap: .6rem;
    font-weight: 700;
    font-size: .9rem;
    padding: .875rem 1.75rem;
    border-radius: 12px;
    border: 1.5px solid rgba(255,255,255,.15);
    color: #fff;
    text-decoration: none;
    transition: background .2s, border-color .2s;
}
.cl-btn-outline:hover {
    background: rgba(255,255,255,.07);
    border-color: rgba(255,255,255,.32);
}

/* ═══════════════════════════════════════════════════════════════
   §5 PROCESSUS
═══════════════════════════════════════════════════════════════ */
.cl-process {
    background: #111716;
    padding: 6rem 0;
}
.cl-process-header {
    text-align: center;
    margin-bottom: 4rem;
}
.cl-process-grid {
    display: grid;
    gap: 2rem;
    position: relative;
}
.cl-process-line {
    display: none;
}
.cl-process-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 1.5rem 1rem;
    position: relative;
    z-index: 1;
}
.cl-process-num {
    font-size: clamp(3rem, 5vw, 4.5rem);
    font-weight: 900;
    color: var(--primary-color);
    line-height: 1;
    margin-bottom: .5rem;
    opacity: .25;
}
.cl-process-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}
.cl-process-step-title {
    font-weight: 900;
    font-size: 1rem;
    color: #fff;
    margin: 0 0 .5rem;
}
.cl-process-step-desc {
    font-size: .83rem;
    color: rgba(255,255,255,.42);
    line-height: 1.7;
    margin: 0;
}
.cl-process-cta {
    text-align: center;
    margin-top: 3.5rem;
}

/* ═══════════════════════════════════════════════════════════════
   §6 ZONES
═══════════════════════════════════════════════════════════════ */
.cl-zones {
    background: #0d1110;
    padding: 6rem 0;
    border-top: 1px solid rgba(255,255,255,.05);
    border-bottom: 1px solid rgba(255,255,255,.05);
}
.cl-zones-inner {
    display: flex;
    flex-direction: column;
    gap: 3rem;
}
.cl-zones-left {
    flex-shrink: 0;
}
.cl-zones-title {
    font-size: clamp(1.6rem, 3.5vw, 2.4rem);
    font-weight: 900;
    color: #fff;
    line-height: 1.1;
    margin: 0 0 .875rem;
}
.cl-zones-desc {
    font-size: .88rem;
    color: rgba(255,255,255,.42);
    line-height: 1.75;
    margin: 0 0 1.5rem;
    max-width: 340px;
}
.cl-zones-pills {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
}
.cl-pill {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .45rem 1rem;
    border-radius: 50px;
    background: rgba(108,163,34,.1);
    border: 1px solid rgba(108,163,34,.25);
    color: rgba(255,255,255,.72);
    font-size: .8rem;
    font-weight: 600;
    text-decoration: none;
    transition: background .18s, border-color .18s, color .18s;
}
.cl-pill:hover {
    background: rgba(108,163,34,.22);
    border-color: rgba(108,163,34,.5);
    color: #fff;
}
.cl-pill-postal {
    color: rgba(255,255,255,.35);
    font-size: .72rem;
    font-family: monospace;
}

/* ═══════════════════════════════════════════════════════════════
   §7 TÉMOIGNAGES
═══════════════════════════════════════════════════════════════ */
.cl-reviews {
    background: #0d1110;
    padding: 6rem 0;
}
.cl-reviews-header {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1.5rem;
    margin-bottom: 3.5rem;
}
.cl-reviews-rating {
    display: flex;
    align-items: center;
    gap: .75rem;
    margin-top: .75rem;
}
.cl-reviews-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
}
.cl-review-featured-card {
    background: linear-gradient(135deg, #0a1e10 0%, #142e1c 60%, #0a1e10 100%);
    border-radius: 20px;
    padding: 2.5rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,.06);
}
.cl-review-featured-quote {
    position: absolute;
    top: -.5rem;
    left: .5rem;
    font-size: 9rem;
    font-weight: 900;
    color: rgba(255,255,255,.04);
    line-height: 1;
    pointer-events: none;
    user-select: none;
}
.cl-review-featured-text {
    color: rgba(255,255,255,.8);
    font-size: 1.1rem;
    font-style: italic;
    line-height: 1.7;
    margin: 0 0 2rem;
    position: relative;
    z-index: 1;
}
.cl-review-featured-foot {
    display: flex;
    align-items: center;
    gap: .875rem;
    position: relative;
    z-index: 1;
}
.cl-review-small-card {
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 18px;
    padding: 1.75rem;
    display: flex;
    flex-direction: column;
    transition: background .2s, border-color .2s;
}
.cl-review-small-card:hover {
    background: rgba(255,255,255,.06);
    border-color: rgba(255,255,255,.12);
}
.cl-review-small-text {
    color: rgba(255,255,255,.58);
    font-size: .855rem;
    font-style: italic;
    line-height: 1.7;
    flex: 1;
    margin: 0 0 1.25rem;
}
.cl-review-foot {
    display: flex;
    align-items: center;
    gap: .625rem;
    padding-top: 1rem;
    border-top: 1px solid rgba(255,255,255,.06);
}
.cl-review-avatar-large {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    border: 1.5px solid rgba(255,255,255,.12);
}
.cl-review-avatar-small {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    border: 1px solid rgba(255,255,255,.1);
}
.cl-review-avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.cl-review-avatar-init {
    width: 100%;
    height: 100%;
    background: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 900;
    font-size: .95rem;
}
.cl-review-author-name {
    font-weight: 700;
    font-size: .88rem;
    color: #fff;
}
.cl-review-author-date {
    font-size: .72rem;
    color: rgba(255,255,255,.32);
}
.cl-review-source-badge {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: .4rem;
    background: rgba(255,255,255,.06);
    padding: .3rem .7rem;
    border-radius: 50px;
    color: rgba(255,255,255,.38);
    font-size: .7rem;
    font-weight: 600;
}

/* ═══════════════════════════════════════════════════════════════
   §8 FAQ
═══════════════════════════════════════════════════════════════ */
.cl-faq {
    background: #111716;
    padding: 6rem 0;
}
.cl-faq-layout {
    display: grid;
    gap: 3rem;
}
.cl-faq-left {
    position: sticky;
    top: 6rem;
    align-self: flex-start;
}
.cl-faq-left-title {
    font-size: clamp(1.7rem, 3.5vw, 2.6rem);
    font-weight: 900;
    color: #fff;
    line-height: 1.1;
    margin: 0 0 1rem;
}
.cl-faq-left-sub {
    color: rgba(255,255,255,.42);
    font-size: .9rem;
    line-height: 1.75;
    margin: 0 0 1.75rem;
}
.cl-faq-item details {
    border-bottom: 1px solid rgba(255,255,255,.08);
}
.cl-faq-item details summary {
    list-style: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.25rem 0;
    font-weight: 700;
    font-size: .95rem;
    color: rgba(255,255,255,.75);
    transition: color .2s;
}
.cl-faq-item details summary::-webkit-details-marker { display: none; }
.cl-faq-item details[open] summary { color: var(--primary-color); }
.cl-faq-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    border: 1.5px solid rgba(255,255,255,.15);
    color: rgba(255,255,255,.5);
    flex-shrink: 0;
    transition: transform .25s ease, border-color .2s, color .2s;
    font-size: .7rem;
}
.cl-faq-item details[open] .cl-faq-icon {
    transform: rotate(45deg);
    border-color: var(--primary-color);
    color: var(--primary-color);
}
.cl-faq-body {
    padding: 0 0 1.25rem;
    font-size: .88rem;
    color: rgba(255,255,255,.42);
    line-height: 1.8;
}

/* ═══════════════════════════════════════════════════════════════
   §9 CTA FINAL
═══════════════════════════════════════════════════════════════ */
.cl-cta-final {
    background: linear-gradient(155deg, #0a2214 0%, #1a5c35 50%, #0a2214 100%);
    position: relative;
    overflow: hidden;
}
.cl-cta-dots {
    position: absolute;
    inset: 0;
    background-image: radial-gradient(rgba(255,255,255,.45) 1px, transparent 1px);
    background-size: 28px 28px;
    opacity: .03;
    pointer-events: none;
}
.cl-cta-glow {
    position: absolute;
    top: 0;
    left: 50%;
    width: 900px;
    height: 600px;
    border-radius: 50%;
    background: radial-gradient(ellipse, rgba(108,163,34,.22) 0%, transparent 65%);
    transform: translateX(-50%) translateY(-45%);
    pointer-events: none;
}
.cl-cta-inner {
    position: relative;
    z-index: 1;
    text-align: center;
    padding-top: 7rem;
    padding-bottom: 7rem;
}
.cl-cta-badge {
    display: inline-flex;
    align-items: center;
    gap: .625rem;
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 50px;
    padding: .5rem 1.25rem;
    margin-bottom: 2rem;
    color: rgba(255,255,255,.55);
    font-size: .72rem;
    font-weight: 800;
    letter-spacing: .16em;
    text-transform: uppercase;
}
.cl-cta-badge-dot {
    width: .5rem;
    height: .5rem;
    border-radius: 50%;
    background: var(--primary-color);
    animation: cl-pulse 2s infinite;
    display: inline-block;
}
.cl-cta-h2 {
    font-size: clamp(2.4rem, 7vw, 5.5rem);
    font-weight: 950;
    color: #fff;
    line-height: 0.95;
    letter-spacing: -2px;
    margin: 0 0 1.5rem;
}
.cl-cta-sub {
    color: rgba(255,255,255,.45);
    font-size: 1.05rem;
    line-height: 1.75;
    max-width: 600px;
    margin: 0 auto 3rem;
}
.cl-cta-btns {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 3.5rem;
}
.cl-btn-cta-white {
    display: inline-flex;
    align-items: center;
    gap: .75rem;
    background: #fff;
    color: var(--primary-color);
    font-size: 1.05rem;
    font-weight: 900;
    padding: 1.1rem 2.5rem;
    border-radius: 16px;
    text-decoration: none;
    box-shadow: 0 10px 40px rgba(0,0,0,.3);
    transition: transform .2s, background .2s;
}
.cl-btn-cta-white:hover {
    background: #f0fdf4;
    transform: scale(1.03);
}
.cl-btn-cta-outline {
    display: inline-flex;
    align-items: center;
    gap: .75rem;
    border: 1.5px solid rgba(255,255,255,.22);
    color: #fff;
    font-size: 1.05rem;
    font-weight: 700;
    padding: 1.1rem 2.5rem;
    border-radius: 16px;
    text-decoration: none;
    transition: background .2s, border-color .2s;
}
.cl-btn-cta-outline:hover {
    background: rgba(255,255,255,.08);
    border-color: rgba(255,255,255,.4);
}
.cl-trust-pills {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: .75rem;
}
.cl-trust-pill {
    display: flex;
    align-items: center;
    gap: .5rem;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.1);
    padding: .45rem 1.1rem;
    border-radius: 50px;
    color: rgba(255,255,255,.45);
    font-size: .78rem;
    font-weight: 600;
}

/* ═══════════════════════════════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════════════════════════════ */
@media (min-width: 768px) {
    .cl-zones-inner {
        flex-direction: row;
        align-items: flex-start;
        gap: 4rem;
    }
    .cl-zones-left { flex: 0 0 320px; }
    .cl-zones-right { flex: 1; }
    .cl-process-grid { grid-template-columns: repeat(2, 1fr); }
    .cl-faq-layout { grid-template-columns: 1fr; }
}

@media (min-width: 1024px) {
    .cl-hero-grid {
        grid-template-columns: 1fr;
    }
    .cl-about-grid {
        grid-template-columns: 1fr 1fr;
    }
    .cl-process-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 0;
    }
    .cl-process-line {
        display: block;
        position: absolute;
        top: 3.5rem;
        left: 12%;
        right: 12%;
        height: 2px;
        background: linear-gradient(90deg, var(--primary-color), rgba(108,163,34,.4), var(--primary-color));
        opacity: .3;
        z-index: 0;
    }
    .cl-reviews-grid {
        grid-template-columns: repeat(3, 1fr);
    }
    .cl-review-featured-card {
        grid-column: span 2;
    }
    .cl-faq-layout {
        grid-template-columns: 340px 1fr;
        gap: 5rem;
    }
    .cl-svc-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 1023px) {
    .cl-review-card {
        position: relative;
        right: auto;
        bottom: auto;
        width: auto;
        margin-top: 2rem;
    }
    .cl-features {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 767px) {
    .cl-features {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    .cl-h1 { letter-spacing: -1px; }
    .cl-svc-grid { grid-template-columns: 1fr; }
    .cl-about-stats { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 479px) {
    .cl-features { grid-template-columns: 1fr; }
    .cl-about-stats { grid-template-columns: repeat(2, 1fr); }
}
</style>

<div class="lp">

{{-- ══════════════════════════════════════════════════════════
     §1  HERO — Dark bold reference design
     ══════════════════════════════════════════════════════════ --}}
<section class="cl-hero">

    {{-- Background --}}
    <div class="cl-hero-bg">
        @if($heroImgUrl)
            <img src="{{ $heroImgUrl }}"
                 alt="Élagage professionnel dans l'Oise"
                 fetchpriority="high"
                 decoding="async">
            <div class="cl-hero-overlay"></div>
        @else
            <div class="cl-hero-no-img"></div>
            <div class="cl-hero-glow"></div>
        @endif
    </div>

    <div class="cl-hero-inner">
        <div class="cl-shell">
            <div class="cl-hero-grid">

                {{-- ── Colonne texte ── --}}
                <div>
                    {{-- Tag --}}
                    <div class="cl-hero-tag">
                        <span class="cl-hero-tag-line"></span>
                        <span class="cl-hero-tag-text">Élagueur certifié &middot; Département 60 &middot; Oise</span>
                    </div>

                    {{-- H1 --}}
                    <h1 class="cl-h1">
                        {!! preg_replace('/\(([^)]+)\)/', '<span class="hl">($1)</span>', e($heroTitle)) !!}
                    </h1>

                    {{-- Sous-titre --}}
                    <p class="cl-hero-sub">
                        Élagage, abattage, taille de haies et broyage de souches —
                        <span style="color:rgba(255,255,255,.82);">Compiègne, Beauvais, Senlis, Chantilly, Creil, Noyon</span>
                        et toutes les communes de l'Oise.
                    </p>

                    {{-- CTA buttons --}}
                    <div class="cl-hero-ctas">
                        <a href="{{ route('form.step', 'propertyType') }}"
                           onclick="if(typeof trackFormClick==='function')trackFormClick('{{ request()->url() }}')"
                           class="cl-btn-primary">
                            <i class="fas fa-calculator"></i>
                            Devis gratuit
                            <i class="fas fa-arrow-right" style="font-size:.8rem;"></i>
                        </a>
                        <a href="tel:{{ $phoneRaw }}"
                           onclick="if(typeof trackPhoneCall==='function')trackPhoneCall('{{ $phoneRaw }}','hero')"
                           class="cl-btn-phone">
                            <span class="cl-phone-dot">
                                <span class="cl-phone-dot-ring"></span>
                                <i class="fas fa-phone" style="color:var(--primary-color);position:relative;font-size:.9rem;"></i>
                            </span>
                            {{ $phone }}
                        </a>
                    </div>

                    {{-- Social proof inline --}}
                    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:1.5rem;font-size:.82rem;color:rgba(255,255,255,.35);font-weight:600;">
                        <div style="display:flex;align-items:center;gap:.5rem;">
                            <i class="fas fa-star" style="color:#fbbf24;font-size:.75rem;"></i>
                            <i class="fas fa-star" style="color:#fbbf24;font-size:.75rem;"></i>
                            <i class="fas fa-star" style="color:#fbbf24;font-size:.75rem;"></i>
                            <i class="fas fa-star" style="color:#fbbf24;font-size:.75rem;"></i>
                            <i class="fas fa-star" style="color:#fbbf24;font-size:.75rem;"></i>
                            <span style="color:rgba(255,255,255,.72);font-weight:800;">4.9</span>
                            <span>&middot; 120+ avis Google</span>
                        </div>
                        <span style="width:1px;height:14px;background:rgba(255,255,255,.1);display:inline-block;"></span>
                        <span><span style="color:rgba(255,255,255,.72);font-weight:800;">15+</span> ans d'expérience</span>
                        <span style="width:1px;height:14px;background:rgba(255,255,255,.1);display:inline-block;"></span>
                        <span><span style="color:rgba(255,255,255,.72);font-weight:800;">60+</span> communes Oise</span>
                    </div>

                    {{-- 4 feature boxes --}}
                    <div class="cl-features">
                        <div class="cl-feature-item">
                            <div class="cl-feature-title">Écologique</div>
                            <div class="cl-feature-desc">Broyage sur place, valorisation des déchets verts, zéro gaspillage.</div>
                        </div>
                        <div class="cl-feature-item">
                            <div class="cl-feature-title">Rapide</div>
                            <div class="cl-feature-desc">Réponse sous 24h, intervention planifiée selon vos disponibilités.</div>
                        </div>
                        <div class="cl-feature-item">
                            <div class="cl-feature-title">Sécurisé</div>
                            <div class="cl-feature-desc">Artisan assuré RC Pro, équipements certifiés, normes respectées.</div>
                        </div>
                        <div class="cl-feature-item">
                            <div class="cl-feature-title">Sur-mesure</div>
                            <div class="cl-feature-desc">Devis gratuit, adapté à chaque arbre et chaque situation.</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Floating review card --}}
    @php
        $heroReview = null;
        if(!empty($reviews) && count($reviews) > 0) {
            $heroReview = $reviews->first();
        }
    @endphp
    <div class="cl-review-card">
        <div class="cl-review-stars">
            @for($i=1;$i<=5;$i++)
                <i class="fas fa-star cl-review-star"></i>
            @endfor
        </div>
        <p class="cl-review-text">
            &ldquo;{{ $heroReview ? \Illuminate\Support\Str::limit($heroReview->review_text ?? 'Travail impeccable, professionnel et soigneux. Je recommande vivement !', 110) : 'Travail impeccable, professionnel et soigneux. Je recommande vivement !' }}&rdquo;
        </p>
        <div class="cl-review-author">
            <div class="cl-review-avatar">
                @if($heroReview && $heroReview->author_photo_url)
                    <img src="{{ $heroReview->author_photo_url }}"
                         alt="{{ $heroReview->author_name ?? 'Client' }}"
                         class="cl-review-avatar-img">
                @else
                    <div class="cl-review-avatar-initials">
                        {{ strtoupper(substr($heroReview->author_name ?? 'C', 0, 1)) }}
                    </div>
                @endif
            </div>
            <div>
                <div class="cl-review-name">{{ $heroReview->author_name ?? 'Client satisfait' }}</div>
                <div class="cl-review-date">
                    @if($heroReview && $heroReview->review_date)
                        {{ \Carbon\Carbon::parse($heroReview->review_date)->translatedFormat('F Y') }}
                    @else
                        Avis vérifié Google
                    @endif
                </div>
            </div>
            <div style="margin-left:auto;">
                <i class="fab fa-google" style="color:#60a5fa;font-size:.9rem;opacity:.7;"></i>
            </div>
        </div>
    </div>

    {{-- Scroll indicator --}}
    <div style="position:absolute;bottom:2rem;left:50%;transform:translateX(-50%);display:flex;flex-direction:column;align-items:center;gap:.5rem;opacity:.25;z-index:10;pointer-events:none;">
        <span style="color:#fff;font-size:.62rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;">Scroll</span>
        <div style="width:1px;height:2rem;background:rgba(255,255,255,.35);"></div>
    </div>

</section>

{{-- ══════════════════════════════════════════════════════════
     §2  MARQUEE — Ticker confiance
     ══════════════════════════════════════════════════════════ --}}
<div class="cl-marquee-strip">
    <div class="cl-marquee-track">
        @php
        $mItems = [
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
        ];
        @endphp
        @foreach($mItems as $mi)
        <div class="cl-marquee-item">
            <i class="{{ $mi[0] }}" style="color:var(--primary-color);font-size:.75rem;"></i>
            <span style="color:rgba(255,255,255,.42);font-size:.82rem;font-weight:600;white-space:nowrap;">{{ $mi[1] }}</span>
        </div>
        <span class="cl-marquee-dot">&middot;</span>
        @endforeach
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     §3  SERVICES — Dark grid
     ══════════════════════════════════════════════════════════ --}}
@if(($homeConfig['sections']['services']['enabled'] ?? true) && count($svcList) > 0)
<section class="cl-services">
    <div class="cl-shell">

        <div class="cl-svc-header">
            <div>
                <span class="cl-label"><i class="fas fa-leaf"></i> Nos prestations</span>
                <h2 class="cl-h2">
                    {{ $homeConfig['sections']['services']['title'] ?? 'Services d\'élagage' }}<br>
                    <span class="dim">dans l'Oise (60)</span>
                </h2>
            </div>
            <a href="{{ route('services.index') }}" class="cl-btn-ghost">
                Tous les services <i class="fas fa-arrow-right" style="font-size:.7rem;"></i>
            </a>
        </div>

        @php $svcLimit = min(count($svcList), $homeConfig['sections']['services']['limit'] ?? 6); @endphp
        <div class="cl-svc-grid">
            @foreach($svcList as $si => $svc)
            @if($si >= $svcLimit) @break @endif
            <a href="{{ route('services.show', $svc['slug']) }}"
               class="cl-svc-card"
               onclick="if(typeof trackServiceClick==='function')trackServiceClick('{{ addslashes($svc['name']) }}','{{ request()->url() }}')">

                @if(!empty($svc['featured_image']))
                    <img class="cl-svc-card-img"
                         src="{{ url($svc['featured_image']) }}"
                         alt="{{ $svc['name'] }}"
                         loading="lazy">
                @else
                    <div class="cl-svc-card-fallback"></div>
                    <i class="{{ $svc['icon'] ?? 'fas fa-tree' }}"
                       style="position:absolute;font-size:6rem;opacity:.06;color:#fff;top:50%;right:1rem;transform:translateY(-50%);pointer-events:none;z-index:1;"></i>
                @endif

                <div class="cl-svc-card-overlay"></div>

                <div class="cl-svc-card-body">
                    <div style="width:2.25rem;height:2.25rem;border-radius:10px;background:rgba(108,163,34,.22);border:1px solid rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;margin-bottom:.75rem;flex-shrink:0;">
                        <i class="{{ $svc['icon'] ?? 'fas fa-tree' }}" style="color:#fff;font-size:.8rem;"></i>
                    </div>
                    <h3 class="cl-svc-card-title">{{ $svc['name'] }}</h3>
                    @if(!empty($svc['short_description']))
                    <p class="cl-svc-card-desc">
                        {{ \Illuminate\Support\Str::limit($svc['short_description'], 90) }}
                    </p>
                    @endif
                </div>
            </a>
            @endforeach
        </div>

    </div>
</section>
@endif

{{-- ══════════════════════════════════════════════════════════
     §4  À PROPOS — Dark green section
     ══════════════════════════════════════════════════════════ --}}
@if($homeConfig['sections']['about']['enabled'] ?? true)
<section class="cl-about">
    <div class="cl-about-halo"></div>
    <div class="cl-shell">
        <div class="cl-about-grid">

            {{-- Photo + stats --}}
            <div class="cl-about-photo-col">
                @php $aboutPhoto = $homeConfig['about']['image'] ?? null; @endphp
                <div class="cl-about-photo">
                    @if($aboutPhoto)
                        <img src="{{ asset(ltrim($aboutPhoto, '/')) }}"
                             alt="{{ $homeConfig['sections']['about']['title'] ?? 'À propos' }}"
                             loading="lazy">
                    @else
                        <div class="cl-about-photo-fallback">
                            <i class="fas fa-tree" style="font-size:7rem;color:rgba(255,255,255,.06);"></i>
                        </div>
                    @endif
                    @if(setting('company_logo'))
                    <div style="position:absolute;bottom:1rem;right:1rem;width:3.5rem;height:3.5rem;border-radius:12px;overflow:hidden;border:2px solid rgba(255,255,255,.15);background:#fff;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 20px rgba(0,0,0,.4);">
                        <img src="{{ asset(setting('company_logo')) }}"
                             alt="{{ setting('company_name') }}"
                             loading="lazy"
                             style="width:2.25rem;height:auto;max-height:2.25rem;object-fit:contain;">
                    </div>
                    @endif
                </div>

                {{-- Stats --}}
                <div class="cl-about-stats">
                    @foreach([
                        ['500+','Chantiers','#6ca322'],
                        ['15+','Ans exp.','#60a5fa'],
                        ['60+','Communes','#a78bfa'],
                        ['4.9★','Google','#fbbf24'],
                    ] as [$sv, $sl, $sc])
                    <div class="cl-about-stat">
                        <div class="cl-about-stat-num" style="color:{{ $sc }};">{{ $sv }}</div>
                        <div class="cl-about-stat-lbl">{{ $sl }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Texte --}}
            <div class="cl-about-text-col">
                <span class="cl-label"><i class="fas fa-leaf"></i> À propos</span>
                <h2 class="cl-h2" style="margin-bottom:1.25rem;">
                    {{ $homeConfig['sections']['about']['title'] ?? 'Votre élagueur de confiance dans l\'Oise' }}
                </h2>
                <p class="cl-about-body">
                    {{ $homeConfig['sections']['about']['text'] ?? 'Louis Hoffmann est élagueur professionnel certifié, intervenant dans tout le département 60 (Oise) : Compiègne, Beauvais, Senlis, Chantilly, Creil, Noyon et toutes les communes environnantes. Avec plus de 15 ans d\'expérience, nous garantissons des interventions sécurisées, soignées et respectueuses de l\'environnement.' }}
                </p>

                <ul class="cl-checklist">
                    <li>Artisan certifié, assuré en responsabilité civile professionnelle</li>
                    <li>Équipements professionnels, respect des normes de sécurité</li>
                    <li>Valorisation des déchets verts — broyage sur place possible</li>
                    <li>Devis gratuit, transparent, sans engagement</li>
                    <li>Réponse sous 24h pour toute intervention dans le 60</li>
                </ul>

                <div class="cl-about-ctas">
                    <a href="{{ route('contact') }}" class="cl-btn-green">
                        <i class="fas fa-envelope" style="font-size:.85rem;"></i> Nous contacter
                    </a>
                    <a href="{{ route('form.step', 'propertyType') }}" class="cl-btn-outline">
                        <i class="fas fa-calculator" style="font-size:.85rem;"></i> Devis gratuit
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>
@endif

{{-- ══════════════════════════════════════════════════════════
     §5  PROCESSUS — 4 étapes numérotées
     ══════════════════════════════════════════════════════════ --}}
<section class="cl-process">
    <div class="cl-shell">

        <div class="cl-process-header">
            <span class="cl-label"><i class="fas fa-route"></i> Comment ça marche</span>
            <h2 class="cl-h2">Simple, rapide, professionnel</h2>
        </div>

        <div class="cl-process-grid">
            <div class="cl-process-line"></div>

            @foreach([
                [1,'fas fa-phone-alt','Prise de contact','Appelez ou remplissez le formulaire. Réponse garantie sous 24h dans tout le département 60.','rgba(108,163,34,.15)','var(--primary-color)'],
                [2,'fas fa-search-location','Visite & devis','Déplacement gratuit sur site pour évaluer et chiffrer précisément votre projet.','rgba(96,165,250,.12)','#60a5fa'],
                [3,'fas fa-calendar-check','Planification','Date calée selon vos disponibilités en respectant les réglementations locales.','rgba(167,139,250,.12)','#a78bfa'],
                [4,'fas fa-hard-hat','Travaux & nettoyage','Intervention sécurisée, chantier intégralement nettoyé, déchets valorisés.','rgba(251,191,36,.12)','#fbbf24'],
            ] as [$pn, $picon, $ptitle, $ptext, $pbg, $pc])
            <div class="cl-process-step">
                <div class="cl-process-num">{{ str_pad($pn, 2, '0', STR_PAD_LEFT) }}</div>
                <div class="cl-process-icon" style="background:{{ $pbg }};">
                    <i class="{{ $picon }}" style="color:{{ $pc }};"></i>
                </div>
                <h3 class="cl-process-step-title">{{ $ptitle }}</h3>
                <p class="cl-process-step-desc">{{ $ptext }}</p>
            </div>
            @endforeach
        </div>

        <div class="cl-process-cta">
            <a href="{{ route('form.step', 'propertyType') }}" class="cl-btn-green">
                <i class="fas fa-calculator"></i>
                Démarrer mon devis gratuit
            </a>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════
     §6  ZONES D'INTERVENTION
     ══════════════════════════════════════════════════════════ --}}
<section class="cl-zones">
    <div class="cl-shell">
        <div class="cl-zones-inner">

            {{-- Gauche --}}
            <div class="cl-zones-left">
                <span class="cl-label"><i class="fas fa-map-marker-alt"></i> Zone d'intervention</span>
                <h2 class="cl-zones-title">
                    Tout le département<br>
                    <span style="color:var(--primary-color);">60 — Oise</span>
                </h2>
                <p class="cl-zones-desc">
                    Nous intervenons dans l'ensemble des communes de l'Oise et des départements limitrophes — Aisne, Somme, Val-d'Oise.
                </p>
                <a href="{{ route('contact') }}" class="cl-btn-ghost">
                    Vérifier ma commune <i class="fas fa-arrow-right" style="font-size:.7rem;"></i>
                </a>
            </div>

            {{-- Droite : pills --}}
            <div class="cl-zones-right">
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
                <div class="cl-zones-pills">
                    @if($displayCities->count() > 0)
                        @foreach($displayCities as $dcity)
                        <a href="{{ route('ads.index') }}?city={{ $dcity->slug }}" class="cl-pill">
                            <i class="fas fa-map-pin" style="color:var(--primary-color);font-size:.62rem;"></i>
                            {{ $dcity->name }}
                            @if($dcity->postal_code)
                            <span class="cl-pill-postal">{{ $dcity->postal_code }}</span>
                            @endif
                        </a>
                        @endforeach
                    @else
                        @foreach($oiseFallback as [$zname, $zpostal])
                        <span class="cl-pill">
                            <i class="fas fa-map-pin" style="color:var(--primary-color);font-size:.62rem;"></i>
                            {{ $zname }}
                            <span class="cl-pill-postal">{{ $zpostal }}</span>
                        </span>
                        @endforeach
                        <span class="cl-pill" style="border-style:dashed;opacity:.55;">
                            + 40 autres communes…
                        </span>
                    @endif
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════
     §7  TÉMOIGNAGES — 1 large + petites cartes
     ══════════════════════════════════════════════════════════ --}}
@if(($homeConfig['sections']['reviews']['enabled'] ?? true) && !empty($reviews) && count($reviews) > 0)
<section class="cl-reviews">
    <div class="cl-shell">

        <div class="cl-reviews-header">
            <div>
                <span class="cl-label"><i class="fas fa-quote-left"></i> Témoignages</span>
                <h2 class="cl-h2">
                    {{ $homeConfig['sections']['reviews']['title'] ?? 'Ce que disent nos clients' }}
                </h2>
                @if(isset($averageRating) && $averageRating > 0)
                <div class="cl-reviews-rating">
                    <div style="display:flex;gap:3px;">
                        @for($ri = 1; $ri <= 5; $ri++)
                        <i class="fas fa-star" style="font-size:.875rem;color:{{ $ri <= round($averageRating) ? '#fbbf24' : 'rgba(255,255,255,.15)' }};"></i>
                        @endfor
                    </div>
                    <span style="font-weight:900;color:#fff;font-size:1.2rem;">{{ number_format($averageRating, 1) }}/5</span>
                    @if(isset($totalReviews))
                    <span style="color:rgba(255,255,255,.32);font-size:.875rem;">({{ $totalReviews }} avis)</span>
                    @endif
                </div>
                @endif
            </div>
            <a href="{{ route('reviews.all') }}" class="cl-btn-ghost">
                Tous les avis <i class="fas fa-star" style="font-size:.7rem;"></i>
            </a>
        </div>

        <div class="cl-reviews-grid">
            @foreach($reviews->take(min(5, $homeConfig['sections']['reviews']['limit'] ?? 5)) as $rvi => $review)
            @if($rvi === 0)
            <div class="cl-review-featured-card">
                <div class="cl-review-featured-quote">&ldquo;</div>
                <div>
                    <div style="display:flex;gap:3px;margin-bottom:1.25rem;">
                        @for($ri2=1;$ri2<=5;$ri2++)
                        <i class="fas fa-star" style="font-size:.875rem;color:{{ $ri2<=$review->rating ? '#fbbf24' : 'rgba(255,255,255,.15)' }};"></i>
                        @endfor
                    </div>
                    <p class="cl-review-featured-text">
                        &ldquo;{{ \Illuminate\Support\Str::limit($review->review_text ?? 'Excellent travail, très professionnel.', 220) }}&rdquo;
                    </p>
                </div>
                <div class="cl-review-featured-foot">
                    <div class="cl-review-avatar-large">
                        @if($review->author_photo_url)
                            <img src="{{ $review->author_photo_url }}" alt="{{ $review->author_name }}" loading="lazy" class="cl-review-avatar-img">
                        @else
                            <div class="cl-review-avatar-init">{{ strtoupper(substr($review->author_name ?? 'C', 0, 1)) }}</div>
                        @endif
                    </div>
                    <div>
                        <div class="cl-review-author-name">{{ $review->author_name }}</div>
                        <div class="cl-review-author-date">{{ $review->review_date ? \Carbon\Carbon::parse($review->review_date)->translatedFormat('F Y') : '' }}</div>
                    </div>
                    @if($review->source && str_contains($review->source, 'Google'))
                    <div class="cl-review-source-badge">
                        <i class="fab fa-google" style="color:#60a5fa;font-size:.75rem;"></i>
                        Google
                    </div>
                    @endif
                </div>
            </div>
            @else
            <div class="cl-review-small-card">
                <div style="display:flex;gap:3px;margin-bottom:1rem;">
                    @for($ri3=1;$ri3<=5;$ri3++)
                    <i class="fas fa-star" style="font-size:.75rem;color:{{ $ri3<=$review->rating ? '#fbbf24' : 'rgba(255,255,255,.12)' }};"></i>
                    @endfor
                </div>
                <p class="cl-review-small-text">
                    &ldquo;{{ \Illuminate\Support\Str::limit($review->review_text ?? 'Excellent travail.', 160) }}&rdquo;
                </p>
                <div class="cl-review-foot">
                    <div class="cl-review-avatar-small">
                        @if($review->author_photo_url)
                            <img src="{{ $review->author_photo_url }}" alt="{{ $review->author_name }}" loading="lazy" class="cl-review-avatar-img">
                        @else
                            <div class="cl-review-avatar-init" style="font-size:.8rem;">{{ strtoupper(substr($review->author_name ?? 'C', 0, 1)) }}</div>
                        @endif
                    </div>
                    <div>
                        <div class="cl-review-author-name">{{ $review->author_name }}</div>
                        <div class="cl-review-author-date">{{ $review->review_date ? \Carbon\Carbon::parse($review->review_date)->translatedFormat('F Y') : '' }}</div>
                    </div>
                </div>
            </div>
            @endif
            @endforeach
        </div>

    </div>
</section>
@endif

{{-- ══════════════════════════════════════════════════════════
     §8  FAQ — Sticky gauche + accordéon
     ══════════════════════════════════════════════════════════ --}}
<section class="cl-faq">
    <div class="cl-shell">
        <div class="cl-faq-layout">

            {{-- Gauche sticky --}}
            <div class="cl-faq-left">
                <span class="cl-label"><i class="fas fa-question-circle"></i> FAQ</span>
                <h2 class="cl-faq-left-title">Vos questions,<br>nos réponses</h2>
                <p class="cl-faq-left-sub">
                    Tout ce que vous devez savoir avant de nous confier votre projet d'élagage dans l'Oise.
                </p>
                <a href="{{ route('contact') }}" class="cl-btn-green" style="font-size:.85rem;">
                    <i class="fas fa-envelope" style="font-size:.8rem;"></i> Poser une question
                </a>
            </div>

            {{-- Accordéon --}}
            <div>
                @foreach([
                    ['Intervenez-vous dans toute l\'Oise ?','Oui, nous couvrons l\'ensemble du département 60 : Compiègne, Beauvais, Senlis, Chantilly, Creil, Noyon, Verberie, Clermont, Méru, Liancourt, Pont-Sainte-Maxence et toutes les communes alentour. Nous intervenons également dans les départements limitrophes (Aisne, Somme, Val-d\'Oise).'],
                    ['Combien coûte un élagage dans l\'Oise ?','Le tarif dépend du type d\'arbre, de sa hauteur, de son accessibilité et des travaux à effectuer. C\'est pourquoi nous proposons un devis gratuit et sans engagement après visite sur place. Comptez en général entre 150 € et 800 € pour un élagage standard.'],
                    ['Avez-vous les certifications nécessaires ?','Absolument. Louis Hoffmann est artisan certifié, assuré en responsabilité civile professionnelle. Nous respectons toutes les normes de sécurité et les réglementations en vigueur dans le département 60.'],
                    ['Que faites-vous des déchets verts ?','Nous broyons les branches sur place si vous le souhaitez (le broyat peut servir de paillis). Les rémanents peuvent aussi être évacués et valorisés en composterie ou énergie verte.'],
                    ['En quelle saison effectuer l\'élagage ?','L\'élagage se pratique idéalement en fin d\'hiver (février-mars) ou en été (juillet-août). Certains arbres ont leurs propres rythmes. L\'abattage peut se faire toute l\'année. Nous vous conseillons lors du devis.'],
                    ['Puis-je obtenir un devis rapidement ?','Oui. Remplissez notre formulaire en ligne ou appelez-nous directement. Nous vous répondons sous 24h et nous déplaçons gratuitement pour établir un devis détaillé.'],
                ] as $faq)
                <div class="cl-faq-item">
                    <details>
                        <summary>
                            <span>{{ $faq[0] }}</span>
                            <div class="cl-faq-icon"><i class="fas fa-plus"></i></div>
                        </summary>
                        <div class="cl-faq-body">{{ $faq[1] }}</div>
                    </details>
                </div>
                @endforeach
            </div>

        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════
     §9  CTA FINAL — Green gradient
     ══════════════════════════════════════════════════════════ --}}
@if($homeConfig['sections']['cta']['enabled'] ?? true)
<div style="height:3px;background:linear-gradient(90deg,var(--primary-color),rgba(108,163,34,.5),var(--primary-color));"></div>

<section class="cl-cta-final">
    <div class="cl-cta-dots"></div>
    <div class="cl-cta-glow"></div>

    <div class="cl-shell">
        <div class="cl-cta-inner">

            <div class="cl-cta-badge">
                <span class="cl-cta-badge-dot"></span>
                Département 60 — Oise &middot; Picardie
            </div>

            <h2 class="cl-cta-h2">
                {!! nl2br(e($homeConfig['sections']['cta']['title'] ?? "Besoin d'un élagueur\ndans l'Oise ?")) !!}
            </h2>

            <p class="cl-cta-sub">
                Compiègne, Beauvais, Senlis, Chantilly, Creil, Noyon —
                devis gratuit, réponse sous 24h, artisan certifié et assuré.
            </p>

            <div class="cl-cta-btns">
                <a href="{{ route('form.step', 'propertyType') }}"
                   onclick="if(typeof trackFormClick==='function')trackFormClick('{{ request()->url() }}')"
                   class="cl-btn-cta-white">
                    <i class="fas fa-calculator"></i>
                    Devis gratuit en ligne
                </a>
                <a href="tel:{{ $phoneRaw }}"
                   onclick="if(typeof trackPhoneCall==='function')trackPhoneCall('{{ $phoneRaw }}','cta-final')"
                   class="cl-btn-cta-outline">
                    <span class="cl-phone-dot">
                        <span class="cl-phone-dot-ring"></span>
                        <i class="fas fa-phone" style="color:var(--primary-color);position:relative;"></i>
                    </span>
                    {{ $phone }}
                </a>
            </div>

            <div class="cl-trust-pills">
                @foreach(['Devis 100% gratuit','Sans engagement','Réponse sous 24h','Artisan certifié','Tout le dép. 60'] as $tpill)
                <div class="cl-trust-pill">
                    <i class="fas fa-check" style="color:var(--primary-color);font-size:.65rem;"></i>
                    {{ $tpill }}
                </div>
                @endforeach
            </div>

        </div>
    </div>
</section>
@endif

{{-- ══════════════════════════════════════════════════════════
     Sections partials complémentaires
     ══════════════════════════════════════════════════════════ --}}
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
