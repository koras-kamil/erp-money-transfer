<aside id="sidebar" 
       x-cloak
       class="flex flex-col border-r border-slate-800 bg-[#0f172a] shadow-2xl transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] z-[60] h-full flex-shrink-0
              fixed md:relative ltr:left-0 rtl:right-0"
       :class="{
           'translate-x-0': mobileMenuOpen,
           'ltr:-translate-x-full rtl:translate-x-full': !mobileMenuOpen,
           'md:!translate-x-0': true,
           'w-64': window.innerWidth < 768, 
           'md:w-fit md:min-w-[14rem] md:max-w-[20rem]': !isCollapsed && window.innerWidth >= 768,
           'md:w-[4.5rem]': isCollapsed && window.innerWidth >= 768
       }"
       @resize.window="isCollapsed = window.innerWidth < 768 ? false : isCollapsed">

    {{-- 1. HEADER --}}
    <div class="h-16 flex items-center justify-center relative border-b border-slate-800 bg-[#1e293b]/50 whitespace-nowrap overflow-hidden px-3 shrink-0">
        <button @click="mobileMenuOpen = false" class="md:hidden absolute ltr:right-3 rtl:left-3 text-slate-400 hover:text-white transition-colors p-1">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>

        <div class="flex items-center gap-3 transition-all duration-300"
             :class="(isCollapsed && window.innerWidth >= 768) ? 'justify-center w-full' : ''">
            <div class="w-8 h-8 flex-shrink-0 flex items-center justify-center bg-indigo-600 rounded-lg shadow-lg shadow-indigo-500/40">
                <span class="text-white font-black text-sm">S</span>
            </div>
            <div x-show="!isCollapsed || window.innerWidth < 768" class="flex flex-col overflow-hidden transition-opacity duration-200">
                <span class="block text-white font-bold text-xs uppercase tracking-wider">Smart</span>
                <span class="block text-[9px] text-indigo-400 uppercase tracking-widest">System</span>
            </div>
        </div>
    </div>

    {{-- 2. TOGGLE BUTTON --}}
    <button @click="isCollapsed = !isCollapsed"
            class="absolute top-1/2 -translate-y-1/2 z-[70] flex items-center justify-center w-6 h-6 bg-white text-indigo-600 rounded-full border border-slate-200 shadow-md hover:bg-indigo-50 hover:scale-110 transition-all duration-200 group
                   ltr:-right-3 rtl:-left-3 hidden md:flex"
            :class="isCollapsed ? 'rotate-180' : ''"
            title="Toggle Sidebar">
        <svg class="w-3 h-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
    </button>

    {{-- 3. MENU CONTENT --}}
    <div class="flex-1 overflow-y-auto overflow-x-hidden py-4 px-2 space-y-1.5 custom-scrollbar relative z-0">
        
        <div class="px-2 mb-3 transition-all duration-300 whitespace-nowrap overflow-hidden" :class="(isCollapsed && window.innerWidth >= 768) ? 'text-center' : ''">
            <p x-show="!isCollapsed || window.innerWidth < 768" class="text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] px-1">{{ __('messages.main_menu') }}</p>
            <div x-show="isCollapsed && window.innerWidth >= 768" class="h-1 w-1 bg-slate-600 mx-auto rounded-full"></div>
        </div>

        {{-- DASHBOARD --}}
        <a href="{{ route('dashboard') }}" 
           class="flex items-center gap-3 px-2.5 py-2 rounded-lg transition-all group relative overflow-hidden
           {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}"
           :class="(isCollapsed && window.innerWidth >= 768) ? 'justify-center' : ''">
            <div class="w-5 h-5 flex-shrink-0 flex items-center justify-center">
                <svg class="w-5 h-5 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            </div>
            <span x-show="!isCollapsed || window.innerWidth < 768" class="text-xs font-bold whitespace-nowrap transition-opacity duration-200">{{ __('messages.dashboard') }}</span>
            <div x-show="isCollapsed && window.innerWidth >= 768" class="absolute ltr:left-14 rtl:right-14 bg-slate-900 text-white text-[10px] px-2 py-1 rounded shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-all z-50 whitespace-nowrap border border-slate-700 font-medium">
                {{ __('messages.dashboard') }}
            </div>
        </a>

        {{-- ACCOUNTS (NEW SEPARATE SECTION) --}}
        <a href="{{ route('accounts.index') }}" 
           class="flex items-center gap-3 px-2.5 py-2 rounded-lg transition-all group relative overflow-hidden
           {{ request()->routeIs('accounts.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}"
           :class="(isCollapsed && window.innerWidth >= 768) ? 'justify-center' : ''">
            <div class="w-5 h-5 flex-shrink-0 flex items-center justify-center">
                <svg class="w-5 h-5 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
            <span x-show="!isCollapsed || window.innerWidth < 768" class="text-xs font-bold whitespace-nowrap transition-opacity duration-200">{{ __('menu.account') }}</span>
            <div x-show="isCollapsed && window.innerWidth >= 768" class="absolute ltr:left-14 rtl:right-14 bg-slate-900 text-white text-[10px] px-2 py-1 rounded shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-all z-50 whitespace-nowrap border border-slate-700 font-medium">
                {{ __('Accounts') }}
            </div>
        </a>

        {{-- DEFINITION GROUP --}}
        <x-nav-group label="{{ __('menu.define') }}" 
                     :active="request()->routeIs('currency.*') || request()->routeIs('cash-boxes.*') || request()->routeIs('group-spending.*') || request()->routeIs('type-spending.*') || request()->routeIs('profit.*') || request()->routeIs('capitals.*')">
            <x-slot:icon>
                <div class="w-5 h-5 flex-shrink-0 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.572 1.065c-1.543.94-3 .888-8 .888s-.888-.888-.888-.888z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9,9l9,9"/></svg>
                </div>
            </x-slot:icon>
            
            <a href="{{ route('currency.index') }}" class="block px-3 py-1.5 text-[11px] font-medium rounded-lg transition-colors whitespace-nowrap {{ request()->routeIs('currency.*') ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">{{ __('menu.currency') }}</a>
            <a href="{{ route('cash-boxes.index') }}" class="block px-3 py-1.5 text-[11px] font-medium rounded-lg transition-colors whitespace-nowrap {{ request()->routeIs('cash-boxes.*') ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">{{ __('cash_box.title') }}</a>
            <a href="{{ route('group-spending.index') }}" class="block px-3 py-1.5 text-[11px] font-medium rounded-lg transition-colors whitespace-nowrap {{ (request()->routeIs('group-spending.*') || request()->routeIs('type-spending.*')) ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">{{ __('spending.group_title') }}</a>
            <a href="{{ route('profit.groups.index') }}" class="block px-3 py-1.5 text-[11px] font-medium rounded-lg transition-colors whitespace-nowrap {{ request()->routeIs('profit.*') ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">{{ __('profit.menu_tab') }}</a>
            <a href="{{ route('capitals.index') }}" class="block px-3 py-1.5 text-[11px] font-medium rounded-lg transition-colors whitespace-nowrap {{ request()->routeIs('capitals.*') ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">{{ __('menu.capital') }}</a>
        </x-nav-group>

        {{-- ADMIN SECTION --}}
        @role('super-admin')
        <div class="mt-4 pt-4 border-t border-slate-800">
             <div class="px-2 mb-3 transition-all duration-300" :class="(isCollapsed && window.innerWidth >= 768) ? 'text-center' : ''">
                <p x-show="!isCollapsed || window.innerWidth < 768" class="text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] px-2 whitespace-nowrap">{{ app()->getLocale() == 'ku' ? 'بەڕێوەبردن' : 'ADMIN' }}</p>
                <div x-show="isCollapsed && window.innerWidth >= 768" class="h-1 w-1 bg-slate-600 mx-auto rounded-full"></div>
            </div>
            <x-nav-group label="{{ app()->getLocale() == 'ku' ? 'ڕێکخستنی سیستەم' : 'System Admin' }}" :active="request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('activity-log.*')">
                <x-slot:icon>
                    <div class="w-5 h-5 flex-shrink-0 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    </div>
                </x-slot:icon>
                <a href="{{ route('users.index') }}" class="block px-3 py-1.5 text-[11px] font-medium text-slate-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors whitespace-nowrap">{{ app()->getLocale() == 'ku' ? 'بەکارهێنەران' : 'Users' }}</a>
                <a href="{{ route('roles.index') }}" class="block px-3 py-1.5 text-[11px] font-medium text-slate-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors whitespace-nowrap">{{ app()->getLocale() == 'ku' ? 'دەسەڵاتەکان' : 'Roles' }}</a>
                <a href="{{ route('activity-log.index') }}" class="block px-3 py-1.5 text-[11px] font-medium text-slate-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors whitespace-nowrap">{{ __('menu.activity_log') }}</a>
            </x-nav-group>
        </div>
        @endrole
    </div>


   {{-- 4. FOOTER --}}
    <div class="p-3 border-t border-slate-800 bg-[#0a0f1c] space-y-2.5 shrink-0 z-20">
        
        {{-- SETTINGS & LANGUAGE ROW --}}
        <div class="flex items-center gap-2" :class="(isCollapsed && window.innerWidth >= 768) ? 'flex-col' : 'flex-row'">
            
            {{-- SETTINGS BUTTON (Using specific key to fix array error) --}}
            <a href="{{ route('settings.index') }}" 
               class="flex items-center justify-center p-2.5 rounded-xl bg-slate-800 text-slate-400 hover:bg-indigo-600 hover:text-white transition-all shadow-lg border border-slate-700/50 group relative"
               :class="(isCollapsed && window.innerWidth >= 768) ? 'w-full' : 'flex-1'"
               title="{{ __('settings.menu_label') }}">
                
                {{-- Custom 6-Tooth Gear Icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 transition-transform duration-500 ease-in-out group-hover:rotate-180">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                
                {{-- Tooltip (Visible on Hover) --}}
                <div class="absolute bottom-full mb-2 bg-slate-900 text-white text-[10px] px-2 py-1 rounded shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-all z-50 whitespace-nowrap border border-slate-700 font-medium">
                    {{ __('settings.menu_label') }}
                </div>
            </a>

            {{-- LANGUAGE DROPDOWN --}}
            <div class="relative" :class="(isCollapsed && window.innerWidth >= 768) ? 'w-full' : 'flex-1'" x-data="{ langOpen: false }">
                <button @click="langOpen = !langOpen" @click.outside="langOpen = false" class="flex items-center justify-center w-full p-2.5 rounded-xl bg-slate-800 text-slate-300 hover:bg-slate-700 border border-slate-700/50 transition-all font-bold text-xs uppercase shadow-lg">
                    {{ app()->getLocale() }}
                </button>
                <div x-show="langOpen" class="absolute bottom-full mb-2 bg-[#1e293b] border border-slate-700 rounded-xl shadow-2xl z-[60] overflow-hidden min-w-[120px] max-h-[200px]" :class="(isCollapsed && window.innerWidth >= 768) ? 'ltr:left-0 rtl:right-0' : 'ltr:right-0 rtl:left-0'" x-transition style="display: none;" x-cloak>
                    <ul class="py-1 text-xs text-slate-300">
                        <li><a href="{{ route('lang.switch', 'en') }}" class="flex items-center w-full px-4 py-3 hover:bg-slate-700/50 hover:text-white gap-2"><span class="font-bold text-indigo-400">EN</span><span>English</span></a></li>
                        <li><a href="{{ route('lang.switch', 'ku') }}" class="flex items-center w-full px-4 py-3 hover:bg-slate-700/50 hover:text-white gap-2"><span class="font-bold text-emerald-400">KU</span><span>کوردی</span></a></li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- USER PROFILE --}}
        <div class="relative group/user flex items-center gap-2.5 p-2 rounded-lg hover:bg-slate-800/50 transition-all duration-300 cursor-pointer border border-transparent hover:border-slate-700/50" :class="(isCollapsed && window.innerWidth >= 768) ? 'justify-center' : ''">
            <div class="relative w-9 h-9 flex-shrink-0">
                <div class="w-full h-full rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-[10px] font-bold shadow-lg ring-2 ring-[#0f172a] group-hover/user:ring-slate-700 transition-all">{{ substr(Auth::user()->name, 0, 1) }}</div>
                <span class="absolute -top-0.5 -right-0.5 flex h-2.5 w-2.5"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500 border-2 border-[#0f172a]"></span></span>
            </div>
            <div x-show="!isCollapsed || window.innerWidth < 768" class="flex-1 min-w-0 overflow-hidden transition-opacity duration-200">
                <p class="text-xs font-bold text-white truncate leading-tight">{{ Auth::user()->name }}</p>
                <p class="text-[9px] text-slate-500 uppercase tracking-wider truncate">{{ Auth::user()->hasRole('super-admin') ? 'Super Admin' : 'Staff' }}</p>
            </div>
            
            <div x-show="isCollapsed && window.innerWidth >= 768" class="absolute ltr:left-14 rtl:right-14 bottom-0 z-[60] w-max min-w-[130px] bg-slate-800 border border-slate-700 rounded-xl shadow-2xl p-2.5 opacity-0 group-hover/user:opacity-100 pointer-events-none group-hover/user:pointer-events-auto transition-all duration-300">
                <div class="flex flex-col gap-0.5 mb-2 border-b border-slate-700/50 pb-2">
                    <p class="text-[11px] font-bold text-white">{{ Auth::user()->name }}</p>
                    <p class="text-[9px] text-indigo-400 uppercase tracking-wider">{{ Auth::user()->hasRole('super-admin') ? 'Super Admin' : 'Staff' }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="w-full">@csrf<button type="submit" class="flex items-center gap-2 w-full text-[10px] text-red-400 hover:text-red-300 hover:bg-red-500/10 px-2 py-1 rounded transition-colors"><span>{{ __('Logout') }}</span></button></form>
            </div>
            <form action="{{ route('logout') }}" method="POST" x-show="!isCollapsed || window.innerWidth < 768">@csrf<button type="submit" class="text-slate-500 hover:text-red-400 p-1.5 hover:bg-white/5 rounded-lg transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg></button></form>
        </div>
    </div>
</aside>