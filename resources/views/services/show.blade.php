@extends('layouts.app')

@section('title', $pageTitle)

@section('description', $pageDescription)

@section('keywords', $service['meta_keywords'] ?? '')

@push('head')
<style>
    /* Page service : pas de scroll horizontal, contenu fluide */
    .service-page-root {
        overflow-x: hidden;
        max-width: 100%;
    }
    .service-page-root section {
        overflow-x: hidden;
    }
    /* Hero : fond agrandi sans déborder (clip sur le bloc interne) */
    .service-hero-media {
        position: absolute;
        inset: 0;
        overflow: hidden;
        z-index: 0;
    }
    .service-hero-media-inner {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        transform: scale(1.05);
        filter: blur(2px);
    }

    .service-page-content {
        min-width: 0;
    }
    .service-page-content img,
    .service-page-content iframe,
    .service-page-content video {
        max-width: 100% !important;
        height: auto;
    }
    .service-page-content table {
        width: 100%;
        table-layout: fixed;
        word-wrap: break-word;
        border-collapse: collapse;
    }
    .service-page-content td,
    .service-page-content th {
        word-break: break-word;
        overflow-wrap: break-word;
    }
    .service-page-content pre,
    .service-page-content code {
        white-space: pre-wrap;
        word-break: break-word;
        max-width: 100%;
        overflow-x: hidden;
    }
    /* Prose : éviter barre de défilement horizontale sur le bloc principal */
    .service-prose-wrap {
        overflow-x: hidden;
    }
    .service-prose-wrap.prose {
        max-width: none;
    }
</style>
@endpush

