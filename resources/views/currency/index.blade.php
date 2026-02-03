<x-app-layout>
    {{-- REQUIRED: Lottie Player Script --}}
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>

    {{-- STYLES --}}
    <style>
        /* --- CORE TABLE STYLES --- */
        .sheet-input { width: 100%; height: 100%; display: flex; align-items: center; background: transparent; border: 1px solid transparent; padding: 0 8px; font-size: 0.75rem; color: #1f2937; font-weight: 600; border-radius: 6px; transition: all 0.15s ease-in-out; }
        .sheet-input:focus { background-color: #fff; border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2); outline: none; }
        .sheet-input[readonly], .sheet-input[disabled] { cursor: default; color: #64748b; background-color: transparent; }
        
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

    @php
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super-admin'); 
        $userBranchId = $user->branch_id ?? null;
    @endphp

    <div x-data="tableManager()" x-init="initData()" class="py-6 w-full min-w-0 bg-white min-h-screen" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

        {{-- TOOLBAR --}}
        <div class="mx-4 mb-6 flex flex-col md:flex-row justify-between items-center gap-4 no-print">
            <div class="bg-slate-100 p-1 rounded-lg flex items-center shadow-inner">
                <span class="px-5 py-2 text-sm font-bold rounded-md bg-white text-indigo-600 shadow-sm transition">{{ __('currency.config_title') }}</span>
            </div>
            
            <div class="flex flex-wrap items-center gap-2">
                {{-- 🔥 CLOAKED BULK ACTIONS --}}
                <div x-show="selectedIds.length > 0" x-cloak x-transition class="flex items-center gap-2 bg-red-50 px-2 py-1 rounded-lg border border-red-100 mr-2 ml-2">
                    <span class="text-xs font-bold text-red-600 px-2"><span x-text="selectedIds.length"></span> {{ __('currency.selected') }}</span>
                    <x-btn type="bulk-delete" @click="bulkDelete()">{{ __('currency.delete_selected') }}</x-btn>
                    <button @click="selectedIds = []" type="button" class="px-2 py-1.5 text-slate-500 hover:text-slate-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>

                <x-btn type="trash" href="{{ route('currency.trash') }}" title="{{ __('currency.trash') }}" />
                
                <div x-data="{ open: false }" class="relative">
                    <x-btn type="columns" @click="open = !open" @click.away="open = false" title="{{ __('currency.columns') }}" />
                    {{-- 🔥 CLOAKED COLUMN DROPDOWN --}}
                    <div x-show="open" x-cloak class="absolute top-full mt-3 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 p-3 ltr:right-0 rtl:left-0">
                        <div class="flex justify-between items-center px-2 py-1 mb-2 border-b border-slate-100 pb-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">{{ __('currency.columns_title') }}</span>
                            <button @click="resetLayout(); open = false;" class="text-[10px] text-blue-500 hover:underline cursor-pointer">{{ __('currency.reset_layout') }}</button>
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

                <x-btn type="print" href="{{ route('currency.print') }}" target="_blank" title="{{ __('currency.print') }}" />
                <x-btn type="add" @click="addNewRow()" title="{{ __('currency.add_new') }}" />
            </div>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="relative w-full overflow-x-auto table-container bg-white shadow-sm rounded-lg border border-slate-200 mx-4 pb-20">
            <form id="singleRowForm" action="{{ route('currency.store') }}" method="POST" class="hidden">@csrf <div id="singleRowInputs"></div></form>
            
            <div class="overflow-x-auto custom-scrollbar flex-1 relative min-h-[400px]">
                <table class="w-full text-xs text-left rtl:text-right text-slate-500 whitespace-nowrap border-separate border-spacing-0">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-2 py-2 w-[40px] text-center bg-gray-50 sticky left-0 z-10"><input type="checkbox" @click="toggleAllSelection()" :checked="allSelected" class="select-checkbox bg-white"></th>

                            <template x-for="(col, index) in columns" :key="col.field">
                                <th class="px-2 py-1.5 relative select-none group border-b border-blue-100 bg-gray-50" 
                                    :style="'width:' + col.width + 'px'" 
                                    x-show="col.visible"
                                    draggable="true"
                                    @dragstart="dragStart($event, index)"
                                    @dragover.prevent="dragOver($event)"
                                    @drop="drop($event, index)"
                                    :class="[{'dragging-col': draggingIndex === index}, col.class]">
                                    
                                    <div class="th-container" :class="{ 'search-active': openFilter === col.field }">
                                        {{-- CENTERED HEADER TEXT --}}
                                        <div class="th-title">
                                            <div @click="sortBy(col.field)" class="flex items-center justify-center gap-1 cursor-pointer flex-1 h-full hover:text-indigo-600 transition-colors">
                                                <span x-text="col.label"></span>
                                                <svg class="w-3 h-3 text-indigo-500 transition-transform" :class="sortCol === col.field && !sortAsc ? 'rotate-180' : ''" x-show="sortCol === col.field" fill="currentColor" viewBox="0 0 20 20"><path d="M5 10l5-5 5 5H5z"/></svg>
                                            </div>
                                            <button type="button" @click="openFilter = col.field; setTimeout(() => $refs['input-'+col.field].focus(), 100)" class="text-slate-300 hover:text-indigo-500 transition-colors p-0.5" :class="filters[col.field] ? 'text-indigo-600' : ''">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                            </button>
                                        </div>
                                        {{-- 🔥 CLOAKED SEARCH INPUT --}}
                                        <div class="th-search" x-cloak>
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-1.5 pointer-events-none rtl:right-0 rtl:left-auto rtl:pr-1.5">
                                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                            </div>
                                            <input type="text" :x-ref="'input-'+col.field" x-model="filters[col.field]" @input.debounce="filterData()" @keydown.escape="openFilter = null" class="header-search-input" placeholder="{{ __('currency.search') }}">
                                            <button type="button" @click="filters[col.field] = ''; filterData(); openFilter = null;" class="absolute right-1 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500 p-0.5 rounded-md rtl:left-1 rtl:right-auto"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                        </div>
                                    </div>
                                    <div class="resizer" @mousedown.stop.prevent="initResize($event, col)"></div>
                                </th>
                            </template>

                            <th class="px-2 py-1.5 w-[70px] text-center print:hidden bg-gray-50 border-b border-blue-100">{{ __('currency.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <template x-for="(row, rowIndex) in filteredRows" :key="row.id || ('new-'+rowIndex)">
                            <tr class="bg-white hover:bg-slate-50 transition-colors group" 
                                :class="[editingId === row.id ? 'bg-indigo-50/20' : '', selectedIds.includes(row.id) ? 'bg-indigo-50/10' : '', row.isNew ? 'new-row' : '']">
                                
                                <td class="px-2 py-1 text-center"><input type="checkbox" :value="row.id" x-model="selectedIds" class="select-checkbox"></td>

                                <template x-for="col in columns" :key="col.field">
                                    <td x-show="col.visible" :class="col.field === 'id' || col.field === 'is_active' ? 'px-2 py-1 text-center' : 'p-0.5'">
                                        
                                        {{-- ID --}}
                                        <template x-if="col.field === 'id'">
                                            <div class="font-mono text-[10px] text-slate-400">
                                                <span x-text="String(row.id).startsWith('new-') ? getNextId() : row.id"></span>
                                                <input type="hidden" :name="'currencies['+rowIndex+'][id]'" :value="row.id">
                                            </div>
                                        </template>

                                        {{-- Name --}}
                                        <template x-if="col.field === 'currency_type'">
                                            <div>
                                                <span x-show="editingId !== row.id" x-text="row.currency_type" class="px-2 block text-slate-700 font-bold text-xs uppercase truncate"></span>
                                                <input x-show="editingId === row.id" type="text" :id="'input-type-'+row.id" x-model="row.currency_type" class="sheet-input font-bold text-xs uppercase">
                                            </div>
                                        </template>

                                        {{-- Symbol --}}
                                        <template x-if="col.field === 'symbol'">
                                            <div>
                                                <span x-show="editingId !== row.id" x-text="row.symbol" class="block text-center text-slate-600 font-normal text-xs"></span>
                                                <input x-show="editingId === row.id" type="text" x-model="row.symbol" class="sheet-input text-center font-normal text-xs">
                                            </div>
                                        </template>

                                        {{-- Hidden Number Point --}}
                                        <template x-if="col.field === 'digit_number'">
                                            <div>
                                                <span x-show="editingId !== row.id" x-text="row.digit_number" class="block text-center text-slate-400 font-normal text-[10px] bg-slate-50 rounded"></span>
                                                <input x-show="editingId === row.id" type="number" x-model="row.digit_number" class="sheet-input text-center font-normal text-xs">
                                            </div>
                                        </template>

                                        {{-- Price Total --}}
                                        <template x-if="col.field === 'price_total'">
                                            <div>
                                                <div x-show="editingId !== row.id" x-text="formatNumber(row.price_total)" class="text-center font-bold text-xs text-emerald-600"></div>
                                                <input x-show="editingId === row.id" type="text" :value="formatNumber(row.price_total)" @input="handlePriceInput($event, row)" class="sheet-input text-center font-bold text-xs text-emerald-600">
                                            </div>
                                        </template>

                                        {{-- Single Price --}}
                                        <template x-if="col.field === 'price_single'">
                                            <div>
                                                <div x-show="editingId !== row.id" x-text="formatNumber(row.price_single)" class="text-center font-bold text-xs text-blue-600"></div>
                                                <input x-show="editingId === row.id" type="text" :value="formatNumber(row.price_single)" readonly class="sheet-input text-center font-bold text-xs text-blue-600">
                                            </div>
                                        </template>

                                        {{-- Branch --}}
                                        <template x-if="col.field === 'branch_id'">
                                            <div>
                                                <span x-show="editingId !== row.id" x-text="getBranchName(row.branch_id)" class="px-2 block text-[10px] uppercase text-slate-500 font-normal truncate"></span>
                                                <template x-if="isSuperAdmin">
                                                    <select x-show="editingId === row.id" x-model="row.branch_id" class="sheet-input font-normal text-xs text-slate-700">
                                                        <option value="" disabled>{{ __('currency.select_branch') }}</option>
                                                        <template x-for="branch in branches" :key="branch.id">
                                                            <option :value="branch.id" x-text="branch.name" :selected="row.branch_id == branch.id"></option>
                                                        </template>
                                                    </select>
                                                </template>
                                                <template x-if="!isSuperAdmin">
                                                    <div x-show="editingId === row.id" class="sheet-input font-normal text-xs text-slate-500 flex items-center">
                                                        <span x-text="getBranchName(row.branch_id)"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>

                                        {{-- User --}}
                                        <template x-if="col.field === 'creator'">
                                            <div class="px-2 block text-[10px] uppercase text-slate-400 font-bold text-center truncate" x-text="row.creator_name"></div>
                                        </template>

                                        {{-- Date --}}
                                        <template x-if="col.field === 'date'">
                                            <div class="px-2 block text-[10px] font-mono text-slate-400 text-center truncate" x-text="row.formatted_date"></div>
                                        </template>

                                        {{-- Active --}}
                                        <template x-if="col.field === 'is_active'">
                                            <div class="flex items-center justify-center">
                                                <input type="checkbox" value="1" :checked="row.is_active" @change="row.is_active = $event.target.checked" :disabled="editingId !== row.id" class="w-3.5 h-3.5 text-indigo-600 rounded border-slate-300 cursor-pointer">
                                            </div>
                                        </template>
                                    </td>
                                </template>
                                
                                {{-- Actions --}}
                                <td class="px-2 py-1 text-center print:hidden sticky-action group-hover:bg-slate-50 transition-colors">
                                    <div class="flex items-center justify-center gap-2">
                                        <template x-if="editingId === row.id">
                                            <div class="flex items-center gap-1">
                                                <x-btn type="cancel" @click="cancelEdit(row)" title="{{ __('currency.cancel') }}" />
                                                <x-btn type="save" @click="saveRow(row)" title="{{ __('currency.save') }}" />
                                            </div>
                                        </template>
                                        <template x-if="editingId !== row.id">
                                            <div class="flex items-center gap-2">
                                                <x-btn type="delete" @click="deleteRow(row.id)" title="{{ __('currency.delete') }}" />
                                                <x-btn type="edit" @click="startEdit(row.id)" title="{{ __('currency.edit') }}" />
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
            
            {{-- HIDDEN FORMS --}}
            <form id="delete-form" action="" method="POST" class="hidden">@csrf @method('DELETE')</form>
            <form id="bulk-delete-form" action="{{ route('currency.bulk-delete') }}" method="POST" class="hidden">@csrf @method('DELETE')<input type="hidden" name="ids" id="bulk-delete-ids"></form>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('tableManager', () => ({
                originalRows: @json($currencies->map(function($c) {
                    $c->price_total = (float)$c->price_total;
                    $c->price_single = (float)$c->price_single;
                    $c->creator_name = $c->creator?->name ?? __('currency.system'); 
                    $c->formatted_date = $c->created_at ? $c->created_at->format('Y-m-d') : now()->format('Y-m-d');
                    return $c;
                })),
                branches: @json($branches),
                isSuperAdmin: {{ $isSuperAdmin ? 'true' : 'false' }},
                userBranchId: {{ $userBranchId ?? 'null' }},
                
                filteredRows: [],
                editingId: null,
                selectedIds: [],
                openFilter: null,
                sortCol: null,
                sortAsc: true,
                filters: {},
                draggingIndex: null,
                
                defaultColumns: [
                    { field: 'id', label: '#', visible: true, width: 40 },
                    { field: 'currency_type', label: '{{ __('currency.name') }}', visible: true, width: 110 },
                    { field: 'symbol', label: '{{ __('currency.symbol') }}', visible: true, width: 60 },
                    { field: 'digit_number', label: '{{ __('currency.hidden_digit') }}', visible: false, width: 80 }, 
                    { field: 'price_total', label: '{{ __('currency.price_total') }}', visible: true, width: 100 },
                    { field: 'price_single', label: '{{ __('currency.price_single') }}', visible: true, width: 100 },
                    { field: 'branch_id', label: '{{ __('currency.branch') }}', visible: true, width: 120 },
                    { field: 'creator', label: '{{ __('currency.created_by') }}', visible: true, width: 90 }, 
                    { field: 'date', label: '{{ __('currency.date') }}', visible: true, width: 90 }, 
                    { field: 'is_active', label: '{{ __('currency.active') }}', visible: true, width: 60 },
                ],
                columns: [],

                initData() {
                    this.filteredRows = JSON.parse(JSON.stringify(this.originalRows));
                    const savedCols = localStorage.getItem('currency_columns_v9');
                    this.columns = savedCols ? JSON.parse(savedCols) : JSON.parse(JSON.stringify(this.defaultColumns));
                    this.columns.forEach(col => { this.filters[col.field] = ''; });
                },

                resetLayout() {
                    localStorage.removeItem('currency_columns_v9');
                    this.columns = JSON.parse(JSON.stringify(this.defaultColumns));
                    this.columns.forEach(col => { this.filters[col.field] = ''; });
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
                    const startX = e.clientX; const startWidth = parseInt(col.width) || 100;
                    const onMouseMove = (moveEvent) => {
                        const diff = moveEvent.clientX - startX;
                        const isRtl = document.dir === 'rtl';
                        col.width = Math.max(40, (isRtl ? startWidth - diff : startWidth + diff));
                    };
                    const onMouseUp = () => {
                        window.removeEventListener('mousemove', onMouseMove);
                        window.removeEventListener('mouseup', onMouseUp);
                        this.saveState();
                    };
                    window.addEventListener('mousemove', onMouseMove);
                    window.addEventListener('mouseup', onMouseUp);
                },

                saveState() { localStorage.setItem('currency_columns_v9', JSON.stringify(this.columns)); },

                get allSelected() { return this.filteredRows.length > 0 && this.selectedIds.length === this.filteredRows.length; },
                toggleAllSelection() { this.selectedIds = this.allSelected ? [] : this.filteredRows.map(r => r.id); },
                
                addNewRow() {
                    const newId = 'new-' + Date.now();
                    this.filteredRows.unshift({ 
                        id: newId, 
                        currency_type: '', 
                        symbol: '', 
                        digit_number: 0, 
                        price_total: 0, 
                        price_single: 0, 
                        branch_id: this.isSuperAdmin ? '' : this.userBranchId, 
                        is_active: 1, 
                        creator_name: '{{ Auth::user()->name }}',
                        formatted_date: new Date().toISOString().split('T')[0],
                        isNew: true 
                    });
                    this.startEdit(newId);
                },
                startEdit(id) { this.editingId = id; setTimeout(() => { document.getElementById('input-type-'+id)?.focus(); }, 100); },
                cancelEdit(row) {
                    if (String(row.id).startsWith('new-')) { this.filteredRows = this.filteredRows.filter(r => r.id !== row.id); }
                    this.editingId = null;
                },
                saveRow(row) {
                    const formContainer = document.getElementById('singleRowInputs'); formContainer.innerHTML = '';
                    const createInput = (name, value) => { 
                        const i = document.createElement('input'); i.type = 'hidden'; 
                        i.name = `currencies[0][${name}]`; 
                        i.value = (value !== null && value !== undefined && value !== '') ? value : 0; 
                        formContainer.appendChild(i); 
                    };
                    createInput('id', String(row.id).startsWith('new-') ? '' : row.id);
                    createInput('currency_type', row.currency_type); createInput('symbol', row.symbol); createInput('digit_number', row.digit_number);
                    createInput('price_total', row.price_total); createInput('price_single', row.price_single); createInput('branch_id', row.branch_id); createInput('is_active', row.is_active ? 1 : 0);
                    document.getElementById('singleRowForm').submit();
                },
                
                filterData() {
                    this.filteredRows = this.originalRows.filter(row => {
                        return this.columns.every(col => {
                            if (!col.visible) return true; 
                            const filterVal = this.filters[col.field]?.toLowerCase() || '';
                            if (!filterVal) return true;
                            
                            let cellVal = String(row[col.field] || '');
                            if (col.field === 'branch_id') cellVal = this.getBranchName(row.branch_id);
                            if (col.field === 'creator') cellVal = row.creator_name;
                            if (col.field === 'date') cellVal = row.formatted_date;
                            
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
                        
                        if (this.sortCol === 'branch_id') { valA = this.getBranchName(a.branch_id); valB = this.getBranchName(b.branch_id); }
                        else if (this.sortCol === 'creator') { valA = a.creator_name; valB = b.creator_name; }
                        else if (this.sortCol === 'date') { valA = a.formatted_date; valB = b.formatted_date; }

                        if (this.sortCol === 'id' || (!isNaN(parseFloat(valA)) && isFinite(valA))) {
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

                getNextId() {
                    const ids = this.originalRows.map(r => parseInt(r.id)).filter(n => !isNaN(n));
                    const max = ids.length ? Math.max(...ids) : 0;
                    return max + 1;
                },
                getBranchName(id) { const b = this.branches.find(b => b.id == id); return b ? b.name : '-'; },
                formatNumber(num) { if (!num) return ''; return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","); },
                handlePriceInput(e, row) { const val = parseFloat(e.target.value.replace(/,/g, '')) || 0; row.price_total = val; row.price_single = val / 100; e.target.value = this.formatNumber(val); },
                
                deleteRow(id) {
                    const form = document.getElementById('delete-form'); form.action = "{{ route('currency.destroy', ':id') }}".replace(':id', id);
                    if (window.confirmAction) { window.confirmAction('delete-form', "{{ __('currency.delete_confirm') }}"); } 
                    else { if(confirm("{{ __('currency.delete_confirm') }}")) form.submit(); }
                },
                
                bulkDelete() {
                    if (this.selectedIds.length === 0) return;
                    document.getElementById('bulk-delete-ids').value = JSON.stringify(this.selectedIds);
                    if (window.confirmAction) { window.confirmAction('bulk-delete-form', '{{ __('currency.bulk_delete_confirm') }}'); } 
                    else { if(confirm('{{ __('currency.bulk_delete_confirm') }}')) document.getElementById('bulk-force-delete-form').submit(); }
                }
            }));
        });
    </script>
</x-app-layout>