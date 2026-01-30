<x-app-layout>
    {{-- STYLES --}}
    <style>
        /* Shared Styles */
        .sheet-input { width: 100%; height: 100%; background: transparent; border: 1px solid transparent; padding: 10px 12px; font-size: 0.9rem; border-radius: 8px; transition: all 0.2s ease; font-weight: 600; color: #334155; }
        .sheet-input:hover { background-color: #f8fafc; border-color: #e2e8f0; }
        .sheet-input:focus { background-color: #ffffff; border-color: #6366f1; box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.1); outline: none; }
        .sheet-input::placeholder { color: #94a3b8; font-weight: 400; }

        .code-input { font-family: monospace; font-size: 0.85rem; letter-spacing: 0.05em; color: #6366f1; background-color: #f8fafc; border-color: #f1f5f9; }

        /* Select Input Specifics */
        select.sheet-input { cursor: pointer; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em; padding-right: 2.5rem; -webkit-appearance: none; appearance: none; }
        [dir="rtl"] select.sheet-input { background-position: left 0.5rem center; padding-right: 0.75rem; padding-left: 2.5rem; }

        .select-checkbox { width: 1.15rem; height: 1.15rem; border-radius: 6px; border: 2px solid #cbd5e1; color: #6366f1; cursor: pointer; transition: all 0.2s; }
        .select-checkbox:checked { border-color: #6366f1; }

        /* Modern Pills for Sub-Tabs */
        .pill-tab { position: relative; padding: 8px 24px; border-radius: 9999px; font-weight: 700; font-size: 0.85rem; transition: all 0.2s ease; border: 1px solid transparent; }
        .pill-active { background-color: #4f46e5; color: white; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3); }
        .pill-inactive { background-color: white; color: #64748b; border-color: #e2e8f0; }
        .pill-inactive:hover { background-color: #f8fafc; color: #334155; border-color: #cbd5e1; }

        /* Animations */
        @keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .new-row { animation: slideIn 0.2s ease-out forwards; background-color: #f0fdf4 !important; }
        
        .custom-scrollbar::-webkit-scrollbar { height: 8px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
    </style>

    <div x-data="zoneManager('{{ session('active_tab', 'cities') }}')" class="py-8 w-full min-w-0" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

        {{-- 1. HEADER & NAVIGATION --}}
        <div class="mx-4 md:mx-8 mb-8 flex flex-col md:flex-row justify-between items-end gap-6 no-print">
            
            {{-- Left Side: Title & Component --}}
            <div class="flex flex-col gap-4">
                <div>
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight">{{ __('account.zones_list') }}</h3>
                    <p class="text-sm text-slate-500 font-medium mt-1">{{ __('account.manage_full_page') }}</p>
                </div>
                
                {{-- NAV COMPONENT --}}
                <x-account-nav active="zones" />
            </div>

            {{-- Right Side: Sub-Tabs & Actions --}}
            <div class="flex flex-col md:flex-row items-center gap-4">
                
                {{-- Sub-Tabs (Cities/Neighborhoods) --}}
                <div class="flex items-center gap-2 bg-slate-100/50 p-1.5 rounded-full border border-slate-200/60">
                    <button @click="switchTab('cities')" class="pill-tab" :class="activeTab === 'cities' ? 'pill-active' : 'pill-inactive'">
                        {{ __('account.cities') }}
                    </button>
                    <button @click="switchTab('neighborhoods')" class="pill-tab" :class="activeTab === 'neighborhoods' ? 'pill-active' : 'pill-inactive'">
                        {{ __('account.neighborhoods') }}
                    </button>
                </div>

                {{-- Toolbar --}}
                <div class="flex items-center gap-3">
                    <div x-show="selectedIds.length > 0" x-transition.scale.origin.right class="flex items-center gap-2 bg-red-50 px-3 py-2 rounded-xl border border-red-100 shadow-sm">
                        <span class="text-xs font-bold text-red-600"><span x-text="selectedIds.length"></span> {{ __('account.selected') }}</span>
                        <button @click="bulkDelete()" class="p-1 text-red-500 hover:bg-red-200 rounded-lg transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                        <button @click="selectedIds = []" class="p-1 text-slate-400 hover:bg-slate-200 rounded-lg transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    
                    {{-- Add New Button --}}
                    <button @click="addNewRow()" class="h-10 px-6 bg-indigo-600 text-white text-sm font-bold rounded-xl shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:shadow-indigo-300 transition-all transform active:scale-95 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>{{ __('account.add_new') }}</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- 2. CITIES TAB --}}
        <div x-show="activeTab === 'cities'" class="bg-white shadow-xl shadow-slate-200/50 rounded-2xl border border-slate-200 mx-4 md:mx-8 overflow-hidden relative">
            <form id="cities-form" action="{{ route('zones.cities.store') }}" method="POST">
                @csrf
                <div class="overflow-x-auto custom-scrollbar relative min-h-[400px]">
                    <table class="w-full text-sm text-left rtl:text-right text-slate-500 whitespace-nowrap">
                        <thead class="text-xs text-slate-600 uppercase bg-slate-50/80 border-b border-slate-200 font-extrabold sticky top-0 z-20 backdrop-blur-md">
                            <tr>
                                <th class="px-4 py-4 w-[50px] text-center bg-slate-50/95"><input type="checkbox" @click="toggleAllSelection('cities')" class="select-checkbox bg-white"></th>
                                <th class="px-6 py-4 w-[60px] text-center bg-slate-50/95">#</th>
                                <th class="px-6 py-4 w-[15%]">{{ __('account.code') }}</th>
                                <th class="px-6 py-4 w-[40%]">{{ __('account.city_name') }}</th>
                                <th class="px-6 py-4 w-[20%] text-center">{{ __('account.created_by') }}</th>
                                <th class="px-6 py-4 w-[10%] text-center bg-slate-50/95">{{ __('account.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="cities-body" class="divide-y divide-slate-100">
                            @forelse($cities as $index => $city)
                            <tr class="hover:bg-indigo-50/30 transition duration-150 group">
                                <td class="px-4 py-3 text-center"><input type="checkbox" :value="{{ $city->id }}" x-model="selectedIds" class="select-checkbox"></td>
                                <td class="px-6 py-3 text-center font-mono text-xs font-bold text-slate-400">{{ $loop->iteration }} <input type="hidden" name="cities[{{ $index }}][id]" value="{{ $city->id }}"></td>
                                <td class="p-2"><input type="text" name="cities[{{ $index }}][code]" value="{{ $city->code }}" class="sheet-input code-input" placeholder="CODE"></td>
                                <td class="p-2"><input type="text" name="cities[{{ $index }}][city_name]" value="{{ $city->city_name }}" class="sheet-input" placeholder="{{ __('account.city_name') }}"></td>
                                <td class="px-6 py-3 text-center text-xs text-slate-400"><span class="px-2 py-1 bg-slate-100 border border-slate-200 rounded-md">{{ $city->creator->name ?? 'System' }}</span></td>
                                <td class="px-6 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2 opacity-30 group-hover:opacity-100 transition-all duration-200">
                                        <button type="button" @click="saveForm('cities-form')" class="w-8 h-8 flex items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white transition shadow-sm"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></button>
                                        <button type="button" onclick="deleteItem('{{ route('zones.cities.destroy', $city->id) }}')" class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition shadow-sm"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr class="no-data-row"><td colspan="6" class="py-16 text-center text-slate-400 flex flex-col items-center justify-center"><div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4"><svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></div><span class="text-sm font-medium text-slate-500">{{ __('account.none') }}</span></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
        </div>

        {{-- 4. NEIGHBORHOODS TAB --}}
        <div x-show="activeTab === 'neighborhoods'" class="bg-white shadow-xl shadow-slate-200/50 rounded-2xl border border-slate-200 mx-4 md:mx-8 overflow-hidden relative" style="display: none;">
            <form id="neighborhoods-form" action="{{ route('zones.neighborhoods.store') }}" method="POST">
                @csrf
                <div class="overflow-x-auto custom-scrollbar relative min-h-[400px]">
                    <table class="w-full text-sm text-left rtl:text-right text-slate-500 whitespace-nowrap">
                        <thead class="text-xs text-slate-600 uppercase bg-slate-50/80 border-b border-slate-200 font-extrabold sticky top-0 z-20 backdrop-blur-md">
                            <tr>
                                <th class="px-4 py-4 w-[50px] text-center bg-slate-50/95"><input type="checkbox" @click="toggleAllSelection('neighborhoods')" class="select-checkbox bg-white"></th>
                                <th class="px-6 py-4 w-[60px] text-center bg-slate-50/95">#</th>
                                <th class="px-6 py-4 w-[15%]">{{ __('account.code') }}</th>
                                <th class="px-6 py-4 w-[25%]">{{ __('account.city_name') }}</th>
                                <th class="px-6 py-4 w-[30%]">{{ __('account.neighborhood_name') }}</th>
                                <th class="px-6 py-4 w-[15%] text-center">{{ __('account.created_by') }}</th>
                                <th class="px-6 py-4 w-[10%] text-center bg-slate-50/95">{{ __('account.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="neighborhoods-body" class="divide-y divide-slate-100">
                            @forelse($neighborhoods as $index => $neigh)
                            <tr class="hover:bg-indigo-50/30 transition duration-150 group">
                                <td class="px-4 py-3 text-center"><input type="checkbox" :value="{{ $neigh->id }}" x-model="selectedIds" class="select-checkbox"></td>
                                <td class="px-6 py-3 text-center font-mono text-xs font-bold text-slate-400">{{ $loop->iteration }} <input type="hidden" name="neighborhoods[{{ $index }}][id]" value="{{ $neigh->id }}"></td>
                                <td class="p-2"><input type="text" name="neighborhoods[{{ $index }}][code]" value="{{ $neigh->code }}" class="sheet-input code-input" placeholder="---"></td>
                                <td class="p-2">
                                    <select name="neighborhoods[{{ $index }}][city_id]" class="sheet-input">
                                        @foreach($cities as $c) <option value="{{ $c->id }}" {{ $neigh->city_id == $c->id ? 'selected' : '' }}>{{ $c->city_name }}</option> @endforeach
                                    </select>
                                </td>
                                <td class="p-2"><input type="text" name="neighborhoods[{{ $index }}][neighborhood_name]" value="{{ $neigh->neighborhood_name }}" class="sheet-input" placeholder="{{ __('account.neighborhood_name') }}"></td>
                                <td class="px-6 py-3 text-center text-xs text-slate-400"><span class="px-2 py-1 bg-slate-100 border border-slate-200 rounded-md">{{ $neigh->creator->name ?? 'System' }}</span></td>
                                <td class="px-6 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2 opacity-30 group-hover:opacity-100 transition-all duration-200">
                                        <button type="button" @click="saveForm('neighborhoods-form')" class="w-8 h-8 flex items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white transition shadow-sm"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></button>
                                        <button type="button" onclick="deleteItem('{{ route('zones.neighborhoods.destroy', $neigh->id) }}')" class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition shadow-sm"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr class="no-data-row"><td colspan="7" class="py-16 text-center text-slate-400 flex flex-col items-center justify-center"><div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4"><svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div><span class="text-sm font-medium text-slate-500">{{ __('account.none') }}</span></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
        </div>

    </div>

    {{-- DELETE FORM (Hidden) --}}
    <form id="delete-form" action="" method="POST" class="hidden">@csrf @method('DELETE')</form>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('zoneManager', (defaultTab) => ({
                activeTab: defaultTab,
                selectedIds: [],
                
                switchTab(tab) {
                    this.activeTab = tab;
                    this.selectedIds = [];
                },

                saveForm(id) { document.getElementById(id).submit(); },

                addNewRow() {
                    let container, index, row, nextNumber;
                    
                    document.querySelectorAll('.no-data-row').forEach(el => el.style.display = 'none');

                    if (this.activeTab === 'cities') {
                        container = document.getElementById('cities-body');
                        index = Date.now();
                        nextNumber = container.querySelectorAll('tr:not(.no-data-row)').length + 1;

                        row = `
                        <tr class="new-row hover:bg-indigo-50/30 transition duration-150 group">
                            <td class="px-4 py-3 text-center"><input type="checkbox" disabled class="select-checkbox opacity-50"></td>
                            <td class="px-6 py-3 text-center font-mono text-xs font-bold text-slate-400">${nextNumber}</td>
                            <td class="p-2">
                                <input type="text" name="cities[${index}][code]" value="${nextNumber}" class="sheet-input code-input" placeholder="CODE">
                            </td>
                            <td class="p-2">
                                <input type="text" name="cities[${index}][city_name]" class="sheet-input" placeholder="{{ __('account.city_name') }}" autofocus>
                            </td>
                            <td class="px-6 py-3 text-center text-xs text-slate-400">{{ Auth::user()->name }}</td>
                            <td class="px-6 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" onclick="document.getElementById('cities-form').submit()" class="w-8 h-8 flex items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white transition shadow-sm"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></button>
                                    {{-- CANCEL BUTTON (Removes the row) --}}
                                    <button type="button" onclick="this.closest('tr').remove()" class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition shadow-sm"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                </div>
                            </td>
                        </tr>`;
                    } else {
                        container = document.getElementById('neighborhoods-body');
                        index = Date.now();
                        nextNumber = container.querySelectorAll('tr:not(.no-data-row)').length + 1;
                        let cityOptions = `@foreach($cities as $c) <option value="{{ $c->id }}">{{ $c->city_name }}</option> @endforeach`;
                        
                        row = `
                        <tr class="new-row hover:bg-indigo-50/30 transition duration-150 group">
                            <td class="px-4 py-3 text-center"><input type="checkbox" disabled class="select-checkbox opacity-50"></td>
                            <td class="px-6 py-3 text-center font-mono text-xs font-bold text-slate-400">${nextNumber}</td>
                            <td class="p-2">
                                <input type="text" name="neighborhoods[${index}][code]" value="${nextNumber}" class="sheet-input code-input" placeholder="CODE">
                            </td>
                            <td class="p-2">
                                <select name="neighborhoods[${index}][city_id]" class="sheet-input">
                                    ${cityOptions}
                                </select>
                            </td>
                            <td class="p-2">
                                <input type="text" name="neighborhoods[${index}][neighborhood_name]" class="sheet-input" placeholder="{{ __('account.neighborhood_name') }}" autofocus>
                            </td>
                            <td class="px-6 py-3 text-center text-xs text-slate-400">{{ Auth::user()->name }}</td>
                            <td class="px-6 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" onclick="document.getElementById('neighborhoods-form').submit()" class="w-8 h-8 flex items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white transition shadow-sm"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></button>
                                    {{-- CANCEL BUTTON (Removes the row) --}}
                                    <button type="button" onclick="this.closest('tr').remove()" class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition shadow-sm"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                </div>
                            </td>
                        </tr>`;
                    }
                    container.insertAdjacentHTML('beforeend', row);
                    
                    setTimeout(() => {
                        const newRow = container.lastElementChild;
                        newRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        const input = newRow.querySelector('input[autofocus]');
                        if(input) input.focus();
                    }, 50);
                },

                toggleAllSelection(type) {
                    this.selectedIds = [];
                }
            }));
        });

        function deleteItem(url) {
            if(confirm('{{ __('account.are_you_sure') }}')) {
                const form = document.getElementById('delete-form');
                form.action = url;
                form.submit();
            }
        }
    </script>
</x-app-layout>