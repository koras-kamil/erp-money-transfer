<x-app-layout>
    <x-slot name="header">
        {{ __('profit.groups_title') }}
    </x-slot>

    {{-- EXACT SAME SPREADSHEET STYLES FROM CURRENCY PAGE --}}
    <style>
        table { border-collapse: separate; border-spacing: 0; width: 100%; }
        tr { height: 50px; }
        td { border-bottom: 1px solid #e2e8f0; background-color: white; padding: 0; vertical-align: middle; }
        td:not(:last-child) { border-inline-end: 1px solid #f1f5f9; }
        .sheet-input { width: 100%; height: 100%; display: flex; align-items: center; background: transparent; border: none; outline: none; padding: 0 10px; font-size: 0.9rem; color: #1e293b; font-weight: 600; transition: all 0.1s; }
        .sheet-input:focus { background-color: #fff; box-shadow: inset 0 0 0 2px #6366f1; z-index: 5; position: relative; }
        tr:hover td { background-color: #f8fafc; }
        .sticky-col { position: sticky; inset-inline-start: 0; z-index: 10; background-color: #f8fafc; border-inline-end: 2px solid #e2e8f0; }
        tr:hover .sticky-col { background-color: #f1f5f9; }
        @media print { .no-print, button, .print\:hidden { display: none !important; } .overflow-x-auto { overflow: visible !important; } table { width: 100% !important; } }
    </style>

    <div x-data="{ hasChanges: false }" class="py-6" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

        {{-- ACTIONS TOOLBAR --}}
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 px-4 no-print">
            <h3 class="text-xl font-black text-slate-800 tracking-tight">{{ __('profit.groups_title') }}</h3>
            <div class="flex flex-wrap items-center gap-2">
                {{-- Add New --}}
                <button type="button" @click="addNewRow(); hasChanges = true" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-xs font-bold shadow-lg shadow-blue-500/30">
                    {{ __('profit.add_group') }}
                </button>
                {{-- Save --}}
                <button type="button" 
                        @click="hasChanges ? document.getElementById('sheet-form').submit() : Swal.fire({icon: 'info', title: '{{ __('currency.no_changes') ?? 'No changes' }}'})"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-xs font-bold shadow-lg shadow-emerald-500/30">
                    {{ __('profit.save_changes') }}
                </button>
            </div>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="overflow-x-auto pb-4 rounded-xl border border-slate-200 bg-white shadow-sm mx-4">
            <form id="sheet-form" action="{{ route('profit.groups.store') }}" method="POST">
                @csrf
                <table class="w-full min-w-[800px] text-sm text-left text-slate-500">
                    <thead class="text-xs text-slate-700 uppercase bg-slate-100 border-b-2 border-slate-200 h-12">
                        <tr>
                            <th class="w-[60px] text-center sticky-col border-r border-slate-200">#</th>
                            <th class="min-w-[200px] px-2">{{ __('profit.name') }}</th>
                            <th class="min-w-[300px] px-2">{{ __('profit.description') }}</th>
                            <th class="w-[80px] text-center">{{ __('profit.active') }}</th>
                            <th class="w-[60px] text-center print:hidden">{{ __('profit.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="sheet-body">
                        @foreach($groups as $index => $group)
                        <tr class="group transition-colors">
                            <td class="text-center font-bold text-slate-400 sticky-col border-r border-slate-100">
                                {{ $loop->iteration }}
                                <input type="hidden" name="groups[{{ $index }}][id]" value="{{ $group->id }}">
                            </td>
                            <td><input type="text" name="groups[{{ $index }}][name]" value="{{ $group->name }}" class="sheet-input font-bold text-slate-800" placeholder="{{ __('profit.name') }}"></td>
                            <td><input type="text" name="groups[{{ $index }}][description]" value="{{ $group->description }}" class="sheet-input" placeholder="..."></td>
                            <td class="text-center bg-slate-50/30">
                                <div class="flex items-center justify-center h-full">
                                    <input type="checkbox" name="groups[{{ $index }}][is_active]" value="1" {{ $group->is_active ? 'checked' : '' }} class="w-5 h-5 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer">
                                </div>
                            </td>
                            <td class="text-center print:hidden bg-slate-50/30">
                                <div class="flex items-center justify-center h-full">
                                    <button type="button" onclick="deleteRow({{ $group->id }})" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
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
            const index = Date.now();
            const row = `
                <tr class="group bg-blue-50/20 hover:bg-blue-50 transition-colors animate-pulse-once">
                    <td class="text-center sticky-col border-r border-slate-100"><span class="bg-green-100 text-green-700 py-0.5 px-2 rounded text-[10px] font-black">NEW</span></td>
                    <td><input type="text" name="groups[${index}][name]" class="sheet-input font-bold" placeholder="{{ __('profit.name') }}" autofocus></td>
                    <td><input type="text" name="groups[${index}][description]" class="sheet-input" placeholder="..."></td>
                    <td class="text-center bg-slate-50/30"><div class="flex items-center justify-center h-full"><input type="checkbox" name="groups[${index}][is_active]" value="1" checked class="w-5 h-5 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer"></div></td>
                    <td class="text-center print:hidden bg-slate-50/30"><div class="flex items-center justify-center h-full"><button type="button" onclick="this.closest('tr').remove()" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div></td>
                </tr>`;
            document.getElementById('sheet-body').insertAdjacentHTML('beforeend', row);
        }
        function deleteRow(id) {
            const form = document.getElementById('delete-form');
            form.action = "{{ route('profit.groups.destroy', ':id') }}".replace(':id', id);
            window.confirmAction('delete-form', "{{ __('profit.delete_confirm') }}");
        }
    </script>
</x-app-layout>