<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormControllerSimple;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\ReviewsController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AdPublicController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\PublicMediaController;
use App\Http\Controllers\Admin\DevisController;
use App\Http\Controllers\Admin\FactureController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\QuotationStatsController;
use App\Http\Controllers\Admin\SeoAutomationController;

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
        
        // Route pour exécuter le scheduler Laravel via HTTP (pour Hostinger et services externes)
        // Cette route exécute toutes les tâches planifiées (sitemap, indexation, etc.)
        Route::get('/cron/run', function (\Illuminate\Http\Request $request) {
            // Augmenter le timeout pour permettre l'exécution complète
            set_time_limit(300); // 5 minutes
            ini_set('max_execution_time', 300);
            ini_set('default_socket_timeout', 300);
            
            $startTime = microtime(true);
            
            // Récupérer le token depuis la requête ou les settings
            $token = $request->query('token');
            $configuredToken = \App\Models\Setting::get('cron_run_token', null);
            
            // Si aucun token n'est configuré, générer un token et le retourner avec instructions
            if (empty($configuredToken)) {
                $newToken = \Illuminate\Support\Str::random(32);
                \App\Models\Setting::set('cron_run_token', $newToken, 'string', 'system');
                
                return response()->json([
                    'message' => 'Token généré. Utilisez cette URL pour exécuter les tâches planifiées :',
                    'url' => url('/cron/run?token=' . $newToken),
                    'token' => $newToken,
                    'instructions' => [
                        '1. Configurez cette URL dans le gestionnaire de cron de Hostinger',
                        '2. Ou utilisez un service externe (cron-job.org, UptimeRobot, etc.)',
                        '3. Appelez cette URL toutes les minutes pour exécuter le scheduler Laravel',
                        '4. Les tâches planifiées (sitemap, indexation, etc.) s\'exécuteront automatiquement'
                    ]
                ], 200);
            }
            
            // Vérifier le token
            if (empty($token) || $token !== $configuredToken) {
                return response()->json([
                    'error' => 'Token invalide ou manquant',
                    'message' => 'Utilisez ?token=VOTRE_TOKEN dans l\'URL',
                    'hint' => 'Le token est configuré dans les settings de l\'application'
                ], 401);
            }
            
            // Exécuter le scheduler Laravel
            try {
                \Illuminate\Support\Facades\Log::info('🔄 Exécution du scheduler Laravel via HTTP...');
                
                // Exécuter toutes les tâches planifiées
                \Illuminate\Support\Facades\Artisan::call('schedule:run');
                $output = \Illuminate\Support\Facades\Artisan::output();
                
                $executionTime = round(microtime(true) - $startTime, 2);
                
                \Illuminate\Support\Facades\Log::info('✅ Scheduler exécuté avec succès', [
                    'execution_time' => $executionTime . 's',
                    'output' => $output
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Scheduler exécuté avec succès',
                    'execution_time' => $executionTime . ' secondes',
                    'timestamp' => now()->toDateTimeString(),
                    'output' => $output
                ], 200);
                
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('❌ Erreur lors de l\'exécution du scheduler: ' . $e->getMessage());
                
                return response()->json([
                    'success' => false,
                    'error' => 'Erreur lors de l\'exécution du scheduler',
                    'message' => $e->getMessage(),
                    'timestamp' => now()->toDateTimeString()
                ], 500);
            }
        })->name('cron.run');
        
        // Route pour exécuter le scheduler via HTTP (pour services externes comme EasyCron, cron-job.org)
        // Protégée par token pour la sécurité
        // EasyCron peut attendre jusqu'à 5 minutes - on exécute tout et on répond uniquement à la fin
        Route::get('/schedule/run', function (\Illuminate\Http\Request $request) {
            // Augmenter le timeout pour permettre la génération complète (5 minutes max pour EasyCron)
            set_time_limit(300); // 5 minutes
            ini_set('max_execution_time', 300);
            ini_set('default_socket_timeout', 300);
            
            $startTime = microtime(true);
            
            $token = $request->query('token');
            $configuredToken = \App\Models\Setting::where('key', 'schedule_run_token')->value('value');
            
            // Si aucun token n'est configuré, générer un token et le retourner avec instructions
            if (empty($configuredToken)) {
                
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
                $automationEnabled = \App\Models\Setting::get('seo_automation_enabled', true);
                if (!filter_var($automationEnabled, FILTER_VALIDATE_BOOLEAN)) {
                    return response()->json([
                        'message' => 'Automatisation SEO désactivée',
                        'status' => 'skipped'
                    ], 200);
                }
                
                \Illuminate\Support\Facades\Log::info('🔄 Exécution manuelle de la génération d\'articles SEO via HTTP...');
                
                // Exécuter la commande
                \Illuminate\Support\Facades\Artisan::call('seo:run-automations');
                $output = \Illuminate\Support\Facades\Artisan::output();
                
                $executionTime = round(microtime(true) - $startTime, 2);
                
                \Illuminate\Support\Facades\Log::info('✅ Génération d\'articles SEO terminée', [
                    'execution_time' => $executionTime . 's'
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Génération d\'articles SEO exécutée avec succès',
                    'execution_time' => $executionTime . ' secondes',
                    'timestamp' => now()->toDateTimeString(),
                    'output' => $output
                ], 200);
                
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('❌ Erreur lors de la génération d\'articles SEO: ' . $e->getMessage());
                
                return response()->json([
                    'success' => false,
                    'error' => 'Erreur lors de la génération',
                    'message' => $e->getMessage(),
                    'timestamp' => now()->toDateTimeString()
                ], 500);
            }
        })->name('schedule.run');

