<x-app-layout>
    <x-slot name="header">
        {{ __('cash_box.trash') }}
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-6">
        
        <div class="mb-6">
             <a href="{{ route('cash-boxes.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-indigo-600 transition font-bold text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>{{ __('cash_box.back') }}</span>
            </a>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-red-100">
            <div class="p-6 bg-white border-b border-slate-200">
                
                <h3 class="text-lg font-bold text-red-600 mb-6 flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    {{ __('cash_box.trash') }}
                </h3>

                <div class="overflow-x-auto rounded-lg border border-slate-100">
                    <table class="w-full text-sm text-left text-slate-500">
                        <thead class="text-xs text-slate-700 uppercase bg-red-50 border-b border-red-100">
                            <tr>
                                <th class="px-4 py-3">#</th>
                                <th class="px-4 py-3">{{ __('cash_box.name') }}</th>
                                <th class="px-4 py-3">{{ __('cash_box.branch') }}</th>
                                <th class="px-4 py-3">{{ __('cash_box.balance') }}</th>
                                <th class="px-4 py-3">{{ __('cash_box.deleted_at') }}</th>
                                <th class="px-4 py-3 text-center">{{ __('cash_box.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cashBoxes as $box)
                            <tr class="bg-white border-b hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3 font-bold text-slate-800">{{ $box->name }}</td>
                                <td class="px-4 py-3">{{ $box->branch->name ?? '-' }}</td>
                                <td class="px-4 py-3 font-bold">{{ number_format($box->balance, 2) }}</td>
                                <td class="px-4 py-3 text-xs text-red-500 font-medium">
                                    {{ $box->deleted_at->diffForHumans() }}
                                </td>
                                <td class="px-4 py-3 flex justify-center gap-4">
                                    
                                    <form action="{{ route('cash-boxes.restore', $box->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-emerald-600 hover:text-emerald-800 font-bold text-xs flex items-center gap-1 bg-emerald-50 hover:bg-emerald-100 px-2 py-1 rounded transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            {{ __('cash_box.restore') }}
                                        </button>
                                    </form>

                                    <form action="{{ route('cash-boxes.force-delete', $box->id) }}" method="POST" onsubmit="return confirm('{{ __('cash_box.warning_perm_delete') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 font-bold text-xs flex items-center gap-1 bg-red-50 hover:bg-red-100 px-2 py-1 rounded transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            {{ __('cash_box.perm_delete') }}
                                        </button>
                                    </form>

                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        <span>{{ __('cash_box.no_deleted_data') }}</span>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $cashBoxes->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>