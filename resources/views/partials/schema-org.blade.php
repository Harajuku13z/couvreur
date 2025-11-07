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
    $organizationSchema = [
        "@context" => "https://schema.org",
        "@type" => "LocalBusiness",
        "name" => $companyName,
        "description" => $companyDescription,
        "url" => $companyUrl,
        "telephone" => $companyPhone,
        "email" => $companyEmail,
        "address" => [
            "@type" => "PostalAddress",
            "streetAddress" => $companyAddress,
            "addressLocality" => $companyCity,
            "postalCode" => $companyPostalCode,
            "addressCountry" => $companyCountry
        ],
        "priceRange" => "€€",
        "image" => asset(setting('company_logo', 'images/logo.png'))
    ];
    
    // Ajouter les réseaux sociaux si disponibles
    $sameAs = [];
    if (setting('facebook_url')) $sameAs[] = setting('facebook_url');
    if (setting('instagram_url')) $sameAs[] = setting('instagram_url');
    if (setting('linkedin_url')) $sameAs[] = setting('linkedin_url');
    if (!empty($sameAs)) {
        $organizationSchema["sameAs"] = $sameAs;
    }
    
    // Reviews Schema (si sur la page d'accueil)
    $reviewsSchema = null;
    if (isset($reviews) && is_object($reviews) && method_exists($reviews, 'count') && $reviews->count() > 0 && isset($averageRating)) {
        $reviewItems = [];
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
        
        $reviewsSchema = [
            "@context" => "https://schema.org",
            "@type" => "LocalBusiness",
            "name" => $companyName,
            "aggregateRating" => [
                "@type" => "AggregateRating",
                "ratingValue" => number_format($averageRating, 1),
                "reviewCount" => $totalReviews ?? $reviews->count(),
                "bestRating" => "5",
                "worstRating" => "1"
            ],
            "review" => $reviewItems
        ];
    }
    
    // Service Schema (si sur une page service)
    $serviceSchema = null;
    if (isset($service) && is_array($service)) {
        $serviceSchema = [
            "@context" => "https://schema.org",
            "@type" => "Service",
            "serviceType" => $service['name'] ?? '',
            "description" => $service['description'] ?? '',
            "provider" => [
                "@type" => "LocalBusiness",
                "name" => $companyName
            ],
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

{{-- Reviews Schema (Rich Snippets) --}}
@if($reviewsSchema)
<script type="application/ld+json">
{!! json_encode($reviewsSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endif

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

