<x-app-layout>
    {{-- HEADER SLOT: Title & Tabs Stacked --}}
    <x-slot name="header">
        <div class="flex flex-col gap-4">
            {{-- 1. TITLE --}}
            <h2 class="text-xl font-bold leading-tight text-slate-800">
                {{ __('profit.groups_title') }}
            </h2>

            {{-- 2. NAVIGATION TABS --}}
            {{-- We use GET parameters (?tab=...) to switch tabs while keeping the header style --}}
            <div class="flex p-1 bg-white/50 border border-slate-200 rounded-lg shadow-sm w-fit">
                <a href="{{ route('profit.index', ['tab' => 'groups']) }}" 
                   class="px-4 py-1.5 text-xs font-bold uppercase tracking-wider rounded-md transition-all {{ request('tab', 'groups') === 'groups' ? 'bg-indigo-600 text-white shadow' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100' }}">
                    {{ __('profit.menu_groups') }}
                </a>
                <a href="{{ route('profit.index', ['tab' => 'types']) }}" 
                   class="px-4 py-1.5 text-xs font-bold uppercase tracking-wider rounded-md transition-all {{ request('tab') === 'types' ? 'bg-indigo-600 text-white shadow' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100' }}">
                    {{ __('profit.menu_types') }}
                </a>
            </div>
        </div>
    </x-slot>

    {{-- STYLES --}}
    <style>
        /* Base Input Style */
        .sheet-input { 
            width: 100%; border: 1px solid transparent; padding: 6px; border-radius: 4px; 
            outline: none; transition: all 0.2s; font-size: 0.9rem;
        }

        /* LOCKED STATE */
        .sheet-input[readonly] { background-color: transparent; color: #64748b; cursor: default; }
        .sheet-select.locked { background-color: transparent; color: #64748b; pointer-events: none; appearance: none; border-color: transparent; }

        /* EDIT STATE */
        .sheet-input:not([readonly]):focus, .sheet-select:not(.locked):focus { background-color: white; border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2); }
        .sheet-input:not([readonly]), .sheet-select:not(.locked) { background-color: white; border-color: #e2e8f0; color: #1e293b; cursor: text; }

        @media print {
            @page { size: A4 portrait; margin: 5mm; }
            aside, nav, header, .print\:hidden, button, a, .no-print, th:last-child, td:last-child { display: none !important; }
            body { background: white !important; }
            table { width: 100% !important; border-collapse: collapse !important; font-size: 8pt !important; }
            th, td { border: 1px solid #000 !important; padding: 3px 2px !important; text-align: center !important; }
            #print-header { display: block !important; text-align: center; border-bottom: 2px solid #000; margin-bottom: 10px; }
            .sheet-input, .sheet-select { border: none !important; padding: 0 !important; background: transparent !important; appearance: none; }
        }
        #print-header { display: none; }
    </style>

    {{-- MAIN CONTENT --}}
    <div x-data="{ 
        activeTab: '{{ request('tab', 'groups') }}',
        hasChanges: false,
        cols: { id: true, parent: true, name: true, desc: true, active: true, actions: true },
        toggleAll(value) { for (let key in this.cols) { this.cols[key] = value; } }
    }" class="py-6">

        {{-- Print Header --}}
        <div id="print-header">
            <h1 class="uppercase font-bold text-2xl">{{ config('app.name') }}</h1>
            <h2 class="text-lg">{{ request('tab') === 'types' ? __('profit.types_title') : __('profit.groups_title') }}</h2>
            <p class="text-[10px] text-gray-500 mt-1">{{ date('Y-m-d H:i') }} | {{ Auth::user()->name }}</p>
        </div>

        {{-- ACTIONS TOOLBAR --}}
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 print:hidden px-4">
            
            {{-- Trash Button --}}
            <button class="text-xs font-bold text-red-500 hover:text-red-700 flex items-center gap-1 bg-red-50 px-3 py-1.5 rounded-full border border-red-100 transition">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                {{ __('currency.trash') }}
            </button>
            
            {{-- Right Buttons --}}
            <div class="flex flex-wrap items-center gap-2">
                {{-- Manage View Dropdown --}}
                <div x-data="{ openDropdown: false }" class="relative">
                    <button @click="openDropdown = !openDropdown" @click.away="openDropdown = false" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-600 px-3 py-2 rounded-lg text-xs font-bold hover:bg-slate-50 shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>{{ __('cash_box.manage_view') ?? 'View' }}</span>
                    </button>
                    <div x-show="openDropdown" class="absolute ltr:right-0 rtl:left-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 z-50 p-2" x-cloak>
                        <div class="flex gap-2 mb-2 pb-2 border-b border-slate-100">
                            <button @click="toggleAll(true)" class="flex-1 text-[10px] bg-indigo-50 text-indigo-600 py-1 rounded hover:bg-indigo-100 font-bold">ALL</button>
                            <button @click="toggleAll(false)" class="flex-1 text-[10px] bg-slate-50 text-slate-600 py-1 rounded hover:bg-slate-100 font-bold">NONE</button>
                        </div>
                        <div class="space-y-1 max-h-60 overflow-y-auto">
                            @foreach(['id'=>'#', 'parent'=>'Parent Group', 'name'=>'Name', 'desc'=>'Description', 'active'=>'Active'] as $key => $label)
                                <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 rounded cursor-pointer"><input type="checkbox" x-model="cols.{{ $key }}" class="rounded text-indigo-600 w-4 h-4 border-slate-300"><span class="text-xs text-slate-700 font-medium">{{ $label }}</span></label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <button onclick="window.print()" class="bg-slate-700 text-white px-3 py-2 rounded-lg text-xs font-bold hover:bg-slate-800 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    {{ __('currency.print') ?? 'Print' }}
                </button>
                
                {{-- Dynamic Add Button --}}
                <button type="button" @click="activeTab === 'groups' ? addNewGroup() : addNewType(); hasChanges = true" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-xs font-bold shadow-lg shadow-blue-500/30">
                    {{ __('profit.add_group') }}
                </button>

                {{-- Save Button --}}
                <button type="button" 
                        @click="hasChanges ? document.getElementById(activeTab === 'groups' ? 'form-groups' : 'form-types').submit() : Swal.fire({icon: 'info', title: '{{ __('currency.no_changes') ?? 'No changes' }}'})"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-xs font-bold shadow-lg shadow-emerald-500/30">
                    {{ __('profit.save_changes') }}
                </button>
            </div>
        </div>

        {{-- TABLE 1: GROUPS --}}
        <div x-show="activeTab === 'groups'" class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm mx-4">
            <form id="form-groups" action="{{ route('profit.groups.store') }}" method="POST">
                @csrf
                <table class="w-full text-sm text-left rtl:text-right text-slate-500" @input="hasChanges = true" @change="hasChanges = true">
                    <thead class="text-xs text-slate-700 uppercase bg-blue-100/50 border-b border-blue-200">
                        <tr>
                            <th x-show="cols.id" class="px-2 py-3 w-[5%] text-center">#</th>
                            <th x-show="cols.name" class="px-2 py-3 w-[30%]">{{ __('profit.name') }}</th>
                            <th x-show="cols.desc" class="px-2 py-3 w-[45%]">{{ __('profit.description') }}</th>
                            <th x-show="cols.active" class="px-2 py-3 w-[10%] text-center">{{ __('profit.active') }}</th>
                            <th x-show="cols.actions" class="px-2 py-3 w-[10%] text-center print:hidden">{{ __('profit.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="body-groups" class="divide-y divide-slate-100">
                        @foreach($groups as $index => $group)
                        <tr class="hover:bg-slate-50 transition-colors group/row">
                            <td x-show="cols.id" class="px-2 py-2 text-center font-medium text-slate-400">
                                {{ $loop->iteration }}
                                <input type="hidden" name="groups[{{ $index }}][id]" value="{{ $group->id }}">
                            </td>
                            <td x-show="cols.name" class="px-2 py-2">
                                <input type="text" name="groups[{{ $index }}][name]" value="{{ $group->name }}" class="sheet-input font-bold text-slate-700" readonly>
                            </td>
                            <td x-show="cols.desc" class="px-2 py-2">
                                <input type="text" name="groups[{{ $index }}][description]" value="{{ $group->description }}" class="sheet-input" readonly>
                            </td>
                            <td x-show="cols.active" class="px-2 py-2 text-center">
                                <input type="checkbox" name="groups[{{ $index }}][is_active]" value="1" {{ $group->is_active ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer">
                            </td>
                            <td x-show="cols.actions" class="px-2 py-2 text-center print:hidden">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" onclick="enableRow(this)" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                    <button type="button" onclick="deleteRow('groups', {{ $group->id }})" class="p-1.5 text-red-600 hover:bg-red-50 rounded transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </form>
        </div>

        {{-- TABLE 2: TYPES --}}
        <div x-show="activeTab === 'types'" class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm mx-4" style="display: none;">
            <form id="form-types" action="{{ route('profit.types.store') }}" method="POST">
                @csrf
                <table class="w-full text-sm text-left rtl:text-right text-slate-500" @input="hasChanges = true" @change="hasChanges = true">
                    <thead class="text-xs text-slate-700 uppercase bg-blue-100/50 border-b border-blue-200">
                        <tr>
                            <th x-show="cols.id" class="px-2 py-3 w-[5%] text-center">#</th>
                            <th x-show="cols.parent" class="px-2 py-3 w-[25%]">{{ __('profit.parent_group') }}</th>
                            <th x-show="cols.name" class="px-2 py-3 w-[25%]">{{ __('profit.name') }}</th>
                            <th x-show="cols.desc" class="px-2 py-3 w-[30%]">{{ __('profit.description') }}</th>
                            <th x-show="cols.active" class="px-2 py-3 w-[8%] text-center">{{ __('profit.active') }}</th>
                            <th x-show="cols.actions" class="px-2 py-3 w-[7%] text-center print:hidden">{{ __('profit.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="body-types" class="divide-y divide-slate-100">
                        @foreach($types as $index => $type)
                        <tr class="hover:bg-slate-50 transition-colors group/row">
                            <td x-show="cols.id" class="px-2 py-2 text-center font-medium text-slate-400">
                                {{ $loop->iteration }}
                                <input type="hidden" name="types[{{ $index }}][id]" value="{{ $type->id }}">
                            </td>
                            <td x-show="cols.parent" class="px-2 py-2">
                                <select name="types[{{ $index }}][profit_group_id]" class="sheet-select w-full locked">
                                    <option disabled>{{ __('profit.select_group') }}</option>
                                    @foreach($activeGroups as $g)
                                        <option value="{{ $g->id }}" {{ $type->profit_group_id == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td x-show="cols.name" class="px-2 py-2">
                                <input type="text" name="types[{{ $index }}][name]" value="{{ $type->name }}" class="sheet-input font-bold text-slate-700" readonly>
                            </td>
                            <td x-show="cols.desc" class="px-2 py-2">
                                <input type="text" name="types[{{ $index }}][description]" value="{{ $type->description }}" class="sheet-input" readonly>
                            </td>
                            <td x-show="cols.active" class="px-2 py-2 text-center">
                                <input type="checkbox" name="types[{{ $index }}][is_active]" value="1" {{ $type->is_active ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer">
                            </td>
                            <td x-show="cols.actions" class="px-2 py-2 text-center print:hidden">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" onclick="enableRow(this)" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                    <button type="button" onclick="deleteRow('types', {{ $type->id }})" class="p-1.5 text-red-600 hover:bg-red-50 rounded transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </form>
        </div>

    </div>

    {{-- DELETE FORM & SCRIPTS --}}
    <form id="delete-form" action="" method="POST" class="hidden">@csrf @method('DELETE')</form>
    
    <script>
        // 1. ENABLE EDIT MODE
        function enableRow(button) {
            const row = button.closest('tr');
            row.querySelectorAll('.sheet-input').forEach(input => input.removeAttribute('readonly'));
            row.querySelectorAll('.sheet-select').forEach(select => select.classList.remove('locked'));
            // Focus on the Name field
            const nameField = row.querySelector('input[name*="[name]"]');
            if(nameField) nameField.focus();
            
            // Trigger Alpine change detection
            document.querySelector('[x-data]').__x.$data.hasChanges = true;
        }

        // 2. ADD NEW GROUP
        function addNewGroup() {
            const index = Date.now();
            const row = `
                <tr class="bg-blue-50/50">
                    <td x-show="cols.id" class="px-2 py-2 text-center"><span class="bg-green-100 text-green-700 py-0.5 px-2 rounded text-[10px] font-bold">NEW</span></td>
                    <td x-show="cols.name" class="px-2 py-2"><input type="text" name="groups[${index}][name]" class="sheet-input font-bold" placeholder="{{ __('profit.name') }}" autofocus></td>
                    <td x-show="cols.desc" class="px-2 py-2"><input type="text" name="groups[${index}][description]" class="sheet-input" placeholder="..."></td>
                    <td x-show="cols.active" class="px-2 py-2 text-center"><input type="checkbox" name="groups[${index}][is_active]" value="1" checked class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer"></td>
                    <td x-show="cols.actions" class="px-2 py-2 text-center print:hidden">
                        <button type="button" onclick="this.closest('tr').remove()" class="p-1.5 text-slate-400 hover:text-red-500 rounded"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </td>
                </tr>`;
            document.getElementById('body-groups').insertAdjacentHTML('beforeend', row);
        }

        // 3. ADD NEW TYPE
        function addNewType() {
            const index = Date.now();
            let groupOptions = '<option disabled selected>{{ __('profit.select_group') }}</option>';
            @foreach($activeGroups as $g)
                groupOptions += '<option value="{{ $g->id }}">{{ $g->name }}</option>';
            @endforeach

            const row = `
                <tr class="bg-blue-50/50">
                    <td x-show="cols.id" class="px-2 py-2 text-center"><span class="bg-green-100 text-green-700 py-0.5 px-2 rounded text-[10px] font-bold">NEW</span></td>
                    <td x-show="cols.parent" class="px-2 py-2"><select name="types[${index}][profit_group_id]" class="sheet-select w-full">${groupOptions}</select></td>
                    <td x-show="cols.name" class="px-2 py-2"><input type="text" name="types[${index}][name]" class="sheet-input font-bold" placeholder="{{ __('profit.name') }}" autofocus></td>
                    <td x-show="cols.desc" class="px-2 py-2"><input type="text" name="types[${index}][description]" class="sheet-input" placeholder="..."></td>
                    <td x-show="cols.active" class="px-2 py-2 text-center"><input type="checkbox" name="types[${index}][is_active]" value="1" checked class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer"></td>
                    <td x-show="cols.actions" class="px-2 py-2 text-center print:hidden">
                        <button type="button" onclick="this.closest('tr').remove()" class="p-1.5 text-slate-400 hover:text-red-500 rounded"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </td>
                </tr>`;
            document.getElementById('body-types').insertAdjacentHTML('beforeend', row);
        }

        // 4. DELETE LOGIC
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