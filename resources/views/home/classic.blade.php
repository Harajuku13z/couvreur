{{-- ============================================================
     LANDING PAGE v3 — Louis Hoffmann Élagage · Département 60
     Design : magazine éditorial, blanc dominant, typographie bold
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
/* ─── Reset & Base ─────────────────────────────────────── */
.lp-v3 *, .lp-v3 *::before, .lp-v3 *::after { box-sizing: border-box; }
.lp-v3 { font-family: inherit; color: #0f172a; }

/* ─── Shell ────────────────────────────────────────────── */
.lp-shell {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 1.25rem;
}
@media (min-width: 768px) { .lp-shell { padding: 0 2rem; } }
@media (min-width: 1280px) { .lp-shell { padding: 0 3rem; } }

/* ─── Section label ────────────────────────────────────── */
.lp-label {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    font-size: .72rem;
    font-weight: 800;
    letter-spacing: .18em;
    text-transform: uppercase;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

/* ─── Hero ─────────────────────────────────────────────── */
.hero-v3 {
    position: relative;
    min-height: 100svh;
    background: #0f1f13;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.hero-v3-bg {
    position: absolute;
    inset: 0;
    z-index: 0;
}
.hero-v3-bg img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center 30%;
    display: block;
}
.hero-v3-bg-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        155deg,
        rgba(10, 20, 12, .92) 0%,
        rgba(10, 20, 12, .72) 45%,
        rgba(10, 20, 12, .55) 100%
    );
}
.hero-v3-inner {
    position: relative;
    z-index: 10;
    padding-top: 7rem;
    padding-bottom: 5rem;
}
.hero-v3-grid {
    display: grid;
    gap: 3rem;
    align-items: center;
}
@media (min-width: 1024px) {
    .hero-v3-grid {
        grid-template-columns: 1fr 400px;
        gap: 4rem;
    }
}

/* ─── Hero animated dot tag ────────────────────────────── */
.hero-tag {
    display: inline-flex;
    align-items: center;
    gap: .75rem;
    margin-bottom: 2rem;
}
.hero-tag-dots {
    display: flex;
    align-items: center;
    gap: .3rem;
}
.hero-tag-dot-1 {
    width: 8px; height: 8px; border-radius: 50%;
    background: #4ade80;
    animation: pulse-dot 2s ease-in-out infinite;
}
.hero-tag-dot-2 {
    width: 6px; height: 6px; border-radius: 50%;
    background: #16a34a;
}
.hero-tag-dot-3 {
    width: 4px; height: 4px; border-radius: 50%;
    background: #14532d;
}
@keyframes pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: .6; transform: scale(.8); }
}

/* ─── Hero H1 ───────────────────────────────────────────── */
.hero-h1 {
    font-size: clamp(2.8rem, 6.5vw, 5.5rem);
    font-weight: 900;
    line-height: .95;
    letter-spacing: -.02em;
    color: #fff;
    margin: 0 0 1.75rem;
}

/* ─── Marquee ───────────────────────────────────────────── */
@keyframes lp-marquee {
    from { transform: translateX(0); }
    to   { transform: translateX(-50%); }
}
.marquee-v3-track {
    display: flex;
    width: max-content;
    animation: lp-marquee 26s linear infinite;
}
.marquee-v3-track:hover { animation-play-state: paused; }

/* ─── Services grid ─────────────────────────────────────── */
.svc-grid {
    display: grid;
    gap: 1.25rem;
    grid-template-columns: repeat(3, 1fr);
}
@media (max-width: 1023px) {
    .svc-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 639px) {
    .svc-grid { grid-template-columns: 1fr; }
}

/* ─── Service card ──────────────────────────────────────── */
.svc-card-v3 {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    display: block;
    height: 280px;
    text-decoration: none;
    transition: transform .3s ease, box-shadow .3s ease;
}
.svc-card-v3:hover {
    transform: translateY(-6px);
    box-shadow: 0 24px 60px rgba(0, 0, 0, .18);
}
@media (max-width: 639px) {
    .svc-card-v3 { height: 240px; }
}
.svc-card-v3-img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform .6s ease;
}
.svc-card-v3:hover .svc-card-v3-img {
    transform: scale(1.06);
}
.svc-card-v3-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        to top,
        rgba(6, 21, 13, .96) 0%,
        rgba(6, 21, 13, .55) 50%,
        rgba(6, 21, 13, .15) 100%
    );
}
.svc-card-v3-content {
    position: absolute;
    inset: 0;
    padding: 1.75rem;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}

/* ─── About section ─────────────────────────────────────── */
.about-grid {
    display: grid;
    gap: 3.5rem;
    align-items: stretch;
}
@media (min-width: 1024px) {
    .about-grid { grid-template-columns: 1fr 1fr; }
}
.about-photo-wrap {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    min-height: 360px;
    flex: 1;
}
.about-photo-wrap img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
}
.about-col-left {
    display: flex;
    flex-direction: column;
}
.about-stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: .75rem;
    margin-top: 1.25rem;
    flex-shrink: 0;
}

/* ─── Checklist ─────────────────────────────────────────── */
.lp-checklist { list-style: none; margin: 0; padding: 0; }
.lp-checklist li {
    display: flex;
    align-items: flex-start;
    gap: .75rem;
    padding: .45rem 0;
    font-size: .9rem;
    color: rgba(255,255,255,.65);
}
.lp-checklist li::before {
    content: '';
    display: inline-flex;
    flex-shrink: 0;
    width: 1.2rem;
    height: 1.2rem;
    margin-top: .1rem;
    border-radius: .35rem;
    background-color: var(--primary-color);
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 12 9' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 4l3.5 3.5L11 1' stroke='white' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-size: 62% 62%;
    background-repeat: no-repeat;
    background-position: center;
}

/* ─── Process steps ─────────────────────────────────────── */
.process-grid {
    display: grid;
    gap: 2rem;
    position: relative;
}
@media (min-width: 768px)  { .process-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .process-grid { grid-template-columns: repeat(4, 1fr); gap: 0; } }

