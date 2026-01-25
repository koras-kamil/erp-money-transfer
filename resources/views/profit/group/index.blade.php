<x-app-layout>
    {{-- STYLES --}}
    <style>
        .sheet-input { width: 100%; height: 100%; display: flex; align-items: center; background: transparent; border: 1px solid transparent; padding: 0 12px; font-size: 0.875rem; color: #1f2937; font-weight: 400; border-radius: 6px; transition: all 0.15s ease-in-out; }
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
        allIds: {{ json_encode($groups->pluck('id')) }},
        cols: { select: true, id: true, code: true, name: true, branch: true, creator: true, desc: true, created_at: true, active: true, actions: true },
        
        toggleAll(value) { for (let key in this.cols) { this.cols[key] = value; } },
        
        toggleSelection(id) {
            if (this.selectedIds.includes(id)) { this.selectedIds = this.selectedIds.filter(i => i !== id); } else { this.selectedIds.push(id); }
        },
        toggleAllSelection() {
            if (this.selectedIds.length === this.allIds.length) { this.selectedIds = []; } else { this.selectedIds = [...this.allIds]; }
        },
        
        // --- FIXED BULK DELETE FUNCTION ---
        bulkDelete() {
            if (this.selectedIds.length === 0) return;
            
            // 1. Populate the hidden input with IDs
            document.getElementById('bulk-delete-ids').value = JSON.stringify(this.selectedIds);
            
            // 2. Submit the form safely
            if (window.confirmAction) {
                window.confirmAction('bulk-delete-form', '{{ __('profit.bulk_delete_confirm') }}');
            } else {
                if(confirm('{{ __('profit.bulk_delete_confirm') }}')) {
                    document.getElementById('bulk-delete-form').submit();
                }
            }
        },

        startEdit(id) { 
            this.editingId = id; 
            this.hasChanges = true;
            setTimeout(() => { document.getElementById('input-name-'+id)?.focus(); }, 100);
        },
        saveRow() { document.getElementById('form-groups').submit(); }
    }" class="py-6 w-full min-w-0" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

        {{-- TOOLBAR --}}
        <div class="mx-4 mb-6 flex flex-col md:flex-row justify-between items-center gap-4 no-print">
            
            {{-- Navigation Tabs --}}
            <div class="bg-slate-100 p-1 rounded-lg flex items-center shadow-inner">
                <a href="{{ route('profit.groups.index') }}" class="px-5 py-2 text-sm font-bold rounded-md bg-white text-indigo-600 shadow-sm transition">{{ __('profit.menu_groups') }}</a>
                <a href="{{ route('profit.types.index') }}" class="px-5 py-2 text-sm font-bold rounded-md text-slate-500 hover:text-slate-700 transition">{{ __('profit.menu_types') }}</a>
            </div>

            <div class="flex items-center gap-2">
                
                {{-- BULK ACTIONS (Red Button) --}}
                <div x-show="selectedIds.length > 0" x-transition class="flex items-center gap-2 bg-red-50 px-2 py-1 rounded-lg border border-red-100 mr-2 ml-2">
                    <span class="text-xs font-bold text-red-600 px-2"><span x-text="selectedIds.length"></span> {{ __('profit.selected') }}</span>
                    <button @click="bulkDelete()" type="button" class="px-3 py-1.5 bg-red-600 text-white text-xs font-bold rounded shadow-sm hover:bg-red-700 transition">
                        {{ __('profit.delete_selected') }}
                    </button>
                    <button @click="selectedIds = []" type="button" class="px-2 py-1.5 text-slate-500 hover:text-slate-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Trash (Red) --}}
                <a href="{{ route('profit.groups.trash') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-50 text-red-500 border border-red-100 hover:bg-red-100 transition shadow-sm">
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
                            @foreach(['select'=>'Select', 'id'=>'#', 'code'=>__('profit.code'), 'name'=>__('profit.name'), 'branch'=>__('profit.branch'), 'creator'=>__('profit.created_by'), 'desc'=>__('profit.description'), 'created_at'=>__('profit.created_at'), 'active'=>__('profit.active')] as $key => $label)
                                <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 rounded cursor-pointer"><input type="checkbox" x-model="cols.{{ $key }}" class="rounded text-indigo-600 w-4 h-4 border-slate-300"><span class="text-xs text-slate-700 font-medium">{{ $label }}</span></label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Print (Dark Slate) --}}
                <a href="{{ route('profit.groups.pdf') }}" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-700 text-white hover:bg-slate-800 transition shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                </a>
                
                {{-- Add New (Blue) --}}
                <button type="button" @click="addNewGroup()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
            </div>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="relative overflow-x-auto table-container bg-white shadow-sm rounded-lg border border-slate-200 mx-4">
            <form id="form-groups" action="{{ route('profit.groups.store') }}" method="POST">
                @csrf
                <table class="w-full text-sm text-left rtl:text-right text-slate-500 whitespace-nowrap">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th x-show="cols.select" class="px-4 py-3 w-[40px] text-center">
                                <input type="checkbox" @click="toggleAllSelection()" :checked="selectedIds.length > 0 && selectedIds.length === allIds.length" class="select-checkbox bg-white">
                            </th>
                            <th x-show="cols.id" class="px-6 py-3 font-medium text-center w-16">#</th>
                            <th x-show="cols.code" class="px-6 py-3 font-medium w-24">{{ __('profit.code') }}</th>
                            <th x-show="cols.name" class="px-6 py-3 font-medium min-w-[200px]">{{ __('profit.name') }}</th>
                            <th x-show="cols.branch" class="px-6 py-3 font-medium min-w-[150px]">{{ __('profit.branch') }}</th>
                            <th x-show="cols.creator" class="px-6 py-3 font-medium min-w-[120px]">{{ __('profit.created_by') }}</th>
                            <th x-show="cols.desc" class="px-6 py-3 font-medium min-w-[250px]">{{ __('profit.description') }}</th>
                            <th x-show="cols.created_at" class="px-6 py-3 font-medium text-center min-w-[150px]">{{ __('profit.created_at') }}</th>
                            <th x-show="cols.active" class="px-6 py-3 font-medium text-center w-24">{{ __('profit.active') }}</th>
                            <th x-show="cols.actions" class="px-6 py-3 font-medium text-center w-28 print:hidden"></th>
                        </tr>
                    </thead>
                    <tbody id="body-groups" class="divide-y divide-slate-100">
                        @foreach($groups as $index => $group)
                        <tr class="bg-white hover:bg-slate-50 transition-colors group/row" 
                            :class="[editingId === {{ $group->id }} ? 'bg-indigo-50/20' : '', selectedIds.includes({{ $group->id }}) ? 'bg-indigo-50/10' : '']">
                            
                            {{-- Checkbox --}}
                            <td x-show="cols.select" class="px-4 py-4 text-center">
                                <input type="checkbox" :value="{{ $group->id }}" x-model="selectedIds" class="select-checkbox">
                            </td>

                            <td x-show="cols.id" class="px-6 py-4 font-normal text-slate-400 text-center">{{ $loop->iteration }} <input type="hidden" name="groups[{{ $index }}][id]" value="{{ $group->id }}"></td>
                            
                            {{-- ID Code (Readonly Input) --}}
                            <td x-show="cols.code" class="p-1">
                                <input type="text" name="groups[{{ $index }}][code]" value="{{ $group->code }}" class="sheet-input font-normal uppercase text-slate-500" readonly>
                            </td>
                            
                            <td x-show="cols.name" class="p-1">
                                <span x-show="editingId !== {{ $group->id }}" class="px-3 block text-slate-700 font-bold truncate">{{ $group->name }}</span>
                                <input x-show="editingId === {{ $group->id }}" id="input-name-{{ $group->id }}" type="text" name="groups[{{ $index }}][name]" value="{{ $group->name }}" class="sheet-input font-bold text-slate-700">
                            </td>
                            <td x-show="cols.branch" class="p-1">
                                <span x-show="editingId !== {{ $group->id }}" class="px-3 block text-slate-600 font-normal truncate">{{ $group->branch->name ?? '-' }}</span>
                                <select x-show="editingId === {{ $group->id }}" name="groups[{{ $index }}][branch_id]" class="sheet-input font-normal">
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ $group->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td x-show="cols.creator" class="px-6 py-4 text-[10px] uppercase text-gray-400 font-normal truncate">{{ $group->creator->name ?? 'System' }}</td>
                            <td x-show="cols.desc" class="p-1">
                                <span x-show="editingId !== {{ $group->id }}" class="px-3 block text-slate-500 italic font-normal truncate">{{ $group->description ?? '-' }}</span>
                                <input x-show="editingId === {{ $group->id }}" type="text" name="groups[{{ $index }}][description]" value="{{ $group->description }}" class="sheet-input font-normal">
                            </td>
                            <td x-show="cols.created_at" class="px-6 py-4 text-center text-xs text-gray-400 font-mono font-normal">
                                {{ $group->created_at ? $group->created_at->format('Y-m-d H:i') : '-' }}
                            </td>
                            <td x-show="cols.active" class="px-6 py-4 text-center">
                                <input type="checkbox" 
                                       name="groups[{{ $index }}][is_active]" 
                                       value="1" 
                                       {{ $group->is_active ? 'checked' : '' }} 
                                       :class="{ 'pointer-events-none opacity-50': editingId !== {{ $group->id }} }"
                                       class="w-4 h-4 text-indigo-600 rounded border-slate-300 cursor-pointer">
                            </td>
                            <td x-show="cols.actions" class="px-6 py-4 text-center print:hidden">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" @click="saveRow()" x-show="editingId === {{ $group->id }}" class="text-emerald-500 transition active:scale-95"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></button>
                                    <button type="button" @click="startEdit({{ $group->id }})" x-show="editingId !== {{ $group->id }}" class="text-slate-400 hover:text-blue-600 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                    <button type="button" onclick="deleteRow({{ $group->id }})" class="text-slate-400 hover:text-red-600 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </form>
        </div>
    </div>
    
    {{-- HIDDEN FORMS (MUST BE HERE) --}}
    <form id="bulk-delete-form" action="{{ route('profit.groups.bulk-delete') }}" method="POST" class="hidden">
        @csrf 
        @method('DELETE')
        <input type="hidden" name="ids" id="bulk-delete-ids">
    </form>
    
    <form id="delete-form" action="" method="POST" class="hidden">@csrf @method('DELETE')</form>

    <script>
        function addNewGroup() {
            const tableBody = document.getElementById('body-groups');
            const index = Date.now();
            const nextId = tableBody.querySelectorAll('tr').length + 1;
            const currentUserName = "{{ auth()->user()->name }}";
            const userBranchId = "{{ auth()->user()->branch_id }}";

            const dateString = new Date().toLocaleString('sv-SE', { timeZone: 'Asia/Baghdad' }).replace('T', ' ').slice(0, 16);

            const row = `
            <tr class="bg-blue-50/40 border-b border-blue-100 transition-all duration-300">
                <td x-show="cols.select" class="px-4 py-4 text-center"></td>
                <td x-show="cols.id" class="px-6 py-4 text-center font-bold text-indigo-600">${nextId}</td>
                
                {{-- CODE FIELD (Shows ID) --}}
                <td x-show="cols.code" class="p-1">
                    <input type="text" value="${nextId}" class="sheet-input font-normal uppercase text-slate-400" readonly>
                </td>
                
                <td x-show="cols.name" class="p-1"><input type="text" name="groups[${index}][name]" class="sheet-input font-bold" placeholder="{{ __('profit.name') }}" autofocus></td>
                <td x-show="cols.branch" class="p-1">
                    <select name="groups[${index}][branch_id]" class="sheet-input font-normal">
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" ${ "{{ $branch->id }}" == userBranchId ? 'selected' : '' }>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td x-show="cols.creator" class="px-6 py-4 text-[10px] text-slate-400 italic font-normal text-center">${currentUserName}</td>
                <td x-show="cols.desc" class="p-1"><input type="text" name="groups[${index}][description]" class="sheet-input font-normal" placeholder="..."></td>
                <td x-show="cols.created_at" class="px-6 py-4 text-center text-xs text-indigo-400 font-mono font-normal">${dateString}</td>
                <td x-show="cols.active" class="px-6 py-4 text-center">
                    <input type="checkbox" name="groups[${index}][is_active]" value="1" checked class="w-4 h-4 text-indigo-600 rounded cursor-pointer">
                </td>
                <td x-show="cols.actions" class="px-6 py-4 text-center print:hidden">
                    <div class="flex items-center justify-center gap-2">
                        <button type="button" onclick="document.getElementById('form-groups').submit()" class="text-emerald-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></button>
                        <button type="button" onclick="this.closest('tr').remove()" class="text-slate-400 hover:text-red-500 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                </td>
            </tr>`;
            
            tableBody.insertAdjacentHTML('beforeend', row);
            setTimeout(() => { tableBody.lastElementChild.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 100);
        }

        function deleteRow(id) {
            const form = document.getElementById('delete-form');
            form.action = "{{ route('profit.groups.destroy', ':id') }}".replace(':id', id);
            
            // Confirm Action
            if (window.confirmAction) {
                window.confirmAction('delete-form', "{{ __('profit.delete_confirm') }}");
            } else {
                if(confirm("{{ __('profit.delete_confirm') }}")) { form.submit(); }
            }
        }
    </script>
</x-app-layout>