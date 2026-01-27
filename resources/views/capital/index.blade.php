<x-app-layout>

    {{-- STYLES --}}
    <style>
        .sheet-input { width: 100%; border: 1px solid transparent; padding: 6px; border-radius: 4px; outline: none; transition: all 0.2s; font-size: 0.875rem; color: #1f2937; font-weight: 400; }
        .sheet-input[readonly] { background-color: transparent; color: #64748b; cursor: default; } 
        .sheet-input:not([readonly]):focus { background-color: white; border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2); }
        .sheet-input:not([readonly]) { background-color: white; border-color: #e2e8f0; color: #1e293b; cursor: text; }
        
        select.sheet-input {
            -webkit-appearance: none; appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.2em 1.2em;
            padding-right: 2.5rem; padding-left: 0.75rem; cursor: pointer; text-overflow: ellipsis;
        }
        [dir="rtl"] select.sheet-input { background-position: left 0.5rem center; padding-right: 0.75rem; padding-left: 2.5rem; }
        
        .table-container::-webkit-scrollbar { height: 6px; }
        .table-container::-webkit-scrollbar-track { background: #f1f1f1; }
        .table-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .select-checkbox { width: 1.1rem; height: 1.1rem; border-radius: 4px; border: 1px solid #cbd5e1; color: #6366f1; cursor: pointer; transition: all 0.2s; }
        
        .input-error { border-color: #ef4444 !important; background-color: #fef2f2 !important; color: #b91c1c !important; }

        /* Math Operator Indicator */
        .math-badge { font-size: 0.65rem; padding: 2px 6px; border-radius: 4px; font-weight: 800; display: inline-block; margin-right: 4px; }
        .math-mul { background-color: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; } 
        .math-div { background-color: #eff6ff; color: #3b82f6; border: 1px solid #bfdbfe; } 

        @media print { .no-print, button, a { display: none !important; } }
    </style>

    @php
        $globalTotalBalance = \App\Models\Capital::sum('balance_usd');
        $globalTotalCount = \App\Models\Capital::count();
        $setting = \App\Models\Setting::where('key', 'base_currency_id')->first();
        $systemBaseId = $setting ? $setting->value : 0;
        
        // Get Max ID to continue count visually
        $maxId = \App\Models\Capital::max('id') ?? 0;
    @endphp

    <script>
        const SYSTEM_BASE_ID = "{{ $systemBaseId }}";
        let NEXT_ROW_ID = {{ $maxId + 1 }};
    </script>

    <div x-data="capitalTable()" class="py-6 w-full min-w-0" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

        {{-- SUMMARY CARDS --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8 px-4 no-print">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between relative overflow-hidden group transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-indigo-100 hover:border-indigo-200">
                <div class="absolute ltr:-right-6 rtl:-left-6 -top-6 w-24 h-24 bg-indigo-50 rounded-full transition-transform duration-700 ease-in-out group-hover:scale-[15]"></div>
                <div class="relative z-10 pointer-events-none">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider group-hover:text-indigo-800 transition-colors">{{ __('capital.balance_usd') }}</p>
                    <h4 class="text-2xl font-black text-indigo-700 mt-1 transition-transform group-hover:translate-x-1">${{ number_format($globalTotalBalance, 2) }}</h4>
                </div>
                <div class="relative z-10 p-3 bg-white text-indigo-600 rounded-xl shadow-sm border border-indigo-50 group-hover:border-indigo-200 transition-transform duration-300 group-hover:rotate-12"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between relative overflow-hidden group transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-100 hover:border-emerald-200">
                <div class="absolute ltr:-right-6 rtl:-left-6 -top-6 w-24 h-24 bg-emerald-50 rounded-full transition-transform duration-700 ease-in-out group-hover:scale-[15]"></div>
                <div class="relative z-10 pointer-events-none">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider group-hover:text-emerald-800 transition-colors">{{ __('capital.share_percent') }}</p>
                    <div class="flex items-baseline gap-1 mt-1 transition-transform group-hover:translate-x-1"><h4 class="text-2xl font-black" :class="currentTotalShare > 100 ? 'text-red-500' : 'text-emerald-600'"><span x-text="currentTotalShare"></span>%</h4><span class="text-sm font-bold text-slate-400">/ 100%</span></div>
                </div>
                <div class="relative z-10 p-3 bg-white text-emerald-600 rounded-xl shadow-sm border border-emerald-50 group-hover:border-emerald-200 transition-transform duration-300 group-hover:rotate-12"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg></div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between relative overflow-hidden group transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-blue-100 hover:border-blue-200">
                <div class="absolute ltr:-right-6 rtl:-left-6 -top-6 w-24 h-24 bg-blue-50 rounded-full transition-transform duration-700 ease-in-out group-hover:scale-[15]"></div>
                <div class="relative z-10 pointer-events-none">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider group-hover:text-blue-800 transition-colors">{{ __('capital.owner') }}</p>
                    <h4 class="text-2xl font-black text-slate-700 mt-1 transition-transform group-hover:translate-x-1 group-hover:text-blue-700">{{ number_format($globalTotalCount) }}</h4>
                </div>
                <div class="relative z-10 p-3 bg-white text-blue-600 rounded-xl shadow-sm border border-blue-50 group-hover:border-blue-200 transition-transform duration-300 group-hover:rotate-12"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg></div>
            </div>
        </div>

        {{-- TOOLBAR --}}
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 px-4 no-print">
            <div class="flex items-center gap-3">
                <div>
                    <h3 class="text-xl font-black text-slate-800 tracking-tight">{{ __('capital.title') }}</h3>
                    <p class="text-xs text-slate-500 font-medium mt-1">{{ __('capital.subtitle') }}</p>
                </div>

                {{-- BULK DELETE BUTTON (Shown when items selected) --}}
                <div x-show="selectedIds.length > 0" x-transition class="flex items-center gap-2" style="display: none;">
                    <span class="text-xs font-bold text-red-600 bg-red-50 px-2 py-1 rounded border border-red-100"><span x-text="selectedIds.length"></span> {{ __('capital.selected') }}</span>
                    <button @click="bulkDelete()" class="px-3 py-1 bg-red-600 text-white text-xs font-bold rounded shadow-sm hover:bg-red-700 transition flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        {{ __('capital.delete_selected') }}
                    </button>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('capitals.trash') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-50 text-red-500 border border-red-100 hover:bg-red-100 transition shadow-sm" title="{{ __('capital.trash') }}"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></a>
                
                {{-- Column Dropdown --}}
                <div x-data="{ openDropdown: false }" class="relative"><button @click="openDropdown = !openDropdown" @click.away="openDropdown = false" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition shadow-sm"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg></button><div x-show="openDropdown" class="absolute top-full mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 z-50 p-2 ltr:right-0 rtl:left-0" x-cloak style="display:none;"><div class="flex gap-2 mb-2 pb-2 border-b border-slate-100"><button @click="toggleAll(true)" class="flex-1 text-[10px] bg-indigo-50 text-indigo-600 py-1 rounded font-bold">{{ __('All') }}</button><button @click="toggleAll(false)" class="flex-1 text-[10px] bg-slate-50 text-slate-600 py-1 rounded font-bold">{{ __('None') }}</button></div><div class="space-y-1 max-h-60 overflow-y-auto">@foreach(['id'=>'#', 'owner'=>__('capital.owner'), 'share'=>__('capital.share_percent'), 'amount'=>__('capital.amount'), 'currency'=>__('capital.currency'), 'balance'=>__('capital.balance_usd')] as $key => $label)<label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 rounded cursor-pointer"><input type="checkbox" x-model="cols.{{ $key }}" class="rounded text-indigo-600 w-4 h-4 border-slate-300"><span class="text-xs text-slate-700 font-medium">{{ $label }}</span></label>@endforeach</div></div></div>
                
                <a href="{{ route('capitals.pdf') }}" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-700 text-white hover:bg-slate-800 transition shadow-sm" title="{{ __('capital.report_title') }}"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg></a>
                
                <button type="button" @click="addNewRow()" class="px-4 py-2 bg-blue-600 text-white font-bold text-sm rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-200 transition flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>{{ __('capital.add_new') }}</button>
            </div>
        </div>

        {{-- TABLE FORM --}}
        <div class="relative overflow-x-auto table-container bg-white shadow-sm rounded-lg border border-gray-200 mx-4">
            <form id="sheet-form" action="{{ route('capitals.store') }}" method="POST">
                @csrf
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 whitespace-nowrap">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th x-show="cols.select" class="px-4 py-3 w-[40px] text-center"><input type="checkbox" @click="toggleAllSelection()" class="select-checkbox bg-white"></th>
                            <th x-show="cols.id" class="px-4 py-3 w-[50px] text-center">#</th>
                            <th x-show="cols.owner" class="px-4 py-3 min-w-[180px]">{{ __('capital.owner') }}</th>
                            <th x-show="cols.share" class="px-4 py-3 w-[140px] text-center">{{ __('capital.share_percent') }} <div class="text-[10px] font-black mt-0.5 transition-colors" :class="isOverLimit ? 'text-red-500' : 'text-emerald-600'">(<span x-text="currentTotalShare"></span>% / 100%)</div></th>
                            <th x-show="cols.amount" class="px-4 py-3 min-w-[140px] text-right">{{ __('capital.amount') }}</th>
                            <th x-show="cols.currency" class="px-4 py-3 min-w-[120px] text-center">{{ __('capital.currency') }}</th>
                            <th x-show="cols.rate" class="px-4 py-3 min-w-[120px] text-right">{{ __('capital.price_usd') }}</th>
                            <th x-show="cols.balance" class="px-4 py-3 min-w-[140px] text-right bg-indigo-50 text-indigo-700">{{ __('capital.balance_usd') }}</th>
                            <th x-show="cols.creator" class="px-4 py-3 min-w-[120px] text-center">{{ __('capital.created_by') }}</th>
                            <th x-show="cols.date" class="px-4 py-3 min-w-[120px] text-center">{{ __('capital.date') }}</th>
                            <th x-show="cols.actions" class="px-4 py-3 w-[80px] text-center print:hidden">{{ __('capital.action') }}</th>
                        </tr>
                    </thead>
                    <tbody id="sheet-body" class="divide-y divide-gray-100">
                        @foreach($capitals as $index => $capital)
                        @php 
                            $isBase = $capital->currency_id == $systemBaseId;
                            $rate = $capital->currency->price_single ?? 1;
                            $op = ($rate > 2.0) ? '/' : '*';
                            if($isBase) { $op = '*'; $rate = 1; }
                        @endphp
                        <tr class="bg-white hover:bg-gray-50 transition-colors group/row edit-row-{{ $capital->id }}" :class="editingId === {{ $capital->id }} ? 'bg-indigo-50/20 editing-active' : ''">
                            {{-- SAFE ID INPUT: INSIDE TD TO PREVENT DUPLICATION --}}
                            <td x-show="cols.select" class="px-4 py-4 text-center">
                                <input type="hidden" name="capitals[{{ $index }}][id]" value="{{ $capital->id }}">
                                <input type="checkbox" :value="{{ $capital->id }}" x-model="selectedIds" class="select-checkbox select-checkbox-row">
                            </td>
                            
                            <td x-show="cols.id" class="px-4 py-4 text-center font-normal text-gray-500">{{ $capital->id }}</td>
                            
                            {{-- Owner --}}
                            <td x-show="cols.owner" class="p-1">
                                <span x-show="editingId !== {{ $capital->id }}" class="px-3 block text-gray-700 font-bold truncate">{{ $capital->owner->name ?? 'Unknown' }}</span>
                                <select x-show="editingId === {{ $capital->id }}" name="capitals[{{ $index }}][owner_id]" class="sheet-input font-bold text-gray-700">@foreach($owners as $owner)<option value="{{ $owner->id }}" {{ $capital->owner_id == $owner->id ? 'selected' : '' }}>{{ $owner->name }}</option>@endforeach</select>
                            </td>

                            {{-- Share --}}
                            <td x-show="cols.share" class="p-1 text-center">
                                <span x-show="editingId !== {{ $capital->id }}" class="share-display px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs font-bold border border-blue-100" data-value="{{ $capital->share_percentage }}">{{ $capital->share_percentage }}%</span>
                                <input x-show="editingId === {{ $capital->id }}" type="number" step="0.01" name="capitals[{{ $index }}][share_percentage]" value="{{ $capital->share_percentage }}" @input="recalculateShares()" class="sheet-input text-center font-bold text-blue-700 share-input" required>
                            </td>

                            {{-- Amount --}}
                            <td x-show="cols.amount" class="p-1 text-right">
                                <span x-show="editingId !== {{ $capital->id }}" class="px-3 block text-gray-700 font-medium">{{ number_format($capital->amount, 0) }}</span>
                                <input x-show="editingId === {{ $capital->id }}" type="text" name="capitals[{{ $index }}][amount]" value="{{ number_format($capital->amount, 0) }}" oninput="calculateRow(this)" class="sheet-input text-right font-medium amount-input" id="row-{{ $capital->id }}" required>
                            </td>

                            {{-- Currency --}}
                            <td x-show="cols.currency" class="p-1 text-center">
                                <span x-show="editingId !== {{ $capital->id }}" class="font-bold text-xs text-slate-600 border border-slate-200 px-2 py-1 rounded bg-slate-50">{{ $capital->currency->currency_type ?? '-' }}</span>
                                <select x-show="editingId === {{ $capital->id }}" name="capitals[{{ $index }}][currency_id]" onchange="calculateRow(this)" class="sheet-input text-center font-bold text-slate-600 currency-select">
                                    @foreach($currencies as $curr)
                                        @php
                                            $currRate = $curr->price_single ?? 1;
                                            $currOp = ($currRate > 2.0) ? '/' : '*';
                                            if($curr->id == $systemBaseId) { $currOp = '*'; $currRate = 1; }
                                        @endphp
                                        <option value="{{ $curr->id }}" data-rate="{{ $currRate }}" data-op="{{ $currOp }}" {{ $capital->currency_id == $curr->id ? 'selected' : '' }}>{{ $curr->currency_type }}</option>
                                    @endforeach
                                </select>
                            </td>

                            {{-- Rate --}}
                            <td x-show="cols.rate" class="p-1 text-right text-xs text-slate-500 px-3">
                                <div x-show="editingId !== {{ $capital->id }}">
                                    @if($isBase)
                                        <span class="text-emerald-600 font-bold bg-emerald-50 px-1 rounded">{{ __('Base') }}</span>
                                    @else
                                        @if($op == '*') <span class="math-badge math-mul">x</span> @else <span class="math-badge math-div">÷</span> @endif
                                        1 $ = {{ number_format($rate, 2) }}
                                    @endif
                                </div>
                                <input x-show="editingId === {{ $capital->id }}" type="text" readonly class="sheet-input text-right text-xs text-slate-500 bg-gray-50 rate-input" value="{{ $rate }}">
                            </td>

                            {{-- Balance --}}
                            <td x-show="cols.balance" class="p-1 text-right">
                                <span x-show="editingId !== {{ $capital->id }}" class="px-3 block font-black text-indigo-600">{{ number_format($capital->balance_usd, 2) }} $</span>
                                <input x-show="editingId === {{ $capital->id }}" type="text" readonly class="sheet-input text-right font-black text-indigo-600 bg-transparent balance-input" value="{{ number_format($capital->balance_usd, 2) }}">
                            </td>

                            <td x-show="cols.creator" class="p-1 text-center text-xs text-slate-400">{{ $capital->creator->name ?? 'System' }}</td>
                            <td x-show="cols.date" class="p-1 text-center text-xs text-slate-500"><span x-show="editingId !== {{ $capital->id }}">{{ $capital->date }}</span><input x-show="editingId === {{ $capital->id }}" type="date" name="capitals[{{ $index }}][date]" value="{{ $capital->date }}" class="sheet-input text-center text-xs"></td>
                            
                            <td x-show="cols.actions" class="px-4 py-4 text-center print:hidden">
                                <div x-show="editingId !== {{ $capital->id }}" class="flex items-center justify-center gap-2">
                                    <button type="button" @click="startEdit({{ $capital->id }})" class="text-slate-400 hover:text-blue-600 transition hover:scale-110"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></button>
                                    <button type="button" onclick="deleteDatabaseRow({{ $capital->id }})" class="text-slate-400 hover:text-red-600 transition hover:scale-110"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                </div>
                                <div x-show="editingId === {{ $capital->id }}" class="flex items-center justify-center gap-2">
                                    <button type="button" @click="saveForm()" class="text-emerald-500 transition hover:scale-110"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></button>
                                    <button type="button" @click="cancelEdit()" class="text-red-400 hover:text-red-600 transition hover:scale-110"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </form>
        </div>
        <div class="px-6 py-4 no-print">{{ $capitals->links() }}</div>
    </div>

    {{-- HIDDEN FORMS FOR DELETION --}}
    <form id="delete-form" action="" method="POST" class="hidden">@csrf @method('DELETE')</form>
    <form id="bulk-delete-form" action="{{ route('capitals.bulk-delete') }}" method="POST" class="hidden">@csrf @method('DELETE') <input type="hidden" name="ids" id="bulk-delete-ids"></form>

    {{-- SCRIPTS --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('capitalTable', () => ({
                editingId: null,
                selectedIds: [],
                dbTotalShare: {{ $totalShareUsed ?? 0 }},
                currentTotalShare: {{ $totalShareUsed ?? 0 }},
                offPageShare: 0, 
                isOverLimit: false,
                isSubmitting: false, // Prevent double click
                cols: { select: true, id: true, owner: true, share: true, amount: true, currency: true, rate: true, balance: true, creator: true, date: true, actions: true },

                init() {
                    let visibleSum = 0;
                    document.querySelectorAll('.share-display').forEach(el => {
                        visibleSum += parseFloat(el.dataset.value) || 0;
                    });
                    this.offPageShare = parseFloat((this.dbTotalShare - visibleSum).toFixed(2));
                    if(this.offPageShare < 0) this.offPageShare = 0; 
                },

                toggleAll(value) { for (let key in this.cols) { this.cols[key] = value; } },
                toggleAllSelection() {
                    let checkboxes = document.querySelectorAll('.select-checkbox-row');
                    let allChecked = this.selectedIds.length === checkboxes.length && checkboxes.length > 0;
                    this.selectedIds = [];
                    if (!allChecked) { checkboxes.forEach(cb => this.selectedIds.push(parseInt(cb.value))); }
                },
                
                // BULK DELETE WITH CONFIRMATION
                bulkDelete() {
                    if (this.selectedIds.length === 0) return;
                    
                    const form = document.getElementById('bulk-delete-form');
                    document.getElementById('bulk-delete-ids').value = JSON.stringify(this.selectedIds);
                    const message = "{{ __('capital.warning_perm_delete') }}";

                    // Use Global Helper if available, else standard confirm
                    if (window.confirmAction) {
                        window.confirmAction('bulk-delete-form', message);
                    } else {
                        if(confirm(message)) { form.submit(); }
                    }
                },

                startEdit(id) { 
                    this.editingId = id; 
                    setTimeout(() => { const input = document.querySelector(`.edit-row-${id} .amount-input`); if(input) input.focus(); }, 50);
                },
                cancelEdit() { this.editingId = null; window.location.reload(); },
                
                saveForm() {
                    if (this.isSubmitting) return;

                    const form = document.getElementById('sheet-form');
                    
                    if (this.isOverLimit) {
                        alert("{{ __('capital.total_share_error') ?? 'Error: Total shares cannot exceed 100%' }}");
                        return;
                    }
                    
                    if (!form.reportValidity()) {
                        return;
                    }

                    this.isSubmitting = true;

                    document.querySelectorAll('.amount-input').forEach(el => { 
                        el.value = el.value.replace(/,/g, ''); 
                    });
                    
                    form.submit();
                },

                recalculateShares() {
                    let newSum = this.offPageShare; 
                    document.querySelectorAll('#sheet-body tr').forEach(row => {
                        let valueToAdd = 0;
                        if (row.classList.contains('editing-active')) {
                            const input = row.querySelector('.share-input');
                            if (input) valueToAdd = parseFloat(input.value) || 0;
                        } 
                        else {
                            const span = row.querySelector('.share-display');
                            if (span) valueToAdd = parseFloat(span.dataset.value) || 0;
                        }
                        newSum += valueToAdd;
                    });

                    this.currentTotalShare = newSum.toFixed(2);
                    this.isOverLimit = this.currentTotalShare > 100;
                },

                addNewRow() {
                    if (parseFloat(this.currentTotalShare) >= 100) { alert("{{ __('capital.limit_reached') ?? 'Limit Reached' }}"); return; }
                    const tableBody = document.getElementById('sheet-body');
                    const index = Date.now();
                    const rowId = `row-${index}`;
                    const today = new Date().toISOString().split('T')[0];
                    let displayId = NEXT_ROW_ID; 
                    NEXT_ROW_ID++; 

                    let ownerOptions = `<option value="" disabled selected>{{ __('capital.owner') }}</option>`;
                    @foreach($owners as $owner) ownerOptions += `<option value="{{ $owner->id }}">{{ $owner->name }}</option>`; @endforeach
                    
                    let currencyOptions = `<option value="" disabled selected>{{ __('capital.currency') }}</option>`;
                    @foreach($currencies as $curr) 
                        var rate = {{ $curr->price_single ?? 0 }};
                        var op = (rate > 2.0) ? '/' : '*';
                        if ({{ $curr->id }} == SYSTEM_BASE_ID) { rate = 1; op = '*'; }
                        currencyOptions += `<option value="{{ $curr->id }}" data-rate="${rate}" data-op="${op}">{{ $curr->currency_type }}</option>`; 
                    @endforeach

                    const rowHtml = `
                    <tr id="${rowId}" class="bg-blue-50/40 border-b border-blue-100 transition-all duration-300 editing-active">
                        <td x-show="cols.select" class="px-4 py-4 text-center"></td>
                        <td x-show="cols.id" class="px-4 py-4 text-center font-normal text-indigo-600 font-bold">${displayId}</td>
                        <td x-show="cols.owner" class="p-1"><select name="capitals[${index}][owner_id]" class="sheet-input font-bold text-gray-700" required>${ownerOptions}</select></td>
                        <td x-show="cols.share" class="p-1"><input type="number" step="0.01" name="capitals[${index}][share_percentage]" oninput="this.dispatchEvent(new Event('input', {bubbles: true}))" @input="recalculateShares()" class="sheet-input text-center font-bold text-blue-700 share-input" placeholder="0" required></td>
                        <td x-show="cols.amount" class="p-1"><input type="text" name="capitals[${index}][amount]" oninput="calculateRow(this)" class="sheet-input text-right font-medium amount-input" placeholder="0" required></td>
                        <td x-show="cols.currency" class="p-1"><select name="capitals[${index}][currency_id]" onchange="calculateRow(this)" class="sheet-input text-center font-bold text-slate-600 currency-select" required>${currencyOptions}</select></td>
                        <td x-show="cols.rate" class="p-1"><input type="text" readonly class="sheet-input text-right text-xs text-slate-500 bg-gray-50 rate-input" value="0"></td>
                        <td x-show="cols.balance" class="p-1"><input type="text" readonly class="sheet-input text-right font-black text-indigo-600 bg-transparent balance-input" value="0.00"></td>
                        <td x-show="cols.creator" class="p-1 text-center text-xs text-slate-400">{{ Auth::user()->name }}</td>
                        <td x-show="cols.date" class="p-1"><input type="date" name="capitals[${index}][date]" value="${today}" class="sheet-input text-center text-xs"></td>
                        
                        <td x-show="cols.actions" class="px-4 py-4 text-center print:hidden">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" @click="saveForm()" class="text-emerald-500 transition hover:scale-110" title="Save All"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></button>
                                <button type="button" onclick="this.closest('tr').remove(); setTimeout(() => recalculateShares(), 100)" class="text-slate-400 hover:text-red-500 transition hover:scale-110" title="Cancel Row"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                            </div>
                        </td>
                    </tr>`;
                    
                    tableBody.insertAdjacentHTML('beforeend', rowHtml);
                    setTimeout(() => { const newRow = document.getElementById(rowId); if(newRow) { newRow.scrollIntoView({ behavior: 'smooth', block: 'center' }); newRow.querySelector('select[name*="owner_id"]').focus(); } }, 100);
                }
            }));
        });

        // GLOBAL SAFE MATH LOGIC
        function calculateRow(element) {
            let row = element.closest('tr');
            let amountInput = row.querySelector('.amount-input');
            let currencySelect = row.querySelector('.currency-select');
            let rateInput = row.querySelector('.rate-input');
            let balanceInput = row.querySelector('.balance-input');

            // 1. Amount
            let rawValue = amountInput.value.replace(/,/g, '');
            if (!isNaN(rawValue) && rawValue !== '') {
                if (!amountInput.value.includes('.')) {
                    amountInput.value = parseFloat(rawValue).toLocaleString('en-US');
                }
            }
            let amount = parseFloat(rawValue) || 0;

            // 2. Data
            let selectedOption = currencySelect.options[currencySelect.selectedIndex];
            let rate = selectedOption ? parseFloat(selectedOption.dataset.rate) : 0;
            let op = selectedOption ? selectedOption.dataset.op : '/';

            // 3. System Base Check
            if (SYSTEM_BASE_ID && currencySelect.value == SYSTEM_BASE_ID) {
                rate = 1;
                op = '*';
                if (rateInput) rateInput.value = "1.00";
            } else {
                if (rateInput) rateInput.value = rate > 0 ? rate : 0;
            }

            // 4. Calculate
            if (rate > 0 && amount > 0) {
                let result = 0;
                if (op === '*') {
                    result = amount * rate;
                } else {
                    result = amount / rate;
                }
                balanceInput.value = result.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            } else {
                balanceInput.value = '0.00';
            }
        }

        // SINGLE DELETE FUNCTION
        function deleteDatabaseRow(id) {
            const form = document.getElementById('delete-form');
            form.action = "{{ route('capitals.destroy', ':id') }}".replace(':id', id);
            
            const message = "{{ __('capital.warning_perm_delete') }}";

            if (window.confirmAction) { 
                window.confirmAction('delete-form', message); 
            } else { 
                if(confirm(message)) { form.submit(); } 
            }
        }
    </script>
</x-app-layout>