<x-app-layout>
    <x-slot name="header">
        {{ __('cash_box.title') }}
    </x-slot>

    {{-- STYLES --}}
    <style>
        /* Base Sheet Input */
        .sheet-input {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            background: transparent;
            border: 1px solid transparent;
            padding: 0 12px;
            font-size: 0.875rem;
            color: #1f2937;
            font-weight: 500;
            border-radius: 6px;
            transition: all 0.15s ease-in-out;
        }

        /* Focus State */
        .sheet-input:focus {
            background-color: #fff;
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        /* --- FIXED DROPDOWN STYLING --- */
        select.sheet-input {
            -webkit-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.2em 1.2em;
            padding-right: 2.5rem; 
            padding-left: 0.75rem;
            cursor: pointer;
            white-space: nowrap; 
        }
        
        /* RTL Support */
        [dir="rtl"] select.sheet-input {
            background-position: left 0.5rem center;
            padding-right: 0.75rem;
            padding-left: 2.5rem; 
        }

        @media print {
            .no-print, button, .print\:hidden { display: none !important; }
            .overflow-x-auto { overflow: visible !important; }
            table { width: 100% !important; }
        }
    </style>

    <div x-data="{ 
        hasChanges: false,
        editingId: null,
        cols: { id: true, name: true, type: true, currency: true, balance: true, branch: true, user: true, active: true, actions: true },
        toggleAll(value) { for (let key in this.cols) { this.cols[key] = value; } },
        startEdit(id) { 
            this.editingId = id; 
            this.hasChanges = true;
            setTimeout(() => { document.getElementById('input-name-'+id)?.focus(); }, 100);
        },
        saveForm() {
            document.getElementById('sheet-form').submit();
        }
    }" class="py-6" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

        {{-- ACTIONS TOOLBAR --}}
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 px-4 no-print">
            <div class="flex items-center gap-4">
                <h3 class="text-xl font-black text-slate-800 tracking-tight">{{ __('cash_box.title') }}</h3>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                
                {{-- Trash Button --}}
                <a href="{{ route('cash-boxes.trash') }}" title="{{ __('cash_box.trash') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-50 text-red-500 border border-red-100 hover:bg-red-100 hover:text-red-600 transition shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </a>

                {{-- Manage Columns (UPDATED ICON: Adjustments/Sliders) --}}
                <div x-data="{ openDropdown: false }" class="relative">
                    <button @click="openDropdown = !openDropdown" @click.away="openDropdown = false" title="{{ __('cash_box.manage_view') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
                        {{-- NEW ICON HERE --}}
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                    </button>
                    <div x-show="openDropdown" class="absolute ltr:right-0 rtl:left-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 z-50 p-2" x-cloak>
                        <div class="flex gap-2 mb-2 pb-2 border-b border-slate-100">
                            <button @click="toggleAll(true)" class="flex-1 text-[10px] bg-indigo-50 text-indigo-600 py-1 rounded hover:bg-indigo-100 font-bold">{{ __('cash_box.all') }}</button>
                            <button @click="toggleAll(false)" class="flex-1 text-[10px] bg-slate-50 text-slate-600 py-1 rounded hover:bg-slate-100 font-bold">{{ __('cash_box.all') }}</button>
                        </div>
                        <div class="space-y-1 max-h-60 overflow-y-auto">
                            @foreach(['id'=>'#', 'name'=>'Name', 'type'=>'Type', 'currency'=>'Currency', 'balance'=>'Balance', 'branch'=>'Branch', 'user'=>'User', 'active'=>'Active'] as $key => $label)
                                <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 rounded cursor-pointer">
                                    <input type="checkbox" x-model="cols.{{ $key }}" class="rounded text-indigo-600 w-4 h-4 border-slate-300">
                                    <span class="text-xs text-slate-700 font-medium">{{ __('cash_box.'.$key) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Print Button --}}
                <a href="{{ route('cash-boxes.print') }}" target="_blank" title="{{ __('cash_box.print') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-700 text-white hover:bg-slate-800 transition shadow-sm shadow-slate-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                </a>

                {{-- Add New Button --}}
                <button type="button" @click="addNewRow(); hasChanges = true" title="{{ __('cash_box.new_box') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition shadow-lg shadow-blue-500/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
            </div>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="relative overflow-x-auto bg-white shadow-sm rounded-lg border border-gray-200 mx-4">
            <form id="sheet-form" action="{{ route('cash-boxes.store-bulk') }}" method="POST">
                @csrf
                <table class="w-full min-w-[1000px] text-sm text-left rtl:text-right text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th x-show="cols.id" class="px-6 py-3 font-medium w-16">#</th>
                            <th x-show="cols.name" class="px-6 py-3 font-medium min-w-[150px]">{{ __('cash_box.name') }}</th>
                            <th x-show="cols.type" class="px-6 py-3 font-medium min-w-[100px]">{{ __('cash_box.type') }}</th>
                            <th x-show="cols.currency" class="px-6 py-3 font-medium text-center min-w-[100px]">{{ __('cash_box.currency') }}</th>
                            <th x-show="cols.balance" class="px-6 py-3 font-medium text-center min-w-[120px]">{{ __('cash_box.balance') }}</th>
                            <th x-show="cols.branch" class="px-6 py-3 font-medium min-w-[180px]">{{ __('cash_box.branch') }}</th>
                            <th x-show="cols.user" class="px-6 py-3 font-medium min-w-[100px]">{{ __('cash_box.user') }}</th>
                            <th x-show="cols.active" class="px-6 py-3 font-medium text-center w-24">{{ __('cash_box.active') }}</th>
                            <th x-show="cols.actions" class="px-6 py-3 font-medium text-center w-28 print:hidden">{{ __('cash_box.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="sheet-body">
                        @foreach($cashBoxes as $index => $box)
                        <tr class="bg-white border-b border-gray-100 hover:bg-gray-50 transition-colors"
                            :class="editingId === {{ $box->id }} ? 'bg-indigo-50/20' : ''">
                            <td x-show="cols.id" class="px-6 py-4 font-medium whitespace-nowrap text-gray-900">{{ $loop->iteration }}<input type="hidden" name="boxes[{{ $index }}][id]" value="{{ $box->id }}"></td>
                            
                            <td x-show="cols.name" class="p-1">
                                <span x-show="editingId !== {{ $box->id }}" class="px-3 block text-gray-700 font-medium">{{ $box->name }}</span>
                                <input x-show="editingId === {{ $box->id }}" id="input-name-{{ $box->id }}" type="text" name="boxes[{{ $index }}][name]" value="{{ $box->name }}" class="sheet-input">
                            </td>

                            <td x-show="cols.type" class="p-1">
                                <span x-show="editingId !== {{ $box->id }}" class="px-3 block text-gray-600">{{ $box->type }}</span>
                                <input x-show="editingId === {{ $box->id }}" type="text" name="boxes[{{ $index }}][type]" value="{{ $box->type }}" class="sheet-input">
                            </td>

                            <td x-show="cols.currency" class="p-1">
                                <div x-show="editingId !== {{ $box->id }}" class="text-center">
                                    <span class="bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded text-[10px] font-bold uppercase">{{ $box->currency->currency_type ?? '-' }}</span>
                                </div>
                                <select x-show="editingId === {{ $box->id }}" name="boxes[{{ $index }}][currency_id]" class="sheet-input text-center text-xs uppercase font-medium">
                                    @foreach($currencies as $currency)
                                        <option value="{{ $currency->id }}" {{ $box->currency_id == $currency->id ? 'selected' : '' }}>{{ $currency->currency_type }}</option>
                                    @endforeach
                                </select>
                            </td>

                            <td x-show="cols.balance" class="p-1">
                                <div x-show="editingId !== {{ $box->id }}" class="text-center text-emerald-600 font-medium">{{ number_format($box->balance, 2) }}</div>
                                <input x-show="editingId === {{ $box->id }}" type="number" step="0.01" name="boxes[{{ $index }}][balance]" value="{{ $box->balance }}" class="sheet-input text-center text-emerald-600 font-medium">
                            </td>

                            <td x-show="cols.branch" class="p-1">
                                <span x-show="editingId !== {{ $box->id }}" class="px-3 block text-xs uppercase text-gray-500">{{ $box->branch->name ?? '-' }}</span>
                                <select x-show="editingId === {{ $box->id }}" name="boxes[{{ $index }}][branch_id]" class="sheet-input text-xs text-gray-700">
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ $box->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </td>

                            <td x-show="cols.user" class="px-6 py-4 text-[10px] uppercase text-gray-400 font-medium">{{ $box->user->name ?? 'SYSTEM' }}</td>
                            
                            <td x-show="cols.active" class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center">
                                    <input type="checkbox" name="boxes[{{ $index }}][is_active]" value="1" {{ $box->is_active ? 'checked' : '' }} :disabled="editingId !== {{ $box->id }}" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 disabled:opacity-50 cursor-pointer">
                                </div>
                            </td>
                            
                            <td x-show="cols.actions" class="px-6 py-4 text-center print:hidden">
                                <div class="flex items-center justify-center gap-2">
                                    
                                    {{-- SAVE BUTTON --}}
                                    <button type="button" @click="saveForm()" x-show="editingId === {{ $box->id }}" class="w-7 h-7 flex items-center justify-center bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg shadow-sm transition transform active:scale-95">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </button>

                                    {{-- EDIT BUTTON --}}
                                    <button type="button" @click="startEdit({{ $box->id }})" x-show="editingId !== {{ $box->id }}" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="{{ __('Edit') }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>

                                    {{-- DELETE BUTTON --}}
                                    <button type="button" onclick="deleteDatabaseRow({{ $box->id }})" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="{{ __('Delete') }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    <form id="delete-form" action="" method="POST" class="hidden">@csrf @method('DELETE')</form>

    <script>
        function addNewRow() {
            const tableBody = document.getElementById('sheet-body');
            const index = Date.now(); 
            const userBranchId = "{{ Auth::user()->branch_id ?? '' }}"; 
            
            const currencyOptions = `@foreach($currencies as $c)<option value="{{ $c->id }}">{{ $c->currency_type }}</option>@endforeach`;
            let branchOptions = '';
            @foreach($branches as $b)
                branchOptions += `<option value="{{ $b->id }}" ${ "{{ $b->id }}" == userBranchId ? 'selected' : '' }>{{ $b->name }}</option>`;
            @endforeach

            const rowHtml = `
                <tr class="bg-blue-50/40 border-b border-blue-100 transition-all duration-300">
                    <td x-show="cols.id" class="px-6 py-3 font-medium text-slate-400 text-center">
                        <span class="bg-blue-100 text-blue-600 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider">{{ __('New') }}</span>
                    </td>
                    <td x-show="cols.name" class="p-1">
                        <input type="text" name="boxes[${index}][name]" class="sheet-input font-medium text-gray-700" placeholder="{{ __('cash_box.name') }}..." autofocus>
                    </td>
                    <td x-show="cols.type" class="p-1">
                        <input type="text" name="boxes[${index}][type]" class="sheet-input font-medium text-gray-600" placeholder="{{ __('cash_box.type') }}">
                    </td>
                    <td x-show="cols.currency" class="p-1">
                        <select name="boxes[${index}][currency_id]" class="sheet-input text-center text-xs font-medium uppercase text-gray-700">${currencyOptions}</select>
                    </td>
                    <td x-show="cols.balance" class="p-1">
                        <input type="number" step="0.01" name="boxes[${index}][balance]" value="0" class="sheet-input text-center font-medium text-emerald-600">
                    </td>
                    <td x-show="cols.branch" class="p-1">
                        <select name="boxes[${index}][branch_id]" class="sheet-input text-xs font-medium text-gray-500">${branchOptions}</select>
                    </td>
                    <td x-show="cols.user" class="px-6 py-3 text-xs text-gray-400 uppercase font-medium">{{ Auth::user()->name }}</td>
                    <td x-show="cols.active" class="px-6 py-3 text-center">
                        <div class="flex items-center justify-center h-full">
                            <input type="checkbox" name="boxes[${index}][is_active]" value="1" checked class="w-4 h-4 text-indigo-600 bg-white border-gray-300 rounded focus:ring-indigo-500 cursor-pointer">
                        </div>
                    </td>
                    <td x-show="cols.actions" class="px-6 py-3 text-center print:hidden">
                        <div class="flex items-center justify-center gap-2">
                             {{-- NEW ROW SAVE BUTTON --}}
                             <button type="button" onclick="document.getElementById('sheet-form').submit()" class="w-7 h-7 flex items-center justify-center bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg shadow-sm transition transform active:scale-95">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                             </button>
                             {{-- NEW ROW CANCEL BUTTON --}}
                             <button type="button" onclick="this.closest('tr').remove()" class="w-7 h-7 flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                             </button>
                        </div>
                    </td>
                </tr>`;
            tableBody.insertAdjacentHTML('beforeend', rowHtml);
            
            setTimeout(() => {
                const newRows = tableBody.querySelectorAll('tr');
                newRows[newRows.length - 1].scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 100);
        }

        function deleteDatabaseRow(id) {
            const form = document.getElementById('delete-form');
            form.action = "{{ route('cash-boxes.destroy', ':id') }}".replace(':id', id);
            window.confirmAction('delete-form', "{{ app()->getLocale() == 'ku' ? 'دڵنیایت لە سڕینەوە؟' : 'Are you sure you want to delete?' }}");
        }
    </script>
</x-app-layout>