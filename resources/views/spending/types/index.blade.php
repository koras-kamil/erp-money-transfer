<x-app-layout>
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

        /* Readonly State */
        .sheet-input[readonly] {
            cursor: default;
            color: #64748b;
        }

        /* --- FIXED DROPDOWN STYLING --- */
        select.sheet-input, .sheet-select {
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
            width: 100%;
            border: 1px solid transparent; 
            padding-top: 6px;
            padding-bottom: 6px;
            border-radius: 4px; 
            outline: none; 
            transition: all 0.2s; 
            font-size: 0.9rem;
        }
        
        select.sheet-input:focus, .sheet-select:focus {
            background-color: white; 
            border-color: #6366f1; 
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
        }

        /* RTL Support */
        [dir="rtl"] select.sheet-input, [dir="rtl"] .sheet-select {
            background-position: left 0.5rem center;
            padding-right: 0.75rem;
            padding-left: 2.5rem; 
        }

        @media print {
            .no-print, button, .print\:hidden { display: none !important; }
        }
    </style>

    {{-- Main Wrapper --}}
    <div x-data="{ 
        hasChanges: false,
        editingId: null,
        cols: { id: true, code: true, name: true, group: true, acc_code: true, note: true, branch: true, user: true, actions: true },
        toggleAll(value) { for (let key in this.cols) { this.cols[key] = value; } },
        startEdit(id) { 
            this.editingId = id; 
            setTimeout(() => { document.getElementById('input-name-'+id)?.focus(); }, 100);
        },
        saveRow() {
            document.getElementById('sheet-form').submit();
        }
    }" class="py-6 w-full min-w-0" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

        {{-- UNIFIED TOOLBAR --}}
        <div class="mx-4 mb-4 bg-white p-3 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4 no-print">

            {{-- 1. TABS --}}
            <div class="bg-slate-100 p-1 rounded-lg flex items-center">
                <a href="{{ route('group-spending.index') }}" 
                   class="px-4 py-2 text-sm font-bold rounded-md transition-all {{ request()->routeIs('group-spending.*') ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    {{ __('spending.group_title') }}
                </a>
                <a href="{{ route('type-spending.index') }}" 
                   class="px-4 py-2 text-sm font-bold rounded-md transition-all {{ request()->routeIs('type-spending.*') ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    {{ __('spending.type_header') }}
                </a>
            </div>

            {{-- 2. TOOLS --}}
            <div class="flex flex-wrap items-center gap-2">
                {{-- Trash --}}
                <a href="{{ route('type-spending.trash') }}" class="w-9 h-9 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition border border-red-100" title="{{ __('spending.trash') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </a>

                {{-- Manage Columns (ICON CHANGED TO COG) --}}
                <div x-data="{ openDropdown: false }" class="relative">
                    <button @click="openDropdown = !openDropdown" @click.away="openDropdown = false" class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-50 text-slate-600 border border-slate-200 hover:bg-white hover:text-indigo-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.572 1.065c-1.543.94-3 .888-8 .888s-.888-.888-.888-.888z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </button>
                    <div x-show="openDropdown" class="absolute top-full mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 z-50 p-2 ltr:right-0 rtl:left-0" x-cloak>
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

                {{-- Print --}}
                <a href="{{ route('type-spending.print') }}" target="_blank" class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-700 text-white hover:bg-slate-800 transition shadow-md shadow-slate-300/50" title="{{ __('spending.print') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                </a>

                {{-- Add New --}}
                <button type="button" @click="addNewRow()" class="w-9 h-9 flex items-center justify-center rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition" title="{{ __('spending.add_new') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
            </div>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="relative w-full overflow-x-auto bg-white shadow-sm rounded-xl border border-slate-200 mx-4 max-w-[calc(100vw-2rem)]">
            <form id="sheet-form" action="{{ route('type-spending.store') }}" method="POST">
                @csrf
                <table class="w-full text-sm text-left rtl:text-right text-slate-500" @input="hasChanges = true" @change="hasChanges = true">
                    <thead class="text-xs text-slate-700 uppercase bg-blue-50/50 border-b border-blue-100">
                        <tr>
                            <th x-show="cols.id" class="px-4 py-3 w-[5%] text-center">#</th>
                            <th x-show="cols.code" class="px-4 py-3 w-[10%]">{{ __('spending.code') }}</th>
                            <th x-show="cols.name" class="px-4 py-3 w-[15%]">{{ __('spending.name') }}</th>
                            <th x-show="cols.group" class="px-4 py-3 w-[15%]">{{ __('spending.group_title') }}</th>
                            <th x-show="cols.acc_code" class="px-4 py-3 w-[10%] text-center">{{ __('spending.accountant_code') }}</th>
                            <th x-show="cols.note" class="px-4 py-3 w-[15%]">{{ __('spending.note') }}</th>
                            <th x-show="cols.branch" class="px-4 py-3 w-[15%]">{{ __('spending.branch') }}</th>
                            <th x-show="cols.user" class="px-4 py-3 w-[10%] text-center print:hidden">{{ __('spending.created_by') }}</th>
                            <th x-show="cols.actions" class="px-4 py-3 w-[5%] text-center print:hidden">{{ __('spending.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="sheet-body" class="divide-y divide-slate-100">
                        @foreach($types as $index => $type)
                        <tr class="hover:bg-slate-50 transition-colors group/row"
                            :class="editingId === {{ $type->id }} ? 'bg-indigo-50/20' : ''">
                            
                            {{-- ID --}}
                            <td x-show="cols.id" class="px-4 py-2 text-center font-medium text-slate-400">
                                {{ $loop->iteration }}
                                <input type="hidden" name="types[{{ $index }}][id]" value="{{ $type->id }}">
                            </td>
                            
                            {{-- Code --}}
                            <td x-show="cols.code" class="p-1">
                                <input type="text" name="types[{{ $index }}][code]" value="{{ $type->code }}" class="sheet-input font-bold uppercase text-slate-500 cursor-not-allowed" readonly>
                            </td>

                            {{-- Name --}}
                            <td x-show="cols.name" class="p-1">
                                <span x-show="editingId !== {{ $type->id }}" class="px-2 block text-slate-700 font-bold">{{ $type->name }}</span>
                                <input x-show="editingId === {{ $type->id }}" id="input-name-{{ $type->id }}" type="text" name="types[{{ $index }}][name]" value="{{ $type->name }}" class="sheet-input font-bold text-slate-700">
                            </td>

                            {{-- Group Dropdown --}}
                            <td x-show="cols.group" class="p-1">
                                <span x-show="editingId !== {{ $type->id }}" class="px-2 block text-slate-600">{{ $type->group->name ?? '-' }}</span>
                                <select x-show="editingId === {{ $type->id }}" name="types[{{ $index }}][group_spending_id]" class="sheet-select">
                                    <option value="" disabled>{{ __('spending.select_group') }}</option>
                                    @foreach($groups as $g)
                                        <option value="{{ $g->id }}" {{ $type->group_spending_id == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                                    @endforeach
                                </select>
                            </td>

                            {{-- Accountant Code --}}
                            <td x-show="cols.acc_code" class="p-1">
                                <span x-show="editingId !== {{ $type->id }}" class="block text-center text-slate-600">{{ $type->accountant_code }}</span>
                                <input x-show="editingId === {{ $type->id }}" type="text" name="types[{{ $index }}][accountant_code]" value="{{ $type->accountant_code }}" class="sheet-input text-center">
                            </td>

                            {{-- Note --}}
                            <td x-show="cols.note" class="p-1">
                                <span x-show="editingId !== {{ $type->id }}" class="px-2 block text-slate-500 text-xs truncate">{{ $type->note }}</span>
                                <input x-show="editingId === {{ $type->id }}" type="text" name="types[{{ $index }}][note]" value="{{ $type->note }}" class="sheet-input text-xs">
                            </td>

                            {{-- Branch --}}
                            <td x-show="cols.branch" class="p-1">
                                <span x-show="editingId !== {{ $type->id }}" class="px-2 block text-slate-600">{{ $type->branch->name ?? '-' }}</span>
                                <select x-show="editingId === {{ $type->id }}" name="types[{{ $index }}][branch_id]" class="sheet-select">
                                    <option value="" disabled>{{ __('spending.select_branch') }}</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ $type->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </td>

                            {{-- User --}}
                            <td x-show="cols.user" class="px-4 py-2 text-center text-[10px] text-slate-400 print:hidden">
                                {{ $type->creator->name ?? '-' }}
                            </td>

                            {{-- Actions --}}
                            <td x-show="cols.actions" class="px-4 py-2 text-center print:hidden">
                                <div class="relative h-8 flex items-center justify-center w-full">
                                    {{-- MODE 1: EDITING (Show Save) --}}
                                    <div x-show="editingId === {{ $type->id }}" 
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 scale-90"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         class="absolute inset-0 flex items-center justify-center">
                                        <button type="button" @click="saveRow()" 
                                                class="w-7 h-7 flex items-center justify-center bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg shadow-sm transition transform active:scale-95" title="{{ __('spending.save') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                    </div>

                                    {{-- MODE 2: VIEWING (Show Edit/Delete) --}}
                                    <div x-show="editingId !== {{ $type->id }}" 
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 scale-90"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         class="flex items-center justify-center gap-1">
                                        <button type="button" @click="startEdit({{ $type->id }})" 
                                                class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                        <button type="button" onclick="deleteDatabaseRow({{ $type->id }})" 
                                                class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
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
            const userBranchId = "{{ Auth::user()->branch_id }}"; 

            // 1. CALCULATE NEXT ID (Current Row Count + 1)
            const rowCount = tableBody.querySelectorAll('tr').length;
            const nextId = rowCount + 1;

            let groupOptions = '<option value="" disabled selected>{{ __('spending.select_group') }}</option>';
            @foreach($groups as $g) groupOptions += '<option value="{{ $g->id }}">{{ $g->name }}</option>'; @endforeach

            let branchOptions = '<option value="" disabled>{{ __('spending.select_branch') }}</option>';
            @foreach($branches as $branch) 
                branchOptions += `<option value="{{ $branch->id }}" ${ "{{ $branch->id }}" == userBranchId ? 'selected' : '' }>{{ $branch->name }}</option>`; 
            @endforeach

            const rowHtml = `
            <tr class="bg-blue-50/40 border-b border-blue-100 transition-all duration-300">
                
                {{-- ID COLUMN --}}
                <td x-show="cols.id" class="px-4 py-2 text-center font-bold text-blue-600">
                    ${nextId}
                </td>

                {{-- CODE COLUMN --}}
                <td x-show="cols.code" class="p-1"><input type="text" value="${nextId}" class="sheet-input font-bold uppercase text-slate-400 cursor-not-allowed" readonly></td>
                
                <td x-show="cols.name" class="p-1"><input type="text" name="types[${index}][name]" class="sheet-input font-bold" placeholder="{{ __('spending.name') }}" autofocus></td>
                <td x-show="cols.group" class="p-1"><select name="types[${index}][group_spending_id]" class="sheet-select w-full">${groupOptions}</select></td>
                <td x-show="cols.acc_code" class="p-1"><input type="text" name="types[${index}][accountant_code]" class="sheet-input text-center"></td>
                <td x-show="cols.note" class="p-1"><input type="text" name="types[${index}][note]" class="sheet-input text-xs"></td>
                <td x-show="cols.branch" class="p-1"><select name="types[${index}][branch_id]" class="sheet-select w-full">${branchOptions}</select></td>
                <td x-show="cols.user" class="px-4 py-2 text-center text-[10px] text-slate-400 italic">{{ Auth::user()->name }}</td>
                <td x-show="cols.actions" class="px-4 py-2 text-center print:hidden">
                    <div class="flex items-center justify-center gap-1">
                        {{-- SAVE BUTTON --}}
                        <button type="button" onclick="document.getElementById('sheet-form').submit()" 
                                class="w-7 h-7 flex items-center justify-center bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg shadow-sm transition transform active:scale-95" title="{{ __('spending.save') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </button>
                        {{-- CANCEL BUTTON --}}
                        <button type="button" onclick="this.closest('tr').remove()" 
                                class="w-7 h-7 flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition" title="Cancel">
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
            form.action = "{{ route('type-spending.destroy', ':id') }}".replace(':id', id);
            window.confirmAction('delete-form', "{{ __('spending.delete_confirm') }}");
        }
    </script>
</x-app-layout>