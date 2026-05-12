@extends('layouts.app')

@section('title', $pageTitle ?? 'Service professionnel')
@section('description', $pageDescription ?? 'Service professionnel de qualité. Devis gratuit et intervention rapide.')
@section('keywords', !empty($pageKeywords) ? $pageKeywords . (!empty($extendedKeywords) ? ', ' . implode(', ', $extendedKeywords) : '') : (!empty($extendedKeywords) ? implode(', ', $extendedKeywords) : ''))

@push('head')
@php
    $companyName = setting('company_name', 'Votre Entreprise');
    $companyPhone = setting('company_phone', '');
    $companyPhoneRaw = setting('company_phone_raw', preg_replace('/\D+/', '', $companyPhone));
    $companyAddress = setting('company_address', '');
    $companyCity = setting('company_city', '');
    $companyPostalCode = setting('company_postal_code', '');
    $companyCountry = setting('company_country', 'France');
    $companyRegion = setting('company_region', '');
    $companyHours = setting('company_hours', 'Lun–Sam · 8h–19h');
    $companyEmail = setting('company_email', '');
    $companyLogo = setting('company_logo');
    $companyLogoUrl = $companyLogo ? (str_starts_with($companyLogo, 'http') ? $companyLogo : asset($companyLogo)) : url('logo/logo.png');
    $sameAs = array_values(array_filter([
        setting('google_business_url'),
        setting('facebook_url'),
        setting('instagram_url'),
        setting('linkedin_url'),
    ]));
    $businessType = Str::contains(Str::lower($serviceName . ' ' . $mainKeyword), ['toiture', 'couvreur', 'couverture', 'zinguerie'])
        ? 'RoofingContractor'
        : 'ProfessionalService';
    $providerSchema = [
        '@type' => $businessType,
        'name' => $companyName,
        'url' => url('/'),
        'telephone' => $companyPhoneRaw,
        'logo' => $companyLogoUrl,
        'image' => $companyLogoUrl,
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => $companyAddress,
            'addressLocality' => $companyCity,
            'addressRegion' => $companyRegion,
            'postalCode' => $companyPostalCode,
            'addressCountry' => $companyCountry ?: 'FR',
        ],
        'areaServed' => [
            '@type' => 'City',
            'name' => $cityModel->name ?? '',
            'postalCode' => $cityModel->postal_code ?? '',
        ],
        'openingHours' => $companyHours,
    ];
    if (!empty($sameAs)) {
        $providerSchema['sameAs'] = $sameAs;
    }
    $serviceSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => $ad->title ?? $serviceName,
        'serviceType' => $serviceName,
        'description' => strip_tags($pageDescription ?? ''),
        'url' => $canonicalUrl,
        'areaServed' => [
            '@type' => 'City',
            'name' => $cityModel->name ?? '',
            'postalCode' => $cityModel->postal_code ?? '',
        ],
        'provider' => $providerSchema,
    ];
    $localBusinessSchema = [
        '@context' => 'https://schema.org',
        '@type' => $businessType,
        'name' => $companyName,
        'url' => url('/'),
        'telephone' => $companyPhoneRaw,
        'logo' => $companyLogoUrl,
        'image' => $companyLogoUrl,
        'description' => setting('company_description', strip_tags($pageDescription ?? '')),
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => $companyAddress,
            'addressLocality' => $companyCity,
            'addressRegion' => $companyRegion,
            'postalCode' => $companyPostalCode,
            'addressCountry' => $companyCountry ?: 'FR',
        ],
        'areaServed' => [
            [
                '@type' => 'City',
                'name' => $cityModel->name ?? '',
                'postalCode' => $cityModel->postal_code ?? '',
            ],
        ],
    ];
    if (!empty($sameAs)) {
        $localBusinessSchema['sameAs'] = $sameAs;
    }

    $heroImageUrl = null;
    if (!empty($featuredImage)) {
        $heroImageUrl = str_starts_with((string) $featuredImage, 'http')
            ? $featuredImage
            : asset(ltrim((string) $featuredImage, '/'));
    }

    $heroDescription = Str::limit(strip_tags((string) $pageDescription), 180);
    $cityLabel = trim(($cityModel->name ?? '') . (!empty($cityModel->postal_code) ? ' (' . $cityModel->postal_code . ')' : ''));
    $serviceLabel = $servicePage['name'] ?? $serviceName;
    $ratingDisplay = $averageReviewRating > 0 ? number_format($averageReviewRating, 1, ',', ' ') : null;
