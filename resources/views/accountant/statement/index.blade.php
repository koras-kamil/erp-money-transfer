<x-app-layout>
    {{-- AlpineJS --}}
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>

    <div x-data="{
            search: '{{ request('search') }}',
            accounts: {{ $search_list->toJson() ?? '[]' }},
            showSidebar: (window.innerWidth >= 1024),
            get filteredAccounts() {
                if (this.search === '') return [];
                const q = this.search.toLowerCase();
                return this.accounts.filter(a => {
                    const name = a.name ? a.name.toLowerCase() : '';
                    const code = a.code ? String(a.code).toLowerCase() : '';
                    const mobile = a.mobile_number_1 ? String(a.mobile_number_1) : '';
                    return name.includes(q) || code.includes(q) || mobile.includes(q);
                }).slice(0, 15);
            }
         }" 
         class="flex flex-col lg:flex-row gap-5 items-start w-full font-sans text-slate-800" dir="rtl">

        {{-- ==================================================================================== --}}
        {{-- 1. RIGHT-SIDE MENU (Search & Profile Card) --}}
        {{-- ==================================================================================== --}}
        <div :class="showSidebar ? 'w-full lg:w-[320px] opacity-100' : 'w-0 opacity-0 pointer-events-none'"
             class="flex-shrink-0 bg-white border border-slate-200 rounded-[1.5rem] shadow-sm transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] overflow-hidden sticky top-4 flex flex-col z-20"
             style="max-h: calc(100vh - 120px);">
             
             <div class="w-full lg:w-[320px] flex flex-col h-full">
                {{-- 🔍 SEARCH INPUT --}}
                <div class="p-3 border-b border-slate-100 bg-slate-50/80 sticky top-0 z-20 shrink-0">
                    <form method="GET" action="{{ route('accountant.statement.index') }}" class="relative group">
                        <input type="text" x-model="search" name="search"
                               placeholder="{{ __('accountant.search_users') }}" 
                               class="w-full h-11 px-10 rounded-xl border border-slate-300 bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm font-black text-center text-slate-700 placeholder-slate-400 transition-all shadow-sm"
                               autocomplete="off">
                        
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>

                        <button type="button" x-show="search.length > 0" @click="search = ''" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-rose-400 hover:text-rose-600 transition-colors" x-cloak>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </form>
                </div>

                {{-- 📄 CONTENT AREA (Results or Profile) --}}
                <div class="flex-1 overflow-y-auto custom-scrollbar bg-white">
                    
                    {{-- Search Results --}}
                    <div x-show="search.length > 0" x-cloak>
                        <div class="px-4 py-2.5 text-xs font-black text-slate-400 uppercase tracking-wider bg-slate-50 border-b border-slate-100 flex justify-between sticky top-0">
                            <span>{{ __('accountant.results') }}</span>
                            <span x-text="filteredAccounts.length" class="bg-slate-200 px-2 rounded text-slate-600"></span>
                        </div>
                        
                        <template x-for="acc in filteredAccounts" :key="acc.id">
                            <a :href="'/accountant/statement?account_id=' + acc.id" 
                               class="flex items-center gap-3 p-3.5 border-b border-slate-50 hover:bg-indigo-50 transition-colors group cursor-pointer">
                                <img :src="acc.profile_picture ? '/storage/' + acc.profile_picture : 'https://ui-avatars.com/api/?name=' + acc.name" 
                                     class="w-10 h-10 rounded-full border border-slate-200 object-cover group-hover:border-indigo-300">
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm font-bold text-slate-700 group-hover:text-indigo-700 truncate" x-text="acc.name"></span>
                                        <span class="text-xs font-mono text-slate-400 bg-slate-100 px-1.5 rounded" x-text="acc.code"></span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="text-slate-400" x-text="acc.mobile_number_1 || '-'"></span>
                                        <span class="text-slate-400" x-text="acc.city ? (acc.city.city_name || acc.city.name || '') : ''"></span>
                                    </div>
                                </div>
                            </a>
                        </template>

                        <div x-show="filteredAccounts.length === 0" class="p-8 text-center text-slate-400">
                            <p class="text-sm font-bold italic">{{ __('accountant.no_users_found') }}</p>
                        </div>
                    </div>

                    {{-- User Profile --}}
                    @if($account)
                        <div x-show="search.length === 0">
                            <div class="p-6 flex flex-col items-center border-b border-slate-200 bg-white relative">
                                <div class="flex items-center justify-between w-full mb-4">
                                    <div class="flex-1 text-center">
                                        <h2 class="text-lg font-black text-slate-800 leading-tight truncate px-1">{{ $account->name }}</h2>
                                        @if($account->secondary_name)
                                            <span class="text-xs font-bold text-slate-400 block truncate mt-1">{{ $account->secondary_name }}</span>
                                        @endif
                                    </div>
                                    <div class="relative group cursor-pointer ml-4">
                                        <img src="{{ $account->profile_picture ? asset('storage/'.$account->profile_picture) : 'https://ui-avatars.com/api/?name='.$account->name }}" 
                                             class="w-16 h-16 rounded-full object-cover border-4 border-slate-50 shadow-md ring-1 ring-slate-200">
                                        <a href="{{ route('accounts.edit', $account->id) }}" class="absolute -bottom-1 -right-1 bg-white text-slate-500 p-1.5 rounded-full shadow border border-slate-100 hover:text-white hover:bg-indigo-600 transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </a>
                                    </div>
                                </div>
                                <div class="w-full flex items-center justify-center gap-4 bg-slate-50 border border-slate-100 rounded-xl p-2.5 shadow-inner">
                                    <span class="px-2 py-0.5 rounded bg-white border border-slate-200 text-xs font-black font-mono text-indigo-600 shadow-sm">{{ $account->code }}</span>
                                    <div class="h-5 w-px bg-slate-300"></div>
                                    <span class="text-xs font-bold text-slate-500">{{ $account->city->city_name ?? $account->city->name ?? '-' }}</span>
                                    <div class="h-5 w-px bg-slate-300"></div>
                                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ $account->account_type ?? 'User' }}</span>
                                </div>
                            </div>

                            <div class="p-5 border-b border-slate-200 bg-slate-50/40">
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-xs text-slate-400 uppercase font-black tracking-widest">{{ __('accountant.qr_code') }}</span>
                                    <button class="text-slate-400 hover:text-emerald-600 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </button>
                                </div>
                                <div class="w-full bg-white border border-slate-200 rounded-xl p-3 flex items-center justify-between h-20 shadow-sm overflow-hidden group hover:border-indigo-300 transition-colors cursor-pointer">
                                    <div class="flex flex-col justify-center ml-3">
                                        <span class="text-xs text-slate-400 font-bold uppercase">{{ __('accountant.manual_code') }}</span>
                                        <span class="text-xl font-mono font-black text-slate-700">{{ $account->manual_code ?? '-' }}</span>
                                    </div>
                                    <svg class="w-14 h-14 text-slate-800" fill="currentColor" viewBox="0 0 448 512"><path d="M0 80C0 53.5 21.5 32 48 32h96c26.5 0 48 21.5 48 48v96c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V80zm64 32v64h64v-64H64zM0 336c0-26.5 21.5-48 48-48h96c26.5 0 48 21.5 48 48v96c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V336zm64 32v64h64v-64H64zM304 32h96c26.5 0 48 21.5 48 48v96c0 26.5-21.5 48-48 48h-96c-26.5 0-48-21.5-48-48V80c0-26.5 21.5-48 48-48zm48 64v64h64v-64h-64zM256 304c0-8.8 7.2-16 16-16h64c8.8 0 16 7.2 16 16s7.2 16 16 16h32c8.8 0 16-7.2 16-16s7.2-16 16-16s16 7.2 16 16v96c0 8.8-7.2 16-16 16H368c-8.8 0-16-7.2-16-16s-7.2-16-16-16s-16 7.2-16 16v64c0 8.8-7.2 16-16 16H272c-8.8 0-16-7.2-16-16V304zM368 480a16 16 0 1 1 0-32 16 16 0 1 1 0 32zm64 0a16 16 0 1 1 0-32 16 16 0 1 1 0 32z"/></svg>
                                </div>
                            </div>

                            <div class="p-5 border-b border-slate-200 bg-white">
                                <h3 class="text-xs font-black text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ __('accountant.financial_status') }}
                                </h3>
                                
                                {{-- 🔥 FILTER BUTTON: ALL CURRENCIES --}}
                                @if(count($supportedCurrencies ?? []) > 0)
                                <div class="mb-3">
                                    <a href="{{ request()->fullUrlWithQuery(['currency_id' => null]) }}" 
                                       class="flex items-center justify-center px-4 py-2 rounded-xl border transition-all text-xs font-bold {{ !request('currency_id') ? 'bg-slate-800 text-white border-slate-900 shadow-md' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                                        {{ __('accountant.all_currencies') ?? 'All Currencies' }}
                                    </a>
                                </div>
                                @endif

                                <div class="space-y-3">
                                    @forelse($supportedCurrencies ?? [] as $currency)
                                        @php
                                            $bal = $currency->current_balance ?? 0;
                                            $isDebt = $bal < 0;
                                            $isActive = request('currency_id') == $currency->id;
                                            $theme = $isActive 
                                                ? ['bg'=>'bg-indigo-600', 'text'=>'text-white', 'border'=>'border-indigo-700', 'label'=>'text-indigo-100'] 
                                                : ($isDebt 
                                                    ? ['bg'=>'bg-white hover:bg-rose-50', 'text'=>'text-rose-600', 'border'=>'border-rose-200', 'label'=>'text-slate-500'] 
                                                    : ['bg'=>'bg-white hover:bg-emerald-50', 'text'=>'text-emerald-600', 'border'=>'border-emerald-200', 'label'=>'text-slate-500']);
                                            $status = $isDebt ? __('accountant.debt') : __('accountant.loan');
                                        @endphp
                                        
                                        {{-- 🔥 CLICKABLE CURRENCY CARD --}}
                                        <a href="{{ request()->fullUrlWithQuery(['currency_id' => $currency->id]) }}" 
                                           class="flex items-center justify-between px-4 py-2.5 rounded-xl border shadow-sm transition-all {{ $theme['border'] }} {{ $theme['bg'] }} {{ $isActive ? 'ring-2 ring-indigo-300 ring-offset-1 scale-[1.02]' : '' }}">
                                            <div class="flex flex-col">
                                                <span class="text-[10px] font-black uppercase tracking-widest {{ $theme['label'] }} mb-0.5">{{ $currency->currency_type }}</span>
                                                <span class="text-[11px] font-bold opacity-80 uppercase {{ $isActive ? 'text-indigo-100' : 'text-slate-400' }}">{{ $status }}</span>
                                            </div>
                                            <span class="font-black text-base {{ $theme['text'] }}" dir="ltr">{{ $isDebt ? '-' : '+' }}{{ number_format(abs($bal), 0) }}</span>
                                        </a>
                                    @empty
                                        <div class="text-center p-3 rounded-lg bg-slate-50 border border-dashed border-slate-200"><span class="text-xs text-slate-400 font-bold italic">{{ __('accountant.no_currency') }}</span></div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @else
                        <div x-show="search.length === 0" class="flex flex-col items-center justify-center h-full text-slate-400 p-8 text-center">
                            <p class="text-xs font-bold text-slate-500 uppercase">{{ __('accountant.select_account_prompt') ?? 'تکایە هەژمارێک هەڵبژێرە' }}</p>
                        </div>
                    @endif
                </div>
             </div>
        </div>

        {{-- ==================================================================================== --}}
        {{-- 2. MAIN TABLE CONTENT CARD --}}
        {{-- ==================================================================================== --}}
        
        @if($account)
            <div class="flex-1 min-w-0 bg-white border border-slate-200 rounded-[1.5rem] shadow-sm flex flex-col overflow-hidden w-full">
                
                {{-- Toolbar --}}
                <div class="bg-slate-50/50 border-b border-slate-200 p-4 flex flex-col xl:flex-row justify-between items-center gap-4 shrink-0">
                    
                    {{-- Left Side: Title & Toggle --}}
                    <div class="flex items-center gap-3 w-full xl:w-auto">
                        <button @click="showSidebar = !showSidebar" 
                                class="flex items-center justify-center w-10 h-10 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-indigo-50 hover:text-indigo-600 transition-colors shadow-sm"
                                :title="showSidebar ? '{{ __('Close Search') }}' : '{{ __('Open Search') }}'">
                            <svg x-show="!showSidebar" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <svg x-show="showSidebar" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </button>

                        <div class="w-px h-6 bg-slate-200 mx-1 hidden xl:block"></div>

                        <h1 class="text-sm font-black text-slate-800 uppercase tracking-wide px-1">
                            {{ __('accountant.statement') }} - {{ $account->name }}
                        </h1>
                    </div>

                    {{-- Right Side: TYPE FILTERS, DATE FILTER, RESET & Print --}}
                    <div class="flex flex-wrap items-center gap-2.5 w-full xl:w-auto justify-end">
                        
                        {{-- 🔥 TRANSACTION TYPE FILTERS (Receive / Pay) --}}
                        <div class="flex items-center gap-2 border-l border-slate-200 pl-3 ml-1 rtl:border-r rtl:border-l-0 rtl:pr-3 rtl:pl-0 rtl:mr-1 rtl:ml-0">
                            <a href="{{ request()->fullUrlWithQuery(['type' => request('type') == 'receive' ? null : 'receive']) }}" 
                               class="flex items-center gap-1.5 px-4 py-2 rounded-xl border transition-all text-xs font-black shadow-sm {{ request('type') == 'receive' ? 'bg-emerald-600 text-white border-emerald-700 ring-2 ring-emerald-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' }}">
                                وەرگرتن
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                            </a>
                            
                            <a href="{{ request()->fullUrlWithQuery(['type' => request('type') == 'pay' ? null : 'pay']) }}" 
                               class="flex items-center gap-1.5 px-4 py-2 rounded-xl border transition-all text-xs font-black shadow-sm {{ request('type') == 'pay' ? 'bg-rose-600 text-white border-rose-700 ring-2 ring-rose-200' : 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100' }}">
                                پارەدان
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                            </a>
                        </div>

                        {{-- CLEAR FILTERS ICON (Red X) --}}
                        @if($account || request('start_date') || request('search') || request('currency_id') || request('type'))
                            <a href="{{ route('accountant.statement.index') }}?account_id={{ $account->id }}" title="{{ __('Clear All Filters') }}" 
                               class="w-10 h-10 flex items-center justify-center bg-rose-50 text-rose-500 border border-rose-100 rounded-xl hover:bg-rose-100 hover:text-rose-600 transition-colors shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif

                        {{-- DATE FILTER --}}
                        <form method="GET" action="{{ route('accountant.statement.index') }}" class="flex items-center gap-1.5 bg-white border border-slate-200 rounded-xl p-1 shadow-sm">
                            <input type="hidden" name="account_id" value="{{ request('account_id') }}">
                            @if(request('currency_id')) <input type="hidden" name="currency_id" value="{{ request('currency_id') }}"> @endif
                            @if(request('type')) <input type="hidden" name="type" value="{{ request('type') }}"> @endif
                            
                            <input type="date" name="start_date" value="{{ request('start_date') }}" class="h-8 w-[115px] text-xs font-mono font-bold border-0 bg-transparent focus:ring-0 text-slate-600 cursor-pointer text-center">
                            <span class="text-slate-300 text-xs">-</span>
                            <input type="date" name="end_date" value="{{ request('end_date') }}" class="h-8 w-[115px] text-xs font-mono font-bold border-0 bg-transparent focus:ring-0 text-slate-600 cursor-pointer text-center">
                            
                            <button type="submit" class="h-8 px-4 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-xs font-bold shadow-sm">
                                {{ __('Filter') }}
                            </button>
                        </form>

                        <div class="w-px h-6 bg-slate-200 mx-1 hidden md:block"></div>

                        {{-- 🟢 X-BTN FOR PRINT --}}
                        <x-btn type="print" onclick="window.print()" title="{{ __('Print') }}" />
                    </div>
                </div>

                {{-- 🔥 PHP CALCULATIONS FOR TABLE FOOTER --}}
                @php
                    $totals = [];
                    foreach($transactions as $trx) {
                        $curr = $trx->currency->currency_type ?? 'USD';
                        if(!isset($totals[$curr])) {
                            $totals[$curr] = ['total' => 0, 'discount' => 0, 'amount' => 0, 'debt' => 0];
                        }
                        $totals[$curr]['total'] += $trx->total;
                        $totals[$curr]['discount'] += $trx->discount;
                        $totals[$curr]['amount'] += $trx->amount;
                        $totals[$curr]['debt'] += ($trx->total - $trx->amount);
                    }
                @endphp

                {{-- Table Wrapper (This is the ONLY part that scrolls!) --}}
                <div class="flex-1 overflow-x-auto overflow-y-auto w-full custom-scrollbar relative">
                    <table class="w-full text-xs text-right whitespace-nowrap border-collapse">
                        
                        <thead class="bg-slate-50 text-slate-500 font-bold border-b-2 border-slate-300 uppercase tracking-wider sticky top-0 z-10 shadow-sm">
                            <tr>
                                <th class="px-3 py-3.5 text-center w-8 border-r border-slate-200">#</th>
                                
                                {{-- SEPARATED DATE & TIME COLUMNS --}}
                                <th class="px-3 py-3.5">{{ __('accountant.date') }}</th>
                                <th class="px-3 py-3.5">Time</th>
                                
                                <th class="px-3 py-3.5">{{ __('accountant.bill_no') }}</th>
                                <th class="px-3 py-3.5">{{ __('accountant.bill_type') }}</th>
                                
                                {{-- 🔥 PROMINENT CURRENCY COLUMN --}}
                                <th class="px-3 py-3.5 text-center">{{ __('accountant.currency') }}</th>
                                
                                <th class="px-3 py-3.5 text-center border-l border-r border-slate-200">{{ __('accountant.total') }}</th>
                                <th class="px-3 py-3.5 text-center text-rose-600">{{ __('accountant.discount') }}</th>
                                <th class="px-3 py-3.5 text-center text-emerald-600">{{ __('accountant.cash') }}</th>
                                <th class="px-3 py-3.5 text-center text-orange-600">{{ __('accountant.loan') }}</th>
                                <th class="px-3 py-3.5">{{ __('accountant.exchange_rate') ?? 'Rate' }}</th>
                                <th class="px-3 py-3.5 max-w-[200px]">{{ __('accountant.note') }}</th>
                                
                                {{-- ACTIONS --}}
                                <th class="px-3 py-3.5 text-center border-l border-slate-200">{{ __('accountant.actions') ?? 'Ops' }}</th>
                            </tr>
                        </thead>
                        
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($transactions as $index => $trx)
                            <tr class="hover:bg-indigo-50/40 transition-colors group">
                                <td class="px-3 py-2.5 text-center font-mono text-slate-400 border-r border-slate-50">{{ $index + 1 }}</td>
                                
                                {{-- DATE & TIME --}}
                                <td class="px-3 py-2.5 font-mono text-slate-600 font-bold">{{ $trx->created_at->format('Y/m/d') }}</td>
                                <td class="px-3 py-2.5 font-mono text-slate-400 text-[11px]">{{ $trx->created_at->format('h:i A') }}</td>

                                <td class="px-3 py-2.5 font-mono text-slate-500">{{ $trx->id ?? '-' }}</td>
                                
                                <td class="px-3 py-2.5">
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold border bg-slate-50 text-slate-600 border-slate-100">
                                            {{ $trx->type == 'receive' ? 'پاره‌ وەرگرتن' : ($trx->type == 'pay' ? 'پارەدان' : ucfirst($trx->type ?? 'General')) }}
                                        </span>
                                        @if($trx->is_debt)
                                            <span class="px-1.5 py-[2px] rounded text-[9px] font-black bg-rose-100 text-rose-700 border border-rose-200 shadow-sm">DEBT</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- 🔥 STYLED CURRENCY BADGE --}}
                                <td class="px-3 py-2.5 text-center">
                                    <span class="font-black text-[11px] uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-md border border-indigo-200 shadow-sm">
                                        {{ $trx->currency->currency_type ?? 'USD' }}
                                    </span>
                                </td>

                                <td class="px-3 py-2.5 text-center font-black text-slate-800 bg-slate-50/50 border-l border-r border-slate-100">{{ number_format($trx->total) }}</td>
                                
                                {{-- Hide discount from main row since it has its own row --}}
                                <td class="px-3 py-2.5 text-center font-bold text-slate-300">-</td>
                                
                                <td class="px-3 py-2.5 text-center font-bold text-emerald-600">{{ number_format($trx->amount ?? 0) }}</td>
                                <td class="px-3 py-2.5 text-center font-bold text-orange-500">{{ number_format($trx->total - $trx->amount) }}</td>
                                
                                <td class="px-3 py-2.5 font-mono text-slate-400 text-[11px]">
                                    {{ floatval($trx->exchange_rate ?? 1) == 1 ? '-' : rtrim(rtrim(number_format(floatval($trx->exchange_rate), 4, '.', ','), '0'), '.') }}
                                </td>
                                
                                <td class="px-3 py-2.5 text-slate-400 italic truncate max-w-[200px]">{{ Str::limit($trx->note, 30) }}</td>
                                
                                {{-- 🟢 X-BTN ROW ACTIONS --}}
                                <td class="px-3 py-2.5 text-center border-l border-slate-50">
                                    <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <x-btn type="edit" href="#" title="Edit" />
                                        <form action="#" method="POST" class="inline m-0 p-0">
                                            @csrf @method('DELETE')
                                            <x-btn type="delete" onclick="if(confirm('{{ __('messages.confirm_action' ?? 'Are you sure?') }}')) this.closest('form').submit();" title="Delete" />
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            {{-- 🔥 SECONDARY ROW: SPLIT DISCOUNT (Only displays if discount exists) --}}
                            @if($trx->discount > 0)
                            <tr class="bg-rose-50/40 hover:bg-rose-50/80 transition-colors group">
                                <td class="px-3 py-2.5 text-center font-black text-rose-300 border-r border-slate-50">↳</td>
                                
                                <td class="px-3 py-2.5 font-mono text-slate-500">{{ $trx->created_at->format('Y/m/d') }}</td>
                                <td class="px-3 py-2.5 font-mono text-slate-400 text-[11px]">{{ $trx->created_at->format('h:i A') }}</td>

                                <td class="px-3 py-2.5 font-mono text-slate-500">{{ $trx->id ?? '-' }}</td>
                                
                                <td class="px-3 py-2.5">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold border bg-rose-100 text-rose-600 border-rose-200 shadow-sm">
                                        {{ __('accountant.discount') }} - {{ $trx->type == 'receive' ? 'پاره‌ وەرگرتن' : ($trx->type == 'pay' ? 'پارەدان' : ucfirst($trx->type ?? 'General')) }}
                                    </span>
                                </td>

                                <td class="px-3 py-2.5 text-center">
                                    <span class="font-black text-[11px] uppercase tracking-wider text-rose-700 bg-rose-100 px-2.5 py-1 rounded-md border border-rose-200 shadow-sm">
                                        {{ $trx->currency->currency_type ?? 'USD' }}
                                    </span>
                                </td>

                                <td class="px-3 py-2.5 text-center text-slate-300 border-l border-r border-rose-50">-</td>
                                <td class="px-3 py-2.5 text-center font-black text-rose-600 bg-rose-100/50">{{ number_format($trx->discount) }}</td>
                                <td class="px-3 py-2.5 text-center text-slate-300">-</td>
                                <td class="px-3 py-2.5 text-center text-slate-300">-</td>
                                
                                <td class="px-3 py-2.5 text-center text-slate-300">-</td>
                                <td class="px-3 py-2.5 text-rose-400 italic font-bold truncate max-w-[200px]">{{ __('accountant.discount_applied') ?? 'داشکاندنی پسوڵە' }}</td>
                                
                                <td class="px-3 py-2.5 border-l border-slate-50"></td>
                            </tr>
                            @endif

                            @empty
                            <tr>
                                <td colspan="100%" class="text-center py-16 text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-10 h-10 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span class="text-sm font-bold">{{ __('accountant.no_data') }}</span>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        
                        {{-- 🔥 STICKY FOOTER ROW FOR GRAND TOTALS --}}
                        @if($transactions->count() > 0)
                            <tfoot class="bg-indigo-50 border-t-[3px] border-indigo-200 sticky bottom-0 z-20 shadow-[0_-4px_10px_-2px_rgba(0,0,0,0.1)]">
                                @foreach($totals as $curr => $t)
                                    <tr class="border-b border-indigo-100 last:border-0 hover:bg-indigo-100/50 transition-colors">
                                        
                                        <td colspan="5" class="px-3 py-3.5 text-left rtl:text-right font-black text-indigo-900 tracking-wide">
                                            {{ __('accountant.grand_total') ?? 'کۆی گشتی' }}
                                        </td>
                                        
                                        <td class="px-3 py-3.5 text-center border-l border-r border-indigo-100/50">
                                            <span class="bg-indigo-600 text-white px-3 py-1.5 rounded shadow-sm text-[11px] font-black uppercase tracking-wider">{{ $curr }}</span>
                                        </td>

                                        <td class="px-3 py-3.5 text-center text-slate-800 font-black text-sm bg-indigo-100/40 border-l border-indigo-100/50">{{ number_format($t['total']) }}</td>
                                        <td class="px-3 py-3.5 text-center text-rose-600 font-black text-sm border-l border-indigo-100/50">{{ $t['discount'] > 0 ? number_format($t['discount']) : '-' }}</td>
                                        <td class="px-3 py-3.5 text-center text-emerald-600 font-black text-sm border-l border-indigo-100/50">{{ number_format($t['amount']) }}</td>
                                        <td class="px-3 py-3.5 text-center text-orange-600 font-black text-sm border-l border-indigo-100/50">{{ number_format($t['debt']) }}</td>
                                        
                                        <td colspan="3" class="px-3 py-3.5 border-l border-indigo-100/50"></td>
                                    </tr>
                                @endforeach
                            </tfoot>
                        @endif
                        
                    </table>
                </div>

                {{-- Pagination --}}
                @if($transactions->hasPages())
                    <div class="bg-slate-50 border-t border-slate-200 p-3 shrink-0">
                        {{ $transactions->links() }}
                    </div>
                @endif

            </div>
        @else
            {{-- 🟢 EMPTY STATE (SHOWS UNTIL AN ACCOUNT IS SELECTED) --}}
            <div class="flex-1 min-w-0 bg-slate-50/50 border border-slate-200 rounded-[1.5rem] shadow-sm flex flex-col items-center justify-center overflow-hidden w-full p-8 text-center relative" style="min-height: 60vh;">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-50/30 to-transparent z-0"></div>
                <div class="relative z-10">
                    <svg class="w-24 h-24 text-slate-300 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <h2 class="text-2xl font-black text-slate-700 mb-3">{{ __('accountant.select_account_prompt') ?? 'تکایە هەژمارێک هەڵبژێرە' }}</h2>
                    <p class="text-slate-500 font-bold max-w-sm mx-auto">بۆ بینینی وردەکاری پسوڵەکان و ئەنجامدانی مامەڵەکان (پارەدان / وەرگرتن)، سەرەتا لە لیستی لای ڕاست هەژمارێک دیاری بکە.</p>
                </div>
            </div>
        @endif

    </div>
</x-app-layout>