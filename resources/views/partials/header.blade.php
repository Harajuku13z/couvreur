<!-- Header -->
<header class="bg-white shadow-sm">
<style>
    /* Styles spécifiques pour mobile */
    @media (max-width: 768px) {
        .header-mobile {
            padding: 0.75rem 0 !important;
        }
        
        .logo-mobile {
            height: 2.5rem !important;
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
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-2 header-mobile">
            <div class="flex items-center">
                <a href="{{ route('home') }}" class="flex items-center">
                    @if(setting('company_logo'))
                        <img src="{{ asset(setting('company_logo')) }}" alt="{{ setting('company_name') }}" class="h-10 w-auto logo-mobile">
                    @else
                        <span class="text-2xl font-bold text-mobile" style="color: var(--primary-color);">
                            {{ setting('company_name', 'Votre Entreprise') }}
                        </span>
                    @endif
                </a>
            </div>
            
            <!-- Desktop Navigation -->
            <nav class="hidden md:flex items-center space-x-8">
                <a href="{{ route('home') }}" class="text-gray-700 hover:text-primary font-medium">Accueil</a>
                
                
                <!-- Lien direct vers la page Nos Services -->
                <a href="{{ route('services.index') }}" class="text-gray-700 hover:text-primary font-medium">Nos Services</a>
                
                <a href="{{ route('portfolio.index') }}" class="text-gray-700 hover:text-primary font-medium">Nos Réalisations</a>
                
                <a href="{{ route('blog.index') }}" class="text-gray-700 hover:text-primary font-medium">Blog et Astuces</a>
                
            </nav>
            
            <!-- Social Media Icons & CTA Buttons -->
            <div class="hidden md:flex items-center space-x-4">
                <!-- Social Media Icons -->
                @php
                    $socialNetworks = [
                        'facebook_url' => ['icon' => 'fab fa-facebook', 'color' => 'hover:text-blue-600'],
                        'instagram_url' => ['icon' => 'fab fa-instagram', 'color' => 'hover:text-pink-600'],
                        'twitter_url' => ['icon' => 'fab fa-twitter', 'color' => 'hover:text-blue-400'],
                        'linkedin_url' => ['icon' => 'fab fa-linkedin', 'color' => 'hover:text-blue-700'],
                        'youtube_url' => ['icon' => 'fab fa-youtube', 'color' => 'hover:text-red-600'],
                        'tiktok_url' => ['icon' => 'fab fa-tiktok', 'color' => 'hover:text-gray-800'],
                        'pinterest_url' => ['icon' => 'fab fa-pinterest', 'color' => 'hover:text-red-700'],
                        'snapchat_url' => ['icon' => 'fab fa-snapchat', 'color' => 'hover:text-yellow-500'],
                        'whatsapp_url' => ['icon' => 'fab fa-whatsapp', 'color' => 'hover:text-green-600'],
                        'telegram_url' => ['icon' => 'fab fa-telegram', 'color' => 'hover:text-blue-500'],
                    ];
                    
                    $activeSocialNetworks = array_filter($socialNetworks, function($key) {
                        return !empty(setting($key));
                    }, ARRAY_FILTER_USE_KEY);
                @endphp
                
                @if(count($activeSocialNetworks) > 0)
                <div class="flex space-x-3 mr-4">
                    @foreach(array_slice($activeSocialNetworks, 0, 4) as $key => $network)
                        <a href="{{ setting($key) }}" target="_blank" rel="noopener noreferrer" 
                           class="text-gray-600 {{ $network['color'] }} transition-colors text-lg">
                            <i class="{{ $network['icon'] }}"></i>
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
            <button class="md:hidden text-gray-700 hover:text-primary" onclick="toggleMobileMenu()">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>
        
        <!-- Mobile Navigation -->
        <div id="mobileMenu" class="md:hidden hidden border-t border-gray-200 py-4 max-h-screen overflow-y-auto">
            <nav class="flex flex-col space-y-4 px-4">
                <a href="{{ route('home') }}" class="text-gray-700 hover:text-primary font-medium">Accueil</a>
                
                <!-- Lien direct vers la page Nos Services -->
                <a href="{{ route('services.index') }}" class="text-gray-700 hover:text-primary font-medium">Nos Services</a>
                
                <a href="{{ route('portfolio.index') }}" class="text-gray-700 hover:text-primary font-medium">Nos Réalisations</a>
                
                <a href="{{ route('blog.index') }}" class="text-gray-700 hover:text-primary font-medium">Blog et Astuces</a>
                
                <!-- Social Media Icons Mobile -->
                @if(count($activeSocialNetworks) > 0)
                <div class="pt-4 border-t border-gray-200">
                    <div class="text-gray-700 font-medium mb-3">Suivez-nous</div>
                    <div class="flex space-x-4">
                        @foreach($activeSocialNetworks as $key => $network)
                            <a href="{{ setting($key) }}" target="_blank" rel="noopener noreferrer" 
                               class="text-gray-600 {{ $network['color'] }} transition-colors text-xl">
                                <i class="{{ $network['icon'] }}"></i>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <div class="pt-4 border-t border-gray-200 space-y-2">
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
    menu.classList.toggle('hidden');
}

function trackPhoneCall(phone, page) {
    fetch('/api/track-phone-call', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    }).catch(error => console.log('Tracking error:', error));
}

function trackFormClick(page) {
    fetch('/api/track-form-click', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    }).catch(error => console.log('Tracking error:', error));
}
</script>







