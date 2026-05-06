@extends('layouts.app')

@php
    $currentPage = $currentPage ?? 'blog';
    $pageType = 'website';
    $seoData = \App\Helpers\SeoHelper::generateMetaTags('blog', [
        'title' => 'Blog et Astuces',
        'description' => 'Découvrez nos articles et conseils d\'experts en rénovation et couverture',
        'image' => file_exists(public_path('images/og-blog.jpg')) ? asset('images/og-blog.jpg') : null,
    ]);
    $pageTitle = $seoData['title'];
    $pageDescription = $seoData['description'];
    $pageImage = $seoData['og:image'];
@endphp

@push('head')
<style>
@import url('https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,600;0,9..144,700;1,9..144,400&family=Manrope:wght@400;500;600;700&display=swap');
.bl{font-family:'Manrope',sans-serif;background:#FAF7F2;color:#1F1A14;}
.bl h1,.bl h2,.bl h3{font-family:'Fraunces',serif;}
.bl-hero{background:#1F1A14;position:relative;overflow:hidden;padding:5rem 0 4rem;text-align:center;}
.bl-hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 80% 60% at 50% 0%,rgba(255,255,255,.04) 0%,transparent 60%);}
.bl-eyebrow{font-size:.75rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.45);display:block;margin-bottom:1rem;}
.bl-hero-title{font-size:clamp(2.25rem,5vw,3.5rem);font-weight:700;color:#fff;line-height:1.15;margin-bottom:1rem;}
.bl-hero-sub{font-size:1.1rem;color:rgba(255,255,255,.65);max-width:520px;margin:0 auto;}
.bl-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.5rem;padding:4rem 0;}
.bl-card{background:#fff;border:1px solid rgba(30,20,10,.08);border-radius:1rem;overflow:hidden;transition:transform .3s,box-shadow .3s,border-color .3s;}
.bl-card:hover{transform:translateY(-5px);box-shadow:0 20px 48px rgba(30,20,10,.1);border-color:rgba(30,20,10,.15);}
.bl-card-img{height:200px;overflow:hidden;position:relative;background:#F2EDE4;}
.bl-card-img img{width:100%;height:100%;object-fit:cover;transition:transform .5s;display:block;}
.bl-card:hover .bl-card-img img{transform:scale(1.05);}
.bl-card-body{padding:1.5rem;}
.bl-card-meta{display:flex;align-items:center;gap:.75rem;margin-bottom:.75rem;}
.bl-tag{font-size:.7rem;font-weight:700;padding:.2rem .6rem;border-radius:999px;background:rgba(30,20,10,.06);color:#6B6157;text-transform:uppercase;letter-spacing:.06em;}
.bl-date{font-size:.75rem;color:#9C8E84;}
.bl-card-title{font-size:1.125rem;font-weight:700;color:#1F1A14;margin-bottom:.5rem;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.bl-card-title a{text-decoration:none;color:inherit;transition:color .2s;}
.bl-card-title a:hover{color:var(--primary-color,#3b82f6);}
.bl-card-desc{font-size:.8125rem;color:#6B6157;line-height:1.6;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;margin-bottom:1rem;}
.bl-card-link{font-size:.8125rem;font-weight:700;color:var(--primary-color,#3b82f6);text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;transition:gap .2s;}
.bl-card-link:hover{gap:.7rem;}
.bl-empty{text-align:center;padding:5rem 1rem;}
.bl-pagination{display:flex;justify-content:center;padding-bottom:4rem;}
</style>

{{-- Google/Facebook analytics --}}
@if(setting('google_analytics_id'))
<script async src="https://www.googletagmanager.com/gtag/js?id={{ setting('google_analytics_id') }}"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{{ setting('google_analytics_id') }}');</script>
@endif
@endpush

@section('content')
<div class="bl">
    {{-- Hero --}}
    <section class="bl-hero">
        <div class="site-shell" style="position:relative;z-index:1;">
            <span class="bl-eyebrow">Blog & conseils</span>
            <h1 class="bl-hero-title">Nos articles d'experts</h1>
            <p class="bl-hero-sub">Découvrez nos conseils et astuces pour tous vos projets de rénovation et de couverture</p>
        </div>
    </section>

    {{-- Grille articles --}}
    <section style="background:#FAF7F2;">
        <div class="site-shell">
            @if($articles->count() > 0)
            <div class="bl-grid">
                @foreach($articles as $article)
                <article class="bl-card">
                    @if($article->featured_image)
                    <div class="bl-card-img">
                        <img src="{{ asset($article->featured_image) }}" alt="{{ $article->title }}" loading="lazy">
                    </div>
                    @endif
                    <div class="bl-card-body">
                        <div class="bl-card-meta">
                            <span class="bl-tag">Article</span>
                            <span class="bl-date">{{ $article->published_at->format('d/m/Y') }}</span>
                        </div>
                        <h2 class="bl-card-title">
                            <a href="{{ route('blog.show', $article) }}">{{ $article->title }}</a>
                        </h2>
                        @if($article->meta_description)
                        <p class="bl-card-desc">{{ $article->meta_description }}</p>
                        @endif
                        <a href="{{ route('blog.show', $article) }}" class="bl-card-link">
                            Lire la suite
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </article>
                @endforeach
            </div>
            @if($articles->hasPages())
            <div class="bl-pagination">{{ $articles->links() }}</div>
            @endif
            @else
            <div class="bl-empty">
                <div style="width:80px;height:80px;border-radius:50%;background:#F2EDE4;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#9C8E84" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                </div>
                <h3 style="font-family:'Fraunces',serif;font-size:1.5rem;color:#1F1A14;margin-bottom:.5rem;">Aucun article disponible</h3>
                <p style="color:#6B6157;">Les articles seront bientôt disponibles. Revenez bientôt !</p>
            </div>
            @endif
        </div>
    </section>
</div>
@endsection
