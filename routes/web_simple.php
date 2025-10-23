<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormControllerSimple;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ConfigController;

/**
 * ROUTES ULTRA-SIMPLES
 * Navigation directe, pas de AJAX compliqué
 */

// Setup Routes (no middleware)
Route::get('/setup', [ConfigController::class, 'showSetup'])->name('config.setup');
Route::post('/setup', [ConfigController::class, 'processSetup'])->name('config.setup.process');

// Main routes (protected by setup check)
Route::middleware(['check.setup'])->group(function () {
    
    // ===== FORMULAIRE (SIMPLE) =====
    // Landing page
    Route::get('/', [FormControllerSimple::class, 'index'])->name('home');
    
    // Afficher une étape
    Route::get('/form/{step}', [FormControllerSimple::class, 'showStep'])->name('form.step');
    
    // Soumettre une étape (POST) - Navigation directe
    Route::post('/form/{step}/submit', [FormControllerSimple::class, 'submitStep'])->name('form.submit');
    
    // Revenir à l'étape précédente (GET)
    Route::get('/form/{step}/previous', [FormControllerSimple::class, 'previousStep'])->name('form.previous');
    
    // Page de succès
    Route::get('/form/success', [FormControllerSimple::class, 'success'])->name('form.success');

    // ===== ADMIN =====
    Route::prefix('admin')->name('admin.')->group(function () {
        // Routes publiques (login)
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
        });
    });

    // ===== CONFIGURATION =====
    Route::prefix('config')->name('config.')->middleware(['admin.auth'])->group(function () {
        Route::get('/', [ConfigController::class, 'index'])->name('index');
        Route::post('/company', [ConfigController::class, 'updateCompany'])->name('update.company');
        Route::post('/branding', [ConfigController::class, 'updateBranding'])->name('update.branding');
        Route::post('/email', [ConfigController::class, 'updateEmail'])->name('update.email');
        Route::post('/social', [ConfigController::class, 'updateSocial'])->name('update.social');
        Route::post('/seo', [ConfigController::class, 'updateSeo'])->name('update.seo');
        Route::post('/reviews', [ConfigController::class, 'updateReviews'])->name('update.reviews');
        Route::delete('/reviews/{id}', [ConfigController::class, 'deleteReview'])->name('reviews.delete');
        Route::post('/test-email', [ConfigController::class, 'testEmail'])->name('test.email');
        Route::get('/reset', [ConfigController::class, 'showReset'])->name('reset');
        Route::post('/reset', [ConfigController::class, 'resetConfiguration'])->name('reset.confirm');
    });
});



use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormControllerSimple;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ConfigController;

/**
 * ROUTES ULTRA-SIMPLES
 * Navigation directe, pas de AJAX compliqué
 */

// Setup Routes (no middleware)
Route::get('/setup', [ConfigController::class, 'showSetup'])->name('config.setup');
Route::post('/setup', [ConfigController::class, 'processSetup'])->name('config.setup.process');