.process-line {
    display: none;
}
@media (min-width: 1024px) {
    .process-line {
        display: block;
        position: absolute;
        top: 3.2rem;
        left: 12%;
        right: 12%;
        height: 2px;
        background: linear-gradient(90deg, var(--primary-color), #4ade80, var(--primary-color));
        opacity: .35;
        z-index: 0;
    }
}

/* ─── Zones layout ──────────────────────────────────────── */
.zones-layout {
    display: flex;
    flex-direction: column;
    gap: 2.5rem;
    align-items: flex-start;
}
@media (min-width: 768px) {
    .zones-layout {
        flex-direction: row;
        align-items: flex-start;
        gap: 4rem;
    }
    .zones-left  { flex: 0 0 320px; }
    .zones-right { flex: 1; }
}

/* ─── Reviews grid ──────────────────────────────────────── */
.reviews-grid {
    display: grid;
    gap: 1.25rem;
    grid-template-columns: 1fr;
}
@media (min-width: 1024px) {
    .reviews-grid { grid-template-columns: repeat(3, 1fr); }
}

/* ─── FAQ layout ────────────────────────────────────────── */
.faq-layout {
    display: grid;
    gap: 3rem;
}
@media (min-width: 1024px) {
    .faq-layout { grid-template-columns: 340px 1fr; gap: 5rem; }
}

/* ─── FAQ details ───────────────────────────────────────── */
.faq-item details { border-bottom: 1px solid #e5e7eb; }
.faq-item details summary {
    list-style: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.25rem 0;
    font-weight: 700;
    font-size: .95rem;
    color: #0f172a;
    transition: color .2s;
}
.faq-item details summary::-webkit-details-marker { display: none; }
.faq-item details[open] summary { color: var(--primary-color); }
.faq-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    border: 2px solid var(--primary-color);
    color: var(--primary-color);
    flex-shrink: 0;
    transition: transform .25s ease;
    font-size: .7rem;
}
.faq-item details[open] .faq-icon { transform: rotate(45deg); }
.faq-body {
    padding: 0 0 1.25rem;
    font-size: .9rem;
    color: #64748b;
    line-height: 1.75;
}

/* ─── Hover btn utils ───────────────────────────────────── */
.btn-outline-green {
    display: inline-flex;
    align-items: center;
    gap: .6rem;
    font-weight: 700;
    font-size: .875rem;
    padding: .75rem 1.5rem;
    border-radius: 12px;
    border: 2px solid var(--primary-color);
    color: var(--primary-color);
    text-decoration: none;
    transition: background .2s, color .2s;
    background: transparent;
}
.btn-outline-green:hover {
    background: var(--primary-color);
    color: #fff;
}
.btn-solid-green {
    display: inline-flex;
    align-items: center;
    gap: .6rem;
    font-weight: 800;
    font-size: .9rem;
    padding: .85rem 1.75rem;
    border-radius: 12px;
    background: var(--primary-color);
    color: #fff;
    text-decoration: none;
    transition: opacity .2s, transform .2s;
}
.btn-solid-green:hover { opacity: .9; transform: translateY(-1px); }

/* ─── Reveal animation ──────────────────────────────────── */
@keyframes lp-fadeup {
    from { opacity: 0; transform: translateY(24px); }
    to   { opacity: 1; transform: translateY(0); }
}
.lp-reveal   { animation: lp-fadeup .6s ease both; }
.lp-reveal-2 { animation-delay: .1s; }
.lp-reveal-3 { animation-delay: .2s; }
.lp-reveal-4 { animation-delay: .32s; }
</style>

<div class="lp-v3">

{{-- ══════════════════════════════════════════════════════════
     §1  HERO — Full-screen éditorial
     ══════════════════════════════════════════════════════════ --}}
<section class="hero-v3">

    {{-- Fond photo --}}
    <div class="hero-v3-bg">
        @if($heroImgUrl)
            <img src="{{ $heroImgUrl }}" alt="Élagage dans l'Oise — Louis Hoffmann">
        @else
            <div style="position:absolute;inset:0;background:linear-gradient(160deg,#071c0e 0%,#0d3b22 60%,#1a5c35 100%);"></div>
        @endif
        <div class="hero-v3-bg-overlay"></div>
    </div>

    {{-- Halo lumière gauche --}}
    <div style="position:absolute;inset:0;z-index:1;pointer-events:none;overflow:hidden;">
        <div style="position:absolute;top:-200px;left:-200px;width:700px;height:700px;border-radius:50%;background:radial-gradient(circle, rgba(74,222,128,.12) 0%, transparent 65%);"></div>
    </div>

    <div class="hero-v3-inner lp-shell">
        <div class="hero-v3-grid">

            {{-- ── Texte principal ────────────────────── --}}
            <div>

                {{-- Tag ligne --}}
                <div class="hero-tag lp-reveal">
                    <div class="hero-tag-dots">
                        <span class="hero-tag-dot-1"></span>
                        <span class="hero-tag-dot-2"></span>
                        <span class="hero-tag-dot-3"></span>
                    </div>
                    <span style="font-size:.72rem;font-weight:800;letter-spacing:.18em;text-transform:uppercase;color:#4ade80;">
                        Élagueur certifié &middot; Dép. 60 &middot; Oise
                    </span>
                </div>

                {{-- H1 --}}
                <h1 class="hero-h1 lp-reveal lp-reveal-2">
                    {{ $heroTitle }}
                </h1>

                {{-- Sous-titre --}}
                <p class="lp-reveal lp-reveal-3" style="font-size:clamp(1rem,2vw,1.2rem);color:rgba(255,255,255,.55);line-height:1.7;max-width:540px;margin:0 0 2.5rem;">
                    Élagage, abattage, taille de haies et broyage de souches —
                    <span style="color:rgba(255,255,255,.8);">Compiègne, Beauvais, Senlis, Chantilly, Creil, Noyon</span>
                    et toutes les communes de l'Oise.
                </p>

                {{-- CTA buttons --}}
                <div class="lp-reveal lp-reveal-4" style="display:flex;flex-wrap:wrap;gap:1rem;margin-bottom:3rem;">
                    <a href="{{ route('form.step', 'propertyType') }}"
                       onclick="if(typeof trackFormClick==='function')trackFormClick('{{ request()->url() }}')"
                       style="display:inline-flex;align-items:center;gap:.75rem;background:var(--primary-color);color:#fff;font-size:1.05rem;font-weight:900;padding:1.1rem 2.25rem;border-radius:16px;text-decoration:none;transition:transform .2s,box-shadow .2s;box-shadow:0 8px 30px rgba(var(--primary-color-rgb,34,197,94),.4);"
                       onmouseover="this.style.transform='scale(1.03)'"
                       onmouseout="this.style.transform='scale(1)'">
                        <i class="fas fa-calculator"></i>
                        Devis gratuit
                        <i class="fas fa-arrow-right" style="font-size:.8rem;"></i>
                    </a>
                    <a href="tel:{{ $phoneRaw }}"
                       onclick="if(typeof trackPhoneCall==='function')trackPhoneCall('{{ $phoneRaw }}','hero')"
                       style="display:inline-flex;align-items:center;gap:.75rem;border:1.5px solid rgba(255,255,255,.2);color:#fff;font-size:1.05rem;font-weight:700;padding:1.1rem 2.25rem;border-radius:16px;text-decoration:none;transition:background .2s,border-color .2s;"
                       onmouseover="this.style.background='rgba(255,255,255,.07)';this.style.borderColor='rgba(255,255,255,.35)';"
                       onmouseout="this.style.background='transparent';this.style.borderColor='rgba(255,255,255,.2)';">
                        <span style="position:relative;display:inline-flex;">
                            <span style="position:absolute;inset:0;border-radius:50%;background:#4ade80;opacity:.2;animation:pulse-dot 2s infinite;"></span>
                            <i class="fas fa-phone" style="color:#4ade80;position:relative;"></i>
                        </span>
                        {{ $phone }}
                    </a>
                </div>

                {{-- Social proof bar --}}
                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:1.5rem;font-size:.85rem;color:rgba(255,255,255,.38);font-weight:600;">
                    <div style="display:flex;align-items:center;gap:.5rem;">
                        <div style="display:flex;gap:2px;">
                            <i class="fas fa-star" style="color:#fbbf24;font-size:.75rem;"></i>
                            <i class="fas fa-star" style="color:#fbbf24;font-size:.75rem;"></i>
                            <i class="fas fa-star" style="color:#fbbf24;font-size:.75rem;"></i>
                            <i class="fas fa-star" style="color:#fbbf24;font-size:.75rem;"></i>
                            <i class="fas fa-star" style="color:#fbbf24;font-size:.75rem;"></i>
                        </div>
                        <span style="color:rgba(255,255,255,.75);font-weight:800;">4.9</span>
                        <span>&middot; 120+ avis Google</span>
                    </div>
                    <div style="width:1px;height:16px;background:rgba(255,255,255,.12);"></div>
                    <span><span style="color:rgba(255,255,255,.75);font-weight:800;">15+</span> ans d'expérience</span>
                    <div style="width:1px;height:16px;background:rgba(255,255,255,.12);"></div>
                    <span><span style="color:rgba(255,255,255,.75);font-weight:800;">60+</span> communes Oise</span>
                </div>
            </div>

            {{-- ── Panel flottant droit (desktop) ─────── --}}
            <div style="display:none;" class="hero-right-panel">
                <div style="border-radius:24px;overflow:hidden;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);backdrop-filter:blur(16px);">
                    <div style="height:4px;background:linear-gradient(90deg,var(--primary-color),var(--secondary-color,#16a34a),#4ade80);"></div>
                    <div style="padding:1.75rem;">
                        <div style="font-size:.68rem;font-weight:800;letter-spacing:.15em;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:1.25rem;">
                            Zone d'intervention · Oise (60)
                        </div>
                        <div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1rem;">
                            @foreach(['Compiègne','Beauvais','Senlis','Chantilly','Creil','Noyon','Verberie','Pont-Ste-Maxence'] as $hcity)
                            <span style="display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .85rem;border-radius:50px;font-size:.75rem;font-weight:600;background:rgba(var(--primary-color-rgb,34,197,94),.12);color:rgba(255,255,255,.75);border:1px solid rgba(var(--primary-color-rgb,34,197,94),.22);">
                                <i class="fas fa-map-marker-alt" style="font-size:.6rem;color:var(--primary-color);"></i>
                                {{ $hcity }}
                            </span>
                            @endforeach
                        </div>
                        <div style="font-size:.75rem;color:rgba(255,255,255,.28);text-align:center;margin-bottom:1.25rem;">
                            + 60 autres communes du département
                        </div>
                        <a href="{{ route('form.step', 'propertyType') }}"
                           style="display:block;width:100%;text-align:center;background:var(--primary-color);color:#fff;font-weight:900;font-size:.875rem;padding:.9rem 1rem;border-radius:12px;text-decoration:none;transition:opacity .2s;"
                           onmouseover="this.style.opacity='.88'"
                           onmouseout="this.style.opacity='1'">
                            <i class="fas fa-calculator" style="margin-right:.5rem;"></i>Obtenir un devis gratuit
                        </a>
                    </div>
                </div>
                <div style="margin-top:.75rem;border-radius:16px;padding:1rem 1.25rem;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);display:flex;align-items:center;gap:.875rem;">
                    <div style="width:2.5rem;height:2.5rem;border-radius:10px;background:rgba(74,222,128,.18);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-check" style="color:#4ade80;font-size:.8rem;"></i>
                    </div>
                    <div>
                        <div style="color:rgba(255,255,255,.88);font-weight:700;font-size:.85rem;">Réponse garantie sous 24h</div>
                        <div style="color:rgba(255,255,255,.35);font-size:.75rem;">Devis gratuit · Sans engagement</div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Scroll indicator --}}
    <div style="position:absolute;bottom:2rem;left:50%;transform:translateX(-50%);display:flex;flex-direction:column;align-items:center;gap:.5rem;opacity:.3;z-index:10;">
        <span style="color:#fff;font-size:.65rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;">Scroll</span>
        <div style="width:1px;height:2.5rem;background:rgba(255,255,255,.4);"></div>
    </div>
