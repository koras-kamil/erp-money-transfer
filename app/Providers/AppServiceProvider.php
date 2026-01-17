<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;   
use Illuminate\Support\Facades\Schema; 
use App\Models\Branch;          
use Illuminate\Support\Facades\URL; // Add this at the top       

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
        // 1. Pagination Styling
        Paginator::useTailwind();

        // 2. Default String Length
        Schema::defaultStringLength(191);

        // 3. Share '$branches' ONLY with the main layout (Fixes Infinite Loading Loop)
        // We changed '*' to 'layouts.app' so it doesn't try to load on error pages.
        View::composer('layouts.app', function ($view) {
            // Check if table exists to prevent migration errors
            if (Schema::hasTable('branches')) {
                $branches = Branch::select('id', 'name')->get();
                $view->with('branches', $branches);
            }
        });

        // 4. Custom Kurdish Carbon Translations
        if (app()->getLocale() == 'ku') {
            $sorani = [
                'year'   => ':count ساڵ',
                'month'  => ':count مانگ',
                'week'   => ':count هەفتە',
                'day'    => ':count ڕۆژ',
                'hour'   => ':count کاتژمێر',
                'minute' => ':count خولەک',
                'second' => ':count چرکە',
                'ago'    => 'پێش :time', 
                'from_now' => 'لە :time',
                'after'  => ':time دوای',
                'before' => ':time پێش',
                'diff_now' => 'ئێستا',
                'diff_yesterday' => 'دوێنێ',
                'diff_tomorrow' => 'بەیانی',
            ];

            // Force Carbon to use Sorani for 'ku'
            Carbon::setLocale('ku');
            $translator = Carbon::getTranslator();
            $translator->setMessages('ku', ['translations' => $sorani]);
        }

        // 5. Super Admin Gate Permission
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });


      if (str_contains(request()->getHost(), 'loca.lt') || str_contains(request()->getHost(), 'serveo.net')) {
        URL::forceScheme('https');
    
    }
    }
}