// Main routes (protected by setup check)
Route::middleware(['check.setup'])->group(function () {
    
    // ===== FORMULAIRE (SIMPLE) =====
    // Landing page
    Route::get('/', [FormControllerSimple::class, 'index'])->name('home');
    
    // Afficher une étape
    Route::get('/form/{step}', [FormControllerSimple::class, 'showStep'])->name('form.step');
    
    // Soumettre une étape (POST) - Navigation directe
    Route::post('/form/{step}/submit', [FormControllerSimple::class, 'submitStep'])->name('form.submit');
    
    // Revenir à l'étape précédente (GET)
    Route::get('/form/{step}/previous', [FormControllerSimple::class, 'previousStep'])->name('form.previous');
    
    // Page de succès
    Route::get('/form/success', [FormControllerSimple::class, 'success'])->name('form.success');

    // ===== ADMIN =====
    Route::prefix('admin')->name('admin.')->group(function () {
        // Routes publiques (login)
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
        });
    });

    // ===== CONFIGURATION =====
    Route::prefix('config')->name('config.')->middleware(['admin.auth'])->group(function () {
        Route::get('/', [ConfigController::class, 'index'])->name('index');
        Route::post('/company', [ConfigController::class, 'updateCompany'])->name('update.company');
        Route::post('/branding', [ConfigController::class, 'updateBranding'])->name('update.branding');
        Route::post('/email', [ConfigController::class, 'updateEmail'])->name('update.email');
        Route::post('/social', [ConfigController::class, 'updateSocial'])->name('update.social');
        Route::post('/seo', [ConfigController::class, 'updateSeo'])->name('update.seo');
        Route::post('/reviews', [ConfigController::class, 'updateReviews'])->name('update.reviews');
        Route::delete('/reviews/{id}', [ConfigController::class, 'deleteReview'])->name('reviews.delete');
        Route::post('/test-email', [ConfigController::class, 'testEmail'])->name('test.email');
        Route::get('/reset', [ConfigController::class, 'showReset'])->name('reset');
        Route::post('/reset', [ConfigController::class, 'resetConfiguration'])->name('reset.confirm');
    });
});


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormControllerSimple;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ConfigController;

/**
 * ROUTES ULTRA-SIMPLES
 * Navigation directe, pas de AJAX compliqué
 */

// Setup Routes (no middleware)
Route::get('/setup', [ConfigController::class, 'showSetup'])->name('config.setup');
Route::post('/setup', [ConfigController::class, 'processSetup'])->name('config.setup.process');

// Main routes (protected by setup check)
Route::middleware(['check.setup'])->group(function () {
    
    // ===== FORMULAIRE (SIMPLE) =====
    // Landing page
    Route::get('/', [FormControllerSimple::class, 'index'])->name('home');
    
    // Afficher une étape
    Route::get('/form/{step}', [FormControllerSimple::class, 'showStep'])->name('form.step');
    
    // Soumettre une étape (POST) - Navigation directe
    Route::post('/form/{step}/submit', [FormControllerSimple::class, 'submitStep'])->name('form.submit');
    
    // Revenir à l'étape précédente (GET)
    Route::get('/form/{step}/previous', [FormControllerSimple::class, 'previousStep'])->name('form.previous');
    
    // Page de succès
    Route::get('/form/success', [FormControllerSimple::class, 'success'])->name('form.success');

    // ===== ADMIN =====
    Route::prefix('admin')->name('admin.')->group(function () {
        // Routes publiques (login)
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
        });
    });

    // ===== CONFIGURATION =====
    Route::prefix('config')->name('config.')->middleware(['admin.auth'])->group(function () {
        Route::get('/', [ConfigController::class, 'index'])->name('index');
        Route::post('/company', [ConfigController::class, 'updateCompany'])->name('update.company');
        Route::post('/branding', [ConfigController::class, 'updateBranding'])->name('update.branding');
        Route::post('/email', [ConfigController::class, 'updateEmail'])->name('update.email');
        Route::post('/social', [ConfigController::class, 'updateSocial'])->name('update.social');
        Route::post('/seo', [ConfigController::class, 'updateSeo'])->name('update.seo');
        Route::post('/reviews', [ConfigController::class, 'updateReviews'])->name('update.reviews');
        Route::delete('/reviews/{id}', [ConfigController::class, 'deleteReview'])->name('reviews.delete');
        Route::post('/test-email', [ConfigController::class, 'testEmail'])->name('test.email');
        Route::get('/reset', [ConfigController::class, 'showReset'])->name('reset');
        Route::post('/reset', [ConfigController::class, 'resetConfiguration'])->name('reset.confirm');
    });
});



use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormControllerSimple;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ConfigController;

/**
 * ROUTES ULTRA-SIMPLES
 * Navigation directe, pas de AJAX compliqué
 */

// Setup Routes (no middleware)
Route::get('/setup', [ConfigController::class, 'showSetup'])->name('config.setup');
Route::post('/setup', [ConfigController::class, 'processSetup'])->name('config.setup.process');

