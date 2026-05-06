<!-- Footer — fond noir -->
<style>
.sf{background:#0F0C09;color:rgba(255,255,255,.65);font-family:'Manrope',sans-serif;}
.sf-inner{max-width:1200px;margin:0 auto;padding:4rem 1.5rem 2rem;}
.sf-grid{display:grid;grid-template-columns:2fr 1fr 1fr;gap:3rem;}
@media(max-width:768px){.sf-grid{grid-template-columns:1fr;gap:2rem;}.sf-inner{padding:3rem 1.25rem 2rem;}}
.sf-brand-name{font-size:1.25rem;font-weight:800;color:#fff;margin-bottom:.75rem;font-family:'Fraunces',serif;}
.sf-desc{font-size:.875rem;line-height:1.7;color:rgba(255,255,255,.5);max-width:340px;margin-bottom:1.5rem;}
.sf-contact-item{display:flex;align-items:center;gap:.625rem;font-size:.875rem;color:rgba(255,255,255,.6);margin-bottom:.625rem;text-decoration:none;transition:color .18s;}
.sf-contact-item:hover{color:#fff;}
.sf-contact-icon{width:28px;height:28px;border-radius:7px;background:rgba(255,255,255,.07);display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.sf-socials{display:flex;gap:.625rem;margin-top:1.25rem;}
.sf-social{width:34px;height:34px;border-radius:8px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.08);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.6);text-decoration:none;transition:background .18s,color .18s;}
.sf-social:hover{background:rgba(255,255,255,.14);color:#fff;}
.sf-col-title{font-size:.75rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.35);margin-bottom:1rem;}
.sf-link{display:block;font-size:.875rem;color:rgba(255,255,255,.55);text-decoration:none;margin-bottom:.5rem;transition:color .18s;}
.sf-link:hover{color:#fff;}
.sf-bottom{border-top:1px solid rgba(255,255,255,.07);margin-top:3rem;padding-top:1.5rem;display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:1rem;}
.sf-copy{font-size:.8125rem;color:rgba(255,255,255,.35);}
.sf-legal{display:flex;flex-wrap:wrap;gap:1.25rem;}
.sf-legal a{font-size:.8125rem;color:rgba(255,255,255,.35);text-decoration:none;transition:color .18s;}
.sf-legal a:hover{color:rgba(255,255,255,.7);}
.sf-made{font-size:.75rem;color:rgba(255,255,255,.25);text-align:center;margin-top:1rem;}
.sf-made a{color:rgba(255,255,255,.35);text-decoration:none;}
.sf-made a:hover{color:rgba(255,255,255,.6);}
</style>

<footer class="sf">
    <div class="sf-inner">
        <div class="sf-grid">
            <!-- Colonne entreprise -->
            <div>
                <div class="sf-brand-name">{{ setting('company_name', 'Votre Entreprise') }}</div>
                <p class="sf-desc">
                    @php
                        try { $sfDesc = setting('company_description', ''); }
                        catch(Exception $e){ $sfDesc=''; }
                        if(empty($sfDesc)) $sfDesc='Expert en travaux de rénovation et de couverture. Devis gratuit, qualité garantie.';
                    @endphp
                    {{ $sfDesc }}
                </p>

                @php
                    $sfPhone  = setting('company_phone','');
                    $sfPhone2 = setting('company_phone_2','');
                    $sfPhone3 = setting('company_phone_3','');
                    $sfEmail  = setting('company_email','');
                    $sfAddr   = setting('company_address','');
                    $sfCity   = setting('company_city','');
                    $sfZip    = setting('company_postal_code','');
                    $sfHours  = setting('company_hours','');
                @endphp

                @if($sfPhone)
                <a href="tel:{{ setting('company_phone_raw',$sfPhone) }}" class="sf-contact-item">
                    <span class="sf-contact-icon">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.7)" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13 1 .35 1.97.67 2.9a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.93.32 1.9.54 2.9.67A2 2 0 0 1 22 16.92z"/></svg>
                    </span>
                    {{ $sfPhone }}
                </a>
                @endif
                @if($sfPhone2)
                <a href="tel:{{ setting('company_phone_2_raw',$sfPhone2) }}" class="sf-contact-item">
                    <span class="sf-contact-icon">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.7)" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13 1 .35 1.97.67 2.9a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.93.32 1.9.54 2.9.67A2 2 0 0 1 22 16.92z"/></svg>
                    </span>
                    {{ $sfPhone2 }}
                </a>
                @endif
                @if($sfPhone3)
                <a href="tel:{{ setting('company_phone_3_raw',$sfPhone3) }}" class="sf-contact-item">
                    <span class="sf-contact-icon">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.7)" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13 1 .35 1.97.67 2.9a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.93.32 1.9.54 2.9.67A2 2 0 0 1 22 16.92z"/></svg>
                    </span>
                    {{ $sfPhone3 }}
                </a>
                @endif
                @if($sfEmail)
                <a href="mailto:{{ $sfEmail }}" class="sf-contact-item">
                    <span class="sf-contact-icon">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.7)" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </span>
                    {{ $sfEmail }}
                </a>
                @endif
                @if($sfAddr || $sfCity)
                <div class="sf-contact-item">
                    <span class="sf-contact-icon">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.7)" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </span>
                    {{ implode(', ', array_filter([$sfAddr, trim($sfZip.' '.$sfCity)])) }}
                </div>
                @endif
                @if($sfHours)
                <div class="sf-contact-item">
                    <span class="sf-contact-icon">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.7)" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    </span>
                    {{ $sfHours }}
                </div>
                @endif

                {{-- Réseaux sociaux --}}
                @php
                    $sfSocials = [
                        'facebook_url'  => ['icon'=>'fab fa-facebook-f',  'label'=>'Facebook'],
                        'instagram_url' => ['icon'=>'fab fa-instagram',   'label'=>'Instagram'],
                        'twitter_url'   => ['icon'=>'fab fa-twitter',     'label'=>'Twitter'],
                        'linkedin_url'  => ['icon'=>'fab fa-linkedin-in', 'label'=>'LinkedIn'],
                        'youtube_url'   => ['icon'=>'fab fa-youtube',     'label'=>'YouTube'],
                        'tiktok_url'    => ['icon'=>'fab fa-tiktok',      'label'=>'TikTok'],
                        'whatsapp_url'  => ['icon'=>'fab fa-whatsapp',    'label'=>'WhatsApp'],
                    ];
                    $sfActiveSocials = array_filter($sfSocials, fn($k) => !empty(setting($k)), ARRAY_FILTER_USE_KEY);
                @endphp
                @if(count($sfActiveSocials) > 0)
                <div class="sf-socials">
                    @foreach($sfActiveSocials as $key => $net)
                    <a href="{{ setting($key) }}" target="_blank" rel="noopener noreferrer" class="sf-social" aria-label="{{ $net['label'] }}">
                        <i class="{{ $net['icon'] }}" style="font-size:.85rem;"></i>
                    </a>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Liens rapides -->
            <div>
                <div class="sf-col-title">Navigation</div>
                <a href="{{ url('/') }}" class="sf-link">Accueil</a>
                <a href="{{ route('services.index') }}" class="sf-link">Nos Services</a>
                <a href="{{ route('portfolio.index') }}" class="sf-link">Nos Réalisations</a>
                <a href="{{ route('blog.index') }}" class="sf-link">Blog et Astuces</a>
                <a href="{{ route('ads.index') }}" class="sf-link">Nos Annonces</a>
                <a href="{{ route('reviews.all') }}" class="sf-link">Avis Clients</a>
                <a href="{{ route('form.step', 'propertyType') }}" class="sf-link" style="color:var(--primary-color,#B7472A);font-weight:700;">Devis Gratuit →</a>
            </div>

            <!-- Services -->
            <div>
                <div class="sf-col-title">Nos Services</div>
                @php
                    $sfSvcRaw = \App\Models\Setting::get('services','[]');
                    $sfSvcs = is_string($sfSvcRaw) ? json_decode($sfSvcRaw,true) : ($sfSvcRaw??[]);
                    if(!is_array($sfSvcs)) $sfSvcs=[];
                    $sfFeatured = array_filter($sfSvcs, fn($s) => is_array($s) && ($s['is_visible']??true) && ($s['is_featured']??false));
                @endphp
                @if(count($sfFeatured) > 0)
                    @foreach(array_slice($sfFeatured, 0, 5) as $svc)
                        @if(is_array($svc) && isset($svc['name'], $svc['slug']))
                        <a href="{{ route('services.show', $svc['slug']) }}" class="sf-link">{{ $svc['name'] }}</a>
                        @endif
                    @endforeach
                @else
                <a href="{{ route('services.index') }}" class="sf-link">Voir tous nos services</a>
                @endif
                <a href="{{ route('contact') }}" class="sf-link" style="margin-top:.5rem;">Contact</a>
            </div>
        </div>

        <!-- Bottom bar -->
        <div class="sf-bottom">
            <div class="sf-copy">© {{ date('Y') }} {{ setting('company_name','Votre Entreprise') }}. Tous droits réservés.</div>
            <div class="sf-legal">
                <a href="{{ route('legal.mentions') }}">Mentions légales</a>
                <a href="{{ route('legal.privacy') }}">Confidentialité</a>
                <a href="{{ route('legal.cgv') }}">CGV</a>
            </div>
        </div>
        <div class="sf-made">
            Créé par <a href="https://www.osmoseconsulting.fr" target="_blank" rel="noopener">Osmose*</a> avec ❤
        </div>
    </div>
</footer>
