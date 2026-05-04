    <!-- Avis Clients -->
    @if(($homeConfig['sections']['reviews']['enabled'] ?? true) && !empty($reviews))
    <section class="py-20 bg-white">
        <div class="site-shell">

            <div class="text-center mb-14">
                <p class="text-sm font-bold uppercase tracking-widest mb-3" style="color:var(--primary-color);">Témoignages</p>
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4">
                    {{ $homeConfig['sections']['reviews']['title'] ?? 'Ce que disent nos clients de l\'Oise' }}
                </h2>
                @if($averageRating > 0)
                <div class="flex items-center justify-center gap-3 mt-4">
                    <div class="flex gap-0.5">
                        @for($i=1;$i<=5;$i++)
                        <i class="fas fa-star {{ $i <= round($averageRating) ? 'text-amber-400' : 'text-gray-200' }} text-lg"></i>
                        @endfor
                    </div>
                    <span class="font-extrabold text-gray-900 text-xl">{{ number_format($averageRating,1) }}/5</span>
                    <span class="text-gray-400 text-sm">({{ $totalReviews }} avis vérifiés)</span>
                </div>
                @endif
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($reviews->take($homeConfig['sections']['reviews']['limit'] ?? 6) as $review)
                <div class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 p-7 flex flex-col">

                    {{-- Étoiles --}}
                    <div class="flex gap-0.5 mb-4">
                        @for($i=1;$i<=5;$i++)
                        <i class="fas fa-star {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-200' }} text-sm"></i>
                        @endfor
                    </div>

                    {{-- Texte --}}
                    <p class="text-gray-700 leading-relaxed text-sm flex-1 mb-5 italic">
                        "{{ Str::limit($review->review_text ?? 'Excellent travail, très professionnel.', 160) }}"
                    </p>

                    {{-- Auteur --}}
                    <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                        <div class="w-11 h-11 rounded-full overflow-hidden flex-shrink-0">
                            @if($review->author_photo_url)
                            <img src="{{ $review->author_photo_url }}" alt="{{ $review->author_name }}" class="w-full h-full object-cover">
                            @else
                            <div class="w-full h-full flex items-center justify-center text-white font-extrabold text-base"
                                 style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                                {{ strtoupper(substr($review->author_name ?? 'C', 0, 1)) }}
                            </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-gray-900 text-sm truncate">{{ $review->author_name }}</p>
                            <p class="text-gray-400 text-xs">
                                {{ $review->review_date ? \Carbon\Carbon::parse($review->review_date)->translatedFormat('F Y') : '' }}
                                @if($review->source && $review->source !== 'manual')
                                &bull;
                                @if(str_contains($review->source,'Google'))
                                <i class="fab fa-google text-blue-500"></i> Google
                                @else
                                {{ ucfirst($review->source) }}
                                @endif
                                @endif
                            </p>
                        </div>
                        {{-- Icône guillemets --}}
                        <i class="fas fa-quote-right text-2xl text-gray-100 group-hover:text-gray-200 transition-colors flex-shrink-0"></i>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="text-center mt-10">
                <a href="{{ route('reviews.all') }}"
                   class="inline-flex items-center gap-2 font-bold px-8 py-3.5 rounded-2xl border-2 transition-all"
                   style="border-color:var(--primary-color); color:var(--primary-color);"
                   onmouseover="this.style.background='var(--primary-color)';this.style.color='#fff';"
                   onmouseout="this.style.background='transparent';this.style.color='var(--primary-color)';">
                    <i class="fas fa-star text-sm"></i>
                    Lire tous les avis
                </a>
            </div>

        </div>
    </section>
    @endif
