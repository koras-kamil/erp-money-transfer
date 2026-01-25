<x-app-layout>
    <x-slot name="header">
        {{ __('profit.menu_types') }} - {{ __('profit.trash') ?? 'Trash' }}
    </x-slot>

    {{-- STYLES --}}
    <style>
        @media print { .no-print, button, a { display: none !important; } }
        /* Checkbox Style */
        .select-checkbox { width: 1.1rem; height: 1.1rem; border-radius: 4px; border: 1px solid #cbd5e1; color: #6366f1; cursor: pointer; transition: all 0.2s; }
        .select-checkbox:focus { ring: 2px; ring-color: #e0e7ff; }
    </style>

    <div x-data="{ 
        selectedIds: [],
        // Convert IDs to array ensures 'Select All' works perfectly
        allIds: {{ json_encode($types->pluck('id')->toArray()) }},
        
        // Toggle single row selection
        toggleSelection(id) {
            if (this.selectedIds.includes(id)) {
                this.selectedIds = this.selectedIds.filter(i => i !== id);
            } else {
                this.selectedIds.push(id);
            }
        },

        // Toggle 'Select All'
        toggleAllSelection() {
            if (this.selectedIds.length === this.allIds.length) {
                this.selectedIds = [];
            } else {
                this.selectedIds = [...this.allIds];
            }
        },
        
        // BULK RESTORE
        bulkRestore() {
            if (this.selectedIds.length === 0) return;
            document.getElementById('bulk-restore-ids').value = JSON.stringify(this.selectedIds);
            document.getElementById('bulk-restore-form').submit();
        },

        // BULK FORCE DELETE
        bulkForceDelete() {
            if (this.selectedIds.length === 0) return;
            
            // Set IDs
            document.getElementById('bulk-force-delete-ids').value = JSON.stringify(this.selectedIds);

            // Use Custom Confirm Action
            if (window.confirmAction) {
                window.confirmAction('bulk-force-delete-form', '{{ __('profit.confirm_delete') ?? 'Are you sure?' }}');
            } else {
                if(confirm('{{ __('profit.confirm_delete') ?? 'Are you sure?' }}')) {
                    document.getElementById('bulk-force-delete-form').submit();
                }
            }
        }

    }" class="py-6 w-full min-w-0" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">
        
        {{-- TOOLBAR --}}
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 px-4 no-print">
            
            {{-- Title & Bulk Actions --}}
            <div class="flex items-center gap-3">
                <div class="p-2 bg-red-100 rounded-lg text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                
                <h3 x-show="selectedIds.length === 0" class="text-xl font-black text-slate-800 tracking-tight">{{ __('profit.menu_types') }} ({{ __('profit.trash') ?? 'Trash' }})</h3>

                {{-- BULK ACTIONS BAR (Appears when items selected) --}}
                <div x-show="selectedIds.length > 0" x-transition class="flex items-center gap-2" style="display: none;">
                    <span class="text-xs font-bold text-red-600 bg-red-50 px-2 py-1 rounded border border-red-100"><span x-text="selectedIds.length"></span> {{ __('profit.selected') }}</span>
                    
                    {{-- Restore Selected --}}
                    <button @click="bulkRestore()" class="px-3 py-1.5 bg-emerald-500 text-white text-xs font-bold rounded shadow-sm hover:bg-emerald-600 transition flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        {{ __('profit.restore_selected') ?? 'Restore' }}
                    </button>

                    {{-- Delete Selected --}}
                    <button @click="bulkForceDelete()" class="px-3 py-1.5 bg-red-600 text-white text-xs font-bold rounded shadow-sm hover:bg-red-700 transition flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        {{ __('profit.delete_selected') ?? 'Delete' }}
                    </button>
                    
                    {{-- Cancel --}}
                    <button @click="selectedIds = []" class="text-slate-400 hover:text-slate-600 text-xs underline px-2">{{ __('profit.cancel') ?? 'Cancel' }}</button>
                </div>
            </div>
            
            <a href="{{ route('profit.types.index') }}" class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm font-medium">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>{{ __('profit.back') ?? 'Back' }}</span>
            </a>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="relative overflow-x-auto bg-white shadow-sm rounded-xl border border-slate-200 mx-4">
            <table class="w-full text-sm text-left rtl:text-right text-slate-500">
                <thead class="text-xs text-slate-700 uppercase bg-red-50/50 border-b border-red-100">
                    <tr>
                        {{-- Select All Checkbox --}}
                        <th class="px-4 py-3 text-center w-[40px]">
                            <input type="checkbox" @click="toggleAllSelection()" :checked="selectedIds.length > 0 && selectedIds.length === allIds.length" class="select-checkbox bg-white">
                        </th>
                        <th class="px-6 py-3 font-bold text-center w-16">#</th>
                        <th class="px-6 py-3 font-bold">{{ __('profit.name') }}</th>
                        <th class="px-6 py-3 font-bold">{{ __('profit.parent_group') }}</th>
                        <th class="px-6 py-3 font-bold text-center">{{ __('profit.deleted_by') }}</th>
                        <th class="px-6 py-3 font-bold text-center">{{ __('profit.deleted_at') }}</th>
                        <th class="px-6 py-3 font-bold text-center w-32">{{ __('profit.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($types as $type)
                    <tr class="bg-white hover:bg-red-50/30 transition-colors group" :class="selectedIds.includes({{ $type->id }}) ? 'bg-red-50/60' : ''">
                        
                        {{-- Checkbox --}}
                        <td class="px-4 py-4 text-center">
                            {{-- Important: :value="{{ $type->id }}" binds as integer to match allIds --}}
                            <input type="checkbox" :value="{{ $type->id }}" x-model="selectedIds" class="select-checkbox">
                        </td>

                        <td class="px-6 py-4 text-center font-medium text-slate-400">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 font-bold text-slate-800">{{ $type->name }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $type->group->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-center">
                            @if($type->deleter)
                                <span class="text-xs font-bold text-slate-700">{{ $type->deleter->name }}</span>
                            @else
                                <span class="text-xs text-slate-400 italic">System</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center text-xs text-red-500 font-medium">
                            {{ $type->deleted_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                {{-- Single Restore --}}
                                <form action="{{ route('profit.types.restore', $type->id) }}" method="POST">
                                    @csrf 
                                    <button type="submit" class="p-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 rounded-lg transition-colors shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </button>
                                </form>
                                {{-- Single Delete --}}
                                <button onclick="confirmDelete('{{ route('profit.types.force-delete', $type->id) }}')" class="p-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg transition-colors shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400 italic">{{ __('profit.trash_empty') ?? 'Trash is empty' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4 px-4">{{ $types->links() }}</div>
    </div>
    
    {{-- HIDDEN FORMS FOR BULK ACTIONS --}}
    <form id="bulk-restore-form" action="{{ route('profit.types.bulk-restore') }}" method="POST" class="hidden">
        @csrf 
        <input type="hidden" name="ids" id="bulk-restore-ids">
    </form>

    <form id="bulk-force-delete-form" action="{{ route('profit.types.bulk-force-delete') }}" method="POST" class="hidden">
        @csrf 
        @method('DELETE')
        <input type="hidden" name="ids" id="bulk-force-delete-ids">
    </form>

    {{-- SINGLE DELETE FORM --}}
    <form id="force-delete-form" action="" method="POST" class="hidden">
        @csrf 
        @method('DELETE')
    </form>
    
    <script>
        function confirmDelete(url) {
            const form = document.getElementById('force-delete-form');
            form.action = url;

            // Use Custom Alert if available
            if (window.confirmAction) {
                window.confirmAction('force-delete-form', '{{ __('profit.confirm_delete') ?? 'Are you sure?' }}');
            } else {
                if(confirm("{{ __('profit.confirm_delete') ?? 'Are you sure?' }}")) {
                    form.submit();
                }
            }
        }
    </script>
</x-app-layout>