@extends('layouts.app')

@php
    $currentPage = $currentPage ?? 'portfolio';
@endphp

@push('head')
<style>
    .portfolio-page-hero {
        position: relative;
        overflow: hidden;
        min-height: 260px;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 55%, color-mix(in srgb, var(--secondary-color) 85%, #0f172a) 100%);
    }
    @media (min-width: 768px) {
        .portfolio-page-hero { min-height: 320px; }
    }
    .portfolio-page-hero::after {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse 80% 60% at 50% 0%, rgba(255,255,255,0.12) 0%, transparent 55%);
        pointer-events: none;
    }
    .portfolio-filter-pill {
        border: 1px solid rgba(0,0,0,0.08);
        background: rgb(243 244 246);
        color: rgb(55 65 81);
        transition: background .2s ease, color .2s ease, border-color .2s ease, box-shadow .2s ease, transform .2s ease;
    }
    .dark .portfolio-filter-pill:not(.is-active) {
        border-color: rgba(255,255,255,0.12);
        background: rgba(255,255,255,0.06);
        color: #e5e7eb;
    }
    .portfolio-filter-pill:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    }
    .portfolio-filter-pill.is-active {
        background: var(--primary-color) !important;
        color: #fff !important;
        border-color: transparent;
        box-shadow: 0 8px 28px color-mix(in srgb, var(--primary-color) 45%, transparent);
    }
    .portfolio-card {
        border: 1px solid rgba(0,0,0,0.06);
        transition: transform .35s cubic-bezier(0.4, 0, 0.2, 1), box-shadow .35s ease, border-color .25s ease;
    }
    .dark .portfolio-card {
        border-color: rgba(255,255,255,0.08);
        background: rgba(255,255,255,0.03);
    }
    .portfolio-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 24px 48px rgba(0,0,0,0.12);
        border-color: color-mix(in srgb, var(--primary-color) 35%, transparent);
    }
    .portfolio-card:focus-visible {
        outline: 2px solid var(--primary-color);
        outline-offset: 3px;
    }
    .portfolio-card-img-wrap {
        aspect-ratio: 16 / 10;
    }
    @media (prefers-reduced-motion: reduce) {
        .portfolio-card,
        .portfolio-filter-pill { transition: none; }
        .portfolio-card:hover { transform: none; }
    }
    .portfolio-cta-band {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        position: relative;
        overflow: hidden;
    }
    .portfolio-cta-band::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse 70% 80% at 100% 0%, rgba(255,255,255,0.12) 0%, transparent 50%);
        pointer-events: none;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-950">
    <!-- Hero -->
    <section class="portfolio-page-hero flex items-center text-white">
        <div class="site-shell relative z-10 w-full py-14 md:py-20">
            <div class="max-w-3xl mx-auto text-center">
                <p class="text-sm font-semibold uppercase tracking-widest text-white/80 mb-3">Galerie</p>
                <h1 class="text-4xl md:text-5xl font-bold mb-6">Nos Réalisations</h1>
                <p class="text-lg md:text-xl text-white/90 leading-relaxed">
                    Découvrez quelques-unes de nos réalisations récentes et laissez-vous inspirer pour votre prochain projet
                </p>
            </div>
        </div>
    </section>

    <!-- Filtres -->
    @if(!empty($serviceTypes) && count($serviceTypes) > 1)
    <section class="py-6 md:py-8 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800">
        <div class="site-shell">
            <div class="flex flex-wrap justify-center gap-2 md:gap-3" role="tablist" aria-label="Filtrer par type de travaux">
                <button type="button" class="filter-btn portfolio-filter-pill is-active px-5 py-2.5 rounded-full text-sm font-semibold"
                        data-filter="all" role="tab" aria-selected="true">
                    Tous les projets
                </button>
                @foreach($serviceTypes as $serviceType)
                <button type="button" class="filter-btn portfolio-filter-pill px-5 py-2.5 rounded-full text-sm font-semibold"
                        data-filter="{{ Str::slug($serviceType) }}" role="tab" aria-selected="false">
                    {{ $serviceType }}
                </button>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Grille -->
    <section class="py-12 md:py-16 bg-gray-50 dark:bg-gray-950">
        <div class="site-shell">
            @if($visiblePortfolio->count() > 0)
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8" id="portfolio-grid">
                @foreach($visiblePortfolio as $item)
                @php
                    $slug = $item['slug'] ?? \Illuminate\Support\Str::slug($item['title'] ?? 'realisation');
                    $url = route('portfolio.show', $slug);
                    $badge = $item['service_type'] ?? $item['work_type'] ?? null;
                @endphp
                <a href="{{ $url }}"
                   class="portfolio-item portfolio-card group block rounded-2xl overflow-hidden bg-white dark:bg-gray-900 shadow-md"
                   data-category="{{ Str::slug($item['work_type'] ?? 'autre') }}">
                    <div class="portfolio-card-img-wrap relative overflow-hidden bg-gray-100 dark:bg-gray-800">
                        @if(!empty($item['images']))
                            @php
                                $firstImage = is_array($item['images']) ? $item['images'][0] : $item['images'];
                                $imgSrc = Str::startsWith($firstImage, ['http://', 'https://']) ? $firstImage : asset(ltrim($firstImage, '/'));
                            @endphp
                            <img src="{{ $imgSrc }}"
                                 alt="{{ $item['title'] ?? 'Réalisation' }}"
                                 class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                 width="800"
                                 height="500"
                                 loading="lazy"
                                 decoding="async">
                        @else
                            <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-primary to-secondary">
                                <i class="fas fa-image text-white/90 text-5xl" aria-hidden="true"></i>
                            </div>
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-black/55 via-black/10 to-transparent opacity-90 group-hover:opacity-100 transition-opacity"></div>
                        <span class="absolute bottom-4 left-4 right-4 flex items-center justify-between text-white text-sm font-medium">
                            <span class="inline-flex items-center gap-2">
                                <i class="fas fa-arrow-right opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300" aria-hidden="true"></i>
                                <span>Voir le projet</span>
                            </span>
                            @if(isset($item['images']) && is_array($item['images']))
                            <span class="text-white/90 text-xs font-normal">
                                <i class="fas fa-images mr-1" aria-hidden="true"></i>{{ count($item['images']) }} photo{{ count($item['images']) > 1 ? 's' : '' }}
                            </span>
                            @endif
                        </span>
                    </div>

                    <div class="p-6">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2 group-hover:text-primary transition-colors">{{ $item['title'] }}</h2>
                        @if(!empty($item['description']))
                        <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed line-clamp-2 mb-4">{{ Str::limit(strip_tags($item['description']), 120) }}</p>
                        @endif
                        <div class="flex items-center justify-between gap-3 flex-wrap">
                            @if($badge)
                            <span class="inline-flex text-xs px-3 py-1 rounded-full font-medium bg-primary/10 text-primary dark:bg-primary/20 dark:text-amber-200">
                                {{ $badge }}
                            </span>
                            @else
                            <span class="text-xs text-gray-500 dark:text-gray-500">Travaux</span>
                            @endif
                            <span class="text-primary font-semibold text-sm inline-flex items-center">
                                Détails <i class="fas fa-chevron-right ml-1 text-xs opacity-70" aria-hidden="true"></i>
                            </span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div class="text-center py-16 md:py-20">
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-800 p-10 md:p-14 max-w-lg mx-auto">
                    <i class="fas fa-images text-gray-200 dark:text-gray-700 text-6xl mb-6" aria-hidden="true"></i>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mb-3">Aucune réalisation pour le moment</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-8">Nos réalisations seront bientôt en ligne.</p>
                    <a href="{{ route('form.step', 'propertyType') }}"
                       class="inline-flex items-center justify-center rounded-xl px-8 py-3.5 font-semibold text-white shadow-lg transition hover:opacity-95"
                       style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                        Demander un devis
                    </a>
                </div>
            </div>
            @endif
        </div>
    </section>

    <!-- CTA -->
    <section class="portfolio-cta-band py-16 md:py-20 text-white relative">
        <div class="site-shell relative z-10 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">Vous avez un projet similaire ?</h2>
            <p class="text-lg md:text-xl text-white/90 mb-10 max-w-2xl mx-auto">
                Contactez-nous pour discuter de vos besoins et obtenir un devis personnalisé pour votre projet
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-stretch sm:items-center">
                <a href="{{ route('form.step', 'propertyType') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-xl px-8 py-4 font-bold text-lg shadow-xl transition hover:brightness-110"
                   style="background-color: var(--accent-color, #f59e0b); color: #0f172a;">
                    <i class="fas fa-calculator" aria-hidden="true"></i>
                    Demander un devis gratuit
                </a>
                <a href="tel:{{ setting('company_phone_raw') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-xl px-8 py-4 font-bold text-lg bg-white/15 hover:bg-white/25 border border-white/30 transition backdrop-blur-sm">
                    <i class="fas fa-phone" aria-hidden="true"></i>
                    {{ setting('company_phone') }}
                </a>
            </div>

            <div class="mt-14 grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto text-left md:text-center">
                <div class="flex md:flex-col items-center gap-4 md:gap-0 rounded-xl bg-white/10 p-5 border border-white/10">
                    <div class="w-12 h-12 md:w-16 md:h-16 shrink-0 rounded-full bg-white/20 flex items-center justify-center md:mx-auto md:mb-4">
                        <i class="fas fa-clock text-xl" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-1">Réponse rapide</h3>
                        <p class="text-white/85 text-sm">Devis sous 24h</p>
                    </div>
                </div>
                <div class="flex md:flex-col items-center gap-4 md:gap-0 rounded-xl bg-white/10 p-5 border border-white/10">
                    <div class="w-12 h-12 md:w-16 md:h-16 shrink-0 rounded-full bg-white/20 flex items-center justify-center md:mx-auto md:mb-4">
                        <i class="fas fa-shield-alt text-xl" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-1">Garantie décennale</h3>
                        <p class="text-white/85 text-sm">Assurance professionnelle</p>
                    </div>
                </div>
                <div class="flex md:flex-col items-center gap-4 md:gap-0 rounded-xl bg-white/10 p-5 border border-white/10">
                    <div class="w-12 h-12 md:w-16 md:h-16 shrink-0 rounded-full bg-white/20 flex items-center justify-center md:mx-auto md:mb-4">
                        <i class="fas fa-star text-xl" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-1">Qualité garantie</h3>
                        <p class="text-white/85 text-sm">Artisans qualifiés</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const portfolioItems = document.querySelectorAll('.portfolio-item');
    if (!filterButtons.length || !portfolioItems.length) return;

    function setActive(btn) {
        filterButtons.forEach(function(b) {
            b.classList.remove('is-active');
            b.setAttribute('aria-selected', 'false');
        });
        btn.classList.add('is-active');
        btn.setAttribute('aria-selected', 'true');
    }

    filterButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            setActive(this);

            portfolioItems.forEach(function(item) {
                if (filter === 'all' || item.getAttribute('data-category') === filter) {
                    item.style.display = '';
                    item.style.animation = 'fadeInUp 0.45s ease-out';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});
</script>

<style>
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
}
@media (prefers-reduced-motion: reduce) {
    @keyframes fadeInUp { from, to { opacity: 1; transform: none; } }
}
</style>
@endsection
