<x-app-layout>
    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        .header-search-input { width: 100%; height: 28px; background-color: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding-left: 24px; padding-right: 24px; font-size: 0.75rem; color: #334155; transition: all 0.15s; }
        [dir="rtl"] .header-search-input { padding-left: 8px; padding-right: 24px; } 
        .header-search-input:focus { border-color: #6366f1; outline: none; box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1); }
        
        .th-container { position: relative; width: 100%; height: 28px; display: flex; align-items: center; overflow: hidden; }
        .th-title { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; gap: 4px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); transform: translateY(0); opacity: 1; }
        .th-search { position: absolute; inset: 0; display: flex; align-items: center; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); transform: translateY(100%); opacity: 0; pointer-events: none; }
        .search-active .th-title { transform: translateY(-100%); opacity: 0; pointer-events: none; }
        .search-active .th-search { transform: translateY(0); opacity: 1; pointer-events: auto; }
        .dragging { opacity: 0.5; background: #e0e7ff; border: 2px dashed #6366f1; }
        .resizer { position: absolute; right: 0; top: 0; height: 100%; width: 5px; cursor: col-resize; user-select: none; touch-action: none; z-index: 20; }
        .resizer:hover, .resizing { background-color: #6366f1; opacity: 1; }
        [dir="rtl"] .resizer { left: 0; right: auto; }

        @media print { .no-print, button, a, form { display: none !important; } .overflow-x-auto { overflow: visible !important; } table { width: 100% !important; } }
    </style>

    <div x-data="ledgerManager()" x-init="init()" class="py-6 w-full min-w-0 bg-slate-50 min-h-screen" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">
        
        {{-- HEADER TOOLBAR --}}
        <div class="mx-4 mb-4 flex flex-col xl:flex-row justify-between items-center gap-4 bg-white p-3 rounded-2xl shadow-sm border border-slate-200 no-print">
            <div class="flex items-center gap-3 w-full xl:w-auto">
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex flex-shrink-0 items-center justify-center shadow-lg shadow-indigo-500/30 text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div class="flex flex-col">
                    <h2 class="text-lg font-black text-slate-800 tracking-tight">
                        {{ app()->getLocale() == 'ku' ? 'جوڵەی قاصە' : 'Cashbox Statement' }} : <span class="text-indigo-600">{{ $cashbox->name }}</span>
                    </h2>
                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">{{ app()->getLocale() == 'ku' ? 'لق' : 'Branch' }}: {{ $cashbox->branch->name ?? '-' }}</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 xl:gap-3 w-full xl:w-auto justify-end">
                
                {{-- 🟢 COMPACT DATE FILTER (Moved next to buttons) --}}
                <form method="GET" action="{{ route('accountant.cashbox_reports.show', $cashbox->id) }}" class="flex flex-wrap items-center gap-1.5 ltr:mr-2 rtl:ml-2 border-r border-slate-200 ltr:pr-3 rtl:pl-3">
                    @if(request('currency_id'))
                        <input type="hidden" name="currency_id" value="{{ request('currency_id') }}">
                    @endif
                    
                    <div class="flex items-center bg-slate-50 border border-slate-200 rounded-lg px-2 h-9 focus-within:ring-1 focus-within:ring-indigo-500 focus-within:border-indigo-500 transition-all">
                        <span class="text-[10px] font-bold text-slate-400 ltr:mr-1 rtl:ml-1">{{ app()->getLocale() == 'ku' ? 'لە' : 'From' }}:</span>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="bg-transparent border-none text-xs p-0 h-full w-24 font-bold text-slate-700 focus:ring-0 cursor-pointer">
                    </div>
                    
                    <div class="flex items-center bg-slate-50 border border-slate-200 rounded-lg px-2 h-9 focus-within:ring-1 focus-within:ring-indigo-500 focus-within:border-indigo-500 transition-all">
                        <span class="text-[10px] font-bold text-slate-400 ltr:mr-1 rtl:ml-1">{{ app()->getLocale() == 'ku' ? 'تا' : 'To' }}:</span>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="bg-transparent border-none text-xs p-0 h-full w-24 font-bold text-slate-700 focus:ring-0 cursor-pointer">
                    </div>

                    <button type="submit" class="h-9 w-9 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-sm transition-all flex items-center justify-center" title="{{ app()->getLocale() == 'ku' ? 'فلتەرکردن' : 'Filter' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    </button>

                    @if(request('start_date') || request('end_date'))
                        <a href="?currency_id={{ request('currency_id') }}" class="h-9 w-9 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded-lg shadow-sm transition-colors flex items-center justify-center" title="{{ app()->getLocale() == 'ku' ? 'لابردنی بەروار' : 'Clear Dates' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </form>

                {{-- Column Config --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.outside="open = false" class="h-9 px-3 flex items-center gap-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 shadow-sm transition-all border border-slate-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        <span class="text-xs font-bold hidden sm:block">{{ app()->getLocale() == 'ku' ? 'ستوونەکان' : 'Columns' }}</span>
                    </button>
                    <div x-show="open" x-transition x-cloak class="absolute top-full mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 z-50 p-2 ltr:right-0 rtl:left-0 max-h-[60vh] overflow-y-auto custom-scrollbar">
                        <div class="text-[10px] font-black text-slate-400 uppercase px-2 py-1 mb-1 border-b border-slate-100">{{ app()->getLocale() == 'ku' ? 'ستوونەکان' : 'Columns' }}</div>
                        <template x-for="col in columns" :key="col.field">
                            <label class="flex items-center justify-between px-2 py-1.5 hover:bg-slate-50 rounded-lg cursor-pointer transition group">
                                <span class="text-[11px] text-slate-700 font-bold group-hover:text-indigo-600" x-text="col.label"></span>
                                <input type="checkbox" x-model="col.visible" class="rounded text-indigo-600 w-3.5 h-3.5 border-slate-300 focus:ring-indigo-500 transition-all">
                            </label>
                        </template>
                    </div>
                </div>

                <a href="{{ route('accountant.cashbox_reports.index') }}" class="flex items-center gap-1.5 px-3 h-9 bg-white border border-slate-200 text-slate-600 rounded-lg text-xs font-bold shadow-sm hover:bg-slate-50 hover:text-indigo-600 transition">
                    <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span class="hidden sm:block">{{ app()->getLocale() == 'ku' ? 'گەڕانەوە' : 'Back' }}</span>
                </a>
                
                <button onclick="window.print()" class="flex items-center gap-1.5 px-3 h-9 bg-slate-800 text-white rounded-lg text-xs font-bold shadow-sm hover:bg-slate-700 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span class="hidden sm:block">{{ app()->getLocale() == 'ku' ? 'چاپ' : 'Print' }}</span>
                </button>
            </div>
        </div>

        {{-- 🟢 SLIGHTLY LARGER BALANCE CARDS (MAWE) --}}
        <div class="mx-4 mb-4 flex flex-wrap items-center gap-3 no-print">
            
            {{-- "All Currencies" Pill --}}
            <a href="?currency_id=&start_date={{ request('start_date') }}&end_date={{ request('end_date') }}" class="group px-4 py-2.5 rounded-xl border transition-all duration-300 shadow-sm flex items-center gap-2
                {{ !request('currency_id') ? 'bg-indigo-600 border-indigo-700 shadow-indigo-500/30 text-white' : 'bg-white border-slate-200 hover:border-indigo-300 hover:bg-slate-50' }}">
                <span class="text-xs font-black uppercase tracking-widest opacity-90 {{ !request('currency_id') ? 'text-indigo-50' : 'text-slate-500' }}">{{ app()->getLocale() == 'ku' ? 'گشتی' : 'All' }}</span>
            </a>

            {{-- Dynamic Currency Pills (Preserves Date Filter) --}}
            @foreach($liveBalances as $bal)
                <a href="?currency_id={{ $bal->currency_id }}&start_date={{ request('start_date') }}&end_date={{ request('end_date') }}" class="group px-4 py-2.5 rounded-xl border transition-all duration-300 shadow-sm flex items-center gap-2
                    {{ request('currency_id') == $bal->currency_id ? 'bg-indigo-50 border-indigo-400 ring-1 ring-indigo-400' : 'bg-white border-slate-200 hover:border-indigo-300 hover:bg-slate-50' }}">
                    <span class="text-xs font-black uppercase tracking-widest {{ request('currency_id') == $bal->currency_id ? 'text-indigo-500' : 'text-slate-400' }}">
                        {{ $bal->currency_type }} :
                    </span>
                    <span class="text-sm font-mono font-black {{ $bal->amount < 0 ? 'text-rose-600' : (request('currency_id') == $bal->currency_id ? 'text-indigo-900' : 'text-emerald-600') }}">
                        {{ number_format($bal->amount, 2) }}
                    </span>
                </a>
            @endforeach
        </div>

        {{-- 🟢 ADVANCED TABLE CONTAINER --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mx-4 overflow-hidden flex flex-col relative pb-10">
            <div class="overflow-x-auto custom-scrollbar w-full relative">
                <table class="w-full text-sm text-center text-slate-600 whitespace-nowrap border-separate border-spacing-0">
                    
                    {{-- HEADERS (With Search & Resize) --}}
                    <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-black border-b border-slate-200 sticky top-0 z-30 tracking-wider">
                        <tr>
                            <template x-for="(col, index) in columns" :key="col.field">
                                <th x-show="col.visible" class="px-2 py-2 relative group select-none bg-slate-50 border-b border-r border-slate-200 hover:bg-slate-100 transition-colors" 
                                    :style="`width: ${col.width}px; min-width: ${col.width}px`" draggable="true" @dragstart="dragStart($event, index)" @dragover.prevent @drop="drop($event, index)">
                                    
                                    <div class="th-container" :class="{ 'search-active': openFilter === col.field }">
                                        <div class="th-title">
                                            <div @click="sortBy(col.field)" class="flex items-center justify-center gap-1.5 flex-1 h-full cursor-pointer hover:text-indigo-600">
                                                <span x-text="col.label"></span>
                                                <svg x-show="sortCol === col.field" class="w-3 h-3 text-indigo-500 transition-transform" :class="sortAsc ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5 10l5-5 5 5H5z"/></svg>
                                            </div>
                                            <button type="button" @click.stop="openFilter = col.field; setTimeout(() => $refs['search-'+col.field].focus(), 100)" class="text-slate-300 hover:text-indigo-600 p-1 rounded transition-colors" :class="filters[col.field] ? 'text-indigo-500 bg-indigo-50' : ''">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                            </button>
                                        </div>
                                        <div class="th-search" x-cloak>
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-1.5 pointer-events-none rtl:right-0 rtl:left-auto rtl:pr-1.5">
                                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                            </div>
                                            <input type="text" x-model="filters[col.field]" :x-ref="'search-'+col.field" @keydown.escape="openFilter = null" class="header-search-input" placeholder="...">
                                            <button type="button" @click="filters[col.field] = ''; openFilter = null;" class="absolute right-1 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 p-1 rounded-md rtl:left-1 rtl:right-auto transition-colors"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                        </div>
                                    </div>
                                    <div class="resizer" @mousedown="initResize($event, index)"></div>
                                </th>
                            </template>
                        </tr>
                    </thead>
                    
                    {{-- BODY --}}
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <template x-for="(trx, index) in filteredTransactions" :key="trx.id">
                            <tr class="hover:bg-slate-50 transition-colors group">
                                
                                <template x-for="col in columns" :key="col.field">
                                    <td x-show="col.visible" class="px-2 py-3 border-r border-slate-100 rtl:border-l rtl:border-r-0 align-middle">
                                        <div class="flex items-center justify-center w-full">
                                            
                                            {{-- ID --}}
                                            <template x-if="col.field === 'id'"><span class="font-mono text-slate-400 text-xs font-bold" x-text="'#' + trx.id"></span></template>
                                            
                                            {{-- Date --}}
                                            <template x-if="col.field === 'manual_date'"><span class="font-bold text-slate-700 text-[11px]" x-text="formatDate(trx.manual_date)"></span></template>
                                            
                                            {{-- User --}}
                                            <template x-if="col.field === 'user_id'"><span class="text-[10px] uppercase font-bold text-slate-500 bg-slate-100 px-2 py-1 rounded-md border border-slate-200 shadow-sm" x-text="trx.user?.name || '-'"></span></template>

                                            {{-- Statement / Invoice ID --}}
                                            <template x-if="col.field === 'statement_id'"><span class="font-mono text-indigo-600 font-black text-xs bg-indigo-50 px-2 py-0.5 rounded shadow-sm border border-indigo-100" x-text="trx.statement_id || trx.id"></span></template>
                                            
                                            {{-- Type Badge --}}
                                            <template x-if="col.field === 'type'">
                                                <span class="text-[10px] px-2 py-1 rounded-md font-bold uppercase tracking-widest border shadow-sm"
                                                      :class="{
                                                          'bg-emerald-50 text-emerald-600 border-emerald-200': trx.type === 'receive',
                                                          'bg-rose-50 text-rose-600 border-rose-200': trx.type === 'pay',
                                                          'bg-indigo-50 text-indigo-600 border-indigo-200': trx.type === 'profit',
                                                          'bg-orange-50 text-orange-600 border-orange-200': trx.type === 'spending'
                                                      }"
                                                      x-text="trx.type_name">
                                                </span>
                                            </template>
                                            
                                            {{-- Explanation --}}
                                            <template x-if="col.field === 'explanation'">
                                                <span class="text-xs font-bold text-slate-700 max-w-[200px] truncate" title="trx.explanation" x-text="trx.explanation"></span>
                                            </template>

                                            {{-- Note --}}
                                            <template x-if="col.field === 'note'">
                                                <span class="text-[11px] text-slate-400 max-w-[150px] truncate" x-text="trx.note || '-'"></span>
                                            </template>
                                            
                                            {{-- Currency --}}
                                            <template x-if="col.field === 'currency_id'">
                                                <span class="text-xs font-black bg-slate-100 text-slate-700 px-2 py-1 rounded-lg border border-slate-200 shadow-sm" x-text="trx.currency?.currency_type || '-'"></span>
                                            </template>

                                            {{-- AMOUNT IN (Green) --}}
                                            <template x-if="col.field === 'amount_in'">
                                                <div class="w-full text-center p-1 rounded-lg transition-colors" :class="trx.amount_in > 0 ? 'bg-emerald-50/50' : ''">
                                                    <span x-show="trx.amount_in > 0" class="font-mono font-black text-sm text-emerald-600 bg-white px-3 py-0.5 rounded-md border border-emerald-200 shadow-sm" x-text="formatMoney(trx.amount_in)"></span>
                                                    <span x-show="trx.amount_in === 0" class="text-slate-300 font-black">-</span>
                                                </div>
                                            </template>

                                            {{-- AMOUNT OUT (Red) --}}
                                            <template x-if="col.field === 'amount_out'">
                                                <div class="w-full text-center p-1 rounded-lg transition-colors" :class="trx.amount_out > 0 ? 'bg-rose-50/50' : ''">
                                                    <span x-show="trx.amount_out > 0" class="font-mono font-black text-sm text-rose-600 bg-white px-3 py-0.5 rounded-md border border-rose-200 shadow-sm" x-text="formatMoney(trx.amount_out)"></span>
                                                    <span x-show="trx.amount_out === 0" class="text-slate-300 font-black">-</span>
                                                </div>
                                            </template>

                                            {{-- Exchange Rate --}}
                                            <template x-if="col.field === 'exchange_rate'">
                                                <span class="font-mono text-[11px] text-slate-400" x-text="trx.exchange_rate ? formatMoney(trx.exchange_rate) : '-'"></span>
                                            </template>

                                            {{-- RUNNING BALANCE --}}
                                            <template x-if="col.field === 'running_balance'">
                                                <div class="w-full text-center p-1 rounded-lg">
                                                    <span class="font-mono font-black text-sm px-3 py-0.5 rounded-md shadow-sm border" 
                                                          :class="trx.running_balance < 0 ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-slate-100 text-slate-800 border-slate-200'"
                                                          x-text="formatMoney(trx.running_balance)"></span>
                                                </div>
                                            </template>

                                        </div>
                                    </td>
                                </template>
                            </tr>
                        </template>
                        
                        <tr x-show="filteredTransactions.length === 0">
                            <td colspan="100%" class="text-center py-16 text-slate-400">
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <span class="text-sm font-bold">{{ app()->getLocale() == 'ku' ? 'هیچ زانیارییەک نەدۆزرایەوە' : 'No records found' }}</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        {{-- Pagination --}}
        <div class="mx-4 mt-4 no-print">
            {{ $transactions->withQueryString()->links() }}
        </div>
    </div>

    <script>
        function ledgerManager() {
            return {
                transactions: @json($transactions->items() ?? []),
                currencies: @json($currencies ?? []),
                cashbox: @json($cashbox), 
                broughtForward: @json($broughtForward ?? []), 
                filters: {},
                sortCol: null,
                sortAsc: true,
                openFilter: null,
                
                columns: [
                    { field: 'id', label: '# ID', visible: true, width: 80 },
                    { field: 'manual_date', label: '{{ app()->getLocale() == "ku" ? "بەروار" : "Date" }}', visible: true, width: 140 },
                    { field: 'user_id', label: '{{ app()->getLocale() == "ku" ? "بەکارهێنەر" : "User" }}', visible: true, width: 100 },
                    { field: 'statement_id', label: '{{ app()->getLocale() == "ku" ? "ژمارەی بەڵگە" : "Invoice ID" }}', visible: true, width: 100 },
                    { field: 'type', label: '{{ app()->getLocale() == "ku" ? "جۆر" : "Type" }}', visible: true, width: 100 },
                    { field: 'explanation', label: '{{ app()->getLocale() == "ku" ? "ڕوونکردنەوە" : "Explanation" }}', visible: true, width: 180 },
                    { field: 'note', label: '{{ app()->getLocale() == "ku" ? "تێبینی" : "Note" }}', visible: true, width: 140 },
                    { field: 'currency_id', label: '{{ app()->getLocale() == "ku" ? "جۆری پارە" : "Currency" }}', visible: true, width: 100 },
                    { field: 'amount_in', label: '{{ app()->getLocale() == "ku" ? "هاتوو (+)" : "In (+)" }}', visible: true, width: 120 },
                    { field: 'amount_out', label: '{{ app()->getLocale() == "ku" ? "دەرچوو (-)" : "Out (-)" }}', visible: true, width: 120 },
                    { field: 'exchange_rate', label: '{{ app()->getLocale() == "ku" ? "نرخ" : "Ex. Rate" }}', visible: true, width: 90 },
                    { field: 'running_balance', label: '{{ app()->getLocale() == "ku" ? "ماوەی جوڵە" : "Balance" }}', visible: true, width: 140 },
                ],

                init() { 
                    this.columns.forEach(c => this.filters[c.field] = ''); 
                },

                dragStart(e, index) { e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', index); e.target.classList.add('dragging'); },
                drop(e, targetIndex) { 
                    e.target.classList.remove('dragging');
                    const draggedIndex = e.dataTransfer.getData('text/plain'); 
                    if (draggedIndex === '') return; 
                    const draggedCol = this.columns[draggedIndex]; 
                    this.columns.splice(draggedIndex, 1); 
                    this.columns.splice(targetIndex, 0, draggedCol);
                },

                initResize(e, index) { 
                    const startX = e.clientX;
                    const startWidth = this.columns[index].width; 
                    const onMouseMove = (moveEvent) => { 
                        const isRtl = document.dir === 'rtl';
                        const diff = moveEvent.clientX - startX;
                        this.columns[index].width = Math.max(80, startWidth + (isRtl ? -diff : diff)); 
                    };
                    const onMouseUp = () => { window.removeEventListener('mousemove', onMouseMove); window.removeEventListener('mouseup', onMouseUp); }; 
                    window.addEventListener('mousemove', onMouseMove); 
                    window.addEventListener('mouseup', onMouseUp);
                },

                sortBy(field) { 
                    if (this.sortCol === field) this.sortAsc = !this.sortAsc; else { this.sortCol = field; this.sortAsc = true; } 
                },

                get processedTransactions() {
                    let runningTotals = {};
                    
                    this.currencies.forEach(c => {
                        let bRecord = this.cashbox.balances.find(b => b.currency_id === c.id);
                        let baseBalance = bRecord ? parseFloat(bRecord.balance) : 0;
                        let pastBalance = this.broughtForward[c.id] || 0;
                        runningTotals[c.id] = baseBalance + pastBalance;
                    });

                    let data = [...this.transactions];

                    data.forEach(trx => {
                        let cashAmount = parseFloat(trx.amount);

                        if (trx.type === 'receive' || trx.type === 'profit') {
                            runningTotals[trx.currency_id] += cashAmount;
                        } else {
                            runningTotals[trx.currency_id] -= cashAmount;
                        }
                        
                        trx.page_balance = runningTotals[trx.currency_id];
                    });

                    return data.map(trx => {
                        let isKu = '{{ app()->getLocale() }}' === 'ku';
                        let typeName = this.getTypeBadge(trx.type);
                        let accName = trx.account ? ` (${trx.account.name})` : '';
                        
                        let explanation = '';
                        if (trx.type === 'receive') explanation = isKu ? 'هاتنی پارە' + accName : 'Money In' + accName;
                        else if (trx.type === 'pay') explanation = isKu ? 'دەرچوونی پارە' + accName : 'Money Out' + accName;
                        else if (trx.type === 'profit') explanation = isKu ? 'قازانج هاتۆتە قاصە' : 'Profit Added';
                        else if (trx.type === 'spending') explanation = isKu ? 'خەرجی دەرچووە' : 'Spending Deducted';
                        
                        let cashAmount = parseFloat(trx.amount);

                        return { 
                            ...trx, 
                            type_name: typeName,
                            explanation: explanation,
                            amount_in: (trx.type === 'receive' || trx.type === 'profit') ? cashAmount : 0,
                            amount_out: (trx.type === 'pay' || trx.type === 'spending') ? cashAmount : 0,
                            running_balance: trx.page_balance
                        };
                    });
                },

                get filteredTransactions() { 
                    let data = this.processedTransactions.filter(trx => {
                        for (const col of this.columns) {
                            const filterVal = this.filters[col.field]?.toLowerCase();
                            if (!filterVal) continue;
                            let cellVal = '';
                            if (col.field === 'user_id') cellVal = trx.user?.name || '';
                            else if (col.field === 'currency_id') cellVal = trx.currency?.currency_type || '';
                            else if (col.field === 'statement_id') cellVal = trx.statement_id || trx.id;
                            else cellVal = String(trx[col.field] || '');
                            if (!cellVal.toLowerCase().includes(filterVal)) return false;
                        }
                        return true;
                    });

                    if (this.sortCol) {
                        data.sort((a, b) => {
                            let valA = String(a[this.sortCol] || '').toLowerCase();
                            let valB = String(b[this.sortCol] || '').toLowerCase();
                            if (this.sortCol === 'amount_in' || this.sortCol === 'amount_out' || this.sortCol === 'running_balance') {
                                valA = parseFloat(a[this.sortCol]);
                                valB = parseFloat(b[this.sortCol]);
                            }
                            if (valA < valB) return this.sortAsc ? -1 : 1;
                            if (valA > valB) return this.sortAsc ? 1 : -1;
                            return 0;
                        });
                    }
                    return data;
                },

                formatMoney(val) {
                    if (val === null || val === undefined || isNaN(val) || val === '') return '0';
                    let num = parseFloat(val);
                    return num.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2});
                },

                formatDate(dateStr) {
                    if(!dateStr) return '-'; 
                    const d = new Date(dateStr); 
                    return d.toLocaleDateString('en-GB') + ' ' + d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                },

                getTypeBadge(type) {
                    const isKu = '{{ app()->getLocale() }}' === 'ku';
                    if (type === 'receive') return isKu ? 'وەرگرتن' : 'Receive';
                    if (type === 'pay') return isKu ? 'پێدان' : 'Pay';
                    if (type === 'profit') return isKu ? 'قازانج' : 'Profit';
                    if (type === 'spending') return isKu ? 'خەرجی' : 'Spending';
                    return type;
                }
            }
        }
    </script>
</x-app-layout>