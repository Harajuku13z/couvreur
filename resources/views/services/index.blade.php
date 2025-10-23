<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos Services - {{ setting('company_name', 'Votre Entreprise') }}</title>
    <meta name="description" content="Découvrez tous nos services de {{ setting('company_specialization', 'travaux de rénovation') }}. Solutions complètes et professionnelles pour tous vos projets.">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        :root {
            --primary-color: {{ setting('primary_color', '#3b82f6') }};
            --secondary-color: {{ setting('secondary_color', '#1e40af') }};
            --accent-color: {{ setting('accent_color', '#f59e0b') }};
        }
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .bg-primary {
            background-color: var(--primary-color);
        }
        
        .bg-secondary {
            background-color: var(--secondary-color);
        }
        
        .bg-accent {
            background-color: var(--accent-color);
        }
        
        .text-primary {
            color: var(--primary-color);
        }
        
        .text-secondary {
            color: var(--secondary-color);
        }
        
        .text-accent {
            color: var(--accent-color);
        }
        
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900">
    @include('partials.header')

    <!-- Hero Section -->
    <section class="py-20 bg-primary">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-6xl font-black text-white mb-6">Nos Services</h1>
            <p class="text-xl text-gray-200 max-w-3xl mx-auto">
                Des solutions complètes et professionnelles pour tous vos projets de {{ setting('company_specialization', 'travaux de rénovation') }}
            </p>
        </div>
    </section>

    <!-- Services Grid -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($visibleServices->count() > 0)
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($visibleServices as $service)
                    <div class="group bg-white rounded-2xl shadow-lg overflow-hidden card-hover">
                        @if(!empty($service['featured_image']))
                        <div class="relative h-48 overflow-hidden">
                            <img src="{{ asset($service['featured_image']) }}" 
                                 alt="{{ $service['name'] }}" 
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                        </div>
                        @else
                        <div class="h-48 bg-primary flex items-center justify-center">
                            <i class="fas {{ $service['icon'] ?? 'fa-tools' }} text-white text-4xl"></i>
                        </div>
                        @endif
                        
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-3">{{ $service['name'] }}</h3>
                            <p class="text-gray-600 mb-4">{{ $service['short_description'] ?? $service['description'] }}</p>
                            
                            <div class="flex items-center justify-between">
                                <a href="{{ route('services.show', $service['slug'] ?? Str::slug($service['name'])) }}" 
                                   class="inline-flex items-center text-sm font-semibold text-primary hover:text-secondary transition-colors">
                                    En savoir plus
                                    <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                                </a>
                                
                                @if(isset($service['is_featured']) && $service['is_featured'])
                                <span class="px-3 py-1 rounded-full text-xs font-semibold text-white bg-accent">
                                    <i class="fas fa-star mr-1"></i>Vedette
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-20">
                    <div class="w-24 h-24 bg-primary rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-tools text-white text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Aucun service disponible</h3>
                    <p class="text-gray-600 mb-8">Nos services sont en cours de mise à jour. Revenez bientôt !</p>
                    <a href="{{ route('home') }}" 
                       class="inline-flex items-center px-6 py-3 rounded-full text-lg font-semibold text-white bg-primary hover:bg-secondary transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-home mr-2"></i>
                        Retour à l'accueil
                    </a>
                </div>
            @endif
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-primary">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-black text-white mb-6">
                Besoin d'un Devis Personnalisé ?
            </h2>
            <p class="text-xl text-gray-200 mb-8 max-w-2xl mx-auto">
                Contactez-nous pour obtenir un devis gratuit et adapté à vos besoins
            </p>
            
            <div class="flex flex-col sm:flex-row gap-6 justify-center items-center">
                <a href="{{ route('form.step', 'propertyType') }}" 
                   class="bg-white text-gray-900 px-8 py-4 rounded-full text-lg font-bold hover:bg-gray-100 transition-all duration-300 transform hover:scale-105 shadow-2xl">
                    <i class="fas fa-calculator mr-3"></i>
                    Devis Gratuit
                </a>
                
                <a href="tel:{{ setting('company_phone') }}" 
                   class="glass-effect text-white px-8 py-4 rounded-full text-lg font-bold hover:bg-white hover:bg-opacity-20 transition-all duration-300 transform hover:scale-105">
                    <i class="fas fa-phone mr-3"></i>
                    {{ setting('company_phone') }}
                </a>
            </div>
        </div>
    </section>

    @include('partials.footer')

    <style>
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</body>
</html>








