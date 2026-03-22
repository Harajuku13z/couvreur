<!-- Footer -->
<footer class="site-footer py-16">
    <div class="site-shell">
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Company Info -->
            <div class="lg:col-span-2">
                <div class="mb-6">
                    {{-- h2 : premier titre de section du pied de page (hiérarchie après le h1 de la page) --}}
                    <h2 class="text-2xl font-bold" style="color: var(--footer-text);">{{ setting('company_name', 'Votre Entreprise') }}</h2>
                </div>
                
                <p class="site-footer-link mb-6 text-sm leading-relaxed max-w-md">
                    @php
                        try {
                            $description = setting('company_description', '');
                            if (empty($description)) {
                                $description = 'Expert en travaux de rénovation et de couverture. Devis gratuit, qualité garantie. Nous intervenons rapidement pour tous vos projets de toiture, façade et isolation.';
                            }
                        } catch (Exception $e) {
                            $description = 'Expert en travaux de rénovation et de couverture. Devis gratuit, qualité garantie. Nous intervenons rapidement pour tous vos projets de toiture, façade et isolation.';
                        }
                    @endphp
                    {{ $description }}
                </p>
                
                <div class="flex flex-col space-y-4">
                    @php
                        $phoneMain = setting('company_phone', '');
                        $phone2 = setting('company_phone_2', '');
                        $phone3 = setting('company_phone_3', '');
                    @endphp
                    @if($phoneMain)
                    <div class="flex space-x-4">
                        <a href="tel:{{ setting('company_phone_raw', $phoneMain) }}" class="site-footer-link">
                            <i class="fas fa-phone mr-2"></i>
                            {{ $phoneMain }}
                        </a>
                    </div>
                    @endif
                    @if($phone2)
                    <div class="flex space-x-4 text-sm">
                        <a href="tel:{{ setting('company_phone_2_raw', $phone2) }}" class="site-footer-link">
                            <i class="fas fa-phone mr-2"></i>
                            {{ $phone2 }}
                        </a>
                    </div>
                    @endif
                    @if($phone3)
                    <div class="flex space-x-4 text-sm">
                        <a href="tel:{{ setting('company_phone_3_raw', $phone3) }}" class="site-footer-link">
                            <i class="fas fa-phone mr-2"></i>
                            {{ $phone3 }}
                        </a>
                    </div>
                    @endif
                    
                    <!-- Address -->
                    @php
                        $address = setting('company_address', '');
                        $city = setting('company_city', '');
                        $postalCode = setting('company_postal_code', '');
                        $country = setting('company_country', 'France');
                        
                        $fullAddress = [];
                        if ($address) $fullAddress[] = $address;
                        if ($postalCode && $city) {
                            $fullAddress[] = $postalCode . ' ' . $city;
                        } elseif ($city) {
                            $fullAddress[] = $city;
                        } elseif ($postalCode) {
                            $fullAddress[] = $postalCode;
                        }
                        if ($country) $fullAddress[] = $country;
                        
                        $fullAddressString = implode(', ', $fullAddress);
                        $hours = setting('company_hours', '');
                    @endphp
                    @if($fullAddressString)
                    <div class="flex items-start space-x-4">
                        <i class="fas fa-map-marker-alt mt-1 site-footer-link"></i>
                        <div class="site-footer-link">
                            {{ $fullAddressString }}
                        </div>
                    </div>
                    @endif
                    @if(!empty($hours))
                    <div class="flex items-start space-x-4">
                        <i class="far fa-clock mt-1 site-footer-link"></i>
                        <div class="site-footer-link text-sm">
                            {{ $hours }}
                        </div>
                    </div>
                    @endif
                    
                    <!-- Social Media Icons -->
                    @php
                        $socialNetworks = [
                            'facebook_url' => ['icon' => 'fab fa-facebook', 'color' => 'hover:text-blue-400', 'label' => 'Suivez-nous sur Facebook'],
                            'instagram_url' => ['icon' => 'fab fa-instagram', 'color' => 'hover:text-pink-400', 'label' => 'Suivez-nous sur Instagram'],
                            'twitter_url' => ['icon' => 'fab fa-twitter', 'color' => 'hover:text-blue-300', 'label' => 'Suivez-nous sur Twitter'],
                            'linkedin_url' => ['icon' => 'fab fa-linkedin', 'color' => 'hover:text-blue-500', 'label' => 'Suivez-nous sur LinkedIn'],
                            'youtube_url' => ['icon' => 'fab fa-youtube', 'color' => 'hover:text-red-400', 'label' => 'Suivez-nous sur YouTube'],
                            'tiktok_url' => ['icon' => 'fab fa-tiktok', 'color' => 'hover:text-gray-300', 'label' => 'Suivez-nous sur TikTok'],
                            'pinterest_url' => ['icon' => 'fab fa-pinterest', 'color' => 'hover:text-red-500', 'label' => 'Suivez-nous sur Pinterest'],
                            'snapchat_url' => ['icon' => 'fab fa-snapchat', 'color' => 'hover:text-yellow-400', 'label' => 'Suivez-nous sur Snapchat'],
                            'whatsapp_url' => ['icon' => 'fab fa-whatsapp', 'color' => 'hover:text-green-400', 'label' => 'Contactez-nous sur WhatsApp'],
                            'telegram_url' => ['icon' => 'fab fa-telegram', 'color' => 'hover:text-blue-400', 'label' => 'Contactez-nous sur Telegram'],
                        ];
                        
                        $activeSocialNetworks = array_filter($socialNetworks, function($key) {
                            return !empty(setting($key));
                        }, ARRAY_FILTER_USE_KEY);
                    @endphp
                    
                    @if(count($activeSocialNetworks) > 0)
                    <div class="flex space-x-4">
                        @foreach($activeSocialNetworks as $key => $network)
                            <a href="{{ setting($key) }}" target="_blank" rel="noopener noreferrer" 
                               class="site-footer-link {{ $network['color'] }} transition-colors text-xl"
                               aria-label="{{ $network['label'] }}">
                                <i class="{{ $network['icon'] }}" aria-hidden="true"></i>
                            </a>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Quick Links -->
            <div>
                <h3 class="text-lg font-semibold mb-4" style="color: var(--footer-text);">Liens Rapides</h3>
                <ul class="space-y-2">
                    <li><a href="{{ url('/') }}" class="site-footer-link">Accueil</a></li>
                    <li><a href="{{ route('services.index') }}" class="site-footer-link">Nos Services</a></li>
                    <li><a href="{{ route('portfolio.index') }}" class="site-footer-link">Nos Réalisations</a></li>
                    <li><a href="{{ route('blog.index') }}" class="site-footer-link">Blog et Astuces</a></li>
                    <li><a href="{{ route('ads.index') }}" class="site-footer-link">Nos Annonces</a></li>
                    <li><a href="{{ route('reviews.all') }}" class="site-footer-link">Nos Avis Clients</a></li>
                    <li><a href="{{ route('form.step', 'propertyType') }}" class="site-footer-link">Devis Gratuit</a></li>
                </ul>
            </div>
            
            <!-- Services -->
            <div>
                <h3 class="text-lg font-semibold mb-4" style="color: var(--footer-text);">Nos Services</h3>
                <ul class="space-y-2">
                    @php
                        $servicesData = \App\Models\Setting::get('services', '[]');
                        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
                        
                        // S'assurer que $services est toujours un tableau
                        if (!is_array($services)) {
                            $services = [];
                        }
                        
                        $visibleServices = array_filter($services, function($service) {
                            return is_array($service) && ($service['is_visible'] ?? true) && ($service['is_featured'] ?? false);
                        });
                    @endphp
                    
                    @if(count($visibleServices) > 0)
                        @foreach(array_slice($visibleServices, 0, 4) as $service)
                            @if(is_array($service) && isset($service['name']) && isset($service['slug']))
                            <li>
                                <a href="{{ route('services.show', $service['slug']) }}" class="site-footer-link">
                                    {{ $service['name'] }}
                                </a>
                            </li>
                            @endif
                        @endforeach
                    @else
                        <li><a href="{{ route('services.index') }}" class="site-footer-link">Voir tous nos services</a></li>
                    @endif
                    <li><a href="{{ route('contact') }}" class="site-footer-link">Contact</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Bottom Bar -->
        <div class="border-t site-footer-border mt-12 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="site-footer-link text-sm">
                    © {{ date('Y') }} {{ setting('company_name', 'Votre Entreprise') }}. Tous droits réservés.
                </div>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="{{ route('legal.mentions') }}" class="site-footer-link text-sm">Mentions Légales</a>
                    <a href="{{ route('legal.privacy') }}" class="site-footer-link text-sm">Politique de Confidentialité</a>
                    <a href="{{ route('legal.cgv') }}" class="site-footer-link text-sm">CGV</a>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t site-footer-border">
                <div class="text-center">
                    <p class="site-footer-link text-xs">
                        Ce site a été créé par <a href="https://www.osmoseconsulting.fr" target="_blank" class="site-footer-link font-medium">Osmose*</a> avec amour ❤️
                    </p>
                </div>
            </div>
        </div>
    </div>
</footer>









