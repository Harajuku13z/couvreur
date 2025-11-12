<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormControllerSimple;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\ReviewsController;
use App\Http\Controllers\Admin\DevisController;
use App\Http\Controllers\Admin\FactureController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\QuotationStatsController;

// Inclure les routes des avis
require __DIR__.'/reviews.php';

// Route de test pour les icônes Font Awesome
Route::get('/test-icons', function () {
    return view('test-icons');
})->name('test.icons');

// Route de test pour le tracking des appels
Route::get('/test-phone-tracking', function () {
    return view('test-phone-tracking');
})->name('test.phone.tracking');

        /**
         * ROUTES ULTRA-SIMPLES
         * Navigation directe, pas de AJAX compliqué
         */

        // Setup Routes (no middleware)
        Route::get('/setup', [ConfigController::class, 'showSetup'])->name('config.setup');
        Route::post('/setup', [ConfigController::class, 'processSetup'])->name('config.setup.process');
        
        // Route pour exécuter le scheduler via HTTP (pour services externes comme cron-job.org)
        // Protégée par token pour la sécurité
        Route::get('/schedule/run', function (\Illuminate\Http\Request $request) {
            $token = $request->query('token');
            $configuredToken = \App\Models\Setting::where('key', 'schedule_run_token')->value('value');
            
            // Si aucun token n'est configuré, générer un token et le retourner avec instructions
            if (empty($configuredToken)) {
                $newToken = \Illuminate\Support\Str::random(32);
                \App\Models\Setting::set('schedule_run_token', $newToken, 'string', 'seo');
                
                return response()->json([
                    'message' => 'Token généré. Utilisez cette URL pour exécuter la génération d\'articles :',
                    'url' => url('/schedule/run?token=' . $newToken),
                    'token' => $newToken,
                    'instructions' => 'Configurez cette URL dans un service externe (cron-job.org, UptimeRobot, etc.) pour l\'appeler une fois par jour à l\'heure configurée. Chaque appel génère directement les articles pour toutes les villes favorites.'
                ], 200);
            }
            
            // Vérifier le token
            if (empty($token) || $token !== $configuredToken) {
                return response()->json([
                    'error' => 'Token invalide ou manquant',
                    'message' => 'Utilisez ?token=VOTRE_TOKEN dans l\'URL'
                ], 401);
            }
            
            // Exécuter directement la génération d'articles (sans vérifier l'heure)
            try {
                // Vérifier si l'automatisation est activée
                $automationEnabled = \App\Models\Setting::where('key', 'seo_automation_enabled')->value('value');
                $automationEnabled = filter_var($automationEnabled, FILTER_VALIDATE_BOOLEAN);
                if ($automationEnabled === false && $automationEnabled !== true) {
                    $automationEnabled = true; // Par défaut
                }
                
                if (!$automationEnabled) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Automatisation SEO désactivée',
                        'output' => 'L\'automatisation est désactivée dans les paramètres.'
                    ], 200);
                }
                
                // Vérifier qu'il y a des villes favorites
                $favoriteCitiesCount = \App\Models\City::where('is_favorite', true)->count();
                if ($favoriteCitiesCount === 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Aucune ville favorite',
                        'output' => 'Aucune ville favorite configurée. Marquez au moins une ville comme favorite.'
                    ], 200);
                }
                
                // Exécuter directement la génération d'articles (sans vérifier l'heure)
                // En arrière-plan pour éviter timeout
                \Artisan::call('seo:run-automations');
                $output = \Artisan::output();
                
                \Illuminate\Support\Facades\Log::info('Schedule exécuté via HTTP - Génération directe', [
                    'ip' => $request->ip(),
                    'output' => $output,
                    'timestamp' => now()->format('Y-m-d H:i:s')
                ]);
                
                // Retourner immédiatement pour éviter timeout
                return response()->json([
                    'success' => true,
                    'message' => 'Génération d\'articles lancée à ' . now()->format('Y-m-d H:i:s'),
                    'output' => substr($output, 0, 500) . (strlen($output) > 500 ? '...' : ''),
                    'note' => 'Les articles sont générés directement (mode exécution directe). Le traitement peut prendre quelques minutes.',
                    'status' => 'processing'
                ], 200);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Erreur exécution schedule via HTTP', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'error' => 'Erreur lors de l\'exécution',
                    'message' => $e->getMessage()
                ], 500);
            }
        })->name('schedule.run');

