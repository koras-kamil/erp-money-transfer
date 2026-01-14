<?php

use Illuminate\Support\Facades\Route;
use App\Models\CurrencyConfig;
use App\Http\Controllers\CurrencyConfigController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\CashBoxController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BranchController;

// 1. PUBLIC ROUTES
Route::view('/', 'welcome');
Route::get('lang/{lang}', [LanguageController::class, 'switch'])->name('lang.switch');

// 2. AUTHENTICATED ROUTES
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard
    Route::get('dashboard', function () {
        // Safe check: return 0 if table doesn't exist yet to prevent crash
        try {
            $totalCurrencies = CurrencyConfig::count();
            $activeCurrencies = CurrencyConfig::where('is_active', true)->count();
        } catch (\Exception $e) {
            $totalCurrencies = 0;
            $activeCurrencies = 0;
        }
        return view('dashboard', compact('totalCurrencies', 'activeCurrencies'));
    })->name('dashboard');

    // Profile
    Route::view('profile', 'profile')->name('profile');

    // Branches
    Route::resource('branches', BranchController::class);

    // Activity Logs
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');

    // Currency Configuration
    Route::get('/currency-config', [CurrencyConfigController::class, 'index'])->name('currency.index');
    Route::post('/currency-config', [CurrencyConfigController::class, 'store'])->name('currency.store');
    Route::delete('/currency-config/{currency}', [CurrencyConfigController::class, 'destroy'])->name('currency.destroy');

    // Cash Box Specialized Routes
    Route::get('cash-boxes/trash', [CashBoxController::class, 'trash'])->name('cash-boxes.trash');
    Route::post('cash-boxes/{id}/restore', [CashBoxController::class, 'restore'])->name('cash-boxes.restore');
    Route::delete('cash-boxes/{id}/force-delete', [CashBoxController::class, 'forceDelete'])->name('cash-boxes.force-delete');
    Route::get('cash-boxes/export', [CashBoxController::class, 'export'])->name('cash-boxes.export');
    
    // Cash Box Resource
    Route::resource('cash-boxes', CashBoxController::class);

    // 3. SUPER ADMIN ONLY ROUTES
    Route::middleware(['role:super-admin'])->group(function () {
        
        // User Management
        Route::put('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.updateRole');
        Route::resource('users', UserController::class);

        // Role Management
        Route::resource('roles', RoleController::class);
    });
});

// 4. LOGOUT
Route::post('/logout', function () {
    auth()->logout();                   
    request()->session()->invalidate();  
    request()->session()->regenerateToken(); 
    return redirect('/');               
})->name('logout');

require __DIR__.'/auth.php';