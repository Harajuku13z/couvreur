@extends('layouts.app')

@php
    $pageTitle = $article->meta_title ?: $article->title;
    $pageDescription = $article->meta_description;
    $metaKeywords = $article->meta_keywords;
    if ($metaKeywords) {
        $kws = array_map('trim', explode(',', $metaKeywords));
        $stop = ['votre','notre','mieux','bien','bon','meilleur','le','la','les'];
        $kws = array_filter($kws, fn($k) => !empty($k) && strlen($k)>=3 && !in_array(strtolower(trim($k)),$stop));
        $metaKeywords = !empty($kws) ? implode(', ', $kws) : null;
    }
@endphp
@section('title', $pageTitle)
@section('description', $pageDescription)
@section('keywords', $metaKeywords)

@php
    $pageImage = $article->featured_image ? asset($article->featured_image) : asset(setting('default_blog_og_image','images/og-blog.jpg'));
    $pageType = 'article';
    $currentPage = 'article';
    $ogTitle = $pageTitle;
    $twitterTitle = $pageTitle;
    $ogDescription = $pageDescription;
    $twitterDescription = $pageDescription;
@endphp

@push('head')
<style>
@import url('https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,600;0,9..144,700;1,9..144,400&family=Manrope:wght@400;500;600;700&display=swap');
/* Page wrapper */
.ar{font-family:'Manrope',sans-serif;background:#FAF7F2;color:#1F1A14;}
/* Hero */
.ar-hero{position:relative;min-height:58vh;display:flex;align-items:flex-end;overflow:hidden;}
.ar-hero-bg{position:absolute;inset:0;background-size:cover;background-position:center;}
.ar-hero-gradient{position:absolute;inset:0;background:linear-gradient(to top, rgba(15,12,8,.85) 0%, rgba(15,12,8,.45) 55%, rgba(15,12,8,.15) 100%);}
.ar-hero-no-img{position:absolute;inset:0;background:#1F1A14;}
.ar-hero-no-img::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 80% 60% at 50% 0%,rgba(255,255,255,.04),transparent 60%);}
.ar-hero-inner{position:relative;z-index:1;width:100%;padding:2.5rem 0 3rem;}
.ar-hero-eyebrow{display:inline-flex;align-items:center;gap:.5rem;font-size:.75rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.5);margin-bottom:1.25rem;}
.ar-hero-title{font-family:'Fraunces',serif;font-size:clamp(1.875rem,4.5vw,3.25rem);font-weight:700;color:#fff;line-height:1.15;margin-bottom:1.25rem;word-break:break-word;}
.ar-hero-meta{display:flex;flex-wrap:wrap;align-items:center;gap:.75rem;}
.ar-meta-pill{display:inline-flex;align-items:center;gap:.4rem;font-size:.75rem;font-weight:600;padding:.35rem .75rem;border-radius:999px;background:rgba(255,255,255,.12);color:rgba(255,255,255,.8);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.12);}
/* Layout */
.ar-layout{display:grid;grid-template-columns:1fr 300px;gap:3rem;padding:3.5rem 0 5rem;align-items:start;}
@media(max-width:960px){.ar-layout{grid-template-columns:1fr;gap:2rem;}}
/* Article content */
.ar-body{background:#fff;border:1px solid rgba(30,20,10,.07);border-radius:1rem;padding:2.5rem;min-width:0;}
@media(max-width:640px){.ar-body{padding:1.5rem;}}
/* Article typography */
.ar-content{font-size:1.0625rem;line-height:1.8;color:#3D3530;}
.ar-content h1{font-family:'Fraunces',serif;font-size:2rem;font-weight:700;color:#1F1A14;margin:1.5rem 0 1rem;line-height:1.2;}
.ar-content h2{font-family:'Fraunces',serif;font-size:1.625rem;font-weight:700;color:#1F1A14;margin:2.5rem 0 1rem;padding-bottom:.75rem;border-bottom:2px solid rgba(30,20,10,.08);line-height:1.25;}
.ar-content h3{font-size:1.25rem;font-weight:700;color:#1F1A14;margin:2rem 0 .75rem;padding-left:.875rem;border-left:3px solid var(--primary-color,#B7472A);line-height:1.3;}
.ar-content h4{font-size:1.125rem;font-weight:700;color:#2A2420;margin:1.5rem 0 .5rem;}
.ar-content p{margin-bottom:1.25rem;line-height:1.8;color:#3D3530;}
.ar-content ul,.ar-content ol{margin:1.25rem 0 1.25rem 1.5rem;color:#3D3530;}
.ar-content ul{list-style:disc;}
.ar-content ol{list-style:decimal;}
.ar-content li{margin-bottom:.625rem;line-height:1.75;}
.ar-content strong{font-weight:700;color:#1F1A14;}
.ar-content em{font-style:italic;}
.ar-content a{color:var(--primary-color,#B7472A);text-decoration:underline;text-underline-offset:2px;}
.ar-content a:hover{opacity:.8;}
.ar-content img{max-width:100%;height:auto;border-radius:.75rem;margin:1.5rem 0;}
.ar-content blockquote{border-left:3px solid var(--primary-color,#B7472A);padding:1rem 1.25rem;margin:1.5rem 0;background:#FAF7F2;border-radius:0 .5rem .5rem 0;font-style:italic;color:#6B6157;}
.ar-content code{background:#F2EDE4;padding:.1rem .35rem;border-radius:.25rem;font-size:.875em;font-family:monospace;}
.ar-content pre{background:#1F1A14;color:#E8DED2;padding:1.25rem;border-radius:.75rem;overflow-x:auto;margin:1.5rem 0;font-size:.875rem;}
.ar-content pre code{background:transparent;padding:0;color:inherit;}
.ar-content hr{border:none;border-top:1px solid rgba(30,20,10,.1);margin:2rem 0;}
.ar-content table{width:100%;border-collapse:collapse;margin:1.5rem 0;font-size:.9375rem;}
.ar-content th,.ar-content td{padding:.75rem 1rem;border:1px solid rgba(30,20,10,.1);text-align:left;}
.ar-content th{background:#F2EDE4;font-weight:700;color:#1F1A14;}
.ar-content .cta-final{margin:2.5rem 0;padding:2rem;background:#1F1A14;border-radius:1rem;color:#fff;}
.ar-content .cta-final h3{font-family:'Fraunces',serif;color:#fff;border:none;padding:0;font-size:1.5rem;margin:0 0 .75rem;}
.ar-content .cta-final p{color:rgba(255,255,255,.75);margin-bottom:1rem;}
.ar-content #faq{margin:2.5rem 0;padding:2rem;background:#F2EDE4;border-radius:1rem;border:none;}
.ar-content #faq h2{font-family:'Fraunces',serif;font-size:1.5rem;border:none;padding:0;margin:0 0 1.5rem;}
.ar-content #faq > div[itemscope]{background:#fff;border-radius:.75rem;padding:1.25rem 1.5rem;margin-bottom:.875rem;border-left:3px solid var(--primary-color,#B7472A);}
.ar-content #faq h3{border:none;padding:0;font-size:1.0625rem;margin:0 0 .5rem;}
/* Suggested links */
.ar-links{margin-top:2rem;padding:1.5rem;background:#FAF7F2;border:1px solid rgba(30,20,10,.08);border-radius:.875rem;}
.ar-links-title{font-size:1rem;font-weight:700;color:#1F1A14;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;}
.ar-links-grid{display:grid;grid-template-columns:1fr 1fr;gap:.625rem;}
@media(max-width:500px){.ar-links-grid{grid-template-columns:1fr;}}
.ar-link-item{display:flex;align-items:center;gap:.625rem;padding:.75rem 1rem;background:#fff;border:1px solid rgba(30,20,10,.07);border-radius:.625rem;text-decoration:none;color:#3D3530;font-size:.8125rem;font-weight:600;transition:all .2s;}
.ar-link-item:hover{border-color:var(--primary-color,#B7472A);color:var(--primary-color,#B7472A);}
/* Share */
.ar-share{display:flex;flex-wrap:wrap;align-items:center;gap:.75rem;padding-top:1.5rem;margin-top:1.5rem;border-top:1px solid rgba(30,20,10,.08);}
.ar-share-label{font-size:.8125rem;font-weight:700;color:#6B6157;}
.ar-share-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.45rem 1rem;border-radius:999px;font-size:.8125rem;font-weight:700;text-decoration:none;transition:opacity .18s;}
.ar-share-fb{background:#1877F2;color:#fff;}
.ar-share-wa{background:#25D366;color:#fff;}
.ar-share-em{background:#6B6157;color:#fff;}
.ar-share-cp{background:#F2EDE4;color:#3D3530;border:none;cursor:pointer;font-family:'Manrope',sans-serif;}
.ar-share-btn:hover{opacity:.85;}
.ar-copy-ok{display:none;font-size:.75rem;color:#059669;font-weight:700;padding:.3rem .75rem;background:#D1FAE5;border-radius:999px;}
/* Article meta bottom */
.ar-meta-bottom{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.75rem;padding-top:1.25rem;margin-top:1.25rem;border-top:1px solid rgba(30,20,10,.08);font-size:.8125rem;color:#9C8E84;}
.ar-keyword{display:inline-flex;padding:.2rem .6rem;border-radius:999px;background:rgba(30,20,10,.05);color:#6B6157;font-size:.7rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;}
/* Sidebar */
.ar-sidebar{position:sticky;top:90px;}
.ar-sidebar-card{background:#1F1A14;border-radius:1rem;padding:1.75rem;color:#fff;margin-bottom:1.25rem;}
.ar-sidebar-card-light{background:#fff;border:1px solid rgba(30,20,10,.08);border-radius:1rem;padding:1.5rem;margin-bottom:1.25rem;}
.ar-sc-logo{width:48px;height:48px;border-radius:.75rem;background:rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;margin-bottom:1rem;}
.ar-sc-name{font-family:'Fraunces',serif;font-size:1.25rem;font-weight:700;color:#fff;margin-bottom:.25rem;}
.ar-sc-sub{font-size:.8125rem;color:rgba(255,255,255,.55);margin-bottom:1.25rem;}
.ar-sc-info{display:flex;align-items:center;gap:.625rem;font-size:.8125rem;color:rgba(255,255,255,.6);margin-bottom:.625rem;text-decoration:none;}
.ar-sc-info:hover{color:#fff;}
.ar-sc-info svg{flex-shrink:0;opacity:.7;}
.ar-btn-devis{display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.875rem;border-radius:999px;background:var(--primary-color,#B7472A);color:#fff;font-weight:700;font-size:.9375rem;text-decoration:none;transition:opacity .18s,transform .18s;margin-top:1.25rem;}
.ar-btn-devis:hover{opacity:.9;transform:translateY(-2px);}
.ar-btn-phone{display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.75rem;border-radius:999px;border:1.5px solid rgba(255,255,255,.2);color:rgba(255,255,255,.85);font-weight:600;font-size:.9375rem;text-decoration:none;transition:background .18s;margin-top:.625rem;}
.ar-btn-phone:hover{background:rgba(255,255,255,.08);}
.ar-trust-row{display:flex;justify-content:center;gap:1.25rem;margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid rgba(255,255,255,.07);}
.ar-trust-item{display:flex;align-items:center;gap:.3rem;font-size:.7rem;color:rgba(255,255,255,.45);}
/* Reviews sidebar */
.ar-review{border-bottom:1px solid rgba(30,20,10,.07);padding-bottom:1.25rem;margin-bottom:1.25rem;}
.ar-review:last-child{border:none;padding:0;margin:0;}
.ar-stars{color:#F59E0B;font-size:.75rem;margin-bottom:.35rem;letter-spacing:.05em;}
.ar-review-text{font-size:.8125rem;color:#6B6157;line-height:1.6;margin-bottom:.5rem;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;}
.ar-reviewer{display:flex;align-items:center;gap:.5rem;}
.ar-reviewer-avatar{width:28px;height:28px;border-radius:50%;background:var(--primary-color,#B7472A);display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;color:#fff;flex-shrink:0;}
.ar-reviewer-name{font-size:.8125rem;font-weight:700;color:#1F1A14;}
/* Mobile CTA bar */
.ar-mobile-cta{display:none;position:fixed;bottom:0;left:0;right:0;z-index:100;background:#1F1A14;border-top:1px solid rgba(255,255,255,.08);padding:.875rem 1.25rem;display:flex;gap:.625rem;}
@media(min-width:961px){.ar-mobile-cta{display:none!important;}}
.ar-mob-btn-devis{flex:1;display:flex;align-items:center;justify-content:center;gap:.4rem;padding:.75rem;border-radius:999px;background:var(--primary-color,#B7472A);color:#fff;font-weight:700;font-size:.875rem;text-decoration:none;}
.ar-mob-btn-phone{width:48px;height:48px;border-radius:50%;background:rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;text-decoration:none;flex-shrink:0;}
</style>

<!-- Article schema -->
<meta property="article:published_time" content="{{ $article->created_at->toISOString() }}">
<meta property="article:author" content="{{ setting('company_name','Expert') }}">
<meta property="article:section" content="Blog">
<meta property="article:tag" content="{{ $article->focus_keyword ?? 'Rénovation' }}">

@if(setting('google_analytics_id'))
<script async src="https://www.googletagmanager.com/gtag/js?id={{ setting('google_analytics_id') }}"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{{ setting('google_analytics_id') }}');</script>
@endif
@endpush

@section('content')
<div class="ar">
    {{-- Hero --}}
    <div class="ar-hero">
        @if($article->featured_image)
            <div class="ar-hero-bg" style="background-image:url('{{ asset($article->featured_image) }}');"></div>
            <div class="ar-hero-gradient"></div>
        @else
            <div class="ar-hero-no-img"></div>
        @endif
        <div class="site-shell ar-hero-inner">
            <div class="ar-hero-eyebrow">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Blog &amp; Conseils
            </div>
            <h1 class="ar-hero-title">{{ $article->title }}</h1>
            <div class="ar-hero-meta">
                @if($article->published_at)
                <span class="ar-meta-pill">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    {{ $article->published_at->format('d/m/Y') }}
                </span>
                @endif
                <span class="ar-meta-pill">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    {{ $article->estimated_reading_time ?? '5' }} min de lecture
                </span>
                @if($article->focus_keyword)
                <span class="ar-meta-pill">{{ $article->focus_keyword }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Layout --}}
    <div class="site-shell ar-layout">
        {{-- Contenu article --}}
        <div>
            <div class="ar-body">
                <div class="ar-content">
                    @php
                        $content = \App\Helpers\InternalLinkingHelper::generateInternalLinks($article->content_html, 'article');
                        $content = preg_replace_callback(
                            '/(?<!href=["\'])(?<!>)(https?:\/\/[^\s<>"\'\)]+)(?![^<]*<\/a>)/',
                            fn($m) => '<a href="'.$m[1].'" target="_blank" rel="noopener noreferrer">'.$m[1].'</a>',
                            $content
                        );
                    @endphp
                    {!! $content !!}
                </div>

                {{-- Liens connexes --}}
                @php $suggestedLinks = \App\Helpers\InternalLinkingHelper::getSuggestedLinks('article', 4); @endphp
                @if(count($suggestedLinks) > 0)
                <div class="ar-links">
                    <div class="ar-links-title">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        Articles et services connexes
                    </div>
                    <div class="ar-links-grid">
                        @foreach($suggestedLinks as $link)
                        <a href="{{ $link['url'] }}" class="ar-link-item">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            {{ $link['title'] }}
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Partage --}}
                <div class="ar-share">
                    <span class="ar-share-label">Partager :</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}&quote={{ urlencode($article->title) }}"
                       target="_blank" rel="noopener noreferrer" class="ar-share-btn ar-share-fb">
                        <i class="fab fa-facebook-f"></i> Facebook
                    </a>
                    <a href="https://wa.me/?text={{ urlencode($article->title.' - '.request()->url()) }}"
                       target="_blank" rel="noopener noreferrer" class="ar-share-btn ar-share-wa">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                    <a href="mailto:?subject={{ urlencode($article->title) }}&body={{ urlencode('Je vous partage cet article : '.request()->url()) }}"
                       class="ar-share-btn ar-share-em">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        Email
                    </a>
                    <button onclick="copyUrl()" class="ar-share-btn ar-share-cp">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        Copier
                    </button>
                    <span class="ar-copy-ok" id="copy-ok">✓ Lien copié</span>
                </div>

                {{-- Meta bottom --}}
                <div class="ar-meta-bottom">
                    <div style="display:flex;align-items:center;gap:.75rem;">
                        @if($article->published_at)
                        <span>Publié le {{ $article->published_at->format('d/m/Y') }}</span>
                        @endif
                        <span>· {{ $article->estimated_reading_time ?? '5' }} min de lecture</span>
                    </div>
                    @if($article->focus_keyword)
                    <span class="ar-keyword">{{ $article->focus_keyword }}</span>
                    @endif
                </div>
            </div>

            {{-- Avis clients (mobile / sous contenu) --}}
            @if(isset($reviews) && count($reviews) > 0)
            <div style="background:#fff;border:1px solid rgba(30,20,10,.07);border-radius:1rem;padding:2rem;margin-top:1.5rem;" class="hidden lg:hidden">
                {{-- répété dans sidebar --}}
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <aside class="ar-sidebar">
            {{-- Card entreprise --}}
            <div class="ar-sidebar-card">
                <div class="ar-sc-logo">
                    @if(setting('company_logo'))
                    <img src="{{ asset(setting('company_logo')) }}" alt="{{ setting('company_name') }}" style="height:2rem;width:auto;object-fit:contain;filter:brightness(0) invert(1);">
                    @else
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    @endif
                </div>
                <div class="ar-sc-name">{{ setting('company_name') }}</div>
                <div class="ar-sc-sub">Votre expert local en {{ setting('company_specialization','rénovation') }}</div>

                @php
                    $scAddr = setting('company_address','');
                    $scCity = setting('company_city','');
                    $scZip  = setting('company_postal_code','');
                    $scFullAddr = trim(implode(', ', array_filter([$scAddr, trim($scZip.' '.$scCity)])));
                @endphp

                @if($scFullAddr)
                <div class="ar-sc-info">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    {{ $scFullAddr }}
                </div>
                @endif
                @if(setting('company_phone'))
                <a href="tel:{{ setting('company_phone_raw') }}" class="ar-sc-info">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13 1 .35 1.97.67 2.9a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.93.32 1.9.54 2.9.67A2 2 0 0 1 22 16.92z"/></svg>
                    {{ setting('company_phone') }}
                </a>
                @endif
                @if(setting('company_email'))
                <a href="mailto:{{ setting('company_email') }}" class="ar-sc-info" style="word-break:break-all;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    {{ setting('company_email') }}
                </a>
                @endif

                <a href="{{ route('form.step','propertyType') }}" class="ar-btn-devis">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    Devis gratuit en ligne
                </a>
                @if(setting('company_phone'))
                <a href="tel:{{ setting('company_phone_raw') }}" class="ar-btn-phone">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13 1 .35 1.97.67 2.9a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.93.32 1.9.54 2.9.67A2 2 0 0 1 22 16.92z"/></svg>
                    Appeler maintenant
                </a>
                @endif

                <div class="ar-trust-row">
                    <div class="ar-trust-item">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        Garantie
                    </div>
                    <div class="ar-trust-item">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
                        Certifié
                    </div>
                    <div class="ar-trust-item">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        5★
                    </div>
                </div>
            </div>

            {{-- Avis clients --}}
            @if(isset($reviews) && count($reviews) > 0)
            <div class="ar-sidebar-card-light">
                <div style="font-size:.8125rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#9C8E84;margin-bottom:1rem;">Avis clients</div>
                @foreach($reviews->take(3) as $review)
                <div class="ar-review">
                    <div class="ar-stars">★★★★★</div>
                    <div class="ar-review-text">"{{ $review->review_text }}"</div>
                    <div class="ar-reviewer">
                        <div class="ar-reviewer-avatar">{{ mb_substr($review->author_name,0,1) }}</div>
                        <span class="ar-reviewer-name">{{ $review->author_name }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </aside>
    </div>

    {{-- Mobile CTA bar --}}
    <div class="ar-mobile-cta">
        <a href="{{ route('form.step','propertyType') }}" class="ar-mob-btn-devis">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            Devis gratuit
        </a>
        @if(setting('company_phone'))
        <a href="tel:{{ setting('company_phone_raw') }}" class="ar-mob-btn-phone">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.85)" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13 1 .35 1.97.67 2.9a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.93.32 1.9.54 2.9.67A2 2 0 0 1 22 16.92z"/></svg>
        </a>
        @endif
    </div>
</div>

<script>
function copyUrl(){
    var url='{{ request()->url() }}';
    if(navigator.clipboard&&window.isSecureContext){
        navigator.clipboard.writeText(url).then(function(){showOk();});
    } else {
        var t=document.createElement('textarea');t.value=url;t.style.position='fixed';t.style.opacity='0';document.body.appendChild(t);t.select();document.execCommand('copy');document.body.removeChild(t);showOk();
    }
}
function showOk(){
    var el=document.getElementById('copy-ok');
    if(!el) return;
    el.style.display='inline-flex';
    setTimeout(function(){el.style.display='none';},3000);
}
</script>
@endsection
