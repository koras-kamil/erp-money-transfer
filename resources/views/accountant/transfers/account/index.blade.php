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
        .resizer { position: absolute; right: 0; top: 0; height: 100%; width: 5px; cursor: col-resize; z-index: 20; }
        .resizer:hover, .resizing { background-color: #6366f1; opacity: 1; }
        [dir="rtl"] .resizer { left: 0; right: auto; }

        @media print { .no-print, button, a { display: none !important; } }
    </style>

    <div x-data="accTransferManager()" x-init="init()" class="py-6 w-full min-w-0 bg-slate-50 min-h-screen" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">
        
        <div class="mx-4 mb-6 flex flex-col md:flex-row justify-between items-center gap-4 bg-white p-4 rounded-2xl shadow-sm border border-slate-200 no-print">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex flex-shrink-0 items-center justify-center shadow-lg shadow-indigo-500/30 text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </div>
                <div class="flex flex-col">
                    <h2 class="text-xl font-black text-slate-800 tracking-tight">{{ __('accounttransfer.account_transfers_history') }}</h2>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('account_transfers.create') }}" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-xl shadow-md shadow-indigo-500/20 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    {{ __('accounttransfer.new_transfer') }}
                </a>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mx-4 overflow-hidden flex flex-col relative pb-4">
            <div class="overflow-x-auto custom-scrollbar w-full relative">
                <table class="w-full text-sm text-center text-slate-600 whitespace-nowrap border-separate border-spacing-0">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-black border-b border-slate-200 sticky top-0 z-30 tracking-wider">
                        <tr>
                            <template x-for="(col, index) in columns" :key="col.field">
                                <th x-show="col.visible" class="px-2 py-2 relative group select-none bg-slate-50 border-b border-r border-slate-200" :style="`width: ${col.width}px; min-width: ${col.width}px`">
                                    <div class="th-container" :class="{ 'search-active': openFilter === col.field }">
                                        <div class="th-title">
                                            <div @click="sortBy(col.field)" class="flex items-center justify-center gap-1.5 flex-1 h-full cursor-pointer hover:text-indigo-600">
                                                <span x-text="col.label"></span>
                                                <svg x-show="sortCol === col.field" class="w-3 h-3 transition-transform" :class="sortAsc ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5 10l5-5 5 5H5z"/></svg>
                                            </div>
                                            <button type="button" @click.stop="openFilter = col.field; setTimeout(() => $refs['search-'+col.field].focus(), 100)" class="text-slate-300 hover:text-indigo-600 p-1 rounded transition-colors"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></button>
                                        </div>
                                        <div class="th-search" x-cloak>
                                            <input type="text" x-model="filters[col.field]" :x-ref="'search-'+col.field" @keydown.escape="openFilter = null" class="header-search-input" placeholder="...">
                                            <button type="button" @click="filters[col.field] = ''; openFilter = null;" class="absolute right-1 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 p-1 rounded-md rtl:left-1 rtl:right-auto"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                        </div>
                                    </div>
                                    <div class="resizer" @mousedown="initResize($event, index)"></div>
                                </th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <template x-for="(trx, index) in filteredTransfers" :key="trx.id">
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <template x-for="col in columns" :key="col.field">
                                    <td x-show="col.visible" class="px-2 py-3 border-r border-slate-100 rtl:border-l rtl:border-r-0 align-middle">
                                        <div class="flex items-center justify-center w-full">
                                            <template x-if="col.field === 'id'"><span class="font-mono text-slate-400 text-xs font-bold" x-text="'#' + trx.id"></span></template>
                                            <template x-if="col.field === 'manual_date'"><span class="font-bold text-slate-700 text-xs" x-text="formatDate(trx.manual_date)"></span></template>
                                            <template x-if="col.field === 'from_account'"><span class="text-xs font-bold text-slate-700" x-text="trx.from_account_name"></span></template>
                                            <template x-if="col.field === 'amount_sent'">
                                                <div class="w-full text-center p-1 rounded-lg bg-rose-50/30">
                                                    <span class="font-mono font-black text-sm text-rose-600 bg-white px-3 py-0.5 rounded-md border border-rose-200 shadow-sm" x-text="formatMoney(trx.amount_sent)"></span>
                                                    <span class="text-[10px] font-bold text-slate-400 ml-1" x-text="trx.from_currency_type"></span>
                                                </div>
                                            </template>
                                            <template x-if="col.field === 'to_account'"><span class="text-xs font-bold text-slate-700" x-text="trx.to_account_name"></span></template>
                                            <template x-if="col.field === 'amount_received'">
                                                <div class="w-full text-center p-1 rounded-lg bg-emerald-50/30">
                                                    <span class="font-mono font-black text-sm text-emerald-600 bg-white px-3 py-0.5 rounded-md border border-emerald-200 shadow-sm" x-text="formatMoney(trx.amount_received)"></span>
                                                    <span class="text-[10px] font-bold text-slate-400 ml-1" x-text="trx.to_currency_type"></span>
                                                </div>
                                            </template>
                                            <template x-if="col.field === 'exchange_rate'"><span class="font-mono text-[11px] text-slate-500" x-text="trx.exchange_rate ? formatMoney(trx.exchange_rate) : '-'"></span></template>
                                            <template x-if="col.field === 'statement_id'"><span class="font-mono text-indigo-500 font-bold text-xs" x-text="trx.statement_id || '-'"></span></template>
                                            <template x-if="col.field === 'user'"><span class="text-[10px] uppercase font-bold text-slate-500 bg-slate-100 px-2 py-1 rounded-md border border-slate-200" x-text="trx.user_name"></span></template>
                                        </div>
                                    </td>
                                </template>
                            </tr>
                        </template>
                        <tr x-show="filteredTransfers.length === 0">
                            <td colspan="100%" class="text-center py-16 text-slate-400"><span class="text-sm font-bold">{{ __('accounttransfer.no_records_found') }}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="px-4 pt-4 no-print border-t border-slate-100">{{ $transfers->links() }}</div>
        </div>
    </div>

    <script>
        function accTransferManager() {
            return {
                transfers: @json($transfers->items() ?? []),
                filters: {}, sortCol: null, sortAsc: true, openFilter: null,
                columns: [
                    { field: 'id', label: '# ID', visible: true, width: 80 },
                    { field: 'manual_date', label: '{{ __("accounttransfer.date") }}', visible: true, width: 140 },
                    { field: 'from_account', label: '{{ __("accounttransfer.sender_account") }}', visible: true, width: 150 },
                    { field: 'amount_sent', label: '{{ __("accounttransfer.amount_sent") }}', visible: true, width: 150 },
                    { field: 'to_account', label: '{{ __("accounttransfer.receiver_account") }}', visible: true, width: 150 },
                    { field: 'amount_received', label: '{{ __("accounttransfer.amount_received") }}', visible: true, width: 150 },
                    { field: 'exchange_rate', label: '{{ __("accounttransfer.ex_rate") }}', visible: true, width: 100 },
                    { field: 'statement_id', label: '{{ __("accounttransfer.doc_id") }}', visible: true, width: 100 },
                    { field: 'user', label: '{{ __("accounttransfer.user") }}', visible: true, width: 120 }
                ],
                init() { this.columns.forEach(c => this.filters[c.field] = ''); },
                initResize(e, index) { const startX = e.clientX; const startWidth = this.columns[index].width; const onMouseMove = (moveEvent) => { const isRtl = document.dir === 'rtl'; const diff = moveEvent.clientX - startX; this.columns[index].width = Math.max(80, startWidth + (isRtl ? -diff : diff)); }; const onMouseUp = () => { window.removeEventListener('mousemove', onMouseMove); window.removeEventListener('mouseup', onMouseUp); }; window.addEventListener('mousemove', onMouseMove); window.addEventListener('mouseup', onMouseUp); },
                sortBy(field) { if (this.sortCol === field) this.sortAsc = !this.sortAsc; else { this.sortCol = field; this.sortAsc = true; } },
                get processedTransfers() {
                    return this.transfers.map(trx => ({ 
                        ...trx, 
                        from_account_name: trx.from_account?.name || '-',
                        to_account_name: trx.to_account?.name || '-',
                        from_currency_type: trx.from_currency?.currency_type || '-',
                        to_currency_type: trx.to_currency?.currency_type || '-',
                        user_name: trx.user?.name || '-'
                    }));
                },
                get filteredTransfers() { 
                    let data = this.processedTransfers.filter(trx => {
                        for (const col of this.columns) {
                            const filterVal = this.filters[col.field]?.toLowerCase();
                            if (!filterVal) continue;
                            let cellVal = String(trx[col.field] || '');
                            if (col.field === 'id') cellVal = '#' + trx.id;
                            else if (col.field === 'from_account') cellVal = trx.from_account_name;
                            else if (col.field === 'to_account') cellVal = trx.to_account_name;
                            else if (col.field === 'user') cellVal = trx.user_name;
                            if (!cellVal.toLowerCase().includes(filterVal)) return false;
                        }
                        return true;
                    });
                    if (this.sortCol) { data.sort((a, b) => { let valA = String(a[this.sortCol] || '').toLowerCase(); let valB = String(b[this.sortCol] || '').toLowerCase(); if (valA < valB) return this.sortAsc ? -1 : 1; if (valA > valB) return this.sortAsc ? 1 : -1; return 0; }); }
                    return data;
                },
                formatMoney(val) { return val === null || isNaN(val) ? '0' : parseFloat(val).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2}); },
                formatDate(dateStr) { if(!dateStr) return '-'; const d = new Date(dateStr); return d.toLocaleDateString('en-GB') + ' ' + d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}); }
            }
        }
    </script>
</x-app-layout>