</section>

<style>
@media (min-width: 1024px) {
    .hero-right-panel { display: block !important; }
}
</style>

{{-- ══════════════════════════════════════════════════════════
     §2  MARQUEE — Ticker de confiance
     ══════════════════════════════════════════════════════════ --}}
<div style="background:#0f1f13;border-top:1px solid rgba(255,255,255,.06);border-bottom:1px solid rgba(255,255,255,.06);padding:.9rem 0;overflow:hidden;">
    <div class="marquee-v3-track">
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
        <div style="display:flex;align-items:center;gap:.625rem;padding:0 2rem;flex-shrink:0;">
            <i class="{{ $mi[0] }}" style="color:var(--primary-color);font-size:.75rem;"></i>
            <span style="color:rgba(255,255,255,.48);font-size:.82rem;font-weight:600;white-space:nowrap;">{{ $mi[1] }}</span>
        </div>
        <span style="color:rgba(255,255,255,.12);flex-shrink:0;">&middot;</span>
        @endforeach
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     §3  SERVICES — Grille éditoriale magazine
     ══════════════════════════════════════════════════════════ --}}
@if(($homeConfig['sections']['services']['enabled'] ?? true) && count($svcList) > 0)
<section style="background:#fff;padding:6rem 0;">
    <div class="lp-shell">

        {{-- Header --}}
        <div style="display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:1.5rem;margin-bottom:3.5rem;">
            <div>
                <span class="lp-label"><i class="fas fa-leaf"></i> Nos prestations</span>
                <h2 style="font-size:clamp(1.9rem,4.5vw,3.2rem);font-weight:900;color:#0f172a;line-height:1.05;margin:0;">
                    {{ $homeConfig['sections']['services']['title'] ?? 'Services d\'élagage' }}<br>
                    <span style="color:#94a3b8;">dans l'Oise (60)</span>
                </h2>
            </div>
            <a href="{{ route('services.index') }}" class="btn-outline-green">
                Tous les services <i class="fas fa-arrow-right" style="font-size:.75rem;"></i>
            </a>
        </div>

        {{-- Grid --}}
        @php $svcLimit = min(count($svcList), $homeConfig['sections']['services']['limit'] ?? 6); @endphp
        <div class="svc-grid">
            @foreach($svcList as $si => $svc)
            @if($si >= $svcLimit) @break @endif
            <a href="{{ route('services.show', $svc['slug']) }}"
               class="svc-card-v3"
               onclick="if(typeof trackServiceClick==='function')trackServiceClick('{{ addslashes($svc['name']) }}','{{ request()->url() }}')">

                {{-- Image ou fallback dégradé --}}
                @if(!empty($svc['featured_image']))
                    <img class="svc-card-v3-img"
                         src="{{ url($svc['featured_image']) }}"
                         alt="{{ $svc['name'] }}"
                         loading="lazy">
                @else
                    <div style="position:absolute;inset:0;background:linear-gradient(135deg,#0d3b22 0%,#1a5c35 60%,#0d6e2e 100%);"></div>
                    <i class="{{ $svc['icon'] ?? 'fas fa-tree' }}"
                       style="position:absolute;font-size:6rem;opacity:.06;color:#fff;top:50%;right:1rem;transform:translateY(-50%);pointer-events:none;"></i>
                @endif

                <div class="svc-card-v3-overlay"></div>

                {{-- Hover tint --}}
                <div style="position:absolute;inset:0;background:linear-gradient(135deg,rgba(var(--primary-color-rgb,34,197,94),.22) 0%,transparent 70%);opacity:0;transition:opacity .35s;" class="svc-hover-tint"></div>

                <div class="svc-card-v3-content">
                    <div style="width:2.25rem;height:2.25rem;border-radius:10px;background:rgba(var(--primary-color-rgb,34,197,94),.22);border:1px solid rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;margin-bottom:.875rem;flex-shrink:0;backdrop-filter:blur(6px);">
                        <i class="{{ $svc['icon'] ?? 'fas fa-tree' }}" style="color:#fff;font-size:.8rem;"></i>
                    </div>
                    <h3 style="color:#fff;font-weight:900;font-size:1.1rem;line-height:1.2;margin:0 0 .4rem;">
                        {{ $svc['name'] }}
                    </h3>
                    @if(!empty($svc['short_description']))
                    <p style="color:rgba(255,255,255,.6);font-size:.8rem;line-height:1.6;margin:0 0 .75rem;">
                        {{ \Illuminate\Support\Str::limit($svc['short_description'], 90) }}
                    </p>
                    @endif
                    <div style="display:flex;align-items:center;gap:.4rem;font-size:.78rem;font-weight:700;color:var(--primary-color);opacity:0;transform:translateY(6px);transition:opacity .3s,transform .3s;" class="svc-cta-arrow">
                        Découvrir <i class="fas fa-arrow-right" style="font-size:.65rem;"></i>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

    </div>
