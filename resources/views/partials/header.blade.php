{{-- ===== HEADER — Fond blanc, texte sombre ===== --}}
<style>
#site-header {
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 1000;
    background: #ffffff;
    border-bottom: 1px solid rgba(0,0,0,.07);
    transition: background .25s ease, box-shadow .25s ease;
}
#site-header.scrolled {
    background: rgba(255,255,255,.97);
    box-shadow: 0 4px 24px rgba(0,0,0,.08);
    backdrop-filter: blur(12px);
}

/* Logo */
.site-logo-img {
    height: 2.75rem;
    width: auto;
    max-width: 160px;
    object-fit: contain;
    display: block;
}
@media(min-width:640px){ .site-logo-img { max-width: 180px; height: 3rem; } }
.site-logo-text {
    font-weight: 800;
    font-size: 1rem;
    color: #1F1A14;
    letter-spacing: -.02em;
    line-height: 1.2;
}

/* Nav links */
.nav-link {
    color: #5C5046;
    font-weight: 600;
    font-size: .88rem;
    letter-spacing: .01em;
    transition: color .18s;
    position: relative;
    text-decoration: none;
}
.nav-link:hover { color: #1F1A14; }
.nav-link::after {
    content: '';
    position: absolute;
    bottom: -3px; left: 0; right: 0;
    height: 2px;
    background: var(--primary-color, #B7472A);
    transform: scaleX(0);
    transition: transform .22s ease;
    border-radius: 2px;
}
.nav-link:hover::after, .nav-link.active::after { transform: scaleX(1); }
.nav-link.active { color: #1F1A14; }

/* Hamburger */
#hamburger span { background: #1F1A14 !important; }

/* Dropdown */
.nav-dropdown { position: relative; }
.nav-dropdown-menu {
    position: absolute;
    top: calc(100% + 14px);
    left: 50%;
    transform: translateX(-50%) translateY(6px);
    min-width: 230px;
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 16px 48px rgba(0,0,0,.12);
    padding: 8px 0;
    opacity: 0;
    visibility: hidden;
    transition: opacity .2s, transform .2s;
    border: 1px solid rgba(0,0,0,.07);
}
.nav-dropdown:hover .nav-dropdown-menu {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(0);
}
.nav-dropdown-menu a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 18px;
    font-size: .86rem;
    font-weight: 600;
    color: #5C5046;
    transition: background .15s, color .15s;
    text-decoration: none;
}
.nav-dropdown-menu a:hover {
    background: #FAF7F2;
    color: #1F1A14;
}
.nav-dropdown-menu a:first-child {
    border-bottom: 1px solid rgba(0,0,0,.06);
    color: var(--primary-color, #B7472A);
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .08em;
    padding-bottom: 12px;
}

/* CTA buttons header */
.hdr-btn-phone {
    display: flex;
    align-items: center;
    gap: 7px;
    font-weight: 700;
    font-size: .83rem;
    padding: 8px 16px;
    border-radius: 10px;
    border: 1.5px solid rgba(0,0,0,.12);
    color: #5C5046;
    text-decoration: none;
    transition: border-color .18s, color .18s, background .18s;
    letter-spacing: .01em;
}
.hdr-btn-phone:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
    background: rgba(0,0,0,.02);
}
.hdr-btn-devis {
    display: flex;
    align-items: center;
    gap: 7px;
    font-weight: 700;
    font-size: .83rem;
    padding: 9px 18px;
    border-radius: 10px;
    background: var(--primary-color, #B7472A);
    color: #fff;
    text-decoration: none;
    transition: filter .18s, transform .18s;
}
.hdr-btn-devis:hover { filter: brightness(.88); transform: translateY(-1px); }

/* Mobile menu — noir (overlay plein écran) */
#mobile-menu {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 999;
    background: #111111;
    overflow-y: auto;
    flex-direction: column;
}
#mobile-menu.open { display: flex; }
.mob-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 13px 16px;
    border-radius: 12px;
    font-weight: 600;
    font-size: .95rem;
    color: rgba(255,255,255,.8);
    text-decoration: none;
    transition: background .15s, color .15s;
}
.mob-link:hover, .mob-link.active { background: rgba(255,255,255,.07); color: #fff; }
.mob-link-sub {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 16px 10px 44px;
    border-radius: 10px;
    font-weight: 500;
    font-size: .87rem;
    color: rgba(255,255,255,.55);
    text-decoration: none;
    transition: background .15s, color .15s;
}
.mob-link-sub:hover { background: rgba(255,255,255,.05); color: rgba(255,255,255,.85); }
</style>

<header id="site-header">
    <div class="site-shell">
        <div class="flex items-center justify-between" style="padding-top:14px; padding-bottom:14px;">

            {{-- Logo --}}
            <a href="{{ url('/') }}" class="flex items-center gap-3 flex-shrink-0" style="min-width:0;">
                @if(setting('company_logo'))
                    <img src="{{ asset(setting('company_logo')) }}"
                         alt="{{ setting('company_name') }}"
                         class="site-logo-img"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                    <span class="flex items-center gap-3" style="display:none;">
                        <span style="width:36px;height:36px;border-radius:10px;background:var(--primary-color);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-home" style="color:#fff;font-size:.8rem;"></i></span>
                        <span class="site-logo-text" style="max-width:160px;">{{ setting('company_name', 'Votre Entreprise') }}</span>
                    </span>
                @else
                    <div style="width:36px;height:36px;border-radius:10px;background:var(--primary-color);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-home" style="color:#fff;font-size:.8rem;"></i>
                    </div>
                    <span class="site-logo-text" style="max-width:160px;">{{ setting('company_name', 'Votre Entreprise') }}</span>
                @endif
            </a>

            {{-- Desktop Nav --}}
            @php
                $navServices = \App\Models\Setting::get('services', '[]');
                $navServices = is_string($navServices) ? json_decode($navServices, true) : ($navServices ?? []);
                if(!is_array($navServices)) $navServices = [];
                $navFeatured = array_filter($navServices, fn($s) => is_array($s) && ($s['is_menu'] ?? false) && ($s['is_visible'] ?? true));
            @endphp

            <nav class="hidden md:flex items-center gap-7 lg:gap-9">
                <a href="{{ url('/') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">Accueil</a>

                <div class="nav-dropdown">
                    <a href="{{ route('services.index') }}" class="nav-link flex items-center gap-1.5 {{ request()->routeIs('services.*') ? 'active' : '' }}">
                        Services
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-top:1px;opacity:.5;"><path d="m6 9 6 6 6-6"/></svg>
                    </a>
                    <div class="nav-dropdown-menu">
                        <a href="{{ route('services.index') }}">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                            Tous nos services
                        </a>
                        @foreach($navFeatured as $svc)
                            @if(isset($svc['name'], $svc['slug']))
                            <a href="{{ route('services.show', $svc['slug']) }}">
                                <i class="{{ $svc['icon'] ?? 'fas fa-wrench' }}" style="font-size:.7rem;opacity:.6;"></i>
                                {{ $svc['name'] }}
                            </a>
                            @endif
                        @endforeach
                    </div>
                </div>

                <a href="{{ route('portfolio.index') }}" class="nav-link {{ request()->routeIs('portfolio.*') ? 'active' : '' }}">Réalisations</a>
                <a href="{{ route('blog.index') }}" class="nav-link {{ request()->routeIs('blog.*') ? 'active' : '' }}">Blog</a>
                <a href="{{ route('contact') }}" class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
            </nav>

            {{-- Desktop CTAs --}}
            <div class="hidden md:flex items-center gap-3">
                <a href="tel:{{ setting('company_phone_raw', setting('company_phone')) }}"
                   class="hdr-btn-phone"
                   onclick="if(typeof trackPhoneCall==='function')trackPhoneCall('{{ setting('company_phone_raw', setting('company_phone')) }}','header')">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
                    {{ setting('company_phone', '06 42 21 41 51') }}
                </a>
                <a href="{{ route('form.step', 'propertyType') }}"
                   class="hdr-btn-devis"
                   onclick="if(typeof trackFormClick==='function')trackFormClick('{{ request()->url() }}')">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    Devis gratuit
                </a>
            </div>

            {{-- Hamburger --}}
            <button id="hamburger" class="md:hidden flex flex-col gap-1.5 p-2 rounded-lg" onclick="openMobileMenu()" aria-label="Menu">
                <span class="block w-6 h-0.5 rounded transition-all" style="background:#1F1A14;"></span>
                <span class="block w-6 h-0.5 rounded transition-all" style="background:#1F1A14;"></span>
                <span class="block w-4 h-0.5 rounded transition-all" style="background:#1F1A14;"></span>
            </button>
        </div>
    </div>
</header>

{{-- Mobile Menu --}}
<div id="mobile-menu" role="dialog" aria-modal="true" aria-label="Menu navigation">
    <div class="flex items-center justify-between px-5 py-4 flex-shrink-0" style="border-bottom:1px solid rgba(255,255,255,.08);">
        <a href="{{ url('/') }}" class="flex items-center gap-3" style="min-width:0;">
            @if(setting('company_logo'))
                <img src="{{ asset(setting('company_logo')) }}"
                     alt="{{ setting('company_name') }}"
                     style="height:2.2rem;width:auto;max-width:130px;object-fit:contain;display:block;filter:brightness(0) invert(1);"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                <span class="flex items-center gap-3" style="display:none;">
                    <span style="width:32px;height:32px;border-radius:9px;background:var(--primary-color);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-home" style="color:#fff;font-size:.75rem;"></i></span>
                    <span style="font-weight:800;color:#fff;font-size:.95rem;max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ setting('company_name') }}</span>
                </span>
            @else
                <div style="width:32px;height:32px;border-radius:9px;background:var(--primary-color);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-home" style="color:#fff;font-size:.75rem;"></i>
                </div>
                <span style="font-weight:800;color:#fff;font-size:.95rem;max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ setting('company_name') }}</span>
            @endif
        </a>
        <button onclick="closeMobileMenu()"
                style="width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,.08);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.7);border:none;cursor:pointer;flex-shrink:0;"
                aria-label="Fermer">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>
    </div>

    <nav class="flex-1 px-4 py-5 space-y-1 overflow-y-auto">
        <a href="{{ url('/') }}" class="mob-link {{ request()->is('/') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--primary-color)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Accueil
        </a>
        <a href="{{ route('services.index') }}" class="mob-link {{ request()->routeIs('services.*') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--primary-color)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76Z"/></svg>
            Nos Services
        </a>
        @foreach($navFeatured as $svc)
            @if(isset($svc['name'], $svc['slug']))
            <a href="{{ route('services.show', $svc['slug']) }}" class="mob-link-sub">
                <i class="{{ $svc['icon'] ?? 'fas fa-wrench' }}" style="color:var(--primary-color);font-size:.75rem;"></i>
                {{ $svc['name'] }}
            </a>
            @endif
        @endforeach
        <a href="{{ route('portfolio.index') }}" class="mob-link {{ request()->routeIs('portfolio.*') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--primary-color)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M3 15h18M9 3v18"/></svg>
            Réalisations
        </a>
        <a href="{{ route('blog.index') }}" class="mob-link {{ request()->routeIs('blog.*') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--primary-color)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            Blog
        </a>
        <a href="{{ route('contact') }}" class="mob-link {{ request()->routeIs('contact') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--primary-color)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            Contact
        </a>
    </nav>

    <div class="px-4 pb-8 pt-4 space-y-3 flex-shrink-0" style="border-top:1px solid rgba(255,255,255,.08);">
        <a href="{{ route('form.step', 'propertyType') }}"
           class="flex items-center justify-center gap-2 font-bold py-4 rounded-2xl text-base"
           style="background:var(--primary-color);color:#fff;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Devis gratuit en ligne
        </a>
        <a href="tel:{{ setting('company_phone_raw', setting('company_phone')) }}"
           class="flex items-center justify-center gap-2 font-bold py-4 rounded-2xl border-2 text-base transition-colors"
           style="border-color:rgba(255,255,255,.2);color:rgba(255,255,255,.85);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
            {{ setting('company_phone', '06 42 21 41 51') }}
        </a>
    </div>
</div>

{{-- Spacer --}}
<div style="height:60px;" class="md:hidden"></div>
<div style="height:65px;" class="hidden md:block"></div>

<script>
(function(){
    const h = document.getElementById('site-header');
    if(!h) return;
    window.addEventListener('scroll', function(){
        h.classList.toggle('scrolled', window.scrollY > 20);
    }, { passive: true });
})();
function openMobileMenu(){const m=document.getElementById('mobile-menu');if(m){m.classList.add('open');document.body.style.overflow='hidden';}}
function closeMobileMenu(){const m=document.getElementById('mobile-menu');if(m){m.classList.remove('open');document.body.style.overflow='';}}
function trackFormClick(p){fetch('/api/track-form-click',{method:'GET'}).catch(()=>{});}
function trackPhoneCall(n,l){fetch('/api/track-phone-call?num='+encodeURIComponent(n)+'&loc='+encodeURIComponent(l||''),{method:'GET'}).catch(()=>{});}
</script>
