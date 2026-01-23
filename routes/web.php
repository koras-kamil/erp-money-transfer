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
 Route::prefix('profit-groups')->name('profit.groups.')->group(function () {
    Route::get('/', [ProfitGroupController::class, 'index'])->name('index');
    Route::post('/store', [ProfitGroupController::class, 'store'])->name('store');
    Route::delete('/{id}', [ProfitGroupController::class, 'destroy'])->name('destroy');
    Route::get('/pdf', [ProfitGroupController::class, 'downloadPdf'])->name('pdf');
    
    // TRASH ROUTES
    Route::get('/trash', [ProfitGroupController::class, 'trash'])->name('trash');
    Route::post('/restore/{id}', [ProfitGroupController::class, 'restore'])->name('restore');
    
    // FIX: Changed 'force_delete' to 'force-delete' to match your Blade file
    Route::delete('/force-delete/{id}', [ProfitGroupController::class, 'forceDelete'])->name('force-delete');
});

// Profit Types Routes
Route::prefix('profit-types')->name('profit.types.')->group(function () {
    Route::get('/', [ProfitTypeController::class, 'index'])->name('index');
    Route::post('/store', [ProfitTypeController::class, 'store'])->name('store');
    Route::delete('/{id}', [ProfitTypeController::class, 'destroy'])->name('destroy');
    Route::get('/pdf', [ProfitTypeController::class, 'downloadPdf'])->name('pdf');

    // Trash
    Route::get('/trash', [ProfitTypeController::class, 'trash'])->name('trash');
    Route::post('/restore/{id}', [ProfitTypeController::class, 'restore'])->name('restore');
    Route::delete('/force-delete/{id}', [ProfitTypeController::class, 'forceDelete'])->name('force_delete');
});

    /*
    |--------------------------------------------------------------------------
    | CURRENCY CONFIGURATION
    |--------------------------------------------------------------------------
    */
    Route::prefix('currency-config')->name('currency.')->group(function () {
        // 1. New PDF Print Route (Add this line)
        Route::get('/print', [CurrencyConfigController::class, 'downloadPdf'])->name('print');

        // Existing Routes...
        Route::get('/trash', [CurrencyConfigController::class, 'trash'])->name('trash');
        Route::post('/{id}/restore', [CurrencyConfigController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force-delete', [CurrencyConfigController::class, 'forceDelete'])->name('force-delete');
        
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
        Route::get('/print', [CashBoxController::class, 'downloadPdf'])->name('print');
        Route::post('/store-bulk', [CashBoxController::class, 'storeBulk'])->name('store-bulk');
        Route::get('/export', [CashBoxController::class, 'export'])->name('export');
        
        // Trash & Restore
        Route::get('/trash', [CashBoxController::class, 'trash'])->name('trash');
        Route::post('/{id}/restore', [CashBoxController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force-delete', [CashBoxController::class, 'forceDelete'])->name('force-delete');
    });
    Route::resource('cash-boxes', CashBoxController::class);

    /*
    |--------------------------------------------------------------------------
    | SPENDING: GROUP SPENDING
    |--------------------------------------------------------------------------
    */
    Route::prefix('group-spending')->name('group-spending.')->group(function () {
        Route::get('/print', [GroupSpendingController::class, 'downloadPdf'])->name('print');
        
        // Trash & Restore
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
        Route::get('/print', [TypeSpendingController::class, 'downloadPdf'])->name('print');

        // Trash & Restore
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