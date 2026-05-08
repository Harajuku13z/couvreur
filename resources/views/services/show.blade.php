@extends('layouts.app')

@section('title', $pageTitle)
@section('description', $pageDescription)
@section('keywords', $service['meta_keywords'] ?? '')

@push('head')
<style>
/* ═══ BASE ═══ */
.sp { font-family: inherit; }

/* ── Hero ── */
.sp-hero {
    position: relative; min-height: 65vh; display: flex;
    align-items: flex-end; overflow: hidden;
}
.sp-hero-bg {
    position: absolute; inset: 0;
    background: linear-gradient(140deg, #071c10, #0d3b22 50%, #1a5c35);
}
.sp-hero-bg img {
    width: 100%; height: 100%; object-fit: cover;
    object-position: center 30%;
}
.sp-hero-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(5,20,10,.92) 0%, rgba(5,20,10,.55) 45%, rgba(5,20,10,.15) 100%);
}
.sp-hero-wave {
    position: absolute; bottom: 0; left: 0; right: 0;
}

/* ── Layout ── */
.sp-layout {
    display: flex; flex-direction: column; gap: 2rem;
    max-width: 1160px; margin: 0 auto;
    padding: 2.5rem 1.5rem 4rem;
}
@media(min-width:1024px){
    .sp-layout { flex-direction: row; align-items: flex-start; gap: 2.5rem; padding: 3rem 2.5rem 5rem; }
    .sp-main { flex: 1; min-width: 0; }
    .sp-sidebar { width: 320px; flex-shrink: 0; position: sticky; top: 90px; }
}

