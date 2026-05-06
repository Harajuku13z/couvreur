@extends('layouts.app')

@php
    $currentPage = $currentPage ?? 'portfolio';
@endphp

@push('head')
<style>
@import url('https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,600;0,9..144,700;1,9..144,400&family=Manrope:wght@400;500;600;700&display=swap');
.pf{font-family:'Manrope',sans-serif;background:#FAF7F2;color:#1F1A14;}
.pf h1,.pf h2,.pf h3{font-family:'Fraunces',serif;}
.pf-hero{background:#1F1A14;position:relative;overflow:hidden;padding:5rem 0 4rem;text-align:center;}
.pf-hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 80% 60% at 50% 0%,rgba(255,255,255,.04) 0%,transparent 60%);}
.pf-eyebrow{font-size:.75rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.45);display:block;margin-bottom:1rem;}
.pf-hero-title{font-size:clamp(2.25rem,5vw,3.5rem);font-weight:700;color:#fff;line-height:1.15;margin-bottom:1rem;}
.pf-hero-sub{font-size:1.1rem;color:rgba(255,255,255,.65);max-width:540px;margin:0 auto;}
.pf-filters{background:#fff;border-bottom:1px solid rgba(30,20,10,.07);padding:1.25rem 0;}
.pf-pill{border:1.5px solid rgba(30,20,10,.12);background:transparent;color:#6B6157;padding:.5rem 1.25rem;border-radius:999px;font-size:.8125rem;font-weight:600;cursor:pointer;transition:all .2s;font-family:'Manrope',sans-serif;}
.pf-pill:hover{border-color:rgba(30,20,10,.3);color:#1F1A14;transform:translateY(-1px);}
.pf-pill.is-active{background:#1F1A14;color:#fff;border-color:#1F1A14;}
.pf-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.5rem;padding:4rem 0;}
.pf-card{background:#fff;border:1px solid rgba(30,20,10,.08);border-radius:1rem;overflow:hidden;text-decoration:none;display:block;transition:transform .3s,box-shadow .3s,border-color .3s;color:inherit;}
.pf-card:hover{transform:translateY(-6px);box-shadow:0 20px 48px rgba(30,20,10,.1);border-color:rgba(30,20,10,.15);}
.pf-card-img{aspect-ratio:16/10;position:relative;overflow:hidden;background:#F2EDE4;}
.pf-card-img img{width:100%;height:100%;object-fit:cover;transition:transform .5s;display:block;}
.pf-card:hover .pf-card-img img{transform:scale(1.05);}
.pf-card-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(15,12,8,.6) 0%,rgba(15,12,8,.1) 50%,transparent 100%);}
.pf-card-label{position:absolute;bottom:1rem;left:1rem;right:1rem;display:flex;align-items:center;justify-content:space-between;color:#fff;font-size:.8125rem;font-weight:600;}
.pf-card-body{padding:1.5rem;}
.pf-card-title{font-size:1.1rem;font-weight:700;color:#1F1A14;margin-bottom:.5rem;transition:color .2s;}
.pf-card:hover .pf-card-title{color:var(--primary-color,#3b82f6);}
.pf-card-desc{font-size:.8125rem;color:#6B6157;line-height:1.6;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;margin-bottom:1rem;}
.pf-card-footer{display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap;}
.pf-tag{font-size:.7rem;font-weight:700;padding:.2rem .6rem;border-radius:999px;background:rgba(30,20,10,.06);color:#6B6157;text-transform:uppercase;letter-spacing:.06em;}
.pf-link{font-size:.8125rem;font-weight:700;color:var(--primary-color,#3b82f6);display:inline-flex;align-items:center;gap:.3rem;}
.pf-empty{text-align:center;padding:5rem 1rem;}
.pf-cta{background:#1F1A14;padding:5rem 0;text-align:center;}
.pf-cta-title{font-family:'Fraunces',serif;font-size:clamp(1.75rem,4vw,2.75rem);font-weight:700;color:#fff;margin-bottom:1rem;}
.pf-cta-sub{font-size:1rem;color:rgba(255,255,255,.6);max-width:480px;margin:0 auto 2.5rem;}
.pf-btn-primary{display:inline-flex;align-items:center;gap:.6rem;padding:.9rem 2rem;border-radius:999px;background:var(--primary-color,#3b82f6);color:#fff;font-weight:700;text-decoration:none;transition:opacity .2s,transform .2s;font-family:'Manrope',sans-serif;}
.pf-btn-primary:hover{opacity:.9;transform:translateY(-2px);}
.pf-btn-outline{display:inline-flex;align-items:center;gap:.6rem;padding:.9rem 2rem;border-radius:999px;border:1.5px solid rgba(255,255,255,.22);color:#fff;font-weight:600;text-decoration:none;transition:background .2s,transform .2s;font-family:'Manrope',sans-serif;}
.pf-btn-outline:hover{background:rgba(255,255,255,.08);transform:translateY(-2px);}
.pf-cta-trust{display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem;max-width:700px;margin:3.5rem auto 0;}
@media(max-width:600px){.pf-cta-trust{grid-template-columns:1fr;}}
.pf-trust-item{background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);border-radius:.75rem;padding:1.25rem;display:flex;align-items:center;gap:1rem;}
.pf-trust-icon{width:44px;height:44px;border-radius:50%;background:rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.pf-trust-label{font-size:.9375rem;font-weight:600;color:#fff;margin-bottom:.15rem;}
.pf-trust-sub{font-size:.75rem;color:rgba(255,255,255,.55);}
@keyframes fadeInUp{from{opacity:0;transform:translateY(14px);}to{opacity:1;transform:translateY(0);}}
</style>
@endpush

@section('content')
<div class="pf">
    {{-- Hero --}}
    <section class="pf-hero">
        <div class="site-shell" style="position:relative;z-index:1;">
            <span class="pf-eyebrow">Galerie</span>
            <h1 class="pf-hero-title">Nos Réalisations</h1>
            <p class="pf-hero-sub">Découvrez quelques-unes de nos réalisations récentes et laissez-vous inspirer pour votre prochain projet</p>
        </div>
    </section>

    {{-- Filtres --}}
    @if(!empty($serviceTypes) && count($serviceTypes) > 1)
    <div class="pf-filters">
        <div class="site-shell">
            <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:.5rem;" role="tablist">
                <button type="button" class="pf-pill is-active" data-filter="all" role="tab" aria-selected="true">Tous les projets</button>
                @foreach($serviceTypes as $serviceType)
                <button type="button" class="pf-pill" data-filter="{{ \Illuminate\Support\Str::slug($serviceType) }}" role="tab" aria-selected="false">{{ $serviceType }}</button>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Grille --}}
    <section style="background:#FAF7F2;">
        <div class="site-shell">
            @if(isset($visiblePortfolio) && $visiblePortfolio->count() > 0)
            <div class="pf-grid" id="pf-grid">
                @foreach($visiblePortfolio as $item)
                @php
                    $slug = $item['slug'] ?? \Illuminate\Support\Str::slug($item['title'] ?? 'realisation');
                    $url = route('portfolio.show', $slug);
                    $badge = $item['service_type'] ?? $item['work_type'] ?? null;
                    $firstImage = null;
                    if (!empty($item['images'])) {
                        $raw = is_array($item['images']) ? $item['images'][0] : $item['images'];
                        $firstImage = str_starts_with((string)$raw, 'http') ? $raw : asset(ltrim((string)$raw, '/'));
                    }
                    $imgCount = is_array($item['images'] ?? null) ? count($item['images']) : 0;
                @endphp
                <a href="{{ $url }}"
                   class="pf-card pf-item"
                   data-category="{{ \Illuminate\Support\Str::slug($item['work_type'] ?? 'autre') }}"
                   aria-label="{{ $item['title'] ?? 'Réalisation' }}">
                    <div class="pf-card-img">
                        @if($firstImage)
                            <img src="{{ $firstImage }}" alt="{{ $item['title'] ?? 'Réalisation' }}" loading="lazy" decoding="async">
                        @else
                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--primary-color,#3b82f6),var(--secondary-color,#10b981));">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="rgba(255,255,255,.7)"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            </div>
                        @endif
                        <div class="pf-card-overlay"></div>
                        <div class="pf-card-label">
                            <span style="display:inline-flex;align-items:center;gap:.4rem;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                Voir le projet
                            </span>
                            @if($imgCount > 1)
                            <span style="font-size:.75rem;opacity:.85;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:middle;margin-right:3px;"><rect x="2" y="5" width="20" height="14" rx="2"/><rect x="5" y="2" width="14" height="3" rx="1"/></svg>
                                {{ $imgCount }} photos
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="pf-card-body">
                        <h2 class="pf-card-title">{{ $item['title'] ?? 'Réalisation' }}</h2>
                        @if(!empty($item['description']))
                        <p class="pf-card-desc">{{ \Illuminate\Support\Str::limit(strip_tags($item['description']), 110) }}</p>
                        @endif
                        <div class="pf-card-footer">
                            @if($badge)
                            <span class="pf-tag">{{ $badge }}</span>
                            @else
                            <span class="pf-tag">Travaux</span>
                            @endif
                            <span class="pf-link">
                                Détails
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div class="pf-empty">
                <div style="width:80px;height:80px;border-radius:50%;background:#F2EDE4;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#9C8E84" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                </div>
                <h3 style="font-family:'Fraunces',serif;font-size:1.5rem;color:#1F1A14;margin-bottom:.5rem;">Aucune réalisation pour le moment</h3>
                <p style="color:#6B6157;margin-bottom:1.5rem;">Nos réalisations seront bientôt en ligne.</p>
                <a href="{{ route('form.step', 'propertyType') }}" class="pf-btn-primary">Demander un devis</a>
            </div>
            @endif
        </div>
    </section>

    {{-- CTA --}}
    <section class="pf-cta">
        <div class="site-shell">
            <p style="font-size:.75rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.38);margin-bottom:.75rem;">Votre projet</p>
            <h2 class="pf-cta-title">Vous avez un projet similaire ?</h2>
            <p class="pf-cta-sub">Contactez-nous pour discuter de vos besoins et obtenir un devis personnalisé</p>
            <div style="display:flex;flex-wrap:wrap;gap:1rem;justify-content:center;">
                <a href="{{ route('form.step', 'propertyType') }}" class="pf-btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    Devis gratuit
                </a>
                @if(setting('company_phone'))
                <a href="tel:{{ setting('company_phone') }}" class="pf-btn-outline">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13.5 19.79 19.79 0 0 1 1.61 5c-.11-1.08.72-2 1.8-2h3a2 2 0 0 1 2 1.72c.13 1 .35 1.97.67 2.9a2 2 0 0 1-.45 2.11L7.91 10.4a16 16 0 0 0 5.69 5.69l1.67-1.67a2 2 0 0 1 2.11-.45c.93.32 1.9.54 2.9.67A2 2 0 0 1 22 16.92z"/></svg>
                    {{ setting('company_phone') }}
                </a>
                @endif
            </div>
            <div class="pf-cta-trust">
                <div class="pf-trust-item">
                    <div class="pf-trust-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div>
                        <div class="pf-trust-label">Réponse rapide</div>
                        <div class="pf-trust-sub">Devis sous 24h</div>
                    </div>
                </div>
                <div class="pf-trust-item">
                    <div class="pf-trust-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <div>
                        <div class="pf-trust-label">Garantie décennale</div>
                        <div class="pf-trust-sub">Assurance professionnelle</div>
                    </div>
                </div>
                <div class="pf-trust-item">
                    <div class="pf-trust-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </div>
                    <div>
                        <div class="pf-trust-label">Qualité garantie</div>
                        <div class="pf-trust-sub">Artisans qualifiés</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var btns = document.querySelectorAll('.pf-pill');
    var items = document.querySelectorAll('.pf-item');
    if (!btns.length) return;
    btns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var filter = this.getAttribute('data-filter');
            btns.forEach(function(b){ b.classList.remove('is-active'); b.setAttribute('aria-selected','false'); });
            this.classList.add('is-active'); this.setAttribute('aria-selected','true');
            items.forEach(function(item) {
                var show = filter === 'all' || item.getAttribute('data-category') === filter;
                item.style.display = show ? '' : 'none';
                if (show) item.style.animation = 'fadeInUp .4s ease-out';
            });
        });
    });
});
</script>
@endsection
