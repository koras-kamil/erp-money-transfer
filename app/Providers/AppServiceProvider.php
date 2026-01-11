<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
   public function boot(): void
    {
        Paginator::useTailwind();

        // 2. Add this Logic
        // If the app is currently in Kurdish ('ku'), force Carbon to use Sorani
        if (config('app.locale') == 'ku' || request()->segment(1) == 'ku') {
            
            // Define Sorani Translations
            Carbon::setLocale('ku');
            $translator = Carbon::getTranslator();
            $translator->addResource('array', [
                'year' => ':count ساڵ',
                'month' => ':count مانگ',
                'week' => ':count هەفتە',
                'day' => ':count ڕۆژ',
                'hour' => ':count کاتژمێر',
                'minute' => ':count خولەک',
                'second' => ':count چرکە',
                'ago' => 'پێش :time', 
                'from_now' => 'لە :time',
                'after' => ':time دوای',
                'before' => ':time پێش',
            ], 'ku');
        }
    }
}
