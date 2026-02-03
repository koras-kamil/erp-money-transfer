<x-app-layout>
    {{-- ASSETS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>

    {{-- STYLES (MATCHING CASHBOX TRASH) --}}
    <style>
        /* --- CORE STYLES --- */
        .sheet-input { width: 100%; height: 100%; display: flex; align-items: center; background: transparent; border: 1px solid #f3f4f6; padding: 0 12px; font-size: 0.875rem; color: #1f2937; font-weight: 600; border-radius: 8px; transition: all 0.15s ease-in-out; }
        .sheet-input:focus { background-color: #fff; border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2); outline: none; }
        
        /* Scrollbar */
        .custom-scrollbar::-webkit-scrollbar { height: 10px; width: 10px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 5px; border: 2px solid #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        #map-container { height: 450px; width: 100%; border-radius: 12px; z-index: 1; }
        [x-cloak] { display: none !important; }
        
        .select-checkbox { width: 1.1rem; height: 1.1rem; border-radius: 4px; border: 1px solid #cbd5e1; color: #6366f1; cursor: pointer; transition: all 0.2s; }

        /* --- HEADER INTERACTIVITY --- */
        .th-container { position: relative; width: 100%; height: 32px; display: flex; align-items: center; justify-content: space-between; overflow: visible; }
        .th-title { position: absolute; inset: 0; display: flex; align-items: center; justify-content: space-between; gap: 4px; transition: all 0.2s ease; transform: translateY(0); opacity: 1; cursor: grab; z-index: 10; }
        .th-title:active { cursor: grabbing; }
        .search-active .th-title { transform: translateY(-150%); opacity: 0; pointer-events: none; }

        /* Search Input */
        .th-search { position: absolute; inset: 0; display: flex; align-items: center; transition: all 0.2s ease; transform: translateY(150%); opacity: 0; pointer-events: none; z-index: 20; background-color: #fef2f2; } /* Red tint */
        .search-active .th-search { transform: translateY(0); opacity: 1; pointer-events: auto; }

        .header-search-input { width: 100%; height: 28px; background-color: #fff; border: 1px solid #cbd5e1; border-radius: 6px; padding-left: 8px; padding-right: 24px; font-size: 0.75rem; color: #1f2937; transition: all 0.15s; }
        [dir="rtl"] .header-search-input { padding-left: 24px; padding-right: 8px; }
        .header-search-input:focus { border-color: #ef4444; outline: none; box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.1); }

        /* Resizer */
        .resizer { position: absolute; right: -4px; top: 0; height: 100%; width: 8px; cursor: col-resize; z-index: 50; touch-action: none; }
        .resizer:hover::after, .resizing::after { content: ''; position: absolute; right: 4px; top: 20%; height: 60%; width: 2px; background-color: #ef4444; }
        [dir="rtl"] .resizer { right: auto; left: -4px; }
        [dir="rtl"] .resizer:hover::after { right: auto; left: 4px; }
        .dragging-col { opacity: 0.4; background-color: #fee2e2; border: 2px dashed #ef4444; }

        @media print { .no-print, button, .print\:hidden { display: none !important; } .overflow-x-auto { overflow: visible !important; } table { width: 100% !important; } }
    </style>

    <div x-data="trashManager()" x-init="initData()" class="py-6 w-full min-w-0" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

        {{-- TOOLBAR --}}
        <div class="mx-4 mb-6 flex flex-col md:flex-row justify-between items-center gap-4 no-print">
            
            {{-- TITLE (RED FOR TRASH) --}}
            <div class="flex items-center gap-3">
                <div class="p-2 bg-red-100 rounded-lg text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <h3 x-show="selectedIds.length === 0" class="text-xl font-black text-slate-800 tracking-tight">{{ __('account.trash_bin') }}</h3>

                {{-- BULK ACTIONS BAR --}}
                <div x-show="selectedIds.length > 0" x-transition class="flex items-center gap-2" style="display: none;">
                    <span class="text-xs font-bold text-red-600 bg-red-50 px-2 py-1 rounded border border-red-100"><span x-text="selectedIds.length"></span> {{ __('account.selected') }}</span>
                    
                    {{-- Restore Selected --}}
                    <button @click="bulkRestore()" class="px-3 py-1.5 bg-emerald-500 text-white text-xs font-bold rounded shadow-sm hover:bg-emerald-600 transition flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        {{ __('account.restore') }}
                    </button>

                    {{-- Delete Selected --}}
                    <button @click="bulkForceDelete()" class="px-3 py-1.5 bg-red-600 text-white text-xs font-bold rounded shadow-sm hover:bg-red-700 transition flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        {{ __('account.delete_permanently') }}
                    </button>
                    
                    {{-- Cancel --}}
                    <button @click="selectedIds = []" class="text-slate-400 hover:text-slate-600 text-xs underline px-2">{{ __('account.cancel') }}</button>
                </div>
            </div>

            {{-- Back To List --}}
            <a href="{{ route('accounts.index') }}" class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm font-medium text-xs">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('account.back_to_list') }}
            </a>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="relative w-full overflow-x-auto table-container bg-white shadow-sm rounded-xl border border-slate-200 mx-4 pb-20">
            
            {{-- Loading Spinner --}}
            <div x-show="loading" class="absolute inset-0 bg-white/60 backdrop-blur-[1px] z-50 flex items-center justify-center transition-opacity">
                <div class="w-8 h-8 border-3 border-red-600 border-t-transparent rounded-full animate-spin"></div>
            </div>

            <table class="w-full text-sm text-left rtl:text-right text-slate-500 whitespace-nowrap border-separate border-spacing-0">
                <thead class="text-xs text-slate-700 uppercase bg-red-50/50 border-b border-red-100 sticky top-0 z-20">
                    <tr>
                        <th class="px-4 py-3 w-[40px] text-center bg-slate-50/95 border-b border-red-100"><input type="checkbox" @click="toggleAllSelection()" :checked="data.length > 0 && selectedIds.length === data.length" class="select-checkbox bg-white"></th>
                        <template x-for="(col, index) in columns" :key="col.field">
                            <th x-show="col.visible" 
                                class="px-4 py-2 relative h-12 transition-colors duration-200 border-r border-transparent select-none group border-b border-red-100 bg-slate-50/95" 
                                :style="'min-width:' + col.width + 'px'"
                                draggable="true" @dragstart="dragStart($event, index)" @dragover.prevent="dragOver($event)" @drop="drop($event, index)"
                                :class="[{'dragging-col': draggingIndex === index}, col.class]">
                                
                                <div class="th-container" :class="{ 'search-active': openFilter === col.field }">
                                    {{-- Title --}}
                                    <div class="th-title">
                                        <div class="flex items-center justify-center gap-1 cursor-pointer flex-1 h-full hover:text-red-600 transition-colors" @click="sortBy(col.field)">
                                            <span x-text="col.label" class="whitespace-nowrap"></span>
                                            <span class="text-red-500 text-[9px] flex flex-col -space-y-1" x-show="params.sort === col.field">
                                                <span :class="params.direction === 'asc' ? 'text-red-600' : 'text-slate-300'">▲</span>
                                                <span :class="params.direction === 'desc' ? 'text-red-600' : 'text-slate-300'">▼</span>
                                            </span>
                                        </div>
                                        <button x-show="col.searchable !== false" type="button" @click.stop="openFilter = col.field; setTimeout(() => $refs['input-'+col.field]?.focus(), 100)" class="p-1 rounded-md text-slate-400 hover:text-red-600 hover:bg-white transition" :class="filters[col.field] ? 'text-red-600' : ''">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </button>
                                    </div>
                                    
                                    {{-- Search Input (Red Theme) --}}
                                    <div x-show="col.searchable !== false" class="th-search" @click.stop>
                                        <input type="text" :x-ref="'input-'+col.field" x-model.debounce.500ms="filters[col.field]" @input="fetchData()" @keydown.escape="openFilter = null" class="header-search-input w-full" placeholder="{{ __('account.search') }}">
                                        <button type="button" @click.stop="filters[col.field] = ''; fetchData(); openFilter = null;" class="absolute right-1 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500 p-0.5 rounded-md rtl:left-1 rtl:right-auto"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                    </div>
                                </div>
                                <div class="resizer" @mousedown.stop.prevent="initResize($event, col)"></div>
                            </th>
                        </template>
                        <th class="px-4 py-3 w-[100px] text-center print:hidden bg-red-50/50 border-b border-red-100 sticky right-0 z-20">{{ __('account.actions') }}</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">
                    <template x-for="acc in data" :key="acc.id">
                        <tr class="bg-white hover:bg-slate-50 transition-colors group" :class="selectedIds.includes(acc.id) ? 'bg-red-50/10' : ''">
                            <td class="px-4 py-2 text-center align-middle"><input type="checkbox" :value="acc.id" x-model="selectedIds" class="select-checkbox"></td>

                            <template x-for="col in columns" :key="col.field">
                                <td x-show="col.visible" class="p-1" :class="col.class">
                                    {{-- ID --}}
                                    <template x-if="col.field === 'id'"><div class="px-4 py-4 text-center font-mono text-xs text-slate-400" x-text="acc.id"></div></template>

                                    {{-- IMAGE --}}
                                    <template x-if="col.field === 'image'">
                                        <div @click="zoomImage(acc.image_url)" class="flex items-center justify-center cursor-pointer p-1">
                                            <div class="w-10 h-10 rounded-full bg-slate-50 border border-slate-200 overflow-hidden flex items-center justify-center shadow-sm relative group-hover:scale-110 transition-transform">
                                                <template x-if="acc.image_url"><img :src="acc.image_url" class="w-full h-full object-cover"></template>
                                                <template x-if="!acc.image_url"><span class="text-[10px] font-bold text-slate-400" x-text="acc.initial"></span></template>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- TEXT FIELDS --}}
                                    <template x-if="col.field === 'name'"><span class="px-3 block text-slate-700 font-bold text-xs truncate" x-text="acc.name"></span></template>
                                    <template x-if="col.field === 'secondary_name'"><span class="px-3 block text-slate-500 font-normal text-xs truncate" x-text="acc.secondary_name || '-'"></span></template>
                                    <template x-if="col.field === 'branch_id'"><span class="px-2 block text-[10px] uppercase text-slate-500 font-normal truncate" x-text="acc.branch_text || '-'"></span></template>
                                    
                                    {{-- BADGE --}}
                                    <template x-if="col.field === 'account_type'">
                                        <div class="text-center"><span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border bg-gray-50 text-gray-500" x-text="acc.account_type"></span></div>
                                    </template>
                                    
                                    <template x-if="col.field === 'currency_id'"><div class="text-center"><span class="bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded text-[10px] font-bold uppercase" x-text="acc.currency_text"></span></div></template>
                                    <template x-if="col.field === 'debt_limit'"><div class="text-center font-bold text-xs text-emerald-600" x-text="acc.debt_limit ? Number(acc.debt_limit).toLocaleString() : '-'"></div></template>

                                    <template x-if="col.field === 'created_at'"><div class="px-4 py-4 text-[10px] text-slate-400 font-normal text-center truncate" x-text="acc.deleted_at"></div></template>

                                    {{-- GPS ICON --}}
                                    <template x-if="col.field === 'location'">
                                        <div class="text-center">
                                            <button @click="viewMap(acc.location, acc.name)" 
                                                    class="p-1.5 rounded-full transition-colors"
                                                    :class="acc.location ? 'text-red-600 hover:bg-red-50' : 'text-slate-300 cursor-not-allowed'"
                                                    :disabled="!acc.location">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            </button>
                                        </div>
                                    </template>
                                    
                                    {{-- STATUS --}}
                                    <template x-if="col.field === 'is_active'"><div class="flex items-center justify-center"><input type="checkbox" :checked="acc.is_active" disabled class="w-4 h-4 text-gray-400 rounded border-slate-300"></div></template>
                                    
                                    {{-- FALLBACK --}}
                                    <template x-if="!['id','image','name','secondary_name','branch_id','account_type','currency_id','debt_limit','created_at','location','is_active'].includes(col.field)">
                                        <div class="px-3 text-xs text-slate-500 truncate" x-text="acc[col.field.replace('_id', '_text')] || acc[col.field] || '-'"></div>
                                    </template>
                                </td>
                            </template>
                            <td class="px-3 py-2 text-center print:hidden sticky-action group-hover:bg-slate-50 transition-colors">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- RESTORE BUTTON --}}
                                    <form :action="'/accounts/' + acc.id + '/restore'" method="POST">
                                        @csrf
                                        <button type="submit" class="p-1.5 text-emerald-600 hover:bg-emerald-100 rounded-lg transition" title="{{ __('account.restore') }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        </button>
                                    </form>
                                    {{-- FORCE DELETE --}}
                                    <button @click="forceDeleteRow(acc.id)" class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg transition" title="{{ __('account.delete_permanently') }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    
                    {{-- NO DATA COMPONENT --}}
                    <tr x-show="!loading && data.length === 0" x-transition.opacity class="bg-white">
                        <td :colspan="Object.keys(columns).length + 2" class="py-10 text-center border-b border-slate-50">
                            <div class="flex flex-col justify-center items-center">
                                <dotlottie-player src="https://lottie.host/ace77418-be70-4ea4-8c0a-88efe0221c91/aCjbIohU9b.lottie" background="transparent" speed="1" style="width: 200px; height: 200px;" loop autoplay></dotlottie-player>
                                <span class="text-slate-400 font-medium mt-4">{{ __('account.trash_empty') }}</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        <div class="mx-4 mt-2 px-4 py-3 bg-white border border-slate-200 rounded-lg flex justify-between items-center" x-show="pagination.last_page > 1">
            <div class="text-[10px] text-slate-500 font-medium"><span class="font-bold text-slate-700" x-text="pagination.from"></span> - <span class="font-bold text-slate-700" x-text="pagination.to"></span> / <span class="font-bold text-slate-700" x-text="pagination.total"></span></div>
            <div class="flex gap-1"><template x-for="link in pagination.links"><button @click="changePage(link.url)" x-html="link.label" :disabled="!link.url || link.active" class="w-7 h-7 flex items-center justify-center rounded-md text-[10px] font-bold transition-all border" :class="link.active ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white text-slate-500 hover:bg-slate-100 border-slate-200 hover:border-slate-300 disabled:opacity-50'" x-show="link.url"></button></template></div>
        </div>

        {{-- MAP MODAL --}}
        <div x-show="showMapModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 p-4" x-cloak><div @click.away="showMapModal = false" class="bg-white rounded-xl w-full max-w-3xl h-[500px] overflow-hidden shadow-2xl relative"><div id="map-container" class="w-full h-full"></div>
            <div class="absolute top-4 left-4 z-[500] flex bg-white rounded-lg shadow-md p-1 gap-1">
                <button @click="setMapStyle('road')" class="px-3 py-1.5 text-xs font-bold rounded-md transition-colors" :class="currentMapStyle === 'road' ? 'bg-indigo-100 text-indigo-700' : 'text-slate-600 hover:bg-slate-50'">{{ __('account.road_map') }}</button>
                <button @click="setMapStyle('satellite')" class="px-3 py-1.5 text-xs font-bold rounded-md transition-colors" :class="currentMapStyle === 'satellite' ? 'bg-indigo-100 text-indigo-700' : 'text-slate-600 hover:bg-slate-50'">{{ __('account.satellite_map') }}</button>
            </div>
            <button @click="showMapModal = false" class="absolute top-4 right-4 bg-white text-slate-700 w-8 h-8 flex items-center justify-center rounded-full shadow-md z-[500] hover:bg-slate-100 hover:text-red-500 font-bold transition-colors">✕</button>
        </div></div>
        
        <div x-show="zoomedImage" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/95 backdrop-blur-sm" @click="zoomedImage = null" x-cloak><img :src="zoomedImage" class="max-w-[85vw] max-h-[85vh] rounded-lg shadow-2xl scale-100" @click.stop><button @click="zoomedImage = null" class="absolute top-6 right-6 text-white p-2">X</button></div>

        {{-- FORCE DELETE FORMS --}}
        <form id="force-delete-form" method="POST" style="display:none">@csrf @method('DELETE')</form>
        <form id="bulk-force-delete-form" action="{{ route('accounts.bulk-force-delete') }}" method="POST" class="hidden">@csrf @method('DELETE')<input type="hidden" name="ids" id="bulk-delete-ids"></form>
        <form id="bulk-restore-form" action="{{ route('accounts.bulk-restore') }}" method="POST" class="hidden">@csrf <input type="hidden" name="ids" id="bulk-restore-ids"></form>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('trashManager', () => ({
                data: @json($accounts->items()),
                pagination: @json($accounts),
                
                showMapModal: false, mapInstance: null, selectedIds: [], zoomedImage: null, openFilter: null, draggingIndex: null, sortCol: 'id', sortAsc: false, filters: {},
                mapLayers: {}, currentMapStyle: 'road', mapMarker: null,
                
                columns: [
                    { field: 'id', label: '#', visible: true, width: 40, searchable: true },
                    { field: 'image', label: "{{ __('account.image') }}", visible: true, width: 60, searchable: false },
                    { field: 'code', label: "{{ __('account.code') }}", visible: true, width: 80, searchable: true },
                    { field: 'name', label: "{{ __('account.name') }}", visible: true, width: 150, searchable: true },
                    { field: 'secondary_name', label: "{{ __('account.secondary_name') }}", visible: true, width: 150, searchable: true },
                    { field: 'branch_id', label: "{{ __('account.branch') }}", visible: true, width: 120, searchable: false },
                    { field: 'account_type', label: "{{ __('account.type') }}", visible: true, width: 100, searchable: true },
                    { field: 'currency_id', label: "{{ __('account.currency') }}", visible: true, width: 80, searchable: false },
                    { field: 'debt_limit', label: "{{ __('account.debt_limit') }}", visible: true, width: 100, searchable: false },
                    { field: 'created_at', label: "{{ __('account.deleted_at') }}", visible: true, width: 120, searchable: false },
                    { field: 'manual_code', label: "{{ __('account.manual_code') }}", visible: true, width: 80, searchable: true },
                    { field: 'mobile_number_1', label: "{{ __('account.mobile_1') }}", visible: true, width: 110, searchable: true },
                    { field: 'mobile_number_2', label: "{{ __('account.mobile_2') }}", visible: false, width: 110, searchable: true },
                    { field: 'city_id', label: "{{ __('account.city') }}", visible: false, width: 100, searchable: false },
                    { field: 'neighborhood_id', label: "{{ __('account.neighborhood') }}", visible: false, width: 100, searchable: false },
                    { field: 'location', label: "{{ __('account.gps_location') }}", visible: true, width: 60, searchable: false },
                    { field: 'is_active', label: "{{ __('account.status') }}", visible: true, width: 60, searchable: false }
                ],
                
                params: { sort: 'deleted_at', direction: 'desc', page: 1 },

                initData() { 
                    const saved = localStorage.getItem('acc_trash_cols'); 
                    if(saved) {
                        const savedCols = JSON.parse(saved);
                        this.columns = savedCols.map(s => {
                            const def = this.columns.find(c => c.field === s.field);
                            return def ? { ...def, visible: s.visible, width: s.width } : null;
                        }).filter(c => c !== null);
                    }
                    this.columns.forEach(c => { this.filters[c.field] = ''; });
                },
                saveState() { localStorage.setItem('acc_trash_cols', JSON.stringify(this.columns)); },
                
                dragStart(e, i) { this.draggingIndex = i; e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', i); },
                dragOver(e) { e.preventDefault(); e.dataTransfer.dropEffect = 'move'; },
                drop(e, targetIndex) { if (this.draggingIndex === null || this.draggingIndex === targetIndex) return; const element = this.columns.splice(this.draggingIndex, 1)[0]; this.columns.splice(targetIndex, 0, element); this.draggingIndex = null; this.saveState(); },
                initResize(e, col) { const startX = e.clientX; const startWidth = parseInt(col.width) || 100; const onMouseMove = (ev) => { col.width = Math.max(50, (document.dir === 'rtl' ? startWidth - (ev.clientX - startX) : startWidth + (ev.clientX - startX))); }; const onMouseUp = () => { window.removeEventListener('mousemove', onMouseMove); window.removeEventListener('mouseup', onMouseUp); this.saveState(); }; window.addEventListener('mousemove', onMouseMove); window.addEventListener('mouseup', onMouseUp); },
                toggleAllSelection() { this.selectedIds = (this.selectedIds.length === this.data.length) ? [] : this.data.map(a => a.id); },
                
                // --- TRASH SPECIFIC ACTIONS ---
                forceDeleteRow(id) {
                    const form = document.getElementById('force-delete-form');
                    form.action = '/accounts/' + id + '/force-delete';
                    if (window.confirmAction) {
                        window.confirmAction('force-delete-form', "{{ __('account.are_you_sure_permanent') }}");
                    } else {
                        if (confirm("{{ __('account.are_you_sure_permanent') }}")) form.submit();
                    }
                },
                bulkForceDelete() {
                    if (this.selectedIds.length === 0) return;
                    document.getElementById('bulk-delete-ids').value = JSON.stringify(this.selectedIds);
                    if (window.confirmAction) {
                        window.confirmAction('bulk-force-delete-form', "{{ __('account.are_you_sure_permanent') }}");
                    } else {
                        if (confirm("{{ __('account.are_you_sure_permanent') }}")) document.getElementById('bulk-force-delete-form').submit();
                    }
                },
                bulkRestore() {
                    if (this.selectedIds.length === 0) return;
                    document.getElementById('bulk-restore-ids').value = JSON.stringify(this.selectedIds);
                    document.getElementById('bulk-restore-form').submit();
                },
                
                fetchData(url = null) { 
                    this.loading = true; 
                    const oldData = [...this.data];
                    let targetUrl = url || "{{ route('accounts.trash') }}"; 
                    let query = new URLSearchParams(); 
                    for (let key in this.params) { if(this.params[key]) query.append(key, this.params[key]); } 
                    for (let key in this.filters) { if(this.filters[key]) query.append(key, this.filters[key]); }
                    if (!url) targetUrl += '?' + query.toString(); 
                    
                    fetch(targetUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(res => {
                            if(!res.ok) throw new Error('Network response was not ok');
                            return res.json();
                        })
                        .then(response => { 
                            this.data = response.data; 
                            this.pagination = response; 
                            this.loading = false; 
                        })
                        .catch(() => { 
                            this.loading = false; 
                            this.data = oldData; 
                        }); 
                },
                
                sortBy(field) { if (this.params.sort === field) { this.params.direction = this.params.direction === 'asc' ? 'desc' : 'asc'; } else { this.params.sort = field; this.params.direction = 'asc'; } this.fetchData(); },
                changePage(url) { if(url) this.fetchData(url); },
                formatDate(iso) { if(!iso) return '-'; return new Date(iso).toLocaleString(); },
                
                viewMap(loc, name) {
                    if(!loc) return;
                    this.showMapModal = true;
                    this.mapTitle = name;
                    this.$nextTick(() => {
                        let c = loc.split(',').map(Number);
                        if(!this.mapInstance) {
                            this.mapInstance = L.map('map-container').setView(c, 15);
                            const road = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 });
                            const sat = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { maxZoom: 19 });
                            this.mapLayers = { road, satellite: sat };
                            this.mapLayers[this.currentMapStyle].addTo(this.mapInstance);
                        } else {
                            this.mapInstance.setView(c, 15);
                            this.mapInstance.invalidateSize(); 
                        }
                        if (this.mapMarker) this.mapInstance.removeLayer(this.mapMarker);
                        this.mapMarker = L.marker(c).addTo(this.mapInstance).bindPopup(`<b class="text-lg">${name}</b>`).openPopup();
                    });
                },
                
                setMapStyle(style) {
                    if (this.currentMapStyle === style) return;
                    if (this.mapInstance.hasLayer(this.mapLayers[this.currentMapStyle])) { this.mapInstance.removeLayer(this.mapLayers[this.currentMapStyle]); }
                    this.mapLayers[style].addTo(this.mapInstance);
                    this.currentMapStyle = style;
                },
                
                zoomImage(url) { if(url) this.zoomedImage = url; }
            }));
        });
    </script>
</x-app-layout>