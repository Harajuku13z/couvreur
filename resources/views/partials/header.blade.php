<!-- Header -->
<header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
            <div class="flex items-center">
                <a href="{{ route('home') }}" class="flex items-center">
                    @if(setting('company_logo'))
                        <img src="{{ asset(setting('company_logo')) }}" alt="{{ setting('company_name') }}" class="h-10 w-auto">
                    @else
                        <span class="text-2xl font-bold" style="color: var(--primary-color);">
                            {{ setting('company_name', 'Votre Entreprise') }}
                        </span>
                    @endif
                </a>
            </div>
            
            <!-- Desktop Navigation -->
            <nav class="hidden md:flex items-center space-x-8">
                <a href="{{ route('home') }}" class="text-gray-700 hover:text-primary font-medium">Accueil</a>
                
                @php
                    $servicesData = \App\Models\Setting::get('services', '[]');
                    $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
                    
                    // S'assurer que $services est toujours un tableau
                    if (!is_array($services)) {
                        $services = [];
                    }
                    
                    $featuredServices = array_filter($services, function($service) {
                        return is_array($service) && (($service['is_featured'] ?? false) || ($service['is_menu'] ?? false));
                    });
                @endphp
                
                @if(count($featuredServices) > 0)
                <div class="relative group">
                    <button class="text-gray-700 hover:text-primary font-medium flex items-center">
                        Nos Services
                        <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </button>
                    <div class="absolute top-full left-0 mt-2 w-48 bg-white rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <div class="py-2">
                            @foreach($featuredServices as $service)
                                @if(is_array($service) && isset($service['name']) && isset($service['slug']))
                                <a href="{{ route('services.show', $service['slug']) }}" 
                                   class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-primary transition-colors">
                                    {{ $service['name'] }}
                                </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
                
                <a href="{{ route('portfolio.index') }}" class="text-gray-700 hover:text-primary font-medium">Nos Réalisations</a>
                
                <a href="{{ route('blog.index') }}" class="text-gray-700 hover:text-primary font-medium">Blog et Astuces</a>
                
            </nav>
            
            <!-- CTA Buttons -->
            <div class="hidden md:flex items-center space-x-4">
                <a href="{{ route('form.step', 'propertyType') }}" 
                   class="text-white px-4 py-2 rounded-lg transition-colors font-medium"
                   style="background-color: var(--primary-color);"
                   onmouseover="this.style.backgroundColor='var(--secondary-color)'"
                   onmouseout="this.style.backgroundColor='var(--primary-color)'"
                   onclick="trackFormClick('{{ request()->url() }}')">
                    <i class="fas fa-calculator mr-2"></i>Devis Gratuit
                </a>
                <a href="tel:{{ setting('company_phone') }}" 
                   class="text-white px-4 py-2 rounded-lg transition-colors font-medium"
                   style="background-color: var(--accent-color);"
                   onmouseover="this.style.backgroundColor='var(--primary-color)'"
                   onmouseout="this.style.backgroundColor='var(--accent-color)'">
                    <i class="fas fa-phone mr-2"></i>Appelez-nous
                </a>
            </div>
            
            <!-- Mobile Menu Button -->
            <button class="md:hidden text-gray-700 hover:text-primary" onclick="toggleMobileMenu()">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>
        
        <!-- Mobile Navigation -->
        <div id="mobileMenu" class="md:hidden hidden border-t border-gray-200 py-4">
            <nav class="flex flex-col space-y-4">
                <a href="{{ route('home') }}" class="text-gray-700 hover:text-primary font-medium">Accueil</a>
                
                @if(count($featuredServices) > 0)
                <div class="space-y-2">
                    <div class="text-gray-700 font-medium">Nos Services</div>
                    @foreach($featuredServices as $service)
                    <a href="{{ route('services.show', $service['slug']) }}" 
                       class="block pl-4 text-gray-600 hover:text-primary">
                        {{ $service['name'] }}
                    </a>
                    @endforeach
                </div>
                @endif
                
                <a href="{{ route('portfolio.index') }}" class="text-gray-700 hover:text-primary font-medium">Nos Réalisations</a>
                
                <a href="{{ route('blog.index') }}" class="text-gray-700 hover:text-primary font-medium">Blog et Astuces</a>
                
                
                <div class="pt-4 border-t border-gray-200 space-y-2">
                    <a href="{{ route('form.step', 'propertyType') }}" 
                       class="block text-white px-4 py-2 rounded-lg text-center transition-colors font-medium"
                       style="background-color: var(--primary-color);"
                       onmouseover="this.style.backgroundColor='var(--secondary-color)'"
                       onmouseout="this.style.backgroundColor='var(--primary-color)'"
                       onclick="trackFormClick('{{ request()->url() }}')">
                        <i class="fas fa-calculator mr-2"></i>Devis Gratuit
                    </a>
                    <a href="tel:{{ setting('company_phone') }}" 
                       class="block text-white px-4 py-2 rounded-lg text-center transition-colors font-medium"
                       style="background-color: var(--accent-color);"
                       onmouseover="this.style.backgroundColor='var(--primary-color)'"
                       onmouseout="this.style.backgroundColor='var(--accent-color)'">
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







