<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CurrencyConfigController;
use App\Http\Controllers\CashBoxController;
use App\Http\Controllers\GroupSpendingController;
use App\Http\Controllers\TypeSpendingController;
use App\Http\Controllers\ProfitGroupController;
use App\Http\Controllers\ProfitTypeController;
use App\Http\Controllers\CapitalController;

/*
|--------------------------------------------------------------------------
| FINANCE ROUTES
|--------------------------------------------------------------------------
*/

// 1. Profit Configuration
Route::prefix('profit-groups')->name('profit.groups.')->group(function () {
    Route::delete('/bulk-delete', [ProfitGroupController::class, 'bulkDelete'])->name('bulk-delete');
    Route::post('/bulk-restore', [ProfitGroupController::class, 'bulkRestore'])->name('bulk-restore');
    Route::delete('/bulk-force-delete', [ProfitGroupController::class, 'bulkForceDelete'])->name('bulk-force-delete');
    Route::get('/trash', [ProfitGroupController::class, 'trash'])->name('trash');
    Route::post('/restore/{id}', [ProfitGroupController::class, 'restore'])->name('restore');
    Route::delete('/force-delete/{id}', [ProfitGroupController::class, 'forceDelete'])->name('force-delete');
    Route::get('/', [ProfitGroupController::class, 'index'])->name('index');
    Route::post('/store', [ProfitGroupController::class, 'store'])->name('store');
    Route::get('/pdf', [ProfitGroupController::class, 'downloadPdf'])->name('pdf');
    Route::delete('/{id}', [ProfitGroupController::class, 'destroy'])->name('destroy');
});

Route::prefix('profit-types')->name('profit.types.')->group(function () {
    Route::delete('/bulk-delete', [ProfitTypeController::class, 'bulkDelete'])->name('bulk-delete');
    Route::post('/bulk-restore', [ProfitTypeController::class, 'bulkRestore'])->name('bulk-restore');
    Route::delete('/bulk-force-delete', [ProfitTypeController::class, 'bulkForceDelete'])->name('bulk-force-delete');
    Route::get('/trash', [ProfitTypeController::class, 'trash'])->name('trash');
    Route::post('/restore/{id}', [ProfitTypeController::class, 'restore'])->name('restore');
    Route::delete('/force-delete/{id}', [ProfitTypeController::class, 'forceDelete'])->name('force-delete');
    Route::get('/', [ProfitTypeController::class, 'index'])->name('index');
    Route::post('/store', [ProfitTypeController::class, 'store'])->name('store');
    Route::get('/pdf', [ProfitTypeController::class, 'downloadPdf'])->name('pdf');
    Route::delete('/{id}', [ProfitTypeController::class, 'destroy'])->name('destroy');
});

// 2. Currency Configuration
Route::prefix('currency-config')->name('currency.')->group(function () {
    Route::post('/update-rates', [CurrencyConfigController::class, 'updateRates'])->name('update-rates');
    Route::get('/print', [CurrencyConfigController::class, 'downloadPdf'])->name('print');
    Route::delete('/bulk-delete', [CurrencyConfigController::class, 'bulkDelete'])->name('bulk-delete');
    Route::post('/bulk-restore', [CurrencyConfigController::class, 'bulkRestore'])->name('bulk-restore');
    Route::delete('/bulk-force-delete', [CurrencyConfigController::class, 'bulkForceDelete'])->name('bulk-force-delete');
    Route::get('/trash', [CurrencyConfigController::class, 'trash'])->name('trash');
    Route::post('/{id}/restore', [CurrencyConfigController::class, 'restore'])->name('restore');
    Route::delete('/{id}/force-delete', [CurrencyConfigController::class, 'forceDelete'])->name('force-delete');
    Route::get('/', [CurrencyConfigController::class, 'index'])->name('index');
    Route::post('/', [CurrencyConfigController::class, 'store'])->name('store');
    Route::delete('/{currency}', [CurrencyConfigController::class, 'destroy'])->name('destroy');
});

