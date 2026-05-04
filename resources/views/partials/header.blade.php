{{-- ===== HEADER MODERNE — sticky glassmorphism ===== --}}
<style>
#site-header {
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 1000;
    transition: background .35s ease, box-shadow .35s ease, padding .25s ease;
    background: transparent;
}
#site-header.scrolled {
    background: rgba(255,255,255,.96);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    box-shadow: 0 2px 24px rgba(0,0,0,.08);
}
#site-header.scrolled .nav-link  { color: #1f2937; }
#site-header.scrolled .nav-link:hover { color: var(--primary-color); }
#site-header.scrolled .logo-light { display: none; }
#site-header.scrolled .logo-dark  { display: block; }
#site-header .logo-dark { display: none; }
#site-header .logo-light { display: block; }

/* Sur les pages intérieures (non-hero) */
#site-header.solid {
    background: #fff;
    box-shadow: 0 2px 24px rgba(0,0,0,.08);
}
#site-header.solid .nav-link  { color: #1f2937; }
#site-header.solid .nav-link:hover { color: var(--primary-color); }
#site-header.solid .logo-light { display: none; }
#site-header.solid .logo-dark  { display: block; }

.nav-link {
    color: rgba(255,255,255,.92);
    font-weight: 600;
    font-size: .92rem;
    transition: color .2s;
    position: relative;
}
.nav-link::after {
    content: '';
    position: absolute;
    bottom: -4px; left: 0; right: 0;
    height: 2px;
    background: var(--primary-color);
    transform: scaleX(0);
    transition: transform .25s ease;
    border-radius: 2px;
}
.nav-link:hover::after, .nav-link.active::after { transform: scaleX(1); }

/* Dropdown */
.nav-dropdown { position: relative; }
.nav-dropdown-menu {
    position: absolute;
    top: calc(100% + 12px);
    left: 50%;
    transform: translateX(-50%) translateY(6px);
    min-width: 220px;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 20px 60px rgba(0,0,0,.14);
    padding: 8px 0;
    opacity: 0;
    visibility: hidden;
    transition: opacity .2s, transform .2s;
    border: 1px solid rgba(0,0,0,.06);
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
    font-size: .88rem;
    font-weight: 600;
    color: #374151;
    transition: background .15s, color .15s;
}
.nav-dropdown-menu a:hover {
    background: rgba(var(--primary-color-rgb,34,197,94),.07);
    color: var(--primary-color);
}
.nav-dropdown-menu a:first-child {
    border-bottom: 1px solid #f3f4f6;
    color: var(--primary-color);
    font-size: .82rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    padding-bottom: 12px;
}

/* Mobile */
#mobile-menu {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 999;
    background: #fff;
    overflow-y: auto;
}
#mobile-menu.open { display: flex; flex-direction: column; }
</style>

