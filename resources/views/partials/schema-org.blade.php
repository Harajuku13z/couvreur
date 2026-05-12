{{-- Schema.org Structured Data --}}
@php
    $companyName = setting('company_name', 'Votre Entreprise');
    $companyDescription = setting('company_description', '');
    $companyPhone = setting('company_phone_raw', '');
    $companyPhone2 = setting('company_phone_2_raw', '');
    $companyPhone3 = setting('company_phone_3_raw', '');
    $companyEmail = setting('company_email', '');
    $companyAddress = setting('company_address', '');
    $companyCity = setting('company_city', '');
    $companyPostalCode = setting('company_postal_code', '');
    $companyCountry = setting('company_country', 'France');
    $companyHours = setting('company_hours', '');
    $companyUrl = url('/');
    
    // Organisation Schema - Logo optimisé pour Google
    $companyLogo = setting('company_logo');
    $logoUrl = null;
    
    // Priorité 1: Logo d'entreprise configuré
    if ($companyLogo) {
        $logoUrl = strpos($companyLogo, 'http') === 0 ? $companyLogo : url($companyLogo);
    }
    
    // Priorité 2: Favicon 96x96 ou 192x192 (optimal pour Google)
    if (!$logoUrl) {
        $seoConfigData = \App\Models\Setting::get('seo_config', '[]');
        $seoConfig = is_string($seoConfigData) ? json_decode($seoConfigData, true) : ($seoConfigData ?? []);
        
        // Essayer le favicon 192x192 d'abord (recommandé par Google)
        $favicon192 = $seoConfig['favicon_192x192'] ?? 'favicons/favicon-192x192.png';
        if (file_exists(public_path($favicon192))) {
            $logoUrl = url($favicon192);
        } else {
            // Essayer le favicon 96x96
            $favicon96 = $seoConfig['favicon_96x96'] ?? 'favicons/favicon-96x96.png';
            if (file_exists(public_path($favicon96))) {
                $logoUrl = url($favicon96);
            } else {
                // Essayer site_favicon
                $siteFavicon = setting('site_favicon');
                if ($siteFavicon && file_exists(public_path($siteFavicon))) {
                    $logoUrl = url($siteFavicon);
                }
            }
        }
    }
    
    // Fallback: Logo par défaut
    if (!$logoUrl) {
        $logoUrl = url('logo/logo.png');
    }
    
    // Construire l'adresse complète (toujours inclure même si vide pour éviter les erreurs)
    // Google exige au moins un champ d'adresse rempli pour LocalBusiness
    $addressSchema = [
        "@type" => "PostalAddress",
        "addressCountry" => $companyCountry ?: "FR"  // Toujours au moins le pays
    ];
    
    // Ajouter les autres champs s'ils existent
    if (!empty($companyAddress)) {
        $addressSchema["streetAddress"] = $companyAddress;
    }
    if (!empty($companyCity)) {
        $addressSchema["addressLocality"] = $companyCity;
    }
    if (!empty($companyPostalCode)) {
        $addressSchema["postalCode"] = $companyPostalCode;
    }
    
    // Préparer les reviews si disponibles
    $reviewItems = [];
    if (isset($reviews) && is_object($reviews) && method_exists($reviews, 'count') && $reviews->count() > 0 && isset($averageRating)) {
        foreach ($reviews->take(5) as $review) {
            $reviewItems[] = [
                "@type" => "Review",
                "author" => [
                    "@type" => "Person",
                    "name" => $review->author_name
                ],
                "reviewRating" => [
                    "@type" => "Rating",
                    "ratingValue" => $review->rating,
                    "bestRating" => "5"
                ],
                "reviewBody" => $review->review_text
            ];
        }
    }
    
    // Construire le schéma global d'organisation / service professionnel
    $organizationSchema = [
        "@context" => "https://schema.org",
        "@type" => ["Organization", "ProfessionalService"],
        "name" => $companyName,
        "url" => $companyUrl,
        "address" => $addressSchema
    ];
    
    // Ajouter les champs optionnels seulement s'ils ont une valeur
    if (!empty($companyDescription)) {
        $organizationSchema["description"] = $companyDescription;
    }
    if (!empty($companyPhone)) {
        $organizationSchema["telephone"] = $companyPhone;
    }
    if (!empty($companyEmail)) {
        $organizationSchema["email"] = $companyEmail;
    }
    $organizationSchema["priceRange"] = "€€";
    
    // Contact points (plusieurs numéros avec type)
    $contactPoints = [];
    if (!empty($companyPhone)) {
        $contactPoints[] = [
            "@type" => "ContactPoint",
            "telephone" => $companyPhone,
            "contactType" => "customer service",
            "areaServed" => $companyCountry ?: "FR",
            "availableLanguage" => ["fr-FR"]
        ];
    }
    if (!empty($companyPhone2)) {
        $contactPoints[] = [
            "@type" => "ContactPoint",
            "telephone" => $companyPhone2,
            "contactType" => "sales",
            "areaServed" => $companyCountry ?: "FR",
            "availableLanguage" => ["fr-FR"]
        ];
    }
    if (!empty($companyPhone3)) {
        $contactPoints[] = [
            "@type" => "ContactPoint",
            "telephone" => $companyPhone3,
            "contactType" => "emergency",
            "areaServed" => $companyCountry ?: "FR",
            "availableLanguage" => ["fr-FR"]
        ];
    }
    if (!empty($contactPoints)) {
        $organizationSchema["contactPoint"] = $contactPoints;
    }
    
    // openingHoursSpecification (parse un format simple ex: "Lun–Ven 8h00–19h00, Sam 9h00–13h00, Dim fermé")
    if (!empty($companyHours)) {
        $dayMap = [
            'Lun' => 'Monday',
            'Mar' => 'Tuesday',
            'Mer' => 'Wednesday',
            'Jeu' => 'Thursday',
            'Ven' => 'Friday',
            'Sam' => 'Saturday',
            'Dim' => 'Sunday',
        ];
        $openingHours = [];
        $parts = preg_split('/[,;]+/', $companyHours);
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') continue;
            
            // Ex: "Lun–Ven 8h00–19h00" ou "Sam 9h–13h"
            if (!preg_match('/^(Lun|Mar|Mer|Jeu|Ven|Sam|Dim)(?:\s*[–\-]\s*(Lun|Mar|Mer|Jeu|Ven|Sam|Dim))?\s+(.+)$/ui', $part, $m)) {
                continue;
            }
            $startDay = $m[1];
            $endDay = $m[2] ?? null;
            $hoursPart = trim($m[3]);
            
            if (stripos($hoursPart, 'fermé') !== false) {
                continue;
            }
            
            // Extraire heures d'ouverture/fermeture (8h, 8h00, 08:00, etc.)
            if (!preg_match('/(\d{1,2})[hH:]?(\d{0,2})\s*[–\-]\s*(\d{1,2})[hH:]?(\d{0,2})/u', $hoursPart, $hm)) {
                continue;
            }
            $openH = str_pad($hm[1], 2, '0', STR_PAD_LEFT);
            $openM = $hm[2] !== '' ? str_pad($hm[2], 2, '0', STR_PAD_LEFT) : '00';
            $closeH = str_pad($hm[3], 2, '0', STR_PAD_LEFT);
            $closeM = $hm[4] !== '' ? str_pad($hm[4], 2, '0', STR_PAD_LEFT) : '00';
            
            $opens = $openH . ':' . $openM;
            $closes = $closeH . ':' . $closeM;
            
            // Générer la liste des jours concernés
            $days = [];
            $keys = array_keys($dayMap);
            $startIndex = array_search($startDay, $keys, true);
            if ($startIndex === false) {
                continue;
            }
            if ($endDay) {
                $endIndex = array_search($endDay, $keys, true);
                if ($endIndex === false) {
                    $days[] = $dayMap[$startDay];
                } else {
                    for ($i = $startIndex; $i <= $endIndex; $i++) {
                        $days[] = $dayMap[$keys[$i]];
                    }
                }
            } else {
                $days[] = $dayMap[$startDay];
            }
            
            $openingHours[] = [
                "@type" => "OpeningHoursSpecification",
                "dayOfWeek" => $days,
                "opens" => $opens,
                "closes" => $closes,
            ];
        }
        
        if (!empty($openingHours)) {
            $organizationSchema["openingHoursSpecification"] = $openingHours;
        }
    }
    
    // Logo optimisé pour Google Search Results
    // Google recommande un logo d'au moins 112x112px pour apparaître dans les résultats
    // Format optimal: carré, minimum 112x112px, maximum 1000x1000px
    $organizationSchema["image"] = $logoUrl;
    $organizationSchema["logo"] = [
        "@type" => "ImageObject",
        "url" => $logoUrl,
        // Dimensions optimales pour Google (carré recommandé)
        "width" => 192,
        "height" => 192
    ];
    
    // Ajouter aussi le favicon comme image alternative (Google peut l'utiliser)
    $faviconUrl = null;
    $seoConfigData = \App\Models\Setting::get('seo_config', '[]');
    $seoConfig = is_string($seoConfigData) ? json_decode($seoConfigData, true) : ($seoConfigData ?? []);
    
    // Utiliser le favicon 96x96 ou 192x192 comme image alternative
    $favicon192 = $seoConfig['favicon_192x192'] ?? 'favicons/favicon-192x192.png';
    if (file_exists(public_path($favicon192))) {
        $faviconUrl = url($favicon192);
    } else {
        $favicon96 = $seoConfig['favicon_96x96'] ?? 'favicons/favicon-96x96.png';
        if (file_exists(public_path($favicon96))) {
            $faviconUrl = url($favicon96);
        }
    }
    
    // Si le favicon est différent du logo, l'ajouter comme image alternative
    if ($faviconUrl && $faviconUrl !== $logoUrl) {
        if (!isset($organizationSchema["image"])) {
            $organizationSchema["image"] = [];
        }
        if (!is_array($organizationSchema["image"])) {
            $organizationSchema["image"] = [$organizationSchema["image"]];
        }
        $organizationSchema["image"][] = $faviconUrl;
    }
    
    // Ajouter les réseaux sociaux si disponibles
    $sameAs = [];
    if (setting('google_business_url')) $sameAs[] = setting('google_business_url');
    if (setting('facebook_url')) $sameAs[] = setting('facebook_url');
    if (setting('instagram_url')) $sameAs[] = setting('instagram_url');
    if (setting('linkedin_url')) $sameAs[] = setting('linkedin_url');
    if (!empty($sameAs)) {
        $organizationSchema["sameAs"] = $sameAs;
    }
    
    // Ajouter les reviews directement dans l'organisation (fusion pour éviter les doublons)
    if (!empty($reviewItems)) {
        $organizationSchema["aggregateRating"] = [
            "@type" => "AggregateRating",
            "ratingValue" => number_format($averageRating, 1),
            "reviewCount" => $totalReviews ?? $reviews->count(),
            "bestRating" => "5",
            "worstRating" => "1"
        ];
        $organizationSchema["review"] = $reviewItems;
    }
    
    // Service Schema (si sur une page service)
    $serviceSchema = null;
    if (isset($service) && is_array($service)) {
        // Créer un provider LocalBusiness complet avec toutes les infos
        // L'adresse est OBLIGATOIRE pour LocalBusiness
        $serviceProvider = [
            "@type" => "LocalBusiness",
            "name" => $companyName,
            "url" => $companyUrl,
            "address" => $addressSchema  // OBLIGATOIRE - toujours présent
        ];
        
        // Ajouter les champs optionnels seulement s'ils ont une valeur
        if (!empty($companyPhone)) {
            $serviceProvider["telephone"] = $companyPhone;
        }
        if (!empty($companyEmail)) {
            $serviceProvider["email"] = $companyEmail;
        }
        $serviceProvider["priceRange"] = "€€";
        if ($logoUrl && $logoUrl !== url('logo/logo.png')) {
            $serviceProvider["image"] = $logoUrl;
            $serviceProvider["logo"] = $logoUrl;
        }
        
        // Ajouter les réseaux sociaux si disponibles
        if (!empty($sameAs)) {
            $serviceProvider["sameAs"] = $sameAs;
        }
        
        $serviceSchema = [
            "@context" => "https://schema.org",
            "@type" => "Service",
            "serviceType" => $service['name'] ?? '',
            "description" => $service['description'] ?? '',
            "provider" => $serviceProvider,
            "areaServed" => [
                "@type" => "Country",
                "name" => $companyCountry
            ]
        ];
    }
@endphp

{{-- Organisation Schema --}}
<script type="application/ld+json">
{!! json_encode($organizationSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>

{{-- Reviews sont maintenant incluses dans l'organisation pour éviter les doublons --}}

{{-- Service Schema --}}
@if($serviceSchema)
<script type="application/ld+json">
{!! json_encode($serviceSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endif

{{-- Breadcrumbs Schema --}}
@if(isset($breadcrumbs) && is_array($breadcrumbs) && count($breadcrumbs) > 0)
@php
    $breadcrumbItems = [];
    $position = 1;
    foreach ($breadcrumbs as $breadcrumb) {
        $breadcrumbItems[] = [
            "@type" => "ListItem",
            "position" => $position++,
            "name" => $breadcrumb['name'] ?? '',
            "item" => $breadcrumb['url'] ?? ''
        ];
    }
    
    $breadcrumbSchema = [
        "@context" => "https://schema.org",
        "@type" => "BreadcrumbList",
        "itemListElement" => $breadcrumbItems
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endif
