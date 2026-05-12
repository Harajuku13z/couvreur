<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\City;
use App\Models\Review;
use App\Models\Setting;
use App\Helpers\SeoHelper;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class AdPublicController extends Controller
{
    public function index()
    {
        $ads = Ad::where('status', 'published')
            ->with('city')
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->paginate(12);
        
        // Définir la page courante pour le SEO
        $currentPage = 'ads';
        
        return view('ads.index', compact('ads', 'currentPage'));
    }

    public function show(string $slug)
    {
        // Chercher l'annonce par slug avec relation template
        $ad = Ad::with('template', 'city')->where('slug', $slug)->firstOrFail();
        $auditService = app(\App\Services\AdSeoAuditService::class);

        if ($ad->status === 'archived') {
            $redirectUrl = $ad->template?->service_slug
                ? route('services.show', $ad->template->service_slug)
                : route('services.index');

            return redirect($redirectUrl, 301);
        }

        if ($ad->status !== 'published') {
            abort(404);
        }
        
        $cityModel = $ad->city;
        
        if (!$cityModel) {
            abort(404, 'Ville non trouvée');
        }
        
        // Variables pour le SEO - utiliser getMetaForCity si template existe
        $currentPage = 'ads';
        $canonicalUrl = SeoHelper::normalizeAbsoluteCanonicalUrl(route('ads.show', $ad->slug));
        
        // Récupérer l'image du template ou de l'annonce
        $featuredImage = null;
        $pageTitle = null;
        $pageDescription = null;
        $pageKeywords = null;
        $ogTitle = null;
        $ogDescription = null;
        $twitterTitle = null;
        $twitterDescription = null;
        
        // Si l'annonce a un template, utiliser getMetaForCity pour les métadonnées personnalisées
        if ($ad->template_id && $ad->template) {
            $metaForCity = $ad->template->getMetaForCity($cityModel);
            $baseTitle = $metaForCity['meta_title'] ?? $ad->meta_title ?? $ad->title ?? 'Service professionnel';
            $pageDescription = $metaForCity['meta_description'] ?? $ad->meta_description ?? 'Service professionnel à ' . $cityModel->name . '. Devis gratuit et intervention rapide.';
            $pageKeywords = $metaForCity['meta_keywords'] ?? $ad->meta_keywords ?? '';
            $ogTitle = $metaForCity['og_title'] ?? $baseTitle;
            $ogDescription = $metaForCity['og_description'] ?? $pageDescription;
            $twitterTitle = $metaForCity['twitter_title'] ?? $ogTitle ?? $baseTitle;
            $twitterDescription = $metaForCity['twitter_description'] ?? $ogDescription ?? $pageDescription;
            
            // Ajouter le code postal au titre si pas déjà présent
            $postalCode = $cityModel->postal_code ?? '';
            if ($postalCode && strpos($baseTitle, $postalCode) === false) {
                // Extraire le mot-clé principal (keyword de l'annonce ou titre)
                $keyword = $ad->keyword ?? $ad->title ?? '';
                // Ajouter le code postal au titre
                $pageTitle = rtrim($baseTitle, '.') . ' ' . $postalCode;
            } else {
                $pageTitle = $baseTitle;
            }
            
            // Récupérer l'image du template
            $featuredImage = $ad->template->featured_image ?? null;
        } else {
            // Utiliser les métadonnées de l'annonce directement
            $baseTitle = $ad->meta_title ?? $ad->title ?? 'Service professionnel';
            $pageDescription = $ad->meta_description ?? 'Service professionnel à ' . $cityModel->name . '. Devis gratuit et intervention rapide.';
            $pageKeywords = $ad->meta_keywords ?? '';
            
            // Ajouter le code postal au titre si pas déjà présent
            $postalCode = $cityModel->postal_code ?? '';
            if ($postalCode && strpos($baseTitle, $postalCode) === false) {
                $pageTitle = rtrim($baseTitle, '.') . ' ' . $postalCode;
            } else {
                $pageTitle = $baseTitle;
            }
            
            $ogTitle = $pageTitle;
            $ogDescription = $pageDescription;
            $twitterTitle = $pageTitle;
            $twitterDescription = $pageDescription;
            
            // Pas d'image si pas de template
            $featuredImage = null;
        }
        
        // Extraire le mot-clé principal pour les alt des images
        $mainKeyword = $ad->keyword ?? $ad->title ?? '';
        if (empty($mainKeyword) && $ad->template) {
            $mainKeyword = $ad->template->service_name ?? '';
        }
        $serviceName = $ad->template->service_name ?? $mainKeyword ?? $ad->title ?? 'Service';
        
        // Ajouter le code postal au mot-clé si présent
        $postalCode = $cityModel->postal_code ?? '';
        if ($postalCode && !empty($mainKeyword)) {
            $mainKeywordWithPostalCode = $mainKeyword . ' ' . $postalCode;
        } else {
            $mainKeywordWithPostalCode = $mainKeyword;
        }
        
        // Récupérer les portfolio items (réalisations)
        $portfolioData = \App\Models\Setting::get('portfolio_items', '[]');
        $portfolioItems = is_string($portfolioData) ? json_decode($portfolioData, true) : ($portfolioData ?? []);
        if (!is_array($portfolioItems)) {
            $portfolioItems = [];
        }
        // Filtrer seulement les éléments visibles
        $portfolioItems = array_values(array_filter(array_map(function ($item) {
            if (!is_array($item)) {
                return null;
            }

            $rawImages = $item['images'] ?? [];
            if (is_string($rawImages) && $rawImages !== '') {
                $rawImages = [$rawImages];
            } elseif (!is_array($rawImages)) {
                $rawImages = [];
            }

            $images = array_values(array_filter(array_map(function ($image) {
                $image = trim((string) $image);
                if ($image === '') {
                    return null;
                }

                return str_starts_with($image, 'http') ? $image : asset(ltrim($image, '/'));
            }, $rawImages)));

            $item['images'] = $images;
            $item['slug'] = $item['slug'] ?? Str::slug($item['title'] ?? 'realisation');

            return $item;
        }, $portfolioItems), function($item) {
            return is_array($item) && (isset($item['is_visible']) ? $item['is_visible'] : true);
        }));
        
        $pageTitle = $this->buildAdPageTitle($ad, $cityModel, $serviceName, $pageTitle);
        $pageDescription = $this->buildAdPageDescription($ad, $cityModel, $serviceName, $pageDescription);
        $ogTitle = $pageTitle;
        $ogDescription = $pageDescription;
        $twitterTitle = $pageTitle;
        $twitterDescription = $pageDescription;

        // Générer les mots-clés étendus pour le SEO
        $extendedKeywords = $this->generateExtendedKeywords($mainKeyword, $cityModel, $pageKeywords);
        $seoAudit = $auditService->analyze($ad);
        $robotsMeta = $auditService->shouldAutoNoindex()
            ? $seoAudit['recommended_robots']
            : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
        $shouldEmitCanonical = !str_contains(Str::lower($robotsMeta), 'noindex');

        $pageImage = $featuredImage ? asset($featuredImage) : null;
        $pageType = 'website';
        
        // Récupérer des annonces similaires
        $relatedAds = Ad::where('city_id', $ad->city_id)
            ->where('id', '!=', $ad->id)
            ->where('status', 'published')
            ->take(3)
            ->get();

        $servicePage = null;
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        if (is_array($services) && $ad->template?->service_slug) {
            $servicePage = collect($services)->first(function ($service) use ($ad) {
                return is_array($service)
                    && ($service['slug'] ?? null) === $ad->template->service_slug
                    && ($service['is_visible'] ?? true);
            });
        }
        $serviceUrl = $servicePage
            ? route('services.show', $servicePage['slug'])
            : route('services.index');

        $nearbyAds = Ad::with('city')
            ->where('status', 'published')
            ->where('id', '!=', $ad->id)
            ->when($ad->template_id, fn ($query) => $query->where('template_id', $ad->template_id), function ($query) use ($ad) {
                $query->where('keyword', $ad->keyword);
            })
            ->whereHas('city', function ($query) use ($cityModel) {
                $query->where('region', $cityModel->region);
            })
            ->limit(4)
            ->get();

        $faqItems = [
            [
                'question' => "Comment obtenir un devis pour {$mainKeywordWithPostalCode} ?",
                'answer' => "Nous préparons un devis gratuit après un premier échange sur votre besoin à {$cityModel->name}. Selon le projet, une visite technique peut être organisée pour affiner le chiffrage.",
            ],
            [
                'question' => "Intervenez-vous uniquement à {$cityModel->name} ?",
                'answer' => "Non. Cette page cible {$cityModel->name}, mais l'équipe intervient aussi dans les communes voisines de la région pour les demandes similaires.",
            ],
            [
                'question' => "Quels délais pour une intervention {$mainKeyword} ?",
                'answer' => "Les délais dépendent du niveau d'urgence, des matériaux et de la saison. Pour les demandes courantes, nous revenons généralement sous 24 heures avec une première réponse.",
            ],
        ];

        $reviewSummary = Cache::remember('ads_public_review_summary', 600, function () {
            $items = Review::where('is_active', true)
                ->orderBy('review_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->take(4)
                ->get();

            return [
                'items' => $items,
                'average' => (float) (Review::where('is_active', true)->avg('rating') ?? 0),
                'count' => (int) Review::where('is_active', true)->count(),
            ];
        });

        $activeReviews = $reviewSummary['items'];
        $averageReviewRating = $reviewSummary['average'];
        $reviewCount = $reviewSummary['count'];

        $breadcrumbs = [
            ['name' => 'Accueil', 'url' => route('home')],
            ['name' => 'Services', 'url' => route('services.index')],
            ['name' => $ad->title ?? $serviceName, 'url' => $canonicalUrl],
        ];
        $renderedContentHtml = $this->optimizeContentHtml($ad->content_html);
        
        return view('ads.show', compact(
            'ad', 
            'cityModel', 
            'currentPage', 
            'pageTitle', 
            'pageDescription', 
            'pageKeywords', 
            'ogTitle', 
            'ogDescription', 
            'twitterTitle', 
            'twitterDescription', 
            'pageImage', 
            'pageType', 
            'relatedAds', 
            'featuredImage',
            'portfolioItems',
            'mainKeyword',
            'mainKeywordWithPostalCode',
            'extendedKeywords',
            'seoAudit',
            'robotsMeta',
            'canonicalUrl',
            'shouldEmitCanonical',
            'servicePage',
            'serviceUrl',
            'nearbyAds',
            'faqItems',
            'activeReviews',
            'averageReviewRating',
            'reviewCount',
            'breadcrumbs',
            'renderedContentHtml',
            'serviceName'
        ));
    }
    
    /**
     * Générer des mots-clés étendus pour le SEO
     */
    protected function generateExtendedKeywords($mainKeyword, $city, $existingKeywords = '')
    {
        $keywords = [];
        
        // Ajouter les mots-clés existants
        if (!empty($existingKeywords)) {
            $keywords = array_merge($keywords, array_map('trim', explode(',', $existingKeywords)));
        }
        
        // Ajouter des variations avec la ville et le code postal
        $cityName = $city->name ?? '';
        $postalCode = $city->postal_code ?? '';
        $department = $city->department ?? '';
        $region = $city->region ?? '';
        
        if (!empty($mainKeyword)) {
            $keywords[] = $mainKeyword;
            if ($cityName) {
                $keywords[] = $mainKeyword . ' ' . $cityName;
                $keywords[] = $cityName . ' ' . $mainKeyword;
                $keywords[] = 'devis ' . $mainKeyword . ' ' . $cityName;
                $keywords[] = 'entreprise ' . $mainKeyword . ' ' . $cityName;
            }
            if ($postalCode) {
                $keywords[] = $mainKeyword . ' ' . $postalCode;
                $keywords[] = $postalCode . ' ' . $mainKeyword;
            }
            if ($department) {
                $keywords[] = $mainKeyword . ' ' . $department;
                $keywords[] = 'artisan ' . $mainKeyword . ' ' . $department;
            }
            if ($region) {
                $keywords[] = $mainKeyword . ' ' . $region;
            }
            if ($cityName && $postalCode) {
                $keywords[] = $mainKeyword . ' ' . $cityName . ' ' . $postalCode;
                $keywords[] = 'Entreprise ' . $mainKeyword . ' ' . $cityName;
                $keywords[] = 'Professionnel ' . $mainKeyword . ' ' . $postalCode;
                $keywords[] = 'Devis ' . $mainKeyword . ' ' . $cityName;
                $keywords[] = 'Prix ' . $mainKeyword . ' ' . $postalCode;
                $keywords[] = 'Tarif ' . $mainKeyword . ' ' . $cityName;
            }
        }
        
        // Retourner des mots-clés uniques
        return array_unique(array_filter($keywords));
    }

    protected function buildAdPageTitle(Ad $ad, City $city, string $serviceName, ?string $existingTitle = null): string
    {
        $cityName = trim((string) $city->name);
        $postalCode = trim((string) ($city->postal_code ?? ''));
        $cityLabel = trim($cityName . ($postalCode ? ' (' . $postalCode . ')' : ''));

        $title = trim((string) ($existingTitle ?: $ad->meta_title ?: $ad->title ?: $serviceName));

        if ($title === '' || Str::contains(Str::lower($title), 'service professionnel')) {
            $title = "{$serviceName} à {$cityLabel} | Devis gratuit";
        }

        if (!Str::contains(Str::lower($title), Str::lower($serviceName))) {
            $title = "{$serviceName} - {$title}";
        }

        if ($cityName !== '' && !Str::contains(Str::lower($title), Str::lower($cityName))) {
            $title .= ' à ' . $cityLabel;
        }

        if (!Str::contains(Str::lower($title), 'devis') && mb_strlen($title) < 56) {
            $title .= ' | Devis gratuit';
        }

        return Str::limit(trim(preg_replace('/\s+/', ' ', $title)), 68, '');
    }

    protected function buildAdPageDescription(Ad $ad, City $city, string $serviceName, ?string $existingDescription = null): string
    {
        $cityName = trim((string) $city->name);
        $postalCode = trim((string) ($city->postal_code ?? ''));
        $department = trim((string) ($city->department ?? ''));
        $location = trim($cityName . ($postalCode ? ' (' . $postalCode . ')' : ''));

        $description = trim((string) strip_tags($existingDescription ?: $ad->meta_description ?: ''));

        if ($description === '') {
            $description = "{$serviceName} à {$location}" . ($department ? " dans le département {$department}" : '') . ". Devis gratuit, intervention rapide et accompagnement sur mesure par une entreprise locale.";
        }

        if (!Str::contains(Str::lower($description), Str::lower($cityName))) {
            $description = "{$serviceName} à {$location}. {$description}";
        }

        if ($department !== '' && !Str::contains(Str::lower($description), Str::lower($department))) {
            $description .= " Intervention dans le département {$department}.";
        }

        if (!Str::contains(Str::lower($description), 'devis')) {
            $description .= ' Devis gratuit sur demande.';
        }

        return Str::limit(trim(preg_replace('/\s+/', ' ', $description)), 165, '');
    }

    protected function optimizeContentHtml(?string $contentHtml): string
    {
        $contentHtml = trim((string) $contentHtml);

        if ($contentHtml === '') {
            return '<p>Contenu en cours de chargement...</p>';
        }

        $contentHtml = SeoHelper::stripFinancingContent($contentHtml);
        $contentHtml = $this->stripGeneratedPracticalInfoSection($contentHtml);
        $contentHtml = $this->normalizeGeneratedTextColors($contentHtml);

        $contentHtml = preg_replace_callback('/\bclass="([^"]*)"/i', function ($matches) {
            $classes = preg_split('/\s+/', trim($matches[1])) ?: [];
            $filtered = [];

            foreach ($classes as $class) {
                if ($class === '') {
                    continue;
                }

                if (preg_match('/^(grid|inline-grid|grid-cols-\d+|sm:grid-cols-\d+|md:grid-cols-\d+|lg:grid-cols-\d+|xl:grid-cols-\d+)$/', $class)) {
                    continue;
                }

                $filtered[] = $class;
            }

            if (!in_array('space-y-6', $filtered, true)) {
                $filtered[] = 'space-y-6';
            }

            return 'class="' . implode(' ', array_values(array_unique($filtered))) . '"';
        }, $contentHtml) ?? $contentHtml;

        return preg_replace_callback('/<img\b([^>]*)>/i', function ($matches) {
            $attributes = $matches[1];

            if (!preg_match('/\bloading=/i', $attributes)) {
                $attributes .= ' loading="lazy"';
            }

            if (!preg_match('/\bdecoding=/i', $attributes)) {
                $attributes .= ' decoding="async"';
            }

            if (!preg_match('/\bfetchpriority=/i', $attributes)) {
                $attributes .= ' fetchpriority="low"';
            }

            return '<img' . $attributes . '>';
        }, $contentHtml) ?? $contentHtml;
    }

    protected function stripGeneratedPracticalInfoSection(string $contentHtml): string
    {
        $patterns = [
            '/<section\b[^>]*>.*?<h[1-6][^>]*>\s*Informations pratiques\s*<\/h[1-6]>.*?<\/section>/is',
            '/<div\b[^>]*>\s*<h[1-6][^>]*>\s*Informations pratiques\s*<\/h[1-6]>.*?<\/div>\s*(?=<(?:section|div|h[1-6]|$))/is',
            '/<h[1-6][^>]*>\s*Informations pratiques\s*<\/h[1-6]>\s*<ul\b[^>]*>.*?<\/ul>/is',
        ];

        return preg_replace($patterns, '', $contentHtml) ?? $contentHtml;
    }

    protected function normalizeGeneratedTextColors(string $contentHtml): string
    {
        $contentHtml = preg_replace_callback('/\bclass="([^"]*)"/i', function ($matches) {
            $classes = preg_split('/\s+/', trim($matches[1])) ?: [];
            $normalized = [];

            foreach ($classes as $class) {
                if ($class === '') {
                    continue;
                }

                if (preg_match('/^(text|sm:text|md:text|lg:text|xl:text)-(gray|slate|neutral|zinc)-(400|500|600|700|800)$/', $class)) {
                    $normalized[] = preg_replace('/-(gray|slate|neutral|zinc)-(400|500|600|700|800)$/', '-gray-900', $class);
                    continue;
                }

                $normalized[] = $class;
            }

            return 'class="' . implode(' ', array_values(array_unique($normalized))) . '"';
        }, $contentHtml) ?? $contentHtml;

        $contentHtml = preg_replace(
            '/color\s*:\s*(#6b7280|#4b5563|#374151|#1f2937|rgb\(107,\s*114,\s*128\)|rgb\(75,\s*85,\s*99\)|rgb\(55,\s*65,\s*81\)|rgb\(31,\s*41,\s*55\))/i',
            'color:#111827',
            $contentHtml
        ) ?? $contentHtml;

        return $contentHtml;
    }
}
