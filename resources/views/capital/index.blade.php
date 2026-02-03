<x-app-layout>
    {{-- REQUIRED: Lottie Script --}}
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>

    {{-- STYLES --}}
    <style>
        .sheet-input { width: 100%; height: 100%; display: flex; align-items: center; background: transparent; border: 1px solid transparent; padding: 0 12px; font-size: 0.875rem; color: #1f2937; font-weight: 500; border-radius: 6px; transition: all 0.15s ease-in-out; }
        .sheet-input:focus { background-color: #fff; border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2); outline: none; }
        .sheet-input[readonly] { cursor: default; color: #64748b; background-color: transparent; }
        
        select.sheet-input {
            -webkit-appearance: none; appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.2em 1.2em;
            padding-right: 2.5rem; padding-left: 0.75rem; cursor: pointer; white-space: nowrap; 
        }
        [dir="rtl"] select.sheet-input { background-position: left 0.5rem center; padding-right: 0.75rem; padding-left: 2.5rem; }
        
        .select-checkbox { width: 1.1rem; height: 1.1rem; border-radius: 4px; border: 1px solid #cbd5e1; color: #6366f1; cursor: pointer; transition: all 0.2s; }
        .table-container::-webkit-scrollbar { height: 6px; }
        .table-container::-webkit-scrollbar-track { background: #f1f1f1; }
        .table-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        
        @keyframes slideIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
        .new-row { animation: slideIn 0.3s ease-out forwards; background-color: #f0fdf4 !important; }
        .ag-row-editing { background-color: #f0fdf4 !important; border-bottom: 1px solid #bbf7d0 !important; }

        /* Header Interactivity */
        .th-container { position: relative; width: 100%; height: 32px; display: flex; align-items: center; overflow: hidden; }
        .th-title { position: absolute; inset: 0; display: flex; align-items: center; justify-content: space-between; gap: 4px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); transform: translateY(0); opacity: 1; cursor: grab; }
        .th-title:active { cursor: grabbing; }
        .search-active .th-title { transform: translateY(-100%); opacity: 0; pointer-events: none; }
        .th-search { position: absolute; inset: 0; display: flex; align-items: center; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); transform: translateY(100%); opacity: 0; pointer-events: none; }
        .search-active .th-search { transform: translateY(0); opacity: 1; pointer-events: auto; }
        .header-search-input { width: 100%; height: 100%; background-color: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 6px; padding-left: 28px; padding-right: 24px; font-size: 0.75rem; color: #1f2937; transition: all 0.15s; }
        [dir="rtl"] .header-search-input { padding-left: 24px; padding-right: 28px; }
        .header-search-input:focus { background-color: #fff; border-color: #3b82f6; outline: none; box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1); }
        .resizer { position: absolute; right: -4px; top: 0; height: 100%; width: 8px; cursor: col-resize; z-index: 50; touch-action: none; }
        .resizer:hover::after, .resizing::after { content: ''; position: absolute; right: 4px; top: 20%; height: 60%; width: 2px; background-color: #3b82f6; }
        [dir="rtl"] .resizer { right: auto; left: -4px; }
        [dir="rtl"] .resizer:hover::after { right: auto; left: 4px; }
        .dragging-col { opacity: 0.4; background-color: #e0e7ff; border: 2px dashed #6366f1; }

        @media print { .no-print, button, .print\:hidden { display: none !important; } .overflow-x-auto { overflow: visible !important; } table { width: 100% !important; } }
    </style>

    @php
        $globalTotalBalance = \App\Models\Capital::sum('balance_usd');
        $globalTotalCount = \App\Models\Capital::count();
        $setting = \App\Models\Setting::where('key', 'base_currency_id')->first();
        $systemBaseId = $setting ? $setting->value : 0;
        $totalShareUsed = \App\Models\Capital::sum('share_percentage');
    @endphp

    <div x-data="tableManager()" x-init="initData()" class="py-6 w-full min-w-0" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

        {{-- SUMMARY CARDS --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 px-4 no-print">
            {{-- Balance Card --}}
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between relative overflow-hidden group transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                <div class="absolute ltr:-right-6 rtl:-left-6 -top-6 w-24 h-24 bg-indigo-50 rounded-full transition-transform duration-700 ease-in-out group-hover:scale-[15]"></div>
                <div class="relative z-10 pointer-events-none">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('capital.balance_usd') }}</p>
                    <h4 class="text-2xl font-black text-indigo-700 mt-1">${{ number_format($globalTotalBalance, 2) }}</h4>
                </div>
                <div class="relative z-10 p-3 bg-white text-indigo-600 rounded-xl shadow-sm border border-indigo-50"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
            </div>
            
            {{-- Share Card --}}
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between relative overflow-hidden group transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                <div class="absolute ltr:-right-6 rtl:-left-6 -top-6 w-24 h-24 bg-emerald-50 rounded-full transition-transform duration-700 ease-in-out group-hover:scale-[15]"></div>
                <div class="relative z-10 pointer-events-none">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('capital.share_percent') }}</p>
                    <div class="flex items-baseline gap-1 mt-1"><h4 class="text-2xl font-black" :class="totalShare > 100 ? 'text-red-500' : 'text-emerald-600'"><span x-text="totalShare"></span>%</h4><span class="text-sm font-bold text-slate-400">/ 100%</span></div>
                </div>
                <div class="relative z-10 p-3 bg-white text-emerald-600 rounded-xl shadow-sm border border-emerald-50"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg></div>
            </div>

            {{-- Owner Card --}}
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between relative overflow-hidden group transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                <div class="absolute ltr:-right-6 rtl:-left-6 -top-6 w-24 h-24 bg-blue-50 rounded-full transition-transform duration-700 ease-in-out group-hover:scale-[15]"></div>
                <div class="relative z-10 pointer-events-none">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('capital.owner') }}</p>
                    <h4 class="text-2xl font-black text-slate-700 mt-1">{{ number_format($globalTotalCount) }}</h4>
                </div>
                <div class="relative z-10 p-3 bg-white text-blue-600 rounded-xl shadow-sm border border-blue-50"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg></div>
            </div>
        </div>

        {{-- TOOLBAR --}}
        <div class="mx-4 mb-6 flex flex-col md:flex-row justify-between items-center gap-4 no-print">
            <div class="bg-slate-100 p-1 rounded-lg flex items-center shadow-inner">
                <span class="px-5 py-2 text-sm font-bold rounded-md bg-white text-indigo-600 shadow-sm transition">{{ __('capital.title') }}</span>
            </div>
            
            <div class="flex flex-wrap items-center gap-2">
                {{-- Bulk Actions --}}
                <div x-show="selectedIds.length > 0" x-transition class="flex items-center gap-2 bg-red-50 px-2 py-1 rounded-lg border border-red-100 mr-2 ml-2">
                    <span class="text-xs font-bold text-red-600 px-2"><span x-text="selectedIds.length"></span> {{ __('capital.selected') }}</span>
                    <x-btn type="bulk-delete" @click="bulkDelete()">{{ __('capital.delete_selected') }}</x-btn>
                    <button @click="selectedIds = []" type="button" class="px-2 py-1.5 text-slate-500 hover:text-slate-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>

                <x-btn type="trash" href="{{ route('capitals.trash') }}" title="{{ __('capital.trash') }}" />
                
                {{-- Column Config --}}
                <div x-data="{ open: false }" class="relative">
                    <x-btn type="columns" @click="open = !open" @click.away="open = false" title="{{ __('capital.columns') }}" />
                    <div x-show="open" class="absolute top-full mt-3 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 p-3 ltr:right-0 rtl:left-0" style="display:none;">
                        <div class="flex justify-between items-center px-2 py-1 mb-2 border-b border-slate-100 pb-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">{{ __('capital.columns') }}</span>
                            <button @click="resetLayout(); open = false;" class="text-[10px] text-blue-500 hover:underline cursor-pointer">{{ __('capital.reset_layout') }}</button>
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

                <x-btn type="print" href="{{ route('capitals.pdf') }}" title="{{ __('capital.report_title') }}" />
                <x-btn type="add" @click="addNewRow()" title="{{ __('capital.add_new') }}" />
            </div>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="relative w-full overflow-x-auto table-container bg-white shadow-sm rounded-lg border border-slate-200 mx-4 pb-20">
            <form id="singleRowForm" action="{{ route('capitals.store') }}" method="POST" class="hidden">@csrf <div id="singleRowInputs"></div></form>
            <table class="w-full text-sm text-left rtl:text-right text-slate-500 whitespace-nowrap border-separate border-spacing-0">
                <thead class="text-xs text-slate-700 uppercase bg-blue-50/50 border-b border-blue-100 sticky top-0 z-20">
                    <tr>
                        <th class="px-4 py-3 w-[40px] text-center bg-slate-50/95 border-b border-blue-100"><input type="checkbox" @click="toggleAllSelection()" :checked="allSelected" class="select-checkbox bg-white"></th>

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
                                    <div class="th-title">
                                        <div @click="sortBy(col.field)" class="flex items-center gap-1 cursor-pointer flex-1 h-full hover:text-indigo-600 transition-colors">
                                            <span x-text="col.label"></span>
                                            <svg class="w-3 h-3 text-indigo-500 transition-transform" :class="sortCol === col.field && !sortAsc ? 'rotate-180' : ''" x-show="sortCol === col.field" fill="currentColor" viewBox="0 0 20 20"><path d="M5 10l5-5 5 5H5z"/></svg>
                                        </div>
                                        <button type="button" @click="openFilter = col.field; setTimeout(() => $refs['input-'+col.field].focus(), 100)" class="p-1 rounded-md text-slate-400 hover:text-indigo-600 hover:bg-slate-100 transition" :class="filters[col.field] ? 'text-indigo-600' : ''">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </button>
                                    </div>
                                    <div class="th-search">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none rtl:right-0 rtl:left-auto rtl:pr-2">
                                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </div>
                                        <input type="text" :x-ref="'input-'+col.field" x-model="filters[col.field]" @input.debounce="filterData()" @keydown.escape="openFilter = null" class="header-search-input" placeholder="{{ __('capital.search') }}">
                                        <button type="button" @click="filters[col.field] = ''; filterData(); openFilter = null;" class="absolute right-0 top-0 h-full px-2 text-gray-400 hover:text-red-500 rtl:left-0 rtl:right-auto transition">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="resizer" @mousedown.stop.prevent="initResize($event, col)"></div>
                            </th>
                        </template>

                        <th class="px-4 py-3 w-[5%] text-center print:hidden bg-blue-50/50 border-b border-blue-100">{{ __('capital.action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <template x-for="(row, rowIndex) in filteredRows" :key="row.id || ('new-'+rowIndex)">
                        <tr class="hover:bg-slate-50 transition-colors group" 
                            :class="[editingId === row.id ? 'bg-indigo-50/20' : '', selectedIds.includes(row.id) ? 'bg-indigo-50/10' : '', row.isNew ? 'new-row' : '']">
                            
                            <td class="px-4 py-4 text-center"><input type="checkbox" :value="row.id" x-model="selectedIds" class="select-checkbox"></td>

                            {{-- Cells Loop --}}
                            <template x-for="col in columns" :key="col.field">
                                <td x-show="col.visible" :class="col.field === 'id' ? 'px-4 py-4 text-center font-mono text-xs text-slate-400' : 'p-1'">
                                    
                                    {{-- ID --}}
                                    <template x-if="col.field === 'id'">
                                        <div class="font-normal text-slate-400">
                                            <span x-text="String(row.id).startsWith('new-') ? getNextId() : row.id"></span>
                                            <input type="hidden" :name="'capitals['+rowIndex+'][id]'" :value="row.id">
                                        </div>
                                    </template>

                                    {{-- Owner --}}
                                    <template x-if="col.field === 'owner'">
                                        <div>
                                            <span x-show="editingId !== row.id" x-text="getOwnerName(row.owner_id)" class="px-3 block text-slate-700 font-bold truncate"></span>
                                            <select x-show="editingId === row.id" x-model="row.owner_id" class="sheet-input font-bold text-slate-700">
                                                <option value="" disabled selected>{{ __('capital.owner') }}</option>
                                                <template x-for="owner in owners" :key="owner.id"><option :value="owner.id" x-text="owner.name"></option></template>
                                            </select>
                                        </div>
                                    </template>

                                    {{-- Share --}}
                                    <template x-if="col.field === 'share'">
                                        <div class="text-center">
                                            <span x-show="editingId !== row.id" x-text="row.share_percentage + '%'" class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs font-bold border border-blue-100"></span>
                                            <input x-show="editingId === row.id" type="number" step="0.01" x-model="row.share_percentage" @input="recalculateShares()" class="sheet-input text-center font-bold text-blue-700">
                                        </div>
                                    </template>

                                    {{-- Amount --}}
                                    <template x-if="col.field === 'amount'">
                                        <div>
                                            <div x-show="editingId !== row.id" x-text="formatNumberDisplay(row.amount)" class="px-3 block text-right font-medium text-slate-700"></div>
                                            <input x-show="editingId === row.id" type="text" x-model="row.amount" @input="calculateRow(row)" class="sheet-input text-right font-medium">
                                        </div>
                                    </template>

                                    {{-- Currency --}}
                                    <template x-if="col.field === 'currency'">
                                        <div>
                                            <div x-show="editingId !== row.id" class="text-center"><span x-text="getCurrencyName(row.currency_id)" class="bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded text-[10px] font-bold uppercase"></span></div>
                                            <select x-show="editingId === row.id" x-model="row.currency_id" @change="calculateRow(row)" class="sheet-input text-center text-xs uppercase font-bold text-indigo-600">
                                                <template x-for="curr in currencies" :key="curr.id"><option :value="curr.id" x-text="curr.currency_type"></option></template>
                                            </select>
                                        </div>
                                    </template>

                                    {{-- Rate --}}
                                    <template x-if="col.field === 'rate'">
                                        <div class="px-3 text-right text-xs text-slate-500">
                                            <div x-show="editingId !== row.id">
                                                <span>1 $ = <span x-text="formatRate(row.rate)"></span></span>
                                            </div>
                                            <input x-show="editingId === row.id" type="text" :value="row.rate" readonly class="sheet-input text-right text-xs text-slate-500 bg-gray-50">
                                        </div>
                                    </template>

                                    {{-- Balance --}}
                                    <template x-if="col.field === 'balance'">
                                        <div>
                                            <div x-show="editingId !== row.id" x-text="formatNumberDisplay(row.balance_usd) + ' $'" class="px-3 block text-right font-black text-indigo-600"></div>
                                            <input x-show="editingId === row.id" type="text" :value="formatNumberDisplay(row.balance_usd)" readonly class="sheet-input text-right font-black text-indigo-600 bg-transparent">
                                        </div>
                                    </template>

                                    {{-- Creator --}}
                                    <template x-if="col.field === 'creator'">
                                        <div class="px-4 py-4 text-[10px] uppercase text-slate-400 font-normal text-center truncate" x-text="row.creator_name"></div>
                                    </template>

                                    {{-- Date --}}
                                    <template x-if="col.field === 'date'">
                                        <div>
                                            <div x-show="editingId !== row.id" class="px-4 py-4 text-center text-xs text-slate-400 font-mono font-normal" x-text="row.date"></div>
                                            <input x-show="editingId === row.id" type="date" x-model="row.date" class="sheet-input text-center text-xs">
                                        </div>
                                    </template>
                                </td>
                            </template>
                            
                            {{-- Actions --}}
                            <td class="px-4 py-4 text-center print:hidden">
                                <div class="flex items-center justify-center gap-2">
                                    <template x-if="editingId === row.id">
                                        <div class="flex items-center gap-1">
                                            <x-btn type="cancel" @click="cancelEdit(row)" title="{{ __('capital.cancel') }}" />
                                            <x-btn type="save" @click="saveRow(row)" title="{{ __('capital.save') }}" />
                                        </div>
                                    </template>
                                    <template x-if="editingId !== row.id">
                                        <div class="flex items-center gap-2">
                                            <x-btn type="delete" @click="deleteRow(row.id)" title="{{ __('capital.delete') }}" />
                                            <x-btn type="edit" @click="startEdit(row.id)" title="{{ __('capital.edit') }}" />
                                        </div>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>

                    {{-- NO DATA COMPONENT (CLEAN & SMALL) --}}
                    <x-no-data />

                </tbody>
            </table>
        </div>
        <form id="delete-form" action="" method="POST" class="hidden">@csrf @method('DELETE')</form>
        <form id="bulk-delete-form" action="{{ route('capitals.bulk-delete') }}" method="POST" class="hidden">@csrf @method('DELETE')<input type="hidden" name="ids" id="bulk-delete-ids"></form>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('tableManager', () => ({
                // DATA - PRELOADED
                originalRows: @json($capitals->map(function($c) use ($systemBaseId) {
                    $c->amount = (float)$c->amount;
                    $c->share_percentage = (float)$c->share_percentage;
                    $c->balance_usd = (float)$c->balance_usd;
                    
                    // Rate Logic
                    $rate = $c->currency->price_single ?? 1;
                    if($c->currency_id == $systemBaseId) $rate = 1;
                    $c->rate = $rate; 
                    
                    // Creator
                    $c->creator_name = $c->creator->name ?? 'System';
                    
                    return $c;
                })), 
                owners: @json($owners),
                currencies: @json($currencies),
                systemBaseId: {{ $systemBaseId }},
                
                filteredRows: [],
                editingId: null,
                selectedIds: [],
                openFilter: null,
                sortCol: null,
                sortAsc: true,
                filters: {},
                draggingIndex: null,
                totalShare: {{ $totalShareUsed ?? 0 }},
                
                defaultColumns: [
                    { field: 'id', label: '#', visible: true, width: 50 },
                    { field: 'owner', label: '{{ __('capital.owner') }}', visible: true, width: 180 },
                    { field: 'share', label: '{{ __('capital.share_percent') }}', visible: true, width: 120 },
                    { field: 'amount', label: '{{ __('capital.amount') }}', visible: true, width: 140 },
                    { field: 'currency', label: '{{ __('capital.currency') }}', visible: true, width: 100 },
                    { field: 'rate', label: '{{ __('capital.price_usd') }}', visible: true, width: 120 },
                    { field: 'balance', label: '{{ __('capital.balance_usd') }}', visible: true, width: 140 },
                    { field: 'creator', label: '{{ __('capital.created_by') }}', visible: true, width: 120 },
                    { field: 'date', label: '{{ __('capital.date') }}', visible: true, width: 120 },
                ],
                columns: [],

                initData() {
                    this.filteredRows = JSON.parse(JSON.stringify(this.originalRows));
                    const savedCols = localStorage.getItem('capital_columns_v6');
                    this.columns = savedCols ? JSON.parse(savedCols) : JSON.parse(JSON.stringify(this.defaultColumns));
                    this.columns.forEach(col => { this.filters[col.field] = ''; });
                    this.recalculateShares();
                },

                resetLayout() {
                    localStorage.removeItem('capital_columns_v6');
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
                    const startX = e.clientX; const startWidth = parseInt(col.width) || 100;
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

                saveState() { localStorage.setItem('capital_columns_v6', JSON.stringify(this.columns)); },

                // Helpers
                getOwnerName(id) { const o = this.owners.find(x => x.id == id); return o ? o.name : '-'; },
                getCurrencyName(id) { const c = this.currencies.find(c => c.id == id); return c ? c.currency_type : '-'; },
                formatDate(date) { return date ? new Date(date).toISOString().slice(0,10) : '-'; },
                formatNumberDisplay(num) { return num ? parseFloat(num).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00'; },
                formatRate(num) { return num ? parseFloat(num).toFixed(2) : '0.00'; },
                
                getNextId() {
                    const ids = this.originalRows.map(r => parseInt(r.id)).filter(n => !isNaN(n));
                    const max = ids.length ? Math.max(...ids) : 0;
                    return max + 1;
                },

                // LIVE CALCULATION
                calculateRow(row) {
                    const curr = this.currencies.find(c => c.id == row.currency_id);
                    if (!curr) return;

                    let rate = parseFloat(curr.price_single || 1);
                    if (row.currency_id == this.systemBaseId) rate = 1;
                    
                    row.rate = rate; 

                    let rawAmount = String(row.amount).replace(/,/g, '');
                    const amount = parseFloat(rawAmount || 0);
                    
                    if (row.currency_id == this.systemBaseId) {
                        row.balance_usd = amount;
                    } else if (rate > 2.0) {
                        row.balance_usd = amount / rate;
                    } else {
                        row.balance_usd = amount * rate;
                    }
                },

                recalculateShares() {
                    let total = 0;
                    this.filteredRows.forEach(r => { total += parseFloat(r.share_percentage || 0); });
                    this.totalShare = total.toFixed(2);
                },

                // Actions
                get allSelected() { return this.filteredRows.length > 0 && this.selectedIds.length === this.filteredRows.length; },
                toggleAllSelection() { this.selectedIds = this.allSelected ? [] : this.filteredRows.map(r => r.id); },
                
                addNewRow() {
                    const newId = 'new-' + Date.now();
                    const defaultCurrency = this.currencies.length > 0 ? this.currencies[0].id : '';
                    
                    this.filteredRows.unshift({ 
                        id: newId, 
                        owner_id: '', 
                        share_percentage: 0, 
                        amount: '', 
                        currency_id: defaultCurrency, 
                        rate: 1,
                        balance_usd: 0, 
                        creator_name: '{{ Auth::user()->name }}', 
                        date: new Date().toISOString().slice(0,10), 
                        isNew: true 
                    });
                    this.calculateRow(this.filteredRows[0]);
                    this.startEdit(newId);
                },
                startEdit(id) { this.editingId = id; setTimeout(() => { document.getElementById('input-name-'+id)?.focus(); }, 100); },
                cancelEdit(row) {
                    if (String(row.id).startsWith('new-')) { this.filteredRows = this.filteredRows.filter(r => r.id !== row.id); }
                    this.editingId = null;
                    this.recalculateShares();
                },
                saveRow(row) {
                    if (!row.owner_id) { alert('Error: Owner is required.'); return; }
                    if (this.totalShare > 100) { alert('Error: Total Share exceeds 100%.'); return; }

                    const formContainer = document.getElementById('singleRowInputs'); formContainer.innerHTML = '';
                    const createInput = (name, value) => { const i = document.createElement('input'); i.type = 'hidden'; i.name = `capitals[0][${name}]`; i.value = value || ''; formContainer.appendChild(i); };
                    
                    const idToSend = String(row.id).startsWith('new-') ? '' : row.id;
                    createInput('id', idToSend);
                    createInput('owner_id', row.owner_id);
                    createInput('share_percentage', row.share_percentage);
                    
                    let cleanAmount = String(row.amount).replace(/,/g, '');
                    createInput('amount', cleanAmount);
                    
                    createInput('currency_id', row.currency_id);
                    createInput('date', row.date);
                    
                    document.getElementById('singleRowForm').submit();
                },
                
                // Filtering
                filterData() {
                    this.filteredRows = this.originalRows.filter(row => {
                        return this.columns.every(col => {
                            const filterVal = this.filters[col.field]?.toLowerCase() || '';
                            if (!filterVal) return true;
                            let cellVal = String(row[col.field] || '');
                            if (col.field === 'owner') cellVal = this.getOwnerName(row.owner_id);
                            if (col.field === 'currency') cellVal = this.getCurrencyName(row.currency_id);
                            return cellVal.toLowerCase().includes(filterVal);
                        });
                    });
                    this.sortData();
                    this.recalculateShares();
                },
                
                // SORTING FIX
                sortBy(field) {
                    if (this.sortCol === field) this.sortAsc = !this.sortAsc; else { this.sortCol = field; this.sortAsc = true; }
                    this.sortData();
                },
                sortData() {
                    if (!this.sortCol) return;
                    this.filteredRows.sort((a, b) => {
                        let valA = a[this.sortCol]; let valB = b[this.sortCol];
                        
                        // Sort by Owner Name
                        if (this.sortCol === 'owner') { 
                            valA = this.getOwnerName(a.owner_id).toLowerCase(); 
                            valB = this.getOwnerName(b.owner_id).toLowerCase(); 
                        }
                        // Sort by Share (Number)
                        else if (this.sortCol === 'share') { 
                            valA = parseFloat(a.share_percentage); 
                            valB = parseFloat(b.share_percentage); 
                        }
                        // Sort by Amount/Balance
                        else if (this.sortCol === 'amount' || this.sortCol === 'balance') {
                            valA = parseFloat(String(valA).replace(/,/g, ''));
                            valB = parseFloat(String(valB).replace(/,/g, ''));
                        }
                        
                        // Default Sort
                        if (!isNaN(parseFloat(valA)) && isFinite(valA)) {
                            valA = parseFloat(valA); valB = parseFloat(valB);
                        } else {
                            valA = (valA || '').toString().toLowerCase();
                            valB = (valB || '').toString().toLowerCase();
                        }
                        if (valA < valB) return this.sortAsc ? -1 : 1; if (valA > valB) return this.sortAsc ? 1 : -1; return 0;
                    });
                },
                deleteRow(id) {
                    const form = document.getElementById('delete-form'); form.action = "{{ route('capitals.destroy', ':id') }}".replace(':id', id);
                    if (window.confirmAction) { window.confirmAction('delete-form', "{{ __('capital.delete_confirm') }}"); } 
                    else { if(confirm("{{ __('capital.delete_confirm') }}")) form.submit(); }
                },
                bulkDelete() {
                    if (this.selectedIds.length === 0) return;
                    document.getElementById('bulk-delete-ids').value = JSON.stringify(this.selectedIds);
                    if (window.confirmAction) { window.confirmAction('bulk-delete-form', '{{ __('capital.bulk_delete_confirm') }}'); } 
                    else { if(confirm('{{ __('capital.bulk_delete_confirm') }}')) document.getElementById('bulk-delete-form').submit(); }
                }
            }));
        });
    </script>
</x-app-layout>