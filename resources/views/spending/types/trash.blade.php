<x-app-layout>
    <x-slot name="header">
        {{ __('spending.type_header') }} - {{ __('spending.trash') ?? 'Trash' }}
    </x-slot>

    {{-- STYLES --}}
    <style>
        @media print { .no-print, button, a { display: none !important; } }
    </style>

    <div class="py-6 w-full min-w-0" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">

        {{-- TOOLBAR --}}
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 px-4 no-print">
            
            {{-- Title --}}
            <div class="flex items-center gap-3">
                <div class="p-2 bg-red-100 rounded-lg text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <h3 class="text-xl font-black text-slate-800 tracking-tight">{{ __('spending.type_header') }} ({{ __('spending.trash') ?? 'Trash' }})</h3>
            </div>
            
            {{-- Back Button --}}
            <a href="{{ route('type-spending.index') }}" class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm font-medium">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>{{ __('spending.back') ?? 'Back' }}</span>
            </a>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="relative overflow-x-auto bg-white shadow-sm rounded-xl border border-slate-200 mx-4">
            <table class="w-full text-sm text-left rtl:text-right text-slate-500">
                <thead class="text-xs text-slate-700 uppercase bg-red-50/50 border-b border-red-100">
                    <tr>
                        <th class="px-6 py-3 font-bold text-center w-16">#</th>
                        <th class="px-6 py-3 font-bold">{{ __('spending.code') }}</th>
                        <th class="px-6 py-3 font-bold">{{ __('spending.name') }}</th>
                        <th class="px-6 py-3 font-bold">{{ __('spending.group_title') }}</th>
                        <th class="px-6 py-3 font-bold text-center">{{ __('spending.accountant_code') }}</th>
                        <th class="px-6 py-3 font-bold">{{ __('spending.branch') }}</th>
                        
                        {{-- DELETED BY --}}
                        <th class="px-6 py-3 font-bold text-center">{{ __('spending.deleted_by') ?? 'Deleted By' }}</th>
                        
                        {{-- DELETED DATE --}}
                        <th class="px-6 py-3 font-bold text-center">{{ __('spending.deleted_at') ?? 'Deleted Date' }}</th>
                        
                        <th class="px-6 py-3 font-bold text-center w-32">{{ __('spending.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($types as $type)
                    <tr class="bg-white hover:bg-red-50/30 transition-colors group">
                        
                        {{-- ID --}}
                        <td class="px-6 py-4 text-center font-medium text-slate-400">{{ $loop->iteration }}</td>
                        
                        {{-- Code --}}
                        <td class="px-6 py-4 font-mono font-bold text-slate-600">{{ $type->code }}</td>
                        
                        {{-- Name --}}
                        <td class="px-6 py-4 font-bold text-slate-800">{{ $type->name }}</td>
                        
                        {{-- Group --}}
                        <td class="px-6 py-4 text-slate-600">{{ $type->group->name ?? '-' }}</td>
                        
                        {{-- Acc Code --}}
                        <td class="px-6 py-4 text-center font-mono text-slate-500">{{ $type->accountant_code ?? '-' }}</td>
                        
                        {{-- Branch --}}
                        <td class="px-6 py-4 text-slate-600">{{ $type->branch->name ?? '-' }}</td>

                        {{-- DELETED BY USER --}}
                        <td class="px-6 py-4 text-center">
                            @if($type->deleter)
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-600 uppercase border border-slate-300">
                                        {{ substr($type->deleter->name, 0, 1) }}
                                    </div>
                                    <span class="text-xs font-bold text-slate-700">{{ $type->deleter->name }}</span>
                                </div>
                            @else
                                <span class="text-xs text-slate-400 italic">{{ __('spending.system') ?? 'System' }}</span>
                            @endif
                        </td>

                        {{-- Deleted Date --}}
                        <td class="px-6 py-4 text-center text-xs text-red-500 font-medium">
                            <div class="bg-red-50 px-2 py-1 rounded border border-red-100 inline-block">
                                {{ $type->deleted_at->format('Y-m-d H:i') }}
                            </div>
                        </td>
                        
                        {{-- Actions --}}
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                {{-- Restore Button --}}
                                <form action="{{ route('type-spending.restore', $type->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="p-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 rounded-lg transition-colors shadow-sm" title="{{ __('spending.restore') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </button>
                                </form>

                                {{-- Force Delete Button --}}
                                <form id="force-delete-{{ $type->id }}" action="{{ route('type-spending.force-delete', $type->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="button" onclick="window.confirmAction('force-delete-{{ $type->id }}', '{{ __('spending.delete_confirm') }}')" class="p-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg transition-colors shadow-sm" title="{{ __('spending.perm_delete') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </div>
                                <p class="text-sm font-medium">{{ __('spending.trash_empty') ?? 'Trash is empty' }}</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 px-4">
            {{ $types->links() }}
        </div>
    </div>
</x-app-layout>