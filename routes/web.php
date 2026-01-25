<?php

use Illuminate\Support\Facades\Route;

// --- CONTROLLERS ---
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CurrencyConfigController;
use App\Http\Controllers\CashBoxController;
use App\Http\Controllers\GroupSpendingController;
use App\Http\Controllers\TypeSpendingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Models\CurrencyConfig;
use App\Http\Controllers\ProfitGroupController;
use App\Http\Controllers\ProfitTypeController;

/*
|--------------------------------------------------------------------------
| 1. PUBLIC ROUTES
|--------------------------------------------------------------------------
*/
Route::view('/', 'welcome');
Route::get('lang/{lang}', [LanguageController::class, 'switch'])->name('lang.switch');

/*
|--------------------------------------------------------------------------
| 2. AUTHENTICATED ROUTES (Middleware: auth, verified)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // --- DASHBOARD ---
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

    // --- PROFILE ---
    Route::view('profile', 'profile')->name('profile');
Route::post('/currency/update-rate', [CurrencyController::class, 'updateRate'])->name('currency.update-rate');
    // --- BRANCHES & SWITCHING ---
    Route::resource('branches', BranchController::class);
    Route::post('/switch-branch', [BranchController::class, 'switch'])->name('branch.switch');

    // --- ACTIVITY LOGS ---
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');

    /*
    |--------------------------------------------------------------------------
    | PROFIT CONFIGURATION (Groups & Types)
    |--------------------------------------------------------------------------
    */
    
    // --- PROFIT GROUPS ---
    Route::prefix('profit-groups')->name('profit.groups.')->group(function () {
        // 1. Bulk Operations (MUST BE BEFORE GENERIC ROUTES)
        Route::delete('/bulk-delete', [ProfitGroupController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/bulk-restore', [ProfitGroupController::class, 'bulkRestore'])->name('bulk-restore');
        Route::delete('/bulk-force-delete', [ProfitGroupController::class, 'bulkForceDelete'])->name('bulk-force-delete');
        
        // 2. Trash & Restore (Specific IDs)
        Route::get('/trash', [ProfitGroupController::class, 'trash'])->name('trash');
        Route::post('/restore/{id}', [ProfitGroupController::class, 'restore'])->name('restore');
        Route::delete('/force-delete/{id}', [ProfitGroupController::class, 'forceDelete'])->name('force-delete');

        // 3. Standard Routes
        Route::get('/', [ProfitGroupController::class, 'index'])->name('index');
        Route::post('/store', [ProfitGroupController::class, 'store'])->name('store');
        Route::get('/pdf', [ProfitGroupController::class, 'downloadPdf'])->name('pdf');
        
        // 4. Generic Wildcard Route (MUST BE LAST)
        Route::delete('/{id}', [ProfitGroupController::class, 'destroy'])->name('destroy');
    });

    // --- PROFIT TYPES ---
    Route::prefix('profit-types')->name('profit.types.')->group(function () {
        // 1. Bulk Operations (MUST BE BEFORE GENERIC ROUTES)
        Route::delete('/bulk-delete', [ProfitTypeController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/bulk-restore', [ProfitTypeController::class, 'bulkRestore'])->name('bulk-restore');
        Route::delete('/bulk-force-delete', [ProfitTypeController::class, 'bulkForceDelete'])->name('bulk-force-delete');

        // 2. Trash & Restore (Specific IDs)
        Route::get('/trash', [ProfitTypeController::class, 'trash'])->name('trash');
        Route::post('/restore/{id}', [ProfitTypeController::class, 'restore'])->name('restore');
        Route::delete('/force-delete/{id}', [ProfitTypeController::class, 'forceDelete'])->name('force-delete');

        // 3. Standard Routes
        Route::get('/', [ProfitTypeController::class, 'index'])->name('index');
        Route::post('/store', [ProfitTypeController::class, 'store'])->name('store');
        Route::get('/pdf', [ProfitTypeController::class, 'downloadPdf'])->name('pdf');

        // 4. Generic Wildcard Route (MUST BE LAST)
        Route::delete('/{id}', [ProfitTypeController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | CURRENCY CONFIGURATION
    |--------------------------------------------------------------------------
    */
    Route::prefix('currency-config')->name('currency.')->group(function () {
        // 1. Specific Actions
        Route::get('/print', [CurrencyConfigController::class, 'downloadPdf'])->name('print');

        // 2. Bulk Operations (MUST BE BEFORE WILDCARDS)
        Route::delete('/bulk-delete', [CurrencyConfigController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/bulk-restore', [CurrencyConfigController::class, 'bulkRestore'])->name('bulk-restore');
        Route::delete('/bulk-force-delete', [CurrencyConfigController::class, 'bulkForceDelete'])->name('bulk-force-delete');

        // 3. Trash Operations
        Route::get('/trash', [CurrencyConfigController::class, 'trash'])->name('trash');
        Route::post('/{id}/restore', [CurrencyConfigController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force-delete', [CurrencyConfigController::class, 'forceDelete'])->name('force-delete');

        // 4. Standard CRUD (Wildcards LAST)
        Route::get('/', [CurrencyConfigController::class, 'index'])->name('index');
        Route::post('/', [CurrencyConfigController::class, 'store'])->name('store');
        Route::delete('/{currency}', [CurrencyConfigController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | CASH BOX CONFIGURATION
    |--------------------------------------------------------------------------
    */
    Route::prefix('cash-boxes')->name('cash-boxes.')->group(function () {
        // 1. Specific Actions
        Route::get('/print', [CashBoxController::class, 'downloadPdf'])->name('print');
        Route::post('/store-bulk', [CashBoxController::class, 'storeBulk'])->name('store-bulk');
        Route::get('/export', [CashBoxController::class, 'export'])->name('export');
        
        // 2. Bulk Operations (Fixed Order)
        Route::delete('/bulk-delete', [CashBoxController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/bulk-restore', [CashBoxController::class, 'bulkRestore'])->name('bulk-restore');
        Route::delete('/bulk-force-delete', [CashBoxController::class, 'bulkForceDelete'])->name('bulk-force-delete');

        // 3. Trash & Restore
        Route::get('/trash', [CashBoxController::class, 'trash'])->name('trash');
        Route::post('/{id}/restore', [CashBoxController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force-delete', [CashBoxController::class, 'forceDelete'])->name('force-delete');
    });
    // Resource Route (defines index, store, update, destroy automatically)
    // Note: The manual routes above take precedence for specific paths like 'trash'
    Route::resource('cash-boxes', CashBoxController::class);

    /*
    |--------------------------------------------------------------------------
    | SPENDING: GROUP SPENDING
    |--------------------------------------------------------------------------
    */
    Route::prefix('group-spending')->name('group-spending.')->group(function () {
        // 1. Specific Actions
        Route::get('/print', [GroupSpendingController::class, 'downloadPdf'])->name('print');

        // 2. Bulk Operations (Fixed Order)
        Route::delete('/bulk-delete', [GroupSpendingController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/bulk-restore', [GroupSpendingController::class, 'bulkRestore'])->name('bulk-restore');
        Route::delete('/bulk-force-delete', [GroupSpendingController::class, 'bulkForceDelete'])->name('bulk-force-delete');
        
        // 3. Trash & Restore
        Route::get('/trash', [GroupSpendingController::class, 'trash'])->name('trash');
        Route::post('/{id}/restore', [GroupSpendingController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force-delete', [GroupSpendingController::class, 'forceDelete'])->name('force-delete');
    });
    Route::resource('group-spending', GroupSpendingController::class);

    /*
    |--------------------------------------------------------------------------
    | SPENDING: TYPE SPENDING
    |--------------------------------------------------------------------------
    */
    Route::prefix('type-spending')->name('type-spending.')->group(function () {
        // 1. Specific Actions
        Route::get('/print', [TypeSpendingController::class, 'downloadPdf'])->name('print');

        // 2. Bulk Operations (Fixed Order)
        Route::delete('/bulk-delete', [TypeSpendingController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/bulk-restore', [TypeSpendingController::class, 'bulkRestore'])->name('bulk-restore');
        Route::delete('/bulk-force-delete', [TypeSpendingController::class, 'bulkForceDelete'])->name('bulk-force-delete');

        // 3. Trash & Restore
        Route::get('/trash', [TypeSpendingController::class, 'trash'])->name('trash');
        Route::post('/{id}/restore', [TypeSpendingController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force-delete', [TypeSpendingController::class, 'forceDelete'])->name('force-delete');
    });
    Route::resource('type-spending', TypeSpendingController::class);

    /*
    |--------------------------------------------------------------------------
    | 3. SUPER ADMIN ONLY ROUTES (User Management)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:super-admin'])->group(function () {
        Route::put('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.updateRole');
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
    });

});

/*
|--------------------------------------------------------------------------
| 4. LOGOUT ROUTE
|--------------------------------------------------------------------------
*/
Route::post('/logout', function () {
    auth()->logout();                   
    request()->session()->invalidate();  
    request()->session()->regenerateToken(); 
    return redirect('/');               
})->name('logout');

require __DIR__.'/auth.php';