@extends('layouts.app')

@section('title', 'Ajouter un avis - ' . setting('company_name'))

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16">
        <div class="container mx-auto px-4">
            <div class="text-center">
                <a href="{{ route('reviews.all') }}" class="text-blue-200 hover:text-white mb-6 inline-block transition">
                    <i class="fas fa-arrow-left mr-2"></i>Retour aux avis
                </a>
                <h1 class="text-5xl md:text-6xl font-bold mb-6">
                    Partagez votre expérience
                </h1>
                <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                    Aidez d'autres clients en partageant votre avis sur nos services
                </p>
            </div>
        </div>
    </div>

    <!-- Form Section -->
    <div class="py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-8 text-center">
                        <i class="fas fa-star text-4xl text-yellow-500 mb-4"></i>
                        <h2 class="text-3xl font-bold text-gray-800 mb-2">Votre avis compte !</h2>
                        <p class="text-gray-600">Votre témoignage aide d'autres clients à nous faire confiance</p>
                    </div>

                    <form id="reviewForm" class="p-8 space-y-8" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Honeypot et timestamp pour protection anti-spam -->
                        <input type="text" name="honeypot" style="display: none;" tabindex="-1" autocomplete="off">
                        <input type="hidden" name="timestamp" value="{{ time() }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Nom -->
                            <div>
                                <label for="author_name" class="block text-lg font-semibold text-gray-700 mb-3">
                                    Votre nom *
                                </label>
                                <input type="text" id="author_name" name="author_name" required
                                       class="w-full px-6 py-4 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
                                       placeholder="Votre nom complet">
                            </div>

                            <!-- Note -->
                            <div>
                                <label class="block text-lg font-semibold text-gray-700 mb-3">
                                    Votre note *
                                </label>
                                <div class="flex space-x-2" id="rating-stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        <button type="button" class="star-rating text-4xl text-gray-300 hover:text-yellow-400 transition-all duration-200 hover:scale-110" data-rating="{{ $i }}">
                                            <i class="fas fa-star"></i>
                                        </button>
                                    @endfor
                                </div>
                                <input type="hidden" id="rating" name="rating" value="5" required>
                                <p class="text-sm text-gray-500 mt-2">Cliquez sur les étoiles pour noter</p>
                            </div>
                        </div>

                        <!-- Commentaire -->
                        <div>
                            <label for="review_text" class="block text-lg font-semibold text-gray-700 mb-3">
                                Votre avis *
                            </label>
                            <textarea id="review_text" name="review_text" rows="6" required
                                      class="w-full px-6 py-4 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg resize-none"
                                      placeholder="Décrivez votre expérience avec nos services... (minimum 10 caractères)"></textarea>
                            <p class="text-sm text-gray-500 mt-2">Partagez les détails de votre expérience pour aider d'autres clients</p>
                        </div>

                        <!-- Système de photos supprimé -->

                        <!-- Submit Button -->
                        <div class="text-center pt-6">
                            <button type="submit" class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-12 py-4 rounded-full font-semibold text-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                <i class="fas fa-paper-plane mr-2"></i>Publier mon avis
                            </button>
                            <p class="text-sm text-gray-500 mt-4">
                                <i class="fas fa-shield-alt mr-1"></i>
                                Votre avis sera publié après validation
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
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
    
    // Gestion de l'upload de photos
    const photoInput = document.getElementById('review_photos');
    const photoPreview = document.getElementById('photo-preview');
    
    photoInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        photoPreview.innerHTML = '';
        
        if (files.length > 0) {
            photoPreview.classList.remove('hidden');
            
            files.forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'relative group';
                        div.innerHTML = `
                            <img src="${e.target.result}" class="w-full h-24 object-cover rounded-lg">
                            <button type="button" class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600" onclick="removePhoto(${index})">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                        photoPreview.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                }
            });
        } else {
            photoPreview.classList.add('hidden');
        }
    });
    
    // Fonction pour supprimer une photo
    window.removePhoto = function(index) {
        const dt = new DataTransfer();
        const files = Array.from(photoInput.files);
        files.splice(index, 1);
        files.forEach(file => dt.items.add(file));
        photoInput.files = dt.files;
        
        // Recharger l'aperçu
        photoInput.dispatchEvent(new Event('change'));
    };
    
    // Gestion du formulaire
    document.getElementById('reviewForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Afficher le loading
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Publication en cours...';
        submitBtn.disabled = true;
        
        // Préparer les données
        const formData = new FormData(this);
        
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
                window.location.href = '{{ route("reviews.all") }}';
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
});
</script>
@endsection