</section>
<style>
.svc-card-v3:hover .svc-hover-tint { opacity: 1 !important; }
.svc-card-v3:hover .svc-cta-arrow  { opacity: 1 !important; transform: translateY(0) !important; }
</style>
@endif

{{-- ══════════════════════════════════════════════════════════
     §4  À PROPOS — Dark éditorial, photo + texte
     ══════════════════════════════════════════════════════════ --}}
@if($homeConfig['sections']['about']['enabled'] ?? true)
<section style="background:#0f1f13;padding:6rem 0;position:relative;overflow:hidden;">
    {{-- Halo décoratif --}}
    <div style="position:absolute;top:0;left:0;width:600px;height:600px;border-radius:50%;background:radial-gradient(circle,rgba(74,222,128,.1) 0%,transparent 65%);transform:translate(-40%,-40%);pointer-events:none;z-index:0;"></div>

    <div class="lp-shell" style="position:relative;z-index:1;">
        <div class="about-grid">

            {{-- Colonne gauche : photo + stats --}}
            <div class="about-col-left">
                @php $aboutPhoto = $homeConfig['about']['image'] ?? null; @endphp
                <div class="about-photo-wrap" style="flex:1;">
                    @if($aboutPhoto)
                        <img src="{{ asset(ltrim($aboutPhoto, '/')) }}"
                             alt="{{ $homeConfig['sections']['about']['title'] ?? 'À propos' }}"
                             loading="lazy">
                    @else
                        <div style="position:absolute;inset:0;background:linear-gradient(145deg,#0d3b22,#1a5c35);"></div>
                        <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-tree" style="font-size:8rem;color:rgba(255,255,255,.06);"></i>
                        </div>
                    @endif
                    {{-- Badge logo --}}
                    @if(setting('company_logo'))
                    <div style="position:absolute;bottom:1rem;right:1rem;width:3.5rem;height:3.5rem;border-radius:12px;overflow:hidden;border:2px solid rgba(255,255,255,.2);background:#fff;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 20px rgba(0,0,0,.3);">
                        <img src="{{ asset(setting('company_logo')) }}"
                             alt="{{ setting('company_name') }}"
                             style="width:2.25rem;height:auto;object-fit:contain;">
                    </div>
                    @endif
                </div>

                {{-- Statistiques --}}
                <div class="about-stats-row">
                    @foreach([
                        ['500+','Chantiers','#4ade80'],
                        ['15+','Ans exp.','#60a5fa'],
                        ['60+','Communes','#a78bfa'],
                        ['4.9★','Google','#fbbf24'],
                    ] as [$sv, $sl, $sc])
                    <div style="border-radius:14px;padding:.875rem .5rem;border:1px solid rgba(255,255,255,.07);background:rgba(255,255,255,.03);text-align:center;">
                        <div style="color:{{ $sc }};font-weight:900;font-size:1rem;line-height:1.2;margin-bottom:.2rem;">{{ $sv }}</div>
                        <div style="color:rgba(255,255,255,.38);font-size:.68rem;font-weight:600;line-height:1.3;">{{ $sl }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Colonne droite : texte --}}
            <div style="display:flex;flex-direction:column;justify-content:center;">
                <span class="lp-label" style="color:#4ade80;"><i class="fas fa-leaf"></i> À propos</span>
                <h2 style="font-size:clamp(1.75rem,3.5vw,2.8rem);font-weight:900;color:#fff;line-height:1.08;margin:0 0 1.25rem;">
                    {{ $homeConfig['sections']['about']['title'] ?? 'Votre élagueur de confiance dans l\'Oise' }}
                </h2>
                <p style="color:rgba(255,255,255,.52);font-size:.95rem;line-height:1.8;margin-bottom:1.75rem;">
                    {{ $homeConfig['sections']['about']['text'] ?? 'Louis Hoffmann est élagueur professionnel certifié, intervenant dans tout le département 60 (Oise) : Compiègne, Beauvais, Senlis, Chantilly, Creil, Noyon et toutes les communes environnantes. Avec plus de 15 ans d\'expérience, nous garantissons des interventions sécurisées, soignées et respectueuses de l\'environnement.' }}
                </p>

                <ul class="lp-checklist" style="margin-bottom:2rem;">
                    <li>Artisan certifié, assuré en responsabilité civile professionnelle</li>
                    <li>Équipements professionnels, respect des normes de sécurité</li>
                    <li>Valorisation des déchets verts — broyage sur place possible</li>
                    <li>Devis gratuit, transparent, sans engagement</li>
                    <li>Réponse sous 24h pour toute intervention dans le 60</li>
                </ul>

                <div style="display:flex;flex-wrap:wrap;gap:.875rem;">
                    <a href="{{ route('contact') }}" class="btn-solid-green">
                        <i class="fas fa-envelope" style="font-size:.85rem;"></i> Nous contacter
                    </a>
                    <a href="{{ route('form.step', 'propertyType') }}"
                       style="display:inline-flex;align-items:center;gap:.6rem;font-weight:700;font-size:.9rem;padding:.85rem 1.75rem;border-radius:12px;border:1.5px solid rgba(255,255,255,.18);color:#fff;text-decoration:none;transition:background .2s,border-color .2s;"
                       onmouseover="this.style.background='rgba(255,255,255,.07)'"
                       onmouseout="this.style.background='transparent'">
                        <i class="fas fa-calculator" style="font-size:.85rem;"></i> Devis gratuit
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>
@endif

