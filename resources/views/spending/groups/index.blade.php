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
        }
    </style>

    <div x-data="{ 
        hasChanges: false,
        editingId: null,
        cols: { id: true, code: true, name: true, acc_code: true, branch: true, user: true, actions: true },
        toggleAll(value) { for (let key in this.cols) { this.cols[key] = value; } },
        startEdit(id) { 
            this.editingId = id; 
            setTimeout(() => { document.getElementById('input-name-'+id)?.focus(); }, 100);
        },
        saveRow() {
            document.getElementById('sheet-form').submit();
        }
    }" class="py-6" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

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
                <a href="{{ route('group-spending.trash') }}" class="w-9 h-9 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition border border-red-100" title="{{ __('spending.trash') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </a>

                {{-- Manage Columns (ICON CHANGED TO COG) --}}
                <div x-data="{ openDropdown: false }" class="relative">
                    <button @click="openDropdown = !openDropdown" @click.away="openDropdown = false" class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-50 text-slate-600 border border-slate-200 hover:bg-white hover:text-indigo-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.572 1.065c-1.543.94-3 .888-8 .888s-.888-.888-.888-.888z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </button>
                    <div x-show="openDropdown" class="absolute top-full mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 z-50 p-2 ltr:right-0 rtl:left-0" x-cloak>
                        <div class="flex gap-2 mb-2 pb-2 border-b border-slate-100">
                            <button @click="toggleAll(true)" class="flex-1 text-[10px] bg-indigo-50 text-indigo-600 py-1 rounded hover:bg-indigo-100 font-bold">ALL</button>
                            <button @click="toggleAll(false)" class="flex-1 text-[10px] bg-slate-50 text-slate-600 py-1 rounded hover:bg-slate-100 font-bold">NONE</button>
                        </div>
                        <div class="space-y-1 max-h-60 overflow-y-auto">
                            @foreach(['id'=>'#', 'code'=>__('spending.code'), 'name'=>__('spending.name'), 'acc_code'=>__('spending.accountant_code'), 'branch'=>__('spending.branch'), 'user'=>__('spending.created_by')] as $key => $label)
                                <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 rounded cursor-pointer"><input type="checkbox" x-model="cols.{{ $key }}" class="rounded text-indigo-600 w-4 h-4 border-slate-300"><span class="text-xs text-slate-700 font-medium">{{ $label }}</span></label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Print --}}
                <a href="{{ route('group-spending.print') }}" target="_blank" class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-700 text-white hover:bg-slate-800 transition shadow-md shadow-slate-300/50" title="{{ __('spending.print') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                </a>

                {{-- Add New --}}
                <button type="button" @click="addNewRow()" class="w-9 h-9 flex items-center justify-center rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition" title="{{ __('spending.add_new') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
            </div>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="relative overflow-x-auto bg-white shadow-sm rounded-lg border border-slate-200 mx-4">
            <form id="sheet-form" action="{{ route('group-spending.store') }}" method="POST">
                @csrf
                <table class="w-full text-sm text-left rtl:text-right text-slate-500" @input="hasChanges = true" @change="hasChanges = true">
                    <thead class="text-xs text-slate-700 uppercase bg-blue-50/50 border-b border-blue-100">
                        <tr>
                            <th x-show="cols.id" class="px-6 py-3 w-[5%] text-center">#</th>
                            <th x-show="cols.code" class="px-6 py-3 w-[15%]">{{ __('spending.code') }}</th>
                            <th x-show="cols.name" class="px-6 py-3 w-[25%]">{{ __('spending.name') }}</th>
                            <th x-show="cols.acc_code" class="px-6 py-3 w-[20%] text-center">{{ __('spending.accountant_code') }}</th>
                            <th x-show="cols.branch" class="px-6 py-3 w-[20%]">{{ __('spending.branch') }}</th>
                            <th x-show="cols.user" class="px-6 py-3 w-[10%] text-center print:hidden">{{ __('spending.created_by') }}</th>
                            <th x-show="cols.actions" class="px-6 py-3 w-[5%] text-center print:hidden">{{ __('spending.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="sheet-body" class="divide-y divide-slate-100">
                        @foreach($groups as $index => $group)
                        <tr class="bg-white hover:bg-slate-50 transition-colors group/row" 
                            :class="editingId === {{ $group->id }} ? 'bg-indigo-50/20' : ''">
                            
                            {{-- ID --}}
                            <td x-show="cols.id" class="px-6 py-3 text-center font-medium text-slate-400">
                                {{ $loop->iteration }}
                                <input type="hidden" name="spendings[{{ $index }}][id]" value="{{ $group->id }}">
                            </td>
                            
                            {{-- Code --}}
                            <td x-show="cols.code" class="p-1">
                                <input type="text" name="spendings[{{ $index }}][code]" value="{{ $group->code }}" class="sheet-input font-bold uppercase text-slate-500" readonly>
                            </td>

                            {{-- Name --}}
                            <td x-show="cols.name" class="p-1">
                                <span x-show="editingId !== {{ $group->id }}" class="px-3 block text-slate-700 font-bold">{{ $group->name }}</span>
                                <input x-show="editingId === {{ $group->id }}" id="input-name-{{ $group->id }}" type="text" name="spendings[{{ $index }}][name]" value="{{ $group->name }}" class="sheet-input font-bold text-slate-700">
                            </td>

                            {{-- Accountant Code --}}
                            <td x-show="cols.acc_code" class="p-1">
                                <span x-show="editingId !== {{ $group->id }}" class="block text-center text-slate-600">{{ $group->accountant_code }}</span>
                                <input x-show="editingId === {{ $group->id }}" type="text" name="spendings[{{ $index }}][accountant_code]" value="{{ $group->accountant_code }}" class="sheet-input text-center">
                            </td>

                            {{-- Branch --}}
                            <td x-show="cols.branch" class="p-1">
                                <span x-show="editingId !== {{ $group->id }}" class="px-3 block text-slate-600">{{ $group->branch->name ?? '-' }}</span>
                                <select x-show="editingId === {{ $group->id }}" name="spendings[{{ $index }}][branch_id]" class="sheet-input">
                                    <option value="" disabled>{{ __('spending.select_branch') }}</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ $group->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </td>

                            {{-- User --}}
                            <td x-show="cols.user" class="px-6 py-3 text-center text-[10px] text-slate-400 print:hidden">{{ $group->creator->name ?? '-' }}</td>
                            
                            {{-- Actions --}}
                            <td x-show="cols.actions" class="px-6 py-3 text-center print:hidden">
                                <div class="relative flex items-center justify-center gap-2 w-full">
                                    
                                    {{-- SAVE BUTTON (Visible when Editing) --}}
                                    <button type="button" @click="saveRow()" x-show="editingId === {{ $group->id }}" 
                                            class="w-7 h-7 flex items-center justify-center bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg shadow-sm transition transform active:scale-95" title="{{ __('spending.save') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </button>

                                    {{-- EDIT BUTTON --}}
                                    <button type="button" @click="startEdit({{ $group->id }})" x-show="editingId !== {{ $group->id }}" 
                                            class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="{{ __('Edit') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>

                                    {{-- DELETE BUTTON --}}
                                    <button type="button" onclick="deleteDatabaseRow({{ $group->id }})" 
                                            class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="{{ __('Delete') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
            const userBranchId = "{{ Auth::user()->branch_id }}"; 

            let branchOptions = '<option value="" disabled>{{ __('spending.select_branch') }}</option>';
            @foreach($branches as $branch) 
                branchOptions += `<option value="{{ $branch->id }}" ${ "{{ $branch->id }}" == userBranchId ? 'selected' : '' }>{{ $branch->name }}</option>`; 
            @endforeach

            const rowHtml = `
            <tr class="bg-blue-50/40 border-b border-blue-100 transition-all duration-300">
                <td x-show="cols.id" class="px-6 py-3 text-center"><span class="bg-blue-100 text-blue-600 py-0.5 px-2 rounded text-[10px] font-bold">NEW</span></td>
                <td x-show="cols.code" class="p-1"><input type="text" value="AUTO" class="sheet-input font-bold uppercase text-slate-400" readonly></td>
                <td x-show="cols.name" class="p-1"><input type="text" name="spendings[${index}][name]" class="sheet-input font-bold" placeholder="{{ __('spending.name') }}" autofocus></td>
                <td x-show="cols.acc_code" class="p-1"><input type="text" name="spendings[${index}][accountant_code]" class="sheet-input text-center"></td>
                <td x-show="cols.branch" class="p-1"><select name="spendings[${index}][branch_id]" class="sheet-input">${branchOptions}</select></td>
                <td x-show="cols.user" class="px-6 py-3 text-center text-[10px] text-slate-400 italic">{{ Auth::user()->name }}</td>
                <td x-show="cols.actions" class="px-6 py-3 text-center print:hidden">
                    <div class="flex items-center justify-center gap-1">
                        <button type="button" onclick="document.getElementById('sheet-form').submit()" 
                                class="w-7 h-7 flex items-center justify-center bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg shadow-sm transition transform active:scale-95" title="{{ __('spending.save') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </button>
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
            form.action = "{{ route('group-spending.destroy', ':id') }}".replace(':id', id);
            window.confirmAction('delete-form', "{{ __('spending.delete_confirm') }}");
        }
    </script>
</x-app-layout>