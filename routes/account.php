<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ZoneController;

/*
|--------------------------------------------------------------------------
| ACCOUNT ROUTES
|--------------------------------------------------------------------------
*/

// 1. Trash Routes (MUST BE BEFORE RESOURCE)
Route::prefix('accounts')->name('accounts.')->group(function () {
    Route::get('trash', [AccountController::class, 'trash'])->name('trash');
    Route::post('{id}/restore', [AccountController::class, 'restore'])->name('restore');
    Route::delete('{id}/force-delete', [AccountController::class, 'forceDelete'])->name('force-delete');
    
    // 🔥 ADDED MISSING BULK RESTORE ROUTE
    Route::post('bulk-restore', [AccountController::class, 'bulkRestore'])->name('bulk-restore');
    
    // Bulk Force Delete
    Route::delete('bulk-force-delete', [AccountController::class, 'bulkForceDelete'])->name('bulk-force-delete');
    
    // Bulk Soft Delete (for Index page)
    Route::delete('bulk-delete', [AccountController::class, 'bulkDelete'])->name('bulk-delete');
});

// 2. Resource Route
Route::resource('accounts', AccountController::class);

/*
|--------------------------------------------------------------------------
| ZONE ROUTES
|--------------------------------------------------------------------------
*/
Route::controller(ZoneController::class)->prefix('zones')->name('zones.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/cities', 'storeCities')->name('cities.store');
    Route::delete('/cities/{id}', 'destroyCity')->name('cities.destroy');
    Route::post('/neighborhoods', 'storeNeighborhoods')->name('neighborhoods.store');
    Route::delete('/neighborhoods/{id}', 'destroyNeighborhood')->name('neighborhoods.destroy');
});