@extends('layouts.app')

@section('title', 'Nos Réalisations - ' . setting('company_name', 'Votre Entreprise'))

@push('head')
<style>
    /* Styles spécifiques pour mobile */
    @media (max-width: 768px) {
        /* Images responsive */
        .mobile-responsive-img {
            max-width: 100%;
            height: auto;
            display: block;
            object-fit: cover;
        }
        
        /* Portfolio grid mobile */
        .portfolio-grid-mobile {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <section class="py-20 bg-gradient-to-br from-blue-600 to-blue-800 text-white">
        <div class="container mx-auto px-4">
            <div class="text-center">
                <h1 class="text-5xl font-bold mb-6">Nos Réalisations</h1>
                <p class="text-xl max-w-3xl mx-auto leading-relaxed text-blue-100">
                    Découvrez quelques-unes de nos réalisations récentes et laissez-vous inspirer pour votre prochain projet
                </p>
            </div>
        </div>
    </section>

    <!-- Filtres -->
    @if(!empty($serviceTypes) && count($serviceTypes) > 1)
    <section class="py-8 bg-white border-b">
        <div class="container mx-auto px-4">
            <div class="flex flex-wrap justify-center gap-4">
                <button class="filter-btn active px-6 py-3 rounded-full bg-blue-600 text-white font-medium transition-all duration-300 shadow-lg" data-filter="all">
                    Tous les projets
                </button>
                @foreach($serviceTypes as $serviceType)
                <button class="filter-btn px-6 py-3 rounded-full bg-gray-100 text-gray-700 hover:bg-blue-600 hover:text-white font-medium transition-all duration-300 shadow-sm" data-filter="{{ Str::slug($serviceType) }}">
                    {{ $serviceType }}
                </button>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Grille des réalisations -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            @if($visiblePortfolio->count() > 0)
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 portfolio-grid-mobile" id="portfolio-grid">
                @foreach($visiblePortfolio as $item)
                <div class="portfolio-item bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 cursor-pointer" 
                     data-category="{{ Str::slug($item['work_type'] ?? 'autre') }}"
                     onclick="window.location.href='{{ route('portfolio.show', $item['slug'] ?? \Illuminate\Support\Str::slug($item['title'] ?? 'realisation')) }}'">
                    <!-- Image principale -->
                    <div class="relative h-64 overflow-hidden">
                        @if(!empty($item['images']))
                            @php 
                                $firstImage = is_array($item['images']) ? $item['images'][0] : $item['images'];
                            @endphp
                            <img src="{{ url($firstImage) }}" 
                                 alt="{{ $item['title'] }}" 
                                 class="w-full h-full object-cover transition-transform duration-300 hover:scale-105 mobile-responsive-img"
                                 style="max-width: 100%; height: auto; display: block;"
                                 loading="lazy">
                        @else
                            <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                <i class="fas fa-image text-gray-400 text-4xl"></i>
                            </div>
                        @endif
                        
                        <!-- Overlay avec bouton -->
                        <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-40 transition-all duration-300 flex items-center justify-center">
                            <div class="opacity-0 hover:opacity-100 transition-opacity duration-300">
                                <button class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold shadow-lg hover:bg-blue-50 transition-colors">
                                    <i class="fas fa-eye mr-2"></i>
                                    Voir le projet
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contenu -->
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $item['title'] }}</h3>
                        @if(isset($item['description']) && $item['description'])
                        <p class="text-gray-600 text-sm mb-4">{{ Str::limit($item['description'], 100) }}</p>
                        @endif
                        
                        <!-- Informations du projet -->
                        <div class="flex items-center justify-between mb-4">
                            <!-- Type de service -->
                            @if(isset($item['service_type']))
                            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-3 py-1 rounded-full font-medium">
                                {{ $item['service_type'] }}
                            </span>
                            @endif
                            
                            <!-- Nombre de photos -->
                            @if(isset($item['images']) && is_array($item['images']))
                            <div class="flex items-center text-gray-500 text-sm">
                                <i class="fas fa-images mr-1"></i>
                                <span>{{ count($item['images']) }} photo{{ count($item['images']) > 1 ? 's' : '' }}</span>
                            </div>
                            @endif
                        </div>
                        
                        <!-- Bouton voir la réalisation complète -->
                        <div class="mt-4">
                            <a href="{{ route('portfolio.show', $item['slug'] ?? \Illuminate\Support\Str::slug($item['title'] ?? 'realisation')) }}" 
                               class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors text-center block">
                                <i class="fas fa-eye mr-2"></i>
                                Voir la réalisation complète
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-16">
                <div class="bg-white rounded-2xl shadow-lg p-12 max-w-md mx-auto">
                    <i class="fas fa-images text-gray-300 text-6xl mb-4"></i>
                    <h3 class="text-2xl font-bold text-gray-700 mb-4">Aucune réalisation disponible</h3>
                    <p class="text-gray-500 mb-6">Nos réalisations seront bientôt disponibles.</p>
                    <a href="{{ route('form.step', 'propertyType') }}" 
                       class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                        Demander un devis
                    </a>
                </div>
            </div>
            @endif
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-blue-600 to-blue-800 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl font-bold mb-6">Vous avez un projet similaire ?</h2>
            <p class="text-xl mb-8 max-w-3xl mx-auto text-blue-100">
                Contactez-nous pour discuter de vos besoins et obtenir un devis personnalisé pour votre projet
            </p>
            <div class="flex flex-col sm:flex-row gap-6 justify-center">
                <a href="{{ route('form.step', 'propertyType') }}" 
                   class="bg-yellow-500 hover:bg-yellow-600 text-white px-8 py-4 rounded-lg font-bold text-lg transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    <i class="fas fa-calculator mr-2"></i>
                    Demander un devis gratuit
                </a>
                <a href="tel:{{ setting('company_phone_raw') }}" 
                   class="bg-green-500 hover:bg-green-600 text-white px-8 py-4 rounded-lg font-bold text-lg transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    <i class="fas fa-phone mr-2"></i>
                    {{ setting('company_phone') }}
                </a>
            </div>
            
            <!-- Informations supplémentaires -->
            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
                <div class="text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-clock text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Réponse rapide</h3>
                    <p class="text-blue-100">Devis sous 24h</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-shield-alt text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Garantie décennale</h3>
                    <p class="text-blue-100">Assurance professionnelle</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-star text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Qualité garantie</h3>
                    <p class="text-blue-100">Artisans qualifiés</p>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- JavaScript pour les filtres -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const portfolioItems = document.querySelectorAll('.portfolio-item');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            // Mettre à jour les boutons actifs
            filterButtons.forEach(btn => {
                btn.classList.remove('active', 'bg-blue-600', 'text-white');
                btn.classList.add('bg-gray-100', 'text-gray-700');
            });
            this.classList.add('active', 'bg-blue-600', 'text-white');
            this.classList.remove('bg-gray-100', 'text-gray-700');
            
            // Filtrer les éléments
            portfolioItems.forEach(item => {
                if (filter === 'all' || item.getAttribute('data-category') === filter) {
                    item.style.display = 'block';
                    item.style.animation = 'fadeInUp 0.5s ease-out';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});

</script>

<style>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
@endsection





