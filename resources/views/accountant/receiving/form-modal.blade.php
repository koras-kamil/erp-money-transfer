<div x-data="receivingForm()" @open-receiving-modal.window="openModal($event.detail)" x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-hidden" dir="rtl">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="closeModal()"></div>
    <div class="flex h-screen w-full items-center justify-center p-2">
        <div class="relative w-full max-w-2xl transform overflow-hidden rounded-xl bg-white shadow-2xl transition-all border border-slate-100 flex flex-col max-h-[95vh]">
            
            {{-- HEADER --}}
            <div class="bg-slate-50 px-4 py-2 flex justify-between items-center border-b border-slate-100 flex-shrink-0">
                <div class="flex items-center gap-2">
                    <div class="bg-indigo-600 text-white p-1 rounded shadow-sm">
                        <template x-if="isEditing"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></template>
                        <template x-if="!isEditing"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg></template>
                    </div>
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest">
                        <span x-text="isEditing ? '{{ __('Edit Receive') }} #' + editingId : '{{ __('accountant.new_receive') }}'"></span>
                    </h3>
                </div>
                <button type="button" @click="closeModal()" class="text-slate-400 hover:text-rose-500 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>

            {{-- FORM --}}
            <form :action="isEditing ? '/accountant/receiving/' + editingId : '{{ route('accountant.receiving.store') }}'" method="POST" class="p-4 overflow-y-auto custom-scrollbar flex-1 bg-white">
                @csrf
                <input type="hidden" name="_method" :value="isEditing ? 'PUT' : 'POST'">
                <input type="hidden" name="account_id" :value="selectedAccount ? selectedAccount.id : ''">
                <input type="hidden" name="type" value="receive">
                <input type="hidden" name="target_currency_id" :value="target_currency_id">
                <input type="hidden" name="profit_account_id" :value="profitAccount ? profitAccount.id : ''">
                <input type="hidden" name="spending_account_id" :value="spendingAccount ? spendingAccount.id : ''">
                
                {{-- 1. USER SECTION --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 mb-4 bg-white">
                    <div class="col-span-1 md:col-span-8 relative">
                        <div class="relative w-full h-14 mb-2" @click.away="searchOpen = false">
                            <div x-show="!selectedAccount" class="w-full h-full">
                                <input type="text" x-model="searchQuery" @click="searchOpen = true" @focus="searchOpen = true" @input="searchOpen = true" placeholder="{{ __('accountant.search_users') }}" class="w-full h-full border border-slate-300 rounded-lg px-4 text-sm font-bold focus:ring-0 placeholder:text-slate-400 text-center bg-white">
                                <svg class="absolute top-1/2 -translate-y-1/2 right-4 w-5 h-5 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>

                            <div x-show="selectedAccount" class="w-full h-full border border-indigo-200 rounded-lg bg-indigo-50/50 flex items-center justify-between px-3 shadow-sm relative overflow-hidden" @click="searchOpen = !searchOpen">
                                <div class="absolute inset-0 bg-white/50 z-0"></div>
                                <div class="flex items-center gap-3 z-10"><img :src="selectedAccount?.avatar || 'https://ui-avatars.com/api/?name=' + selectedAccount?.name" class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm"></div>
                                <div class="flex-1 text-center z-10"><span class="text-base font-black text-slate-800 tracking-tight" x-text="selectedAccount?.name"></span></div>
                                <button type="button" @click.stop="clearSelection()" class="text-rose-500 hover:bg-rose-100 p-1.5 rounded-full transition z-10 bg-white border border-rose-100 shadow-sm"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                            </div>

                            <div x-show="searchOpen" class="absolute top-full left-0 w-full bg-white border border-slate-200 shadow-xl max-h-60 overflow-y-auto z-50 rounded-b-md custom-scrollbar mt-1">
                                <template x-for="acc in filteredAccounts" :key="acc.id">
                                    <div @click="selectAccount(acc)" class="px-3 py-2 hover:bg-indigo-50 cursor-pointer text-sm font-bold border-b border-slate-50 last:border-0 flex items-center justify-between text-right gap-3">
                                        <div class="flex items-center gap-3 overflow-hidden">
                                            <img :src="acc.avatar || 'https://ui-avatars.com/api/?name=' + acc.name" class="w-8 h-8 rounded-full object-cover border border-slate-200 flex-shrink-0">
                                            <div class="flex flex-col truncate"><span x-text="acc.name" class="truncate text-slate-700"></span></div>
                                        </div>
                                        <span class="text-slate-400 font-mono text-xs flex-shrink-0 bg-slate-50 px-1.5 py-0.5 rounded" x-text="acc.code"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-lg p-1.5 h-10 shadow-sm" x-show="selectedAccount">
                            <div class="relative flex-shrink-0">
                                <button type="button" @click.stop="showUserConfig = !showUserConfig" class="w-7 h-7 flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:bg-white rounded-md transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></button>
                                <div x-show="showUserConfig" @click.away="showUserConfig = false" class="absolute top-full right-0 mt-1 w-40 bg-white border border-slate-200 rounded-lg shadow-xl z-50 p-2 text-xs text-right" style="display: none;">
                                    <div class="font-bold text-slate-400 mb-1 px-1 uppercase text-[10px]">{{ __('Toggle Info') }}</div>
                                    <label class="flex items-center gap-2 p-1.5 hover:bg-slate-50 rounded cursor-pointer"><input type="checkbox" x-model="userInfoVisible.code" class="rounded text-indigo-600 w-3.5 h-3.5"><span class="text-slate-600">Code</span></label>
                                    <label class="flex items-center gap-2 p-1.5 hover:bg-slate-50 rounded cursor-pointer"><input type="checkbox" x-model="userInfoVisible.city" class="rounded text-indigo-600 w-3.5 h-3.5"><span class="text-slate-600">City</span></label>
                                </div>
                            </div>
                            <div class="w-px h-5 bg-slate-300"></div>
                            <div class="flex-1 flex items-center gap-3 overflow-x-auto custom-scrollbar px-1 text-xs font-medium text-slate-600 whitespace-nowrap">
                                <div x-show="userInfoVisible.code" class="flex items-center gap-1 bg-white border border-slate-200 px-1.5 py-0.5 rounded shadow-sm text-indigo-600 font-mono font-bold" x-text="selectedAccount?.code"></div>
                                <div x-show="userInfoVisible.city && selectedAccount?.city_name" class="flex items-center gap-1"><span class="text-slate-400">City:</span> <span x-text="selectedAccount?.city_name"></span></div>
                            </div>
                        </div>
                    </div>

                    {{-- 🟢 SUPPORTED CURRENCIES & LIVE BALANCE --}}
                    <div class="col-span-1 md:col-span-4 p-3 bg-white rounded-xl border border-slate-200 shadow-sm flex flex-col">
                        <div class="text-[12px] font-bold text-slate-400 text-center mb-3">{{ __('accountant.supported_currencies') }}</div>
                        <div class="flex flex-col gap-2 w-full">
                            <template x-for="curr in availableCurrencies" :key="curr.id">
                                <div @click="target_currency_id = curr.id; updateRate()" 
                                      class="cursor-pointer w-full px-4 py-2.5 rounded-lg text-sm font-bold border transition-all flex items-center justify-between"
                                      :class="target_currency_id == curr.id ? 'bg-[#4f46e5] text-white border-[#4f46e5] shadow-md' : 'bg-white text-slate-700 border-slate-200 hover:border-indigo-300'">
                                    
                                    {{-- 🟢 BALANCES LOGIC FROM YOUR PREVIOUS CODE --}}
                                    <span class="font-mono text-sm opacity-90" dir="ltr" x-text="formatDisplayMoney(selectedAccount?.balances?.[curr.id])">0</span>
                                    <span x-text="curr.currency_type" class="text-[14px]"></span>
                                </div>
                            </template>
                            <span x-show="availableCurrencies.length === 0" class="text-xs text-slate-400 italic text-center mt-2">{{ __('accountant.no_currency') }}</span>
                        </div>
                    </div>
                </div>

                {{-- 2. MAIN TRANSACTION --}}
                <div class="border border-slate-300 mb-4 bg-white rounded-md overflow-hidden">
                    <div class="grid grid-cols-12 border-b border-slate-200">
                        <div class="col-span-12 md:col-span-6 border-b md:border-b-0 md:border-r border-slate-200 p-1 flex items-center bg-slate-50/50"><label class="w-24 text-xs font-bold text-slate-500 uppercase text-center">{{ __('accountant.type_money') }}</label><select name="currency_id" x-model="form.currency_id" @change="setCurrency($event.target.value)" class="flex-1 h-9 text-base font-bold border-0 bg-transparent focus:ring-0 text-center cursor-pointer"><template x-for="curr in currencies" :key="curr.id"><option :value="curr.id" x-text="curr.currency_type"></option></template></select></div>
                        <div class="col-span-12 md:col-span-6 p-1 flex items-center bg-slate-50/50"><label class="w-24 text-xs font-bold text-slate-500 uppercase text-center">{{ __('accountant.price_usd') }}</label><input type="text" name="exchange_rate" x-model="form.rate" @input="formatRateInput(); calculateTotal()" class="flex-1 h-9 text-base font-bold border-0 bg-transparent focus:ring-0 text-center placeholder-slate-300 format-number"></div>
                    </div>
                    <div class="grid grid-cols-12 border-b border-slate-200">
                        <div class="col-span-12 p-1 flex items-center"><label class="w-28 text-xs font-bold text-slate-600 uppercase text-center">{{ __('accountant.amount_pay') }}</label><input type="text" name="amount" x-model="form.amount" @input="formatNumber('amount'); calculateTotal()" class="flex-1 h-10 text-lg font-black border-0 bg-white focus:ring-0 text-center text-slate-800 format-number" placeholder="0"></div>
                    </div>
                    <div class="grid grid-cols-12 border-b border-slate-200">
                        <div class="col-span-12 p-1 flex items-center"><label class="w-28 text-xs font-bold text-slate-500 uppercase text-center">{{ __('accountant.discount') }}</label><input type="text" name="discount" x-model="form.discount" @input="formatNumber('discount'); calculateTotal()" class="flex-1 h-9 text-base font-bold border-0 bg-white focus:ring-0 text-center text-rose-500 placeholder:text-rose-200 format-number" placeholder="0"></div>
                    </div>
                    <div class="grid grid-cols-12 border-b border-slate-200">
                        <div class="col-span-12 p-1 flex items-center"><label class="w-28 text-xs font-bold text-emerald-700 uppercase text-center">{{ __('accountant.cash_box') }}</label>
                            <select name="cashbox_id" x-model="form.cashbox_id" class="flex-1 h-9 text-base font-bold border-0 bg-white focus:ring-0 text-center text-emerald-800 cursor-pointer">
                                <option value="" disabled selected>{{ __('Select') }}</option>
                                <template x-for="box in mainFilteredCashboxes" :key="box.id"><option :value="box.id" x-text="box.name"></option></template>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-12">
                        <div class="col-span-12 p-1 flex items-center"><label class="w-28 text-xs font-bold text-indigo-700 uppercase text-center">{{ __('accountant.total_after_discount') }}</label>
                            <div class="flex-1 flex items-center justify-center gap-2 format-number">
                                <input type="text" name="total" x-model="form.total" @input="formatNumber('total'); recalcRateFromTotal(); if($event.target.value === '') resetTotal();" :readonly="isTotalLocked" class="w-32 md:w-56 h-10 text-lg font-black border-0 bg-white focus:ring-0 text-center text-indigo-800 format-number">
                                <span class="text-xs font-bold text-indigo-400" x-text="getCurrencyCode(target_currency_id)"></span>
                                <button type="button" @click="if(form.currency_id != target_currency_id) isTotalLocked = !isTotalLocked" :class="form.currency_id == target_currency_id ? 'text-slate-300 cursor-not-allowed' : 'text-indigo-400 hover:text-indigo-600'" :disabled="form.currency_id == target_currency_id">
                                    <svg x-show="isTotalLocked" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    <svg x-show="!isTotalLocked" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Parties --}}
                <div class="border border-slate-300 mb-4 bg-white rounded-md overflow-hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 border-b border-slate-300">
                        <div class="border-b md:border-b-0 md:border-r border-slate-300 p-1"><input type="text" name="giver_name" x-model="form.giver_name" class="w-full h-9 text-base border-0 bg-white text-center focus:ring-0 placeholder:text-slate-400" placeholder="{{ __('accountant.giver_name') }}"></div>
                        <div class="p-1"><input type="text" name="giver_mobile" x-model="form.giver_mobile" class="w-full h-9 text-base border-0 bg-white text-center focus:ring-0 placeholder:text-slate-400" placeholder="{{ __('accountant.giver_mobile') }}"></div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2">
                        <div class="border-b md:border-b-0 md:border-r border-slate-300 p-1"><input type="text" name="receiver_name" x-model="form.receiver_name" class="w-full h-9 text-base border-0 bg-white text-center focus:ring-0 placeholder:text-slate-400" placeholder="{{ __('accountant.receiver_name') }}"></div>
                        <div class="p-1"><input type="text" name="receiver_mobile" x-model="form.receiver_mobile" class="w-full h-9 text-base border-0 bg-white text-center focus:ring-0 placeholder:text-slate-400" placeholder="{{ __('accountant.receiver_mobile') }}"></div>
                    </div>
                </div>

                {{-- NOTE & FOOTER --}}
                <div class="border border-slate-300 bg-white mb-4 p-2 rounded-md">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div class="relative order-last md:order-first">
                            <label class="absolute top-1 right-2 text-[9px] text-slate-400 font-bold uppercase">{{ __('accountant.manual_code') }}</label>
                            <input type="text" name="statement_id" class="w-full h-12 text-lg text-center rounded-md border border-slate-200 focus:border-indigo-300 focus:ring-0 bg-white">
                        </div>
                        <div class="relative order-first md:order-last">
                            <input type="date" onclick="this.showPicker()" x-model="form.manual_date" name="manual_date" class="w-full h-12 text-lg text-center rounded-md border border-slate-200 focus:border-indigo-300 focus:ring-0 bg-white text-slate-700 font-bold cursor-pointer">
                        </div>
                    </div>
                    <div class="mt-2"><input name="note" class="w-full h-10 text-base text-center rounded-md border border-slate-200 focus:border-indigo-300 focus:ring-0 placeholder:text-slate-300 placeholder:font-light" placeholder="{{ __('accountant.note') }}"></div>
                </div>

                <div class="grid grid-cols-4 gap-0 text-white text-xs font-bold text-center">
                    <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 py-3 transition-colors">{{ __('accountant.save') }}</button>
                    <button type="button" @click="closeModal()" class="bg-rose-400 hover:bg-rose-500 py-3 transition-colors">{{ __('accountant.close') }}</button>
                    <button type="button" class="bg-sky-500 hover:bg-sky-600 py-3 transition-colors">{{ __('accountant.hold') }}</button>
                    <button type="button" class="bg-indigo-500 hover:bg-indigo-600 py-3 transition-colors">{{ __('accountant.print_large') }}</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function receivingForm() {
            return {
                showModal: false,
                isEditing: false, 
                showUserConfig: false,
                editingId: null,
                
                accounts: @json($accounts ?? []), 
                cashboxes: @json($cashboxes ?? []),
                currencies: @json($currencies ?? []),

                searchQuery: '', selectedAccount: null, searchOpen: false,
                userInfoVisible: { code: true, city: true },
                isTotalLocked: true, target_currency_id: null,

                mainFilteredCashboxes: [],

                form: {
                    amount: '', discount: '', currency_id: '', cashbox_id: '', rate: 1, total: 0, 
                    manual_date: new Date().toISOString().split('T')[0], note: '', statement_id: '', 
                    giver_name: '', giver_mobile: '', receiver_name: '', receiver_mobile: ''
                },

                openModal(detail) {
                    this.showModal = true;
                    if (detail && detail.id) {
                        this.isEditing = true;
                        this.editingId = detail.id;
                        this.form = { ...this.form, ...detail }; 
                        this.form.rate = parseFloat(detail.exchange_rate);
                        if(detail.account_id) {
                            const acc = this.accounts.find(a => a.id == detail.account_id);
                            if(acc) {
                                this.selectAccount(acc);
                                this.target_currency_id = detail.target_currency_id || detail.currency_id;
                                this.updateRate();
                            }
                        }
                    } else {
                        this.isEditing = false;
                        this.form = { amount: '', discount: '', currency_id: '', cashbox_id: '', rate: 1, total: 0, manual_date: new Date().toISOString().split('T')[0] };
                        this.selectedAccount = null; this.searchQuery = '';
                        if (this.currencies.length > 0) this.setCurrency(this.currencies[0].id);
                    }
                },
                closeModal() { this.showModal = false; },

                get filteredAccounts() { 
                    if(this.searchQuery === '') return this.accounts.slice(0, 10);
                    const q = this.searchQuery.toLowerCase();
                    return this.accounts.filter(a => a.name.toLowerCase().includes(q) || String(a.code).toLowerCase().includes(q));
                },
                selectAccount(acc) { 
                    this.selectedAccount = acc;
                    this.searchQuery = acc.name; 
                    this.searchOpen = false;
                    
                    if (acc.default_currency_id) {
                        this.target_currency_id = acc.default_currency_id;
                    } else if (acc.supported_currencies && acc.supported_currencies.length > 0) {
                        this.target_currency_id = acc.supported_currencies[0];
                    } else {
                        this.target_currency_id = this.form.currency_id;
                    }
                    this.updateRate();
                },

                setCurrency(id) { 
                    this.form.currency_id = id;
                    this.updateMainCashboxes(); 
                    this.updateRate(); 
                },
                
                updateMainCashboxes() {
                    this.mainFilteredCashboxes = this.cashboxes.filter(b => b.currency_id == this.form.currency_id);
                    if(this.mainFilteredCashboxes.length > 0) this.form.cashbox_id = this.mainFilteredCashboxes[0].id;
                },

                get availableCurrencies() { 
                    if (!this.selectedAccount) return [];
                    let supported = this.selectedAccount.supported_currencies || [];
                    if (supported.length === 0) return []; 
                    return this.currencies.filter(c => supported.includes(c.id) || supported.includes(String(c.id)));
                },

                // 🟢 FIXED MATH CALCULATIONS
                parseNumber(val) { if (!val) return 0; return parseFloat(val.toString().replace(/,/g, '')) || 0; },
                
                updateRate() {
                    if (!this.target_currency_id) { this.form.rate = 1; this.calculateTotal(); return; }
                    
                    if (this.form.currency_id == this.target_currency_id) {
                        this.isTotalLocked = true;
                        this.form.rate = 1;
                        this.calculateTotal();
                        return;
                    }

                    if (!this.isTotalLocked) return;
                    const s = this.currencies.find(c => c.id == this.form.currency_id); 
                    const t = this.currencies.find(c => c.id == this.target_currency_id);
                    if (s && t) { 
                        let sRate = parseFloat(s.price_single || 1);
                        let tRate = parseFloat(t.price_single || 1);
                        if (sRate === 0) sRate = 1; if (tRate === 0) tRate = 1;
                        if (tRate >= sRate) { this.form.rate = parseFloat((tRate / sRate).toFixed(6)); } 
                        else { this.form.rate = parseFloat((sRate / tRate).toFixed(6)); } 
                        this.calculateTotal();
                    } 
                },
                
                calculateTotal() { 
                    if (!this.isTotalLocked) return;
                    const amt = this.parseNumber(this.form.amount); 
                    const discount = this.parseNumber(this.form.discount); 
                    const rate = this.parseNumber(this.form.rate);
                    if (!this.form.currency_id || !this.target_currency_id || amt === 0) { this.form.total = ''; return; }

                    if (this.form.currency_id == this.target_currency_id) {
                        this.form.total = (amt + discount).toLocaleString('en-US', { maximumFractionDigits: 2 });
                        return;
                    }

                    const s = this.currencies.find(c => c.id == this.form.currency_id);
                    const t = this.currencies.find(c => c.id == this.target_currency_id); 
                    
                    let sPrice = s ? parseFloat(s.price_single || 1) : 1;
                    let tPrice = t ? parseFloat(t.price_single || 1) : 1;

                    let converted = 0;
                    if (tPrice >= sPrice) { converted = amt * rate; } else { converted = (rate !== 0) ? (amt / rate) : 0; }
                    
                    this.form.total = (converted + discount).toLocaleString('en-US', { maximumFractionDigits: 2 });
                },
                
                recalcRateFromTotal() { 
                    if (this.isTotalLocked) return;
                    const total = this.parseNumber(this.form.total);
                    const amt = this.parseNumber(this.form.amount); 
                    const discount = this.parseNumber(this.form.discount); 
                    const effectiveTotal = total - discount;
                    if (amt === 0 || effectiveTotal <= 0) { this.form.rate = 1; return; } 
                    if (effectiveTotal > amt) { this.form.rate = parseFloat((effectiveTotal / amt).toFixed(6)); } 
                    else { this.form.rate = parseFloat((amt / effectiveTotal).toFixed(6)); } 
                },
                
                getCurrencyCode(id) { 
                    if(!id) return '';
                    const c = this.currencies.find(x => x.id == id); 
                    return c ? c.currency_type : '';
                },

                formatDisplayMoney(val) {
                    if (!val) return '0';
                    return parseFloat(val).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2});
                },

                formatNumber(field) { if(this.form[field]) this.form[field] = String(this.form[field]).replace(/[^0-9.,]/g, ''); },
                formatRateInput() { if(this.form.rate) this.form.rate = String(this.form.rate).replace(/[^0-9.,]/g, ''); },
                clearSelection() { this.selectedAccount = null; this.searchQuery = ''; }
            }
        }
    </script>
</div>