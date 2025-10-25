@extends('layouts.app')

@section('title', 'Tous nos avis clients - ' . setting('company_name'))

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12">
    <div class="container mx-auto px-4">
        <!-- Header -->
        <div class="text-center mb-12">
            <a href="{{ route('home') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
                <i class="fas fa-arrow-left mr-2"></i>Retour à l'accueil
            </a>
            <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                Nos Avis Clients
            </h1>
            <p class="text-xl text-gray-600">
                Ce que nos clients disent de nous
            </p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-12 max-w-4xl mx-auto">
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="text-3xl font-bold text-blue-600">{{ $stats['total'] }}</div>
                <div class="text-sm text-gray-600 mt-1">Avis total</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="text-3xl font-bold text-yellow-500">{{ $stats['average'] }}</div>
                <div class="text-sm text-gray-600 mt-1">Note moyenne</div>
                <div class="flex justify-center mt-1">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star {{ $i <= $stats['average'] ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                    @endfor
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="text-3xl font-bold text-green-600">{{ $stats['five_stars'] }}</div>
                <div class="text-sm text-gray-600 mt-1">5 étoiles</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="text-3xl font-bold text-indigo-600">{{ $stats['four_stars'] }}</div>
                <div class="text-sm text-gray-600 mt-1">4 étoiles</div>
            </div>
        </div>

        <!-- Reviews Grid -->
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                @forelse($reviews as $review)
                    <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition">
                        <!-- Rating -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                @endfor
                            </div>
                            @if($review->source)
                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">
                                    @if(str_contains($review->source, 'Google'))
                                        <i class="fab fa-google mr-1"></i>Google Maps
                                    @elseif(str_contains($review->source, 'Travaux'))
                                        <i class="fas fa-tools mr-1"></i>Travaux.com
                                    @elseif(str_contains($review->source, 'LeBonCoin'))
                                        <i class="fas fa-shopping-cart mr-1"></i>LeBonCoin
                                    @elseif(str_contains($review->source, 'Trustpilot'))
                                        <i class="fas fa-shield-alt mr-1"></i>Trustpilot
                                    @elseif(str_contains($review->source, 'Facebook'))
                                        <i class="fab fa-facebook mr-1"></i>Facebook
                                    @else
                                        <i class="fas fa-star mr-1"></i>{{ $review->source }}
                                    @endif
                                </span>
                            @endif
                        </div>

                        <!-- Review Text -->
                        <p class="text-gray-700 mb-4 line-clamp-4">{{ $review->review_text }}</p>

                        <!-- Review Photos -->
                        @if($review->review_photos && count($review->review_photos) > 0)
                            <div class="mb-4">
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach(array_slice($review->review_photos, 0, 4) as $photo)
                                        <img src="{{ Storage::url($photo) }}" 
                                             alt="Photo de l'avis" 
                                             class="w-full h-20 object-cover rounded-lg cursor-pointer hover:opacity-80 transition"
                                             onclick="openImageModal('{{ Storage::url($photo) }}')">
                                    @endforeach
                                </div>
                                @if(count($review->review_photos) > 4)
                                    <p class="text-xs text-gray-500 mt-1">+{{ count($review->review_photos) - 4 }} autres photos</p>
                                @endif
                            </div>
                        @endif

                        <!-- Author -->
                        <div class="flex items-center mt-4 pt-4 border-t border-gray-200">
                            @if($review->author_photo)
                                <img src="{{ $review->author_photo }}" alt="{{ $review->author_name }}" class="w-10 h-10 rounded-full mr-3 object-cover border-2 border-gray-200">
                            @elseif($review->author_photo_url)
                                <img src="{{ $review->author_photo_url }}" alt="{{ $review->author_name }}" class="w-10 h-10 rounded-full mr-3 object-cover border-2 border-gray-200">
                            @else
                                <div class="w-10 h-10 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold mr-3">
                                    {{ $review->author_initials }}
                                </div>
                            @endif
                            <div class="flex-1">
                                @if($review->author_link)
                                    <a href="{{ $review->author_link }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-blue-600 hover:text-blue-800 hover:underline">
                                        {{ $review->author_name }}
                                        <i class="fas fa-external-link-alt text-xs ml-1"></i>
                                    </a>
                                @else
                                    <p class="font-semibold text-gray-900">{{ $review->author_name }}</p>
                                @endif
                                <p class="text-sm text-gray-500">{{ $review->review_date ? $review->review_date->format('d/m/Y') : '' }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-12">
                        <i class="fas fa-star text-6xl text-gray-300 mb-4"></i>
                        <p class="text-xl text-gray-600">Aucun avis pour le moment</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($reviews->hasPages())
            <div class="flex justify-center">
                {{ $reviews->links() }}
            </div>
            @endif
        </div>

        <!-- Add Review Section -->
        <div class="max-w-4xl mx-auto mt-16">
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-star text-yellow-500 mr-2"></i>
                        Partagez votre expérience
                    </h2>
                    <p class="text-lg text-gray-600">
                        Aidez d'autres clients en partageant votre avis sur nos services
                    </p>
                </div>

                <form id="reviewForm" class="space-y-6" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nom -->
                        <div>
                            <label for="author_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Votre nom *
                            </label>
                            <input type="text" id="author_name" name="author_name" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Votre nom complet">
                        </div>

                        <!-- Note -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Votre note *
                            </label>
                            <div class="flex space-x-1" id="rating-stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <button type="button" class="star-rating text-2xl text-gray-300 hover:text-yellow-400 transition" data-rating="{{ $i }}">
                                        <i class="fas fa-star"></i>
                                    </button>
                                @endfor
                            </div>
                            <input type="hidden" id="rating" name="rating" value="5" required>
                        </div>
                    </div>

                    <!-- Commentaire -->
                    <div>
                        <label for="review_text" class="block text-sm font-medium text-gray-700 mb-2">
                            Votre avis *
                        </label>
                        <textarea id="review_text" name="review_text" rows="4" required
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Décrivez votre expérience avec nos services..."></textarea>
                    </div>

                    <!-- Photos -->
                    <div>
                        <label for="review_photos" class="block text-sm font-medium text-gray-700 mb-2">
                            Photos (optionnel)
                        </label>
                        <input type="file" id="review_photos" name="review_photos[]" multiple accept="image/*"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-sm text-gray-500 mt-1">Vous pouvez ajouter plusieurs photos de vos travaux</p>
                    </div>

                    <!-- reCAPTCHA -->
                    <div class="flex justify-center">
                        <div class="g-recaptcha" data-sitekey="{{ setting('recaptcha_site_key', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI') }}"></div>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-center">
                        <button type="submit" class="bg-blue-600 text-white px-8 py-4 rounded-lg hover:bg-blue-700 transition text-lg font-semibold">
                            <i class="fas fa-paper-plane mr-2"></i>Publier mon avis
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- CTA -->
        <div class="text-center mt-12">
            <div class="bg-white rounded-lg shadow-lg p-8 max-w-2xl mx-auto">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">
                    Vous aussi, faites-nous confiance !
                </h2>
                <p class="text-gray-600 mb-6">
                    Obtenez votre devis personnalisé gratuitement
                </p>
                <a href="{{ route('home') }}" class="inline-block bg-blue-600 text-white px-8 py-4 rounded-lg hover:bg-blue-700 transition text-lg font-semibold">
                    <i class="fas fa-calculator mr-2"></i>Démarrer mon projet
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden flex items-center justify-center p-4">
    <div class="relative max-w-4xl max-h-full">
        <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white text-2xl hover:text-gray-300 z-10">
            <i class="fas fa-times"></i>
        </button>
        <img id="modalImage" src="" alt="Photo de l'avis" class="max-w-full max-h-full object-contain">
    </div>
</div>
@endsection

@section('scripts')
<!-- reCAPTCHA -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des étoiles de notation
    const stars = document.querySelectorAll('.star-rating');
    const ratingInput = document.getElementById('rating');
    
    stars.forEach((star, index) => {
        star.addEventListener('click', function() {
            const rating = parseInt(this.dataset.rating);
            ratingInput.value = rating;
            
            // Mettre à jour l'affichage des étoiles
            stars.forEach((s, i) => {
                if (i < rating) {
                    s.classList.remove('text-gray-300');
                    s.classList.add('text-yellow-400');
                } else {
                    s.classList.remove('text-yellow-400');
                    s.classList.add('text-gray-300');
                }
            });
        });
        
        star.addEventListener('mouseenter', function() {
            const rating = parseInt(this.dataset.rating);
            stars.forEach((s, i) => {
                if (i < rating) {
                    s.classList.remove('text-gray-300');
                    s.classList.add('text-yellow-400');
                } else {
                    s.classList.remove('text-yellow-400');
                    s.classList.add('text-gray-300');
                }
            });
        });
    });
    
    // Gestion du formulaire
    document.getElementById('reviewForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Vérifier reCAPTCHA
        const recaptchaResponse = grecaptcha.getResponse();
        if (!recaptchaResponse) {
            alert('Veuillez compléter le reCAPTCHA');
            return;
        }
        
        // Préparer les données
        const formData = new FormData(this);
        formData.append('recaptcha_response', recaptchaResponse);
        
        // Afficher le loading
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Publication en cours...';
        submitBtn.disabled = true;
        
        // Envoyer la requête
        fetch('{{ route("reviews.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Afficher le message de succès
                alert('Votre avis a été soumis avec succès ! Il sera publié après validation.');
                this.reset();
                grecaptcha.reset();
                
                // Réinitialiser les étoiles
                stars.forEach(star => {
                    star.classList.remove('text-yellow-400');
                    star.classList.add('text-gray-300');
                });
                ratingInput.value = '5';
            } else {
                alert('Erreur: ' + (data.message || 'Une erreur est survenue'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Une erreur est survenue lors de la soumission de votre avis');
        })
        .finally(() => {
            // Restaurer le bouton
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
    
    // Fonctions pour la modal d'image
    window.openImageModal = function(imageSrc) {
        document.getElementById('modalImage').src = imageSrc;
        document.getElementById('imageModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };
    
    window.closeImageModal = function() {
        document.getElementById('imageModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    };
    
    // Fermer la modal en cliquant sur le fond
    document.getElementById('imageModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeImageModal();
        }
    });
    
    // Fermer la modal avec Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
        }
    });
});
</script>
@endsection







