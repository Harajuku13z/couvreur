@php
    $hl = $homeLayout ?? ($homeConfig['layout'] ?? 'classic');
    if (!in_array($hl, ['classic', 'showcase', 'magazine', 'conversion'], true)) {
        $hl = 'classic';
    }
@endphp

@if($hl === 'magazine')
    <!-- Hero Magazine : grille éditoriale -->
    <section class="relative min-h-[88vh] lg:min-h-screen flex items-center overflow-hidden hero-mobile pt-20 pb-12"
             @if($homeConfig['hero']['background_image'] ?? null)
             style="background-color: var(--secondary-color); background-image: url('{{ asset($homeConfig['hero']['background_image']) }}'); background-attachment: scroll; background-size: cover; background-position: center; background-repeat: no-repeat; --hero-bg: url('{{ asset($homeConfig['hero']['background_image']) }}');"
             @else
             style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));"
             @endif>
        @if($homeConfig['hero']['background_image'] ?? null)
        <div class="absolute inset-0 bg-black/50 z-0"></div>
        @endif
        <div class="container mx-auto px-4 relative z-10 w-full">
            <div class="grid lg:grid-cols-12 gap-10 lg:gap-14 items-center">
                <div class="lg:col-span-7 text-white text-left">
                    @if(($homeConfig['trust_badges']['garantie_decennale'] ?? false) || ($homeConfig['trust_badges']['certifie_rge'] ?? false) || ($homeConfig['trust_badges']['show_rating'] ?? false))
                    <div class="flex flex-wrap gap-3 mb-8">
                        @if($homeConfig['trust_badges']['garantie_decennale'] ?? false)
                        <span class="inline-flex items-center gap-2 bg-white/15 backdrop-blur px-3 py-1.5 rounded-full text-sm"><i class="fas fa-shield-alt text-amber-300"></i> Garantie décennale</span>
                        @endif
                        @if($homeConfig['trust_badges']['certifie_rge'] ?? false)
                        <span class="inline-flex items-center gap-2 bg-white/15 backdrop-blur px-3 py-1.5 rounded-full text-sm"><i class="fas fa-certificate text-emerald-300"></i> RGE</span>
                        @endif
                        @if(($homeConfig['trust_badges']['show_rating'] ?? false) && $averageRating > 0)
                        <span class="inline-flex items-center gap-2 bg-white/15 backdrop-blur px-3 py-1.5 rounded-full text-sm"><i class="fas fa-star text-yellow-300"></i> {{ number_format($averageRating, 1) }}/5</span>
                        @endif
                    </div>
                    @endif
                    <p class="text-sm uppercase tracking-[0.2em] text-white/80 mb-4 font-semibold">{{ setting('company_specialization', 'Rénovation') }}</p>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold mb-6 leading-tight">
                        {{ $homeConfig['hero']['title'] ?? setting('company_name', 'Votre Entreprise') }}
                    </h1>
                    <p class="text-lg sm:text-xl text-white/90 mb-8 max-w-xl leading-relaxed">
                        {{ $homeConfig['hero']['subtitle'] ?? 'Expert en ' . setting('company_specialization', 'Travaux de Rénovation') }}
                    </p>
                    @php
                        $phoneMain = setting('company_phone', '');
                        $phone2 = setting('company_phone_2', '');
                    @endphp
                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                        <a href="{{ route('form.step', 'propertyType') }}"
                           class="inline-flex justify-center items-center bg-white text-gray-900 px-8 py-4 rounded-xl text-lg font-semibold hover:bg-gray-100 transition shadow-lg">
                            <i class="fas fa-calculator mr-2"></i>
                            {{ $homeConfig['hero']['cta_text'] ?? 'Demander un Devis Gratuit' }}
                        </a>
                        @if(($homeConfig['hero']['show_phone'] ?? true) && $phoneMain)
                        <a href="tel:{{ setting('company_phone_raw', $phoneMain) }}"
                           class="inline-flex justify-center items-center border-2 border-white/80 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-white/10 transition">
                            <i class="fas fa-phone mr-2"></i> {{ $phoneMain }}
                        </a>
                        @endif
                    </div>
                </div>
                <div class="lg:col-span-5 w-full mt-10 lg:mt-0">
                    <div class="relative w-full max-w-lg mx-auto lg:max-w-none rounded-3xl overflow-hidden shadow-2xl border border-white/20 aspect-[4/5] max-h-[min(72vh,560px)] bg-gradient-to-br from-white/10 to-white/5">
                        @if(!empty($homeConfig['hero']['magazine_side_image']))
                        <img src="{{ asset($homeConfig['hero']['magazine_side_image']) }}"
                             alt="{{ strip_tags($homeConfig['hero']['title'] ?? setting('company_name', 'Notre équipe')) }}"
                             class="absolute inset-0 h-full w-full object-cover object-center"
                             loading="eager"
                             decoding="async"
                             fetchpriority="high">
                        @else
                        <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-white/10 to-transparent" aria-hidden="true">
                            <i class="fas fa-camera text-7xl text-white/20"></i>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

