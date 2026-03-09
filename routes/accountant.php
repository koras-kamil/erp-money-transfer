<?php

use App\Http\Controllers\Accountant\ReceivingController;
use App\Http\Controllers\Accountant\PayingController;
use App\Http\Controllers\Accountant\CashboxReportController; // 🟢 Make sure this is imported!
use App\Http\Controllers\Accountant\StatementController; // 🟢 Import This
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Accountant\CashboxTransferController;


Route::middleware(['auth', 'web'])->prefix('accountant')->as('accountant.')->group(function () {
    
    // ==========================================
    // 🟢 RECEIVING ROUTES
    // ==========================================
    // ==========================================
    // 🟢 RECEIVING ROUTES
    // ==========================================
    Route::get('/receiving', [ReceivingController::class, 'index'])->name('receiving.index');
    Route::post('/receiving', [ReceivingController::class, 'store'])->name('receiving.store'); 
    
    // 1. Static Routes (MUST BE BEFORE THE {id} ROUTES)
    Route::get('/receiving/trash', [ReceivingController::class, 'trash'])->name('receiving.trash');
    Route::delete('/receiving/bulk-delete', [ReceivingController::class, 'bulkDelete'])->name('receiving.bulk-delete');
    Route::post('/receiving/bulk-restore', [ReceivingController::class, 'bulkRestore'])->name('receiving.bulk-restore');
    Route::delete('/receiving/bulk-force-delete', [ReceivingController::class, 'bulkForceDelete'])->name('receiving.bulk-force-delete');

    // 2. Dynamic Routes (With {id})
    Route::get('/receiving/{id}/edit', [ReceivingController::class, 'edit'])->name('receiving.edit');
    Route::put('/receiving/{id}', [ReceivingController::class, 'update'])->name('receiving.update');
    Route::delete('/receiving/{id}', [ReceivingController::class, 'destroy'])->name('receiving.destroy');
    Route::post('/receiving/{id}/restore', [ReceivingController::class, 'restore'])->name('receiving.restore');
    Route::delete('/receiving/{id}/force-delete', [ReceivingController::class, 'forceDelete'])->name('receiving.force-delete');
    // ==========================================
    // 🔴 PAYING ROUTES
    // ==========================================

    Route::get('/paying', [PayingController::class, 'index'])->name('paying.index');
    Route::post('/paying', [PayingController::class, 'store'])->name('paying.store');
    
    // 🟢 THESE 4 MUST BE HERE, BEFORE THE {id} ROUTES:
    Route::get('/paying/trash', [PayingController::class, 'trash'])->name('paying.trash');
    Route::delete('/paying/bulk-delete', [PayingController::class, 'bulkDelete'])->name('paying.bulk-delete');
    Route::post('/paying/bulk-restore', [PayingController::class, 'bulkRestore'])->name('paying.bulk-restore');
    Route::delete('/paying/bulk-force-delete', [PayingController::class, 'bulkForceDelete'])->name('paying.bulk-force-delete');

    // Dynamic Routes (With {id})
    Route::get('/paying/{id}/edit', [PayingController::class, 'edit'])->name('paying.edit');
    Route::put('/paying/{id}', [PayingController::class, 'update'])->name('paying.update');
    Route::delete('/paying/{id}', [PayingController::class, 'destroy'])->name('paying.destroy');
    Route::post('/paying/{id}/restore', [PayingController::class, 'restore'])->name('paying.restore');
    Route::delete('/paying/{id}/force-delete', [PayingController::class, 'forceDelete'])->name('paying.force-delete');
    // ==========================================
    // 🔵 STATEMENT ROUTES (Fixes RouteNotFoundException)
    // ==========================================
    // Index page (List of users to select for statement)
    Route::get('/statement', [StatementController::class, 'index'])->name('statement.index');
    
    // Show specific user statement
    Route::get('/statement/{id}', [StatementController::class, 'show'])->name('statement.show');


    // ==========================================
 // 📊 CASHBOX REPORTS ROUTES
 // ==========================================
 Route::get('/cashbox-reports', [CashboxReportController::class, 'index'])->name('cashbox_reports.index');
 Route::get('/cashbox-reports/{id}', [CashboxReportController::class, 'show'])->name('cashbox_reports.show');


 // ==========================================
    // 🔄 CASHBOX TRANSFERS
    // ==========================================
    Route::get('/transfers', [CashboxTransferController::class, 'index'])->name('transfers.index');
    Route::get('/transfers/create', [CashboxTransferController::class, 'create'])->name('transfers.create');
    Route::post('/transfers', [CashboxTransferController::class, 'store'])->name('transfers.store');

    
});