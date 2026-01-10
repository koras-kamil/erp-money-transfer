<?php
namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;

class LanguageController extends Controller
{
    public function switch($lang)
    {
        // 1. Check if the language is allowed
        if (in_array($lang, ['en', 'ku'])) {
            // 2. Put the language choice in the "memory" (Session)
            Session::put('locale', $lang);
        }
        
        // 3. Go back to the page we were on
        return redirect()->back();
    }
}