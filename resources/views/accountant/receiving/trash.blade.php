<x-app-layout>
    <div class="min-h-screen bg-slate-50 py-8 px-4 font-sans text-slate-800" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">
        
        {{-- HEADER --}}
        <div class="max-w-7xl mx-auto mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <svg class="w-6 h-6 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    {{ __('accountant.trash') }}
                </h1>
                <p class="text-slate-500 text-sm mt-1">{{ __('accountant.trash_subtitle') ?? 'Manage deleted transactions' }}</p>
            </div>
            
            <x-btn type="back" href="{{ route('accountant.receiving.index') }}">
                {{ __('accountant.back_to_list') }}
            </x-btn>
        </div>

        {{-- TABLE --}}
        <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left rtl:text-right">
                    <thead class="bg-rose-50 text-rose-700 uppercase text-xs font-bold border-b border-rose-100">
                        <tr>
                            <th class="px-6 py-4">#</th>
                            <th class="px-6 py-4">{{ __('accountant.user') }}</th>
                            <th class="px-6 py-4">{{ __('accountant.amount') }}</th>
                            <th class="px-6 py-4">{{ __('accountant.deleted_at') }}</th>
                            <th class="px-6 py-4 text-center">{{ __('accountant.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($transactions as $trx)
                        <tr class="hover:bg-rose-50/30 transition-colors group">
                            <td class="px-6 py-4 font-mono text-slate-400">#{{ $trx->id }}</td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-700">{{ $trx->account->name }}</div>
                                <div class="text-xs text-slate-400">{{ $trx->account->manual_code }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-700">{{ number_format($trx->amount, 2) }}</span>
                            </td>
                            <td class="px-6 py-4 text-rose-500 text-xs">
                                <div>{{ $trx->deleted_at->format('Y-m-d') }}</div>
                                <div>{{ $trx->deleted_at->format('h:i A') }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- RESTORE BUTTON --}}
                                    <form action="{{ route('accountant.receiving.restore', $trx->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold hover:bg-emerald-200 transition-colors flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            {{ __('accountant.restore') }}
                                        </button>
                                    </form>

                                    {{-- FORCE DELETE BUTTON --}}
                                    <form action="{{ route('accountant.receiving.force-delete', $trx->id) }}" method="POST" onsubmit="return confirm('{{ __('accountant.permanent_delete_confirm') }}');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 bg-rose-100 text-rose-700 rounded-lg text-xs font-bold hover:bg-rose-200 transition-colors flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            {{ __('accountant.permanent_delete') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                {{ __('accountant.trash_empty') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</x-app-layout>