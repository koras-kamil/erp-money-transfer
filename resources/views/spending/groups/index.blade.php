<x-app-layout>
    {{-- STYLES: Exact Cash Box Design --}}
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
        
        /* Swipe Support */
        .table-container::-webkit-scrollbar { height: 6px; }
        .table-container::-webkit-scrollbar-track { background: #f1f1f1; }
        .table-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

        @media print { .no-print, button, .print\:hidden { display: none !important; } .overflow-x-auto { overflow: visible !important; } table { width: 100% !important; } }
    </style>

    <div x-data="{ 
        hasChanges: false,
        editingId: null,
        cols: { id: true, code: true, name: true, branch: true, user: true, created_at: true, active: true, actions: true },
        toggleAll(value) { for (let key in this.cols) { this.cols[key] = value; } },
        startEdit(id) { 
            this.editingId = id; 
            this.hasChanges = true;
            setTimeout(() => { document.getElementById('input-name-'+id)?.focus(); }, 100);
        },
        saveRow() { document.getElementById('sheet-form').submit(); }
    }" class="py-6 w-full min-w-0" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

        {{-- TOOLBAR --}}
        <div class="mx-4 mb-4 bg-white p-3 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4 no-print">
            
            {{-- RESTORED TABS --}}
            <div class="bg-slate-100 p-1 rounded-lg flex items-center">
                <a href="{{ route('group-spending.index') }}" class="px-4 py-2 text-sm font-bold rounded-md transition-all {{ request()->routeIs('group-spending.*') ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">{{ __('spending.group_tab') }}</a>
                <a href="{{ route('type-spending.index') }}" class="px-4 py-2 text-sm font-bold rounded-md transition-all {{ request()->routeIs('type-spending.*') ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">{{ __('spending.type_header') }}</a>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('group-spending.trash') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-50 text-red-500 border border-red-100 hover:bg-red-100 transition shadow-sm"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></a>
                
                <div x-data="{ openDropdown: false }" class="relative">
                    <button @click="openDropdown = !openDropdown" @click.away="openDropdown = false" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                    </button>
                    <div x-show="openDropdown" class="absolute ltr:right-0 rtl:left-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 z-50 p-2" x-cloak>
                        <div class="flex gap-2 mb-2 pb-2 border-b border-slate-100">
                            <button @click="toggleAll(true)" class="flex-1 text-[10px] bg-indigo-50 text-indigo-600 py-1 rounded font-bold">{{ __('spending.all') }}</button>
                            <button @click="toggleAll(false)" class="flex-1 text-[10px] bg-slate-50 text-slate-600 py-1 rounded font-bold">{{ __('spending.none') }}</button>
                        </div>
                        <div class="space-y-1 max-h-60 overflow-y-auto">
                            @foreach(['id'=>'#', 'code'=>__('spending.code'), 'name'=>__('spending.name'), 'branch'=>__('spending.branch'), 'user'=>__('spending.created_by'), 'created_at'=>__('spending.created_at'), 'active'=>__('spending.active')] as $key => $label)
                                <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 rounded cursor-pointer">
                                    <input type="checkbox" x-model="cols.{{ $key }}" class="rounded text-indigo-600 w-4 h-4 border-slate-300">
                                    <span class="text-xs text-slate-700 font-medium">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <a href="{{ route('group-spending.print') }}" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-700 text-white hover:bg-slate-800 transition shadow-sm"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg></a>
                <button type="button" @click="addNewRow()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow-lg transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg></button>
            </div>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="relative overflow-x-auto table-container bg-white shadow-sm rounded-lg border border-slate-200 mx-4">
            <form id="sheet-form" action="{{ route('group-spending.store') }}" method="POST">
                @csrf
                <table class="w-full text-sm text-left rtl:text-right text-slate-500 whitespace-nowrap">
                    <thead class="text-xs text-slate-700 uppercase bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th x-show="cols.id" class="px-6 py-3 font-medium text-center w-[5%]">#</th>
                            <th x-show="cols.code" class="px-6 py-3 font-medium w-[15%]">{{ __('spending.code') }}</th>
                            <th x-show="cols.name" class="px-6 py-3 font-medium w-[30%]">{{ __('spending.name') }}</th>
                            <th x-show="cols.branch" class="px-6 py-3 font-medium w-[20%]">{{ __('spending.branch') }}</th>
                            <th x-show="cols.user" class="px-6 py-3 font-medium text-center w-[10%]">{{ __('spending.created_by') }}</th>
                            <th x-show="cols.created_at" class="px-6 py-3 font-medium text-center w-[15%]">{{ __('spending.created_at') }}</th>
                            <th x-show="cols.active" class="px-6 py-3 font-medium text-center w-[5%]">{{ __('spending.active') }}</th>
                            <th x-show="cols.actions" class="px-6 py-3 font-medium text-center w-[5%] print:hidden">{{ __('spending.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="sheet-body" class="divide-y divide-slate-100">
                        @foreach($groups as $index => $group)
                        <tr class="bg-white hover:bg-slate-50 transition-colors group/row" :class="editingId === {{ $group->id }} ? 'bg-indigo-50/20' : ''">
                            <td x-show="cols.id" class="px-6 py-4 text-center font-normal text-slate-400">
                                {{ $loop->iteration }} <input type="hidden" name="spendings[{{ $index }}][id]" value="{{ $group->id }}">
                            </td>
                            <td x-show="cols.code" class="p-1">
                                <input type="text" name="spendings[{{ $index }}][code]" value="{{ $group->code }}" class="sheet-input font-normal uppercase text-slate-500" readonly>
                            </td>
                            <td x-show="cols.name" class="p-1">
                                <span x-show="editingId !== {{ $group->id }}" class="px-3 block text-slate-700 font-bold truncate">{{ $group->name }}</span>
                                <input x-show="editingId === {{ $group->id }}" id="input-name-{{ $group->id }}" type="text" name="spendings[{{ $index }}][name]" value="{{ $group->name }}" class="sheet-input font-bold text-slate-700">
                            </td>
                            <td x-show="cols.branch" class="p-1">
                                <span x-show="editingId !== {{ $group->id }}" class="px-3 block text-slate-600 font-normal truncate">{{ $group->branch->name ?? '-' }}</span>
                                <select x-show="editingId === {{ $group->id }}" name="spendings[{{ $index }}][branch_id]" class="sheet-input font-normal text-slate-700">
                                    <option value="" disabled>{{ __('spending.select_branch') }}</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ $group->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td x-show="cols.user" class="px-6 py-3 text-center text-[10px] text-slate-400 font-normal truncate print:hidden">{{ $group->creator->name ?? '-' }}</td>
                            <td x-show="cols.created_at" class="px-6 py-3 text-center text-xs text-slate-400 font-mono font-normal">
                                {{ $group->created_at ? $group->created_at->format('Y-m-d H:i') : '-' }}
                            </td>
                            <td x-show="cols.active" class="px-6 py-4 text-center">
                                {{-- VISUAL LOCK: Uses pointer-events-none instead of disabled so data submits --}}
                                <input type="checkbox" 
                                       name="spendings[{{ $index }}][is_active]" 
                                       value="1" 
                                       {{ $group->is_active ? 'checked' : '' }} 
                                       :class="{ 'pointer-events-none opacity-50': editingId !== {{ $group->id }} }"
                                       class="w-4 h-4 text-indigo-600 rounded border-slate-300 cursor-pointer">
                            </td>
                            <td x-show="cols.actions" class="px-6 py-3 text-center print:hidden">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" @click="saveRow()" x-show="editingId === {{ $group->id }}" class="w-7 h-7 flex items-center justify-center bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg shadow-sm transition transform active:scale-95">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                    
                                    <div x-show="editingId !== {{ $group->id }}" class="flex items-center gap-1">
                                        <button type="button" @click="startEdit({{ $group->id }})" class="p-1.5 text-slate-400 hover:text-blue-600 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                        <button type="button" onclick="deleteDatabaseRow({{ $group->id }})" class="p-1.5 text-slate-400 hover:text-red-600 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
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
            const nextId = tableBody.querySelectorAll('tr').length + 1;

            let branchOptions = '<option value="" disabled>{{ __('spending.select_branch') }}</option>';
            @foreach($branches as $branch) 
                branchOptions += `<option value="{{ $branch->id }}" ${ "{{ $branch->id }}" == userBranchId ? 'selected' : '' }>{{ $branch->name }}</option>`; 
            @endforeach

            const dateString = new Date().toLocaleString('sv-SE', { timeZone: 'Asia/Baghdad' }).replace('T', ' ').slice(0, 16);

            const rowHtml = `
            <tr class="bg-blue-50/40 border-b border-blue-100 transition-all duration-300">
                <td x-show="cols.id" class="px-6 py-3 text-center font-normal text-indigo-600">${nextId}</td>
                <td x-show="cols.code" class="p-1"><input type="text" value="${nextId}" class="sheet-input font-normal uppercase text-slate-400" readonly></td>
                <td x-show="cols.name" class="p-1"><input type="text" name="spendings[${index}][name]" class="sheet-input font-bold text-slate-700" placeholder="{{ __('spending.name') }}" autofocus></td>
                <td x-show="cols.branch" class="p-1"><select name="spendings[${index}][branch_id]" class="sheet-input font-normal text-slate-700">${branchOptions}</select></td>
                <td x-show="cols.user" class="px-6 py-3 text-center text-[10px] text-slate-400 italic font-normal">{{ Auth::user()->name }}</td>
                <td x-show="cols.created_at" class="px-6 py-3 text-center text-xs text-indigo-400 font-mono font-normal">${dateString}</td>
                <td x-show="cols.active" class="px-6 py-4 text-center">
                    {{-- New rows are fully editable immediately --}}
                    <input type="checkbox" name="spendings[${index}][is_active]" value="1" checked class="w-4 h-4 text-indigo-600 rounded border-slate-300 cursor-pointer">
                </td>
                <td x-show="cols.actions" class="px-6 py-3 text-center print:hidden">
                    <div class="flex items-center justify-center gap-1">
                        <button type="button" onclick="document.getElementById('sheet-form').submit()" class="w-7 h-7 flex items-center justify-center bg-emerald-500 text-white rounded-lg shadow-sm transition transform active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </button>
                        <button type="button" onclick="this.closest('tr').remove()" class="w-7 h-7 flex items-center justify-center text-slate-400 hover:text-red-500 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </td>
            </tr>`;
            
            tableBody.insertAdjacentHTML('beforeend', rowHtml);
            setTimeout(() => { tableBody.lastElementChild.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 100);
        }

        function deleteDatabaseRow(id) {
            const form = document.getElementById('delete-form');
            form.action = "{{ route('group-spending.destroy', ':id') }}".replace(':id', id);
            window.confirmAction('delete-form', "{{ __('spending.delete_confirm') }}");
        }
    </script>
</x-app-layout>