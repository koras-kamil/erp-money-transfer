<x-app-layout>
    {{-- STYLES --}}
    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 10px; width: 10px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 5px; border: 2px solid #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        [x-cloak] { display: none !important; }
        .select-checkbox { width: 1.1rem; height: 1.1rem; border-radius: 4px; border: 1px solid #cbd5e1; color: #6366f1; cursor: pointer; transition: all 0.2s; }
        .th-container { position: relative; width: 100%; height: 32px; display: flex; align-items: center; justify-content: center; overflow: visible; }
        .th-title { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; gap: 4px; transition: all 0.2s ease; transform: translateY(0); opacity: 1; cursor: grab; z-index: 10; }
        .th-title:active { cursor: grabbing; }
        .search-active .th-title { transform: translateY(-150%); opacity: 0; pointer-events: none; }
        .th-search { position: absolute; inset: 0; display: flex; align-items: center; transition: all 0.2s ease; transform: translateY(150%); opacity: 0; pointer-events: none; z-index: 20; background-color: #f8fafc; }
        .search-active .th-search { transform: translateY(0); opacity: 1; pointer-events: auto; }
        .header-search-input { width: 100%; height: 28px; background-color: #fff; border: 1px solid #cbd5e1; border-radius: 6px; padding-left: 8px; padding-right: 24px; font-size: 0.75rem; color: #1f2937; transition: all 0.15s; }
        [dir="rtl"] .header-search-input { padding-left: 24px; padding-right: 8px; }
        .header-search-input:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1); }
        .resizer { position: absolute; right: -4px; top: 0; height: 100%; width: 8px; cursor: col-resize; z-index: 50; touch-action: none; }
        .resizer:hover::after, .resizing::after { content: ''; position: absolute; right: 4px; top: 20%; height: 60%; width: 2px; background-color: #3b82f6; }
        [dir="rtl"] .resizer { right: auto; left: -4px; }
        [dir="rtl"] .resizer:hover::after { right: auto; left: 4px; }
        .dragging-col { opacity: 0.4; background-color: #e0e7ff; border: 2px dashed #6366f1; }
        @media print { .no-print, button, .print\:hidden { display: none !important; } .overflow-x-auto { overflow: visible !important; } table { width: 100% !important; } }
    </style>

    <div x-data="expensesManager()" x-init="initData()" class="py-6 w-full min-w-0" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

        {{-- TOOLBAR --}}
        <div class="mx-4 mb-6 flex flex-col md:flex-row justify-between items-center gap-4 no-print">
            
            {{-- Title Left Side --}}
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-bold text-slate-800">{{ __('expense.spending_transactions') ?? 'Spending / Expenses' }}</h2>
            </div>

            {{-- Action Buttons & Date Filter (Right Side) --}}
            <div class="flex flex-wrap items-center gap-2">
                
                {{-- Date Dropdown --}}
                <select x-model="dateFilter" @change="fetchData()" class="w-auto inline-block bg-white border border-slate-300 text-slate-700 text-sm font-bold rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 py-2 ltr:pl-3 ltr:pr-10 rtl:pr-3 rtl:pl-10 shadow-sm transition-colors cursor-pointer hover:bg-slate-50 mr-2 ml-2">
                    <option value="all">{{ __('expense.all_time') ?? 'All Time' }}</option>
                    <option value="today">{{ __('expense.today') ?? 'Today' }}</option>
                    <option value="yesterday">{{ __('expense.yesterday') ?? 'Yesterday' }}</option>
                    <option value="this_month">{{ __('expense.this_month') ?? 'This Month' }}</option>
                    <option value="last_month">{{ __('expense.last_month') ?? 'Last Month' }}</option>
                    <option value="this_year">{{ __('expense.this_year') ?? 'This Year' }}</option>
                    <option value="last_year">{{ __('expense.last_year') ?? 'Last Year' }}</option>
                </select>

                {{-- Bulk Delete Trigger --}}
                <div x-show="selectedIds.length > 0" x-transition class="flex items-center gap-2 bg-red-50 px-2 py-1 rounded-lg border border-red-100 mr-2 ml-2">
                    <span class="text-xs font-bold text-red-600 px-2"><span x-text="selectedIds.length"></span> {{ __('account.selected') }}</span>
                    <x-btn type="bulk-delete" @click="bulkDelete()">{{ __('account.delete_selected') }}</x-btn>
                    <button @click="selectedIds = []" type="button" class="px-2 py-1.5 text-slate-500 hover:text-slate-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                
                {{-- Hide/Show Columns Dropdown --}}
                <div x-data="{ open: false }" class="relative" @click.outside="open = false">
                    <x-btn type="columns" @click="open = !open" title="{{ __('account.columns') }}" />
                    <div x-show="open" @click.stop class="absolute top-full mt-3 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 p-3 ltr:right-0 rtl:left-0" style="display:none;">
                        <div class="flex justify-between items-center px-2 py-1 mb-2 border-b border-slate-100 pb-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">{{ __('account.columns') }}</span>
                            <button @click="resetLayout(); open = false;" class="text-[10px] text-blue-500 hover:underline cursor-pointer">{{ __('account.reset_layout') }}</button>
                        </div>
                        <div class="max-h-60 overflow-y-auto space-y-1">
                            <template x-for="col in columns" :key="col.field">
                                <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 rounded cursor-pointer transition">
                                    <input type="checkbox" x-model="col.visible" class="rounded text-indigo-600 w-4 h-4 border-slate-300 focus:ring-indigo-500">
                                    <span class="text-xs text-slate-700 font-medium" x-text="col.label"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>

                <x-btn type="print" href="#" target="_blank" title="{{ __('account.print') }}" />
                <x-btn type="add" href="{{ route('accountant.expenses.create') }}" title="{{ __('account.add_new') }}" />
            </div>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="relative w-full overflow-x-auto table-container bg-white shadow-sm rounded-lg border border-slate-200 mx-4 pb-20">
            <div x-show="loading" class="absolute inset-0 bg-white/60 backdrop-blur-[1px] z-50 flex items-center justify-center transition-opacity">
                <div class="w-8 h-8 border-3 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
            </div>

            <table class="w-full text-sm text-center rtl:text-center text-slate-500 whitespace-nowrap border-separate border-spacing-0">
                <thead class="text-xs text-slate-700 uppercase bg-blue-50/50 border-b border-blue-100 sticky top-0 z-20">
                    <tr>
                        {{-- 🟢 هێڵی کاڵی ستوونی (border-x border-slate-200) لێرە زیاد کرا --}}
                        <th class="px-4 py-3 w-[40px] text-center bg-slate-50/95 border-b border-blue-100 border-x border-slate-200"><input type="checkbox" @click="toggleAllSelection()" :checked="data.length > 0 && selectedIds.length === data.length" class="select-checkbox bg-white"></th>
                        <template x-for="(col, index) in columns" :key="col.field">
                            <th x-show="col.visible" 
                                class="px-4 py-2 relative h-12 text-center transition-colors duration-200 border-x border-slate-200 select-none group border-b border-blue-100 bg-slate-50/95" 
                                :style="'min-width:' + col.width + 'px'"
                                draggable="true" @dragstart="dragStart($event, index)" @dragover.prevent="dragOver($event)" @drop="drop($event, index)"
                                :class="[{'dragging-col': draggingIndex === index}, col.class]">
                                
                                <div class="th-container" :class="{ 'search-active': openFilter === col.field }">
                                    <div class="th-title">
                                        <div class="flex items-center justify-center gap-1 cursor-pointer flex-1 h-full hover:text-indigo-600 transition-colors" @click="sortBy(col.field)">
                                            <span x-text="col.label" class="whitespace-nowrap"></span>
                                            <span class="text-indigo-500 text-[9px] flex flex-col -space-y-1" x-show="params.sort === col.field">
                                                <span :class="params.direction === 'asc' ? 'text-indigo-600' : 'text-slate-300'">▲</span>
                                                <span :class="params.direction === 'desc' ? 'text-indigo-600' : 'text-slate-300'">▼</span>
                                            </span>
                                        </div>
                                        <button x-show="col.searchable !== false" type="button" @click.stop="openFilter = col.field; setTimeout(() => $refs['input-'+col.field]?.focus(), 100)" class="p-1 rounded-md text-slate-400 hover:text-indigo-600 hover:bg-slate-100 transition" :class="filters[col.field] ? 'text-indigo-600' : ''">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </button>
                                    </div>
                                    <div x-show="col.searchable !== false" class="th-search" @click.stop>
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-1.5 pointer-events-none rtl:right-0 rtl:left-auto rtl:pr-1.5">
                                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </div>
                                        <input type="text" :x-ref="'input-'+col.field" x-model="filters[col.field]" @input.debounce.400ms="fetchData()" @keydown.escape="openFilter = null; filters[col.field] = ''; fetchData();" class="header-search-input w-full" placeholder="{{ __('account.search') }}">
                                        <button type="button" @click.stop="filters[col.field] = ''; fetchData(); openFilter = null;" class="absolute right-1 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500 p-0.5 rounded-md rtl:left-1 rtl:right-auto"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                    </div>
                                </div>
                                <div class="resizer" @mousedown.stop.prevent="initResize($event, col)"></div>
                            </th>
                        </template>
                        <th class="px-4 py-3 w-[100px] text-center print:hidden bg-blue-50/50 border-b border-blue-100 sticky right-0 z-20 border-x border-slate-200">{{ __('account.actions') }}</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">
                    <template x-for="(exp, index) in data" :key="exp.id">
                        <tr class="transition-colors group hover:bg-slate-100" :class="selectedIds.includes(exp.id) ? 'bg-indigo-50/20' : (index % 2 === 0 ? 'bg-white' : 'bg-slate-50')">
                            
                            {{-- 🟢 هێڵی کاڵی ستوونی (border-x border-slate-200/50) بۆ هەموو خانەکان زیادکرا --}}
                            <td class="px-4 py-2 text-center align-middle border-x border-slate-200/50"><input type="checkbox" :value="exp.id" x-model="selectedIds" class="select-checkbox"></td>
                            
                            <template x-for="col in columns" :key="col.field">
                                <td x-show="col.visible" class="p-1 text-center border-x border-slate-200/50" :class="col.class">
                                    {{-- 1. Attachment --}}
                                    <template x-if="col.field === 'attachment'">
                                        <div class="flex items-center justify-center">
                                            <template x-if="exp.attachment_url">
                                                <a :href="exp.attachment_url" target="_blank" class="p-1.5 text-indigo-500 hover:bg-indigo-50 rounded-lg transition-colors" title="View Attachment">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                </a>
                                            </template>
                                            <template x-if="!exp.attachment_url"><span class="text-slate-300">-</span></template>
                                        </div>
                                    </template>

                                    {{-- 2. Money Formatting --}}
                                    <template x-if="col.field === 'cash_amount'"><div class="text-center font-bold text-emerald-600" x-text="Number(exp.cash_amount) > 0 ? Number(exp.cash_amount).toLocaleString() : '-'"></div></template>
                                    <template x-if="col.field === 'debt_amount'"><div class="text-center font-bold text-rose-500" x-text="Number(exp.debt_amount) > 0 ? Number(exp.debt_amount).toLocaleString() : '-'"></div></template>
                                    <template x-if="col.field === 'discount'"><div class="text-center font-bold text-orange-500" x-text="Number(exp.discount) > 0 ? Number(exp.discount).toLocaleString() : '-'"></div></template>
                                    <template x-if="col.field === 'exchange_rate'"><div class="text-center text-xs text-slate-500" x-text="Number(exp.exchange_rate).toLocaleString()"></div></template>

                                    {{-- 3. Row Counter & Bold Text Fields --}}
                                    <template x-if="col.field === 'id'">
                                        <div class="px-2 py-3 text-center font-bold text-sm text-slate-500" x-text="(pagination.current_page - 1) * pagination.per_page + index + 1"></div>
                                    </template>
                                    
                                    <template x-if="col.field === 'voucher_number'"><div class="font-bold text-slate-700" x-text="exp.voucher_number"></div></template>
                                    <template x-if="col.field === 'category_name'"><div class="font-bold text-indigo-600 text-xs" x-text="exp.category_name"></div></template>
                                    
                                    <template x-if="!['id','attachment','cash_amount','debt_amount','discount','exchange_rate','voucher_number','category_name'].includes(col.field)">
                                        <div class="px-3 text-xs text-slate-600 truncate text-center" x-text="exp[col.field] || '-'"></div>
                                    </template>
                                </td>
                            </template>

                            {{-- ACTIONS COLUMN --}}
                            <td class="px-3 py-2 text-center print:hidden sticky-action group-hover:bg-slate-100 transition-colors border-x border-slate-200/50">
                                <div class="flex items-center justify-center gap-2">
                                    <a :href="exp.edit_url" class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg transition" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <button @click="deleteRow(exp.delete_url)" class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg transition" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!loading && data.length === 0" x-transition.opacity class="bg-white"><td :colspan="Object.keys(columns).length + 2" class="py-10 text-center border-b border-slate-50"><span class="text-slate-400 font-medium mt-4">{{ __('account.no_data_found') ?? 'No Data Found' }}</span></td></tr>
                </tbody>
            </table>
        </div>
        
        <div class="mx-4 mt-2 px-4 py-3 bg-white border border-slate-200 rounded-lg flex justify-between items-center" x-show="pagination.last_page > 1">
            <div class="text-[10px] text-slate-500 font-medium"><span class="font-bold text-slate-700" x-text="pagination.from"></span> - <span class="font-bold text-slate-700" x-text="pagination.to"></span> / <span class="font-bold text-slate-700" x-text="pagination.total"></span></div>
            <div class="flex gap-1"><template x-for="link in pagination.links"><button @click="changePage(link.url)" x-html="link.label" :disabled="!link.url || link.active" class="w-7 h-7 flex items-center justify-center rounded-md text-[10px] font-bold transition-all border" :class="link.active ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white text-slate-500 hover:bg-slate-100 border-slate-200 hover:border-slate-300 disabled:opacity-50'" x-show="link.url"></button></template></div>
        </div>
        
        {{-- GLOBAL HIDDEN FORMS --}}
        <form id="delete-form" method="POST" style="display:none">@csrf @method('DELETE')</form>
        <form id="bulk-delete-form" action="{{ route('accountant.expenses.bulk-delete') }}" method="POST" class="hidden">@csrf @method('DELETE')<input type="hidden" name="ids" id="bulk-delete-ids"></form>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('expensesManager', () => {
                
                const defaultColumns = [
                    { field: 'id', label: "ڕێز", visible: true, width: 60, searchable: false },
                    { field: 'voucher_number', label: "ژمارەی بەڵگە", visible: true, width: 120, searchable: true },
                    { field: 'expense_date', label: "بەروار", visible: true, width: 110, searchable: true },
                    { field: 'category_name', label: "جۆری پسوڵە", visible: true, width: 160, searchable: true },
                    { field: 'cash_amount', label: "نەقد", visible: true, width: 120, searchable: true },
                    { field: 'debt_amount', label: "قەرز", visible: true, width: 120, searchable: true },
                    { field: 'discount', label: "داشکاندن", visible: true, width: 100, searchable: true },
                    { field: 'account_name', label: "هەژمار", visible: true, width: 140, searchable: true },
                    { field: 'currency_name', label: "جۆری پارە", visible: true, width: 90, searchable: true },
                    { field: 'exchange_rate', label: "نرخی پارە", visible: true, width: 90, searchable: true },
                    { field: 'cashbox_name', label: "قاسە", visible: true, width: 130, searchable: true },
                    { field: 'creator_name', label: "یوزەر", visible: true, width: 120, searchable: true },
                    { field: 'note', label: "تێبینی", visible: true, width: 160, searchable: true },
                    { field: 'manual_voucher', label: "بەڵگەی دەستی", visible: true, width: 110, searchable: true },
                    { field: 'attachment', label: "هاوپێچکردنی بەڵگە", visible: true, width: 120, searchable: false },
                ];
                
                let initialFilters = {};
                defaultColumns.forEach(c => { initialFilters[c.field] = ''; });

                return {
                    data: {!! json_encode($expenses->items(), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!},
                    pagination: {!! json_encode($expenses, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!},
                    
                    selectedIds: [], 
                    openFilter: null, draggingIndex: null, loading: false,
                    dateFilter: 'this_month', 
                    filters: initialFilters,
                    columns: defaultColumns,
                    params: { sort: 'expense_date', direction: 'desc', page: 1 },

                    initData() { 
                        const saved = localStorage.getItem('exp_table_v3');
                        if(saved) {
                            const savedCols = JSON.parse(saved);
                            this.columns = savedCols.map(s => {
                                const def = this.columns.find(c => c.field === s.field);
                                return def ? { ...def, visible: s.visible, width: s.width } : null;
                            }).filter(c => c !== null);
                        }
                    },
                    saveState() { localStorage.setItem('exp_table_v3', JSON.stringify(this.columns)); },
                    resetLayout() { localStorage.removeItem('exp_table_v3'); location.reload(); },

                    dragStart(e, i) { this.draggingIndex = i; e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', i); },
                    dragOver(e) { e.preventDefault(); e.dataTransfer.dropEffect = 'move'; },
                    drop(e, targetIndex) { if (this.draggingIndex === null || this.draggingIndex === targetIndex) return; const element = this.columns.splice(this.draggingIndex, 1)[0]; this.columns.splice(targetIndex, 0, element); this.draggingIndex = null; this.saveState(); },
                    initResize(e, col) { const startX = e.clientX; const startWidth = parseInt(col.width) || 100; const onMouseMove = (ev) => { col.width = Math.max(50, (document.dir === 'rtl' ? startWidth - (ev.clientX - startX) : startWidth + (ev.clientX - startX))); }; const onMouseUp = () => { window.removeEventListener('mousemove', onMouseMove); window.removeEventListener('mouseup', onMouseUp); this.saveState(); }; window.addEventListener('mousemove', onMouseMove); window.addEventListener('mouseup', onMouseUp); },
                    toggleAllSelection() { this.selectedIds = (this.selectedIds.length === this.data.length) ? [] : this.data.map(a => a.id); },
                    
                    deleteRow(url) {
                        const form = document.getElementById('delete-form');
                        form.action = url;
                        if (confirm("{{ __('account.are_you_sure') ?? 'Are you sure?' }}")) form.submit();
                    },
                    bulkDelete() {
                        if (this.selectedIds.length === 0) return;
                        document.getElementById('bulk-delete-ids').value = JSON.stringify(this.selectedIds);
                        if (confirm("{{ __('account.are_you_sure') ?? 'Are you sure?' }}")) document.getElementById('bulk-delete-form').submit();
                    },
                    
                    fetchData(pageUrl = null) { 
                        this.loading = true;
                        let targetUrl = new URL("{{ route('accountant.expenses.index') }}", window.location.origin); 
                        
                        if (pageUrl) {
                            let passedUrl = new URL(pageUrl, window.location.origin);
                            let page = passedUrl.searchParams.get('page');
                            if (page) targetUrl.searchParams.set('page', page);
                        } else {
                            targetUrl.searchParams.set('page', 1);
                        }
                        
                        if(this.dateFilter !== 'all') {
                            targetUrl.searchParams.set('date_filter', this.dateFilter);
                        }

                        if (this.params.sort) targetUrl.searchParams.set('sort', this.params.sort);
                        if (this.params.direction) targetUrl.searchParams.set('direction', this.params.direction);
                        
                        for (let key in this.filters) { 
                            let val = this.filters[key];
                            targetUrl.searchParams.set(key, val !== null && val !== undefined ? val : '');
                        }
                        
                        targetUrl.searchParams.set('_t', Date.now());
                        
                        fetch(targetUrl.toString(), { 
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } 
                        })
                        .then(res => { if(!res.ok) throw new Error('Network error'); return res.json(); })
                        .then(response => { 
                            this.data = response.data; 
                            this.pagination = response; 
                            this.loading = false; 
                        })
                        .catch(() => { this.loading = false; });
                    },
                    
                    sortBy(field) { if (this.params.sort === field) { this.params.direction = this.params.direction === 'asc' ? 'desc' : 'asc'; } else { this.params.sort = field; this.params.direction = 'asc'; } this.fetchData(); },
                    changePage(url) { if(url) this.fetchData(url); }
                };
            });
        });
    </script>
</x-app-layout>