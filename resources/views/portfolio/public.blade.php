<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio - {{ setting('company_name', 'Rénovation Expert') }}</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: {{ setting('primary_color', '#3b82f6') }};
            --secondary-color: {{ setting('secondary_color', '#10b981') }};
            --accent-color: {{ setting('accent_color', '#f59e0b') }};
        }
        
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
</head>
<body class="bg-gray-50">
    @include('partials.header')

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Nos Réalisations</h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Découvrez nos projets de rénovation et nos réalisations pour vous inspirer
            </p>
        </div>

        @if(count($visibleItems) > 0)
            <!-- Portfolio Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 portfolio-grid-mobile">
                @foreach($visibleItems as $item)
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                        <!-- Images Carousel -->
                        <div class="relative">
                            @if(isset($item['images']) && count($item['images']) > 0)
                                <div class="aspect-w-16 aspect-h-9">
                                    <img src="{{ url($item['images'][0]) }}" 
                                         alt="{{ $item['title'] }}" 
                                         class="w-full h-64 object-cover mobile-responsive-img"
                                         style="max-width: 100%; height: auto; display: block;"
                                         loading="lazy">
                                </div>
                                @if(count($item['images']) > 1)
                                    <div class="absolute top-4 right-4 bg-black bg-opacity-50 text-white px-2 py-1 rounded-full text-sm">
                                        <i class="fas fa-images mr-1"></i>{{ count($item['images']) }}
                                    </div>
                                @endif
                            @else
                                <div class="w-full h-64 bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-image text-gray-400 text-4xl"></i>
                                </div>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-xl font-semibold text-gray-900">{{ $item['title'] }}</h3>
                                <span class="px-3 py-1 rounded-full text-sm font-medium
                                    @if($item['work_type'] === 'roof') bg-green-100 text-green-800
                                    @elseif($item['work_type'] === 'facade') bg-yellow-100 text-yellow-800
                                    @elseif($item['work_type'] === 'isolation') bg-purple-100 text-purple-800
                                    @else bg-blue-100 text-blue-800
                                    @endif">
                                    @if($item['work_type'] === 'roof') Toiture
                                    @elseif($item['work_type'] === 'facade') Façade
                                    @elseif($item['work_type'] === 'isolation') Isolation
                                    @else {{ ucfirst($item['work_type']) }}
                                    @endif
                                </span>
                            </div>

                            @if(isset($item['description']) && !empty($item['description']))
                                <p class="text-gray-600 mb-4 line-clamp-3">{{ $item['description'] }}</p>
                            @endif

                            <!-- Action Button -->
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">
                                    <i class="fas fa-calendar mr-1"></i>
                                    {{ isset($item['created_at']) ? \Carbon\Carbon::parse($item['created_at'])->format('M Y') : 'Récent' }}
                                </span>
                                <a href="{{ route('portfolio.show', $item['id'] ?? 0) }}" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary text-sm">
                                    <i class="fas fa-eye mr-1"></i>Voir le projet
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- No Items -->
            <div class="text-center py-12">
                <div class="max-w-md mx-auto">
                    <i class="fas fa-images text-gray-400 text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Aucune réalisation disponible</h3>
                    <p class="text-gray-600 mb-6">
                        Nos réalisations seront bientôt disponibles. En attendant, demandez votre devis gratuit !
                    </p>
                    <a href="{{ route('form.step', 'propertyType') }}" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-secondary">
                        <i class="fas fa-calculator mr-2"></i>Demander un Devis
                    </a>
                </div>
            </div>
        @endif

        <!-- CTA Section -->
        <div class="mt-16 bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-8 text-center text-white">
            <h2 class="text-3xl font-bold mb-4">Prêt à transformer votre projet ?</h2>
            <p class="text-xl mb-6 opacity-90">
                Obtenez votre devis gratuit en quelques minutes
            </p>
            <a href="{{ route('form.step', 'propertyType') }}" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                <i class="fas fa-calculator mr-2"></i>Commencer mon Devis
            </a>
        </div>
    </main>

    <!-- Footer -->
    @include('partials.footer')
</body>
</html>








