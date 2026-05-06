{{-- ============================================================
     PAGE ACCUEIL — Design Artisan Editorial
     Police : Fraunces (display) + Manrope (corps)
     Palette : fond chaud #FAF7F2 · primary depuis settings
     Données : toutes issues de homeConfig + Laravel DB
     ============================================================ --}}

@php
    $phone    = $companySettings['phone'] ?? setting('company_phone', '');
    $phoneRaw = preg_replace('/\s+/', '', $phone);
    $city     = $companySettings['city'] ?? setting('company_city', 'Paris');
    $name     = $companySettings['name'] ?? setting('company_name', 'Votre Entreprise');

    /* Hero */
    $heroTitle   = $homeConfig['hero']['title']    ?? $name;
    $heroSub     = $homeConfig['hero']['subtitle'] ?? 'Expert en ' . setting('company_specialization', 'travaux de rénovation') . '. Devis gratuit, intervention rapide, qualité garantie.';
    $heroCta     = $homeConfig['hero']['cta_text'] ?? 'Devis gratuit en 1 min';
    $showPhone   = $homeConfig['hero']['show_phone'] ?? true;
    $heroImg     = $homeConfig['hero']['background_image'] ?? null;
    $heroImgUrl  = $heroImg ? asset(ltrim($heroImg, '/')) : null;

    /* Stats (depuis homeConfig ou défaut) */
    $statsData = $homeConfig['stats'] ?? [
        ['label'=>'Chantiers réalisés','value'=>'500+'],
        ['label'=>'Note Google','value'=>($totalReviews > 0 ? number_format($averageRating,1,',','').'/5' : '5/5')],
        ['label'=>'Délai de réponse','value'=>'24h'],
        ['label'=>'Garantie décennale','value'=>'10 ans'],
    ];

    /* Sections flags */
    $secServices  = $homeConfig['sections']['services']  ?? ['enabled'=>true,'title'=>'Nos Services','limit'=>6];
    $secPortfolio = $homeConfig['sections']['portfolio'] ?? ['enabled'=>true,'title'=>'Nos Réalisations','limit'=>3];
    $secReviews   = $homeConfig['sections']['reviews']   ?? ['enabled'=>true,'title'=>'Avis Clients','limit'=>3];
    $secAbout     = $homeConfig['sections']['about']     ?? ['enabled'=>true,'title'=>'Pourquoi nous choisir ?'];
    $secCta       = $homeConfig['sections']['cta']       ?? ['enabled'=>true,'title'=>'Un projet ? Parlons-en.'];

    /* About */
    $aboutEnabled = $homeConfig['about']['enabled'] ?? ($secAbout['enabled'] ?? true);
    $aboutTitle   = $homeConfig['about']['title']   ?? ($secAbout['title'] ?? 'Un artisan, pas une multinationale.');
    $aboutContent = $homeConfig['about']['content'] ?? $homeConfig['sections']['about']['text'] ?? '';
    $aboutImg     = $homeConfig['about']['image']   ?? null;
    $aboutImgUrl  = $aboutImg ? asset(ltrim($aboutImg, '/')) : null;

    /* Services */
    $allSvc  = is_array($services) ? $services : [];
    $svcList = array_values(array_filter($allSvc, fn($s) => is_array($s) && ($s['is_visible'] ?? true)));
    $svcLimit = (int)($secServices['limit'] ?? 6);

    /* Portfolio */
    $portLimit = (int)($secPortfolio['limit'] ?? 3);
    $displayedPortfolio = array_slice($portfolioItems ?? [], 0, $portLimit);

    /* Reviews */
    $revLimit = (int)($secReviews['limit'] ?? 3);
    $ratingVal = round((float)($averageRating ?? 5), 1);
    $reviewCount = (int)($totalReviews ?? 0);
    $displayedReviews = $reviews->take($revLimit);
@endphp

@push('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght,SOFT@9..144,500,30;9..144,700,30;9..144,800,30&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
/* ── DESIGN SYSTEM ─────────────────────────────────────────── */
.at {
    --p:    var(--primary-color, #B7472A);
    --pd:   color-mix(in srgb, var(--p) 75%, #000);
    --ps:   color-mix(in srgb, var(--p) 12%, transparent);
    --pf:   #fff;

    --bg:      #FAF7F2;
    --bgs:     #F2EDE4;
    --bgc:     #FFFFFF;
    --ink:     #1F1A14;
    --ink2:    #4A3F32;
    --ink3:    #7A6E5F;
    --ln:      rgba(31,26,20,.10);
    --lns:     rgba(31,26,20,.18);

    --s1: 0 1px 2px rgba(31,26,20,.05), 0 1px 3px rgba(31,26,20,.08);
    --s2: 0 4px 14px rgba(31,26,20,.07), 0 2px 4px rgba(31,26,20,.04);
    --s3: 0 12px 40px rgba(31,26,20,.10);

    --r:   14px;
    --rsm: 10px;
    --rlg: 22px;

    --fd: "Fraunces", ui-serif, Georgia, serif;
    --fb: "Manrope", ui-sans-serif, system-ui, sans-serif;
    --cw: 1200px;

    font-family: var(--fb);
    background: var(--bg);
    color: var(--ink);
    -webkit-font-smoothing: antialiased;
}
.at *, .at *::before, .at *::after { box-sizing: border-box; }
.at h1,.at h2,.at h3,.at h4 {
    font-family: var(--fd); font-weight: 700;
    letter-spacing: -.025em; color: var(--ink); margin: 0;
}
.at h1 { font-size: clamp(38px,5vw,66px); line-height: 1.04; }
.at h2 { font-size: clamp(26px,3vw,42px); line-height: 1.1; }
.at h3 { font-size: clamp(17px,1.4vw,21px); line-height: 1.25; }
.at p  { margin: 0; }
.at a  { color: inherit; text-decoration: none; }

/* Conteneur */
.at-w { width:100%; max-width:var(--cw); margin:0 auto; padding:0 24px; }

/* Sections */
.at-sec  { padding: 88px 0; }
.at-sec.tight { padding: 56px 0; }

/* Eyebrow */
.at-ey {
    display: inline-flex; align-items: center; gap: 8px;
    font-size: 11.5px; font-weight: 700; letter-spacing: .12em;
    text-transform: uppercase; color: var(--p); margin-bottom: 14px;
}
.at-ey::before { content:''; display:block; width:22px; height:2px; background:var(--p); border-radius:2px; }

/* Section header */
.at-sh { margin-bottom: 48px; max-width: 720px; }
.at-sh p { color: var(--ink3); font-size: 17px; margin-top: 12px; }

/* Boutons */
.at-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    height: 52px; padding: 0 24px; border: 0; border-radius: 999px;
    font-family: var(--fb); font-weight: 600; font-size: 15px;
    cursor: pointer; transition: all .15s; text-decoration: none;
}
.at-btn-p  { background: var(--p); color: var(--pf); }
.at-btn-p:hover { filter: brightness(.88); transform: translateY(-1px); }
.at-btn-g  { background: var(--bgc); color: var(--ink); border: 1.5px solid var(--lns); }
.at-btn-g:hover { background: var(--bgs); }
.at-btn-lg { height: 60px; padding: 0 32px; font-size: 16px; }

/* Card */
.at-card { background: var(--bgc); border-radius: var(--r); box-shadow: var(--s1); border: 1px solid var(--ln); }

/* Photo (background-image) */
.at-photo {
    position: relative; overflow: hidden; background: var(--bgs);
    background-size: cover; background-position: center;
}
.at-photo img { width:100%; height:100%; object-fit:cover; display:block; }

/* ── §1 HERO ───────────────────────────────────────────────── */
.at-hero {
    position: relative; min-height: min(720px, 88vh);
    display: flex; align-items: stretch; overflow: hidden; padding: 0 !important;
}
.at-hero-bg  { position:absolute; inset:0; background-size:cover; background-position:center; }
.at-hero-ov1 { position:absolute; inset:0; background:linear-gradient(180deg,rgba(12,10,8,.52) 0%,rgba(12,10,8,.42) 50%,rgba(12,10,8,.88) 100%); }
.at-hero-ov2 { position:absolute; inset:0; background:linear-gradient(90deg,rgba(12,10,8,.72) 0%,rgba(12,10,8,.18) 70%,transparent 100%); }
.at-hero-in  {
    position:relative; z-index:2; color:#fff;
    display:flex; align-items:center;
    width:100%; max-width:var(--cw); margin:0 auto;
    padding: 88px 24px 110px;
}
.at-hero-box { max-width: 780px; }
.at-badge {
    display:inline-flex; align-items:center; gap:8px;
    padding:6px 14px; border-radius:999px;
    background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.22);
    backdrop-filter:blur(8px); font-size:13px; font-weight:600; color:#fff;
    margin-bottom:24px;
}
.at-badge-dot { width:8px; height:8px; border-radius:50%; background:#6FCF97; animation:atpulse 2s infinite; }
@keyframes atpulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.6;transform:scale(1.25)} }
.at-hero-ctas { display:flex; gap:12px; margin-top:36px; flex-wrap:wrap; }
.at-btn-tel {
    display:inline-flex; align-items:center; gap:8px;
    height:60px; padding:0 28px; border-radius:999px;
    background:rgba(255,255,255,.12); color:#fff;
    border:1px solid rgba(255,255,255,.3); backdrop-filter:blur(8px);
    font-weight:600; font-size:15px; text-decoration:none;
    transition:background .15s;
}
.at-btn-tel:hover { background:rgba(255,255,255,.2); }
.at-hero-trust { display:flex; align-items:center; gap:24px; margin-top:44px; flex-wrap:wrap; }
.at-trust-div  { width:1px; height:32px; background:rgba(255,255,255,.2); }
.at-avatars    { display:flex; }
.at-avatar {
    width:36px; height:36px; border-radius:50%;
    border:2px solid rgba(255,255,255,.9);
    font-weight:700; font-size:13px; color:#fff;
    display:flex; align-items:center; justify-content:center;
}
.at-hero-logosbar {
    position:absolute; bottom:0; left:0; right:0; z-index:3;
    padding:13px 24px;
    background:rgba(0,0,0,.38); backdrop-filter:blur(12px);
    border-top:1px solid rgba(255,255,255,.1);
    display:flex; justify-content:center; gap:36px; flex-wrap:wrap;
    font-size:11.5px; color:rgba(255,255,255,.72);
    font-weight:600; letter-spacing:.3px; text-transform:uppercase;
    font-family: var(--fb);
}
.at-hero-logosbar span { display:inline-flex; align-items:center; gap:6px; }

