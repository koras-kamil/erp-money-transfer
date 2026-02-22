<header class="sticky top-0 z-30 px-3 md:px-6 py-2 bg-[#f8fafc]/90 backdrop-blur-sm print:hidden">
    <div class="max-w-7xl mx-auto w-full">
        <div class="bg-white border border-slate-200/60 shadow-sm rounded-2xl px-3 py-2">
            
            {{-- TOOLBAR CONTAINER --}}
            <div class="flex flex-nowrap items-center justify-between w-full h-10 gap-2">
                
                {{-- 🟢 RIGHT SIDE (Menu & Bell) --}}
                <div class="flex items-center gap-3 shrink-0">
                    <div class="md:hidden shrink-0">
                        <button @click="mobileMenuOpen = !mobileMenuOpen" class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-50 text-slate-600 border border-slate-200 hover:bg-slate-100 hover:text-indigo-600 transition active:scale-95">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                        </button>
                    </div>

                    {{-- 🔔 NOTIFICATION BELL --}}
                    <div x-data="{ open: false }" class="relative z-50">
                        <button @click="open = !open" type="button" class="relative flex items-center justify-center w-9 h-9 transition-all duration-200 bg-white border border-slate-200 rounded-xl shadow-sm hover:border-indigo-300 hover:text-indigo-600 focus:ring-2 focus:ring-indigo-500/20 active:scale-95 group text-slate-500">
                            <svg class="w-4 h-4 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <span class="absolute top-1.5 right-1.5 flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500 border border-white"></span>
                                </span>
                            @endif
                        </button>
                    </div>
                </div>

                {{-- 🟡 MIDDLE SIDE: COMPACT SMART SEARCH (max-w-sm makes it smaller) --}}
                <div x-data="globalSearch()" class="flex-1 max-w-sm mx-4 hidden md:block relative z-50">
                    <div class="relative">
                        <div class="absolute inset-y-0 ltr:left-0 rtl:right-0 flex items-center ltr:pl-3 rtl:pr-3 pointer-events-none text-slate-400">
                            <svg x-show="!loading" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <svg x-show="loading" class="w-3.5 h-3.5 animate-spin text-indigo-500" fill="none" viewBox="0 0 24 24" x-cloak><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        </div>
                        
                        <input type="text" 
                               x-model="query" 
                               @input.debounce.400ms="performSearch" 
                               @focus="isOpen = true" 
                               @click.outside="isOpen = false"
                               placeholder="{{ __('گەڕان بۆ هەژمار، سندوق، بەکارهێنەر...') }}" 
                               class="w-full h-9 ltr:pl-9 rtl:pr-9 ltr:pr-4 rtl:pl-4 bg-slate-50 border border-slate-200 rounded-lg text-[13px] focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/10 transition-all font-medium text-slate-700 placeholder-slate-400">
                        
                        <button x-show="query.length > 0" @click="query = ''; results = {}; isOpen = false" class="absolute inset-y-0 ltr:right-0 rtl:left-0 flex items-center ltr:pr-3 rtl:pl-3 text-slate-400 hover:text-rose-500" x-cloak>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Search Results Dropdown --}}
                    <div x-show="isOpen && query.length >= 2" x-transition class="absolute top-full mt-2 w-full bg-white rounded-xl shadow-2xl border border-slate-100 overflow-hidden" x-cloak>
                        <div class="max-h-[350px] overflow-y-auto custom-scrollbar">
                            <template x-for="(items, category) in results" :key="category">
                                <div x-show="items.length > 0">
                                    <div class="px-3 py-1.5 bg-slate-50 text-[9px] font-black text-indigo-500 uppercase tracking-widest border-y border-slate-100" x-text="category"></div>
                                    <template x-for="item in items" :key="item.url">
                                        <a :href="item.url" class="flex items-center justify-between px-3 py-2.5 hover:bg-indigo-50 border-b border-slate-50 last:border-0 transition-colors group">
                                            <div class="flex flex-col">
                                                <span class="text-[13px] font-bold text-slate-700 group-hover:text-indigo-700" x-text="item.title"></span>
                                                <span class="text-[10px] text-slate-400 font-bold mt-0.5" x-text="item.subtitle"></span>
                                            </div>
                                            <svg class="w-3.5 h-3.5 text-slate-300 group-hover:text-indigo-500 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </template>
                                </div>
                            </template>

                            <div x-show="!loading && Object.keys(results).length === 0" class="p-4 text-center text-slate-400">
                                <span class="text-xs font-bold">{{ __('هیچ داتایەک نەدۆزرایەوە.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 🔴 LEFT SIDE (Help, Finance, Branch) --}}
                <div class="flex flex-1 flex-row items-center justify-end gap-2 shrink-0">
                    
                    {{-- 1. HELP BUTTON --}}
                    <div class="hidden md:flex items-center shrink-0">
                        <button @click="isBlurred = !isBlurred" type="button" class="flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg border border-indigo-100 hover:bg-indigo-100 transition active:scale-95 h-9">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <span class="text-[11px] font-bold uppercase tracking-wide">{{ __('Help') }}</span>
                        </button>
                    </div>

                    {{-- 2. FINANCE BUTTON --}}
                    <button @click="showExchangeModal = true" type="button" class="relative group flex items-center justify-center md:justify-between transition-all duration-200 bg-white border border-slate-200 rounded-lg shadow-sm hover:border-emerald-300 focus:ring-2 focus:ring-emerald-500/20 active:scale-95 w-9 h-9 md:w-auto md:h-9 md:pl-3 md:pr-1 md:py-1"> 
                        <div class="flex items-center gap-2">
                            <span class="hidden md:block text-[11px] font-bold text-slate-700 truncate">{{ __('messages.finance') }}</span>
                            <div class="bg-emerald-50 text-emerald-600 rounded p-1 shrink-0 group-hover:bg-emerald-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>
                    </button>

                    {{-- 3. BRANCH SELECTOR --}}
                    <div x-data="{ open: false }" class="relative z-50 group" @click.outside="open = false">
                        <button @click="open = !open" type="button" class="flex items-center justify-center md:justify-between transition-all duration-200 bg-white border border-slate-200 rounded-lg shadow-sm hover:border-indigo-300 focus:ring-2 focus:ring-indigo-500/20 active:scale-95 w-9 h-9 md:w-auto md:h-9 md:pl-2.5 md:pr-1 md:py-1"> 
                            <div class="md:hidden bg-indigo-50 text-indigo-600 rounded p-1 shrink-0"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg></div>
                            <div class="hidden md:flex items-center gap-2.5">
                                <div class="flex items-center gap-1.5 text-slate-700">
                                    <span class="text-[11px] font-bold truncate max-w-[100px]">{{ __('messages.all_branches') }}</span>
                                </div>
                                <div class="bg-indigo-50 text-indigo-600 rounded p-1 shrink-0 group-hover:bg-indigo-100 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                </div>
                            </div>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Script for Smart Search --}}
  <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('globalSearch', () => ({
                query: '',
                results: {},
                loading: false,
                isOpen: false,
                async performSearch() {
                    // 🟢 CHANGED: Trigger search immediately on 1 letter
                    if (this.query.length < 1) { 
                        this.results = {};
                        return;
                    }
                    this.loading = true;
                    try {
                        let targetUrl = `{{ url('/smart-search') }}?q=${encodeURIComponent(this.query)}`;
                        
                        let response = await fetch(targetUrl, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });

                        if (response.ok) {
                            this.results = await response.json();
                        } else {
                            console.error("Server returned status: ", response.status);
                        }
                    } catch (error) {
                        console.error('Search failed', error);
                        this.results = {};
                    }
                    this.loading = false;
                }
            }));
        });
    </script>
</header>