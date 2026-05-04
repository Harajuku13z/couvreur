    <!-- Services Section — design moderne -->
    @if(($homeConfig['sections']['services']['enabled'] ?? true) && !empty($services))
    <section class="py-20 bg-gray-50">
        <div class="site-shell">

            {{-- En-tête section --}}
            <div class="text-center mb-14">
                <p class="text-sm font-bold uppercase tracking-widest mb-3" style="color:var(--primary-color);">Ce que nous faisons</p>
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4">
                    {{ $homeConfig['sections']['services']['title'] ?? 'Nos Services' }}
                </h2>
                <p class="text-gray-500 max-w-xl mx-auto text-lg">
                    Élagage, abattage, taille de haies et broyage de souches dans tout le département Oise (60)
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-7">
                @foreach(collect($services)->take($homeConfig['sections']['services']['limit'] ?? 6) as $service)
                <div class="service-card group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col hover:shadow-xl hover:border-transparent transition-all duration-300">

                    {{-- Image ou icône --}}
                    @if(!empty($service['featured_image']))
                    <div class="relative h-52 overflow-hidden">
                        <img src="{{ url($service['featured_image']) }}"
                             alt="{{ $service['name'] }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                             width="667" height="350" loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
                        <div class="absolute bottom-3 left-4">
                            <span class="inline-flex items-center gap-1.5 bg-white/90 backdrop-blur text-xs font-bold px-3 py-1 rounded-full"
                                  style="color:var(--primary-color);">
                                <i class="{{ $service['icon'] ?? 'fas fa-tools' }} text-xs"></i>
                                {{ Str::limit($service['name'], 25) }}
                            </span>
                        </div>
                    </div>
                    @else
                    <div class="h-44 flex items-center justify-center relative overflow-hidden"
                         style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);">
                        <i class="{{ $service['icon'] ?? 'fas fa-tools' }} text-6xl text-white/80 group-hover:scale-110 transition-transform duration-300"></i>
                        <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                             style="background: radial-gradient(circle at center, rgba(255,255,255,.1) 0%, transparent 70%);"></div>
                    </div>
                    @endif

                    <div class="p-6 flex flex-col flex-1">
                        <h3 class="text-lg font-extrabold text-gray-900 mb-2 group-hover:text-primary transition-colors" style="--text-primary:var(--primary-color);">
                            {{ $service['name'] }}
                        </h3>
                        <p class="text-gray-500 text-sm leading-relaxed flex-1 mb-5">
                            {{ $service['short_description'] ?? Str::limit($service['description'] ?? '', 110) }}
                        </p>

                        {{-- Footer card --}}
                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                            <a href="{{ route('services.show', $service['slug']) }}"
                               class="inline-flex items-center gap-2 font-bold text-sm transition"
                               style="color:var(--primary-color);"
                               onclick="trackServiceClick('{{ $service['name'] }}', '{{ request()->url() }}')">
                                En savoir plus
                                <i class="fas fa-arrow-right text-xs group-hover:translate-x-1 transition-transform duration-200"></i>
                            </a>
                            <a href="{{ route('form.step', 'propertyType') }}"
                               class="text-xs font-semibold text-white px-3 py-1.5 rounded-lg transition"
                               style="background:var(--primary-color);"
                               onmouseover="this.style.background='var(--secondary-color)';"
                               onmouseout="this.style.background='var(--primary-color)';">
                                Devis gratuit
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- CTA voir tous les services --}}
            @if(count($services) > ($homeConfig['sections']['services']['limit'] ?? 6))
            <div class="text-center mt-10">
                <a href="{{ route('services.index') }}"
                   class="inline-flex items-center gap-2 border-2 font-bold px-8 py-3.5 rounded-2xl transition-all"
                   style="border-color:var(--primary-color); color:var(--primary-color);"
                   onmouseover="this.style.background='var(--primary-color)'; this.style.color='#fff';"
                   onmouseout="this.style.background='transparent'; this.style.color='var(--primary-color)';">
                    Voir tous nos services <i class="fas fa-arrow-right text-sm"></i>
                </a>
            </div>
            @endif

        </div>
    </section>
    @endif
