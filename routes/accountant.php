<?php

use App\Http\Controllers\Accountant\ReceivingController;
use App\Http\Controllers\Accountant\PayingController;
use App\Http\Controllers\Accountant\StatementController; // 🟢 Import This
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'web'])->prefix('accountant')->as('accountant.')->group(function () {
    
    // ==========================================
    // 🟢 RECEIVING ROUTES
    // ==========================================
    Route::get('/receiving', [ReceivingController::class, 'index'])->name('receiving.index');
    Route::post('/receiving', [ReceivingController::class, 'store'])->name('receiving.store'); 
    
    Route::get('/receiving/{id}/edit', [ReceivingController::class, 'edit'])->name('receiving.edit');
    Route::put('/receiving/{id}', [ReceivingController::class, 'update'])->name('receiving.update');
    Route::delete('/receiving/bulk-delete', [ReceivingController::class, 'bulkDelete'])->name('receiving.bulk-delete');
    Route::delete('/receiving/{id}', [ReceivingController::class, 'destroy'])->name('receiving.destroy');
    Route::get('/receiving/trash', [ReceivingController::class, 'trash'])->name('receiving.trash');
    Route::post('/receiving/{id}/restore', [ReceivingController::class, 'restore'])->name('receiving.restore');
    Route::delete('/receiving/{id}/force-delete', [ReceivingController::class, 'forceDelete'])->name('receiving.force-delete');

    // ==========================================
    // 🔴 PAYING ROUTES
    // ==========================================
    Route::get('/paying', [PayingController::class, 'index'])->name('paying.index');
    Route::post('/paying', [PayingController::class, 'store'])->name('paying.store');
    
    Route::get('/paying/{id}/edit', [PayingController::class, 'edit'])->name('paying.edit');
    Route::put('/paying/{id}', [PayingController::class, 'update'])->name('paying.update');
    Route::delete('/paying/bulk-delete', [PayingController::class, 'bulkDelete'])->name('paying.bulk-delete');
    Route::delete('/paying/{id}', [PayingController::class, 'destroy'])->name('paying.destroy');
    Route::get('/paying/trash', [PayingController::class, 'trash'])->name('paying.trash');
    Route::post('/paying/{id}/restore', [PayingController::class, 'restore'])->name('paying.restore');
    Route::delete('/paying/{id}/force-delete', [PayingController::class, 'forceDelete'])->name('paying.force-delete');

    // ==========================================
    // 🔵 STATEMENT ROUTES (Fixes RouteNotFoundException)
    // ==========================================
    // Index page (List of users to select for statement)
    Route::get('/statement', [StatementController::class, 'index'])->name('statement.index');
    
    // Show specific user statement
    Route::get('/statement/{id}', [StatementController::class, 'show'])->name('statement.show');

});