{{-- ══════════════════════════════════════════════════════════
     §5  PROCESSUS — 4 étapes, horizontal desktop
     ══════════════════════════════════════════════════════════ --}}
<section style="background:#fff;padding:6rem 0;">
    <div class="lp-shell">

        <div style="text-align:center;margin-bottom:4rem;">
            <span class="lp-label"><i class="fas fa-route"></i> Comment ça marche</span>
            <h2 style="font-size:clamp(1.75rem,4vw,2.8rem);font-weight:900;color:#0f172a;margin:0;">
                Simple, rapide, professionnel
            </h2>
        </div>

        <div class="process-grid">
            <div class="process-line"></div>

            @foreach([
                [1,'fas fa-phone-alt','Prise de contact','Appelez ou remplissez le formulaire en ligne. Réponse garantie sous 24h dans tout le département 60.','#10b981','#d1fae5','#065f46'],
                [2,'fas fa-search-location','Visite & devis','Déplacement gratuit sur site pour évaluer et chiffrer précisément — Compiègne, Beauvais, Senlis…','#3b82f6','#dbeafe','#1e3a8a'],
                [3,'fas fa-calendar-check','Planification','Date calée selon vos disponibilités et la saison, en respectant les réglementations locales.','#8b5cf6','#ede9fe','#4c1d95'],
                [4,'fas fa-hard-hat','Travaux & nettoyage','Intervention sécurisée, chantier intégralement nettoyé, valorisation des déchets verts.','#f59e0b','#fef3c7','#78350f'],
            ] as [$pn, $picon, $ptitle, $ptext, $pc, $plight, $pdark])
            <div style="position:relative;display:flex;flex-direction:column;align-items:center;text-align:center;padding:1.5rem 1.25rem;z-index:1;" class="process-step">
                {{-- Numéro --}}
                <div style="position:relative;margin-bottom:1.5rem;">
                    <div style="width:3.5rem;height:3.5rem;border-radius:14px;background:{{ $pc }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:900;box-shadow:0 8px 24px {{ $pc }}40;transition:transform .3s;" class="process-num">
                        {{ $pn }}
                    </div>
                    <div style="position:absolute;inset:-8px;border-radius:20px;background:{{ $pc }};opacity:.14;z-index:-1;"></div>
                </div>
                {{-- Icône --}}
                <div style="width:2.5rem;height:2.5rem;border-radius:12px;background:{{ $plight }};display:flex;align-items:center;justify-content:center;margin-bottom:1rem;">
                    <i class="{{ $picon }}" style="color:{{ $pdark }};font-size:.9rem;"></i>
                </div>
                <h3 style="font-weight:900;color:#0f172a;font-size:.95rem;margin:0 0 .5rem;">{{ $ptitle }}</h3>
                <p style="color:#64748b;font-size:.83rem;line-height:1.7;margin:0;">{{ $ptext }}</p>
            </div>
            @endforeach
        </div>

        <div style="text-align:center;margin-top:3.5rem;">
            <a href="{{ route('form.step', 'propertyType') }}"
               style="display:inline-flex;align-items:center;gap:.75rem;background:linear-gradient(135deg,var(--primary-color),var(--secondary-color,#16a34a));color:#fff;font-size:1rem;font-weight:900;padding:1.1rem 2.5rem;border-radius:16px;text-decoration:none;box-shadow:0 8px 30px rgba(var(--primary-color-rgb,34,197,94),.3);transition:transform .2s,box-shadow .2s;"
               onmouseover="this.style.transform='scale(1.02)'"
               onmouseout="this.style.transform='scale(1)'">
                <i class="fas fa-calculator"></i>
                Démarrer mon devis gratuit
            </a>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════
     §6  ZONES D'INTERVENTION — Pills villes Oise
     ══════════════════════════════════════════════════════════ --}}