/* ── §2 STATS ──────────────────────────────────────────────── */
.at-stats { padding:56px 0; background:#111111; color:#fff; }
.at-stats-grid {
    display:grid; grid-template-columns:repeat(4,1fr); gap:24px; text-align:center;
}
.at-stat-n {
    font-family:var(--fd); font-weight:700;
    font-size:clamp(34px,4vw,52px); letter-spacing:-.02em; line-height:1;
    color:var(--p);
}
.at-stat-l { font-size:13.5px; color:rgba(255,255,255,.55); margin-top:8px; letter-spacing:.3px; }

/* ── §3 SERVICES ───────────────────────────────────────────── */
.at-svc-hdr { display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:48px; gap:24px; flex-wrap:wrap; }
.at-svc-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(290px,1fr)); gap:20px; }
.at-svc-card { overflow:hidden; display:flex; flex-direction:column; cursor:pointer; transition:transform .15s, box-shadow .15s; text-decoration:none; }
.at-svc-card:hover { transform:translateY(-3px); box-shadow:var(--s2); }
.at-svc-photo {
    height:210px; position:relative; overflow:hidden;
    background:var(--bgs); background-size:cover; background-position:center;
    border-radius:var(--r) var(--r) 0 0;
}
.at-svc-photo-inner { width:100%; height:100%; object-fit:cover; display:block; transition:transform .4s; }
.at-svc-card:hover .at-svc-photo-inner { transform:scale(1.04); }
.at-svc-body { padding:22px; display:flex; flex-direction:column; gap:9px; flex:1; }
.at-svc-more { display:inline-flex; align-items:center; gap:6px; color:var(--p); font-weight:600; font-size:13.5px; margin-top:auto; padding-top:8px; }

/* ── §4 COMMENT ÇA MARCHE ──────────────────────────────────── */
.at-how-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
.at-how-n    { font-family:var(--fd); font-weight:700; font-size:13px; color:var(--p); letter-spacing:.5px; margin-bottom:12px; }
.at-how-ico  { width:46px; height:46px; border-radius:12px; background:var(--ps); color:var(--p); display:flex; align-items:center; justify-content:center; margin-bottom:14px; }

/* ── §5 ABOUT / POURQUOI NOUS ──────────────────────────────── */
.at-why-grid { display:grid; grid-template-columns:1fr 1.3fr; gap:60px; align-items:center; }
.at-why-img  { border-radius:var(--rlg); overflow:hidden; aspect-ratio:4/5; background-size:cover; background-position:center; position:relative; min-height:360px; }
.at-why-qcard {
    position:absolute; bottom:20px; left:20px; right:20px; padding:18px;
    background:rgba(255,255,255,.96); border-radius:var(--rsm); backdrop-filter:blur(8px);
}
.at-benefits { display:grid; grid-template-columns:repeat(2,1fr); gap:16px; margin-top:28px; }
.at-benefit  { display:flex; gap:12px; align-items:flex-start; }
.at-ben-ico  { width:38px; height:38px; border-radius:9px; flex-shrink:0; background:var(--ps); color:var(--p); display:flex; align-items:center; justify-content:center; }

/* ── §6 PORTFOLIO ──────────────────────────────────────────── */
.at-real-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; }
.at-real-tag  { position:absolute; top:12px; left:12px; background:rgba(255,255,255,.95); color:var(--ink); padding:4px 10px; border-radius:999px; font-size:12px; font-weight:600; }

/* ── §7 AVIS ───────────────────────────────────────────────── */
.at-avis-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(290px,1fr)); gap:16px; }
.at-avis-card { padding:26px; display:flex; flex-direction:column; gap:14px; }
.at-avis-foot { display:flex; align-items:center; gap:12px; padding-top:12px; border-top:1px solid var(--ln); }
.at-avis-av   { width:34px; height:34px; border-radius:50%; background:var(--bgs); display:flex; align-items:center; justify-content:center; font-weight:600; font-size:13px; color:var(--ink2); }

/* ── §8 VILLES ─────────────────────────────────────────────── */
.at-pills { display:flex; flex-wrap:wrap; gap:8px; }
.at-pill {
    padding:9px 16px; background:var(--bgc); border:1px solid var(--ln);
    border-radius:999px; font-size:13.5px; font-weight:500;
    display:inline-flex; align-items:center; gap:6px;
    color:var(--ink); text-decoration:none; transition:border-color .15s, color .15s;
}
.at-pill:hover { border-color:var(--p); color:var(--p); }