// 3. Cash Boxes
Route::prefix('cash-boxes')->name('cash-boxes.')->group(function () {
    Route::get('/print', [CashBoxController::class, 'downloadPdf'])->name('print');
    Route::post('/store-bulk', [CashBoxController::class, 'storeBulk'])->name('store-bulk');
    Route::get('/export', [CashBoxController::class, 'export'])->name('export');
    Route::delete('/bulk-delete', [CashBoxController::class, 'bulkDelete'])->name('bulk-delete');
    Route::post('/bulk-restore', [CashBoxController::class, 'bulkRestore'])->name('bulk-restore');
    Route::delete('/bulk-force-delete', [CashBoxController::class, 'bulkForceDelete'])->name('bulk-force-delete');
    Route::get('/trash', [CashBoxController::class, 'trash'])->name('trash');
    Route::post('/{id}/restore', [CashBoxController::class, 'restore'])->name('restore');
    Route::delete('/{id}/force-delete', [CashBoxController::class, 'forceDelete'])->name('force-delete');
});
Route::resource('cash-boxes', CashBoxController::class);

// 4. Spending
Route::prefix('group-spending')->name('group-spending.')->group(function () {
    Route::get('/print', [GroupSpendingController::class, 'downloadPdf'])->name('print');
    Route::delete('/bulk-delete', [GroupSpendingController::class, 'bulkDelete'])->name('bulk-delete');
    Route::post('/bulk-restore', [GroupSpendingController::class, 'bulkRestore'])->name('bulk-restore');
    Route::delete('/bulk-force-delete', [GroupSpendingController::class, 'bulkForceDelete'])->name('bulk-force-delete');
    Route::get('/trash', [GroupSpendingController::class, 'trash'])->name('trash');
    Route::post('/{id}/restore', [GroupSpendingController::class, 'restore'])->name('restore');
    Route::delete('/{id}/force-delete', [GroupSpendingController::class, 'forceDelete'])->name('force-delete');
});
Route::resource('group-spending', GroupSpendingController::class);

Route::prefix('type-spending')->name('type-spending.')->group(function () {
    Route::get('/print', [TypeSpendingController::class, 'downloadPdf'])->name('print');
    Route::delete('/bulk-delete', [TypeSpendingController::class, 'bulkDelete'])->name('bulk-delete');
    Route::post('/bulk-restore', [TypeSpendingController::class, 'bulkRestore'])->name('bulk-restore');
    Route::delete('/bulk-force-delete', [TypeSpendingController::class, 'bulkForceDelete'])->name('bulk-force-delete');
    Route::get('/trash', [TypeSpendingController::class, 'trash'])->name('trash');
    Route::post('/{id}/restore', [TypeSpendingController::class, 'restore'])->name('restore');
    Route::delete('/{id}/force-delete', [TypeSpendingController::class, 'forceDelete'])->name('force-delete');
});
Route::resource('type-spending', TypeSpendingController::class);

// 5. Capitals
Route::prefix('capitals')->name('capitals.')->group(function () {
    Route::delete('/bulk-delete', [CapitalController::class, 'bulkDelete'])->name('bulk-delete');
    Route::post('/bulk-restore', [CapitalController::class, 'bulkRestore'])->name('bulk-restore');
    Route::delete('/bulk-force-delete', [CapitalController::class, 'bulkForceDelete'])->name('bulk-force-delete');
    Route::get('/trash', [CapitalController::class, 'trash'])->name('trash');
    Route::get('/pdf', [CapitalController::class, 'downloadPdf'])->name('pdf');
    Route::post('/{id}/restore', [CapitalController::class, 'restore'])->name('restore');
    Route::delete('/{id}/force-delete', [CapitalController::class, 'forceDelete'])->name('forceDelete');
});
Route::resource('capitals', CapitalController::class);