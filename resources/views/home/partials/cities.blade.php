    <!-- Villes d'intervention — Oise (60) -->
    <section class="py-16 bg-gray-50 border-y border-gray-100">
        <div class="site-shell">

            <div class="text-center mb-10">
                <p class="text-sm font-bold uppercase tracking-widest mb-3" style="color:var(--primary-color);">Zone d'intervention</p>
                <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900 mb-3">
                    Élagage & Abattage dans tout le département 60
                </h2>
                <p class="text-gray-500 max-w-xl mx-auto">
                    Nous intervenons dans l'ensemble des communes de l'Oise et des départements limitrophes.
                </p>
            </div>

            @php
                // Villes favorites depuis la BDD + villes phares en fallback
                $displayCities = isset($favoriteCities) && $favoriteCities->count() > 0
                    ? $favoriteCities
                    : collect([]);

                // Villes phares de l'Oise à afficher si pas de favoris configurés
                $oiseFallback = [
                    ['name'=>'Compiègne',    'postal'=>'60200'],
                    ['name'=>'Beauvais',     'postal'=>'60000'],
                    ['name'=>'Senlis',       'postal'=>'60300'],
                    ['name'=>'Chantilly',    'postal'=>'60500'],
                    ['name'=>'Creil',        'postal'=>'60100'],
                    ['name'=>'Noyon',        'postal'=>'60400'],
                    ['name'=>'Verberie',     'postal'=>'60410'],
                    ['name'=>'Clermont',     'postal'=>'60600'],
                    ['name'=>'Compiègne',    'postal'=>'60200'],
                    ['name'=>'Pont-Sainte-Maxence','postal'=>'60700'],
                    ['name'=>'Méru',         'postal'=>'60110'],
                    ['name'=>'Liancourt',    'postal'=>'60140'],
                    ['name'=>'Gouvieux',     'postal'=>'60270'],
                    ['name'=>'Lacroix-Saint-Ouen','postal'=>'60610'],
                    ['name'=>'Margny-lès-Compiègne','postal'=>'60280'],
                    ['name'=>'Ribécourt-Dreslincourt','postal'=>'60170'],
                ];
            @endphp

            @if($displayCities->count() > 0)
            {{-- Villes depuis la BDD --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                @foreach($displayCities as $city)
                <a href="{{ route('ads.index') }}?city={{ $city->slug }}"
                   class="group bg-white border border-gray-200 hover:border-transparent hover:shadow-md rounded-xl px-4 py-3 text-center transition-all duration-200 hover:-translate-y-0.5">
                    <div class="w-8 h-8 rounded-lg mx-auto mb-2 flex items-center justify-center" style="background:rgba(var(--primary-color-rgb,34,197,94),.1);">
                        <i class="fas fa-tree text-xs" style="color:var(--primary-color);"></i>
                    </div>
                    <div class="text-sm font-bold text-gray-800 group-hover:text-primary transition-colors leading-tight" style="--text-primary:var(--primary-color);">
                        {{ $city->name }}
                    </div>
                    @if($city->postal_code)
                    <div class="text-xs text-gray-400 mt-0.5">{{ $city->postal_code }}</div>
                    @endif
                </a>
                @endforeach
            </div>
            @else
            {{-- Fallback : villes phares de l'Oise --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                @foreach($oiseFallback as $city)
                <div class="group bg-white border border-gray-200 hover:border-transparent hover:shadow-md rounded-xl px-4 py-3 text-center transition-all duration-200 hover:-translate-y-0.5 cursor-default">
                    <div class="w-8 h-8 rounded-lg mx-auto mb-2 flex items-center justify-center" style="background:rgba(34,197,94,.1);">
                        <i class="fas fa-tree text-xs text-emerald-600"></i>
                    </div>
                    <div class="text-sm font-bold text-gray-800 leading-tight">{{ $city['name'] }}</div>
                    <div class="text-xs text-gray-400 mt-0.5">{{ $city['postal'] }}</div>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Mention couverture globale --}}
            <div class="mt-8 text-center">
                <p class="text-sm text-gray-500">
                    <i class="fas fa-map-marker-alt mr-1" style="color:var(--primary-color);"></i>
                    Et dans toutes les autres communes du département Oise (60) —
                    <a href="{{ route('contact') }}" class="font-semibold underline hover:no-underline" style="color:var(--primary-color);">Contactez-nous</a>
                    pour vérifier votre zone.
                </p>
            </div>

        </div>
    </section>
