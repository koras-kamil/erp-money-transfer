<x-app-layout>
    <x-slot name="header">
        {{ __('currency.trash') ?? 'Trash' }}
    </x-slot>

    {{-- STYLES --}}
    <style>
        @media print {
            .no-print, button, .print\:hidden { display: none !important; }
        }
    </style>

    <div class="py-6" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

        {{-- TOOLBAR --}}
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 px-4 no-print">
            
            {{-- Title --}}
            <div class="flex items-center gap-4">
                <h3 class="text-xl font-black text-red-600 tracking-tight">{{ __('currency.trash') ?? 'Trash' }}</h3>
            </div>
            
            {{-- Back Button --}}
            <a href="{{ route('currency.index') }}" class="flex items-center gap-2 px-4 py-2 bg-slate-700 text-white rounded-xl hover:bg-slate-800 transition shadow-sm shadow-slate-300">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span class="text-sm font-bold">{{ __('currency.back') ?? 'Back' }}</span>
            </a>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="relative overflow-x-auto bg-white shadow-sm rounded-lg border border-red-100 mx-4">
            <table class="w-full min-w-[800px] text-sm text-left rtl:text-right text-gray-500">
                <thead class="text-xs text-red-700 uppercase bg-red-50 border-b border-red-100">
                    <tr>
                        <th class="px-6 py-3 font-medium w-16">#</th>
                        <th class="px-6 py-3 font-medium min-w-[140px]">{{ __('currency.type') }}</th>
                        <th class="px-6 py-3 font-medium text-center min-w-[80px]">{{ __('currency.symbol') }}</th>
                        <th class="px-6 py-3 font-medium text-center min-w-[130px]">{{ __('currency.price_total') }}</th>
                        <th class="px-6 py-3 font-medium text-center min-w-[150px]">{{ __('currency.deleted_at') ?? 'Deleted Date' }}</th>
                        <th class="px-6 py-3 font-medium text-center w-32">{{ __('currency.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($currencies as $currency)
                    <tr class="bg-white border-b border-gray-100 hover:bg-red-50/50 transition-colors">
                        {{-- ID --}}
                        <td class="px-6 py-4 font-medium whitespace-nowrap text-gray-900">{{ $loop->iteration }}</td>
                        
                        {{-- Type --}}
                        <td class="px-6 py-4 font-bold text-gray-800 uppercase">{{ $currency->currency_type }}</td>
                        
                        {{-- Symbol --}}
                        <td class="px-6 py-4 text-center text-gray-600">{{ $currency->symbol }}</td>
                        
                        {{-- Price --}}
                        <td class="px-6 py-4 text-center text-emerald-600 font-bold">{{ number_format($currency->price_total, 3) }}</td>
                        
                        {{-- Deleted At --}}
                        <td class="px-6 py-4 text-center text-xs text-red-400 font-medium">
                            <span dir="ltr">{{ $currency->deleted_at->format('Y-m-d H:i') }}</span>
                            <br>
                            <span class="text-[10px] opacity-75">({{ $currency->deleted_at->diffForHumans() }})</span>
                        </td>
                        
                        {{-- Actions --}}
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-3">
                                {{-- Restore Button --}}
                                <form action="{{ route('currency.restore', $currency->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="p-2 text-emerald-500 hover:bg-emerald-50 rounded-lg transition-colors" title="{{ __('currency.restore') }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </button>
                                </form>

                                {{-- Force Delete Button --}}
                                <button type="button" onclick="confirmForceDelete({{ $currency->id }})" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="{{ __('currency.perm_delete') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400 bg-gray-50">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <p>{{ __('currency.trash_empty') ?? 'Trash is empty' }}</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Hidden Delete Form --}}
    <form id="force-delete-form" action="" method="POST" class="hidden">
        @csrf @method('DELETE')
    </form>

    <script>
        function confirmForceDelete(id) {
            const form = document.getElementById('force-delete-form');
            form.action = "{{ route('currency.force-delete', ':id') }}".replace(':id', id);
            
            Swal.fire({
                title: '{{ __('currency.warning_perm_delete') ?? "Are you sure?" }}',
                text: "{{ __('currency.cant_undone') ?? "This action cannot be undone!" }}",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '{{ __('currency.yes_delete') ?? "Yes, delete it!" }}',
                cancelButtonText: '{{ __('currency.cancel') ?? "Cancel" }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>
</x-app-layout>