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

        @media print { .no-print, button, a { display: none !important; } .overflow-x-auto { overflow: visible !important; } table { width: 100% !important; } }
    </style>

    {{-- 🟢 REMOVED THE GRAY: Using bg-white to make the tab switch completely seamless! --}}
    <div x-data="transferListManager()" x-init="init()" class="py-6 w-full min-w-0 bg-white min-h-screen" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">
        
        {{-- TOOLBAR WITH TABS --}}
        <div class="mx-4 mb-6 flex flex-col md:flex-row justify-between items-center gap-4 bg-white p-3 rounded-2xl shadow-sm  no-print">
            
            <div class="flex flex-wrap items-center gap-3">
                {{-- 🟢 NAVIGATION TABS --}}
                <div class="bg-slate-100 p-1 rounded-lg flex items-center shadow-inner gap-1">
                    {{-- Inactive Tab (Link to Live Cashboxes) --}}
                    <a href="{{ route('accountant.cashbox_reports.index') }}" class="px-5 py-2 text-sm font-bold rounded-md text-slate-500 hover:text-indigo-600 hover:bg-slate-200/50 transition">
                        {{ __('cashboxreport.live_cashboxes') }}
                    </a>
                    
                    {{-- Active Tab (Current Page) --}}
                    <span class="px-5 py-2 text-sm font-bold rounded-md bg-white text-indigo-600 shadow-sm transition cursor-default">
                        {{ __('cashboxreport.transfers_history') }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                {{-- Column Config --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.outside="open = false" class="h-9 px-3 flex items-center gap-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 shadow-sm transition-all border border-slate-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        <span class="text-xs font-bold hidden sm:block">{{ __('cashboxreport.columns') }}</span>
                    </button>
                    <div x-show="open" x-transition x-cloak class="absolute top-full mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 z-50 p-2 ltr:right-0 rtl:left-0 max-h-[60vh] overflow-y-auto custom-scrollbar">
                        <div class="text-[10px] font-black text-slate-400 uppercase px-2 py-1 mb-1 border-b border-slate-100">{{ __('cashboxreport.columns') }}</div>
                        <template x-for="col in columns" :key="col.field">
                            <label class="flex items-center justify-between px-2 py-1.5 hover:bg-slate-50 rounded-lg cursor-pointer transition group">
                                <span class="text-[11px] text-slate-700 font-bold group-hover:text-indigo-600" x-text="col.label"></span>
                                <input type="checkbox" x-model="col.visible" class="rounded text-indigo-600 w-3.5 h-3.5 border-slate-300 focus:ring-indigo-500 transition-all">
                            </label>
                        </template>
                    </div>
                </div>

                {{-- Quick "New Transfer" Button --}}
                <a href="{{ route('accountant.transfers.create') }}" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-black rounded-lg shadow-md shadow-indigo-500/20 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    {{ __('cashboxreport.new_transfer') }}
                </a>
            </div>
        </div>

        {{-- 🟢 ADVANCED TABLE CONTAINER --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mx-4 overflow-hidden flex flex-col relative pb-4">
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
                                            <div @click="sortBy(col.field)" class="flex items-center justify-center gap-1.5 flex-1 h-full cursor-pointer hover:text-indigo-600"
                                                 :class="col.field === 'amount_sent' ? 'text-rose-600 hover:text-rose-700' : (col.field === 'amount_received' ? 'text-emerald-600 hover:text-emerald-700' : '')">
                                                <span x-text="col.label"></span>
                                                <svg x-show="sortCol === col.field" class="w-3 h-3 transition-transform" :class="sortAsc ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5 10l5-5 5 5H5z"/></svg>
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
                        <template x-for="(trx, index) in filteredTransfers" :key="trx.id">
                            <tr class="hover:bg-slate-50 transition-colors group">
                                
                                <template x-for="col in columns" :key="col.field">
                                    <td x-show="col.visible" class="px-2 py-3 border-r border-slate-100 rtl:border-l rtl:border-r-0 align-middle">
                                        <div class="flex items-center justify-center w-full">
                                            
                                            {{-- ID --}}
                                            <template x-if="col.field === 'id'">
                                                <span class="font-mono text-slate-400 text-xs font-bold" x-text="'#' + trx.id"></span>
                                            </template>
                                            
                                            {{-- Date --}}
                                            <template x-if="col.field === 'manual_date'">
                                                <span class="font-bold text-slate-700 text-xs" x-text="formatDate(trx.manual_date)"></span>
                                            </template>

                                            {{-- Sender Cashbox --}}
                                            <template x-if="col.field === 'from_cashbox'">
                                                <span class="text-xs font-bold text-slate-700" x-text="trx.from_cashbox_name"></span>
                                            </template>
                                            
                                            {{-- Amount Sent --}}
                                            <template x-if="col.field === 'amount_sent'">
                                                <div class="w-full text-center p-1 rounded-lg bg-rose-50/30 group-hover:bg-rose-50/70 transition-colors">
                                                    <span class="font-mono font-black text-sm text-rose-600 bg-white px-3 py-0.5 rounded-md border border-rose-200 shadow-sm" x-text="formatMoney(trx.amount_sent)"></span>
                                                    <span class="text-[10px] font-bold text-slate-400 ml-1" x-text="trx.from_currency_type"></span>
                                                </div>
                                            </template>

                                            {{-- Receiver Cashbox --}}
                                            <template x-if="col.field === 'to_cashbox'">
                                                <span class="text-xs font-bold text-slate-700" x-text="trx.to_cashbox_name"></span>
                                            </template>

                                            {{-- Amount Received --}}
                                            <template x-if="col.field === 'amount_received'">
                                                <div class="w-full text-center p-1 rounded-lg bg-emerald-50/30 group-hover:bg-emerald-50/70 transition-colors">
                                                    <span class="font-mono font-black text-sm text-emerald-600 bg-white px-3 py-0.5 rounded-md border border-emerald-200 shadow-sm" x-text="formatMoney(trx.amount_received)"></span>
                                                    <span class="text-[10px] font-bold text-slate-400 ml-1" x-text="trx.to_currency_type"></span>
                                                </div>
                                            </template>
                                            
                                            {{-- Exchange Rate --}}
                                            <template x-if="col.field === 'exchange_rate'">
                                                <span class="font-mono text-[11px] text-slate-500" x-text="trx.exchange_rate ? formatMoney(trx.exchange_rate) : '-'"></span>
                                            </template>

                                            {{-- Statement / Invoice ID --}}
                                            <template x-if="col.field === 'statement_id'">
                                                <span class="font-mono text-indigo-500 font-bold text-xs" x-text="trx.statement_id || '-'"></span>
                                            </template>

                                            {{-- User --}}
                                            <template x-if="col.field === 'user'">
                                                <span class="text-[10px] uppercase font-bold text-slate-500 bg-slate-100 px-2 py-1 rounded-md border border-slate-200 shadow-sm" x-text="trx.user_name"></span>
                                            </template>

                                        </div>
                                    </td>
                                </template>
                            </tr>
                        </template>
                        
                        <tr x-show="filteredTransfers.length === 0">
                            <td colspan="100%" class="text-center py-16 text-slate-400">
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                    </div>
                                    <span class="text-sm font-bold">{{ __('cashboxreport.no_records_found') }}</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            <div class="px-4 pt-4 no-print border-t border-slate-100">
                {{ $transfers->links() }}
            </div>
        </div>
    </div>

    <script>
        function transferListManager() {
            return {
                transfers: @json($transfers->items() ?? []),
                filters: {},
                sortCol: null,
                sortAsc: true,
                openFilter: null,
                
                columns: [
                    { field: 'id', label: '# ID', visible: true, width: 80 },
                    { field: 'manual_date', label: '{{ __("cashboxreport.date") }}', visible: true, width: 140 },
                    { field: 'from_cashbox', label: '{{ __("cashboxreport.sender") }}', visible: true, width: 150 },
                    { field: 'amount_sent', label: '{{ __("cashboxreport.amount_sent") }}', visible: true, width: 150 },
                    { field: 'to_cashbox', label: '{{ __("cashboxreport.receiver") }}', visible: true, width: 150 },
                    { field: 'amount_received', label: '{{ __("cashboxreport.amount_received") }}', visible: true, width: 150 },
                    { field: 'exchange_rate', label: '{{ __("cashboxreport.ex_rate") }}', visible: true, width: 100 },
                    { field: 'statement_id', label: '{{ __("cashboxreport.doc_id") }}', visible: true, width: 100 },
                    { field: 'user', label: '{{ __("cashboxreport.user") }}', visible: true, width: 120 }
                ],

                init() { 
                    this.columns.forEach(c => this.filters[c.field] = ''); 
                },

                dragStart(e, index) { e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', index); e.target.classList.add('dragging'); },
                drop(e, targetIndex) { e.target.classList.remove('dragging'); const draggedIndex = e.dataTransfer.getData('text/plain'); if (draggedIndex === '') return; const draggedCol = this.columns[draggedIndex]; this.columns.splice(draggedIndex, 1); this.columns.splice(targetIndex, 0, draggedCol); },
                initResize(e, index) { const startX = e.clientX; const startWidth = this.columns[index].width; const onMouseMove = (moveEvent) => { const isRtl = document.dir === 'rtl'; const diff = moveEvent.clientX - startX; this.columns[index].width = Math.max(80, startWidth + (isRtl ? -diff : diff)); }; const onMouseUp = () => { window.removeEventListener('mousemove', onMouseMove); window.removeEventListener('mouseup', onMouseUp); }; window.addEventListener('mousemove', onMouseMove); window.addEventListener('mouseup', onMouseUp); },
                sortBy(field) { if (this.sortCol === field) this.sortAsc = !this.sortAsc; else { this.sortCol = field; this.sortAsc = true; } },

                get processedTransfers() {
                    return this.transfers.map(trx => {
                        // Safe relationship checking
                        const fromBox = trx.from_cashbox || trx.fromCashbox || {};
                        const toBox = trx.to_cashbox || trx.toCashbox || {};
                        const fromCurr = trx.from_currency || trx.fromCurrency || {};
                        const toCurr = trx.to_currency || trx.toCurrency || {};
                        const user = trx.user || {};

                        return { 
                            ...trx, 
                            from_cashbox_name: fromBox.name || '-',
                            to_cashbox_name: toBox.name || '-',
                            from_currency_type: fromCurr.currency_type || '-',
                            to_currency_type: toCurr.currency_type || '-',
                            user_name: user.name || '-'
                        };
                    });
                },

                // 🟢 ADVANCED SMART SEARCH
                get filteredTransfers() { 
                    let data = this.processedTransfers.filter(trx => {
                        for (const col of this.columns) {
                            const filterVal = this.filters[col.field]?.toLowerCase();
                            if (!filterVal) continue;
                            
                            let cellVal = '';
                            
                            if (col.field === 'id') cellVal = '#' + trx.id;
                            else if (col.field === 'manual_date') cellVal = this.formatDate(trx.manual_date);
                            else if (col.field === 'from_cashbox') cellVal = trx.from_cashbox_name;
                            else if (col.field === 'to_cashbox') cellVal = trx.to_cashbox_name;
                            else if (col.field === 'amount_sent') cellVal = this.formatMoney(trx.amount_sent) + ' ' + trx.from_currency_type;
                            else if (col.field === 'amount_received') cellVal = this.formatMoney(trx.amount_received) + ' ' + trx.to_currency_type;
                            else if (col.field === 'exchange_rate') cellVal = trx.exchange_rate ? this.formatMoney(trx.exchange_rate) : '-';
                            else if (col.field === 'statement_id') cellVal = String(trx.statement_id || trx.id);
                            else if (col.field === 'user') cellVal = trx.user_name;
                            else cellVal = String(trx[col.field] || '');
                            
                            if (!cellVal.toLowerCase().includes(filterVal)) return false;
                        }
                        return true;
                    });

                    if (this.sortCol) {
                        data.sort((a, b) => {
                            let valA, valB;
                            
                            if (this.sortCol === 'amount_sent' || this.sortCol === 'amount_received' || this.sortCol === 'exchange_rate') {
                                valA = parseFloat(a[this.sortCol]) || 0;
                                valB = parseFloat(b[this.sortCol]) || 0;
                            } else if (this.sortCol === 'from_cashbox') {
                                valA = a.from_cashbox_name.toLowerCase();
                                valB = b.from_cashbox_name.toLowerCase();
                            } else if (this.sortCol === 'to_cashbox') {
                                valA = a.to_cashbox_name.toLowerCase();
                                valB = b.to_cashbox_name.toLowerCase();
                            } else if (this.sortCol === 'user') {
                                valA = a.user_name.toLowerCase();
                                valB = b.user_name.toLowerCase();
                            } else {
                                valA = String(a[this.sortCol] || '').toLowerCase();
                                valB = String(b[this.sortCol] || '').toLowerCase();
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
                }
            }
        }
    </script>
</x-app-layout>