// API Routes
// Route pour tracking des appels téléphoniques (POST et GET pour compatibilité)
Route::match(['get', 'post'], '/api/track-phone-call', [FormControllerSimple::class, 'trackPhoneCall'])->name('api.track.phone');
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
    Route::get('/contact', [App\Http\Controllers\ContactController::class, 'index'])->name('contact');
    Route::post('/contact', [App\Http\Controllers\ContactController::class, 'send'])->name('contact.send');
    Route::get('/contact/success', [App\Http\Controllers\ContactController::class, 'success'])->name('contact.success');
    // Route de succès AVANT les routes avec paramètres
    Route::get('/form/success', [FormControllerSimple::class, 'success'])->name('form.success');
    // Route pour tous les avis
    Route::get('/avis', [FormControllerSimple::class, 'allReviews'])->name('reviews.all');
    Route::get('/avis/ajouter', [FormControllerSimple::class, 'createReview'])->name('reviews.create');
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

    // ===== ROUTES PUBLIQUES DEVIS =====
    Route::get('/devis/{id}/pdf/{token}', [DevisController::class, 'publicPdf'])->name('devis.public.pdf');

    // ===== ADMIN =====
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/login', [AdminController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminController::class, 'authenticate'])->name('authenticate');
        Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
        
        Route::middleware(['admin.auth'])->group(function () {
            Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
            Route::get('/submissions', [AdminController::class, 'submissions'])->name('submissions');
            Route::post('/submissions/delete-all', [AdminController::class, 'deleteAllSubmissions'])->name('submissions.delete-all');
            Route::get('/abandoned-submissions', [AdminController::class, 'abandonedSubmissions'])->name('abandoned-submissions');
            Route::get('/submissions/{id}', [AdminController::class, 'showSubmission'])->name('submission.show');
            Route::post('/submissions/{id}/mark-abandoned', [AdminController::class, 'markSubmissionAsAbandoned'])->name('submission.mark-abandoned');
            Route::get('/submissions/{id}/create-client', [AdminController::class, 'createClientFromSubmission'])->name('submission.create-client');
            Route::get('/abandoned-submissions/{id}', [AdminController::class, 'showAbandonedSubmission'])->name('abandoned-submission.show');
            Route::get('/export/submissions', [AdminController::class, 'exportSubmissions'])->name('export.submissions');
            Route::get('/export/abandoned-submissions', [AdminController::class, 'exportAbandonedSubmissions'])->name('export.abandoned-submissions');
            Route::get('/statistics', [AdminController::class, 'statistics'])->name('statistics');
            Route::get('/phone-calls', [AdminController::class, 'phoneCalls'])->name('phone-calls');
            Route::post('/phone-calls/delete-all', [AdminController::class, 'deleteAllPhoneCalls'])->name('phone-calls.delete-all');
            Route::put('/phone-calls/{id}/update-city', [AdminController::class, 'updatePhoneCallCity'])->name('phone-calls.update-city');
            Route::get('/visits', [App\Http\Controllers\VisitsController::class, 'index'])->name('visits');
            Route::get('/visits/data', [App\Http\Controllers\VisitsController::class, 'getVisitsData'])->name('visits.data');
            
            // ===== DEVIS & FACTURATION =====
            Route::prefix('quotations')->name('quotations.')->group(function () {
                Route::get('/dashboard', [QuotationStatsController::class, 'dashboard'])->name('dashboard');
            });
            
            // Clients
            Route::prefix('clients')->name('clients.')->group(function () {
                Route::get('/', [ClientController::class, 'index'])->name('index');
                Route::post('/', [ClientController::class, 'store'])->name('store');
                Route::get('/search', [ClientController::class, 'search'])->name('search');
                Route::delete('/{id}', [ClientController::class, 'destroy'])->name('destroy');
            });
            
            // Devis
            Route::prefix('devis')->name('devis.')->group(function () {
                Route::get('/', [DevisController::class, 'index'])->name('index');
                Route::get('/create', [DevisController::class, 'create'])->name('create');
                Route::post('/generate-lines', [DevisController::class, 'generateLines'])->name('generate-lines');
                Route::post('/', [DevisController::class, 'store'])->name('store');
                Route::get('/{id}', [DevisController::class, 'show'])->name('show');
                Route::get('/{id}/edit', [DevisController::class, 'edit'])->name('edit');
                Route::put('/{id}', [DevisController::class, 'update'])->name('update');
                Route::post('/{id}/validate', [DevisController::class, 'validate'])->name('validate');
                Route::get('/{id}/pdf', [DevisController::class, 'pdf'])->name('pdf');
                Route::get('/{id}/download-pdf', [DevisController::class, 'downloadPdf'])->name('download-pdf');
                Route::post('/{id}/send-email', [DevisController::class, 'sendEmail'])->name('send-email');
                Route::delete('/{id}', [DevisController::class, 'destroy'])->name('destroy');
            });
            
            // Factures
            Route::prefix('factures')->name('factures.')->group(function () {
                Route::get('/', [FactureController::class, 'index'])->name('index');
                Route::get('/{id}', [FactureController::class, 'show'])->name('show');
                Route::get('/{id}/pdf', [FactureController::class, 'pdf'])->name('pdf');
                Route::get('/{id}/download-pdf', [FactureController::class, 'downloadPdf'])->name('download-pdf');
                Route::post('/{id}/send-email', [FactureController::class, 'sendEmail'])->name('send-email');
                Route::post('/{id}/send-reminder', [FactureController::class, 'sendReminder'])->name('send-reminder');
                Route::post('/{id}/mark-paid', [FactureController::class, 'markAsPaid'])->name('mark-paid');
                Route::post('/{id}/record-payment', [FactureController::class, 'recordPayment'])->name('record-payment');
                Route::delete('/{id}', [FactureController::class, 'destroy'])->name('destroy');
            });
            
            // ===== SETTINGS =====
            // Routes déplacées en dehors du groupe admin pour accès sans authentification
            // ===== ADS ADMIN =====
            Route::post('/ads/create-manual', [App\Http\Controllers\AdAdminController::class, 'createManual'])->name('ads.create.manual');
            Route::post('/ads/remove-duplicates', [App\Http\Controllers\AdAdminController::class, 'removeDuplicates'])->name('ads.remove.duplicates');
            Route::post('/ads/delete-all', [App\Http\Controllers\AdAdminController::class, 'deleteAll'])->name('ads.delete-all');
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
            
            // ===== ANNONCES =====
            Route::get('/ads', [App\Http\Controllers\AdAdminController::class, 'index'])->name('ads.index');
            Route::post('/ads/delete-all', [App\Http\Controllers\AdAdminController::class, 'deleteAll'])->name('ads.delete-all');
            
            // ===== GÉNÉRATION EN MASSE (SYSTÈME PRINCIPAL) =====
            Route::get('/ads/bulk-ads', [App\Http\Controllers\Admin\BulkAdsController::class, 'index'])->name('ads.bulk-ads');
            Route::post('/ads/bulk-ads/generate', [App\Http\Controllers\Admin\BulkAdsController::class, 'generateBulkAds'])->name('ads.bulk-ads.generate');
            Route::post('/ads/bulk-ads/generate-keyword', [App\Http\Controllers\Admin\BulkAdsController::class, 'generateBulkAdsByKeyword'])->name('ads.bulk-ads.generate-keyword');
            Route::get('/ads/bulk-ads/favorite-cities', [App\Http\Controllers\Admin\BulkAdsController::class, 'getFavoriteCities'])->name('ads.bulk-ads.favorite-cities');
            Route::get('/ads/bulk-ads/cities-by-region', [App\Http\Controllers\Admin\BulkAdsController::class, 'getCitiesByRegion'])->name('ads.bulk-ads.cities-by-region');
            
            // ===== CRÉATION MANUELLE =====
            Route::get('/ads/manual', [App\Http\Controllers\Admin\ManualAdController::class, 'index'])->name('ads.manual');
            Route::post('/ads/manual', [App\Http\Controllers\Admin\ManualAdController::class, 'store'])->name('ads.manual.store');
            Route::get('/ads/manual/favorite-cities', [App\Http\Controllers\Admin\ManualAdController::class, 'getFavoriteCities'])->name('ads.manual.favorite-cities');
            Route::get('/ads/manual/cities-by-region', [App\Http\Controllers\Admin\ManualAdController::class, 'getCitiesByRegion'])->name('ads.manual.cities-by-region');

        // ===== TEMPLATES D'ANNONCES =====
        Route::get('/ads/templates', [App\Http\Controllers\Admin\AdTemplateController::class, 'index'])->name('ads.templates.index');
        Route::get('/ads/templates/create', [App\Http\Controllers\Admin\AdTemplateController::class, 'create'])->name('ads.templates.create');
        Route::post('/ads/templates', [App\Http\Controllers\Admin\AdTemplateController::class, 'store'])->name('ads.templates.store');
        Route::post('/ads/templates/create-from-service', [App\Http\Controllers\Admin\AdTemplateController::class, 'createFromService'])->name('ads.templates.create-from-service');
        Route::post('/ads/templates/create-from-keyword', [App\Http\Controllers\Admin\AdTemplateController::class, 'createFromKeyword'])->name('ads.templates.create-from-keyword');
        Route::get('/ads/templates/cities', [App\Http\Controllers\Admin\AdTemplateController::class, 'getCities'])->name('ads.templates.cities');
        Route::get('/ads/templates/{template}', [App\Http\Controllers\Admin\AdTemplateController::class, 'show'])->name('ads.templates.show');
        Route::get('/ads/templates/{template}/edit', [App\Http\Controllers\Admin\AdTemplateController::class, 'edit'])->name('ads.templates.edit');
        Route::put('/ads/templates/{template}', [App\Http\Controllers\Admin\AdTemplateController::class, 'update'])->name('ads.templates.update');
        Route::post('/ads/templates/generate-ads', [App\Http\Controllers\Admin\AdTemplateController::class, 'generateAdsFromTemplate'])->name('ads.templates.generate-ads');
        Route::get('/ads/templates/generate-all-links', [App\Http\Controllers\Admin\AdTemplateController::class, 'generateAllLinks'])->name('ads.templates.generate-all-links');
        Route::post('/ads/templates/{template}/toggle-status', [App\Http\Controllers\Admin\AdTemplateController::class, 'toggleStatus'])->name('ads.templates.toggle-status');
        Route::delete('/ads/templates/{template}', [App\Http\Controllers\Admin\AdTemplateController::class, 'destroy'])->name('ads.templates.destroy');

            // ===== AUTOMATISATION SEO =====
            Route::get('/seo-automation/password', [App\Http\Controllers\Admin\SeoAutomationController::class, 'passwordForm'])->name('seo-automation.password-form');
            Route::post('/seo-automation/verify-password', [App\Http\Controllers\Admin\SeoAutomationController::class, 'verifyPassword'])->name('seo-automation.verify-password');
            
            Route::middleware('seo.automation.password')->group(function () {
                Route::get('/seo-automation', [App\Http\Controllers\Admin\SeoAutomationController::class, 'index'])->name('seo-automation.index');
                // Redirection propre pour les accès GET à /seo-automation/run
                Route::get('/seo-automation/run', [App\Http\Controllers\Admin\SeoAutomationController::class, 'redirectRunGet'])->name('seo-automation.run.get');
                Route::post('/seo-automation/run', [App\Http\Controllers\Admin\SeoAutomationController::class, 'run'])->name('seo-automation.run');
                Route::post('/seo-automation/toggle', [App\Http\Controllers\Admin\SeoAutomationController::class, 'toggle'])->name('seo-automation.toggle');
                Route::post('/seo-automation/save-time', [App\Http\Controllers\Admin\SeoAutomationController::class, 'saveTime'])->name('seo-automation.save-time');
                Route::post('/seo-automation/save-og-image', [App\Http\Controllers\Admin\SeoAutomationController::class, 'saveOgImage'])->name('seo-automation.save-og-image');
                Route::post('/seo-automation/upload-og-image', [App\Http\Controllers\Admin\SeoAutomationController::class, 'uploadOgImage'])->name('seo-automation.upload-og-image');
                Route::post('/seo-automation/keyword-image', [App\Http\Controllers\Admin\SeoAutomationController::class, 'storeKeywordImage'])->name('seo-automation.keyword-image.store');
                Route::delete('/seo-automation/keyword-image/{keywordImage}', [App\Http\Controllers\Admin\SeoAutomationController::class, 'destroyKeywordImage'])->name('seo-automation.keyword-image.destroy');
                Route::post('/seo-automation/test', [App\Http\Controllers\Admin\SeoAutomationController::class, 'testConnections'])->name('seo-automation.test');
                Route::post('/seo-automation/test-api', [App\Http\Controllers\Admin\SeoAutomationController::class, 'testApi'])->name('seo-automation.test-api');
            Route::post('/seo-automation/save-config', [App\Http\Controllers\Admin\SeoAutomationController::class, 'saveApiConfig'])->name('seo-automation.save-config');
            Route::post('/seo-automation/generate-keywords', [App\Http\Controllers\Admin\SeoAutomationController::class, 'generateKeywords'])->name('seo-automation.generate-keywords');
            Route::post('/seo-automation/save-keywords', [App\Http\Controllers\Admin\SeoAutomationController::class, 'saveKeywords'])->name('seo-automation.save-keywords');
            Route::post('/seo-automation/run/{city}', [App\Http\Controllers\Admin\SeoAutomationController::class, 'runForCity'])->name('seo-automation.run-city');
            Route::post('/seo-automation/{seoAutomation}/retry', [App\Http\Controllers\Admin\SeoAutomationController::class, 'retry'])->name('seo-automation.retry');
            Route::post('/seo-automation/retry-pending-failed', [App\Http\Controllers\Admin\SeoAutomationController::class, 'retryPendingAndFailed'])->name('seo-automation.retry-pending-failed');
            Route::post('/seo-automation/force-run', [App\Http\Controllers\Admin\SeoAutomationController::class, 'forceRun'])->name('seo-automation.force-run');
            Route::post('/seo-automation/execute-now', [App\Http\Controllers\Admin\SeoAutomationController::class, 'executeNow'])->name('seo-automation.execute-now');
            Route::post('/seo-automation/reset-all', [App\Http\Controllers\Admin\SeoAutomationController::class, 'resetAll'])->name('seo-automation.reset-all');
            Route::post('/seo-automation/test-scheduler', [App\Http\Controllers\Admin\SeoAutomationController::class, 'testScheduler'])->name('seo-automation.test-scheduler');
            Route::get('/seo-automation/schedule-token', [App\Http\Controllers\Admin\SeoAutomationController::class, 'getScheduleToken'])->name('seo-automation.schedule-token');
            Route::post('/seo-automation/regenerate-schedule-token', [App\Http\Controllers\Admin\SeoAutomationController::class, 'regenerateScheduleToken'])->name('seo-automation.regenerate-schedule-token');
            Route::post('/seo-automation/test-schedule-http', [App\Http\Controllers\Admin\SeoAutomationController::class, 'testScheduleHttp'])->name('seo-automation.test-schedule-http');
            });

            // ===== ARTICLES =====
            // ===== ARTICLES (NOUVEAU SYSTÈME) =====
            Route::get('/articles', [App\Http\Controllers\Admin\ArticleController::class, 'index'])->name('articles.index');
            Route::get('/articles/generate', [App\Http\Controllers\Admin\ArticleController::class, 'generate'])->name('articles.generate');
            Route::get('/articles/create', [App\Http\Controllers\Admin\ArticleController::class, 'create'])->name('articles.create');
            Route::post('/articles', [App\Http\Controllers\Admin\ArticleController::class, 'store'])->name('articles.store');
            Route::delete('/articles', [App\Http\Controllers\Admin\ArticleController::class, 'destroyAll'])->name('articles.destroy-all');
            
            // Routes pour génération IA
            Route::post('/articles/generate-titles', [App\Http\Controllers\Admin\ArticleController::class, 'generateTitles'])->name('articles.generate-titles');
            Route::post('/articles/generate-content', [App\Http\Controllers\Admin\ArticleController::class, 'generateContent'])->name('articles.generate-content');
            Route::post('/articles/upload-image', [App\Http\Controllers\Admin\ArticleController::class, 'uploadImage'])->name('articles.upload-image');
            Route::put('/articles/images/{imageId}/metadata', [App\Http\Controllers\Admin\ArticleController::class, 'updateImageMetadata'])->name('articles.images.update-metadata');
            Route::get('/articles/{articleId}/images', [App\Http\Controllers\Admin\ArticleController::class, 'getArticleImages'])->name('articles.images');
            Route::get('/articles/images/available', [App\Http\Controllers\Admin\ArticleController::class, 'getAvailableImages'])->name('articles.images.available');
            Route::get('/articles/menu-links', [App\Http\Controllers\Admin\ArticleController::class, 'getMenuLinks'])->name('articles.menu-links');
            Route::post('/articles/create-from-titles', [App\Http\Controllers\Admin\ArticleController::class, 'createFromTitles'])->name('articles.create-from-titles');
            
            // Routes IA améliorées (AVANT les routes avec paramètres)
            Route::get('/articles/ai-generate', [App\Http\Controllers\Admin\ArticleAiController::class, 'form'])->name('articles.ai.form');
            Route::post('/articles/ai-generate', [App\Http\Controllers\Admin\ArticleAiController::class, 'generate'])->name('articles.ai.generate');
            Route::post('/articles/ai-test', [App\Http\Controllers\Admin\ArticleAiController::class, 'test'])->name('articles.ai.test');
            
            // Routes avec paramètres (APRÈS les routes spécifiques)
            Route::get('/articles/{article}', [App\Http\Controllers\Admin\ArticleController::class, 'show'])->name('articles.show');
            Route::get('/articles/{article}/edit', [App\Http\Controllers\Admin\ArticleController::class, 'edit'])->name('articles.edit');
            Route::put('/articles/{article}', [App\Http\Controllers\Admin\ArticleController::class, 'update'])->name('articles.update');
            Route::delete('/articles/{article}', [App\Http\Controllers\Admin\ArticleController::class, 'destroy'])->name('articles.destroy');
            
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
        Route::post('/security', [ConfigController::class, 'updateSecurity'])->name('update.security');
        Route::post('/analytics', [ConfigController::class, 'updateAnalytics'])->name('update.analytics');
        
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
        Route::get('/pages', [App\Http\Controllers\SeoController::class, 'pages'])->name('pages');
        Route::get('/test', function() { return view('admin.seo.test'); })->name('test.page');
        Route::post('/update', [App\Http\Controllers\SeoController::class, 'update'])->name('update');
        Route::post('/update-pages', [App\Http\Controllers\SeoController::class, 'updatePages'])->name('update-pages');
        Route::post('/update-page', [App\Http\Controllers\SeoController::class, 'updatePage'])->name('update-page');
        Route::post('/generate-ai', [App\Http\Controllers\SeoController::class, 'generateSeoWithAI'])->name('generate-ai');
        Route::post('/generate-page-ai', [App\Http\Controllers\SeoController::class, 'generatePageSeoWithAI'])->name('generate-page-ai');
        Route::get('/test-seo', [App\Http\Controllers\SeoController::class, 'testSeo'])->name('test');
        Route::post('/validate', [App\Http\Controllers\SeoController::class, 'validateSeoForGoogle'])->name('validate');
    });

    Route::prefix('admin/indexation')->name('admin.indexation.')->middleware(['admin.auth'])->group(function () {
        Route::get('/', [App\Http\Controllers\IndexationController::class, 'index'])->name('index');
        Route::post('/update', [App\Http\Controllers\IndexationController::class, 'update'])->name('update');
        Route::post('/update-sitemap', [App\Http\Controllers\IndexationController::class, 'updateSitemap'])->name('update-sitemap');
        Route::get('/urls', [App\Http\Controllers\IndexationController::class, 'getAllUrls'])->name('urls');
        Route::post('/index-urls', [App\Http\Controllers\IndexationController::class, 'indexUrls'])->name('index-urls');
        Route::post('/submit-all-to-google', [App\Http\Controllers\IndexationController::class, 'submitAllUrlsToGoogle'])->name('submit-all-to-google');
        Route::post('/submit-sitemap-to-google', [App\Http\Controllers\IndexationController::class, 'submitSitemapToGoogle'])->name('submit-sitemap-to-google');
        Route::post('/test-google', [App\Http\Controllers\IndexationController::class, 'testGoogleConnection'])->name('test-google');
        Route::post('/toggle-daily-indexing', [App\Http\Controllers\IndexationController::class, 'toggleDailyIndexing'])->name('toggle-daily-indexing');
        Route::post('/reset-indexed-urls', [App\Http\Controllers\IndexationController::class, 'resetIndexedUrls'])->name('reset-indexed-urls');
        Route::post('/run-daily-indexing', [App\Http\Controllers\IndexationController::class, 'runDailyIndexing'])->name('run-daily-indexing');
        Route::post('/test-single-url', [App\Http\Controllers\IndexationController::class, 'testSingleUrl'])->name('test-single-url');
        Route::get('/indexjump-balance', [App\Http\Controllers\IndexationController::class, 'getIndexJumpBalance'])->name('indexjump-balance');
        Route::post('/test-indexjump', [App\Http\Controllers\IndexationController::class, 'testIndexJumpConnection'])->name('test-indexjump');
        Route::post('/test-indexjump-url', [App\Http\Controllers\IndexationController::class, 'testIndexJumpUrl'])->name('test-indexjump-url');
        Route::post('/verify-status', [App\Http\Controllers\IndexationController::class, 'verifyStatus'])->name('verify-status');
        Route::post('/verify-statuses', [App\Http\Controllers\IndexationController::class, 'verifyStatuses'])->name('verify-statuses');
        Route::get('/statuses', [App\Http\Controllers\IndexationController::class, 'getStatuses'])->name('statuses');
        Route::post('/submit-sitemap-to-indexjump', [App\Http\Controllers\IndexationController::class, 'submitSitemapToIndexJump'])->name('submit-sitemap-to-indexjump');
        Route::post('/update-indexjump-token', [App\Http\Controllers\IndexationController::class, 'updateIndexJumpToken'])->name('update-indexjump-token');
        Route::post('/reset-sitemaps', [App\Http\Controllers\IndexationController::class, 'resetSitemaps'])->name('reset-sitemaps');
    });
    
    // Route pour servir le favicon.ico (pour Google) - DOIT être avant les autres routes
    Route::get('/favicon.ico', function() {
        // D'abord, vérifier si favicon.ico existe directement
        $icoPath = public_path('favicon.ico');
        if (file_exists($icoPath)) {
            $mimeType = @mime_content_type($icoPath) ?: 'image/x-icon';
            return response()->file($icoPath, ['Content-Type' => $mimeType]);
        }
        
        // Sinon, chercher le favicon configuré
        $faviconPath = \App\Models\Setting::get('site_favicon');
        
        // Si un favicon est configuré, le servir
        if ($faviconPath) {
            $fullPath = public_path($faviconPath);
            if (file_exists($fullPath)) {
                $mimeType = @mime_content_type($fullPath) ?: 'image/png';
                return response()->file($fullPath, ['Content-Type' => $mimeType]);
            }
        }
        
        // Vérifier aussi dans seo_config
        $seoConfigData = \App\Models\Setting::get('seo_config', '[]');
        $seoConfig = is_string($seoConfigData) ? json_decode($seoConfigData, true) : ($seoConfigData ?? []);
        if (!empty($seoConfig['favicon'])) {
            $fullPath = public_path($seoConfig['favicon']);
            if (file_exists($fullPath)) {
                $mimeType = @mime_content_type($fullPath) ?: 'image/png';
                return response()->file($fullPath, ['Content-Type' => $mimeType]);
            }
        }
        
        // Fallback: chercher n'importe quel favicon dans public
        $faviconFiles = glob(public_path('favicon*'));
        if (!empty($faviconFiles)) {
            $faviconFile = $faviconFiles[0];
            $mimeType = @mime_content_type($faviconFile) ?: 'image/png';
            return response()->file($faviconFile, ['Content-Type' => $mimeType]);
        }
        
        // Si aucun favicon trouvé, retourner 204 No Content (au lieu de 404)
        return response('', 204);
    })->name('favicon.ico');
    
    // Routes publiques SEO
    Route::get('/sitemap.xml', [App\Http\Controllers\SeoController::class, 'generateSitemap'])->name('sitemap.xml');
    // Route pour sitemap_index.xml - rediriger vers sitemap.xml (on n'utilise plus sitemap_index.xml)
    Route::get('/sitemap_index.xml', function() {
        // Supprimer le fichier s'il existe
        $indexPath = public_path('sitemap_index.xml');
        if (file_exists($indexPath)) {
            @unlink($indexPath);
        }
        // Rediriger vers sitemap.xml
        return redirect('/sitemap.xml', 301);
    })->name('sitemap_index.xml');
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
    Route::post('/config/update/conversion', [ConfigController::class, 'updateConversion'])->name('config.update.conversion');
    Route::post('/config/generate/faqs', [ConfigController::class, 'generateFaqsWithAI'])->name('config.generate.faqs');
    Route::post('/config/test-chatgpt', [ConfigController::class, 'testChatGPT'])->name('config.test.chatgpt');
    Route::post('/config/test-groq', [ConfigController::class, 'testGroq'])->name('config.test.groq');
    Route::post('/config/test-chatgpt-generate', [ConfigController::class, 'testChatGPTGenerate'])->name('config.test.chatgpt.generate');
    Route::post('/config/test-groq-generate', [ConfigController::class, 'testGroqGenerate'])->name('config.test.groq.generate');
    
    // ===== PAGES LÉGALES =====
    Route::get('/mentions-legales', [App\Http\Controllers\LegalController::class, 'mentionsLegales'])->name('legal.mentions');
    Route::get('/politique-confidentialite', [App\Http\Controllers\LegalController::class, 'politiqueConfidentialite'])->name('legal.privacy');
    Route::get('/cgv', [App\Http\Controllers\LegalController::class, 'cgv'])->name('legal.cgv');
    
    // ===== PAGE EMPLOI (cachée des menus, accessible uniquement via sitemap) =====
    Route::get('/jobs', [App\Http\Controllers\JobController::class, 'index'])->name('jobs.index');
    
    // ===== SERVICES =====
    // Services publics
    Route::get('/services', [ServicesController::class, 'publicIndex'])->name('services.index');
    Route::get('/services/{slug}', [ServicesController::class, 'show'])->name('services.show');
    
    // ===== ANNONCES PUBLIQUES =====
    // Annonces avec format /nomduservice-nomdelaville
    Route::get('/{service}-{city}', [App\Http\Controllers\AdController::class, 'show'])->name('ads.show.slug');
    
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
        
        // Routes IA pour la génération de services
        Route::get('/ai', [App\Http\Controllers\ServiceAiController::class, 'form'])->name('ai.form');
        Route::post('/ai/generate', [App\Http\Controllers\ServiceAiController::class, 'generate'])->name('ai.generate');
        Route::post('/ai/test', [App\Http\Controllers\ServiceAiController::class, 'test'])->name('ai.test');
    });
});













