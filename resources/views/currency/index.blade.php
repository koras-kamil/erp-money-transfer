<x-app-layout>
    <x-slot name="header">
        {{ __('currency.config_title') }}
    </x-slot>

    <style>
        /* Screen Input Styles */
        .sheet-input { 
            width: 100%; 
            background: transparent; 
            border: 1px solid transparent; 
            padding: 6px; 
            border-radius: 4px; 
            outline: none; 
            transition: all 0.2s; 
            font-size: 0.9rem;
        }
        .sheet-input:focus { 
            background-color: white; 
            border-color: #6366f1; 
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2); 
        }
        .sheet-input:hover { 
            background-color: #f8fafc; 
            border-color: #e2e8f0; 
        }

        /* PRINT SETTINGS - A4 PORTRAIT */
        @media print {
            @page { 
                size: A4 portrait; /* Changed to Portrait */
                margin: 5mm; /* Narrow margins to fit all columns */
            }

            /* Hide UI Elements */
            aside, nav, header, 
            .min-h-screen > div:first-child, 
            .print\:hidden, 
            button, 
            .no-print,
            /* Hide Actions column only */
            th:last-child, td:last-child { 
                display: none !important; 
            }

            /* Reset Backgrounds & Fonts */
            body, .min-h-screen, .bg-gray-100 { 
                background: white !important; 
                margin: 0 !important; 
                padding: 0 !important; 
                -webkit-print-color-adjust: exact !important; 
                print-color-adjust: exact !important;
            }

            main { width: 100% !important; margin: 0 !important; padding: 0 !important; }

            /* Table Fit Logic */
            .overflow-x-auto { overflow: visible !important; border: none !important; box-shadow: none !important; }

            table { 
                width: 100% !important; 
                border-collapse: collapse !important; 
                font-size: 8pt !important; /* Smaller font to fit Portrait width */
                margin-top: 10px !important;
                table-layout: fixed; /* Ensures columns respect width */
            }

            th, td { 
                border: 1px solid #000 !important; 
                padding: 3px 2px !important; /* Tight padding for Portrait */
                color: black !important; 
                text-align: center !important;
                word-wrap: break-word;
            }

            /* Inputs flatten for print */
            input { 
                border: none !important; 
                background: transparent !important; 
                padding: 0 !important; 
                margin: 0 !important; 
                width: 100% !important; 
                text-align: center;
                font-size: inherit;
            }

            th { background-color: #f3f4f6 !important; font-weight: bold !important; }
            tr { page-break-inside: avoid; }
            thead { display: table-header-group; }

            #print-header { 
                display: block !important; 
                text-align: center; 
                border-bottom: 2px solid #000; 
                margin-bottom: 10px; 
                padding-bottom: 5px; 
            }
        }

        #print-header { display: none; }
    </style>

    <div x-data="{}" class="py-6">
        
        <div id="print-header">
            <h1 class="text-xl font-bold uppercase">{{ config('app.name') }}</h1>
            <h2 class="text-sm">{{ __('currency.config_title') }}</h2>
            <p class="text-[10px] text-gray-500">{{ date('Y-m-d H:i') }} | {{ Auth::user()->name }}</p>
        </div>

        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 print:hidden px-4 md:px-0">
            <h3 class="text-lg font-bold text-slate-700">{{ __('currency.config_title') }}</h3>
            
            <div class="flex flex-wrap items-center gap-2">
                <button onclick="window.print()" class="flex items-center gap-2 bg-slate-700 text-white px-3 py-2 rounded-lg text-xs font-bold hover:bg-slate-800 shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>{{ __('currency.print') ?? 'Print' }}</span>
                </button>

                <button type="button" onclick="addNewRow()" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-lg shadow-blue-500/30 transition-all active:scale-95 text-xs font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>{{ __('currency.add_new') }}</span>
                </button>

                <button type="button" onclick="submitIfChanged()" class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg shadow-lg shadow-emerald-500/30 transition-all active:scale-95 text-xs font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>{{ __('currency.save_changes') }}</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm mx-4 md:mx-0">
            <form id="sheet-form" action="{{ route('currency.store') }}" method="POST">
                @csrf
                <table class="w-full text-sm text-left text-slate-500" onchange="markAsDirty()" oninput="markAsDirty()">
                    <thead class="text-xs text-slate-700 uppercase bg-blue-100/50 border-b border-blue-200">
                        <tr>
                            <th class="px-2 py-3 w-[5%] text-center">#</th>
                            <th class="px-2 py-3 w-[15%]">{{ __('currency.type') }}</th>
                            <th class="px-2 py-3 w-[10%] text-center">{{ __('currency.symbol') }}</th>
                            <th class="px-2 py-3 w-[10%] text-center">{{ __('currency.digit') }}</th>
                            <th class="px-2 py-3 w-[12%] text-center bg-slate-50">{{ __('currency.price_total') }}</th>
                            <th class="px-2 py-3 w-[12%] text-center bg-slate-50">{{ __('currency.price_single') }}</th>
                            <th class="px-2 py-3 w-[12%] text-center bg-slate-50">{{ __('currency.price_sell') }}</th>
                            <th class="px-2 py-3 w-[14%]">{{ __('currency.branch') }}</th>
                            <th class="px-2 py-3 w-[5%] text-center">{{ __('currency.active') }}</th>
                            <th class="px-2 py-3 w-[5%] text-center print:hidden">{{ __('currency.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="sheet-body" class="divide-y divide-slate-100">
                        @foreach($currencies as $index => $currency)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-2 py-2 text-center font-medium text-slate-400">
                                {{ $loop->iteration }}
                                <input type="hidden" name="currencies[{{ $index }}][id]" value="{{ $currency->id }}">
                            </td>
                            <td class="px-2 py-2"><input type="text" name="currencies[{{ $index }}][currency_type]" value="{{ $currency->currency_type }}" class="sheet-input font-bold text-slate-800"></td>
                            <td class="px-2 py-2"><input type="text" name="currencies[{{ $index }}][symbol]" value="{{ $currency->symbol }}" class="sheet-input text-center"></td>
                            <td class="px-2 py-2"><input type="number" name="currencies[{{ $index }}][digit_number]" value="{{ $currency->digit_number }}" class="sheet-input text-center"></td>
                            <td class="px-2 py-2 bg-slate-50/50"><input type="number" step="0.001" name="currencies[{{ $index }}][price_total]" value="{{ $currency->price_total }}" class="sheet-input text-center"></td>
                            <td class="px-2 py-2 bg-slate-50/50"><input type="number" step="0.001" name="currencies[{{ $index }}][price_single]" value="{{ $currency->price_single }}" class="sheet-input text-center"></td>
                            <td class="px-2 py-2 bg-slate-50/50"><input type="number" step="0.001" name="currencies[{{ $index }}][price_sell]" value="{{ $currency->price_sell }}" class="sheet-input font-bold text-emerald-600 text-center"></td>
                            <td class="px-2 py-2"><input type="text" name="currencies[{{ $index }}][branch]" value="{{ $currency->branch }}" class="sheet-input"></td>
                            <td class="px-2 py-2 text-center">
                                <input type="checkbox" name="currencies[{{ $index }}][is_active]" value="1" {{ $currency->is_active ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer">
                            </td>
                            <td class="px-2 py-2 text-center print:hidden">
                                <button type="button" onclick="deleteDatabaseRow({{ $currency->id }})" class="p-1.5 bg-red-50 text-red-600 rounded hover:bg-red-100 transition shadow-sm" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    <form id="delete-form" action="" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Track changes
        let hasChanges = false;

        function markAsDirty() {
            hasChanges = true;
        }

        // Logic for "Save Changes" Button
        function submitIfChanged() {
            if (!hasChanges) {
                Swal.fire({
                    title: '{{ __("currency.no_changes_title") ?? "No Changes" }}',
                    text: '{{ __("currency.no_changes_text") ?? "Nothing was updated or written to be saved." }}',
                    icon: 'warning',
                    confirmButtonColor: '#f59e0b', // Orange/Amber for warning
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'rounded-2xl shadow-xl border border-orange-100',
                        title: 'text-slate-800 font-bold',
                        content: 'text-slate-500'
                    }
                });
                return;
            }
            // If changes exist, submit the form
            document.getElementById('sheet-form').submit();
        }

        function addNewRow() {
            markAsDirty(); // Adding a row counts as a change
            
            const tableBody = document.getElementById('sheet-body');
            const index = Date.now(); 
            
            const rowHtml = `
                <tr class="animate-pulse bg-blue-50/50 transition-all duration-500">
                    <td class="px-2 py-2 text-center">
                        <span class="bg-green-100 text-green-700 py-0.5 px-2 rounded text-[10px] font-bold">{{ __('currency.new') }}</span>
                    </td>
                    <td class="px-2 py-2"><input type="text" name="currencies[${index}][currency_type]" placeholder="..." class="sheet-input font-bold text-slate-600" autofocus></td>
                    <td class="px-2 py-2"><input type="text" name="currencies[${index}][symbol]" class="sheet-input text-center"></td>
                    <td class="px-2 py-2"><input type="number" name="currencies[${index}][digit_number]" value="0" class="sheet-input text-center"></td>
                    <td class="px-2 py-2"><input type="number" step="0.001" name="currencies[${index}][price_total]" value="0" class="sheet-input text-center"></td>
                    <td class="px-2 py-2"><input type="number" step="0.001" name="currencies[${index}][price_single]" value="0" class="sheet-input text-center"></td>
                    <td class="px-2 py-2"><input type="number" step="0.001" name="currencies[${index}][price_sell]" value="0" class="sheet-input font-bold text-emerald-600 text-center"></td>
                    <td class="px-2 py-2"><input type="text" name="currencies[${index}][branch]" class="sheet-input"></td>
                    <td class="px-2 py-2 text-center">
                        <input type="checkbox" name="currencies[${index}][is_active]" value="1" checked class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer">
                    </td>
                    <td class="px-2 py-2 text-center print:hidden">
                        <button type="button" onclick="removeVisualRow(this)" class="p-1.5 bg-slate-100 text-slate-400 hover:bg-red-100 hover:text-red-500 rounded transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </td>
                </tr>`;
            
            tableBody.insertAdjacentHTML('beforeend', rowHtml);
            
            const newRow = tableBody.lastElementChild;
            const firstInput = newRow.querySelector('input');
            if(firstInput) firstInput.focus();
            
            setTimeout(() => {
                newRow.classList.remove('animate-pulse', 'bg-blue-50/50');
                newRow.classList.add('hover:bg-slate-50');
            }, 1000);
        }

        function deleteDatabaseRow(id) {
            Swal.fire({
                title: '{{ __("currency.are_you_sure") }}',
                text: '{{ __("currency.delete_warning") }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: '{{ __("currency.yes_delete") }}',
                cancelButtonText: '{{ __("currency.cancel") }}',
                customClass: {
                    popup: 'rounded-2xl shadow-xl border border-slate-100',
                    title: 'text-slate-800 font-bold',
                    content: 'text-slate-500'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('delete-form');
                    form.action = "{{ route('currency.destroy', ':id') }}".replace(':id', id);
                    form.submit();
                }
            })
        }

        function removeVisualRow(button) {
            button.closest('tr').remove();
            // Optional: If you want to check if the form is still dirty after removing a new row,
            // you might need more complex logic, but usually keeping hasChanges=true is safer.
        }
    </script>
</x-app-layout>