@extends('layouts.app')

@section('title', $pageTitle)
@section('description', $pageDescription)
@section('keywords', $service['meta_keywords'] ?? '')

@push('head')
<style>
.service-page-root { overflow-x: hidden; }
.service-hero-bg {
    position: absolute; inset: 0; overflow: hidden;
}
.service-hero-bg img {
    width: 100%; height: 100%; object-fit: cover;
    transform: scale(1.08);
    filter: blur(3px) brightness(.5);
    transition: transform 8s ease;
}
.service-hero-bg.no-img { background: linear-gradient(140deg, #071c10, #0d3b22 50%, #1a5c35); }

/* Prose */
.service-content { min-width: 0; overflow-x: hidden; }
.service-content img, .service-content video, .service-content iframe { max-width:100%!important; height:auto; }
.service-content table { width:100%; table-layout:fixed; border-collapse:collapse; }
.service-content td, .service-content th { word-break:break-word; overflow-wrap:break-word; }
.service-content pre, .service-content code { white-space:pre-wrap; word-break:break-word; max-width:100%; }

/* Sticky sidebar */
@media (min-width: 1024px) {
    .sidebar-sticky { position: sticky; top: 100px; }
}

/* Breadcrumb */
.breadcrumb-sep::before { content: '/'; margin: 0 8px; opacity: .4; }
</style>
@endpush

@section('content')
<div class="service-page-root bg-gray-50 min-h-screen">

    {{-- ══════════════════════════════════════════
         HERO
    ══════════════════════════════════════════ --}}
    <section class="relative min-h-[60vh] md:min-h-[72vh] flex items-end pb-0 overflow-hidden">

        {{-- Fond --}}
        <div class="service-hero-bg {{ empty($service['featured_image']) ? 'no-img' : '' }}">
            @if(!empty($service['featured_image']))
                <img src="{{ asset($service['featured_image']) }}" alt="{{ $service['name'] }}" loading="eager">
            @endif
        </div>
        {{-- Overlay gradient --}}
        <div class="absolute inset-0 z-[1]"
             style="background: linear-gradient(to top, rgba(7,28,16,.96) 0%, rgba(7,28,16,.7) 40%, rgba(7,28,16,.25) 100%);"></div>

        {{-- Contenu --}}
        <div class="site-shell relative z-10 pb-20 pt-32 w-full">

            {{-- Breadcrumb --}}
            <nav class="flex items-center text-xs text-white/50 font-medium mb-6 flex-wrap">
                <a href="{{ url('/') }}" class="hover:text-white/80 transition-colors">Accueil</a>
                <span class="breadcrumb-sep"></span>
                <a href="{{ route('services.index') }}" class="hover:text-white/80 transition-colors">Services</a>
                <span class="breadcrumb-sep"></span>
                <span class="text-white/80">{{ $service['name'] }}</span>
            </nav>

            <div class="max-w-4xl">
                {{-- Badge service --}}
                <div class="inline-flex items-center gap-2 bg-white/10 border border-white/20 backdrop-blur-sm rounded-full px-4 py-1.5 mb-5">
                    <i class="{{ $service['icon'] ?? 'fas fa-tree' }} text-xs" style="color:var(--primary-color);"></i>
                    <span class="text-white/80 text-xs font-bold uppercase tracking-widest">Service — Département 60</span>
                </div>

                <h1 class="text-4xl sm:text-5xl md:text-6xl font-black text-white mb-5 leading-[1.05]">
                    {{ $service['name'] }}
                </h1>

                <p class="text-white/75 text-lg md:text-xl leading-relaxed max-w-2xl mb-8">
                    {{ $service['short_description'] }}
                </p>

                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('form.step', 'propertyType') }}"
                       class="inline-flex items-center gap-2.5 text-white font-bold px-8 py-4 rounded-xl shadow-2xl hover:scale-[1.03] transition-all"
                       style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                        <i class="fas fa-calculator"></i> Devis gratuit
                    </a>
                    <a href="tel:{{ setting('company_phone_raw', setting('company_phone')) }}"
                       class="inline-flex items-center gap-2.5 text-white font-bold px-8 py-4 rounded-xl border-2 border-white/30 hover:bg-white/10 transition-all">
                        <i class="fas fa-phone text-emerald-300 animate-pulse"></i>
                        {{ setting('company_phone', '06 42 21 41 51') }}
                    </a>
                </div>
            </div>

        </div>

        {{-- Vague --}}
        <div class="absolute bottom-0 left-0 right-0 z-10">
            <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" style="display:block;width:100%;height:60px;">
                <path d="M0,60 C480,10 960,50 1440,0 L1440,60 Z" fill="#f9fafb"/>
            </svg>
        </div>
    </section>

    {{-- ══════════════════════════════════════════
         CONTENU + SIDEBAR
    ══════════════════════════════════════════ --}}
    <section class="py-16">
        <div class="site-shell">
            <div class="flex flex-col lg:flex-row gap-10 items-start">

                {{-- ─── Contenu principal ─── --}}
                <div class="flex-1 min-w-0">

                    @if(isset($service['error']) && $service['error'])
                    <div class="bg-red-50 border border-red-200 rounded-2xl p-8 mb-8">
                        <div class="flex gap-4">
                            <i class="fas fa-exclamation-triangle text-red-500 text-3xl flex-shrink-0 mt-1"></i>
                            <div>
                                <h3 class="text-lg font-bold text-red-800 mb-2">Erreur de génération du contenu</h3>
                                <p class="text-red-700 mb-4">{{ $service['error_message'] ?? 'Une erreur est survenue lors de la génération du contenu par l\'IA.' }}</p>
                                @if(isset($service['debug_info']))
                                <details>
                                    <summary class="text-sm text-red-600 cursor-pointer">Détails techniques</summary>
                                    <pre class="mt-2 p-4 bg-red-100 rounded text-xs overflow-x-auto">{{ json_encode($service['debug_info'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </details>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Article principal --}}
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 md:p-12 mb-8">
                        <div class="service-content prose prose-lg prose-headings:font-black prose-headings:text-gray-900 prose-p:text-gray-600 prose-p:leading-relaxed prose-a:font-semibold max-w-none"
                             style="--tw-prose-links: var(--primary-color);">
                            {!! $service['description'] !!}
                        </div>
                    </div>

                    {{-- Avantages clés --}}
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 mb-8">
                        <h2 class="text-2xl font-black text-gray-900 mb-6">
                            <i class="fas fa-check-circle mr-2" style="color:var(--primary-color);"></i>
                            Pourquoi nous choisir pour votre {{ $service['name'] }} ?
                        </h2>
                        <div class="grid sm:grid-cols-2 gap-4">
                            @foreach([
                                ['fas fa-shield-alt','Artisan assuré','Responsabilité civile professionnelle, garantie décennale'],
                                ['fas fa-map-marker-alt','Tout le département 60','Compiègne, Beauvais, Senlis, Chantilly, Creil, Noyon…'],
                                ['fas fa-clock','Réponse 24h','Contact rapide, délai d\'intervention adapté à vos besoins'],
                                ['fas fa-file-invoice','Devis gratuit','Transparent, détaillé, sans engagement de votre part'],
                                ['fas fa-recycle','Éco-responsable','Valorisation des déchets verts, broyage sur place si souhaité'],
                                ['fas fa-hard-hat','Sécurité maximale','Équipements certifiés, formation continue de nos équipes'],
                            ] as $adv)
                            <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 border border-gray-100">
                                <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"
                                     style="background:rgba(var(--primary-color-rgb,34,197,94),.12);">
                                    <i class="{{ $adv[0] }} text-sm" style="color:var(--primary-color);"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-gray-900 text-sm">{{ $adv[1] }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5 leading-relaxed">{{ $adv[2] }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- CTA bande --}}
                    <div class="rounded-3xl p-8 md:p-10 text-white text-center mb-8 relative overflow-hidden"
                         style="background: linear-gradient(135deg, #071c10 0%, #0d3b22 50%, #1a5c35 100%);">
                        <div class="absolute inset-0 opacity-[.04]"
                             style="background-image: radial-gradient(rgba(255,255,255,.8) 1px, transparent 1px); background-size: 30px 30px;"></div>
                        <div class="relative z-10">
                            <div class="w-14 h-14 rounded-2xl mx-auto mb-5 flex items-center justify-center text-white"
                                 style="background:rgba(255,255,255,.15);">
                                <i class="{{ $service['icon'] ?? 'fas fa-tree' }} text-2xl"></i>
                            </div>
                            <h3 class="text-2xl md:text-3xl font-black mb-3">Prêt pour votre {{ $service['name'] }} dans l'Oise ?</h3>
                            <p class="text-white/70 mb-8 max-w-xl mx-auto text-base">Devis gratuit, réponse sous 24h, artisan certifié — tout le département 60.</p>
                            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                                <a href="{{ route('form.step', 'propertyType') }}"
                                   class="inline-flex items-center justify-center gap-2.5 bg-white font-black px-8 py-4 rounded-xl shadow-xl hover:scale-[1.02] transition-all"
                                   style="color:var(--primary-color);">
                                    <i class="fas fa-calculator"></i> Devis gratuit
                                </a>
                                <a href="tel:{{ setting('company_phone_raw', setting('company_phone')) }}"
                                   class="inline-flex items-center justify-center gap-2.5 border-2 border-white/40 text-white font-bold px-8 py-4 rounded-xl hover:bg-white/10 transition-all">
                                    <i class="fas fa-phone text-emerald-300"></i>
                                    {{ setting('company_phone', '06 42 21 41 51') }}
                                </a>
                            </div>
                        </div>
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
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 mb-8">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-black text-gray-900">Nos réalisations</h2>
                            <a href="{{ route('portfolio.index') }}" class="text-sm font-bold hover:underline" style="color:var(--primary-color);">
                                Tout voir <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                        <div class="grid md:grid-cols-3 gap-5">
                            @foreach($relatedPortfolio as $item)
                            <article class="group rounded-2xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 bg-white">
                                @if(isset($item['images']) && is_array($item['images']) && count($item['images']) > 0)
                                <div class="relative h-44 overflow-hidden bg-gray-100">
                                    <img src="{{ url($item['images'][0]) }}" alt="{{ $item['title'] }}"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" loading="lazy">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end justify-center pb-3">
                                        <a href="{{ route('portfolio.show', $item['id'] ?? $loop->index) }}"
                                           class="text-white text-xs font-bold px-4 py-2 rounded-lg"
                                           style="background:var(--primary-color);">
                                            <i class="fas fa-eye mr-1"></i>Voir
                                        </a>
                                    </div>
                                </div>
                                @endif
                                <div class="p-4">
                                    <h3 class="font-bold text-gray-900 text-sm line-clamp-2">{{ $item['title'] }}</h3>
                                    @if(isset($item['description']) && $item['description'])
                                    <p class="text-gray-500 text-xs mt-1 line-clamp-2">{{ \Illuminate\Support\Str::limit($item['description'], 90) }}</p>
                                    @endif
                                </div>
                            </article>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Avis --}}
                    @php $serviceReviews = \App\Models\Review::where('is_active', true)->take(3)->get(); @endphp
                    @if($serviceReviews->count() > 0)
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8">
                        <h2 class="text-2xl font-black text-gray-900 mb-6">
                            <i class="fas fa-star mr-2 text-amber-400"></i>Avis de nos clients
                        </h2>
                        <div class="grid md:grid-cols-3 gap-5">
                            @foreach($serviceReviews as $review)
                            <div class="bg-gray-50 rounded-2xl border border-gray-100 p-5 flex flex-col relative overflow-hidden">
                                <div class="absolute top-3 right-4 text-5xl font-black text-gray-100 leading-none select-none">"</div>
                                <div class="flex gap-0.5 mb-3">
                                    @for($i=1;$i<=5;$i++)
                                    <i class="fas fa-star text-xs {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-200' }}"></i>
                                    @endfor
                                </div>
                                <p class="text-gray-600 text-sm leading-relaxed flex-1 mb-4 italic">
                                    "{{ \Illuminate\Support\Str::limit($review->review_text ?? 'Excellent travail.', 130) }}"
                                </p>
                                <div class="flex items-center gap-2.5 pt-3 border-t border-gray-100">
                                    <div class="w-8 h-8 rounded-full overflow-hidden flex-shrink-0">
                                        @if($review->author_photo_url)
                                        <img src="{{ $review->author_photo_url }}" alt="{{ $review->author_name }}" class="w-full h-full object-cover">
                                        @else
                                        <div class="w-full h-full flex items-center justify-center text-white text-xs font-black"
                                             style="background:linear-gradient(135deg,var(--primary-color),var(--secondary-color));">
                                            {{ strtoupper(substr($review->author_name ?? 'C', 0, 1)) }}
                                        </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900 text-xs">{{ $review->author_name }}</div>
                                        <div class="text-gray-400 text-[10px]">{{ $review->review_date ? \Carbon\Carbon::parse($review->review_date)->translatedFormat('F Y') : '' }}</div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="text-center mt-6">
                            <a href="{{ route('reviews.all') }}" class="inline-flex items-center gap-2 font-bold text-sm hover:underline" style="color:var(--primary-color);">
                                Voir tous les avis <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                    @endif

                </div>

                {{-- ─── Sidebar ─── --}}
                <aside class="lg:w-80 flex-shrink-0 sidebar-sticky">

                    {{-- CTA principal --}}
                    <div class="rounded-3xl p-7 text-white mb-5 relative overflow-hidden"
                         style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                        <div class="absolute inset-0 opacity-10"
                             style="background-image:radial-gradient(rgba(255,255,255,.8) 1px, transparent 1px); background-size:20px 20px;"></div>
                        <div class="relative z-10">
                            <div class="flex items-center gap-2 mb-4">
                                <i class="fas fa-calculator text-lg"></i>
                                <span class="font-black text-base">Devis gratuit</span>
                            </div>
                            <p class="text-white/80 text-sm mb-5 leading-relaxed">Sans engagement, réponse sous 24h pour tout le département 60.</p>
                            <a href="{{ route('form.step', 'propertyType') }}"
                               class="block w-full text-center bg-white font-black py-3.5 rounded-xl shadow-lg hover:scale-[1.02] transition-all text-sm"
                               style="color:var(--primary-color);">
                                <i class="fas fa-arrow-right mr-2"></i>Obtenir mon devis
                            </a>
                        </div>
                    </div>

                    {{-- Téléphone --}}
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 mb-5 text-center">
                        <div class="w-12 h-12 rounded-2xl mx-auto mb-3 flex items-center justify-center"
                             style="background:rgba(var(--primary-color-rgb,34,197,94),.12);">
                            <i class="fas fa-phone text-lg animate-pulse" style="color:var(--primary-color);"></i>
                        </div>
                        <div class="text-xs font-bold uppercase tracking-wide text-gray-400 mb-1">Appelez-nous</div>
                        <a href="tel:{{ setting('company_phone_raw', setting('company_phone')) }}"
                           class="text-xl font-black hover:underline" style="color:var(--primary-color);">
                            {{ setting('company_phone', '06 42 21 41 51') }}
                        </a>
                        <div class="text-xs text-gray-400 mt-2">Du lundi au samedi · 8h–19h</div>
                    </div>

                    {{-- Certifications --}}
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 mb-5">
                        <div class="text-sm font-black text-gray-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-certificate" style="color:var(--primary-color);"></i> Garanties
                        </div>
                        <div class="space-y-3">
                            @foreach([
                                ['fas fa-shield-alt','Assurance RC Pro'],
                                ['fas fa-award','Artisan certifié'],
                                ['fas fa-leaf','Éco-responsable'],
                                ['fas fa-hard-hat','Sécurité NF'],
                                ['fas fa-hand-holding-usd','Devis gratuit'],
                            ] as $cert)
                            <div class="flex items-center gap-2.5 text-sm text-gray-700">
                                <div class="w-6 h-6 rounded-lg flex items-center justify-center flex-shrink-0"
                                     style="background:rgba(var(--primary-color-rgb,34,197,94),.1);">
                                    <i class="{{ $cert[0] }} text-[10px]" style="color:var(--primary-color);"></i>
                                </div>
                                {{ $cert[1] }}
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Zone --}}
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 mb-5">
                        <div class="text-sm font-black text-gray-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-map-marker-alt" style="color:var(--primary-color);"></i> Zone Oise (60)
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach(['Compiègne','Beauvais','Senlis','Chantilly','Creil','Noyon','Verberie','Méru'] as $c)
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-lg"
                                  style="background:rgba(var(--primary-color-rgb,34,197,94),.08); color:var(--primary-color);">
                                {{ $c }}
                            </span>
                            @endforeach
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-gray-100 text-gray-500">+50 communes…</span>
                        </div>
                    </div>

                    {{-- Autres services --}}
                    @php
                        $allSvcs = \App\Models\Setting::get('services', '[]');
                        $allSvcs = is_string($allSvcs) ? json_decode($allSvcs, true) : ($allSvcs ?? []);
                        if(!is_array($allSvcs)) $allSvcs = [];
                        $otherServices = collect($allSvcs)
                            ->filter(fn($s) => is_array($s) && ($s['is_visible'] ?? true) && ($s['slug'] ?? '') !== $service['slug'])
                            ->take(4);
                    @endphp
                    @if($otherServices->count() > 0)
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                        <div class="text-sm font-black text-gray-900 mb-4">Autres services</div>
                        <div class="space-y-2">
                            @foreach($otherServices as $other)
                            <a href="{{ route('services.show', $other['slug']) }}"
                               class="flex items-center gap-2.5 text-sm font-semibold text-gray-700 hover:text-primary transition-colors group py-2 border-b border-gray-50 last:border-0">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform"
                                     style="background:rgba(var(--primary-color-rgb,34,197,94),.1);">
                                    <i class="{{ $other['icon'] ?? 'fas fa-tree' }} text-[10px]" style="color:var(--primary-color);"></i>
                                </div>
                                {{ $other['name'] }}
                                <i class="fas fa-chevron-right text-xs ml-auto text-gray-300 group-hover:text-primary transition-colors"></i>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </aside>
            </div>
        </div>
    </section>

</div>
@endsection
