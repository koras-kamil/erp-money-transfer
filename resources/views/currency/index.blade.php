<x-app-layout>
    <x-slot name="header">
        {{ __('currency.config_title') }}
    </x-slot>

    <style>
        /* --- SPREADSHEET GRID STYLING --- */
        table { border-collapse: separate; border-spacing: 0; width: 100%; }
        
        /* Row & Cell Sizing */
        tr { height: 50px; }
        
        td {
            border-bottom: 1px solid #e2e8f0;
            background-color: white;
            padding: 0;
            vertical-align: middle;
        }

        /* Vertical Borders for Grid Look */
        td:not(:last-child) { border-inline-end: 1px solid #f1f5f9; }

        /* --- INPUT STYLING --- */
        .sheet-input {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            background: transparent;
            border: none;
            outline: none;
            padding: 0 10px;
            font-size: 0.9rem;
            color: #1e293b;
            font-weight: 600;
            transition: all 0.1s;
        }

        /* Focus: Blue Inset Border */
        .sheet-input:focus {
            background-color: #fff;
            box-shadow: inset 0 0 0 2px #6366f1;
            z-index: 5;
            position: relative;
        }

        /* Hover Effect */
        tr:hover td { background-color: #f8fafc; }

        /* --- STICKY COLUMNS (For Mobile & Desktop Scrolling) --- */
        .sticky-col {
            position: sticky;
            inset-inline-start: 0; /* Left in EN, Right in KU */
            z-index: 10;
            background-color: #f8fafc;
            border-inline-end: 2px solid #e2e8f0;
        }
        
        /* Fix Sticky background on hover */
        tr:hover .sticky-col { background-color: #f1f5f9; }

        @media print {
            .no-print, button, .print\:hidden { display: none !important; }
            .overflow-x-auto { overflow: visible !important; }
            table { width: 100% !important; }
        }
    </style>

    <div x-data="{ 
        hasChanges: false,
        cols: { id: true, type: true, symbol: true, digit: true, total: true, single: true, branch: true, active: true, actions: true },
        toggleAll(value) { for (let key in this.cols) { this.cols[key] = value; } }
    }" class="py-6" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

        {{-- ACTIONS TOOLBAR --}}
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 px-4 no-print">
            
            {{-- Left Side: Title & Trash --}}
            <div class="flex items-center gap-4">
                <h3 class="text-xl font-black text-slate-800 tracking-tight">{{ __('currency.config_title') }}</h3>
                <a href="{{ route('currency.trash') }}" class="text-xs font-bold text-red-500 hover:text-red-700 flex items-center gap-1 bg-red-50 px-3 py-1.5 rounded-full border border-red-100 transition">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    {{ __('currency.trash') ?? 'Trash' }}
                </a>
            </div>
            
            {{-- Right Side: Buttons --}}
            <div class="flex flex-wrap items-center gap-2">
                
                {{-- 1. MANAGE VIEW (RESTORED) --}}
                <div x-data="{ openDropdown: false }" class="relative">
                    <button @click="openDropdown = !openDropdown" @click.away="openDropdown = false" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-600 px-3 py-2 rounded-lg text-xs font-bold hover:bg-slate-50 shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>{{ __('cash_box.manage_view') ?? 'View' }}</span>
                    </button>
                    {{-- Dropdown Menu --}}
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

                {{-- 2. PRINT --}}
                <button onclick="window.print()" class="bg-slate-700 text-white px-3 py-2 rounded-lg text-xs font-bold hover:bg-slate-800 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    {{ __('currency.print') ?? 'Print' }}
                </button>

                {{-- 3. ADD NEW --}}
                <button type="button" @click="addNewRow(); hasChanges = true" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-xs font-bold shadow-lg shadow-blue-500/30">
                    {{ __('currency.add_new') }}
                </button>

                {{-- 4. SAVE --}}
                <button type="button" 
                        @click="hasChanges ? document.getElementById('sheet-form').submit() : Swal.fire({icon: 'info', title: '{{ __('currency.no_changes') ?? 'No changes' }}'})"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-xs font-bold shadow-lg shadow-emerald-500/30">
                    {{ __('currency.save_changes') }}
                </button>
            </div>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="overflow-x-auto pb-4 rounded-xl border border-slate-200 bg-white shadow-sm mx-4">
            <form id="sheet-form" action="{{ route('currency.store') }}" method="POST">
                @csrf
                {{-- Min-width to ensure PC grid looks nice but scrollable on mobile --}}
                <table class="w-full min-w-[1000px] text-sm text-left text-slate-500">
                    <thead class="text-xs text-slate-700 uppercase bg-slate-100 border-b-2 border-slate-200 h-12">
                        <tr>
                            <th x-show="cols.id" class="w-[60px] text-center sticky-col border-r border-slate-200">#</th>
                            <th x-show="cols.type" class="min-w-[140px] px-2">{{ __('currency.type') }}</th>
                            <th x-show="cols.symbol" class="min-w-[80px] text-center">{{ __('currency.symbol') }}</th>
                            <th x-show="cols.digit" class="min-w-[80px] text-center">{{ __('currency.digit') }}</th>
                            <th x-show="cols.total" class="min-w-[130px] text-center bg-slate-50">{{ __('currency.price_total') }}</th>
                            <th x-show="cols.single" class="min-w-[130px] text-center bg-slate-50">{{ __('currency.price_single') }}</th>
                            <th x-show="cols.branch" class="min-w-[150px] px-2">{{ __('currency.branch') }}</th>
                            <th x-show="cols.active" class="w-[80px] text-center">{{ __('currency.active') }}</th>
                            <th x-show="cols.actions" class="w-[60px] text-center print:hidden">{{ __('currency.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="sheet-body">
                        @foreach($currencies as $index => $currency)
                        <tr class="group transition-colors">
                            {{-- ID (Sticky) --}}
                            <td x-show="cols.id" class="text-center font-bold text-slate-400 sticky-col border-r border-slate-100">
                                {{ $loop->iteration }}
                                <input type="hidden" name="currencies[{{ $index }}][id]" value="{{ $currency->id }}">
                            </td>
                            
                            <td x-show="cols.type"><input type="text" name="currencies[{{ $index }}][currency_type]" value="{{ $currency->currency_type }}" class="sheet-input font-bold uppercase text-slate-800" placeholder="CODE"></td>
                            <td x-show="cols.symbol"><input type="text" name="currencies[{{ $index }}][symbol]" value="{{ $currency->symbol }}" class="sheet-input text-center" placeholder="$"></td>
                            <td x-show="cols.digit"><input type="number" name="currencies[{{ $index }}][digit_number]" value="{{ $currency->digit_number }}" class="sheet-input text-center"></td>
                            <td x-show="cols.total" class="bg-slate-50/50"><input type="number" step="0.001" name="currencies[{{ $index }}][price_total]" value="{{ $currency->price_total }}" class="sheet-input text-center text-emerald-600 font-bold"></td>
                            <td x-show="cols.single" class="bg-slate-50/50"><input type="number" step="0.001" name="currencies[{{ $index }}][price_single]" value="{{ $currency->price_single }}" class="sheet-input text-center text-blue-600 font-bold"></td>
                            <td x-show="cols.branch"><input type="text" name="currencies[{{ $index }}][branch]" value="{{ $currency->branch }}" class="sheet-input"></td>
                            
                            {{-- Active Checkbox --}}
                            <td x-show="cols.active" class="text-center bg-slate-50/30">
                                <div class="flex items-center justify-center h-full">
                                    <input type="checkbox" name="currencies[{{ $index }}][is_active]" value="1" {{ $currency->is_active ? 'checked' : '' }} class="w-5 h-5 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer">
                                </div>
                            </td>
                            
                            {{-- Delete Button --}}
                            <td x-show="cols.actions" class="text-center print:hidden bg-slate-50/30">
                                <div class="flex items-center justify-center h-full">
                                    <button type="button" onclick="deleteDatabaseRow({{ $currency->id }})" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
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
            // Note: Added x-show attributes to new row to respect toggles
            const rowHtml = `
                <tr class="group bg-blue-50/20 hover:bg-blue-50 transition-colors animate-pulse-once">
                    <td x-show="cols.id" class="text-center sticky-col border-r border-slate-100">
                        <span class="bg-green-100 text-green-700 py-0.5 px-2 rounded text-[10px] font-black">NEW</span>
                    </td>
                    <td x-show="cols.type"><input type="text" name="currencies[${index}][currency_type]" class="sheet-input font-bold uppercase" placeholder="CODE" autofocus></td>
                    <td x-show="cols.symbol"><input type="text" name="currencies[${index}][symbol]" class="sheet-input text-center" placeholder="$"></td>
                    <td x-show="cols.digit"><input type="number" name="currencies[${index}][digit_number]" value="0" class="sheet-input text-center"></td>
                    <td x-show="cols.total" class="bg-slate-50/50"><input type="number" step="0.001" name="currencies[${index}][price_total]" value="0" class="sheet-input text-center text-emerald-600 font-bold"></td>
                    <td x-show="cols.single" class="bg-slate-50/50"><input type="number" step="0.001" name="currencies[${index}][price_single]" value="0" class="sheet-input text-center text-blue-600 font-bold"></td>
                    <td x-show="cols.branch"><input type="text" name="currencies[${index}][branch]" class="sheet-input"></td>
                    <td x-show="cols.active" class="text-center bg-slate-50/30">
                        <div class="flex items-center justify-center h-full">
                            <input type="checkbox" name="currencies[${index}][is_active]" value="1" checked class="w-5 h-5 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer">
                        </div>
                    </td>
                    <td x-show="cols.actions" class="text-center print:hidden bg-slate-50/30">
                         <div class="flex items-center justify-center h-full">
                            <button type="button" onclick="this.closest('tr').remove()" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                    </td>
                </tr>`;
            tableBody.insertAdjacentHTML('beforeend', rowHtml);
            // Re-initialize Alpine in case of complex reactivity (usually not needed for x-show, but good practice if logic fails)
        }

        function deleteDatabaseRow(id) {
            const form = document.getElementById('delete-form');
            form.action = "{{ route('currency.destroy', ':id') }}".replace(':id', id);
            window.confirmAction('delete-form', "{{ app()->getLocale() == 'ku' ? 'دڵنیایت لە سڕینەوە؟' : 'Are you sure you want to delete?' }}");
        }
    </script>
</x-app-layout>