/* ── §9 CONTACT FORM ───────────────────────────────────────── */
.at-cg { display:grid; grid-template-columns:1fr 1.3fr; gap:56px; align-items:flex-start; }
.at-ci {
    display:flex; align-items:center; gap:14px; padding:17px;
    background:var(--bgc); border:1px solid var(--ln); border-radius:var(--r);
    text-decoration:none; color:var(--ink);
}
.at-ci-ico { width:42px; height:42px; border-radius:11px; background:var(--ps); color:var(--p); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.at-ci-lbl { font-size:11.5px; color:var(--ink3); text-transform:uppercase; letter-spacing:.5px; font-weight:600; }
.at-ci-val { font-weight:700; font-size:16px; }

/* Formulaire */
.at-form   { display:flex; flex-direction:column; gap:13px; }
.at-frow   { display:grid; grid-template-columns:1fr 1fr; gap:13px; }
.at-field  { display:flex; flex-direction:column; gap:5px; }
.at-field label { font-size:12.5px; font-weight:600; color:var(--ink2); }
.at-field input,.at-field select,.at-field textarea {
    width:100%; padding:0 14px; height:46px;
    border:1.5px solid var(--lns); border-radius:10px;
    font-family:var(--fb); font-size:14.5px; background:var(--bgc);
    color:var(--ink); outline:none; transition:border-color .15s;
}
.at-field input:focus,.at-field select:focus,.at-field textarea:focus { border-color:var(--p); }
.at-field textarea { height:90px; padding-top:12px; resize:vertical; }
.at-submit {
    width:100%; height:52px; margin-top:6px;
    background:var(--p); color:var(--pf); border:0;
    border-radius:999px; font-family:var(--fb); font-size:15px; font-weight:600;
    cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;
    transition:filter .15s, transform .15s;
}
.at-submit:hover { filter:brightness(.88); transform:translateY(-1px); }

/* ── §10 CTA STRIP ─────────────────────────────────────────── */
.at-cta-strip { padding:64px 0; background:#111111; color:#fff; }
.at-cta-inner { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:32px; }

/* ── §11 FAQ ───────────────────────────────────────────────── */
.at-faq-item { padding:20px 24px; cursor:pointer; }
.at-faq-item summary { font-weight:700; font-size:16px; list-style:none; display:flex; justify-content:space-between; align-items:center; gap:12px; color:var(--ink); user-select:none; }
.at-faq-item summary::-webkit-details-marker { display:none; }
.at-faq-item[open] summary { color:var(--p); }
.at-faq-item[open] .at-faq-icon { transform:rotate(45deg); }
.at-faq-icon { width:24px; height:24px; border-radius:50%; background:var(--ps); color:var(--p); display:flex; align-items:center; justify-content:center; flex-shrink:0; transition:transform .2s; }
.at-faq-body { margin-top:12px; color:var(--ink2); font-size:14.5px; line-height:1.65; }

/* ── RESPONSIVE ────────────────────────────────────────────── */
@media(max-width:960px){
    .at-stats-grid { grid-template-columns:repeat(2,1fr); gap:28px; }
    .at-how-grid   { grid-template-columns:repeat(2,1fr); }
    .at-why-grid   { grid-template-columns:1fr; gap:32px; }
    .at-real-grid  { grid-template-columns:1fr 1fr; }
    .at-cg         { grid-template-columns:1fr; gap:32px; }
    .at-hero-logosbar { gap:18px; font-size:10.5px; }
    .at-trust-div  { display:none; }
}
@media(max-width:640px){
    .at-how-grid   { grid-template-columns:1fr; }
    .at-svc-grid   { grid-template-columns:1fr; }
    .at-avis-grid  { grid-template-columns:1fr; }
    .at-frow       { grid-template-columns:1fr; }
    .at-benefits   { grid-template-columns:1fr; }
    .at-real-grid  { grid-template-columns:1fr; }
    .at-sec        { padding:56px 0; }
    .at-hero-in    { padding:72px 20px 100px; }
}
</style>
@endpush

<div class="at">

{{-- ═══ §1 HERO ═══════════════════════════════════════════════ --}}
<section class="at-hero">
    <div class="at-hero-bg" style="{{ $heroImgUrl ? 'background-image:url('.e($heroImgUrl).');' : 'background:#1a1a1a;' }}"></div>
    <div class="at-hero-ov1"></div>
    <div class="at-hero-ov2"></div>

    <div class="at-hero-in">
        <div class="at-hero-box">
            <div class="at-badge">
                <span class="at-badge-dot"></span>
                Disponible aujourd'hui · {{ $city }}
            </div>

            <h1 style="color:#fff;">{{ $heroTitle }}</h1>

            <p style="font-size:clamp(16px,1.4vw,20px); color:rgba(255,255,255,.85); margin-top:22px; max-width:620px; line-height:1.6;">
                {{ $heroSub }}
            </p>

            <div class="at-hero-ctas">
                <a href="#at-contact"
                   class="at-btn at-btn-p at-btn-lg"
                   style="box-shadow:0 8px 28px rgba(0,0,0,.32);">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    {{ $heroCta }}
                </a>
                @if($showPhone && $phone)
                <a href="tel:{{ $phoneRaw }}" class="at-btn-tel">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
                    {{ $phone }}
                </a>
                @endif
            </div>

            <div class="at-hero-trust">
                @if($reviewCount > 0)
                <div style="display:flex; align-items:center; gap:12px;">
                    <div class="at-avatars">
                        @foreach($reviews->take(4) as $ri => $rv)
                        <div class="at-avatar" style="background:{{ ['#D9C7A0','#C2B59B','#A89B7E','#8C7B5E'][$ri%4] }}; margin-left:{{ $ri===0?'0':'-9px' }};">
                            {{ mb_strtoupper(mb_substr($rv->author_name??'C',0,1)) }}
                        </div>
                        @endforeach
                    </div>
                    <div>
                        <div style="display:flex; align-items:center; gap:6px; color:#FFD166;">
                            @for($s=1;$s<=5;$s++)<svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>@endfor
                            <span style="color:#fff; font-weight:700; font-size:13.5px;">{{ number_format($ratingVal,1,',','') }}/5</span>
                        </div>
                        <div style="font-size:12px; color:rgba(255,255,255,.65);">{{ $reviewCount }} avis Google</div>
                    </div>
                </div>
                <div class="at-trust-div"></div>
                @endif
                <div style="display:flex; align-items:center; gap:9px;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary-color,#B7472A)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>
                    <div>
                        <div style="font-weight:700; font-size:13.5px; color:#fff;">Garantie décennale</div>
                        <div style="font-size:12px; color:rgba(255,255,255,.6);">Artisan certifié</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="at-hero-logosbar">
        <span><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/></svg> Garantie décennale</span>
        <span><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="m15.5 12.5 2.5 9-6-3-6 3 2.5-9"/></svg> Artisan certifié</span>
        <span><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg> Devis 100% gratuit</span>
        @if($reviewCount > 0)
        <span><svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg> {{ number_format($ratingVal,1,',','') }}/5 Google</span>
        @endif
        <span><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76Z"/></svg> Intervention rapide</span>
    </div>
</section>


{{-- ═══ §2 STATS ════════════════════════════════════════════════ --}}
<section class="at-stats">
    <div class="at-w">
        <div class="at-stats-grid">
            @foreach($statsData as $stat)
            <div>
                <div class="at-stat-n">{{ $stat['value'] }}</div>
                <div class="at-stat-l">{{ $stat['label'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ═══ §3 SERVICES ════════════════════════════════════════════ --}}
@if(($secServices['enabled'] ?? true) && !empty($svcList))
<section class="at-sec">
    <div class="at-w">
        <div class="at-svc-hdr">
            <div class="at-sh" style="margin-bottom:0;">
                <div class="at-ey">Nos services</div>
                <h2>{{ $secServices['title'] ?? 'Tout ce qu\'on peut faire pour vous' }}</h2>
                <p>Un seul interlocuteur, un travail soigné, un devis clair.</p>
            </div>
            <a href="{{ route('services.index') }}" class="at-btn at-btn-g">
                Voir tous les services
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            </a>
        </div>
        <div class="at-svc-grid">
            @foreach(array_slice($svcList, 0, $svcLimit) as $svc)
            @php
                $svcImg = null;
                if (!empty($svc['featured_image'])) {
                    $raw = $svc['featured_image'];
                    $svcImg = strpos($raw,'http')===0 ? $raw : asset(ltrim($raw,'/'));
                } elseif (!empty($svc['image'])) {
                    $raw = $svc['image'];
                    $svcImg = strpos($raw,'http')===0 ? $raw : asset(ltrim($raw,'/'));
                }
                $svcName  = $svc['name']  ?? $svc['title'] ?? 'Service';
                $svcShort = $svc['short_description'] ?? $svc['description'] ?? '';
                $svcSlug  = $svc['slug']  ?? \Illuminate\Support\Str::slug($svcName);
                $svcIcon  = $svc['icon']  ?? '';
            @endphp
            <a href="{{ route('services.show', $svcSlug) }}" class="at-card at-svc-card">
                <div class="at-svc-photo">
                    @if($svcImg)
                        <img src="{{ $svcImg }}" alt="{{ $svcName }}" class="at-svc-photo-inner" loading="lazy">
                    @else
                        <div style="width:100%;height:100%;background:linear-gradient(135deg,var(--ps),var(--bgs));display:flex;align-items:center;justify-content:center;">
                            <i class="{{ $svcIcon ?: 'fas fa-wrench' }}" style="font-size:2.5rem;color:var(--p);opacity:.35;"></i>
                        </div>
                    @endif
                </div>
                <div class="at-svc-body">
                    <h3>{{ $svcName }}</h3>
                    @if($svcShort)
                    <p style="color:var(--ink3); font-size:14px; line-height:1.55;">{{ Str::limit($svcShort, 120) }}</p>
                    @endif
                    <span class="at-svc-more">
                        En savoir plus
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif


{{-- ═══ §4 COMMENT ÇA MARCHE ═══════════════════════════════════ --}}
<section style="background:#1F1A14; padding:88px 0; position:relative; overflow:hidden;">
    <div style="position:absolute;inset:0;background:radial-gradient(ellipse 70% 60% at 60% 50%,color-mix(in srgb,var(--primary-color,#B7472A) 18%,transparent),transparent);pointer-events:none;"></div>
    <div class="at-w" style="position:relative;z-index:1;">
        <div style="text-align:center; margin-bottom:60px;">
            <div style="display:inline-flex;align-items:center;gap:8px;font-size:11.5px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--primary-color,#B7472A);margin-bottom:14px;">
                <span style="display:block;width:22px;height:2px;background:var(--primary-color,#B7472A);border-radius:2px;"></span>
                Comment ça marche
            </div>
            <h2 style="font-family:'Fraunces',ui-serif,Georgia,serif;color:#fff;font-size:clamp(26px,3vw,42px);font-weight:700;letter-spacing:-.025em;line-height:1.1;margin:0;">De l'appel au chantier fini, <span style="color:var(--primary-color,#B7472A);">en 4 étapes.</span></h2>
        </div>
        <div class="at-how-grid" style="position:relative;">
            @php
            $steps = [
                ['n'=>'01','t'=>'Vous nous contactez','d'=>'Par téléphone ou via le formulaire. Réponse garantie sous 24h.','svg'=>'<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92Z"/>'],
                ['n'=>'02','t'=>'Visite chez vous','d'=>'On se déplace pour évaluer le chantier, gratuitement.','svg'=>'<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>'],
                ['n'=>'03','t'=>'Devis sous 24h','d'=>'Détaillé, ligne par ligne, sans surprise.','svg'=>'<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/>'],
                ['n'=>'04','t'=>'On réalise','d'=>'Chantier propre, équipe formée, livraison dans les délais.','svg'=>'<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76Z"/>'],
            ];
            @endphp
            @foreach($steps as $i => $step)
            <div style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);border-radius:18px;padding:32px 24px;position:relative;transition:background .2s,border-color .2s;" onmouseover="this.style.background='rgba(255,255,255,.09)';this.style.borderColor='var(--primary-color,#B7472A)'" onmouseout="this.style.background='rgba(255,255,255,.05)';this.style.borderColor='rgba(255,255,255,.09)'">
                <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;">
                    <div style="width:52px;height:52px;border-radius:14px;background:var(--primary-color,#B7472A);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 8px 24px color-mix(in srgb,var(--primary-color,#B7472A) 45%,transparent);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $step['svg'] !!}</svg>
                    </div>
                    <span style="font-size:42px;font-weight:800;font-family:'Fraunces',serif;color:rgba(255,255,255,.08);line-height:1;letter-spacing:-.03em;">{{ $step['n'] }}</span>
                </div>
                <h3 style="font-family:'Fraunces',serif;font-size:19px;font-weight:700;color:#fff;margin-bottom:10px;letter-spacing:-.02em;">{{ $step['t'] }}</h3>
                <p style="color:rgba(255,255,255,.55);font-size:14px;line-height:1.65;margin:0;">{{ $step['d'] }}</p>
                @if($i < 3)
                <div style="position:absolute;right:-1px;top:50%;transform:translateY(-50%);width:2px;height:40px;background:linear-gradient(to bottom,transparent,var(--primary-color,#B7472A),transparent);display:none;" class="at-step-divider"></div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ═══ §5 À PROPOS / POURQUOI NOUS ═══════════════════════════ --}}