<section style="background:#f0faf4;padding:5rem 0;border-top:1px solid #c6e9d0;border-bottom:1px solid #c6e9d0;">
    <div class="lp-shell">
        <div class="zones-layout">

            {{-- Gauche --}}
            <div class="zones-left">
                <span class="lp-label"><i class="fas fa-map-marker-alt"></i> Zone d'intervention</span>
                <h2 style="font-size:clamp(1.5rem,3vw,2.2rem);font-weight:900;color:#0f172a;line-height:1.1;margin:0 0 .875rem;">
                    Tout le département<br><span style="color:var(--primary-color);">60 — Oise</span>
                </h2>
                <p style="font-size:.875rem;color:#4b5563;line-height:1.75;margin-bottom:1.5rem;">
                    Nous intervenons dans l'ensemble des communes de l'Oise et des départements limitrophes — Aisne, Somme, Val-d'Oise.
                </p>
                <a href="{{ route('contact') }}" class="btn-outline-green" style="font-size:.82rem;padding:.65rem 1.25rem;">
                    Vérifier ma commune <i class="fas fa-arrow-right" style="font-size:.7rem;"></i>
                </a>
            </div>

            {{-- Droite : pills --}}
            <div class="zones-right">
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
                <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
                    @if($displayCities->count() > 0)
                        @foreach($displayCities as $dcity)
                        <a href="{{ route('ads.index') }}?city={{ $dcity->slug }}"
                           style="display:inline-flex;align-items:center;gap:.4rem;padding:.45rem 1rem;border-radius:50px;border:1px solid #b8dfca;background:#fff;color:#1f4730;font-size:.8rem;font-weight:600;text-decoration:none;transition:all .18s;box-shadow:0 1px 3px rgba(0,0,0,.05);"
                           onmouseover="this.style.borderColor='var(--primary-color)';this.style.color='var(--primary-color)';this.style.background='rgba(var(--primary-color-rgb,34,197,94),.07)';"
                           onmouseout="this.style.borderColor='#b8dfca';this.style.color='#1f4730';this.style.background='#fff';">
                            <i class="fas fa-map-pin" style="color:var(--primary-color);font-size:.65rem;"></i>
                            {{ $dcity->name }}
                            @if($dcity->postal_code)
                            <span style="color:#9ca3af;font-size:.72rem;font-family:monospace;">{{ $dcity->postal_code }}</span>
                            @endif
                        </a>
                        @endforeach
                    @else
                        @foreach($oiseFallback as [$zname, $zpostal])
                        <div style="display:inline-flex;align-items:center;gap:.4rem;padding:.45rem 1rem;border-radius:50px;border:1px solid #b8dfca;background:#fff;color:#1f4730;font-size:.8rem;font-weight:600;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                            <i class="fas fa-map-pin" style="color:var(--primary-color);font-size:.65rem;"></i>
                            {{ $zname }}
                            <span style="color:#9ca3af;font-size:.72rem;font-family:monospace;">{{ $zpostal }}</span>
                        </div>
                        @endforeach
                        <div style="display:inline-flex;align-items:center;padding:.45rem 1rem;border-radius:50px;border:1px dashed #b8dfca;background:transparent;color:#9ca3af;font-size:.78rem;font-style:italic;">
                            + 40 autres communes…
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════
     §7  TÉMOIGNAGES — Grande citation + cartes
     ══════════════════════════════════════════════════════════ --}}