<header id="site-header" class="{{ !request()->is('/') ? 'solid' : '' }}">
    <div class="site-shell">
        <div class="flex items-center justify-between py-3 md:py-4">

            {{-- Logo --}}
            <a href="{{ url('/') }}" class="flex items-center flex-shrink-0">
                @if(setting('company_logo'))
                    <img src="{{ asset(setting('company_logo')) }}"
                         alt="{{ setting('company_name', 'Louis Hoffmann Élagage') }}"
                         class="logo-light h-11 w-auto"
                         width="200" height="44">
                    <img src="{{ asset(setting('company_logo')) }}"
                         alt="{{ setting('company_name', 'Louis Hoffmann Élagage') }}"
                         class="logo-dark h-11 w-auto"
                         width="200" height="44">
                @else
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white"
                             style="background: var(--primary-color);">
                            <i class="fas fa-tree text-sm"></i>
                        </div>
                        <span class="logo-dark font-extrabold text-gray-900 text-lg hidden">
                            {{ setting('company_name', 'Louis Hoffmann') }}
                        </span>
                        <span class="logo-light font-extrabold text-white text-lg">
                            {{ setting('company_name', 'Louis Hoffmann') }}
                        </span>
                    </div>
                @endif
            </a>

            {{-- Desktop Nav --}}
            @php
                $navServices = \App\Models\Setting::get('services', '[]');
                $navServices = is_string($navServices) ? json_decode($navServices, true) : ($navServices ?? []);
                if(!is_array($navServices)) $navServices = [];
                $navFeatured = array_filter($navServices, fn($s) => is_array($s) && ($s['is_menu'] ?? false) && ($s['is_visible'] ?? true));
            @endphp

            <nav class="hidden md:flex items-center gap-7">
                <a href="{{ url('/') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">Accueil</a>

                <div class="nav-dropdown">
                    <a href="{{ route('services.index') }}" class="nav-link flex items-center gap-1">
                        Services <i class="fas fa-chevron-down text-[10px] mt-0.5"></i>
                    </a>
                    <div class="nav-dropdown-menu">
                        <a href="{{ route('services.index') }}">
                            <i class="fas fa-list-ul text-xs"></i> Tous nos services
                        </a>
                        @foreach($navFeatured as $svc)
                            @if(isset($svc['name'], $svc['slug']))
                            <a href="{{ route('services.show', $svc['slug']) }}">
                                <i class="{{ $svc['icon'] ?? 'fas fa-leaf' }} text-xs opacity-60"></i>
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
                {{-- Phone --}}
                <a href="tel:{{ setting('company_phone_raw', setting('company_phone')) }}"
                   class="flex items-center gap-2 font-bold text-sm px-4 py-2.5 rounded-xl border-2 border-white/30 text-white hover:bg-white/15 transition-all scrolled-phone"
                   onclick="if(typeof trackPhoneCall==='function')trackPhoneCall('{{ setting('company_phone_raw', setting('company_phone')) }}','header')">
                    <i class="fas fa-phone text-xs animate-pulse"></i>
                    {{ setting('company_phone', '06 42 21 41 51') }}
                </a>
                {{-- Devis --}}
                <a href="{{ route('form.step', 'propertyType') }}"
                   class="flex items-center gap-2 text-white font-bold text-sm px-5 py-2.5 rounded-xl shadow-lg hover:scale-[1.03] transition-all"
                   style="background: var(--primary-color);"
                   onclick="if(typeof trackFormClick==='function')trackFormClick('{{ request()->url() }}')">
                    <i class="fas fa-calculator text-xs"></i>
                    Devis gratuit
                </a>
            </div>

            {{-- Hamburger --}}
            <button id="hamburger" class="md:hidden flex flex-col gap-1.5 p-2 rounded-lg" onclick="openMobileMenu()" aria-label="Menu">
                <span class="block w-6 h-0.5 bg-white rounded transition-all" id="hb1"></span>
                <span class="block w-6 h-0.5 bg-white rounded transition-all" id="hb2"></span>
                <span class="block w-4 h-0.5 bg-white rounded transition-all" id="hb3"></span>
            </button>

        </div>
    </div>
</header>

