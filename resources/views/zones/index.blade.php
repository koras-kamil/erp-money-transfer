<x-app-layout>
    {{-- LOTTIE PLAYER --}}
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>

    {{-- STYLES (MATCHING ACCOUNTS EXACTLY) --}}
    <style>
        /* --- CORE TABLE STYLES --- */
        .sheet-input { width: 100%; height: 100%; display: flex; align-items: center; background: transparent; border: 1px solid transparent; padding: 0 12px; font-size: 0.875rem; color: #1f2937; font-weight: 600; border-radius: 8px; transition: all 0.15s ease-in-out; }
        .sheet-input:focus { background-color: #fff; border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2); outline: none; }
        
        select.sheet-input { -webkit-appearance: none; appearance: none; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.2em 1.2em; padding-right: 2.5rem; padding-left: 0.75rem; cursor: pointer; white-space: nowrap; }
        [dir="rtl"] select.sheet-input { background-position: left 0.5rem center; padding-right: 0.75rem; padding-left: 2.5rem; }
        
        .select-checkbox { width: 1.1rem; height: 1.1rem; border-radius: 4px; border: 1px solid #cbd5e1; color: #6366f1; cursor: pointer; transition: all 0.2s; }
        .table-container::-webkit-scrollbar { height: 6px; }
        .table-container::-webkit-scrollbar-track { background: #f1f1f1; }
        .table-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        
        .new-row { animation: slideIn 0.3s ease-out forwards; background-color: #f0fdf4 !important; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }

        /* --- HEADER INTERACTIVITY --- */
        .th-container { position: relative; width: 100%; height: 32px; display: flex; align-items: center; overflow: visible; }
        .th-title { position: absolute; inset: 0; display: flex; align-items: center; justify-content: space-between; gap: 4px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); transform: translateY(0); opacity: 1; cursor: grab; z-index: 10; }
        .th-title:active { cursor: grabbing; }
        .search-active .th-title { transform: translateY(-150%); opacity: 0; pointer-events: none; }

        .th-search { position: absolute; inset: 0; display: flex; align-items: center; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); transform: translateY(150%); opacity: 0; pointer-events: none; z-index: 20; background: #fff; }
        .search-active .th-search { transform: translateY(0); opacity: 1; pointer-events: auto; }

        .header-search-input { width: 100%; height: 28px; background-color: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 6px; padding-left: 8px; padding-right: 24px; font-size: 0.75rem; color: #1f2937; transition: all 0.15s; }
        [dir="rtl"] .header-search-input { padding-left: 24px; padding-right: 8px; }
        .header-search-input:focus { background-color: #fff; border-color: #3b82f6; outline: none; box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1); }

        .resizer { position: absolute; right: -4px; top: 0; height: 100%; width: 10px; cursor: col-resize; z-index: 50; touch-action: none; }
        .resizer:hover::after, .resizing::after { content: ''; position: absolute; right: 4px; top: 20%; height: 60%; width: 2px; background-color: #3b82f6; }
        [dir="rtl"] .resizer { right: auto; left: -4px; }
        [dir="rtl"] .resizer:hover::after { right: auto; left: 4px; }
        .dragging-col { opacity: 0.4; background-color: #e0e7ff; border: 2px dashed #6366f1; }

        /* Pill Tabs (For Cities/Neighborhoods) */
        .pill-tab { position: relative; padding: 6px 18px; border-radius: 9999px; font-weight: 700; font-size: 0.8rem; transition: all 0.2s ease; border: 1px solid transparent; cursor: pointer; }
        .pill-active { background-color: #4f46e5; color: white; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3); }
        .pill-inactive { background-color: white; color: #64748b; border-color: #e2e8f0; }
        .pill-inactive:hover { background-color: #f8fafc; color: #334155; border-color: #cbd5e1; }

        @media print { .no-print, button, .print\:hidden { display: none !important; } .overflow-x-auto { overflow: visible !important; } table { width: 100% !important; } }
    </style>

    {{-- Main Container --}}
    <div x-data="zoneManager('{{ session('active_tab', 'cities') }}')" x-init="initData()" class="py-6 w-full min-w-0 bg-white min-h-screen" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

        {{-- MAIN TOOLBAR (Tabs like Account Page) --}}
        <div class="mx-4 mb-6 flex flex-col md:flex-row justify-between items-center gap-4 no-print">
            
            {{-- Main Navigation Tabs --}}
            <div class="bg-slate-100 p-1 rounded-lg flex items-center shadow-inner">
                <a href="{{ route('accounts.index') }}" class="px-5 py-2 text-sm font-bold rounded-md text-slate-500 hover:text-indigo-600 hover:bg-white/50 transition">
                    {{ __('account.main_tab') }}
                </a>
                {{-- Active Tab (Zones) --}}
                <a href="{{ route('zones.index') }}" class="px-5 py-2 text-sm font-bold rounded-md bg-white text-indigo-600 shadow-sm transition">
                    {{ __('account.zones_tab') }}
                </a>
                <a href="#" class="px-5 py-2 text-sm font-bold rounded-md text-slate-500 hover:text-indigo-600 hover:bg-white/50 transition opacity-50 cursor-not-allowed">
                    {{ __('account.reports_tab') }}
                </a>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                
                {{-- Sub-Tabs (Cities/Neighborhoods) --}}
                <div class="flex items-center gap-2 bg-slate-50 p-1 rounded-full border border-slate-200 mr-2">
                    <button @click="switchTab('cities')" class="pill-tab" :class="activeTab === 'cities' ? 'pill-active' : 'pill-inactive'">
                        {{ __('account.cities') }}
                    </button>
                    <button @click="switchTab('neighborhoods')" class="pill-tab" :class="activeTab === 'neighborhoods' ? 'pill-active' : 'pill-inactive'">
                        {{ __('account.neighborhoods') }}
                    </button>
                </div>

                {{-- Bulk Delete --}}
                <div x-show="selectedIds.length > 0" x-transition class="flex items-center gap-2 bg-red-50 px-2 py-1 rounded-lg border border-red-100 mr-2">
                    <span class="text-xs font-bold text-red-600 px-2"><span x-text="selectedIds.length"></span> {{ __('account.selected') }}</span>
                    <x-btn type="bulk-delete" @click="bulkDelete()">{{ __('account.delete_selected') }}</x-btn>
                    <button @click="selectedIds = []" type="button" class="px-2 py-1.5 text-slate-500 hover:text-slate-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>

                {{-- Column Config --}}
                <div x-data="{ open: false }" class="relative">
                    <x-btn type="columns" @click="open = !open" @click.away="open = false" title="{{ __('account.columns') }}" />
                    <div x-show="open" class="absolute top-full mt-3 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 p-3 ltr:right-0 rtl:left-0" style="display:none;">
                        <div class="flex justify-between items-center px-2 py-1 mb-2 border-b border-slate-100 pb-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">{{ __('account.columns') }}</span>
                            <button @click="resetLayout(); open = false;" class="text-[10px] text-blue-500 hover:underline cursor-pointer">{{ __('account.reset_layout') }}</button>
                        </div>
                        <div class="max-h-60 overflow-y-auto space-y-1">
                            <template x-for="col in currentColumns" :key="col.field">
                                <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 rounded cursor-pointer transition">
                                    <input type="checkbox" x-model="col.visible" class="rounded text-indigo-600 w-4 h-4 border-slate-300 focus:ring-indigo-500">
                                    <span class="text-xs text-slate-700 font-medium" x-text="col.label"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>

                <x-btn type="add" @click="addNewRow()" title="{{ __('account.add_new') }}" />
            </div>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="relative w-full overflow-x-auto table-container bg-white shadow-sm rounded-lg border border-slate-200 mx-4 pb-20">
            <div class="bg-white shadow-sm rounded-lg border border-slate-200 overflow-x-auto table-container">
                <form id="sheet-form" :action="formAction" method="POST">
                    @csrf
                    <table class="w-full text-sm text-left rtl:text-right text-slate-500 whitespace-nowrap border-separate border-spacing-0 table-fixed">
                        <thead class="text-xs text-slate-700 uppercase bg-blue-50/50 border-b border-blue-100 sticky top-0 z-20">
                            <tr>
                                <th class="px-4 py-3 w-[40px] text-center bg-slate-50/95 border-b border-blue-100"><input type="checkbox" @click="toggleAllSelection()" :checked="allSelected" class="select-checkbox bg-white"></th>

                                {{-- Draggable Columns --}}
                                <template x-for="(col, index) in currentColumns" :key="col.field">
                                    <th x-show="col.visible" 
                                        class="px-4 py-2 relative h-12 transition-colors duration-200 border-r border-transparent select-none group border-b border-blue-100 bg-slate-50/95"
                                        :style="'width:' + col.width + 'px'"
                                        draggable="true"
                                        @dragstart="dragStart($event, index)"
                                        @dragover.prevent="dragOver($event)"
                                        @drop="drop($event, index)"
                                        :class="[{'dragging-col': draggingIndex === index}, col.class]">
                                        
                                        <div class="th-container" :class="{ 'search-active': openFilter === col.field }">
                                            <div class="th-title">
                                                <div @click="sortBy(col.field)" class="flex items-center gap-1 cursor-pointer flex-1 h-full hover:text-indigo-600 transition-colors">
                                                    <span x-text="col.label"></span>
                                                    <svg class="w-3 h-3 text-indigo-500 transition-transform" :class="sortCol === col.field && !sortAsc ? 'rotate-180' : ''" x-show="sortCol === col.field" fill="currentColor" viewBox="0 0 20 20"><path d="M5 10l5-5 5 5H5z"/></svg>
                                                </div>
                                                {{-- Search Trigger --}}
                                                <button type="button" @click.stop="openFilter = col.field; setTimeout(() => $refs['input-'+col.field]?.focus(), 100)" class="p-1 rounded-md text-slate-400 hover:text-indigo-600 hover:bg-slate-100 transition" :class="filters[col.field] ? 'text-indigo-600' : ''">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                                </button>
                                            </div>
                                            {{-- Search Input (Correct Implementation) --}}
                                            <div class="th-search" @click.stop>
                                                <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none rtl:right-0 rtl:left-auto rtl:pr-2">
                                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                                </div>
                                                <input type="text" :x-ref="'input-'+col.field" x-model.debounce.500ms="filters[col.field]" @input="filterData()" class="header-search-input w-full" placeholder="{{ __('account.search') }}">
                                                <button type="button" @click.stop="filters[col.field] = ''; filterData(); openFilter = null;" class="absolute right-0 top-0 h-full px-2 text-gray-400 hover:text-red-500 rtl:left-0 rtl:right-auto transition">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="resizer" @mousedown.stop.prevent="initResize($event, col)"></div>
                                    </th>
                                </template>

                                {{-- Fixed Actions Column --}}
                                <th class="px-4 py-3 w-[5%] text-center print:hidden bg-blue-50/50 border-b border-blue-100 sticky right-0 z-20">{{ __('account.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(row, rowIndex) in filteredRows" :key="row.id || ('new-'+rowIndex)">
                                <tr class="bg-white hover:bg-slate-50 transition-colors group/row" 
                                    :class="[editingId === row.id ? 'bg-indigo-50/20' : '', selectedIds.includes(row.id) ? 'bg-indigo-50/10' : '', row.isNew ? 'new-row' : '']">
                                    
                                    <td class="px-4 py-4 text-center"><input type="checkbox" :value="row.id" x-model="selectedIds" class="select-checkbox"></td>

                                    {{-- Cells Loop --}}
                                    <template x-for="col in currentColumns" :key="col.field">
                                        <td x-show="col.visible" :class="col.field === 'id' ? 'px-4 py-4 font-normal text-slate-500 text-center' : 'p-1'">
                                            
                                            {{-- ID --}}
                                            <template x-if="col.field === 'id'">
                                                <div class="font-normal text-slate-400">
                                                    <span x-text="rowIndex + 1"></span>
                                                    <input type="hidden" :name="(activeTab === 'cities' ? 'cities[' : 'neighborhoods[') + rowIndex + '][id]'" :value="row.id">
                                                </div>
                                            </template>

                                            {{-- Code --}}
                                            <template x-if="col.field === 'code'">
                                                <input type="text" :name="(activeTab === 'cities' ? 'cities[' : 'neighborhoods[') + rowIndex + '][code]'" :value="row.code" class="sheet-input font-normal uppercase text-slate-500" readonly>
                                            </template>

                                            {{-- City Name --}}
                                            <template x-if="activeTab === 'cities' && col.field === 'city_name'">
                                                <div>
                                                    <span x-show="editingId !== row.id" x-text="row.city_name" class="px-3 block text-slate-700 font-bold truncate"></span>
                                                    <input x-show="editingId === row.id" :id="'input-name-'+row.id" type="text" :name="'cities['+rowIndex+'][city_name]'" x-model="row.city_name" class="sheet-input font-bold text-slate-700">
                                                </div>
                                            </template>

                                            {{-- Neighborhood Name --}}
                                            <template x-if="activeTab === 'neighborhoods' && col.field === 'neighborhood_name'">
                                                <div>
                                                    <span x-show="editingId !== row.id" x-text="row.neighborhood_name" class="px-3 block text-slate-700 font-bold truncate"></span>
                                                    <input x-show="editingId === row.id" :id="'input-neigh-'+row.id" type="text" :name="'neighborhoods['+rowIndex+'][neighborhood_name]'" x-model="row.neighborhood_name" class="sheet-input font-bold text-slate-700">
                                                </div>
                                            </template>

                                            {{-- City Select (For Neighborhoods) --}}
                                            <template x-if="activeTab === 'neighborhoods' && col.field === 'city_id'">
                                                <div>
                                                    <span x-show="editingId !== row.id" x-text="getCityName(row.city_id)" class="px-3 block text-slate-600 font-normal truncate"></span>
                                                    <select x-show="editingId === row.id" :name="'neighborhoods['+rowIndex+'][city_id]'" x-model="row.city_id" class="sheet-input font-normal text-slate-700">
                                                        <option value="" disabled>{{ __('account.select_city') }}</option>
                                                        <template x-for="c in cities" :key="c.id"><option :value="c.id" x-text="c.city_name"></option></template>
                                                    </select>
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
                                        </td>
                                    </template>
                                    
                                    {{-- Actions --}}
                                    <td class="px-4 py-4 text-center print:hidden">
                                        <div class="flex items-center justify-center gap-2">
                                            <template x-if="editingId === row.id">
                                                <div class="flex items-center gap-1">
                                                    <x-btn type="cancel" @click="cancelEdit(row)" title="{{ __('account.cancel') }}" />
                                                    <x-btn type="save" @click="saveRow()" title="{{ __('account.save') }}" />
                                                </div>
                                            </template>
                                            <template x-if="editingId !== row.id">
                                                <div class="flex items-center gap-2">
                                                    <x-btn type="delete" @click="deleteRow(row.id)" title="{{ __('account.delete') }}" />
                                                    <x-btn type="edit" @click="startEdit(row.id)" title="{{ __('account.edit') }}" />
                                                </div>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </template>

                            {{-- NO DATA ANIMATION --}}
                            <template x-if="filteredRows.length === 0">
                                <tr class="bg-white">
                                    <td :colspan="currentColumns.length + 2" class="py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <dotlottie-player src="https://lottie.host/ace77418-be70-4ea4-8c0a-88efe0221c91/aCjbIohU9b.lottie" background="transparent" speed="1" style="width: 200px; height: 200px;" loop autoplay></dotlottie-player>
                                            <span class="text-slate-400 font-medium mt-4">{{ __('account.no_data_found') }}</span>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
        <form id="delete-form" action="" method="POST" class="hidden">@csrf @method('DELETE')</form>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('zoneManager', (defaultTab) => ({
                activeTab: defaultTab,
                cities: @json($cities),
                neighborhoods: @json($neighborhoods),
                
                // State
                originalRows: [], filteredRows: [], editingId: null, selectedIds: [], openFilter: null, sortCol: null, sortAsc: true, filters: {}, draggingIndex: null,

                // Config
                cityColumns: [
                    { field: 'id', label: '#', visible: true, width: 50 },
                    { field: 'code', label: '{{ __('account.code') }}', visible: true, width: 100 },
                    { field: 'city_name', label: '{{ __('account.city_name') }}', visible: true, width: 250 },
                    { field: 'user', label: '{{ __('account.created_by') }}', visible: true, width: 100 },
                    { field: 'created_at', label: '{{ __('account.created_at') }}', visible: true, width: 120 },
                ],
                neighColumns: [
                    { field: 'id', label: '#', visible: true, width: 50 },
                    { field: 'code', label: '{{ __('account.code') }}', visible: true, width: 100 },
                    { field: 'city_id', label: '{{ __('account.city_name') }}', visible: true, width: 200 },
                    { field: 'neighborhood_name', label: '{{ __('account.neighborhood_name') }}', visible: true, width: 250 },
                    { field: 'user', label: '{{ __('account.created_by') }}', visible: true, width: 100 },
                    { field: 'created_at', label: '{{ __('account.created_at') }}', visible: true, width: 120 },
                ],

                get currentColumns() { return this.activeTab === 'cities' ? this.cityColumns : this.neighColumns; },
                get formAction() { return this.activeTab === 'cities' ? "{{ route('zones.cities.store') }}" : "{{ route('zones.neighborhoods.store') }}"; },

                initData() {
                    this.loadTab(this.activeTab);
                    const cCols = localStorage.getItem('zones_city_cols'); if(cCols) this.cityColumns = JSON.parse(cCols);
                    const nCols = localStorage.getItem('zones_neigh_cols'); if(nCols) this.neighColumns = JSON.parse(nCols);
                },

                switchTab(tab) { this.activeTab = tab; this.loadTab(tab); this.selectedIds = []; this.editingId = null; },
                
                loadTab(tab) {
                    this.originalRows = tab === 'cities' ? JSON.parse(JSON.stringify(this.cities)) : JSON.parse(JSON.stringify(this.neighborhoods));
                    this.filteredRows = JSON.parse(JSON.stringify(this.originalRows));
                    this.filters = {};
                },

                // --- DRAG & DROP ---
                dragStart(e, index) { this.draggingIndex = index; e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', index); },
                dragOver(e) { e.preventDefault(); e.dataTransfer.dropEffect = 'move'; },
                drop(e, targetIndex) {
                    if (this.draggingIndex === null || this.draggingIndex === targetIndex) return;
                    let cols = this.activeTab === 'cities' ? this.cityColumns : this.neighColumns;
                    const element = cols.splice(this.draggingIndex, 1)[0];
                    cols.splice(targetIndex, 0, element);
                    this.draggingIndex = null;
                    this.saveState();
                },

                // --- RESIZING ---
                initResize(e, col) {
                    const startX = e.clientX; 
                    const startWidth = parseInt(col.width) || 100;
                    const onMouseMove = (moveEvent) => {
                        const diff = moveEvent.clientX - startX;
                        const isRtl = document.dir === 'rtl';
                        col.width = isRtl ? startWidth - diff : startWidth + diff;
                    };
                    const onMouseUp = () => {
                        window.removeEventListener('mousemove', onMouseMove);
                        window.removeEventListener('mouseup', onMouseUp);
                        this.saveState();
                    };
                    window.addEventListener('mousemove', onMouseMove);
                    window.addEventListener('mouseup', onMouseUp);
                },

                saveState() { 
                    if(this.activeTab === 'cities') localStorage.setItem('zones_city_cols', JSON.stringify(this.cityColumns));
                    else localStorage.setItem('zones_neigh_cols', JSON.stringify(this.neighColumns));
                },
                
                resetLayout() { 
                    localStorage.removeItem('zones_city_cols'); 
                    localStorage.removeItem('zones_neigh_cols'); 
                    window.location.reload(); 
                },

                // Helpers
                getCityName(id) { const c = this.cities.find(x => x.id == id); return c ? c.city_name : '-'; },
                getUserName(id) { return id ? '{{ Auth::user()->name }}' : 'SYSTEM'; },
                formatDate(date) { return date ? new Date(date).toISOString().slice(0,16).replace('T',' ') : '-'; },

                // Actions
                get allSelected() { return this.filteredRows.length > 0 && this.selectedIds.length === this.filteredRows.length; },
                toggleAllSelection() { this.selectedIds = this.allSelected ? [] : this.filteredRows.map(r => r.id); },
                
                addNewRow() {
                    const newId = 'new-' + Date.now();
                    const newRow = this.activeTab === 'cities' 
                        ? { id: newId, code: 'NEW', city_name: '', user_id: {{ Auth::id() }}, created_at: new Date(), isNew: true }
                        : { id: newId, code: 'NEW', city_id: '', neighborhood_name: '', user_id: {{ Auth::id() }}, created_at: new Date(), isNew: true };
                    
                    this.filteredRows.unshift(newRow);
                    this.startEdit(newId);
                },
                startEdit(id) { 
                    this.editingId = id; 
                    setTimeout(() => { 
                        let el = this.activeTab === 'cities' ? document.getElementById('input-name-'+id) : document.getElementById('input-neigh-'+id);
                        if(el) el.focus(); 
                    }, 100); 
                },
                cancelEdit(row) {
                    if (String(row.id).startsWith('new-')) { this.filteredRows = this.filteredRows.filter(r => r.id !== row.id); }
                    this.editingId = null;
                },
                saveRow() { document.getElementById('sheet-form').submit(); },
                
                // Filter
                filterData() {
                    this.filteredRows = this.originalRows.filter(row => {
                        let cols = this.activeTab === 'cities' ? this.cityColumns : this.neighColumns;
                        return cols.every(col => {
                            const filterVal = this.filters[col.field]?.toLowerCase() || '';
                            if (!filterVal) return true;
                            let cellVal = String(row[col.field] || '');
                            if (col.field === 'city_id') cellVal = this.getCityName(row.city_id);
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
                        if (valA < valB) return this.sortAsc ? -1 : 1; if (valA > valB) return this.sortAsc ? 1 : -1; return 0;
                    });
                },
                deleteRow(id) {
                    const route = this.activeTab === 'cities' ? "{{ route('zones.cities.destroy', ':id') }}" : "{{ route('zones.neighborhoods.destroy', ':id') }}";
                    const form = document.getElementById('delete-form'); form.action = route.replace(':id', id);
                    if (window.confirmAction) { window.confirmAction('delete-form', "{{ __('account.delete_confirm') }}"); } 
                    else { if(confirm("{{ __('account.delete_confirm') }}")) form.submit(); }
                },
                bulkDelete() {
                    if (this.selectedIds.length === 0) return;
                    // For zones, you might need a dedicated bulk delete route. 
                    // This alert is here until you implement that controller logic.
                    alert('Please implement bulk delete controller logic for zones.'); 
                }
            }));
        });
    </script>
</x-app-layout>