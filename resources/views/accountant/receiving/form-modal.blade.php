{{-- 🟢 RECEIVING FORM MODAL --}}
<div x-data="receivingForm()" 
     @open-receiving-modal.window="openModal($event.detail)"
     x-show="showModal" 
     x-cloak 
     class="fixed inset-0 z-[100] overflow-hidden" dir="rtl">
    
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="closeModal()"></div>
    
    <div class="flex h-screen w-full items-center justify-center p-2">
        <div class="relative w-full max-w-2xl transform overflow-hidden rounded-xl bg-white shadow-2xl transition-all border border-slate-100 flex flex-col max-h-[95vh]">
            
            {{-- HEADER --}}
            <div class="bg-slate-50 px-4 py-2 flex justify-between items-center border-b border-slate-100 flex-shrink-0">
                <div class="flex items-center gap-2">
                    <div class="bg-emerald-600 text-white p-1 rounded shadow-sm">
                        <template x-if="isEditing">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </template>
                        <template x-if="!isEditing">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        </template>
                    </div>
                    
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest">
                        <span x-show="isEditing">{!! addslashes(__('accountant.edit_receive')) !!} #<span x-text="editingId"></span></span>
                        <span x-show="!isEditing">{!! addslashes(__('accountant.new_receive')) !!}</span>
                    </h3>
                </div>
                <button type="button" @click="closeModal()" class="text-slate-400 hover:text-rose-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- FORM --}}
            <form :action="isEditing ? '/accountant/receiving/' + editingId : '{{ route('accountant.receiving.store') }}'" 
                  method="POST" 
                  class="p-4 overflow-y-auto custom-scrollbar flex-1 bg-white">
                @csrf
                <input type="hidden" name="_method" :value="isEditing ? 'PUT' : 'POST'">
                <input type="hidden" name="account_id" :value="selectedAccount ? selectedAccount.id : ''">
                <input type="hidden" name="type" value="receive">
                <input type="hidden" name="target_currency_id" :value="target_currency_id">
                <input type="hidden" name="profit_account_id" :value="profitAccount ? profitAccount.id : ''">
                <input type="hidden" name="spending_account_id" :value="spendingAccount ? spendingAccount.id : ''">
                
                {{-- 🟢 1. USER SECTION --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-2 mb-4 bg-white">
                    <div class="col-span-1 md:col-span-8 relative flex flex-col gap-1">
             
                        {{-- 🟢 A. SEARCH BAR --}}
                        <div class="relative w-full h-16" @click.away="searchOpen = false">
                          
                            <div x-show="!selectedAccount" class="relative w-full h-full">
                                <input type="text" x-model="searchQuery" 
                                       @click="searchOpen = true" @focus="searchOpen = true" @input="searchOpen = true"
                                       placeholder="{!! addslashes(__('accountant.search_users')) !!}" 
                                       class="w-full h-full border border-slate-300 rounded-lg px-4 text-sm font-bold focus:ring-0 placeholder:text-slate-400 text-center bg-white pr-12 pl-12">
                  
                                <button type="button" x-show="searchQuery" @click.stop="clearSelection()" class="absolute top-1/2 -translate-y-1/2 left-3 text-rose-500 hover:bg-rose-50 rounded-full p-1 transition z-10">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                                <svg class="absolute top-1/2 -translate-y-1/2 right-4 w-5 h-5 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>

                            <div x-show="selectedAccount" class="w-full h-full border border-emerald-200 rounded-lg bg-emerald-50/50 flex items-center justify-between px-3 shadow-sm relative overflow-hidden" @click="searchOpen = !searchOpen">
                                <div class="absolute inset-0 bg-white/50 z-0"></div>
                                <button type="button" @click.stop="clearSelection()" class="absolute left-3 top-1/2 -translate-y-1/2 z-20 text-rose-500 hover:bg-rose-100 p-2 rounded-full transition bg-white border border-rose-100 shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                                <div class="flex-1 flex items-center justify-center z-10 px-10 text-center">
                                    <span class="text-xl font-black text-slate-800 tracking-tight" x-text="selectedAccount?.name"></span>
                                </div>
                                <div class="absolute right-3 top-1/2 -translate-y-1/2 z-10 flex items-center">
                                    <img :src="selectedAccount?.avatar || 'https://ui-avatars.com/api/?name=' + selectedAccount?.name" class="w-12 h-12 rounded-full object-cover border-2 border-white shadow-sm bg-white">
                                </div>
                            </div>

                            <div x-show="searchOpen" class="absolute top-full left-0 w-full bg-white border border-slate-200 shadow-xl max-h-60 overflow-y-auto z-50 rounded-b-md custom-scrollbar mt-1 z-50">
                                <template x-for="acc in filteredAccounts" :key="acc.id">
                                    <div @click="selectAccount(acc)" class="px-3 py-2.5 hover:bg-emerald-50 cursor-pointer text-sm font-bold border-b border-slate-50 last:border-0 flex items-center justify-between text-right gap-3">
                                        <div class="flex items-center gap-3 overflow-hidden">
                                            <img :src="acc.avatar || 'https://ui-avatars.com/api/?name=' + acc.name" class="w-9 h-9 rounded-full object-cover border border-slate-200 flex-shrink-0">
                                            <div class="flex flex-col truncate"><span x-text="acc.name" class="truncate text-slate-700 text-sm"></span></div>
                                        </div>
                                        <span class="text-slate-400 font-mono text-xs flex-shrink-0 bg-slate-50 px-1.5 py-0.5 rounded" x-text="acc.code"></span>
                                    </div>
                                 </template>
                            </div>
                        </div>
                        
                        {{-- 🟢 B. INFO SECTION --}}
                        <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-lg p-1.5 h-10 shadow-sm w-full" x-show="selectedAccount">
                            <div class="relative flex-shrink-0">
                                <button type="button" @click.stop="showUserConfig = !showUserConfig" class="w-7 h-7 flex items-center justify-center text-slate-400 hover:text-emerald-600 hover:bg-white rounded-md transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></button>
                                <div x-show="showUserConfig" @click.away="showUserConfig = false" class="absolute top-full right-0 mt-1 w-48 bg-white border border-slate-200 rounded-lg shadow-xl z-50 p-2 text-xs text-right" style="display: none;">
                                    <div class="font-bold text-slate-400 mb-1 px-1 uppercase text-[10px]">{!! addslashes(__('accountant.toggle_columns')) !!}</div>
                                    <label class="flex items-center gap-2 p-1.5 hover:bg-slate-50 rounded cursor-pointer"><input type="checkbox" x-model="userInfoVisible.code" class="rounded text-emerald-600 w-3.5 h-3.5"><span class="text-slate-600">{!! addslashes(__('accountant.manual_code')) !!}</span></label>
                                    <label class="flex items-center gap-2 p-1.5 hover:bg-slate-50 rounded cursor-pointer"><input type="checkbox" x-model="userInfoVisible.city" class="rounded text-emerald-600 w-3.5 h-3.5"><span class="text-slate-600">{!! addslashes(__('accountant.city')) !!}</span></label>
                                    <label class="flex items-center gap-2 p-1.5 hover:bg-slate-50 rounded cursor-pointer"><input type="checkbox" x-model="userInfoVisible.neighborhood" class="rounded text-emerald-600 w-3.5 h-3.5"><span class="text-slate-600">{!! addslashes(__('accountant.neighborhood')) !!}</span></label>
                                    <label class="flex items-center gap-2 p-1.5 hover:bg-slate-50 rounded cursor-pointer"><input type="checkbox" x-model="userInfoVisible.mobile" class="rounded text-emerald-600 w-3.5 h-3.5"><span class="text-slate-600">{!! addslashes(__('accountant.giver_mobile')) !!}</span></label>
                                </div>
                            </div>
                            <div class="w-px h-5 bg-slate-300"></div>
                            <div class="flex-1 flex items-center gap-3 overflow-x-auto custom-scrollbar px-1 text-xs font-medium text-slate-600 whitespace-nowrap">
                                <div x-show="userInfoVisible.code" class="flex items-center gap-1 bg-white border border-slate-200 px-2 py-0.5 rounded shadow-sm text-emerald-600 font-mono font-bold" x-text="selectedAccount?.code"></div>
                                <div x-show="userInfoVisible.city && selectedAccount?.city_name" class="flex items-center gap-1"><span class="text-slate-400">{!! addslashes(__('accountant.city')) !!}:</span> <span x-text="selectedAccount?.city_name"></span></div>
                                <div x-show="userInfoVisible.neighborhood && selectedAccount?.neighborhood_name" class="flex items-center gap-1 border-r border-slate-200 pr-2 mr-2"><span class="text-slate-400">{!! addslashes(__('accountant.neighborhood')) !!}:</span> <span x-text="selectedAccount?.neighborhood_name"></span></div>
                                <div x-show="userInfoVisible.mobile && selectedAccount?.mobile" class="flex items-center gap-1 font-mono text-slate-500" x-text="selectedAccount?.mobile"></div>
                            </div>
                        </div>
                    </div>

                    {{-- 🟢 SUPPORTED CURRENCIES & LIVE BALANCE --}}
                    <div class="col-span-1 md:col-span-4 p-3 bg-white rounded-xl border border-slate-200 shadow-sm flex flex-col">
                        <div class="text-[12px] font-bold text-slate-400 text-center mb-3">{!! addslashes(__('accountant.supported_currencies')) !!}</div>
                        <div class="flex flex-col gap-2 w-full">
                            <template x-for="curr in availableCurrencies" :key="curr.id">
                                <div @click="target_currency_id = curr.id; updateRate()" 
                                      class="cursor-pointer w-full px-4 py-2.5 rounded-lg text-sm font-bold border transition-all flex items-center justify-between"
                                      :class="target_currency_id == curr.id ? 'bg-emerald-600 text-white border-emerald-600 shadow-md' : 'bg-white text-slate-700 border-slate-200 hover:border-emerald-300'">
                                    
                                    {{-- 🟢 LIVE BALANCE --}}
                                    <div class="flex flex-col items-start">
                                        <span class="font-mono text-sm font-black" dir="ltr"
                                              :class="target_currency_id == curr.id ? 'text-white' : ( (selectedAccount?.balances?.[curr.id] < 0) ? 'text-rose-600' : 'text-emerald-600' )"
                                              x-text="(selectedAccount?.balances?.[curr.id] < 0 ? '-' : '+') + formatDisplayMoney(Math.abs(selectedAccount?.balances?.[curr.id] || 0))">
                                        </span>
                                        <span class="text-[10px] opacity-80" x-text="(selectedAccount?.balances?.[curr.id] < 0) ? '{!! addslashes(__('accountant.debt')) !!}' : '{!! addslashes(__('accountant.creditor')) !!}'"></span>
                                    </div>
                                    
                                    <span x-text="curr.currency_type" class="text-[14px]"></span>
                                </div>
                            </template>
                            
                            <span x-show="availableCurrencies.length === 0" class="text-xs text-slate-400 italic text-center mt-2">{!! addslashes(__('accountant.no_currency')) !!}</span>
                        </div>
                    </div>
                </div>

                {{-- 🟢 2. MAIN TRANSACTION --}}
                <div class="border border-slate-300 mb-4 bg-white rounded-md overflow-hidden">
                    <div class="grid grid-cols-12 border-b border-slate-200">
                        <div class="col-span-12 md:col-span-6 border-b md:border-b-0 md:border-r border-slate-200 p-1 flex items-center bg-slate-50/50"><label class="w-24 text-xs font-bold text-slate-500 uppercase text-center">{!! addslashes(__('accountant.type_money')) !!}</label><select name="currency_id" x-model="form.currency_id" @change="setCurrency($event.target.value)" class="flex-1 h-9 text-base font-bold border-0 bg-transparent focus:ring-0 text-center cursor-pointer"><template x-for="curr in currencies" :key="curr.id"><option :value="curr.id" x-text="curr.currency_type"></option></template></select></div>
                        <div class="col-span-12 md:col-span-6 p-1 flex items-center bg-slate-50/50"><label class="w-24 text-xs font-bold text-slate-500 uppercase text-center">{!! addslashes(__('accountant.exchange_rate')) !!}</label><input type="text" name="exchange_rate" x-model="form.rate" @input="formatRateInput(); calculateTotal()" class="flex-1 h-9 text-base font-bold border-0 bg-transparent focus:ring-0 text-center placeholder-slate-300 format-number"></div>
                    </div>
                    
                    {{-- 🟢 AMOUNT FIELD WITH CONVERSION BADGE --}}
                    <div class="grid grid-cols-12 border-b border-slate-200">
                        <div class="col-span-12 p-1 flex items-center"><label class="w-28 text-xs font-bold text-slate-600 uppercase text-center">{!! addslashes(__('accountant.amount_receive')) !!}</label>
                            <div class="flex-1 relative flex items-center justify-center">
                                <input type="text" name="amount" x-model="form.amount" @input="formatNumber('amount'); calculateTotal()" class="w-full h-10 text-lg font-black border-0 bg-white focus:ring-0 text-center text-slate-800 format-number" placeholder="0">
                                <span x-show="form.currency_id && target_currency_id && form.currency_id != target_currency_id && parseNumber(form.amount) > 0" class="absolute left-3 text-[10px] font-black text-slate-400 bg-slate-50 px-1.5 py-0.5 rounded border border-slate-200" x-text="formatDisplayMoney(convertToTarget(form.amount)) + ' ' + getCurrencyCode(target_currency_id)" x-cloak></span>
                            </div>
                        </div>
                    </div>
                    
                    {{-- 🟢 DISCOUNT FIELD WITH CONVERSION BADGE --}}
                    <div class="grid grid-cols-12 border-b border-slate-200">
                        <div class="col-span-12 p-1 flex items-center"><label class="w-28 text-xs font-bold text-slate-500 uppercase text-center">{!! addslashes(__('accountant.discount')) !!}</label>
                            <div class="flex-1 relative flex items-center justify-center">
                                <input type="text" name="discount" x-model="form.discount" @input="formatNumber('discount'); calculateTotal()" class="w-full h-9 text-base font-bold border-0 bg-white focus:ring-0 text-center text-rose-500 placeholder:text-rose-200 format-number" placeholder="0">
                                <span x-show="form.currency_id && target_currency_id && form.currency_id != target_currency_id && parseNumber(form.discount) > 0" class="absolute left-3 text-[10px] font-black text-rose-400 bg-rose-50 px-1.5 py-0.5 rounded border border-rose-100" x-text="formatDisplayMoney(convertToTarget(form.discount)) + ' ' + getCurrencyCode(target_currency_id)" x-cloak></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-12 border-b border-slate-200">
                        <div class="col-span-12 p-1 flex items-center"><label class="w-28 text-xs font-bold text-emerald-700 uppercase text-center">{!! addslashes(__('accountant.cashbox')) !!}</label>
                            <select name="cashbox_id" x-model="form.cashbox_id" class="flex-1 h-9 text-base font-bold border-0 bg-white focus:ring-0 text-center text-emerald-800 cursor-pointer">
                                <option value="" disabled selected>-- {!! addslashes(__('accountant.select')) !!} --</option>
                                <template x-for="box in mainFilteredCashboxes" :key="box.id"><option :value="box.id" x-text="box.name"></option></template>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-12">
                        <div class="col-span-12 p-1 flex items-center"><label class="w-28 text-xs font-bold text-emerald-700 uppercase text-center">{!! addslashes(__('accountant.total')) !!}</label>
                            <div class="flex-1 flex items-center justify-center gap-2 format-number">
                                <input type="text" name="total" x-model="form.total" @input="formatNumber('total'); recalcRateFromTotal(); if($event.target.value === '') resetTotal();" :readonly="isTotalLocked" class="w-32 md:w-56 h-10 text-lg font-black border-0 bg-white focus:ring-0 text-center text-emerald-800 format-number">
                                <span class="text-xs font-bold text-emerald-400" x-text="getCurrencyCode(target_currency_id)"></span>
                                <button type="button" @click="if(form.currency_id != target_currency_id) isTotalLocked = !isTotalLocked" :class="form.currency_id == target_currency_id ? 'text-slate-300 cursor-not-allowed' : 'text-emerald-400 hover:text-emerald-600'" :disabled="form.currency_id == target_currency_id">
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
                        <div class="border-b md:border-b-0 md:border-r border-slate-300 p-1"><input type="text" name="giver_name" x-model="form.giver_name" class="w-full h-9 text-base border-0 bg-white text-center focus:ring-0 placeholder:text-slate-400" placeholder="{!! addslashes(__('accountant.giver_name')) !!}"></div>
                        <div class="p-1"><input type="text" name="giver_mobile" x-model="form.giver_mobile" class="w-full h-9 text-base border-0 bg-white text-center focus:ring-0 placeholder:text-slate-400" placeholder="{!! addslashes(__('accountant.giver_mobile')) !!}"></div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2">
                        <div class="border-b md:border-b-0 md:border-r border-slate-300 p-1"><input type="text" name="receiver_name" x-model="form.receiver_name" class="w-full h-9 text-base border-0 bg-white text-center focus:ring-0 placeholder:text-slate-400" placeholder="{!! addslashes(__('accountant.receiver_name')) !!}"></div>
                        <div class="p-1"><input type="text" name="receiver_mobile" x-model="form.receiver_mobile" class="w-full h-9 text-base border-0 bg-white text-center focus:ring-0 placeholder:text-slate-400" placeholder="{!! addslashes(__('accountant.receiver_mobile')) !!}"></div>
                    </div>
                </div>

                {{-- 🟢 3. PROFIT SECTION --}}
                <div class="mb-2">
                    <label class="flex items-center gap-2 cursor-pointer mb-1 bg-emerald-50/50 border border-emerald-200 p-1.5 rounded w-fit">
                        <input type="checkbox" x-model="showProfit" class="rounded text-emerald-600 w-4 h-4 border-emerald-300">
                        <span class="text-xs font-black text-emerald-800 uppercase px-1">{!! addslashes(__('accountant.profit')) !!}</span>
                    </label>
                    <div x-show="showProfit" x-collapse class="border-2 border-emerald-400 bg-white rounded-md shadow-sm">
                        <div class="grid grid-cols-12 border-b border-emerald-200">
                            <div class="col-span-2 border-l border-emerald-200 p-0 flex items-center justify-center bg-emerald-50/30"><label class="text-[9px] font-bold text-emerald-800 text-center">{!! addslashes(__('accountant.amount')) !!}</label></div>
                            <div class="col-span-4 p-0"><input type="text" name="profit_amount" x-model="form.profit_amount" @input="formatNumber('profit_amount')" class="w-full h-9 text-sm font-black border-0 bg-white text-center text-emerald-600 format-number" placeholder="0"></div>
                            <div class="col-span-2 border-l border-r border-emerald-200 p-0 flex items-center justify-center bg-emerald-50/30"><label class="text-[9px] font-bold text-emerald-800 text-center">{!! addslashes(__('accountant.type')) !!}</label></div>
                            <div class="col-span-4 p-0"><select name="profit_category_id" x-model="form.profit_category_id" @change="autoSelectProfitCurrency()" class="w-full h-9 text-xs font-bold border-0 bg-white text-center cursor-pointer text-slate-700">
    <option value="">-- {!! addslashes(__('accountant.type')) !!} --</option>
    <template x-for="cat in profitTypes" :key="cat.id">
        <option :value="cat.id" x-text="cat.name || cat.type_name || cat.title || cat.type"></option>
    </template>
</select></div>
                        </div>
                        <div class="grid grid-cols-12 border-b border-emerald-200">
                            <div class="col-span-2 border-l border-emerald-200 p-0 flex items-center justify-center bg-emerald-50/30"><label class="text-[9px] font-bold text-emerald-800 text-center">{!! addslashes(__('accountant.currency')) !!}</label></div>
                            <div class="col-span-4 p-0"><select name="profit_currency_id" x-model="form.profit_currency_id" @change="updateProfitCashboxes()" class="w-full h-9 text-xs font-bold border-0 bg-white text-center cursor-pointer text-slate-700"><option value="">-- {!! addslashes(__('accountant.currency')) !!} --</option><template x-for="curr in currencies" :key="curr.id"><option :value="curr.id" x-text="curr.currency_type"></option></template></select></div>
                            <div class="col-span-2 border-l border-r border-emerald-200 p-0 flex items-center justify-center bg-emerald-50/30"><label class="text-[9px] font-bold text-emerald-800 text-center">{!! addslashes(__('accountant.cashbox')) !!}</label></div>
                            <div class="col-span-4 p-0">
                                <select name="profit_cashbox_id" x-model="form.profit_cashbox_id" :disabled="form.profit_is_debt" :class="form.profit_is_debt ? 'bg-slate-100 text-slate-400' : 'bg-white text-emerald-700'" class="w-full h-9 text-xs font-bold border-0 text-center cursor-pointer text-emerald-700">
                                    <option value="" x-show="form.profit_is_debt" disabled>{!! addslashes(__('accountant.disabled')) !!}</option>
                                    <option value="" x-show="!form.profit_is_debt" selected>-- {!! addslashes(__('accountant.select')) !!} --</option>
                                    <template x-for="box in profitFilteredCashboxes" :key="box.id"><option :value="box.id" x-text="box.name"></option></template>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 bg-rose-50/30">
                            <div class="col-span-4 p-0 flex items-center justify-center bg-rose-100/30 border-l border-emerald-200"><label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="profit_is_debt" x-model="form.profit_is_debt" value="1" class="rounded text-rose-500 w-3.5 h-3.5"><span class="text-[10px] font-black text-rose-700">{!! addslashes(__('accountant.debt')) !!}</span></label></div>
                            <div class="col-span-8 p-0 relative bg-white"><div x-show="form.profit_is_debt" class="relative w-full h-full" @click.away="profitSearchOpen = false"><input type="text" x-model="profitSearchQuery" @click="profitSearchOpen = true" @input="profitSearchOpen = true" placeholder="{!! addslashes(__('accountant.search_users')) !!}" class="w-full h-9 text-xs font-bold text-center border-0 focus:ring-0 text-rose-800"><div x-show="profitSearchOpen" class="absolute top-full left-0 w-full bg-white border border-slate-200 shadow-xl max-h-60 overflow-y-auto z-50 rounded-b-md custom-scrollbar"><template x-for="acc in filteredProfitAccounts" :key="acc.id"><div @click="profitAccount = acc; profitSearchQuery = acc.name; profitSearchOpen = false;" class="px-3 py-2 hover:bg-rose-50 cursor-pointer text-xs font-bold border-b border-slate-50 flex justify-between items-center text-right"><div><span x-text="acc.name"></span></div><span class="text-slate-400" x-text="acc.code"></span></div></template></div><div class="absolute right-2 top-1/2 -translate-y-1/2 text-[9px] text-green-600 font-black" x-show="profitAccount">✓</div></div><div x-show="!form.profit_is_debt" class="w-full h-9 flex items-center justify-center text-[10px] text-slate-300 italic bg-slate-50">{!! addslashes(__('accountant.debt_disabled')) !!}</div></div>
                        </div>
                    </div>
                </div>

                {{-- 🟢 4. SPENDING SECTION --}}
                <div class="mb-4">
                    <label class="flex items-center gap-2 cursor-pointer mb-1 bg-rose-50/50 border border-rose-200 p-1.5 rounded w-fit">
                        <input type="checkbox" x-model="showSpending" class="rounded text-rose-600 w-4 h-4 border-rose-300">
                        <span class="text-xs font-black text-rose-800 uppercase px-1">{!! addslashes(__('accountant.spending')) !!}</span>
                    </label>
                    <div x-show="showSpending" x-collapse class="border-2 border-rose-400 bg-white rounded-md shadow-sm">
                        <div class="grid grid-cols-12 border-b border-rose-200">
                            <div class="col-span-2 border-l border-rose-200 p-0 flex items-center justify-center bg-rose-50/30"><label class="text-[9px] font-bold text-rose-800 text-center">{!! addslashes(__('accountant.amount')) !!}</label></div>
                            <div class="col-span-4 p-0"><input type="text" name="spending_amount" x-model="form.spending_amount" @input="formatNumber('spending_amount')" class="w-full h-9 text-sm font-black border-0 bg-white text-center focus:ring-0 text-rose-600 placeholder:text-rose-200 format-number" placeholder="0"></div>
                            <div class="col-span-2 border-l border-r border-rose-200 p-0 flex items-center justify-center bg-rose-50/30"><label class="text-[9px] font-bold text-rose-800 text-center">{!! addslashes(__('accountant.type')) !!}</label></div>
                            <div class="col-span-4 p-0"><select name="spending_category_id" x-model="form.spending_category_id" @change="autoSelectSpendingCurrency()" class="w-full h-9 text-xs font-bold border-0 bg-white text-center cursor-pointer text-slate-700"><option value="">-- {!! addslashes(__('accountant.type')) !!} --</option><template x-for="cat in spendingTypes" :key="cat.id"><option :value="cat.id" x-text="cat.name"></option></template></select></div>
                        </div>
                        <div class="grid grid-cols-12 border-b border-rose-200">
                            <div class="col-span-2 border-l border-rose-200 p-0 flex items-center justify-center bg-rose-50/30"><label class="text-[9px] font-bold text-rose-800 text-center">{!! addslashes(__('accountant.currency')) !!}</label></div>
                            <div class="col-span-4 p-0"><select name="spending_currency_id" x-model="form.spending_currency_id" @change="updateSpendingCashboxes()" class="w-full h-9 text-xs font-bold border-0 bg-white text-center cursor-pointer text-slate-700"><option value="">-- {!! addslashes(__('accountant.currency')) !!} --</option><template x-for="curr in currencies" :key="curr.id"><option :value="curr.id" x-text="curr.currency_type"></option></template></select></div>
                            <div class="col-span-2 border-l border-r border-rose-200 p-0 flex items-center justify-center bg-rose-50/30"><label class="text-[9px] font-bold text-rose-800 text-center">{!! addslashes(__('accountant.cashbox')) !!}</label></div>
                            <div class="col-span-4 p-0">
                                <select name="spending_cashbox_id" x-model="form.spending_cashbox_id" :disabled="form.spending_is_debt" :class="form.spending_is_debt ? 'bg-slate-100 text-slate-400' : 'bg-white text-rose-700'" class="w-full h-9 text-xs font-bold border-0 text-center cursor-pointer text-rose-700">
                                    <option value="" x-show="form.spending_is_debt" disabled>{!! addslashes(__('accountant.disabled')) !!}</option>
                                    <option value="" x-show="!form.spending_is_debt" selected>-- {!! addslashes(__('accountant.select')) !!} --</option>
                                    <template x-for="box in spendingFilteredCashboxes" :key="box.id"><option :value="box.id" x-text="box.name"></option></template>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 bg-rose-50/30">
                            <div class="col-span-4 p-0 flex items-center justify-center bg-rose-100/30 border-l border-rose-200"><label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="spending_is_debt" x-model="form.spending_is_debt" value="1" class="rounded text-rose-500 w-3.5 h-3.5"><span class="text-[10px] font-black text-rose-700">{!! addslashes(__('accountant.debt')) !!}</span></label></div>
                            <div class="col-span-8 p-0 relative bg-white"><div x-show="form.spending_is_debt" class="relative w-full h-full" @click.away="spendingSearchOpen = false"><input type="text" x-model="spendingSearchQuery" @click="spendingSearchOpen = true" @input="spendingSearchOpen = true" placeholder="{!! addslashes(__('accountant.search_users')) !!}" class="w-full h-9 text-xs font-bold text-center border-0 focus:ring-0 text-rose-800"><div x-show="spendingSearchOpen" class="absolute top-full left-0 w-full bg-white border border-slate-200 shadow-xl max-h-60 overflow-y-auto z-50 rounded-b-md custom-scrollbar"><template x-for="acc in filteredSpendingAccounts" :key="acc.id"><div @click="spendingAccount = acc; spendingSearchQuery = acc.name; spendingSearchOpen = false;" class="px-3 py-2 hover:bg-rose-50 cursor-pointer text-xs font-bold border-b border-slate-50 flex justify-between items-center text-right"><div><span x-text="acc.name"></span></div><span class="text-slate-400" x-text="acc.code"></span></div></template></div><div class="absolute right-2 top-1/2 -translate-y-1/2 text-[9px] text-green-600 font-black" x-show="spendingAccount">✓</div></div><div x-show="!form.spending_is_debt" class="w-full h-9 flex items-center justify-center text-[10px] text-slate-300 italic bg-slate-50">{!! addslashes(__('accountant.debt_disabled')) !!}</div></div>
                        </div>
                    </div>
                </div>

                {{-- NOTE & FOOTER --}}
                <div class="border border-slate-300 bg-white mb-4 p-2 rounded-md">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div class="relative order-last md:order-first">
                            <label class="absolute top-1 right-2 text-[9px] text-slate-400 font-bold uppercase">{!! addslashes(__('accountant.manual_code')) !!}</label>
                            <input type="text" name="statement_id" x-model="form.statement_id" class="w-full h-12 text-lg text-center rounded-md border border-slate-200 focus:border-emerald-300 focus:ring-0 bg-white">
                        </div>
                        <div class="relative order-first md:order-last">
                            <input type="date" onclick="this.showPicker()" x-model="form.manual_date" name="manual_date" class="w-full h-12 text-lg text-center rounded-md border border-slate-200 focus:border-emerald-300 focus:ring-0 bg-white text-slate-700 font-bold cursor-pointer">
                        </div>
                    </div>
                    <div class="mt-2">
                        <input name="note" x-model="form.note" class="w-full h-10 text-base text-center rounded-md border border-slate-200 focus:border-emerald-300 focus:ring-0 placeholder:text-slate-300 placeholder:font-light" placeholder="{!! addslashes(__('accountant.note')) !!}">
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-0 text-white text-xs font-bold text-center">
                    <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 py-3 transition-colors">{!! addslashes(__('accountant.save')) !!}</button>
                    <button type="button" @click="closeModal()" class="bg-rose-400 hover:bg-rose-500 py-3 transition-colors">{!! addslashes(__('accountant.cancel')) !!}</button>
                    <button type="button" class="bg-sky-500 hover:bg-sky-600 py-3 transition-colors">{!! addslashes(__('accountant.hold')) !!}</button>
                    <button type="button" class="bg-slate-700 hover:bg-slate-800 py-3 transition-colors">{!! addslashes(__('accountant.print_large')) !!}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 🟢 THE FULLY UPDATED JAVASCRIPT WITH PERFECT SMART MATH --}}
    <script>
        function receivingForm() {
            return {
                showModal: false,
                isEditing: false, 
                showUserConfig: false,
                editingId: null,
                
                accounts: {!! json_encode($accounts ?? []) !!}, 
                profitTypes: {!! json_encode($profitTypes ?? []) !!},
                spendingTypes: {!! json_encode($spendingTypes ?? []) !!},
                cashboxes: {!! json_encode($cashboxes ?? []) !!},
                currencies: {!! json_encode($currencies ?? []) !!},

                searchQuery: '', selectedAccount: null, searchOpen: false,
                profitSearchQuery: '', profitAccount: null, profitSearchOpen: false,
                spendingSearchQuery: '', spendingAccount: null, spendingSearchOpen: false,
                
                showProfit: false, showSpending: false,
                userInfoVisible: { code: true, city: true, neighborhood: true, mobile: true },
                isTotalLocked: true, target_currency_id: null,

                mainFilteredCashboxes: [],
                profitFilteredCashboxes: [],
                spendingFilteredCashboxes: [],

                form: {
                    amount: '', discount: '', currency_id: '', cashbox_id: '', rate: 1, total: 0, 
                    manual_date: new Date().toISOString().split('T')[0], note: '', statement_id: '', 
                    giver_name: '', giver_mobile: '', receiver_name: '', receiver_mobile: '',
                    profit_category_id: '', profit_amount: '', profit_is_debt: false, profit_cashbox_id: '', profit_currency_id: '',
                    spending_category_id: '', spending_amount: '', spending_is_debt: false, spending_cashbox_id: '', spending_currency_id: ''
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
                        
                        // 🔥 Run math instantly when Edit Modal opens
                        this.$nextTick(() => { this.calculateTotal(); });
                        
                    } else {
                        this.isEditing = false;
                        this.form = { amount: '', discount: '', currency_id: '', cashbox_id: '', rate: 1, total: 0, profit_is_debt: false, spending_is_debt: false, manual_date: new Date().toISOString().split('T')[0], note: '', statement_id: '' };
                        this.selectedAccount = null; this.searchQuery = '';
                        this.profitAccount = null; this.profitSearchQuery = '';
                        this.spendingAccount = null; this.spendingSearchQuery = '';
                        if (this.currencies.length > 0) this.setCurrency(this.currencies[0].id);
                        this.profitFilteredCashboxes = [];
                        this.spendingFilteredCashboxes = [];
                    }
                },
                closeModal() { this.showModal = false; },

                get filteredAccounts() { 
                    if(this.searchQuery === '') return this.accounts.slice(0, 10);
                    const q = this.searchQuery.toLowerCase();
                    return this.accounts.filter(a => a.name.toLowerCase().includes(q) || String(a.code).toLowerCase().includes(q));
                },
                get filteredProfitAccounts() {
                    if(this.profitSearchQuery === '') return this.accounts.slice(0, 10);
                    const q = this.profitSearchQuery.toLowerCase();
                    return this.accounts.filter(a => a.name.toLowerCase().includes(q) || String(a.code).toLowerCase().includes(q));
                },
                get filteredSpendingAccounts() {
                    if(this.spendingSearchQuery === '') return this.accounts.slice(0, 10);
                    const q = this.spendingSearchQuery.toLowerCase();
                    return this.accounts.filter(a => a.name.toLowerCase().includes(q) || String(a.code).toLowerCase().includes(q));
                },
            selectAccount(acc) { 
                    this.selectedAccount = acc;
                    this.searchQuery = acc.name; 
                    this.searchOpen = false;
                    
                    let supported = acc.supported_currencies || [];
                    
                    // Default target currency to the account's actual default currency
                    if (acc.default_currency_id) {
                        this.target_currency_id = acc.default_currency_id;
                    } else if (supported.length > 0) {
                        this.target_currency_id = supported[0];
                    } else {
                        this.target_currency_id = this.form.currency_id;
                    }
                    
                    this.updateRate();
                },

                setCurrency(id) { 
                    this.form.currency_id = id;
                    this.updateMainCashboxes(); 
                    
                    // 🟢 FIX: We removed the auto-sync here.
                    // Now, when you change the dropdown, the Target Currency (left side) 
                    // will NOT change. It stays exactly on what you selected!
                    
                    this.updateRate(); 
                },
                
                updateMainCashboxes() {
                    this.mainFilteredCashboxes = this.cashboxes.filter(b => b.currency_id == this.form.currency_id);
                    if(this.mainFilteredCashboxes.length > 0) this.form.cashbox_id = this.mainFilteredCashboxes[0].id;
                },

                autoSelectProfitCurrency() {
                    const cat = this.profitTypes.find(c => c.id == this.form.profit_category_id);
                    if (cat && cat.currency_id) { 
                        this.form.profit_currency_id = cat.currency_id;
                        this.updateProfitCashboxes(); 
                    }
                },
                updateProfitCashboxes() {
                    if (!this.form.profit_currency_id) {
                        this.profitFilteredCashboxes = [];
                    } else {
                        this.profitFilteredCashboxes = this.cashboxes.filter(b => b.currency_id == this.form.profit_currency_id);
                    }
                    if(this.profitFilteredCashboxes.length > 0) this.form.profit_cashbox_id = this.profitFilteredCashboxes[0].id;
                },

                autoSelectSpendingCurrency() {
                    const cat = this.spendingTypes.find(c => c.id == this.form.spending_category_id);
                    if (cat && cat.currency_id) { 
                        this.form.spending_currency_id = cat.currency_id;
                        this.updateSpendingCashboxes(); 
                    }
                },
                updateSpendingCashboxes() {
                    if (!this.form.spending_currency_id) {
                        this.spendingFilteredCashboxes = [];
                    } else {
                        this.spendingFilteredCashboxes = this.cashboxes.filter(b => b.currency_id == this.form.spending_currency_id);
                    }
                    if(this.spendingFilteredCashboxes.length > 0) this.form.spending_cashbox_id = this.spendingFilteredCashboxes[0].id;
                },

                get availableCurrencies() { 
                    if (!this.selectedAccount) return [];
                    let supported = this.selectedAccount.supported_currencies || [];
                    if (supported.length === 0) return []; 
                    return this.currencies.filter(c => supported.includes(c.id) || supported.includes(String(c.id)));
                },

                parseNumber(val) { 
                    if (!val) return 0;
                    return parseFloat(val.toString().replace(/,/g, '')) || 0; 
                },
                
                // 🟢 NEW SMART ALGORITHM FOR CONVERTING CURRENCY IN UI (Iranian Rial / IQD safe!)
                convertToTarget(val) {
                    const num = this.parseNumber(val);
                    if (!num || !this.form.currency_id || !this.target_currency_id) return 0;
                    if (this.form.currency_id == this.target_currency_id) return num;
                    
                    const rate = this.parseNumber(this.form.rate) || 1;
                    const s = this.currencies.find(c => c.id == this.form.currency_id);
                    const t = this.currencies.find(c => c.id == this.target_currency_id);
                    if(!s || !t) return 0;
                    
                    let sPrice = parseFloat(s.price_single || 1);
                    let tPrice = parseFloat(t.price_single || 1);
                    
                    // If target currency is WEAKER (e.g. converting 1 USD to 60,000 IRR) -> MULTIPLY
                    if (tPrice > sPrice) {
                        return num * rate;
                    } 
                    // If target currency is STRONGER (e.g. converting 60,000 IRR to 1 USD) -> DIVIDE
                    else if (tPrice < sPrice) {
                        return num / rate;
                    } 
                    // Fallback
                    else {
                        if (rate > 50) return num * rate;
                        return num / rate;
                    }
                },

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
                        
                        // Ensures Rate is always displayed as a Whole Number multiplier 
                        if (tRate > sRate) { 
                            this.form.rate = parseFloat((tRate / sRate).toFixed(6)); 
                        } else if (tRate < sRate) { 
                            this.form.rate = parseFloat((sRate / tRate).toFixed(6)); 
                        } else {
                            this.form.rate = 1;
                        }
                        this.calculateTotal();
                    } 
                },
                
                // 🟢 RECEIVING FORM ALWAYS USES (AMOUNT - DISCOUNT)
                calculateTotal() { 
                    if (!this.isTotalLocked) return;
                    const amt = this.parseNumber(this.form.amount); 
                    const discount = this.parseNumber(this.form.discount); 
                    
                    // MINUS (-) FOR RECEIVING FORM
                    const baseTotal = amt + discount;
                    
                    if (!this.form.currency_id || !this.target_currency_id || baseTotal <= 0) { 
                        this.form.total = ''; return; 
                    }

                    if (this.form.currency_id == this.target_currency_id) {
                        this.form.total = baseTotal.toLocaleString('en-US', { maximumFractionDigits: 2 });
                        return;
                    }

                    let converted = this.convertToTarget(baseTotal);
                    this.form.total = converted.toLocaleString('en-US', { maximumFractionDigits: 2 });
                },
                
                // 🟢 REVERSES THE SMART MATH IF TOTAL IS TYPED MANUALLY
                recalcRateFromTotal() { 
                    if (this.isTotalLocked) return;
                    const total = this.parseNumber(this.form.total); 
                    const amt = this.parseNumber(this.form.amount);
                    const discount = this.parseNumber(this.form.discount);
                    
                    // MINUS (-) FOR RECEIVING FORM
                    const baseTotal = amt + discount;
                    
                    if (baseTotal <= 0 || total === 0) { this.form.rate = 1; return; } 
                    
                    const s = this.currencies.find(c => c.id == this.form.currency_id);
                    const t = this.currencies.find(c => c.id == this.target_currency_id); 
                    let sPrice = s ? parseFloat(s.price_single || 1) : 1;
                    let tPrice = t ? parseFloat(t.price_single || 1) : 1;

                    if (tPrice > sPrice) { 
                        this.form.rate = parseFloat((total / baseTotal).toFixed(6)); 
                    } else if (tPrice < sPrice) { 
                        this.form.rate = parseFloat((baseTotal / total).toFixed(6)); 
                    } else {
                        if (total > baseTotal) {
                            this.form.rate = parseFloat((total / baseTotal).toFixed(6));
                        } else {
                            this.form.rate = parseFloat((baseTotal / total).toFixed(6));
                        }
                    }
                },
                
                getCurrencyCode(id) { 
                    if(!id) return '';
                    const c = this.currencies.find(x => x.id == id); 
                    return c ? c.currency_type : '';
                },

                formatDisplayMoney(val) {
                    if (!val && val !== 0) return '0';
                    return parseFloat(val).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2});
                },

                formatNumber(field) { if(this.form[field]) this.form[field] = String(this.form[field]).replace(/[^0-9.,]/g, ''); },
                formatRateInput() { if(this.form.rate) this.form.rate = String(this.form.rate).replace(/[^0-9.,]/g, ''); },
                
                clearSelection() { this.selectedAccount = null; this.searchQuery = ''; }
            }
        }
    </script>
</div>