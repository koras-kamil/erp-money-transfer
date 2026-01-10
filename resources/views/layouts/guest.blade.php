<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Smart System') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="ont-sans antialiased text-slate-900 antialiased bg-[#f8fafc] selection:bg-indigo-100">
        
        <div class="fixed top-6 ltr:right-6 rtl:left-6 z-50">
            <div class="flex p-1 bg-white/80 backdrop-blur-md rounded-2xl border border-slate-200 shadow-xl shadow-slate-200/50">
                <a href="{{ route('lang.switch', 'en') }}" 
                   class="px-4 py-2 text-[10px] font-black rounded-xl transition-all duration-200 {{ app()->getLocale() == 'en' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'text-slate-500 hover:text-indigo-600' }}">
                   EN
                </a>
                <a href="{{ route('lang.switch', 'ku') }}" 
                   class="px-4 py-2 text-[10px] font-black rounded-xl transition-all duration-200 {{ app()->getLocale() == 'ku' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'text-slate-500 hover:text-indigo-600' }}">
                   کوردی
                </a>
            </div>
        </div>

        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            
            <div class="mb-8 transform hover:scale-105 transition-transform duration-300">
                <a href="/" wire:navigate class="flex flex-col items-center gap-4">
                    <div class="w-16 h-16 bg-gradient-to-tr from-indigo-600 to-violet-500 rounded-[1.5rem] flex items-center justify-center shadow-2xl shadow-indigo-200 rotate-3">
                        <span class="text-white font-bold text-2xl -rotate-3 leading-none">S</span>
                    </div>
                    <h1 class="text-xl font-black text-slate-800 uppercase tracking-widest">Smart System</h1>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-2 px-8 py-10 bg-white border border-slate-200/60 shadow-[0_20px_50px_rgba(0,0,0,0.05)] overflow-hidden sm:rounded-[2.5rem]">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-slate-800">{{ __('messages.welcome_back') ?? 'Welcome' }}</h2>
                    <p class="text-slate-400 text-xs font-medium mt-1">{{ __('messages.login_subtitle') ?? 'Please enter your details' }}</p>
                </div>

                {{ $slot }}
            </div>

            <div class="mt-8 text-slate-400 text-[10px] font-bold uppercase tracking-widest">
                © {{ date('Y') }} Smart System Enterprise
            </div>
        </div>
    </body>
</html>