    <!-- Reviews Section -->
    @if(($homeConfig['sections']['reviews']['enabled'] ?? true) && !empty($reviews))
    <section class="py-20 bg-gray-100">
        <div class="site-shell">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">
                    {{ $homeConfig['sections']['reviews']['title'] ?? 'Avis de Nos Clients' }}
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Ce que nos clients disent de nous
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($reviews->take($homeConfig['sections']['reviews']['limit'] ?? 6) as $review)
                <div class="bg-white p-6 rounded-2xl shadow-lg">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center font-bold">
                            {{ $review->author_initials ?? substr($review->author_name, 0, 1) }}
                        </div>
                        <div class="ml-4">
                            <h4 class="font-semibold text-gray-800">{{ $review->author_name }}</h4>
                            <div class="flex text-yellow-400">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= $review->rating ? '' : 'text-gray-300' }}"></i>
                                @endfor
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">{{ Str::limit($review->review_text, 150) }}</p>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">
                            {{ $review->review_date ? $review->review_date->diffForHumans() : $review->created_at->diffForHumans() }}
                        </span>
                        @if($review->source)
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">{{ $review->source }}</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Bouton "Lire tous les avis" -->
            <div class="text-center mt-12">
                <a href="{{ route('reviews.all') }}" 
                   class="bg-primary text-white px-8 py-4 rounded-lg font-semibold hover:bg-secondary transition-colors text-lg">
                    <i class="fas fa-star mr-2"></i>
                    Lire Tous les Avis
                </a>
            </div>
        </div>
    </section>
    @endif
