<x-app-layout>
    <x-slot name="header">
        {{ app()->getLocale() == 'ku' ? 'سڕاوەکان' : 'Trash' }}
    </x-slot>

    <style>
        @media print { .no-print, button, a { display: none !important; } }
        /* Matching Checkbox Style */
        .select-checkbox { width: 1.1rem; height: 1.1rem; border-radius: 4px; border: 1px solid #cbd5e1; color: #e11d48; cursor: pointer; transition: all 0.2s; }
        .select-checkbox:focus { box-shadow: 0 0 0 2px #ffe4e6; border-color: #f43f5e; }
    </style>

    <div x-data="{ 
        selectedIds: [],
        allIds: {{ json_encode($transactions->pluck('id')) }},
        
        toggleSelection(id) {
            if (this.selectedIds.includes(id)) {
                this.selectedIds = this.selectedIds.filter(i => i !== id);
            } else {
                this.selectedIds.push(id);
            }
        },
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
            
            Swal.fire({
                title: '{{ app()->getLocale() == 'ku' ? 'دڵنیای لە سڕینەوەی یەکجاری؟' : 'Are you sure?' }}',
                text: '{{ app()->getLocale() == 'ku' ? 'ئەم کارە ناگەڕێتەوە و زانیارییەکان بۆ هەمیشە دەسڕێنەوە!' : 'This action cannot be undone!' }}', 
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: '{{ app()->getLocale() == 'ku' ? 'بەڵێ، بیسڕەوە' : 'Yes, Delete' }}',
                cancelButtonText: '{{ app()->getLocale() == 'ku' ? 'پاشگەزبوونەوە' : 'Cancel' }}',
                background: '#fff',
                borderRadius: '1rem',
                customClass: {
                    popup: 'rounded-xl shadow-xl border border-slate-100',
                    confirmButton: 'rounded-lg px-4 py-2 font-bold',
                    cancelButton: 'rounded-lg px-4 py-2 font-bold'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('bulk-force-delete-ids').value = JSON.stringify(this.selectedIds);
                    document.getElementById('bulk-force-delete-form').submit();
                }
            });
        }

    }" class="py-6 w-full min-w-0" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

        {{-- TOOLBAR --}}
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 px-4 no-print">
            
            {{-- Title & Bulk Actions --}}
            <div class="flex items-center gap-3">
                <div class="p-2 bg-rose-100 rounded-lg text-rose-600 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                
                <h3 x-show="selectedIds.length === 0" class="text-xl font-black text-slate-800 tracking-tight">
                    {{ app()->getLocale() == 'ku' ? 'سڕاوەکان - پسوڵەی خەرجی' : 'Trash - Paying' }}
                </h3>

                {{-- BULK ACTIONS BAR (Visible when items selected) --}}
                <div x-show="selectedIds.length > 0" x-transition class="flex items-center gap-2" style="display: none;">
                    <span class="text-xs font-bold text-rose-700 bg-rose-50 px-2 py-1 rounded border border-rose-200">
                        <span x-text="selectedIds.length"></span> {{ __('accountant.selected') ?? 'Selected' }}
                    </span>
                    
                    {{-- Restore Selected --}}
                    <button @click="bulkRestore()" class="px-3 py-1.5 bg-emerald-500 text-white text-xs font-bold rounded shadow-sm hover:bg-emerald-600 transition flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        {{ app()->getLocale() == 'ku' ? 'گەڕاندنەوە' : 'Restore' }}
                    </button>

                    {{-- Delete Selected --}}
                    <button @click="bulkForceDelete()" class="px-3 py-1.5 bg-red-600 text-white text-xs font-bold rounded shadow-sm hover:bg-red-700 transition flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        {{ app()->getLocale() == 'ku' ? 'سڕینەوە' : 'Delete' }}
                    </button>
                    
                    {{-- Cancel --}}
                    <button @click="selectedIds = []" class="text-slate-400 hover:text-slate-600 text-xs underline px-2">
                        {{ app()->getLocale() == 'ku' ? 'پاشگەزبوونەوە' : 'Cancel' }}
                    </button>
                </div>
            </div>
            
            {{-- Back Button --}}
            <a href="{{ route('accountant.paying.index') }}" class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm font-medium">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>{{ app()->getLocale() == 'ku' ? 'گەڕانەوە' : 'Back' }}</span>
            </a>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="relative overflow-x-auto bg-white shadow-sm rounded-xl border border-slate-200 mx-4">
            <table class="w-full text-sm text-left rtl:text-right text-slate-500">
                <thead class="text-xs text-slate-700 uppercase bg-rose-50/80 border-b border-rose-200">
                    <tr>
                        {{-- Select All --}}
                        <th class="px-4 py-3 text-center w-[40px]">
                            <input type="checkbox" @click="toggleAllSelection()" :checked="selectedIds.length > 0 && selectedIds.length === allIds.length" class="select-checkbox bg-white">
                        </th>
                        <th class="px-6 py-3 font-bold text-center w-16">#</th>
                        <th class="px-6 py-3 font-bold">{{ __('accountant.user') ?? 'User' }}</th>
                        <th class="px-6 py-3 font-bold text-center">{{ __('accountant.type_money') ?? 'Currency' }}</th>
                        <th class="px-6 py-3 font-bold text-center">{{ __('accountant.amount') ?? 'Amount' }}</th>
                        <th class="px-6 py-3 font-bold text-center">{{ __('accountant.total_invoice') ?? 'Total' }}</th>
                        <th class="px-6 py-3 font-bold text-center">{{ __('accountant.deleted_by') ?? 'Deleted By' }}</th>
                        <th class="px-6 py-3 font-bold text-center">{{ __('accountant.deleted_at') ?? 'Deleted At' }}</th>
                        <th class="px-6 py-3 font-bold text-center w-32">{{ __('accountant.actions') ?? 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $trx)
                    <tr class="bg-white hover:bg-rose-50/40 transition-colors group" :class="selectedIds.includes({{ $trx->id }}) ? 'bg-rose-50/80' : ''">
                        
                        {{-- Checkbox --}}
                        <td class="px-4 py-4 text-center">
                            <input type="checkbox" :value="{{ $trx->id }}" x-model="selectedIds" class="select-checkbox">
                        </td>

                        {{-- ID --}}
                        <td class="px-6 py-4 text-center font-mono text-slate-400">#{{ $trx->id }}</td>
                        
                        {{-- Account / User --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2 p-1.5 w-fit opacity-80">
                                @if($trx->account)
                                    <img src="{{ $trx->account->profile_picture ? asset('storage/'.$trx->account->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($trx->account->name) }}" class="w-8 h-8 rounded-full grayscale object-cover ring-1 ring-slate-200">
                                    <div class="flex flex-col leading-none">
                                        <span class="font-bold text-slate-600 text-xs truncate max-w-[160px]">{{ $trx->account->name }}</span>
                                        <span class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $trx->account->code }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">-</span>
                                @endif
                            </div>
                        </td>

                        {{-- Currency --}}
                        <td class="px-6 py-4 text-center">
                            <span class="text-[10px] font-bold bg-slate-100 text-slate-600 px-2 py-0.5 rounded border border-slate-200 shadow-sm">{{ $trx->currency->currency_type ?? '-' }}</span>
                        </td>
                        
                        {{-- Amount --}}
                        <td class="px-6 py-4 text-center">
                            <span class="font-bold text-rose-600 text-sm line-through opacity-70">{{ number_format($trx->amount, 2) }}</span>
                        </td>
                        
                        {{-- Total Invoice --}}
                        <td class="px-6 py-4 text-center">
                            <span class="bg-slate-100 text-slate-500 px-2.5 py-1 rounded border border-slate-200 font-black text-sm shadow-sm">{{ number_format($trx->amount + $trx->discount, 2) }}</span>
                        </td>
                        
                        {{-- DELETED BY --}}
                        <td class="px-6 py-4 text-center">
                            @if($trx->user)
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-600 uppercase border border-slate-300">
                                        {{ substr($trx->user->name, 0, 1) }}
                                    </div>
                                    <span class="text-xs font-bold text-slate-700">{{ $trx->user->name }}</span>
                                </div>
                            @else
                                <span class="text-xs text-slate-400 italic">System</span>
                            @endif
                        </td>

                        {{-- Deleted Date --}}
                        <td class="px-6 py-4 text-center text-xs text-red-500 font-medium">
                            <div class="bg-red-50 px-2 py-1 rounded border border-red-100 inline-block shadow-sm">
                                {{ $trx->deleted_at->format('Y-m-d H:i') }}
                            </div>
                        </td>
                        
                        {{-- Actions --}}
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                {{-- Restore Button --}}
                                <form action="{{ route('accountant.paying.restore', $trx->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="p-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white rounded-lg transition-colors shadow-sm border border-emerald-200" title="{{ app()->getLocale() == 'ku' ? 'گەڕاندنەوە' : 'Restore' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </button>
                                </form>

                                {{-- Force Delete Button --}}
                                <button type="button" onclick="confirmForceDelete({{ $trx->id }})" class="p-2 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded-lg transition-colors shadow-sm border border-red-200" title="{{ app()->getLocale() == 'ku' ? 'سڕینەوەی یەکجاری' : 'Force Delete' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-16 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <div class="w-16 h-16 bg-rose-50 rounded-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-rose-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </div>
                                <p class="text-sm font-bold text-rose-900">{{ app()->getLocale() == 'ku' ? 'هیچ زانیارییەکی سڕاوە نییە' : 'Trash is empty' }}</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 px-4">
            {{ $transactions->links() }}
        </div>
    </div>

    {{-- HIDDEN FORMS FOR BULK ACTIONS --}}
    <form id="bulk-restore-form" action="{{ route('accountant.paying.bulk-restore') }}" method="POST" class="hidden">@csrf <input type="hidden" name="ids" id="bulk-restore-ids"></form>
    <form id="bulk-force-delete-form" action="{{ route('accountant.paying.bulk-force-delete') }}" method="POST" class="hidden">@csrf @method('DELETE') <input type="hidden" name="ids" id="bulk-force-delete-ids"></form>

    {{-- SINGLE FORCE DELETE FORM --}}
    <form id="force-delete-form" action="" method="POST" class="hidden">@csrf @method('DELETE')</form>

    <script>
        function confirmForceDelete(id) {
            const form = document.getElementById('force-delete-form');
            form.action = "{{ route('accountant.paying.force-delete', ':id') }}".replace(':id', id);
            
            Swal.fire({
                title: '{{ app()->getLocale() == 'ku' ? 'دڵنیای لە سڕینەوەی یەکجاری؟' : 'Are you sure?' }}',
                text: "{{ app()->getLocale() == 'ku' ? 'ئەم کارە ناگەڕێتەوە و زانیارییەکان بۆ هەمیشە دەسڕێنەوە!' : 'This action cannot be undone!' }}",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: '{{ app()->getLocale() == 'ku' ? 'بەڵێ، بیسڕەوە' : 'Yes, Delete' }}',
                cancelButtonText: '{{ app()->getLocale() == 'ku' ? 'پاشگەزبوونەوە' : 'Cancel' }}',
                background: '#fff',
                borderRadius: '1rem',
                customClass: {
                    popup: 'rounded-xl shadow-xl border border-slate-100',
                    confirmButton: 'rounded-lg px-4 py-2 font-bold',
                    cancelButton: 'rounded-lg px-4 py-2 font-bold'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>
</x-app-layout>