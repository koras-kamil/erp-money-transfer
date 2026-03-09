<x-app-layout>
    <div class="py-8 w-full max-w-6xl mx-auto px-4" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">
        
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="bg-indigo-100 p-3 rounded-xl shadow-inner">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </div>
                <h2 class="text-2xl font-black text-slate-800">{{ __('cashboxreport.cashbox_transfer') }}</h2>
            </div>
            <a href="{{ route('accountant.transfers.index') }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl shadow-sm hover:bg-slate-50 hover:text-indigo-600 transition-colors">
                {{ __('cashboxreport.back') }}
            </a>
        </div>

        @if($errors->any())
            <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 font-bold text-sm border border-red-200 shadow-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        {{-- 🟢 ALPINE JS MANAGER --}}
        <form x-data="transferManager()" x-init="init()" action="{{ route('accountant.transfers.store') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-0 relative border-b border-slate-200">
                
                {{-- Arrow Icon pointing from Right to Left --}}
                <div class="hidden md:flex absolute top-[15%] left-1/2 -translate-x-1/2 -translate-y-1/2 w-12 h-12 bg-white border border-slate-200 rounded-full items-center justify-center shadow-lg z-10">
                    <svg class="w-6 h-6 text-indigo-500 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </div>

                {{-- RIGHT SIDE: SENDER --}}
                <div class="p-6 bg-slate-50/50 ltr:border-r rtl:border-l border-slate-200 flex flex-col h-full">
                    <div class="flex items-center gap-2 mb-6 border-b border-slate-200 pb-3">
                        <span class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center font-black shadow-inner">-</span>
                        <h3 class="text-lg font-black text-slate-700">{{ __('cashboxreport.sender_cashbox') }}</h3>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1.5">{{ __('cashboxreport.select_cashbox') }}</label>
                            <select name="from_cashbox_id" x-model="fromBox" required class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-bold focus:ring-rose-500 focus:border-rose-500 shadow-sm transition-all">
                                <option value="">...</option>
                                @foreach($cashboxes as $box) <option value="{{ $box->id }}">{{ $box->name }}</option> @endforeach
                            </select>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1.5">{{ __('cashboxreport.currency') }}</label>
                                <select name="from_currency_id" x-model="fromCurr" @change="checkCurrencies()" required class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-bold focus:ring-rose-500 focus:border-rose-500 shadow-sm transition-all">
                                    <option value="">...</option>
                                    @foreach($currencies as $curr) <option value="{{ $curr->id }}">{{ $curr->currency_type }}</option> @endforeach
                                </select>
                                
                                <div x-show="fromBalance !== null" x-transition class="mt-2 flex items-center justify-between bg-white border border-slate-200 rounded-lg px-3 py-1.5 shadow-sm">
                                    <span class="text-[10px] font-bold text-slate-400">{{ __('cashboxreport.balance') }}:</span>
                                    <span class="font-mono text-sm font-black" :class="fromBalance < 0 ? 'text-rose-600' : 'text-emerald-600'" x-text="formatMoney(fromBalance)"></span>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-black text-rose-600 mb-1.5">{{ __('cashboxreport.amount_sent') }}</label>
                                <input type="text" autocomplete="off" name="amount_sent" x-model="amountSent" required class="w-full bg-white border border-rose-300 rounded-xl px-4 py-2.5 text-lg font-mono font-black text-rose-600 focus:ring-rose-500 focus:border-rose-500 placeholder-slate-300 shadow-sm transition-all">
                            </div>
                        </div>

                        {{-- 🟢 NEW GIVER DETAILS (From Excel) --}}
                        <div class="grid grid-cols-2 gap-4 border-t border-slate-200 pt-5 mt-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1.5">{{ __('cashboxreport.giver_name') }}</label>
                                <input type="text" autocomplete="off" name="giver_name" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-bold focus:ring-rose-500 focus:border-rose-500 shadow-sm transition-all" placeholder="...">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1.5">{{ __('cashboxreport.giver_phone') }}</label>
                                <input type="text" autocomplete="off" name="giver_phone" dir="ltr" class="w-full text-right bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-bold focus:ring-rose-500 focus:border-rose-500 shadow-sm transition-all" placeholder="07...">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- LEFT SIDE: RECEIVER --}}
                <div class="p-6 bg-indigo-50/20 flex flex-col h-full">
                    <div class="flex items-center gap-2 mb-6 border-b border-indigo-100 pb-3">
                        <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center font-black shadow-inner">+</span>
                        <h3 class="text-lg font-black text-indigo-900">{{ __('cashboxreport.receiver_cashbox') }}</h3>
                    </div>

                    <div class="space-y-5 flex-1">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1.5">{{ __('cashboxreport.select_cashbox') }}</label>
                            <select name="to_cashbox_id" x-model="toBox" required class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-bold focus:ring-emerald-500 focus:border-emerald-500 shadow-sm transition-all">
                                <option value="">...</option>
                                @foreach($cashboxes as $box) <option value="{{ $box->id }}">{{ $box->name }}</option> @endforeach
                            </select>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1.5">{{ __('cashboxreport.currency') }}</label>
                                <select name="to_currency_id" x-model="toCurr" @change="checkCurrencies()" required class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-bold focus:ring-emerald-500 focus:border-emerald-500 shadow-sm transition-all">
                                    <option value="">...</option>
                                    @foreach($currencies as $curr) <option value="{{ $curr->id }}">{{ $curr->currency_type }}</option> @endforeach
                                </select>

                                <div x-show="toBalance !== null" x-transition class="mt-2 flex items-center justify-between bg-white border border-slate-200 rounded-lg px-3 py-1.5 shadow-sm">
                                    <span class="text-[10px] font-bold text-slate-400">{{ __('cashboxreport.balance') }}:</span>
                                    <span class="font-mono text-sm font-black" :class="toBalance < 0 ? 'text-rose-600' : 'text-emerald-600'" x-text="formatMoney(toBalance)"></span>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-black mb-1.5" :class="isSameCurrency ? 'text-slate-400' : 'text-emerald-600'">{{ __('cashboxreport.amount_received') }}</label>
                                <input type="text" autocomplete="off" name="amount_received" x-model="amountReceived" required 
                                       class="w-full rounded-xl px-4 py-2.5 text-lg font-mono font-black focus:ring-emerald-500 focus:border-emerald-500 shadow-sm transition-all"
                                       :class="isSameCurrency ? 'bg-slate-100 border-slate-200 text-slate-500 cursor-not-allowed' : 'bg-white border-emerald-300 text-emerald-600'"
                                       :readonly="isSameCurrency">
                            </div>
                        </div>

                        {{-- 🟢 NEW RECEIVER DETAILS (From Excel) --}}
                        <div class="grid grid-cols-2 gap-4 border-t border-indigo-100 pt-5 mt-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1.5">{{ __('cashboxreport.receiver_name') }}</label>
                                <input type="text" autocomplete="off" name="receiver_name" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-bold focus:ring-emerald-500 focus:border-emerald-500 shadow-sm transition-all" placeholder="...">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1.5">{{ __('cashboxreport.receiver_phone') }}</label>
                                <input type="text" autocomplete="off" name="receiver_phone" dir="ltr" class="w-full text-right bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-bold focus:ring-emerald-500 focus:border-emerald-500 shadow-sm transition-all" placeholder="07...">
                            </div>
                        </div>

                        {{-- EXCHANGE RATE (Under Receiver) --}}
                        <div class="mt-4 pt-4 border-t border-indigo-100">
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-[11px] font-bold" :class="isSameCurrency ? 'text-slate-400' : 'text-indigo-800'">
                                    {{ __('cashboxreport.ex_rate') }}
                                </label>
                                <button type="button" x-show="!isSameCurrency" @click="toggleOperation()" class="px-2 py-0.5 bg-white hover:bg-indigo-50 text-indigo-600 font-black rounded text-[10px] transition-colors border border-indigo-200 shadow-sm" title="Switch between Multiply and Divide">
                                    <span x-text="calcOp === '*' ? '{{ __('cashboxreport.multiply') }}' : '{{ __('cashboxreport.divide') }}'"></span>
                                </button>
                            </div>

                            <input type="text" autocomplete="off" name="exchange_rate" x-model="exchangeRate" 
                                   class="w-full rounded-xl px-4 py-2.5 text-sm font-mono focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition-all"
                                   :class="isSameCurrency ? 'bg-slate-100 border-slate-200 text-slate-400 cursor-not-allowed opacity-60' : 'bg-white border-indigo-300 text-indigo-900 font-bold'"
                                   :readonly="isSameCurrency"
                                   :required="!isSameCurrency">
                        </div>

                    </div>
                </div>

            </div>

            {{-- BOTTOM INFO SECTION --}}
            <div class="p-6 bg-slate-50/50">
                <div class="flex items-center gap-2 mb-6 border-b border-slate-200 pb-3">
                    <span class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center shadow-inner">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </span>
                    <h3 class="text-lg font-black text-slate-700">{{ __('cashboxreport.income_expense_details') }}</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 mb-1.5">{{ __('cashboxreport.doc_id') }}</label>
                        <input type="text" autocomplete="off" name="statement_id" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-mono focus:ring-orange-500 focus:border-orange-500 shadow-sm transition-all">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 mb-1.5">{{ __('cashboxreport.date') }}</label>
                        <input type="datetime-local" name="manual_date" value="{{ now()->format('Y-m-d\TH:i') }}" required class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-bold focus:ring-orange-500 focus:border-orange-500 shadow-sm transition-all">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 mb-1.5">{{ __('cashboxreport.note') }}</label>
                        <input type="text" autocomplete="off" name="note" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-orange-500 focus:border-orange-500 shadow-sm transition-all">
                    </div>
                </div>
            </div>

            {{-- SUBMIT BUTTON --}}
            <div class="p-4 bg-white border-t border-slate-200 flex justify-end">
                <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-xl shadow-md shadow-indigo-500/20 transition-all flex items-center gap-2 group">
                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ __('cashboxreport.save_transfer') }}
                </button>
            </div>
        </form>

    </div>

    {{-- Alpine JS Script --}}
    <script>
        function transferManager() {
            return {
                balances: @json($liveBalances),
                currencies: @json($currencies),
                fromBox: '',
                fromCurr: '',
                toBox: '',
                toCurr: '',
                amountSent: '',
                amountReceived: '',
                exchangeRate: '',
                calcOp: '*', // Default is Multiply

                init() {
                    // 🟢 NEW: Instantly inject commas as the user types
                    this.$watch('amountSent', (val) => {
                        let formatted = this.formatNumberInput(val);
                        if (val !== formatted) this.amountSent = formatted;
                        this.calculate();
                    });

                    // 🟢 NEW: Instantly inject commas for exchange rate
                    this.$watch('exchangeRate', (val) => {
                        let formatted = this.formatNumberInput(val);
                        if (val !== formatted) this.exchangeRate = formatted;
                        this.calculate();
                    });
                },

                get isSameCurrency() {
                    return this.fromCurr !== '' && this.toCurr !== '' && this.fromCurr === this.toCurr;
                },

                checkCurrencies() {
                    if (this.isSameCurrency) {
                        this.amountReceived = this.amountSent;
                        this.exchangeRate = '1';
                        this.calcOp = '*';
                    } else {
                        let cFrom = this.currencies.find(c => c.id == this.fromCurr);
                        let cTo = this.currencies.find(c => c.id == this.toCurr);

                        if (cFrom && cTo) {
                            let pFrom = parseFloat(cFrom.price_single || 1);
                            let pTo = parseFloat(cTo.price_single || 1);
                            
                            if (pFrom < pTo) {
                                this.exchangeRate = this.formatNumberInput((pTo / pFrom).toFixed(4).replace(/\.?0+$/, ''));
                                this.calcOp = '*';
                            } 
                            else if (pFrom > pTo) {
                                this.exchangeRate = this.formatNumberInput((pFrom / pTo).toFixed(4).replace(/\.?0+$/, ''));
                                this.calcOp = '/';
                            } 
                            else {
                                this.exchangeRate = '1';
                                this.calcOp = '*';
                            }
                        } else {
                            this.exchangeRate = '';
                            this.amountReceived = '';
                        }
                    }
                    this.calculate();
                },

                toggleOperation() {
                    this.calcOp = this.calcOp === '*' ? '/' : '*';
                    this.calculate();
                },

                calculate() {
                    if (this.isSameCurrency) {
                        this.amountReceived = this.amountSent;
                        return;
                    }

                    let sentStr = String(this.amountSent || '').replace(/,/g, '');
                    let rateStr = String(this.exchangeRate || '').replace(/,/g, '');

                    let sent = parseFloat(sentStr) || 0;
                    let rate = parseFloat(rateStr) || 0;

                    if (sent > 0 && rate > 0) {
                        let res = this.calcOp === '*' ? (sent * rate) : (sent / rate);
                        // Apply commas to the result!
                        this.amountReceived = this.formatNumberInput(res.toFixed(2).replace(/\.00$/, ''));
                    } else {
                        this.amountReceived = '';
                    }
                },

                // 🟢 NEW HELPER: Handles typing logic to strip letters and add commas
                formatNumberInput(val) {
                    if (!val) return '';
                    // Remove anything that isn't a number or a dot
                    let str = String(val).replace(/[^0-9.]/g, '');
                    // Split at the decimal point to avoid comma formatting the decimals
                    let parts = str.split('.');
                    // Add commas using Regex to the integer part
                    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                    // Ensure only one decimal point
                    if (parts.length > 2) parts.pop();
                    return parts.join('.');
                },

                get fromBalance() {
                    if (this.fromBox && this.fromCurr && this.balances[this.fromBox]) {
                        return this.balances[this.fromBox][this.fromCurr] ?? 0;
                    }
                    return null;
                },

                get toBalance() {
                    if (this.toBox && this.toCurr && this.balances[this.toBox]) {
                        return this.balances[this.toBox][this.toCurr] ?? 0;
                    }
                    return null;
                },

                formatMoney(val) {
                    if (val === null || isNaN(val)) return '0.00';
                    return parseFloat(val).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2});
                }
            }
        }
    </script>
</x-app-layout>