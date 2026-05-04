@extends('layouts.app')

@php
    // Métadonnées passées depuis AdPublicController::show()
@endphp

@section('title', $pageTitle ?? 'Service professionnel')
@section('description', $pageDescription ?? 'Service professionnel de qualité. Devis gratuit et intervention rapide.')
@section('keywords', !empty($pageKeywords) ? $pageKeywords . (!empty($extendedKeywords) ? ', ' . implode(', ', $extendedKeywords) : '') : (!empty($extendedKeywords) ? implode(', ', $extendedKeywords) : ''))

@push('head')
<style>
    html, body { overflow-x: hidden; }

    /* ── Hero ─────────────────────────────────────────────────── */
    .ad-hero {
        position: relative;
        overflow: hidden;
    }
    .ad-hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(0,0,0,.72) 0%, rgba(0,0,0,.45) 60%, rgba(0,0,0,.25) 100%);
        z-index: 1;
    }
    .ad-hero-content { position: relative; z-index: 2; }

    /* ── Badges confiance ─────────────────────────────────────── */
    .trust-badge {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        background: rgba(255,255,255,.15);
        backdrop-filter: blur(6px);
        border: 1px solid rgba(255,255,255,.25);
        border-radius: 999px;
        padding: .35rem .9rem;
        font-size: .82rem;
        font-weight: 600;
        color: #fff;
    }

    /* ── Sidebar CTA sticky ───────────────────────────────────── */
    .cta-sidebar {
        position: sticky;
        top: 90px;
    }
    .cta-card {
        background: #fff;
        border-radius: 1.25rem;
        box-shadow: 0 8px 40px rgba(0,0,0,.12);
        overflow: hidden;
    }
    .cta-card-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        padding: 1.5rem;
        text-align: center;
        color: #fff;
    }

    /* ── Contenu annonce ──────────────────────────────────────── */
    .ad-content-wrap {
        word-wrap: break-word;
        overflow-wrap: break-word;
        overflow-x: hidden;
    }
    .ad-content-wrap * {
        max-width: 100%;
        box-sizing: border-box;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    .ad-content-wrap img, .ad-content-wrap iframe, .ad-content-wrap video {
        max-width: 100% !important;
        height: auto;
        display: block;
    }
    .ad-content-wrap table { width: 100% !important; table-layout: fixed; }
    .ad-content-wrap pre, .ad-content-wrap code { white-space: pre-wrap !important; }
    .ad-content-wrap a { color: var(--primary-color); }

    /* ── Cards services similaires ────────────────────────────── */
    .related-card {
        transition: transform .25s ease, box-shadow .25s ease;
        border-radius: 1rem;
        overflow: hidden;
    }
    .related-card:hover { transform: translateY(-4px); box-shadow: 0 16px 40px rgba(0,0,0,.12); }

    /* ── Avis ─────────────────────────────────────────────────── */
    .review-card {
        border-radius: 1rem;
        background: #fff;
        border: 1px solid #f1f5f9;
        transition: box-shadow .25s ease;
    }
    .review-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.08); }

    /* ── CTA bottom banner ────────────────────────────────────── */
    .bottom-cta {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        border-radius: 1.5rem;
    }

    /* ── Boutons ──────────────────────────────────────────────── */
    .btn-primary {
        background: var(--primary-color);
        color: #fff;
        font-weight: 700;
        border-radius: .75rem;
        padding: .85rem 2rem;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        transition: background .2s, transform .2s;
        text-decoration: none;
    }
    .btn-primary:hover { background: var(--secondary-color); transform: translateY(-1px); color: #fff; }
    .btn-accent {
        background: var(--accent-color, #f59e0b);
        color: #fff;
        font-weight: 700;
        border-radius: .75rem;
        padding: .85rem 2rem;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        transition: background .2s, transform .2s;
        text-decoration: none;
    }
    .btn-accent:hover { filter: brightness(1.1); transform: translateY(-1px); color: #fff; }
    .btn-outline-white {
        border: 2px solid rgba(255,255,255,.7);
        color: #fff;
        font-weight: 700;
        border-radius: .75rem;
        padding: .8rem 1.75rem;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        transition: background .2s;
        text-decoration: none;
        background: transparent;
    }
    .btn-outline-white:hover { background: rgba(255,255,255,.15); color: #fff; }

    /* ── Breadcrumb ───────────────────────────────────────────── */
    .breadcrumb-bar { background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
    .breadcrumb-bar a { color: var(--primary-color); text-decoration: none; font-size: .875rem; }
    .breadcrumb-bar span { color: #64748b; font-size: .875rem; }

    /* ── Pulse animation pour le bouton appel ─────────────────── */
    @keyframes pulse-ring {
        0% { box-shadow: 0 0 0 0 rgba(var(--primary-color-rgb,59,130,246),.5); }
        70% { box-shadow: 0 0 0 10px rgba(var(--primary-color-rgb,59,130,246),0); }
        100% { box-shadow: 0 0 0 0 rgba(var(--primary-color-rgb,59,130,246),0); }
    }
    .btn-call-pulse { animation: pulse-ring 2s ease infinite; }

    @media (max-width: 768px) {
        .cta-sidebar { position: static; }
        .ad-hero { min-height: 70vh !important; }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 overflow-x-hidden">

    {{-- ═══════════════════════════════════════════════════════════
         HERO
    ═══════════════════════════════════════════════════════════ --}}
    <section class="ad-hero min-h-[60vh] flex items-center pt-16 pb-10"
             @if(!empty($featuredImage))
             style="background: url('{{ str_starts_with($featuredImage,'http') ? $featuredImage : asset($featuredImage) }}') center/cover no-repeat; background-color: var(--secondary-color);"
             @else
             style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);"
             @endif>
        <div class="ad-hero-overlay"></div>
        <div class="ad-hero-content container mx-auto px-4">

            {{-- Breadcrumb --}}
            <nav class="mb-6 flex flex-wrap items-center gap-2 text-white/75 text-sm" aria-label="Fil d'Ariane">
                <a href="{{ route('home') }}" class="hover:text-white transition"><i class="fas fa-home mr-1"></i>Accueil</a>
                <i class="fas fa-chevron-right text-xs text-white/40"></i>
                <a href="{{ route('ads.index') }}" class="hover:text-white transition">Services</a>
                <i class="fas fa-chevron-right text-xs text-white/40"></i>
                <span class="text-white/90">{{ Str::limit($ad->title ?? 'Service', 40) }}</span>
            </nav>

            {{-- Trust badges --}}
            @php
                $garantie = setting('trust_garantie_decennale', true);
                $rge      = setting('trust_certifie_rge', true);
                $avgRating = \App\Models\Review::where('is_active', true)->avg('rating');
                $nbReviews = \App\Models\Review::where('is_active', true)->count();
            @endphp
            <div class="flex flex-wrap gap-2 mb-6">
                @if($garantie)
                <span class="trust-badge"><i class="fas fa-shield-alt text-amber-300"></i> Garantie décennale</span>
                @endif
                @if($rge)
                <span class="trust-badge"><i class="fas fa-leaf text-emerald-300"></i> Certifié RGE</span>
                @endif
                @if($avgRating > 0)
                <span class="trust-badge"><i class="fas fa-star text-yellow-300"></i> {{ number_format($avgRating,1) }}/5 ({{ $nbReviews }} avis)</span>
                @endif
                <span class="trust-badge"><i class="fas fa-check-circle text-green-300"></i> Devis gratuit</span>
            </div>

            <div class="max-w-3xl">
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-white leading-tight mb-4 drop-shadow-lg">
                    {{ $ad->title ?? 'Service professionnel' }}
                </h1>
                <p class="text-lg md:text-xl text-white/90 mb-8 leading-relaxed">
                    Intervention professionnelle à <strong>{{ $cityModel->name ?? 'votre ville' }}</strong>
                    @if($cityModel->postal_code ?? null) ({{ $cityModel->postal_code }})@endif
                    &mdash; Réponse rapide, devis gratuit et sans engagement.
                </p>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('form.step', 'propertyType') }}" class="btn-accent text-lg shadow-xl">
                        <i class="fas fa-calculator"></i> Devis gratuit en ligne
                    </a>
                    <a href="tel:{{ setting('company_phone_raw') }}"
                       class="btn-outline-white text-lg btn-call-pulse"
                       onclick="if(typeof window.trackPhoneCall==='function'){window.trackPhoneCall('{{ setting('company_phone_raw') }}','ads/{{ $ad->slug ?? '' }}');}return true;">
                        <i class="fas fa-phone"></i> {{ setting('company_phone') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         CONTENU PRINCIPAL + SIDEBAR
    ═══════════════════════════════════════════════════════════ --}}
    <section class="py-12 md:py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-7xl mx-auto">
                <div class="lg:grid lg:grid-cols-3 lg:gap-10 items-start">

                    {{-- Colonne contenu (2/3) --}}
                    <div class="lg:col-span-2">

                        {{-- Info rapide --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
                            @php
                                $infoItems = [
                                    ['icon'=>'fas fa-map-marker-alt','label'=>$cityModel->name ?? 'Votre ville','color'=>'blue'],
                                    ['icon'=>'fas fa-clock','label'=>'Réponse sous 24h','color'=>'green'],
                                    ['icon'=>'fas fa-file-invoice','label'=>'Devis gratuit','color'=>'amber'],
                                    ['icon'=>'fas fa-tools','label'=>'Artisan qualifié','color'=>'purple'],
                                ];
                            @endphp
                            @foreach($infoItems as $item)
                            <div class="bg-white rounded-xl shadow-sm p-4 flex flex-col items-center text-center border border-gray-100 hover:shadow-md transition">
                                <div class="w-10 h-10 rounded-full bg-{{ $item['color'] }}-100 flex items-center justify-center mb-2">
                                    <i class="{{ $item['icon'] }} text-{{ $item['color'] }}-600"></i>
                                </div>
                                <span class="text-xs font-semibold text-gray-700 leading-tight">{{ $item['label'] }}</span>
                            </div>
                            @endforeach
                        </div>

                        {{-- Contenu HTML de l'annonce --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-10 mb-8 ad-content-wrap">
                            <div class="prose prose-sm md:prose-base max-w-none dark:prose-invert">
                                {!! $ad->content_html ?? '<p>Contenu en cours de chargement...</p>' !!}
                            </div>

                            @if(!empty($extendedKeywords))
                            <div style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
                                {{ implode(', ', $extendedKeywords) }}
                            </div>
                            @endif
                        </div>

                        {{-- CTA banner inline (mobile + desktop) --}}
                        <div class="bottom-cta p-8 md:p-10 text-white text-center mb-10">
                            <p class="text-sm uppercase tracking-widest text-white/70 mb-2 font-semibold">Prêt à démarrer ?</p>
                            <h2 class="text-2xl md:text-3xl font-extrabold mb-3">
                                Votre projet à {{ $cityModel->name ?? 'votre ville' }}
                            </h2>
                            <p class="text-white/85 mb-6 max-w-xl mx-auto">
                                Obtenez un devis personnalisé et gratuit. Notre équipe vous répond rapidement.
                            </p>
                            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                <a href="{{ route('form.step', 'propertyType') }}" class="btn-accent text-lg shadow-lg">
                                    <i class="fas fa-calculator"></i> Simulateur de devis
                                </a>
                                <a href="tel:{{ setting('company_phone_raw') }}"
                                   class="btn-outline-white text-lg"
                                   onclick="if(typeof window.trackPhoneCall==='function'){window.trackPhoneCall('{{ setting('company_phone_raw') }}','ads/{{ $ad->slug ?? '' }}');}return true;">
                                    <i class="fas fa-phone"></i> Appeler maintenant
                                </a>
                            </div>
                        </div>

                        {{-- Réalisations --}}
                        @if(!empty($portfolioItems) && count($portfolioItems) > 0)
                        <div class="mb-10">
                            <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                                <span class="w-1 h-7 rounded-full inline-block" style="background:var(--primary-color);"></span>
                                Nos Réalisations
                            </h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                @foreach(array_slice($portfolioItems, 0, 4) as $portfolioItem)
                                @if(is_array($portfolioItem) && !empty($portfolioItem['images']) && is_array($portfolioItem['images']))
                                @php
                                    $itemTitle = $portfolioItem['title'] ?? 'Réalisation';
                                    $itemSlug  = !empty($portfolioItem['slug']) ? $portfolioItem['slug'] : Str::slug($itemTitle);
                                @endphp
                                <a href="{{ route('portfolio.show', $itemSlug) }}" class="related-card block bg-white shadow-sm border border-gray-100 group">
                                    <div class="relative overflow-hidden h-52">
                                        <img src="{{ asset($portfolioItem['images'][0]) }}"
                                             alt="{{ ($mainKeyword ?? '') . ' ' . $itemTitle }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition duration-300 flex items-end p-4">
                                            <span class="text-white text-sm font-semibold"><i class="fas fa-search-plus mr-1"></i> Voir la réalisation</span>
                                        </div>
                                    </div>
                                    <div class="p-4">
                                        <h3 class="font-bold text-gray-900 mb-1">{{ $itemTitle }}</h3>
                                        @if(!empty($portfolioItem['description']))
                                        <p class="text-gray-500 text-sm">{{ Str::limit($portfolioItem['description'], 80) }}</p>
                                        @endif
                                    </div>
                                </a>
                                @endif
                                @endforeach
                            </div>
                            @if(count($portfolioItems) > 4)
                            <div class="text-center mt-6">
                                <a href="{{ route('portfolio.index') }}" class="btn-primary">
                                    <i class="fas fa-images"></i> Voir toutes nos réalisations
                                </a>
                            </div>
                            @endif
                        </div>
                        @endif

                    </div>{{-- /col contenu --}}

                    {{-- Sidebar CTA (1/3) --}}
                    <div class="mt-10 lg:mt-0">
                        <div class="cta-sidebar">

                            {{-- Card devis --}}
                            <div class="cta-card mb-6">
                                <div class="cta-card-header">
                                    <div class="text-3xl mb-2">🏠</div>
                                    <h3 class="text-lg font-extrabold">Devis Gratuit</h3>
                                    <p class="text-white/80 text-sm mt-1">Réponse sous 24h • Sans engagement</p>
                                </div>
                                <div class="p-6 space-y-3">
                                    <a href="{{ route('form.step', 'propertyType') }}" class="btn-accent w-full justify-center text-base">
                                        <i class="fas fa-calculator"></i> Simulateur en ligne
                                    </a>
                                    <a href="tel:{{ setting('company_phone_raw') }}"
                                       class="btn-primary w-full justify-center text-base btn-call-pulse"
                                       onclick="if(typeof window.trackPhoneCall==='function'){window.trackPhoneCall('{{ setting('company_phone_raw') }}','ads/{{ $ad->slug ?? '' }}-sidebar');}return true;">
                                        <i class="fas fa-phone"></i> {{ setting('company_phone') }}
                                    </a>
                                </div>
                            </div>

                            {{-- Certifications --}}
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                                <h4 class="font-bold text-gray-900 mb-4 text-sm uppercase tracking-wider">Certifications &amp; Garanties</h4>
                                <ul class="space-y-3">
                                    @foreach([
                                        ['fas fa-shield-alt','text-blue-600','Garantie décennale'],
                                        ['fas fa-leaf','text-green-600','Certification RGE'],
                                        ['fas fa-award','text-amber-600','Qualibat'],
                                        ['fas fa-hard-hat','text-slate-600','Artisan qualifié'],
                                    ] as $cert)
                                    <li class="flex items-center gap-3 text-sm text-gray-700">
                                        <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center flex-shrink-0">
                                            <i class="{{ $cert[0] }} {{ $cert[1] }}"></i>
                                        </div>
                                        {{ $cert[2] }}
                                    </li>
                                    @endforeach
                                </ul>
                            </div>

                            {{-- Zone d'intervention --}}
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                                <h4 class="font-bold text-gray-900 mb-3 text-sm uppercase tracking-wider">Zone d'intervention</h4>
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-red-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <i class="fas fa-map-marker-alt text-red-500"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $cityModel->name ?? 'Votre ville' }}
                                            @if($cityModel->postal_code ?? null) <span class="text-gray-500">({{ $cityModel->postal_code }})</span>@endif
                                        </p>
                                        <p class="text-xs text-gray-500 mt-0.5">et communes environnantes</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Avis rapide --}}
                            @if($avgRating > 0)
                            <div class="bg-amber-50 border border-amber-100 rounded-2xl p-5 text-center">
                                <div class="text-3xl font-extrabold text-amber-600">{{ number_format($avgRating, 1) }}<span class="text-lg">/5</span></div>
                                <div class="flex justify-center gap-0.5 my-1">
                                    @for($i=1;$i<=5;$i++)
                                    <i class="fas fa-star {{ $i <= round($avgRating) ? 'text-amber-400' : 'text-amber-200' }} text-sm"></i>
                                    @endfor
                                </div>
                                <p class="text-xs text-amber-700 font-medium">{{ $nbReviews }} avis clients</p>
                            </div>
                            @endif

                        </div>
                    </div>{{-- /sidebar --}}

                </div>{{-- /grid --}}
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         SERVICES SIMILAIRES
    ═══════════════════════════════════════════════════════════ --}}
    @if(isset($relatedAds) && $relatedAds->count() > 0)
    <section class="py-14 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-10">
                    <p class="text-sm font-semibold uppercase tracking-widest mb-2" style="color:var(--primary-color);">Découvrez aussi</p>
                    <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900">
                        Autres services à {{ $cityModel->name ?? 'votre ville' }}
                    </h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($relatedAds as $relatedAd)
                    <a href="{{ route('ads.show', $relatedAd->slug) }}" class="related-card block bg-gray-50 border border-gray-100 p-6 group">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl flex-shrink-0 flex items-center justify-center"
                                 style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                                <i class="fas fa-tools text-white"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-gray-900 group-hover:text-primary transition mb-1 truncate"
                                    style="--text-primary: var(--primary-color);">
                                    {{ $relatedAd->title }}
                                </h3>
                                <p class="text-gray-500 text-sm line-clamp-2">{{ Str::limit($relatedAd->meta_description, 90) }}</p>
                                <span class="mt-2 inline-flex items-center gap-1 text-sm font-semibold" style="color:var(--primary-color);">
                                    Voir le service <i class="fas fa-arrow-right text-xs"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- ═══════════════════════════════════════════════════════════
         AVIS CLIENTS
    ═══════════════════════════════════════════════════════════ --}}
    <section class="py-14 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-10">
                    <p class="text-sm font-semibold uppercase tracking-widest mb-2" style="color:var(--primary-color);">Témoignages</p>
                    <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900">Ce que disent nos clients</h2>
                </div>

                @php $reviews = \App\Models\Review::where('is_active', true)->take(3)->get(); @endphp

                @if($reviews->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    @foreach($reviews as $review)
                    <div class="review-card p-6">
                        {{-- Stars --}}
                        <div class="flex gap-0.5 mb-3">
                            @for($i=1;$i<=5;$i++)
                            <i class="fas fa-star {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-200' }} text-sm"></i>
                            @endfor
                        </div>
                        {{-- Texte --}}
                        <p class="text-gray-700 text-sm leading-relaxed mb-4 italic">
                            "{{ $review->review_text ? Str::limit($review->review_text, 140) : 'Très satisfait du service.' }}"
                        </p>
                        {{-- Auteur --}}
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0">
                                @if($review->author_photo_url)
                                <img src="{{ $review->author_photo_url }}" alt="{{ $review->author_name }}" class="w-full h-full object-cover">
                                @else
                                <div class="w-full h-full flex items-center justify-center text-white font-bold text-sm"
                                     style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                                    {{ $review->author_initials ?? substr($review->author_name,0,1) }}
                                </div>
                                @endif
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 text-sm">{{ $review->author_name }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ $review->review_date ? \Carbon\Carbon::parse($review->review_date)->format('M Y') : '' }}
                                    @if($review->source && $review->source !== 'manual')
                                    &bull;
                                    @if(str_contains($review->source,'Google'))<i class="fab fa-google"></i> Google@else{{ ucfirst($review->source) }}@endif
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="text-center">
                    <a href="{{ route('reviews.all') }}" class="btn-primary">
                        <i class="fas fa-comments"></i> Voir tous les avis
                    </a>
                </div>
                @endif
            </div>
        </div>
    </section>

</div>
@endsection

@push('head')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'serviceType' => $mainKeyword ?? 'Service',
    'provider' => [
        '@type' => 'LocalBusiness',
        'name' => setting('company_name', 'Votre Entreprise'),
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => $cityModel->name ?? '',
            'postalCode' => $cityModel->postal_code ?? '',
            'addressCountry' => 'FR',
        ],
        'telephone' => setting('company_phone_raw', ''),
        'url' => url('/'),
    ],
    'areaServed' => [
        '@type' => 'City',
        'name' => $cityModel->name ?? '',
        'postalCode' => $cityModel->postal_code ?? '',
    ],
    'description' => strip_tags($pageDescription ?? ''),
    'url' => url()->current(),
] + (!empty($extendedKeywords) ? ['keywords' => implode(', ', array_slice($extendedKeywords, 0, 10))] : []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@push('scripts')
<script>
(function() {
    if (typeof window.trackPhoneCall === 'undefined') {
        window.trackPhoneCall = function(phoneNumber, sourcePage) {
            const payload = {
                phone_number: phoneNumber || '{{ setting('company_phone_raw') }}',
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
