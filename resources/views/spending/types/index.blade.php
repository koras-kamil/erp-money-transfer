<x-app-layout>
    <x-slot name="header">
        {{-- TAB NAVIGATION --}}
        <div class="text-sm font-medium text-center text-slate-500 border-b border-slate-200">
            <ul class="flex flex-wrap -mb-px">
                <li class="me-2">
                    <a href="{{ route('group-spending.index') }}" 
                       class="inline-block p-4 border-b-2 rounded-t-lg transition-colors {{ request()->routeIs('group-spending.*') ? 'text-indigo-600 border-indigo-600 active' : 'border-transparent hover:text-slate-600 hover:border-slate-300' }}">
                        {{ __('spending.group_title') }}
                    </a>
                </li>
                <li class="me-2">
                    <a href="{{ route('type-spending.index') }}" 
                       class="inline-block p-4 border-b-2 rounded-t-lg transition-colors {{ request()->routeIs('type-spending.*') ? 'text-indigo-600 border-indigo-600 active' : 'border-transparent hover:text-slate-600 hover:border-slate-300' }}">
                        {{ __('spending.type_header') }}
                    </a>
                </li>
            </ul>
        </div>
    </x-slot>

    {{-- STYLES --}}
    <style>
        .sheet-input {
            width: 100%; height: 100%; display: flex; align-items: center;
            background: transparent; border: 1px solid transparent; padding: 0 10px;
            font-size: 0.875rem; color: #1f2937; font-weight: 500; border-radius: 6px;
            transition: all 0.15s ease-in-out;
        }
        .sheet-input:focus {
            background-color: #fff; border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1); outline: none;
        }
        .sheet-input[readonly] { color: #9ca3af; cursor: default; }
        
        select.sheet-input {
            -webkit-appearance: none; appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.2em 1.2em;
            padding-right: 2.5rem; padding-left: 0.75rem; cursor: pointer; white-space: nowrap; text-overflow: ellipsis;
        }
        [dir="rtl"] select.sheet-input {
            background-position: left 0.5rem center; padding-right: 0.75rem; padding-left: 2.5rem;
        }
        select.sheet-input.locked { pointer-events: none; color: #6b7280; background-image: none; padding: 0 10px; }

        @media print {
            .no-print, button, .print\:hidden { display: none !important; }
            .overflow-x-auto { overflow: visible !important; }
            table { width: 100% !important; }
        }
    </style>

    <div x-data="{ 
        hasChanges: false,
        editingId: null,
        cols: { id: true, code: true, name: true, group: true, acc_code: true, note: true, branch: true, user: true, actions: true },
        toggleAll(value) { for (let key in this.cols) { this.cols[key] = value; } },
        startEdit(id) { 
            this.editingId = id; 
            this.hasChanges = true;
            setTimeout(() => { document.getElementById('input-name-'+id)?.focus(); }, 100);
        }
    }" class="py-6" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

        {{-- ACTIONS TOOLBAR --}}
        <div class="flex flex-col md:flex-row justify-end items-center mb-6 gap-4 px-4 no-print">
            <div class="flex flex-wrap items-center gap-3">
                
                {{-- TRASH --}}
                <a href="{{ route('type-spending.trash') }}" title="{{ __('spending.trash') }}" 
                   class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-50 text-red-500 border border-red-100 hover:bg-red-100 hover:text-red-600 transition shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </a>

                {{-- MANAGE COLUMNS --}}
                <div x-data="{ openDropdown: false }" class="relative">
                    <button @click="openDropdown = !openDropdown" @click.away="openDropdown = false" title="{{ __('cash_box.manage_view') }}" 
                            class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                    <div x-show="openDropdown" class="absolute ltr:right-0 rtl:left-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 z-50 p-2" x-cloak>
                        <div class="flex gap-2 mb-2 pb-2 border-b border-slate-100">
                            <button @click="toggleAll(true)" class="flex-1 text-[10px] bg-indigo-50 text-indigo-600 py-1 rounded hover:bg-indigo-100 font-bold">ALL</button>
                            <button @click="toggleAll(false)" class="flex-1 text-[10px] bg-slate-50 text-slate-600 py-1 rounded hover:bg-slate-100 font-bold">NONE</button>
                        </div>
                        <div class="space-y-1 max-h-60 overflow-y-auto">
                            @foreach(['id'=>'#', 'code'=>'Code', 'name'=>'Name', 'group'=>'Group', 'acc_code'=>'Accountant', 'note'=>'Note', 'branch'=>'Branch', 'user'=>'User'] as $key => $label)
                                <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 rounded cursor-pointer"><input type="checkbox" x-model="cols.{{ $key }}" class="rounded text-indigo-600 w-4 h-4 border-slate-300"><span class="text-xs text-slate-700 font-medium">{{ $label }}</span></label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- PRINT PDF (Fixed) --}}
                <a href="{{ route('type-spending.print') }}" target="_blank" title="{{ __('spending.print') }}" 
                   class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-700 text-white hover:bg-slate-800 transition shadow-sm shadow-slate-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                </a>

                {{-- ADD NEW --}}
                <button type="button" @click="addNewRow(); hasChanges = true" title="{{ __('spending.add_new') }}" 
                        class="w-10 h-10 flex items-center justify-center rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition shadow-lg shadow-blue-500/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>

                {{-- SAVE --}}
                <button type="button" title="{{ __('spending.save') }}"
                        @click="hasChanges ? document.getElementById('sheet-form').submit() : Swal.fire({icon: 'info', title: '{{ __('spending.no_changes') }}'})"
                        class="w-10 h-10 flex items-center justify-center rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-lg shadow-emerald-500/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </button>
            </div>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="relative overflow-x-auto bg-white shadow-sm rounded-lg border border-gray-200 mx-4">
            <form id="sheet-form" action="{{ route('type-spending.store') }}" method="POST">
                @csrf
                <table class="w-full min-w-[1000px] text-sm text-left rtl:text-right text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th x-show="cols.id" class="px-6 py-3 font-medium w-16">#</th>
                            <th x-show="cols.code" class="px-6 py-3 font-medium min-w-[100px]">{{ __('spending.code') }}</th>
                            <th x-show="cols.name" class="px-6 py-3 font-medium min-w-[150px]">{{ __('spending.name') }}</th>
                            <th x-show="cols.group" class="px-6 py-3 font-medium min-w-[150px]">{{ __('spending.group_title') }}</th>
                            <th x-show="cols.acc_code" class="px-6 py-3 font-medium text-center min-w-[120px]">{{ __('spending.accountant_code') }}</th>
                            <th x-show="cols.note" class="px-6 py-3 font-medium min-w-[150px]">{{ __('spending.note') }}</th>
                            <th x-show="cols.branch" class="px-6 py-3 font-medium min-w-[150px]">{{ __('spending.branch') }}</th>
                            <th x-show="cols.user" class="px-6 py-3 font-medium text-center w-24 print:hidden">{{ __('spending.created_by') }}</th>
                            <th x-show="cols.actions" class="px-6 py-3 font-medium text-center w-28 print:hidden">{{ __('spending.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="sheet-body">
                        @foreach($types as $index => $type)
                        <tr class="bg-white border-b border-gray-100 hover:bg-gray-50 transition-colors"
                            :class="editingId === {{ $type->id }} ? 'bg-indigo-50/20' : ''">
                            
                            {{-- ID --}}
                            <td x-show="cols.id" class="px-6 py-4 font-medium whitespace-nowrap text-gray-900">
                                {{ $loop->iteration }}
                                <input type="hidden" name="types[{{ $index }}][id]" value="{{ $type->id }}">
                            </td>
                            
                            {{-- Code --}}
                            <td x-show="cols.code" class="p-1">
                                <input type="text" name="types[{{ $index }}][code]" value="{{ $type->code }}" class="sheet-input font-bold uppercase" readonly>
                            </td>

                            {{-- Name --}}
                            <td x-show="cols.name" class="p-1">
                                <span x-show="editingId !== {{ $type->id }}" class="px-3 block text-gray-700 font-medium">{{ $type->name }}</span>
                                <input x-show="editingId === {{ $type->id }}" id="input-name-{{ $type->id }}" type="text" name="types[{{ $index }}][name]" value="{{ $type->name }}" class="sheet-input font-bold">
                            </td>

                            {{-- Group Dropdown --}}
                            <td x-show="cols.group" class="p-1">
                                <span x-show="editingId !== {{ $type->id }}" class="px-3 block text-gray-600">{{ $type->group->name ?? '-' }}</span>
                                <select x-show="editingId === {{ $type->id }}" name="types[{{ $index }}][group_spending_id]" class="sheet-input">
                                    @foreach($activeGroups as $g)
                                        <option value="{{ $g->id }}" {{ $type->group_spending_id == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                                    @endforeach
                                </select>
                            </td>

                            {{-- Accountant Code --}}
                            <td x-show="cols.acc_code" class="p-1">
                                <span x-show="editingId !== {{ $type->id }}" class="block text-center text-gray-600">{{ $type->accountant_code }}</span>
                                <input x-show="editingId === {{ $type->id }}" type="text" name="types[{{ $index }}][accountant_code]" value="{{ $type->accountant_code }}" class="sheet-input text-center">
                            </td>

                            {{-- Note --}}
                            <td x-show="cols.note" class="p-1">
                                <span x-show="editingId !== {{ $type->id }}" class="px-3 block text-gray-500 text-xs truncate">{{ $type->note }}</span>
                                <input x-show="editingId === {{ $type->id }}" type="text" name="types[{{ $index }}][note]" value="{{ $type->note }}" class="sheet-input text-xs">
                            </td>

                            {{-- Branch --}}
                            <td x-show="cols.branch" class="p-1">
                                <span x-show="editingId !== {{ $type->id }}" class="px-3 block text-gray-600">{{ $type->branch->name ?? '-' }}</span>
                                <select x-show="editingId === {{ $type->id }}" name="types[{{ $index }}][branch_id]" class="sheet-input">
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ $type->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </td>

                            {{-- User --}}
                            <td x-show="cols.user" class="px-6 py-4 text-[10px] text-center text-gray-400 font-medium">
                                {{ $type->creator->name ?? '-' }}
                            </td>

                            {{-- Actions --}}
                            <td x-show="cols.actions" class="px-6 py-4 text-center print:hidden">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" @click="startEdit({{ $type->id }})" 
                                            x-show="editingId !== {{ $type->id }}"
                                            class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <span x-show="editingId === {{ $type->id }}" class="text-emerald-500 animate-pulse">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                    <button type="button" onclick="deleteDatabaseRow({{ $type->id }})" 
                                            class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
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
            const userBranchId = "{{ Auth::user()->branch_id }}"; // Auto Select User Branch

            let groupOptions = '<option value="" disabled selected>{{ __('spending.select_group') }}</option>';
            @foreach($activeGroups as $g) groupOptions += '<option value="{{ $g->id }}">{{ $g->name }}</option>'; @endforeach

            // AUTO SELECT BRANCH
            let branchOptions = '<option value="" disabled>{{ __('spending.select_branch') }}</option>';
            @foreach($branches as $branch) 
                branchOptions += `<option value="{{ $branch->id }}" ${ "{{ $branch->id }}" == userBranchId ? 'selected' : '' }>{{ $branch->name }}</option>`; 
            @endforeach

            const rowHtml = `
            <tr class="bg-blue-50/40 border-b border-blue-100 transition-all duration-300">
                <td x-show="cols.id" class="px-6 py-4 font-medium text-slate-400 text-center">
                    <span class="bg-blue-100 text-blue-600 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider">NEW</span>
                </td>
                <td x-show="cols.code" class="p-1"><input type="text" value="AUTO" class="sheet-input font-bold uppercase text-slate-400 cursor-not-allowed" readonly></td>
                <td x-show="cols.name" class="p-1"><input type="text" name="types[${index}][name]" class="sheet-input font-bold" placeholder="{{ __('spending.name') }}" autofocus></td>
                <td x-show="cols.group" class="p-1"><select name="types[${index}][group_spending_id]" class="sheet-input text-gray-700">${groupOptions}</select></td>
                <td x-show="cols.acc_code" class="p-1"><input type="text" name="types[${index}][accountant_code]" class="sheet-input text-center"></td>
                <td x-show="cols.note" class="p-1"><input type="text" name="types[${index}][note]" class="sheet-input text-xs"></td>
                <td x-show="cols.branch" class="p-1"><select name="types[${index}][branch_id]" class="sheet-input text-gray-700">${branchOptions}</select></td>
                <td x-show="cols.user" class="px-6 py-4 text-[10px] text-center text-slate-400 italic">{{ Auth::user()->name }}</td>
                <td x-show="cols.actions" class="px-6 py-4 text-center print:hidden">
                    <button type="button" onclick="this.closest('tr').remove()" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </td>
            </tr>`;
            tableBody.insertAdjacentHTML('beforeend', rowHtml);
        }

        function deleteDatabaseRow(id) {
            const form = document.getElementById('delete-form');
            form.action = "{{ route('type-spending.destroy', ':id') }}".replace(':id', id);
            window.confirmAction('delete-form', "{{ __('spending.delete_confirm') }}");
        }
    </script>
</x-app-layout>