<aside id="sidebar" class="fixed top-0 bottom-0 z-50 w-56 transition-transform duration-300 ease-in-out {{ app()->getLocale() == 'ku' ? 'translate-x-full' : '-translate-x-full' }} md:translate-x-0 bg-[#0f172a] border-slate-800 flex flex-col shadow-2xl ltr:left-0 ltr:border-r rtl:right-0 rtl:border-l">

    {{-- 1. HEADER --}}
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
        <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    {{-- 2. MENU CONTENT --}}
    <div class="flex-1 overflow-visible py-4 px-2 space-y-4">
        <nav class="space-y-0.5">
            
            {{-- MAIN SECTION --}}
            <p class="px-3 text-[9px] font-bold text-slate-600 uppercase tracking-widest mb-2">{{ __('messages.main_menu') }}</p>

            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-all text-xs font-medium {{ request()->routeIs('dashboard') ? 'bg-indigo-600 shadow-lg shadow-indigo-600/30 text-white' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>{{ __('messages.dashboard') }}</span>
            </a>

            <x-nav-group label="{{ __('menu.define') }}" :active="request()->routeIs('currency.*') || request()->routeIs('cash-boxes.*')">
                <x-slot:icon>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.572 1.065c-1.543.94-3 .888-8 .888s-.888-.888-.888-.888z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9,9l9,9"/></svg>
                </x-slot:icon>

                <a href="{{ route('currency.index') }}" class="block px-3 py-2 text-[11px] font-medium rounded-lg transition-colors {{ request()->routeIs('currency.index') ? 'bg-indigo-600/10 text-indigo-400' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30' }}">
                    {{ __('menu.currency') }}
                </a>

                <a href="{{ route('cash-boxes.index') }}" class="block px-3 py-2 text-[11px] font-medium rounded-lg transition-colors {{ request()->routeIs('cash-boxes.index') ? 'bg-indigo-600/10 text-indigo-400' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30' }}">
                    {{ __('cash_box.title') }}
                </a>
            </x-nav-group>

            {{-- ADMIN SECTION (Super Admin Only) --}}
            @role('super-admin')
            <div class="mt-6 pt-6 border-t border-slate-700/50">
                {{-- NEW STYLE FOR HEADER TEXT --}}
                <p class="px-3 text-[10px] font-black uppercase tracking-widest mb-3 text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-cyan-300">
                    {{ app()->getLocale() == 'ku' ? 'بەڕێوەبردن' : 'ADMINISTRATION' }}
                </p>

                <x-nav-group label="{{ app()->getLocale() == 'ku' ? 'ڕێکخستنی سیستەم' : 'System Admin' }}" 
                             :active="request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('activity-log.*')">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    </x-slot:icon>

                    <a href="{{ route('users.index') }}" class="block px-3 py-2 text-[11px] font-medium rounded-lg transition-colors {{ request()->routeIs('users.*') ? 'bg-indigo-600/10 text-indigo-400' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30' }}">
                        {{ app()->getLocale() == 'ku' ? 'بەکارهێنەران' : 'Users Management' }}
                    </a>

                    <a href="{{ route('roles.index') }}" class="block px-3 py-2 text-[11px] font-medium rounded-lg transition-colors {{ request()->routeIs('roles.*') ? 'bg-indigo-600/10 text-indigo-400' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30' }}">
                        {{ app()->getLocale() == 'ku' ? 'دەسەڵاتەکان' : 'Roles & Permissions' }}
                    </a>

                    <a href="{{ route('activity-log.index') }}" class="block px-3 py-2 text-[11px] font-medium rounded-lg transition-colors {{ request()->routeIs('activity-log.index') ? 'bg-indigo-600/10 text-indigo-400' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30' }}">
                        {{ __('menu.activity_log') }}
                    </a>
                </x-nav-group>
            </div>
            @endrole

        </nav>
    </div>

    {{-- 3. FOOTER --}}
    <div class="p-3 bg-[#0a0f1c] border-t border-slate-800 space-y-3">
        
        {{-- NEW LANGUAGE SWITCHER STYLE --}}
        <div class="flex items-center justify-between p-1 rounded-full bg-slate-800/50 border border-slate-700">
            <a href="{{ route('lang.switch', 'en') }}" class="flex-1 flex items-center justify-center py-1.5 rounded-full text-[10px] font-bold transition-all duration-300 {{ app()->getLocale() == 'en' ? 'bg-white text-indigo-900 shadow-sm scale-100' : 'text-slate-400 hover:text-white scale-90' }}">
               EN
            </a>
            <a href="{{ route('lang.switch', 'ku') }}" class="flex-1 flex items-center justify-center py-1.5 rounded-full text-[10px] font-bold transition-all duration-300 {{ app()->getLocale() == 'ku' ? 'bg-white text-emerald-700 shadow-sm scale-100' : 'text-slate-400 hover:text-white scale-90' }}">
               KU
            </a>
        </div>

        <div class="flex items-center gap-3 p-2.5 rounded-xl bg-slate-800/40 border border-slate-700/50 hover:bg-slate-800 transition-colors group">
            
            {{-- Avatar --}}
            <div class="relative">
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold shadow-lg shadow-indigo-500/20 border border-white/10">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                {{-- Nice Online Point --}}
                <span class="absolute -top-1 -right-1 flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ Auth::user()->hasRole('super-admin') ? 'bg-emerald-400' : 'bg-rose-400' }} opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 {{ Auth::user()->hasRole('super-admin') ? 'bg-emerald-500' : 'bg-rose-500' }} border-2 border-[#0f172a]"></span>
                </span>
            </div>

            {{-- Name & Role (RED for User, GREEN for Admin) --}}
            <div class="flex-1 min-w-0 flex flex-col justify-center">
                <p class="text-[11px] font-bold text-white truncate leading-tight">{{ Auth::user()->name }}</p>
                
                <p class="text-[9px] font-black uppercase tracking-wider truncate mt-0.5 {{ Auth::user()->hasRole('super-admin') ? 'text-emerald-400' : 'text-rose-400' }}">
                    {{ Auth::user()->hasRole('super-admin') ? (app()->getLocale() == 'ku' ? 'بەڕێوەبەر' : 'SUPER ADMIN') : (app()->getLocale() == 'ku' ? 'کارمەند' : 'STAFF MEMBER') }}
                </p>
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-slate-500 hover:text-red-400 p-1.5 hover:bg-red-500/10 rounded-lg transition-all" title="Logout">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </div>
</aside>