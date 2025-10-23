@extends('layouts.app')

@section('title', setting('meta_title', company('name') . ' - Obtenez Votre Devis Gratuit'))

@section('content')

<!-- Header blanc fixe -->
<nav class="bg-white shadow-md fixed w-full top-0 z-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center h-20">
            <div class="flex items-center">
                @if(setting('company_logo'))
                    <img src="{{ asset(setting('company_logo')) }}" alt="{{ company('name') }}" class="h-14 object-contain">
                @else
                    <span class="text-2xl font-bold" style="color: var(--primary-color);">
                        {{ company('name', 'Rénovation Expert') }}
                    </span>
                @endif
            </div>
            <div class="hidden md:flex items-center space-x-8">
                <a href="#services" class="text-gray-700 hover:text-primary font-medium transition">Services</a>
                <a href="#realisations" class="text-gray-700 hover:text-primary font-medium transition">Réalisations</a>
                <a href="#avis" class="text-gray-700 hover:text-primary font-medium transition">Avis Clients</a>
                @if(company('phone'))
                <a href="tel:{{ company('phone_raw') }}" class="flex items-center text-gray-700 hover:text-primary font-semibold transition">
                    <i class="fas fa-phone mr-2"></i>
                    {{ company('phone') }}
                </a>
                @endif
            </div>
            <div>
                <a href="{{ route('form.step', 'propertyType') }}" class="btn-primary inline-block px-8 py-3 rounded-full font-bold text-white shadow-lg hover:shadow-xl transform hover:scale-105 transition duration-300">
                    <i class="fas fa-calculator mr-2"></i>
                    Devis Gratuit
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="h-20"></div>

<!-- Hero -->
<section class="relative overflow-hidden py-20 md:py-32" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 left-0 w-96 h-96 bg-white rounded-full -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-white rounded-full translate-x-1/2 translate-y-1/2"></div>
    </div>
    <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-6xl mx-auto grid md:grid-cols-2 gap-12 items-center">
            <div class="text-white">
                <div class="inline-flex items-center bg-white/20 backdrop-blur-sm px-6 py-3 rounded-full mb-8">
                    <i class="fas fa-check-circle text-green-300 mr-2"></i>
                    <span class="font-semibold">100% Gratuit & Sans Engagement</span>
                </div>
                <h1 class="text-5xl md:text-6xl font-black mb-6 leading-tight">
                    @if(company('slogan'))
                        {{ company('slogan') }}
                    @else
                        Rénovez Votre Habitat<br/>
                        <span class="text-yellow-300">Devis en 2 Minutes</span>
                    @endif
                </h1>
                <p class="text-xl md:text-2xl text-white/90 mb-10 leading-relaxed">
                    {{ company('description', "Expert en toiture, façade et isolation") }}
                </p>
                <a href="{{ route('form.step', 'propertyType') }}" class="inline-flex items-center bg-yellow-400 text-gray-900 px-12 py-6 rounded-full text-2xl font-black hover:bg-yellow-300 transition transform hover:scale-105 shadow-2xl">
                    <i class="fas fa-rocket mr-3 text-3xl"></i>
                    <span>OBTENIR MON DEVIS</span>
                </a>
                <div class="grid grid-cols-3 gap-6 mt-12 pt-12 border-t border-white/20">
                    <div class="text-center"><div class="text-4xl font-bold text-yellow-300">2 min</div><div class="text-sm text-white/80">Formulaire rapide</div></div>
                    <div class="text-center"><div class="text-4xl font-bold text-yellow-300">24h</div><div class="text-sm text-white/80">Réponse garantie</div></div>
                    <div class="text-center"><div class="text-4xl font-bold text-yellow-300">0€</div><div class="text-sm text-white/80">100% Gratuit</div></div>
                </div>
            </div>
            <div class="bg-white rounded-3xl shadow-2xl p-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">🏠 Votre Projet</h2>
                <p class="text-gray-600 mb-6">Commencez votre simulation pour obtenir un devis personnalisé et gratuit en quelques clics.</p>
                <ul class="space-y-3 mb-6 text-gray-700">
                    <li class="flex items-center"><i class="fas fa-check-circle text-green-500 mr-3"></i><span>Sans engagement</span></li>
                    <li class="flex items-center"><i class="fas fa-check-circle text-green-500 mr-3"></i><span>Réponse rapide</span></li>
                    <li class="flex items-center"><i class="fas fa-check-circle text-green-500 mr-3"></i><span>Totalement gratuit</span></li>
                </ul>
                <a href="{{ route('form.step', 'propertyType') }}" class="w-full flex items-center justify-center btn-primary text-white px-8 py-5 rounded-xl text-xl font-bold hover:shadow-xl transition">
                    Démarrer ma simulation
                    <i class="fas fa-arrow-right ml-3"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Réalisations -->
@if(setting('hero_image_1') || setting('hero_image_2') || setting('hero_image_3'))
<section id="realisations" class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">Nos Réalisations</h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">Découvrez quelques-uns de nos projets récents</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
            @foreach([1,2,3] as $i)
                @php($img = setting('hero_image_'.$i))
                @if($img)
                <div class="group relative overflow-hidden rounded-2xl shadow-lg hover:shadow-2xl transition duration-300">
                    <img src="{{ asset($img) }}" alt="Réalisation {{ $i }}" class="w-full h-80 object-cover group-hover:scale-110 transition duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition duration-300"></div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Services -->