/* ── Content blocks ── */
.sp-card {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 16px;
    padding: 2rem 2rem; margin-bottom: 1.5rem;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.sp-card:last-child { margin-bottom: 0; }

/* ── Prose content ── force dark text even when html.dark is active */
.sp-prose { color: #374151 !important; line-height: 1.8; }
.sp-prose h2 { font-size: 1.35rem; font-weight: 800; color: #111827 !important; margin: 1.75rem 0 .75rem; }
.sp-prose h3 { font-size: 1.1rem; font-weight: 700; color: #111827 !important; margin: 1.4rem 0 .6rem; }
.sp-prose p { margin-bottom: 1rem; font-size: .975rem; color: #374151 !important; }
.sp-prose ul { padding-left: 1.25rem; margin-bottom: 1rem; }
.sp-prose ul li { margin-bottom: .4rem; font-size: .975rem; color: #374151 !important; }
.sp-prose strong { color: #111827 !important; }
.sp-prose a { color: inherit !important; }
.sp-prose img, .sp-prose video, .sp-prose iframe { max-width:100%!important; height:auto; border-radius: 10px; }
.sp-prose table { width:100%; border-collapse:collapse; font-size:.9rem; color: #374151 !important; }
.sp-prose td, .sp-prose th { padding:.5rem .75rem; border:1px solid #e5e7eb; word-break:break-word; color: #374151 !important; }
/* reset Tailwind prose dark-mode overrides */
.sp-prose, .sp-prose * { --tw-prose-body: #374151; --tw-prose-headings: #111827; --tw-prose-bold: #111827; --tw-prose-links: #374151; }

/* ── Forcer 1 colonne dans le contenu IA (neutralise les grids générés) ── */
.sp-prose div[class*="grid"],
.sp-prose div[class*="flex"],
.sp-prose div[class*="columns"] {
    display: block !important;
}
.sp-prose div[class*="grid"] > *,
.sp-prose div[class*="flex"] > *,
.sp-prose div[class*="columns"] > * {
    display: block !important;
    width: 100% !important;
    margin-bottom: 1.25rem;
}
/* Masquer les blocs "Pourquoi choisir" et "Financement" générés par l'IA
   (déjà présents dans nos blocs dédiés plus bas dans la page) */
.sp-prose div[class*="bg-green"],
.sp-prose div[class*="bg-yellow"],
.sp-prose div[class*="bg-amber"],
.sp-prose div[class*="bg-emerald"],
.sp-prose div[class*="border-l-4"],
.sp-prose div[class*="rounded-xl"] {
    background: transparent !important;
    border: none !important;
    padding: 0 !important;
    border-left: none !important;
}

/* ── Styles contenu IA nouveau template colonne unique ── force dark text vs html.dark */
.ai-content-single { color: #374151 !important; line-height: 1.8; }
.ai-content-single p { margin-bottom: 1.1rem; font-size: .975rem; color: #374151 !important; }
.ai-content-single h2, .ai-content-single h3, .ai-content-single h4 { color: #111827 !important; }
.ai-intro { font-size: 1.05rem !important; color: #1f2937 !important; font-weight: 500; }
.ai-garantie {
    border-left: 3px solid var(--primary-color);
    padding: 1rem 1.25rem; margin: 1.75rem 0;
    background: #f0fdf4; border-radius: 0 10px 10px 0;
}
.ai-garantie h3 { font-size: 1rem; font-weight: 700; color: #111827 !important; margin: 0 0 .4rem; }
.ai-garantie p { font-size: .9rem; color: #374151 !important; margin: 0; }
.ai-h3-prestations { font-size: 1.15rem; font-weight: 800; color: #111827 !important; margin: 1.75rem 0 1rem; border-bottom: 2px solid #e5e7eb; padding-bottom: .5rem; }
.ai-prestations-list { list-style: none; padding: 0; margin: 0 0 1.5rem; display: flex; flex-direction: column; gap: .6rem; }
.ai-prestations-list li { display: flex; align-items: flex-start; gap: .65rem; font-size: .925rem; color: #374151 !important; line-height: 1.5; }
.ai-prestations-list li i { color: var(--primary-color); font-size: .8rem; flex-shrink: 0; margin-top: .2rem; }
.ai-faq-block { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.5rem; margin-top: 1.5rem; }
.ai-faq-block h4 { font-size: 1rem; font-weight: 800; color: #111827 !important; margin: 0 0 1rem; }
.ai-faq-block details { border-bottom: 1px solid #e5e7eb; }
.ai-faq-block details:last-child { border-bottom: none; }
.ai-faq-block summary { padding: .7rem 0; cursor: pointer; font-weight: 600; font-size: .875rem; color: #1f2937; list-style: none; }
.ai-faq-block summary::-webkit-details-marker { display: none; }
.ai-faq-block details[open] summary { color: var(--primary-color); }
.ai-faq-block .faq-answer { font-size: .875rem; color: #6b7280; padding: 0 0 .75rem; line-height: 1.65; }

/* ── Avantages ── single col */
.sp-adv-list { display: flex; flex-direction: column; gap: .85rem; }
.sp-adv-item {
    display: flex; align-items: flex-start; gap: 1rem;
    padding: 1rem 1.1rem; border-radius: 12px;
    border: 1px solid #e5e7eb; background: #f9fafb;
    transition: border-color .2s ease, background .2s ease;
}
.sp-adv-item:hover { border-color: var(--primary-color); background: #fff; }
.sp-adv-icon {
    width: 2.4rem; height: 2.4rem; border-radius: 10px; flex-shrink: 0;
    background: rgba(var(--primary-color-rgb, 34,197,94),.12);
    display: flex; align-items: center; justify-content: center;
    font-size: .95rem; color: var(--primary-color);
}
.sp-adv-title { font-weight: 700; font-size: .9rem; color: #111827; margin-bottom: .2rem; }
.sp-adv-desc { font-size: .825rem; color: #6b7280; line-height: 1.5; }

/* ── CTA band ── */
.sp-cta-band {
    border-radius: 16px; padding: 2rem 2rem; color: #fff;
    background: linear-gradient(135deg, #071c10 0%, #0d3b22 60%, #1a5c35 100%);
    text-align: center; margin-bottom: 1.5rem;
}
.sp-cta-band h3 { font-size: 1.35rem; font-weight: 800; margin-bottom: .6rem; }
.sp-cta-band p { font-size: .9rem; color: rgba(255,255,255,.72); margin-bottom: 1.5rem; }
.sp-cta-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
    background: #fff; font-weight: 800; font-size: .95rem;
    padding: .85rem 2rem; border-radius: 50px;
    text-decoration: none !important; transition: all .2s ease;
    color: var(--primary-color) !important;
    box-shadow: 0 4px 14px rgba(0,0,0,.2);
}
.sp-cta-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.25); }
.sp-cta-phone {
    display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
    border: 1.5px solid rgba(255,255,255,.3); color: #fff !important;
    font-weight: 600; font-size: .9rem; padding: .8rem 1.75rem;
    border-radius: 50px; text-decoration: none !important; margin-top: .75rem;
    transition: all .2s ease;
}
.sp-cta-phone:hover { border-color: rgba(255,255,255,.7); background: rgba(255,255,255,.07); }

/* ── Avis ── */
.sp-review {
    border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.1rem 1.25rem;
    margin-bottom: .85rem; background: #f9fafb;
}
.sp-review:last-child { margin-bottom: 0; }
.sp-review-stars { display:flex; gap:.2rem; margin-bottom:.5rem; }
.sp-review-stars i { color: #f59e0b; font-size: .75rem; }
.sp-review-text { font-size: .875rem; color: #374151; font-style: italic; line-height: 1.65; margin-bottom: .65rem; }
.sp-review-author { font-size: .775rem; font-weight: 700; color: #111827; }
.sp-review-city { font-size: .75rem; color: #9ca3af; }

/* ══════════ SIDEBAR ══════════ */
.sb-block {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 16px;
    overflow: hidden; margin-bottom: 1.25rem; box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.sb-block:last-child { margin-bottom: 0; }

/* CTA box */
.sb-cta {
    background: linear-gradient(155deg, var(--primary-color), var(--secondary-color, #1a5c35));
    padding: 1.5rem 1.5rem; border-radius: 16px; color: #fff;
    margin-bottom: 1.25rem;
}
.sb-cta-title { font-size: 1.05rem; font-weight: 800; margin-bottom: .35rem; }
.sb-cta-sub { font-size: .8rem; color: rgba(255,255,255,.75); margin-bottom: 1.25rem; line-height: 1.55; }
.sb-cta-btn {
    display: block; width: 100%; text-align: center;
    background: #fff; color: var(--primary-color) !important;
    font-weight: 800; font-size: .9rem; padding: .8rem 1rem;
    border-radius: 50px; text-decoration: none !important;
    box-shadow: 0 3px 12px rgba(0,0,0,.18); transition: all .2s ease;
}
.sb-cta-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(0,0,0,.22); }
.sb-cta-microcopy { font-size: .73rem; color: rgba(255,255,255,.55); text-align: center; margin-top: .6rem; }

/* Phone */
.sb-phone {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1.1rem 1.25rem;
}
.sb-phone-label { font-size: .72rem; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .07em; margin-bottom: .2rem; }
.sb-phone-num { font-size: 1.1rem; font-weight: 900; color: var(--primary-color); text-decoration: none !important; }
.sb-phone-num:hover { text-decoration: underline !important; }
.sb-phone-icon {
    width: 2.5rem; height: 2.5rem; border-radius: 50%; flex-shrink: 0;
    background: rgba(var(--primary-color-rgb, 34,197,94),.1);
    display: flex; align-items: center; justify-content: center;
    color: var(--primary-color); font-size: .95rem;
}
.sb-hours { padding: .6rem 1.25rem 1rem; font-size: .775rem; color: #9ca3af; border-top: 1px solid #f3f4f6; }

/* Bloc générique sidebar */
.sb-head {
    display: flex; align-items: center; gap: .5rem;
    font-size: .8rem; font-weight: 800; color: #111827;
    text-transform: uppercase; letter-spacing: .06em;
    padding: .9rem 1.25rem; border-bottom: 1px solid #f3f4f6;
}
.sb-head i { color: var(--primary-color); font-size: .85rem; }
.sb-body { padding: 1rem 1.25rem; }

/* Raisons */
.sb-raison {
    display: flex; align-items: flex-start; gap: .7rem;
    padding: .6rem 0; border-bottom: 1px solid #f9fafb;
}
.sb-raison:last-child { border-bottom: none; padding-bottom: 0; }
.sb-raison-dot {
    width: 1.25rem; height: 1.25rem; border-radius: 50%; flex-shrink: 0; margin-top: .1rem;
    background: var(--primary-color); display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: .55rem;
}
.sb-raison-text { font-size: .845rem; color: #374151; font-weight: 500; line-height: 1.45; }

/* Financement */
.sb-financement-item {
    display: flex; align-items: center; gap: .65rem;
    padding: .5rem 0; font-size: .845rem; color: #374151;
    border-bottom: 1px dashed #f3f4f6;
}
.sb-financement-item:last-child { border-bottom: none; }
.sb-financement-item i { color: var(--primary-color); font-size: .85rem; flex-shrink: 0; }

/* Infos pratiques */
.sb-info-item {
    display: flex; align-items: flex-start; gap: .65rem;
    padding: .55rem 0; font-size: .825rem; color: #374151;
    border-bottom: 1px solid #f9fafb;
}
.sb-info-item:last-child { border-bottom: none; }
.sb-info-item i { color: var(--primary-color); font-size: .8rem; flex-shrink: 0; margin-top: .15rem; }
.sb-info-label { font-weight: 700; color: #111827; font-size: .75rem; display: block; }
.sb-info-val { color: #374151; font-size: .825rem; }
.sb-info-val a { color: var(--primary-color); text-decoration: none; }
.sb-info-val a:hover { text-decoration: underline; }

/* Partage */
.sb-share { display: flex; gap: .6rem; }
.sb-share-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    font-size: .775rem; font-weight: 600; padding: .45rem .85rem;
    border-radius: 50px; text-decoration: none !important;
    border: 1px solid var(--border, #e5e7eb); color: #374151;
    transition: all .15s ease;
}
.sb-share-btn:hover { background: #f3f4f6; }
.sb-share-btn.fb { border-color: #1877f2; color: #1877f2; }
.sb-share-btn.fb:hover { background: #eff4ff; }
.sb-share-btn.wa { border-color: #25d366; color: #25d366; }
.sb-share-btn.wa:hover { background: #f0fff4; }

/* Autres services */
.sb-service-link {
    display: flex; align-items: center; gap: .65rem;
    padding: .65rem 0; border-bottom: 1px solid #f9fafb;
    text-decoration: none !important; color: #374151; font-size: .875rem; font-weight: 600;
    transition: color .15s ease;
}
.sb-service-link:last-child { border-bottom: none; }
.sb-service-link:hover { color: var(--primary-color); }
.sb-service-link-icon {
    width: 1.75rem; height: 1.75rem; border-radius: 8px; flex-shrink: 0;
    background: rgba(var(--primary-color-rgb, 34,197,94),.1);
    display: flex; align-items: center; justify-content: center;
    font-size: .65rem; color: var(--primary-color);
}

/* Zone pills */
.sp-zone-pill {
    display: inline-block; background: rgba(var(--primary-color-rgb,34,197,94),.07);
    border: 1px solid rgba(var(--primary-color-rgb,34,197,94),.2);
    border-radius: 50px; padding: .25rem .7rem;
    font-size: .75rem; font-weight: 600; color: var(--primary-color);
    margin: .2rem;
}

/* Breadcrumb */
.sp-bc { font-size: .78rem; font-weight: 500; display: flex; flex-wrap: wrap; gap: .2rem; align-items: center; color: rgba(255,255,255,.5); margin-bottom: 1.5rem; }
.sp-bc a { color: rgba(255,255,255,.6); text-decoration: none; }
.sp-bc a:hover { color: rgba(255,255,255,.9); }
.sp-bc-sep { margin: 0 .35rem; opacity: .35; }

@media(max-width:639px){ .sp-card { padding: 1.5rem; } }
</style>
@endpush

@section('content')
<div class="sp">

{{-- ══════════════════════════════
     HERO
══════════════════════════════ --}}
<section class="sp-hero">
    <div class="sp-hero-bg {{ empty($service['featured_image']) ? '' : '' }}">
        @if(!empty($service['featured_image']))
            <img src="{{ asset($service['featured_image']) }}" alt="{{ $service['name'] }}" loading="eager">
        @endif
    </div>
    <div class="sp-hero-overlay"></div>

    <div style="position:relative;z-index:2;width:100%;padding:0 1.5rem 5rem; max-width:1160px; margin:0 auto; padding-top:8rem;">
        <nav class="sp-bc">
            <a href="{{ url('/') }}">Accueil</a>
            <span class="sp-bc-sep">/</span>
            <a href="{{ route('services.index') }}">Services</a>
            <span class="sp-bc-sep">/</span>
            <span style="color:rgba(255,255,255,.85);">{{ $service['name'] }}</span>
        </nav>

        <div style="display:inline-flex;align-items:center;gap:.5rem;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-radius:50px;padding:.35rem .9rem;margin-bottom:1.25rem;">
            <i class="{{ $service['icon'] ?? 'fas fa-tree' }}" style="color:var(--primary-color);font-size:.75rem;"></i>
            <span style="font-size:.73rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.75);">{{ setting('company_city', 'Votre région') }}</span>
        </div>

        <h1 style="font-size:clamp(2rem,5vw,3.5rem);font-weight:900;color:#fff;line-height:1.08;letter-spacing:-.025em;margin-bottom:1rem;">
            {{ $service['name'] }}
        </h1>
        <p style="font-size:clamp(.95rem,1.8vw,1.15rem);color:rgba(255,255,255,.75);max-width:580px;line-height:1.65;margin-bottom:2rem;">
            {{ $service['short_description'] }}
        </p>

        <div style="display:flex;flex-wrap:wrap;gap:.85rem;">
            <a href="{{ route('form.step', 'propertyType') }}"
               style="display:inline-flex;align-items:center;gap:.55rem;background:var(--primary-color);color:#fff !important;font-weight:800;font-size:.95rem;padding:.85rem 1.75rem;border-radius:50px;text-decoration:none;box-shadow:0 4px 16px rgba(0,0,0,.25);transition:all .2s ease;"
               onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
                <i class="fas fa-file-alt"></i> Devis gratuit
            </a>
            <a href="tel:{{ setting('company_phone_raw', setting('company_phone')) }}"
               style="display:inline-flex;align-items:center;gap:.55rem;color:#fff !important;font-weight:600;font-size:.95rem;padding:.85rem 1.5rem;border-radius:50px;border:1.5px solid rgba(255,255,255,.3);text-decoration:none;transition:all .2s ease;"
               onmouseover="this.style.borderColor='rgba(255,255,255,.7)'" onmouseout="this.style.borderColor='rgba(255,255,255,.3)'">
                <i class="fas fa-phone" style="color:var(--primary-color);"></i>
                {{ setting('company_phone', '06 42 21 41 51') }}
            </a>
        </div>
    </div>

    <div class="sp-hero-wave">
        <svg viewBox="0 0 1440 56" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" style="display:block;width:100%;height:56px;">
            <path d="M0,56 C360,10 1080,46 1440,0 L1440,56 Z" fill="#f3f4f6"/>
        </svg>
    </div>
</section>

{{-- ══════════════════════════════
     MAIN LAYOUT (content + sidebar)
══════════════════════════════ --}}
<div style="background:#f3f4f6; padding-bottom:1px;">
<div class="sp-layout">

    {{-- ─── CONTENU PRINCIPAL ─── --}}
    <main class="sp-main">

        @if(isset($service['error']) && $service['error'])
        <div class="sp-card" style="border-color:#fecaca;background:#fff5f5;">
            <div style="display:flex;gap:1rem;">
                <i class="fas fa-exclamation-triangle" style="color:#ef4444;font-size:1.5rem;flex-shrink:0;margin-top:.1rem;"></i>
                <div>
                    <div style="font-weight:700;color:#b91c1c;margin-bottom:.35rem;">Erreur de génération du contenu</div>
                    <p style="font-size:.875rem;color:#dc2626;">{{ $service['error_message'] ?? 'Une erreur est survenue.' }}</p>
                </div>
            </div>
        </div>
        @endif

        {{-- Article IA / contenu principal --}}
        <div class="sp-card">
            <div class="sp-prose prose max-w-none">
                {!! $service['description'] !!}
            </div>
        </div>

        {{-- Avantages — SINGLE COLONNE --}}
        <div class="sp-card">
            <div style="display:flex;align-items:center;gap:.65rem;margin-bottom:1.5rem;">
                <div style="width:2rem;height:2rem;border-radius:8px;background:rgba(var(--primary-color-rgb,34,197,94),.12);display:flex;align-items:center;justify-content:center;color:var(--primary-color);">
                    <i class="fas fa-check-circle" style="font-size:.9rem;"></i>
                </div>
                <h2 style="font-size:1.15rem;font-weight:800;color:#111827;margin:0;">
                    Pourquoi choisir {{ setting('company_name', 'Louis Hoffmann Élagage') }} pour votre {{ $service['name'] }} ?
                </h2>
            </div>

            <div class="sp-adv-list">
                @foreach([
                    ['fas fa-shield-alt', 'Assuré & certifié', 'Assurance RC Pro à jour, équipes formées. Vos biens et votre propriété sont couverts en toutes circonstances.'],
                    ['fas fa-map-marker-alt', 'Intervention locale', setting('company_city','Votre ville') . ' et les communes alentour — on se déplace chez vous.'],
                    ['fas fa-clock', 'Réactivité garantie', 'Devis sous 24h, intervention programmée à votre convenance. Urgences traitées en priorité.'],
                    ['fas fa-file-invoice', 'Devis gratuit & transparent', 'Devis détaillé écrit avant chaque intervention. Ce qui est signé est ce qui est facturé. Zéro surprise.'],
                    ['fas fa-broom', 'Chantier propre, déchets évacués', 'Branches, troncs, souches, broyat — tout repart avec nous. Votre propriété est laissée impeccable.'],
                    ['fas fa-leaf', 'Respect du végétal & de l\'environnement', 'Taille raisonnée, techniques adaptées à chaque essence, valorisation des déchets verts par broyage.'],
                ] as $adv)
                <div class="sp-adv-item">
                    <div class="sp-adv-icon"><i class="{{ $adv[0] }}"></i></div>
                    <div>
                        <div class="sp-adv-title">{{ $adv[1] }}</div>
                        <div class="sp-adv-desc">{{ $adv[2] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- CTA intermédiaire --}}
        <div class="sp-cta-band">
            <h3>Prêt pour votre {{ $service['name'] }} à {{ setting('company_city','votre ville') }} ?</h3>
            <p>Devis gratuit, réponse sous 24h — artisan local certifié, {{ setting('company_city','votre région') }} et alentours.</p>
            <a href="{{ route('form.step', 'propertyType') }}" class="sp-cta-btn">
                <i class="fas fa-file-alt"></i> Obtenir mon devis gratuit
            </a>
            <br>
            <a href="tel:{{ setting('company_phone_raw', setting('company_phone')) }}" class="sp-cta-phone">
                <i class="fas fa-phone"></i> {{ setting('company_phone', '06 42 21 41 51') }}
            </a>
        </div>

        {{-- Réalisations --}}
        @php
            $portfolioItems = \App\Models\Setting::get('portfolio_items', []);
            if (!is_array($portfolioItems)) $portfolioItems = [];
            $relatedPortfolio = collect($portfolioItems)
                ->filter(fn($item) => is_array($item) && isset($item['title']))
                ->take(3);
        @endphp
        @if($relatedPortfolio->count() > 0)
        <div class="sp-card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
                <h2 style="font-size:1.1rem;font-weight:800;color:#111827;margin:0;">
                    <i class="fas fa-images mr-2" style="color:var(--primary-color);"></i>Nos réalisations
                </h2>
                <a href="{{ route('portfolio.index') }}" style="font-size:.8rem;font-weight:700;color:var(--primary-color);text-decoration:none;">
                    Tout voir <i class="fas fa-arrow-right" style="font-size:.7rem;"></i>
                </a>
            </div>
            <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));">
                @foreach($relatedPortfolio as $item)
                <article style="border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;background:#fff;">
                    @if(isset($item['images']) && is_array($item['images']) && count($item['images']) > 0)
                    <div style="height:140px;overflow:hidden;background:#f3f4f6;">
                        <img src="{{ url($item['images'][0]) }}" alt="{{ $item['title'] }}"
                             style="width:100%;height:100%;object-fit:cover;display:block;" loading="lazy">
                    </div>
                    @endif
                    <div style="padding:.75rem;">
                        <div style="font-weight:700;font-size:.825rem;color:#111827;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">{{ $item['title'] }}</div>
                    </div>
                </article>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Avis --}}
        @php $serviceReviews = \App\Models\Review::where('is_active', true)->take(4)->get(); @endphp
        @if($serviceReviews->count() > 0)
        <div class="sp-card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
                <h2 style="font-size:1.1rem;font-weight:800;color:#111827;margin:0;">
                    <i class="fas fa-star mr-2" style="color:#f59e0b;"></i>Avis de nos clients
                </h2>
                <div style="display:flex;align-items:center;gap:.3rem;font-size:.8rem;color:#6b7280;">
                    @for($i=0;$i<5;$i++)<i class="fas fa-star" style="color:#f59e0b;font-size:.7rem;"></i>@endfor
                    <span style="font-weight:700;color:#111827;margin-left:.3rem;">4.9/5</span>
                </div>
            </div>
            @foreach($serviceReviews as $review)
            <div class="sp-review">
                <div class="sp-review-stars">
                    @for($i=1;$i<=5;$i++)<i class="fas fa-star {{ $i <= $review->rating ? '' : '' }}" style="{{ $i <= $review->rating ? 'color:#f59e0b' : 'color:#e5e7eb' }};font-size:.75rem;"></i>@endfor
                </div>
                <p class="sp-review-text">"{{ \Illuminate\Support\Str::limit($review->review_text ?? '', 160) }}"</p>
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <div class="sp-review-author">{{ $review->author_name }}</div>
                    <div class="sp-review-city"><i class="fas fa-map-marker-alt" style="font-size:.65rem;margin-right:.25rem;color:var(--primary-color);"></i>{{ $review->review_date ? \Carbon\Carbon::parse($review->review_date)->translatedFormat('M Y') : '' }}</div>
                </div>
            </div>
            @endforeach
            <div style="text-align:center;margin-top:1rem;">
                <a href="{{ route('reviews.all') }}" style="font-size:.8rem;font-weight:700;color:var(--primary-color);text-decoration:none;">
                    Tous les avis <i class="fas fa-arrow-right" style="font-size:.7rem;"></i>
                </a>
            </div>
        </div>
        @endif

    </main>

    {{-- ─── SIDEBAR CONVERSION ─── --}}
    <aside class="sp-sidebar">

        {{-- 1. CTA DEVIS ── primary action --}}
        <div class="sb-cta">
            <div class="sb-cta-title">
                <i class="fas fa-file-alt" style="margin-right:.4rem;"></i>Devis gratuit
            </div>
            <div class="sb-cta-sub">Sans engagement · Réponse sous 24h<br>{{ setting('company_city','votre région') }} et alentours</div>
            <a href="{{ route('form.step', 'propertyType') }}" class="sb-cta-btn">
                Obtenir mon devis <i class="fas fa-arrow-right" style="font-size:.75rem;margin-left:.25rem;"></i>
            </a>
            <div class="sb-cta-microcopy">
                <i class="fas fa-lock" style="font-size:.65rem;"></i> Vos données restent confidentielles
            </div>
        </div>

        {{-- 2. TÉLÉPHONE --}}
        <div class="sb-block">
            <div class="sb-phone">
                <div>
                    <div class="sb-phone-label">Nous appeler</div>
                    <a href="tel:{{ setting('company_phone_raw', setting('company_phone')) }}" class="sb-phone-num">
                        {{ setting('company_phone', '06 42 21 41 51') }}
                    </a>
                </div>
                <div class="sb-phone-icon">
                    <i class="fas fa-phone"></i>
                </div>
            </div>
            <div class="sb-hours">
                <i class="fas fa-clock" style="margin-right:.35rem;"></i> Lun–Sam · 8h–19h
            </div>
        </div>

        {{-- 3. POURQUOI NOUS --}}
        <div class="sb-block">
            <div class="sb-head">
                <i class="fas fa-award"></i> Pourquoi nous choisir
            </div>
            <div class="sb-body">
                @php $raisons = [
                    'Artisan local à ' . setting('company_city','votre ville'),
                    'Assurance RC Pro complète à jour',
                    'Équipe certifiée, matériel professionnel',
                    'Devis écrit, tarif transparent',
                    'Chantier nettoyé, déchets évacués',
                    '4.9/5 · 120+ avis Google vérifiés',
                ]; @endphp
                <div style="display:flex;flex-direction:column;">
                    @foreach($raisons as $r)
                    <div class="sb-raison">
                        <span class="sb-raison-dot"><i class="fas fa-check" style="font-size:.5rem;"></i></span>
                        <span class="sb-raison-text">{{ $r }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- 4. FINANCEMENT --}}
        <div class="sb-block">
            <div class="sb-head">
                <i class="fas fa-hand-holding-usd"></i> Financement & aides
            </div>
            <div class="sb-body">
                <p style="font-size:.8rem;color:#6b7280;margin-bottom:.85rem;line-height:1.55;">
                    Certaines interventions peuvent bénéficier d'aides locales ou d'obligations de sécurité (arbres dangereux). Renseignez-vous auprès de votre mairie ou de votre assureur.
                </p>
                <div>
                    @foreach([
                        ['fas fa-leaf','Conseil personnalisé offert'],
                        ['fas fa-file-alt','Attestation fournie sur demande'],
                        ['fas fa-info-circle','Devis utilisable pour dossier assurance'],
                    ] as $f)
                    <div class="sb-financement-item">
                        <i class="{{ $f[0] }}"></i>
                        <span>{{ $f[1] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- 5. INFOS PRATIQUES --}}
        <div class="sb-block">
            <div class="sb-head">
                <i class="fas fa-info-circle"></i> Infos pratiques
            </div>
            <div class="sb-body">
                @php
                    $infoItems = [
                        ['fas fa-building', 'Société', setting('company_name', 'Votre Entreprise')],
                        ['fas fa-map-marker-alt', 'Adresse', setting('company_address', '')],
                        ['fas fa-phone', 'Téléphone', '<a href="tel:' . setting('company_phone_raw', setting('company_phone')) . '">' . setting('company_phone', '') . '</a>'],
                        ['fas fa-envelope', 'Email', '<a href="mailto:' . setting('company_email', '') . '">' . setting('company_email', '') . '</a>'],
                    ];
                @endphp
                <div>
                    @foreach($infoItems as $info)
                    <div class="sb-info-item">
                        <i class="{{ $info[0] }}"></i>
                        <div>
                            <span class="sb-info-label">{{ $info[1] }}</span>
                            <span class="sb-info-val">{!! $info[2] !!}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- 6. ZONE --}}
        <div class="sb-block">
            <div class="sb-head">
                <i class="fas fa-map"></i> Zone d'intervention
            </div>
            <div class="sb-body">
                <div style="line-height:1;">
                    @php
                        $zoneCities = setting('intervention_cities', '');
                        $zoneList = $zoneCities ? array_slice(array_map('trim', explode(',', $zoneCities)), 0, 10) : [setting('company_city','Votre ville')];
                    @endphp
                    @foreach($zoneList as $ville)
                    <span class="sp-zone-pill">{{ $ville }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- 7. PARTAGER --}}
        <div class="sb-block">
            <div class="sb-head">
                <i class="fas fa-share-alt"></i> Partager ce service
            </div>
            <div class="sb-body">
                <div class="sb-share">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                       target="_blank" rel="noopener" class="sb-share-btn fb">
                        <i class="fab fa-facebook"></i> Facebook
                    </a>
                    <a href="https://wa.me/?text={{ urlencode($service['name'] . ' — ' . url()->current()) }}"
                       target="_blank" rel="noopener" class="sb-share-btn wa">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                    <a href="mailto:?subject={{ urlencode($service['name']) }}&body={{ urlencode(url()->current()) }}"
                       class="sb-share-btn">
                        <i class="fas fa-envelope"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- 8. AUTRES SERVICES --}}
        @php
            $allSvcs = \App\Models\Setting::get('services', '[]');
            $allSvcs = is_string($allSvcs) ? json_decode($allSvcs, true) : ($allSvcs ?? []);
            if (!is_array($allSvcs)) $allSvcs = [];
            $otherSvcs = collect($allSvcs)
                ->filter(fn($s) => is_array($s) && ($s['is_visible'] ?? true) && ($s['slug'] ?? '') !== ($service['slug'] ?? ''))
                ->take(5);
        @endphp
        @if($otherSvcs->count() > 0)
        <div class="sb-block">
            <div class="sb-head">
                <i class="fas fa-th-large"></i> Nos autres services
            </div>
            <div class="sb-body">
                @foreach($otherSvcs as $other)
                <a href="{{ route('services.show', $other['slug']) }}" class="sb-service-link">
                    <span class="sb-service-link-icon">
                        <i class="{{ $other['icon'] ?? 'fas fa-tree' }}"></i>
                    </span>
                    {{ $other['name'] }}
                    <i class="fas fa-chevron-right" style="font-size:.65rem;margin-left:auto;color:#d1d5db;"></i>
                </a>
                @endforeach
            </div>
        </div>
        @endif

    </aside>

</div>{{-- /sp-layout --}}
</div>{{-- /bg --}}

</div>{{-- /sp --}}
@endsection