@if($aboutEnabled)
<section class="at-sec">
    <div class="at-w">
        <div class="at-why-grid">
            {{-- Image --}}
            <div class="at-why-img"
                 style="{{ $aboutImgUrl ? 'background-image:url('.e($aboutImgUrl).');' : 'background:linear-gradient(135deg,#E8DDD4,#1a1a1a);' }}">
                <div class="at-why-qcard">
                    <div style="display:flex; gap:8px; align-items:center; margin-bottom:6px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="var(--primary-color,#B7472A)" stroke="none"><path d="M9.4 7C5.9 7 3 9.9 3 13.4V19h6v-6H6c0-2 1.4-3.4 3.4-3.4V7Zm11.6 0c-3.5 0-6.4 2.9-6.4 6.4V19h6v-6h-3c0-2 1.4-3.4 3.4-3.4V7Z"/></svg>
                        <span style="font-weight:600; font-size:13px; color:var(--ink);">{{ $aboutTitle }}</span>
                    </div>
                    <div style="font-size:12px; color:var(--ink3);">— {{ $name }}, {{ $city }}</div>
                </div>
            </div>
            {{-- Texte --}}
            <div>
                <div class="at-ey">Pourquoi nous ?</div>
                <h2>{{ $aboutTitle }}</h2>
                <p style="color:var(--ink2); margin-top:16px; font-size:16.5px; line-height:1.65;">
                    {{ $aboutContent ?: 'On habite le coin, on connaît les maisons d\'ici, et on tient à notre réputation. Pas de sous-traitance, pas de surprise sur le devis, pas de client laissé sans nouvelles.' }}
                </p>
                <div class="at-benefits">
                    @php $bens = [
                        ['svg'=>'<path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>','t'=>'Rapide','d'=>'Devis sous 24h.'],
                        ['svg'=>'<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/>','t'=>'Garanti','d'=>'Décennale incluse.'],
                        ['svg'=>'<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76Z"/>','t'=>'Soigné','d'=>'Chantier propre.'],
                        ['svg'=>'<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>','t'=>'Humain','d'=>'Un seul interlocuteur.'],
                    ]; @endphp
                    @foreach($bens as $b)
                    <div class="at-benefit">
                        <div class="at-ben-ico">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $b['svg'] !!}</svg>
                        </div>
                        <div>
                            <div style="font-weight:700; font-size:15px;">{{ $b['t'] }}</div>
                            <div style="color:var(--ink3); font-size:13px; margin-top:2px;">{{ $b['d'] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
@endif


{{-- ═══ §5b ÉCOLOGIE + FINANCEMENT (section fusionnée) ═══════ --}}
@php
    $ecoEnabled = $homeConfig['ecology']['enabled']   ?? false;
    $finEnabled = $homeConfig['financing']['enabled'] ?? false;
    $bothActive = $ecoEnabled && $finEnabled;
@endphp
@if($ecoEnabled || $finEnabled)
<section class="at-sec" style="background:var(--bgs);">
    <div class="at-w">
        <div style="display:grid;grid-template-columns:{{ $bothActive ? '1fr 1fr' : '1fr' }};gap:24px;align-items:stretch;">

            {{-- ▌ PANNEAU ÉCOLOGIE --}}
            @if($ecoEnabled)
            @php $ecoBgImg = $homeConfig['ecology']['image'] ?? null; @endphp
            <div style="position:relative;border-radius:20px;overflow:hidden;display:flex;flex-direction:column;justify-content:space-between;">
                <div style="position:absolute;inset:0;{{ $ecoBgImg ? 'background-image:url('.asset(ltrim($ecoBgImg,'/')).'); background-size:cover; background-position:center;' : 'background:linear-gradient(145deg,#0d2016 0%,#14331e 60%,#0F2A18 100%);' }}"></div>
                <div style="position:absolute;inset:0;background:linear-gradient(160deg,rgba(8,22,13,.55) 0%,rgba(8,22,13,.82) 100%);"></div>
                <div style="position:absolute;bottom:-20px;right:-10px;font-size:200px;opacity:.07;line-height:1;pointer-events:none;user-select:none;">🌿</div>
                <div style="position:relative;z-index:1;padding:44px 40px 44px;">
                    <div style="display:inline-flex;align-items:center;gap:7px;font-size:10.5px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#4ADE80;margin-bottom:16px;">
                        <span style="width:18px;height:2px;background:#4ADE80;border-radius:2px;display:block;flex-shrink:0;"></span>
                        Engagement environnemental
                    </div>
                    <h3 style="font-family:'Fraunces',serif;color:#fff;font-size:clamp(21px,1.9vw,28px);font-weight:700;letter-spacing:-.02em;line-height:1.2;margin:0 0 18px;">Notre Engagement Écologique</h3>
                    <p style="color:rgba(255,255,255,.7);font-size:14.5px;line-height:1.72;margin:0 0 28px;">
                        Nous privilégions des matériaux respectueux de l'environnement : tuiles recyclables, isolants naturels et peintures écologiques. Nos travaux d'isolation et de rénovation visent à réduire les consommations d'énergie et à améliorer la performance thermique de votre logement. En choisissant notre entreprise, vous contribuez à une rénovation durable et responsable.
                    </p>
                    <div style="display:flex;flex-wrap:wrap;gap:10px;">
                        <div style="display:inline-flex;align-items:center;gap:7px;padding:9px 16px;background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.25);border-radius:999px;font-size:13px;font-weight:600;color:#fff;">♻️ Matériaux recyclables</div>
                        <div style="display:inline-flex;align-items:center;gap:7px;padding:9px 16px;background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.25);border-radius:999px;font-size:13px;font-weight:600;color:#fff;">🌱 Isolants naturels</div>
                        <div style="display:inline-flex;align-items:center;gap:7px;padding:9px 16px;background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.25);border-radius:999px;font-size:13px;font-weight:600;color:#fff;">🏠 Éco-rénovation</div>
                    </div>
                </div>
            </div>
            @endif

            {{-- ▌ PANNEAU FINANCEMENT --}}
            @if($finEnabled)
            @php $finBgImg = $homeConfig['financing']['image'] ?? null; @endphp
            <div style="position:relative;border-radius:20px;overflow:hidden;display:flex;flex-direction:column;justify-content:space-between;">
                <div style="position:absolute;inset:0;{{ $finBgImg ? 'background-image:url('.asset(ltrim($finBgImg,'/')).'); background-size:cover; background-position:center;' : 'background:linear-gradient(145deg,#0c1628 0%,#162040 60%,#0F1A35 100%);' }}"></div>
                <div style="position:absolute;inset:0;background:linear-gradient(160deg,rgba(8,14,32,.55) 0%,rgba(8,14,32,.82) 100%);"></div>
                <div style="position:absolute;bottom:-20px;right:-10px;font-size:200px;opacity:.07;line-height:1;pointer-events:none;user-select:none;">💡</div>
                <div style="position:relative;z-index:1;padding:44px 40px 44px;">
                    <div style="display:inline-flex;align-items:center;gap:7px;font-size:10.5px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#60A5FA;margin-bottom:16px;">
                        <span style="width:18px;height:2px;background:#60A5FA;border-radius:2px;display:block;flex-shrink:0;"></span>
                        Financement &amp; Aides
                    </div>
                    <h3 style="font-family:'Fraunces',serif;color:#fff;font-size:clamp(21px,1.9vw,28px);font-weight:700;letter-spacing:-.02em;line-height:1.2;margin:0 0 18px;">Aides et Financements Disponibles</h3>
                    <p style="color:rgba(255,255,255,.7);font-size:14.5px;line-height:1.72;margin:0 0 22px;">
                        Grâce aux dispositifs comme MaPrimeRénov', l'Éco-prêt à taux zéro (Éco-PTZ), ou encore les Certificats d'Économie d'Énergie (CEE), vos travaux de rénovation énergétique peuvent être partiellement financés. Nous vous accompagnons dans vos démarches administratives pour faciliter l'obtention des aides et subventions disponibles.
                    </p>
                    <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:18px;">
                        <div style="display:flex;align-items:center;gap:12px;padding:13px 16px;background:rgba(255,255,255,.07);border:1px solid rgba(96,165,250,.2);border-radius:12px;">
                            <span style="font-size:22px;flex-shrink:0;">🏠</span>
                            <div><div style="font-weight:700;font-size:14px;color:#fff;">MaPrimeRénov'</div><div style="font-size:12.5px;color:rgba(255,255,255,.5);margin-top:2px;">Aide de l'État pour la rénovation</div></div>
                        </div>
                        <div style="display:flex;align-items:center;gap:12px;padding:13px 16px;background:rgba(255,255,255,.07);border:1px solid rgba(96,165,250,.2);border-radius:12px;">
                            <span style="font-size:22px;flex-shrink:0;">⚡</span>
                            <div><div style="font-weight:700;font-size:14px;color:#fff;">Éco-PTZ &amp; Certificats CEE</div><div style="font-size:12.5px;color:rgba(255,255,255,.5);margin-top:2px;">Économies d'énergie certifiées</div></div>
                        </div>
                    </div>
                    <div style="padding:13px 16px;background:rgba(96,165,250,.1);border:1px solid rgba(96,165,250,.22);border-radius:12px;font-size:13.5px;color:rgba(255,255,255,.78);line-height:1.58;">
                        💡 Nos experts vous conseillent gratuitement sur les solutions les plus avantageuses.
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</section>
@endif


{{-- ═══ §6 RÉALISATIONS ════════════════════════════════════════ --}}
@if(($secPortfolio['enabled'] ?? true) && !empty($displayedPortfolio))
<section class="at-sec" style="background:var(--bgs);">
    <div class="at-w">
        <div class="at-svc-hdr">
            <div class="at-sh" style="margin-bottom:0;">
                <div class="at-ey">Réalisations</div>
                <h2>{{ $secPortfolio['title'] ?? 'Des chantiers récents, dans votre région' }}</h2>
                <p>Photos prises sur place, jamais de stock.</p>
            </div>
            <a href="{{ route('portfolio.index') }}" class="at-btn at-btn-g">
                Toutes nos réalisations
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            </a>
        </div>
        <div class="at-real-grid">
            @foreach($displayedPortfolio as $item)
            @php
                $imgs   = $item['images'] ?? [];
                $pImg   = null;
                if (is_array($imgs) && count($imgs) > 0) {
                    $raw  = $imgs[0];
                    $pImg = strpos($raw,'http')===0 ? $raw : asset(ltrim($raw,'/'));
                }
                $pSlug = $item['slug'] ?? \Illuminate\Support\Str::slug($item['title'] ?? 'realisation');
                $pLoc  = $item['city'] ?? $item['location'] ?? '';
            @endphp
            <a href="{{ route('portfolio.show', $pSlug) }}" class="at-card" style="overflow:hidden;cursor:pointer;display:block;transition:transform .15s, box-shadow .15s;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='var(--s2)'" onmouseout="this.style.transform='none';this.style.boxShadow=''">
                <div class="at-photo" style="height:240px; {{ $pImg ? 'background-image:url('.e($pImg).');' : 'background:linear-gradient(135deg,var(--ps),var(--bgs));' }}">
                    @if($pImg)<img src="{{ $pImg }}" alt="{{ $item['title']??'' }}" style="width:100%;height:100%;object-fit:cover;display:block;" loading="lazy">@endif
                    @if($pLoc)<div class="at-real-tag">📍 {{ $pLoc }}</div>@endif
                </div>
                <div style="padding:18px 20px;">
                    <h3 style="font-size:17px; margin-bottom:6px;">{{ $item['title'] ?? 'Réalisation' }}</h3>
                    <p style="color:var(--ink3); font-size:13.5px; line-height:1.55;">{{ Str::limit($item['description'] ?? '', 100) }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif


{{-- ═══ §7 AVIS CLIENTS ═══════════════════════════════════════ --}}
@if(($secReviews['enabled'] ?? true) && $displayedReviews->count() > 0)
<section class="at-sec">
    <div class="at-w">
        <div class="at-sh" style="text-align:center; margin-inline:auto;">
            <div class="at-ey" style="justify-content:center;">Ce qu'on en dit</div>
            <h2>{{ $secReviews['title'] ?? $reviewCount.' avis · '.number_format($ratingVal,1,',','').' / 5 sur Google' }}</h2>
        </div>
        <div class="at-avis-grid">
            @foreach($displayedReviews as $rev)
            <div class="at-card at-avis-card">
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <div style="display:flex; color:#F5A623; gap:2px;">
                        @for($s=1;$s<=(int)($rev->rating??5);$s++)<svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>@endfor
                    </div>
                    <svg width="19" height="19" viewBox="0 0 24 24"><path d="M22.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.4h5.9c-.3 1.4-1 2.5-2.2 3.3v2.7h3.5c2-1.9 3.3-4.7 3.3-8.1Z" fill="#4285F4"/><path d="M12 23c3 0 5.5-1 7.3-2.7l-3.5-2.7c-1 .7-2.2 1-3.7 1-2.9 0-5.3-1.9-6.2-4.5H2.3v2.8C4.1 20.5 7.8 23 12 23Z" fill="#34A853"/><path d="M5.8 14.1a6.7 6.7 0 0 1 0-4.2V7.1H2.3a11 11 0 0 0 0 9.8l3.5-2.8Z" fill="#FBBC05"/><path d="M12 5.4c1.6 0 3.1.6 4.2 1.7l3.1-3.1A11 11 0 0 0 12 1C7.8 1 4.1 3.5 2.3 7.1l3.5 2.8C6.7 7.3 9.1 5.4 12 5.4Z" fill="#EA4335"/></svg>
                </div>
                <p style="font-size:14.5px; line-height:1.65; color:var(--ink2); flex:1;">« {{ $rev->review_text ?? '' }} »</p>
                <div class="at-avis-foot">
                    <div class="at-avis-av">{{ mb_strtoupper(mb_substr($rev->author_name??'C',0,1)) }}</div>
                    <div>
                        <div style="font-weight:600; font-size:13.5px;">{{ $rev->author_name ?? 'Client' }}</div>
                        <div style="font-size:12px; color:var(--ink3);">{{ $rev->review_date ? \Carbon\Carbon::parse($rev->review_date)->translatedFormat('d M Y') : '' }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif


{{-- ═══ §7b PARTENAIRES ════════════════════════════════════════ --}}
@php
    $partEnabled  = (bool)($homeConfig['partners']['enabled']  ?? false);
    $partTitle    = is_string($homeConfig['partners']['title']    ?? '') ? ($homeConfig['partners']['title']    ?? 'Nos partenaires de confiance') : 'Nos partenaires de confiance';
    $partIntro    = is_string($homeConfig['partners']['intro']    ?? '') ? ($homeConfig['partners']['intro']    ?? '') : '';
    $partLogos    = is_array($homeConfig['partners']['logos']    ?? []) ? ($homeConfig['partners']['logos']    ?? []) : [];
    $partFeatured = is_array($homeConfig['partners']['featured'] ?? null) ? ($homeConfig['partners']['featured'] ?? null) : null;
@endphp
@if($partEnabled && (count($partLogos) > 0 || !empty($partFeatured)))
<section class="at-sec tight" style="background:var(--bgs);">
    <div class="at-w">
        <div class="at-sh" style="text-align:center; margin-inline:auto; margin-bottom:36px;">
            <div class="at-ey" style="justify-content:center;">Partenaires</div>
            <h2>{{ $partTitle }}</h2>
            @if($partIntro)<p style="color:var(--ink3);font-size:16px;margin-top:10px;max-width:580px;margin-left:auto;margin-right:auto;">{{ $partIntro }}</p>@endif
        </div>
        @php
            $validLogos = array_filter(is_array($partLogos) ? $partLogos : [], function($logo) {
                $u = is_array($logo) ? trim((string)($logo['logo'] ?? $logo['url'] ?? $logo['image'] ?? '')) : trim((string)$logo);
                return !empty($u);
            });
            $pf = is_array($partFeatured) ? $partFeatured : [];
            $pfEnabled = (bool)($pf['enabled'] ?? false);
            $pfHasContent = !empty($pf['title']) || !empty($pf['body']) || !empty($pf['image']);
        @endphp
        @if(count($validLogos) > 0)
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px;margin-bottom:{{ ($pfEnabled && $pfHasContent) ? '40px' : '0' }};">
            @foreach($validLogos as $logo)
            @php
                $logoUrl  = is_array($logo) ? trim((string)($logo['logo'] ?? $logo['url'] ?? $logo['image'] ?? '')) : trim((string)$logo);
                $logoAlt  = is_array($logo) ? (string)($logo['name'] ?? $logo['alt'] ?? 'Partenaire') : 'Partenaire';
                $logoLink = is_array($logo) ? trim((string)($logo['url'] ?? '')) : '';
                $logoSrc  = strpos($logoUrl,'http')===0 ? $logoUrl : asset(ltrim($logoUrl,'/'));
                $logoBox  = 'width:100%;height:88px;background:#fff;border:1px solid rgba(30,20,10,.07);border-radius:14px;display:flex;align-items:center;justify-content:center;padding:14px 16px;box-shadow:0 1px 4px rgba(30,20,10,.05);transition:box-shadow .2s,border-color .2s;';
            @endphp
            @if($logoLink)
            <a href="{{ $logoLink }}" target="_blank" rel="noopener" style="text-decoration:none;display:block;" onmouseover="this.firstElementChild.style.boxShadow='0 6px 20px rgba(30,20,10,.1)';this.firstElementChild.style.borderColor='var(--primary-color,#B7472A)'" onmouseout="this.firstElementChild.style.boxShadow='0 1px 4px rgba(30,20,10,.05)';this.firstElementChild.style.borderColor='rgba(30,20,10,.07)'">
                <div style="{{ $logoBox }}"><img src="{{ $logoSrc }}" alt="{{ $logoAlt }}" style="max-height:52px;max-width:110px;width:auto;height:auto;object-fit:contain;" loading="lazy" onerror="this.closest('a').style.display='none'"></div>
            </a>
            @else
            <div style="{{ $logoBox }}" onmouseover="this.style.boxShadow='0 6px 20px rgba(30,20,10,.1)';this.style.borderColor='var(--primary-color,#B7472A)'" onmouseout="this.style.boxShadow='0 1px 4px rgba(30,20,10,.05)';this.style.borderColor='rgba(30,20,10,.07)'">
                <img src="{{ $logoSrc }}" alt="{{ $logoAlt }}" style="max-height:52px;max-width:110px;width:auto;height:auto;object-fit:contain;" loading="lazy" onerror="this.parentElement.style.display='none'">
            </div>
            @endif
            @endforeach
        </div>
        @endif
        @php
            $pfImg = !empty($pf['image']) ? (strpos($pf['image'],'http')===0 ? $pf['image'] : asset(ltrim($pf['image'],'/'))) : null;
        @endphp
        @if($pfEnabled && $pfHasContent)
        <div style="background:#fff;border:1px solid var(--ln);border-radius:20px;padding:32px;display:grid;grid-template-columns:{{ $pfImg ? '140px 1fr' : '1fr' }};gap:28px;align-items:center;max-width:680px;margin:0 auto;box-shadow:var(--s2);">
            @if($pfImg)
            <img src="{{ $pfImg }}" alt="{{ $pf['title'] ?? 'Partenaire' }}" style="width:140px;height:90px;object-fit:contain;border-radius:10px;background:var(--bgs);padding:8px;">
            @endif
            <div>
                @if(!empty($pf['title']))<div style="font-weight:700;font-size:18px;color:var(--ink);margin-bottom:4px;">{{ $pf['title'] }}</div>@endif
                @if(!empty($pf['subtitle']))<div style="font-size:13px;color:var(--p);font-weight:600;margin-bottom:10px;">{{ $pf['subtitle'] }}</div>@endif
                @if(!empty($pf['body']))<p style="color:var(--ink3);font-size:14.5px;line-height:1.6;margin:0;">{{ $pf['body'] }}</p>@endif
                @if(!empty($pf['link_url']))<a href="{{ $pf['link_url'] }}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:6px;margin-top:14px;font-size:13.5px;font-weight:600;color:var(--p);text-decoration:none;">{{ $pf['link_label'] ?? 'Voir le partenaire' }} <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>@endif
            </div>
        </div>
        @endif
    </div>
</section>
@endif


{{-- ═══ §8 ZONE D'INTERVENTION (villes + carte) ══════════════════════════════════════════════ --}}
@php
    $ziHasCities = isset($favoriteCities) && $favoriteCities->count() > 0;
    $ziHasMap    = !empty($departmentsMap['show']);
@endphp
@if($ziHasCities || $ziHasMap)
@if($ziHasMap)
@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
@endpush
@endif
<style>
.zi-grid{display:grid;grid-template-columns:1fr 2fr;gap:2rem;align-items:start;}
@media(max-width:800px){.zi-grid{grid-template-columns:1fr;}}
.zi-pill{display:inline-flex;align-items:center;gap:.5rem;padding:.45rem 1rem;border-radius:999px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.82);text-decoration:none;font-size:.875rem;font-weight:600;transition:background .18s,border-color .18s;}
.zi-pill:hover{background:rgba(255,255,255,.16);border-color:rgba(255,255,255,.28);}
.zi-dept-li{border-radius:1rem;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);padding:1rem;}
.zi-dept-link{display:flex;align-items:center;justify-content:space-between;gap:.5rem;font-weight:600;color:#fff;text-decoration:none;font-size:.9375rem;transition:color .18s;}
.zi-dept-link:hover{color:var(--primary-color,#B7472A);}
.zi-dept-badge{display:inline-flex;align-items:center;justify-content:center;width:2.25rem;height:2.25rem;border-radius:.5rem;color:#fff;font-size:.75rem;font-weight:800;margin-right:.75rem;flex-shrink:0;background:linear-gradient(135deg,var(--primary-color,#B7472A),var(--secondary-color,#D4572C));}
.zi-city-chip{display:inline-block;border-radius:.5rem;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);padding:.3rem .7rem;font-size:.8125rem;font-weight:500;color:rgba(255,255,255,.72);text-decoration:none;transition:border-color .18s,background .18s;}
.zi-city-chip:hover{background:rgba(255,255,255,.12);border-color:rgba(255,255,255,.22);}
.zi-map-wrap{border-radius:1.25rem;overflow:hidden;border:1px solid rgba(255,255,255,.12);height:460px;}
@media(max-width:800px){.zi-map-wrap{height:320px;}}
</style>
<section style="background:#1F1A14;padding:5rem 0;">
    <div class="at-w">
        {{-- Intro --}}
        <div style="max-width:700px;margin-bottom:2.5rem;">
            <div class="at-ey" style="color:rgba(255,255,255,.4);">Zone d'intervention</div>
            <h2 style="font-family:'Fraunces',serif;font-size:clamp(1.75rem,4vw,2.75rem);font-weight:700;color:#fff;line-height:1.2;margin:.75rem 0 1rem;">On intervient à {{ $city }} et dans un rayon de 30&nbsp;km.</h2>
            <p style="color:rgba(255,255,255,.58);font-size:1.0625rem;line-height:1.65;">Une demande hors zone&nbsp;? Appelez-nous, on regarde au cas par cas.</p>
        </div>

        {{-- Villes --}}
        @if($ziHasCities)
        <div style="display:flex;flex-wrap:wrap;gap:.625rem;margin-bottom:{{ $ziHasMap ? '3.5rem' : '0' }};">
            @foreach($favoriteCities as $fc)
            <a href="{{ route('ads.index') }}?city={{ $fc->slug }}" class="zi-pill">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="var(--primary-color,#B7472A)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                {{ $fc->name }}
            </a>
            @endforeach
        </div>
        @endif

        {{-- Carte + départements --}}
        @if($ziHasMap)
        @php
            $ziItems         = $departmentsMap['items'] ?? [];
            $ziGeoUrl        = $departmentsMap['geoJsonUrl'] ?? '';
            $ziUseCityCollapse = count($ziItems) > 1;
        @endphp
        <div style="border-top:1px solid rgba(255,255,255,.1);padding-top:3rem;">
            <div style="margin-bottom:2rem;">
                <div class="at-ey" style="color:rgba(255,255,255,.4);">Nos départements d'intervention</div>
                <p style="color:rgba(255,255,255,.52);font-size:.9375rem;line-height:1.65;margin-top:.5rem;">
                    Nous sommes présents dans tous ces départements. Retrouvez ci-dessous nos secteurs d'intervention
                    @if($ziUseCityCollapse) ; déployez «&nbsp;Villes desservies&nbsp;» pour afficher les principales communes.
                    @else et les principales villes desservies. @endif
                </p>
            </div>
            <div class="zi-grid">
                {{-- Liste départements --}}
                <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:1rem;">
                    @foreach($ziItems as $row)
                    <li class="zi-dept-li">
                        <a href="{{ $row['url'] }}" class="zi-dept-link">
                            <span style="display:flex;align-items:center;min-width:0;">
                                <span class="zi-dept-badge">{{ $row['code'] }}</span>
                                <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $row['name'] }}</span>
                            </span>
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.3)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                        @if(!empty($row['cities']) && is_array($row['cities']))
                            @if($ziUseCityCollapse)
                            <details style="margin-top:.875rem;border:1px solid rgba(255,255,255,.08);border-radius:.75rem;background:rgba(255,255,255,.03);">
                                <summary style="cursor:pointer;list-style:none;padding:.6rem .875rem;font-size:.8125rem;font-weight:600;color:rgba(255,255,255,.55);">Villes desservies</summary>
                                <div style="padding:.625rem .875rem .75rem;border-top:1px solid rgba(255,255,255,.06);">
                                    <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
                                        @foreach($row['cities'] as $ziCity)
                                        <a href="{{ $ziCity['url'] }}" class="zi-city-chip">{{ $ziCity['name'] }}</a>
                                        @endforeach
                                    </div>
                                </div>
                            </details>
                            @else
                            <div style="margin-top:.875rem;display:flex;flex-wrap:wrap;gap:.5rem;">
                                @foreach($row['cities'] as $ziCity)
                                <a href="{{ $ziCity['url'] }}" class="zi-city-chip">{{ $ziCity['name'] }}</a>
                                @endforeach
                            </div>
                            @endif
                        @endif
                    </li>
                    @endforeach
                </ul>

                {{-- Carte Leaflet --}}
                <div class="zi-map-wrap">
                    <div id="zi-map" style="width:100%;height:100%;" role="img" aria-label="Carte des départements d'intervention"></div>
                </div>
            </div>
        </div>
        @endif
    </div>
</section>
@if($ziHasMap)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
(function(){
    var items=@json($departmentsMap['items']??[]);
    var geoUrl=@json($departmentsMap['geoJsonUrl']??'');
    var primary=getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim()||'#B7472A';
    var secondary=getComputedStyle(document.documentElement).getPropertyValue('--secondary-color').trim()||'#D4572C';
    function normalizeCode(c){c=String(c||'').trim().toUpperCase();if(c==='2A'||c==='2B')return c;if(/^\d+$/.test(c))return c.padStart(2,'0');return c;}
    var highlight=new Set(items.map(function(it){return normalizeCode(it.code);}));
    var urlByCode={};items.forEach(function(it){urlByCode[normalizeCode(it.code)]=it.url;});
    var map=L.map('zi-map',{scrollWheelZoom:false,zoomControl:true,attributionControl:false});
    fetch(geoUrl,{credentials:'same-origin'})
    .then(function(r){return r.json();})
    .then(function(geojson){
        var layer=L.geoJSON(geojson,{
            style:function(feat){
                var code=normalizeCode(feat.properties&&feat.properties.code);
                var on=highlight.has(code);
                return{color:on?primary:'#374151',weight:on?2:0.5,fillColor:on?secondary:'#1f2937',fillOpacity:on?0.7:0.4};
            },
            onEachFeature:function(feature,lyr){
                var nom=(feature.properties&&feature.properties.nom)?feature.properties.nom:'';
                var code=normalizeCode(feature.properties&&feature.properties.code);
                lyr.bindTooltip(nom+(code?' ('+code+')':''),{sticky:true});
                lyr.on('click',function(){if(highlight.has(code)&&urlByCode[code])window.location.href=urlByCode[code];});
            }
        });
        layer.addTo(map);
        var targetBounds=null;
        if(geojson.features&&geojson.features.length){
            geojson.features.forEach(function(feat){
                var code=normalizeCode(feat.properties&&feat.properties.code);
                if(!highlight.has(code))return;
                try{var b=L.geoJSON(feat).getBounds();if(b&&b.isValid())targetBounds=targetBounds?targetBounds.extend(b):b;}catch(e){}
            });
        }
        try{
            if(targetBounds&&targetBounds.isValid()){map.fitBounds(targetBounds,{padding:[32,32],maxZoom:highlight.size===1?10:9});}
            else{map.fitBounds(layer.getBounds(),{padding:[24,24]});}
        }catch(e){}
        setTimeout(function(){try{map.invalidateSize();}catch(e2){}},100);
        window.addEventListener('resize',function(){try{map.invalidateSize();}catch(e3){}});
    })
    .catch(function(){document.getElementById('zi-map').innerHTML='<div style="padding:2rem;text-align:center;color:#f87171;font-size:.875rem;">Impossible de charger la carte.</div>';});
})();
</script>
@endif
@endif


{{-- ═══ §9 FORMULAIRE CONTACT ════════════════════════════════ --}}
<section class="at-sec" id="at-contact" style="background:var(--bg);">
    <div class="at-w">
        <div class="at-cg">
            {{-- Infos contact --}}
            <div>
                <div class="at-ey">Demandez votre devis</div>
                <h2>Parlons de votre projet.</h2>
                <p style="color:var(--ink3); font-size:16.5px; margin-top:14px; line-height:1.65;">
                    Remplissez le formulaire ou appelez-nous. On vous rappelle dans les 24h, devis détaillé sans engagement.
                </p>
                <div style="display:flex; flex-direction:column; gap:14px; margin-top:28px;">
                    @if($phone)
                    <a href="tel:{{ $phoneRaw }}" class="at-ci">
                        <div class="at-ci-ico">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
                        </div>
                        <div>
                            <div class="at-ci-lbl">Téléphone</div>
                            <div class="at-ci-val">{{ $phone }}</div>
                        </div>
                    </a>
                    @endif
                    <div class="at-ci">
                        <div class="at-ci-ico">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        </div>
                        <div>
                            <div class="at-ci-lbl">Horaires</div>
                            <div class="at-ci-val" style="font-size:15px;">Lun–Sam · 8h – 19h</div>
                        </div>
                    </div>
                    <div class="at-ci">
                        <div class="at-ci-ico">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <div>
                            <div class="at-ci-lbl">Zone</div>
                            <div class="at-ci-val" style="font-size:15px;">{{ $city }} et 30 km autour</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Formulaire --}}
            <div class="at-card" style="padding:32px;">
                @if(session('success') || session('contact_success'))
                <div style="text-align:center; padding:20px 0;">
                    <div style="width:56px;height:56px;border-radius:50%;background:var(--ps);color:var(--p);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
                    </div>
                    <h3>Message envoyé !</h3>
                    <p style="color:var(--ink3); margin-top:10px;">On vous rappelle dans les 24h.</p>
                </div>
                @else
                <h3 style="font-size:21px; margin-bottom:6px;">Demande de devis gratuit</h3>
                <p style="color:var(--ink3); font-size:13.5px; margin-bottom:22px;">Réponse sous 24h · Sans engagement</p>

                @if($errors->any())
                <div style="padding:12px 16px; background:#FEF2F2; border:1px solid #FECACA; border-radius:10px; color:#DC2626; font-size:13.5px; margin-bottom:12px;">
                    @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
                </div>
                @endif

                <form action="{{ route('contact.send') }}" method="POST" enctype="multipart/form-data" class="at-form">
                    @csrf

                    <div class="at-frow">
                        <div class="at-field">
                            <label>Nom complet *</label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="Jean Dupont" required minlength="3">
                        </div>
                        <div class="at-field">
                            <label>Email *</label>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="jean@email.fr" required>
                        </div>
                    </div>
                    <div class="at-frow">
                        <div class="at-field">
                            <label>Téléphone *</label>
                            <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="06 12 34 56 78" required minlength="6">
                        </div>
                        <div class="at-field">
                            <label>Code postal *</label>
                            <input type="text" name="postal_code" value="{{ old('postal_code') }}" placeholder="21800" required minlength="4" maxlength="10">
                        </div>
                    </div>
                    <div class="at-frow">
                        <div class="at-field">
                            <label>Ville *</label>
                            <input type="text" name="city" value="{{ old('city') }}" placeholder="Chevigny-Saint-Sauveur" required minlength="2">
                        </div>
                        <div class="at-field">
                            <label>Quand vous rappeler ? *</label>
                            <select name="callback_time" required>
                                <option value="">Choisissez un créneau</option>
                                <option value="matin" {{ old('callback_time')=='matin'?'selected':'' }}>Matin (9h – 12h)</option>
                                <option value="apres-midi" {{ old('callback_time')=='apres-midi'?'selected':'' }}>Après-midi (14h – 17h)</option>
                                <option value="soir" {{ old('callback_time')=='soir'?'selected':'' }}>Soir (17h – 19h)</option>
                                <option value="flexible" {{ old('callback_time')=='flexible'?'selected':'' }}>Flexible</option>
                            </select>
                        </div>
                    </div>
                    @if(!empty($svcList))
                    <div class="at-field">
                        <label>Service qui vous intéresse *</label>
                        <select name="service_interest" required>
                            <option value="">Sélectionnez un service</option>
                            @foreach($svcList as $svc)
                            <option value="{{ $svc['name']??'' }}" {{ old('service_interest')==($svc['name']??'') ? 'selected' : '' }}>{{ $svc['name'] ?? '' }}</option>
                            @endforeach
                            <option value="Autre" {{ old('service_interest')=='Autre'?'selected':'' }}>Autre</option>
                        </select>
                    </div>
                    @endif
                    <div class="at-field">
                        <label>Sujet *</label>
                        <input type="text" name="subject" value="{{ old('subject') }}" placeholder="Résumé de votre demande" required minlength="6">
                    </div>
                    <div class="at-field">
                        <label>Message *</label>
                        <textarea name="message" rows="4" placeholder="Décrivez votre projet en détail…" required minlength="6">{{ old('message') }}</textarea>
                    </div>
                    <div class="at-field">
                        <label>Photos (optionnel)</label>
                        <input type="file" name="attachments[]" accept="image/jpeg,image/png,image/jpg,image/webp" multiple style="padding:.55rem .875rem;border:1.5px dashed rgba(30,20,10,.15);border-radius:.625rem;font-size:.875rem;color:var(--ink3);background:var(--bgs);cursor:pointer;width:100%;box-sizing:border-box;">
                    </div>
                    <input type="hidden" name="source" value="homepage">
                    <button type="submit" class="at-submit">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        Envoyer ma demande
                    </button>
                    <p style="font-size:11.5px; color:var(--ink3); text-align:center; margin-top:8px;">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:middle;margin-right:3px;"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Vos données sont protégées · Réponse sous 24h
                    </p>
                </form>
                @endif
            </div>
        </div>
    </div>
</section>


{{-- ═══ §10 FAQ ════════════════════════════════════════════════ --}}
@if(!empty($faqs))
<section class="at-sec" style="background:var(--bgs);">
    <div class="at-w">
        <div class="at-sh" style="text-align:center; margin-inline:auto;">
            <div class="at-ey" style="justify-content:center;">Questions fréquentes</div>
            <h2>On répond à tout.</h2>
        </div>
        <div style="max-width:760px; margin:0 auto; display:flex; flex-direction:column; gap:10px;">
            @foreach(array_slice($faqs,0,7) as $faq)
            <details class="at-card at-faq-item">
                <summary>
                    {{ $faq['question'] ?? $faq['q'] ?? '' }}
                    <div class="at-faq-icon">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    </div>
                </summary>
                <div class="at-faq-body">{{ $faq['answer'] ?? $faq['r'] ?? $faq['a'] ?? '' }}</div>
            </details>
            @endforeach
        </div>
    </div>
</section>
@endif


{{-- ═══ §11 CTA STRIP ══════════════════════════════════════════ --}}
@if($secCta['enabled'] ?? true)
<section class="at-cta-strip">
    <div class="at-w">
        <div class="at-cta-inner">
            <div style="max-width:540px;">
                <h2 style="color:#fff; font-family:var(--fd);">{!! nl2br(e($secCta['title'] ?? "Un projet ? Un devis sous 24h, c'est promis.")) !!}</h2>
                <p style="color:rgba(255,255,255,.65); margin-top:12px; font-size:16px; line-height:1.6;">
                    Échange rapide par téléphone, déplacement gratuit, devis détaillé sans engagement.
                </p>
            </div>
            <div style="display:flex; flex-wrap:wrap; gap:12px;">
                @if($phone)
                <a href="tel:{{ $phoneRaw }}"
                   class="at-btn at-btn-lg"
                   style="background:var(--p);color:var(--pf);">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
                    {{ $phone }}
                </a>
                @endif
                <a href="#at-contact"
                   class="at-btn at-btn-lg"
                   style="background:transparent; color:#fff; border:1.5px solid rgba(255,255,255,.28);">
                    Demander un devis
                </a>
            </div>
        </div>
    </div>
</section>
@endif

</div>{{-- .at --}}
