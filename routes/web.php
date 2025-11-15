<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormControllerSimple;
use App\Http\Controllers\HomeController;
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

// Routes publiques de fallback (pour éviter les erreurs si les pages n'existent pas encore)
Route::get('/portfolio', function () {
    if (\Illuminate\Support\Facades\View::exists('portfolio.index')) {
        return view('portfolio.index', ['currentPage' => 'portfolio']);
    }
    abort(404);
})->name('portfolio.index');

Route::get('/blog', function () {
    if (\Illuminate\Support\Facades\View::exists('blog.index')) {
        return view('blog.index', ['currentPage' => 'blog']);
    }
    abort(404);
})->name('blog.index');

Route::get('/contact', function () {
    if (\Illuminate\Support\Facades\View::exists('contact.index')) {
        return view('contact.index', ['currentPage' => 'contact']);
    }
    abort(404);
})->name('contact');

Route::get('/ads', function () {
    if (\Illuminate\Support\Facades\View::exists('ads.index')) {
        return view('ads.index', ['currentPage' => 'ads']);
    }
    abort(404);
})->name('ads.index');

Route::get('/reviews', function () {
    if (\Illuminate\Support\Facades\View::exists('reviews.all')) {
        return view('reviews.all', ['currentPage' => 'reviews']);
    }
    abort(404);
})->name('reviews.all');

Route::get('/legal/mentions', function () {
    if (\Illuminate\Support\Facades\View::exists('legal.mentions')) {
        return view('legal.mentions');
    }
    abort(404);
})->name('legal.mentions');

Route::get('/legal/privacy', function () {
    if (\Illuminate\Support\Facades\View::exists('legal.privacy')) {
        return view('legal.privacy');
    }
    abort(404);
})->name('legal.privacy');

Route::get('/legal/cgv', function () {
    if (\Illuminate\Support\Facades\View::exists('legal.cgv')) {
        return view('legal.cgv');
    }
    abort(404);
})->name('legal.cgv');

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
