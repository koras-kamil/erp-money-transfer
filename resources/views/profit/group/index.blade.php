<x-app-layout>
    {{-- STYLES --}}
    <style>
        /* Make inputs look like real inputs so user knows to fill them */
        .sheet-input { 
            width: 100%; 
            background-color: #ffffff; /* White background */
            border: 1px solid #e2e8f0; /* Light border */
            padding: 4px 8px; 
            border-radius: 6px; 
            outline: none; 
            font-size: 0.875rem; 
            transition: all 0.2s; 
            color: #1e293b;
        }
        .sheet-input:focus { 
            border-color: #6366f1; 
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2); 
        }
        
        /* Readonly inputs should look flat */
        .sheet-input[readonly] { 
            background-color: transparent; 
            border-color: transparent; 
            cursor: default; 
            color: #64748b; 
            font-weight: 500;
        }
        
        /* Select inputs */
        .sheet-select { 
            width: 100%; 
            background-color: #ffffff; 
            border: 1px solid #e2e8f0; 
            padding: 4px 8px; 
            border-radius: 6px; 
            outline: none; 
            font-size: 0.875rem; 
            cursor: pointer; 
        }
        .sheet-select:focus { border-color: #6366f1; }

        @media print { .no-print, button, a { display: none !important; } }
    </style>

    <div x-data="{ 
        editingId: null,
        cols: { id: true, code: true, name: true, branch: true, creator: true, desc: true, active: true, actions: true },
        toggleAll(value) { for (let key in this.cols) { this.cols[key] = value; } },
        startEdit(id) { this.editingId = id; },
        saveRow() { document.getElementById('form-groups').submit(); }
    }" class="py-6 w-full" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

        {{-- TOOLBAR --}}
        <div class="mx-6 mb-4 bg-white p-3 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4 no-print">
            <div class="bg-slate-100 p-1 rounded-lg flex items-center">
                <a href="{{ route('profit.groups.index') }}" class="px-5 py-2 text-sm font-bold rounded-md bg-white text-indigo-600 shadow-sm transition">{{ __('profit.menu_groups') }}</a>
                <a href="{{ route('profit.types.index') }}" class="px-5 py-2 text-sm font-bold rounded-md text-slate-500 hover:text-slate-700 transition">{{ __('profit.menu_types') }}</a>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('profit.groups.trash') }}" class="w-10 h-10 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 border border-red-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </a>
                
                {{-- Column Filter --}}
                <div x-data="{ openDropdown: false }" class="relative">
                    <button @click="openDropdown = !openDropdown" @click.away="openDropdown = false" class="w-10 h-10 flex items-center justify-center rounded-lg bg-slate-50 text-slate-600 border border-slate-200 hover:bg-white hover:text-indigo-600 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                    <div x-show="openDropdown" class="absolute top-full mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 z-50 p-2 ltr:right-0 rtl:left-0" x-cloak style="display:none;">
                        <div class="flex gap-2 mb-2 pb-2 border-b border-slate-100">
                            <button @click="toggleAll(true)" class="flex-1 text-[10px] bg-indigo-50 text-indigo-600 py-1 rounded hover:bg-indigo-100 font-bold">ALL</button>
                            <button @click="toggleAll(false)" class="flex-1 text-[10px] bg-slate-50 text-slate-600 py-1 rounded hover:bg-slate-100 font-bold">NONE</button>
                        </div>
                        <div class="space-y-1 max-h-60 overflow-y-auto">
                            @foreach(['id'=>'#', 'code'=>__('profit.code'), 'name'=>__('profit.name'), 'branch'=>__('profit.branch'), 'creator'=>__('profit.created_by'), 'desc'=>__('profit.description'), 'active'=>__('profit.active')] as $key => $label)
                                <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 rounded cursor-pointer"><input type="checkbox" x-model="cols.{{ $key }}" class="rounded text-indigo-600 w-4 h-4 border-slate-300"><span class="text-xs text-slate-700 font-medium">{{ $label }}</span></label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <a href="{{ route('profit.groups.pdf') }}" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-lg bg-slate-700 text-white hover:bg-slate-800 transition shadow-lg shadow-slate-300/50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                </a>
                
                <button type="button" @click="addNewGroup()" class="w-10 h-10 flex items-center justify-center rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="mx-6 bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <form id="form-groups" action="{{ route('profit.groups.store') }}" method="POST">
                    @csrf
                    <table class="w-full text-sm text-left rtl:text-right text-slate-500 table-fixed whitespace-nowrap">
                        <thead class="text-xs text-slate-700 uppercase bg-blue-50/50 border-b border-blue-100">
                            <tr>
                                <th x-show="cols.id" class="px-4 py-3 w-[5%] text-center">#</th>
                                <th x-show="cols.code" class="px-4 py-3 w-[8%]">{{ __('profit.code') }}</th>
                                <th x-show="cols.name" class="px-4 py-3 w-[20%]">{{ __('profit.name') }}</th>
                                <th x-show="cols.branch" class="px-4 py-3 w-[12%]">{{ __('profit.branch') }}</th>
                                <th x-show="cols.creator" class="px-4 py-3 w-[12%]">{{ __('profit.created_by') }}</th>
                                <th x-show="cols.desc" class="px-4 py-3 w-[28%]">{{ __('profit.description') }}</th>
                                <th x-show="cols.active" class="px-4 py-3 w-[5%] text-center">{{ __('profit.active') }}</th>
                                <th x-show="cols.actions" class="px-4 py-3 w-[10%] text-center print:hidden">{{ __('profit.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="body-groups" class="divide-y divide-slate-100">
                            @foreach($groups as $index => $group)
                            <tr class="hover:bg-slate-50 transition-colors group/row" :class="editingId === {{ $group->id }} ? 'bg-indigo-50/20' : ''">
                                
                                <td x-show="cols.id" class="px-4 py-2 text-center font-medium text-slate-400">
                                    {{ $loop->iteration }} <input type="hidden" name="groups[{{ $index }}][id]" value="{{ $group->id }}">
                                </td>
                                
                                {{-- CODE COLUMN - SYSTEM STYLE --}}
                                <td x-show="cols.code" class="px-4 py-2">
                                    <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                        {{ $group->code }}
                                    </span>
                                </td>

                                <td x-show="cols.name" class="px-2 py-1">
                                    <span x-show="editingId !== {{ $group->id }}" class="block px-2 text-slate-700 font-bold truncate">{{ $group->name }}</span>
                                    <input x-show="editingId === {{ $group->id }}" type="text" name="groups[{{ $index }}][name]" value="{{ $group->name }}" class="sheet-input font-bold">
                                </td>

                                <td x-show="cols.branch" class="px-2 py-1">
                                    <span x-show="editingId !== {{ $group->id }}" class="block px-2 text-slate-600 font-medium truncate">{{ $group->branch->name ?? '-' }}</span>
                                    <select x-show="editingId === {{ $group->id }}" name="groups[{{ $index }}][branch_id]" class="sheet-select">
                                        <option value="">{{ __('profit.all_branches') }}</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ $group->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </td>

                                <td x-show="cols.creator" class="px-2 py-1">
                                    <span class="block px-2 text-slate-500 text-xs truncate">{{ $group->creator->name ?? 'System' }}</span>
                                </td>

                                <td x-show="cols.desc" class="px-2 py-1">
                                    <span x-show="editingId !== {{ $group->id }}" class="block px-2 text-slate-500 truncate" title="{{ $group->description }}">{{ $group->description }}</span>
                                    <input x-show="editingId === {{ $group->id }}" type="text" name="groups[{{ $index }}][description]" value="{{ $group->description }}" class="sheet-input">
                                </td>

                                <td x-show="cols.active" class="px-2 py-1 text-center">
                                    <input type="checkbox" name="groups[{{ $index }}][is_active]" value="1" {{ $group->is_active ? 'checked' : '' }} :disabled="editingId !== {{ $group->id }}" class="w-4 h-4 text-indigo-600 rounded border-slate-300 cursor-pointer disabled:opacity-50">
                                </td>

                                <td x-show="cols.actions" class="px-2 py-1 text-center print:hidden">
                                    <div class="flex items-center justify-center h-8 gap-1">
                                        {{-- Save Icon Button --}}
                                        <div x-show="editingId === {{ $group->id }}">
                                            <button type="button" @click="saveRow()" class="w-7 h-7 flex items-center justify-center bg-emerald-500 text-white rounded shadow hover:bg-emerald-600 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            </button>
                                        </div>
                                        {{-- Edit/Delete Buttons --}}
                                        <div x-show="editingId !== {{ $group->id }}" class="flex items-center gap-1">
                                            <button type="button" @click="startEdit({{ $group->id }})" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                            <button type="button" onclick="deleteRow({{ $group->id }})" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
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
    </div>
    
    <form id="delete-form" action="" method="POST" class="hidden">@csrf @method('DELETE')</form>

    <script>
        function addNewGroup() {
            const tableBody = document.getElementById('body-groups');
            const index = Date.now();
            
            const rows = tableBody.querySelectorAll('tr');
            let nextId = 1;
            if (rows.length > 0) {
                const lastRow = rows[rows.length - 1];
                const lastIdText = lastRow.cells[0].innerText.trim();
                const lastIdNum = parseInt(lastIdText);
                if (!isNaN(lastIdNum)) { nextId = lastIdNum + 1; } 
                else { nextId = rows.length + 1; }
            }

            const userBranchName = "{{ auth()->user()->branch->name ?? 'Main Branch' }}";
            const userBranchId = "{{ auth()->user()->branch_id }}";
            const currentUserName = "{{ auth()->user()->name }}"; 

            const row = `
            <tr class="bg-blue-50/40 border-b border-blue-100 transition-all duration-300">
                <td x-show="cols.id" class="px-4 py-2 text-center font-bold text-blue-600">${nextId}</td>
                
                <td x-show="cols.code" class="px-4 py-2">
                    <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                        ${nextId}
                    </span>
                </td>

                <td x-show="cols.name" class="px-2 py-1">
                    <input type="text" name="groups[${index}][name]" class="sheet-input font-bold" placeholder="{{ __('profit.name') }}" autofocus>
                </td>
                
                <td x-show="cols.branch" class="px-2 py-1">
                    <input type="text" value="${userBranchName}" class="sheet-input text-slate-600 cursor-default" readonly>
                    <input type="hidden" name="groups[${index}][branch_id]" value="${userBranchId}">
                </td>

                <td x-show="cols.creator" class="px-2 py-1">
                    <span class="block px-2 text-slate-500 text-xs truncate">${currentUserName}</span>
                </td>

                <td x-show="cols.desc" class="px-2 py-1">
                    <input type="text" name="groups[${index}][description]" class="sheet-input" placeholder="...">
                </td>

                <td x-show="cols.active" class="px-2 py-1 text-center">
                    <input type="checkbox" name="groups[${index}][is_active]" value="1" checked class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer">
                </td>
                
                <td x-show="cols.actions" class="px-2 py-1 text-center print:hidden">
                    <div class="flex items-center justify-center h-8 gap-1">
                        <button type="button" onclick="document.getElementById('form-groups').submit()" class="w-7 h-7 flex items-center justify-center bg-emerald-500 text-white rounded shadow hover:bg-emerald-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </button>
                        <button type="button" onclick="this.closest('tr').remove()" class="w-7 h-7 flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </td>
            </tr>`;
            
            tableBody.insertAdjacentHTML('beforeend', row);
            setTimeout(() => { 
                tableBody.lastElementChild.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 50);
        }

        function deleteRow(id) {
            const form = document.getElementById('delete-form');
            form.action = "{{ route('profit.groups.destroy', ':id') }}".replace(':id', id);
            if(confirm("{{ __('profit.delete_confirm') }}")) form.submit();
        }
    </script>
</x-app-layout>