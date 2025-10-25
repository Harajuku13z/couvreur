<?php

use App\Http\Controllers\ReviewsController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/reviews')->name('admin.reviews.')->middleware(['auth', 'admin'])->group(function () {
    // Routes principales
    Route::get('/', [ReviewsController::class, 'index'])->name('index');
    Route::post('/delete-all', [ReviewsController::class, 'deleteAll'])->name('delete-all');
    Route::post('/{id}/toggle-status', [ReviewsController::class, 'toggleStatus'])->name('toggle-status');
    Route::delete('/{id}', [ReviewsController::class, 'delete'])->name('delete');
    
    // Configuration Google
    Route::get('/google/config', [ReviewsController::class, 'googleConfig'])->name('google.config');
    Route::post('/google/config', [ReviewsController::class, 'saveGoogleConfig'])->name('google.config.save');
    Route::post('/google/import-auto', [ReviewsController::class, 'importGoogleAuto'])->name('google.import-auto');
    Route::post('/google/test-outscraper', [ReviewsController::class, 'testOutscraperConnection'])->name('google.test-outscraper');
});
