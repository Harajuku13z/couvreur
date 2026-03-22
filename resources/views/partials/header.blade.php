<!-- Header -->
<header class="site-header shadow-sm" style="background-color: var(--header-bg); border-bottom: 1px solid var(--header-border);">
<style>
    /* Styles spécifiques pour mobile */
    @media (max-width: 768px) {
        .header-mobile {
            padding: 1rem 0 !important;
        }
        
        .logo-mobile {
            height: 3rem !important;
            width: auto !important;
        }
        
        .text-mobile {
            font-size: 1.5rem !important;
        }
        
        .button-mobile {
            padding: 0.75rem 1.25rem !important;
            font-size: 0.9rem !important;
        }
    }
</style>
    <div class="site-shell">
        <div class="flex justify-between items-center py-3 header-mobile">
            <div class="flex items-center">
                <a href="{{ url('/') }}" class="flex items-center">
                    @if(setting('company_logo'))
                        <img src="{{ asset(setting('company_logo')) }}" alt="{{ setting('company_name') }}" class="h-12 w-auto logo-mobile">
                    @else
                        <span class="text-2xl font-bold text-mobile" style="color: var(--primary-color);">
                            {{ setting('company_name', 'Votre Entreprise') }}
                        </span>
                    @endif
                </a>
            </div>
            
            <!-- Desktop Navigation -->
            <nav class="hidden md:flex items-center space-x-8">
                <a href="{{ url('/') }}" class="site-nav-link font-medium">Accueil</a>
                
                @php
                    $servicesData = \App\Models\Setting::get('services', '[]');
                    $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
                    
                    // S'assurer que $services est toujours un tableau
                    if (!is_array($services)) {
                        $services = [];
                    }
                    
                    $featuredServices = array_filter($services, function($service) {
                        return is_array($service) && ($service['is_menu'] ?? false) && ($service['is_visible'] ?? true);
                    });
                @endphp
                
                @if(count($featuredServices) > 0)
                <div class="relative group">
                    <a href="{{ route('services.index') }}" class="site-nav-link font-medium flex items-center">
                        Nos Services
                        <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </a>
                    <div class="absolute top-full left-0 mt-2 w-48 rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50" style="background: var(--dropdown-bg);">
                        <div class="py-2">
                            <a href="{{ route('services.index') }}" 
                               class="block px-4 py-2 site-dropdown-link transition-colors font-semibold border-b" style="border-color: var(--header-border);">
                                <i class="fas fa-list mr-2"></i>Tous nos services
                            </a>
                            @foreach($featuredServices as $service)
                                @if(is_array($service) && isset($service['name']) && isset($service['slug']))
                                <a href="{{ route('services.show', $service['slug']) }}" 
                                   class="block px-4 py-2 site-dropdown-link transition-colors">
                                    {{ $service['name'] }}
                                </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                @else
                <!-- Lien direct vers la page Nos Services si pas de services mis en vedette -->
                <a href="{{ route('services.index') }}" class="site-nav-link font-medium">Nos Services</a>
                @endif
                
                <a href="{{ route('portfolio.index') }}" class="site-nav-link font-medium">Nos Réalisations</a>
                
                <a href="{{ route('blog.index') }}" class="site-nav-link font-medium">Blog et Astuces</a>
                
                <a href="{{ route('contact') }}" class="site-nav-link font-medium">Contact</a>
                
            </nav>
            
            <!-- Social Media Icons & CTA Buttons -->
            <div class="hidden md:flex items-center space-x-4">
                <!-- Social Media Icons -->
                @php
                    $socialNetworks = [
                        'facebook_url' => ['icon' => 'fab fa-facebook', 'color' => 'hover:text-blue-600', 'label' => 'Suivez-nous sur Facebook'],
                        'instagram_url' => ['icon' => 'fab fa-instagram', 'color' => 'hover:text-pink-600', 'label' => 'Suivez-nous sur Instagram'],
                        'twitter_url' => ['icon' => 'fab fa-twitter', 'color' => 'hover:text-blue-400', 'label' => 'Suivez-nous sur Twitter'],
                        'linkedin_url' => ['icon' => 'fab fa-linkedin', 'color' => 'hover:text-blue-700', 'label' => 'Suivez-nous sur LinkedIn'],
                        'youtube_url' => ['icon' => 'fab fa-youtube', 'color' => 'hover:text-red-600', 'label' => 'Suivez-nous sur YouTube'],
                        'tiktok_url' => ['icon' => 'fab fa-tiktok', 'color' => 'hover:text-gray-800', 'label' => 'Suivez-nous sur TikTok'],
                        'pinterest_url' => ['icon' => 'fab fa-pinterest', 'color' => 'hover:text-red-700', 'label' => 'Suivez-nous sur Pinterest'],
                        'snapchat_url' => ['icon' => 'fab fa-snapchat', 'color' => 'hover:text-yellow-500', 'label' => 'Suivez-nous sur Snapchat'],
                        'whatsapp_url' => ['icon' => 'fab fa-whatsapp', 'color' => 'hover:text-green-600', 'label' => 'Contactez-nous sur WhatsApp'],
                        'telegram_url' => ['icon' => 'fab fa-telegram', 'color' => 'hover:text-blue-500', 'label' => 'Contactez-nous sur Telegram'],
                    ];
                    
                    $activeSocialNetworks = array_filter($socialNetworks, function($key) {
                        return !empty(setting($key));
                    }, ARRAY_FILTER_USE_KEY);
                @endphp
                
                @if(count($activeSocialNetworks) > 0)
                <div class="flex space-x-3 mr-4">
                    @foreach(array_slice($activeSocialNetworks, 0, 4) as $key => $network)
                        <a href="{{ setting($key) }}" target="_blank" rel="noopener noreferrer" 
                           class="site-nav-muted {{ $network['color'] }} transition-colors text-lg"
                           aria-label="{{ $network['label'] }}">
                            <i class="{{ $network['icon'] }}" aria-hidden="true"></i>
                        </a>
                    @endforeach
                </div>
                @endif
                
                <!-- CTA Buttons -->
                <a href="{{ route('form.step', 'propertyType') }}" 
                   class="text-white px-4 py-2 rounded-lg transition-colors font-medium button-mobile"
                   style="background-color: var(--primary-color);"
                   onmouseover="this.style.backgroundColor='var(--secondary-color)'"
                   onmouseout="this.style.backgroundColor='var(--primary-color)'"
                   onclick="trackFormClick('{{ request()->url() }}')">
                    <i class="fas fa-calculator mr-2"></i>Simulateur de Prix
                </a>
                <a href="tel:{{ setting('company_phone') }}" 
                   class="text-white px-4 py-2 rounded-lg transition-colors font-medium button-mobile"
                   style="background-color: var(--primary-color);"
                   onmouseover="this.style.backgroundColor='var(--secondary-color)'"
                   onmouseout="this.style.backgroundColor='var(--primary-color)'">
                    <i class="fas fa-phone mr-2"></i>Appelez-nous
                </a>
            </div>
            
            <!-- Mobile Menu Button -->
            <button class="md:hidden site-nav-link" onclick="toggleMobileMenu()" aria-label="Ouvrir le menu de navigation" aria-expanded="false" id="mobileMenuButton">
                <i class="fas fa-bars text-xl" aria-hidden="true"></i>
            </button>
        </div>
        
        <!-- Mobile Navigation -->
        <div id="mobileMenu" class="md:hidden hidden border-t py-4 max-h-screen overflow-y-auto" style="border-color: var(--header-border);">
            <nav class="site-shell flex flex-col space-y-4">
                <a href="{{ url('/') }}" class="site-nav-link font-medium">Accueil</a>
                
                @if(count($featuredServices) > 0)
                <div class="space-y-2">
                    <a href="{{ route('services.index') }}" class="site-nav-link font-medium">Nos Services</a>
                    <div class="pl-4 space-y-1">
                        <a href="{{ route('services.index') }}" 
                           class="block site-nav-muted font-semibold">
                            <i class="fas fa-list mr-2"></i>Tous nos services
                        </a>
                        @foreach($featuredServices as $service)
                        <a href="{{ route('services.show', $service['slug']) }}" 
                           class="block site-nav-muted">
                            {{ $service['name'] }}
                        </a>
                        @endforeach
                    </div>
                </div>
                @else
                <!-- Lien direct vers la page Nos Services si pas de services mis en vedette -->
                <a href="{{ route('services.index') }}" class="site-nav-link font-medium">Nos Services</a>
                @endif
                
                <a href="{{ route('portfolio.index') }}" class="site-nav-link font-medium">Nos Réalisations</a>
                
                <a href="{{ route('blog.index') }}" class="site-nav-link font-medium">Blog et Astuces</a>
                
                <a href="{{ route('contact') }}" class="site-nav-link font-medium">Contact</a>
                
                <!-- Social Media Icons Mobile -->
                @if(count($activeSocialNetworks) > 0)
                <div class="pt-4 border-t" style="border-color: var(--header-border);">
                    <div class="site-nav-link font-medium mb-3">Suivez-nous</div>
                    <div class="flex space-x-4">
                        @foreach($activeSocialNetworks as $key => $network)
                            <a href="{{ setting($key) }}" target="_blank" rel="noopener noreferrer" 
                               class="site-nav-muted {{ $network['color'] }} transition-colors text-xl">
                                <i class="{{ $network['icon'] }}"></i>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <div class="pt-4 border-t space-y-2" style="border-color: var(--header-border);">
                    <a href="{{ route('form.step', 'propertyType') }}" 
                       class="block text-white px-4 py-2 rounded-lg text-center transition-colors font-medium button-mobile"
                       style="background-color: var(--primary-color);"
                       onmouseover="this.style.backgroundColor='var(--secondary-color)'"
                       onmouseout="this.style.backgroundColor='var(--primary-color)'"
                       onclick="trackFormClick('{{ request()->url() }}')">
                        <i class="fas fa-calculator mr-2"></i>Simulateur de Prix
                    </a>
                    <a href="tel:{{ setting('company_phone') }}" 
                       class="block text-white px-4 py-2 rounded-lg text-center transition-colors font-medium button-mobile"
                       style="background-color: var(--primary-color);"
                       onmouseover="this.style.backgroundColor='var(--secondary-color)'"
                       onmouseout="this.style.backgroundColor='var(--primary-color)'">
                        <i class="fas fa-phone mr-2"></i>Appelez-nous
                    </a>
                </div>
            </nav>
        </div>
    </div>
</header>

<script>
function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    const button = document.getElementById('mobileMenuButton');
    const isHidden = menu.classList.contains('hidden');
    
    menu.classList.toggle('hidden');
    if (button) {
        button.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
        button.setAttribute('aria-label', isHidden ? 'Fermer le menu de navigation' : 'Ouvrir le menu de navigation');
    }
}

// trackPhoneCall est géré automatiquement par phone-tracking.js dans layouts/app.blade.php
// Plus besoin de fonction locale, le script global s'occupe de tout

function trackFormClick(page) {
    fetch('/api/track-form-click', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    }).catch(error => console.log('Tracking error:', error));
}
</script>