@endphp
<style>
.sp { font-family: inherit; }
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
.sp-card {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 16px;
    padding: 2rem; margin-bottom: 1.5rem;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.sp-card:last-child { margin-bottom: 0; }
.sp-prose { color: #374151 !important; line-height: 1.8; }
.sp-prose h2 { font-size: 1.35rem; font-weight: 800; color: #111827 !important; margin: 1.75rem 0 .75rem; }
.sp-prose h3 { font-size: 1.1rem; font-weight: 700; color: #111827 !important; margin: 1.4rem 0 .6rem; }
.sp-prose p { margin-bottom: 1rem; font-size: .975rem; color: #374151 !important; }
.sp-prose ul { padding-left: 1.25rem; margin-bottom: 1rem; }
.sp-prose ul li { margin-bottom: .4rem; font-size: .975rem; color: #374151 !important; }
.sp-prose strong { color: #111827 !important; }
.sp-prose a { color: inherit !important; }
.sp-prose img, .sp-prose video, .sp-prose iframe { max-width:100%!important; height:auto; border-radius:10px; display:block; }
.sp-prose table { width:100%; border-collapse:collapse; font-size:.9rem; color:#374151 !important; }
.sp-prose td, .sp-prose th { padding:.5rem .75rem; border:1px solid #e5e7eb; word-break:break-word; color:#374151 !important; }
.sp-prose, .sp-prose * { --tw-prose-body: #374151; --tw-prose-headings: #111827; --tw-prose-bold: #111827; --tw-prose-links: #374151; }
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
.sp-prose div[class*="grid"] > *:last-child,
.sp-prose div[class*="flex"] > *:last-child,
.sp-prose div[class*="columns"] > *:last-child {
    margin-bottom: 0;
}
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
.sp-cta-band {
    border-radius: 16px; padding: 2rem; color: #fff;
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
.sb-block {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 16px;
    overflow: hidden; margin-bottom: 1.25rem; box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.sb-block:last-child { margin-bottom: 0; }
.sb-cta {
    background: linear-gradient(155deg, var(--primary-color), var(--secondary-color, #1a5c35));
    padding: 1.5rem; border-radius: 16px; color: #fff;
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
.sb-head {
    display: flex; align-items: center; gap: .5rem;
    font-size: .8rem; font-weight: 800; color: #111827;
    text-transform: uppercase; letter-spacing: .06em;
    padding: .9rem 1.25rem; border-bottom: 1px solid #f3f4f6;
}
.sb-head i { color: var(--primary-color); font-size: .85rem; }
.sb-body { padding: 1rem 1.25rem; }
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
.sb-share { display: flex; gap: .6rem; flex-wrap: wrap; }
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
.sp-zone-pill {
    display: inline-block; background: rgba(var(--primary-color-rgb,34,197,94),.07);
    border: 1px solid rgba(var(--primary-color-rgb,34,197,94),.2);
    border-radius: 50px; padding: .25rem .7rem;
    font-size: .75rem; font-weight: 600; color: var(--primary-color);
    margin: .2rem;
}
.sp-bc { font-size: .78rem; font-weight: 500; display: flex; flex-wrap: wrap; gap: .2rem; align-items: center; color: rgba(255,255,255,.5); margin-bottom: 1.5rem; }
.sp-bc a { color: rgba(255,255,255,.6); text-decoration: none; }
.sp-bc a:hover { color: rgba(255,255,255,.9); }
.sp-bc-sep { margin: 0 .35rem; opacity: .35; }
.ad-hero-badge {
    display:inline-flex;align-items:center;gap:.5rem;background:rgba(255,255,255,.1);
    border:1px solid rgba(255,255,255,.2);border-radius:50px;padding:.35rem .9rem;margin-bottom:1.25rem;
}
.ad-chip-grid {
    display:grid; gap:1rem; grid-template-columns:repeat(auto-fit,minmax(160px,1fr));
}
.ad-chip-card {
    border:1px solid #e5e7eb; background:#f9fafb; border-radius:14px; padding:1rem;
    text-align:center;
}
.ad-chip-icon {
    width:2.5rem;height:2.5rem;border-radius:999px;margin:0 auto .55rem;
    background:rgba(var(--primary-color-rgb,34,197,94),.12);display:flex;align-items:center;justify-content:center;
    color:var(--primary-color);
}
.ad-faq details { border-bottom: 1px solid #e5e7eb; padding: .9rem 0; }
.ad-faq details:last-child { border-bottom: none; }
.ad-faq summary { cursor: pointer; font-weight: 700; color: #111827; list-style: none; }
.ad-faq summary::-webkit-details-marker { display: none; }
.ad-faq p { margin-top: .75rem; color: #4b5563; font-size: .925rem; line-height: 1.7; }
.ad-portfolio-grid {
    display:grid; gap:1rem; grid-template-columns:repeat(auto-fill,minmax(180px,1fr));
}
.ad-portfolio-card {
    border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;background:#fff;
}
.ad-portfolio-card img {
    width:100%;height:140px;object-fit:cover;display:block;
}
.ad-list-grid {
    display:grid; gap:1rem; grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
}
@media(max-width:639px){ .sp-card { padding: 1.5rem; } }
</style>
@endpush

@section('content')
<div class="sp">

<section class="sp-hero">
    <div class="sp-hero-bg">
        @if($heroImageUrl)
            <img src="{{ $heroImageUrl }}" alt="{{ $ad->title ?? $serviceName }}" loading="eager">
        @endif
    </div>
    <div class="sp-hero-overlay"></div>

    <div style="position:relative;z-index:2;width:100%;padding:0 1.5rem 5rem;max-width:1160px;margin:0 auto;padding-top:8rem;">
        <nav class="sp-bc">
            <a href="{{ route('home') }}">Accueil</a>
            <span class="sp-bc-sep">/</span>
            <a href="{{ route('services.index') }}">Services</a>
            @if(!empty($servicePage))
                <span class="sp-bc-sep">/</span>
                <a href="{{ $serviceUrl }}">{{ $serviceLabel }}</a>
            @endif
            <span class="sp-bc-sep">/</span>
            <span style="color:rgba(255,255,255,.85);">{{ $ad->title ?? $serviceName }}</span>
        </nav>

        <div class="ad-hero-badge">
            <i class="fas fa-map-marker-alt" style="color:var(--primary-color);font-size:.75rem;"></i>
            <span style="font-size:.73rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.75);">
                {{ $cityLabel ?: $companyCity ?: 'Votre région' }}
            </span>
        </div>

        <h1 style="font-size:clamp(2rem,5vw,3.5rem);font-weight:900;color:#fff;line-height:1.08;letter-spacing:-.025em;margin-bottom:1rem;">
            {{ $ad->title ?? $serviceName }}
        </h1>
        <p style="font-size:clamp(.95rem,1.8vw,1.15rem);color:rgba(255,255,255,.75);max-width:640px;line-height:1.65;margin-bottom:2rem;">
            {{ $heroDescription }}
        </p>

        <div style="display:flex;flex-wrap:wrap;gap:.85rem;">
            <a href="{{ route('form.step', 'propertyType') }}"
               style="display:inline-flex;align-items:center;gap:.55rem;background:var(--primary-color);color:#fff !important;font-weight:800;font-size:.95rem;padding:.85rem 1.75rem;border-radius:50px;text-decoration:none;box-shadow:0 4px 16px rgba(0,0,0,.25);transition:all .2s ease;">
                <i class="fas fa-file-alt"></i> Devis gratuit
            </a>
            <a href="tel:{{ $companyPhoneRaw }}"
               style="display:inline-flex;align-items:center;gap:.55rem;color:#fff !important;font-weight:600;font-size:.95rem;padding:.85rem 1.5rem;border-radius:50px;border:1.5px solid rgba(255,255,255,.3);text-decoration:none;transition:all .2s ease;"
               onclick="if(typeof window.trackPhoneCall==='function'){window.trackPhoneCall('{{ $companyPhoneRaw }}','ads/{{ $ad->slug ?? '' }}');}return true;">
                <i class="fas fa-phone" style="color:var(--primary-color);"></i>
                {{ $companyPhone }}
            </a>
        </div>
    </div>

    <div class="sp-hero-wave">
        <svg viewBox="0 0 1440 56" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" style="display:block;width:100%;height:56px;">
            <path d="M0,56 C360,10 1080,46 1440,0 L1440,56 Z" fill="#f3f4f6"/>
        </svg>
    </div>
</section>

<div style="background:#f3f4f6;padding-bottom:1px;">
<div class="sp-layout">

    <main class="sp-main">
        <div class="sp-card">
            <div class="ad-chip-grid">
                @foreach([
                    ['icon' => 'fas fa-map-marker-alt', 'label' => $cityLabel ?: 'Intervention locale'],
                    ['icon' => 'fas fa-clock', 'label' => 'Réponse sous 24h'],
                    ['icon' => 'fas fa-file-invoice', 'label' => 'Devis gratuit'],
                    ['icon' => 'fas fa-tools', 'label' => $serviceLabel],
                ] as $chip)
                <div class="ad-chip-card">
                    <div class="ad-chip-icon"><i class="{{ $chip['icon'] }}"></i></div>
                    <div style="font-size:.8rem;font-weight:700;color:#374151;line-height:1.4;">{{ $chip['label'] }}</div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="sp-card">
            <div class="sp-prose max-w-none">
                {!! $renderedContentHtml !!}
            </div>
        </div>

        <div class="sp-card">
            <div style="display:flex;align-items:center;gap:.65rem;margin-bottom:1.5rem;">
                <div style="width:2rem;height:2rem;border-radius:8px;background:rgba(var(--primary-color-rgb,34,197,94),.12);display:flex;align-items:center;justify-content:center;color:var(--primary-color);">
                    <i class="fas fa-check-circle" style="font-size:.9rem;"></i>
                </div>
                <h2 style="font-size:1.15rem;font-weight:800;color:#111827;margin:0;">
                    Pourquoi choisir {{ $companyName }} pour votre {{ $serviceLabel }} à {{ $cityModel->name ?? $companyCity }} ?
                </h2>
            </div>

            <div class="sp-adv-list">
                @foreach([
                    ['fas fa-shield-alt', 'Entreprise assurée', 'Intervention réalisée avec assurance professionnelle et méthodes adaptées à votre projet.'],
                    ['fas fa-map-marker-alt', 'Présence locale', 'Nous intervenons à ' . ($cityModel->name ?? $companyCity) . ' et dans les communes voisines avec un vrai suivi terrain.'],
                    ['fas fa-clock', 'Réactivité', 'Premier retour rapide pour cadrer le besoin, le délai et le budget de votre intervention.'],
                    ['fas fa-file-invoice', 'Devis clair', 'Chaque prestation fait l’objet d’un devis détaillé et sans surprise avant intervention.'],
                    ['fas fa-broom', 'Chantier propre', 'Le chantier est laissé propre et organisé pour une expérience rassurante du début à la fin.'],
                    ['fas fa-thumbs-up', 'Conseil personnalisé', 'Nous adaptons la solution à votre situation, à la ville ciblée et au type de prestation demandée.'],
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

        @if(!empty($servicePage))
        <div class="sp-card">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
                <div>
                    <h2 style="font-size:1.1rem;font-weight:800;color:#111827;margin:0 0 .45rem;">Page service associée</h2>
                    <p style="font-size:.92rem;color:#6b7280;line-height:1.65;">
                        Cette annonce locale complète notre page service principale pour <strong>{{ $serviceLabel }}</strong>.
                    </p>
                </div>
                <a href="{{ $serviceUrl }}" class="sp-cta-btn" style="margin-top:0;">
                    <i class="fas fa-arrow-right"></i> Voir la page service
                </a>
            </div>
        </div>
        @endif

        @if(!empty($portfolioItems) && count($portfolioItems) > 0)
        <div class="sp-card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
                <h2 style="font-size:1.1rem;font-weight:800;color:#111827;margin:0;">
                    <i class="fas fa-images mr-2" style="color:var(--primary-color);"></i>Nos réalisations
                </h2>
                <a href="{{ route('portfolio.index') }}" style="font-size:.8rem;font-weight:700;color:var(--primary-color);text-decoration:none;">
                    Tout voir <i class="fas fa-arrow-right" style="font-size:.7rem;"></i>
                </a>
            </div>
            <div class="ad-portfolio-grid">
                @foreach(array_slice($portfolioItems, 0, 4) as $portfolioItem)
                    @php
                        $itemTitle = $portfolioItem['title'] ?? 'Réalisation';
                        $itemSlug = !empty($portfolioItem['slug']) ? $portfolioItem['slug'] : Str::slug($itemTitle);
                        $portfolioImage = is_array($portfolioItem['images'] ?? null)
                            ? ($portfolioItem['images'][0] ?? null)
                            : ($portfolioItem['images'] ?? null);
                    @endphp
                    @if($portfolioImage)
                    <article class="ad-portfolio-card">
                        <a href="{{ route('portfolio.show', $itemSlug) }}">
                            <img src="{{ $portfolioImage }}" alt="{{ $itemTitle }}" loading="lazy" decoding="async">
                        </a>
                        <div style="padding:.75rem;">
                            <div style="font-weight:700;font-size:.825rem;color:#111827;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">
                                {{ $itemTitle }}
                            </div>
                        </div>
                    </article>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        @if(!empty($faqItems))
        <div class="sp-card">
            <h2 style="font-size:1.1rem;font-weight:800;color:#111827;margin:0 0 1rem;">
                Questions fréquentes sur {{ $mainKeywordWithPostalCode }}
            </h2>
            <div class="ad-faq">
                @foreach($faqItems as $faq)
                <details>
                    <summary>{{ $faq['question'] }}</summary>
                    <p>{{ $faq['answer'] }}</p>
                </details>
                @endforeach
            </div>
        </div>
        @endif

        @if(isset($activeReviews) && $activeReviews->count() > 0)
        <div class="sp-card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
                <h2 style="font-size:1.1rem;font-weight:800;color:#111827;margin:0;">
                    <i class="fas fa-star mr-2" style="color:#f59e0b;"></i>Avis de nos clients
                </h2>
                @if($ratingDisplay)
                <div style="display:flex;align-items:center;gap:.3rem;font-size:.8rem;color:#6b7280;">
                    @for($i=0;$i<5;$i++)<i class="fas fa-star" style="color:#f59e0b;font-size:.7rem;"></i>@endfor
                    <span style="font-weight:700;color:#111827;margin-left:.3rem;">{{ $ratingDisplay }}/5</span>
                </div>
                @endif
            </div>
            @foreach($activeReviews as $review)
            <div class="sp-review">
                <div class="sp-review-stars">
                    @for($i=1;$i<=5;$i++)
                        <i class="fas fa-star" style="{{ $i <= $review->rating ? 'color:#f59e0b' : 'color:#e5e7eb' }};"></i>
                    @endfor
                </div>
                <p class="sp-review-text">"{{ \Illuminate\Support\Str::limit($review->review_text ?? '', 160) }}"</p>
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <div class="sp-review-author">{{ $review->author_name }}</div>
                    <div class="sp-review-city">
                        @if($review->review_date)
                            {{ \Carbon\Carbon::parse($review->review_date)->translatedFormat('M Y') }}
                        @endif
                    </div>
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

        @if(isset($nearbyAds) && $nearbyAds->count() > 0)
        <div class="sp-card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
                <h2 style="font-size:1.1rem;font-weight:800;color:#111827;margin:0;">
                    Interventions proches pour {{ $serviceLabel }}
                </h2>
            </div>
            <div class="ad-list-grid">
                @foreach($nearbyAds as $nearbyAd)
                <a href="{{ route('ads.show', $nearbyAd->slug) }}" style="border:1px solid #e5e7eb;border-radius:12px;padding:1rem;text-decoration:none;color:inherit;background:#f9fafb;">
                    <div style="font-size:.78rem;color:#6b7280;margin-bottom:.35rem;">
                        {{ $nearbyAd->city->name ?? 'Commune voisine' }}
                        @if($nearbyAd->city->postal_code ?? null) ({{ $nearbyAd->city->postal_code }}) @endif
                    </div>
                    <div style="font-weight:700;color:#111827;line-height:1.4;margin-bottom:.4rem;">{{ $nearbyAd->title }}</div>
                    <div style="font-size:.85rem;color:#6b7280;">{{ Str::limit($nearbyAd->meta_description, 90) }}</div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <div class="sp-cta-band">
            <h3>Prêt pour votre {{ $serviceLabel }} à {{ $cityModel->name ?? $companyCity }} ?</h3>
            <p>Devis gratuit, réponse sous 24h — intervention locale et accompagnement rapide.</p>
            <a href="{{ route('form.step', 'propertyType') }}" class="sp-cta-btn">
                <i class="fas fa-file-alt"></i> Obtenir mon devis gratuit
            </a>
            <br>
            <a href="tel:{{ $companyPhoneRaw }}" class="sp-cta-phone"
               onclick="if(typeof window.trackPhoneCall==='function'){window.trackPhoneCall('{{ $companyPhoneRaw }}','ads/{{ $ad->slug ?? '' }}-cta');}return true;">
                <i class="fas fa-phone"></i> {{ $companyPhone }}
            </a>
        </div>
    </main>

    <aside class="sp-sidebar">
        <div class="sb-cta">
            <div class="sb-cta-title">
                <i class="fas fa-file-alt" style="margin-right:.4rem;"></i>Devis gratuit
            </div>
            <div class="sb-cta-sub">Sans engagement · Réponse sous 24h<br>{{ $cityLabel ?: ($companyCity ?: 'votre région') }}</div>
            <a href="{{ route('form.step', 'propertyType') }}" class="sb-cta-btn">
                Obtenir mon devis <i class="fas fa-arrow-right" style="font-size:.75rem;margin-left:.25rem;"></i>
            </a>
            <div class="sb-cta-microcopy">
                <i class="fas fa-lock" style="font-size:.65rem;"></i> Vos données restent confidentielles
            </div>
        </div>

        <div class="sb-block">
            <div class="sb-phone">
                <div>
                    <div class="sb-phone-label">Nous appeler</div>
                    <a href="tel:{{ $companyPhoneRaw }}" class="sb-phone-num"
                       onclick="if(typeof window.trackPhoneCall==='function'){window.trackPhoneCall('{{ $companyPhoneRaw }}','ads/{{ $ad->slug ?? '' }}-sidebar');}return true;">
                        {{ $companyPhone }}
                    </a>
                </div>
                <div class="sb-phone-icon">
                    <i class="fas fa-phone"></i>
                </div>
            </div>
            <div class="sb-hours">
                <i class="fas fa-clock" style="margin-right:.35rem;"></i> {{ $companyHours }}
            </div>
        </div>

        <div class="sb-block">
            <div class="sb-head">
                <i class="fas fa-award"></i> Pourquoi nous choisir
            </div>
            <div class="sb-body">
                @foreach([
                    'Intervention locale à ' . ($cityModel->name ?? $companyCity),
                    'Prestation adaptée à votre besoin réel',
                    'Devis détaillé et sans surprise',
                    'Suivi rapide avant et après intervention',
                    'Entreprise joignable par téléphone et email',
                    $reviewCount > 0 && $ratingDisplay ? $ratingDisplay . '/5 · ' . $reviewCount . ' avis clients' : 'Clients accompagnés avec sérieux',
                ] as $reason)
                <div class="sb-raison">
                    <span class="sb-raison-dot"><i class="fas fa-check" style="font-size:.5rem;"></i></span>
                    <span class="sb-raison-text">{{ $reason }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="sb-block">
            <div class="sb-head">
                <i class="fas fa-info-circle"></i> Infos pratiques
            </div>
            <div class="sb-body">
                @foreach([
                    ['fas fa-building', 'Société', $companyName],
                    ['fas fa-map-marker-alt', 'Adresse', $companyAddress ?: $companyCity],
                    ['fas fa-envelope', 'Email', $companyEmail ? '<a href="mailto:' . $companyEmail . '">' . $companyEmail . '</a>' : 'Sur demande'],
                    ['fas fa-map', 'Ville ciblée', $cityLabel ?: ($cityModel->name ?? 'Votre ville')],
                ] as $info)
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

        <div class="sb-block">
            <div class="sb-head">
                <i class="fas fa-map"></i> Zone d'intervention
            </div>
            <div class="sb-body">
                <div style="line-height:1;">
                    @foreach(array_filter([
                        $cityModel->name ?? null,
                        $cityModel->region ?? null,
                        $companyCity ?: null,
                    ]) as $zone)
                        <span class="sp-zone-pill">{{ $zone }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="sb-block">
            <div class="sb-head">
                <i class="fas fa-share-alt"></i> Partager cette page
            </div>
            <div class="sb-body">
                <div class="sb-share">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                       target="_blank" rel="noopener" class="sb-share-btn fb">
                        <i class="fab fa-facebook"></i> Facebook
                    </a>
                    <a href="https://wa.me/?text={{ urlencode(($ad->title ?? $serviceLabel) . ' — ' . url()->current()) }}"
                       target="_blank" rel="noopener" class="sb-share-btn wa">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                    <a href="mailto:?subject={{ urlencode($ad->title ?? $serviceLabel) }}&body={{ urlencode(url()->current()) }}"
                       class="sb-share-btn">
                        <i class="fas fa-envelope"></i> Email
                    </a>
                </div>
            </div>
        </div>

        @if((isset($relatedAds) && $relatedAds->count() > 0) || !empty($servicePage))
        <div class="sb-block">
            <div class="sb-head">
                <i class="fas fa-th-large"></i> À découvrir aussi
            </div>
            <div class="sb-body">
                @if(!empty($servicePage))
                <a href="{{ $serviceUrl }}" class="sb-service-link">
                    <span class="sb-service-link-icon"><i class="fas fa-link"></i></span>
                    {{ $serviceLabel }}
                    <i class="fas fa-chevron-right" style="font-size:.65rem;margin-left:auto;color:#d1d5db;"></i>
                </a>
                @endif
                @foreach(($relatedAds ?? collect())->take(5) as $relatedAd)
                <a href="{{ route('ads.show', $relatedAd->slug) }}" class="sb-service-link">
                    <span class="sb-service-link-icon"><i class="fas fa-map-pin"></i></span>
                    {{ $relatedAd->title }}
                    <i class="fas fa-chevron-right" style="font-size:.65rem;margin-left:auto;color:#d1d5db;"></i>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </aside>

</div>
</div>

</div>
@endsection

@push('head')
<script type="application/ld+json">
@json($localBusinessSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
</script>
<script type="application/ld+json">
@json($serviceSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
</script>
@endpush

@push('scripts')
<script>
(function() {
    if (typeof window.trackPhoneCall === 'undefined') {
        window.trackPhoneCall = function(phoneNumber, sourcePage) {
            const payload = {
                phone_number: phoneNumber || '{{ $companyPhoneRaw }}',
                source_page: sourcePage || window.location.pathname,
                referrer_url: document.referrer || window.location.href,
            };
            fetch('/api/track-phone-call', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify(payload),
                keepalive: true,
            }).catch(() => {});
        };
    }
})();
</script>
@endpush
