<x-app-layout>
    <style>
        .table-container::-webkit-scrollbar { height: 6px; }
        .table-container::-webkit-scrollbar-track { background: #f1f1f1; }
        .table-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        @media print { .no-print { display: none !important; } .overflow-x-auto { overflow: visible !important; } table { width: 100% !important; } }
    </style>

    <div x-data="reportManager()" class="py-6 w-full min-w-0 bg-white min-h-screen" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">
        
        {{-- TOOLBAR WITH TABS --}}
        <div class="mx-4 mb-6 flex flex-col md:flex-row justify-between items-center gap-4 no-print">
            <div class="flex flex-wrap items-center gap-3">
                
                {{-- 🟢 NAVIGATION TABS --}}
                <div class="bg-slate-100 p-1 rounded-lg flex items-center shadow-inner gap-1">
                    {{-- Active Tab (Current Page) --}}
                    <span class="px-5 py-2 text-sm font-bold rounded-md bg-white text-indigo-600 shadow-sm transition cursor-default">
                        {{ app()->getLocale() == 'ku' ? 'ڕاپۆرتی قاصەکان (ڕاستەوخۆ)' : 'Live Cashbox Report' }}
                    </span>
                    {{-- Inactive Tab (Link to Transfers) --}}
                    <a href="{{ route('accountant.transfers.index') }}" class="px-5 py-2 text-sm font-bold rounded-md text-slate-500 hover:text-indigo-600 hover:bg-slate-200/50 transition">
                        {{ app()->getLocale() == 'ku' ? 'مێژووی گواستنەوەکان' : 'Transfers History' }}
                    </a>
                </div>

                {{-- Base Currency Badge --}}
                <span class="text-xs text-slate-400 font-mono bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                    {{ app()->getLocale() == 'ku' ? 'پارەی سەرەکی' : 'Base Currency' }}: <span class="font-bold text-indigo-500">{{ $baseCurrency->currency_type ?? 'N/A' }}</span>
                </span>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                {{-- 🟢 QUICK "NEW TRANSFER" BUTTON --}}
                <a href="{{ route('accountant.transfers.create') }}" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-bold shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    {{ app()->getLocale() == 'ku' ? 'گواستنەوەی نوێ' : 'New Transfer' }}
                </a>

                <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-lg text-sm font-bold shadow-sm hover:bg-slate-50 hover:text-indigo-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    {{ app()->getLocale() == 'ku' ? 'چاپکردن' : 'Print' }}
                </button>
            </div>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="relative w-full overflow-x-auto table-container bg-white shadow-sm rounded-lg border border-slate-200 mx-4 pb-10">
            <table class="w-full text-sm text-center text-slate-500 whitespace-nowrap border-separate border-spacing-0">
                
                {{-- HEADERS --}}
                <thead class="text-xs text-slate-500 uppercase bg-slate-50/80 border-b border-slate-100 tracking-wide">
                    <tr>
                        <th class="px-4 py-4 text-center align-middle font-bold border-b border-slate-100 w-12">#</th>
                        <th class="px-4 py-4 text-center align-middle font-bold border-b border-slate-100 min-w-[150px]">{{ app()->getLocale() == 'ku' ? 'ناوی قاصە' : 'Cashbox Name' }}</th>
                        
                        {{-- Dynamic Currency Columns --}}
                        @foreach($currencies as $curr)
                            <th class="px-4 py-4 text-center align-middle font-bold border-b border-slate-100 min-w-[120px]">{{ $curr->currency_type }}</th>
                        @endforeach

                        {{-- Base Currency Highlight Header --}}
                        <th class="px-4 py-4 text-center align-middle font-black text-indigo-700 bg-indigo-50/50 border-b border-indigo-100 min-w-[150px]">
                            {{ app()->getLocale() == 'ku' ? 'کۆی گشتی بە پارەی سەرەکی' : 'Base Currency Total' }}
                        </th>
                        
                        <th class="px-4 py-4 text-center align-middle font-bold border-b border-slate-100 min-w-[120px]">{{ app()->getLocale() == 'ku' ? 'بەکارهێنەر' : 'User' }}</th>
                        <th class="px-4 py-4 text-center align-middle font-bold border-b border-slate-100 min-w-[120px]">{{ app()->getLocale() == 'ku' ? 'لق' : 'Branch' }}</th>
                        <th class="px-4 py-4 text-center align-middle font-bold border-b border-slate-100 w-20 no-print">{{ app()->getLocale() == 'ku' ? 'بینین' : 'View' }}</th>
                    </tr>
                </thead>
                
                {{-- BODY --}}
                <tbody class="divide-y divide-slate-50 bg-white">
                    <template x-for="(box, index) in cashBoxes" :key="box.id">
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            
                            {{-- ID --}}
                            <td class="px-4 py-3 text-center align-middle font-mono text-slate-400 text-xs" x-text="index + 1"></td>
                            
                            {{-- Name --}}
                            <td class="px-4 py-3 text-center align-middle">
                                <span class="font-bold text-slate-700 text-sm" x-text="box.name"></span>
                            </td>
                            
                            {{-- Dynamic Currency Balances --}}
                            <template x-for="curr in currencies" :key="curr.id">
                                <td class="px-4 py-3 text-center align-middle">
                                    <div class="flex items-center justify-center w-full">
                                        <span class="font-mono text-sm px-2 py-1 rounded-md" 
                                              :class="box.balances['curr_'+curr.id] == 0 ? 'text-slate-400' : (box.balances['curr_'+curr.id] < 0 ? 'text-rose-600 bg-rose-50 font-bold' : 'text-emerald-600 bg-emerald-50 font-bold')"
                                              x-text="formatMoney(box.balances['curr_'+curr.id])">
                                        </span>
                                    </div>
                                </td>
                            </template>

                            {{-- Calculated Base Currency for this Row (Highlighted Column) --}}
                            <td class="px-4 py-3 text-center align-middle bg-indigo-50/30 group-hover:bg-indigo-50/60 transition-colors">
                                <div class="flex items-center justify-center w-full">
                                    <span class="font-mono font-black text-sm px-3 py-1 rounded border shadow-sm bg-white" 
                                          :class="calculateBaseTotalForRow(box) < 0 ? 'text-rose-600 border-rose-200' : 'text-indigo-700 border-indigo-100'"
                                          x-text="formatMoney(calculateBaseTotalForRow(box))">
                                    </span>
                                </div>
                            </td>

                            {{-- User & Branch --}}
                            <td class="px-4 py-3 text-center align-middle">
                                <span class="text-[10px] uppercase font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded" x-text="box.user_name"></span>
                            </td>
                            <td class="px-4 py-3 text-center align-middle">
                                <span class="text-xs text-slate-500 font-medium" x-text="box.branch_name"></span>
                            </td>
                            
                            {{-- EYE ICON (View Details) --}}
                            <td class="px-4 py-3 text-center align-middle no-print">
                                <div class="flex items-center justify-center w-full">
                                    <a :href="'/accountant/cashbox-reports/' + box.id" class="inline-flex items-center justify-center p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="بینین">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>

                {{-- FOOTER (GRAND TOTALS) --}}
                <tfoot class="bg-slate-50/80 border-t border-slate-100">
                    <tr>
                        <td colspan="2" class="px-4 py-5 text-center align-middle text-sm font-black text-slate-700 tracking-wide border-t border-slate-200">
                            {{ app()->getLocale() == 'ku' ? 'کۆی گشتی هەموو قاصەکان' : 'Total All Cashboxes' }}
                        </td>
                        
                        <template x-for="curr in currencies" :key="curr.id">
                            <td class="px-4 py-5 text-center align-middle border-t border-slate-200">
                                <span class="font-mono text-sm font-bold"
                                      :class="calculateColumnTotal(curr.id) < 0 ? 'text-rose-600' : 'text-slate-700'"
                                      x-text="formatMoney(calculateColumnTotal(curr.id))"></span>
                            </td>
                        </template>

                        <td class="px-4 py-5 text-center align-middle bg-indigo-50/50 border-t border-indigo-200">
                            <span class="font-mono text-base font-black" 
                                  :class="calculateGrandBaseTotal() < 0 ? 'text-rose-600' : 'text-indigo-700'"
                                  x-text="formatMoney(calculateGrandBaseTotal())"></span>
                        </td>
                        
                        <td colspan="3" class="border-t border-slate-200"></td>
                    </tr>
                </tfoot>

            </table>
        </div>
    </div>

    <script>
        function reportManager() {
            return {
                cashBoxes: @json($cashBoxes),
                currencies: @json($currencies),
                baseCurrency: @json($baseCurrency),

                // 🟢 Bulletproof Money Formatter (Handles Negatives & Decimals perfectly)
                formatMoney(val) {
                    if (val === null || val === undefined || isNaN(val) || val === '') return '0';
                    let num = parseFloat(val);
                    return num.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2});
                },

                // Get Total for a Specific Currency Column
                calculateColumnTotal(currencyId) {
                    let total = 0;
                    this.cashBoxes.forEach(box => {
                        let amount = parseFloat(box.balances['curr_' + currencyId]) || 0;
                        total += amount;
                    });
                    return total;
                },

                // Convert all currencies in a single box into the Base Currency
                calculateBaseTotalForRow(box) {
                    let rowTotal = 0;
                    if (!this.baseCurrency) return 0;
                    
                    let basePrice = parseFloat(this.baseCurrency.price_single || 1);

                    this.currencies.forEach(curr => {
                        let amount = parseFloat(box.balances['curr_' + curr.id]) || 0;
                        if (amount === 0) return;

                        if (curr.id === this.baseCurrency.id) {
                            rowTotal += amount;
                        } else {
                            // Conversion logic
                            let currPrice = parseFloat(curr.price_single || 1);
                            
                            if (basePrice > currPrice) {
                                rowTotal += (amount * basePrice);
                            } else if (basePrice < currPrice) {
                                rowTotal += (amount / currPrice);
                            }
                        }
                    });

                    return rowTotal;
                },

                // Grand Total of the Base Currency Column
                calculateGrandBaseTotal() {
                    let grandTotal = 0;
                    this.cashBoxes.forEach(box => {
                        grandTotal += this.calculateBaseTotalForRow(box);
                    });
                    return grandTotal;
                }
            }
        }
    </script>
</x-app-layout>