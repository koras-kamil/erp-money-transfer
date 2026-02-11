<x-app-layout>
    {{-- REQUIRED: Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>

    {{-- STYLES (Same as Index) --}}
    <style>
        [x-cloak] { display: none !important; }
        .header-search-input { width: 100%; height: 28px; background-color: #fff; border: 1px solid #e2e8f0; border-radius: 4px; padding-left: 20px; padding-right: 20px; font-size: 0.75rem; color: #334155; transition: all 0.15s; }
        [dir="rtl"] .header-search-input { padding-left: 8px; padding-right: 24px; } 
        .header-search-input:focus { border-color: #6366f1; outline: none; box-shadow: 0 0 0 1px rgba(99, 102, 241, 0.1); }
    </style>

    <div class="py-6" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-100">
                
                {{-- HEADER --}}
                <div class="bg-slate-50 px-4 py-3 flex justify-between items-center border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <div class="bg-indigo-600 text-white p-1.5 rounded-lg shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">{{ __('Edit Transaction') }} #{{ $transaction->id }}</h3>
                    </div>
                    <a href="{{ route('accountant.receiving.index') }}" class="text-slate-400 hover:text-rose-500 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                </div>

                {{-- FORM BODY --}}
                <form action="{{ route('accountant.receiving.update', $transaction->id) }}" method="POST" class="p-4 bg-white"
                      x-data="editManager()" x-init="init()">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="account_id" :value="selectedAccount ? selectedAccount.id : ''">
                    
                    {{-- 🟢 USER SECTION --}}
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-0 border border-slate-300 mb-4 bg-white">
                        
                        {{-- User Search & Info --}}
                        <div class="col-span-1 md:col-span-8 p-2 relative">
                            {{-- 🔍 SEARCH INPUT --}}
                            <div class="relative mb-2">
                                <div class="w-full mb-1 relative">
                                    <input type="text" x-model="searchQuery" 
                                           @click="searchOpen = true" 
                                           @focus="searchOpen = true" 
                                           placeholder="{{ __('accountant.search_users') }}" 
                                           class="w-full border border-slate-300 rounded-md p-1 text-sm font-bold focus:ring-0 placeholder:text-slate-400 text-center bg-white h-9 header-search-input">
                                    
                                    <svg x-show="!selectedAccount" class="absolute top-1/2 -translate-y-1/2 right-3 w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    
                                    <button type="button" x-show="selectedAccount" @click.stop="clearSelection()" class="absolute top-1/2 -translate-y-1/2 right-2 text-rose-500 hover:bg-rose-50 rounded-full p-0.5 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                    
                                    <div x-show="searchOpen" @click.away="searchOpen = false" class="absolute top-full left-0 w-full bg-white border border-slate-200 shadow-xl max-h-40 overflow-y-auto z-50 rounded-b-md">
                                        <template x-for="acc in filteredAccounts" :key="acc.id">
                                            <div @click="selectAccount(acc)" class="px-3 py-2 hover:bg-indigo-50 cursor-pointer text-sm font-bold border-b border-slate-50 last:border-0 flex justify-between"><span x-text="acc.name"></span><span class="text-slate-400" x-text="acc.code"></span></div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- 🟢 USER NAME & GEAR ICON --}}
                            <div class="grid gap-2 mb-1" :class="userInfoVisible.code ? 'grid-cols-2' : 'grid-cols-1'">
                                <div class="bg-slate-50 border border-slate-200 p-1 text-center min-h-[30px] flex items-center justify-center rounded-md relative group">
                                    {{-- ⚙️ GEAR ICON (Inside Name Box, Left Side) --}}
                                    <div class="absolute left-1 top-1/2 -translate-y-1/2 z-30">
                                        <button type="button" @click.stop="showUserConfig = !showUserConfig" class="text-slate-300 hover:text-indigo-600 transition p-1 rounded-full hover:bg-slate-200">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        </button>
                                        <div x-show="showUserConfig" @click.away="showUserConfig = false" class="absolute top-full left-0 mt-1 w-48 bg-white border border-slate-200 rounded-lg shadow-xl z-50 p-2 text-xs text-right" dir="rtl">
                                            <div class="font-bold text-slate-400 mb-1 px-1 uppercase text-[10px] tracking-wider">{{ __('accountant.toggle_columns') }}</div>
                                            <label class="flex items-center gap-2 p-1.5 hover:bg-slate-50 rounded cursor-pointer"><input type="checkbox" x-model="userInfoVisible.code" class="rounded text-indigo-600 w-3.5 h-3.5 border-slate-300"><span class="text-slate-600 font-bold">{{ __('accountant.manual_code') }}</span></label>
                                            <label class="flex items-center gap-2 p-1.5 hover:bg-slate-50 rounded cursor-pointer"><input type="checkbox" x-model="userInfoVisible.city" class="rounded text-indigo-600 w-3.5 h-3.5 border-slate-300"><span class="text-slate-600 font-bold">{{ __('accountant.city') }}</span></label>
                                            <label class="flex items-center gap-2 p-1.5 hover:bg-slate-50 rounded cursor-pointer"><input type="checkbox" x-model="userInfoVisible.neighborhood" class="rounded text-indigo-600 w-3.5 h-3.5 border-slate-300"><span class="text-slate-600 font-bold">{{ __('accountant.neighborhood') }}</span></label>
                                            <label class="flex items-center gap-2 p-1.5 hover:bg-slate-50 rounded cursor-pointer"><input type="checkbox" x-model="userInfoVisible.mobile" class="rounded text-indigo-600 w-3.5 h-3.5 border-slate-300"><span class="text-slate-600 font-bold">{{ __('accountant.giver_mobile') }}</span></label>
                                        </div>
                                    </div>
                                    <span class="text-sm font-bold text-slate-700" x-text="selectedAccount?.name || '{{ __('accountant.name_users') }}'"></span>
                                </div>
                                <div x-show="userInfoVisible.code" class="bg-slate-50 border border-slate-200 p-1 text-center min-h-[30px] flex items-center justify-center rounded-md"><span class="text-sm text-slate-500" x-text="selectedAccount?.code || '-'"></span></div>
                            </div>
                            
                            {{-- Extra Info --}}
                            <div class="bg-slate-50 border border-slate-200 p-1 text-center min-h-[30px] flex items-center justify-center gap-3 rounded-md" x-show="userInfoVisible.city || userInfoVisible.neighborhood || userInfoVisible.mobile">
                                <span x-show="userInfoVisible.city" class="text-sm text-slate-500" x-text="selectedAccount?.city_name || ''"></span>
                                <span x-show="userInfoVisible.neighborhood" class="text-sm text-slate-500" x-text="selectedAccount?.neighborhood_name || ''"></span>
                                <span x-show="userInfoVisible.mobile" class="text-sm text-slate-500 font-mono" x-text="selectedAccount?.mobile || ''"></span>
                            </div>
                        </div>

                        {{-- Supported Currencies --}}
                        <div class="col-span-1 md:col-span-4 p-2 border-t md:border-t-0 md:border-r border-slate-300">
                            <div class="text-xs font-bold text-center uppercase mb-1">{{ __('accountant.supported_currencies') }}</div>
                            <div class="flex flex-wrap gap-1 justify-center min-h-[60px] content-start">
                                <template x-for="curr in availableCurrencies" :key="curr.id">
                                    <span @click="target_currency_id = curr.id; updateRate()" class="cursor-pointer px-2 py-1 rounded-md text-sm font-bold border transition-all" :class="target_currency_id == curr.id ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white border-slate-300 text-slate-600 hover:bg-slate-50'">
                                        <span x-text="curr.currency_type"></span>
                                    </span>
                                </template>
                                <span x-show="availableCurrencies.length === 0" class="text-xs text-slate-400 italic mt-2">{{ __('accountant.no_currency') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- FINANCIALS --}}
                    <div class="border border-slate-300 mb-4 bg-white">
                        <div class="grid grid-cols-12 border-b border-slate-200">
                            <div class="col-span-12 md:col-span-6 border-b md:border-b-0 md:border-r border-slate-200 p-1 flex items-center"><label class="w-24 md:w-24 text-xs font-bold text-slate-500 uppercase text-center">{{ __('accountant.type_money') }}</label><select name="currency_id" x-model="form.currency_id" @change="setCurrency($event.target.value)" class="flex-1 h-9 text-base font-bold border border-slate-300 rounded-md bg-white focus:ring-0 text-center p-0 cursor-pointer"><template x-for="curr in currencies" :key="curr.id"><option :value="curr.id" x-text="curr.currency_type"></option></template></select></div>
                            <div class="col-span-12 md:col-span-6 p-1 flex items-center"><label class="w-24 md:w-24 text-xs font-bold text-slate-500 uppercase text-center">{{ __('accountant.price_usd') }}</label><input type="text" name="exchange_rate" x-model="form.rate" @input="formatRate($event); calculateTotal()" class="flex-1 h-9 text-base font-bold border border-slate-300 rounded-md bg-white focus:ring-0 text-center p-0 placeholder-slate-300"></div>
                        </div>
                        <div class="grid grid-cols-12 border-b border-slate-200">
                            <div class="col-span-12 p-1 flex items-center"><label class="w-28 md:w-40 text-xs font-bold text-slate-600 uppercase text-center">{{ __('accountant.amount_receive') }}</label><input type="text" name="amount" x-model="form.amount" @input="formatInput($event)" class="flex-1 h-10 text-lg font-black border border-slate-300 rounded-md bg-white focus:ring-0 text-center p-0 text-slate-800" placeholder="0.00"></div>
                        </div>
                        <div class="grid grid-cols-12 border-b border-slate-200">
                            <div class="col-span-12 p-1 flex items-center"><label class="w-28 md:w-40 text-xs font-bold text-slate-500 uppercase text-center">{{ __('accountant.discount') }}</label><input type="text" x-model="form.discount" name="discount" @input="formatInput($event)" class="flex-1 h-9 text-base font-bold border border-slate-300 rounded-md bg-white focus:ring-0 text-center p-0 text-rose-500" placeholder="0"></div>
                        </div>
                        <div class="grid grid-cols-12 border-b border-slate-200">
                            <div class="col-span-12 p-1 flex items-center"><label class="w-28 md:w-40 text-xs font-bold text-emerald-700 uppercase text-center">{{ __('accountant.cash_box') }}</label>
                                <select name="cashbox_id" x-model="form.cashbox_id" class="flex-1 h-9 text-base font-bold border border-slate-300 rounded-md bg-white focus:ring-0 text-center p-0 text-emerald-800 cursor-pointer">
                                    <option value="" disabled selected>{{ __('Select') }}</option>
                                    <template x-for="box in availableCashboxes" :key="box.id">
                                        <option :value="box.id" :selected="box.id == form.cashbox_id" x-text="box.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        {{-- 🟢 TOTAL ROW --}}
                        <div class="grid grid-cols-12">
                            <div class="col-span-12 p-1 flex items-center"><label class="w-28 md:w-40 text-xs font-bold text-indigo-700 uppercase text-center">{{ __('accountant.total_after_discount') }}</label>
                                <div class="flex-1 flex items-center justify-center gap-2">
                                    <input type="text" x-model="form.total" @input="formatInput($event); recalcRateFromTotal(); if($event.target.value === '') resetTotal();" :readonly="isTotalLocked" class="w-32 md:w-56 h-10 text-lg font-black border border-slate-300 rounded-md bg-white focus:ring-0 text-center p-0 text-indigo-800">
                                    <span class="text-xs font-bold text-indigo-400" x-text="getCurrencyCode(target_currency_id)"></span>
                                    <button type="button" @click="isTotalLocked = !isTotalLocked" class="text-indigo-400 hover:text-indigo-600" title="{{ __('Lock/Unlock') }}"><svg x-show="isTotalLocked" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg><svg x-show="!isTotalLocked" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg></button>
                                    <button type="button" @click="resetTotal()" class="text-emerald-500 hover:text-emerald-700" title="{{ __('Reset Total') }}"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- PARTIES --}}
                    <div class="border border-slate-300 mb-4 bg-white">
                        <div class="grid grid-cols-1 md:grid-cols-2 border-b border-slate-300">
                            <div class="border-b md:border-b-0 md:border-r border-slate-300 p-1"><input type="text" name="giver_name" x-model="form.giver_name" class="w-full h-9 text-base border border-slate-300 rounded-md px-1 bg-white text-center focus:ring-0 placeholder:text-slate-400" placeholder="{{ __('accountant.giver_name') }}"></div>
                            <div class="p-1"><input type="text" name="giver_mobile" x-model="form.giver_mobile" class="w-full h-9 text-base border border-slate-300 rounded-md px-1 bg-white text-center focus:ring-0 placeholder:text-slate-400" placeholder="{{ __('accountant.giver_mobile') }}"></div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2">
                            <div class="border-b md:border-b-0 md:border-r border-slate-300 p-1"><input type="text" name="receiver_name" x-model="form.receiver_name" class="w-full h-9 text-base border border-slate-300 rounded-md px-1 bg-white text-center focus:ring-0 placeholder:text-slate-400" placeholder="{{ __('accountant.receiver_name') }}"></div>
                            <div class="p-1"><input type="text" name="receiver_mobile" x-model="form.receiver_mobile" class="w-full h-9 text-base border border-slate-300 rounded-md px-1 bg-white text-center focus:ring-0 placeholder:text-slate-400" placeholder="{{ __('accountant.receiver_mobile') }}"></div>
                        </div>
                    </div>

                    {{-- PROFIT & SPENDING --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="border border-slate-300 bg-white">
                            <div class="grid grid-cols-12 border-b border-slate-300">
                                <div class="col-span-8 border-r border-slate-300 p-1"><input type="text" name="profit_note" class="w-full h-9 text-base border border-slate-300 rounded-md px-1 bg-white text-center focus:ring-0" placeholder="FastPay / Dynar"></div>
                                <div class="col-span-4 p-1"><input type="text" name="profit_amount" class="w-full h-9 text-base font-bold border border-slate-300 rounded-md px-1 bg-white text-center focus:ring-0" placeholder="0"></div>
                            </div>
                            <div class="bg-slate-50 p-1 text-center text-xs text-slate-500 font-bold border-t border-slate-300">{{ __('accountant.profit') }}</div>
                        </div>
                        <div class="border border-slate-300 bg-white">
                            <div class="grid grid-cols-12 border-b border-slate-300">
                                <div class="col-span-8 border-r border-slate-300 p-1"><input type="text" name="spending_note" class="w-full h-9 text-base border border-slate-300 rounded-md px-1 bg-white text-center focus:ring-0" placeholder="Note"></div>
                                <div class="col-span-4 p-1"><input type="text" name="spending_amount" class="w-full h-9 text-base font-bold border border-slate-300 rounded-md px-1 bg-white text-center focus:ring-0" placeholder="0"></div>
                            </div>
                            <div class="bg-slate-50 p-1 text-center text-xs text-slate-500 font-bold border-t border-slate-300">{{ __('accountant.spending') }}</div>
                        </div>
                    </div>

                    {{-- NOTE & MANUAL CODE --}}
                    <div class="border border-slate-300 bg-white mb-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 border-b border-slate-300">
                            <div class="border-b md:border-b-0 md:border-r border-slate-300 p-1 relative"><label class="absolute top-0 right-0 p-1 text-[9px] text-slate-400">{{ __('accountant.manual_code') }}</label><input type="text" name="statement_id" x-model="form.statement_id" class="w-full h-12 text-lg border border-slate-300 rounded-md bg-white text-center focus:ring-0"></div>
                            <div class="p-1 relative">
                                <input type="date" onclick="this.showPicker()" x-model="form.manual_date" name="manual_date" class="w-full h-12 text-lg border border-slate-300 rounded-md bg-white text-center focus:ring-0 text-slate-700 cursor-pointer font-bold">
                            </div>
                        </div>
                        <div class="p-1 bg-white"><input name="note" x-model="form.note" class="w-full h-10 text-base border border-slate-300 rounded-md px-1 bg-white text-center focus:ring-0 placeholder:text-slate-400" placeholder="{{ __('accountant.note') }}"></div>
                    </div>

                    {{-- FOOTER BUTTONS --}}
                    <div class="grid grid-cols-4 gap-0 text-white text-xs font-bold text-center">
                        <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 py-3 transition-colors">{{ __('accountant.update') }}</button>
                        <a href="{{ route('accountant.receiving.index') }}" class="bg-rose-400 hover:bg-rose-500 py-3 transition-colors flex items-center justify-center">{{ __('accountant.cancel') }}</a>
                        <button type="button" class="bg-sky-500 hover:bg-sky-600 py-3 transition-colors">{{ __('accountant.hold') }}</button>
                        <button type="button" class="bg-indigo-500 hover:bg-indigo-600 py-3 transition-colors">{{ __('accountant.print_large') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editManager() {
            return {
                cashboxes: @json($cashboxes ?? []),
                currencies: @json($currencies ?? []),
                accounts: @json($accounts ?? []),
                searchOpen: false, 
                searchQuery: '', 
                selectedAccount: null, 
                target_currency_id: null, 
                isTotalLocked: true, 
                showUserConfig: false,
                userInfoVisible: { code: true, city: true, neighborhood: true, mobile: true },
                
                // Initialize form with existing transaction data
                form: {
                    amount: '{{ $transaction->amount }}',
                    currency_id: '{{ $transaction->currency_id }}',
                    cashbox_id: '{{ $transaction->cashbox_id }}',
                    rate: '{{ $transaction->exchange_rate }}',
                    discount: '{{ $transaction->discount }}',
                    total: '{{ $transaction->total }}',
                    manual_date: '{{ \Carbon\Carbon::parse($transaction->manual_date)->format("Y-m-d") }}',
                    statement_id: '{{ $transaction->statement_id }}',
                    note: '{{ $transaction->note }}',
                    giver_name: '{{ $transaction->giver_name }}',
                    giver_mobile: '{{ $transaction->giver_mobile }}',
                    receiver_name: '{{ $transaction->receiver_name }}',
                    receiver_mobile: '{{ $transaction->receiver_mobile }}',
                },

                init() {
                    // Pre-select account
                    const accountId = {{ $transaction->account_id }};
                    this.selectedAccount = this.accounts.find(a => a.id == accountId);
                    if(this.selectedAccount) {
                        this.searchQuery = this.selectedAccount.name;
                    }

                    // Pre-select currency info
                    this.target_currency_id = {{ $transaction->currency_id }}; // Can improve if needed logic for conversion
                    
                    // Format Total on Load
                    this.formatTotalOnLoad();

                    // Watchers
                    this.$watch('form.amount', () => this.calculateTotal());
                    this.$watch('form.discount', () => this.calculateTotal());
                    this.$watch('form.currency_id', () => this.updateCashboxSelection());
                },
                
                get filteredAccounts() { 
                    if (this.searchQuery === '') return this.accounts.slice(0, 10); 
                    const q = this.searchQuery.toLowerCase(); 
                    return this.accounts.filter(acc => acc.name.toLowerCase().includes(q) || String(acc.code).toLowerCase().includes(q)); 
                },
                
                selectAccount(account) { 
                    this.selectedAccount = account; 
                    this.searchQuery = account.name; 
                    this.searchOpen = false; 
                },
                
                clearSelection() { 
                    this.selectedAccount = null; 
                    this.searchQuery = ''; 
                },
                
                get availableCurrencies() { 
                    if (!this.selectedAccount) return this.currencies; 
                    let supported = this.selectedAccount.supported_currencies || []; 
                    if (supported.length === 0) return this.currencies;
                    return this.currencies.filter(c => supported.includes(c.id) || supported.includes(String(c.id))); 
                },
                
                get availableCashboxes() { 
                    if (!this.form.currency_id) return []; 
                    return this.cashboxes.filter(box => box.currency_id == this.form.currency_id); 
                },

                updateCashboxSelection() { 
                    let validBoxes = this.availableCashboxes; 
                    if (!validBoxes.find(b => b.id == this.form.cashbox_id)) { 
                        this.form.cashbox_id = validBoxes.length > 0 ? validBoxes[0].id : ''; 
                    } 
                },
                
                parseNumber(val) { return (!val) ? 0 : parseFloat(val.toString().replace(/,/g, '')) || 0; },
                getCurrencyCode(id) { const c = this.currencies.find(x => x.id == id); return c ? c.currency_type : ''; },
                
                updateRate() { 
                    if (!this.isTotalLocked) return; 
                    // ... (Rate logic same as index) ...
                },
                
                calculateTotal() { 
                    if (!this.isTotalLocked) return; 
                    const amt = this.parseNumber(this.form.amount); 
                    const discount = this.parseNumber(this.form.discount); 
                    // Basic Calc for Edit (Improve if conversion logic needed)
                    let total = amt - discount;
                    let final = total.toFixed(2);
                    let parts = final.split('.'); 
                    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ','); 
                    this.form.total = parts.join('.'); 
                },
                
                recalcRateFromTotal() { 
                    // Reverse logic ...
                },
                
                resetTotal() { 
                    this.isTotalLocked = true; 
                    this.calculateTotal(); 
                },

                formatTotalOnLoad() {
                    let val = this.form.total.toString();
                    let parts = parseFloat(val).toFixed(2).split('.');
                    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                    this.form.total = parts.join('.');
                }
            }
        }
        function formatInput(e) { let value = e.target.value.replace(/[^0-9.]/g, ''); if (!value) return; let parts = value.split('.'); parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ','); e.target.value = parts.join('.'); }
        function formatRate(e) { e.target.value = e.target.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1'); }
    </script>
</x-app-layout>