// Main routes (protected by setup check)
Route::middleware(['check.setup'])->group(function () {
    
    // ===== FORMULAIRE (SIMPLE) =====
    // Landing page
    Route::get('/', [FormControllerSimple::class, 'index'])->name('home');
    
    // Afficher une étape
    Route::get('/form/{step}', [FormControllerSimple::class, 'showStep'])->name('form.step');
    
    // Soumettre une étape (POST) - Navigation directe
    Route::post('/form/{step}/submit', [FormControllerSimple::class, 'submitStep'])->name('form.submit');
    
    // Revenir à l'étape précédente (GET)
    Route::get('/form/{step}/previous', [FormControllerSimple::class, 'previousStep'])->name('form.previous');
    
    // Page de succès
    Route::get('/form/success', [FormControllerSimple::class, 'success'])->name('form.success');

    // ===== ADMIN =====
    Route::prefix('admin')->name('admin.')->group(function () {
        // Routes publiques (login)
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
        });
    });

    // ===== CONFIGURATION =====
    Route::prefix('config')->name('config.')->middleware(['admin.auth'])->group(function () {
        Route::get('/', [ConfigController::class, 'index'])->name('index');
        Route::post('/company', [ConfigController::class, 'updateCompany'])->name('update.company');
        Route::post('/branding', [ConfigController::class, 'updateBranding'])->name('update.branding');
        Route::post('/email', [ConfigController::class, 'updateEmail'])->name('update.email');
        Route::post('/social', [ConfigController::class, 'updateSocial'])->name('update.social');
        Route::post('/seo', [ConfigController::class, 'updateSeo'])->name('update.seo');
        Route::post('/reviews', [ConfigController::class, 'updateReviews'])->name('update.reviews');
        Route::delete('/reviews/{id}', [ConfigController::class, 'deleteReview'])->name('reviews.delete');
        Route::post('/test-email', [ConfigController::class, 'testEmail'])->name('test.email');
        Route::get('/reset', [ConfigController::class, 'showReset'])->name('reset');
        Route::post('/reset', [ConfigController::class, 'resetConfiguration'])->name('reset.confirm');
    });
});


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormControllerSimple;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ConfigController;

/**
 * ROUTES ULTRA-SIMPLES
 * Navigation directe, pas de AJAX compliqué
 */

// Setup Routes (no middleware)
Route::get('/setup', [ConfigController::class, 'showSetup'])->name('config.setup');
Route::post('/setup', [ConfigController::class, 'processSetup'])->name('config.setup.process');

// Main routes (protected by setup check)
Route::middleware(['check.setup'])->group(function () {
    
    // ===== FORMULAIRE (SIMPLE) =====
    // Landing page
    Route::get('/', [FormControllerSimple::class, 'index'])->name('home');
    
    // Afficher une étape
    Route::get('/form/{step}', [FormControllerSimple::class, 'showStep'])->name('form.step');
    
    // Soumettre une étape (POST) - Navigation directe
    Route::post('/form/{step}/submit', [FormControllerSimple::class, 'submitStep'])->name('form.submit');
    
    // Revenir à l'étape précédente (GET)
    Route::get('/form/{step}/previous', [FormControllerSimple::class, 'previousStep'])->name('form.previous');
    
    // Page de succès
    Route::get('/form/success', [FormControllerSimple::class, 'success'])->name('form.success');

    // ===== ADMIN =====
    Route::prefix('admin')->name('admin.')->group(function () {
        // Routes publiques (login)
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
        });
    });

    // ===== CONFIGURATION =====
    Route::prefix('config')->name('config.')->middleware(['admin.auth'])->group(function () {
        Route::get('/', [ConfigController::class, 'index'])->name('index');
        Route::post('/company', [ConfigController::class, 'updateCompany'])->name('update.company');
        Route::post('/branding', [ConfigController::class, 'updateBranding'])->name('update.branding');
        Route::post('/email', [ConfigController::class, 'updateEmail'])->name('update.email');
        Route::post('/social', [ConfigController::class, 'updateSocial'])->name('update.social');
        Route::post('/seo', [ConfigController::class, 'updateSeo'])->name('update.seo');
        Route::post('/reviews', [ConfigController::class, 'updateReviews'])->name('update.reviews');
        Route::delete('/reviews/{id}', [ConfigController::class, 'deleteReview'])->name('reviews.delete');
        Route::post('/test-email', [ConfigController::class, 'testEmail'])->name('test.email');
        Route::get('/reset', [ConfigController::class, 'showReset'])->name('reset');
        Route::post('/reset', [ConfigController::class, 'resetConfiguration'])->name('reset.confirm');
    });
});



