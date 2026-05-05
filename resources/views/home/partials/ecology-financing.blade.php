{{-- Sections Écologie & Financement — redesign propre, sans gradient criard --}}
@php
    $ecoVisible = ($homeConfig['ecology']['enabled'] ?? false)
                  && !empty(trim((string) ($homeConfig['ecology']['content'] ?? '')));
    $finVisible = ($homeConfig['financing']['enabled'] ?? false)
                  && !empty(trim((string) ($homeConfig['financing']['content'] ?? '')));
    $ecoBadges  = $homeConfig['ecology']['badges']  ?? [];
    $finBadges  = $homeConfig['financing']['badges'] ?? [];
    $showEcoMat = (bool)($ecoBadges['materiaux_recycles'] ?? true);
    $showEcoEn  = (bool)($ecoBadges['energies_vertes']    ?? true);
    $showMpr    = (bool)($finBadges['maprimerenov']        ?? false);
    $showCee    = (bool)($finBadges['certificats_cee']     ?? false);
@endphp

@if($ecoVisible || $finVisible)
<style>
.eco-grid { display:grid; gap:1.5rem; grid-template-columns:1fr; }
@media(min-width:768px){ .eco-grid { grid-template-columns:{{ ($ecoVisible && $finVisible) ? '1fr 1fr' : '1fr' }}; } }
</style>
<section style="background:#f8faf8; padding:5rem 0; border-top:1px solid #e8f0e8;">
    <div class="site-shell">

        {{-- Titre de section --}}
        <div style="text-align:center; margin-bottom:3rem;">
            <span style="display:inline-flex;align-items:center;gap:.5rem;font-size:.72rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--primary-color);">
                <span style="width:1.5rem;height:2px;background:var(--primary-color);display:block;"></span>
                Nos engagements
                <span style="width:1.5rem;height:2px;background:var(--primary-color);display:block;"></span>
            </span>
        </div>

        <div class="eco-grid">

            @if($ecoVisible)
            {{-- ── ÉCOLOGIE ── --}}
            <div style="background:#fff;border:1px solid #e2ede2;border-radius:20px;padding:2.5rem;position:relative;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.04);">

                {{-- Accent ligne haut --}}
                <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(to right,var(--primary-color),rgba(var(--primary-color-rgb,34,197,94),.2));border-radius:0;"></div>

                {{-- Icône + titre --}}
                <div style="display:flex;align-items:flex-start;gap:1.25rem;margin-bottom:1.5rem;">
                    <div style="width:3rem;height:3rem;border-radius:12px;background:rgba(var(--primary-color-rgb,34,197,94),.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-leaf" style="color:var(--primary-color);font-size:1.15rem;"></i>
                    </div>
                    <div>
                        <h3 style="font-size:1.2rem;font-weight:800;color:#111827;margin:0 0 .25rem;">
                            {{ $homeConfig['ecology']['title'] ?? 'Notre Engagement Écologique' }}
                        </h3>
                        <div style="width:2.5rem;height:2px;background:var(--primary-color);border-radius:2px;"></div>
                    </div>
                </div>

                {{-- Contenu --}}
                <p style="font-size:.925rem;color:#4b5563;line-height:1.8;margin-bottom:{{ ($showEcoMat || $showEcoEn) ? '2rem' : '0' }};">
                    {!! nl2br(e($homeConfig['ecology']['content'])) !!}
                </p>

                {{-- Badges --}}
                @if($showEcoMat || $showEcoEn)
                <div style="display:flex;flex-wrap:wrap;gap:.75rem;">
                    @if($showEcoMat)
                    <div style="display:flex;align-items:center;gap:.6rem;background:rgba(var(--primary-color-rgb,34,197,94),.08);border:1px solid rgba(var(--primary-color-rgb,34,197,94),.2);border-radius:50px;padding:.5rem 1.1rem;">
                        <i class="fas fa-recycle" style="color:var(--primary-color);font-size:.85rem;"></i>
                        <span style="font-size:.8rem;font-weight:700;color:#374151;">Matériaux recyclés</span>
                    </div>
                    @endif
                    @if($showEcoEn)
                    <div style="display:flex;align-items:center;gap:.6rem;background:rgba(var(--primary-color-rgb,34,197,94),.08);border:1px solid rgba(var(--primary-color-rgb,34,197,94),.2);border-radius:50px;padding:.5rem 1.1rem;">
                        <i class="fas fa-seedling" style="color:var(--primary-color);font-size:.85rem;"></i>
                        <span style="font-size:.8rem;font-weight:700;color:#374151;">Énergies vertes</span>
                    </div>
                    @endif
                </div>
                @endif
            </div>
            @endif

            @if($finVisible)
            {{-- ── FINANCEMENT ── --}}
            <div style="background:#fff;border:1px solid #fde9b7;border-radius:20px;padding:2.5rem;position:relative;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.04);">

                {{-- Accent ligne haut --}}
                <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(to right,#f59e0b,rgba(245,158,11,.2));border-radius:0;"></div>

                {{-- Icône + titre --}}
                <div style="display:flex;align-items:flex-start;gap:1.25rem;margin-bottom:1.5rem;">
                    <div style="width:3rem;height:3rem;border-radius:12px;background:rgba(245,158,11,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-hand-holding-usd" style="color:#d97706;font-size:1.15rem;"></i>
                    </div>
                    <div>
                        <h3 style="font-size:1.2rem;font-weight:800;color:#111827;margin:0 0 .25rem;">
                            {{ $homeConfig['financing']['title'] ?? 'Aides et Financements Disponibles' }}
                        </h3>
                        <div style="width:2.5rem;height:2px;background:#f59e0b;border-radius:2px;"></div>
                    </div>
                </div>

                {{-- Contenu --}}
                <p style="font-size:.925rem;color:#4b5563;line-height:1.8;margin-bottom:{{ ($showMpr || $showCee) ? '2rem' : '0' }};">
                    {!! nl2br(e($homeConfig['financing']['content'])) !!}
                </p>

                {{-- Badges --}}
                @if($showMpr || $showCee)
                <div style="display:flex;flex-wrap:wrap;gap:.75rem;">
                    @if($showMpr)
                    <div style="display:flex;align-items:center;gap:.6rem;background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.3);border-radius:50px;padding:.5rem 1.1rem;">
                        <i class="fas fa-home" style="color:#d97706;font-size:.85rem;"></i>
                        <span style="font-size:.8rem;font-weight:700;color:#374151;">MaPrimeRénov'</span>
                    </div>
                    @endif
                    @if($showCee)
                    <div style="display:flex;align-items:center;gap:.6rem;background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.3);border-radius:50px;padding:.5rem 1.1rem;">
                        <i class="fas fa-certificate" style="color:#d97706;font-size:.85rem;"></i>
                        <span style="font-size:.8rem;font-weight:700;color:#374151;">Certificats CEE</span>
                    </div>
                    @endif
                </div>
                @endif
            </div>
            @endif

        </div>
    </div>
</section>
@endif
