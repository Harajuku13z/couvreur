<!-- Footer -->
<footer class="bg-gray-900 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Company Info -->
            <div class="lg:col-span-2">
                <div class="mb-6">
                    <h3 class="text-2xl font-bold">{{ setting('company_name', 'Votre Entreprise') }}</h3>
                </div>
                
                <p class="text-gray-400 mb-6 text-sm leading-relaxed max-w-md">
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
                
                <div class="flex space-x-4">
                    <a href="tel:{{ setting('company_phone') }}" class="text-gray-400 hover:text-white transition-colors">
                        <i class="fas fa-phone mr-2"></i>
                        {{ setting('company_phone', '01 23 45 67 89') }}
                    </a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div>
                <h4 class="text-lg font-semibold mb-4">Liens Rapides</h4>
                <ul class="space-y-2">
                    <li><a href="{{ route('home') }}" class="text-gray-400 hover:text-white transition-colors">Accueil</a></li>
                    <li><a href="{{ route('services.index') }}" class="text-gray-400 hover:text-white transition-colors">Nos Services</a></li>
                    <li><a href="{{ route('portfolio.index') }}" class="text-gray-400 hover:text-white transition-colors">Nos Réalisations</a></li>
                    <li><a href="{{ route('blog.index') }}" class="text-gray-400 hover:text-white transition-colors">Blog et Astuces</a></li>
                    <li><a href="{{ route('ads.index') }}" class="text-gray-400 hover:text-white transition-colors">Nos Annonces</a></li>
                    <li><a href="{{ route('reviews.all') }}" class="text-gray-400 hover:text-white transition-colors">Nos Avis Clients</a></li>
                    <li><a href="{{ route('form.step', 'propertyType') }}" class="text-gray-400 hover:text-white transition-colors">Devis Gratuit</a></li>
                </ul>
            </div>
            
            <!-- Services -->
            <div>
                <h4 class="text-lg font-semibold mb-4">Nos Services</h4>
                <ul class="space-y-2">
                    @php
                        $servicesData = \App\Models\Setting::get('services', '[]');
                        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
                        
                        // S'assurer que $services est toujours un tableau
                        if (!is_array($services)) {
                            $services = [];
                        }
                        
                        $visibleServices = array_filter($services, function($service) {
                            return is_array($service) && (($service['is_active'] ?? true) && ($service['is_featured'] ?? false));
                        });
                    @endphp
                    
                    @if(count($visibleServices) > 0)
                        @foreach(array_slice($visibleServices, 0, 4) as $service)
                            @if(is_array($service) && isset($service['name']) && isset($service['slug']))
                            <li>
                                <a href="{{ route('services.show', $service['slug']) }}" class="text-gray-400 hover:text-white transition-colors">
                                    {{ $service['name'] }}
                                </a>
                            </li>
                            @endif
                        @endforeach
                    @else
                        <li><a href="{{ route('services.index') }}" class="text-gray-400 hover:text-white transition-colors">Voir tous nos services</a></li>
                    @endif
                </ul>
            </div>
        </div>
        
        <!-- Bottom Bar -->
        <div class="border-t border-gray-800 mt-12 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-gray-400 text-sm">
                    © {{ date('Y') }} {{ setting('company_name', 'Votre Entreprise') }}. Tous droits réservés.
                </div>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="{{ route('legal.mentions') }}" class="text-gray-400 hover:text-white transition-colors text-sm">Mentions Légales</a>
                    <a href="{{ route('legal.privacy') }}" class="text-gray-400 hover:text-white transition-colors text-sm">Politique de Confidentialité</a>
                    <a href="{{ route('legal.cgv') }}" class="text-gray-400 hover:text-white transition-colors text-sm">CGV</a>
                </div>
            </div>
        </div>
    </div>
</footer>