use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormControllerSimple;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ConfigController;

/**
 * ROUTES ULTRA-SIMPLES
 * Navigation directe, pas de AJAX compliqué
 */

// Setup Routes (no middleware)
Route::get('/setup', [ConfigController::class, 'showSetup'])->name('config.setup');
Route::post('/setup', [ConfigController::class, 'processSetup'])->name('config.setup.process');

// Main routes (protected by setup check)
Route::middleware(['check.setup'])->group(function () {
    
    // ===== FORMULAIRE (SIMPLE) =====
    // Landing page
    Route::get('/', [FormControllerSimple::class, 'index'])->name('home');
    
    // Afficher une étape
    Route::get('/form/{step}', [FormControllerSimple::class, 'showStep'])->name('form.step');
    
    // Soumettre une étape (POST) - Navigation directe
    Route::post('/form/{step}/submit', [FormControllerSimple::class, 'submitStep'])->name('form.submit');
    
    // Revenir à l'étape précédente (GET)
    Route::get('/form/{step}/previous', [FormControllerSimple::class, 'previousStep'])->name('form.previous');
    
    // Page de succès
    Route::get('/form/success', [FormControllerSimple::class, 'success'])->name('form.success');

    // ===== ADMIN =====
    Route::prefix('admin')->name('admin.')->group(function () {
        // Routes publiques (login)
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
        });
    });

    // ===== CONFIGURATION =====
    Route::prefix('config')->name('config.')->middleware(['admin.auth'])->group(function () {
        Route::get('/', [ConfigController::class, 'index'])->name('index');
        Route::post('/company', [ConfigController::class, 'updateCompany'])->name('update.company');
        Route::post('/branding', [ConfigController::class, 'updateBranding'])->name('update.branding');
        Route::post('/email', [ConfigController::class, 'updateEmail'])->name('update.email');
        Route::post('/social', [ConfigController::class, 'updateSocial'])->name('update.social');
        Route::post('/seo', [ConfigController::class, 'updateSeo'])->name('update.seo');
        Route::post('/reviews', [ConfigController::class, 'updateReviews'])->name('update.reviews');
        Route::delete('/reviews/{id}', [ConfigController::class, 'deleteReview'])->name('reviews.delete');
        Route::post('/test-email', [ConfigController::class, 'testEmail'])->name('test.email');
        Route::get('/reset', [ConfigController::class, 'showReset'])->name('reset');
        Route::post('/reset', [ConfigController::class, 'resetConfiguration'])->name('reset.confirm');
    });
});


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormControllerSimple;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ConfigController;

/**
 * ROUTES ULTRA-SIMPLES
 * Navigation directe, pas de AJAX compliqué
 */

// Setup Routes (no middleware)
Route::get('/setup', [ConfigController::class, 'showSetup'])->name('config.setup');
Route::post('/setup', [ConfigController::class, 'processSetup'])->name('config.setup.process');

