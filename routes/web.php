<?php

use App\Http\Controllers\CurrencyConfigController;
use App\Http\Controllers\LanguageController;
use App\Models\CurrencyConfig; // <--- Imported to count currencies for the Dashboard
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// ========================================================================
// 1. PUBLIC ROUTES (No Login Required)
// ========================================================================

// The Landing Page
Route::view('/', 'welcome');

// Language Switcher Route
// This calls the 'switch' method in LanguageController to change the session locale (en/ku)
Route::get('lang/{lang}', [LanguageController::class, 'switch'])->name('lang.switch');


// ========================================================================
// 2. AUTHENTICATED ROUTES (Login Required)
// ========================================================================

// Dashboard Route
// We changed this from Route::view() to Route::get() so we can pass data to the page.
Route::get('dashboard', function () {
    
    // Logic: Count all rows in the currency_configs table
    $totalCurrencies = CurrencyConfig::count();

    // Logic: Count only rows where 'is_active' is 1 (true)
    $activeCurrencies = CurrencyConfig::where('is_active', true)->count();

    // Return the dashboard view and pass these two variables to it
    return view('dashboard', compact('totalCurrencies', 'activeCurrencies'));

})->middleware(['auth', 'verified'])->name('dashboard'); 
// 'middleware' ensures only logged-in & verified users can see this.


// User Profile Route
Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');


// ========================================================================
// 3. CURRENCY CONFIGURATION ROUTES (Your Sheet System)
// ========================================================================

// [GET] Show the Currency Page
// Calls the 'index' method to load the table view with data.
Route::get('/currency-config', [CurrencyConfigController::class, 'index'])
    ->name('currency.index')
    ->middleware(['auth']); // Protect this route

// [POST] Save Changes
// Calls the 'store' method to save new rows or update existing ones.
Route::post('/currency-config', [CurrencyConfigController::class, 'store'])
    ->name('currency.store')
    ->middleware(['auth']);

// [DELETE] Delete a Row
// Calls the 'destroy' method. {currency} passes the ID to the controller.
Route::delete('/currency-config/{currency}', [CurrencyConfigController::class, 'destroy'])
    ->name('currency.destroy')
    ->middleware(['auth']);


// ========================================================================
// 4. AUTHENTICATION & LOGOUT
// ========================================================================

// Custom Logout Route
Route::post('/logout', function () {
    auth()->logout();                   // Logs the user out
    request()->session()->invalidate();  // Destroys the old session (security)
    request()->session()->regenerateToken(); // Generates a new CSRF token (security)
    return redirect('/');               // Sends user back to welcome page
})->name('logout');

// Load standard auth routes (Login, Register, Password Reset)
require __DIR__.'/auth.php';