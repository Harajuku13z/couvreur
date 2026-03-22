    <!-- Services Section -->
    @if(($homeConfig['sections']['services']['enabled'] ?? true) && !empty($services))
    <section class="py-20">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">
                    {{ $homeConfig['sections']['services']['title'] ?? 'Nos Services' }}
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Des solutions complètes pour tous vos projets de rénovation
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach(collect($services)->take($homeConfig['sections']['services']['limit'] ?? 6) as $service)
                <div class="service-card bg-white rounded-2xl shadow-lg overflow-hidden">
                    @if(!empty($service['featured_image']))
                    <div class="h-48 bg-cover bg-center mobile-responsive-img service-image-mobile" style="background-image: url('{{ url($service['featured_image']) }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">
                        <img src="{{ url($service['featured_image']) }}" 
                             alt="{{ $service['name'] }}" 
                             class="w-full h-full object-cover mobile-responsive-img"
                             style="display: none;"
                             width="667"
                             height="350"
                             loading="lazy">
                    </div>
                    @else
                    <div class="h-48 bg-gradient-to-br from-primary to-secondary flex items-center justify-center">
                        <i class="{{ $service['icon'] ?? 'fas fa-tools' }} text-6xl text-white"></i>
                    </div>
                    @endif
                    
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-3">{{ $service['name'] }}</h3>
                        <p class="text-gray-600 mb-4">{{ $service['short_description'] ?? Str::limit($service['description'], 120) }}</p>
                        <a href="{{ route('services.show', $service['slug']) }}" 
                           class="inline-flex items-center font-semibold transition"
                           style="color: var(--primary-color);"
                           onmouseover="this.style.color='var(--secondary-color)';"
                           onmouseout="this.style.color='var(--primary-color)';"
                           onclick="trackServiceClick('{{ $service['name'] }}', '{{ request()->url() }}')">
                            En savoir plus <i class="fas fa-arrow-right ml-2" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif
