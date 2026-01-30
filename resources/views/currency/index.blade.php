<x-app-layout>
    {{-- STYLES --}}
    <style>
        /* Shared Input Styles */
        .sheet-input { width: 100%; height: 100%; display: flex; align-items: center; background: transparent; border: 1px solid transparent; padding: 0 12px; font-size: 0.875rem; color: #1f2937; font-weight: 400; border-radius: 6px; transition: all 0.15s ease-in-out; }
        .sheet-input:focus { background-color: #fff; border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1); outline: none; }
        .sheet-input[readonly] { cursor: default; color: #64748b; background-color: transparent; }
        
        select.sheet-input {
            -webkit-appearance: none; appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.2em 1.2em;
            padding-right: 2.5rem; padding-left: 0.75rem; cursor: pointer; white-space: nowrap; 
        }
        [dir="rtl"] select.sheet-input { background-position: left 0.5rem center; padding-right: 0.75rem; padding-left: 2.5rem; }
        
        /* Checkbox & Scrollbar */
        .select-checkbox { width: 1.1rem; height: 1.1rem; border-radius: 4px; border: 1px solid #cbd5e1; color: #6366f1; cursor: pointer; transition: all 0.2s; }
        .select-checkbox:focus { ring: 2px; ring-color: #e0e7ff; }
        .table-container::-webkit-scrollbar { height: 6px; }
        .table-container::-webkit-scrollbar-track { background: #f1f1f1; }
        .table-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

        /* --- STANDARD ANIMATION (Copy this to any page to reuse) --- */
        @keyframes slideIn { 
            from { opacity: 0; transform: translateY(-10px); } 
            to { opacity: 1; transform: translateY(0); } 
        }
        .new-row { 
            animation: slideIn 0.3s ease-out forwards; 
            background-color: #f0fdf4 !important; /* Flash Green */
        }

        @media print { .no-print, button, .print\:hidden { display: none !important; } .overflow-x-auto { overflow: visible !important; } table { width: 100% !important; } }
    </style>

    <div x-data="{ 
        hasChanges: false,
        editingId: null,
        selectedIds: [],
        allIds: {{ json_encode($currencies->pluck('id')) }},
        cols: { select: true, id: true, type: true, symbol: true, digit: true, total: true, single: true, branch: true, active: true, actions: true },
        
        toggleAll(value) { for (let key in this.cols) { this.cols[key] = value; } },
        
        toggleSelection(id) {
            if (this.selectedIds.includes(id)) { this.selectedIds = this.selectedIds.filter(i => i !== id); } else { this.selectedIds.push(id); }
        },
        toggleAllSelection() {
            if (this.selectedIds.length === this.allIds.length) { this.selectedIds = []; } else { this.selectedIds = [...this.allIds]; }
        },
        
        bulkDelete() {
            if (this.selectedIds.length === 0) return;
            document.getElementById('bulk-delete-ids').value = JSON.stringify(this.selectedIds);
            
            if (window.confirmAction) {
                window.confirmAction('bulk-delete-form', '{{ __('currency.bulk_delete_confirm') }}');
            } else {
                if(confirm('{{ __('currency.bulk_delete_confirm') }}')) document.getElementById('bulk-delete-form').submit();
            }
        },

        startEdit(id) { 
            this.editingId = id; 
            setTimeout(() => { document.getElementById('input-type-'+id)?.focus(); }, 100);
        },
        saveRow() { cleanAndSubmit(); }
    }" class="py-6 w-full min-w-0" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

        {{-- TOOLBAR --}}
        <div class="mx-4 mb-6 flex flex-col md:flex-row justify-between items-center gap-4 no-print">
            
            {{-- Navigation Tabs --}}
            <div class="bg-slate-100 p-1 rounded-lg flex items-center shadow-inner">
                <span class="px-5 py-2 text-sm font-bold rounded-md bg-white text-indigo-600 shadow-sm transition">{{ __('currency.config_title') }}</span>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                
                {{-- BULK ACTIONS BAR --}}
                <div x-show="selectedIds.length > 0" x-transition class="flex items-center gap-2 bg-red-50 px-2 py-1 rounded-lg border border-red-100 mr-2 ml-2">
                    <span class="text-xs font-bold text-red-600 px-2"><span x-text="selectedIds.length"></span> {{ __('currency.selected') }}</span>
                    <button @click="bulkDelete()" type="button" class="px-3 py-1.5 bg-red-600 text-white text-xs font-bold rounded shadow-sm hover:bg-red-700 transition">
                        {{ __('currency.delete_selected') }}
                    </button>
                    <button @click="selectedIds = []" type="button" class="px-2 py-1.5 text-slate-500 hover:text-slate-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Trash --}}
                <a href="{{ route('currency.trash') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-50 text-red-500 border border-red-100 hover:bg-red-100 transition shadow-sm" title="{{ __('currency.trash') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </a>
                
                {{-- Columns --}}
                <div x-data="{ openDropdown: false }" class="relative">
                    <button @click="openDropdown = !openDropdown" @click.away="openDropdown = false" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                    </button>
                    <div x-show="openDropdown" class="absolute top-full mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 z-50 p-2 ltr:right-0 rtl:left-0" x-cloak style="display:none;">
                        <div class="flex gap-2 mb-2 pb-2 border-b border-slate-100">
                            <button @click="toggleAll(true)" class="flex-1 text-[10px] bg-indigo-50 text-indigo-600 py-1 rounded font-bold">{{ __('currency.all') }}</button>
                            <button @click="toggleAll(false)" class="flex-1 text-[10px] bg-slate-50 text-slate-600 py-1 rounded font-bold">{{ __('currency.none') }}</button>
                        </div>
                        <div class="space-y-1 max-h-60 overflow-y-auto">
                            @foreach(['select'=>'Select', 'id'=>'#', 'type'=>__('currency.type'), 'symbol'=>__('currency.symbol'), 'digit'=>__('currency.digit'), 'total'=>__('currency.price_total'), 'single'=>__('currency.price_single'), 'branch'=>__('currency.branch'), 'active'=>__('currency.active')] as $key => $label)
                                <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 rounded cursor-pointer"><input type="checkbox" x-model="cols.{{ $key }}" class="rounded text-indigo-600 w-4 h-4 border-slate-300"><span class="text-xs text-slate-700 font-medium">{{ $label }}</span></label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Print --}}
                <a href="{{ route('currency.print') }}" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-700 text-white hover:bg-slate-800 transition shadow-sm" title="{{ __('currency.print') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                </a>
                
                {{-- Add New --}}
                <button type="button" @click="addNewRow()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow-lg transition" title="{{ __('currency.add_new') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
            </div>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="relative w-full overflow-x-auto table-container bg-white shadow-sm rounded-lg border border-slate-200 mx-4">
            <form id="sheet-form" action="{{ route('currency.store') }}" method="POST">
                @csrf
                <table class="w-full text-sm text-left rtl:text-right text-slate-500 whitespace-nowrap">
                    <thead class="text-xs text-slate-700 uppercase bg-blue-50/50 border-b border-blue-100">
                        <tr>
                            <th x-show="cols.select" class="px-4 py-3 w-[40px] text-center">
                                <input type="checkbox" @click="toggleAllSelection()" :checked="selectedIds.length > 0 && selectedIds.length === allIds.length" class="select-checkbox bg-white">
                            </th>
                            <th x-show="cols.id" class="px-4 py-3 w-[5%] text-center">#</th>
                            <th x-show="cols.type" class="px-4 py-3 w-[15%]">{{ __('currency.type') }}</th>
                            <th x-show="cols.symbol" class="px-4 py-3 w-[10%] text-center">{{ __('currency.symbol') }}</th>
                            <th x-show="cols.digit" class="px-4 py-3 w-[10%] text-center">{{ __('currency.digit') }}</th>
                            <th x-show="cols.total" class="px-4 py-3 w-[15%] text-center">{{ __('currency.price_total') }}</th>
                            <th x-show="cols.single" class="px-4 py-3 w-[15%] text-center">{{ __('currency.price_single') }}</th>
                            <th x-show="cols.branch" class="px-4 py-3 min-w-[180px]">{{ __('currency.branch') }}</th>
                            <th x-show="cols.active" class="px-4 py-3 w-[10%] text-center">{{ __('currency.active') }}</th>
                            <th x-show="cols.actions" class="px-4 py-3 w-[5%] text-center print:hidden">{{ __('currency.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="sheet-body" class="divide-y divide-slate-100">
                        @foreach($currencies as $index => $currency)
                        <tr class="bg-white hover:bg-slate-50 transition-colors group/row" 
                            :class="[editingId === {{ $currency->id }} ? 'bg-indigo-50/20' : '', selectedIds.includes({{ $currency->id }}) ? 'bg-indigo-50/10' : '']">
                            
                            <td x-show="cols.select" class="px-4 py-4 text-center">
                                <input type="checkbox" :value="{{ $currency->id }}" x-model="selectedIds" class="select-checkbox">
                            </td>

                            <td x-show="cols.id" class="px-4 py-4 text-center font-normal text-slate-400">
                                {{ $loop->iteration }} <input type="hidden" name="currencies[{{ $index }}][id]" value="{{ $currency->id }}">
                            </td>
                            
                            <td x-show="cols.type" class="p-1">
                                <span x-show="editingId !== {{ $currency->id }}" class="px-3 block text-slate-700 font-normal uppercase">{{ $currency->currency_type }}</span>
                                <input x-show="editingId === {{ $currency->id }}" type="text" id="input-type-{{ $currency->id }}" name="currencies[{{ $index }}][currency_type]" value="{{ $currency->currency_type }}" class="sheet-input font-normal uppercase">
                            </td>

                            <td x-show="cols.symbol" class="p-1">
                                <span x-show="editingId !== {{ $currency->id }}" class="block text-center text-slate-600 font-normal">{{ $currency->symbol }}</span>
                                <input x-show="editingId === {{ $currency->id }}" type="text" name="currencies[{{ $index }}][symbol]" value="{{ $currency->symbol }}" class="sheet-input text-center font-normal">
                            </td>

                            <td x-show="cols.digit" class="p-1">
                                <span x-show="editingId !== {{ $currency->id }}" class="block text-center text-slate-600 font-normal">{{ $currency->digit_number }}</span>
                                <input x-show="editingId === {{ $currency->id }}" type="number" name="currencies[{{ $index }}][digit_number]" value="{{ $currency->digit_number }}" class="sheet-input text-center font-normal">
                            </td>

                            <td x-show="cols.total" class="p-1">
                                <div x-show="editingId !== {{ $currency->id }}" class="text-center font-normal text-emerald-600">{{ number_format($currency->price_total, 0) }}</div>
                                <input x-show="editingId === {{ $currency->id }}" type="text" name="currencies[{{ $index }}][price_total]" value="{{ number_format($currency->price_total, 0) }}" oninput="handlePriceInput(this)" class="sheet-input text-center font-normal text-emerald-600 price-input">
                            </td>

                            <td x-show="cols.single" class="p-1">
                                <div x-show="editingId !== {{ $currency->id }}" class="text-center font-normal text-blue-600">{{ number_format($currency->price_single, 0) }}</div>
                                <input x-show="editingId === {{ $currency->id }}" type="text" name="currencies[{{ $index }}][price_single]" value="{{ number_format($currency->price_single, 0) }}" readonly class="sheet-input text-center font-normal text-blue-600 bg-slate-50 price-single">
                            </td>

                            <td x-show="cols.branch" class="p-1">
                                <span x-show="editingId !== {{ $currency->id }}" class="px-3 block text-xs uppercase text-slate-500 font-normal truncate">
                                    {{ optional($currency->branch)->name ?? '-' }}
                                </span>
                                <select x-show="editingId === {{ $currency->id }}" name="currencies[{{ $index }}][branch_id]" class="sheet-input font-normal text-slate-700">
                                    <option value="" disabled>{{ __('Select Branch') }}</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ (optional($currency->branch)->id == $branch->id) ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            
                            <td x-show="cols.active" class="px-4 py-4 text-center">
                                <div class="flex items-center justify-center">
                                    <input type="checkbox" 
                                           name="currencies[{{ $index }}][is_active]" 
                                           value="1" 
                                           {{ $currency->is_active ? 'checked' : '' }} 
                                           :class="{ 'pointer-events-none opacity-50': editingId !== {{ $currency->id }} }"
                                           class="w-4 h-4 text-indigo-600 rounded border-slate-300 cursor-pointer">
                                </div>
                            </td>
                            
                            <td x-show="cols.actions" class="px-4 py-4 text-center print:hidden">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" @click="saveRow()" x-show="editingId === {{ $currency->id }}" class="text-emerald-500 transition active:scale-95"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></button>
                                    <button type="button" @click="startEdit({{ $currency->id }})" x-show="editingId !== {{ $currency->id }}" class="text-slate-400 hover:text-blue-600 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                    <button type="button" onclick="deleteDatabaseRow({{ $currency->id }})" class="text-slate-400 hover:text-red-600 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    {{-- BULK DELETE FORM --}}
    <form id="bulk-delete-form" action="{{ route('currency.bulk-delete') }}" method="POST" class="hidden">@csrf @method('DELETE')<input type="hidden" name="ids" id="bulk-delete-ids"></form>
    <form id="delete-form" action="" method="POST" class="hidden">@csrf @method('DELETE')</form>

    <script>
        @php $userBranchId = auth()->user()->branch_id ?? ''; @endphp

        function formatNumber(num) {
            if (!num) return '';
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        function parseNumber(str) {
            if (!str) return 0;
            return parseFloat(str.replace(/,/g, '')) || 0;
        }

        function handlePriceInput(input) {
            let rawValue = parseNumber(input.value);
            let singleValue = rawValue / 100;
            let row = input.closest('tr');
            let singleInput = row.querySelector('.price-single');

            if (!isNaN(rawValue)) {
                input.value = formatNumber(rawValue); 
                singleInput.value = formatNumber(singleValue);
            } else {
                singleInput.value = 0;
            }
        }

        function cleanAndSubmit() {
            document.querySelectorAll('.price-input, .price-single').forEach(el => { el.value = parseNumber(el.value); });
            document.getElementById('sheet-form').submit();
        }

        function addNewRow() {
            const tableBody = document.getElementById('sheet-body');
            const index = Date.now(); 
            const rowCount = tableBody.querySelectorAll('tr').length;
            const nextId = rowCount + 1;
            const placeholderType = "{{ __('currency.type') }}";
            const placeholderSymbol = "{{ __('currency.symbol') }}";
            const userBranchId = @json($userBranchId);

            let branchOptions = '<option value="" disabled>{{ __('Select Branch') }}</option>';
            @foreach($branches as $branch)
                var isSelected = (String("{{ $branch->id }}") === String(userBranchId)) ? 'selected' : '';
                branchOptions += `<option value="{{ $branch->id }}" ${isSelected}>{{ $branch->name }}</option>`;
            @endforeach

            const rowHtml = `
            <tr class="new-row hover:bg-slate-50 transition-colors">
                <td x-show="cols.select" class="px-4 py-4 text-center"></td>
                <td x-show="cols.id" class="px-4 py-4 text-center font-normal text-indigo-600">${nextId}</td>
                <td x-show="cols.type" class="p-1"><input type="text" name="currencies[${index}][currency_type]" class="sheet-input font-bold uppercase" placeholder="${placeholderType}" autofocus></td>
                <td x-show="cols.symbol" class="p-1"><input type="text" name="currencies[${index}][symbol]" class="sheet-input text-center font-normal" placeholder="${placeholderSymbol}"></td>
                <td x-show="cols.digit" class="p-1"><input type="number" name="currencies[${index}][digit_number]" value="0" class="sheet-input text-center font-normal"></td>
                <td x-show="cols.total" class="p-1"><input type="text" name="currencies[${index}][price_total]" value="0" oninput="handlePriceInput(this)" class="sheet-input text-center font-normal text-emerald-600 price-input"></td>
                <td x-show="cols.single" class="p-1"><input type="text" name="currencies[${index}][price_single]" value="0" readonly class="sheet-input text-center font-normal text-blue-600 bg-slate-50 price-single"></td>
                <td x-show="cols.branch" class="p-1"><select name="currencies[${index}][branch_id]" class="sheet-input font-normal text-slate-700">${branchOptions}</select></td>
                
                <td x-show="cols.active" class="px-4 py-4 text-center"><div class="flex items-center justify-center"><input type="checkbox" name="currencies[${index}][is_active]" value="1" checked class="w-4 h-4 text-indigo-600 rounded border-slate-300 cursor-pointer"></div></td>
                
                <td x-show="cols.actions" class="px-4 py-4 text-center print:hidden">
                    <div class="flex items-center justify-center gap-2">
                        <button type="button" onclick="cleanAndSubmit()" class="text-emerald-500 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></button>
                        <button type="button" onclick="this.closest('tr').remove()" class="text-slate-400 hover:text-red-500 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                </td>
            </tr>`;
            
            tableBody.insertAdjacentHTML('beforeend', rowHtml);
            setTimeout(() => { const newRows = tableBody.querySelectorAll('tr'); newRows[newRows.length - 1].scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 100);
        }

        function deleteDatabaseRow(id) {
            const form = document.getElementById('delete-form');
            form.action = "{{ route('currency.destroy', ':id') }}".replace(':id', id);
            
            if (window.confirmAction) {
                window.confirmAction('delete-form', "{{ __('currency.delete_confirm') }}");
            } else {
                if(confirm("{{ __('currency.delete_confirm') }}")) { form.submit(); }
            }
        }
    </script>
</x-app-layout>