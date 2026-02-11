<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;   
use Illuminate\Support\Facades\Schema; 
use App\Models\Branch;          
use Illuminate\Support\Facades\URL;

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
        // 🟢 1. Pagination Styling (Forces Tailwind to fix "ugly list")
        Paginator::useTailwind();

        // 🟢 2. Global Pagination Limit
        // This allows you to use PER_PAGE in all your controllers
        if (!defined('PER_PAGE')) {
            define('PER_PAGE', 20);
        }

        // 3. Default String Length (Fixes migration errors on some DBs)
        Schema::defaultStringLength(191);

        // 4. Force HTTPS for Local Tunnels (ngrok, serveo, etc.)
        if (str_contains(request()->getHost(), 'loca.lt') || str_contains(request()->getHost(), 'serveo.net')) {
            URL::forceScheme('https');
        }

        // 5. Share '$branches' ONLY with the main layout (Prevents errors on other pages)
        View::composer('layouts.app', function ($view) {
            // Check if table exists to prevent migration errors
            if (Schema::hasTable('branches')) {
                $branches = Branch::select('id', 'name')->get();
                $view->with('branches', $branches);
            }
        });

        // 6. Super Admin Gate Permission
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });

        // 7. Custom Kurdish Carbon Translations
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
    }
}