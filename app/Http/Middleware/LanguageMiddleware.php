<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;

class LanguageMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Get the locale from Session or Config
        $locale = Session::get('locale', config('app.locale'));
        App::setLocale($locale);

        // 2. If the locale is Kurdish, force Sorani translations into Carbon
        if ($locale == 'ku') {
            Carbon::setLocale('ku');
            $translator = Carbon::getTranslator();
            
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

            // This overwrites the built-in Kurmanji file (ku.php) in the vendor folder
            $translator->setMessages('ku', [
                'translations' => $sorani
            ]);
        }

        return $next($request);
    }
}