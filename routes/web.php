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
// Controllers
use App\Http\Controllers\GroupSpendingController;
use App\Http\Controllers\TypeSpendingController;
use App\Http\Controllers\ProfitConfigController; // <--- Make sure this is imported

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

    // --- PROFIT MANAGEMENT ROUTES (Required to fix your error) ---
    Route::prefix('profit-config')->name('profit.')->group(function () {
        Route::get('/', [ProfitConfigController::class, 'index'])->name('index');
        Route::post('/groups', [ProfitConfigController::class, 'storeGroups'])->name('groups.store');
        Route::delete('/groups/{id}', [ProfitConfigController::class, 'destroyGroup'])->name('groups.destroy');
        Route::post('/types', [ProfitConfigController::class, 'storeTypes'])->name('types.store');
        Route::delete('/types/{id}', [ProfitConfigController::class, 'destroyType'])->name('types.destroy');
    });

    // Profile
    Route::view('profile', 'profile')->name('profile');

    // --- BRANCHES & SWITCHING ---
    Route::resource('branches', BranchController::class);
    Route::post('/switch-branch', [BranchController::class, 'switch'])->name('branch.switch');

    // Activity Logs
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');

    // --- CURRENCY CONFIGURATION ROUTES ---
    Route::prefix('currency-config')->name('currency.')->group(function () {
        Route::get('/trash', [CurrencyConfigController::class, 'trash'])->name('trash');
        Route::post('/{id}/restore', [CurrencyConfigController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force-delete', [CurrencyConfigController::class, 'forceDelete'])->name('force-delete');
        
        Route::get('/', [CurrencyConfigController::class, 'index'])->name('index');
        Route::post('/', [CurrencyConfigController::class, 'store'])->name('store');
        Route::delete('/{currency}', [CurrencyConfigController::class, 'destroy'])->name('destroy');
    });

    // --- CASH BOX ROUTES ---
  Route::prefix('cash-boxes')->name('cash-boxes.')->group(function () {
    Route::get('/print', [CashBoxController::class, 'downloadPdf'])->name('print');
    Route::post('/store-bulk', [CashBoxController::class, 'storeBulk'])->name('store-bulk');
    Route::get('/trash', [CashBoxController::class, 'trash'])->name('trash');
    Route::post('/{id}/restore', [CashBoxController::class, 'restore'])->name('restore');
    Route::delete('/{id}/force-delete', [CashBoxController::class, 'forceDelete'])->name('force-delete');
    Route::get('/export', [CashBoxController::class, 'export'])->name('export');
});
Route::resource('cash-boxes', CashBoxController::class);


    // --- GROUP SPENDING ROUTES (Separate) ---
 Route::prefix('group-spending')->name('group-spending.')->group(function () {
    // Print Route (First)
    Route::get('/print', [GroupSpendingController::class, 'downloadPdf'])->name('print');
    
    // Trash Routes
    Route::get('/trash', [GroupSpendingController::class, 'trash'])->name('trash');
    Route::post('/{id}/restore', [GroupSpendingController::class, 'restore'])->name('restore');
    Route::delete('/{id}/force-delete', [GroupSpendingController::class, 'forceDelete'])->name('force-delete');
});
Route::resource('group-spending', GroupSpendingController::class);



    // --- TYPE SPENDING ROUTES (Separate) ---
  Route::prefix('type-spending')->name('type-spending.')->group(function () {
    // 1. PDF Print Route (Must be at the top)
    Route::get('/print', [TypeSpendingController::class, 'downloadPdf'])->name('print');

    // 2. Trash & Restore Routes
    Route::get('/trash', [TypeSpendingController::class, 'trash'])->name('trash');
    Route::post('/{id}/restore', [TypeSpendingController::class, 'restore'])->name('restore');
    Route::delete('/{id}/force-delete', [TypeSpendingController::class, 'forceDelete'])->name('force-delete');
});

// 3. Resource Route (Must be outside or after specific routes)
Route::resource('type-spending', TypeSpendingController::class);

    // 3. SUPER ADMIN ONLY ROUTES
    Route::middleware(['role:super-admin'])->group(function () {
        Route::put('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.updateRole');
        Route::resource('users', UserController::class);
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