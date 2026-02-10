<x-app-layout>
    {{-- 1. ASSETS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

    {{-- STYLES --}}
    <style>
        .sheet-input { width: 100%; height: 100%; display: flex; align-items: center; background: transparent; border: 1px solid #f3f4f6; padding: 0 12px; font-size: 0.875rem; color: #1f2937; font-weight: 600; border-radius: 8px; transition: all 0.15s ease-in-out; }
        .sheet-input:focus { background-color: #fff; border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2); outline: none; }
        .form-input { width: 100%; border: 1px solid #e2e8f0; padding: 10px; border-radius: 10px; font-size: 0.9rem; transition: all 0.2s; }
        .form-input:focus { border-color: #6366f1; ring: 2px; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); outline: none; }
        .custom-scrollbar::-webkit-scrollbar { height: 10px; width: 10px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 5px; border: 2px solid #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        #map-container { height: 450px; width: 100%; border-radius: 12px; z-index: 1; }
        [x-cloak] { display: none !important; }
        .select-checkbox { width: 1.1rem; height: 1.1rem; border-radius: 4px; border: 1px solid #cbd5e1; color: #6366f1; cursor: pointer; transition: all 0.2s; }
        .th-container { position: relative; width: 100%; height: 32px; display: flex; align-items: center; justify-content: center; overflow: visible; } /* Centered Header */
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

    <div x-data="accountsManager()" x-init="initData()" class="py-6 w-full min-w-0" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

        {{-- TOOLBAR --}}
        <div class="mx-4 mb-6 flex flex-col md:flex-row justify-between items-center gap-4 no-print">
            <div class="bg-slate-100 p-1 rounded-lg flex items-center shadow-inner">
                <a href="{{ route('accounts.index') }}" class="px-5 py-2 text-sm font-bold rounded-md bg-white text-indigo-600 shadow-sm transition">{{ __('account.main_tab') }}</a>
                <a href="{{ route('zones.index') }}" class="px-5 py-2 text-sm font-bold rounded-md text-slate-500 hover:text-indigo-600 hover:bg-white/50 transition">{{ __('account.zones_tab') }}</a>
                <a href="#" class="px-5 py-2 text-sm font-bold rounded-md text-slate-500 hover:text-indigo-600 hover:bg-white/50 transition opacity-50 cursor-not-allowed">{{ __('account.reports_tab') }}</a>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <div x-show="selectedIds.length > 0" x-transition class="flex items-center gap-2 bg-red-50 px-2 py-1 rounded-lg border border-red-100 mr-2 ml-2">
                    <span class="text-xs font-bold text-red-600 px-2"><span x-text="selectedIds.length"></span> {{ __('account.selected') }}</span>
                    <x-btn type="bulk-delete" @click="bulkDelete()">{{ __('account.delete_selected') }}</x-btn>
                    <button @click="selectedIds = []" type="button" class="px-2 py-1.5 text-slate-500 hover:text-slate-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>

                <x-btn type="trash" href="{{ route('accounts.trash') }}" title="{{ __('account.trash') }}" />
                
                <div x-data="{ open: false }" class="relative">
                    <x-btn type="columns" @click="open = !open" @click.outside="open = false" title="{{ __('account.columns') }}" />
                    <div x-show="open" class="absolute top-full mt-3 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 p-3 ltr:right-0 rtl:left-0" style="display:none;">
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

                <x-btn type="print" href="{{ route('accounts.print') }}" target="_blank" title="{{ __('account.print') }}" />
                <x-btn type="add" @click="openCreate()" title="{{ __('account.add_new') }}" />
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
                        <th class="px-4 py-3 w-[40px] text-center bg-slate-50/95 border-b border-blue-100"><input type="checkbox" @click="toggleAllSelection()" :checked="data.length > 0 && selectedIds.length === data.length" class="select-checkbox bg-white"></th>
                        <template x-for="(col, index) in columns" :key="col.field">
                            {{-- ✅ Added 'text-center' to TH --}}
                            <th x-show="col.visible" 
                                class="px-4 py-2 relative h-12 text-center transition-colors duration-200 border-r border-transparent select-none group border-b border-blue-100 bg-slate-50/95" 
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
                                        <input type="text" :x-ref="'input-'+col.field" x-model="filters[col.field]" @input.debounce.500ms="fetchData()" @keydown.escape="openFilter = null" class="header-search-input w-full" placeholder="{{ __('account.search') }}">
                                        <button type="button" @click.stop="filters[col.field] = ''; fetchData(); openFilter = null;" class="absolute right-1 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500 p-0.5 rounded-md rtl:left-1 rtl:right-auto"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                    </div>
                                </div>
                                <div class="resizer" @mousedown.stop.prevent="initResize($event, col)"></div>
                            </th>
                        </template>
                        <th class="px-4 py-3 w-[100px] text-center print:hidden bg-blue-50/50 border-b border-blue-100 sticky right-0 z-20">{{ __('account.actions') }}</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">
                    <template x-for="acc in data" :key="acc.id">
                        <tr class="bg-white hover:bg-slate-50 transition-colors group" :class="selectedIds.includes(acc.id) ? 'bg-indigo-50/10' : ''">
                            <td class="px-4 py-2 text-center align-middle"><input type="checkbox" :value="acc.id" x-model="selectedIds" class="select-checkbox"></td>
                            <template x-for="col in columns" :key="col.field">
                                {{-- ✅ Added 'text-center' to TD --}}
                                <td x-show="col.visible" class="p-1 text-center" :class="col.class">
                                    <template x-if="col.field === 'id'"><div class="px-4 py-4 text-center font-mono text-xs text-slate-400" x-text="acc.id"></div></template>
                                    <template x-if="col.field === 'image'">
                                        <div @click="zoomImage(acc.image_url)" class="flex items-center justify-center cursor-pointer p-1">
                                            <div class="w-10 h-10 rounded-full bg-slate-50 border border-slate-200 overflow-hidden flex items-center justify-center shadow-sm relative group-hover:scale-110 transition-transform">
                                                <template x-if="acc.image_url"><img :src="acc.image_url" class="w-full h-full object-cover"></template>
                                                <template x-if="!acc.image_url"><span class="text-[10px] font-bold text-slate-400" x-text="acc.initial"></span></template>
                                            </div>
                                        </div>
                                    </template>
                                    {{-- ✅ Forced Center Alignment for Name & Secondary Name --}}
                                    <template x-if="col.field === 'name'"><span class="px-3 block text-center text-slate-700 font-bold text-xs truncate" x-text="acc.name"></span></template>
                                    <template x-if="col.field === 'secondary_name'"><span class="px-3 block text-center text-slate-500 font-normal text-xs truncate" x-text="acc.secondary_name || '-'"></span></template>
                                    <template x-if="col.field === 'branch_id'"><span class="px-2 block text-center text-[10px] uppercase text-slate-500 font-normal truncate" x-text="acc.branch_text || '-'"></span></template>
                                    <template x-if="col.field === 'account_type'"><div class="text-center"><span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border bg-slate-50 text-slate-600" x-text="acc.account_type"></span></div></template>
                                    
                                    {{-- 🔥 SUPPORTED CURRENCIES (Showing NAME not SYMBOL) --}}
                                    <template x-if="col.field === 'supported_currencies'">
                                        <div class="px-3 flex gap-1 justify-center flex-wrap">
                                            <template x-for="currId in (acc.supported_currency_ids || [])">
                                                {{-- ✅ UPDATED: getCurrencyName() instead of Symbol --}}
                                                <span class="bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded-[4px] text-[9px] border border-slate-200 font-bold uppercase" 
                                                      x-text="getCurrencyName(currId)"></span>
                                            </template>
                                        </div>
                                    </template>

                                    <template x-if="col.field === 'debt_limit'"><div class="text-center font-bold text-xs text-emerald-600" x-text="acc.debt_limit ? Number(acc.debt_limit).toLocaleString() : '-'"></div></template>
                                    <template x-if="col.field === 'debt_due_time'"><div class="text-center"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold border inline-flex items-center gap-1" :class="acc.debt_due_time > 0 ? 'bg-orange-50 text-orange-600 border-orange-100' : 'bg-slate-50 text-slate-400 border-slate-100'"><span x-show="acc.debt_due_time > 0">⏳</span><span x-text="acc.debt_due_time > 0 ? acc.debt_due_time + ' {{ __('account.days') }}' : '-'"></span></span></div></template>
                                    <template x-if="col.field === 'created_by'"><div class="px-4 py-4 text-[10px] uppercase text-slate-500 font-bold text-center truncate" x-text="acc.creator_name"></div></template>
                                    <template x-if="col.field === 'created_at'"><div class="px-4 py-4 text-[10px] text-slate-400 font-normal text-center truncate" x-text="formatDate(acc.created_at)"></div></template>
                                    <template x-if="col.field === 'location'"><div class="text-center"><button @click="viewMap(acc.location, acc.name)" class="p-1.5 rounded-full transition-colors" :class="acc.location ? 'text-indigo-600 hover:bg-indigo-50' : 'text-slate-300 cursor-not-allowed'" :disabled="!acc.location"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg></button></div></template>
                                    <template x-if="col.field === 'is_active'"><div class="flex items-center justify-center"><input type="checkbox" :checked="acc.is_active" disabled class="w-4 h-4 text-indigo-600 rounded border-slate-300"></div></template>
                                    {{-- Default Fallback Centered --}}
                                    <template x-if="!['id','image','name','secondary_name','branch_id','account_type','supported_currencies','debt_limit','debt_due_time','created_by','created_at','location','is_active'].includes(col.field)"><div class="px-3 text-xs text-slate-500 truncate text-center" x-text="acc[col.field.replace('_id', '_text')] || acc[col.field] || '-'"></div></template>
                                </td>
                            </template>
                            <td class="px-3 py-2 text-center print:hidden sticky-action group-hover:bg-slate-50 transition-colors">
                                <div class="flex items-center justify-center gap-2">
                                    <x-btn type="edit" @click="openEdit(acc)" title="{{ __('account.edit') }}" />
                                    <button @click="deleteRow(acc.delete_url)" class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!loading && data.length === 0" x-transition.opacity class="bg-white"><td :colspan="Object.keys(columns).length + 2" class="py-10 text-center border-b border-slate-50"><div class="flex flex-col justify-center items-center"><dotlottie-player src="https://lottie.host/ace77418-be70-4ea4-8c0a-88efe0221c91/aCjbIohU9b.lottie" background="transparent" speed="1" style="width: 200px; height: 200px;" loop autoplay></dotlottie-player><span class="text-slate-400 font-medium mt-4">{{ __('account.no_data_found') }}</span></div></td></tr>
                </tbody>
            </table>
        </div>
        
        <div class="mx-4 mt-2 px-4 py-3 bg-white border border-slate-200 rounded-lg flex justify-between items-center" x-show="pagination.last_page > 1">
            <div class="text-[10px] text-slate-500 font-medium"><span class="font-bold text-slate-700" x-text="pagination.from"></span> - <span class="font-bold text-slate-700" x-text="pagination.to"></span> / <span class="font-bold text-slate-700" x-text="pagination.total"></span></div>
            <div class="flex gap-1"><template x-for="link in pagination.links"><button @click="changePage(link.url)" x-html="link.label" :disabled="!link.url || link.active" class="w-7 h-7 flex items-center justify-center rounded-md text-[10px] font-bold transition-all border" :class="link.active ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white text-slate-500 hover:bg-slate-100 border-slate-200 hover:border-slate-300 disabled:opacity-50'" x-show="link.url"></button></template></div>
        </div>

        {{-- MODAL --}}
        <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak>
            <div @click.away="showModal = false" class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl p-8 relative">
                <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100"><div><h3 class="text-2xl font-black text-slate-800" x-text="editMode ? '{{ __('account.edit_account') }}' : '{{ __('account.new_account') }}'"></h3><p class="text-sm text-slate-400 mt-1">{{ __('account.subtitle') }}</p></div><button @click="showModal = false" class="text-slate-400 hover:text-slate-600 bg-slate-50 hover:bg-slate-100 p-2.5 rounded-xl transition-colors"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div>
                
                <form :action="editMode ? (item.edit_url || '/accounts/'+item.id) : '{{ route('accounts.store') }}'" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    @csrf <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>
                    {{-- Left Col --}}
                    <div class="space-y-5">
                        <div><label class="form-label">{{ __('account.name') }} *</label><input type="text" name="name" x-model="item.name" class="form-input" required></div>
                        <div><label class="form-label">{{ __('account.secondary_name') }}</label><input type="text" name="secondary_name" x-model="item.secondary_name" class="form-input"></div>
                        <div><label class="form-label">{{ __('account.type') }} *</label><select name="account_type" x-model="item.account_type_raw" class="form-input bg-white" required><option value="customer">{{ __('account.customer') }}</option><option value="vendor">{{ __('account.vendor') }}</option><option value="buyer_and_seller">{{ __('account.buyer_and_seller') }}</option><option value="other">{{ __('account.other') }}</option></select></div>
                        <div><label class="form-label">{{ __('account.branch') }}</label><select name="branch_id" x-model="item.branch_id" class="form-input bg-white"><option value="">{{ __('account.select_branch') }}</option>@foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach</select></div>
                        <div class="grid grid-cols-2 gap-4"><div><label class="form-label">{{ __('account.code') }}</label><input type="text" name="code" x-model="item.code" class="form-input bg-slate-50" readonly></div><div><label class="form-label">{{ __('account.manual_code') }}</label><input type="text" name="manual_code" x-model="item.manual_code" class="form-input"></div></div>
                        <div class="grid grid-cols-2 gap-4"><div><label class="form-label">{{ __('account.mobile_1') }}</label><input type="text" name="mobile_number_1" x-model="item.mobile_number_1" class="form-input"></div><div><label class="form-label">{{ __('account.mobile_2') }}</label><input type="text" name="mobile_number_2" x-model="item.mobile_number_2" class="form-input"></div></div>
                    </div>
                    {{-- Right Col --}}
                    <div class="space-y-5">
                        
                        {{-- 🔥 MULTI-SELECT SUPPORTED CURRENCIES ONLY --}}
                        <div x-data="{ openMulti: false }">
                            <label class="form-label">{{ __('account.supported_currencies') }} *</label>
                            <div class="relative">
                                <button type="button" @click="openMulti = !openMulti" @click.outside="openMulti = false" 
                                    class="form-input bg-white text-left flex items-center justify-between">
                                    <span class="text-sm text-slate-700" x-text="item.supported_currency_ids.length > 0 ? item.supported_currency_ids.length + ' {{ __('account.selected') }}' : '{{ __('account.select_supported') }}'"></span>
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                <div x-show="openMulti" class="absolute z-10 mt-1 w-full bg-white shadow-xl rounded-xl border border-slate-100 max-h-48 overflow-y-auto py-1" style="display:none;">
                                    @foreach($currencies as $c)
                                    <label class="flex items-center px-4 py-2 hover:bg-slate-50 cursor-pointer">
                                        <input type="checkbox" value="{{ $c->id }}" x-model="item.supported_currency_ids" class="rounded text-indigo-600 w-4 h-4 border-slate-300 focus:ring-indigo-500 mr-2">
                                        <span class="text-sm text-slate-700">{{ $c->currency_type }} ({{ $c->symbol }})</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            <template x-for="id in item.supported_currency_ids" :key="id">
                                <input type="hidden" name="supported_currency_ids[]" :value="id">
                            </template>
                        </div>

                        <div class="grid grid-cols-2 gap-4"><div><label class="form-label">{{ __('account.city') }}</label><select name="city_id" x-model="item.city_id" class="form-input bg-white"><option value="">{{ __('account.none') }}</option>@foreach($cities as $c)<option value="{{ $c->id }}">{{ $c->city_name }}</option>@endforeach</select></div><div><label class="form-label">{{ __('account.neighborhood') }}</label><select name="neighborhood_id" x-model="item.neighborhood_id" class="form-input bg-white"><option value="">{{ __('account.none') }}</option>@foreach($neighborhoods as $n)<option value="{{ $n->id }}">{{ $n->neighborhood_name }}</option>@endforeach</select></div></div>
                        <div class="relative"><label class="form-label">{{ __('account.gps_location') }}</label><div class="relative mt-1"><input type="text" name="location" x-model="item.location" class="form-input pl-10" readonly @click="getLocation()"><button type="button" @click="getLocation()" class="absolute inset-y-0 left-0 pl-3 flex items-center text-indigo-500 z-10"><svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg></button></div></div>
                        <div class="grid grid-cols-2 gap-4"><div><label class="form-label">{{ __('account.debt_limit') }}</label><input type="number" step="0.01" name="debt_limit" x-model="item.debt_limit" class="form-input" placeholder="0.00"></div><div><label class="form-label">{{ __('account.due_time') }} ({{ __('account.days') }})</label><input type="number" name="debt_due_time" x-model="item.debt_due_time" class="form-input text-center font-bold text-orange-500" placeholder="0"></div></div>
                        <div><label class="form-label">{{ __('account.profile_picture') }}</label><div class="relative border-2 border-dashed border-slate-300 rounded-xl p-4 hover:bg-slate-50 transition-all text-center cursor-pointer group"><input type="file" name="profile_picture" class="absolute inset-0 opacity-0 w-full h-full cursor-pointer" @change="fileName = $event.target.files[0].name"><div class="flex flex-col items-center"><span class="text-xs font-bold text-slate-500 group-hover:text-indigo-600">{{ __('account.upload_text') }}</span><span x-show="fileName" x-text="fileName" class="text-[10px] text-emerald-600 font-bold mt-1"></span></div></div></div>
                        <input type="hidden" name="is_active" value="0"><div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border"><input type="checkbox" name="is_active" value="1" x-model="item.is_active"><label>{{ __('account.active') }}</label></div>
                    </div>
                    <div class="col-span-2 flex justify-end gap-3 pt-4 border-t">
                        <button type="button" @click="showModal = false" class="px-6 py-2 text-slate-600 font-bold hover:bg-slate-100 rounded-lg">{{ __('account.cancel') }}</button>
                        <button type="submit" class="px-8 py-2 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700">{{ __('account.save') }}</button>
                    </div>
                </form>
            </div>
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

        {{-- GLOBAL HIDDEN FORMS --}}
        <form id="delete-form" method="POST" style="display:none">@csrf @method('DELETE')</form>
        <form id="bulk-delete-form" action="{{ route('accounts.bulk-delete') }}" method="POST" class="hidden">@csrf @method('DELETE')<input type="hidden" name="ids" id="bulk-delete-ids"></form>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('accountsManager', () => ({
                // ✅ SAFE DATA INJECTION: Prevents crash on special characters/Kurdistan text
                data: {!! json_encode($accounts->items(), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) !!},
                pagination: {!! json_encode($accounts, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) !!},
                branches: {!! json_encode($branches, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) !!},
                currencies: {!! json_encode($currencies, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) !!},
                
                showModal: false, editMode: false, showMapModal: false, mapInstance: null, selectedIds: [], item: { supported_currency_ids: [] }, zoomedImage: null, openFilter: null, draggingIndex: null, sortCol: 'id', sortAsc: false, fileName: '', filters: {},
                mapLayers: {}, currentMapStyle: 'road', mapMarker: null,
                
                columns: [
                    { field: 'id', label: '#', visible: true, width: 60, searchable: true },
                    { field: 'image', label: "{{ __('account.image') }}", visible: true, width: 80, searchable: false },
                    { field: 'code', label: "{{ __('account.code') }}", visible: true, width: 100, searchable: true },
                    { field: 'name', label: "{{ __('account.name') }}", visible: true, width: 180, searchable: true },
                    { field: 'secondary_name', label: "{{ __('account.secondary_name') }}", visible: true, width: 160, searchable: true },
                    { field: 'branch_id', label: "{{ __('account.branch') }}", visible: true, width: 140, searchable: false }, 
                    { field: 'account_type', label: "{{ __('account.type') }}", visible: true, width: 120, searchable: true },
                    { field: 'supported_currencies', label: "{{ __('account.supported_currencies') }}", visible: true, width: 160, searchable: false },
                    { field: 'debt_limit', label: "{{ __('account.debt_limit') }}", visible: true, width: 120, searchable: false },
                    { field: 'debt_due_time', label: "{{ __('account.due_time') }}", visible: true, width: 120, searchable: false },
                    { field: 'created_by', label: "{{ __('account.created_by') }}", visible: true, width: 140, searchable: false },
                    { field: 'created_at', label: "{{ __('account.created_at') }}", visible: true, width: 150, searchable: false },
                    { field: 'manual_code', label: "{{ __('account.manual_code') }}", visible: true, width: 100, searchable: true },
                    { field: 'mobile_number_1', label: "{{ __('account.mobile_1') }}", visible: true, width: 120, searchable: true },
                    { field: 'mobile_number_2', label: "{{ __('account.mobile_2') }}", visible: false, width: 120, searchable: true },
                    { field: 'city_id', label: "{{ __('account.city') }}", visible: false, width: 120, searchable: false },
                    { field: 'neighborhood_id', label: "{{ __('account.neighborhood') }}", visible: false, width: 120, searchable: false },
                    { field: 'location', label: "{{ __('account.gps_location') }}", visible: true, width: 80, searchable: false },
                    { field: 'is_active', label: "{{ __('account.status') }}", visible: true, width: 80, searchable: false }
                ],
                
                params: { sort: 'id', direction: 'desc', page: 1 },

                initData() { 
                    // ✅ NEW CACHE KEY to prevent blank table due to structure change
                    const saved = localStorage.getItem('acc_v15');
                    if(saved) {
                        const savedCols = JSON.parse(saved);
                        this.columns = savedCols.map(s => {
                            const def = this.columns.find(c => c.field === s.field);
                            return def ? { ...def, visible: s.visible, width: s.width } : null;
                        }).filter(c => c !== null);
                    }
                    this.columns.forEach(c => { this.filters[c.field] = ''; });
                    @if($errors->any()) this.showModal = true; @endif
                },
                saveState() { localStorage.setItem('acc_v15', JSON.stringify(this.columns)); },
                resetLayout() { localStorage.removeItem('acc_v15'); location.reload(); },

                dragStart(e, i) { this.draggingIndex = i; e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', i); },
                dragOver(e) { e.preventDefault(); e.dataTransfer.dropEffect = 'move'; },
                drop(e, targetIndex) { if (this.draggingIndex === null || this.draggingIndex === targetIndex) return; const element = this.columns.splice(this.draggingIndex, 1)[0]; this.columns.splice(targetIndex, 0, element); this.draggingIndex = null; this.saveState(); },
                initResize(e, col) { const startX = e.clientX; const startWidth = parseInt(col.width) || 100; const onMouseMove = (ev) => { col.width = Math.max(50, (document.dir === 'rtl' ? startWidth - (ev.clientX - startX) : startWidth + (ev.clientX - startX))); }; const onMouseUp = () => { window.removeEventListener('mousemove', onMouseMove); window.removeEventListener('mouseup', onMouseUp); this.saveState(); }; window.addEventListener('mousemove', onMouseMove); window.addEventListener('mouseup', onMouseUp); },
                toggleAllSelection() { this.selectedIds = (this.selectedIds.length === this.data.length) ? [] : this.data.map(a => a.id); },
                
                deleteRow(url) {
                    const form = document.getElementById('delete-form');
                    form.action = url;
                    if (window.confirmAction) { window.confirmAction('delete-form', "{{ __('account.are_you_sure') }}"); } 
                    else { if (confirm("{{ __('account.are_you_sure') }}")) form.submit(); }
                },
                bulkDelete() {
                    if (this.selectedIds.length === 0) return;
                    document.getElementById('bulk-delete-ids').value = JSON.stringify(this.selectedIds);
                    if (window.confirmAction) { window.confirmAction('bulk-delete-form', "{{ __('account.are_you_sure') }}"); } 
                    else { if (confirm("{{ __('account.are_you_sure') }}")) document.getElementById('bulk-delete-form').submit(); }
                },
                
                fetchData(url = null) { 
                    this.loading = true;
                    const oldData = [...this.data];
                    let targetUrl = url || "{{ route('accounts.index') }}"; 
                    let query = new URLSearchParams(); 
                    for (let key in this.params) { if(this.params[key]) query.append(key, this.params[key]); } 
                    for (let key in this.filters) { if(this.filters[key]) query.append(key, this.filters[key]); }
                    if (!url) targetUrl += '?' + query.toString(); 
                    
                    fetch(targetUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(res => { if(!res.ok) throw new Error('Network response was not ok'); return res.json(); })
                        .then(response => { this.data = response.data; this.pagination = response; this.loading = false; })
                        .catch(() => { this.loading = false; this.data = oldData; });
                },
                
                sortBy(field) { if (this.params.sort === field) { this.params.direction = this.params.direction === 'asc' ? 'desc' : 'asc'; } else { this.params.sort = field; this.params.direction = 'asc'; } this.fetchData(); },
                changePage(url) { if(url) this.fetchData(url); },
                formatDate(iso) { if(!iso) return '-'; return new Date(iso).toLocaleString(); },
                
                // ✅ UPDATED: Returns Currency Name (Type) instead of Symbol
                getCurrencyName(id) { 
                    const c = this.currencies.find(cur => cur.id == id); 
                    return c ? c.currency_type : id; 
                },

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
                zoomImage(url) { if(url) this.zoomedImage = url; },
                getLocation() { if (navigator.geolocation) { navigator.geolocation.getCurrentPosition( (p) => { this.item.location = p.coords.latitude + ',' + p.coords.longitude; }, (e) => { alert('GPS Error: ' + e.message); } ); } },
                
                openCreate() { 
                    this.showModal = true; this.editMode = false; this.fileName = '';
                    let baseCode = {!! json_encode($autoCode ?? '', JSON_HEX_APOS) !!}; 
                    this.item = { 
                        code: baseCode, 
                        account_type_raw: 'customer', 
                        is_active: true, 
                        supported_currency_ids: [], 
                        branch_id: '', 
                        city_id: '', 
                        neighborhood_id: '', 
                        debt_due_time: 0 
                    }; 
                },
                openEdit(acc) { 
                    this.item = JSON.parse(JSON.stringify(acc)); 
                    this.fileName = '';
                    
                    let supp = this.item.supported_currency_ids;
                    if (typeof supp === 'string') { try { supp = JSON.parse(supp); } catch(e) { supp = []; } }
                    this.item.supported_currency_ids = Array.isArray(supp) ? supp.map(String) : [];

                    this.item.is_active = (acc.is_active == 1 || acc.is_active == true); 
                    this.editMode = true; this.showModal = true; 
                }
            }));
        });
    </script>
</x-app-layout>