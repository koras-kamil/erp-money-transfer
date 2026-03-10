<x-app-layout>
    <div class="py-8 w-full max-w-6xl mx-auto px-4" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">
        
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="bg-indigo-100 p-3 rounded-xl shadow-inner">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </div>
                <h2 class="text-2xl font-black text-slate-800">{{ __('accounttransfer.account_transfer') }}</h2>
            </div>
            <a href="{{ route('account_transfers.index') }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl shadow-sm hover:bg-slate-50 hover:text-indigo-600 transition-colors">
                {{ __('accounttransfer.back') }}
            </a>
        </div>

        @if($errors->any())
            <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 font-bold text-sm border border-red-200 shadow-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form x-data="hawalaManager()" x-init="init()" action="{{ route('account_transfers.store') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-0 relative border-b border-slate-200">
                
                <div class="hidden md:flex absolute top-[15%] left-1/2 -translate-x-1/2 -translate-y-1/2 w-12 h-12 bg-white border border-slate-200 rounded-full items-center justify-center shadow-lg z-10">
                    <svg class="w-6 h-6 text-indigo-500 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </div>

                {{-- RIGHT SIDE: SENDER ACCOUNT (هەژماری پێدەر) --}}
                <div class="p-6 bg-slate-50/50 ltr:border-r rtl:border-l border-slate-200 flex flex-col h-full">
                    <div class="flex items-center gap-2 mb-6 border-b border-slate-200 pb-3">
                        <span class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center font-black shadow-inner">-</span>
                        <h3 class="text-lg font-black text-slate-700">{{ __('accounttransfer.sender_account') }}</h3>
                    </div>

                    <div class="space-y-5">
                        {{-- Account Selection & Mini Balance Table --}}
                        <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm">
                            <label class="block text-xs font-bold text-slate-500 mb-1.5">{{ __('accounttransfer.select_account') }}</label>
                            <select name="from_account_id" x-model="fromAcc" required class="w-full bg-slate-50 border border-slate-300 rounded-lg px-4 py-2 text-sm font-bold focus:ring-rose-500 focus:border-rose-500 mb-3">
                                <option value="">...</option>
                                @foreach($accounts as $acc) <option value="{{ $acc->id }}">{{ $acc->name }}</option> @endforeach
                            </select>

                            {{-- 🟢 THE MINI BALANCE TABLE (Like Excel) --}}
                            <div x-show="fromAcc" x-transition class="overflow-hidden rounded-lg border border-slate-200">
                                <table class="w-full text-xs text-center whitespace-nowrap">
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="curr in currencies" :key="curr.id">
                                            <tr :class="fromCurr == curr.id ? 'bg-indigo-600 text-white' : 'bg-slate-50 text-slate-600'">
                                                <td class="px-3 py-1.5 font-bold border-r border-slate-200 w-1/3" :class="fromCurr == curr.id ? 'border-indigo-500' : ''" x-text="curr.currency_type"></td>
                                                <td class="px-3 py-1.5 font-mono font-black w-2/3" x-text="formatMoney(balances[fromAcc]?.[curr.id] || 0)"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1.5">{{ __('accounttransfer.currency') }}</label>
                                <select name="from_currency_id" x-model="fromCurr" @change="checkCurrencies()" required class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-bold focus:ring-rose-500 focus:border-rose-500 shadow-sm transition-all">
                                    <option value="">...</option>
                                    <template x-for="curr in currencies" :key="curr.id">
                                        <option :value="curr.id" x-text="curr.currency_type"></option>
                                    </template>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-black text-rose-600 mb-1.5">{{ __('accounttransfer.amount_sent') }}</label>
                                <input type="text" autocomplete="off" name="amount_sent" x-model="amountSent" required class="w-full bg-white border border-rose-300 rounded-xl px-4 py-2.5 text-lg font-mono font-black text-rose-600 focus:ring-rose-500 focus:border-rose-500 placeholder-slate-300 shadow-sm transition-all">
                            </div>
                        </div>

                        {{-- GIVER DETAILS (From Excel) --}}
                        <div class="border-t border-slate-200 pt-5 mt-2">
                            <label class="block text-xs font-bold text-slate-500 mb-1.5">{{ __('accounttransfer.giver_name_hawala') }}</label>
                            <input type="text" autocomplete="off" name="giver_name" class="w-full bg-yellow-50 border border-yellow-300 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-800 focus:ring-rose-500 focus:border-rose-500 shadow-sm transition-all" placeholder="...">
                        </div>
                    </div>
                </div>

                {{-- LEFT SIDE: RECEIVER ACCOUNT (هەژماری وەرگر) --}}
                <div class="p-6 bg-indigo-50/20 flex flex-col h-full">
                    <div class="flex items-center gap-2 mb-6 border-b border-indigo-100 pb-3">
                        <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center font-black shadow-inner">+</span>
                        <h3 class="text-lg font-black text-indigo-900">{{ __('accounttransfer.receiver_account') }}</h3>
                    </div>

                    <div class="space-y-5 flex-1">
                        {{-- Account Selection & Mini Balance Table --}}
                        <div class="bg-white p-3 rounded-xl border border-indigo-100 shadow-sm">
                            <label class="block text-xs font-bold text-slate-500 mb-1.5">{{ __('accounttransfer.select_account') }}</label>
                            <select name="to_account_id" x-model="toAcc" required class="w-full bg-slate-50 border border-slate-300 rounded-lg px-4 py-2 text-sm font-bold focus:ring-emerald-500 focus:border-emerald-500 mb-3">
                                <option value="">...</option>
                                @foreach($accounts as $acc) <option value="{{ $acc->id }}">{{ $acc->name }}</option> @endforeach
                            </select>

                            {{-- 🟢 THE MINI BALANCE TABLE --}}
                            <div x-show="toAcc" x-transition class="overflow-hidden rounded-lg border border-slate-200">
                                <table class="w-full text-xs text-center whitespace-nowrap">
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="curr in currencies" :key="curr.id">
                                            <tr :class="toCurr == curr.id ? 'bg-indigo-600 text-white' : 'bg-slate-50 text-slate-600'">
                                                <td class="px-3 py-1.5 font-bold border-r border-slate-200 w-1/3" :class="toCurr == curr.id ? 'border-indigo-500' : ''" x-text="curr.currency_type"></td>
                                                <td class="px-3 py-1.5 font-mono font-black w-2/3" x-text="formatMoney(balances[toAcc]?.[curr.id] || 0)"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1.5">{{ __('accounttransfer.currency') }}</label>
                                <select name="to_currency_id" x-model="toCurr" @change="checkCurrencies()" required class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-bold focus:ring-emerald-500 focus:border-emerald-500 shadow-sm transition-all">
                                    <option value="">...</option>
                                    <template x-for="curr in currencies" :key="curr.id">
                                        <option :value="curr.id" x-text="curr.currency_type"></option>
                                    </template>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-black mb-1.5" :class="isSameCurrency ? 'text-slate-400' : 'text-emerald-600'">{{ __('accounttransfer.amount_received') }}</label>
                                <input type="text" autocomplete="off" name="amount_received" x-model="amountReceived" required 
                                       class="w-full rounded-xl px-4 py-2.5 text-lg font-mono font-black focus:ring-emerald-500 focus:border-emerald-500 shadow-sm transition-all"
                                       :class="isSameCurrency ? 'bg-slate-100 border-slate-200 text-slate-500 cursor-not-allowed' : 'bg-white border-emerald-300 text-emerald-600'"
                                       :readonly="isSameCurrency">
                            </div>
                        </div>

                        {{-- RECEIVER DETAILS (From Excel) --}}
                        <div class="border-t border-indigo-100 pt-5 mt-2">
                            <label class="block text-xs font-bold text-slate-500 mb-1.5">{{ __('accounttransfer.receiver_name_hawala') }}</label>
                            <input type="text" autocomplete="off" name="receiver_name" class="w-full bg-yellow-50 border border-yellow-300 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-800 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm transition-all" placeholder="...">
                        </div>

                        {{-- EXCHANGE RATE --}}
                        <div class="mt-4 pt-4 border-t border-indigo-100">
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-[11px] font-bold" :class="isSameCurrency ? 'text-slate-400' : 'text-indigo-800'">
                                    {{ __('accounttransfer.ex_rate') }}
                                </label>
                                <button type="button" x-show="!isSameCurrency" @click="toggleOperation()" class="px-2 py-0.5 bg-white hover:bg-indigo-50 text-indigo-600 font-black rounded text-[10px] transition-colors border border-indigo-200 shadow-sm" title="Switch between Multiply and Divide">
                                    <span x-text="calcOp === '*' ? '{{ __('accounttransfer.multiply') }}' : '{{ __('accounttransfer.divide') }}'"></span>
                                </button>
                            </div>

                            <input type="text" autocomplete="off" name="exchange_rate" x-model="exchangeRate" 
                                   class="w-full rounded-xl px-4 py-2.5 text-sm font-mono focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition-all"
                                   :class="isSameCurrency ? 'bg-slate-100 border-slate-200 text-slate-400 cursor-not-allowed opacity-60' : 'bg-indigo-100 border-indigo-300 text-indigo-900 font-bold'"
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
                    <h3 class="text-lg font-black text-slate-700">{{ __('accounttransfer.income_expense_details') }}</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 mb-1.5">{{ __('accounttransfer.doc_id') }}</label>
                        <input type="text" autocomplete="off" name="statement_id" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-mono focus:ring-orange-500 focus:border-orange-500 shadow-sm transition-all">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 mb-1.5">{{ __('accounttransfer.date') }}</label>
                        <input type="datetime-local" name="manual_date" value="{{ now()->format('Y-m-d\TH:i') }}" required class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-bold focus:ring-orange-500 focus:border-orange-500 shadow-sm transition-all">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 mb-1.5">{{ __('accounttransfer.note') }}</label>
                        <input type="text" autocomplete="off" name="note" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-orange-500 focus:border-orange-500 shadow-sm transition-all">
                    </div>
                </div>
            </div>

            {{-- SUBMIT BUTTON --}}
            <div class="p-4 bg-white border-t border-slate-200 flex justify-end">
                <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-xl shadow-md shadow-indigo-500/20 transition-all flex items-center gap-2 group">
                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ __('accounttransfer.save_transfer') }}
                </button>
            </div>
        </form>
    </div>

    {{-- Alpine JS Script --}}
    <script>
        function hawalaManager() {
            return {
                balances: @json($liveBalances),
                currencies: @json($currencies),
                fromAcc: '',
                fromCurr: '',
                toAcc: '',
                toCurr: '',
                amountSent: '',
                amountReceived: '',
                exchangeRate: '',
                calcOp: '*', 

                init() {
                    this.$watch('amountSent', (val) => {
                        let formatted = this.formatNumberInput(val);
                        if (val !== formatted) this.amountSent = formatted;
                        this.calculate();
                    });

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
                        this.amountReceived = this.formatNumberInput(res.toFixed(2).replace(/\.00$/, ''));
                    } else {
                        this.amountReceived = '';
                    }
                },

                formatNumberInput(val) {
                    if (!val) return '';
                    let str = String(val).replace(/[^0-9.-]/g, '');
                    let parts = str.split('.');
                    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                    if (parts.length > 2) parts.pop();
                    return parts.join('.');
                },

                formatMoney(val) {
                    if (val === null || isNaN(val)) return '0';
                    return parseFloat(val).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2});
                }
            }
        }
    </script>
</x-app-layout>