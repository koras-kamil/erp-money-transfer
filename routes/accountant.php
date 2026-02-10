<?php

use App\Http\Controllers\Accountant\ReceivingController;
use App\Http\Controllers\Accountant\PayingController; // ✅ Correct Import
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'web'])->prefix('accountant')->as('accountant.')->group(function () {
    
    // ==========================================
    // 🟢 RECEIVING (WERGR) ROUTES
    // ==========================================
    Route::get('/receiving', [ReceivingController::class, 'index'])->name('receiving.index');
    
    // Note: The form currently uses 'accountant.store'. 
    // Ideally, specific forms should point to specific routes (e.g., receiving.store).
    // Keeping this for backward compatibility with your receiving form:
    Route::post('/receiving/store', [ReceivingController::class, 'store'])->name('store'); 
    
    Route::get('/receiving/{id}/edit', [ReceivingController::class, 'edit'])->name('receiving.edit');
    Route::put('/receiving/{id}', [ReceivingController::class, 'update'])->name('receiving.update');
    Route::delete('/receiving/bulk-delete', [ReceivingController::class, 'bulkDelete'])->name('receiving.bulk-delete');
    Route::delete('/receiving/{id}', [ReceivingController::class, 'destroy'])->name('receiving.destroy');

    // Receiving Trash
    Route::get('/receiving/trash', [ReceivingController::class, 'trash'])->name('receiving.trash');
    Route::post('/receiving/{id}/restore', [ReceivingController::class, 'restore'])->name('receiving.restore');
    Route::delete('/receiving/{id}/force-delete', [ReceivingController::class, 'forceDelete'])->name('receiving.force-delete');
    Route::get('/receiving/pdf', [ReceivingController::class, 'pdf'])->name('receiving.pdf');

    // ==========================================
    // 🔴 PAYING (GIVER / PIDER) ROUTES (NEW)
    // ==========================================
    // This fixes the "Route not defined" error:
   Route::get('/paying', [PayingController::class, 'index'])->name('paying.index');
    
    Route::post('/paying/store', [PayingController::class, 'store'])->name('paying.store');
    Route::get('/paying/{id}/edit', [PayingController::class, 'edit'])->name('paying.edit');
    Route::put('/paying/{id}', [PayingController::class, 'update'])->name('paying.update');
    Route::delete('/paying/bulk-delete', [PayingController::class, 'bulkDelete'])->name('paying.bulk-delete');
    Route::delete('/paying/{id}', [PayingController::class, 'destroy'])->name('paying.destroy');

    Route::get('/paying/trash', [PayingController::class, 'trash'])->name('paying.trash');
    Route::post('/paying/{id}/restore', [PayingController::class, 'restore'])->name('paying.restore');
    Route::delete('/paying/{id}/force-delete', [PayingController::class, 'forceDelete'])->name('paying.force-delete');
    Route::get('/paying/pdf', [PayingController::class, 'pdf'])->name('paying.pdf');
});