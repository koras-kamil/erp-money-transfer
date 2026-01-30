<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route; // ✅ CRITICAL IMPORT

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // ✅ 1. AUTH ROUTES (Login, Register, Password Reset)
            // This MUST be registered so /login works
            Route::middleware('web')
                ->group(base_path('routes/auth.php'));

            // 2. Finance Routes
            Route::middleware(['web', 'auth'])
                ->group(base_path('routes/finance.php'));

            // 3. Account Routes
            Route::middleware(['web', 'auth'])
                ->group(base_path('routes/account.php'));

            // 4. Admin Routes
            Route::middleware(['web', 'auth'])
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register Language Middleware
        $middleware->web(append: [
            \App\Http\Middleware\LanguageMiddleware::class,
        ]);

        // Register Spatie Permission Aliases
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();