// Route publique pour la page d'accueil
Route::get('/', [HomeController::class, 'index'])->name('home');

// Routes publiques pour les services
Route::get('/services', [ServicesController::class, 'publicIndex'])->name('services.index');
Route::get('/services/{slug}', [ServicesController::class, 'show'])->name('services.show');

// Routes publiques pour le formulaire (placer les routes spécifiques AVANT le wildcard)
    Route::get('/form/success', [FormControllerSimple::class, 'success'])->name('form.success');
    Route::get('/form/{currentStep}/previous', [FormControllerSimple::class, 'previousStep'])->name('form.previous');
    Route::post('/form/{step}/submit', [FormControllerSimple::class, 'submitStep'])->name('form.submit');
    Route::get('/form/{step}', [FormControllerSimple::class, 'showStep'])->name('form.step');

// Route publique pour servir les photos de soumission (email, pas d'auth)
Route::get('/media/submissions/{id}/{file}', [PublicMediaController::class, 'submissionPhoto'])
    ->where(['id' => '[0-9]+', 'file' => '[A-Za-z0-9._-]+'])
    ->name('media.submission.photo');

// Routes publiques pour le portfolio
Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio.index');
Route::get('/portfolio/{slug}', [PortfolioController::class, 'show'])->name('portfolio.show');

// Routes publiques pour le blog
Route::get('/blog', [ArticleController::class, 'index'])->name('blog.index');
Route::get('/blog/{article}', [ArticleController::class, 'show'])->name('blog.show');

// Route publique pour le contact
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');

// Routes publiques pour le simulateur de coûts
Route::get('/simulateur', [App\Http\Controllers\CostSimulatorController::class, 'index'])->name('simulator.index');
Route::post('/simulateur/calculate', [App\Http\Controllers\CostSimulatorController::class, 'calculate'])->name('simulator.calculate');

// Routes publiques pour les annonces
Route::get('/ads', [AdPublicController::class, 'index'])->name('ads.index');
Route::get('/ads/{slug}', [AdPublicController::class, 'show'])->name('ads.show');

// Routes publiques pour les avis
Route::get('/reviews', [FormControllerSimple::class, 'allReviews'])->name('reviews.all');

// Routes publiques pour les pages légales
Route::get('/legal/mentions', [LegalController::class, 'mentionsLegales'])->name('legal.mentions');
Route::get('/legal/privacy', [LegalController::class, 'politiqueConfidentialite'])->name('legal.privacy');
Route::get('/legal/cgv', [LegalController::class, 'cgv'])->name('legal.cgv');

