<?php

use Illuminate\Support\Facades\Route;

// 🟢 Controllers Import List
use App\Http\Controllers\Accountant\ReceivingController;
use App\Http\Controllers\Accountant\PayingController;
use App\Http\Controllers\Accountant\CashboxReportController;
use App\Http\Controllers\Accountant\StatementController;
use App\Http\Controllers\Accountant\CashboxTransferController;
use App\Http\Controllers\Accountant\AccountTransferController;
use App\Http\Controllers\Accountant\ExpenseController;
use App\Http\Controllers\Accountant\IncomeController; // 🟢 کۆنترۆڵەری داهاتەکان زیاد کرا

/*
|--------------------------------------------------------------------------
| ACCOUNTANT SECURE ROUTES
|--------------------------------------------------------------------------
| All routes inside this group are protected by the 'auth' middleware.
| Only logged-in users can access these endpoints.
*/
Route::middleware(['web', 'auth'])->group(function () {

    // ==========================================
    // 🔵 MAIN ACCOUNTANT GROUP
    // URL Prefix: /accountant/ 
    // Route Name Prefix: accountant.*
    // ==========================================
    Route::prefix('accountant')->as('accountant.')->group(function () {
        
        // ------------------------------------------
        // 💸 EXPENSES / SPENDING TRANSACTIONS
        // ------------------------------------------
        // Bulk routes MUST come before the resource to prevent {expense} wildcard conflict
        Route::delete('/expenses/bulk-delete', [ExpenseController::class, 'bulkDelete'])->name('expenses.bulk-delete');
        Route::resource('expenses', ExpenseController::class);

        // ------------------------------------------
        // 💰 INCOMES / PROFIT TRANSACTIONS
        // ------------------------------------------
        // Bulk routes MUST come before the resource to prevent {income} wildcard conflict
        Route::delete('/incomes/bulk-delete', [IncomeController::class, 'bulkDelete'])->name('incomes.bulk-delete');
        Route::resource('incomes', IncomeController::class);

        // ------------------------------------------
        // 📥 RECEIVING
        // ------------------------------------------
        Route::get('/receiving', [ReceivingController::class, 'index'])->name('receiving.index');
        Route::post('/receiving', [ReceivingController::class, 'store'])->name('receiving.store'); 
        
        // Static Routes
        Route::get('/receiving/trash', [ReceivingController::class, 'trash'])->name('receiving.trash');
        Route::delete('/receiving/bulk-delete', [ReceivingController::class, 'bulkDelete'])->name('receiving.bulk-delete');
        Route::post('/receiving/bulk-restore', [ReceivingController::class, 'bulkRestore'])->name('receiving.bulk-restore');
        Route::delete('/receiving/bulk-force-delete', [ReceivingController::class, 'bulkForceDelete'])->name('receiving.bulk-force-delete');

        // Dynamic Routes (With {id})
        Route::get('/receiving/{id}/edit', [ReceivingController::class, 'edit'])->name('receiving.edit');
        Route::put('/receiving/{id}', [ReceivingController::class, 'update'])->name('receiving.update');
        Route::delete('/receiving/{id}', [ReceivingController::class, 'destroy'])->name('receiving.destroy');
        Route::post('/receiving/{id}/restore', [ReceivingController::class, 'restore'])->name('receiving.restore');
        Route::delete('/receiving/{id}/force-delete', [ReceivingController::class, 'forceDelete'])->name('receiving.force-delete');
        
        // ------------------------------------------
        // 📤 PAYING
        // ------------------------------------------
        Route::get('/paying', [PayingController::class, 'index'])->name('paying.index');
        Route::post('/paying', [PayingController::class, 'store'])->name('paying.store');
        
        // Static Routes
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
        
        // ------------------------------------------
        // 📄 STATEMENTS
        // ------------------------------------------
        Route::get('/statement', [StatementController::class, 'index'])->name('statement.index');
        Route::get('/statement/{id}', [StatementController::class, 'show'])->name('statement.show');

        // ------------------------------------------
        // 📊 CASHBOX REPORTS
        // ------------------------------------------
        Route::get('/cashbox-reports', [CashboxReportController::class, 'index'])->name('cashbox_reports.index');
        Route::get('/cashbox-reports/{id}', [CashboxReportController::class, 'show'])->name('cashbox_reports.show');

        // ------------------------------------------
        // 🔄 CASHBOX TRANSFERS
        // ------------------------------------------
        Route::get('/transfers', [CashboxTransferController::class, 'index'])->name('transfers.index');
        Route::get('/transfers/create', [CashboxTransferController::class, 'create'])->name('transfers.create');
        Route::post('/transfers', [CashboxTransferController::class, 'store'])->name('transfers.store');

    });

    // ==========================================
    // 🟡 ACCOUNT TRANSFERS (HAWALA) 
    // URL Prefix: /accountant/
    // Route Name: account_transfers.* (Intentionally excluded from accountant.*)
    // ==========================================
    Route::prefix('accountant')->group(function () {
        Route::get('/account-transfers', [AccountTransferController::class, 'index'])->name('account_transfers.index');
        Route::get('/account-transfers/create', [AccountTransferController::class, 'create'])->name('account_transfers.create');
        Route::post('/account-transfers', [AccountTransferController::class, 'store'])->name('account_transfers.store');
    });

});