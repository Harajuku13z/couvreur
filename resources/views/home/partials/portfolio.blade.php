    <!-- Portfolio Section -->
    @if(($homeConfig['sections']['portfolio']['enabled'] ?? true) && !empty($portfolioItems))
    <section class="py-20 bg-white">
        <div class="site-shell">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">
                    {{ $homeConfig['sections']['portfolio']['title'] ?? 'Nos Réalisations' }}
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Découvrez quelques-unes de nos réalisations récentes
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach(collect($portfolioItems)->take($homeConfig['sections']['portfolio']['limit'] ?? 6) as $item)
                <a href="{{ route('portfolio.show', $item['slug'] ?? \Illuminate\Support\Str::slug($item['title'] ?? 'realisation')) }}" class="block bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    @if(!empty($item['images']))
                        @php $firstImage = is_array($item['images']) ? $item['images'][0] : $item['images']; @endphp
                        <div class="h-64 relative overflow-hidden bg-gray-100">
                            <img src="{{ asset($firstImage) }}"
                                 alt="{{ $item['title'] ?? 'Réalisation' }}"
                                 class="absolute inset-0 w-full h-full object-cover portfolio-image-mobile"
                                 width="800"
                                 height="512"
                                 loading="lazy"
                                 decoding="async">
                        </div>
                    @else
                        <div class="h-64 bg-gradient-to-br from-primary to-secondary flex items-center justify-center">
                            <i class="fas fa-image text-6xl text-white"></i>
                        </div>
                    @endif
                    
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $item['title'] }}</h3>
                        <p class="text-gray-600 mb-4">{{ Str::limit($item['description'], 100) }}</p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">{{ $item['type'] ?? 'Réalisation' }}</span>
                            <div class="inline-flex items-center text-primary font-semibold">
                                Voir le projet <i class="fas fa-arrow-right ml-1"></i>
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            
            <div class="text-center mt-12">
                <a href="{{ route('portfolio.index') }}" 
                   class="inline-flex items-center bg-primary text-white px-8 py-4 rounded-full text-lg font-semibold hover:bg-secondary transition-all duration-300 transform hover:scale-105 shadow-lg">
                    Voir Toutes Nos Réalisations <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </section>
    @endif