// Routes admin (login/logout - publiques)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/login', [AdminController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminController::class, 'authenticate'])->name('authenticate');
        Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
        
    // Routes protégées (nécessitent authentification)
        Route::middleware(['admin.auth'])->group(function () {
            Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
            Route::get('/submissions', [AdminController::class, 'submissions'])->name('submissions');
            Route::get('/abandoned-submissions', [AdminController::class, 'abandonedSubmissions'])->name('abandoned-submissions');
            Route::get('/submissions/{id}', [AdminController::class, 'showSubmission'])->name('submission.show');
        Route::get('/submissions/{id}/create-client', [AdminController::class, 'createClientFromSubmission'])->name('submission.create-client');
            Route::post('/submissions/{id}/mark-abandoned', [AdminController::class, 'markSubmissionAsAbandoned'])->name('submission.mark-abandoned');
        Route::post('/submissions/delete-all', [AdminController::class, 'deleteAllSubmissions'])->name('submissions.delete-all');
            Route::get('/abandoned-submissions/{id}', [AdminController::class, 'showAbandonedSubmission'])->name('abandoned-submission.show');
            Route::get('/export/submissions', [AdminController::class, 'exportSubmissions'])->name('export.submissions');
            Route::get('/export/abandoned-submissions', [AdminController::class, 'exportAbandonedSubmissions'])->name('export.abandoned-submissions');
            Route::get('/statistics', [AdminController::class, 'statistics'])->name('statistics');
            Route::get('/phone-calls', [AdminController::class, 'phoneCalls'])->name('phone-calls');
            Route::get('/phone-calls/{id}', [AdminController::class, 'showPhoneCall'])->name('phone-calls.show');
        Route::post('/phone-calls/{id}/update-city', [AdminController::class, 'updatePhoneCallCity'])->name('phone-calls.update-city');
            Route::post('/phone-calls/delete-all', [AdminController::class, 'deleteAllPhoneCalls'])->name('phone-calls.delete-all');
            Route::get('/visits', [App\Http\Controllers\VisitsController::class, 'index'])->name('visits');
        
        // Routes pour les devis
            Route::prefix('devis')->name('devis.')->group(function () {
                Route::get('/', [DevisController::class, 'index'])->name('index');
                Route::get('/create', [DevisController::class, 'create'])->name('create');
                Route::post('/generate-lines', [DevisController::class, 'generateLines'])->name('generate-lines');
                Route::post('/', [DevisController::class, 'store'])->name('store');
                Route::get('/{id}', [DevisController::class, 'show'])->name('show');
                Route::get('/{id}/edit', [DevisController::class, 'edit'])->name('edit');
                Route::put('/{id}', [DevisController::class, 'update'])->name('update');
                Route::post('/{id}/validate', [DevisController::class, 'validate'])->name('validate');
            Route::delete('/{id}', [DevisController::class, 'destroy'])->name('destroy');
                Route::get('/{id}/pdf', [DevisController::class, 'pdf'])->name('pdf');
                Route::get('/{id}/download-pdf', [DevisController::class, 'downloadPdf'])->name('download-pdf');
                Route::post('/{id}/send-email', [DevisController::class, 'sendEmail'])->name('send-email');
            Route::get('/public/{id}/{token}', [DevisController::class, 'publicPdf'])->name('public-pdf');
            });
            
        // Routes pour les factures
            Route::prefix('factures')->name('factures.')->group(function () {
                Route::get('/', [FactureController::class, 'index'])->name('index');
                Route::get('/{id}', [FactureController::class, 'show'])->name('show');
            Route::post('/{id}/mark-as-paid', [FactureController::class, 'markAsPaid'])->name('mark-as-paid');
                Route::get('/{id}/pdf', [FactureController::class, 'pdf'])->name('pdf');
                Route::get('/{id}/download-pdf', [FactureController::class, 'downloadPdf'])->name('download-pdf');
                Route::post('/{id}/send-email', [FactureController::class, 'sendEmail'])->name('send-email');
                Route::post('/{id}/send-reminder', [FactureController::class, 'sendReminder'])->name('send-reminder');
                Route::post('/{id}/record-payment', [FactureController::class, 'recordPayment'])->name('record-payment');
                Route::delete('/{id}', [FactureController::class, 'destroy'])->name('destroy');
            });
            
        // Routes pour les clients
        Route::prefix('clients')->name('clients.')->group(function () {
            Route::get('/', [ClientController::class, 'index'])->name('index');
            Route::post('/', [ClientController::class, 'store'])->name('store');
            Route::get('/search', [ClientController::class, 'search'])->name('search');
            Route::delete('/{id}', [ClientController::class, 'destroy'])->name('destroy');
        });
        
        // Routes pour les articles
        Route::prefix('articles')->name('articles.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\ArticleController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\ArticleController::class, 'create'])->name('create');
            Route::get('/generate', [App\Http\Controllers\Admin\ArticleController::class, 'generate'])->name('generate');
            Route::get('/ai/form', [App\Http\Controllers\Admin\ArticleController::class, 'generate'])->name('ai.form');
            Route::post('/', [App\Http\Controllers\Admin\ArticleController::class, 'store'])->name('store');
            Route::get('/{article}', [App\Http\Controllers\Admin\ArticleController::class, 'show'])->name('show');
            Route::get('/{article}/edit', [App\Http\Controllers\Admin\ArticleController::class, 'edit'])->name('edit');
            Route::put('/{article}', [App\Http\Controllers\Admin\ArticleController::class, 'update'])->name('update');
            Route::delete('/{article}', [App\Http\Controllers\Admin\ArticleController::class, 'destroy'])->name('destroy');
            Route::delete('/', [App\Http\Controllers\Admin\ArticleController::class, 'destroyAll'])->name('destroy-all');
            Route::post('/generate-titles', [App\Http\Controllers\Admin\ArticleController::class, 'generateTitles'])->name('generate-titles');
            Route::post('/generate-content', [App\Http\Controllers\Admin\ArticleController::class, 'generateContent'])->name('generate-content');
            Route::post('/upload-image', [App\Http\Controllers\Admin\ArticleController::class, 'uploadImage'])->name('upload-image');
            Route::post('/images/{imageId}/metadata', [App\Http\Controllers\Admin\ArticleController::class, 'updateImageMetadata'])->name('update-image-metadata');
            Route::get('/{articleId}/images', [App\Http\Controllers\Admin\ArticleController::class, 'getArticleImages'])->name('get-images');
            Route::get('/menu/links', [App\Http\Controllers\Admin\ArticleController::class, 'getMenuLinks'])->name('get-menu-links');
            Route::get('/images/available', [App\Http\Controllers\Admin\ArticleController::class, 'getAvailableImages'])->name('get-available-images');
            Route::post('/create-from-titles', [App\Http\Controllers\Admin\ArticleController::class, 'createFromTitles'])->name('create-from-titles');
        });
        
        // Routes pour les annonces
        Route::prefix('ads')->name('ads.')->group(function () {
            Route::get('/', [App\Http\Controllers\AdAdminController::class, 'index'])->name('index');
            Route::get('/manual', [App\Http\Controllers\Admin\ManualAdController::class, 'index'])->name('manual');
            Route::post('/manual', [App\Http\Controllers\Admin\ManualAdController::class, 'store'])->name('manual.store');
            Route::get('/manual/cities-by-region', [App\Http\Controllers\Admin\ManualAdController::class, 'getCitiesByRegion'])->name('manual.cities-by-region');
            Route::get('/manual/favorite-cities', [App\Http\Controllers\Admin\ManualAdController::class, 'getFavoriteCities'])->name('manual.favorite-cities');
            Route::post('/{ad}/publish', [App\Http\Controllers\AdAdminController::class, 'publish'])->name('publish');
            Route::post('/{ad}/archive', [App\Http\Controllers\AdAdminController::class, 'archive'])->name('archive');
            Route::delete('/{ad}', [App\Http\Controllers\AdAdminController::class, 'destroy'])->name('destroy');
            Route::post('/create-manual', [App\Http\Controllers\AdAdminController::class, 'createManual'])->name('create-manual');
            Route::post('/remove-duplicates', [App\Http\Controllers\AdAdminController::class, 'removeDuplicates'])->name('remove-duplicates');
            Route::delete('/', [App\Http\Controllers\AdAdminController::class, 'deleteAll'])->name('delete-all');
            
            // Routes pour les templates d'annonces
            Route::prefix('templates')->name('templates.')->group(function () {
                Route::get('/', [App\Http\Controllers\Admin\AdTemplateController::class, 'index'])->name('index');
                Route::get('/create', [App\Http\Controllers\Admin\AdTemplateController::class, 'create'])->name('create');
                Route::post('/', [App\Http\Controllers\Admin\AdTemplateController::class, 'store'])->name('store');
                Route::post('/create-from-service', [App\Http\Controllers\Admin\AdTemplateController::class, 'createFromService'])->name('create-from-service');
                Route::post('/create-from-keyword', [App\Http\Controllers\Admin\AdTemplateController::class, 'createFromKeyword'])->name('create-from-keyword');
                Route::get('/cities', [App\Http\Controllers\Admin\AdTemplateController::class, 'getCities'])->name('cities');
                Route::post('/generate-all-links', [App\Http\Controllers\Admin\AdTemplateController::class, 'generateAllLinks'])->name('generate-all-links');
                Route::get('/generate-all-links', [App\Http\Controllers\Admin\AdTemplateController::class, 'generateAllLinks']);
                Route::post('/generate-ads', [App\Http\Controllers\Admin\AdTemplateController::class, 'generateAdsFromTemplate'])->name('generate-ads');
                Route::get('/{template}', [App\Http\Controllers\Admin\AdTemplateController::class, 'show'])->name('show');
                Route::get('/{template}/edit', [App\Http\Controllers\Admin\AdTemplateController::class, 'edit'])->name('edit');
                Route::put('/{template}', [App\Http\Controllers\Admin\AdTemplateController::class, 'update'])->name('update');
                Route::delete('/{template}', [App\Http\Controllers\Admin\AdTemplateController::class, 'destroy'])->name('destroy');
            });
        });
        
        // Note: Les routes pour les avis sont définies dans routes/reviews.php
        
        // Routes pour la page d'accueil
        Route::prefix('homepage')->name('homepage.')->group(function () {
            Route::get('/edit', [ConfigController::class, 'editHomepage'])->name('edit');
            Route::post('/update', [ConfigController::class, 'updateHomepage'])->name('update');
            Route::post('/generate-ai', [ConfigController::class, 'generateHomepageContentAI'])->name('generate-ai');
            Route::post('/generate-all-ai', [ConfigController::class, 'generateAllHomepageContentAI'])->name('generate-all-ai');
        });
        
        // Routes pour le SEO
        Route::prefix('seo')->name('seo.')->group(function () {
            Route::get('/', [App\Http\Controllers\SeoController::class, 'index'])->name('index');
            Route::post('/update', [App\Http\Controllers\SeoController::class, 'update'])->name('update');
            Route::get('/pages', [App\Http\Controllers\SeoController::class, 'pages'])->name('pages');
            Route::post('/pages', [App\Http\Controllers\SeoController::class, 'updatePages'])->name('pages.update');
            Route::post('/validate', [App\Http\Controllers\SeoController::class, 'validateSeoForGoogle'])->name('validate');
            Route::post('/generate-ai', [App\Http\Controllers\SeoController::class, 'generateSeoWithAI'])->name('generate-ai');
            Route::post('/generate-page-ai', [App\Http\Controllers\SeoController::class, 'generatePageSeoWithAI'])->name('generate-page-ai');
        });
        
        // Routes pour les villes
        Route::prefix('cities')->name('cities.')->group(function () {
            Route::get('/', [App\Http\Controllers\CityController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\CityController::class, 'store'])->name('store');
            Route::put('/{id}', [App\Http\Controllers\CityController::class, 'update'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\CityController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/toggle-favorite', [App\Http\Controllers\CityController::class, 'toggleFavorite'])->name('toggle-favorite');
            Route::delete('/destroy/all', [App\Http\Controllers\CityController::class, 'destroyAll'])->name('destroy.all');
            Route::post('/import/json', [App\Http\Controllers\CityController::class, 'importFromJson'])->name('import.json');
            Route::post('/import/department', [App\Http\Controllers\CityController::class, 'importByDepartment'])->name('import.department');
            Route::post('/import/region', [App\Http\Controllers\CityController::class, 'importByRegion'])->name('import.region');
            Route::post('/import/radius', [App\Http\Controllers\CityController::class, 'importByRadius'])->name('import.radius');
            Route::get('/api/cities', [App\Http\Controllers\CityController::class, 'getCities'])->name('api.cities');
            Route::get('/api/departments', [App\Http\Controllers\CityController::class, 'getDepartments'])->name('api.departments');
        });
        
        // Routes pour la configuration légale
        Route::prefix('legal')->name('legal.')->group(function () {
            Route::get('/config', [App\Http\Controllers\LegalAdminController::class, 'index'])->name('config');
            Route::post('/config', [App\Http\Controllers\LegalAdminController::class, 'update'])->name('config.update');
        });
        });
    });

