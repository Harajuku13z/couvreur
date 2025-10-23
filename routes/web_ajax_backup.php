<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ConfigController;

// Setup Routes (no middleware, available before setup is completed)
Route::get('/setup', [ConfigController::class, 'showSetup'])->name('config.setup');
Route::post('/setup', [ConfigController::class, 'processSetup'])->name('config.setup.process');

// Main routes (protected by setup check)
Route::middleware(['check.setup'])->group(function () {
    // Home Route
    Route::get('/', [FormController::class, 'index'])->name('home');
    
    // Routes du formulaire
    Route::get('/form/{step}', [FormController::class, 'showStep'])->name('form.step');
    Route::post('/form/{step}/save', [FormController::class, 'saveStep'])->name('form.save');
    Route::post('/form/{step}/next', [FormController::class, 'nextStep'])->name('form.next');
    Route::post('/form/{step}/previous', [FormController::class, 'previousStep'])->name('form.previous');
    Route::post('/form/abandon', [FormController::class, 'abandon'])->name('form.abandon');
    Route::get('/form/success', [FormController::class, 'success'])->name('form.success');

    // Routes admin
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

    // Configuration Routes (require admin auth)
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

    // API Routes
    Route::prefix('api')->group(function () {
        Route::post('/track-call', [FormController::class, 'trackCall']);
    });
});
