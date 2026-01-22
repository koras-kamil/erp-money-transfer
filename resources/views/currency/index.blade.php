<x-app-layout>
    <x-slot name="header">
        {{ __('currency.config_title') }}
    </x-slot>

    {{-- STYLES (Exact match from Cash Box) --}}
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
            font-size: 0.875rem; /* 14px */
            color: #1f2937; /* gray-800 */
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

        /* Custom Dropdown Styling */
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
        cols: { id: true, type: true, symbol: true, digit: true, total: true, single: true, branch: true, active: true, actions: true },
        toggleAll(value) { for (let key in this.cols) { this.cols[key] = value; } },
        startEdit(id) { 
            this.editingId = id; 
            this.hasChanges = true;
            setTimeout(() => { document.getElementById('input-type-'+id)?.focus(); }, 100);
        }
    }" class="py-6" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

        {{-- ACTIONS TOOLBAR --}}
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 px-4 no-print">
            <div class="flex items-center gap-4">
                <h3 class="text-xl font-black text-slate-800 tracking-tight">{{ __('currency.config_title') }}</h3>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('currency.trash') }}" title="{{ __('currency.trash') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-50 text-red-500 border border-red-100 hover:bg-red-100 hover:text-red-600 transition shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </a>

                <div x-data="{ openDropdown: false }" class="relative">
                    <button @click="openDropdown = !openDropdown" @click.away="openDropdown = false" title="{{ __('cash_box.manage_view') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                    <div x-show="openDropdown" class="absolute ltr:right-0 rtl:left-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 z-50 p-2" x-cloak>
                        <div class="flex gap-2 mb-2 pb-2 border-b border-slate-100">
                            <button @click="toggleAll(true)" class="flex-1 text-[10px] bg-indigo-50 text-indigo-600 py-1 rounded hover:bg-indigo-100 font-bold">ALL</button>
                            <button @click="toggleAll(false)" class="flex-1 text-[10px] bg-slate-50 text-slate-600 py-1 rounded hover:bg-slate-100 font-bold">NONE</button>
                        </div>
                        <div class="space-y-1 max-h-60 overflow-y-auto">
                            @foreach(['id'=>'#', 'type'=>'Type', 'symbol'=>'Symbol', 'digit'=>'Digit', 'total'=>'Total Price', 'single'=>'Single Price', 'branch'=>'Branch', 'active'=>'Active'] as $key => $label)
                                <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 rounded cursor-pointer">
                                    <input type="checkbox" x-model="cols.{{ $key }}" class="rounded text-indigo-600 w-4 h-4 border-slate-300">
                                    <span class="text-xs text-slate-700 font-medium">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- UPDATED: Print Button now links to PDF Route --}}
                <a href="{{ route('currency.print') }}" target="_blank" title="{{ __('currency.print') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-700 text-white hover:bg-slate-800 transition shadow-sm shadow-slate-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                </a>

                <button type="button" @click="addNewRow(); hasChanges = true" title="{{ __('currency.add_new') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition shadow-lg shadow-blue-500/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>

                <button type="button" title="{{ __('currency.save_changes') }}" @click="hasChanges ? document.getElementById('sheet-form').submit() : Swal.fire({icon: 'info', title: '{{ __('currency.no_changes') ?? 'No changes' }}'})" class="w-10 h-10 flex items-center justify-center rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-lg shadow-emerald-500/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </button>
            </div>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="relative overflow-x-auto bg-white shadow-sm rounded-lg border border-gray-200 mx-4">
            <form id="sheet-form" action="{{ route('currency.store') }}" method="POST">
                @csrf
                <table class="w-full min-w-[1000px] text-sm text-left rtl:text-right text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th x-show="cols.id" class="px-6 py-3 font-medium w-16">#</th>
                            <th x-show="cols.type" class="px-6 py-3 font-medium min-w-[140px]">{{ __('currency.type') }}</th>
                            <th x-show="cols.symbol" class="px-6 py-3 font-medium text-center min-w-[80px]">{{ __('currency.symbol') }}</th>
                            <th x-show="cols.digit" class="px-6 py-3 font-medium text-center min-w-[80px]">{{ __('currency.digit') }}</th>
                            <th x-show="cols.total" class="px-6 py-3 font-medium text-center min-w-[130px]">{{ __('currency.price_total') }}</th>
                            <th x-show="cols.single" class="px-6 py-3 font-medium text-center min-w-[130px]">{{ __('currency.price_single') }}</th>
                            <th x-show="cols.branch" class="px-6 py-3 font-medium min-w-[150px]">{{ __('currency.branch') }}</th>
                            <th x-show="cols.active" class="px-6 py-3 font-medium text-center w-24">{{ __('currency.active') }}</th>
                            <th x-show="cols.actions" class="px-6 py-3 font-medium text-center w-28 print:hidden">{{ __('currency.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="sheet-body">
                        @foreach($currencies as $index => $currency)
                        <tr class="bg-white border-b border-gray-100 hover:bg-gray-50 transition-colors"
                            :class="editingId === {{ $currency->id }} ? 'bg-indigo-50/20' : ''">
                            
                            {{-- ID --}}
                            <td x-show="cols.id" class="px-6 py-4 font-medium whitespace-nowrap text-gray-900">
                                {{ $loop->iteration }}
                                <input type="hidden" name="currencies[{{ $index }}][id]" value="{{ $currency->id }}">
                            </td>
                            
                            {{-- Type --}}
                            <td x-show="cols.type" class="p-1">
                                <span x-show="editingId !== {{ $currency->id }}" class="px-3 block text-gray-800 font-medium">{{ $currency->currency_type }}</span>
                                <input x-show="editingId === {{ $currency->id }}" type="text" id="input-type-{{ $currency->id }}" name="currencies[{{ $index }}][currency_type]" value="{{ $currency->currency_type }}" class="sheet-input font-medium uppercase">
                            </td>

                            {{-- Symbol --}}
                            <td x-show="cols.symbol" class="p-1">
                                <span x-show="editingId !== {{ $currency->id }}" class="block text-center text-gray-600">{{ $currency->symbol }}</span>
                                <input x-show="editingId === {{ $currency->id }}" type="text" name="currencies[{{ $index }}][symbol]" value="{{ $currency->symbol }}" class="sheet-input text-center">
                            </td>

                            {{-- Digit --}}
                            <td x-show="cols.digit" class="p-1">
                                <span x-show="editingId !== {{ $currency->id }}" class="block text-center text-gray-600">{{ $currency->digit_number }}</span>
                                <input x-show="editingId === {{ $currency->id }}" type="number" name="currencies[{{ $index }}][digit_number]" value="{{ $currency->digit_number }}" class="sheet-input text-center">
                            </td>

                            {{-- Total Price --}}
                            <td x-show="cols.total" class="p-1">
                                <div x-show="editingId !== {{ $currency->id }}" class="text-center font-medium text-emerald-600">{{ number_format($currency->price_total, 3) }}</div>
                                <input x-show="editingId === {{ $currency->id }}" type="number" step="0.001" name="currencies[{{ $index }}][price_total]" value="{{ $currency->price_total }}" class="sheet-input text-center font-medium text-emerald-600">
                            </td>

                            {{-- Single Price --}}
                            <td x-show="cols.single" class="p-1">
                                <div x-show="editingId !== {{ $currency->id }}" class="text-center font-medium text-blue-600">{{ number_format($currency->price_single, 3) }}</div>
                                <input x-show="editingId === {{ $currency->id }}" type="number" step="0.001" name="currencies[{{ $index }}][price_single]" value="{{ $currency->price_single }}" class="sheet-input text-center font-medium text-blue-600">
                            </td>

                            {{-- Branch --}}
                            <td x-show="cols.branch" class="p-1">
                                <span x-show="editingId !== {{ $currency->id }}" class="px-3 block text-gray-500">{{ $currency->branch }}</span>
                                <input x-show="editingId === {{ $currency->id }}" type="text" name="currencies[{{ $index }}][branch]" value="{{ $currency->branch }}" class="sheet-input text-gray-700">
                            </td>
                            
                            {{-- Active --}}
                            <td x-show="cols.active" class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center">
                                    <input type="checkbox" name="currencies[{{ $index }}][is_active]" value="1" {{ $currency->is_active ? 'checked' : '' }} 
                                           :disabled="editingId !== {{ $currency->id }}"
                                           class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 disabled:opacity-50 cursor-pointer">
                                </div>
                            </td>
                            
                            {{-- Actions --}}
                            <td x-show="cols.actions" class="px-6 py-4 text-center print:hidden">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Edit Button --}}
                                    <button type="button" @click="startEdit({{ $currency->id }})" 
                                            x-show="editingId !== {{ $currency->id }}"
                                            class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="{{ __('Edit') }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    
                                    {{-- Edit Indicator --}}
                                    <span x-show="editingId === {{ $currency->id }}" class="text-emerald-500 animate-pulse">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </span>

                                    {{-- Delete Button --}}
                                    <button type="button" onclick="deleteDatabaseRow({{ $currency->id }})" 
                                            class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="{{ __('Delete') }}">
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
            const rowHtml = `
                <tr class="bg-blue-50/40 border-b border-blue-100 transition-all duration-300">
                    <td x-show="cols.id" class="px-6 py-4 font-medium text-slate-400 text-center">
                        <span class="bg-blue-100 text-blue-600 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider">NEW</span>
                    </td>
                    <td x-show="cols.type" class="p-1"><input type="text" name="currencies[${index}][currency_type]" class="sheet-input font-medium uppercase" placeholder="CODE" autofocus></td>
                    <td x-show="cols.symbol" class="p-1"><input type="text" name="currencies[${index}][symbol]" class="sheet-input text-center" placeholder="$"></td>
                    <td x-show="cols.digit" class="p-1"><input type="number" name="currencies[${index}][digit_number]" value="0" class="sheet-input text-center"></td>
                    <td x-show="cols.total" class="p-1"><input type="number" step="0.001" name="currencies[${index}][price_total]" value="0" class="sheet-input text-center font-medium text-emerald-600"></td>
                    <td x-show="cols.single" class="p-1"><input type="number" step="0.001" name="currencies[${index}][price_single]" value="0" class="sheet-input text-center font-medium text-blue-600"></td>
                    <td x-show="cols.branch" class="p-1"><input type="text" name="currencies[${index}][branch]" class="sheet-input text-gray-700"></td>
                    <td x-show="cols.active" class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center">
                            <input type="checkbox" name="currencies[${index}][is_active]" value="1" checked class="w-4 h-4 text-indigo-600 bg-white border-gray-300 rounded focus:ring-indigo-500 cursor-pointer">
                        </div>
                    </td>
                    <td x-show="cols.actions" class="px-6 py-4 text-center print:hidden">
                         <button type="button" onclick="this.closest('tr').remove()" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                         </button>
                    </td>
                </tr>`;
            tableBody.insertAdjacentHTML('beforeend', rowHtml);
        }

        function deleteDatabaseRow(id) {
            const form = document.getElementById('delete-form');
            form.action = "{{ route('currency.destroy', ':id') }}".replace(':id', id);
            window.confirmAction('delete-form', "{{ app()->getLocale() == 'ku' ? 'دڵنیایت لە سڕینەوە؟' : 'Are you sure you want to delete?' }}");
        }
    </script>
</x-app-layout>