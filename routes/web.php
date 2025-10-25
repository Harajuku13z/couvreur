<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormControllerSimple;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\ReviewsController;

// Inclure les routes des avis
require __DIR__.'/reviews.php';

/**
 * ROUTES ULTRA-SIMPLES
 * Navigation directe, pas de AJAX compliqué
 */

// Setup Routes (no middleware)
Route::get('/setup', [ConfigController::class, 'showSetup'])->name('config.setup');
Route::post('/setup', [ConfigController::class, 'processSetup'])->name('config.setup.process');

// API Routes
Route::get('/api/track-phone-call', [FormControllerSimple::class, 'trackPhoneCall'])->name('api.track.phone');
Route::get('/api/track-form-click', [FormControllerSimple::class, 'trackFormClick'])->name('api.track.form');
Route::get('/api/track-service-click', [FormControllerSimple::class, 'trackServiceClick'])->name('api.track.service');
Route::get('/api/reviews/all', function() {
    $reviews = \App\Models\Review::where('is_active', true)
        ->orderBy('review_date', 'desc')
        ->orderBy('created_at', 'desc')
        ->get();
    
    $html = '';
    foreach ($reviews as $review) {
        $html .= '<div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">';
        $html .= '<div class="flex items-center mb-6">';
        
        // Photo de profil ou initiales
        $html .= '<div class="w-16 h-16 rounded-full overflow-hidden mr-4 flex-shrink-0">';
        if ($review->author_photo_url) {
            $html .= '<img src="' . $review->author_photo_url . '" alt="' . $review->author_name . '" class="w-full h-full object-cover">';
        } else {
            $html .= '<div class="w-full h-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-2xl">';
            $html .= $review->author_initials;
            $html .= '</div>';
        }
        $html .= '</div>';
        
        $html .= '<div class="flex-1">';
        $html .= '<div class="flex items-center justify-between mb-2">';
        $html .= '<h3 class="font-bold text-gray-900 text-lg">' . $review->author_name . '</h3>';
        
        // Badge de plateforme
        if ($review->source && $review->source !== 'manual') {
            $badgeClass = match($review->source) {
                'google' => 'bg-blue-100 text-blue-800',
                'facebook' => 'bg-blue-100 text-blue-800',
                'travaux.com' => 'bg-green-100 text-green-800',
                'pages-jaunes' => 'bg-yellow-100 text-yellow-800',
                default => 'bg-gray-100 text-gray-800'
            };
            
            $icon = match($review->source) {
                'google' => 'fab fa-google',
                'facebook' => 'fab fa-facebook',
                'travaux.com' => 'fas fa-tools',
                'pages-jaunes' => 'fas fa-book',
                default => 'fas fa-globe'
            };
            
            $html .= '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $badgeClass . '">';
            $html .= '<i class="' . $icon . ' mr-1"></i>' . ucfirst($review->source);
            $html .= '</span>';
        }
        
        $html .= '</div>';
        $html .= '<div class="flex items-center">';
        $html .= '<div class="flex text-yellow-400 mr-3">';
        for ($i = 0; $i < 5; $i++) {
            if ($i < $review->rating) {
                $html .= '<i class="fas fa-star"></i>';
            } else {
                $html .= '<i class="far fa-star"></i>';
            }
        }
        $html .= '</div>';
        $html .= '<span class="text-sm text-gray-500">' . $review->rating . '/5</span>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<p class="text-gray-600 leading-relaxed mb-4">"' . \Str::limit($review->review_text, 150) . '"</p>';
        
        $html .= '<div class="flex items-center justify-between text-sm text-gray-500">';
        $html .= '<div class="flex items-center">';
        $html .= '<i class="fas fa-clock mr-2"></i>';
        if ($review->review_date) {
            $html .= '<span>' . $review->review_date->diffForHumans() . '</span>';
        } else {
            $html .= '<span>' . $review->created_at->diffForHumans() . '</span>';
        }
        $html .= '</div>';
        
        if ($review->is_verified) {
            $html .= '<div class="flex items-center text-green-600">';
            $html .= '<i class="fas fa-check-circle mr-1"></i>';
            $html .= '<span class="text-xs">Vérifié</span>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
    }
    
    return response()->json([
        'success' => true,
        'html' => $html,
        'count' => $reviews->count()
    ]);
});

// Main routes (protected by setup check)
Route::middleware(['check.setup'])->group(function () {
    // ===== FORMULAIRE (SIMPLE) =====
    Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/devis-gratuit', function() {
        return redirect()->route('form.step', ['step' => 'propertyType']);
    })->name('devis.gratuit');
    // Route de succès AVANT les routes avec paramètres
    Route::get('/form/success', [FormControllerSimple::class, 'success'])->name('form.success');
    // Route pour tous les avis
    Route::get('/avis', [FormControllerSimple::class, 'allReviews'])->name('reviews.all');
    Route::post('/avis', [FormControllerSimple::class, 'storeReview'])->name('reviews.store');
    // Route pour nos réalisations
    Route::get('/nos-realisations', [App\Http\Controllers\PortfolioController::class, 'index'])->name('portfolio.index');
    Route::get('/nos-realisations/{slug}', [App\Http\Controllers\PortfolioController::class, 'show'])->name('portfolio.show');
    // Route pour tracking des appels téléphoniques
    Route::post('/track-phone-call', [FormControllerSimple::class, 'trackPhoneCall'])->name('track.phone');
    // Routes génériques avec paramètres
    Route::get('/form/{step}', [FormControllerSimple::class, 'showStep'])->name('form.step');
    Route::post('/form/{step}/submit', [FormControllerSimple::class, 'submitStep'])->name('form.submit');
    Route::get('/form/{step}/previous', [FormControllerSimple::class, 'previousStep'])->name('form.previous');
    
    // Redirection pour les accès GET aux routes submit
    Route::get('/form/{step}/submit', function($step) {
        return redirect()->route('form.step', $step);
    });

    // ===== ADMIN =====
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/login', [AdminController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminController::class, 'authenticate'])->name('authenticate');
        Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
        
        Route::middleware(['admin.auth'])->group(function () {
            Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
            Route::get('/submissions', [AdminController::class, 'submissions'])->name('submissions');
            Route::get('/abandoned-submissions', [AdminController::class, 'abandonedSubmissions'])->name('abandoned-submissions');
            Route::get('/submissions/{id}', [AdminController::class, 'showSubmission'])->name('submission.show');
            Route::get('/abandoned-submissions/{id}', [AdminController::class, 'showAbandonedSubmission'])->name('abandoned-submission.show');
            Route::get('/export/submissions', [AdminController::class, 'exportSubmissions'])->name('export.submissions');
            Route::get('/export/abandoned-submissions', [AdminController::class, 'exportAbandonedSubmissions'])->name('export.abandoned-submissions');
            Route::get('/statistics', [AdminController::class, 'statistics'])->name('statistics');
            Route::get('/phone-calls', [AdminController::class, 'phoneCalls'])->name('phone-calls');
            // ===== SETTINGS =====
            // Routes déplacées en dehors du groupe admin pour accès sans authentification
            // ===== ADS ADMIN =====
            Route::post('/ads/create-manual', [App\Http\Controllers\AdAdminController::class, 'createManual'])->name('ads.create.manual');
            Route::post('/ads/remove-duplicates', [App\Http\Controllers\AdAdminController::class, 'removeDuplicates'])->name('ads.remove.duplicates');
            Route::post('/ads/{ad}/publish', [App\Http\Controllers\AdAdminController::class, 'publish'])->name('ads.publish');
            Route::post('/ads/{ad}/archive', [App\Http\Controllers\AdAdminController::class, 'archive'])->name('ads.archive');
            Route::delete('/ads/{ad}', [App\Http\Controllers\AdAdminController::class, 'destroy'])->name('ads.destroy');
            
            // ===== CITIES CRUD =====
            Route::get('/cities', [App\Http\Controllers\CityController::class, 'index'])->name('cities.index');
            Route::post('/cities', [App\Http\Controllers\CityController::class, 'store'])->name('cities.store');
            Route::put('/cities/{city}', [App\Http\Controllers\CityController::class, 'update'])->name('cities.update');
            Route::delete('/cities/{city}', [App\Http\Controllers\CityController::class, 'destroy'])->name('cities.destroy');
            Route::delete('/cities', [App\Http\Controllers\CityController::class, 'destroyAll'])->name('cities.destroy.all');
            Route::post('/cities/import/department', [App\Http\Controllers\CityController::class, 'importByDepartment'])->name('cities.import.department');
            Route::post('/cities/import/region', [App\Http\Controllers\CityController::class, 'importByRegion'])->name('cities.import.region');
            Route::post('/cities/import/radius', [App\Http\Controllers\CityController::class, 'importByRadius'])->name('cities.import.radius');
            Route::post('/cities/import/json', [App\Http\Controllers\CityController::class, 'importFromJson'])->name('cities.import.json');
            
            // ===== CITIES FAVORITES & AJAX =====
            Route::post('/cities/{city}/toggle-favorite', [App\Http\Controllers\CityController::class, 'toggleFavorite'])->name('cities.toggle-favorite');
            Route::get('/cities/ajax/get-cities', [App\Http\Controllers\CityController::class, 'getCities'])->name('cities.ajax.get-cities');
            Route::get('/cities/departments', [App\Http\Controllers\CityController::class, 'getDepartments'])->name('cities.departments');
            
            // ===== ADS GENERATION PAGE =====
            Route::get('/ads/generation', [App\Http\Controllers\Admin\AdGenerationPageController::class, 'index'])->name('ads.generation');
            Route::post('/ads/generation', [App\Http\Controllers\Admin\AdGenerationPageController::class, 'generate'])->name('ads.generation.generate');
            Route::get('/ads/generation/favorite-cities', [App\Http\Controllers\Admin\AdGenerationPageController::class, 'getFavoriteCities'])->name('ads.generation.favorite-cities');
            Route::get('/ads/generation/cities-by-region', [App\Http\Controllers\Admin\AdGenerationPageController::class, 'getCitiesByRegion'])->name('ads.generation.cities-by-region');
            
            // ===== ADS CREATION PAGES =====
            // Service + Villes
            Route::get('/ads/service-cities', [App\Http\Controllers\Admin\ServiceCitiesController::class, 'index'])->name('ads.service-cities');
            Route::post('/ads/service-cities', [App\Http\Controllers\Admin\ServiceCitiesController::class, 'generate'])->name('ads.service-cities.generate');
            Route::get('/ads/service-cities/favorite-cities', [App\Http\Controllers\Admin\ServiceCitiesController::class, 'getFavoriteCities'])->name('ads.service-cities.favorite-cities');
            Route::get('/ads/service-cities/cities-by-region', [App\Http\Controllers\Admin\ServiceCitiesController::class, 'getCitiesByRegion'])->name('ads.service-cities.cities-by-region');
            
            // Mot-clé + Villes
            Route::get('/ads/keyword-cities', [App\Http\Controllers\Admin\KeywordCitiesController::class, 'index'])->name('ads.keyword-cities');
            Route::post('/ads/keyword-cities', [App\Http\Controllers\Admin\KeywordCitiesController::class, 'generate'])->name('ads.keyword-cities.generate');
            Route::get('/ads/keyword-cities/favorite-cities', [App\Http\Controllers\Admin\KeywordCitiesController::class, 'getFavoriteCities'])->name('ads.keyword-cities.favorite-cities');
            Route::get('/ads/keyword-cities/cities-by-region', [App\Http\Controllers\Admin\KeywordCitiesController::class, 'getCitiesByRegion'])->name('ads.keyword-cities.cities-by-region');
            
            // Création manuelle
            Route::get('/ads/manual', [App\Http\Controllers\Admin\ManualAdController::class, 'index'])->name('ads.manual');
            Route::post('/ads/manual', [App\Http\Controllers\Admin\ManualAdController::class, 'store'])->name('ads.manual.store');
            Route::get('/ads/manual/favorite-cities', [App\Http\Controllers\Admin\ManualAdController::class, 'getFavoriteCities'])->name('ads.manual.favorite-cities');
            Route::get('/ads/manual/cities-by-region', [App\Http\Controllers\Admin\ManualAdController::class, 'getCitiesByRegion'])->name('ads.manual.cities-by-region');

            // ===== GENERATION ENDPOINTS =====
            // Routes articles supprimées - système refait de zéro

            // ===== ANNONCES =====
            Route::get('/ads', [App\Http\Controllers\AdAdminController::class, 'index'])->name('admin.ads.index');
            Route::post('/ads/generate/service-cities', [App\Http\Controllers\AdGenerationController::class, 'generateByServiceCities'])->name('ads.generate.service-cities');
            Route::post('/ads/generate/keyword-cities', [App\Http\Controllers\AdGenerationController::class, 'generateByKeywordCities'])->name('ads.generate.keyword-cities');
            Route::post('/ads/generate/seo-articles', [App\Http\Controllers\AdGenerationController::class, 'generateSeoArticles'])->name('ads.generate.seo-articles');
            Route::post('/ads/create/from-seo', [App\Http\Controllers\AdGenerationController::class, 'createPagesFromSeo'])->name('ads.create.from-seo');
            Route::get('/ads/jobs/{job}', [App\Http\Controllers\AdGenerationController::class, 'jobStatus'])->name('ads.jobs.show');

            // ===== ARTICLES =====
            // ===== ARTICLES (NOUVEAU SYSTÈME) =====
            Route::get('/articles', [App\Http\Controllers\Admin\ArticleController::class, 'index'])->name('articles.index');
            Route::get('/articles/generate', [App\Http\Controllers\Admin\ArticleController::class, 'generate'])->name('articles.generate');
            Route::get('/articles/create', [App\Http\Controllers\Admin\ArticleController::class, 'create'])->name('articles.create');
            Route::post('/articles', [App\Http\Controllers\Admin\ArticleController::class, 'store'])->name('articles.store');
            Route::delete('/articles', [App\Http\Controllers\Admin\ArticleController::class, 'destroyAll'])->name('articles.destroy-all');
            Route::get('/articles/{article}', [App\Http\Controllers\Admin\ArticleController::class, 'show'])->name('articles.show');
            Route::get('/articles/{article}/edit', [App\Http\Controllers\Admin\ArticleController::class, 'edit'])->name('articles.edit');
            Route::put('/articles/{article}', [App\Http\Controllers\Admin\ArticleController::class, 'update'])->name('articles.update');
            Route::delete('/articles/{article}', [App\Http\Controllers\Admin\ArticleController::class, 'destroy'])->name('articles.destroy');
            
            // Routes pour génération IA
            Route::post('/articles/generate-titles', [App\Http\Controllers\Admin\ArticleController::class, 'generateTitles'])->name('articles.generate-titles');
            Route::post('/articles/generate-content', [App\Http\Controllers\Admin\ArticleController::class, 'generateContent'])->name('articles.generate-content');
            Route::post('/articles/upload-image', [App\Http\Controllers\Admin\ArticleController::class, 'uploadImage'])->name('articles.upload-image');
            Route::post('/articles/create-from-titles', [App\Http\Controllers\Admin\ArticleController::class, 'createFromTitles'])->name('articles.create-from-titles');
            
            // ===== IMAGE GENERATION =====
            Route::post('/generate-image', [App\Http\Controllers\ImageGenerationController::class, 'generateImage'])->name('generate.image');

            // ===== SEO TEMPLATES =====
            Route::get('/seo/templates', [App\Http\Controllers\SeoTemplateController::class, 'index'])->name('seo.templates.index');
            Route::post('/seo/templates', [App\Http\Controllers\SeoTemplateController::class, 'store'])->name('seo.templates.store');
            
            // ===== CONFIGURATION DE LA PAGE D'ACCUEIL =====
            Route::get('/homepage', [ConfigController::class, 'editHomepage'])->name('homepage.edit');
            Route::post('/homepage', [ConfigController::class, 'updateHomepage'])->name('homepage.update');
            Route::post('/homepage/generate-ai', [ConfigController::class, 'generateHomepageContentAI'])->name('homepage.generate-ai');
            Route::post('/homepage/generate-all-ai', [ConfigController::class, 'generateAllHomepageContentAI'])->name('homepage.generate-all-ai');
            
            // ===== GESTION DES AVIS =====
            // Les routes des avis sont maintenant dans routes/reviews.php
        });
    });

    // ===== CONFIGURATION =====
    Route::prefix('config')->name('config.')->middleware(['admin.auth'])->group(function () {
        Route::get('/', [ConfigController::class, 'index'])->name('index');
        Route::post('/company', [ConfigController::class, 'updateCompany'])->name('update.company');
        Route::post('/branding', [ConfigController::class, 'updateBranding'])->name('update.branding');
        Route::post('/email', [ConfigController::class, 'updateEmail'])->name('update.email');
        Route::post('/portfolio', [ConfigController::class, 'updatePortfolio'])->name('update.portfolio');
        Route::post('/social', [ConfigController::class, 'updateSocial'])->name('update.social');
        
        Route::post('/test-email', [ConfigController::class, 'testEmail'])->name('test.email');
    Route::post('/update-email-template', [ConfigController::class, 'updateEmailTemplate'])->name('update.email-template');
    Route::post('/test-email-template', [ConfigController::class, 'testEmailTemplate'])->name('test.email-template');
        Route::get('/reset', [ConfigController::class, 'showReset'])->name('reset');
        Route::post('/reset', [ConfigController::class, 'resetConfiguration'])->name('reset.confirm');
    });

    // ===== PORTFOLIO PUBLIC =====
    Route::get('/portfolio', [App\Http\Controllers\PortfolioController::class, 'index'])->name('portfolio.public');
    
    // ===== PORTFOLIO ADMIN =====
    Route::prefix('admin/portfolio')->name('portfolio.admin.')->middleware(['admin.auth'])->group(function () {
        Route::get('/', [ConfigController::class, 'portfolioIndex'])->name('index');
        Route::get('/data', [ConfigController::class, 'getPortfolioData'])->name('data');
        Route::get('/edit/{id}', [ConfigController::class, 'editPortfolioItem'])->name('edit');
        Route::post('/add', [ConfigController::class, 'addPortfolioItem'])->name('add');
        Route::post('/update/{id}', [ConfigController::class, 'updatePortfolioItem'])->name('update');
        Route::delete('/delete/{id}', [ConfigController::class, 'deletePortfolioItem'])->name('delete');
        Route::post('/reorder', [ConfigController::class, 'reorderPortfolio'])->name('reorder');
    });
    
    // Route simple pour l'upload portfolio (sans middleware complexe)
    Route::post('/admin/portfolio/upload', [ConfigController::class, 'addPortfolioItem'])->name('portfolio.upload.simple');
    
    // Route ultra-simple pour l'upload (sans aucun middleware)
    Route::post('/upload-portfolio', [ConfigController::class, 'addPortfolioItem'])->name('portfolio.upload.ultra');
    
    // ===== SEO ROUTES =====
    Route::prefix('admin/seo')->name('admin.seo.')->middleware(['admin.auth'])->group(function () {
        Route::get('/', [App\Http\Controllers\SeoController::class, 'index'])->name('index');
        Route::get('/test', function() { return view('admin.seo.test'); })->name('test.page');
        Route::post('/update', [App\Http\Controllers\SeoController::class, 'update'])->name('update');
        Route::post('/update-page', [App\Http\Controllers\SeoController::class, 'updatePage'])->name('update-page');
        Route::get('/test-seo', [App\Http\Controllers\SeoController::class, 'testSeo'])->name('test');
    });
    
    // Routes publiques SEO
    Route::get('/sitemap.xml', [App\Http\Controllers\SeoController::class, 'generateSitemap'])->name('sitemap.xml');
    Route::get('/robots.txt', [App\Http\Controllers\SeoController::class, 'generateRobots'])->name('robots.txt');
    Route::get('/manifest.json', [App\Http\Controllers\SeoController::class, 'generateManifest'])->name('manifest.json');
    
    // ===== PUBLIC PAGES =====
    Route::get('/annonces', [App\Http\Controllers\AdPublicController::class, 'index'])->name('ads.index');
    Route::get('/annonces/{slug}', [App\Http\Controllers\AdPublicController::class, 'show'])->name('ads.show');
    // ===== ARTICLES PUBLICS (NOUVEAU SYSTÈME) =====
    Route::get('/blog', [App\Http\Controllers\ArticleController::class, 'index'])->name('blog.index');
    Route::get('/blog/{article}', [App\Http\Controllers\ArticleController::class, 'show'])->name('blog.show');
    
    // Route de test ultra-simple
    Route::post('/test-upload', [ConfigController::class, 'testUpload'])->name('portfolio.test.upload');
    
    // ===== CONFIGURATION IA =====
    Route::post('/config/update/ai', [ConfigController::class, 'updateAI'])->name('config.update.ai');
    Route::post('/config/test-chatgpt', [ConfigController::class, 'testChatGPT'])->name('config.test.chatgpt');
    
    // ===== SERVICES =====
    // Services publics
    Route::get('/services', [ServicesController::class, 'publicIndex'])->name('services.index');
    Route::get('/services/{slug}', [ServicesController::class, 'show'])->name('services.show');
    
    // ===== PAGES LÉGALES =====
    Route::get('/mentions-legales', [App\Http\Controllers\LegalController::class, 'mentionsLegales'])->name('legal.mentions');
    Route::get('/politique-confidentialite', [App\Http\Controllers\LegalController::class, 'politiqueConfidentialite'])->name('legal.privacy');
    Route::get('/cgv', [App\Http\Controllers\LegalController::class, 'cgv'])->name('legal.cgv');
    
    // ===== ADMIN PAGES LÉGALES =====
    Route::prefix('admin/legal')->name('admin.legal.')->middleware(['admin.auth'])->group(function () {
        Route::get('/config', [App\Http\Controllers\LegalAdminController::class, 'index'])->name('config');
        Route::post('/config', [App\Http\Controllers\LegalAdminController::class, 'update'])->name('config.update');
    });
    
    // Services admin
    Route::prefix('admin/services')->name('services.admin.')->middleware(['admin.auth'])->group(function () {
        Route::get('/', [ServicesController::class, 'index'])->name('index');
        Route::get('/create', [ServicesController::class, 'create'])->name('create');
        Route::post('/', [ServicesController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [ServicesController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ServicesController::class, 'update'])->name('update');
        Route::delete('/{id}', [ServicesController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/regenerate', [ServicesController::class, 'regenerate'])->name('regenerate');
        Route::post('/regenerate-all', [ServicesController::class, 'regenerateAll'])->name('regenerate.all');
        Route::post('/generate-content', [ServicesController::class, 'generateContent'])->name('generate.content');
        Route::get('/debug/{slug}', [ServicesController::class, 'debug'])->name('debug');
        Route::post('/force-regenerate/{slug}', [ServicesController::class, 'forceRegenerate'])->name('force.regenerate');
        Route::post('/fix-images/{slug}', [ServicesController::class, 'fixImages'])->name('fix.images');
        Route::post('/clean-duplicates', [ServicesController::class, 'cleanExistingServices'])->name('clean.duplicates');
    });
});













