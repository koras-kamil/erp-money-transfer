<x-app-layout>
    {{-- ASSETS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <style>
        /* Shared Styles with Zones Page */
        .form-input { width: 100%; border: 1px solid #e2e8f0; padding: 10px; border-radius: 10px; font-size: 0.9rem; transition: all 0.2s; }
        .form-input:focus { border-color: #6366f1; ring: 2px; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); outline: none; }
        
        .custom-scrollbar::-webkit-scrollbar { height: 10px; width: 10px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 5px; border: 2px solid #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .search-active { background-color: #fff !important; box-shadow: inset 0 -2px 0 #6366f1; }
        .sticky-col-shadow { box-shadow: -4px 0 8px -4px rgba(0,0,0,0.1); clip-path: inset(0px 0px 0px -10px); }
        [dir="rtl"] .sticky-col-shadow { box-shadow: 4px 0 8px -4px rgba(0,0,0,0.1); }
        
        #map-container { height: 400px; width: 100%; border-radius: 12px; z-index: 1; }
        [x-cloak] { display: none !important; }
        
        .select-checkbox { width: 1.15rem; height: 1.15rem; border-radius: 6px; border: 2px solid #cbd5e1; color: #6366f1; cursor: pointer; transition: all 0.2s; }
        .select-checkbox:checked { border-color: #6366f1; }
    </style>

    <div x-data="accountsManager()" x-init="initData()" class="py-8 w-full min-w-0" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

        {{-- 1. HEADER & NAVIGATION --}}
        <div class="mx-4 md:mx-8 mb-8 flex flex-col md:flex-row justify-between items-end gap-6 no-print">
            
            {{-- Left Side: Title & Tabs --}}
            <div class="flex flex-col gap-4">
                <div>
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight">{{ __('account.title') }}</h3>
                    <p class="text-sm text-slate-500 font-medium mt-1">{{ __('account.subtitle') }}</p>
                </div>
                
                {{-- NEW SHARED COMPONENT TAB --}}
                <x-account-nav active="accounts" />
            </div>

            {{-- Right Side: Toolbar --}}
            <div class="flex flex-wrap items-center gap-2">
                
                {{-- Bulk Actions --}}
                <div x-show="selectedIds.length > 0" x-transition.scale.origin.right class="flex items-center gap-2 bg-red-50 px-3 py-2 rounded-xl border border-red-100 shadow-sm mr-2">
                    <span class="text-xs font-bold text-red-600"><span x-text="selectedIds.length"></span> {{ __('account.selected') }}</span>
                    <button @click="bulkDelete()" type="button" class="p-1 text-red-500 hover:bg-red-200 rounded-lg transition" title="{{ __('account.delete_selected') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                    <button @click="selectedIds = []" type="button" class="p-1 text-slate-400 hover:bg-slate-200 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Trash --}}
                <a href="#" class="w-10 h-10 flex items-center justify-center rounded-xl text-red-400 hover:text-red-600 hover:bg-red-50 transition border border-transparent hover:border-red-100"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></a>
                
                {{-- Print --}}
                <button @click="window.print()" class="w-10 h-10 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition border border-transparent hover:border-slate-200"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z"/></svg></button>
                
                {{-- Columns Dropdown --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.outside="open = false" class="w-10 h-10 flex items-center justify-center rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition border border-transparent hover:border-indigo-100"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg></button>
                    <div x-show="open" class="absolute top-full mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 z-50 p-2 ltr:right-0 rtl:left-0" x-cloak>
                        <div class="space-y-1 max-h-60 overflow-y-auto">
                            <template x-for="(col, key) in columns" :key="key">
                                <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 rounded cursor-pointer transition-colors">
                                    <input type="checkbox" x-model="col.visible" class="rounded text-indigo-600 w-4 h-4 border-slate-300">
                                    <span class="text-xs text-slate-700 font-medium" x-text="col.label"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Add New Button --}}
                <button @click="openCreate()" class="h-10 px-6 bg-indigo-600 text-white text-sm font-bold rounded-xl shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:shadow-indigo-300 transition-all transform active:scale-95 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>{{ __('account.add_new') }}</span>
                </button>
            </div>
        </div>

        {{-- 2. MAIN TABLE --}}
        <div class="relative w-full overflow-x-auto table-container bg-white shadow-xl shadow-slate-200/50 rounded-2xl border border-slate-200 mx-4 md:mx-8">
            
            {{-- Loading Spinner --}}
            <div x-show="loading" class="absolute inset-0 bg-white/60 backdrop-blur-[2px] z-50 flex items-center justify-center transition-opacity">
                <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
            </div>
            
            <div class="overflow-x-auto custom-scrollbar flex-1 relative min-h-[400px]">
                <table class="w-full text-sm text-left rtl:text-right text-slate-500 whitespace-nowrap">
                    <thead class="text-xs text-slate-600 uppercase bg-slate-50/80 border-b border-slate-200 font-extrabold sticky top-0 z-20 backdrop-blur-md">
                        <tr>
                            <th class="px-4 py-4 w-[50px] text-center bg-slate-50/95"><input type="checkbox" @click="toggleAllSelection()" :checked="data.length > 0 && selectedIds.length === data.length" class="select-checkbox bg-white"></th>
                            
                            <template x-for="(col, field) in columns" :key="field">
                                <th x-show="col.visible" class="py-4 px-4 relative h-14 min-w-[130px] transition-colors duration-200 border-r border-transparent" :class="[searchOpen === field ? 'search-active' : 'hover:bg-slate-100/50', col.class]">
                                    
                                    <div class="flex items-center justify-between gap-3 w-full h-full" x-show="searchOpen !== field">
                                        <div class="flex items-center gap-1.5 cursor-pointer select-none group" @click="sortBy(field)">
                                            <span x-text="col.label" class="group-hover:text-indigo-600 transition-colors"></span>
                                            <span class="text-indigo-500 text-[10px] flex flex-col -space-y-1" x-show="params.sort === field">
                                                <span :class="params.direction === 'asc' ? 'text-indigo-600' : 'text-slate-300'">▲</span>
                                                <span :class="params.direction === 'desc' ? 'text-indigo-600' : 'text-slate-300'">▼</span>
                                            </span>
                                        </div>
                                        <button x-show="field !== 'image' && field !== 'location'" @click.stop="openSearch(field)" class="text-slate-300 hover:text-indigo-500 transition-colors"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></button>
                                    </div>

                                    <div x-show="searchOpen === field" @click.outside="closeSearch()" class="absolute inset-0 flex items-center px-2 z-30 w-full h-full bg-white">
                                        <div class="relative w-full">
                                            <input type="text" x-model.debounce.500ms="params[field]" :x-ref="'input_' + field" class="w-full h-9 pl-3 pr-8 text-xs border border-indigo-200 rounded-lg focus:ring-2 focus:ring-indigo-500/50 bg-white" :placeholder="col.label" autocomplete="off">
                                            <button @click="params[field] = ''; closeSearch()" class="absolute right-1 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500 p-1 rounded-md"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                        </div>
                                    </div>
                                </th>
                            </template>
                            
                            <th class="px-4 py-4 text-center sticky right-0 bg-slate-50/95 backdrop-blur z-20 sticky-col-shadow">{{ __('account.actions') }}</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        <template x-for="acc in data" :key="acc.id">
                            <tr class="hover:bg-indigo-50/30 transition duration-150 group" :class="selectedIds.includes(acc.id) ? 'bg-indigo-50/20' : ''">
                                
                                <td class="px-4 py-4 text-center">
                                    <input type="checkbox" :value="acc.id" x-model="selectedIds" class="select-checkbox">
                                </td>

                                <td x-show="columns.id.visible" class="px-6 py-4 font-mono text-sm text-slate-400 hidden xl:table-cell" x-text="acc.id"></td>
                                <td x-show="columns.image.visible" class="px-6 py-4">
                                    <div @click="zoomImage(acc.image_url)" class="w-9 h-9 rounded-full bg-white border border-slate-200 p-0.5 shadow-sm relative overflow-hidden cursor-pointer hover:scale-110 transition-all">
                                        <template x-if="acc.image_url"><img :src="acc.image_url" class="w-full h-full rounded-full object-cover"></template>
                                        <template x-if="!acc.image_url"><div class="w-full h-full rounded-full bg-slate-100 flex items-center justify-center text-slate-400 font-bold text-[10px]" x-text="acc.initial"></div></template>
                                    </div>
                                </td>
                                <td x-show="columns.code.visible" class="px-6 py-4 hidden md:table-cell"><span class="text-xs font-mono font-bold text-indigo-500 bg-indigo-50/50 rounded px-2 py-1" x-text="acc.code"></span></td>
                                <td x-show="columns.manual_code.visible" class="px-6 py-4 text-sm text-slate-500 font-mono hidden xl:table-cell" x-text="acc.manual_code"></td>
                                <td x-show="columns.name.visible" class="px-6 py-4"><div class="flex flex-col"><span class="font-bold text-slate-700 text-sm" x-text="acc.name"></span><span class="text-xs text-slate-400" x-text="acc.secondary_name"></span></div></td>
                                <td x-show="columns.account_type.visible" class="px-6 py-4"><span class="px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider border" :class="{'bg-blue-50 text-blue-600 border-blue-100': acc.account_type_raw === 'customer', 'bg-purple-50 text-purple-600 border-purple-100': acc.account_type_raw === 'vendor', 'bg-orange-50 text-orange-600 border-orange-100': acc.account_type_raw === 'buyer_and_seller', 'bg-gray-50 text-gray-600 border-gray-100': acc.account_type_raw === 'other'}" x-text="acc.account_type"></span></td>
                                <td x-show="columns.mobile_number_1.visible" class="px-6 py-4 text-sm font-mono text-slate-500 hidden lg:table-cell" x-text="acc.mobile_number_1"></td>
                                <td x-show="columns.currency_id.visible" class="px-6 py-4 text-sm font-bold text-slate-700 hidden sm:table-cell" x-text="acc.currency_text"></td>
                                <td x-show="columns.city_id.visible" class="px-6 py-4 text-sm text-slate-500 hidden xl:table-cell" x-text="acc.city_text"></td>
                                <td x-show="columns.neighborhood_id.visible" class="px-6 py-4 text-sm text-slate-500 hidden xl:table-cell" x-text="acc.neighborhood_text"></td>
                                <td x-show="columns.debt_limit.visible" class="px-6 py-4 text-sm font-mono text-slate-600 hidden lg:table-cell" x-text="acc.debt_limit"></td>
                                <td x-show="columns.debt_due_time.visible" class="px-6 py-4 text-sm text-slate-500 hidden 2xl:table-cell" x-text="acc.debt_due_time"></td>
                                <td x-show="columns.location.visible" class="px-6 py-4 hidden md:table-cell"><button @click="viewMap(acc.location, acc.name)" class="flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium border transition-colors" :class="acc.location ? 'text-indigo-600 bg-indigo-50 border-indigo-200 hover:bg-indigo-100' : 'text-slate-400 bg-slate-50 border-slate-200 cursor-not-allowed'"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span x-text="acc.location ? '{{ __('account.map') }}' : '{{ __('account.no_gps') }}'"></span></button></td>
                                <td x-show="columns.is_active.visible" class="px-6 py-4 text-center hidden sm:table-cell"><span class="w-2.5 h-2.5 rounded-full inline-block" :class="acc.is_active ? 'bg-emerald-500' : 'bg-red-500'"></span></td>

                                <td class="px-6 py-4 text-center sticky-action bg-white group-hover:bg-slate-50 transition-colors">
                                    <div class="flex items-center justify-center gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                        <button @click="openEdit(acc)" class="w-8 h-8 flex items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-500 hover:text-white transition shadow-sm" title="{{ __('account.edit') }}"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                        <form :action="acc.delete_url" method="POST" onsubmit="return confirm('{{ __('account.are_you_sure') }}')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition shadow-sm" title="{{ __('account.delete') }}"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="data.length === 0 && !loading" x-cloak><td colspan="15" class="px-6 py-24 text-center text-slate-400 flex flex-col items-center justify-center"><div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4"><svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg></div><span class="text-sm font-medium text-slate-500">{{ __('account.none') }}</span></td></tr>
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex justify-between items-center" x-show="pagination.total > 0">
                <div class="text-xs text-slate-500 font-medium"><span class="font-bold text-slate-700" x-text="pagination.from + ' - ' + pagination.to"></span> / <span class="font-bold text-slate-700" x-text="pagination.total"></span></div>
                <div class="flex gap-1"><template x-for="link in pagination.links"><button @click="changePage(link.url)" x-html="link.label" :disabled="!link.url || link.active" class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold transition-all border" :class="link.active ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white text-slate-500 hover:bg-slate-100 border-slate-200 hover:border-slate-300 disabled:opacity-50'" x-show="link.url"></button></template></div>
            </div>
        </div>

        {{-- MODALS --}}
        <div x-show="showMapModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" x-cloak x-transition.opacity><div @click.away="showMapModal = false" class="bg-white rounded-3xl shadow-2xl w-full max-w-3xl overflow-hidden relative"><div class="p-4 border-b flex justify-between items-center bg-slate-50"><h3 class="font-bold text-lg text-slate-800 flex items-center gap-2"><svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg> <span x-text="mapTitle"></span></h3><button @click="showMapModal = false" class="text-slate-400 hover:text-red-500 transition"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div><div class="p-2"><div id="map-container"></div></div></div></div>
        <div x-show="zoomedImage" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/95 backdrop-blur-sm" @click="zoomedImage = null" x-cloak x-transition.opacity><img :src="zoomedImage" class="max-w-[85vw] max-h-[85vh] rounded-lg shadow-2xl ring-4 ring-white/10 scale-95 transition-transform duration-300" :class="zoomedImage ? 'scale-100' : 'scale-95'" @click.stop><button @click="zoomedImage = null" class="absolute top-6 right-6 text-white/70 hover:text-white p-2"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div>
        
        {{-- CREATE/EDIT MODAL --}}
        <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" style="display: none;" x-transition.opacity x-cloak>
            <div @click.away="showModal = false" class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto p-8 relative transform transition-all" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
                <div class="flex justify-between items-center mb-8 pb-4 border-b border-slate-100"><div><h3 class="text-2xl font-black text-slate-800" x-text="editMode ? '{{ __('account.edit_account') }}' : '{{ __('account.new_account') }}'"></h3><p class="text-sm text-slate-400 mt-1">{{ __('account.subtitle') }}</p></div><button @click="showModal = false" class="text-slate-400 hover:text-slate-600 bg-slate-50 hover:bg-slate-100 p-2.5 rounded-xl transition-colors"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div>
                <form :action="editMode ? item.edit_url : '{{ route('accounts.store') }}'" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    @csrf <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>
                    <div class="space-y-5">
                        <div><label class="form-label">{{ __('account.account_code') }}</label><input type="text" name="code" x-model="item.code" class="form-input bg-slate-50 text-slate-500 font-mono tracking-wide" readonly></div>
                        <div><label class="form-label">{{ __('account.manual_code') }}</label><input type="text" name="manual_code" x-model="item.manual_code" class="form-input" placeholder="{{ __('account.manual_code') }}"></div>
                        <div><label class="form-label">{{ __('account.name') }} <span class="text-red-500">*</span></label><input type="text" name="name" x-model="item.name" class="form-input" required placeholder="{{ __('account.name') }}"></div>
                        <div><label class="form-label">{{ __('account.secondary_name') }}</label><input type="text" name="secondary_name" x-model="item.secondary_name" class="form-input"></div>
                        <div class="grid grid-cols-2 gap-4"><div><label class="form-label">{{ __('account.mobile_1') }}</label><input type="text" name="mobile_number_1" x-model="item.mobile_number_1" class="form-input" placeholder="+964"></div><div><label class="form-label">{{ __('account.mobile_2') }}</label><input type="text" name="mobile_number_2" x-model="item.mobile_number_2" class="form-input"></div></div>
                    </div>
                    <div class="space-y-5">
                        <div><label class="form-label">{{ __('account.account_type') }} <span class="text-red-500">*</span></label><select name="account_type" x-model="item.account_type_raw" class="form-input bg-white" required><option value="customer">{{ __('account.customer') }}</option><option value="vendor">{{ __('account.vendor') }}</option><option value="buyer_and_seller">{{ __('account.buyer_and_seller') }}</option><option value="other">{{ __('account.other') }}</option></select></div>
                        <div class="grid grid-cols-2 gap-4"><div><label class="form-label">{{ __('account.currency') }} <span class="text-red-500">*</span></label><select name="currency_id" x-model="item.currency_id" class="form-input bg-white" required><option value="" disabled selected>{{ __('account.select_currency') }}</option>@foreach($currencies as $curr)<option value="{{ $curr->id }}">{{ $curr->currency_type }}</option>@endforeach</select></div>
                        <div><label class="form-label">{{ __('account.city') }}</label><select name="city_id" x-model="item.city_id" class="form-input bg-white"><option value="">{{ __('account.none') }}</option>@foreach($cities as $c)<option value="{{ $c->id }}">{{ $c->city_name }}</option>@endforeach</select></div></div>
                        <div><label class="form-label">{{ __('account.neighborhood') }}</label><select name="neighborhood_id" x-model="item.neighborhood_id" class="form-input bg-white"><option value="">{{ __('account.none') }}</option>@foreach($neighborhoods as $n)<option value="{{ $n->id }}">{{ $n->neighborhood_name }}</option>@endforeach</select></div>
                        <div><label class="form-label">{{ __('account.gps_location') }}</label><input type="text" name="location" x-model="item.location" class="form-input pl-10 bg-slate-50 cursor-pointer hover:bg-slate-100 transition-colors" readonly placeholder="{{ __('account.gps_location') }}" @click="getLocation()"><button type="button" @click="getLocation()" class="absolute bottom-2.5 left-2.5 text-indigo-500 hover:text-indigo-700 transition p-1" title="{{ __('account.gps_location') }}"><svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg></button></div>
                        <div class="grid grid-cols-2 gap-4"><div><label class="form-label">{{ __('account.debt_limit') }}</label><input type="number" step="0.01" name="debt_limit" x-model="item.debt_limit" class="form-input" placeholder="0.00"></div><div><label class="form-label">{{ __('account.due_time') }}</label><input type="number" name="debt_due_time" x-model="item.debt_due_time" class="form-input" placeholder="0"></div></div>
                        <div><label class="form-label">{{ __('account.profile_picture') }}</label><div class="relative border-2 border-dashed border-slate-300 rounded-xl p-4 hover:bg-slate-50 hover:border-indigo-400 transition-all text-center cursor-pointer group"><input type="file" name="profile_picture" class="absolute inset-0 opacity-0 w-full h-full cursor-pointer"><div class="flex flex-col items-center"><svg class="w-8 h-8 text-slate-400 group-hover:text-indigo-500 mb-2 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg><span class="text-xs font-bold text-slate-500 group-hover:text-indigo-600 transition-colors">{{ __('account.profile_picture') }}</span></div></div></div>
                        <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100"><input type="checkbox" name="is_active" id="is_active" class="rounded text-indigo-600 w-5 h-5 border-slate-300 focus:ring-indigo-500" value="1" x-model="item.is_active" :checked="item.is_active"><label for="is_active" class="text-sm font-bold text-slate-700 cursor-pointer select-none">{{ __('account.active_account') }}</label></div>
                    </div>
                    <div class="col-span-1 md:col-span-2 flex justify-end gap-3 mt-6 pt-6 border-t border-slate-100">
                        <button type="button" @click="showModal = false" class="px-6 py-3 text-slate-600 font-bold hover:bg-slate-100 rounded-xl transition-colors">{{ __('account.cancel') }}</button>
                        <button type="submit" class="px-8 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all transform active:scale-95">{{ __('account.save') }}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('accountsManager', () => ({
                data: [], pagination: {}, searchOpen: null, zoomedImage: null, showModal: false, editMode: false, loading: false, showMapModal: false, mapInstance: null, mapMarker: null, mapTitle: '', selectedIds: [], item: {}, 
                
                columns: {
                    'id': { label: '#', class: 'hidden xl:table-cell', visible: true },
                    'image': { label: "{{ __('account.image') }}", class: '', visible: true },
                    'code': { label: "{{ __('account.code') }}", class: 'hidden md:table-cell', visible: true },
                    'manual_code': { label: "{{ __('account.manual_code') }}", class: 'hidden xl:table-cell', visible: true },
                    'name': { label: "{{ __('account.name') }}", class: '', visible: true },
                    'account_type': { label: "{{ __('account.type') }}", class: '', visible: true },
                    'mobile_number_1': { label: "{{ __('account.mobile_1') }}", class: 'hidden lg:table-cell', visible: true },
                    'currency_id': { label: "{{ __('account.currency') }}", class: 'hidden sm:table-cell', visible: true },
                    'city_id': { label: "{{ __('account.city') }}", class: 'hidden xl:table-cell', visible: true },
                    'neighborhood_id': { label: "{{ __('account.neighborhood') }}", class: 'hidden xl:table-cell', visible: true },
                    'debt_limit': { label: "{{ __('account.debt_limit') }}", class: 'hidden lg:table-cell', visible: true },
                    'debt_due_time': { label: "{{ __('account.due_time') }}", class: 'hidden 2xl:table-cell', visible: true },
                    'location': { label: "{{ __('account.gps_location') }}", class: 'hidden md:table-cell', visible: true },
                    'is_active': { label: "{{ __('account.status') }}", class: 'hidden sm:table-cell', visible: true }
                },

                params: { sort: 'id', direction: 'desc', page: 1, id: '', code: '', manual_code: '', name: '', mobile_number_1: '', account_type: '', debt_limit: '', currency_id: '', city_id: '', neighborhood_id: '', is_active: '' },

                initData() { this.fetchData(); this.$watch('params', () => { this.fetchData(); }); document.addEventListener('click', (e) => { if (e.target.closest('.pagination a')) { e.preventDefault(); this.fetchData(e.target.closest('.pagination a').href); } }); },

                fetchData(url = null) {
                    this.loading = true; let targetUrl = url || "{{ route('accounts.index') }}"; let query = new URLSearchParams();
                    for (let key in this.params) { if(this.params[key]) query.append(key, this.params[key]); }
                    if (!url) targetUrl += '?' + query.toString();
                    fetch(targetUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(res => res.json()).then(response => { this.data = response.data; this.pagination = response; this.loading = false; }).catch(() => this.loading = false);
                },

                toggleAllSelection() { this.selectedIds = (this.selectedIds.length === this.data.length) ? [] : this.data.map(a => a.id); },
                bulkDelete() { if(confirm('{{ __('account.are_you_sure') }}')) { alert('Implement bulk delete API here'); } },

                viewMap(loc, name) {
                    if (!loc) return; this.showMapModal = true; this.mapTitle = name;
                    this.$nextTick(() => {
                        let coords = loc.split(',').map(Number);
                        if (!this.mapInstance) { this.mapInstance = L.map('map-container').setView(coords, 13); L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(this.mapInstance); } 
                        else { this.mapInstance.setView(coords, 13); this.mapInstance.invalidateSize(); }
                        if (this.mapMarker) this.mapInstance.removeLayer(this.mapMarker);
                        this.mapMarker = L.marker(coords).addTo(this.mapInstance).bindPopup(name).openPopup();
                    });
                },

                openSearch(field) { this.searchOpen = field; this.$nextTick(() => { const input = this.$refs['input_' + field]; if (input) input.focus(); }); },
                closeSearch() { this.searchOpen = null; },
                sortBy(field) { if (this.params.sort === field) { this.params.direction = this.params.direction === 'asc' ? 'desc' : 'asc'; } else { this.params.sort = field; this.params.direction = 'asc'; } this.fetchData(); },
                changePage(url) { if(url) this.fetchData(url); },
                zoomImage(url) { if(url) this.zoomedImage = url; },
                getLocation() { if (navigator.geolocation) { this.item.location = 'Locating...'; navigator.geolocation.getCurrentPosition( (position) => { this.item.location = position.coords.latitude + ', ' + position.coords.longitude; }, (error) => { alert('GPS Error: ' + error.message); this.item.location = ''; } ); } else { alert('Geolocation not supported'); } },
                openCreate() { this.showModal = true; this.editMode = false; this.item = { code: '{{ $autoCode }}', account_type_raw: 'customer', is_active: true, currency_id: '', city_id: '', neighborhood_id: '' }; },
                openEdit(acc) { this.item = JSON.parse(JSON.stringify(acc)); this.item.is_active = !!acc.is_active; this.editMode = true; this.showModal = true; }
            }));
        });
    </script>
</x-app-layout>