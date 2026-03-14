<x-app-layout>
    <div x-data="expenseForm()" class="py-8 w-full max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">
        
        {{-- Header --}}
        <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    {{ __('expense.new_expense') ?? 'تۆمارکردنی خەرجی نوێ' }}
                </h2>
                <p class="text-sm text-slate-500 mt-1">{{ __('expense.new_expense_desc') ?? 'زیادکردنی خەرجییەکی نوێ بە هەژمارکردنی ڕاستەوخۆ.' }}</p>
            </div>
            <a href="{{ route('accountant.expenses.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 text-sm font-bold rounded-xl hover:bg-slate-50 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('expense.back') ?? 'گەڕانەوە' }}
            </a>
        </div>

        {{-- Main Form Container --}}
        <form action="{{ route('accountant.expenses.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                
                {{-- 📝 MAIN AREA: Table First, then Documents --}}
                <div class="lg:col-span-3 p-6 sm:p-8 flex flex-col h-full bg-white rounded-2xl shadow-sm border border-slate-200">
                    
                    {{-- THE DYNAMIC TABLE --}}
                    <div class="border border-slate-200 rounded-xl overflow-x-auto overflow-y-visible shadow-sm flex-1 pb-6">
                        <table class="w-full text-sm text-center min-w-[600px]">
                            <thead class="bg-indigo-50/80 text-indigo-900 border-b border-indigo-100">
                                <tr>
                                    <th class="py-3 px-3 w-12 text-xs uppercase tracking-wider font-black">{{ __('expense.id') ?? 'ڕێز' }}</th>
                                    <th class="py-3 px-3 w-1/3 text-xs uppercase tracking-wider font-black">{{ __('expense.category_name') ?? 'جۆری خەرجی' }} *</th>
                                    <th class="py-3 px-3 w-1/4 text-xs uppercase tracking-wider font-black">{{ __('expense.price') ?? 'نرخ' }} *</th>
                                    <th class="py-3 px-3 text-xs uppercase tracking-wider font-black">{{ __('expense.note') ?? 'تێبینی' }}</th>
                                    <th class="py-3 px-3 w-12"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr class="hover:bg-slate-50 transition-colors group">
                                        <td class="p-2.5 font-black text-slate-400 text-sm" x-text="index + 1"></td>
                                        
                                        {{-- SEARCHABLE CATEGORY INPUT --}}
                                        <td class="p-2.5" x-data="{ catSearchOpen: false }">
                                            <input type="hidden" :name="'items['+index+'][category_id]'" :value="item.category_id">
                                            
                                            <div class="relative text-right">
                                                <input type="text" x-model="item.category_name" 
                                                       @focus="catSearchOpen = true" 
                                                       @input="catSearchOpen = true; item.category_id = ''" 
                                                       class="w-full text-sm font-bold text-slate-700 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-white shadow-sm py-2 px-3 transition-colors"
                                                       :class="!item.category_id && item.category_name !== '' ? 'border-orange-400' : 'border-slate-200'"
                                                       placeholder="{{ __('expense.search_category') ?? 'گەڕان بۆ خەرجی...' }}" required>

                                                <div x-show="catSearchOpen" @click.outside="catSearchOpen = false" x-transition.opacity 
                                                     class="absolute z-[60] w-64 mt-1 bg-white border border-slate-200 rounded-xl shadow-2xl overflow-hidden ltr:left-0 rtl:right-0" 
                                                     style="display: none;">
                                                    <ul class="max-h-48 overflow-y-auto custom-scrollbar text-right">
                                                        <template x-for="cat in categoriesList.filter(c => c.name.toLowerCase().includes(item.category_name.toLowerCase()))" :key="cat.id">
                                                            <li @click="item.category_id = cat.id; item.category_name = cat.name; catSearchOpen = false;" 
                                                                class="py-2.5 px-4 text-xs hover:bg-indigo-50 cursor-pointer font-bold border-b border-slate-50 last:border-0 text-slate-700" 
                                                                x-text="cat.name">
                                                            </li>
                                                        </template>
                                                        <li x-show="categoriesList.filter(c => c.name.toLowerCase().includes(item.category_name.toLowerCase())).length === 0" class="py-3 text-xs text-slate-400 text-center">
                                                            هیچ ئەنجامێک نەدۆزرایەوە
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="p-2.5">
                                            <input type="hidden" :name="'items['+index+'][price]'" :value="item.price">
                                            <input type="number" step="0.01" x-model.number="item.price" class="w-full text-center font-bold text-slate-700 rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 text-sm shadow-sm py-2" placeholder="0" required>
                                        </td>
                                        <td class="p-2.5">
                                            <input type="hidden" :name="'items['+index+'][note]'" :value="item.note">
                                            <input type="text" x-model="item.note" class="w-full text-sm text-right rounded-lg border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 bg-white shadow-sm py-2" placeholder="{{ __('expense.note_placeholder') ?? 'تێبینی...' }}">
                                        </td>
                                        <td class="p-2.5">
                                            <button type="button" @click="removeItem(index)" class="text-slate-300 hover:text-red-500 transition p-2 rounded-lg hover:bg-red-50" title="Remove Row">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- Add Row Button & Currency Settings --}}
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mt-6 gap-4 pb-4">
                        <button type="button" @click="addItem()" class="flex items-center gap-2 px-5 py-2.5 bg-slate-50 hover:bg-slate-100 text-slate-700 font-bold rounded-xl transition-colors text-sm border border-slate-200 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> {{ __('expense.add_row') ?? 'زیادکردنی ڕیز' }}
                        </button>

                        <div class="flex items-center gap-3 bg-indigo-50/50 p-3 rounded-2xl border border-indigo-100 shadow-sm w-full sm:w-auto">
                            
                            {{-- Searchable Currency Dropdown --}}
                            <div class="flex items-center gap-2" x-data="{ searchCurOpen: false, searchCur: '' }">
                                <label class="text-xs font-bold text-indigo-800">{{ __('expense.currency_name') ?? 'جۆری پارە' }}</label>
                                <div class="relative">
                                    <input type="hidden" name="currency_id" :value="form.currency_id">
                                    <div @click="searchCurOpen = !searchCurOpen; if(searchCurOpen) $nextTick(() => $refs.searchCurInput.focus())" class="w-28 flex items-center justify-between rounded-xl border border-white shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold py-2 px-3 bg-white cursor-pointer hover:border-indigo-300 transition-colors">
                                        <span x-text="form.currency_id ? currenciesData[form.currency_id].type : '...'" class="text-indigo-700"></span>
                                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                    <div x-show="searchCurOpen" @click.outside="searchCurOpen = false" x-transition.opacity class="absolute z-50 w-48 mt-2 bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden ltr:left-0 rtl:right-0" style="display: none;">
                                        <div class="p-2 border-b border-slate-100 bg-slate-50">
                                            <input x-ref="searchCurInput" type="text" x-model="searchCur" class="w-full text-xs rounded-lg border-slate-300 py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500" placeholder="{{ __('expense.search_currency') ?? 'گەڕان بۆ دراو...' }}">
                                        </div>
                                        <ul class="max-h-48 overflow-y-auto custom-scrollbar">
                                            <template x-for="cur in currenciesList.filter(c => c.type.toLowerCase().includes(searchCur.toLowerCase()))" :key="cur.id">
                                                <li @click="form.currency_id = cur.id; searchCurOpen = false; searchCur = ''" 
                                                    class="py-2.5 px-4 text-xs hover:bg-indigo-50 cursor-pointer font-bold border-b border-slate-50 last:border-0" 
                                                    :class="form.currency_id == cur.id ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700'"
                                                    x-text="cur.type">
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <label class="text-xs font-bold text-indigo-800">{{ __('expense.exchange_rate') ?? 'نرخی پارە' }}</label>
                                <input type="number" step="0.000001" name="exchange_rate" x-model="form.exchange_rate" class="w-28 rounded-xl border border-white shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm text-center py-2 font-mono bg-white">
                            </div>
                        </div>
                    </div>
                    
                    <div class="border-t border-slate-100 my-4"></div>

                    {{-- Document Information Section --}}
                    <div class="bg-slate-50 border border-slate-200 p-5 rounded-2xl mb-6 grid grid-cols-1 md:grid-cols-3 gap-5 items-end shadow-inner mt-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1.5">{{ __('expense.expense_date') ?? 'بەروار' }} *</label>
                            <input type="date" name="expense_date" x-model="form.date" class="w-full text-center rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold bg-white shadow-sm py-2.5" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1.5">{{ __('expense.manual_voucher') ?? 'بەڵگەی دەستی' }}</label>
                            <input type="text" name="manual_voucher" x-model="form.manual_voucher" class="w-full text-center rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold bg-white shadow-sm py-2.5" placeholder="---">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1.5">{{ __('expense.attachment') ?? 'هاوپێچکردنی بەڵگە' }}</label>
                            <input type="file" name="attachment" class="w-full text-sm text-slate-500 file:mr-2 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer border border-slate-300 rounded-xl bg-white shadow-sm h-[42px] leading-[42px]">
                        </div>
                    </div>

                    {{-- Global Note & Submit Footer --}}
                    <div class="flex flex-col md:flex-row gap-5 items-end mt-auto pt-2">
                        <div class="flex-1 w-full">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('expense.general_note') ?? 'تێبینی گشتی' }}</label>
                            <input type="text" name="note" x-model="form.note" class="w-full text-sm rounded-xl border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm bg-slate-50 hover:bg-white transition-colors py-3 px-4" placeholder="{{ __('expense.general_note_placeholder') ?? 'هەر تێبینییەکی گشتی...' }}">
                        </div>
                        
                        <div class="flex items-center gap-3 shrink-0">
                            <span x-show="remainingAmount !== 0" class="text-xs font-bold text-red-600 animate-pulse bg-red-50 px-4 py-2 rounded-xl border border-red-200" dir="ltr">
                                {{ __('expense.balance_must_be_zero') ?? 'Balance must be 0 to save!' }}
                            </span>
                            <button type="submit" class="px-8 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-md hover:shadow-lg hover:shadow-indigo-500/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed text-base flex items-center justify-center gap-2" :disabled="remainingAmount !== 0 || (form.debt_amount > 0 && !form.account_id)">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('expense.save_expense') ?? 'تۆمارکردن' }}
                            </button>
                        </div>
                    </div>

                </div>

                {{-- 🧮 SIDEBAR: Calculations & Searchable Account --}}
                <div class="lg:col-span-1 p-6 bg-white space-y-6 rounded-2xl border border-slate-200 shadow-sm h-fit relative overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-indigo-500"></div>
                    
                    {{-- Payment Inputs --}}
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1.5">{{ __('expense.discount') ?? 'داشکاندن' }}</label>
                            <input type="number" step="0.01" name="discount" x-model.number="form.discount" class="w-full text-center font-black text-orange-500 text-lg rounded-xl border-slate-200 focus:ring-orange-500 focus:border-orange-500 py-2.5 shadow-sm bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1.5">{{ __('expense.cash_amount') ?? 'نەقد' }}</label>
                            <input type="number" step="0.01" name="cash_amount" x-model.number="form.cash_amount" class="w-full text-center font-black text-emerald-600 text-lg rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500 py-2.5 shadow-sm bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1.5">{{ __('expense.debt_amount') ?? 'قەرز' }}</label>
                            <input type="number" step="0.01" name="debt_amount" x-model.number="form.debt_amount" class="w-full text-center font-black text-rose-500 text-lg rounded-xl border-slate-200 focus:ring-rose-500 focus:border-rose-500 py-2.5 shadow-sm bg-white">
                        </div>
                    </div>

                    <div class="border-t border-slate-100 my-4"></div>

                    {{-- THE LOCKED ACCOUNT SEARCH --}}
                    <div x-data="{ searchOpen: false, searchAcc: '' }" class="relative">
                        <label class="block text-sm font-black text-slate-800 mb-2">{{ __('expense.account_name') ?? 'هەژمار' }}</label>
                        
                        <input type="hidden" name="account_id" :value="form.account_id">

                        {{-- Lock Overlay --}}
                        <div class="relative" :class="form.debt_amount <= 0 ? 'opacity-60 grayscale cursor-not-allowed' : ''">
                            
                            <div x-show="form.debt_amount <= 0" class="absolute inset-0 z-10" title="تکایە سەرەتا بڕی قەرز بنووسە (Enter debt first)"></div>

                            <div @click="if(form.debt_amount > 0) { searchOpen = !searchOpen; if(searchOpen) $nextTick(() => $refs.searchInput.focus()) }" 
                                 class="w-full flex items-center justify-between border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white shadow-sm py-3 px-4 font-bold transition-colors"
                                 :class="form.debt_amount > 0 ? 'cursor-pointer border hover:border-indigo-300' : 'border border-dashed bg-slate-50'">
                                <span x-text="form.account_id ? accountsData.find(a => a.id == form.account_id)?.name : '{{ __('expense.select') ?? '- دیاری بکە -' }}'" :class="form.account_id ? 'text-indigo-700' : 'text-slate-400'"></span>
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>

                            {{-- Search List --}}
                            <div x-show="searchOpen" @click.outside="searchOpen = false" x-transition.opacity class="absolute z-50 w-full mt-2 bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden" style="display: none;">
                                <div class="p-2 border-b border-slate-100 bg-slate-50">
                                    <input x-ref="searchInput" type="text" x-model="searchAcc" class="w-full text-xs rounded-lg border-slate-300 py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500" placeholder="{{ __('expense.search_account') ?? 'گەڕان بۆ هەژمار...' }}">
                                </div>
                                <ul class="max-h-56 overflow-y-auto custom-scrollbar">
                                    <li @click="form.account_id = ''; searchOpen = false; searchAcc = ''" class="py-2.5 px-4 text-xs hover:bg-slate-100 cursor-pointer text-slate-500">{{ __('expense.select') ?? '- دیاری بکە -' }}</li>
                                    <template x-for="acc in accountsData.filter(a => a.name.toLowerCase().includes(searchAcc.toLowerCase()))" :key="acc.id">
                                        <li @click="form.account_id = acc.id; searchOpen = false; searchAcc = ''" 
                                            class="py-2.5 px-4 text-xs hover:bg-indigo-50 cursor-pointer font-bold border-b border-slate-50 last:border-0" 
                                            :class="form.account_id == acc.id ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700'"
                                            x-text="acc.name">
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                        
                        <p x-show="form.debt_amount <= 0" class="text-[10px] font-bold text-rose-500 mt-2">تکایە سەرەتا بڕی قەرز بنووسە بۆ هەڵبژاردنی هەژمار.</p>

                        {{-- INTERACTIVE ACCOUNT BALANCES CARD --}}
                        <div x-show="form.account_id && form.debt_amount > 0" class="mt-4 bg-indigo-50/60 rounded-2xl p-4 border border-indigo-100" x-transition>
                            <h4 class="text-xs font-bold text-indigo-800 mb-3 flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                {{ __('expense.account_balances') ?? 'باڵانسی هەژمار (دیاریکردنی دراو)' }}
                            </h4>
                            <div class="space-y-2">
                                <template x-for="curId in getSelectedAccountCurrencies()" :key="curId">
                                    <div @click="form.currency_id = curId" 
                                         class="flex justify-between items-center px-3 py-2.5 rounded-xl cursor-pointer transition-all border"
                                         :class="form.currency_id == curId ? 'border-indigo-500 bg-white ring-1 ring-indigo-500 shadow-md transform scale-[1.02]' : 'border-indigo-100 bg-white/70 shadow-sm hover:border-indigo-300 hover:bg-white'">
                                        
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-4 h-4 rounded-full border flex items-center justify-center transition-colors"
                                                 :class="form.currency_id == curId ? 'border-indigo-600 bg-indigo-600' : 'border-slate-300 bg-white'">
                                                <div x-show="form.currency_id == curId" class="w-1.5 h-1.5 bg-white rounded-full"></div>
                                            </div>
                                            <span class="text-xs font-bold" :class="form.currency_id == curId ? 'text-indigo-800' : 'text-slate-500'" x-text="currenciesData[curId].type"></span>
                                        </div>
                                        
                                        <span class="text-sm font-black" 
                                              :class="getAccountBalance(curId) < 0 ? 'text-red-600' : (form.currency_id == curId ? 'text-indigo-800' : 'text-indigo-600')" 
                                              dir="ltr" 
                                              x-text="getAccountBalance(curId).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})">
                                        </span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 my-4"></div>

                    {{-- Live Calculations --}}
                    <div class="space-y-4">
                        <div class="flex justify-between items-center bg-slate-50 py-3 px-4 rounded-xl border border-slate-100 shadow-sm">
                            <span class="text-xs font-bold text-slate-500">{{ __('expense.total_spending') ?? 'کۆی خەرجی' }}</span>
                            <span class="text-xl font-black text-slate-800" x-text="totalSpending.toLocaleString()"></span>
                        </div>
                        <div class="flex justify-between items-center py-3 px-4 rounded-xl border shadow-sm transition-colors" :class="remainingAmount == 0 ? 'border-emerald-200 bg-emerald-50' : 'border-red-200 bg-red-50'">
                            <span class="text-xs font-bold" :class="remainingAmount == 0 ? 'text-emerald-700' : 'text-red-700'">{{ __('expense.remaining_amount') ?? 'ماوەی خەرجی' }}</span>
                            <span class="text-xl font-black" :class="remainingAmount == 0 ? 'text-emerald-700' : 'text-red-700'" x-text="remainingAmount.toLocaleString()"></span>
                        </div>
                    </div>

                    {{-- Cashbox (Only shows if Cash Amount > 0) --}}
                    <div x-show="form.cash_amount > 0" x-transition.opacity class="bg-emerald-50 p-4 mt-6 rounded-2xl border border-emerald-200 shadow-sm relative overflow-hidden">
                        <div class="absolute right-0 top-0 bottom-0 w-1.5 bg-emerald-400"></div>
                        <label class="block text-xs font-bold text-emerald-800 mb-2">{{ __('expense.cashbox_name') ?? 'قاسە' }} *</label>
                        <select name="cashbox_id" x-model="form.cashbox_id" class="w-full border-emerald-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 text-sm font-bold bg-white py-2.5 shadow-sm" :required="form.cash_amount > 0">
                            <option value="">{{ __('expense.select') ?? '- دیاری بکە -' }}</option>
                            @foreach($cashboxes as $box)
                                <option value="{{ $box->id }}">{{ $box->name }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>

            </div>
        </form>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('expenseForm', () => ({
                
                accountsData: {!! json_encode($accounts->map(function($acc) use ($allBalances) {
                    $supported = is_string($acc->supported_currency_ids) ? json_decode($acc->supported_currency_ids, true) : $acc->supported_currency_ids;
                    $supported = is_array($supported) ? $supported : [];
                    $balances = [];
                    foreach($supported as $curId) {
                        $record = $allBalances->where('account_id', $acc->id)->where('currency_id', $curId)->first();
                        $balances[$curId] = $record ? (float) $record->balance : 0;
                    }
                    return ['id' => $acc->id, 'name' => $acc->name, 'supported' => $supported, 'balances' => $balances];
                })) !!},
                
                currenciesData: {!! json_encode($currencies->keyBy('id')->map(function($c) { return ['type' => $c->currency_type, 'rate' => $c->price_single ?? 1]; })) !!},
                currenciesList: {!! json_encode($currencies->map(function($c) { return ['id' => $c->id, 'type' => $c->currency_type]; })) !!},
                categoriesList: {!! json_encode($categories->map(function($c) { return ['id' => $c->id, 'name' => $c->name]; })) !!},

                form: {
                    account_id: '', cashbox_id: '', 
                    currency_id: '{{ $currencies->first()->id ?? "" }}', 
                    exchange_rate: 1, 
                    date: new Date().toISOString().split('T')[0],
                    discount: 0, cash_amount: 0, debt_amount: 0,
                    manual_voucher: '', note: ''
                },
                
                // 🟢 تەنها یەک ڕیز دروست دەکات لە سەرەتادا لەبری ٥ ڕیز
                items: [ { category_id: '', category_name: '', price: 0, note: '' } ],

                init() {
                    if(this.form.currency_id && this.currenciesData[this.form.currency_id]) {
                        this.form.exchange_rate = this.currenciesData[this.form.currency_id].rate || 1;
                    }

                    this.$watch('form.currency_id', (newId) => {
                        if(newId && this.currenciesData[newId]) {
                            this.form.exchange_rate = this.currenciesData[newId].rate || 1;
                        }
                    });

                    this.$watch('form.debt_amount', (val) => {
                        if (Number(val) <= 0) {
                            this.form.account_id = '';
                        }
                    });
                },

                get totalSpending() {
                    return this.items.reduce((total, item) => total + (Number(item.price) || 0), 0);
                },
                
                get remainingAmount() {
                    let total = this.totalSpending;
                    let discount = Number(this.form.discount) || 0;
                    let cash = Number(this.form.cash_amount) || 0;
                    let debt = Number(this.form.debt_amount) || 0;
                    return Math.round((total - discount - cash - debt) * 100) / 100;
                },

                getSelectedAccountCurrencies() {
                    if (!this.form.account_id) return [];
                    let acc = this.accountsData.find(a => a.id == this.form.account_id);
                    return (acc && acc.supported) ? acc.supported : [];
                },

                getAccountBalance(curId) {
                    if (!this.form.account_id) return 0;
                    let acc = this.accountsData.find(a => a.id == this.form.account_id);
                    if (!acc || !acc.balances || acc.balances[curId] === undefined) return 0;
                    return Number(acc.balances[curId]);
                },

                addItem() {
                    this.items.push({ category_id: '', category_name: '', price: 0, note: '' });
                },
                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                }
            }));
        });
    </script>
</x-app-layout>