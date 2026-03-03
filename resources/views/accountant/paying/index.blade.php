<x-app-layout>
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>

    {{-- STYLES (Red/Rose Theme) --}}
    <style>
        [x-cloak] { display: none !important; }
        .sheet-input { width: 100%; height: 100%; display: flex; align-items: center; background: transparent; border: 1px solid transparent; padding: 0 12px; font-size: 0.75rem; color: #1f2937; font-weight: 500; border-radius: 6px; transition: all 0.15s ease-in-out; }
        .sheet-input:focus { background-color: #fff; border-color: #f43f5e; box-shadow: 0 0 0 2px rgba(244, 63, 94, 0.2); outline: none; }
        .sheet-input[readonly], .sheet-input[disabled] { cursor: default; color: #64748b; background-color: transparent; }
        .select-checkbox { width: 1rem; height: 1rem; border-radius: 4px; border: 1px solid #cbd5e1; color: #e11d48; cursor: pointer; transition: all 0.2s; }
        .table-container::-webkit-scrollbar { height: 6px; }
        .table-container::-webkit-scrollbar-track { background: #f1f1f1; }
        .table-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        
        /* --- HEADER INTERACTIVITY --- */
        .th-container { position: relative; width: 100%; height: 32px; display: flex; align-items: center; overflow: hidden; padding: 0 4px; }
        .th-title { position: absolute; inset: 0; display: flex; align-items: center; justify-content: space-between; gap: 4px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); transform: translateY(0); opacity: 1; cursor: grab; padding: 0 8px; }
        .th-title:active { cursor: grabbing; }
        .search-active .th-title { transform: translateY(-100%); opacity: 0; pointer-events: none; }
        .th-search { position: absolute; inset: 0; display: flex; align-items: center; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); transform: translateY(100%); opacity: 0; pointer-events: none; padding: 0 4px; }
        .search-active .th-search { transform: translateY(0); opacity: 1; pointer-events: auto; }
        .header-search-input { width: 100%; height: 100%; background-color: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 6px; padding-left: 24px; padding-right: 20px; font-size: 0.7rem; color: #1f2937; transition: all 0.15s; }
        [dir="rtl"] .header-search-input { padding-left: 20px; padding-right: 24px; }
        .header-search-input:focus { background-color: #fff; border-color: #f43f5e; outline: none; box-shadow: 0 0 0 2px rgba(244, 63, 94, 0.1); }
        .resizer { position: absolute; right: -4px; top: 0; height: 100%; width: 8px; cursor: col-resize; z-index: 50; touch-action: none; }
        .resizer:hover::after, .resizing::after { content: ''; position: absolute; right: 4px; top: 20%; height: 60%; width: 2px; background-color: #f43f5e; }
        [dir="rtl"] .resizer { right: auto; left: -4px; }
        [dir="rtl"] .resizer:hover::after { right: auto; left: 4px; }
        .dragging-col { opacity: 0.4; background-color: #ffe4e6; border: 2px dashed #f43f5e; }
        @media print { .no-print, button, .print\:hidden { display: none !important; } .overflow-x-auto { overflow: visible !important; } table { width: 100% !important; } }
    </style>

    <div x-data="accountantManager()" x-init="init()" class="py-6 w-full min-w-0 bg-white min-h-screen" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">
        
        {{-- ALERTS --}}
      

        {{-- TOOLBAR --}}
        <div class="mx-4 mb-6 flex flex-col md:flex-row justify-between items-center gap-4 no-print">
            <div class="flex items-center gap-3">
                <div class="bg-slate-100 p-1 rounded-lg flex items-center shadow-inner">
                    <span class="px-5 py-2 text-sm font-bold rounded-md bg-white text-rose-600 shadow-sm transition">
                        {{ app()->getLocale() == 'ku' ? 'پسوڵەی پارەدان' : __('accountant.paying_title') }}
                    </span>
                </div>
                <span class="text-xs text-slate-400 font-mono bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100"><span x-text="transactions.length"></span> {{ __('accountant.records') }}</span>
            </div>
            
            <div class="flex flex-wrap items-center gap-2">
                {{-- Bulk Delete --}}
                <div x-show="selectedIds.length > 0" x-transition class="flex items-center gap-2 bg-red-50 px-2 py-1 rounded-lg border border-red-100 mr-2 ml-2">
                    <span class="text-xs font-bold text-red-600 px-2"><span x-text="selectedIds.length"></span> {{ __('accountant.selected') ?? 'Selected' }}</span>
                    <x-btn type="bulk-delete" @click="bulkDelete()">{{ __('Delete') }}</x-btn>
                    <button @click="selectedIds = []" type="button" class="px-2 py-1.5 text-slate-500 hover:text-slate-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                
                <x-btn type="trash" href="{{ route('accountant.paying.trash') }}" title="{{ __('accountant.trash') }}" />
                
                {{-- Column Config --}}
                <div x-data="{ open: false }" class="relative">
                    <x-btn type="columns" @click="open = !open" @click.away="open = false" title="{{ __('accountant.toggle_columns') ?? 'Columns' }}" />
                    <div x-show="open" class="absolute top-full mt-3 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 p-3 ltr:right-0 rtl:left-0" style="display:none;">
                        <div class="flex justify-between items-center px-2 py-1 mb-2 border-b border-slate-100 pb-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">{{ __('accountant.columns') ?? 'Columns' }}</span>
                            <button @click="resetLayout(); open = false;" class="text-[10px] text-blue-500 hover:underline cursor-pointer">{{ __('Reset') }}</button>
                        </div>
                        <div class="max-h-60 overflow-y-auto space-y-1">
                            <template x-for="col in columns" :key="col.field">
                                <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 rounded cursor-pointer transition">
                                    <input type="checkbox" x-model="col.visible" class="rounded text-rose-600 w-4 h-4 border-slate-300 focus:ring-rose-500">
                                    <span class="text-xs text-slate-700 font-medium" x-text="col.label"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>

                <x-btn type="print" @click="window.print()" title="{{ __('accountant.print') }}" />
                <x-btn type="add" @click="$dispatch('open-paying-modal')" title="{{ __('Add New') }}" />
            </div>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="relative w-full overflow-x-auto table-container bg-white shadow-sm rounded-lg border border-slate-200 mx-4 pb-20">
            <table class="w-full text-sm text-left rtl:text-right text-slate-500 whitespace-nowrap border-separate border-spacing-0">
                <thead class="text-xs text-slate-700 uppercase bg-rose-50 border-b border-rose-100">
                    <tr>
                        <th class="px-4 py-3 w-[40px] text-center bg-rose-50 border-b border-rose-100 sticky left-0 z-10">
                            <input type="checkbox" @click="toggleAllSelection()" :checked="allSelected" class="select-checkbox bg-white text-rose-600 focus:ring-rose-500">
                        </th>

                        {{-- 🟢 Draggable Columns with STRICT min-width & Centered Content --}}
                        <template x-for="(col, index) in columns" :key="col.field">
                            <th x-show="col.visible" 
                                class="py-2 relative h-12 transition-colors duration-200 border-r border-transparent select-none group border-b border-rose-100 bg-rose-50"
                                :style="'min-width:' + col.width + 'px; max-width:' + col.width + 'px; width:' + col.width + 'px;'"
                                draggable="true"
                                @dragstart="dragStart($event, index)"
                                @dragover.prevent="dragOver($event)"
                                @drop="drop($event, index)"
                                :class="{'dragging-col': draggingIndex === index}">
                                
                                <div class="th-container" :class="{ 'search-active': openFilter === col.field }">
                                    {{-- Title --}}
                                    <div class="th-title">
                                        <div @click="sortBy(col.field)" class="flex items-center justify-center gap-1 cursor-pointer flex-1 h-full hover:text-rose-600 transition-colors overflow-hidden">
                                            <span x-text="col.label" class="truncate font-bold tracking-wide text-[11px] text-center"></span>
                                            <svg class="w-3 h-3 text-rose-500 transition-transform shrink-0" :class="sortCol === col.field && !sortAsc ? 'rotate-180' : ''" x-show="sortCol === col.field" fill="currentColor" viewBox="0 0 20 20"><path d="M5 10l5-5 5 5H5z"/></svg>
                                        </div>
                                        <button type="button" @click.stop="openFilter = col.field; setTimeout(() => $refs['input-'+col.field].focus(), 100)" class="p-1 rounded-md text-slate-400 hover:text-rose-600 hover:bg-white transition shrink-0" :class="filters[col.field] ? 'text-rose-600' : ''">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </button>
                                    </div>
                                    {{-- Search --}}
                                    <div class="th-search">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-1.5 pointer-events-none rtl:right-0 rtl:left-auto rtl:pr-1.5">
                                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </div>
                                        <input type="text" :x-ref="'input-'+col.field" x-model="filters[col.field]" @input.debounce="filterData()" @keydown.escape="openFilter = null" class="header-search-input w-full" placeholder="{{ __('Search') }}">
                                        <button type="button" @click="filters[col.field] = ''; filterData(); openFilter = null;" class="absolute right-1 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500 p-0.5 rounded-md rtl:left-1 rtl:right-auto transition"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                    </div>
                                </div>
                                <div class="resizer" @mousedown.stop.prevent="initResize($event, col)"></div>
                            </th>
                        </template>

                        <th class="px-4 py-3 w-[80px] text-center print:hidden bg-rose-50 border-b border-rose-100">{{ __('accountant.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <template x-for="(trx, rowIndex) in filteredTransactions" :key="trx.id">
                        <tr class="hover:bg-slate-50 transition-colors group" :class="selectedIds.includes(trx.id) ? 'bg-rose-50/10' : ''">
                            
                            <td class="px-4 py-2 text-center sticky left-0 bg-white group-hover:bg-slate-50 transition-colors"><input type="checkbox" :value="trx.id" x-model="selectedIds" class="select-checkbox text-rose-600 focus:ring-rose-500"></td>

                            <template x-for="col in columns" :key="col.field">
                                <td x-show="col.visible" class="p-2 align-middle">
                                    
                                    {{-- ID --}}
                                    <template x-if="col.field === 'id'">
                                        <div class="flex justify-center items-center w-full"><span class="font-mono text-slate-400 text-xs" x-text="'#' + trx.id"></span></div>
                                    </template>
                                    
                                    {{-- Account / User --}}
                                    <template x-if="col.field === 'account_id'">
                                        <div class="flex justify-center items-center w-full px-2">
                                            <a :href="'/accountant/statement?account_id=' + trx.account_id" class="flex items-center gap-2 group/link hover:bg-rose-50/80 p-1.5 rounded-lg transition-colors cursor-pointer w-fit text-right" title="View Statement">
                                                <img :src="trx.account?.profile_picture ? '/storage/'+trx.account.profile_picture : 'https://ui-avatars.com/api/?name='+(trx.account?.name || 'User')" class="w-8 h-8 rounded-full object-cover ring-1 ring-slate-200 group-hover/link:ring-rose-300 transition-all">
                                                <div class="flex flex-col leading-none text-right">
                                                    <span class="font-bold text-rose-600 text-xs truncate max-w-[160px] group-hover/link:text-rose-800 transition-colors" x-text="trx.account?.name"></span>
                                                    <span class="text-[10px] text-slate-400 font-mono mt-0.5 group-hover/link:text-rose-500 transition-colors" x-text="trx.account?.code"></span>
                                                </div>
                                            </a>
                                        </div>
                                    </template>

                                    {{-- 🟢 AMOUNT (Now Red/Rose Theme) --}}
                                    <template x-if="col.field === 'amount'">
                                        <div class="flex justify-center items-center w-full px-3">
                                            <span class="font-bold text-rose-600 text-sm" x-text="formatMoney(trx.amount)"></span>
                                        </div>
                                    </template>
                                    
                                    {{-- Currency --}}
                                    <template x-if="col.field === 'currency_id'">
                                        <div class="flex justify-center items-center w-full px-3">
                                            <span class="text-[10px] font-bold bg-slate-100 text-slate-600 px-2 py-0.5 rounded border border-slate-200 shadow-sm" x-text="trx.currency?.currency_type || '-'"></span>
                                        </div>
                                    </template>
                                    
                                    {{-- TOTAL INVOICE (Amount + Discount / کۆی گشتی) --}}
                                    <template x-if="col.field === 'total'">
                                        <div class="flex justify-center items-center w-full px-3">
                                            <span class="bg-slate-50 text-slate-800 px-2.5 py-1 rounded border border-slate-200 font-black text-sm shadow-sm" x-text="formatMoney(trx.invoice_total)"></span>
                                        </div>
                                    </template>
                                    
                                    {{-- DISCOUNT (Emerald color for discount in paying form to contrast with red amount) --}}
                                    <template x-if="col.field === 'discount'">
                                        <div class="flex justify-center items-center w-full px-3" x-show="trx.discount > 0">
                                            <span class="text-emerald-500 text-sm font-bold" x-text="formatMoney(trx.discount)"></span>
                                        </div>
                                    </template>
                                    
                                    {{-- EXCHANGE RATE --}}
                                    <template x-if="col.field === 'exchange_rate'">
                                        <div class="flex justify-center items-center w-full px-3">
                                            <span class="text-orange-600 text-xs font-mono bg-orange-50 px-2 py-0.5 rounded border border-orange-100 shadow-sm" x-text="trx.exchange_rate ? formatMoney(trx.exchange_rate) : '-'"></span>
                                        </div>
                                    </template>
                                    
                                    {{-- TYPE INVOICE (Dynamic Receiving/Paying Badge) --}}
                                    <template x-if="col.field === 'invoice_type'">
                                        <div class="flex justify-center items-center w-full px-3">
                                            <span class="text-[10px] uppercase font-bold border px-2 py-0.5 rounded shadow-sm"
                                                  :class="trx.type === 'receive' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-rose-50 text-rose-600 border-rose-200'"
                                                  x-text="trx.type === 'receive' ? '{{ app()->getLocale() == 'ku' ? 'وەرگرتن' : 'Receiving' }}' : '{{ app()->getLocale() == 'ku' ? 'پێدان' : 'Paying' }}'">
                                            </span>
                                        </div>
                                    </template>

                                    {{-- Strings & Dates --}}
                                    <template x-if="col.field === 'type'"><div class="flex justify-center items-center w-full px-3"><span class="text-[10px] uppercase font-bold text-slate-500" x-text="trx.type || '-'"></span></div></template>
                                    <template x-if="col.field === 'statement_id'"><div class="flex justify-center items-center w-full px-3"><span class="font-mono text-xs bg-slate-50 px-2 py-0.5 rounded border border-slate-200 text-slate-600 shadow-sm" x-text="trx.statement_id || '-'"></span></div></template>
                                    <template x-if="col.field === 'manual_date'"><div class="flex justify-center items-center w-full px-3"><span class="text-xs text-slate-700 font-bold" x-text="formatDate(trx.manual_date)"></span></div></template>
                                    <template x-if="col.field === 'cashbox_id'"><div class="flex justify-center items-center w-full px-3"><span class="text-xs font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-100" x-text="trx.cashbox?.name || '-'"></span></div></template>
                                    <template x-if="col.field === 'note'"><div class="flex justify-center items-center w-full px-3"><span class="text-xs text-slate-500 truncate max-w-[200px] text-center" x-text="trx.note || '-'"></span></div></template>
                                    <template x-if="col.field === 'giver_name'"><div class="flex justify-center items-center w-full px-3 text-xs truncate max-w-[140px] text-center" x-text="trx.giver_name || '-'"></div></template>
                                    <template x-if="col.field === 'giver_mobile'"><div class="flex justify-center items-center w-full px-3 text-xs font-mono text-slate-500 text-center" x-text="trx.giver_mobile || '-'"></div></template>
                                    <template x-if="col.field === 'receiver_name'"><div class="flex justify-center items-center w-full px-3 text-xs truncate max-w-[140px] text-center" x-text="trx.receiver_name || '-'"></div></template>
                                    <template x-if="col.field === 'receiver_mobile'"><div class="flex justify-center items-center w-full px-3 text-xs font-mono text-slate-500 text-center" x-text="trx.receiver_mobile || '-'"></div></template>
                                    <template x-if="col.field === 'user_id'"><div class="flex justify-center items-center w-full px-3"><span class="text-[10px] uppercase font-bold text-slate-400 bg-slate-50 px-2 py-0.5 rounded border border-slate-100" x-text="trx.user?.name || '-'"></span></div></template>
                                    
                                    <template x-if="col.field === 'created_at'"><div class="flex justify-center items-center w-full px-3 text-[10px] text-slate-400" x-text="formatDate(trx.created_at)"></div></template>
                                    <template x-if="col.field === 'updated_at'"><div class="flex justify-center items-center w-full px-3 text-[10px] text-slate-400" x-text="formatDate(trx.updated_at)"></div></template>
                                </td>
                            </template>
                            
                            {{-- Actions (Always visible) --}}
                            <td class="px-3 py-2 text-center print:hidden bg-white group-hover:bg-slate-50 transition-colors">
                                <div class="flex items-center justify-center gap-1.5">
                                    <x-btn type="edit" @click="$dispatch('open-paying-modal', trx)" title="{{ __('accountant.edit') }}" />
                                    <form :action="`/accountant/paying/${trx.id}`" method="POST" class="m-0 p-0 inline">
                                        @csrf @method('DELETE')
                                        <x-btn type="delete" onclick="if(confirm('{{ __('accountant.delete_confirm') }}')) this.closest('form').submit();" title="{{ __('accountant.delete') }}" />
                                    </form>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <tr x-show="filteredTransactions.length === 0"><td colspan="100%" class="text-center py-16 text-slate-400"><div class="flex flex-col items-center"><svg class="w-10 h-10 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><span class="text-sm font-bold">{{ __('accountant.no_data') }}</span></div></td></tr>
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if($transactions->hasPages())
            <div class="mx-4 mt-2 mb-6">
                {{ $transactions->links() }}
            </div>
        @endif

        <form id="bulk-delete-form" action="{{ route('accountant.paying.bulk-delete') }}" method="POST" class="hidden">
            @csrf @method('DELETE')
            <input type="hidden" name="ids" id="bulk-delete-ids">
        </form>

    </div>

    @include('accountant.paying.form-modal')

    <script>
        function accountantManager() {
            return {
                transactions: @json($transactions->items() ?? []),
                currencies: @json($currencies ?? []),
                filters: {}, selectedIds: [], sortCol: null, sortAsc: true, openFilter: null, draggingIndex: null,
                
                // 🟢 Same precise widths as Receiving for perfect centering
                columns: [
                    { field: 'id', label: '#', visible: true, width: 70 },
                    { field: 'account_id', label: @json(__('accountant.user')), visible: true, width: 250 },
                    { field: 'amount', label: @json(__('accountant.amount')), visible: true, width: 140 },
                    { field: 'currency_id', label: @json(__('accountant.type_money')), visible: true, width: 110 },
                    { field: 'total', label: @json(__('accountant.total_invoice')), visible: true, width: 150 },
                    { field: 'discount', label: @json(__('accountant.discount')), visible: true, width: 130 },
                    { field: 'exchange_rate', label: @json(__('accountant.exchange_rate')), visible: true, width: 130 },
                    { field: 'type', label: 'Type', visible: false, width: 100 },
                    { field: 'invoice_type', label: @json(__('accountant.invoice_type')), visible: true, width: 130 },
                    { field: 'statement_id', label: @json(__('accountant.statement_id')), visible: true, width: 130 },
                    { field: 'manual_date', label: @json(__('accountant.manual_date')), visible: true, width: 150 },
                    { field: 'cashbox_id', label: @json(__('accountant.cashbox')), visible: true, width: 150 },
                    { field: 'note', label: @json(__('accountant.note')), visible: true, width: 250 },
                    { field: 'giver_name', label: @json(__('accountant.giver_name')), visible: false, width: 180 },
                    { field: 'giver_mobile', label: @json(__('accountant.giver_mobile')), visible: false, width: 140 },
                    { field: 'receiver_name', label: @json(__('accountant.receiver_name')), visible: false, width: 180 },
                    { field: 'receiver_mobile', label: @json(__('accountant.receiver_mobile')), visible: false, width: 140 },
                    { field: 'user_id', label: @json(__('accountant.created_by')), visible: true, width: 150 },
                    { field: 'created_at', label: @json(__('accountant.date')), visible: true, width: 160 },
                    { field: 'updated_at', label: 'Updated At', visible: false, width: 160 },
                ],
                
                init() { 
                    // Bumped to v7 to ensure the new Rose theme and precise column widths apply automatically!
                    const savedCols = localStorage.getItem('paying_cols_v7');
                    if (savedCols) this.columns = JSON.parse(savedCols);
                    this.columns.forEach(c => this.filters[c.field] = '');
                },
                
                resetLayout() {
                    localStorage.removeItem('paying_cols_v7');
                    location.reload();
                },

                dragStart(e, index) { this.draggingIndex = index; e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', index); },
                dragOver(e) { e.preventDefault(); e.dataTransfer.dropEffect = 'move'; },
                drop(e, targetIndex) {
                    if (this.draggingIndex === null || this.draggingIndex === targetIndex) return;
                    const element = this.columns.splice(this.draggingIndex, 1)[0];
                    this.columns.splice(targetIndex, 0, element);
                    this.draggingIndex = null;
                    this.saveState();
                },

                initResize(e, col) { 
                    const startX = e.clientX; 
                    const startWidth = parseInt(col.width) || 100; 
                    const onMouseMove = (moveEvent) => { 
                        const diff = moveEvent.clientX - startX;
                        const isRtl = document.dir === 'rtl';
                        col.width = Math.max(90, (isRtl ? startWidth - diff : startWidth + diff)); 
                    }; 
                    const onMouseUp = () => { 
                        window.removeEventListener('mousemove', onMouseMove); 
                        window.removeEventListener('mouseup', onMouseUp); 
                        this.saveState();
                    }; 
                    window.addEventListener('mousemove', onMouseMove); 
                    window.addEventListener('mouseup', onMouseUp); 
                },

                saveState() { localStorage.setItem('paying_cols_v7', JSON.stringify(this.columns)); },

                get processedTransactions() {
                    return this.transactions.map(trx => {
                        let t_amount = parseFloat(trx.amount) || 0;
                        let t_disc = parseFloat(trx.discount) || 0;
                        
                        // 🟢 Total Invoice = Cash Amount + Discount (Same exact logic as Receiving)
                        let invoice_total = t_amount + t_disc;
                        
                        return { ...trx, invoice_total: invoice_total };
                    });
                },

                get filteredTransactions() { 
                    let data = this.processedTransactions.filter(trx => {
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
                
                bulkDelete() { 
                    if (confirm(@json(__('accountant.delete_confirm')))) { 
                        document.getElementById('bulk-delete-ids').value = JSON.stringify(this.selectedIds); 
                        document.getElementById('bulk-delete-form').submit(); 
                    } 
                },
                
                sortBy(field) { 
                    if (this.sortCol === field) this.sortAsc = !this.sortAsc; 
                    else { this.sortCol = field; this.sortAsc = true; } 
                },
                filterData() {
                    // Triggers reactivity
                }
            }
        }
        
        function formatMoney(val) { return val ? parseFloat(val).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2}).replace(/\.00$/, '') : '0'; }
        function formatDate(dateStr) { if(!dateStr) return '-'; const d = new Date(dateStr); return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}); }
    </script>
</x-app-layout>