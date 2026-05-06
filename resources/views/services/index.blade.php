@extends('layouts.app')

@php
    $currentPage = 'services';
    $seoData = \App\Helpers\SeoHelper::generateMetaTags('services', [
        'title' => 'Nos Services',
        'description' => 'Découvrez tous nos services de ' . setting('company_specialization', 'travaux de rénovation') . '. Solutions complètes et professionnelles.',
        'image' => file_exists(public_path('images/og-services.jpg')) ? asset('images/og-services.jpg') : null,
    ]);
    $pageTitle       = $seoData['title'];
    $pageDescription = $seoData['description'];
    $pageImage       = $seoData['og:image'];
    $companyCity     = setting('company_city', 'votre ville');
    $specialization  = setting('company_specialization', 'rénovation');
@endphp

@push('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,700;9..144,800&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
/* ── Base ── */
.ps{font-family:'Manrope',sans-serif;background:#FAF7F2;color:#1F1A14;}
.ps h1,.ps h2,.ps h3{font-family:'Fraunces',serif;}
.ps-shell{width:100%;max-width:1200px;margin:0 auto;padding:0 24px;}

/* ── Hero ── */
.ps-hero{background:#1F1A14;padding:72px 0 56px;position:relative;overflow:hidden;}
.ps-hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 70% 50% at 50% 0%,rgba(255,255,255,.04),transparent 60%);}
.ps-hero-inner{position:relative;z-index:1;}
.ps-eyebrow{display:inline-flex;align-items:center;gap:10px;font-size:11.5px;font-weight:700;letter-spacing:.13em;text-transform:uppercase;color:rgba(255,255,255,.45);margin-bottom:18px;}
.ps-eyebrow-bar{width:20px;height:2px;background:var(--primary-color,#B7472A);border-radius:2px;}
.ps-hero h1{font-size:clamp(2rem,5vw,3.25rem);font-weight:800;color:#fff;line-height:1.1;letter-spacing:-.03em;margin:0 0 16px;max-width:700px;}
.ps-hero h1 em{font-style:italic;color:rgba(255,255,255,.55);}
.ps-hero p{font-size:1.0625rem;color:rgba(255,255,255,.62);max-width:560px;line-height:1.65;margin:0;}

/* ── Grid ── */
.ps-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:20px;padding:52px 0 64px;}

/* ── Card ── */
.ps-card{background:#fff;border:1px solid rgba(30,20,10,.08);border-radius:16px;overflow:hidden;display:flex;flex-direction:column;transition:transform .18s,box-shadow .18s,border-color .18s;text-decoration:none;color:inherit;}
.ps-card:hover{transform:translateY(-5px);box-shadow:0 22px 52px rgba(30,20,10,.11);border-color:rgba(30,20,10,.14);}

/* Card photo */
.ps-card-photo{height:190px;position:relative;overflow:hidden;background:#E8DDD4;flex-shrink:0;}
.ps-card-photo img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .5s;}
.ps-card:hover .ps-card-photo img{transform:scale(1.06);}
.ps-card-photo-bg{width:100%;height:100%;background:linear-gradient(135deg,var(--primary-color,#B7472A),var(--secondary-color,#1F1A14));display:flex;align-items:center;justify-content:center;}
.ps-card-overlay{position:absolute;inset:0;background:linear-gradient(180deg,transparent 35%,rgba(0,0,0,.58) 100%);}
.ps-card-label{position:absolute;bottom:0;left:0;right:0;padding:14px 16px;}
.ps-card-label-type{font-size:11px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:rgba(255,255,255,.75);margin-bottom:4px;}
.ps-card-label-name{font-family:'Fraunces',serif;font-size:20px;font-weight:800;color:#fff;line-height:1.15;letter-spacing:-.01em;}

/* Card body */
.ps-card-body{padding:20px 22px;display:flex;flex-direction:column;gap:14px;flex:1;}
.ps-card-stats{display:flex;justify-content:space-between;font-size:12px;color:#6B6157;border-bottom:1px solid rgba(30,20,10,.07);padding-bottom:13px;gap:8px;}
.ps-card-stat{display:inline-flex;align-items:center;gap:4px;white-space:nowrap;}
.ps-card-stat.primary{color:var(--primary-color,#B7472A);font-weight:700;}
.ps-card-desc{font-size:13.5px;color:#6B6157;line-height:1.6;flex:1;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;}
.ps-card-footer{display:flex;justify-content:space-between;align-items:center;margin-top:2px;}
.ps-card-link{display:inline-flex;align-items:center;gap:6px;font-size:13.5px;font-weight:700;color:var(--primary-color,#B7472A);transition:gap .18s;}
.ps-card:hover .ps-card-link{gap:10px;}

/* ── Services list (second section) ── */
.ps-list-sec{background:#F2EDE4;padding:60px 0;}
.ps-list-head{margin-bottom:36px;}
.ps-list-ey{display:inline-flex;align-items:center;gap:10px;font-size:11.5px;font-weight:700;letter-spacing:.13em;text-transform:uppercase;color:rgba(30,20,10,.38);margin-bottom:14px;}
.ps-list-head h2{font-size:clamp(1.5rem,3.5vw,2.25rem);font-weight:800;color:#1F1A14;line-height:1.15;letter-spacing:-.02em;margin:0;}
.ps-list-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:14px;}
.ps-list-card{background:#fff;border:1px solid rgba(30,20,10,.07);border-radius:14px;padding:20px 22px;display:flex;gap:14px;align-items:flex-start;transition:box-shadow .18s,border-color .18s,transform .18s;text-decoration:none;color:inherit;cursor:pointer;}
.ps-list-card:hover{box-shadow:0 10px 30px rgba(30,20,10,.09);border-color:rgba(30,20,10,.13);transform:translateY(-2px);}
.ps-list-ico{width:44px;height:44px;border-radius:12px;background:rgba(var(--primary-rgb,183,71,42),.1);color:var(--primary-color,#B7472A);display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.ps-list-info h3{font-size:16px;font-weight:700;margin:0 0 5px;color:#1F1A14;}
.ps-list-info p{font-size:13px;color:#6B6157;line-height:1.55;margin:0;}

/* ── CTA ── */
.ps-cta{background:#1F1A14;padding:72px 0;text-align:center;}
.ps-cta h2{font-size:clamp(1.625rem,4vw,2.625rem);font-weight:800;color:#fff;letter-spacing:-.025em;line-height:1.15;margin:0 0 14px;}
.ps-cta p{font-size:1rem;color:rgba(255,255,255,.58);max-width:480px;margin:0 auto 36px;line-height:1.65;}
.ps-cta-btns{display:flex;flex-wrap:wrap;gap:14px;justify-content:center;}
.ps-btn-p{display:inline-flex;align-items:center;gap:8px;padding:.875rem 2rem;border-radius:999px;background:var(--primary-color,#B7472A);color:#fff;font-weight:700;font-size:.9375rem;text-decoration:none;transition:opacity .18s,transform .18s;}
.ps-btn-p:hover{opacity:.88;transform:translateY(-2px);}
.ps-btn-o{display:inline-flex;align-items:center;gap:8px;padding:.875rem 2rem;border-radius:999px;border:1.5px solid rgba(255,255,255,.22);color:#fff;font-weight:600;font-size:.9375rem;text-decoration:none;transition:background .18s,transform .18s;}
.ps-btn-o:hover{background:rgba(255,255,255,.07);transform:translateY(-2px);}

@media(max-width:640px){.ps-card-stats{font-size:11px;}.ps-grid{padding:36px 0 48px;}}
</style>
@endpush

@section('content')
<div class="ps">

    {{-- ── Hero ── --}}
    <section class="ps-hero">
        <div class="ps-shell ps-hero-inner">
            <div class="ps-eyebrow"><span class="ps-eyebrow-bar"></span>Nos prestations</div>
            <h1>Tous nos services<br><em>de {{ $specialization }}</em></h1>
            <p>Un seul interlocuteur pour tous vos travaux. Artisans qualifiés, devis gratuit, garantie décennale.</p>
        </div>
    </section>

    {{-- ── Grille cards photo ── --}}
    <section style="background:#FAF7F2;">
        <div class="ps-shell">
            @if(isset($visibleServices) && $visibleServices->count() > 0)
            <div class="ps-grid">
                @foreach($visibleServices as $service)
                @php
                    $slug    = $service['slug'] ?? \Illuminate\Support\Str::slug($service['name'] ?? 'service');
                    $raw     = $service['featured_image'] ?? $service['image'] ?? '';
                    $svcImg  = $raw ? (str_starts_with($raw,'http') ? $raw : asset(ltrim($raw,'/'))) : null;
                    $svcDesc = $service['short_description'] ?? $service['description'] ?? '';
                    $svcIcon = $service['icon'] ?? 'fas fa-tools';
                @endphp
                <a href="{{ route('services.show', $slug) }}" class="ps-card">
                    <div class="ps-card-photo">
                        @if($svcImg)
                            <img src="{{ $svcImg }}" alt="{{ $service['name'] }}" loading="lazy">
                        @else
                            <div class="ps-card-photo-bg">
                                <i class="{{ $svcIcon }}" style="font-size:2.75rem;color:rgba(255,255,255,.3);"></i>
                            </div>
                        @endif
                        <div class="ps-card-overlay"></div>
                        <div class="ps-card-label">
                            <div class="ps-card-label-type">{{ $specialization }}</div>
                            <div class="ps-card-label-name">{{ $service['name'] }}</div>
                        </div>
                    </div>
                    <div class="ps-card-body">
                        <div class="ps-card-stats">
                            <span class="ps-card-stat">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                Artisan qualifié
                            </span>
                            <span class="ps-card-stat">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>
                                Garantie décennale
                            </span>
                            <span class="ps-card-stat primary">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                Devis sous 24h
                            </span>
                        </div>
                        @if($svcDesc)
                        <p class="ps-card-desc">{{ $svcDesc }}</p>
                        @endif
                        <div class="ps-card-footer">
                            <span class="ps-card-link">
                                En savoir plus
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div style="text-align:center;padding:5rem 1rem;">
                <div style="width:80px;height:80px;border-radius:50%;background:#F2EDE4;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#9C8E84" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76Z"/></svg>
                </div>
                <h3 style="font-family:'Fraunces',serif;font-size:1.5rem;color:#1F1A14;margin-bottom:.5rem;">Aucun service disponible</h3>
                <p style="color:#6B6157;">Nos services sont en cours de mise à jour. Revenez bientôt !</p>
            </div>
            @endif
        </div>
    </section>

    {{-- ── Liste exhaustive ── --}}
    @if(isset($visibleServices) && $visibleServices->count() > 0)
    <section class="ps-list-sec">
        <div class="ps-shell">
            <div class="ps-list-head">
                <div class="ps-list-ey">
                    <span style="width:20px;height:2px;background:var(--primary-color,#B7472A);border-radius:2px;display:block;"></span>
                    Tous nos services
                </div>
                <h2>L'expertise d'un artisan complet,<br>sans sous-traitance.</h2>
            </div>
            <div class="ps-list-grid">
                @foreach($visibleServices as $service)
                @php
                    $slug    = $service['slug'] ?? \Illuminate\Support\Str::slug($service['name'] ?? 'service');
                    $svcDesc = $service['short_description'] ?? $service['description'] ?? '';
                    $svcIcon = $service['icon'] ?? 'fas fa-tools';
                @endphp
                <a href="{{ route('services.show', $slug) }}" class="ps-list-card">
                    <div class="ps-list-ico">
                        <i class="{{ $svcIcon }}" style="font-size:1.25rem;"></i>
                    </div>
                    <div class="ps-list-info">
                        <h3>{{ $service['name'] }}</h3>
                        @if($svcDesc)<p>{{ Str::limit($svcDesc, 100) }}</p>@endif
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ── CTA ── --}}
    <section class="ps-cta">
        <div class="ps-shell">
            <p style="font-size:.75rem;font-weight:700;letter-spacing:.13em;text-transform:uppercase;color:rgba(255,255,255,.35);margin-bottom:.875rem;">Passez à l'action</p>
            <h2>Besoin d'un devis personnalisé ?</h2>
            <p>Contactez-nous pour un devis gratuit adapté à votre projet. Réponse sous 24h, sans engagement.</p>
            <div class="ps-cta-btns">
                <a href="{{ route('form.step', 'propertyType') }}" class="ps-btn-p">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    Devis gratuit en 2 min
                </a>
                @if(setting('company_phone'))
                <a href="tel:{{ preg_replace('/\s+/', '', setting('company_phone')) }}" class="ps-btn-o">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13.5 19.79 19.79 0 0 1 1.61 5c-.11-1.08.72-2 1.8-2h3a2 2 0 0 1 2 1.72c.13 1 .35 1.97.67 2.9a2 2 0 0 1-.45 2.11L7.91 10.4a16 16 0 0 0 5.69 5.69l1.67-1.67a2 2 0 0 1 2.11-.45c.93.32 1.9.54 2.9.67A2 2 0 0 1 22 16.92z"/></svg>
                    {{ setting('company_phone') }}
                </a>
                @endif
            </div>
        </div>
    </section>

</div>
@endsection
