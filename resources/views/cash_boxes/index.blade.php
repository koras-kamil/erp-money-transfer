<x-app-layout>
    {{-- STYLES --}}
    <style>
        /* --- CORE TABLE STYLES --- */
        .sheet-input { width: 100%; height: 100%; display: flex; align-items: center; background: transparent; border: 1px solid transparent; padding: 0 12px; font-size: 0.75rem; color: #1f2937; font-weight: 500; border-radius: 6px; transition: all 0.15s ease-in-out; }
        .sheet-input:focus { background-color: #fff; border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2); outline: none; }
        .sheet-input[readonly], .sheet-input[disabled] { cursor: default; color: #64748b; background-color: transparent; }
        
        /* Red Placeholder for Required Fields */
        .placeholder-red-400::placeholder { color: #f87171; opacity: 1; }
        
        select.sheet-input {
            -webkit-appearance: none; appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.25rem center; background-repeat: no-repeat; background-size: 1em 1em;
            padding-right: 1.5rem; padding-left: 0.5rem; cursor: pointer; white-space: nowrap; 
        }
        [dir="rtl"] select.sheet-input { background-position: left 0.25rem center; padding-right: 0.5rem; padding-left: 1.5rem; }
        
        /* Checkbox & Scrollbar */
        .select-checkbox { width: 1rem; height: 1rem; border-radius: 4px; border: 1px solid #cbd5e1; color: #6366f1; cursor: pointer; transition: all 0.2s; }
        .table-container::-webkit-scrollbar { height: 6px; }
        .table-container::-webkit-scrollbar-track { background: #f1f1f1; }
        .table-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        
        /* Animation */
        @keyframes slideIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
        .new-row { animation: slideIn 0.3s ease-out forwards; background-color: #f0fdf4 !important; }
        .ag-row-editing { background-color: #f0fdf4 !important; border-bottom: 1px solid #bbf7d0 !important; }

        /* --- HEADER INTERACTIVITY --- */
        .th-container { position: relative; width: 100%; height: 28px; display: flex; align-items: center; overflow: hidden; }
        .th-title { position: absolute; inset: 0; display: flex; align-items: center; justify-content: space-between; gap: 4px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); transform: translateY(0); opacity: 1; cursor: grab; }
        .th-title:active { cursor: grabbing; }
        .search-active .th-title { transform: translateY(-100%); opacity: 0; pointer-events: none; }

        .th-search { position: absolute; inset: 0; display: flex; align-items: center; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); transform: translateY(100%); opacity: 0; pointer-events: none; }
        .search-active .th-search { transform: translateY(0); opacity: 1; pointer-events: auto; }

        .header-search-input { width: 100%; height: 100%; background-color: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 6px; padding-left: 24px; padding-right: 20px; font-size: 0.7rem; color: #1f2937; transition: all 0.15s; }
        [dir="rtl"] .header-search-input { padding-left: 20px; padding-right: 24px; }
        .header-search-input:focus { background-color: #fff; border-color: #3b82f6; outline: none; box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1); }

        .resizer { position: absolute; right: -4px; top: 0; height: 100%; width: 8px; cursor: col-resize; z-index: 50; touch-action: none; }
        .resizer:hover::after, .resizing::after { content: ''; position: absolute; right: 4px; top: 20%; height: 60%; width: 2px; background-color: #3b82f6; }
        [dir="rtl"] .resizer { right: auto; left: -4px; }
        [dir="rtl"] .resizer:hover::after { right: auto; left: 4px; }
        .dragging-col { opacity: 0.4; background-color: #e0e7ff; border: 2px dashed #6366f1; }

        @media print { .no-print, button, .print\:hidden { display: none !important; } .overflow-x-auto { overflow: visible !important; } table { width: 100% !important; } }
    </style>

    <div x-data="tableManager()" x-init="initData()" class="py-6 w-full min-w-0 bg-white min-h-screen" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

        {{-- ERROR MESSAGES --}}
        @if ($errors->any())
            <div class="mx-4 mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <ul class="list-disc list-inside text-sm text-red-600">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- TOOLBAR --}}
        <div class="mx-4 mb-6 flex flex-col md:flex-row justify-between items-center gap-4 no-print">
            <div class="bg-slate-100 p-1 rounded-lg flex items-center shadow-inner">
                <span class="px-5 py-2 text-sm font-bold rounded-md bg-white text-indigo-600 shadow-sm transition">{{ __('cash_box.title') }}</span>
            </div>
            
            <div class="flex flex-wrap items-center gap-2">
                {{-- Bulk Actions --}}
                <div x-show="selectedIds.length > 0" x-transition class="flex items-center gap-2 bg-red-50 px-2 py-1 rounded-lg border border-red-100 mr-2 ml-2">
                    <span class="text-xs font-bold text-red-600 px-2"><span x-text="selectedIds.length"></span> {{ __('cash_box.selected') }}</span>
                    <x-btn type="bulk-delete" @click="bulkDelete()">{{ __('cash_box.delete_selected') }}</x-btn>
                    <button @click="selectedIds = []" type="button" class="px-2 py-1.5 text-slate-500 hover:text-slate-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>

                <x-btn type="trash" href="{{ route('cash-boxes.trash') }}" title="{{ __('cash_box.trash') }}" />
                
                {{-- Column Config --}}
                <div x-data="{ open: false }" class="relative">
                    <x-btn type="columns" @click="open = !open" @click.away="open = false" title="{{ __('cash_box.columns') }}" />
                    <div x-show="open" class="absolute top-full mt-3 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 p-3 ltr:right-0 rtl:left-0" style="display:none;">
                        <div class="flex justify-between items-center px-2 py-1 mb-2 border-b border-slate-100 pb-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">{{ __('cash_box.columns') }}</span>
                            <button @click="resetLayout(); open = false;" class="text-[10px] text-blue-500 hover:underline cursor-pointer">{{ __('cash_box.reset_layout') }}</button>
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

                <x-btn type="print" href="{{ route('cash-boxes.downloadPdf') }}" title="{{ __('cash_box.print') }}" />
                <x-btn type="add" @click="addNewRow()" title="{{ __('cash_box.add_new') }}" />
            </div>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="relative w-full overflow-x-auto table-container bg-white shadow-sm rounded-lg border border-slate-200 mx-4 pb-20">
            <form id="singleRowForm" action="{{ route('cash-boxes.store-bulk') }}" method="POST" class="hidden">@csrf <div id="singleRowInputs"></div></form>
            <table class="w-full text-sm text-left rtl:text-right text-slate-500 whitespace-nowrap border-separate border-spacing-0">
                <thead class="text-xs text-slate-700 uppercase bg-blue-50/50 border-b border-blue-100 sticky top-0 z-20">
                    <tr>
                        <th class="px-4 py-3 w-[40px] text-center bg-slate-50/95 border-b border-blue-100"><input type="checkbox" @click="toggleAllSelection()" :checked="allSelected" class="select-checkbox bg-white"></th>

                        {{-- Draggable Columns --}}
                        <template x-for="(col, index) in columns" :key="col.field">
                            <th x-show="col.visible" 
                                class="px-4 py-2 relative h-12 transition-colors duration-200 border-r border-transparent select-none group border-b border-blue-100 bg-slate-50/95"
                                :style="'width:' + col.width + 'px'"
                                draggable="true"
                                @dragstart="dragStart($event, index)"
                                @dragover.prevent="dragOver($event)"
                                @drop="drop($event, index)"
                                :class="[{'dragging-col': draggingIndex === index}, col.class]">
                                
                                <div class="th-container" :class="{ 'search-active': openFilter === col.field }">
                                    {{-- Title --}}
                                    <div class="th-title">
                                        <div @click="sortBy(col.field)" class="flex items-center justify-center gap-1 cursor-pointer flex-1 h-full hover:text-indigo-600 transition-colors">
                                            <span x-text="col.label"></span>
                                            <svg class="w-3 h-3 text-indigo-500 transition-transform" :class="sortCol === col.field && !sortAsc ? 'rotate-180' : ''" x-show="sortCol === col.field" fill="currentColor" viewBox="0 0 20 20"><path d="M5 10l5-5 5 5H5z"/></svg>
                                        </div>
                                        <button type="button" @click="openFilter = col.field; setTimeout(() => $refs['input-'+col.field].focus(), 100)" class="p-1 rounded-md text-slate-400 hover:text-indigo-600 hover:bg-slate-100 transition" :class="filters[col.field] ? 'text-indigo-600' : ''">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </button>
                                    </div>
                                    {{-- Search --}}
                                    <div class="th-search">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-1.5 pointer-events-none rtl:right-0 rtl:left-auto rtl:pr-1.5">
                                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </div>
                                        <input type="text" :x-ref="'input-'+col.field" x-model="filters[col.field]" @input.debounce="filterData()" @keydown.escape="openFilter = null" class="header-search-input" placeholder="{{ __('cash_box.search') }}">
                                        <button type="button" @click="filters[col.field] = ''; filterData(); openFilter = null;" class="absolute right-1 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500 p-0.5 rounded-md rtl:left-1 rtl:right-auto"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                    </div>
                                </div>
                                <div class="resizer" @mousedown.stop.prevent="initResize($event, col)"></div>
                            </th>
                        </template>

                        <th class="px-4 py-3 w-[5%] text-center print:hidden bg-blue-50/50 border-b border-blue-100">{{ __('cash_box.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <template x-for="(row, rowIndex) in filteredRows" :key="row.id || ('new-'+rowIndex)">
                        <tr class="bg-white hover:bg-slate-50 transition-colors group" 
                            :class="[editingId === row.id ? 'bg-indigo-50/20' : '', selectedIds.includes(row.id) ? 'bg-indigo-50/10' : '', row.isNew ? 'new-row' : '']">
                            
                            <td class="px-4 py-2 text-center"><input type="checkbox" :value="row.id" x-model="selectedIds" class="select-checkbox"></td>

                            {{-- Cells Loop --}}
                            <template x-for="col in columns" :key="col.field">
                                <td x-show="col.visible" :class="col.field === 'id' ? 'px-4 py-4 text-center font-mono text-xs text-slate-400' : 'p-1'">
                                    
                                    {{-- ID --}}
                                    <template x-if="col.field === 'id'">
                                        <div>
                                            <span x-text="String(row.id).startsWith('new-') ? getNextId() : row.id"></span>
                                            <input type="hidden" :name="'boxes['+rowIndex+'][id]'" :value="row.id">
                                        </div>
                                    </template>

                                    {{-- Name (UPDATED: Regular Font + Red Text Placeholder) --}}
                                    <template x-if="col.field === 'name'">
                                        <div>
                                            <span x-show="editingId !== row.id" x-text="row.name" class="px-3 block text-slate-700 font-bold text-xs truncate"></span>
                                            <input x-show="editingId === row.id" 
                                                   :id="'input-name-'+row.id" 
                                                   type="text" 
                                                   x-model="row.name" 
                                                   class="sheet-input font-normal text-xs text-slate-700 placeholder-red-400" 
                                                   placeholder="{{ __('cash_box.required') }}">
                                        </div>
                                    </template>

                                    {{-- Type --}}
                                    <template x-if="col.field === 'type'">
                                        <div>
                                            <span x-show="editingId !== row.id" x-text="row.type" class="px-3 block text-slate-600 font-normal text-xs truncate"></span>
                                            <input x-show="editingId === row.id" type="text" x-model="row.type" class="sheet-input font-normal text-xs">
                                        </div>
                                    </template>

                                    {{-- Currency --}}
                                    <template x-if="col.field === 'currency'">
                                        <div>
                                            <div x-show="editingId !== row.id" class="text-center"><span x-text="getCurrencyName(row.currency_id)" class="bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded text-[10px] font-bold uppercase"></span></div>
                                            <select x-show="editingId === row.id" x-model="row.currency_id" class="sheet-input text-center text-xs uppercase font-normal text-indigo-600">
                                                <template x-for="curr in currencies" :key="curr.id"><option :value="curr.id" x-text="curr.currency_type"></option></template>
                                            </select>
                                        </div>
                                    </template>

                                    {{-- Balance --}}
                                    <template x-if="col.field === 'balance'">
                                        <div>
                                            <div x-show="editingId !== row.id" x-text="formatNumberDisplay(row.balance)" class="text-center font-bold text-xs text-emerald-600"></div>
                                            <input x-show="editingId === row.id" type="text" :value="formatNumberDisplay(row.balance)" @input="handleNumberInput($event, row)" class="sheet-input text-center font-normal text-xs text-emerald-600">
                                        </div>
                                    </template>

                                    {{-- Branch --}}
                                    <template x-if="col.field === 'branch'">
                                        <div>
                                            <span x-show="editingId !== row.id" x-text="getBranchName(row.branch_id)" class="px-2 block text-[10px] uppercase text-slate-500 font-normal truncate"></span>
                                            <select x-show="editingId === row.id" x-model="row.branch_id" class="sheet-input text-xs font-normal text-slate-700">
                                                <option value="" disabled>Select Branch</option>
                                                <template x-for="branch in branches" :key="branch.id"><option :value="branch.id" x-text="branch.name"></option></template>
                                            </select>
                                        </div>
                                    </template>

                                    {{-- Desc --}}
                                    <template x-if="col.field === 'desc'">
                                        <div>
                                            <span x-show="editingId !== row.id" x-text="row.description || '-'" class="px-3 block text-slate-500 text-xs italic font-normal truncate"></span>
                                            <input x-show="editingId === row.id" type="text" x-model="row.description" class="sheet-input font-normal text-xs">
                                        </div>
                                    </template>

                                    {{-- User --}}
                                    <template x-if="col.field === 'user'">
                                        <div class="px-4 py-4 text-[10px] uppercase text-slate-400 font-normal text-center truncate" x-text="getUserName(row.user_id)"></div>
                                    </template>

                                    {{-- Date --}}
                                    <template x-if="col.field === 'created_at'">
                                        <div class="px-4 py-4 text-center text-xs text-slate-400 font-mono font-normal" x-text="formatDate(row.created_at)"></div>
                                    </template>

                                    {{-- Active --}}
                                    <template x-if="col.field === 'active'">
                                        <div class="flex items-center justify-center">
                                            <input type="checkbox" value="1" :checked="row.is_active" @change="row.is_active = $event.target.checked" :disabled="editingId !== row.id" class="w-4 h-4 text-indigo-600 rounded border-slate-300 cursor-pointer">
                                        </div>
                                    </template>
                                </td>
                            </template>
                            
                            {{-- Actions --}}
                            <td class="px-3 py-2 text-center print:hidden sticky-action group-hover:bg-slate-50 transition-colors">
                                <div class="flex items-center justify-center gap-2">
                                    <template x-if="editingId === row.id">
                                        <div class="flex items-center gap-1">
                                            <x-btn type="cancel" @click="cancelEdit(row)" title="{{ __('cash_box.cancel') }}" />
                                            <x-btn type="save" @click="saveRow(row)" title="{{ __('cash_box.save') }}" />
                                        </div>
                                    </template>
                                    <template x-if="editingId !== row.id">
                                        <div class="flex items-center gap-2">
                                            <x-btn type="delete" @click="deleteRow(row.id)" title="{{ __('cash_box.delete') }}" />
                                            <x-btn type="edit" @click="startEdit(row.id)" title="{{ __('cash_box.edit') }}" />
                                        </div>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>

                    {{-- NO DATA COMPONENT --}}
                    <x-no-data />

                </tbody>
            </table>
        </div>
        <form id="delete-form" action="" method="POST" class="hidden">@csrf @method('DELETE')</form>
        <form id="bulk-delete-form" action="{{ route('cash-boxes.bulk-delete') }}" method="POST" class="hidden">@csrf @method('DELETE')<input type="hidden" name="ids" id="bulk-delete-ids"></form>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('tableManager', () => ({
                originalRows: @json($cashBoxes instanceof \Illuminate\Pagination\LengthAwarePaginator ? $cashBoxes->items() : $cashBoxes), 
                currencies: @json($currencies),
                branches: @json($branches),
                filteredRows: [],
                editingId: null,
                selectedIds: [],
                openFilter: null,
                sortCol: null,
                sortAsc: true,
                filters: {},
                draggingIndex: null,
                
                // UPDATED COLUMN ORDER
                defaultColumns: [
                    { field: 'id', label: '#', visible: true, width: 50 },
                    { field: 'name', label: '{{ __('cash_box.name') }}', visible: true, width: 200 },
                    { field: 'type', label: '{{ __('cash_box.type') }}', visible: true, width: 120 },
                    { field: 'currency', label: '{{ __('cash_box.currency') }}', visible: true, width: 100 },
                    { field: 'balance', label: '{{ __('cash_box.balance') }}', visible: true, width: 120 },
                    { field: 'branch', label: '{{ __('cash_box.branch') }}', visible: true, width: 150 },
                    { field: 'desc', label: '{{ __('cash_box.desc') }}', visible: true, width: 250 },
                    { field: 'user', label: '{{ __('cash_box.user') }}', visible: true, width: 100 },
                    { field: 'created_at', label: '{{ __('cash_box.created_at') }}', visible: true, width: 150 },
                    { field: 'active', label: '{{ __('cash_box.active') }}', visible: true, width: 80 },
                ],
                columns: [],

                initData() {
                    this.filteredRows = JSON.parse(JSON.stringify(this.originalRows));
                    const savedCols = localStorage.getItem('cashbox_columns_v3');
                    this.columns = savedCols ? JSON.parse(savedCols) : JSON.parse(JSON.stringify(this.defaultColumns));
                    this.columns.forEach(col => { this.filters[col.field] = ''; });
                },

                resetLayout() {
                    localStorage.removeItem('cashbox_columns_v3');
                    this.columns = JSON.parse(JSON.stringify(this.defaultColumns));
                    this.columns.forEach(col => { this.filters[col.field] = ''; });
                },

                // Drag & Drop
                dragStart(e, index) { this.draggingIndex = index; e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', index); },
                dragOver(e) { e.preventDefault(); e.dataTransfer.dropEffect = 'move'; },
                drop(e, targetIndex) {
                    if (this.draggingIndex === null || this.draggingIndex === targetIndex) return;
                    const element = this.columns.splice(this.draggingIndex, 1)[0];
                    this.columns.splice(targetIndex, 0, element);
                    this.draggingIndex = null;
                    this.saveState();
                },

                // Resizing
                initResize(e, col) {
                    const startX = e.clientX; 
                    const startWidth = parseInt(col.width) || 100;
                    const onMouseMove = (moveEvent) => {
                        const diff = moveEvent.clientX - startX;
                        const isRtl = document.dir === 'rtl';
                        col.width = Math.max(50, (isRtl ? startWidth - diff : startWidth + diff));
                    };
                    const onMouseUp = () => {
                        window.removeEventListener('mousemove', onMouseMove);
                        window.removeEventListener('mouseup', onMouseUp);
                        this.saveState();
                    };
                    window.addEventListener('mousemove', onMouseMove);
                    window.addEventListener('mouseup', onMouseUp);
                },

                saveState() { localStorage.setItem('cashbox_columns_v3', JSON.stringify(this.columns)); },

                // Data Helpers
                getCurrencyName(id) { const c = this.currencies.find(c => c.id == id); return c ? c.currency_type : '-'; },
                getBranchName(id) { const b = this.branches.find(b => b.id == id); return b ? b.name : '-'; },
                getUserName(id) { return id ? '{{ Auth::user()->name }}' : 'SYSTEM'; },
                formatDate(date) { return date ? new Date(date).toISOString().slice(0,16).replace('T',' ') : '-'; },
                formatNumberDisplay(num) { return num ? num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") : ''; },
                handleNumberInput(e, row) { 
                    let val = e.target.value.replace(/,/g, '');
                    if (!isNaN(val)) { row.balance = val; e.target.value = this.formatNumberDisplay(val); }
                },
                
                // Get Next ID
                getNextId() {
                    const ids = this.originalRows.map(r => parseInt(r.id)).filter(n => !isNaN(n));
                    const max = ids.length ? Math.max(...ids) : 0;
                    return max + 1;
                },

                // Actions
                get allSelected() { return this.filteredRows.length > 0 && this.selectedIds.length === this.filteredRows.length; },
                toggleAllSelection() { this.selectedIds = this.allSelected ? [] : this.filteredRows.map(r => r.id); },
                
                addNewRow() {
                    const newId = 'new-' + Date.now();
                    const defaultBranch = this.branches.length > 0 ? this.branches[0].id : '';
                    const defaultCurrency = this.currencies.length > 0 ? this.currencies[0].id : '';
                    this.filteredRows.unshift({ id: newId, name: '', type: '', currency_id: defaultCurrency, balance: 0, branch_id: defaultBranch, description: '', user_id: {{ Auth::id() }}, created_at: new Date(), is_active: 1, isNew: true });
                    this.startEdit(newId);
                },
                startEdit(id) { 
                    this.editingId = id;
                    setTimeout(() => { document.getElementById('input-name-'+id)?.focus(); }, 100);
                },
                cancelEdit(row) {
                    if (String(row.id).startsWith('new-')) { this.filteredRows = this.filteredRows.filter(r => r.id !== row.id); }
                    this.editingId = null;
                },
                
                // SAVE LOGIC WITH NICE NOTIFICATION
                saveRow(row) {
                    // Client-Side Validation with SweetAlert
                    if (!row.name || row.name.trim() === '') {
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __("messages.error") }}',
                            text: '{{ __("cash_box.name_required") }}',
                            confirmButtonColor: '#4f46e5'
                        });
                        return;
                    }
                    if (!row.branch_id) {
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __("messages.error") }}',
                            text: '{{ __("cash_box.branch_required") }}',
                            confirmButtonColor: '#4f46e5'
                        });
                        return;
                    }
                    if (!row.currency_id) {
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __("messages.error") }}',
                            text: '{{ __("cash_box.currency_required") }}',
                            confirmButtonColor: '#4f46e5'
                        });
                        return;
                    }

                    const formContainer = document.getElementById('singleRowInputs');
                    formContainer.innerHTML = '';
                    const createInput = (name, value) => { const i = document.createElement('input'); i.type = 'hidden'; i.name = `boxes[0][${name}]`; i.value = value || ''; formContainer.appendChild(i); };
                    
                    const idToSend = String(row.id).startsWith('new-') ? '' : row.id;
                    
                    createInput('id', idToSend);
                    createInput('name', row.name);
                    createInput('type', row.type); 
                    createInput('currency_id', row.currency_id);
                    createInput('balance', row.balance); 
                    createInput('branch_id', row.branch_id); 
                    createInput('description', row.description);
                    createInput('is_active', row.is_active ? 1 : 0);
                    
                    document.getElementById('singleRowForm').submit();
                },
                
                // Sorting & Filtering
                filterData() {
                    this.filteredRows = this.originalRows.filter(row => {
                        return this.columns.every(col => {
                            const filterVal = this.filters[col.field]?.toLowerCase() || '';
                            if (!filterVal) return true;
                            let cellVal = String(row[col.field] || '');
                            if (col.field === 'branch') cellVal = this.getBranchName(row.branch_id);
                            if (col.field === 'currency') cellVal = this.getCurrencyName(row.currency_id);
                            return cellVal.toLowerCase().includes(filterVal);
                        });
                    });
                    this.sortData();
                },
                sortBy(field) {
                    if (this.sortCol === field) this.sortAsc = !this.sortAsc; else { this.sortCol = field; this.sortAsc = true; }
                    this.sortData();
                },
                sortData() {
                    if (!this.sortCol) return;
                    this.filteredRows.sort((a, b) => {
                        let valA = a[this.sortCol]; let valB = b[this.sortCol];
                        if (this.sortCol === 'id' || (!isNaN(parseFloat(valA)) && isFinite(valA))) {
                            if(String(valA).startsWith('new-')) valA = this.sortAsc ? 99999999 : -99999999;
                            if(String(valB).startsWith('new-')) valB = this.sortAsc ? 99999999 : -99999999;
                            valA = parseFloat(valA); valB = parseFloat(valB);
                        } else {
                            valA = (valA || '').toString().toLowerCase();
                            valB = (valB || '').toString().toLowerCase();
                        }
                        if (valA < valB) return this.sortAsc ? -1 : 1; 
                        if (valA > valB) return this.sortAsc ? 1 : -1; 
                        return 0;
                    });
                },
                deleteRow(id) {
                    const form = document.getElementById('delete-form'); form.action = "{{ route('cash-boxes.destroy', ':id') }}".replace(':id', id);
                    if (window.confirmAction) { window.confirmAction('delete-form', "{{ __('cash_box.delete_confirm') }}"); } 
                    else { if(confirm("{{ __('cash_box.delete_confirm') }}")) form.submit(); }
                },
                bulkDelete() {
                    if (this.selectedIds.length === 0) return;
                    document.getElementById('bulk-delete-ids').value = JSON.stringify(this.selectedIds);
                    if (window.confirmAction) { window.confirmAction('bulk-delete-form', '{{ __('cash_box.bulk_delete_confirm') }}'); } 
                    else { if(confirm('{{ __('cash_box.bulk_delete_confirm') }}')) document.getElementById('bulk-delete-form').submit(); }
                }
            }));
        });
    </script>
</x-app-layout>