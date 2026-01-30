<x-app-layout>
    {{-- STYLES --}}
    <style>
        /* Input Styles */
        .sheet-input { width: 100%; height: 100%; display: flex; align-items: center; background: transparent; border: 1px solid transparent; padding: 0 8px; font-size: 0.875rem; color: #1f2937; font-weight: 400; border-radius: 6px; transition: all 0.15s ease-in-out; }
        .sheet-input:focus { background-color: #fff; border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1); outline: none; }
        .sheet-input[readonly] { cursor: default; color: #64748b; background-color: transparent; }
        
        /* Select Dropdown */
        select.sheet-input {
            -webkit-appearance: none; appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.2rem center; background-repeat: no-repeat; background-size: 1.2em 1.2em;
            padding-right: 1.5rem; padding-left: 0.5rem; cursor: pointer; white-space: nowrap; 
            text-overflow: ellipsis; overflow: hidden;
        }
        [dir="rtl"] select.sheet-input { background-position: left 0.2rem center; padding-right: 0.5rem; padding-left: 1.5rem; }
        
        /* Custom Scrollbar */
        .table-container::-webkit-scrollbar { height: 6px; }
        .table-container::-webkit-scrollbar-track { background: #f1f1f1; }
        .table-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        
        .select-checkbox { width: 1.1rem; height: 1.1rem; border-radius: 4px; border: 1px solid #cbd5e1; color: #6366f1; cursor: pointer; transition: all 0.2s; }
        
        /* Row Animation */
        @keyframes slideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .new-row { animation: slideIn 0.3s ease-out forwards; background-color: #f0fdf4 !important; }

        @media print { .no-print, button, .print\:hidden { display: none !important; } .overflow-x-auto { overflow: visible !important; } table { width: 100% !important; } }
    </style>

    <div x-data="{ 
        hasChanges: false,
        editingId: null,
        selectedIds: [],
        allIds: {{ json_encode($cashBoxes->pluck('id')) }},
        cols: { select: true, id: true, name: true, type: true, currency: true, balance: true, branch: true, desc: true, user: true, created_at: true, active: true, actions: true },
        
        toggleAll(value) { for (let key in this.cols) { this.cols[key] = value; } },
        toggleSelection(id) { if (this.selectedIds.includes(id)) { this.selectedIds = this.selectedIds.filter(i => i !== id); } else { this.selectedIds.push(id); } },
        toggleAllSelection() { if (this.selectedIds.length === this.allIds.length) { this.selectedIds = []; } else { this.selectedIds = [...this.allIds]; } },
        
        bulkDelete() {
            if (this.selectedIds.length === 0) return;
            document.getElementById('bulk-delete-ids').value = JSON.stringify(this.selectedIds);
            if (window.confirmAction) { window.confirmAction('bulk-delete-form', '{{ __('cash_box.bulk_delete_confirm') }}'); } 
            else { if(confirm('{{ __('cash_box.bulk_delete_confirm') }}')) document.getElementById('bulk-delete-form').submit(); }
        },

        startEdit(id) { 
            this.editingId = id; 
            setTimeout(() => { document.getElementById('input-name-'+id)?.focus(); }, 100);
        },
        saveForm() { 
            document.querySelectorAll('.number-format').forEach(el => { el.value = el.value.replace(/,/g, ''); });
            document.getElementById('sheet-form').submit(); 
        }
    }" class="py-6 w-full min-w-0" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

        {{-- TOOLBAR --}}
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 px-4 no-print">
            <h3 class="text-xl font-black text-slate-800 tracking-tight">{{ __('cash_box.title') }}</h3>
            
            <div class="flex flex-wrap items-center gap-2">
                
                {{-- BULK ACTIONS --}}
                <div x-show="selectedIds.length > 0" x-transition class="flex items-center gap-2 bg-red-50 px-2 py-1 rounded-lg border border-red-100 mr-2 ml-2">
                    <span class="text-xs font-bold text-red-600 px-2"><span x-text="selectedIds.length"></span> {{ __('cash_box.selected') }}</span>
                    <button @click="bulkDelete()" type="button" class="px-3 py-1.5 bg-red-600 text-white text-xs font-bold rounded shadow-sm hover:bg-red-700 transition">{{ __('cash_box.delete_selected') }}</button>
                    <button @click="selectedIds = []" type="button" class="px-2 py-1.5 text-slate-500 hover:text-slate-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>

                {{-- Trash --}}
                <a href="{{ route('cash-boxes.trash') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-50 text-red-500 border border-red-100 hover:bg-red-100 transition shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </a>

                {{-- Columns --}}
                <div x-data="{ openDropdown: false }" class="relative">
                    <button @click="openDropdown = !openDropdown" @click.away="openDropdown = false" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                    </button>
                    <div x-show="openDropdown" class="absolute ltr:right-0 rtl:left-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 z-50 p-2" x-cloak style="display:none;">
                        <div class="flex gap-2 mb-2 pb-2 border-b border-slate-100">
                            <button @click="toggleAll(true)" class="flex-1 text-[10px] bg-indigo-50 text-indigo-600 py-1 rounded font-bold">ALL</button>
                            <button @click="toggleAll(false)" class="flex-1 text-[10px] bg-slate-50 text-slate-600 py-1 rounded font-bold">NONE</button>
                        </div>
                        <div class="space-y-1 max-h-60 overflow-y-auto">
                            @foreach(['id'=>'#', 'name'=>'Name', 'type'=>'Type', 'currency'=>'Currency', 'balance'=>'Balance', 'branch'=>'Branch', 'desc'=>'Description', 'user'=>'User', 'created_at'=>'Date Created', 'active'=>'Active'] as $key => $label)
                                <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 rounded cursor-pointer">
                                    <input type="checkbox" x-model="cols.{{ $key }}" class="rounded text-indigo-600 w-4 h-4 border-slate-300">
                                    <span class="text-xs text-slate-700 font-medium">{{ __('cash_box.'.$key) ?? $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Print --}}
                <a href="{{ route('cash-boxes.print') }}" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-700 text-white hover:bg-slate-800 transition shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                </a>

                {{-- Add New --}}
                <button type="button" @click="addNewRow(); hasChanges = true" class="w-10 h-10 flex items-center justify-center rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
            </div>
        </div>

        {{-- TABLE FORM --}}
        <form id="sheet-form" action="{{ route('cash-boxes.store-bulk') }}" method="POST">
            @csrf
            
            {{-- STANDARD COMPONENT WRAPPER --}}
            <x-app-table>
                <x-table-header>
                    {{-- 1. Checkbox: Tight fit --}}
                    <th x-show="cols.select" class="px-4 py-3 text-center w-px">
                        <input type="checkbox" @click="toggleAllSelection()" :checked="selectedIds.length > 0 && selectedIds.length === allIds.length" class="select-checkbox bg-white">
                    </th>

                    {{-- 2. ID: Tight fit, no wrap --}}
                    <th x-show="cols.id" class="px-4 py-3 text-center w-px whitespace-nowrap">#</th>

                    {{-- 3. Name: Wide, allow breathing room --}}
                    <th x-show="cols.name" class="px-4 py-3 text-left min-w-[200px]">
                        {{ __('cash_box.name') }}
                    </th>

                    {{-- 4. Type: Short text, no wrap --}}
                    <th x-show="cols.type" class="px-4 py-3 text-left whitespace-nowrap">
                        {{ __('cash_box.type') }}
                    </th>

                    {{-- 5. Currency: Short text, center --}}
                    <th x-show="cols.currency" class="px-4 py-3 text-center whitespace-nowrap">
                        {{ __('cash_box.currency') }}
                    </th>

                    {{-- 6. Balance: Numbers need space but shouldn't wrap --}}
                    <th x-show="cols.balance" class="px-4 py-3 text-center whitespace-nowrap min-w-[120px]">
                        {{ __('cash_box.balance') }}
                    </th>

                    {{-- 7. Branch: Can be long, give min width --}}
                    <th x-show="cols.branch" class="px-4 py-3 text-left min-w-[150px]">
                        {{ __('cash_box.branch') }}
                    </th>

                    {{-- 8. Desc: Long text, needs most space --}}
                    <th x-show="cols.desc" class="px-4 py-3 text-left min-w-[250px]">
                        {{ __('cash_box.desc') }}
                    </th>

                    {{-- 9. User: Short text --}}
                    <th x-show="cols.user" class="px-4 py-3 text-center whitespace-nowrap">
                        {{ __('cash_box.user') }}
                    </th>

                    {{-- 10. Date: Standard width --}}
                    <th x-show="cols.created_at" class="px-4 py-3 text-center whitespace-nowrap">
                        {{ __('cash_box.created_at') }}
                    </th>

                    {{-- 11. Active: Tight fit --}}
                    <th x-show="cols.active" class="px-4 py-3 text-center w-px whitespace-nowrap">
                        {{ __('cash_box.active') }}
                    </th>

                    {{-- 12. Actions: Tight fit, always at end --}}
                    <th x-show="cols.actions" class="px-4 py-3 text-center w-px print:hidden"></th>
                </x-table-header>

                <tbody id="sheet-body" class="divide-y divide-slate-100">
                    @foreach($cashBoxes as $index => $box)
                    <tr class="bg-white hover:bg-slate-50 transition-colors group/row" 
                        :class="[editingId === {{ $box->id }} ? 'bg-indigo-50/20' : '', selectedIds.includes({{ $box->id }}) ? 'bg-indigo-50/10' : '']">
                        
                        <td x-show="cols.select" class="px-4 py-4 text-center"><input type="checkbox" :value="{{ $box->id }}" x-model="selectedIds" class="select-checkbox"></td>
                        <td x-show="cols.id" class="px-4 py-4 font-normal text-slate-500 text-center">{{ $loop->iteration }}<input type="hidden" name="boxes[{{ $index }}][id]" value="{{ $box->id }}"></td>
                        
                        <td x-show="cols.name" class="p-1">
                            <span x-show="editingId !== {{ $box->id }}" class="px-3 block text-slate-700 font-normal truncate">{{ $box->name }}</span>
                            <input x-show="editingId === {{ $box->id }}" id="input-name-{{ $box->id }}" type="text" name="boxes[{{ $index }}][name]" value="{{ $box->name }}" class="sheet-input font-normal">
                        </td>
                        <td x-show="cols.type" class="p-1">
                            <span x-show="editingId !== {{ $box->id }}" class="px-3 block text-slate-600 font-normal truncate">{{ $box->type }}</span>
                            <input x-show="editingId === {{ $box->id }}" type="text" name="boxes[{{ $index }}][type]" value="{{ $box->type }}" class="sheet-input font-normal">
                        </td>
                        <td x-show="cols.currency" class="p-1">
                            <div x-show="editingId !== {{ $box->id }}" class="text-center"><span class="bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded text-[10px] font-bold uppercase">{{ $box->currency->currency_type ?? '-' }}</span></div>
                            <select x-show="editingId === {{ $box->id }}" name="boxes[{{ $index }}][currency_id]" class="sheet-input text-center text-xs uppercase font-normal">
                                @foreach($currencies as $currency) <option value="{{ $currency->id }}" {{ $box->currency_id == $currency->id ? 'selected' : '' }}>{{ $currency->currency_type }}</option> @endforeach
                            </select>
                        </td>
                        <td x-show="cols.balance" class="p-1">
                            <div x-show="editingId !== {{ $box->id }}" class="text-center text-emerald-600 font-normal">{{ number_format($box->balance, 2) }}</div>
                            <input x-show="editingId === {{ $box->id }}" type="text" name="boxes[{{ $index }}][balance]" value="{{ number_format($box->balance, 2) }}" oninput="formatNumber(this)" class="sheet-input text-center text-emerald-600 font-normal number-format">
                        </td>
                        <td x-show="cols.branch" class="p-1">
                            <span x-show="editingId !== {{ $box->id }}" class="px-3 block text-xs uppercase text-slate-500 font-normal truncate">{{ optional($box->branch)->name ?? '-' }}</span>
                            <select x-show="editingId === {{ $box->id }}" name="boxes[{{ $index }}][branch_id]" class="sheet-input text-xs font-normal text-slate-700">
                                @foreach($branches as $branch) <option value="{{ $branch->id }}" {{ $box->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option> @endforeach
                            </select>
                        </td>
                        <td x-show="cols.desc" class="p-1">
                            <span x-show="editingId !== {{ $box->id }}" class="px-3 block text-slate-500 text-xs italic font-normal truncate">{{ $box->description ?? '-' }}</span>
                            <input x-show="editingId === {{ $box->id }}" type="text" name="boxes[{{ $index }}][description]" value="{{ $box->description }}" class="sheet-input font-normal">
                        </td>
                        <td x-show="cols.user" class="px-4 py-4 text-[10px] uppercase text-slate-400 font-normal text-center truncate">{{ $box->user->name ?? 'SYSTEM' }}</td>
                        <td x-show="cols.created_at" class="px-4 py-4 text-center text-xs text-slate-400 font-mono font-normal">{{ $box->created_at ? $box->created_at->format('Y-m-d H:i') : '-' }}</td>
                        <td x-show="cols.active" class="px-4 py-4 text-center">
                            <input type="checkbox" name="boxes[{{ $index }}][is_active]" value="1" {{ $box->is_active ? 'checked' : '' }} :class="{ 'pointer-events-none opacity-50': editingId !== {{ $box->id }} }" class="w-4 h-4 text-indigo-600 bg-slate-100 border-slate-300 rounded cursor-pointer">
                        </td>
                        <td x-show="cols.actions" class="px-4 py-4 text-center print:hidden">
                            <div class="relative h-8 flex items-center justify-center w-full">
                                <div x-show="editingId === {{ $box->id }}" class="absolute inset-0 flex items-center justify-center">
                                    <button type="button" @click="saveForm()" class="text-emerald-500 transition active:scale-95"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></button>
                                </div>
                                <div x-show="editingId !== {{ $box->id }}" class="flex items-center justify-center gap-2">
                                    <button type="button" @click="startEdit({{ $box->id }})" class="text-slate-400 hover:text-blue-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                    <button type="button" onclick="deleteDatabaseRow({{ $box->id }})" class="text-slate-400 hover:text-red-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </x-app-table>
        </form>
    </div>

    {{-- FORMS --}}
    <form id="bulk-delete-form" action="{{ route('cash-boxes.bulk-delete') }}" method="POST" class="hidden">@csrf @method('DELETE')<input type="hidden" name="ids" id="bulk-delete-ids"></form>
    <form id="delete-form" action="" method="POST" class="hidden">@csrf @method('DELETE')</form>

    <script>
        function formatNumber(input) {
            let value = input.value.replace(/,/g, '');
            if (!isNaN(value) && value !== '') { input.value = value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","); }
        }

        function addNewRow() {
            const tableBody = document.getElementById('sheet-body');
            const index = Date.now(); 
            const userBranchId = "{{ Auth::user()->branch_id ?? '' }}"; 
            const nextId = tableBody.querySelectorAll('tr').length + 1;
            
            const currencyOptions = `@foreach($currencies as $c)<option value="{{ $c->id }}">{{ $c->currency_type }}</option>@endforeach`;
            let branchOptions = '';
            @foreach($branches as $b) branchOptions += `<option value="{{ $b->id }}" ${ "{{ $b->id }}" == userBranchId ? 'selected' : '' }>{{ $b->name }}</option>`; @endforeach
            const dateString = new Date().toLocaleString('sv-SE', { timeZone: 'Asia/Baghdad' }).replace('T', ' ').slice(0, 16);

            const rowHtml = `
                <tr class="new-row bg-slate-50 transition-colors">
                    <td x-show="cols.select" class="px-4 py-4 text-center"></td>
                    <td x-show="cols.id" class="px-4 py-3 font-normal text-indigo-600 text-center">${nextId}</td>
                    
                    <td x-show="cols.name" class="p-1"><input type="text" name="boxes[${index}][name]" class="sheet-input font-normal text-slate-700" placeholder="..." autofocus></td>
                    <td x-show="cols.type" class="p-1"><input type="text" name="boxes[${index}][type]" class="sheet-input font-normal text-slate-600"></td>
                    <td x-show="cols.currency" class="p-1"><select name="boxes[${index}][currency_id]" class="sheet-input text-center text-xs uppercase font-normal">${currencyOptions}</select></td>
                    
                    <td x-show="cols.balance" class="p-1"><input type="text" name="boxes[${index}][balance]" value="0" oninput="formatNumber(this)" class="sheet-input text-center font-normal text-emerald-600 number-format"></td>
                    <td x-show="cols.branch" class="p-1"><select name="boxes[${index}][branch_id]" class="sheet-input text-xs font-normal text-slate-500">${branchOptions}</select></td>
                    <td x-show="cols.desc" class="p-1"><input type="text" name="boxes[${index}][description]" class="sheet-input font-normal text-slate-500" placeholder="..."></td>
                    <td x-show="cols.user" class="px-4 py-3 text-xs text-slate-400 uppercase font-normal text-center">{{ Auth::user()->name }}</td>
                    <td x-show="cols.created_at" class="px-4 py-3 text-xs text-indigo-400 text-center font-mono font-normal">${dateString}</td>
                    <td x-show="cols.active" class="px-4 py-3 text-center"><input type="checkbox" name="boxes[${index}][is_active]" value="1" checked class="w-4 h-4 text-indigo-600 bg-white border-slate-300 rounded cursor-pointer"></td>
                    
                    <td x-show="cols.actions" class="px-4 py-3 text-center print:hidden">
                        <div class="flex items-center justify-center gap-2">
                             <button type="button" onclick="document.querySelectorAll('.number-format').forEach(el => { el.value = el.value.replace(/,/g, ''); }); document.getElementById('sheet-form').submit()" class="text-emerald-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></button>
                             <button type="button" onclick="this.closest('tr').remove()" class="text-slate-400 hover:text-red-500 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                    </td>
                </tr>`;
            
            tableBody.insertAdjacentHTML('beforeend', rowHtml);
            setTimeout(() => { tableBody.lastElementChild.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 100);
        }

        function deleteDatabaseRow(id) {
            const form = document.getElementById('delete-form');
            form.action = "{{ route('cash-boxes.destroy', ':id') }}".replace(':id', id);
            if (window.confirmAction) { window.confirmAction('delete-form', "{{ __('cash_box.delete_confirm') }}"); } 
            else { if(confirm("{{ __('cash_box.delete_confirm') }}")) { form.submit(); } }
        }
    </script>
</x-app-layout>