@if(($homeConfig['sections']['reviews']['enabled'] ?? true) && !empty($reviews) && count($reviews) > 0)
<section style="background:#f8f9fa;padding:6rem 0;">
    <div class="lp-shell">

        <div style="display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:1.5rem;margin-bottom:3.5rem;">
            <div>
                <span class="lp-label"><i class="fas fa-quote-left"></i> Témoignages</span>
                <h2 style="font-size:clamp(1.75rem,4vw,2.8rem);font-weight:900;color:#0f172a;margin:0;">
                    {{ $homeConfig['sections']['reviews']['title'] ?? 'Ce que disent nos clients' }}
                </h2>
                @if(isset($averageRating) && $averageRating > 0)
                <div style="display:flex;align-items:center;gap:.75rem;margin-top:.75rem;">
                    <div style="display:flex;gap:3px;">
                        @for($ri = 1; $ri <= 5; $ri++)
                        <i class="fas fa-star" style="font-size:.9rem;color:{{ $ri <= round($averageRating) ? '#fbbf24' : '#e5e7eb' }};"></i>
                        @endfor
                    </div>
                    <span style="font-weight:900;color:#0f172a;font-size:1.2rem;">{{ number_format($averageRating, 1) }}/5</span>
                    @if(isset($totalReviews))
                    <span style="color:#94a3b8;font-size:.875rem;">({{ $totalReviews }} avis)</span>
                    @endif
                </div>
                @endif
            </div>
            <a href="{{ route('reviews.all') }}" class="btn-outline-green" style="font-size:.82rem;padding:.65rem 1.25rem;">
                Tous les avis <i class="fas fa-star" style="font-size:.7rem;"></i>
            </a>
        </div>

        <div class="reviews-grid">
            @foreach($reviews->take(min(5, $homeConfig['sections']['reviews']['limit'] ?? 5)) as $rvi => $review)
            @if($rvi === 0)
            {{-- Grande carte sombre --}}
            <div style="background:linear-gradient(135deg,#0f1f13 0%,#1a4a28 60%,#0d3b22 100%);border-radius:24px;padding:2.5rem;display:flex;flex-direction:column;justify-content:space-between;position:relative;overflow:hidden;grid-column:span 1;"
                 class="review-featured">
                <div style="position:absolute;top:-1rem;left:-.5rem;font-size:8rem;font-weight:900;color:rgba(255,255,255,.04);line-height:1;pointer-events:none;user-select:none;">&ldquo;</div>
                <div>
                    <div style="display:flex;gap:3px;margin-bottom:1.25rem;">
                        @for($ri2=1;$ri2<=5;$ri2++)
                        <i class="fas fa-star" style="font-size:.875rem;color:{{ $ri2<=$review->rating ? '#fbbf24' : 'rgba(255,255,255,.18)' }};"></i>
                        @endfor
                    </div>
                    <p style="color:rgba(255,255,255,.82);font-size:1.15rem;line-height:1.7;font-weight:500;font-style:italic;margin:0 0 2rem;">
                        &ldquo;{{ \Illuminate\Support\Str::limit($review->review_text ?? 'Excellent travail, très professionnel.', 220) }}&rdquo;
                    </p>
                </div>
                <div style="display:flex;align-items:center;gap:.875rem;">
                    <div style="width:3rem;height:3rem;border-radius:50%;overflow:hidden;flex-shrink:0;border:2px solid rgba(255,255,255,.18);">
                        @if($review->author_photo_url)
                            <img src="{{ $review->author_photo_url }}" alt="{{ $review->author_name }}" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            <div style="width:100%;height:100%;background:var(--primary-color);display:flex;align-items:center;justify-content:center;font-weight:900;color:#fff;font-size:1.1rem;">
                                {{ strtoupper(substr($review->author_name ?? 'C', 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div>
                        <div style="color:#fff;font-weight:700;font-size:.9rem;">{{ $review->author_name }}</div>
                        <div style="color:rgba(255,255,255,.38);font-size:.75rem;">{{ $review->review_date ? \Carbon\Carbon::parse($review->review_date)->translatedFormat('F Y') : '' }}</div>
                    </div>
                    @if($review->source && str_contains($review->source, 'Google'))
                    <div style="margin-left:auto;display:flex;align-items:center;gap:.4rem;background:rgba(255,255,255,.07);padding:.35rem .75rem;border-radius:50px;">
                        <i class="fab fa-google" style="color:#60a5fa;font-size:.75rem;"></i>
                        <span style="color:rgba(255,255,255,.45);font-size:.7rem;font-weight:600;">Google</span>
                    </div>
                    @endif
                </div>
            </div>
            @else
            {{-- Petite carte blanche --}}
            <div style="background:#fff;border:1px solid #f1f5f9;border-radius:20px;padding:1.75rem;display:flex;flex-direction:column;box-shadow:0 2px 12px rgba(0,0,0,.05);transition:box-shadow .25s,transform .25s;position:relative;overflow:hidden;"
                 onmouseover="this.style.boxShadow='0 12px 36px rgba(0,0,0,.1)';this.style.transform='translateY(-3px)';"
                 onmouseout="this.style.boxShadow='0 2px 12px rgba(0,0,0,.05)';this.style.transform='translateY(0)';">
                <div style="position:absolute;top:.75rem;right:1rem;font-size:3.5rem;font-weight:900;color:#f8fafc;line-height:1;pointer-events:none;user-select:none;">&rdquo;</div>
                <div style="display:flex;gap:2px;margin-bottom:1rem;">
                    @for($ri3=1;$ri3<=5;$ri3++)
                    <i class="fas fa-star" style="font-size:.75rem;color:{{ $ri3<=$review->rating ? '#fbbf24' : '#e5e7eb' }};"></i>
                    @endfor
                </div>
                <p style="color:#475569;font-size:.855rem;line-height:1.7;font-style:italic;flex:1;margin:0 0 1.25rem;">
                    &ldquo;{{ \Illuminate\Support\Str::limit($review->review_text ?? 'Excellent travail.', 160) }}&rdquo;
                </p>
                <div style="display:flex;align-items:center;gap:.625rem;padding-top:1rem;border-top:1px solid #f1f5f9;">
                    <div style="width:2.25rem;height:2.25rem;border-radius:50%;overflow:hidden;flex-shrink:0;">
                        @if($review->author_photo_url)
                            <img src="{{ $review->author_photo_url }}" alt="{{ $review->author_name }}" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            <div style="width:100%;height:100%;background:linear-gradient(135deg,var(--primary-color),var(--secondary-color,#16a34a));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:900;font-size:.85rem;">
                                {{ strtoupper(substr($review->author_name ?? 'C', 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div>
                        <div style="font-weight:700;color:#0f172a;font-size:.8rem;">{{ $review->author_name }}</div>
                        <div style="color:#94a3b8;font-size:.7rem;">{{ $review->review_date ? \Carbon\Carbon::parse($review->review_date)->translatedFormat('F Y') : '' }}</div>
                    </div>
                </div>
            </div>
            @endif
            @endforeach
        </div>

    </div>
</section>
<style>
@media (min-width: 1024px) {
    .review-featured { grid-column: span 2 !important; }
}
</style>
@endif

{{-- ══════════════════════════════════════════════════════════
     §8  FAQ — Accordéon propre, sticky gauche
     ══════════════════════════════════════════════════════════ --}}
<section style="background:#fff;padding:6rem 0;">
    <div class="lp-shell">
        <div class="faq-layout">

            {{-- Gauche sticky --}}
            <div style="position:sticky;top:6rem;align-self:flex-start;">
                <span class="lp-label"><i class="fas fa-question-circle"></i> FAQ</span>
                <h2 style="font-size:clamp(1.7rem,3.5vw,2.6rem);font-weight:900;color:#0f172a;line-height:1.1;margin:0 0 1rem;">
                    Vos questions,<br>nos réponses
                </h2>
                <p style="color:#64748b;font-size:.9rem;line-height:1.75;margin-bottom:1.75rem;">
                    Tout ce que vous devez savoir avant de nous confier votre projet d'élagage dans l'Oise.
                </p>
                <a href="{{ route('contact') }}" class="btn-solid-green" style="font-size:.85rem;">
                    <i class="fas fa-envelope" style="font-size:.8rem;"></i> Poser une question
                </a>
            </div>

            {{-- Droite accordéon --}}
            <div>
                @foreach([
                    ['Intervenez-vous dans toute l\'Oise ?','Oui, nous couvrons l\'ensemble du département 60 : Compiègne, Beauvais, Senlis, Chantilly, Creil, Noyon, Verberie, Clermont, Méru, Liancourt, Pont-Sainte-Maxence et toutes les communes alentour. Nous intervenons également dans les départements limitrophes (Aisne, Somme, Val-d\'Oise).'],
                    ['Combien coûte un élagage dans l\'Oise ?','Le tarif dépend du type d\'arbre, de sa hauteur, de son accessibilité et des travaux à effectuer. C\'est pourquoi nous proposons un devis gratuit et sans engagement après visite sur place. Comptez en général entre 150 € et 800 € pour un élagage standard.'],
                    ['Avez-vous les certifications nécessaires ?','Absolument. Louis Hoffmann est artisan certifié, assuré en responsabilité civile professionnelle. Nous respectons toutes les normes de sécurité et les réglementations en vigueur dans le département 60.'],
                    ['Que faites-vous des déchets verts ?','Nous broyons les branches sur place si vous le souhaitez (le broyat peut servir de paillis). Les rémanents peuvent aussi être évacués et valorisés en composterie ou énergie verte.'],
                    ['En quelle saison effectuer l\'élagage ?','L\'élagage se pratique idéalement en fin d\'hiver (février-mars) ou en été (juillet-août). Certains arbres ont leurs propres rythmes. L\'abattage peut se faire toute l\'année. Nous vous conseillons lors du devis.'],
                    ['Puis-je obtenir un devis rapidement ?','Oui. Remplissez notre formulaire en ligne ou appelez-nous directement. Nous vous répondons sous 24h et nous déplaçons gratuitement pour établir un devis détaillé.'],
                ] as $faq)
                <div class="faq-item">
                    <details>
                        <summary>
                            <span>{{ $faq[0] }}</span>
                            <div class="faq-icon"><i class="fas fa-plus"></i></div>
                        </summary>
                        <div class="faq-body">{{ $faq[1] }}</div>
                    </details>
                </div>
                @endforeach
            </div>

        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════
     §9  CTA FINAL — Gradient sombre, impact maximal
     ══════════════════════════════════════════════════════════ --}}
@if($homeConfig['sections']['cta']['enabled'] ?? true)
<div style="height:3px;background:linear-gradient(90deg,var(--primary-color),var(--secondary-color,#16a34a),var(--primary-color));"></div>

<section style="background:linear-gradient(155deg,#0a2214 0%,#1a5c35 50%,#0a2214 100%);position:relative;overflow:hidden;">
    {{-- Motif pointillé --}}
    <div style="position:absolute;inset:0;background-image:radial-gradient(rgba(255,255,255,.5) 1px,transparent 1px);background-size:28px 28px;opacity:.03;pointer-events:none;"></div>
    {{-- Halo central --}}
    <div style="position:absolute;top:0;left:50%;width:900px;height:600px;border-radius:50%;background:radial-gradient(ellipse,rgba(74,222,128,.2) 0%,transparent 65%);transform:translateX(-50%) translateY(-45%);pointer-events:none;"></div>

    <div class="lp-shell" style="position:relative;z-index:1;text-align:center;padding-top:7rem;padding-bottom:7rem;">

        {{-- Badge pill --}}
        <div style="display:inline-flex;align-items:center;gap:.625rem;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);border-radius:50px;padding:.5rem 1.25rem;margin-bottom:2rem;">
            <span style="width:.5rem;height:.5rem;border-radius:50%;background:#4ade80;animation:pulse-dot 2s infinite;display:inline-block;"></span>
            <span style="color:rgba(255,255,255,.65);font-size:.72rem;font-weight:800;letter-spacing:.16em;text-transform:uppercase;">Département 60 — Oise · Picardie</span>
        </div>

        {{-- H2 --}}
        <h2 style="font-size:clamp(2.4rem,7vw,5.5rem);font-weight:900;color:#fff;line-height:.95;letter-spacing:-.02em;margin:0 0 1.5rem;">
            {!! nl2br(e($homeConfig['sections']['cta']['title'] ?? "Besoin d'un élagueur\ndans l'Oise ?")) !!}
        </h2>

        <p style="color:rgba(255,255,255,.48);font-size:1.1rem;line-height:1.75;max-width:600px;margin:0 auto 3rem;">
            Compiègne, Beauvais, Senlis, Chantilly, Creil, Noyon —
            devis gratuit, réponse sous 24h, artisan certifié et assuré.
        </p>

        {{-- CTAs --}}
        <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:1rem;margin-bottom:3.5rem;">
            <a href="{{ route('form.step', 'propertyType') }}"
               onclick="if(typeof trackFormClick==='function')trackFormClick('{{ request()->url() }}')"
               style="display:inline-flex;align-items:center;gap:.75rem;background:#fff;color:var(--primary-color);font-size:1.05rem;font-weight:900;padding:1.1rem 2.5rem;border-radius:16px;text-decoration:none;box-shadow:0 10px 40px rgba(0,0,0,.3);transition:transform .2s,box-shadow .2s;"
               onmouseover="this.style.transform='scale(1.03)';this.style.background='#f0fdf4';"
               onmouseout="this.style.transform='scale(1)';this.style.background='#fff';">
                <i class="fas fa-calculator"></i>
                Devis gratuit en ligne
            </a>
            <a href="tel:{{ $phoneRaw }}"
               onclick="if(typeof trackPhoneCall==='function')trackPhoneCall('{{ $phoneRaw }}','cta-final')"
               style="display:inline-flex;align-items:center;gap:.75rem;border:1.5px solid rgba(255,255,255,.22);color:#fff;font-size:1.05rem;font-weight:700;padding:1.1rem 2.5rem;border-radius:16px;text-decoration:none;transition:background .2s,border-color .2s;"
               onmouseover="this.style.background='rgba(255,255,255,.08)';this.style.borderColor='rgba(255,255,255,.4)';"
               onmouseout="this.style.background='transparent';this.style.borderColor='rgba(255,255,255,.22)';">
                <span style="position:relative;display:inline-flex;">
                    <span style="position:absolute;inset:0;border-radius:50%;background:#4ade80;opacity:.22;animation:pulse-dot 2s infinite;"></span>
                    <i class="fas fa-phone" style="color:#4ade80;position:relative;"></i>
                </span>
                {{ $phone }}
            </a>
        </div>

        {{-- Trust pills --}}
        <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:.75rem;">
            @foreach(['Devis 100% gratuit','Sans engagement','Réponse sous 24h','Artisan certifié','Tout le dép. 60'] as $tpill)
            <div style="display:flex;align-items:center;gap:.5rem;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);padding:.45rem 1.1rem;border-radius:50px;color:rgba(255,255,255,.5);font-size:.78rem;font-weight:600;">
                <i class="fas fa-check" style="color:#4ade80;font-size:.65rem;"></i>
                {{ $tpill }}
            </div>
            @endforeach
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
