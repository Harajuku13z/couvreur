    <!-- Villes principales (SEO local & maillage interne) -->
    @if(isset($favoriteCities) && $favoriteCities->count() > 0)
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-10">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                    Villes principales d'intervention
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Nous intervenons régulièrement dans ces villes pour vos travaux de rénovation et de toiture.
                </p>
            </div>
            <div class="max-w-4xl mx-auto">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @foreach($favoriteCities as $city)
                    <a href="{{ route('ads.index') }}?city={{ $city->slug }}"
                       class="group block bg-white rounded-xl border border-gray-200 px-4 py-3 text-center shadow-sm hover:shadow-md transition-all duration-200">
                        <div class="text-sm font-semibold text-gray-800 group-hover:text-primary">
                            {{ $city->name }}
                        </div>
                        @if($city->postal_code)
                        <div class="text-xs text-gray-500">
                            {{ $city->postal_code }}
                        </div>
                        @endif
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif
