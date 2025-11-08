{{-- Schema.org Structured Data --}}
@php
    $companyName = setting('company_name', 'Votre Entreprise');
    $companyDescription = setting('company_description', '');
    $companyPhone = setting('company_phone_raw', '');
    $companyEmail = setting('company_email', '');
    $companyAddress = setting('company_address', '');
    $companyCity = setting('company_city', '');
    $companyPostalCode = setting('company_postal_code', '');
    $companyCountry = setting('company_country', 'France');
    $companyUrl = url('/');
    
    // Organisation Schema
    $companyLogo = setting('company_logo');
    $logoUrl = $companyLogo ? (strpos($companyLogo, 'http') === 0 ? $companyLogo : url($companyLogo)) : url('logo/logo.png');
    
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
    
    // Construire le schéma d'organisation (UN SEUL LocalBusiness avec toutes les infos)
    // L'adresse est OBLIGATOIRE pour LocalBusiness
    $organizationSchema = [
        "@context" => "https://schema.org",
        "@type" => "LocalBusiness",
        "name" => $companyName,
        "url" => $companyUrl,
        "address" => $addressSchema  // OBLIGATOIRE - toujours présent
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
    if ($logoUrl && $logoUrl !== url('logo/logo.png')) { // Éviter le logo par défaut
        $organizationSchema["image"] = $logoUrl;
        $organizationSchema["logo"] = $logoUrl;  // Logo pour affichage dans Google (requis pour favicon dans résultats)
    }
    
    // Ajouter les réseaux sociaux si disponibles
    $sameAs = [];
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
    
    // FAQ Schema (si FAQ présente)
    $faqSchema = null;
    if (isset($faqs) && is_array($faqs) && count($faqs) > 0) {
        $faqItems = [];
        foreach ($faqs as $faq) {
            if (isset($faq['question']) && isset($faq['answer'])) {
                $faqItems[] = [
                    "@type" => "Question",
                    "name" => $faq['question'],
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => $faq['answer']
                    ]
                ];
            }
        }
        
        if (!empty($faqItems)) {
            $faqSchema = [
                "@context" => "https://schema.org",
                "@type" => "FAQPage",
                "mainEntity" => $faqItems
            ];
        }
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

{{-- FAQ Schema --}}
@if($faqSchema)
<script type="application/ld+json">
{!! json_encode($faqSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
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

