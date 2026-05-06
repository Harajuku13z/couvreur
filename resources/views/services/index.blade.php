@extends('layouts.app')

@php
    $currentPage = 'services';
    $seoData = \App\Helpers\SeoHelper::generateMetaTags('services', [
        'title' => 'Nos Services',
        'description' => 'Découvrez tous nos services de ' . setting('company_specialization', 'travaux de rénovation') . '. Solutions complètes et professionnelles.',
        'image' => file_exists(public_path('images/og-services.jpg')) ? asset('images/og-services.jpg') : null,
    ]);
    $pageTitle = $seoData['title'];
    $pageDescription = $seoData['description'];
    $pageImage = $seoData['og:image'];
@endphp

@push('head')
<style>
@import url('https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,600;0,9..144,700;1,9..144,400&family=Manrope:wght@400;500;600;700&display=swap');
.sv{font-family:'Manrope',sans-serif;background:#FAF7F2;color:#1F1A14;}
.sv h1,.sv h2,.sv h3{font-family:'Fraunces',serif;}
.sv-hero{background:#1F1A14;position:relative;overflow:hidden;padding:5rem 0 4rem;}
.sv-hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 80% 60% at 50% 0%,rgba(255,255,255,.04) 0%,transparent 60%);}
.sv-eyebrow{display:inline-block;font-size:.75rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.5);margin-bottom:1rem;}
.sv-hero-title{font-size:clamp(2.25rem,5vw,3.5rem);font-weight:700;color:#fff;line-height:1.15;margin-bottom:1rem;}
.sv-hero-sub{font-size:1.125rem;color:rgba(255,255,255,.7);max-width:560px;margin:0 auto;}
.sv-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.5rem;padding:4rem 0;}
.sv-card{background:#fff;border:1px solid rgba(30,20,10,.08);border-radius:1rem;overflow:hidden;transition:transform .3s,box-shadow .3s,border-color .3s;}
.sv-card:hover{transform:translateY(-6px);box-shadow:0 20px 48px rgba(30,20,10,.1);border-color:rgba(30,20,10,.15);}
.sv-card-img{height:200px;overflow:hidden;position:relative;background:#F2EDE4;}
.sv-card-img img{width:100%;height:100%;object-fit:cover;transition:transform .5s;}
.sv-card:hover .sv-card-img img{transform:scale(1.05);}
.sv-card-icon{width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--primary-color,#3b82f6),var(--secondary-color,#10b981));}
.sv-card-body{padding:1.5rem;}
.sv-card-name{font-size:1.125rem;font-weight:700;color:#1F1A14;margin-bottom:.5rem;}
.sv-card-desc{font-size:.875rem;color:#6B6157;line-height:1.6;margin-bottom:1.25rem;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;}
.sv-card-link{font-size:.875rem;font-weight:700;color:var(--primary-color,#3b82f6);display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;transition:gap .2s;}
.sv-card-link:hover{gap:.7rem;}
.sv-badge{display:inline-flex;align-items:center;gap:.3rem;font-size:.7rem;font-weight:700;padding:.25rem .6rem;border-radius:999px;background:rgba(245,158,11,.12);color:#92400e;text-transform:uppercase;letter-spacing:.06em;}
.sv-empty{text-align:center;padding:5rem 1rem;}
.sv-cta{background:#1F1A14;padding:5rem 0;text-align:center;}
.sv-cta-title{font-family:'Fraunces',serif;font-size:clamp(1.75rem,4vw,2.75rem);font-weight:700;color:#fff;margin-bottom:1rem;}
.sv-cta-sub{font-size:1rem;color:rgba(255,255,255,.65);max-width:500px;margin:0 auto 2.5rem;}
.sv-btn-primary{display:inline-flex;align-items:center;gap:.6rem;padding:.9rem 2rem;border-radius:999px;background:var(--primary-color,#3b82f6);color:#fff;font-weight:700;text-decoration:none;transition:opacity .2s,transform .2s;}
.sv-btn-primary:hover{opacity:.9;transform:translateY(-2px);}
.sv-btn-outline{display:inline-flex;align-items:center;gap:.6rem;padding:.9rem 2rem;border-radius:999px;border:1.5px solid rgba(255,255,255,.25);color:#fff;font-weight:600;text-decoration:none;transition:background .2s,transform .2s;}
.sv-btn-outline:hover{background:rgba(255,255,255,.08);transform:translateY(-2px);}
.sv-actions{display:flex;flex-wrap:wrap;gap:1rem;justify-content:center;}
</style>
@endpush

@section('content')
<div class="sv">
    {{-- Hero --}}
    <section class="sv-hero">
        <div class="site-shell" style="text-align:center;position:relative;z-index:1;">
            <span class="sv-eyebrow">Nos prestations</span>
            <h1 class="sv-hero-title">Tous nos services<br><em style="font-style:italic;color:rgba(255,255,255,.6);">de {{ setting('company_specialization','rénovation') }}</em></h1>
            <p class="sv-hero-sub">Des solutions complètes et professionnelles pour tous vos projets. Artisans qualifiés, devis gratuit.</p>
        </div>
    </section>

    {{-- Grille services --}}
    <section style="background:#FAF7F2;">
        <div class="site-shell">
            @if(isset($visibleServices) && $visibleServices->count() > 0)
            <div class="sv-grid">
                @foreach($visibleServices as $service)
                @php
                    $slug = $service['slug'] ?? \Illuminate\Support\Str::slug($service['name'] ?? 'service');
                    $svcImg = null;
                    if (!empty($service['featured_image'])) {
                        $raw = $service['featured_image'];
                        $svcImg = str_starts_with($raw,'http') ? $raw : url($raw);
                    } elseif (!empty($service['image'])) {
                        $raw = $service['image'];
                        $svcImg = str_starts_with($raw,'http') ? $raw : url($raw);
                    }
                    $svcDesc = $service['short_description'] ?? $service['description'] ?? '';
                    $svcIcon = $service['icon'] ?? 'fas fa-tools';
                @endphp
                <div class="sv-card">
                    <div class="sv-card-img">
                        @if($svcImg)
                            <img src="{{ $svcImg }}" alt="{{ $service['name'] }}" loading="lazy">
                        @else
                            <div class="sv-card-icon">
                                <i class="{{ $svcIcon }} fa-3x" style="color:rgba(255,255,255,.9);"></i>
                            </div>
                        @endif
                    </div>
                    <div class="sv-card-body">
                        @if(!empty($service['is_featured']))
                        <div style="margin-bottom:.6rem;">
                            <span class="sv-badge">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                Vedette
                            </span>
                        </div>
                        @endif
                        <h3 class="sv-card-name">{{ $service['name'] }}</h3>
                        @if($svcDesc)
                        <p class="sv-card-desc">{{ $svcDesc }}</p>
                        @endif
                        <a href="{{ route('services.show', $slug) }}" class="sv-card-link">
                            En savoir plus
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="sv-empty">
                <div style="width:80px;height:80px;border-radius:50%;background:#F2EDE4;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;">
                    <i class="fas fa-tools fa-2x" style="color:#9C8E84;"></i>
                </div>
                <h3 style="font-family:'Fraunces',serif;font-size:1.5rem;color:#1F1A14;margin-bottom:.5rem;">Aucun service disponible</h3>
                <p style="color:#6B6157;">Nos services sont en cours de mise à jour. Revenez bientôt !</p>
                <a href="{{ url('/') }}" class="sv-btn-primary" style="margin-top:1.5rem;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Retour à l'accueil
                </a>
            </div>
            @endif
        </div>
    </section>

    {{-- CTA --}}
    <section class="sv-cta">
        <div class="site-shell">
            <p style="font-size:.75rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:.75rem;">Passez à l'action</p>
            <h2 class="sv-cta-title">Besoin d'un devis personnalisé ?</h2>
            <p class="sv-cta-sub">Contactez-nous pour un devis gratuit adapté à votre projet. Réponse sous 24h.</p>
            <div class="sv-actions">
                <a href="{{ route('form.step', 'propertyType') }}" class="sv-btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    Devis gratuit en 2 min
                </a>
                @if(setting('company_phone'))
                <a href="tel:{{ setting('company_phone') }}" class="sv-btn-outline">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13.5 19.79 19.79 0 0 1 1.61 5c-.11-1.08.72-2 1.8-2h3a2 2 0 0 1 2 1.72c.13 1 .35 1.97.67 2.9a2 2 0 0 1-.45 2.11L7.91 10.4a16 16 0 0 0 5.69 5.69l1.67-1.67a2 2 0 0 1 2.11-.45c.93.32 1.9.54 2.9.67A2 2 0 0 1 22 16.92z"/></svg>
                    {{ setting('company_phone') }}
                </a>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection
