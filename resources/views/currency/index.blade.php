<x-app-layout>
    <x-slot name="header">
        {{ __('currency.config_title') }}
    </x-slot>

    <style>
        .sheet-input { 
            width: 100%; background: transparent; border: 1px solid transparent; 
            padding: 6px; border-radius: 4px; outline: none; transition: all 0.2s; font-size: 0.9rem;
        }
        .sheet-input:focus { background-color: white; border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2); }
        .sheet-input:hover { background-color: #f8fafc; border-color: #e2e8f0; }

        @media print {
            @page { size: A4 portrait; margin: 5mm; }
            aside, nav, header, .print\:hidden, button, .no-print, th:last-child, td:last-child { display: none !important; }
            body { background: white !important; }
            table { width: 100% !important; border-collapse: collapse !important; font-size: 8pt !important; }
            th, td { border: 1px solid #000 !important; padding: 3px 2px !important; text-align: center !important; }
            #print-header { display: block !important; text-align: center; border-bottom: 2px solid #000; margin-bottom: 10px; }
        }
        #print-header { display: none; }
    </style>

    <div x-data="{ hasChanges: false }" class="py-6">
        <div id="print-header">
            <h1 class="text-xl font-bold uppercase">{{ config('app.name') }}</h1>
            <h2 class="text-sm">{{ __('currency.config_title') }}</h2>
            <p class="text-[10px] text-gray-500">{{ date('Y-m-d H:i') }} | {{ Auth::user()->name }}</p>
        </div>

        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 print:hidden px-4">
            <h3 class="text-lg font-bold text-slate-700">{{ __('currency.config_title') }}</h3>
            
            <div class="flex flex-wrap items-center gap-2">
                <button onclick="window.print()" class="bg-slate-700 text-white px-3 py-2 rounded-lg text-xs font-bold hover:bg-slate-800 transition">
                    {{ __('currency.print') ?? 'Print' }}
                </button>
                <button type="button" @click="addNewRow(); hasChanges = true" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-xs font-bold shadow-lg shadow-blue-500/30">
                    {{ __('currency.add_new') }}
                </button>
                <button type="button" 
                        @click="hasChanges ? document.getElementById('sheet-form').submit() : Swal.fire({icon: 'info', title: '{{ __('currency.no_changes') ?? 'No changes' }}'})"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-xs font-bold shadow-lg shadow-emerald-500/30">
                    {{ __('currency.save_changes') }}
                </button>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm mx-4">
            <form id="sheet-form" action="{{ route('currency.store') }}" method="POST">
                @csrf
                <table class="w-full text-sm text-left text-slate-500" @input="hasChanges = true" @change="hasChanges = true">
                    <thead class="text-xs text-slate-700 uppercase bg-blue-100/50 border-b border-blue-200">
                        <tr>
                            <th class="px-2 py-3 w-[5%] text-center">#</th>
                            <th class="px-2 py-3 w-[20%]">{{ __('currency.type') }}</th>
                            <th class="px-2 py-3 w-[10%] text-center">{{ __('currency.symbol') }}</th>
                            <th class="px-2 py-3 w-[10%] text-center">{{ __('currency.digit') }}</th>
                            <th class="px-2 py-3 w-[15%] text-center bg-slate-50">{{ __('currency.price_total') }}</th>
                            <th class="px-2 py-3 w-[15%] text-center bg-slate-50">{{ __('currency.price_single') }}</th>
                            <th class="px-2 py-3 w-[15%]">{{ __('currency.branch') }}</th>
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
                            <td class="px-2 py-2"><input type="text" name="currencies[{{ $index }}][currency_type]" value="{{ $currency->currency_type }}" class="sheet-input font-bold text-slate-800 uppercase"></td>
                            <td class="px-2 py-2"><input type="text" name="currencies[{{ $index }}][symbol]" value="{{ $currency->symbol }}" class="sheet-input text-center"></td>
                            <td class="px-2 py-2"><input type="number" name="currencies[{{ $index }}][digit_number]" value="{{ $currency->digit_number }}" class="sheet-input text-center"></td>
                            <td class="px-2 py-2 bg-slate-50/50"><input type="number" step="0.001" name="currencies[{{ $index }}][price_total]" value="{{ $currency->price_total }}" class="sheet-input text-center"></td>
                            <td class="px-2 py-2 bg-slate-50/50"><input type="number" step="0.001" name="currencies[{{ $index }}][price_single]" value="{{ $currency->price_single }}" class="sheet-input text-center"></td>
                            <td class="px-2 py-2"><input type="text" name="currencies[{{ $index }}][branch]" value="{{ $currency->branch }}" class="sheet-input"></td>
                            <td class="px-2 py-2 text-center">
                                <input type="checkbox" name="currencies[{{ $index }}][is_active]" value="1" {{ $currency->is_active ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded">
                            </td>
                            <td class="px-2 py-2 text-center print:hidden">
                                <button type="button" onclick="deleteDatabaseRow({{ $currency->id }})" class="p-1.5 text-red-600 hover:bg-red-50 rounded transition">
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

    <form id="delete-form" action="" method="POST" class="hidden">@csrf @method('DELETE')</form>

    <script>
        function addNewRow() {
            const tableBody = document.getElementById('sheet-body');
            const index = Date.now(); 
            const rowHtml = `
                <tr class="bg-blue-50/50">
                    <td class="px-2 py-2 text-center"><span class="bg-green-100 text-green-700 py-0.5 px-2 rounded text-[10px] font-bold">NEW</span></td>
                    <td class="px-2 py-2"><input type="text" name="currencies[${index}][currency_type]" class="sheet-input font-bold uppercase" autofocus></td>
                    <td class="px-2 py-2"><input type="text" name="currencies[${index}][symbol]" class="sheet-input text-center"></td>
                    <td class="px-2 py-2"><input type="number" name="currencies[${index}][digit_number]" value="0" class="sheet-input text-center"></td>
                    <td class="px-2 py-2"><input type="number" step="0.001" name="currencies[${index}][price_total]" value="0" class="sheet-input text-center"></td>
                    <td class="px-2 py-2"><input type="number" step="0.001" name="currencies[${index}][price_single]" value="0" class="sheet-input text-center"></td>
                    <td class="px-2 py-2"><input type="text" name="currencies[${index}][branch]" class="sheet-input"></td>
                    <td class="px-2 py-2 text-center"><input type="checkbox" name="currencies[${index}][is_active]" value="1" checked class="w-4 h-4 text-indigo-600 rounded"></td>
                    <td class="px-2 py-2 text-center print:hidden">
                        <button type="button" onclick="removeVisualRow(this)" class="p-1.5 text-slate-400 hover:text-red-500 rounded"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </td>
                </tr>`;
            tableBody.insertAdjacentHTML('beforeend', rowHtml);
        }

        function deleteDatabaseRow(id) {
            const form = document.getElementById('delete-form');
            form.action = "{{ route('currency.destroy', ':id') }}".replace(':id', id);
            // Calls the Global confirmation from app.blade.php
            window.confirmAction('delete-form', "{{ app()->getLocale() == 'ku' ? 'دڵنیایت لە سڕینەوە؟' : 'Are you sure you want to delete?' }}");
        }

        function removeVisualRow(button) { button.closest('tr').remove(); }
    </script>
</x-app-layout>