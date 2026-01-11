@php
    $isKurdish = app()->getLocale() == 'ku';
    // Changed hide transform to match new width (w-56)
    $hideTransform = $isKurdish ? 'translate-x-full' : '-translate-x-full';
    
    // Helpers
    $isActiveGroup = fn($pattern) => request()->routeIs($pattern) || request()->is($pattern);
    $isActiveLink = fn($name) => request()->routeIs($name) ? 'bg-indigo-600 shadow-lg shadow-indigo-600/30 text-white' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100';
    $isActiveSub = fn($name) => request()->routeIs($name) ? 'bg-indigo-600/10 text-indigo-400' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30';
@endphp

<div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 hidden md:hidden transition-opacity"></div>

<aside id="sidebar" class="fixed top-0 bottom-0 z-50 w-56 transition-transform duration-300 ease-in-out {{ $hideTransform }} md:translate-x-0 bg-[#0f172a] border-slate-800 flex flex-col shadow-2xl ltr:left-0 ltr:border-r rtl:right-0 rtl:border-l">

    <div class="h-14 flex items-center justify-between px-4 border-b border-slate-800 bg-[#1e293b]/20">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 bg-indigo-600 rounded-lg flex items-center justify-center shadow-lg shadow-indigo-600/20">
                <span class="text-white font-bold text-xs">S</span>
            </div>
            <div class="flex flex-col">
                <span class="text-white font-bold text-[10px] uppercase tracking-wider">Smart</span>
                <span class="text-[8px] text-indigo-400 uppercase tracking-widest">System</span>
            </div>
        </div>
        <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
    </div>

    <div class="flex-1 overflow-visible py-4 px-2 space-y-4">
        <nav class="space-y-0.5">
            <p class="px-3 text-[9px] font-bold text-slate-600 uppercase tracking-widest mb-2">{{ __('messages.main_menu') }}</p>

            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all text-xs font-medium {{ $isActiveLink('dashboard') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>{{ __('messages.dashboard') }}</span>
            </a>

            <x-nav-group label="{{ __('menu.define') }}" :active="$isActiveGroup('define.*') || $isActiveGroup('currency.*') || $isActiveGroup('cash-boxes.*')">
                <x-slot:icon>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3 .888-8 .888s-.888-.888-.888-.888z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9,9l9,9"/></svg>
                </x-slot:icon>

                <a href="{{ route('currency.index') }}" class="block px-3 py-2 text-[11px] font-medium rounded-lg transition-colors {{ $isActiveSub('currency.index') }}">
                    {{ __('menu.currency') }}
                </a>

                <a href="{{ route('cash-boxes.index') }}" class="block px-3 py-2 text-[11px] font-medium rounded-lg transition-colors {{ $isActiveSub('cash-boxes.index') }}">
                    {{ __('cash_box.title') }}
                </a>
                
                <a href="#" class="block px-3 py-2 text-[11px] font-medium rounded-lg transition-colors {{ $isActiveSub('users.index') }}">
                    {{ __('menu.users') }}
                </a>
            </x-nav-group>
        </nav>
    </div>

    <div class="p-3 bg-[#0a0f1c] border-t border-slate-800 space-y-3">
        
        <div class="flex items-center gap-2 p-1 bg-[#161e2e] rounded-xl border border-slate-700/50">
            <a href="{{ route('lang.switch', 'en') }}" 
               class="flex-1 flex items-center justify-center gap-1.5 py-1.5 rounded-lg text-[10px] font-bold transition-all duration-200
               {{ app()->getLocale() == 'en' 
                  ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20 ring-1 ring-indigo-500' 
                  : 'text-slate-500 hover:bg-slate-800 hover:text-slate-300' }}">
               <span>🇬🇧</span>
               <span>EN</span>
            </a>
            
            <a href="{{ route('lang.switch', 'ku') }}" 
               class="flex-1 flex items-center justify-center gap-1.5 py-1.5 rounded-lg text-[10px] font-bold transition-all duration-200
               {{ app()->getLocale() == 'ku' 
                  ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/20 ring-1 ring-emerald-500' 
                  : 'text-slate-500 hover:bg-slate-800 hover:text-slate-300' }}">
               <span>☀️</span>
               <span>KU</span>
            </a>
        </div>

        <div class="flex items-center gap-2 p-2 rounded-lg hover:bg-slate-800/50 transition-colors group">
            <div class="w-7 h-7 rounded bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-[10px] font-bold">{{ substr(Auth::user()->name, 0, 1) }}</div>
            <div class="flex-1 min-w-0"><p class="text-[11px] font-medium text-white truncate">{{ Auth::user()->name }}</p></div>
            <form action="{{ route('logout') }}" method="POST">@csrf<button type="submit" class="text-slate-500 hover:text-red-400 p-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg></button></form>
        </div>
    </div>
</aside>