<section id="services" class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">Nos Services</h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">Des solutions complètes pour tous vos travaux de rénovation</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
            <div class="bg-white rounded-2xl shadow-lg p-8 hover:shadow-2xl transition">
                <div class="w-16 h-16 rounded-full flex items-center justify-center mb-6 text-white text-3xl" style="background: var(--primary-color);"><i class="fas fa-home"></i></div>
                <h3 class="text-2xl font-bold text-gray-800 mb-4">Travaux de Toiture</h3>
                <p class="text-gray-600 mb-6">Réfection, étanchéité, isolation, traitement...</p>
                <a href="{{ route('form.step', 'propertyType') }}" class="text-primary font-semibold hover:underline">Obtenir un devis →</a>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-8 hover:shadow-2xl transition">
                <div class="w-16 h-16 rounded-full flex items-center justify-center mb-6 text-white text-3xl" style="background: var(--secondary-color);"><i class="fas fa-building"></i></div>
                <h3 class="text-2xl font-bold text-gray-800 mb-4">Travaux de Façade</h3>
                <p class="text-gray-600 mb-6">Ravalement, nettoyage, hydrofuge, ITE...</p>
                <a href="{{ route('form.step', 'propertyType') }}" class="text-primary font-semibold hover:underline">Obtenir un devis →</a>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-8 hover:shadow-2xl transition">
                <div class="w-16 h-16 rounded-full flex items-center justify-center mb-6 text-white text-3xl" style="background: var(--accent-color);"><i class="fas fa-temperature-low"></i></div>
                <h3 class="text-2xl font-bold text-gray-800 mb-4">Travaux d'Isolation</h3>
                <p class="text-gray-600 mb-6">Combles, murs, sols...</p>
                <a href="{{ route('form.step', 'propertyType') }}" class="text-primary font-semibold hover:underline">Obtenir un devis →</a>
            </div>
        </div>
    </div>
</section>

<!-- Avis Clients (depuis la base de données uniquement) -->
@if($reviews && $reviews->count() > 0)
<section id="avis" class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">Ce que disent nos clients</h2>
            <div class="flex items-center justify-center mb-8">
                <div class="flex items-center bg-white rounded-full px-8 py-4 shadow-lg border-2 border-gray-100">
                    <div>
                        <div class="flex items-center text-yellow-400 text-2xl">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star"></i>
                            @endfor
                        </div>
                        <p class="text-gray-700 font-semibold mt-1">
                            <span class="text-2xl font-bold">{{ $reviews->count() > 0 ? number_format($reviews->avg('rating'), 1) : '5.0' }}/5</span> · {{ $reviews->count() }} avis
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto mb-12">
            @foreach($reviews->take(3) as $review)
            <div class="bg-gradient-to-br from-gray-50 to-white rounded-2xl shadow-lg p-8 hover:shadow-xl transition">
                <div class="flex items-center mb-6">
                    <div class="w-14 h-14 rounded-full bg-primary text-white flex items-center justify-center text-xl font-bold mr-4">{{ $review->author_initials }}</div>
                    <div>
                        <h4 class="font-bold text-gray-800">{{ $review->author_name }}</h4>
                        @if($review->author_location)
                        <p class="text-sm text-gray-500">{{ $review->author_location }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center text-yellow-400 mb-4 text-lg">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= $review->rating)
                            <i class="fas fa-star"></i>
                        @else
                            <i class="far fa-star"></i>
                        @endif
                    @endfor
                </div>
                <p class="text-gray-700 italic leading-relaxed">"{{ $review->review_text }}"</p>
            </div>
            @endforeach
        </div>
        
        <!-- Bouton Voir tous les avis -->
        @if($reviews->count() >= 3)
        <div class="text-center">
            <a href="{{ route('reviews.all') }}" class="inline-block bg-white text-primary px-8 py-4 rounded-full font-bold text-lg hover:bg-gray-100 transition shadow-lg border-2 border-primary">
                <i class="fas fa-star mr-2"></i>Voir tous nos avis
                <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
        @endif
    </div>
</section>
@endif

<!-- CTA Final -->
<section class="py-20" style="background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);">
    <div class="container mx-auto px-4 text-center text-white">
        <h2 class="text-4xl md:text-5xl font-bold mb-6">Prêt à Démarrer Votre Projet ?</h2>
        <p class="text-xl md:text-2xl mb-10 max-w-2xl mx-auto">Obtenez votre devis gratuit et personnalisé en moins de 2 minutes</p>
        <a href="{{ route('form.step', 'propertyType') }}" class="inline-flex items-center bg-yellow-400 text-gray-900 px-12 py-6 rounded-full text-2xl font-black hover:bg-yellow-300 transition transform hover:scale-105 shadow-2xl">
            <i class="fas fa-rocket mr-3 text-3xl"></i>
            <span>JE VEUX MON DEVIS GRATUIT</span>
        </a>
    </div>
</section>

@endsection
