<x-app-layout>
    <x-slot name="header">
        {{ __('spending.type_header') }} - Trash
    </x-slot>

    <div class="py-6">
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 px-4">
            <div class="flex items-center gap-3">
                <h3 class="text-lg font-bold text-slate-700">{{ __('spending.type_header') }} (Trash)</h3>
                <a href="{{ route('type-spending.index') }}" class="text-xs font-bold text-indigo-500 hover:text-indigo-700 bg-indigo-50 px-3 py-1.5 rounded-full border border-indigo-100 transition flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to Active
                </a>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm mx-4">
            <table class="w-full text-sm text-left text-slate-500">
                <thead class="text-xs text-slate-700 uppercase bg-rose-50 border-b border-rose-100">
                    <tr>
                        <th class="px-4 py-3 w-[5%] text-center">#</th>
                        <th class="px-4 py-3">{{ __('spending.code') }}</th>
                        <th class="px-4 py-3">{{ __('spending.name') }}</th>
                        <th class="px-4 py-3">{{ __('spending.group_title') }}</th>
                        <th class="px-4 py-3 text-center">{{ __('spending.accountant_code') }}</th>
                        <th class="px-4 py-3">{{ __('spending.branch') }}</th>
                        <th class="px-4 py-3 w-[15%] text-center">{{ __('spending.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($types as $type)
                    <tr class="bg-slate-50/50 hover:bg-rose-50/30 transition-colors">
                        <td class="px-4 py-3 text-center font-medium text-slate-400">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3 font-mono font-bold text-slate-600">{{ $type->code }}</td>
                        <td class="px-4 py-3 font-bold text-slate-700">{{ $type->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $type->group->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center font-mono text-slate-500">{{ $type->accountant_code ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $type->branch->name ?? 'Unknown' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <form action="{{ route('type-spending.restore', $type->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="p-1.5 text-emerald-600 hover:bg-emerald-100 rounded-lg transition" title="Restore"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></button>
                                </form>
                                <form id="force-delete-{{ $type->id }}" action="{{ route('type-spending.force-delete', $type->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="button" onclick="window.confirmAction('force-delete-{{ $type->id }}', '{{ __('spending.delete_confirm') }}')" class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg transition" title="Permanent Delete"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400 italic">Trash is empty.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>