// Routes pour la configuration générale (hors du groupe admin pour éviter les conflits)
    Route::prefix('config')->name('config.')->middleware(['admin.auth'])->group(function () {
        Route::get('/', [ConfigController::class, 'index'])->name('index');
        Route::post('/company', [ConfigController::class, 'updateCompany'])->name('update.company');
        Route::post('/branding', [ConfigController::class, 'updateBranding'])->name('update.branding');
        Route::post('/email', [ConfigController::class, 'updateEmail'])->name('update.email');
    Route::post('/ai', [ConfigController::class, 'updateAI'])->name('update.ai');
        Route::post('/security', [ConfigController::class, 'updateSecurity'])->name('update.security');
        Route::post('/analytics', [ConfigController::class, 'updateAnalytics'])->name('update.analytics');
    Route::post('/conversion', [ConfigController::class, 'updateConversion'])->name('update.conversion');
    Route::post('/social', [ConfigController::class, 'updateSocial'])->name('update.social');
    Route::post('/seo', [ConfigController::class, 'updateSeo'])->name('update.seo');
    Route::post('/reviews', [ConfigController::class, 'updateReviews'])->name('update.reviews');
    Route::delete('/reviews/{id}', [ConfigController::class, 'deleteReview'])->name('reviews.delete');
        Route::post('/test-email', [ConfigController::class, 'testEmail'])->name('test.email');
        Route::post('/test-chatgpt', [ConfigController::class, 'testChatGPT'])->name('test.chatgpt');
        Route::post('/test-groq', [ConfigController::class, 'testGroq'])->name('test.groq');
        Route::post('/test-chatgpt-generate', [ConfigController::class, 'testChatGPTGenerate'])->name('test.chatgpt.generate');
        Route::post('/test-groq-generate', [ConfigController::class, 'testGroqGenerate'])->name('test.groq.generate');
    Route::post('/generate/faqs', [ConfigController::class, 'generateFaqsWithAI'])->name('generate.faqs');
        Route::get('/reset', [ConfigController::class, 'showReset'])->name('reset');
        Route::post('/reset', [ConfigController::class, 'resetConfiguration'])->name('reset.confirm');
    });

