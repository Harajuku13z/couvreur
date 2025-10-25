<?php

use App\Http\Controllers\ReviewsController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/reviews')->name('admin.reviews.')->middleware(['admin.auth'])->group(function () {
    // Routes principales
    Route::get('/', [ReviewsController::class, 'index'])->name('index');
    Route::post('/delete-all', [ReviewsController::class, 'deleteAll'])->name('delete-all');
    Route::post('/{id}/toggle-status', [ReviewsController::class, 'toggleStatus'])->name('toggle-status');
    Route::delete('/{id}', [ReviewsController::class, 'delete'])->name('delete');
    
    // Configuration Google Places
    Route::get('/google/config', [ReviewsController::class, 'googleConfig'])->name('google.config');
    Route::post('/google/config', [ReviewsController::class, 'saveGoogleConfig'])->name('google.config.save');
    Route::post('/google/test', [ReviewsController::class, 'testGoogleConnection'])->name('google.test');
    Route::post('/google/import', [ReviewsController::class, 'importGoogleReviews'])->name('google.import');
    Route::post('/google/import-all', [ReviewsController::class, 'importAllGoogleReviews'])->name('google.import-all');
    
    // OAuth2 Google My Business
    Route::get('/google/oauth', [ReviewsController::class, 'googleOAuth'])->name('google.oauth');
    Route::get('/google/oauth/callback', [ReviewsController::class, 'googleOAuthCallback'])->name('google.oauth.callback');
});