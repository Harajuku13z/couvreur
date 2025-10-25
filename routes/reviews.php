<?php

use App\Http\Controllers\ReviewsController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/reviews')->name('admin.reviews.')->middleware(['admin.auth'])->group(function () {
    // Routes principales
    Route::get('/', [ReviewsController::class, 'index'])->name('index');
    Route::post('/delete-all', [ReviewsController::class, 'deleteAll'])->name('delete-all');
    Route::post('/{id}/toggle-status', [ReviewsController::class, 'toggleStatus'])->name('toggle-status');
    Route::delete('/{id}', [ReviewsController::class, 'delete'])->name('delete');
    
    // Gestion manuelle des avis
    Route::get('/create', [ReviewsController::class, 'create'])->name('create');
    Route::post('/store', [ReviewsController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [ReviewsController::class, 'edit'])->name('edit');
    Route::put('/{id}/update', [ReviewsController::class, 'update'])->name('update');
    
    // Configuration SerpAPI
    Route::get('/serp/config', [ReviewsController::class, 'serpConfig'])->name('serp.config');
    Route::post('/serp/config', [ReviewsController::class, 'saveSerpConfig'])->name('serp.config.save');
    Route::post('/serp/test', [ReviewsController::class, 'testSerpConnection'])->name('serp.test');
    Route::post('/serp/import', [ReviewsController::class, 'importSerpReviews'])->name('serp.import');
});