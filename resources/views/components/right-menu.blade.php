<aside id="sidebar" 
       x-cloak
       x-data="{ 
           mobileMenuOpen: false, 
           isCollapsed: (window.innerWidth >= 768) ? $persist(false).as('sidebar-collapsed') : false,
           tooltip: { show: false, text: '', top: 0, left: 0 },
           
           showTooltip(e, text) {
               if (!this.isCollapsed || window.innerWidth < 768) return;
               const rect = e.currentTarget.getBoundingClientRect();
               this.tooltip.text = text;
               this.tooltip.top = rect.top + (rect.height / 2);
               if (document.dir === 'rtl') {
                   this.tooltip.left = (window.innerWidth - rect.left) + 10;
               } else {
                   this.tooltip.left = rect.right + 10;
               }
               this.tooltip.show = true;
           },
           hideTooltip() {
               this.tooltip.show = false;
           }
       }"
       class="flex flex-col border-r border-slate-800 bg-[#0f172a] shadow-2xl z-[60] h-full flex-shrink-0
              fixed md:relative ltr:left-0 rtl:right-0
              transition-all duration-300 ease-[cubic-bezier(0.25,0.8,0.25,1)]"
       :class="{
           'translate-x-0': mobileMenuOpen,
           'ltr:-translate-x-full rtl:translate-x-full': !mobileMenuOpen,
           'md:!translate-x-0': true,
           'w-60': (window.innerWidth < 768) || (!isCollapsed && window.innerWidth >= 768), 
           'md:w-20': isCollapsed && window.innerWidth >= 768
       }"
       @resize.window="isCollapsed = window.innerWidth < 768 ? false : isCollapsed">

    {{-- 1. HEADER --}}
    <div class="h-16 flex items-center relative border-b border-slate-800 bg-[#1e293b]/50 whitespace-nowrap overflow-hidden shrink-0 transition-all duration-300 px-3"
         :class="isCollapsed ? 'justify-center' : 'justify-start'">
        
        <button @click="mobileMenuOpen = false" class="md:hidden absolute ltr:right-3 rtl:left-3 text-slate-400 hover:text-white transition-colors p-1">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>

        <div class="flex items-center gap-3 w-full">
            <div class="w-10 h-10 flex-shrink-0 flex items-center justify-center bg-indigo-600 rounded-xl shadow-lg shadow-indigo-500/40 transition-all duration-300 group-hover:scale-110">
                <span class="text-white font-black text-lg">S</span>
            </div>
            
            <div class="flex flex-col overflow-hidden transition-all duration-300 ease-in-out origin-left"
                 :class="isCollapsed && window.innerWidth >= 768 ? 'w-0 opacity-0 scale-x-0' : 'w-auto opacity-100 scale-x-100'">
                <span class="block text-white font-bold text-sm uppercase tracking-wider delay-75">Smart</span>
                <span class="block text-[10px] text-indigo-400 uppercase tracking-widest delay-100">System</span>
            </div>
        </div>
    </div>

    {{-- 2. TOGGLE BUTTON --}}
    <button @click="isCollapsed = !isCollapsed"
            class="absolute top-1/2 -translate-y-1/2 z-[70] flex items-center justify-center w-6 h-6 bg-white text-indigo-600 rounded-full border border-slate-200 shadow-md hover:bg-indigo-50 hover:scale-110 transition-all duration-300 group
                   ltr:-right-3 rtl:-left-3 hidden md:flex"
            :class="isCollapsed ? 'rotate-180' : ''"
            title="Toggle Sidebar">
        <svg class="w-3 h-3 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
    </button>

    {{-- 3. MENU CONTENT --}}
    <div class="flex-1 overflow-y-auto overflow-x-hidden py-3 px-2 space-y-0.5 custom-scrollbar relative z-0">
        
        {{-- === MAIN SECTION === --}}
        <div class="px-1 mb-2 transition-all duration-300 whitespace-nowrap overflow-hidden flex items-center" 
             :class="isCollapsed ? 'justify-center' : ''">
            <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] transition-all duration-300 origin-left"
               :class="isCollapsed && window.innerWidth >= 768 ? 'w-0 opacity-0 hidden' : 'w-full opacity-100 block'">
               {{ __('messages.main_menu') }}
            </p>
            <div class="h-1.5 w-1.5 bg-slate-600 rounded-full transition-all duration-300"
                 :class="isCollapsed && window.innerWidth >= 768 ? 'block opacity-100' : 'hidden opacity-0'">
            </div>
        </div>

        {{-- DASHBOARD --}}
        <a href="{{ route('dashboard') }}" 
           @mouseenter="showTooltip($event, '{{ __('messages.dashboard') }}')" @mouseleave="hideTooltip()"
           class="flex items-center gap-3 px-2 py-2 rounded-xl transition-all group relative overflow-hidden min-h-[40px]
           {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}"
           :class="isCollapsed ? 'justify-center' : ''">
            
            <div class="w-5 h-5 flex-shrink-0 flex items-center justify-center">
                <svg class="w-5 h-5 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            </div>
            
            <span class="text-xs font-bold whitespace-nowrap transition-all duration-300 ease-in-out origin-left"
                  :class="isCollapsed && window.innerWidth >= 768 ? 'w-0 opacity-0 translate-x-[-10px]' : 'w-auto opacity-100 translate-x-0'">
                {{ __('messages.dashboard') }}
            </span>
        </a>

        {{-- ACCOUNTS --}}
        <a href="{{ route('accounts.index') }}" 
           @mouseenter="showTooltip($event, '{{ __('menu.account') }}')" @mouseleave="hideTooltip()"
           class="flex items-center gap-3 px-2 py-2 rounded-xl transition-all group relative overflow-hidden min-h-[40px]
           {{ request()->routeIs('accounts.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}"
           :class="isCollapsed ? 'justify-center' : ''">
            <div class="w-5 h-5 flex-shrink-0 flex items-center justify-center">
                <svg class="w-5 h-5 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
            <span class="text-xs font-bold whitespace-nowrap transition-all duration-300 ease-in-out origin-left"
                  :class="isCollapsed && window.innerWidth >= 768 ? 'w-0 opacity-0 translate-x-[-10px]' : 'w-auto opacity-100 translate-x-0'">
                {{ __('menu.account') }}
            </span>
        </a>

        {{-- DEFINITION GROUP --}}
        <div @mouseenter="showTooltip($event, '{{ __('menu.define') }}')" @mouseleave="hideTooltip()">
            <x-nav-group label="{{ __('menu.define') }}" 
                         :active="request()->routeIs('currency.*') || request()->routeIs('cash-boxes.*') || request()->routeIs('group-spending.*') || request()->routeIs('type-spending.*') || request()->routeIs('profit.*') || request()->routeIs('capitals.*')">
                <x-slot:icon>
                    <div class="w-5 h-5 flex-shrink-0 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    </div>
                </x-slot:icon>
                <a href="{{ route('currency.index') }}" class="block px-2 py-1.5 text-[11px] font-medium rounded-lg transition-colors whitespace-nowrap {{ request()->routeIs('currency.*') ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">{{ __('menu.currency') }}</a>
                <a href="{{ route('cash-boxes.index') }}" class="block px-2 py-1.5 text-[11px] font-medium rounded-lg transition-colors whitespace-nowrap {{ request()->routeIs('cash-boxes.*') ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">{{ __('cash_box.title') }}</a>
                <a href="{{ route('group-spending.index') }}" class="block px-2 py-1.5 text-[11px] font-medium rounded-lg transition-colors whitespace-nowrap {{ (request()->routeIs('group-spending.*') || request()->routeIs('type-spending.*')) ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">{{ __('spending.group_title') }}</a>
                <a href="{{ route('profit.groups.index') }}" class="block px-2 py-1.5 text-[11px] font-medium rounded-lg transition-colors whitespace-nowrap {{ request()->routeIs('profit.*') ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">{{ __('profit.menu_tab') }}</a>
                <a href="{{ route('capitals.index') }}" class="block px-2 py-1.5 text-[11px] font-medium rounded-lg transition-colors whitespace-nowrap {{ request()->routeIs('capitals.*') ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">{{ __('menu.capital') }}</a>
            </x-nav-group>
        </div>

        {{-- 🔥 NEW ACCOUNTANT SECTION (UPDATED LINK) --}}
        <div class="mt-2 pt-2 border-t border-slate-800">
            {{-- Section Label --}}
            <div class="px-1 mb-2 transition-all duration-300 flex items-center" :class="isCollapsed ? 'justify-center' : ''">
                <p class="text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] px-1 whitespace-nowrap transition-all duration-300 origin-left"
                   :class="isCollapsed && window.innerWidth >= 768 ? 'w-0 opacity-0 hidden' : 'w-full opacity-100 block'">
                   {{ __('accountant.menu_title') }}
                </p>
                <div class="h-1.5 w-1.5 bg-indigo-500 rounded-full transition-all duration-300"
                     :class="isCollapsed && window.innerWidth >= 768 ? 'block opacity-100' : 'hidden opacity-0'">
                </div>
            </div>

            <div @mouseenter="showTooltip($event, '{{ __('accountant.menu_title') }}')" @mouseleave="hideTooltip()">
                <x-nav-group label="{{ __('accountant.menu_title') }}" :active="request()->routeIs('accountant.*')">
                    <x-slot:icon>
                        <div class="w-5 h-5 flex-shrink-0 flex items-center justify-center">
                            {{-- Calculator/Finance Icon --}}
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        </div>
                    </x-slot:icon>
                    
                    <a href="{{ route('accountant.receiving.index') }}" class="block px-2 py-1.5 text-[11px] font-medium rounded-lg transition-colors whitespace-nowrap {{ request()->routeIs('accountant.receiving.*') ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                        {{ __('accountant.receiving_money') }}
                    </a>
                    
                    {{-- 🟢 UPDATED: Pointing to Paying Index --}}
                    <a href="{{ route('accountant.paying.index') }}" class="block px-2 py-1.5 text-[11px] font-medium rounded-lg transition-colors whitespace-nowrap {{ request()->routeIs('accountant.paying.*') ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                        {{ __('accountant.paying_money') }}
                    </a>
                </x-nav-group>
            </div>
        </div>

        {{-- ADMIN SECTION --}}
        @role('super-admin')
        <div class="mt-2 pt-2 border-t border-slate-800">
             <div class="px-1 mb-2 transition-all duration-300 flex items-center" :class="isCollapsed ? 'justify-center' : ''">
                <p class="text-[9px] font-black text-slate-600 uppercase tracking-[0.2em] px-1 whitespace-nowrap transition-all duration-300 origin-left"
                   :class="isCollapsed && window.innerWidth >= 768 ? 'w-0 opacity-0 hidden' : 'w-full opacity-100 block'">
                   {{ app()->getLocale() == 'ku' ? 'بەڕێوەبردن' : 'ADMIN' }}
                </p>
                <div class="h-1.5 w-1.5 bg-slate-600 rounded-full transition-all duration-300"
                     :class="isCollapsed && window.innerWidth >= 768 ? 'block opacity-100' : 'hidden opacity-0'">
                </div>
            </div>
            
            <div @mouseenter="showTooltip($event, '{{ app()->getLocale() == 'ku' ? 'ڕێکخستنی سیستەم' : 'System Admin' }}')" @mouseleave="hideTooltip()">
                <x-nav-group label="{{ app()->getLocale() == 'ku' ? 'ڕێکخستنی سیستەم' : 'System Admin' }}" :active="request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('activity-log.*')">
                    <x-slot:icon>
                        <div class="w-5 h-5 flex-shrink-0 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        </div>
                    </x-slot:icon>
                    <a href="{{ route('users.index') }}" class="block px-2 py-1.5 text-[11px] font-medium text-slate-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors whitespace-nowrap">{{ app()->getLocale() == 'ku' ? 'بەکارهێنەران' : 'Users' }}</a>
                    <a href="{{ route('roles.index') }}" class="block px-2 py-1.5 text-[11px] font-medium text-slate-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors whitespace-nowrap">{{ app()->getLocale() == 'ku' ? 'دەسەڵاتەکان' : 'Roles' }}</a>
                    <a href="{{ route('activity-log.index') }}" class="block px-2 py-1.5 text-[11px] font-medium text-slate-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors whitespace-nowrap">{{ __('menu.activity_log') }}</a>
                </x-nav-group>
            </div>
        </div>
        @endrole
    </div>

   {{-- 4. FOOTER --}}
    <div class="p-3 border-t border-slate-800 bg-[#0a0f1c] space-y-2.5 shrink-0 z-20">
        
        <div class="grid gap-2 transition-all duration-300" 
             :class="(isCollapsed && window.innerWidth >= 768) ? 'grid-cols-1' : 'grid-cols-2'">
            
            {{-- SETTINGS BUTTON --}}
            <a href="{{ route('settings.index') }}" 
               @mouseenter="showTooltip($event, '{{ __('settings.menu_label') }}')" @mouseleave="hideTooltip()"
               class="flex items-center justify-center h-10 rounded-xl bg-slate-800 text-slate-400 hover:bg-indigo-600 hover:text-white transition-all shadow-lg border border-slate-700/50 group relative w-full">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 transition-transform duration-500 ease-in-out group-hover:rotate-180">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
            </a>

            {{-- LANGUAGE DROPDOWN --}}
            <div class="relative w-full h-10" x-data="{ langOpen: false }">
                <button @click="langOpen = !langOpen" @click.outside="langOpen = false" class="flex items-center justify-center w-full h-full p-2.5 rounded-xl bg-slate-800 text-slate-300 hover:bg-slate-700 border border-slate-700/50 transition-all font-bold text-xs uppercase shadow-lg">
                    {{ app()->getLocale() }}
                </button>
                <div x-show="langOpen" class="absolute bottom-full mb-2 bg-[#1e293b] border border-slate-700 rounded-xl shadow-2xl z-[60] overflow-hidden min-w-[120px]" :class="(isCollapsed && window.innerWidth >= 768) ? 'ltr:left-0 rtl:right-0' : 'ltr:right-0 rtl:left-0'" x-transition x-cloak>
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
            
            <div class="flex-1 min-w-0 overflow-hidden transition-all duration-300 ease-in-out origin-left"
                 :class="isCollapsed && window.innerWidth >= 768 ? 'w-0 opacity-0 scale-x-0' : 'w-auto opacity-100 scale-x-100'">
                <p class="text-xs font-bold text-white truncate leading-tight">{{ Auth::user()->name }}</p>
                <p class="text-[9px] text-slate-500 uppercase tracking-wider truncate">{{ Auth::user()->hasRole('super-admin') ? 'Super Admin' : 'Staff' }}</p>
            </div>
            
            <form action="{{ route('logout') }}" method="POST" 
                  class="transition-all duration-300"
                  :class="isCollapsed && window.innerWidth >= 768 ? 'w-0 opacity-0 hidden' : 'w-auto opacity-100 block'">
                @csrf
                <button type="submit" class="text-slate-500 hover:text-red-400 p-1.5 hover:bg-white/5 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </div>

    {{-- 🔥 THE MAGIC TOOLTIP --}}
    <div x-show="tooltip.show"
         x-cloak
         class="fixed z-[9999] px-3 py-1.5 text-[11px] font-bold text-white bg-slate-900 rounded-lg shadow-xl border border-slate-700 pointer-events-none transition-opacity duration-200 whitespace-nowrap"
         :style="document.dir === 'rtl' 
             ? `top: ${tooltip.top}px; right: ${tooltip.left}px; transform: translateY(-50%);` 
             : `top: ${tooltip.top}px; left: ${tooltip.left}px; transform: translateY(-50%);`">
        <span x-text="tooltip.text"></span>
        <div class="absolute top-1/2 -translate-y-1/2 border-4 border-transparent"
             :class="document.dir === 'rtl' ? '-right-1 border-l-slate-900' : '-left-1 border-r-slate-900'">
        </div>
    </div>

</aside>