@elseif($hl === 'conversion')
    <!-- Hero conversion : plus compact, focus CTA -->
    <section class="relative min-h-[72vh] flex items-center justify-center overflow-hidden hero-mobile pt-20 pb-16"
             @if($homeConfig['hero']['background_image'] ?? null)
             style="background-color: var(--secondary-color); background-image: url('{{ asset($homeConfig['hero']['background_image']) }}'); background-attachment: scroll; background-size: cover; background-position: center; background-repeat: no-repeat; --hero-bg: url('{{ asset($homeConfig['hero']['background_image']) }}');"
             @else
             style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));"
             @endif>
        @if($homeConfig['hero']['background_image'] ?? null)
        <div class="absolute inset-0 bg-black/45 z-0"></div>
        @endif
        <div class="container mx-auto px-4 text-center text-white relative z-10 max-w-4xl">
            @if(($homeConfig['trust_badges']['garantie_decennale'] ?? false) || ($homeConfig['trust_badges']['certifie_rge'] ?? false) || ($homeConfig['trust_badges']['show_rating'] ?? false))
            <div class="flex justify-center flex-wrap gap-3 mb-6">
                @if($homeConfig['trust_badges']['garantie_decennale'] ?? false)
                <span class="text-xs sm:text-sm bg-white/20 backdrop-blur px-3 py-1 rounded-full">Garantie décennale</span>
                @endif
                @if($homeConfig['trust_badges']['certifie_rge'] ?? false)
                <span class="text-xs sm:text-sm bg-white/20 backdrop-blur px-3 py-1 rounded-full">Certifié RGE</span>
                @endif
                @if(($homeConfig['trust_badges']['show_rating'] ?? false) && $averageRating > 0)
                <span class="text-xs sm:text-sm bg-white/20 backdrop-blur px-3 py-1 rounded-full">{{ number_format($averageRating, 1) }}/5 ({{ $totalReviews }} avis)</span>
                @endif
            </div>
            @endif
            <h1 class="text-4xl md:text-6xl font-extrabold mb-5 leading-tight drop-shadow-lg">
                {{ $homeConfig['hero']['title'] ?? setting('company_name', 'Votre Entreprise') }}
            </h1>
            <p class="text-lg md:text-xl mb-10 text-white/95 max-w-2xl mx-auto">
                {{ $homeConfig['hero']['subtitle'] ?? 'Expert en ' . setting('company_specialization', 'Travaux de Rénovation') }}
            </p>
            @php
                $phoneMain = setting('company_phone', '');
                $phone2 = setting('company_phone_2', '');
            @endphp
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-stretch sm:items-center max-w-lg mx-auto">
                <a href="{{ route('form.step', 'propertyType') }}"
                   class="bg-white text-gray-900 px-10 py-5 rounded-2xl text-lg font-bold hover:bg-gray-100 transition shadow-xl transform hover:scale-[1.02]">
                    <i class="fas fa-calculator mr-2"></i>
                    {{ $homeConfig['hero']['cta_text'] ?? 'Devis gratuit en ligne' }}
                </a>
                @if(($homeConfig['hero']['show_phone'] ?? true) && $phoneMain)
                <a href="tel:{{ setting('company_phone_raw', $phoneMain) }}"
                   class="border-2 border-white text-white px-10 py-5 rounded-2xl text-lg font-bold hover:bg-white/15 transition">
                    <i class="fas fa-phone mr-2"></i> {{ $phoneMain }}
                </a>
                @endif
            </div>
            @if(($homeConfig['hero']['show_phone'] ?? true) && $phone2)
            <p class="mt-6">
                <a href="tel:{{ setting('company_phone_2_raw', $phone2) }}" class="text-white/90 underline text-sm">{{ $phone2 }}</a>
            </p>
            @endif
        </div>
    </section>

