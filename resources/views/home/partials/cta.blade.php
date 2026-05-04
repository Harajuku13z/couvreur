    <!-- Bannière CTA finale — Oise (60) -->
    @if($homeConfig['sections']['cta']['enabled'] ?? true)
    <section class="py-0 relative overflow-hidden">
        {{-- Fond dégradé forêt --}}
        <div class="absolute inset-0" style="background: linear-gradient(135deg, #0c2340 0%, #0f3d2e 60%, #1a5c20 100%);"></div>
        {{-- Orbes déco --}}
        <div class="absolute top-0 right-0 w-96 h-96 rounded-full pointer-events-none opacity-10"
             style="background: radial-gradient(circle, rgba(34,197,94,.5) 0%, transparent 70%); transform: translate(30%,-30%);"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 rounded-full pointer-events-none opacity-10"
             style="background: radial-gradient(circle, rgba(16,185,129,.4) 0%, transparent 70%); transform: translate(-30%,30%);"></div>

        <div class="site-shell relative z-10 py-20">
            <div class="max-w-4xl mx-auto text-center">

                {{-- Badge --}}
                <div class="mb-6">
                    <span class="inline-flex items-center gap-2 bg-white/12 border border-white/20 text-white text-sm font-semibold px-5 py-2 rounded-full">
                        <i class="fas fa-map-marker-alt text-emerald-400 text-xs"></i>
                        Département 60 — Oise · Picardie
                    </span>
                </div>

                <h2 class="text-3xl md:text-5xl font-extrabold text-white mb-5 leading-tight drop-shadow-xl">
                    {{ $homeConfig['sections']['cta']['title'] ?? 'Besoin d\'un élagueur dans l\'Oise ?' }}
                </h2>

                <p class="text-lg md:text-xl text-white/80 mb-10 max-w-2xl mx-auto leading-relaxed">
                    Compiègne, Beauvais, Senlis, Chantilly, Creil, Noyon… Louis Hoffmann intervient
                    partout dans le département 60. Devis gratuit, réponse sous 24h.
                </p>

                {{-- Boutons --}}
                <div class="flex flex-col sm:flex-row gap-4 justify-center mb-10">
                    <a href="{{ route('form.step', 'propertyType') }}"
                       class="inline-flex items-center justify-center gap-2 bg-white font-extrabold text-lg px-8 py-4 rounded-2xl shadow-2xl hover:bg-gray-50 transition-all hover:scale-[1.02]"
                       style="color:var(--primary-color);"
                       onclick="if(typeof trackFormClick==='function')trackFormClick('{{ request()->url() }}')">
                        <i class="fas fa-calculator"></i>
                        Devis gratuit en ligne
                    </a>
                    <a href="tel:{{ setting('company_phone_raw', setting('company_phone')) }}"
                       class="inline-flex items-center justify-center gap-2 border-2 border-white/70 text-white font-bold text-lg px-8 py-4 rounded-2xl hover:bg-white/12 transition-all"
                       onclick="if(typeof trackPhoneCall==='function')trackPhoneCall('{{ setting('company_phone_raw', setting('company_phone')) }}', 'home-cta')">
                        <i class="fas fa-phone text-emerald-300"></i>
                        {{ setting('company_phone', '06 42 21 41 51') }}
                    </a>
                    <a href="{{ route('contact') }}"
                       class="inline-flex items-center justify-center gap-2 border border-white/30 text-white/85 font-semibold text-base px-6 py-4 rounded-2xl hover:bg-white/08 transition-all">
                        <i class="fas fa-envelope text-sm"></i>
                        Envoyer un message
                    </a>
                </div>

                {{-- Garanties rapides --}}
                <div class="flex flex-wrap justify-center gap-6 text-white/65 text-sm">
                    @foreach(['Devis 100% gratuit','Sans engagement','Réponse sous 24h','Artisan assuré','Tout le dépt. 60'] as $g)
                    <span class="flex items-center gap-1.5">
                        <i class="fas fa-check text-emerald-400 text-xs"></i>
                        {{ $g }}
                    </span>
                    @endforeach
                </div>

            </div>
        </div>
    </section>
    @endif
