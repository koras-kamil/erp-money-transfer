<x-app-layout>
    {{-- STYLES --}}
    <style>
        .sheet-input { width: 100%; border: 1px solid transparent; padding: 6px; border-radius: 4px; outline: none; transition: all 0.2s; font-size: 0.9rem; }
        .sheet-input[readonly] { background-color: transparent; color: #64748b; cursor: default; }
        .sheet-select { width: 100%; border: 1px solid transparent; padding: 6px; border-radius: 4px; outline: none; transition: all 0.2s; font-size: 0.9rem; cursor: pointer; }
        .sheet-select.locked { background-color: transparent; color: #64748b; pointer-events: none; appearance: none; border-color: transparent; }
        .sheet-input:not([readonly]):focus, .sheet-select:not(.locked):focus { background-color: white; border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2); }
        .sheet-input:not([readonly]), .sheet-select:not(.locked) { background-color: white; border-color: #e2e8f0; color: #1e293b; cursor: text; }
        @media print { .no-print, button, a { display: none !important; } }
    </style>

    <div x-data="{ 
        activeTab: '{{ request('tab', 'groups') }}',
        editingId: null,
        // Columns config
        cols: { id: true, parent: true, name: true, desc: true, active: true, actions: true },
        toggleAll(value) { for (let key in this.cols) { this.cols[key] = value; } },
        
        startEdit(id) { 
            this.editingId = id; 
            setTimeout(() => { document.getElementById('input-name-'+id)?.focus(); }, 100);
        },
        saveRow(formId) {
            document.getElementById(formId).submit();
        }
    }" class="py-6 w-full min-w-0" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

        {{-- UNIFIED TOOLBAR --}}
        <div class="mx-4 mb-4 bg-white p-3 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4 no-print">

            {{-- 1. TABS --}}
            <div class="bg-slate-100 p-1 rounded-lg flex items-center">
                <a href="{{ route('profit.index', ['tab' => 'groups']) }}" 
                   class="px-4 py-2 text-sm font-bold rounded-md transition-all {{ request('tab', 'groups') === 'groups' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    {{ __('profit.menu_groups') }}
                </a>
                <a href="{{ route('profit.index', ['tab' => 'types']) }}" 
                   class="px-4 py-2 text-sm font-bold rounded-md transition-all {{ request('tab') === 'types' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    {{ __('profit.menu_types') }}
                </a>
            </div>

            {{-- 2. TOOLS --}}
            <div class="flex flex-wrap items-center gap-2">
                {{-- Trash --}}
                <a href="{{ route('profit.trash') }}" class="w-9 h-9 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition border border-red-100" title="{{ __('currency.trash') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </a>

                {{-- Manage Columns --}}
                <div x-data="{ openDropdown: false }" class="relative">
                    <button @click="openDropdown = !openDropdown" @click.away="openDropdown = false" class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-50 text-slate-600 border border-slate-200 hover:bg-white hover:text-indigo-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                    <div x-show="openDropdown" class="absolute top-full mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 z-50 p-2 ltr:right-0 rtl:left-0" x-cloak>
                        <div class="flex gap-2 mb-2 pb-2 border-b border-slate-100">
                            <button @click="toggleAll(true)" class="flex-1 text-[10px] bg-indigo-50 text-indigo-600 py-1 rounded hover:bg-indigo-100 font-bold">ALL</button>
                            <button @click="toggleAll(false)" class="flex-1 text-[10px] bg-slate-50 text-slate-600 py-1 rounded hover:bg-slate-100 font-bold">NONE</button>
                        </div>
                        <div class="space-y-1 max-h-60 overflow-y-auto">
                            <template x-if="activeTab === 'groups'">
                                <div>@foreach(['id'=>'#', 'name'=>'Name', 'desc'=>'Description', 'active'=>'Active'] as $key => $label)<label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 rounded cursor-pointer"><input type="checkbox" x-model="cols.{{ $key }}" class="rounded text-indigo-600 w-4 h-4 border-slate-300"><span class="text-xs text-slate-700 font-medium">{{ $label }}</span></label>@endforeach</div>
                            </template>
                            <template x-if="activeTab === 'types'">
                                <div>@foreach(['id'=>'#', 'parent'=>'Parent', 'name'=>'Name', 'desc'=>'Description', 'active'=>'Active'] as $key => $label)<label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 rounded cursor-pointer"><input type="checkbox" x-model="cols.{{ $key }}" class="rounded text-indigo-600 w-4 h-4 border-slate-300"><span class="text-xs text-slate-700 font-medium">{{ $label }}</span></label>@endforeach</div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Print --}}
                <button onclick="window.print()" class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-700 text-white hover:bg-slate-800 transition shadow-md shadow-slate-300/50" title="{{ __('currency.print') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                </button>

                {{-- Add New --}}
                <button type="button" @click="activeTab === 'groups' ? addNewGroup() : addNewType()" class="w-9 h-9 flex items-center justify-center rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition" title="{{ __('profit.add_group') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
            </div>
        </div>

        {{-- ==================== TABLE 1: GROUPS ==================== --}}
        <div x-show="activeTab === 'groups'" class="relative overflow-x-auto bg-white shadow-sm rounded-xl border border-slate-200 mx-4">
            <form id="form-groups" action="{{ route('profit.groups.store') }}" method="POST">
                @csrf
                <table class="w-full text-sm text-left rtl:text-right text-slate-500">
                    <thead class="text-xs text-slate-700 uppercase bg-blue-50/50 border-b border-blue-100">
                        <tr>
                            <th x-show="cols.id" class="px-4 py-3 w-[5%] text-center">#</th>
                            <th x-show="cols.name" class="px-4 py-3 w-[35%]">{{ __('profit.name') }}</th>
                            <th x-show="cols.desc" class="px-4 py-3 w-[45%]">{{ __('profit.description') }}</th>
                            <th x-show="cols.active" class="px-4 py-3 w-[10%] text-center">{{ __('profit.active') }}</th>
                            <th x-show="cols.actions" class="px-4 py-3 w-[5%] text-center print:hidden">{{ __('profit.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="body-groups" class="divide-y divide-slate-100">
                        @foreach($groups as $index => $group)
                        <tr class="hover:bg-slate-50 transition-colors group/row" :class="editingId === {{ $group->id }} ? 'bg-indigo-50/20' : ''">
                            {{-- ID --}}
                            <td x-show="cols.id" class="px-4 py-2 text-center font-medium text-slate-400">
                                {{ $loop->iteration }}
                                <input type="hidden" name="groups[{{ $index }}][id]" value="{{ $group->id }}">
                            </td>
                            
                            {{-- Name --}}
                            <td x-show="cols.name" class="px-4 py-2">
                                <span x-show="editingId !== {{ $group->id }}" class="px-2 block text-slate-700 font-bold">{{ $group->name }}</span>
                                <input x-show="editingId === {{ $group->id }}" id="input-name-{{ $group->id }}" type="text" name="groups[{{ $index }}][name]" value="{{ $group->name }}" class="sheet-input font-bold text-slate-700">
                            </td>

                            {{-- Description --}}
                            <td x-show="cols.desc" class="px-4 py-2">
                                <span x-show="editingId !== {{ $group->id }}" class="px-2 block text-slate-500">{{ $group->description }}</span>
                                <input x-show="editingId === {{ $group->id }}" type="text" name="groups[{{ $index }}][description]" value="{{ $group->description }}" class="sheet-input">
                            </td>

                            {{-- Active --}}
                            <td x-show="cols.active" class="px-4 py-2 text-center">
                                <input type="checkbox" name="groups[{{ $index }}][is_active]" value="1" {{ $group->is_active ? 'checked' : '' }} 
                                       :disabled="editingId !== {{ $group->id }}"
                                       class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer disabled:opacity-50">
                            </td>

                            {{-- Actions --}}
                            <td x-show="cols.actions" class="px-4 py-2 text-center print:hidden">
                                <div class="relative h-8 flex items-center justify-center w-full">
                                    {{-- Edit Mode --}}
                                    <div x-show="editingId === {{ $group->id }}" 
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 scale-90"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         class="absolute inset-0 flex items-center justify-center">
                                        <button type="button" @click="saveRow('form-groups')" class="w-7 h-7 flex items-center justify-center bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg shadow-sm transition transform active:scale-95">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                    </div>
                                    {{-- View Mode --}}
                                    <div x-show="editingId !== {{ $group->id }}" 
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 scale-90"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         class="flex items-center justify-center gap-1">
                                        <button type="button" @click="startEdit({{ $group->id }})" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                        <button type="button" onclick="deleteRow('groups', {{ $group->id }})" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </form>
        </div>

        {{-- ==================== TABLE 2: TYPES ==================== --}}
        <div x-show="activeTab === 'types'" class="relative overflow-x-auto bg-white shadow-sm rounded-xl border border-slate-200 mx-4" style="display: none;">
            <form id="form-types" action="{{ route('profit.types.store') }}" method="POST">
                @csrf
                <table class="w-full text-sm text-left rtl:text-right text-slate-500">
                    <thead class="text-xs text-slate-700 uppercase bg-blue-50/50 border-b border-blue-100">
                        <tr>
                            <th x-show="cols.id" class="px-4 py-3 w-[5%] text-center">#</th>
                            <th x-show="cols.parent" class="px-4 py-3 w-[20%]">{{ __('profit.parent_group') }}</th>
                            <th x-show="cols.name" class="px-4 py-3 w-[25%]">{{ __('profit.name') }}</th>
                            <th x-show="cols.desc" class="px-4 py-3 w-[35%]">{{ __('profit.description') }}</th>
                            <th x-show="cols.active" class="px-4 py-3 w-[10%] text-center">{{ __('profit.active') }}</th>
                            <th x-show="cols.actions" class="px-4 py-3 w-[5%] text-center print:hidden">{{ __('profit.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="body-types" class="divide-y divide-slate-100">
                        @foreach($types as $index => $type)
                        <tr class="hover:bg-slate-50 transition-colors group/row" :class="editingId === {{ $type->id }} ? 'bg-indigo-50/20' : ''">
                            {{-- ID --}}
                            <td x-show="cols.id" class="px-4 py-2 text-center font-medium text-slate-400">
                                {{ $loop->iteration }}
                                <input type="hidden" name="types[{{ $index }}][id]" value="{{ $type->id }}">
                            </td>

                            {{-- Parent Group --}}
                            <td x-show="cols.parent" class="px-4 py-2">
                                <span x-show="editingId !== {{ $type->id }}" class="px-2 block text-slate-600">{{ $type->group->name ?? '-' }}</span>
                                <select x-show="editingId === {{ $type->id }}" name="types[{{ $index }}][profit_group_id]" class="sheet-select w-full">
                                    <option disabled>{{ __('profit.select_group') }}</option>
                                    @foreach($activeGroups as $g)
                                        <option value="{{ $g->id }}" {{ $type->profit_group_id == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                                    @endforeach
                                </select>
                            </td>

                            {{-- Name --}}
                            <td x-show="cols.name" class="px-4 py-2">
                                <span x-show="editingId !== {{ $type->id }}" class="px-2 block text-slate-700 font-bold">{{ $type->name }}</span>
                                <input x-show="editingId === {{ $type->id }}" id="input-name-{{ $type->id }}" type="text" name="types[{{ $index }}][name]" value="{{ $type->name }}" class="sheet-input font-bold text-slate-700">
                            </td>

                            {{-- Description --}}
                            <td x-show="cols.desc" class="px-4 py-2">
                                <span x-show="editingId !== {{ $type->id }}" class="px-2 block text-slate-500">{{ $type->description }}</span>
                                <input x-show="editingId === {{ $type->id }}" type="text" name="types[{{ $index }}][description]" value="{{ $type->description }}" class="sheet-input">
                            </td>

                            {{-- Active --}}
                            <td x-show="cols.active" class="px-4 py-2 text-center">
                                <input type="checkbox" name="types[{{ $index }}][is_active]" value="1" {{ $type->is_active ? 'checked' : '' }} 
                                       :disabled="editingId !== {{ $type->id }}"
                                       class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer disabled:opacity-50">
                            </td>

                            {{-- Actions --}}
                            <td x-show="cols.actions" class="px-4 py-2 text-center print:hidden">
                                <div class="relative h-8 flex items-center justify-center w-full">
                                    {{-- Edit Mode --}}
                                    <div x-show="editingId === {{ $type->id }}" class="absolute inset-0 flex items-center justify-center">
                                        <button type="button" @click="saveRow('form-types')" class="w-7 h-7 flex items-center justify-center bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg shadow-sm transition transform active:scale-95">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                    </div>
                                    {{-- View Mode --}}
                                    <div x-show="editingId !== {{ $type->id }}" class="flex items-center justify-center gap-1">
                                        <button type="button" @click="startEdit({{ $type->id }})" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                        <button type="button" onclick="deleteRow('types', {{ $type->id }})" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
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
        // 1. ADD NEW GROUP ROW
        function addNewGroup() {
            const tableBody = document.getElementById('body-groups');
            const index = Date.now();
            const rowCount = tableBody.querySelectorAll('tr').length;
            const nextId = rowCount + 1;

            const row = `
                <tr class="bg-blue-50/40 border-b border-blue-100 transition-all duration-300">
                    <td x-show="cols.id" class="px-4 py-2 text-center font-bold text-blue-600">${nextId}</td>
                    <td x-show="cols.name" class="px-4 py-2"><input type="text" name="groups[${index}][name]" class="sheet-input font-bold" placeholder="{{ __('profit.name') }}" autofocus></td>
                    <td x-show="cols.desc" class="px-4 py-2"><input type="text" name="groups[${index}][description]" class="sheet-input" placeholder="..."></td>
                    <td x-show="cols.active" class="px-4 py-2 text-center"><input type="checkbox" name="groups[${index}][is_active]" value="1" checked class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer"></td>
                    <td x-show="cols.actions" class="px-4 py-2 text-center print:hidden">
                        <div class="flex items-center justify-center gap-1">
                            <button type="button" onclick="document.getElementById('form-groups').submit()" class="w-7 h-7 flex items-center justify-center bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg shadow-sm transition transform active:scale-95"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></button>
                            <button type="button" onclick="this.closest('tr').remove()" class="w-7 h-7 flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                    </td>
                </tr>`;
            
            tableBody.insertAdjacentHTML('beforeend', row);
            setTimeout(() => { tableBody.lastElementChild.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 100);
        }

        // 2. ADD NEW TYPE ROW
        function addNewType() {
            const tableBody = document.getElementById('body-types');
            const index = Date.now();
            const rowCount = tableBody.querySelectorAll('tr').length;
            const nextId = rowCount + 1;

            let groupOptions = '<option disabled selected>{{ __('profit.select_group') }}</option>';
            @foreach($activeGroups as $g) groupOptions += '<option value="{{ $g->id }}">{{ $g->name }}</option>'; @endforeach

            const row = `
                <tr class="bg-blue-50/40 border-b border-blue-100 transition-all duration-300">
                    <td x-show="cols.id" class="px-4 py-2 text-center font-bold text-blue-600">${nextId}</td>
                    <td x-show="cols.parent" class="px-4 py-2"><select name="types[${index}][profit_group_id]" class="sheet-select w-full">${groupOptions}</select></td>
                    <td x-show="cols.name" class="px-4 py-2"><input type="text" name="types[${index}][name]" class="sheet-input font-bold" placeholder="{{ __('profit.name') }}" autofocus></td>
                    <td x-show="cols.desc" class="px-4 py-2"><input type="text" name="types[${index}][description]" class="sheet-input" placeholder="..."></td>
                    <td x-show="cols.active" class="px-4 py-2 text-center"><input type="checkbox" name="types[${index}][is_active]" value="1" checked class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer"></td>
                    <td x-show="cols.actions" class="px-4 py-2 text-center print:hidden">
                        <div class="flex items-center justify-center gap-1">
                            <button type="button" onclick="document.getElementById('form-types').submit()" class="w-7 h-7 flex items-center justify-center bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg shadow-sm transition transform active:scale-95"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></button>
                            <button type="button" onclick="this.closest('tr').remove()" class="w-7 h-7 flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                    </td>
                </tr>`;
            
            tableBody.insertAdjacentHTML('beforeend', row);
            setTimeout(() => { tableBody.lastElementChild.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 100);
        }

        // 3. DELETE LOGIC
        function deleteRow(type, id) {
            const form = document.getElementById('delete-form');
            if(type === 'groups') {
                form.action = "{{ route('profit.groups.destroy', ':id') }}".replace(':id', id);
            } else {
                form.action = "{{ route('profit.types.destroy', ':id') }}".replace(':id', id);
            }
            window.confirmAction('delete-form', "{{ __('profit.delete_confirm') }}");
        }
    </script>
</x-app-layout>