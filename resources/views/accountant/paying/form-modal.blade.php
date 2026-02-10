<div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true" dir="rtl">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="closeModal()"></div>

    <div class="flex h-screen w-full items-center justify-center p-3">
        {{-- 🟢 COMPACT WIDTH: max-w-2xl --}}
        <div class="relative w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white shadow-2xl transition-all border border-slate-100 flex flex-col max-h-full">
            
            {{-- 🌸 HEADER --}}
            <div class="bg-gradient-to-l from-indigo-50 to-white px-4 py-2.5 flex justify-between items-center border-b border-indigo-50/50 flex-shrink-0">
                <div class="flex items-center gap-2">
                    <div class="bg-indigo-600 text-white p-1.5 rounded-lg shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="text-sm font-black text-slate-800 tracking-tight">{{ __('accountant.new_transaction') }}</h3>
                </div>
                <button type="button" @click="closeModal()" class="text-slate-400 hover:text-rose-500 p-1.5 rounded-full transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- 🌸 FORM BODY --}}
            <form action="{{ route('accountant.store') }}" method="POST"
                  class="p-3 overflow-y-auto custom-scrollbar flex-1"
                  x-data="{ 
                      cashboxes: {{ json_encode($cashboxes) }},
                      currencies: {{ json_encode($currencies) }},
                      accounts: {{ json_encode($accounts) }},

                      showCard: true, 
                      showMobile: true, showCity: true, showHood: true, showAddress: false, showDebt: true, showDebtValue: false,
                      searchOpen: false,
                      searchQuery: '',
                      selectedAccount: null,
                      target_currency_id: null,
                      isTotalLocked: true, 
                      
                      {{-- Search Logic --}}
                      get filteredAccounts() {
                          if (this.searchQuery === '') return this.accounts;
                          return this.accounts.filter(account => {
                              return account.name.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                                     account.code.toLowerCase().includes(this.searchQuery.toLowerCase());
                          });
                      },

                      selectAccount(account) {
                          this.selectedAccount = account;
                          this.searchQuery = '';
                          this.searchOpen = false;
                      },

                      clearSelection() {
                          this.selectedAccount = null;
                          this.searchQuery = '';
                          this.target_currency_id = null;
                      },

                      get availableCurrencies() {
                          if (!this.selectedAccount) return [];
                          let supported = this.selectedAccount.supported_currencies || [];
                          if (supported.length === 0) return [];
                          return this.currencies.filter(c => supported.includes(c.id) || supported.includes(String(c.id)));
                      },
                      
                      get availableCashboxes() {
                          if (!this.form.currency_id) return [];
                          return this.cashboxes.filter(box => box.currency_id == this.form.currency_id);
                      },

                      updateCashboxSelection() {
                          let validBoxes = this.availableCashboxes;
                          this.form.cashbox_id = validBoxes.length === 1 ? validBoxes[0].id : '';
                      },

                      updateCurrencyForAccount(acc) {
                          this.target_currency_id = null;
                          if(acc && acc.supported_currencies && acc.supported_currencies.length > 0) {
                              let firstCurr = acc.supported_currencies[0];
                              this.target_currency_id = firstCurr;
                              this.form.currency_id = firstCurr; 
                              this.setCurrency(firstCurr);
                          }
                      },

                      parseNumber(val) { return (!val) ? 0 : parseFloat(val.toString().replace(/,/g, '')) || 0; },
                      getCurrencyCode(id) { const c = this.currencies.find(x => x.id == id); return c ? c.currency_type : ''; },
                      
                      setCurrency(id) { 
                          this.form.currency_id = id; 
                          this.updateCashboxSelection(); 
                          this.$nextTick(() => { this.updateRate(); }); 
                      },

                      // 🟢 1. Update Rate (Visual Logic)
                      updateRate() {
                          if (!this.isTotalLocked) return;
                          const s = this.currencies.find(c => c.id == this.form.currency_id);
                          const t = this.currencies.find(c => c.id == this.target_currency_id);
                          if (s && t) {
                              let sP = parseFloat(s.price_single || 1); 
                              let tP = parseFloat(t.price_single || 1);
                              this.form.rate = Math.max(sP, tP);
                              
                              if (sP !== 1 && tP !== 1) {
                                  let ratio = tP / sP;
                                  this.form.rate = parseFloat((ratio < 1 ? (1/ratio) : ratio).toFixed(6));
                              }
                              
                              this.calculateTotal();
                          }
                      },

                      // 🟢 2. UNIVERSAL CALCULATION
                      calculateTotal() {
                          if (!this.isTotalLocked) return;

                          const amt = this.parseNumber(this.form.amount);
                          const rate = this.parseNumber(this.form.rate);
                          const discount = this.parseNumber(this.form.discount);

                          if (!this.form.currency_id || !this.target_currency_id || amt === 0) {
                              this.form.total = 0;
                              return;
                          }
                          
                          const s = this.currencies.find(c => c.id == this.form.currency_id);
                          const t = this.currencies.find(c => c.id == this.target_currency_id);
                          
                          if (!s || !t || s.id == t.id) {
                              this.form.total = amt - discount;
                              return;
                          }

                          let sP = parseFloat(s.price_single);
                          let tP = parseFloat(t.price_single);

                          // Logic: Target > Source => Multiply, Source > Target => Divide
                          let converted = 0;
                          if (tP >= sP) {
                              converted = amt * rate;
                          } else {
                              converted = amt / rate;
                          }
                          
                          this.form.total = parseFloat((converted - discount).toFixed(2));
                      },

                      // 🟢 3. Recalculate Rate (Reverse Logic)
                      recalcRateFromTotal() {
                          const total = this.parseNumber(this.form.total);
                          const amt = this.parseNumber(this.form.amount);
                          const discount = this.parseNumber(this.form.discount);
                          const effectiveTotal = total + discount;

                          if (amt === 0 || effectiveTotal === 0) return;

                          const s = this.currencies.find(c => c.id == this.form.currency_id);
                          const t = this.currencies.find(c => c.id == this.target_currency_id);

                          if (s && t && s.id !== t.id) {
                              let sP = parseFloat(s.price_single), tP = parseFloat(t.price_single);
                              if (tP >= sP) {
                                  this.form.rate = parseFloat((effectiveTotal / amt).toFixed(6));
                              } else {
                                  this.form.rate = parseFloat((amt / effectiveTotal).toFixed(6));
                              }
                          }
                      }
                  }"
                  x-init="$watch('form.amount', () => calculateTotal()); $watch('form.discount', () => calculateTotal());">
                
                @csrf
                <input type="hidden" name="account_id" :value="selectedAccount ? selectedAccount.id : ''">
                {{-- 🟢 DEFAULT TO 'pay' --}}
                <input type="hidden" name="type" x-model="transactionType" value="pay">
                <input type="hidden" name="target_currency_id" :value="target_currency_id">
                <input type="hidden" name="city_id" :value="selectedAccount ? selectedAccount.city_id : ''">
                <input type="hidden" name="neighborhood_id" :value="selectedAccount ? selectedAccount.neighborhood_id : ''">

                {{-- 🟦 LAYOUT: 5 Columns --}}
                <div class="grid grid-cols-1 md:grid-cols-5 gap-3 h-full">

                    {{-- 🟢 LEFT (SPAN 2): Search & Info --}}
                    <div class="md:col-span-2 flex flex-col gap-2">
                        
                        {{-- 1️⃣ SEARCH --}}
                        <div class="bg-white p-2.5 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-center">
                            <div class="flex justify-between items-center mb-1">
                                <label class="text-[10px] font-bold text-slate-500">{{ __('accountant.select_account') }}</label>
                                <button type="button" @click="showCard = !showCard" class="text-[9px] text-indigo-500 hover:text-indigo-700 font-bold flex items-center gap-1 transition-colors">
                                    <span x-text="showCard ? '{{ __('accountant.hide') }}' : '{{ __('accountant.show') }}'"></span>
                                </button>
                            </div>
                            <div class="relative">
                                <div @click="searchOpen = true" class="flex items-center w-full bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 hover:border-indigo-500 cursor-pointer shadow-sm h-8 transition-all">
                                    <div x-show="selectedAccount" class="flex items-center gap-2 flex-1 overflow-hidden">
                                        <img :src="selectedAccount?.avatar || 'https://ui-avatars.com/api/?name='+(selectedAccount?.name||'U')" class="w-5 h-5 rounded-full border border-slate-100 shadow-sm">
                                        <div><div class="font-bold text-slate-800 text-xs leading-tight" x-text="selectedAccount?.name"></div><div class="text-[8px] text-slate-400 font-mono" x-text="selectedAccount?.code"></div></div>
                                    </div>
                                    <input type="text" x-model="searchQuery" placeholder="{{ __('accountant.search') }}" class="w-full border-none p-0 bg-transparent text-xs focus:ring-0 font-medium placeholder:text-slate-400 text-center" :class="selectedAccount ? 'hidden' : 'block'">
                                    <button type="button" x-show="selectedAccount" @click.stop="clearSelection()" class="hover:text-rose-500 bg-slate-200 p-0.5 rounded-full ml-auto"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                </div>
                                <div x-show="searchOpen" @click.away="searchOpen = false" class="absolute w-full mt-1 bg-white border border-slate-100 rounded-xl shadow-xl max-h-60 overflow-y-auto z-50 custom-scrollbar p-1">
                                    <template x-for="acc in filteredAccounts" :key="acc.id">
                                        <div @click="selectAccount(acc); updateCurrencyForAccount(acc)" class="flex items-center gap-2.5 px-2.5 py-1.5 hover:bg-indigo-50 rounded-lg cursor-pointer transition-colors mb-0.5">
                                            <img :src="acc.avatar || 'https://ui-avatars.com/api/?name='+acc.name" class="w-7 h-7 rounded-full bg-slate-100">
                                            <div><div class="text-sm font-bold text-slate-800" x-text="acc.name"></div><div class="text-[9px] text-slate-500" x-text="acc.code"></div></div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- 3️⃣ USER INFO --}}
                        <div x-show="showCard" x-transition class="bg-indigo-50/60 border border-indigo-100 rounded-xl p-2.5 shadow-sm relative flex-1">
                            <div class="absolute top-1.5 left-1.5 z-10" x-data="{ openSettings: false }">
                                <button type="button" @click="openSettings = !openSettings" class="text-slate-400 hover:text-indigo-600 p-0.5 rounded hover:bg-indigo-100"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></button>
                                <div x-show="openSettings" @click.outside="openSettings = false" class="absolute left-0 top-6 w-28 bg-white border border-slate-200 shadow-xl rounded-lg z-50 p-1.5 text-[9px]">
                                    <label class="block p-1 hover:bg-slate-50 cursor-pointer rounded"><input type="checkbox" x-model="showMobile" class="text-indigo-600 rounded mr-1"> {{ __('accountant.mobile') }}</label>
                                    <label class="block p-1 hover:bg-slate-50 cursor-pointer rounded"><input type="checkbox" x-model="showCity" class="text-indigo-600 rounded mr-1"> {{ __('accountant.city') }}</label>
                                    <label class="block p-1 hover:bg-slate-50 cursor-pointer rounded"><input type="checkbox" x-model="showAddress" class="text-indigo-600 rounded mr-1"> {{ __('accountant.address') }}</label>
                                    <label class="block p-1 hover:bg-slate-50 cursor-pointer rounded"><input type="checkbox" x-model="showDebt" class="text-indigo-600 rounded mr-1"> {{ __('accountant.debt') }}</label>
                                </div>
                            </div>

                            <div class="flex justify-between items-center mb-1.5 pl-5">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">{{ __('accountant.customer_info') }}</span>
                            </div>
                            
                            <div class="flex flex-col gap-1.5 text-[11px]">
                                <div x-show="showMobile" class="flex items-center gap-1.5 text-slate-700 bg-white/50 p-1.5 rounded"><span class="w-4 h-4 rounded bg-white flex items-center justify-center text-indigo-500 shadow-sm border border-indigo-50 shrink-0"><svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg></span><span class="font-mono font-bold" x-text="selectedAccount?.mobile || '-'"></span></div>
                                <div x-show="showCity" class="flex items-center gap-1.5 text-slate-700 bg-white/50 p-1.5 rounded"><span class="w-4 h-4 rounded bg-white flex items-center justify-center text-indigo-500 shadow-sm border border-indigo-50 shrink-0"><svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></span><span x-text="selectedAccount?.city_name || '-'"></span></div>
                                <div x-show="showAddress" class="flex items-center gap-1.5 text-slate-700 bg-white/50 p-1.5 rounded"><span class="w-4 h-4 rounded bg-white flex items-center justify-center text-indigo-500 shadow-sm border border-indigo-50 shrink-0"><svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg></span><span class="truncate" x-text="selectedAccount?.address_details || '-'"></span></div>
                            </div>

                            <div x-show="showDebt" class="mt-1.5 bg-white px-2 py-1.5 rounded border border-rose-100 shadow-sm flex items-center justify-between">
                                <span class="text-[8px] text-slate-400 font-bold uppercase">{{ __('accountant.debt') }}</span>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs font-black text-rose-500" :class="showDebtValue ? '' : 'blur-sm select-none'" x-text="'$' + (selectedAccount?.debt_limit || '0')"></span>
                                    <button type="button" @click="showDebtValue = !showDebtValue" class="text-slate-300 hover:text-indigo-500"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 🟢 RIGHT (SPAN 3): TARGET --}}
                    <div class="md:col-span-3 flex flex-col gap-2">

                        {{-- 6️⃣ TARGET ACCOUNT --}}
                        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden flex flex-col shadow-sm h-full">
                            <div class="bg-white border-b border-slate-100 px-2.5 py-1.5 flex justify-between items-center">
                                <span class="text-[9px] font-bold text-slate-600 uppercase">{{ __('accountant.target_account') }}</span>
                            </div>
                            <div class="overflow-y-auto max-h-[140px] custom-scrollbar p-1.5 space-y-1">
                                <div x-show="availableCurrencies.length === 0" class="text-center py-3 text-slate-400 text-[10px]">{{ __('accountant.no_currency') }}</div>
                                <template x-for="curr in availableCurrencies" :key="curr.id">
                                    <div @click="target_currency_id = curr.id; updateRate()" 
                                         class="grid grid-cols-12 gap-2 items-center px-2.5 py-2 rounded-lg cursor-pointer border transition-all"
                                         :class="target_currency_id == curr.id ? 'bg-indigo-50 border-indigo-500 ring-1 ring-indigo-200' : 'bg-white border-slate-100 hover:border-slate-300 hover:bg-slate-50'">
                                        <div class="col-span-5 text-[11px] font-bold text-slate-700" x-text="curr.currency_type"></div>
                                        <div class="col-span-5 text-right text-[10px] font-mono text-slate-500">0.00</div>
                                        <div class="col-span-2 flex justify-end">
                                            <div class="w-3.5 h-3.5 rounded-full border-2 flex items-center justify-center transition-all" 
                                                 :class="target_currency_id == curr.id ? 'border-indigo-600 bg-indigo-600' : 'border-slate-300 bg-white'">
                                                <svg x-show="target_currency_id == curr.id" class="w-2 h-2 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- 8️⃣ AMOUNT CARD --}}
                        <div class="bg-white p-2.5 rounded-xl border border-slate-200 shadow-sm">
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-2 mb-1.5">
                                <div class="col-span-6">
                                    <label class="block text-[9px] font-bold text-slate-500 mb-0.5 text-center">{{ __('accountant.currency') }}</label>
                                    <select name="currency_id" x-model="form.currency_id" @change="setCurrency($event.target.value)" class="w-full bg-white border-slate-300 rounded-lg py-1 px-2 text-[11px] font-bold text-slate-700 focus:ring-indigo-500 h-8 text-center">
                                        <template x-for="curr in currencies" :key="curr.id"><option :value="curr.id" x-text="curr.currency_type"></option></template>
                                    </select>
                                </div>
                                <div class="col-span-6">
                                    <label class="block text-[9px] font-bold text-orange-500 mb-0.5 text-center">{{ __('accountant.rate') }}</label>
                                    <input type="text" name="exchange_rate" x-model="form.rate" @input="formatRate($event); calculateTotal()" class="w-full bg-orange-50 border-orange-200 text-orange-600 rounded-lg py-1 px-2 text-[11px] font-bold focus:ring-orange-500 dir-ltr h-8 text-center">
                                </div>
                                <div class="col-span-12">
                                    <label class="block text-[9px] font-bold text-slate-500 mb-0.5 text-center">{{ __('accountant.amount_paid') }}</label>
                                    <input type="text" name="amount" x-model="form.amount" @input="formatInput($event)" class="w-full border-slate-300 rounded-lg py-1 px-2 text-sm font-bold text-slate-800 focus:ring-indigo-500 dir-ltr h-8 text-center" placeholder="0.00">
                                </div>
                                <div class="col-span-12">
                                    <label class="block text-[9px] font-bold text-slate-500 mb-0.5 text-center">{{ __('accountant.cashbox') }}</label>
                                    <select name="cashbox_id" x-model="form.cashbox_id" class="w-full bg-white border-slate-300 rounded-lg py-1 px-2 text-[10px] font-bold text-slate-700 focus:ring-indigo-500 h-8 text-center">
                                        <option value="" disabled selected>{{ __('Select') }}</option>
                                        <template x-for="box in availableCashboxes" :key="box.id"><option :value="box.id" x-text="box.name"></option></template>
                                    </select>
                                </div>
                                <div class="col-span-12">
                                    <label class="block text-[9px] font-bold text-slate-500 mb-0.5 text-center">{{ __('accountant.discount') }}</label>
                                    <input type="text" x-model="form.discount" name="discount" @input="formatInput($event)" class="w-full border-slate-300 rounded-lg py-1 px-2 text-[11px] font-bold text-rose-500 placeholder:text-slate-300 dir-ltr h-8 text-center" placeholder="0">
                                </div>
                            </div>

                            {{-- TOTAL FOOTER --}}
                            <div class="mt-1.5 pt-1.5 border-t border-slate-100 flex justify-between items-center bg-white p-1.5 rounded-lg">
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="isTotalLocked = !isTotalLocked; if(isTotalLocked) calculateTotal()" 
                                            class="p-1 rounded transition-colors"
                                            :class="isTotalLocked ? 'text-slate-400 hover:text-indigo-500' : 'text-indigo-600 bg-indigo-100 shadow-sm'">
                                        <svg x-show="isTotalLocked" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        <svg x-show="!isTotalLocked" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                                    </button>
                                    <span class="text-[9px] font-bold text-slate-500">{{ __('accountant.total') }}:</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="text" x-model="form.total" 
                                           @input="formatInput($event); recalcRateFromTotal()" 
                                           :readonly="isTotalLocked"
                                           class="w-24 text-right bg-transparent border-none p-0 text-sm font-black focus:ring-0 dir-ltr text-center"
                                           :class="isTotalLocked ? 'text-slate-800' : 'text-indigo-600 border-b border-indigo-300'">
                                    <span class="text-[9px] font-bold text-slate-400" x-text="getCurrencyCode(target_currency_id)"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 9️⃣ GIVER & RECEIVER --}}
                    <div class="md:col-span-5 bg-white border border-slate-200 rounded-xl p-2.5 shadow-sm">
                        <div class="space-y-1.5">
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" name="giver_name" class="border-slate-200 rounded-lg py-1 px-2 text-[11px] h-8 focus:border-indigo-500 text-center" placeholder="{{ __('accountant.giver_name') }}">
                                <input type="text" name="giver_mobile" class="border-slate-200 rounded-lg py-1 px-2 text-[11px] h-8 focus:border-indigo-500 text-center" placeholder="{{ __('accountant.giver_mobile') }}">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" name="receiver_name" class="border-slate-200 rounded-lg py-1 px-2 text-[11px] h-8 focus:border-indigo-500 text-center" placeholder="{{ __('accountant.receiver_name') }}">
                                <input type="text" name="receiver_mobile" class="border-slate-200 rounded-lg py-1 px-2 text-[11px] h-8 focus:border-indigo-500 text-center" placeholder="{{ __('accountant.receiver_mobile') }}">
                            </div>
                        </div>
                    </div>

                    {{-- 🔟 STATEMENT & NOTE --}}
                    <div class="md:col-span-5 grid grid-cols-12 gap-2 bg-white p-2.5 rounded-xl border border-slate-200 shadow-sm">
                        <div class="col-span-3">
                            <input type="text" name="statement_id" class="w-full border-slate-300 rounded-lg py-1 px-2 text-[10px] h-8 bg-white focus:bg-white text-center" placeholder="{{ __('Manual ID') }}">
                        </div>
                        <div class="col-span-3">
                            <input type="date" name="manual_date" class="w-full border-slate-300 rounded-lg py-1 px-2 text-[10px] h-8 bg-white focus:bg-white text-slate-600 text-center">
                        </div>
                        <div class="col-span-6">
                            <input name="note" class="w-full border-slate-300 rounded-lg py-1 px-2 text-[10px] h-8 bg-white focus:bg-white text-center" placeholder="{{ __('accountant.note_placeholder') }}">
                        </div>
                    </div>

                    {{-- BUTTONS --}}
                    <div class="md:col-span-5 flex justify-between pt-1.5 border-t border-slate-100">
                        <div class="bg-slate-100 p-0.5 rounded-lg flex gap-0.5">
                            <button type="button" @click="transactionType = 'receive'" class="px-5 py-1.5 rounded-md text-[11px] font-bold transition-all shadow-sm" :class="transactionType === 'receive' ? 'bg-emerald-500 text-white' : 'text-slate-500 hover:bg-white'">{{ __('accountant.receive') }}</button>
                            <button type="button" @click="transactionType = 'pay'" class="px-5 py-1.5 rounded-md text-[11px] font-bold transition-all shadow-sm" :class="transactionType === 'pay' ? 'bg-rose-500 text-white' : 'text-slate-500 hover:bg-white'">{{ __('accountant.pay') }}</button>
                        </div>
                        <div class="flex gap-1.5">
                            <button type="button" @click="closeModal()" class="px-4 py-1.5 bg-white border border-slate-300 text-slate-600 text-[11px] font-bold rounded-lg hover:bg-slate-50">{{ __('accountant.cancel') }}</button>
                            <button type="submit" :disabled="!selectedAccount || form.amount <= 0 || !target_currency_id" class="px-6 py-1.5 text-white text-[11px] font-bold rounded-lg shadow-md transition-all active:scale-95 disabled:opacity-50 disabled:bg-slate-300" :class="transactionType === 'receive' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'">{{ __('accountant.submit') }}</button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function formatInput(e) {
        let value = e.target.value.replace(/[^0-9.]/g, ''); 
        if (!value) return;
        let parts = value.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ','); 
        let cursorPosition = e.target.selectionStart;
        let originalLength = e.target.value.length;
        e.target.value = parts.join('.');
        let newLength = e.target.value.length;
        if (newLength > originalLength) { cursorPosition += (newLength - originalLength); }
        e.target.setSelectionRange(cursorPosition, cursorPosition);
    }

    function formatRate(e) {
        e.target.value = e.target.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
    }
</script>