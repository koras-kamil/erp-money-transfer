<x-app-layout>
    {{-- AlpineJS & Lottie --}}
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>

    {{-- DRAG & DROP & RESIZE STYLES --}}
    <style>
        .th-container { position: relative; width: 100%; height: 32px; display: flex; align-items: center; justify-content: center; overflow: visible; }
        .th-title { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; gap: 4px; transition: all 0.2s ease; transform: translateY(0); opacity: 1; cursor: grab; z-index: 10; }
        .th-title:active { cursor: grabbing; }
        .search-active .th-title { transform: translateY(-150%); opacity: 0; pointer-events: none; }
        .th-search { position: absolute; inset: 0; display: flex; align-items: center; transition: all 0.2s ease; transform: translateY(150%); opacity: 0; pointer-events: none; z-index: 20; background-color: #f8fafc; }
        .search-active .th-search { transform: translateY(0); opacity: 1; pointer-events: auto; }
        .header-search-input { width: 100%; height: 28px; background-color: #fff; border: 1px solid #cbd5e1; border-radius: 6px; padding-left: 8px; padding-right: 24px; font-size: 0.75rem; color: #1f2937; transition: all 0.15s; }
        [dir="rtl"] .header-search-input { padding-left: 24px; padding-right: 8px; }
        .header-search-input:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1); }
        .resizer { position: absolute; right: -4px; top: 0; height: 100%; width: 8px; cursor: col-resize; z-index: 50; touch-action: none; }
        .resizer:hover::after, .resizing::after { content: ''; position: absolute; right: 4px; top: 20%; height: 60%; width: 2px; background-color: #3b82f6; }
        [dir="rtl"] .resizer { right: auto; left: -4px; }
        [dir="rtl"] .resizer:hover::after { right: auto; left: 4px; }
        .dragging-col { opacity: 0.4; background-color: #e0e7ff; border: 2px dashed #6366f1; }
        .custom-scrollbar::-webkit-scrollbar { height: 8px; width: 8px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .acct-table th { border: 1px solid #e2e8f0; }
        .acct-table td { border: 1px solid #f1f5f9; }
    </style>

    <div x-data="statementManager()" x-init="initData()" class="flex flex-col lg:flex-row gap-5 items-start w-full font-sans text-slate-800 p-4" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

        {{-- ==================================================================================== --}}
        {{-- 1. RIGHT-SIDE MENU (Search & Profile Card) --}}
        {{-- ==================================================================================== --}}
        <div :class="showSidebar ? 'w-full lg:w-[320px] opacity-100' : 'w-0 opacity-0 pointer-events-none'"
             class="flex-shrink-0 bg-white border border-slate-200 rounded-[1.5rem] shadow-sm transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] overflow-hidden sticky top-4 flex flex-col z-20"
             style="max-h: calc(100vh - 40px);">
             
             <div class="w-full lg:w-[320px] flex flex-col h-full">
                {{-- SEARCH INPUT --}}
                <div class="p-3 border-b border-slate-100 bg-slate-50/80 sticky top-0 z-20 shrink-0">
                    <form method="GET" action="{{ route('accountant.statement.index') }}" class="relative group">
                        <input type="text" x-model="search" name="search" placeholder="{!! addslashes(__('statement.search_user')) !!}" class="w-full h-11 px-10 rounded-xl border border-slate-300 bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm font-black text-center text-slate-700 placeholder-slate-400 transition-all shadow-sm" autocomplete="off">
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></div>
                        <button type="button" x-show="search.length > 0" @click="search = ''; window.location.href='{{ route('accountant.statement.index') }}'" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-rose-400 hover:text-rose-600 transition-colors" x-cloak><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </form>
                </div>

                <div class="flex-1 overflow-y-auto custom-scrollbar bg-white">
                    <div x-show="search.length > 0" x-cloak>
                        <template x-for="acc in filteredAccounts" :key="acc.id">
                            <a :href="'/accountant/statement?account_id=' + acc.id" class="flex items-center gap-3 p-3.5 border-b border-slate-50 hover:bg-indigo-50 transition-colors group cursor-pointer">
                                <img :src="acc.profile_picture ? '/storage/' + acc.profile_picture : 'https://ui-avatars.com/api/?name=' + acc.name" class="w-10 h-10 rounded-full border border-slate-200 object-cover group-hover:border-indigo-300">
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm font-bold text-slate-700 group-hover:text-indigo-700 truncate" x-text="acc.name"></span>
                                        <span class="text-xs font-mono text-slate-400 bg-slate-100 px-1.5 rounded" x-text="acc.code"></span>
                                    </div>
                                </div>
                            </a>
                        </template>
                    </div>

                    @if($account)
                        <div x-show="search.length === 0">
                            <div class="p-6 flex flex-col items-center border-b border-slate-200 bg-white relative">
                                <div class="flex items-center justify-between w-full mb-4">
                                    <div class="flex-1 text-center"><h2 class="text-lg font-black text-slate-800 leading-tight truncate px-1">{{ $account->name }}</h2></div>
                                    <div class="relative group ml-4"><img src="{{ $account->profile_picture ? asset('storage/'.$account->profile_picture) : 'https://ui-avatars.com/api/?name='.$account->name }}" class="w-16 h-16 rounded-full object-cover border-4 border-slate-50 shadow-md ring-1 ring-slate-200"></div>
                                </div>
                                <div class="w-full flex items-center justify-center gap-4 bg-slate-50 border border-slate-100 rounded-xl p-2.5 shadow-inner">
                                    <span class="px-2 py-0.5 rounded bg-white border border-slate-200 text-xs font-black font-mono text-indigo-600 shadow-sm">{{ $account->code }}</span>
                                    <div class="h-5 w-px bg-slate-300"></div>
                                    <span class="text-xs font-bold text-slate-500">{{ __("statement.{$account->account_type}") ?? 'User' }}</span>
                                </div>

                                {{-- FULL INFORMATION & INLINE EDIT BUTTON --}}
                                <div x-data="{ openInfo: false }" class="w-full mt-5">
                                    <div class="flex items-center gap-2">
                                        <button @click="openInfo = !openInfo" class="flex-1 flex items-center justify-between px-4 py-2.5 bg-slate-50 hover:bg-slate-100 rounded-xl border border-slate-200 transition-colors shadow-sm">
                                            <span class="text-xs font-bold text-slate-700">{!! addslashes(__('statement.full_info')) !!}</span>
                                            <svg class="w-4 h-4 text-slate-500 transition-transform duration-300" :class="openInfo ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                        
                                        <button type="button" @click="openAccountEdit()" class="w-10 h-10 flex items-center justify-center bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm" title="{!! addslashes(__('statement.edit_account')) !!}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                    </div>
                                    
                                    <div x-show="openInfo" x-collapse x-cloak>
                                        <div class="mt-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden divide-y divide-slate-100">
                                            <div class="flex justify-between items-center px-4 py-2.5">
                                                <span class="text-[11px] font-bold text-slate-500">{!! addslashes(__('statement.full_name')) !!}</span>
                                                <span class="text-xs font-black text-slate-800">{{ $account->secondary_name ?? '-' }}</span>
                                            </div>
                                            <div class="flex justify-between items-center px-4 py-2.5 bg-slate-50/50">
                                                <span class="text-[11px] font-bold text-slate-500">{!! addslashes(__('statement.mobile_1')) !!}</span>
                                                <span class="text-xs font-mono font-bold text-slate-800" dir="ltr">{{ $account->mobile_number_1 ?? '-' }}</span>
                                            </div>
                                            <div class="flex justify-between items-center px-4 py-2.5">
                                                <span class="text-[11px] font-bold text-slate-500">{!! addslashes(__('statement.mobile_2')) !!}</span>
                                                <span class="text-xs font-mono font-bold text-slate-800" dir="ltr">{{ $account->mobile_number_2 ?? '-' }}</span>
                                            </div>
                                            <div class="flex justify-between items-center px-4 py-2.5 bg-slate-50/50">
                                                <span class="text-[11px] font-bold text-slate-500">{!! addslashes(__('statement.email')) !!}</span>
                                                <span class="text-xs font-bold text-slate-800">{{ $account->email ?? '-' }}</span>
                                            </div>
                                            <div class="flex justify-between items-center px-4 py-2.5">
                                                <span class="text-[11px] font-bold text-slate-500">{!! addslashes(__('statement.city_and_neighborhood')) !!}</span>
                                                <span class="text-xs font-bold text-slate-800">{{ $account->city->city_name ?? '-' }} / {{ $account->neighborhood->neighborhood_name ?? '-' }}</span>
                                            </div>
                                            <div class="flex justify-between items-center px-4 py-2.5 bg-slate-50/50">
                                                <span class="text-[11px] font-bold text-slate-500">{!! addslashes(__('statement.address')) !!}</span>
                                                <span class="text-xs font-bold text-slate-800">{{ $account->address ?? '-' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- 🟢 بەشی دوایین جوڵەکان 🟢 --}}
                                <div x-data="{ openMovements: false }" class="w-full mt-3">
                                    <button @click="openMovements = !openMovements" class="w-full flex items-center justify-between px-4 py-2 bg-indigo-50 hover:bg-indigo-100 rounded-xl border border-indigo-100 transition-colors shadow-sm">
                                        <span class="text-xs font-bold text-indigo-700">{!! addslashes(__('statement.last_movements')) !!}</span>
                                        <svg class="w-4 h-4 text-indigo-500 transition-transform duration-300" :class="openMovements ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    
                                    <div x-show="openMovements" x-collapse x-cloak>
                                        <div class="mt-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden divide-y divide-slate-100">
                                            <div class="flex justify-between items-center px-4 py-2.5">
                                                <span class="text-[11px] font-bold text-slate-500">{!! addslashes(__('statement.last_receive')) !!}</span>
                                                <span class="text-xs font-mono font-bold text-emerald-600" dir="ltr">{{ $lastMovements['receive'] ?? '-' }}</span>
                                            </div>
                                            <div class="flex justify-between items-center px-4 py-2.5 bg-slate-50/50">
                                                <span class="text-[11px] font-bold text-slate-500">{!! addslashes(__('statement.last_pay')) !!}</span>
                                                <span class="text-xs font-mono font-bold text-rose-600" dir="ltr">{{ $lastMovements['pay'] ?? '-' }}</span>
                                            </div>
                                            <div class="flex justify-between items-center px-4 py-2.5">
                                                <span class="text-[11px] font-bold text-slate-500">{!! addslashes(__('statement.last_sale')) !!}</span>
                                                <span class="text-xs font-mono font-bold text-indigo-600" dir="ltr">{{ $lastMovements['sale'] ?? '-' }}</span>
                                            </div>
                                            <div class="flex justify-between items-center px-4 py-2.5 bg-slate-50/50">
                                                <span class="text-[11px] font-bold text-slate-500">{!! addslashes(__('statement.last_purchase')) !!}</span>
                                                <span class="text-xs font-mono font-bold text-slate-800" dir="ltr">{{ $lastMovements['purchase'] ?? '-' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="p-5 border-b border-slate-200 bg-white">
                                <h3 class="text-xs font-black text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {!! addslashes(__('statement.financial_status')) !!}</h3>
                                <div class="mb-3">
                                    <button @click="filters.target_currency = ''" class="w-full flex items-center justify-center px-4 py-2 rounded-xl border transition-all text-xs font-bold" :class="filters.target_currency === '' ? 'bg-slate-800 text-white border-slate-900 shadow-md' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'">{!! addslashes(__('statement.all_currencies')) !!}</button>
                                </div>
                                <div class="space-y-3">
                                    @foreach($supportedCurrencies ?? [] as $currency)
                                        <div @click="filters.target_currency = (filters.target_currency === '{{ $currency->currency_type }}' ? '' : '{{ $currency->currency_type }}')" 
                                             class="cursor-pointer flex items-center justify-between px-4 py-2.5 rounded-xl border shadow-sm transition-all"
                                             :class="filters.target_currency === '{{ $currency->currency_type }}' ? 'bg-indigo-600 text-white border-indigo-700 ring-2 ring-indigo-300 ring-offset-1 scale-[1.02]' : 'bg-white hover:bg-slate-50 border-slate-200 text-slate-700'">
                                            <div class="flex flex-col">
                                                <span class="text-[10px] font-black uppercase tracking-widest mb-0.5" :class="filters.target_currency === '{{ $currency->currency_type }}' ? 'text-indigo-100' : 'text-slate-500'">{{ $currency->currency_type }}</span>
                                                <span class="text-[11px] font-bold opacity-80 uppercase" :class="filters.target_currency === '{{ $currency->currency_type }}' ? 'text-indigo-100' : 'text-slate-400'" x-text="(trueBalances['{{ $currency->currency_type }}'] < 0) ? '{!! addslashes(__('statement.debt')) !!}' : '{!! addslashes(__('statement.creditor')) !!}'"></span>
                                            </div>
                                            <span class="font-black text-base" :class="filters.target_currency === '{{ $currency->currency_type }}' ? 'text-white' : (trueBalances['{{ $currency->currency_type }}'] < 0 ? 'text-rose-600' : 'text-emerald-600')" dir="ltr" x-text="(trueBalances['{{ $currency->currency_type }}'] < 0 ? '-' : '+') + formatMoney(Math.abs(trueBalances['{{ $currency->currency_type }}'] || 0))"></span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <div x-show="search.length === 0" class="flex flex-col items-center justify-center h-full text-slate-400 p-8 text-center"><p class="text-xs font-bold text-slate-500 uppercase">{!! addslashes(__('statement.select_account_first')) !!}</p></div>
                    @endif
                </div>
             </div>
        </div>

        {{-- ==================================================================================== --}}
        {{-- 2. MAIN CONTENT AREA --}}
        {{-- ==================================================================================== --}}
        @if($account)
            <div class="flex-1 min-w-0 bg-white border border-slate-200 rounded-[1.5rem] shadow-sm flex flex-col overflow-visible w-full relative z-[50]">
                
                {{-- Toolbar --}}
                <div class="bg-slate-50/50 border-b border-slate-200 p-4 flex flex-col xl:flex-row justify-between items-center gap-4 shrink-0 relative z-[60] rounded-t-[1.5rem]">
                    <div class="flex items-center gap-3 w-full xl:w-auto">
                        <button @click="showSidebar = !showSidebar" class="flex items-center justify-center w-10 h-10 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-indigo-50 hover:text-indigo-600 transition-colors shadow-sm shrink-0"><svg x-show="!showSidebar" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg><svg x-show="showSidebar" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg></button>
                        <div class="w-px h-6 bg-slate-200 mx-1 hidden xl:block"></div>
                        <h1 class="text-sm font-black text-slate-800 uppercase tracking-wide px-1 whitespace-nowrap" x-text="filters.target_currency ? '{!! addslashes(__('statement.statement')) !!} - ' + filters.target_currency : '{!! addslashes(__('statement.statement_general')) !!}'"></h1>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5 w-full xl:w-auto justify-end">
                        
                        {{-- Columns Toggle --}}
                        <div x-data="{ openCols: false }" class="relative z-[100]">
                            <button @click="openCols = !openCols" @click.outside="openCols = false" title="{!! addslashes(__('statement.columns')) !!}" class="w-10 h-10 flex items-center justify-center bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-indigo-50 hover:text-indigo-600 transition-colors shadow-sm"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg></button>
                            <div x-show="openCols" class="absolute top-full mt-3 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 z-[999] p-3 ltr:right-0 rtl:left-0" style="display:none;"><div class="flex justify-between items-center px-2 py-1 mb-2 border-b border-slate-100 pb-2"><span class="text-[10px] font-bold text-slate-400 uppercase">{!! addslashes(__('statement.columns')) !!}</span><button @click="resetLayout(); openCols = false;" class="text-[10px] text-blue-500 hover:underline cursor-pointer">{!! addslashes(__('statement.reset_layout')) !!}</button></div><div class="max-h-60 overflow-y-auto space-y-1"><template x-for="col in columns" :key="col.field"><label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 rounded cursor-pointer transition"><input type="checkbox" x-model="col.visible" class="rounded text-indigo-600 w-4 h-4 border-slate-300 focus:ring-indigo-500"><span class="text-xs text-slate-700 font-medium" x-text="col.label"></span></label></template></div></div>
                        </div>

                        {{-- DATE FILTER --}}
                        <form x-ref="filterForm" method="GET" action="{{ route('accountant.statement.index') }}" class="flex items-center gap-1.5 bg-white border border-slate-200 rounded-xl p-1 shadow-sm">
                            <input type="hidden" name="account_id" value="{{ request('account_id') }}">
                            @if(request('currency_id')) <input type="hidden" name="currency_id" value="{{ request('currency_id') }}"> @endif
                            
                            <select x-model="datePreset" @change="applyDatePreset()" class="h-8 w-32 text-xs font-bold border-0 bg-slate-50/50 rounded-lg text-slate-600 focus:ring-0 cursor-pointer">
                                <option value="">{!! addslashes(__('statement.custom_date')) !!}</option>
                                <option value="today">{!! addslashes(__('statement.today')) !!}</option>
                                <option value="yesterday">{!! addslashes(__('statement.yesterday')) !!}</option>
                                <option value="week">{!! addslashes(__('statement.last_week')) !!}</option>
                                <option value="month">{!! addslashes(__('statement.last_month')) !!}</option>
                                <option value="year">{!! addslashes(__('statement.last_year')) !!}</option>
                            </select>

                            <div class="w-px h-5 bg-slate-200 mx-1"></div>

                            <input type="date" x-model="startDate" name="start_date" class="h-8 w-[115px] text-xs font-mono font-bold border-0 bg-transparent focus:ring-0 text-slate-600 cursor-pointer text-center">
                            <span class="text-slate-300 text-xs">-</span>
                            <input type="date" x-model="endDate" name="end_date" class="h-8 w-[115px] text-xs font-mono font-bold border-0 bg-transparent focus:ring-0 text-slate-600 cursor-pointer text-center">
                            
                            <button type="submit" class="h-8 px-4 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-xs font-bold shadow-sm">{!! addslashes(__('statement.search')) !!}</button>
                        </form>
                        
                        <button type="button" onclick="window.print()" title="{!! addslashes(__('statement.print')) !!}" class="h-8 px-4 bg-slate-700 text-white rounded-lg hover:bg-slate-800 transition-colors text-xs font-bold shadow-sm">{!! addslashes(__('statement.print')) !!}</button>
                    </div>
                </div>

                {{-- Table Wrapper --}}
                <div class="flex-1 overflow-x-auto overflow-y-auto w-full custom-scrollbar relative rounded-b-[1.5rem]">
                    <table class="w-full text-xs text-center rtl:text-center text-slate-600 whitespace-nowrap border-collapse acct-table">
                        
                        <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200 sticky top-0 z-20 shadow-sm font-bold">
                            <tr>
                                <template x-for="(col, index) in columns" :key="col.field">
                                    <th x-show="col.visible" class="px-2 py-3 relative h-12 text-center transition-colors duration-200 select-none group border-l border-slate-100 last:border-0" :style="'min-width:' + col.width + 'px'" draggable="true" @dragstart="dragStart($event, index)" @dragover.prevent="dragOver($event)" @drop="drop($event, index)" :class="{'dragging-col': draggingIndex === index}">
                                        <div class="th-container" :class="{ 'search-active': openFilter === col.field }">
                                            <div class="th-title">
                                                <div @click="sortBy(col.field)" class="flex items-center justify-center gap-1 cursor-pointer flex-1 h-full hover:text-indigo-600 transition-colors"><span x-text="col.label" class="whitespace-nowrap tracking-wide text-[11px]"></span></div>
                                                <button x-show="col.searchable !== false" type="button" @click.stop="openFilter = col.field; setTimeout(() => $refs['input-'+col.field]?.focus(), 100)" class="p-1 rounded-md text-slate-300 hover:text-indigo-600 transition" :class="filters[col.field] ? 'text-indigo-600' : ''"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></button>
                                            </div>
                                            <div x-show="col.searchable !== false" class="th-search" @click.stop><input type="text" :x-ref="'input-'+col.field" x-model="filters[col.field]" @keydown.escape="openFilter = null" class="header-search-input w-full text-black" placeholder="{!! addslashes(__('statement.search')) !!}..."><button type="button" @click.stop="filters[col.field] = ''; openFilter = null;" class="absolute right-1 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500 p-0.5 rounded-md rtl:left-1 rtl:right-auto"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div>
                                        </div>
                                        <div class="resizer" @mousedown.stop.prevent="initResize($event, col)"></div>
                                    </th>
                                </template>
                            </tr>
                        </thead>
                        
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <template x-for="(trx, index) in filteredTransactions" :key="trx.row_id">
                                {{-- 🟢 DYNAMIC RED LIGHT BACKGROUND FOR DISCOUNT ROWS --}}
                                <tr :class="trx.is_discount_row ? 'bg-red-50/70 hover:bg-red-100/70 text-red-700' : 'bg-white hover:bg-slate-50'" class="transition-colors group">
                                    <template x-for="col in columns" :key="col.field">
                                        <td x-show="col.visible" class="px-2 py-2.5 text-center align-middle border-l border-slate-50 last:border-0" :class="col.field === 'base_currency' ? (!trx.is_discount_row && (trx.base_currency === 'دینار' || trx.base_currency === 'IQD') ? 'bg-yellow-100/80 text-yellow-800 font-bold' : '') : ''">
                                            
                                            {{-- SHARED FIELDS --}}
                                            <template x-if="col.field === 'row_index'"><span class="font-mono text-slate-400" x-text="trx.row_index"></span></template>
                                            <template x-if="col.field === 'created_time'"><span class="font-mono text-slate-400 font-bold text-[11px]" x-text="trx.created_time"></span></template>
                                            <template x-if="col.field === 'created_date'"><span class="font-mono text-slate-600 font-bold" x-text="trx.created_date"></span></template>
                                            <template x-if="col.field === 'user_name'"><span class="font-bold truncate block text-center max-w-[120px] mx-auto" x-text="trx.user_name"></span></template>
                                            
                                            <template x-if="col.field === 'id'">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <span class="font-mono font-bold" x-text="trx.id"></span>
                                                    <template x-if="!trx.is_discount_row && trx.has_discount">
                                                        <span class="relative flex h-2.5 w-2.5" title="{!! addslashes(__('statement.invoice_has_discount')) !!}">
                                                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                                          <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500"></span>
                                                        </span>
                                                    </template>
                                                </div>
                                            </template>
                                            
                                            <template x-if="col.field === 'bill_type'"><span class="font-bold" x-text="getBillType(trx.bill_type)"></span></template>
                                            <template x-if="col.field === 'exchange_rate'"><span class="font-mono font-bold text-[11px]" x-text="formatMoney(trx.exchange_rate)"></span></template>
                                            <template x-if="col.field === 'base_currency'"><span class="font-bold" x-text="trx.base_currency"></span></template>
                                            
                                            {{-- 🟢 NEW EXPLANATION COLUMN (Ledger Mode) --}}
                                            <template x-if="col.field === 'row_explanation'"><span class="font-bold text-slate-700 bg-slate-100 px-2 py-1 rounded border border-slate-200" x-text="trx.row_explanation"></span></template>
                                            
                                            {{-- 🟢 STRICT MANUAL NOTE COLUMN --}}
                                            <template x-if="col.field === 'note'"><span class="font-medium truncate max-w-[150px] block mx-auto text-slate-600" x-text="trx.display_note"></span></template>

                                            {{-- 🟢 GENERAL MODE --}}
                                            <template x-if="col.field === 'target_currency'"><span class="font-black text-[11px] text-indigo-700 bg-indigo-50 px-2 py-1 rounded-md border border-indigo-200" x-text="trx.target_currency"></span></template>
                                            <template x-if="col.field === 'total_display'"><span class="font-black text-slate-800 bg-slate-100 px-2.5 py-1 rounded" x-text="formatMoney(trx.total_display)"></span></template>
                                            <template x-if="col.field === 'discount_display'"><span class="font-bold" :class="trx.discount_display > 0 ? 'text-red-500' : 'text-slate-400'" x-text="trx.discount_display > 0 ? formatMoney(trx.discount_display) : '-'"></span></template>
                                            <template x-if="col.field === 'cash_display'"><span class="font-black text-emerald-600" x-text="formatMoney(trx.cash_display)"></span></template>
                                            <template x-if="col.field === 'loan_display'"><span class="font-bold text-slate-700" x-text="formatMoney(trx.loan_display)"></span></template>

                                            {{-- 🟢 LEDGER MODE --}}
                                            <template x-if="col.field === 'statement_id'"><span class="font-mono bg-slate-50 px-2 py-0.5 rounded border border-slate-100" x-text="trx.statement_id || '-'"></span></template>
                                            <template x-if="col.field === 'display_amount'"><span class="font-black" x-text="formatMoney(trx.display_amount)"></span></template>
                                            
                                            <template x-if="col.field === 'debit'">
                                                <span class="font-black" :class="trx.debit !== 0 ? (trx.is_discount_row ? 'text-red-600' : 'text-slate-800') : 'text-slate-300'" dir="ltr" x-text="trx.debit !== 0 ? formatMoney(trx.debit) + ' ' + getCurrencySymbol(trx.target_currency) : '-'"></span>
                                            </template>
                                            
                                            <template x-if="col.field === 'credit'">
                                                <span class="font-black" :class="trx.credit !== 0 ? (trx.is_discount_row ? 'text-red-600' : 'text-slate-800') : 'text-slate-300'" dir="ltr" x-text="trx.credit !== 0 ? formatMoney(trx.credit) + ' ' + getCurrencySymbol(trx.target_currency) : '-'"></span>
                                            </template>
                                            
                                            <template x-if="col.field === 'running_balance'">
                                                <span class="font-black text-indigo-700 bg-indigo-50/50 px-2 py-1 rounded border border-indigo-100/50" dir="ltr" x-text="(trx.running_balance < 0 ? '-' : '+') + formatMoney(Math.abs(trx.running_balance)) + ' ' + getCurrencySymbol(trx.target_currency)"></span>
                                            </template>

                                            {{-- 🟢 INVOICE EDIT ACTIONS --}}
                                            <template x-if="col.field === 'actions'">
                                                <div class="flex items-center justify-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <template x-if="!trx.is_discount_row">
                                                        <div class="flex items-center gap-1.5">
                                                            <button type="button" @click="editTransaction(trx)" class="p-1 text-indigo-500 bg-indigo-50 hover:bg-indigo-100 rounded border border-indigo-100">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                            </button>
                                                            <form :action="getDeleteUrl(trx)" method="POST" onsubmit="return confirm('{!! addslashes(__('statement.are_you_sure_delete_invoice')) !!}');" class="inline">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="p-1 text-rose-500 bg-rose-50 hover:bg-rose-100 rounded border border-rose-100">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>

                                        </td>
                                    </template>
                                </tr>
                            </template>
                            <tr x-show="filteredTransactions.length === 0" x-cloak><td :colspan="columns.length" class="text-center py-16 text-slate-400 bg-white"><span class="text-sm font-bold">{!! addslashes(__('statement.no_data_found')) !!}</span></td></tr>
                        </tbody>
                        
                        <tfoot x-show="filters.target_currency !== '' && currentTotals" class="bg-indigo-50 border-t-[3px] border-indigo-200 sticky bottom-0 z-20 shadow-[0_-4px_10px_-2px_rgba(0,0,0,0.1)]" x-cloak>
                            <tr class="border-b border-indigo-100">
                                <template x-for="col in columns" :key="col.field">
                                    <td x-show="col.visible" class="px-2 py-3.5 text-center border-l border-indigo-100/50 align-middle">
                                        <template x-if="col.field === 'base_currency'"><span class="font-black text-indigo-900 tracking-wide text-sm">{!! addslashes(__('statement.grand_total')) !!}</span></template>
                                        <template x-if="col.field === 'debit'"><span class="text-slate-800 font-black text-sm" dir="ltr" x-text="formatMoney(currentTotals?.total_debit) + ' ' + getCurrencySymbol(filters.target_currency)"></span></template>
                                        <template x-if="col.field === 'credit'"><span class="text-slate-800 font-black text-sm" dir="ltr" x-text="formatMoney(currentTotals?.total_credit) + ' ' + getCurrencySymbol(filters.target_currency)"></span></template>
                                        <template x-if="col.field === 'running_balance'"><span class="text-white font-black text-sm bg-indigo-600 px-3 py-1.5 rounded shadow-sm" dir="ltr" x-text="(currentTotals?.balance < 0 ? '-' : '+') + formatMoney(Math.abs(currentTotals?.balance)) + ' ' + getCurrencySymbol(filters.target_currency)"></span></template>
                                        <template x-if="!['base_currency', 'debit', 'credit', 'running_balance'].includes(col.field)"><span></span></template>
                                    </td>
                                </template>
                            </tr>
                        </tfoot>
                        
                    </table>
                </div>
            </div>
        @else
            <div class="flex-1 min-w-0 bg-slate-50/50 border border-slate-200 rounded-[1.5rem] shadow-sm flex flex-col items-center justify-center overflow-hidden w-full p-8 text-center relative" style="min-height: 60vh;">
                <svg class="w-24 h-24 text-slate-300 mx-auto mb-6 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <h2 class="text-2xl font-black text-slate-700 mb-3">{!! addslashes(__('statement.select_account_first')) !!}</h2>
            </div>
        @endif
    </div>

    {{-- 🟢 SECURE PHP INJECTIONS FOR THE MODALS 🟢 --}}
    @php
        if (!isset($cashboxes)) $cashboxes = class_exists('\App\Models\Cashbox') ? \App\Models\Cashbox::where('is_active', true)->get() : collect();
        if (!isset($currencies)) $currencies = class_exists('\App\Models\CurrencyConfig') ? \App\Models\CurrencyConfig::where('is_active', true)->get() : collect();
        if (!isset($profitTypes)) $profitTypes = class_exists('\App\Models\ProfitType') ? \App\Models\ProfitType::all() : collect();
        if (!isset($spendingTypes)) $spendingTypes = class_exists('\App\Models\TypeSpending') ? \App\Models\TypeSpending::all() : collect();
        
        $edit_branches = class_exists('\App\Models\Branch') ? \App\Models\Branch::all() : collect();
        $edit_cities = class_exists('\App\Models\City') ? \App\Models\City::all() : collect();
        $edit_neighborhoods = class_exists('\App\Models\Neighborhood') ? \App\Models\Neighborhood::all() : collect();
        if (!isset($accounts)) {
            $accounts = class_exists('\App\Models\Account') ? \App\Models\Account::where('is_active', true)->get()->map(function($acc) {
                $supported = is_string($acc->supported_currency_ids) ? json_decode($acc->supported_currency_ids, true) : (is_array($acc->supported_currency_ids) ? $acc->supported_currency_ids : []);
                return [
                    'id' => $acc->id, 'name' => $acc->name, 'code' => $acc->manual_code ?? $acc->code,
                    'avatar' => $acc->profile_picture ? asset('storage/'.$acc->profile_picture) : null,
                    'supported_currencies' => $supported ?? [], 'default_currency_id' => $acc->currency_id
                ];
            }) : collect();
        }
    @endphp

    {{-- INVOICE MODALS --}}
    @includeIf('accountant.paying.form-modal')
    @includeIf('accountant.receiving.form-modal')

    {{-- 🟢 THE PURE ALPINEJS ACCOUNT EDIT MODAL --}}
    @if($account)
    <div x-data="{ isEditingAccount: false }" 
         @open-account-edit.window="isEditingAccount = true" 
         @keydown.escape.window="isEditingAccount = false"
         x-show="isEditingAccount" 
         class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" 
         x-cloak>
         
        <div @click.away="isEditingAccount = false" class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl p-8 relative max-h-[90vh] overflow-y-auto custom-scrollbar">
            
            <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-2xl font-black text-slate-800">{!! addslashes(__('statement.edit_account')) !!}</h3>
                    <p class="text-sm text-slate-400 mt-1">{!! addslashes(__('statement.update_account_info')) !!}</p>
                </div>
                <button type="button" @click="isEditingAccount = false" class="text-slate-400 hover:text-slate-600 bg-slate-50 hover:bg-slate-100 p-2.5 rounded-xl transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <form action="{{ route('accounts.update', $account->id) }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                @csrf
                @method('PUT')
                
                {{-- Left Col --}}
                <div class="space-y-5">
                    <div>
                        <label class="block font-bold text-xs text-slate-700 mb-1">{!! addslashes(__('statement.full_name')) !!} *</label>
                        <input type="text" name="name" value="{{ $account->name }}" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none" required>
                    </div>
                    <div>
                        <label class="block font-bold text-xs text-slate-700 mb-1">{!! addslashes(__('statement.secondary_name')) !!}</label>
                        <input type="text" name="secondary_name" value="{{ $account->secondary_name }}" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-xs text-slate-700 mb-1">{!! addslashes(__('statement.account_type')) !!} *</label>
                        <select name="account_type" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 bg-white" required>
                            <option value="customer" {{ $account->account_type == 'customer' ? 'selected' : '' }}>{!! addslashes(__('statement.customer')) !!}</option>
                            <option value="vendor" {{ $account->account_type == 'vendor' ? 'selected' : '' }}>{!! addslashes(__('statement.vendor')) !!}</option>
                            <option value="buyer_and_seller" {{ $account->account_type == 'buyer_and_seller' ? 'selected' : '' }}>{!! addslashes(__('statement.buyer_and_seller')) !!}</option>
                            <option value="other" {{ $account->account_type == 'other' ? 'selected' : '' }}>{!! addslashes(__('statement.other')) !!}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-xs text-slate-700 mb-1">{!! addslashes(__('statement.branch')) !!}</label>
                        <select name="branch_id" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">{!! addslashes(__('statement.select_branch')) !!}</option>
                            @foreach($edit_branches as $b)
                                <option value="{{ $b->id }}" {{ $account->branch_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-xs text-slate-700 mb-1">{!! addslashes(__('statement.code')) !!}</label>
                            <input type="text" name="code" value="{{ $account->code }}" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm bg-slate-50 text-slate-500" readonly>
                        </div>
                        <div>
                            <label class="block font-bold text-xs text-slate-700 mb-1">{!! addslashes(__('statement.manual_code')) !!}</label>
                            <input type="text" name="manual_code" value="{{ $account->manual_code }}" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-xs text-slate-700 mb-1">{!! addslashes(__('statement.mobile_1')) !!}</label>
                            <input type="text" name="mobile_number_1" value="{{ $account->mobile_number_1 }}" dir="ltr" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm font-mono focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block font-bold text-xs text-slate-700 mb-1">{!! addslashes(__('statement.mobile_2')) !!}</label>
                            <input type="text" name="mobile_number_2" value="{{ $account->mobile_number_2 }}" dir="ltr" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm font-mono focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>
                
                {{-- Right Col --}}
                <div class="space-y-5">
                    
                    @php 
                        $accCurrencies = is_string($account->supported_currency_ids) ? json_decode($account->supported_currency_ids, true) : (is_array($account->supported_currency_ids) ? $account->supported_currency_ids : []);
                        if(!$accCurrencies) $accCurrencies = [];
                    @endphp

                    <div x-data="{ openMulti: false, selectedCurrencies: {{ json_encode($accCurrencies) }} }">
                        <label class="block font-bold text-xs text-slate-700 mb-1">{!! addslashes(__('statement.supported_currencies')) !!} *</label>
                        <div class="relative mt-1">
                            <button type="button" @click="openMulti = !openMulti" @click.outside="openMulti = false" class="w-full border border-slate-200 rounded-lg p-2.5 bg-white text-left flex items-center justify-between focus:ring-2 focus:ring-indigo-500 text-sm">
                                <span class="text-sm text-slate-700" x-text="selectedCurrencies.length > 0 ? selectedCurrencies.length + ' {!! addslashes(__('statement.selected')) !!}' : '{!! addslashes(__('statement.select_supported')) !!}'"></span>
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div x-show="openMulti" class="absolute z-10 mt-1 w-full bg-white shadow-xl rounded-xl border border-slate-100 max-h-48 overflow-y-auto py-1" style="display:none;">
                                @foreach($currencies as $c)
                                <label class="flex items-center px-4 py-2 hover:bg-slate-50 cursor-pointer">
                                    <input type="checkbox" value="{{ $c->id }}" x-model="selectedCurrencies" class="rounded text-indigo-600 w-4 h-4 border-slate-300 focus:ring-indigo-500 mr-2 ml-2">
                                    <span class="text-sm text-slate-700">{{ $c->currency_type }} ({{ $c->symbol }})</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        <template x-for="id in selectedCurrencies" :key="id">
                            <input type="hidden" name="supported_currency_ids[]" :value="id">
                        </template>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-xs text-slate-700 mb-1">{!! addslashes(__('statement.city')) !!}</label>
                            <select name="city_id" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm bg-white focus:ring-2 focus:ring-indigo-500">
                                <option value="">{!! addslashes(__('statement.none')) !!}</option>
                                @foreach($edit_cities as $c)
                                    <option value="{{ $c->id }}" {{ $account->city_id == $c->id ? 'selected' : '' }}>{{ $c->city_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-xs text-slate-700 mb-1">{!! addslashes(__('statement.neighborhood')) !!}</label>
                            <select name="neighborhood_id" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm bg-white focus:ring-2 focus:ring-indigo-500">
                                <option value="">{!! addslashes(__('statement.none')) !!}</option>
                                @foreach($edit_neighborhoods as $n)
                                    <option value="{{ $n->id }}" {{ $account->neighborhood_id == $n->id ? 'selected' : '' }}>{{ $n->neighborhood_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-xs text-slate-700 mb-1">{!! addslashes(__('statement.debt_limit')) !!}</label>
                            <input type="number" step="0.01" name="debt_limit" value="{{ $account->debt_limit }}" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block font-bold text-xs text-slate-700 mb-1">{!! addslashes(__('statement.due_time')) !!}</label>
                            <input type="number" name="debt_due_time" value="{{ $account->debt_due_time }}" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm text-center font-bold text-orange-500 focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block font-bold text-xs text-slate-700 mb-1">{!! addslashes(__('statement.profile_picture')) !!}</label>
                        <input type="file" name="profile_picture" class="w-full border border-slate-200 rounded-lg p-2 text-sm bg-slate-50 cursor-pointer">
                    </div>
                    
                    <input type="hidden" name="is_active" value="0">
                    <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border mt-2">
                        <input type="checkbox" name="is_active" value="1" {{ $account->is_active ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded border-slate-300">
                        <label class="font-bold text-xs text-slate-700">{!! addslashes(__('statement.active')) !!}</label>
                    </div>
                </div>
                
                <div class="col-span-1 md:col-span-2 flex justify-end gap-3 pt-4 border-t mt-4">
                    <button type="button" @click="isEditingAccount = false" class="px-6 py-2.5 text-slate-600 font-bold hover:bg-slate-100 rounded-lg transition-colors">{!! addslashes(__('statement.cancel')) !!}</button>
                    <button type="submit" class="px-8 py-2.5 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 shadow-sm transition-colors">{!! addslashes(__('statement.save')) !!}</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('statementManager', () => ({
                search: '{{ request('search') }}',
                accounts: {!! json_encode($search_list ?? []) !!},
                currencies: {!! json_encode($currencies ?? []) !!},
                showSidebar: (window.innerWidth >= 1024),
                
                // 🟢 DEFAULT TO THE CURRENT MONTH & TODAY 🟢
                datePreset: '',
                startDate: '{{ request('start_date') }}' || new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
                endDate: '{{ request('end_date') }}' || new Date().toISOString().split('T')[0],
                
                applyDatePreset() {
                    if(!this.datePreset) return;
                    const today = new Date();
                    let start = new Date(); let end = new Date();
                    if (this.datePreset === 'yesterday') { start.setDate(today.getDate() - 1); end.setDate(today.getDate() - 1); } 
                    else if (this.datePreset === 'week') { start.setDate(today.getDate() - 7); } 
                    else if (this.datePreset === 'month') { start.setMonth(today.getMonth() - 1); } 
                    else if (this.datePreset === 'year') { start.setFullYear(today.getFullYear() - 1); }
                    this.startDate = start.toISOString().split('T')[0];
                    this.endDate = end.toISOString().split('T')[0];
                    this.$nextTick(() => { this.$refs.filterForm.submit(); });
                },

                get filteredAccounts() {
                    if (this.search === '') return [];
                    const q = this.search.toLowerCase();
                    return this.accounts.filter(a => { return (a.name || '').toLowerCase().includes(q) || String(a.code).toLowerCase().includes(q); }).slice(0, 15);
                },

                raw_data: {!! json_encode(collect($transactions->items() ?? [])->map(function($trx) {
                    return [
                        'id' => $trx->id,
                        'statement_id' => $trx->statement_id,
                        'timestamp' => $trx->created_at ? $trx->created_at->timestamp : 0,
                        'created_date' => $trx->created_at ? $trx->created_at->format('Y/m/d') : '-',
                        'created_time' => $trx->created_at ? $trx->created_at->format('h:i A') : '-',
                        'user_name' => $trx->user->name ?? $trx->creator->name ?? 'ADMIN',
                        'bill_type' => $trx->type,
                        'target_currency' => $trx->targetCurrency->currency_type ?? $trx->currency->currency_type ?? 'USD',
                        'base_currency' => $trx->currency->currency_type ?? 'USD',
                        'amount' => floatval($trx->amount), 
                        'discount' => floatval($trx->discount),
                        'exchange_rate' => floatval($trx->exchange_rate ?? 1),
                        'total' => floatval($trx->total ?? 0), 
                        'note' => $trx->note ?? '',
                        'account_id' => $trx->account_id,
                        'cashbox_id' => $trx->cashbox_id,
                        'currency_id' => $trx->currency_id,
                        'target_currency_id' => $trx->target_currency_id,
                        'manual_date' => $trx->manual_date ? (is_string($trx->manual_date) ? substr($trx->manual_date, 0, 10) : $trx->manual_date->format('Y-m-d')) : now()->format('Y-m-d'),
                        'giver_name' => $trx->giver_name ?? '',
                        'giver_mobile' => $trx->giver_mobile ?? '',
                        'receiver_name' => $trx->receiver_name ?? '',
                        'receiver_mobile' => $trx->receiver_mobile ?? ''
                    ];
                })->values()) !!},
                
                openFilter: null, draggingIndex: null, filters: { target_currency: '' },
                
                columnsGeneral: [
                    { field: 'row_index', label: "{!! addslashes(__('statement.row')) !!}", visible: true, width: 40, searchable: false },
                    { field: 'created_time', label: "{!! addslashes(__('statement.time')) !!}", visible: true, width: 70, searchable: true },
                    { field: 'created_date', label: "{!! addslashes(__('statement.date')) !!}", visible: true, width: 80, searchable: true },
                    { field: 'user_name', label: "{!! addslashes(__('statement.user')) !!}", visible: true, width: 100, searchable: true },
                    { field: 'id', label: "{!! addslashes(__('statement.invoice_number')) !!}", visible: true, width: 80, searchable: true },
                    { field: 'bill_type', label: "{!! addslashes(__('statement.invoice_type')) !!}", visible: true, width: 90, searchable: true },
                    { field: 'total_display', label: "{!! addslashes(__('statement.total_invoice')) !!}", visible: true, width: 90, searchable: true },
                    { field: 'discount_display', label: "{!! addslashes(__('statement.discount')) !!}", visible: true, width: 80, searchable: true },
                    { field: 'cash_display', label: "{!! addslashes(__('statement.cash')) !!}", visible: true, width: 90, searchable: true },
                    { field: 'base_currency', label: "{!! addslashes(__('statement.currency_type')) !!}", visible: true, width: 70, searchable: true },
                    { field: 'exchange_rate', label: "{!! addslashes(__('statement.exchange_rate')) !!}", visible: true, width: 80, searchable: true },
                    { field: 'target_currency', label: "{!! addslashes(__('statement.debt_account_type')) !!}", visible: true, width: 110, searchable: true },
                    { field: 'note', label: "{!! addslashes(__('statement.note')) !!}", visible: true, width: 150, searchable: true },
                    { field: 'actions', label: "{!! addslashes(__('statement.actions')) !!}", visible: true, width: 70, searchable: false },
                ],

                columnsLedger: [
                    { field: 'row_index', label: "{!! addslashes(__('statement.row')) !!}", visible: true, width: 40, searchable: false },
                    { field: 'created_time', label: "{!! addslashes(__('statement.time')) !!}", visible: true, width: 70, searchable: true },
                    { field: 'created_date', label: "{!! addslashes(__('statement.date')) !!}", visible: true, width: 80, searchable: true },
                    { field: 'user_name', label: "{!! addslashes(__('statement.user')) !!}", visible: true, width: 100, searchable: true },
                    { field: 'id', label: "{!! addslashes(__('statement.invoice_number')) !!}", visible: true, width: 80, searchable: true }, 
                    { field: 'bill_type', label: "{!! addslashes(__('statement.invoice_type')) !!}", visible: true, width: 90, searchable: true },
                    { field: 'statement_id', label: "{!! addslashes(__('statement.statement_manual_code')) !!}", visible: true, width: 120, searchable: true }, 
                    { field: 'exchange_rate', label: "{!! addslashes(__('statement.exchange_rate')) !!}", visible: true, width: 80, searchable: true },
                    { field: 'display_amount', label: "{!! addslashes(__('statement.amount')) !!}", visible: true, width: 90, searchable: true },
                    { field: 'base_currency', label: "{!! addslashes(__('statement.currency_type')) !!}", visible: true, width: 70, searchable: true },
                    { field: 'debit', label: "{!! addslashes(__('statement.debit')) !!}", visible: true, width: 90, searchable: true },
                    { field: 'credit', label: "{!! addslashes(__('statement.credit')) !!}", visible: true, width: 90, searchable: true },
                    { field: 'running_balance', label: "{!! addslashes(__('statement.balance')) !!}", visible: true, width: 100, searchable: true },
                    
                    {{-- 🟢 THE NEW EXPLANATION COLUMN 🟢 --}}
                    { field: 'row_explanation', label: "ڕوونکردنەوەی پسووڵە", visible: true, width: 140, searchable: true }, 
                    
                    { field: 'note', label: "{!! addslashes(__('statement.note')) !!}", visible: true, width: 150, searchable: true },
                    { field: 'actions', label: "{!! addslashes(__('statement.actions')) !!}", visible: true, width: 70, searchable: false },
                ],
                
                columns: [],

                initData() {
                    const savedGen = localStorage.getItem('stat_gen_v51');
                    if(savedGen) { this.columnsGeneral = JSON.parse(savedGen); }
                    
                    const savedLedg = localStorage.getItem('stat_ledg_v51');
                    if(savedLedg) { this.columnsLedger = JSON.parse(savedLedg); }
                    
                    this.columns = this.filters.target_currency === '' ? this.columnsGeneral : this.columnsLedger;
                    
                    this.$watch('filters.target_currency', (val) => {
                        this.columns = val === '' ? this.columnsGeneral : this.columnsLedger;
                    });
                },

                saveState() { 
                    if(this.filters.target_currency === '') { localStorage.setItem('stat_gen_v51', JSON.stringify(this.columns)); } 
                    else { localStorage.setItem('stat_ledg_v51', JSON.stringify(this.columns)); }
                },
                resetLayout() { localStorage.removeItem('stat_gen_v51'); localStorage.removeItem('stat_ledg_v51'); location.reload(); },

                dragStart(e, i) { this.draggingIndex = i; e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', i); },
                dragOver(e) { e.preventDefault(); e.dataTransfer.dropEffect = 'move'; },
                drop(e, targetIndex) { if (this.draggingIndex === null || this.draggingIndex === targetIndex) return; const element = this.columns.splice(this.draggingIndex, 1)[0]; this.columns.splice(targetIndex, 0, element); this.draggingIndex = null; this.saveState(); },
                initResize(e, col) { const startX = e.clientX; const startWidth = parseInt(col.width) || 100; const onMouseMove = (ev) => { col.width = Math.max(50, (document.dir === 'rtl' ? startWidth - (ev.clientX - startX) : startWidth + (ev.clientX - startX))); }; const onMouseUp = () => { window.removeEventListener('mousemove', onMouseMove); window.removeEventListener('mouseup', onMouseUp); this.saveState(); }; window.addEventListener('mousemove', onMouseMove); window.addEventListener('mouseup', onMouseUp); },

                getCurrencySymbol(curr) {
                    if(!curr) return '';
                    if(curr.includes('USD') || curr.includes('دۆلار')) return '$';
                    return curr; 
                },

                openAccountEdit() {
                    window.dispatchEvent(new CustomEvent('open-account-edit'));
                },

                editTransaction(trx) {
                    let type = String(trx.bill_type).toLowerCase();
                    let payload = { ...trx };
                    if(type === 'pay') { window.dispatchEvent(new CustomEvent('open-paying-modal', { detail: JSON.parse(JSON.stringify(payload)) })); } 
                    else if(type === 'receive') { window.dispatchEvent(new CustomEvent('open-receiving-modal', { detail: JSON.parse(JSON.stringify(payload)) })); } 
                    else { alert('This transaction type cannot be edited directly yet.'); }
                },

                getDeleteUrl(trx) {
                    let type = String(trx.bill_type).toLowerCase();
                    if(type === 'pay') return `/accountant/paying/${trx.id}`;
                    if(type === 'receive') return `/accountant/receiving/${trx.id}`;
                    return `#`;
                },

                convertToTarget(val, trx) {
                    if (!val) return 0;
                    let s_curr = trx.base_currency || '';
                    let t_curr = trx.target_currency || '';
                    if (s_curr === t_curr) return parseFloat(val);

                    let rate = parseFloat(trx.exchange_rate) || 1;
                    if (rate === 1) return parseFloat(val);

                    // Safely handle large rates
                    if (rate > 50) {
                        if (s_curr.includes('دینار') || s_curr.includes('IQD') || s_curr.includes('تومەن') || s_curr.includes('TOMAN')) { return parseFloat(val) / rate; }
                        if (t_curr.includes('دینار') || t_curr.includes('IQD') || t_curr.includes('تومەن') || t_curr.includes('TOMAN')) { return parseFloat(val) * rate; }
                        return parseFloat(val) / rate;
                    }

                    return parseFloat(val) * rate;
                },

                // 🟢 TRUE BALANCE CALCULATION (DYNAMIC WITH PROFIT/SPENDING)
                get trueBalances() {
                    let bals = {};
                    this.raw_data.forEach(t => bals[t.target_currency] = 0);
                    let sortedData = [...this.raw_data].sort((a, b) => a.timestamp - b.timestamp);
                    
                    sortedData.forEach(trx => {
                        let type = String(trx.bill_type).toLowerCase();
                        let t_amount = parseFloat(trx.amount) || 0;
                        let t_disc = parseFloat(trx.discount) || 0;

                        let target_cash = this.convertToTarget(t_amount, trx);
                        let target_disc = this.convertToTarget(t_disc, trx);

                        let impact = 0;
                        if (type === 'receive' || type === 'return') { 
                            impact = -(target_cash + target_disc); 
                        } 
                        // 🟢 PROFIT AND SPENDING ADDED HERE
                        else if (type === 'pay' || type === 'sale' || type === 'purchase' || type === 'profit' || type === 'spending') { 
                            impact = target_cash + target_disc;
                        } else {
                            impact = this.convertToTarget(parseFloat(trx.total) || 0, trx);
                        }

                        if (bals[trx.target_currency] !== undefined) bals[trx.target_currency] += impact;
                        else bals[trx.target_currency] = impact;
                    });
                    return bals;
                },

                // 🟢 LEDGER SPLIT ENGINE (OLDEST->NEWEST + SEPARATED NOTES)
               // 🟢 LEDGER SPLIT ENGINE (OLDEST->NEWEST + SEPARATED NOTES)
              // 🟢 LEDGER SPLIT ENGINE (OLDEST->NEWEST + SEPARATED NOTES)
                get processedTransactions() {
                    let isLedger = this.filters.target_currency !== '';
                    let rows = [];
                    let rowIndex = 1;
                    
                    let filteredData = this.raw_data.filter(trx => {
                        for (let key in this.filters) {
                            if (this.filters[key] !== '' && key !== 'target_currency') {
                                let val = String(trx[key] || '').toLowerCase();
                                let search = this.filters[key].toLowerCase();
                                if (!val.includes(search)) return false;
                             }
                        }
                        if (isLedger && trx.target_currency !== this.filters.target_currency) return false;
                        return true;
                     });

                    if (!isLedger) {
                        filteredData.sort((a, b) => a.timestamp - b.timestamp); // OLDEST TO NEWEST
                        
                        filteredData.forEach(trx => {
                            let type = String(trx.bill_type).toLowerCase();
                            let t_amount = parseFloat(trx.amount) || 0;
                            let t_disc = parseFloat(trx.discount) || 0;
                            
                            let display_total = 0;
                            let display_cash = t_amount;

                            // GENERAL MODE TOTAL: Amount + Discount for everything
                            if (type === 'pay' || type === 'sale' || type === 'purchase' || type === 'profit' || type === 'spending' || type === 'receive' || type === 'return') {
                                display_total = t_amount + t_disc;
                            } else {
                                display_total = parseFloat(trx.total) || 0;
                            }

                            rows.push({
                                ...trx,
                                row_id: trx.id + '_gen',
                                row_index: rowIndex++,
                                total_display: display_total, 
                                discount_display: t_disc,     
                                cash_display: display_cash,   
                                is_discount_row: false,
                                row_explanation: this.getBillType(type), 
                                display_note: this.translateNote(trx.note) // 🟢 TRANSLATION APPLIED HERE
                            });
                        });
                    } 
                    else {
                        filteredData.sort((a, b) => a.timestamp - b.timestamp); // Oldest top
                        let currentBalance = 0;

                        filteredData.forEach(trx => {
                            let type = String(trx.bill_type).toLowerCase();
                            let t_amount = parseFloat(trx.amount) || 0;
                            let t_disc = parseFloat(trx.discount) || 0;

                            let target_cash = this.convertToTarget(t_amount, trx);
                            let target_disc = this.convertToTarget(t_disc, trx);

                            // 1. CASH ROW
                            if (t_amount > 0 || (t_amount === 0 && t_disc === 0)) {
                                let debit = 0; let credit = 0;
                                if (type === 'receive' || type === 'return') { credit = target_cash; } 
                                else if (type === 'pay' || type === 'sale' || type === 'purchase' || type === 'profit' || type === 'spending') { debit = target_cash; } 
                                
                                currentBalance += (debit - credit);
                                rows.push({
                                    ...trx,
                                    row_id: trx.id + '_cash',
                                    row_index: rowIndex++,
                                    display_amount: t_amount,
                                    debit: debit,             
                                    credit: credit,           
                                    running_balance: currentBalance,
                                    row_explanation: this.getBillType(type) + " - {!! addslashes(__('statement.cash')) !!}", 
                                    display_note: this.translateNote(trx.note), // 🟢 TRANSLATION APPLIED HERE
                                    has_discount: t_disc > 0, 
                                    is_cash_row: true,
                                    is_discount_row: false
                                });
                            }

                            // 2. DISCOUNT ROW 
                            if (t_disc > 0) {
                                let debit = 0; let credit = 0;
                                
                                if (type === 'pay' || type === 'sale' || type === 'purchase' || type === 'profit' || type === 'spending') { 
                                    debit = target_disc;
                                }
                                else if (type === 'receive' || type === 'return') { 
                                    credit = target_disc; 
                                }

                                currentBalance += (debit - credit);
                                rows.push({
                                    ...trx,
                                    row_id: trx.id + '_disc',
                                    row_index: rowIndex++,
                                    display_amount: t_disc, 
                                    debit: debit,
                                    credit: credit,
                                    running_balance: currentBalance,
                                    row_explanation: this.getBillType(type) + " - {!! addslashes(__('statement.invoice_discount')) !!}", 
                                    display_note: this.translateNote(trx.note), // 🟢 TRANSLATION APPLIED HERE
                                    has_discount: true,
                                    is_cash_row: false,
                                    is_discount_row: true
                                });
                            }
                        });
                    }

                    return rows;
                },
                

                get filteredTransactions() { return this.processedTransactions; },

                get currentTotals() {
                    if (!this.filters.target_currency) return null;
                    let t = { total_debit: 0, total_credit: 0, balance: 0 };
                    this.filteredTransactions.forEach(row => {
                        t.total_debit += row.debit;
                        t.total_credit += row.credit;
                    });
                    t.balance = t.total_debit - t.total_credit;
                    return t;
                },

                formatMoney(amount) { 
                    if (!amount && amount !== 0) return '0';
                    return parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }); 
                },

               getBillType(type) {
                    if (!type) return '-';
                    let t = type.toLowerCase();
                    if (t === 'receive') return "{!! addslashes(__('statement.receive')) !!}";
                    if (t === 'pay') return "{!! addslashes(__('statement.pay')) !!}";
                    if (t === 'sale') return "{!! addslashes(__('statement.sale')) !!}";
                    if (t === 'purchase') return "{!! addslashes(__('statement.purchase')) !!}";
                    if (t === 'return') return "{!! addslashes(__('statement.return')) !!}";
                    if (t === 'profit') return "{!! addslashes(__('statement.profit')) !!}"; 
                    if (t === 'spending') return "{!! addslashes(__('statement.spending')) !!}"; 
                    return type;
                },

                // 🟢 INTERCEPTS SYSTEM NOTES AND TRANSLATES THEM
               // 🟢 BULLETPROOF TRANSLATION (Ignores capitals and extra spaces)
                translateNote(note) {
                    if (!note || String(note).trim() === '') return '-';
                    
                    // 1. Get the original note, and make a lowercase version for safe checking
                    let originalNote = String(note).trim();
                    let safeCheck = originalNote.toLowerCase(); 
                    
                    // 2. Safely check using the lowercase version
                    if (safeCheck === 'profit entry') {
                        return "{!! addslashes(__('statement.profit_entry')) !!}";
                    }
                    if (safeCheck.includes('profit entry from paying')) {
                        return "{!! addslashes(__('statement.profit_entry_from_paying')) !!}";
                    }
                    if (safeCheck === 'spending entry') {
                        return "{!! addslashes(__('statement.spending_entry')) !!}";
                    }
                    if (safeCheck.includes('spending entry from paying')) {
                        return "{!! addslashes(__('statement.spending_entry_from_paying')) !!}";
                    }
                    
                    // 3. If it doesn't match any system note, return exactly what the user typed
                    return originalNote; 
                }
           
                
            }));
        });
    </script>
</x-app-layout>