@extends('layouts.app')

@section('title', $pageTitle ?? 'Contact')
@section('description', $pageDescription ?? 'Contactez-nous')

@php
    $pageType = 'website';
    $contactHeroImage = setting('contact_hero_image');
@endphp

@push('head')
<style>
@import url('https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,600;0,9..144,700;1,9..144,400&family=Manrope:wght@400;500;600;700&display=swap');
.ct{font-family:'Manrope',sans-serif;background:#FAF7F2;color:#1F1A14;}
.ct h1,.ct h2,.ct h3{font-family:'Fraunces',serif;}
/* Hero */
.ct-hero{background:#1F1A14;position:relative;overflow:hidden;padding:5rem 0 4rem;text-align:center;}
.ct-hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 80% 60% at 50% 0%,rgba(255,255,255,.04) 0%,transparent 60%);}
.ct-hero-img{position:absolute;inset:0;background-size:cover;background-position:center;}
.ct-hero-overlay{position:absolute;inset:0;background:rgba(15,12,8,.65);}
.ct-eyebrow{font-size:.75rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.45);display:block;margin-bottom:1rem;}
.ct-hero-title{font-size:clamp(2.5rem,6vw,4rem);font-weight:700;color:#fff;line-height:1.1;margin-bottom:1rem;}
.ct-hero-sub{font-size:1.1rem;color:rgba(255,255,255,.7);max-width:520px;margin:0 auto 2.5rem;}
.ct-hero-actions{display:flex;flex-wrap:wrap;gap:1rem;justify-content:center;}
.ct-btn-white{display:inline-flex;align-items:center;gap:.6rem;padding:.85rem 1.75rem;border-radius:999px;background:#fff;color:#1F1A14;font-weight:700;text-decoration:none;transition:all .2s;font-family:'Manrope',sans-serif;}
.ct-btn-white:hover{background:#F2EDE4;transform:translateY(-2px);}
.ct-btn-ghost{display:inline-flex;align-items:center;gap:.6rem;padding:.85rem 1.75rem;border-radius:999px;border:1.5px solid rgba(255,255,255,.25);color:#fff;font-weight:600;text-decoration:none;transition:all .2s;font-family:'Manrope',sans-serif;}
.ct-btn-ghost:hover{background:rgba(255,255,255,.1);transform:translateY(-2px);}
/* Alerts */
.ct-alert{padding:1rem 1.25rem;border-radius:.75rem;margin-bottom:1.5rem;display:flex;align-items:flex-start;gap:.75rem;}
.ct-alert-success{background:#D1FAE5;border-left:4px solid #059669;color:#064E3B;}
.ct-alert-error{background:#FEE2E2;border-left:4px solid #DC2626;color:#7F1D1D;}
/* Main layout */
.ct-main{padding:4rem 0;}
.ct-grid{display:grid;grid-template-columns:2fr 3fr;gap:3rem;align-items:start;}
@media(max-width:900px){.ct-grid{grid-template-columns:1fr;gap:2rem;}}
/* Info cards */
.ct-info-card{background:#fff;border:1px solid rgba(30,20,10,.08);border-radius:.875rem;padding:1.25rem 1.5rem;display:flex;align-items:flex-start;gap:1rem;margin-bottom:1rem;transition:transform .2s,box-shadow .2s,border-color .2s;}
.ct-info-card:hover{transform:translateY(-3px);box-shadow:0 12px 32px rgba(30,20,10,.08);border-color:rgba(30,20,10,.15);}
.ct-info-icon{width:48px;height:48px;border-radius:.625rem;background:linear-gradient(135deg,var(--primary-color,#3b82f6),var(--secondary-color,#10b981));display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.ct-info-label{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#9C8E84;margin-bottom:.2rem;}
.ct-info-value{font-size:.9375rem;font-weight:600;color:#1F1A14;text-decoration:none;}
.ct-info-value:hover{color:var(--primary-color,#3b82f6);}
.ct-info-note{font-size:.75rem;color:#9C8E84;margin-top:.15rem;}
.ct-cta-box{background:#1F1A14;border-radius:1rem;padding:2rem;margin-top:1.5rem;text-align:center;}
.ct-cta-box-title{font-family:'Fraunces',serif;font-size:1.375rem;font-weight:700;color:#fff;margin-bottom:.5rem;}
.ct-cta-box-sub{font-size:.875rem;color:rgba(255,255,255,.6);margin-bottom:1.25rem;}
.ct-btn-primary{display:inline-flex;align-items:center;gap:.6rem;padding:.75rem 1.5rem;border-radius:999px;background:var(--primary-color,#3b82f6);color:#fff;font-weight:700;text-decoration:none;font-family:'Manrope',sans-serif;transition:opacity .2s,transform .2s;}
.ct-btn-primary:hover{opacity:.9;transform:translateY(-2px);}
/* Form */
.ct-form-wrap{background:#fff;border:1px solid rgba(30,20,10,.07);border-radius:1rem;padding:2.5rem;}
.ct-form-title{font-size:1.625rem;font-weight:700;color:#1F1A14;margin-bottom:.25rem;}
.ct-form-sub{font-size:.9375rem;color:#6B6157;margin-bottom:2rem;}
.ct-label{display:block;font-size:.8125rem;font-weight:700;color:#3D3530;margin-bottom:.4rem;}
.ct-input,.ct-textarea,.ct-select{width:100%;padding:.75rem 1rem;border:1.5px solid rgba(30,20,10,.12);border-radius:.625rem;font-family:'Manrope',sans-serif;font-size:.9375rem;color:#1F1A14;background:#fff;transition:border-color .2s,box-shadow .2s;outline:none;box-sizing:border-box;}
.ct-input:focus,.ct-textarea:focus,.ct-select:focus{border-color:var(--primary-color,#3b82f6);box-shadow:0 0 0 3px rgba(59,130,246,.12);}
.ct-textarea{resize:vertical;min-height:130px;}
.ct-file{width:100%;padding:.75rem 1rem;border:1.5px dashed rgba(30,20,10,.15);border-radius:.625rem;font-family:'Manrope',sans-serif;font-size:.875rem;color:#6B6157;background:#FAF7F2;cursor:pointer;box-sizing:border-box;}
.ct-row2{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;}
@media(max-width:560px){.ct-row2{grid-template-columns:1fr;}}
.ct-field{margin-bottom:1.25rem;}
.ct-submit{width:100%;padding:1rem;border:none;border-radius:999px;background:linear-gradient(135deg,var(--primary-color,#3b82f6),var(--secondary-color,#10b981));color:#fff;font-family:'Manrope',sans-serif;font-size:1rem;font-weight:700;cursor:pointer;transition:opacity .2s,transform .2s;display:flex;align-items:center;justify-content:center;gap:.6rem;margin-top:1.5rem;}
.ct-submit:hover{opacity:.9;transform:translateY(-2px);}
.ct-privacy{font-size:.75rem;color:#9C8E84;text-align:center;margin-top:.75rem;display:flex;align-items:center;justify-content:center;gap:.3rem;}
/* Map */
.ct-map-section{padding:4rem 0;background:#F2EDE4;}
.ct-map-title{font-family:'Fraunces',serif;font-size:clamp(1.75rem,4vw,2.5rem);font-weight:700;color:#1F1A14;text-align:center;margin-bottom:.5rem;}
.ct-map-sub{font-size:1rem;color:#6B6157;text-align:center;margin-bottom:2.5rem;}
.ct-map-wrap{border-radius:1rem;overflow:hidden;box-shadow:0 16px 48px rgba(30,20,10,.12);}
/* FAQ */
.ct-faq-section{padding:4rem 0;}
.ct-faq-title{font-family:'Fraunces',serif;font-size:clamp(1.75rem,4vw,2.5rem);font-weight:700;color:#1F1A14;text-align:center;margin-bottom:.5rem;}
.ct-faq-sub{font-size:1rem;color:#6B6157;text-align:center;margin-bottom:2.5rem;}
.ct-faq-item{background:#fff;border:1px solid rgba(30,20,10,.08);border-radius:.75rem;margin-bottom:.75rem;overflow:hidden;transition:border-color .2s;}
.ct-faq-item:hover{border-color:rgba(30,20,10,.18);}
.ct-faq-q{width:100%;background:none;border:none;padding:1.25rem 1.5rem;text-align:left;font-family:'Manrope',sans-serif;font-size:.9375rem;font-weight:700;color:#1F1A14;cursor:pointer;display:flex;justify-content:space-between;align-items:center;gap:1rem;}
.ct-faq-a{padding:0 1.5rem 1.25rem;font-size:.875rem;color:#6B6157;line-height:1.7;border-top:1px solid rgba(30,20,10,.06);}
</style>
@endpush

@section('content')
<div class="ct">
    {{-- Hero --}}
    <section class="ct-hero">
        @if(!empty($contactHeroImage))
            <div class="ct-hero-img" style="background-image:url('{{ asset($contactHeroImage) }}');"></div>
            <div class="ct-hero-overlay"></div>
        @endif
        <div class="site-shell" style="position:relative;z-index:1;">
            <span class="ct-eyebrow">Nous contacter</span>
            <h1 class="ct-hero-title">Parlons de<br><em style="font-style:italic;color:rgba(255,255,255,.55);">votre projet</em></h1>
            <p class="ct-hero-sub">Une question, un projet ? Notre équipe vous répond rapidement — devis gratuit sous 24h.</p>
            <div class="ct-hero-actions">
                <a href="{{ route('form.step', 'propertyType') }}" class="ct-btn-white">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    Devis gratuit en 2 min
                </a>
                @if(!empty($companySettings['phone']))
                <a href="tel:{{ $companySettings['phone_raw'] ?? $companySettings['phone'] }}" class="ct-btn-ghost"
                   @if(method_exists(App\Http\Controllers\Controller::class,'trackPhoneCall')) onclick="trackPhoneCall('{{ $companySettings['phone_raw'] ?? $companySettings['phone'] }}','contact')" @endif>
                    <span style="display:inline-flex;width:8px;height:8px;border-radius:50%;background:#4ade80;box-shadow:0 0 0 3px rgba(74,222,128,.3);"></span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13.5 19.79 19.79 0 0 1 1.61 5c-.11-1.08.72-2 1.8-2h3a2 2 0 0 1 2 1.72c.13 1 .35 1.97.67 2.9a2 2 0 0 1-.45 2.11L7.91 10.4a16 16 0 0 0 5.69 5.69l1.67-1.67a2 2 0 0 1 2.11-.45c.93.32 1.9.54 2.9.67A2 2 0 0 1 22 16.92z"/></svg>
                    {{ $companySettings['phone'] }}
                </a>
                @endif
            </div>
        </div>
    </section>

    {{-- Alerts --}}
    <div class="site-shell" style="padding-top:2rem;">
        @if(session('success'))
        <div class="ct-alert ct-alert-success">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:2px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif
        @if($errors->any())
        <div class="ct-alert ct-alert-error">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:2px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <div>
                <p style="font-weight:700;margin-bottom:.25rem;">Veuillez corriger les erreurs suivantes :</p>
                <ul style="list-style:disc;padding-left:1.25rem;font-size:.875rem;">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
        @if(session('error'))
        <div class="ct-alert ct-alert-error">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:2px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>{{ session('error') }}</span>
        </div>
        @endif
    </div>

    {{-- Main content --}}
    <section class="ct-main">
        <div class="site-shell">
            <div class="ct-grid">
                {{-- Colonne infos --}}
                <div>
                    <p style="font-size:.75rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#9C8E84;margin-bottom:.75rem;">Coordonnées</p>
                    <h2 style="font-family:'Fraunces',serif;font-size:1.875rem;font-weight:700;color:#1F1A14;margin-bottom:1.75rem;line-height:1.2;">Comment<br>nous joindre ?</h2>

                    {{-- Adresse --}}
                    @if(!empty($companySettings['address']) || !empty($companySettings['city']))
                    <div class="ct-info-card">
                        <div class="ct-info-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <div>
                            <div class="ct-info-label">Notre adresse</div>
                            <div class="ct-info-value">
                                @if(!empty($companySettings['address'])){{ $companySettings['address'] }},@endif
                                {{ $companySettings['postal_code'] ?? '' }} {{ $companySettings['city'] ?? '' }}
                            </div>
                            <div class="ct-info-note">{{ $companySettings['country'] ?? 'France' }}</div>
                        </div>
                    </div>
                    @endif

                    {{-- Téléphone --}}
                    @if(!empty($companySettings['phone']))
                    <div class="ct-info-card">
                        <div class="ct-info-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13.5 19.79 19.79 0 0 1 1.61 5c-.11-1.08.72-2 1.8-2h3a2 2 0 0 1 2 1.72c.13 1 .35 1.97.67 2.9a2 2 0 0 1-.45 2.11L7.91 10.4a16 16 0 0 0 5.69 5.69l1.67-1.67a2 2 0 0 1 2.11-.45c.93.32 1.9.54 2.9.67A2 2 0 0 1 22 16.92z"/></svg>
                        </div>
                        <div>
                            <div class="ct-info-label">Appelez-nous</div>
                            <a href="tel:{{ $companySettings['phone_raw'] ?? $companySettings['phone'] }}" class="ct-info-value">{{ $companySettings['phone'] }}</a>
                            <div class="ct-info-note">Lun–Ven : 9h – 18h</div>
                        </div>
                    </div>
                    @endif

                    {{-- Email --}}
                    @if(!empty($companySettings['email']))
                    <div class="ct-info-card">
                        <div class="ct-info-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </div>
                        <div>
                            <div class="ct-info-label">Écrivez-nous</div>
                            <a href="mailto:{{ $companySettings['email'] }}" class="ct-info-value" style="word-break:break-all;">{{ $companySettings['email'] }}</a>
                            <div class="ct-info-note">Réponse sous 24h</div>
                        </div>
                    </div>
                    @endif

                    {{-- CTA simulateur --}}
                    <div class="ct-cta-box">
                        <div style="width:48px;height:48px;border-radius:.625rem;background:rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.9)" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                        </div>
                        <div class="ct-cta-box-title">Devis rapide</div>
                        <div class="ct-cta-box-sub">Obtenez une estimation personnalisée en moins de 2 minutes</div>
                        <a href="{{ route('form.step', 'propertyType') }}" class="ct-btn-primary">
                            Lancer le simulateur
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>

                {{-- Formulaire --}}
                <div class="ct-form-wrap" id="contactForm">
                    <div class="ct-form-title">Envoyez un message</div>
                    <div class="ct-form-sub">Remplissez le formulaire et nous vous recontacterons rapidement</div>

                    <form action="{{ route('contact.send') }}" method="POST" enctype="multipart/form-data" id="ct-form">
                        @csrf
                        <div class="ct-row2">
                            <div class="ct-field">
                                <label for="name" class="ct-label">Nom complet *</label>
                                <input type="text" id="name" name="name" required minlength="3" placeholder="Jean Dupont" class="ct-input" value="{{ old('name') }}">
                            </div>
                            <div class="ct-field">
                                <label for="email" class="ct-label">Email *</label>
                                <input type="email" id="email" name="email" required placeholder="jean.dupont@exemple.fr" class="ct-input" value="{{ old('email') }}">
                            </div>
                        </div>
                        <div class="ct-row2">
                            <div class="ct-field">
                                <label for="phone" class="ct-label">Téléphone *</label>
                                <input type="tel" id="phone" name="phone" required minlength="6" placeholder="06 12 34 56 78" class="ct-input" value="{{ old('phone') }}">
                            </div>
                            <div class="ct-field">
                                <label for="postal_code" class="ct-label">Code postal *</label>
                                <input type="text" id="postal_code" name="postal_code" required minlength="4" maxlength="10" placeholder="21800" class="ct-input" value="{{ old('postal_code') }}">
                            </div>
                        </div>
                        <div class="ct-row2">
                            <div class="ct-field">
                                <label for="city" class="ct-label">Ville *</label>
                                <input type="text" id="city" name="city" required minlength="2" placeholder="Chevigny-Saint-Sauveur" class="ct-input" value="{{ old('city') }}">
                            </div>
                            <div class="ct-field">
                                <label for="callback_time" class="ct-label">Quand vous rappeler ? *</label>
                                <select id="callback_time" name="callback_time" required class="ct-select">
                                    <option value="">Sélectionnez un créneau</option>
                                    <option value="matin" {{ old('callback_time')=='matin'?'selected':'' }}>Matin (9h – 12h)</option>
                                    <option value="apres-midi" {{ old('callback_time')=='apres-midi'?'selected':'' }}>Après-midi (14h – 17h)</option>
                                    <option value="soir" {{ old('callback_time')=='soir'?'selected':'' }}>Soir (17h – 19h)</option>
                                    <option value="flexible" {{ old('callback_time')=='flexible'?'selected':'' }}>Flexible</option>
                                </select>
                            </div>
                        </div>
                        <div class="ct-field">
                            <label for="service_interest" class="ct-label">Service qui vous intéresse *</label>
                            <select id="service_interest" name="service_interest" required class="ct-select">
                                <option value="">Sélectionnez un service</option>
                                @php
                                    $svcRaw = \App\Models\Setting::get('services','[]');
                                    $svcList = is_string($svcRaw) ? json_decode($svcRaw,true) : ($svcRaw??[]);
                                    if(!is_array($svcList)) $svcList=[];
                                @endphp
                                @foreach($svcList as $svc)
                                    @if(is_array($svc) && isset($svc['name']) && ($svc['is_visible']??true))
                                    <option value="{{ $svc['name'] }}" {{ old('service_interest')==$svc['name']?'selected':'' }}>{{ $svc['name'] }}</option>
                                    @endif
                                @endforeach
                                <option value="Autre" {{ old('service_interest')=='Autre'?'selected':'' }}>Autre</option>
                            </select>
                        </div>
                        <div class="ct-field">
                            <label for="subject" class="ct-label">Sujet *</label>
                            <input type="text" id="subject" name="subject" required minlength="6" placeholder="Résumé de votre demande" class="ct-input" value="{{ old('subject') }}">
                        </div>
                        <div class="ct-field">
                            <label for="message" class="ct-label">Message *</label>
                            <textarea id="message" name="message" rows="5" required minlength="6" placeholder="Décrivez votre projet en détail..." class="ct-textarea">{{ old('message') }}</textarea>
                        </div>
                        <div class="ct-field">
                            <label for="attachments" class="ct-label">Photos (optionnel)</label>
                            <input type="file" id="attachments" name="attachments[]" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" multiple class="ct-file">
                            <div style="font-size:.75rem;color:#9C8E84;margin-top:.35rem;">JPEG, PNG, WEBP — max 5 Mo par image</div>
                        </div>

                        {{-- reCAPTCHA --}}
                        @if(setting('recaptcha_enabled', false) && setting('recaptcha_site_key') && setting('recaptcha_secret_key'))
                        <div id="recaptcha-container"></div>
                        <input type="hidden" name="recaptcha_token" id="recaptcha_token">
                        @endif

                        <button type="submit" class="ct-submit" id="submitBtn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                            Envoyer le message
                        </button>
                        <div class="ct-privacy">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            Vos données sont protégées et ne seront jamais partagées
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    {{-- Google Maps --}}
    @php
        $addr = $companySettings['address'] ?? '';
        $mapCity = $companySettings['city'] ?? '';
        $mapZip = $companySettings['postal_code'] ?? '';
        $mapCountry = $companySettings['country'] ?? 'France';
        $fullAddr = trim(implode(' ', array_filter([$addr, $mapZip, $mapCity, $mapCountry])));
    @endphp
    @if($fullAddr)
    <section class="ct-map-section">
        <div class="site-shell">
            <h2 class="ct-map-title">Nous trouver</h2>
            <p class="ct-map-sub">Venez nous rendre visite ou appelez-nous directement</p>
            <div class="ct-map-wrap">
                <iframe width="100%" height="420" style="border:0;display:block;" loading="lazy" allowfullscreen
                    src="https://www.google.com/maps?q={{ urlencode($fullAddr) }}&output=embed"></iframe>
            </div>
        </div>
    </section>
    @endif

    {{-- FAQ --}}
    @if(!empty($faqs) && count($faqs) > 0)
    <section class="ct-faq-section">
        <div class="site-shell">
            <h2 class="ct-faq-title">Questions fréquentes</h2>
            <p class="ct-faq-sub">Les réponses aux questions les plus posées</p>
            <div style="max-width:760px;margin:0 auto;">
                @foreach($faqs as $faq)
                <details class="ct-faq-item">
                    <summary class="ct-faq-q" style="cursor:pointer;list-style:none;">
                        <span>{{ $faq['question'] ?? '' }}</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;transition:transform .3s;"><polyline points="6 9 12 15 18 9"/></svg>
                    </summary>
                    <div class="ct-faq-a">{!! nl2br(e($faq['answer'] ?? '')) !!}</div>
                </details>
                @endforeach
                <div style="text-align:center;margin-top:2rem;">
                    <p style="font-size:.875rem;color:#6B6157;margin-bottom:.75rem;">Vous n'avez pas trouvé la réponse à votre question ?</p>
                    <a href="#contactForm" style="font-size:.875rem;font-weight:700;color:var(--primary-color,#3b82f6);text-decoration:none;">Contactez-nous directement →</a>
                </div>
            </div>
        </div>
    </section>
    @endif
</div>

@push('scripts')
{{-- reCAPTCHA --}}
@if(setting('recaptcha_enabled', false) && setting('recaptcha_site_key') && setting('recaptcha_secret_key'))
@include('form.partials.recaptcha')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('ct-form');
    var btn = document.getElementById('submitBtn');
    if(!form || !btn) return;
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        btn.disabled = true;
        btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:spin 1s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Envoi en cours...';
        if(typeof grecaptcha !== 'undefined') {
            grecaptcha.ready(function() {
                grecaptcha.execute('{{ setting('recaptcha_site_key') }}', {action:'contact'}).then(function(token) {
                    document.getElementById('recaptcha_token').value = token;
                    form.submit();
                }).catch(function(){ form.submit(); });
            });
        } else { form.submit(); }
    });
});
</script>
@endif
<style>
@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}
details.ct-faq-item[open] summary svg{transform:rotate(180deg);}
</style>
@endpush
@endsection
