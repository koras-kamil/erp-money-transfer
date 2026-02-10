<x-app-layout>
    {{-- REQUIRED: Scripts --}}
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>

    {{-- STYLES --}}
    <style>
        [x-cloak] { display: none !important; }
        
        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar { height: 10px; width: 10px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 5px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* Drag & Drop */
        .dragging { opacity: 0.5; background: #e0e7ff; border: 2px dashed #6366f1; }
        .resizer { position: absolute; right: 0; top: 0; height: 100%; width: 4px; cursor: col-resize; user-select: none; touch-action: none; z-index: 20; }
        .resizer:hover, .resizing { background-color: #6366f1; opacity: 1; }
        [dir="rtl"] .resizer { left: 0; right: auto; }

        /* --- HEADER ANIMATION --- */
        .th-container { position: relative; width: 100%; height: 28px; display: flex; align-items: center; overflow: hidden; }
        .th-title { position: absolute; inset: 0; display: flex; align-items: center; justify-content: space-between; gap: 4px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); transform: translateY(0); opacity: 1; cursor: grab; }
        .th-title:active { cursor: grabbing; }
        .th-search { position: absolute; inset: 0; display: flex; align-items: center; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); transform: translateY(100%); opacity: 0; pointer-events: none; }
        .search-active .th-title { transform: translateY(-100%); opacity: 0; pointer-events: none; }
        .search-active .th-search { transform: translateY(0); opacity: 1; pointer-events: auto; }

        /* Header Input */
        .header-search-input { width: 100%; height: 100%; background-color: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding-left: 24px; padding-right: 20px; font-size: 0.7rem; color: #1f2937; transition: all 0.15s; }
        [dir="rtl"] .header-search-input { padding-left: 20px; padding-right: 24px; }
        .header-search-input:focus { background-color: #fff; border-color: #6366f1; outline: none; box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1); }
    </style>

    {{-- MAIN CONTAINER --}}
    <div x-data="accountantManager()" x-init="init()" class="bg-white h-[calc(100vh-65px)] flex flex-col font-sans text-slate-800" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">
        
        {{-- 🟢 ALERTS SECTION (Uncommented & Restored) --}}
        <div class="px-6 pt-4">
            @if ($errors->any())
                <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 p-4 rounded shadow-sm mb-4 relative" role="alert">
                    <p class="font-bold">{{ __('accountant.error') }}</p>
                    <ul class="list-disc list-inside text-sm mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded shadow-sm mb-4" role="alert">
                    <p class="font-bold">{{ __('accountant.save_success') }}</p>
                    <p class="text-sm">{{ session('success') }}</p>
                </div>
            @endif
        </div>

        {{-- 🟢 TOOLBAR (Fixed Z-Index to allow Overrides) --}}
        {{-- Changed z-30 to z-10 to fix dropdown overlap issue --}}
        <div class="px-6 py-3 flex-none flex flex-col md:flex-row justify-between items-center gap-4 no-print bg-white shadow-sm border-b border-slate-100 z-10">
            
            {{-- Left: Title --}}
            <div class="flex items-center gap-4 w-full md:w-auto">
                {{-- Pill Style Title --}}
                <div class="bg-slate-100 p-1 rounded-lg flex items-center shadow-inner">
                    <span class="px-5 py-2 text-sm font-bold rounded-md bg-white text-indigo-600 shadow-sm transition">
                        {{ __('accountant.receiving_title') }}
                    </span>
                </div>
                
                {{-- Record Count --}}
                <span class="text-[10px] text-slate-400 font-mono bg-slate-50 px-1.5 py-0.5 rounded border border-slate-100">
                    {{ $transactions->total() }} {{ __('accountant.records') }}
                </span>
            </div>

            {{-- Right: Actions --}}
            <div class="flex items-center gap-2">
                {{-- Bulk Actions --}}
                <div x-show="selectedIds.length > 0" x-cloak class="flex items-center gap-2 bg-rose-50 px-2 py-1 rounded-lg border border-rose-100 mr-2 transition-all">
                    <span class="text-xs font-bold text-rose-700 px-1"><span x-text="selectedIds.length"></span></span>
                    <button type="button" @click="bulkDelete()" class="p-1 text-rose-600 hover:bg-rose-200 rounded"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </div>

                <x-btn type="trash" href="{{ route('accountant.receiving.trash') }}" title="{{ __('accountant.trash') }}" />
                <x-btn type="print" href="#" @click.prevent="window.print()" title="{{ __('accountant.print') }}" />

                {{-- Column Toggle --}}
                <div x-data="{ open: false }" class="relative">
                    <x-btn type="columns" @click="open = !open" @click.away="open = false" title="{{ __('accountant.columns') }}" />
                    <div x-show="open" x-cloak class="absolute top-full mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-100 z-50 p-2 ltr:right-0 rtl:left-0 max-h-[60vh] overflow-y-auto custom-scrollbar">
                        <div class="text-[10px] font-bold text-slate-400 uppercase px-2 py-1 mb-1">{{ __('accountant.toggle_columns') }}</div>
                        <template x-for="col in columns" :key="col.field">
                            <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 rounded cursor-pointer transition">
                                <input type="checkbox" x-model="col.visible" class="rounded text-indigo-600 w-4 h-4 border-slate-300 focus:ring-indigo-500">
                                <span class="text-xs text-slate-700 font-medium" x-text="col.label"></span>
                            </label>
                        </template>
                    </div>
                </div>

                {{-- ADD BUTTON --}}
                <button @click="openModal()" class="w-9 h-9 flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-md shadow-indigo-200 transition-all transform hover:scale-105 active:scale-95" title="{{ __('accountant.new_transaction') }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
            </div>
        </div>

        {{-- 🟢 TABLE WRAPPER --}}
        <div class="flex-1 p-4 overflow-hidden flex flex-col min-h-0 bg-white">
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 flex-1 overflow-hidden flex flex-col relative">
                
                <div class="overflow-auto custom-scrollbar flex-1 w-full relative">
                    <table class="w-full text-sm text-left rtl:text-right border-separate border-spacing-0 min-w-[1200px]">
                        {{-- Header (White) --}}
                        <thead class="bg-white text-slate-500 uppercase text-xs font-bold border-b border-slate-100 sticky top-0 z-20 shadow-sm">
                            <tr>
                                <th class="px-3 py-2 w-[40px] text-center bg-white border-b border-slate-100 sticky left-0 z-30">
                                    <input type="checkbox" @click="toggleAllSelection()" :checked="allSelected" class="rounded text-indigo-600 w-4 h-4 border-slate-300 focus:ring-indigo-500">
                                </th>

                                <template x-for="(col, index) in columns" :key="col.field">
                                    <th x-show="col.visible" 
                                        class="px-3 py-1.5 whitespace-nowrap relative group select-none bg-white border-b border-slate-100 transition-colors hover:bg-slate-50"
                                        :style="`width: ${col.width}px; min-width: ${col.width}px`"
                                        draggable="true"
                                        @dragstart="dragStart($event, index)"
                                        @dragover.prevent
                                        @drop="drop($event, index)">
                                     
                                        {{-- ANIMATED HEADER --}}
                                        <div class="th-container" :class="{ 'search-active': openFilter === col.field }">
                                            <div class="th-title">
                                                <div @click="sortBy(col.field)" class="flex items-center justify-center gap-1 flex-1 h-full hover:text-indigo-600 transition-colors">
                                                    <span x-text="col.label"></span>
                                                    <svg x-show="sortCol === col.field" class="w-3 h-3 text-indigo-500 transition-transform" :class="sortAsc ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5 10l5-5 5 5H5z"/></svg>
                                                </div>
                                                <button type="button" @click.stop="openFilter = col.field; setTimeout(() => $refs['search-'+col.field].focus(), 100)" class="text-slate-300 hover:text-indigo-600 p-1 rounded-full transition-colors" :class="filters[col.field] ? 'text-indigo-600' : ''">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                                </button>
                                            </div>
                                            <div class="th-search" x-cloak>
                                                <div class="absolute inset-y-0 left-0 flex items-center pl-1.5 pointer-events-none rtl:right-0 rtl:left-auto rtl:pr-1.5">
                                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                                </div>
                                                <input type="text" x-model="filters[col.field]" :x-ref="'search-'+col.field" @keydown.escape="openFilter = null" class="header-search-input" placeholder="Filter...">
                                                <button type="button" @click="filters[col.field] = ''; openFilter = null;" class="absolute right-1 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 p-0.5 rounded rtl:left-1 rtl:right-auto"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                            </div>
                                        </div>
                                        <div class="resizer" @mousedown="initResize($event, index)"></div>
                                    </th>
                                </template>
                                <th class="px-2 py-2 text-center sticky right-0 bg-white shadow-l z-30 w-[70px] border-b border-slate-100">{{ __('accountant.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 bg-white">
                            <template x-for="trx in filteredTransactions" :key="trx.id">
                                <tr class="hover:bg-indigo-50/20 transition-colors group">
                                    <td class="px-3 py-1 text-center bg-white group-hover:bg-indigo-50/20 sticky left-0 z-10 border-r border-transparent rtl:border-l rtl:border-r-0">
                                        <input type="checkbox" :value="trx.id" x-model="selectedIds" class="rounded text-indigo-600 w-4 h-4 border-slate-300 focus:ring-indigo-500">
                                    </td>

                                    <template x-for="col in columns" :key="col.field">
                                        <td x-show="col.visible" class="px-3 py-1 whitespace-nowrap border-r border-transparent rtl:border-l rtl:border-r-0 text-slate-600 bg-white group-hover:bg-indigo-50/20">
                                            
                                            <template x-if="col.field === 'id'"><span class="font-mono text-slate-400 text-[10px]" x-text="'#' + trx.id"></span></template>
                                            
                                            <template x-if="col.field === 'user'">
                                                <div class="flex items-center gap-2">
                                                    <img :src="trx.account?.profile_picture ? '/storage/'+trx.account.profile_picture : 'https://ui-avatars.com/api/?name='+trx.account?.name" class="w-7 h-7 rounded-full object-cover ring-1 ring-slate-100">
                                                    <div class="flex flex-col">
                                                        <span class="font-bold text-slate-700 text-xs truncate max-w-[120px]" x-text="trx.account?.name"></span>
                                                        <span class="text-[10px] text-slate-400 font-mono" x-text="trx.account?.manual_code"></span>
                                                    </div>
                                                </div>
                                            </template>

                                            <template x-if="col.field === 'statement_id'"><span class="font-mono text-[10px] bg-slate-50 px-1.5 py-0.5 rounded text-slate-600" x-text="trx.statement_id || '-'"></span></template>
                                            <template x-if="col.field === 'manual_date'"><span class="text-xs text-slate-700" x-text="formatDate(trx.manual_date)"></span></template>
                                            <template x-if="col.field === 'date'"><span class="text-[10px] text-slate-400" x-text="formatDate(trx.created_at)"></span></template>
                                            <template x-if="col.field === 'invoice_type'"><span class="text-[10px] uppercase font-bold bg-slate-50 text-slate-500 px-1.5 py-0.5 rounded border border-slate-200" x-text="trx.invoice_type"></span></template>

                                            <template x-if="col.field === 'amount'"><span class="font-bold text-slate-800 text-xs" x-text="formatMoney(trx.amount) + ' ' + (trx.currency?.code || 'USD')"></span></template>
                                            <template x-if="col.field === 'discount'"><span class="text-rose-500 text-xs font-bold" x-text="trx.discount > 0 ? formatMoney(trx.discount) : '-'"></span></template>
                                            <template x-if="col.field === 'total'"><span class="bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded border border-emerald-100 font-bold text-xs" x-text="formatMoney(trx.amount - trx.discount)"></span></template>
                                            <template x-if="col.field === 'exchange_rate'"><span class="text-orange-600 text-[10px] font-mono bg-orange-50 px-1 rounded" x-text="trx.exchange_rate > 1 ? formatMoney(trx.exchange_rate) : '-'"></span></template>

                                            <template x-if="col.field === 'giver_name'"><div><div class="text-xs text-slate-700 truncate max-w-[100px]" x-text="trx.giver_name || '-'"></div><div class="text-[10px] text-slate-400" x-text="trx.giver_mobile"></div></div></template>
                                            <template x-if="col.field === 'receiver_name'"><div><div class="text-xs text-slate-700 truncate max-w-[100px]" x-text="trx.receiver_name || '-'"></div><div class="text-[10px] text-slate-400" x-text="trx.receiver_mobile"></div></div></template>
                                            <template x-if="col.field === 'note'"><span class="text-xs text-slate-500 truncate max-w-[180px] block" x-text="trx.note || '-'" :title="trx.note"></span></template>
                                            <template x-if="col.field === 'cashbox'"><span class="text-xs text-slate-600" x-text="trx.cashbox?.name"></span></template>
                                            <template x-if="col.field === 'user_id'"><span class="text-[10px] uppercase text-slate-400" x-text="trx.user?.name"></span></template>
                                        </td>
                                    </template>
                                    
                                    {{-- 🟢 ACTIONS COLUMN (Edit + Delete) --}}
                                    <td class="px-2 py-1 text-center sticky right-0 bg-white group-hover:bg-indigo-50/20 shadow-l z-10 border-b border-slate-100">
                                        <div class="flex items-center justify-center gap-1">
                                            {{-- Edit Button (Restored) --}}
                                            <a :href="`/accountant/receiving/${trx.id}/edit`" class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded transition-colors" title="{{ __('Edit') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </a>

                                            {{-- Delete Button --}}
                                            <form :action="`/accountant/receiving/${trx.id}`" method="POST" onsubmit="return confirm('{{ __('accountant.delete_confirm') }}');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded transition-colors" title="{{ __('Delete') }}"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="filteredTransactions.length === 0">
                                <td colspan="100%" class="text-center py-12 text-slate-400">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-slate-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span>{{ __('accountant.no_data') }}</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Footer Pagination --}}
                <div class="px-4 py-2 border-t border-slate-100 bg-white">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>

        {{-- 🟢 INCLUDE FORM MODAL --}}
        @include('accountant.receiving.form-modal')

        {{-- BULK DELETE FORM --}}
        <form id="bulk-delete-form" action="{{ route('accountant.receiving.bulk-delete') }}" method="POST" class="hidden">
            @csrf @method('DELETE')
            <input type="hidden" name="ids" id="bulk-delete-ids">
        </form>

    </div>

    {{-- SCRIPTS --}}
    <script>
        function accountantManager() {
            return {
                showModal: false, searchOpen: false, searchQuery: '', selectedAccount: null, search: '',
                
                // ✅ DATA FROM CONTROLLER
                accounts: @json($accounts), 
                transactions: @json($transactions->items()),
                currencies: @json($currencies),
                
                transactionType: 'receive', filters: {}, selectedIds: [], sortCol: null, sortAsc: true, openFilter: null,
                
                // ✅ ALL COLUMNS
                columns: [
                    { field: 'id', label: '#', visible: true, width: 60 },
                    { field: 'user', label: '{{ __('accountant.user') }}', visible: true, width: 220 },
                    { field: 'amount', label: '{{ __('accountant.amount') }}', visible: true, width: 120 },
                    { field: 'total', label: '{{ __('accountant.total') }}', visible: true, width: 120 },
                    { field: 'statement_id', label: '{{ __('accountant.statement_id') }}', visible: true, width: 120 },
                    { field: 'manual_date', label: '{{ __('accountant.manual_date') }}', visible: true, width: 140 },
                    { field: 'invoice_type', label: '{{ __('accountant.invoice_type') }}', visible: true, width: 110 },
                    { field: 'discount', label: '{{ __('accountant.discount') }}', visible: true, width: 100 },
                    { field: 'exchange_rate', label: '{{ __('accountant.exchange_rate') }}', visible: true, width: 100 },
                    { field: 'giver_name', label: '{{ __('accountant.giver_name') }}', visible: true, width: 160 },
                    { field: 'giver_mobile', label: '{{ __('accountant.giver_mobile') }}', visible: true, width: 130 },
                    { field: 'receiver_name', label: '{{ __('accountant.receiver_name') }}', visible: true, width: 160 },
                    { field: 'receiver_mobile', label: '{{ __('accountant.receiver_mobile') }}', visible: true, width: 130 },
                    { field: 'note', label: '{{ __('accountant.note') }}', visible: true, width: 200 },
                    { field: 'date', label: '{{ __('accountant.date') }}', visible: true, width: 130 },
                    { field: 'cashbox', label: '{{ __('accountant.cashbox') }}', visible: true, width: 130 },
                    { field: 'user_id', label: '{{ __('accountant.created_by') }}', visible: true, width: 130 },
                ],

                // ✅ FORM DATA
                form: { 
                    amount: '', 
                    currency_id: '', 
                    rate: 1, 
                    discount: 0 
                },

                init() { 
                    if (!this.accounts) this.accounts = [];
                    this.columns.forEach(c => this.filters[c.field] = '');
                    // ✅ AUTO-SELECT FIRST CURRENCY
                    if (this.currencies.length > 0) {
                        this.setCurrency(this.currencies[0].id);
                    }
                },

                setCurrency(id) {
                    this.form.currency_id = id;
                    const selected = this.currencies.find(c => c.id == id);
                    if (selected) {
                        this.form.rate = selected.price_sell || 1; 
                    }
                },
                
                get filteredAccounts() { 
                    if (this.searchQuery === '') return this.accounts.slice(0, 10);
                    const q = this.searchQuery.toLowerCase(); 
                    return this.accounts.filter(acc => acc.name.toLowerCase().includes(q) || String(acc.code).toLowerCase().includes(q));
                },
                
                get filteredTransactions() { 
                    let data = this.transactions.filter(trx => {
                        for (const col of this.columns) {
                            const filterVal = this.filters[col.field]?.toLowerCase();
                            if (!filterVal) continue;
                            
                            let cellVal = '';
                            if (col.field === 'user') cellVal = trx.account?.name || '';
                            else if (col.field === 'cashbox') cellVal = trx.cashbox?.name || '';
                            else if (col.field === 'user_id') cellVal = trx.user?.name || '';
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
                
                bulkDelete() {
                    if (confirm('{{ __('accountant.delete_confirm') }}')) {
                        document.getElementById('bulk-delete-ids').value = JSON.stringify(this.selectedIds);
                        document.getElementById('bulk-delete-form').submit();
                    }
                },

                sortBy(field) {
                    if (this.sortCol === field) this.sortAsc = !this.sortAsc;
                    else { this.sortCol = field; this.sortAsc = true; }
                },

                selectAccount(acc) { this.selectedAccount = acc; this.searchQuery = ''; this.searchOpen = false; },
                clearSelection() { this.selectedAccount = null; this.searchQuery = ''; this.form.amount = ''; },
                
                get needsConversion() { return this.selectedAccount && this.form.currency !== this.selectedAccount.currency; },
                get accountCurrency() { return this.selectedAccount ? this.selectedAccount.currency : 'USD'; },
                get convertedAmount() { const amt = parseFloat(this.form.amount) || 0; return amt; }, 
                get finalTotal() { return Math.max(0, this.convertedAmount - (parseFloat(this.form.discount) || 0)); },
                
                formatMoney(val) { return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(val); },
                formatDate(dateStr) { if(!dateStr) return '-'; const d = new Date(dateStr); return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}); },
                
                openModal() { this.showModal = true; }, closeModal() { this.showModal = false; this.clearSelection(); },
                
                dragStart(e, index) { e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', index); e.target.classList.add('dragging'); },
                drop(e, targetIndex) { e.target.classList.remove('dragging'); const draggedIndex = e.dataTransfer.getData('text/plain'); if (draggedIndex === '') return; const draggedCol = this.columns[draggedIndex]; this.columns.splice(draggedIndex, 1); this.columns.splice(targetIndex, 0, draggedCol); },
                initResize(e, index) { const startX = e.clientX; const startWidth = this.columns[index].width; const onMouseMove = (moveEvent) => { this.columns[index].width = Math.max(50, startWidth + (moveEvent.clientX - startX)); }; const onMouseUp = () => { window.removeEventListener('mousemove', onMouseMove); window.removeEventListener('mouseup', onMouseUp); }; window.addEventListener('mousemove', onMouseMove); window.addEventListener('mouseup', onMouseUp); }
            }
        }
    </script>
</x-app-layout>