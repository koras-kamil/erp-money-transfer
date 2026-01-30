{{-- ROWS --}}
@forelse($accounts as $acc)
<tr class="hover:bg-indigo-50/60 transition duration-150 group border-b border-slate-50 last:border-b-0">
    <td class="px-4 py-4 font-mono text-sm text-slate-400">{{ $acc->id }}</td>
    
    {{-- Profile Picture (Click to Zoom) --}}
    <td class="px-4 py-3">
        <div @click="zoomImage('{{ $acc->profile_picture ? asset('storage/' . $acc->profile_picture) : '' }}')" 
             class="w-10 h-10 rounded-full bg-white border border-slate-200 p-0.5 shadow-sm relative overflow-hidden cursor-pointer hover:ring-2 hover:ring-indigo-400 transition-all">
            @if($acc->profile_picture)
                <img src="{{ asset('storage/' . $acc->profile_picture) }}" class="w-full h-full rounded-full object-cover">
            @else
                <div class="w-full h-full rounded-full bg-slate-100 flex items-center justify-center text-slate-400 font-bold text-xs uppercase select-none">
                    {{ substr($acc->name, 0, 1) }}
                </div>
            @endif
        </div>
    </td>

    <td class="px-4 py-3 text-sm font-mono text-indigo-600 bg-indigo-50/50 rounded px-2 py-1 inline-block mt-3">{{ $acc->code }}</td>
    <td class="px-4 py-3 text-sm text-slate-500 font-mono">{{ $acc->manual_code ?? '-' }}</td>
    
    <td class="px-4 py-3">
        <div class="flex flex-col">
            <span class="font-medium text-slate-700 text-sm leading-tight">{{ $acc->name }}</span>
            <span class="text-xs text-slate-400 mt-0.5 font-normal">{{ $acc->secondary_name }}</span>
        </div>
    </td>
    
    <td class="px-4 py-3">
        <span class="px-2.5 py-1 rounded-md text-xs font-medium uppercase tracking-wide border
            @if($acc->account_type == 'customer') bg-blue-50 text-blue-600 border-blue-100
            @elseif($acc->account_type == 'vendor') bg-purple-50 text-purple-600 border-purple-100
            @elseif($acc->account_type == 'buyer_and_seller') bg-orange-50 text-orange-600 border-orange-100
            @else bg-gray-50 text-gray-600 border-gray-100 @endif">
            {{ __('account.' . $acc->account_type) }}
        </span>
    </td>

    <td class="px-4 py-3 text-sm font-mono text-slate-500">{{ $acc->mobile_number_1 ?? '-' }}</td>
    <td class="px-4 py-3 text-sm font-medium text-slate-700">{{ $acc->currency->currency_type ?? '-' }}</td>
    <td class="px-4 py-3 text-sm text-slate-500">{{ $acc->zone->city ?? '-' }}</td>
    <td class="px-4 py-3 text-sm font-mono text-slate-600">{{ number_format($acc->debt_limit, 0) }}</td>
    <td class="px-4 py-3 text-sm text-slate-500">{{ $acc->debt_due_time }}</td>

    <td class="px-4 py-3 text-center">
        <div class="relative inline-flex group">
            <span class="w-2.5 h-2.5 rounded-full {{ $acc->is_active ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.4)]' : 'bg-red-500' }}"></span>
        </div>
    </td>
    
    <td class="px-4 py-3 text-center sticky right-0 bg-white group-hover:bg-indigo-50/60 transition-colors shadow-[-4px_0_8px_-4px_rgba(0,0,0,0.02)]">
        <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
            <button @click="openEdit({{ $acc }})" class="p-2 bg-white border border-slate-200 rounded-lg text-blue-500 hover:bg-blue-50 hover:border-blue-300 transition shadow-sm" title="Edit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </button>
            <form action="{{ route('accounts.destroy', $acc->id) }}" method="POST" onsubmit="return confirm('{{ __('account.are_you_sure') }}')">
                @csrf @method('DELETE')
                <button type="submit" class="p-2 bg-white border border-slate-200 rounded-lg text-red-500 hover:bg-red-50 hover:border-red-300 transition shadow-sm" title="Delete">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="15" class="px-6 py-24 text-center text-slate-400">
        <div class="flex flex-col items-center justify-center gap-4">
            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center animate-pulse"><svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></div>
            <span class="text-sm font-medium">{{ __('No accounts found') }}</span>
        </div>
    </td>
</tr>
@endforelse

{{-- HIDDEN PAGINATION (Fetched by JS) --}}
<div id="new-pagination" class="hidden">
    {{ $accounts->links() }}
</div>