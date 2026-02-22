<x-app-layout>
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { height: 8px; width: 8px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .header-search-input { width: 100%; height: 28px; background-color: #fff; border: 1px solid #e2e8f0; border-radius: 4px; padding-left: 20px; padding-right: 20px; font-size: 0.75rem; color: #334155; transition: all 0.15s; }
        [dir="rtl"] .header-search-input { padding-left: 8px; padding-right: 24px; } 
        .header-search-input:focus { border-color: #6366f1; outline: none; box-shadow: 0 0 0 1px rgba(99, 102, 241, 0.1); }
        .th-container { position: relative; width: 100%; height: 26px; display: flex; align-items: center; overflow: hidden; }
        .th-title { position: absolute; inset: 0; display: flex; align-items: center; justify-content: space-between; gap: 4px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); transform: translateY(0); opacity: 1; }
        .th-search { position: absolute; inset: 0; display: flex; align-items: center; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); transform: translateY(100%); opacity: 0; pointer-events: none; }
        .search-active .th-title { transform: translateY(-100%); opacity: 0; pointer-events: none; }
        .search-active .th-search { transform: translateY(0); opacity: 1; pointer-events: auto; }
        .dragging { opacity: 0.5; background: #e0e7ff; border: 2px dashed #6366f1; }
        .resizer { position: absolute; right: 0; top: 0; height: 100%; width: 4px; cursor: col-resize; user-select: none; touch-action: none; z-index: 20; }
        .resizer:hover, .resizing { background-color: #6366f1; opacity: 1; }
        [dir="rtl"] .resizer { left: 0; right: auto; }
    </style>

    <div x-data="accountantManager()" x-init="init()" class="bg-white h-[calc(100vh-65px)] flex flex-col font-sans text-slate-800" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">
        
        {{-- ALERTS --}}
        <div class="px-6 pt-2">
            @if ($errors->any())
                <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 p-2 rounded shadow-sm mb-2 text-sm"><ul class="list-disc list-inside">@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul></div>
            @endif
            @if (session('success'))
                <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-2 rounded shadow-sm mb-2 text-sm font-bold">{{ session('success') }}</div>
            @endif
        </div>

        {{-- TOOLBAR --}}
        <div class="px-6 py-2 flex-none flex flex-col md:flex-row justify-between items-center gap-4 no-print bg-white border-b border-slate-100 z-10">
            <div class="flex items-center gap-3">
                <div class="bg-slate-100 p-1 rounded-lg flex items-center shadow-inner">
                    <span class="px-4 py-1.5 text-sm font-bold rounded-md bg-white text-indigo-600 shadow-sm border border-slate-100">{{ __('accountant.paying_title') }}</span>
                </div>
                <span class="text-xs text-slate-400 font-mono bg-slate-50 px-2 py-1 rounded border border-slate-100"><span x-text="transactions.length"></span> {{ __('accountant.records') }}</span>
            </div>
            
            <div class="flex items-center gap-2">
                {{-- Bulk Delete --}}
                <div x-show="selectedIds.length > 0" x-cloak class="flex items-center gap-2">
                    <button type="button" @click="bulkDelete()" class="px-3 py-1.5 bg-red-600 text-white text-xs font-bold rounded shadow-sm hover:bg-red-700 transition-colors">
                        {{ __('Delete') }} (<span x-text="selectedIds.length"></span>)
                    </button>
                </div>
                
                {{-- Trash --}}
                <a href="{{ route('accountant.paying.trash') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-50 text-red-500 border border-red-100 hover:bg-red-100 shadow-sm transition-all" title="{{ __('accountant.trash') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </a>
                
                {{-- Print --}}
                <button type="button" @click.prevent="window.print()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-700 text-white hover:bg-slate-800 shadow-sm transition-all" title="{{ __('accountant.print') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                </button>
                
                {{-- Toggle Columns --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.outside="open = false" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 shadow-sm transition-all" title="{{ __('Toggle Columns') }}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    </button>
                    <div x-show="open" x-transition x-cloak class="absolute top-full mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 z-50 p-2 ltr:right-0 rtl:left-0 max-h-[60vh] overflow-y-auto custom-scrollbar">
                        <div class="text-xs font-bold text-slate-400 uppercase px-2 py-1 mb-1 border-b border-slate-50">{{ __('accountant.toggle_columns') }}</div>
                        <template x-for="col in columns" :key="col.field">
                            <label class="flex items-center justify-between px-2 py-2 hover:bg-indigo-50 rounded cursor-pointer transition group">
                                <span class="text-xs text-slate-700 font-bold group-hover:text-indigo-600" x-text="col.label"></span>
                                <input type="checkbox" x-model="col.visible" class="rounded text-indigo-600 w-4 h-4 border-slate-300 focus:ring-indigo-500">
                            </label>
                        </template>
                    </div>
                </div>

                {{-- 🟢 NEW PAYMENT BUTTON --}}
                <button @click="$dispatch('open-paying-modal')" class="w-10 h-10 flex items-center justify-center rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                </button>
            </div>
        </div>

        {{-- TABLE SECTION --}}
        <div class="flex-1 p-4 overflow-hidden flex flex-col min-h-0 bg-white">
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 flex-1 overflow-hidden flex flex-col relative">
                <div class="overflow-auto custom-scrollbar flex-1 w-full relative">
                    <table class="w-full text-sm text-left rtl:text-right border-separate border-spacing-0 min-w-[1000px]">
                        <thead class="bg-white text-slate-500 uppercase text-[10px] font-bold border-b border-slate-100 sticky top-0 z-10">
                            <tr>
                                <th class="px-3 py-2 w-[40px] text-center bg-white border-b border-slate-100 sticky left-0 z-20">
                                    <input type="checkbox" @click="toggleAllSelection()" :checked="allSelected" class="rounded text-indigo-600 w-4 h-4 border-slate-300 focus:ring-indigo-500">
                                </th>
                                <template x-for="(col, index) in columns" :key="col.field">
                                    <th x-show="col.visible" class="px-3 py-2 relative group select-none bg-white border-b border-slate-100 transition-colors hover:bg-slate-50" :style="`width: ${col.width}px; min-width: ${col.width}px`" draggable="true" @dragstart="dragStart($event, index)" @dragover.prevent @drop="drop($event, index)">
                                        <div class="th-container" :class="{ 'search-active': openFilter === col.field }">
                                            <div class="th-title">
                                                <div @click="sortBy(col.field)" class="flex items-center gap-1 flex-1 h-full cursor-pointer hover:text-indigo-600">
                                                    <span x-text="col.label"></span>
                                                    <svg x-show="sortCol === col.field" class="w-3 h-3 text-indigo-500" :class="sortAsc ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5 10l5-5 5 5H5z"/></svg>
                                                </div>
                                                <button type="button" @click.stop="openFilter = col.field; setTimeout(() => $refs['search-'+col.field].focus(), 100)" class="text-slate-300 hover:text-indigo-600 p-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                                </button>
                                            </div>
                                            <div class="th-search" x-cloak>
                                                <input type="text" x-model="filters[col.field]" :x-ref="'search-'+col.field" @keydown.escape="openFilter = null" class="header-search-input" placeholder="...">
                                                <button type="button" @click="filters[col.field] = ''; openFilter = null;" class="absolute right-1 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 p-0.5 rtl:left-1 rtl:right-auto">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="resizer" @mousedown="initResize($event, index)"></div>
                                    </th>
                                </template>
                                <th class="px-3 py-2 text-center sticky right-0 bg-white border-b border-slate-100 z-10">{{ __('accountant.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 bg-white">
                            <template x-for="trx in filteredTransactions" :key="trx.id">
                                <tr class="hover:bg-indigo-50/30 transition-colors group">
                                    <td class="px-3 py-2 text-center bg-white group-hover:bg-indigo-50/30 sticky left-0 z-10 border-r border-transparent rtl:border-l rtl:border-r-0">
                                        <input type="checkbox" :value="trx.id" x-model="selectedIds" class="rounded text-indigo-600 w-4 h-4 border-slate-300 focus:ring-indigo-500">
                                    </td>
                                    <template x-for="col in columns" :key="col.field">
                                        <td x-show="col.visible" class="px-3 py-2 whitespace-nowrap border-r border-transparent rtl:border-l rtl:border-r-0 text-slate-600 bg-white group-hover:bg-indigo-50/30">
                                            <template x-if="col.field === 'id'"><span class="font-mono text-slate-400 text-xs" x-text="'#' + trx.id"></span></template>
                                            <template x-if="col.field === 'account_id'"><div class="flex items-center gap-2"><img :src="trx.account?.profile_picture ? '/storage/'+trx.account.profile_picture : 'https://ui-avatars.com/api/?name='+trx.account?.name" class="w-8 h-8 rounded-full object-cover ring-1 ring-slate-100"><div class="flex flex-col leading-none"><span class="font-bold text-slate-700 text-xs truncate max-w-[140px]" x-text="trx.account?.name"></span><span class="text-[10px] text-slate-400 font-mono" x-text="trx.account?.code"></span></div></div></template>
                                            <template x-if="col.field === 'amount'"><span class="font-bold text-slate-800 text-xs" x-text="formatMoney(trx.amount) + ' ' + (trx.currency?.currency_type || '')"></span></template>
                                            <template x-if="col.field === 'currency_id'"><span class="text-[10px] font-bold bg-slate-100 text-slate-600 px-2 py-0.5 rounded" x-text="trx.currency?.currency_type || '-'"></span></template>
                                            <template x-if="col.field === 'total'"><span class="bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded border border-emerald-100 font-bold text-xs" x-text="formatMoney(trx.total)"></span></template>
                                            <template x-if="col.field === 'discount'"><span class="text-rose-500 text-xs font-bold" x-text="trx.discount > 0 ? formatMoney(trx.discount) : '-'"></span></template>
                                            <template x-if="col.field === 'exchange_rate'"><span class="text-orange-600 text-xs font-mono bg-orange-50 px-2 rounded" x-text="trx.exchange_rate ? formatMoney(trx.exchange_rate) : '-'"></span></template>
                                            <template x-if="col.field === 'type'"><span class="text-[10px] uppercase font-bold text-slate-500" x-text="trx.type || '-'"></span></template>
                                            <template x-if="col.field === 'invoice_type'"><span class="text-[10px] uppercase font-bold bg-white border border-slate-200 text-slate-500 px-2 py-0.5 rounded" x-text="trx.invoice_type"></span></template>
                                            <template x-if="col.field === 'statement_id'"><span class="font-mono text-xs bg-slate-50 px-2 py-0.5 rounded text-slate-600" x-text="trx.statement_id || '-'"></span></template>
                                            <template x-if="col.field === 'manual_date'"><span class="text-xs text-slate-700" x-text="formatDate(trx.manual_date)"></span></template>
                                            <template x-if="col.field === 'cashbox_id'"><span class="text-xs text-slate-600" x-text="trx.cashbox?.name"></span></template>
                                            <template x-if="col.field === 'note'"><span class="text-xs text-slate-400 truncate max-w-[180px] block" x-text="trx.note || '-'"></span></template>
                                            <template x-if="col.field === 'giver_name'"><div class="text-xs truncate max-w-[100px]" x-text="trx.giver_name || '-'"></div></template>
                                            <template x-if="col.field === 'giver_mobile'"><div class="text-xs font-mono text-slate-500" x-text="trx.giver_mobile || '-'"></div></template>
                                            <template x-if="col.field === 'receiver_name'"><div class="text-xs truncate max-w-[100px]" x-text="trx.receiver_name || '-'"></div></template>
                                            <template x-if="col.field === 'receiver_mobile'"><div class="text-xs font-mono text-slate-500" x-text="trx.receiver_mobile || '-'"></div></template>
                                            <template x-if="col.field === 'user_id'"><span class="text-[10px] uppercase text-slate-400" x-text="trx.user?.name"></span></template>
                                            <template x-if="col.field === 'created_at'"><span class="text-[10px] text-slate-400" x-text="formatDate(trx.created_at)"></span></template>
                                            <template x-if="col.field === 'updated_at'"><span class="text-[10px] text-slate-400" x-text="formatDate(trx.updated_at)"></span></template>
                                        </td>
                                    </template>
                                    <td class="px-3 py-2 text-center sticky right-0 bg-white group-hover:bg-indigo-50/30 border-b border-slate-100 z-10">
                                        <div class="flex items-center justify-center gap-2">
                                            {{-- 🟢 EDIT BUTTON (Dispatches Event) --}}
                                            <button type="button" @click="$dispatch('open-paying-modal', trx)" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </button>
                                            <form :action="`/accountant/paying/${trx.id}`" method="POST" onsubmit="return confirm('{{ __('accountant.delete_confirm') }}');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="filteredTransactions.length === 0"><td colspan="100%" class="text-center py-12 text-slate-400"><div class="flex flex-col items-center"><svg class="w-12 h-12 text-slate-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><span class="text-sm">{{ __('accountant.no_data') }}</span></div></td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-2 border-t border-slate-100 bg-white">{{ $transactions->links() }}</div>
            </div>
        </div>

        {{-- 🟢 INCLUDE MODAL --}}
        @include('accountant.paying.form-modal')

        <form id="bulk-delete-form" action="{{ route('accountant.paying.bulk-delete') }}" method="POST" class="hidden">
            @csrf @method('DELETE')
            <input type="hidden" name="ids" id="bulk-delete-ids">
        </form>

    </div>

    {{-- SCRIPTS (Pure List Logic) --}}
    <script>
        function accountantManager() {
            return {
                transactions: @json($transactions->items() ?? []),
                filters: {}, selectedIds: [], sortCol: null, sortAsc: true, openFilter: null,
                columns: [
                    { field: 'id', label: '#', visible: true, width: 50 },
                    { field: 'account_id', label: '{{ __('accountant.user') }}', visible: true, width: 200 },
                    { field: 'amount', label: '{{ __('accountant.amount') }}', visible: true, width: 120 },
                    { field: 'currency_id', label: '{{ __('accountant.type_money') }}', visible: true, width: 80 },
                    { field: 'total', label: '{{ __('accountant.total') }}', visible: true, width: 120 },
                    { field: 'discount', label: '{{ __('accountant.discount') }}', visible: true, width: 100 },
                    { field: 'exchange_rate', label: '{{ __('accountant.exchange_rate') }}', visible: true, width: 100 },
                    { field: 'type', label: 'Type', visible: false, width: 80 },
                    { field: 'invoice_type', label: '{{ __('accountant.invoice_type') }}', visible: true, width: 100 },
                    { field: 'statement_id', label: '{{ __('accountant.statement_id') }}', visible: true, width: 100 },
                    { field: 'manual_date', label: '{{ __('accountant.manual_date') }}', visible: true, width: 120 },
                    { field: 'cashbox_id', label: '{{ __('accountant.cashbox') }}', visible: true, width: 120 },
                    { field: 'note', label: '{{ __('accountant.note') }}', visible: true, width: 200 },
                    { field: 'giver_name', label: '{{ __('accountant.giver_name') }}', visible: false, width: 150 },
                    { field: 'giver_mobile', label: '{{ __('accountant.giver_mobile') }}', visible: false, width: 120 },
                    { field: 'receiver_name', label: '{{ __('accountant.receiver_name') }}', visible: false, width: 150 },
                    { field: 'receiver_mobile', label: '{{ __('accountant.receiver_mobile') }}', visible: false, width: 120 },
                    { field: 'user_id', label: '{{ __('accountant.created_by') }}', visible: true, width: 120 },
                    { field: 'created_at', label: '{{ __('accountant.date') }}', visible: true, width: 140 },
                    { field: 'updated_at', label: 'Updated At', visible: false, width: 140 },
                ],
                
                init() { 
                    this.columns.forEach(c => this.filters[c.field] = '');
                },

                // 🟢 HELPERS
                dragStart(e, index) { e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', index); e.target.classList.add('dragging'); },
                drop(e, targetIndex) { e.target.classList.remove('dragging'); const draggedIndex = e.dataTransfer.getData('text/plain'); if (draggedIndex === '') return; const draggedCol = this.columns[draggedIndex]; this.columns.splice(draggedIndex, 1); this.columns.splice(targetIndex, 0, draggedCol); },
                initResize(e, index) { const startX = e.clientX; const startWidth = this.columns[index].width; const onMouseMove = (moveEvent) => { this.columns[index].width = Math.max(50, startWidth + (moveEvent.clientX - startX)); }; const onMouseUp = () => { window.removeEventListener('mousemove', onMouseMove); window.removeEventListener('mouseup', onMouseUp); }; window.addEventListener('mousemove', onMouseMove); window.addEventListener('mouseup', onMouseUp); },
                get filteredTransactions() { 
                    let data = this.transactions.filter(trx => {
                        for (const col of this.columns) {
                            const filterVal = this.filters[col.field]?.toLowerCase();
                            if (!filterVal) continue;
                            let cellVal = '';
                            if (col.field === 'account_id') cellVal = trx.account?.name || '';
                            else if (col.field === 'cashbox_id') cellVal = trx.cashbox?.name || '';
                            else if (col.field === 'user_id') cellVal = trx.user?.name || '';
                            else if (col.field === 'currency_id') cellVal = trx.currency?.currency_type || '';
                            else cellVal = String(trx[col.field] || '');
                            if (!cellVal.toLowerCase().includes(filterVal)) return false;
                        }
                        return true;
                    });
                    if (this.sortCol) {
                        data.sort((a, b) => {
                            let valA = String(a[this.sortCol] || '').toLowerCase();
                            let valB = String(b[this.sortCol] || '').toLowerCase();
                            if (valA < valB) return this.sortAsc ? -1 : 1;
                            if (valA > valB) return this.sortAsc ? 1 : -1;
                            return 0;
                        });
                    }
                    return data;
                },
                get allSelected() { return this.filteredTransactions.length > 0 && this.selectedIds.length === this.filteredTransactions.length; },
                toggleAllSelection() { this.selectedIds = this.allSelected ? [] : this.filteredTransactions.map(r => r.id); },
                bulkDelete() { if (confirm('{{ __('accountant.delete_confirm') }}')) { document.getElementById('bulk-delete-ids').value = JSON.stringify(this.selectedIds); document.getElementById('bulk-delete-form').submit(); } },
                sortBy(field) { if (this.sortCol === field) this.sortAsc = !this.sortAsc; else { this.sortCol = field; this.sortAsc = true; } },
            }
        }
        
        function formatMoney(val) { return val ? parseFloat(val).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00'; }
        function formatDate(dateStr) { if(!dateStr) return '-'; const d = new Date(dateStr); return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}); }
    </script>
</x-app-layout>