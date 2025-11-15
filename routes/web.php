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

// Routes publiques pour le formulaire
    Route::get('/form/{step}', [FormControllerSimple::class, 'showStep'])->name('form.step');
    Route::post('/form/{step}/submit', [FormControllerSimple::class, 'submitStep'])->name('form.submit');
Route::get('/form/success', [FormControllerSimple::class, 'success'])->name('form.success');

// Routes publiques pour le portfolio
Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio.index');
Route::get('/portfolio/{slug}', [PortfolioController::class, 'show'])->name('portfolio.show');

// Routes publiques pour le blog
Route::get('/blog', [ArticleController::class, 'index'])->name('blog.index');
Route::get('/blog/{article}', [ArticleController::class, 'show'])->name('blog.show');

// Route publique pour le contact
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');

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
        Route::get('/abandoned-submissions/{id}', [AdminController::class, 'showAbandonedSubmission'])->name('abandoned-submission.show');
        Route::get('/export/submissions', [AdminController::class, 'exportSubmissions'])->name('export.submissions');
        Route::get('/export/abandoned-submissions', [AdminController::class, 'exportAbandonedSubmissions'])->name('export.abandoned-submissions');
        Route::get('/statistics', [AdminController::class, 'statistics'])->name('statistics');
        Route::get('/phone-calls', [AdminController::class, 'phoneCalls'])->name('phone-calls');
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
            Route::post('/{ad}/publish', [App\Http\Controllers\AdAdminController::class, 'publish'])->name('publish');
            Route::post('/{ad}/archive', [App\Http\Controllers\AdAdminController::class, 'archive'])->name('archive');
            Route::delete('/{ad}', [App\Http\Controllers\AdAdminController::class, 'destroy'])->name('destroy');
            Route::post('/create-manual', [App\Http\Controllers\AdAdminController::class, 'createManual'])->name('create-manual');
            Route::post('/remove-duplicates', [App\Http\Controllers\AdAdminController::class, 'removeDuplicates'])->name('remove-duplicates');
            Route::delete('/', [App\Http\Controllers\AdAdminController::class, 'deleteAll'])->name('delete-all');
        });
        
        // Routes pour les avis
        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', [ReviewsController::class, 'index'])->name('index');
            Route::get('/serp-config', [ReviewsController::class, 'serpConfig'])->name('serp-config');
            Route::post('/serp-config', [ReviewsController::class, 'saveSerpConfig'])->name('save-serp-config');
            Route::post('/test-serp', [ReviewsController::class, 'testSerpConnection'])->name('test-serp');
            Route::post('/import-serp', [ReviewsController::class, 'importSerpReviews'])->name('import-serp');
            Route::get('/create', [ReviewsController::class, 'create'])->name('create');
            Route::post('/', [ReviewsController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [ReviewsController::class, 'edit'])->name('edit');
            Route::put('/{id}', [ReviewsController::class, 'update'])->name('update');
            Route::delete('/', [ReviewsController::class, 'deleteAll'])->name('delete-all');
            Route::post('/{id}/toggle', [ReviewsController::class, 'toggleStatus'])->name('toggle-status');
            Route::delete('/{id}', [ReviewsController::class, 'delete'])->name('delete');
        });
    });
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

// Routes admin pour l'indexation
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
    Route::post('/verify-status', [App\Http\Controllers\IndexationController::class, 'verifyStatus'])->name('verify-status');
    Route::post('/verify-statuses', [App\Http\Controllers\IndexationController::class, 'verifyStatuses'])->name('verify-statuses');
    Route::get('/statuses', [App\Http\Controllers\IndexationController::class, 'getStatuses'])->name('statuses');
});

// Routes admin pour l'automatisation SEO
Route::prefix('admin/seo-automation')->name('admin.seo-automation.')->middleware(['admin.auth'])->group(function () {
    Route::get('/', [SeoAutomationController::class, 'index'])->name('index');
    Route::get('/password', [SeoAutomationController::class, 'passwordForm'])->name('password');
    Route::post('/password', [SeoAutomationController::class, 'verifyPassword'])->name('verify-password');
    Route::post('/run', [SeoAutomationController::class, 'run'])->name('run');
    Route::get('/run', [SeoAutomationController::class, 'redirectRunGet'])->name('run.get');
    Route::post('/city/{city}', [SeoAutomationController::class, 'runForCity'])->name('run-city');
    Route::post('/{seoAutomation}/retry', [SeoAutomationController::class, 'retry'])->name('retry');
    Route::delete('/{seoAutomation}', [SeoAutomationController::class, 'destroy'])->name('destroy');
    Route::post('/retry-pending-failed', [SeoAutomationController::class, 'retryPendingAndFailed'])->name('retry-pending-failed');
    Route::post('/toggle', [SeoAutomationController::class, 'toggle'])->name('toggle');
    Route::post('/force-run', [SeoAutomationController::class, 'forceRun'])->name('force-run');
    Route::post('/execute-now', [SeoAutomationController::class, 'executeNow'])->name('execute-now');
    Route::get('/schedule/token', [SeoAutomationController::class, 'getScheduleToken'])->name('get-schedule-token');
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
    Route::post('/save-config', [SeoAutomationController::class, 'saveApiConfig'])->name('save-config');
    Route::post('/test-api', [SeoAutomationController::class, 'testApi'])->name('test-api');
    Route::post('/keyword-image', [SeoAutomationController::class, 'storeKeywordImage'])->name('store-keyword-image');
    Route::delete('/keyword-image/{keywordImage}', [SeoAutomationController::class, 'destroyKeywordImage'])->name('destroy-keyword-image');
});
