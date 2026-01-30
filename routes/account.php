<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ZoneController;

/*
|--------------------------------------------------------------------------
| ACCOUNT & ZONE ROUTES
|--------------------------------------------------------------------------
*/

// 1. Accounts
Route::delete('accounts/bulk-delete', [AccountController::class, 'bulkDelete'])->name('accounts.bulk-delete');
Route::resource('accounts', AccountController::class);

// 2. Zones (Cities & Neighborhoods)
Route::controller(ZoneController::class)->prefix('zones')->name('zones.')->group(function () {
    Route::get('/', 'index')->name('index');
    
    // Cities Actions
    Route::post('/cities', 'storeCities')->name('cities.store');
    Route::delete('/cities/{id}', 'destroyCity')->name('cities.destroy');
    
    // Neighborhood Actions
    Route::post('/neighborhoods', 'storeNeighborhoods')->name('neighborhoods.store');
    Route::delete('/neighborhoods/{id}', 'destroyNeighborhood')->name('neighborhoods.destroy');
});