@section('content')
<div class="service-page-root min-h-screen bg-gray-50 dark:bg-slate-950">
    <!-- Hero -->
    <section class="relative py-16 md:py-24 text-white overflow-hidden rounded-b-3xl shadow-lg">
        <div class="service-hero-media">
            @if($service['featured_image'])
            <div class="service-hero-media-inner absolute inset-0"
                 style="background-image: url('{{ asset($service['featured_image']) }}');"></div>
            <div class="absolute inset-0 bg-black/55 z-[1]"></div>
            @else
            <div class="absolute inset-0 z-0" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);"></div>
            @endif
        </div>

        <div class="site-shell relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <p class="text-white/85 text-sm font-semibold uppercase tracking-widest mb-3">Service</p>
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-6 leading-tight">
                    <i class="{{ $service['icon'] ?? 'fas fa-tools' }} mr-3 opacity-90" aria-hidden="true"></i>{{ $service['name'] }}
                </h1>
                <p class="text-lg md:text-xl mb-10 leading-relaxed text-white/95 max-w-3xl mx-auto">
                    {{ $service['short_description'] }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-stretch sm:items-center">
                    <a href="{{ route('form.step', 'propertyType') }}"
                       class="inline-flex justify-center items-center text-white font-bold py-4 px-8 rounded-xl text-lg shadow-lg transition hover:opacity-95"
                       style="background-color: var(--accent-color);">
                        <i class="fas fa-calculator mr-2"></i>
                        Devis gratuit
                    </a>
                    <a href="tel:{{ setting('company_phone_raw') }}"
                       class="inline-flex justify-center items-center text-white font-bold py-4 px-8 rounded-xl text-lg shadow-lg transition hover:opacity-95"
                       style="background-color: var(--primary-color);">
                        <i class="fas fa-phone mr-2"></i>
                        {{ setting('company_phone') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Contenu principal -->
    <section class="py-12 md:py-16 relative z-[1]">
        <div class="site-shell">
            <div class="max-w-4xl mx-auto -mt-8 md:-mt-10">
                <div class="rounded-2xl border border-gray-200/80 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-xl dark:shadow-slate-900/50 p-6 sm:p-10 md:p-12 text-gray-900 dark:text-slate-100">
                    @if(isset($service['error']) && $service['error'])
                        <div class="bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 rounded-xl p-6 mb-2">
                            <div class="flex gap-4">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-lg font-semibold text-red-800 dark:text-red-200 mb-2">Erreur de génération du contenu</h3>
                                    <p class="text-red-700 dark:text-red-300 mb-4">{{ $service['error_message'] ?? 'Une erreur est survenue lors de la génération du contenu par l\'IA.' }}</p>
                                    @if(isset($service['debug_info']))
                                        <details class="mt-4">
                                            <summary class="text-sm text-red-600 cursor-pointer hover:text-red-800">Détails techniques</summary>
                                            <pre class="mt-2 p-4 bg-red-100 dark:bg-red-950/50 rounded text-xs overflow-x-auto max-w-full">{{ json_encode($service['debug_info'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </details>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="service-prose-wrap prose prose-sm md:prose-base prose-headings:text-gray-900 dark:prose-headings:text-slate-100 prose-p:text-gray-700 dark:prose-p:text-slate-300 prose-a:text-[color:var(--primary-color)] dark:prose-invert max-w-none service-page-content">
                            {!! $service['description'] !!}
                        </div>
                    @endif
                </div>

                <!-- CTA -->
                <div class="mt-8 rounded-2xl p-8 md:p-10 text-white text-center border border-white/10 shadow-lg"
                     style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);">
                    <h3 class="text-xl md:text-2xl font-bold mb-3">Prêt à lancer votre projet {{ $service['name'] }} ?</h3>
                    <p class="text-base md:text-lg mb-8 text-white/95 max-w-xl mx-auto">Contactez-nous pour un devis gratuit et personnalisé.</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ route('form.step', 'propertyType') }}"
                           class="inline-flex justify-center items-center text-white font-bold py-3.5 px-8 rounded-xl text-lg shadow-md transition hover:opacity-95"
                           style="background-color: var(--accent-color);">
                            <i class="fas fa-calculator mr-2"></i>
                            Demander un devis
                        </a>
                        <a href="tel:{{ setting('company_phone_raw') }}"
                           class="inline-flex justify-center items-center bg-white/15 hover:bg-white/25 font-bold py-3.5 px-8 rounded-xl text-lg border border-white/30 transition">
                            <i class="fas fa-phone mr-2"></i>
                            Appeler
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Réalisations -->
    <section class="py-14 md:py-20 bg-gray-100 dark:bg-slate-900/60 border-t border-gray-200/80 dark:border-slate-800">
        <div class="site-shell">
            <div class="max-w-6xl mx-auto w-full">
                <div class="text-center mb-10 md:mb-12">
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-slate-100 mb-3">Nos réalisations</h2>
                    <div class="w-20 h-1 mx-auto rounded-full mb-4" style="background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));"></div>
                    <p class="text-gray-600 dark:text-slate-400 max-w-2xl mx-auto">Quelques chantiers récents dans nos domaines d’expertise.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                    @php
                        $portfolioItems = \App\Models\Setting::get('portfolio_items', []);
                        if (!is_array($portfolioItems)) {
                            $portfolioItems = [];
                        }
                        $relatedPortfolio = collect($portfolioItems)
                            ->filter(function($item) {
                                return is_array($item) && isset($item['title']);
                            })
                            ->take(3);
                    @endphp

                    @foreach($relatedPortfolio as $item)
                        @if(is_array($item) && isset($item['title']))
                        <article class="group bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col min-w-0">
                            @if(isset($item['images']) && is_array($item['images']) && count($item['images']) > 0)
                            <div class="relative h-48 overflow-hidden bg-gray-100 dark:bg-slate-900">
                                <img src="{{ url($item['images'][0]) }}"
                                     alt="{{ $item['title'] }}"
                                     class="w-full h-full object-cover transition duration-500 group-hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end justify-center pb-4">
                                    <a href="{{ route('portfolio.show', $item['id'] ?? $loop->index) }}"
                                       class="text-white text-sm font-semibold px-4 py-2 rounded-lg"
                                       style="background-color: var(--primary-color);">
                                        <i class="fas fa-eye mr-2"></i>Voir le projet
                                    </a>
                                </div>
                            </div>
                            @endif
                            <div class="p-6 flex-1 flex flex-col min-w-0">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-slate-100 mb-2 line-clamp-2">{{ $item['title'] }}</h3>
                                @if(isset($item['description']) && $item['description'])
                                <p class="text-gray-600 dark:text-slate-400 text-sm flex-1 line-clamp-3">{{ Str::limit($item['description'], 120) }}</p>
                                @endif
                            </div>
                        </article>
                        @endif
                    @endforeach
                </div>

                <div class="text-center mt-10">
                    <a href="{{ route('portfolio.index') }}"
                       class="inline-flex items-center justify-center text-white font-semibold py-3 px-8 rounded-xl transition shadow-md hover:opacity-95"
                       style="background-color: var(--primary-color);">
                        Toutes nos réalisations
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Avis -->
    <section class="py-14 md:py-20 bg-white dark:bg-slate-950 border-t border-gray-100 dark:border-slate-800">
        <div class="site-shell">
            <div class="max-w-6xl mx-auto w-full">
                <div class="text-center mb-10 md:mb-12">
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-slate-100 mb-3">Avis clients</h2>
                    <div class="w-20 h-1 mx-auto rounded-full mb-4" style="background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));"></div>
                    <p class="text-gray-600 dark:text-slate-400">Retours sur nos interventions.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                    @php
                        $reviews = \App\Models\Review::where('is_active', true)->take(3)->get();
                    @endphp

                    @if($reviews->count() > 0)
                    @foreach($reviews as $review)
                    <article class="rounded-2xl border border-gray-100 dark:border-slate-700 bg-gray-50 dark:bg-slate-900/80 p-6 shadow-sm hover:shadow-md transition-shadow min-w-0 flex flex-col">
                        <div class="flex items-start gap-4 mb-4">
                            <div class="w-12 h-12 rounded-full overflow-hidden shrink-0 ring-2 ring-white dark:ring-slate-700 shadow">
                                @if($review->author_photo_url)
                                <img src="{{ $review->author_photo_url }}" alt="" class="w-full h-full object-cover">
                                @else
                                <div class="w-full h-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg">
                                    {{ $review->author_initials }}
                                </div>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="font-bold text-gray-900 dark:text-slate-100 truncate">{{ $review->author_name }}</h4>
                                <div class="flex items-center gap-0.5 mt-1">
                                    @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star text-sm text-yellow-400 {{ $i <= $review->rating ? '' : 'opacity-25' }}"></i>
                                    @endfor
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-700 dark:text-slate-300 text-sm leading-relaxed flex-1">
                            @if($review->review_text)
                                {{ Str::limit($review->review_text, 160) }}
                            @else
                                <span class="text-gray-500 italic">Avis sans détail</span>
                            @endif
                        </p>
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-slate-500 mt-4 pt-4 border-t border-gray-200/80 dark:border-slate-700">
                            <span>{{ $review->review_date ? \Carbon\Carbon::parse($review->review_date)->format('d/m/Y') : '' }}</span>
                            @if($review->source && $review->source !== 'manual')
                            <span class="px-2 py-0.5 rounded-full bg-slate-200/80 dark:bg-slate-800 text-[10px] font-medium">
                                {{ Str::limit(ucfirst($review->source), 18) }}
                            </span>
                            @endif
                        </div>
                    </article>
                    @endforeach
                    @else
                    <div class="col-span-full text-center py-10 text-gray-500 dark:text-slate-500 rounded-2xl border border-dashed border-gray-200 dark:border-slate-700">
                        Aucun avis pour le moment.
                    </div>
                    @endif
                </div>

                <div class="text-center mt-10">
                    <a href="{{ route('reviews.all') }}"
                       class="inline-flex items-center justify-center text-white font-semibold py-3 px-8 rounded-xl transition shadow-md hover:opacity-95"
                       style="background-color: var(--primary-color);">
                        Tous les avis
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