@else
    <!-- Hero Classic / Showcase -->
    <section class="relative {{ $hl === 'showcase' ? 'min-h-[92vh]' : 'min-h-screen' }} flex items-center justify-center overflow-hidden hero-mobile pt-16 pb-16"
             @if($homeConfig['hero']['background_image'] ?? null)
             style="background-color: var(--secondary-color); background-image: url('{{ asset($homeConfig['hero']['background_image']) }}'); background-attachment: scroll; background-size: cover; background-position: center; background-repeat: no-repeat; --hero-bg: url('{{ asset($homeConfig['hero']['background_image']) }}');"
             @else
             style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));"
             @endif>
        @if($homeConfig['hero']['background_image'] ?? null)
        <div class="absolute inset-0 bg-black/40 z-0"></div>
        @endif
        @if($hl === 'showcase')
        <div class="absolute inset-0 opacity-30 pointer-events-none z-0" style="background-image: radial-gradient(circle at 20% 50%, rgba(255,255,255,0.15) 0%, transparent 50%), radial-gradient(circle at 80% 20%, rgba(255,255,255,0.1) 0%, transparent 40%);"></div>
        @endif
        <div class="container mx-auto px-4 text-center text-white relative z-10 pt-8 {{ $hl === 'showcase' ? 'max-w-5xl' : '' }}">
            @if(($homeConfig['trust_badges']['garantie_decennale'] ?? false) || ($homeConfig['trust_badges']['certifie_rge'] ?? false) || ($homeConfig['trust_badges']['show_rating'] ?? false))
            <div class="flex justify-center items-center gap-6 mb-8 flex-wrap px-4">
                @if($homeConfig['trust_badges']['garantie_decennale'] ?? false)
                <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full">
                    <i class="fas fa-shield-alt text-yellow-400"></i>
                    <span class="text-sm font-medium">Garantie Décennale</span>
                </div>
                @endif
                @if($homeConfig['trust_badges']['certifie_rge'] ?? false)
                <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full">
                    <i class="fas fa-certificate text-green-400"></i>
                    <span class="text-sm font-medium">Certifié RGE</span>
                </div>
                @endif
                @if(($homeConfig['trust_badges']['show_rating'] ?? false) && $averageRating > 0)
                <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full">
                    <div class="flex text-yellow-400">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star {{ $i <= $averageRating ? '' : 'text-gray-300' }}"></i>
                        @endfor
                    </div>
                    <span class="text-sm font-medium">{{ number_format($averageRating, 1) }}/5 ({{ $totalReviews }} avis)</span>
                </div>
                @endif
            </div>
            @endif

            <h1 class="{{ $hl === 'showcase' ? 'text-5xl md:text-8xl' : 'text-5xl md:text-7xl' }} font-bold mb-6 leading-tight {{ $hl === 'showcase' ? 'tracking-tight' : '' }}">
                {{ $homeConfig['hero']['title'] ?? setting('company_name', 'Votre Entreprise') }}
            </h1>

            <p class="{{ $hl === 'showcase' ? 'text-xl md:text-3xl' : 'text-xl md:text-2xl' }} mb-8 max-w-3xl mx-auto leading-relaxed font-medium">
                {{ $homeConfig['hero']['subtitle'] ?? 'Expert en ' . setting('company_specialization', 'Travaux de Rénovation') }}
            </p>

            @php
                $phoneMain = setting('company_phone', '');
                $phone2 = setting('company_phone_2', '');
            @endphp
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center px-4 mb-8">
                <a href="{{ route('form.step', 'propertyType') }}"
                   class="bg-primary text-white px-8 py-4 rounded-full text-lg font-semibold hover:bg-secondary transition-all duration-300 transform hover:scale-105 shadow-lg {{ $hl === 'showcase' ? 'px-10 py-5 text-xl' : '' }}">
                    <i class="fas fa-calculator mr-2"></i>
                    {{ $homeConfig['hero']['cta_text'] ?? 'Demander un Devis Gratuit' }}
                </a>
                @if(($homeConfig['hero']['show_phone'] ?? true) && $phoneMain)
                <a href="tel:{{ setting('company_phone_raw', $phoneMain) }}"
                   class="bg-primary text-white px-8 py-4 rounded-full text-lg font-semibold hover:bg-secondary transition-all duration-300 transform hover:scale-105 shadow-lg">
                    <i class="fas fa-phone mr-2"></i>
                    {{ $phoneMain }}
                </a>
                @endif
                @if(($homeConfig['hero']['show_phone'] ?? true) && $phone2)
                <a href="tel:{{ setting('company_phone_2_raw', $phone2) }}"
                   class="bg-gray-100 text-gray-900 px-8 py-4 rounded-full text-lg font-semibold hover:bg-white transition-all duration-300 transform hover:scale-105 shadow-lg border border-gray-300">
                    <i class="fas fa-phone-alt mr-2"></i>
                    {{ $phone2 }}
                </a>
                @endif
            </div>
        </div>

        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 text-white animate-bounce">
            <i class="fas fa-chevron-down text-2xl"></i>
        </div>
    </section>
@endif