{{-- Mobile Menu --}}
<div id="mobile-menu" role="dialog" aria-modal="true" aria-label="Menu navigation">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <a href="{{ url('/') }}" class="flex items-center gap-2">
            @if(setting('company_logo'))
                <img src="{{ asset(setting('company_logo')) }}" alt="{{ setting('company_name') }}" class="h-10 w-auto">
            @else
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white" style="background:var(--primary-color);">
                    <i class="fas fa-tree text-xs"></i>
                </div>
                <span class="font-extrabold text-gray-900">{{ setting('company_name', 'Louis Hoffmann') }}</span>
            @endif
        </a>
        <button onclick="closeMobileMenu()" class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-gray-200 transition-colors" aria-label="Fermer">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <nav class="flex-1 px-5 py-6 space-y-1">
        <a href="{{ url('/') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-800 hover:bg-gray-50 transition-colors">
            <i class="fas fa-home text-sm w-4 text-center" style="color:var(--primary-color);"></i> Accueil
        </a>
        <a href="{{ route('services.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-800 hover:bg-gray-50 transition-colors">
            <i class="fas fa-tree text-sm w-4 text-center" style="color:var(--primary-color);"></i> Nos Services
        </a>
        @foreach($navFeatured as $svc)
            @if(isset($svc['name'], $svc['slug']))
            <a href="{{ route('services.show', $svc['slug']) }}" class="flex items-center gap-3 pl-11 pr-4 py-2.5 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                <i class="{{ $svc['icon'] ?? 'fas fa-leaf' }} text-xs" style="color:var(--primary-color);"></i>
                {{ $svc['name'] }}
            </a>
            @endif
        @endforeach
        <a href="{{ route('portfolio.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-800 hover:bg-gray-50 transition-colors">
            <i class="fas fa-images text-sm w-4 text-center" style="color:var(--primary-color);"></i> Réalisations
        </a>
        <a href="{{ route('blog.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-800 hover:bg-gray-50 transition-colors">
            <i class="fas fa-book-open text-sm w-4 text-center" style="color:var(--primary-color);"></i> Blog
        </a>
        <a href="{{ route('contact') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-gray-800 hover:bg-gray-50 transition-colors">
            <i class="fas fa-envelope text-sm w-4 text-center" style="color:var(--primary-color);"></i> Contact
        </a>
    </nav>

    <div class="px-5 pb-8 space-y-3">
        <a href="{{ route('form.step', 'propertyType') }}"
           class="flex items-center justify-center gap-2 text-white font-bold py-4 rounded-2xl text-base shadow-lg"
           style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
            <i class="fas fa-calculator"></i> Devis gratuit en ligne
        </a>
        <a href="tel:{{ setting('company_phone_raw', setting('company_phone')) }}"
           class="flex items-center justify-center gap-2 font-bold py-4 rounded-2xl border-2 text-base transition-colors"
           style="border-color:var(--primary-color); color:var(--primary-color);">
            <i class="fas fa-phone"></i> {{ setting('company_phone', '06 42 21 41 51') }}
        </a>
    </div>
</div>

{{-- Spacer pour les pages intérieures --}}
@if(!request()->is('/'))
<div class="h-[72px] md:h-[80px]"></div>
@endif

<script>
(function(){
    const header = document.getElementById('site-header');
    const hb1 = document.getElementById('hb1');
    const hb2 = document.getElementById('hb2');
    const hamburger = document.getElementById('hamburger');

    // Phone link style synced with scroll
    function syncPhone() {
        const links = document.querySelectorAll('.scrolled-phone');
        const scrolled = header.classList.contains('scrolled') || header.classList.contains('solid');
        links.forEach(l => {
            if(scrolled) {
                l.style.color = 'var(--primary-color)';
                l.style.borderColor = 'var(--primary-color)';
            } else {
                l.style.color = '';
                l.style.borderColor = '';
            }
        });
        // hamburger bars
        if(hamburger) {
            const bars = hamburger.querySelectorAll('span');
            bars.forEach(b => {
                b.style.background = scrolled ? '#1f2937' : '';
            });
        }
    }

    if(header && !header.classList.contains('solid')) {
        window.addEventListener('scroll', function(){
            if(window.scrollY > 60) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            syncPhone();
        }, { passive: true });
    }
    syncPhone();
})();

function openMobileMenu() {
    const m = document.getElementById('mobile-menu');
    m.classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeMobileMenu() {
    const m = document.getElementById('mobile-menu');
    m.classList.remove('open');
    document.body.style.overflow = '';
}

function trackFormClick(page) {
    fetch('/api/track-form-click', { method: 'GET' }).catch(()=>{});
}
</script>
