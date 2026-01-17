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
use App\Http\Controllers\GroupSpendingController;
use App\Http\Controllers\TypeSpendingController;

// 1. PUBLIC ROUTES
Route::view('/', 'welcome');
Route::get('lang/{lang}', [LanguageController::class, 'switch'])->name('lang.switch');

// 2. AUTHENTICATED ROUTES
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard
    Route::get('dashboard', function () {
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

    // --- BRANCHES & SWITCHING ---
    Route::resource('branches', BranchController::class);
    // This is the new route for the navbar dropdown
    Route::post('/switch-branch', [BranchController::class, 'switch'])->name('branch.switch');

    // Activity Logs
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');

    // --- CURRENCY CONFIGURATION ROUTES ---
    Route::prefix('currency-config')->name('currency.')->group(function () {
        // Trash & Restore
        Route::get('/trash', [CurrencyConfigController::class, 'trash'])->name('trash');
        Route::post('/{id}/restore', [CurrencyConfigController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force-delete', [CurrencyConfigController::class, 'forceDelete'])->name('force-delete');
        
        // Standard Actions
        Route::get('/', [CurrencyConfigController::class, 'index'])->name('index');
        Route::post('/', [CurrencyConfigController::class, 'store'])->name('store');
        Route::delete('/{currency}', [CurrencyConfigController::class, 'destroy'])->name('destroy');
    });

    // --- CASH BOX ROUTES ---
    Route::prefix('cash-boxes')->name('cash-boxes.')->group(function () {
        Route::get('/trash', [CashBoxController::class, 'trash'])->name('trash');
        Route::post('/{id}/restore', [CashBoxController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force-delete', [CashBoxController::class, 'forceDelete'])->name('force-delete');
        Route::get('/export', [CashBoxController::class, 'export'])->name('export');
    });
    Route::resource('cash-boxes', CashBoxController::class);

    // --- GROUP SPENDING ROUTES ---
    Route::prefix('group-spending')->name('group-spending.')->group(function () {
        Route::get('/trash', [GroupSpendingController::class, 'trash'])->name('trash');
        Route::post('/{id}/restore', [GroupSpendingController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force-delete', [GroupSpendingController::class, 'forceDelete'])->name('force-delete');
    });
    Route::resource('group-spending', GroupSpendingController::class);

    // --- TYPE SPENDING ROUTES ---
    Route::prefix('type-spending')->name('type-spending.')->group(function () {
        Route::get('/trash', [TypeSpendingController::class, 'trash'])->name('trash');
        Route::post('/{id}/restore', [TypeSpendingController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force-delete', [TypeSpendingController::class, 'forceDelete'])->name('force-delete');
    });
    Route::resource('type-spending', TypeSpendingController::class);


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