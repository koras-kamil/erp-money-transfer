<x-app-layout>
    {{-- STYLES (Identical to Profit Groups) --}}
    <style>
        .sheet-input { width: 100%; height: 100%; display: flex; align-items: center; background: transparent; border: 1px solid transparent; padding: 0 12px; font-size: 0.875rem; color: #1f2937; font-weight: 500; border-radius: 6px; transition: all 0.15s ease-in-out; }
        .sheet-input:focus { background-color: #fff; border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1); outline: none; }
        .sheet-input[readonly] { cursor: default; color: #64748b; background-color: transparent; }
        
        select.sheet-input {
            -webkit-appearance: none; appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.2em 1.2em;
            padding-right: 2.5rem; padding-left: 0.75rem; cursor: pointer; white-space: nowrap; 
        }
        [dir="rtl"] select.sheet-input { background-position: left 0.5rem center; padding-right: 0.75rem; padding-left: 2.5rem; }
        
        /* Checkbox & Scrollbar */
        .select-checkbox { width: 1.1rem; height: 1.1rem; border-radius: 4px; border: 1px solid #cbd5e1; color: #6366f1; cursor: pointer; transition: all 0.2s; }
        .select-checkbox:focus { ring: 2px; ring-color: #e0e7ff; }
        .table-container::-webkit-scrollbar { height: 6px; }
        .table-container::-webkit-scrollbar-track { background: #f1f1f1; }
        .table-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

        @media print { .no-print, button, .print\:hidden { display: none !important; } .overflow-x-auto { overflow: visible !important; } table { width: 100% !important; } }
    </style>

    <div x-data="{ 
        hasChanges: false,
        editingId: null,
        selectedIds: [],
        allIds: {{ json_encode($types->pluck('id')) }},
        cols: { select: true, id: true, code: true, name: true, group: true, branch: true, creator: true, desc: true, created_at: true, active: true, actions: true },
        
        toggleAll(value) { for (let key in this.cols) { this.cols[key] = value; } },
        
        toggleSelection(id) {
            if (this.selectedIds.includes(id)) { this.selectedIds = this.selectedIds.filter(i => i !== id); } else { this.selectedIds.push(id); }
        },
        toggleAllSelection() {
            if (this.selectedIds.length === this.allIds.length) { this.selectedIds = []; } else { this.selectedIds = [...this.allIds]; }
        },
        
        bulkDelete() {
            if (this.selectedIds.length === 0) return;
            document.getElementById('bulk-delete-ids').value = JSON.stringify(this.selectedIds);
            
            if (window.confirmAction) {
                window.confirmAction('bulk-delete-form', '{{ __('profit.bulk_delete_confirm') }}');
            } else {
                if(confirm('{{ __('profit.bulk_delete_confirm') }}')) document.getElementById('bulk-delete-form').submit();
            }
        },

        startEdit(id) { 
            this.editingId = id; 
            this.hasChanges = true;
            setTimeout(() => { document.getElementById('input-name-'+id)?.focus(); }, 100);
        },
        saveRow() { document.getElementById('form-types').submit(); }
    }" class="py-6 w-full min-w-0" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

        {{-- TOOLBAR --}}
        <div class="mx-4 mb-6 flex flex-col md:flex-row justify-between items-center gap-4 no-print">
            
            {{-- UPDATED NAVIGATION TABS --}}
            <div class="bg-white p-1.5 rounded-xl border border-slate-200 shadow-sm flex items-center w-fit">
                
                {{-- 1. Profit Groups Tab (Inactive) --}}
                <a href="{{ route('profit.groups.index') }}" 
                   class="px-4 py-2 text-sm font-bold rounded-lg transition-all text-slate-500 hover:text-indigo-600 hover:bg-slate-50">
                   {{ __('profit.menu_groups') }}
                </a>
            
                {{-- Separator --}}
                <div class="w-px h-4 bg-slate-200 mx-1"></div>
            
                {{-- 2. Profit Types Tab (Active) --}}
                <a href="{{ route('profit.types.index') }}" 
                   class="px-4 py-2 text-sm font-bold rounded-lg transition-all bg-indigo-50 text-indigo-600 shadow-sm border border-indigo-100">
                   {{ __('profit.menu_types') }}
                </a>
            
            </div>

            <div class="flex items-center gap-2">
                {{-- BULK ACTIONS --}}
                <div x-show="selectedIds.length > 0" x-transition class="flex items-center gap-2 bg-red-50 px-2 py-1 rounded-lg border border-red-100 mr-2 ml-2">
                    <span class="text-xs font-bold text-red-600 px-2"><span x-text="selectedIds.length"></span> {{ __('profit.selected') }}</span>
                    <button @click="bulkDelete()" type="button" class="px-3 py-1.5 bg-red-600 text-white text-xs font-bold rounded shadow-sm hover:bg-red-700 transition">
                        {{ __('profit.delete_selected') }}
                    </button>
                    <button @click="selectedIds = []" type="button" class="px-2 py-1.5 text-slate-500 hover:text-slate-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Trash --}}
                <a href="{{ route('profit.types.trash') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-50 text-red-500 border border-red-100 hover:bg-red-100 transition shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </a>
                
                {{-- Columns --}}
                <div x-data="{ openDropdown: false }" class="relative">
                    <button @click="openDropdown = !openDropdown" @click.away="openDropdown = false" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                    </button>
                    <div x-show="openDropdown" class="absolute ltr:right-0 rtl:left-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 z-50 p-2" x-cloak style="display:none;">
                        <div class="flex gap-2 mb-2 pb-2 border-b border-slate-100">
                            <button @click="toggleAll(true)" class="flex-1 text-[10px] bg-indigo-50 text-indigo-600 py-1 rounded font-bold">ALL</button>
                            <button @click="toggleAll(false)" class="flex-1 text-[10px] bg-slate-50 text-slate-600 py-1 rounded font-bold">NONE</button>
                        </div>
                        <div class="space-y-1 max-h-60 overflow-y-auto">
                            @foreach(['select'=>'Select', 'id'=>'#', 'code'=>__('profit.code'), 'name'=>__('profit.name'), 'group'=>__('profit.group_title'), 'branch'=>__('profit.branch'), 'creator'=>__('profit.created_by'), 'desc'=>__('profit.description'), 'created_at'=>__('profit.created_at'), 'active'=>__('profit.active')] as $key => $label)
                                <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 rounded cursor-pointer"><input type="checkbox" x-model="cols.{{ $key }}" class="rounded text-indigo-600 w-4 h-4 border-slate-300"><span class="text-xs text-slate-700 font-medium">{{ $label }}</span></label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Print --}}
                <a href="{{ route('profit.types.pdf') }}" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-700 text-white hover:bg-slate-800 transition shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                </a>
                
                {{-- Add New --}}
                <button type="button" @click="addNewType()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
            </div>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="relative overflow-x-auto table-container bg-white shadow-sm rounded-lg border border-slate-200 mx-4">
            <form id="form-types" action="{{ route('profit.types.store') }}" method="POST">
                @csrf
                <table class="w-full text-sm text-left rtl:text-right text-slate-500 whitespace-nowrap">
                    {{-- GRAY HEADER (Same as Profit Groups) --}}
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th x-show="cols.select" class="px-4 py-3 w-[40px] text-center">
                                <input type="checkbox" @click="toggleAllSelection()" :checked="selectedIds.length > 0 && selectedIds.length === allIds.length" class="select-checkbox bg-white">
                            </th>
                            <th x-show="cols.id" class="px-6 py-3 font-medium text-center w-16">#</th>
                            <th x-show="cols.code" class="px-6 py-3 font-medium w-24">{{ __('profit.code') }}</th>
                            <th x-show="cols.name" class="px-6 py-3 font-medium min-w-[200px]">{{ __('profit.name') }}</th>
                            <th x-show="cols.group" class="px-6 py-3 font-medium min-w-[150px]">{{ __('profit.group_title') }}</th>
                            <th x-show="cols.branch" class="px-6 py-3 font-medium min-w-[150px]">{{ __('profit.branch') }}</th>
                            <th x-show="cols.creator" class="px-6 py-3 font-medium min-w-[120px]">{{ __('profit.created_by') }}</th>
                            <th x-show="cols.desc" class="px-6 py-3 font-medium min-w-[200px]">{{ __('profit.description') }}</th>
                            <th x-show="cols.created_at" class="px-6 py-3 font-medium text-center min-w-[150px]">{{ __('profit.created_at') }}</th>
                            <th x-show="cols.active" class="px-6 py-3 font-medium text-center w-24">{{ __('profit.active') }}</th>
                            <th x-show="cols.actions" class="px-6 py-3 font-medium text-center w-28 print:hidden"></th>
                        </tr>
                    </thead>
                    <tbody id="body-types" class="divide-y divide-slate-100">
                        @foreach($types as $index => $type)
                        <tr class="bg-white hover:bg-slate-50 transition-colors group/row" 
                            :class="[editingId === {{ $type->id }} ? 'bg-indigo-50/20' : '', selectedIds.includes({{ $type->id }}) ? 'bg-indigo-50/10' : '']">
                            
                            {{-- Checkbox --}}
                            <td x-show="cols.select" class="px-4 py-4 text-center">
                                <input type="checkbox" :value="{{ $type->id }}" x-model="selectedIds" class="select-checkbox">
                            </td>

                            <td x-show="cols.id" class="px-6 py-4 font-normal text-slate-400 text-center">{{ $loop->iteration }} <input type="hidden" name="types[{{ $index }}][id]" value="{{ $type->id }}"></td>
                            
                            <td x-show="cols.code" class="p-1">
                                <input type="text" name="types[{{ $index }}][code]" value="{{ $type->code }}" class="sheet-input font-normal uppercase text-slate-500" readonly>
                            </td>
                            
                            <td x-show="cols.name" class="p-1">
                                <span x-show="editingId !== {{ $type->id }}" class="px-3 block text-slate-700 font-bold truncate">{{ $type->name }}</span>
                                <input x-show="editingId === {{ $type->id }}" id="input-name-{{ $type->id }}" type="text" name="types[{{ $index }}][name]" value="{{ $type->name }}" class="sheet-input font-bold text-slate-700">
                            </td>
                            
                            {{-- GROUP COLUMN --}}
                            <td x-show="cols.group" class="p-1">
                                <span x-show="editingId !== {{ $type->id }}" class="px-3 block text-slate-600 font-normal truncate">{{ $type->group->name ?? '-' }}</span>
                                <select x-show="editingId === {{ $type->id }}" name="types[{{ $index }}][profit_group_id]" class="sheet-input font-normal">
                                    <option value="" disabled>{{ __('profit.select_group') }}</option>
                                    @foreach($activeGroups as $g)
                                        <option value="{{ $g->id }}" {{ $type->profit_group_id == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            
                            <td x-show="cols.branch" class="p-1">
                                <span x-show="editingId !== {{ $type->id }}" class="px-3 block text-slate-600 font-normal truncate">{{ $type->branch->name ?? 'Main Branch' }}</span>
                                <select x-show="editingId === {{ $type->id }}" name="types[{{ $index }}][branch_id]" class="sheet-input font-normal">
                                    <option value="">Main Branch</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ $type->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            
                            <td x-show="cols.creator" class="px-6 py-4 text-[10px] uppercase text-gray-400 font-normal truncate">{{ $type->creator->name ?? 'System' }}</td>
                            
                            {{-- DESCRIPTION COLUMN --}}
                            <td x-show="cols.desc" class="p-1">
                                <span x-show="editingId !== {{ $type->id }}" class="px-3 block text-slate-500 italic font-normal truncate">{{ $type->description ?? '-' }}</span>
                                <input x-show="editingId === {{ $type->id }}" type="text" name="types[{{ $index }}][description]" value="{{ $type->description }}" class="sheet-input font-normal">
                            </td>
                            
                            <td x-show="cols.created_at" class="px-6 py-4 text-center text-xs text-gray-400 font-mono font-normal">
                                {{ $type->created_at ? $type->created_at->format('Y-m-d H:i') : '-' }}
                            </td>
                            
                            <td x-show="cols.active" class="px-6 py-4 text-center">
                                <input type="checkbox" name="types[{{ $index }}][is_active]" value="1" {{ $type->is_active ? 'checked' : '' }} 
                                       :class="{ 'pointer-events-none opacity-50': editingId !== {{ $type->id }} }"
                                       class="w-4 h-4 text-indigo-600 rounded border-slate-300 cursor-pointer">
                            </td>
                            
                            <td x-show="cols.actions" class="px-6 py-4 text-center print:hidden">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" @click="saveRow()" x-show="editingId === {{ $type->id }}" class="text-emerald-500 transition active:scale-95"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></button>
                                    <button type="button" @click="startEdit({{ $type->id }})" x-show="editingId !== {{ $type->id }}" class="text-slate-400 hover:text-blue-600 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                    <button type="button" onclick="deleteRow({{ $type->id }})" class="text-slate-400 hover:text-red-600 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </form>
        </div>
    </div>
    
    {{-- HIDDEN FORMS FOR BULK DELETE --}}
    <form id="bulk-delete-form" action="{{ route('profit.types.bulk-delete') }}" method="POST" class="hidden">
        @csrf 
        @method('DELETE')
        <input type="hidden" name="ids" id="bulk-delete-ids">
    </form>
    
    <form id="delete-form" action="" method="POST" class="hidden">@csrf @method('DELETE')</form>

    <script>
        function addNewType() {
            const tableBody = document.getElementById('body-types');
            const index = Date.now();
            const nextId = tableBody.querySelectorAll('tr').length + 1;
            const currentUserName = "{{ auth()->user()->name }}";
            
            const userBranchId = "{{ auth()->user()->branch_id }}"; 

            let groupOptions = '<option value="" disabled selected>{{ __('profit.select_group') }}</option>';
            @foreach($activeGroups as $g)
                groupOptions += `<option value="{{ $g->id }}">{{ $g->name }}</option>`; 
            @endforeach

            let branchOptions = '<option value="">Main Branch</option>';
            @foreach($branches as $branch)
                branchOptions += `<option value="{{ $branch->id }}" ${ "{{ $branch->id }}" == userBranchId ? 'selected' : '' }>{{ $branch->name }}</option>`;
            @endforeach

            const dateString = new Date().toLocaleString('sv-SE', { timeZone: 'Asia/Baghdad' }).replace('T', ' ').slice(0, 16);

            const row = `
            <tr class="bg-blue-50/40 border-b border-blue-100 transition-all duration-300">
                <td x-show="cols.select" class="px-4 py-4 text-center"></td>
                <td x-show="cols.id" class="px-6 py-4 text-center font-bold text-indigo-600">${nextId}</td>
                
                {{-- CODE --}}
                <td x-show="cols.code" class="p-1">
                    <input type="text" value="${nextId}" class="sheet-input font-normal uppercase text-slate-400" readonly>
                </td>
                
                <td x-show="cols.name" class="p-1"><input type="text" name="types[${index}][name]" class="sheet-input font-bold" placeholder="{{ __('profit.name') }}" autofocus></td>
                
                {{-- GROUP --}}
                <td x-show="cols.group" class="p-1"><select name="types[${index}][profit_group_id]" class="sheet-input font-normal">${groupOptions}</select></td>
                
                <td x-show="cols.branch" class="p-1"><select name="types[${index}][branch_id]" class="sheet-input font-normal">${branchOptions}</select></td>
                <td x-show="cols.creator" class="px-6 py-4 text-[10px] text-slate-400 italic font-normal text-center">${currentUserName}</td>
                
                {{-- DESCRIPTION --}}
                <td x-show="cols.desc" class="p-1"><input type="text" name="types[${index}][description]" class="sheet-input font-normal" placeholder="..."></td>
                
                <td x-show="cols.created_at" class="px-6 py-4 text-center text-xs text-indigo-400 font-mono font-normal">${dateString}</td>
                <td x-show="cols.active" class="px-6 py-4 text-center">
                    <input type="checkbox" name="types[${index}][is_active]" value="1" checked class="w-4 h-4 text-indigo-600 rounded cursor-pointer">
                </td>
                <td x-show="cols.actions" class="px-6 py-4 text-center print:hidden">
                    <div class="flex items-center justify-center gap-2">
                        <button type="button" onclick="document.getElementById('form-types').submit()" class="text-emerald-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></button>
                        <button type="button" onclick="this.closest('tr').remove()" class="text-slate-400 hover:text-red-500 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                </td>
            </tr>`;
            
            tableBody.insertAdjacentHTML('beforeend', row);
            setTimeout(() => { tableBody.lastElementChild.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 100);
        }

        function deleteRow(id) {
            const form = document.getElementById('delete-form');
            form.action = "{{ route('profit.types.destroy', ':id') }}".replace(':id', id);
            
            if (window.confirmAction) {
                window.confirmAction('delete-form', "{{ __('profit.delete_confirm') }}");
            } else {
                if(confirm("{{ __('profit.delete_confirm') }}")) { form.submit(); }
            }
        }
    </script>
</x-app-layout>