// Routes admin pour les services (hors du groupe admin pour éviter les conflits)
Route::prefix('admin/services')->name('services.admin.')->middleware(['admin.auth'])->group(function () {
    Route::get('/', [ServicesController::class, 'index'])->name('index');
    Route::get('/create', [ServicesController::class, 'create'])->name('create');
    Route::post('/', [ServicesController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [ServicesController::class, 'edit'])->name('edit');
    Route::put('/{id}', [ServicesController::class, 'update'])->name('update');
    Route::delete('/{id}', [ServicesController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/regenerate', [ServicesController::class, 'regenerate'])->name('regenerate');
});

// Routes admin pour le portfolio (hors du groupe admin pour éviter les conflits)
    Route::prefix('admin/portfolio')->name('portfolio.admin.')->middleware(['admin.auth'])->group(function () {
    Route::get('/', [PortfolioController::class, 'index'])->name('index');
        Route::get('/edit/{id}', [ConfigController::class, 'editPortfolioItem'])->name('edit');
        Route::post('/', [ConfigController::class, 'addPortfolioItem'])->name('store');
        Route::post('/update/{id}', [ConfigController::class, 'updatePortfolioItem'])->name('update');
        Route::delete('/delete/{id}', [ConfigController::class, 'deletePortfolioItem'])->name('delete');
});

// Route pour le sitemap index (retourne le sitemap_index.xml)
Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap.xml');
Route::get('/sitemap_index.xml', function () {
    $indexPath = public_path('sitemap_index.xml');
    if (file_exists($indexPath)) {
        return response(file_get_contents($indexPath), 200)
            ->header('Content-Type', 'application/xml');
    }
    // Si le fichier n'existe pas, générer via le contrôleur
    $controller = app(\App\Http\Controllers\SitemapController::class);
    return $controller->index();
})->name('sitemap_index.xml');

// Route HTTP pour exécuter l'automatisation SEO (pour Hostinger et services externes)
// Cette route vérifie les conditions et exécute seo:run-automations
Route::get('/schedule/run', function (\Illuminate\Http\Request $request) {
    // Augmenter le timeout pour permettre l'exécution complète
    set_time_limit(300); // 5 minutes
    ini_set('max_execution_time', 300);
    ini_set('default_socket_timeout', 300);
    
    $startTime = microtime(true);
    
    // Récupérer le token depuis la requête ou les settings
    $token = $request->query('token');
    $configuredToken = \App\Models\Setting::get('schedule_run_token', null);
    
    // Si aucun token n'est configuré, générer un token et le retourner avec instructions
    if (empty($configuredToken)) {
        $newToken = \Illuminate\Support\Str::random(32);
        \App\Models\Setting::set('schedule_run_token', $newToken, 'string', 'seo');
        
        return response()->json([
            'message' => 'Token généré. Utilisez cette URL pour exécuter l\'automatisation SEO :',
            'url' => url('/schedule/run?token=' . $newToken),
            'token' => $newToken,
            'instructions' => [
                '1. Configurez cette URL dans le gestionnaire de cron de Hostinger',
                '2. Ou utilisez un service externe (cron-job.org, UptimeRobot, etc.)',
                '3. Appelez cette URL selon l\'intervalle configuré (par défaut: toutes les minutes)',
                '4. Le système vérifiera automatiquement si l\'heure configurée est arrivée',
            ]
        ]);
    }
    
    // Vérifier le token
    if ($token !== $configuredToken) {
        return response()->json([
            'error' => 'Token invalide ou manquant',
            'message' => 'Utilisez le token correct pour accéder à cette route.'
        ], 403);
    }
    
    try {
        // Vérifier si l'automatisation est activée
        $automationEnabled = \App\Models\Setting::get('seo_automation_enabled', true);
        $automationEnabled = filter_var($automationEnabled, FILTER_VALIDATE_BOOLEAN);
        
        if (!$automationEnabled) {
            return response()->json([
                'status' => 'skipped',
                'message' => 'Automatisation SEO désactivée',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
        }
        
        // Vérifier qu'il y a des villes favorites
        $favoriteCitiesCount = \App\Models\City::where('is_favorite', true)->count();
        
        if ($favoriteCitiesCount === 0) {
            return response()->json([
                'status' => 'skipped',
                'message' => 'Aucune ville favorite configurée',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
        }
        
        // Récupérer l'intervalle configuré (en minutes)
        $cronInterval = (int)\App\Models\Setting::get('seo_automation_cron_interval', 1);
        $cronInterval = max(1, min(60, $cronInterval));
        
        // Vérifier le dernier timestamp d'exécution
        $lastExecutionKey = 'seo_automation_last_execution';
        $lastExecution = \App\Models\Setting::get($lastExecutionKey, null);
        $now = now();
        
        // Si une exécution récente existe, vérifier l'intervalle
        if ($lastExecution) {
            $lastExecutionTime = \Carbon\Carbon::parse($lastExecution);
            $minutesSinceLastExecution = $now->diffInMinutes($lastExecutionTime);
            
            // Si moins de X minutes se sont écoulées, ignorer cette exécution
            if ($minutesSinceLastExecution < $cronInterval) {
                $remainingMinutes = $cronInterval - $minutesSinceLastExecution;
                return response()->json([
                    'status' => 'skipped',
                    'message' => "Intervalle non atteint. Dernière exécution il y a {$minutesSinceLastExecution} minute(s). Prochaine exécution dans {$remainingMinutes} minute(s).",
                    'last_execution' => $lastExecutionTime->format('Y-m-d H:i:s'),
                    'current_time' => $now->format('Y-m-d H:i:s'),
                    'interval_minutes' => $cronInterval,
                    'minutes_since_last' => $minutesSinceLastExecution,
                    'timestamp' => $now->format('Y-m-d H:i:s')
                ]);
            }
        }
        
        // Utiliser le scheduler pour vérifier si c'est le bon moment
        $scheduler = app(\App\Services\SeoArticleScheduler::class);
        
        // Récupérer les stats pour le debug
        $scheduleStats = $scheduler->getScheduleStats();
        $nextTime = $scheduler->getNextScheduledTime();
        $nextTimeStr = $nextTime ? $nextTime->format('H:i') : 'N/A';
        
        // Vérifier si c'est le moment de créer un article
        if (!$scheduler->shouldCreateArticle()) {
            // Logger pourquoi l'article n'est pas créé pour le debug
            \Illuminate\Support\Facades\Log::info('Schedule HTTP: Article non créé - Conditions non remplies', [
                'next_scheduled_time' => $nextTimeStr,
                'current_time' => $now->format('H:i'),
                'articles_today' => $scheduleStats['articles_today'] ?? 0,
                'total_articles_per_day' => $scheduleStats['total_articles_per_day'] ?? 0,
                'remaining_today' => $scheduleStats['remaining_today'] ?? 0,
                'should_create_now' => $scheduleStats['should_create_now'] ?? false,
            ]);
            
            return response()->json([
                'status' => 'skipped',
                'message' => "Ce n'est pas encore le moment de créer un article. Prochain créneau: {$nextTimeStr}",
                'next_scheduled_time' => $nextTimeStr,
                'current_time' => $now->format('H:i'),
                'timestamp' => $now->format('Y-m-d H:i:s'),
                'debug' => [
                    'articles_today' => $scheduleStats['articles_today'] ?? 0,
                    'total_articles_per_day' => $scheduleStats['total_articles_per_day'] ?? 0,
                    'remaining_today' => $scheduleStats['remaining_today'] ?? 0,
                    'should_create_now' => $scheduleStats['should_create_now'] ?? false,
                ]
            ]);
        }
        
        // Enregistrer le timestamp de cette exécution
        \App\Models\Setting::set($lastExecutionKey, $now->toDateTimeString(), 'string', 'seo');
        
        // Logger le début de l'exécution
        \Illuminate\Support\Facades\Log::info('Schedule HTTP: Début création article', [
            'current_time' => $now->format('H:i'),
            'next_scheduled_time' => $nextTimeStr,
            'articles_today' => $scheduleStats['articles_today'] ?? 0,
        ]);
        
        // Exécuter la commande seo:run-automations
        $exitCode = \Artisan::call('seo:run-automations');
        $output = \Artisan::output();
        
        // Logger le résultat
        \Illuminate\Support\Facades\Log::info('Schedule HTTP: Résultat exécution', [
            'exit_code' => $exitCode,
            'output_preview' => substr($output, 0, 200),
        ]);
        
        $executionTime = round(microtime(true) - $startTime, 2);
        
        // Parser la sortie
        $citiesCount = 0;
        $jobsCount = 0;
        
        if (preg_match('/Traitement de (\d+) ville\(s\) favorite\(s\)\.\.\./', $output, $matches)) {
            $citiesCount = (int)$matches[1];
        }
        if (preg_match('/(\d+) job\(s\) planifié\(s\)/', $output, $matches)) {
            $jobsCount = (int)$matches[1];
        }
        
        return response()->json([
            'status' => 'success',
            'message' => "Automatisation SEO exécutée avec succès",
            'cities_processed' => $citiesCount,
            'jobs_queued' => $jobsCount,
            'execution_time' => $executionTime . 's',
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'output' => $output
        ]);
        
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Schedule HTTP: Erreur lors de l\'exécution de l\'automatisation SEO', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'status' => 'error',
            'message' => 'Erreur lors de l\'exécution: ' . $e->getMessage(),
            'timestamp' => now()->format('Y-m-d H:i:s')
        ], 500);
    }
})->name('schedule.run');

// Routes admin pour l'indexation (SIMPLIFIÉES)
    Route::prefix('admin/indexation')->name('admin.indexation.')->middleware(['admin.auth'])->group(function () {
    // Page principale
        Route::get('/', [App\Http\Controllers\IndexationController::class, 'index'])->name('index');
    
    // Actions
        Route::post('/update', [App\Http\Controllers\IndexationController::class, 'update'])->name('update');
        Route::post('/update-sitemap', [App\Http\Controllers\IndexationController::class, 'updateSitemap'])->name('update-sitemap');
    Route::post('/verify-urls', [App\Http\Controllers\IndexationController::class, 'verifyUrls'])->name('verify-urls');
        Route::post('/index-urls', [App\Http\Controllers\IndexationController::class, 'indexUrls'])->name('index-urls');
    Route::post('/submit-sitemap', [App\Http\Controllers\IndexationController::class, 'submitSitemap'])->name('submit-sitemap');
});

// Routes admin pour la gestion des mots-clés
Route::prefix('admin/keywords')->name('admin.keywords.')->middleware(['admin.auth'])->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\KeywordController::class, 'index'])->name('index');
    Route::post('/generate', [App\Http\Controllers\Admin\KeywordController::class, 'generateKeywords'])->name('generate');
    Route::post('/save', [App\Http\Controllers\Admin\KeywordController::class, 'saveKeywords'])->name('save');
    Route::post('/image', [App\Http\Controllers\Admin\KeywordController::class, 'storeImage'])->name('image.store');
    Route::put('/image/{keywordImage}', [App\Http\Controllers\Admin\KeywordController::class, 'updateImage'])->name('image.update');
    Route::delete('/image/{keywordImage}', [App\Http\Controllers\Admin\KeywordController::class, 'destroyImage'])->name('image.destroy');
});

// Routes admin pour le simulateur de coûts
Route::prefix('admin/simulator')->name('admin.simulator.')->middleware(['admin.auth'])->group(function () {
    Route::get('/config', [App\Http\Controllers\CostSimulatorController::class, 'config'])->name('config');
    Route::post('/save-config', [App\Http\Controllers\CostSimulatorController::class, 'saveConfig'])->name('save-config');
});

// Routes admin pour la configuration du cron
Route::prefix('admin/cron-config')->name('admin.cron-config.')->middleware(['admin.auth'])->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\CronConfigController::class, 'index'])->name('index');
    Route::get('/token', [App\Http\Controllers\Admin\CronConfigController::class, 'getToken'])->name('token');
    Route::post('/regenerate-token', [App\Http\Controllers\Admin\CronConfigController::class, 'regenerateToken'])->name('regenerate-token');
    Route::post('/test-http', [App\Http\Controllers\Admin\CronConfigController::class, 'testHttp'])->name('test-http');
});