// Main routes (protected by setup check)
Route::middleware(['check.setup'])->group(function () {
    
    // ===== FORMULAIRE (SIMPLE) =====
    // Landing page
    Route::get('/', [FormControllerSimple::class, 'index'])->name('home');
    
    // Afficher une étape
    Route::get('/form/{step}', [FormControllerSimple::class, 'showStep'])->name('form.step');
    
    // Soumettre une étape (POST) - Navigation directe
    Route::post('/form/{step}/submit', [FormControllerSimple::class, 'submitStep'])->name('form.submit');
    
    // Revenir à l'étape précédente (GET)
    Route::get('/form/{step}/previous', [FormControllerSimple::class, 'previousStep'])->name('form.previous');
    
    // Page de succès
    Route::get('/form/success', [FormControllerSimple::class, 'success'])->name('form.success');

    // ===== ADMIN =====
    Route::prefix('admin')->name('admin.')->group(function () {
        // Routes publiques (login)
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
        });
    });

    // ===== CONFIGURATION =====
    Route::prefix('config')->name('config.')->middleware(['admin.auth'])->group(function () {
        Route::get('/', [ConfigController::class, 'index'])->name('index');
        Route::post('/company', [ConfigController::class, 'updateCompany'])->name('update.company');
        Route::post('/branding', [ConfigController::class, 'updateBranding'])->name('update.branding');
        Route::post('/email', [ConfigController::class, 'updateEmail'])->name('update.email');
        Route::post('/social', [ConfigController::class, 'updateSocial'])->name('update.social');
        Route::post('/seo', [ConfigController::class, 'updateSeo'])->name('update.seo');
        Route::post('/reviews', [ConfigController::class, 'updateReviews'])->name('update.reviews');
        Route::delete('/reviews/{id}', [ConfigController::class, 'deleteReview'])->name('reviews.delete');
        Route::post('/test-email', [ConfigController::class, 'testEmail'])->name('test.email');
        Route::get('/reset', [ConfigController::class, 'showReset'])->name('reset');
        Route::post('/reset', [ConfigController::class, 'resetConfiguration'])->name('reset.confirm');
    });
});



use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormControllerSimple;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ConfigController;

/**
 * ROUTES ULTRA-SIMPLES
 * Navigation directe, pas de AJAX compliqué
 */

// Setup Routes (no middleware)
Route::get('/setup', [ConfigController::class, 'showSetup'])->name('config.setup');
Route::post('/setup', [ConfigController::class, 'processSetup'])->name('config.setup.process');

// Main routes (protected by setup check)
Route::middleware(['check.setup'])->group(function () {
    
    // ===== FORMULAIRE (SIMPLE) =====
    // Landing page
    Route::get('/', [FormControllerSimple::class, 'index'])->name('home');
    
    // Afficher une étape
    Route::get('/form/{step}', [FormControllerSimple::class, 'showStep'])->name('form.step');
    
    // Soumettre une étape (POST) - Navigation directe
    Route::post('/form/{step}/submit', [FormControllerSimple::class, 'submitStep'])->name('form.submit');
    
    // Revenir à l'étape précédente (GET)
    Route::get('/form/{step}/previous', [FormControllerSimple::class, 'previousStep'])->name('form.previous');
    
    // Page de succès
    Route::get('/form/success', [FormControllerSimple::class, 'success'])->name('form.success');

    // ===== ADMIN =====
    Route::prefix('admin')->name('admin.')->group(function () {
        // Routes publiques (login)
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
        });
    });

    // ===== CONFIGURATION =====
    Route::prefix('config')->name('config.')->middleware(['admin.auth'])->group(function () {
        Route::get('/', [ConfigController::class, 'index'])->name('index');
        Route::post('/company', [ConfigController::class, 'updateCompany'])->name('update.company');
        Route::post('/branding', [ConfigController::class, 'updateBranding'])->name('update.branding');
        Route::post('/email', [ConfigController::class, 'updateEmail'])->name('update.email');
        Route::post('/social', [ConfigController::class, 'updateSocial'])->name('update.social');
        Route::post('/seo', [ConfigController::class, 'updateSeo'])->name('update.seo');
        Route::post('/reviews', [ConfigController::class, 'updateReviews'])->name('update.reviews');
        Route::delete('/reviews/{id}', [ConfigController::class, 'deleteReview'])->name('reviews.delete');
        Route::post('/test-email', [ConfigController::class, 'testEmail'])->name('test.email');
        Route::get('/reset', [ConfigController::class, 'showReset'])->name('reset');
        Route::post('/reset', [ConfigController::class, 'resetConfiguration'])->name('reset.confirm');
    });
});





























