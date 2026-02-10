<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ActivityLogController;
use App\Models\CurrencyConfig;
use App\Notifications\SystemAlert; // Make sure to import your Notification class
require __DIR__.'/accountant.php';

/*
|--------------------------------------------------------------------------
| 1. PUBLIC ROUTES
|--------------------------------------------------------------------------
*/
Route::view('/', 'welcome');
Route::get('lang/{lang}', [LanguageController::class, 'switch'])->name('lang.switch');

/*
|--------------------------------------------------------------------------
| 2. AUTHENTICATED CORE ROUTES
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

    // --- BRANCHES ---
    Route::resource('branches', BranchController::class);
    Route::post('/switch-branch', [BranchController::class, 'switch'])->name('branch.switch');

    // --- NOTIFICATIONS ---
    
    // 1. Mark All as Read (Linked to the "Mark All Read" button in your bell dropdown)
    Route::get('/notifications/read-all', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    })->name('notifications.readAll');

    // 2. Manual Test Trigger (Visit /test-notification to see the bell light up!)
    Route::get('/test-notification', function () {
        auth()->user()->notify(new SystemAlert(
            'System Update', 
            'The notification system has been successfully installed!',
            'success'
        ));
        return back()->with('message', 'Test notification sent!');
    });

    // --- ACTIVITY LOGS ---
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
    
    // --- LOGOUT ---
    Route::post('/logout', function () {
        auth()->logout();                   
        request()->session()->invalidate();  
        request()->session()->regenerateToken(); 
        return redirect('/');               
    })->name('logout');
});