// Routes admin pour l'automatisation SEO
Route::prefix('admin/seo-automation')->name('admin.seo-automation.')->middleware(['admin.auth', 'seo.automation.password'])->group(function () {
    // CRITIQUE : Routes POST/GET spécifiques AVANT routes avec paramètres {seoAutomation}
    Route::post('/google-index-url', [SeoAutomationController::class, 'indexArticle'])->name('google-index-url');
    
    Route::get('/', [SeoAutomationController::class, 'index'])->name('index');
    Route::get('/password', [SeoAutomationController::class, 'passwordForm'])->name('password');
    Route::post('/password', [SeoAutomationController::class, 'verifyPassword'])->name('verify-password');
    Route::post('/run', [SeoAutomationController::class, 'run'])->name('run');
    Route::get('/run', [SeoAutomationController::class, 'redirectRunGet'])->name('run.get');
    Route::post('/retry-pending-failed', [SeoAutomationController::class, 'retryPendingAndFailed'])->name('retry-pending-failed');
    Route::post('/toggle', [SeoAutomationController::class, 'toggle'])->name('toggle');
    Route::post('/force-run', [SeoAutomationController::class, 'forceRun'])->name('force-run');
    Route::post('/execute-now', [SeoAutomationController::class, 'executeNow'])->name('execute-now');
    Route::get('/schedule/token', [SeoAutomationController::class, 'getScheduleToken'])->name('get-schedule-token');
    Route::get('/schedule-token', [SeoAutomationController::class, 'getScheduleToken'])->name('schedule-token');
    Route::post('/schedule/token/regenerate', [SeoAutomationController::class, 'regenerateScheduleToken'])->name('regenerate-schedule-token');
    Route::post('/schedule/test', [SeoAutomationController::class, 'testScheduleHttp'])->name('test-schedule-http');
    Route::post('/scheduler/test', [SeoAutomationController::class, 'testScheduler'])->name('test-scheduler');
    Route::post('/reset-all', [SeoAutomationController::class, 'resetAll'])->name('reset-all');
    Route::post('/save-time', [SeoAutomationController::class, 'saveTime'])->name('save-time');
    Route::post('/upload-og-image', [SeoAutomationController::class, 'uploadOgImage'])->name('upload-og-image');
    Route::post('/save-og-image', [SeoAutomationController::class, 'saveOgImage'])->name('save-og-image');
    Route::post('/generate-keywords', [SeoAutomationController::class, 'generateKeywords'])->name('generate-keywords');
    Route::post('/save-keywords', [SeoAutomationController::class, 'saveKeywords'])->name('save-keywords');
    Route::post('/test-connections', [SeoAutomationController::class, 'testConnections'])->name('test-connections');
    Route::post('/test', [SeoAutomationController::class, 'testConnections'])->name('test');
    Route::post('/save-config', [SeoAutomationController::class, 'saveApiConfig'])->name('save-config');
    Route::post('/test-api', [SeoAutomationController::class, 'testApi'])->name('test-api');
    Route::post('/keyword-image', [SeoAutomationController::class, 'storeKeywordImage'])->name('keyword-image.store');
    Route::delete('/keyword-image/{keywordImage}', [SeoAutomationController::class, 'destroyKeywordImage'])->name('keyword-image.destroy');
    
    // IMPORTANT : Routes avec paramètres EN DERNIER (après toutes les routes spécifiques)
    Route::post('/city/{city}', [SeoAutomationController::class, 'runForCity'])->name('run-city');
    Route::post('/{seoAutomation}/retry', [SeoAutomationController::class, 'retry'])->name('retry');
    Route::delete('/{seoAutomation}', [SeoAutomationController::class, 'destroy'])->name('destroy');
});
