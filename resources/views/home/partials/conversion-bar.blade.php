@php
    $phoneMain = setting('company_phone', '');
    $phoneRaw = setting('company_phone_raw', $phoneMain);
@endphp
<div class="bg-slate-900 text-white py-3 px-4 shadow-md border-b border-white/10">
    <div class="container mx-auto flex flex-col sm:flex-row flex-wrap items-center justify-center gap-3 sm:gap-6 text-sm md:text-base">
        <span class="font-semibold text-center sm:text-left">
            <i class="fas fa-bolt text-amber-400 mr-2" aria-hidden="true"></i>
            Réponse sous 24h · Devis gratuit · Sans engagement
        </span>
        @if($phoneMain)
        <a href="tel:{{ $phoneRaw }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 hover:bg-white/20 font-semibold transition"
           onclick="if (typeof trackPhoneCall === 'function') trackPhoneCall('{{ $phoneRaw }}', 'home-conversion-bar');">
            <i class="fas fa-phone-alt" aria-hidden="true"></i>
            {{ $phoneMain }}
        </a>
        @endif
        <a href="{{ route('form.step', 'propertyType') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-full font-semibold text-white shadow-lg transition hover:opacity-95"
           style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
            <i class="fas fa-calculator" aria-hidden="true"></i>
            {{ $homeConfig['hero']['cta_text'] ?? 'Simulateur de devis' }}
        </a>
    </div>
</div>
