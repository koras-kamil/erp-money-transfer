<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('currency.config_title') }}
        </h2>
    </x-slot>

    <style>
        .sheet-input { width: 100%; height: 100%; background: transparent; border: none; padding: 8px; outline: none; transition: all 0.2s; }
        .sheet-input:focus { background-color: #f0f9ff; box-shadow: inset 0 0 0 2px #3b82f6; }
        thead th { position: sticky; top: 0; z-index: 10; }
        .ghost-row { opacity: 0.6; }
        .ghost-row:hover { opacity: 1; background-color: #f8fafc; }
        /* Custom SweetAlert Font for Kurdish */
        .swal2-popup { font-family: inherit; }
    </style>

    <div class="py-6">
        <div class="max-w-full mx-auto bg-white shadow-xl rounded-lg overflow-hidden border border-slate-300">
            
            <div class="p-4 bg-slate-800 text-white flex justify-between items-center">
                <h2 class="text-lg font-bold">{{ __('currency.config_title') }}</h2>
                <div>
                    <button type="button" onclick="addNewRow()" class="bg-indigo-500 hover:bg-indigo-600 px-4 py-2 rounded text-sm font-bold transition">
                        {{ __('currency.add_new') }}
                    </button>
                    <button type="submit" form="sheet-form" class="bg-emerald-500 hover:bg-emerald-600 px-4 py-2 rounded text-sm font-bold transition ml-2">
                        {{ __('currency.save_changes') }}
                    </button>
                </div>
            </div>

            <form id="sheet-form" action="{{ route('currency.store') }}" method="POST">
                @csrf
                <div class="overflow-x-auto max-h-[70vh]">
                    <table class="w-full border-collapse text-sm text-right">
                        <thead class="bg-slate-200 text-slate-700">
                            <tr>
                                <th class="border border-slate-300 p-2 min-w-[50px] text-center">{{ __('currency.id') }}</th>
                                <th class="border border-slate-300 p-2 min-w-[120px]">{{ __('currency.type') }}</th>
                                <th class="border border-slate-300 p-2 min-w-[80px]">{{ __('currency.symbol') }}</th>
                                <th class="border border-slate-300 p-2 min-w-[80px]">{{ __('currency.digit') }}</th>
                                <th class="border border-slate-300 p-2 min-w-[100px] bg-blue-50">{{ __('currency.price_total') }}</th>
                                <th class="border border-slate-300 p-2 min-w-[100px] bg-blue-50">{{ __('currency.price_single') }}</th>
                                <th class="border border-slate-300 p-2 min-w-[100px] bg-blue-50">{{ __('currency.price_sell') }}</th>
                                <th class="border border-slate-300 p-2 min-w-[120px]">{{ __('currency.branch') }}</th>
                                <th class="border border-slate-300 p-2 min-w-[60px] text-center">{{ __('currency.active') }}</th>
                                <th class="border border-slate-300 p-2 min-w-[80px] text-center">{{ __('currency.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="sheet-body">
                            @foreach($currencies as $index => $currency)
                            <tr class="hover:bg-slate-50 group transition-colors duration-200">
                                <td class="border border-slate-300 bg-slate-100 text-center text-slate-500">
                                    {{ $currency->id }}
                                    <input type="hidden" name="currencies[{{ $index }}][id]" value="{{ $currency->id }}">
                                </td>
                                <td class="border border-slate-300 p-0"><input type="text" name="currencies[{{ $index }}][currency_type]" value="{{ $currency->currency_type }}" class="sheet-input font-bold text-slate-800"></td>
                                <td class="border border-slate-300 p-0"><input type="text" name="currencies[{{ $index }}][symbol]" value="{{ $currency->symbol }}" class="sheet-input text-center"></td>
                                <td class="border border-slate-300 p-0"><input type="number" name="currencies[{{ $index }}][digit_number]" value="{{ $currency->digit_number }}" class="sheet-input text-center"></td>
                                <td class="border border-slate-300 p-0 bg-blue-50/30"><input type="number" step="0.001" name="currencies[{{ $index }}][price_total]" value="{{ $currency->price_total }}" class="sheet-input"></td>
                                <td class="border border-slate-300 p-0 bg-blue-50/30"><input type="number" step="0.001" name="currencies[{{ $index }}][price_single]" value="{{ $currency->price_single }}" class="sheet-input"></td>
                                <td class="border border-slate-300 p-0 bg-blue-50/30"><input type="number" step="0.001" name="currencies[{{ $index }}][price_sell]" value="{{ $currency->price_sell }}" class="sheet-input font-bold text-emerald-600"></td>
                                <td class="border border-slate-300 p-0"><input type="text" name="currencies[{{ $index }}][branch]" value="{{ $currency->branch }}" class="sheet-input"></td>
                                <td class="border border-slate-300 text-center"><input type="checkbox" name="currencies[{{ $index }}][is_active]" value="1" {{ $currency->is_active ? 'checked' : '' }} class="w-4 h-4 accent-indigo-600 cursor-pointer"></td>
                                
                                <td class="border border-slate-300 text-center p-1 bg-slate-50">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" onclick="deleteDatabaseRow({{ $currency->id }})" class="p-1.5 text-red-500 hover:bg-red-100 rounded transition" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>

    <form id="delete-form" action="" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() { addGhostRow(); });

        function addNewRow() {
            const lastRow = document.querySelector('.ghost-row');
            if(lastRow) { activateRow(lastRow.querySelector('input')); lastRow.querySelector('input').focus(); } 
            else { addGhostRow(); }
        }

        // Updated Function: Delete Row using SweetAlert2
        function deleteDatabaseRow(id) {
            Swal.fire({
                title: '{{ __("currency.are_you_sure") }}',
                text: '{{ __("currency.delete_warning") }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: '{{ __("currency.yes_delete") }}',
                cancelButtonText: '{{ __("currency.cancel") }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('delete-form');
                    form.action = "{{ route('currency.destroy', ':id') }}".replace(':id', id);
                    form.submit();
                }
            })
        }

        // Function: Remove Visual Row (No Alert needed for empty rows)
        function removeVisualRow(button) {
            button.closest('tr').remove();
        }

        function addGhostRow() {
            const tableBody = document.getElementById('sheet-body');
            const index = Date.now();
            const rowHtml = `
                <tr class="ghost-row transition-opacity duration-300 bg-slate-50/50">
                    <td class="border border-slate-300 bg-slate-50 text-center text-slate-300 text-xs select-none">+</td>
                    <td class="border border-slate-300 p-0"><input type="text" onfocus="activateRow(this)" name="currencies[${index}][currency_type]" placeholder="..." class="sheet-input font-bold text-slate-600"></td>
                    <td class="border border-slate-300 p-0"><input type="text" onfocus="activateRow(this)" name="currencies[${index}][symbol]" class="sheet-input text-center"></td>
                    <td class="border border-slate-300 p-0"><input type="number" onfocus="activateRow(this)" name="currencies[${index}][digit_number]" value="0" class="sheet-input text-center"></td>
                    <td class="border border-slate-300 p-0 bg-blue-50/10"><input type="number" step="0.001" onfocus="activateRow(this)" name="currencies[${index}][price_total]" value="0" class="sheet-input"></td>
                    <td class="border border-slate-300 p-0 bg-blue-50/10"><input type="number" step="0.001" onfocus="activateRow(this)" name="currencies[${index}][price_single]" value="0" class="sheet-input"></td>
                    <td class="border border-slate-300 p-0 bg-blue-50/10"><input type="number" step="0.001" onfocus="activateRow(this)" name="currencies[${index}][price_sell]" value="0" class="sheet-input font-bold text-emerald-600"></td>
                    <td class="border border-slate-300 p-0"><input type="text" onfocus="activateRow(this)" name="currencies[${index}][branch]" class="sheet-input"></td>
                    <td class="border border-slate-300 text-center"><input type="checkbox" onfocus="activateRow(this)" name="currencies[${index}][is_active]" value="1" checked class="w-4 h-4 accent-indigo-600 cursor-pointer"></td>
                    
                    <td class="border border-slate-300 text-center p-1">
                        <div class="flex items-center justify-center gap-2 opacity-50">
                            <button type="button" onclick="removeVisualRow(this)" class="p-1.5 text-red-500 hover:bg-red-100 rounded transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                    </td>
                </tr>`;
            tableBody.insertAdjacentHTML('beforeend', rowHtml);
        }

        function activateRow(inputElement) {
            const row = inputElement.closest('tr');
            if (!row.classList.contains('ghost-row')) return;
            row.classList.remove('ghost-row', 'bg-slate-50/50');
            row.classList.add('bg-white');
            
            const idCell = row.querySelector('td:first-child');
            idCell.textContent = "{{ __('currency.new') }}";
            idCell.classList.remove('text-slate-300');
            idCell.classList.add('text-green-500', 'font-bold');
            
            const actionDiv = row.querySelector('td:last-child div');
            actionDiv.classList.remove('opacity-50');
            
            const inputs = row.querySelectorAll('input');
            inputs.forEach(input => input.removeAttribute('onfocus'));
            addGhostRow();
        }
    </script>
</x-app-layout>