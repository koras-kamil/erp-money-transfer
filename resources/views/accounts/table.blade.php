<div class="min-w-full inline-block align-middle">
    <div class="overflow-hidden border border-slate-200 rounded-xl">
        <table class="min-w-full text-sm text-left rtl:text-right text-slate-600">
            <thead class="bg-slate-100 text-slate-700 uppercase text-xs font-bold sticky top-0 z-10 shadow-sm">
                <tr>
                    @foreach([
                        'id' => '#',
                        'image' => __('account.image'),
                        'code' => __('account.code'),
                        'manual_code' => __('account.manual_code'),
                        'name' => __('account.name'),
                        'secondary_name' => __('account.secondary_name'),
                        'account_type' => __('account.type'),
                        'mobile_number_1' => __('account.mobile_1'),
                        'currency_id' => __('account.currency'),
                        'zone_id' => __('account.zone'),
                        'debt_limit' => __('account.debt_limit'),
                        'debt_due_time' => __('account.due_time'),
                        'is_active' => __('account.status')
                    ] as $field => $label)
                        <th class="px-4 py-4 whitespace-nowrap border-b border-slate-200 group transition-all duration-300 relative" 
                            :class="searchOpen['{{ $field }}'] ? 'bg-indigo-50' : ''">
                            
                            {{-- HEADER --}}
                            <div class="flex items-center justify-between gap-3" x-show="!searchOpen['{{ $field }}']">
                                <div class="flex items-center gap-1 cursor-pointer select-none" @click="sortBy('{{ $field }}')">
                                    <span>{{ $label }}</span>
                                    <span class="text-indigo-500 text-[10px]" x-show="params.sort === '{{ $field }}'">
                                        <span x-show="params.direction === 'asc'">▲</span>
                                        <span x-show="params.direction === 'desc'">▼</span>
                                    </span>
                                </div>
                                @if($field !== 'image')
                                    <button @click="toggleSearch('{{ $field }}')" class="text-slate-400 hover:text-indigo-600 transition opacity-0 group-hover:opacity-100 p-1 rounded hover:bg-slate-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </button>
                                @endif
                            </div>

                            {{-- SEARCH INPUT (Appears on click) --}}
                            <div x-show="searchOpen['{{ $field }}']" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 class="absolute inset-0 bg-white flex items-center px-2 z-20 border-b-2 border-indigo-500">
                                
                                <div class="relative w-full">
                                    <input type="text" 
                                           x-model.debounce.750ms="params['{{ $field }}']" 
                                           @input="fetchData()" 
                                           class="w-full h-9 pl-2 pr-8 text-sm border-none focus:ring-0 bg-transparent text-indigo-700 font-bold placeholder-indigo-300"
                                           placeholder="Search {{ $label }}..." 
                                           autofocus>
                                    <button @click="toggleSearch('{{ $field }}'); params['{{ $field }}'] = ''; fetchData()" class="absolute right-0 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500 p-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>
                        </th>
                    @endforeach
                    <th class="px-4 py-4 text-center border-b border-slate-200 sticky right-0 bg-slate-100 z-20 shadow-[-4px_0_8px_-4px_rgba(0,0,0,0.1)]">{{ __('account.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse($accounts as $acc)
                <tr class="hover:bg-indigo-50/40 transition-colors group">
                    <td class="px-4 py-4 font-mono text-sm text-slate-500">{{ $acc->id }}</td>
                    
                    {{-- Profile Picture --}}
                    <td class="px-4 py-4">
                        <div class="w-12 h-12 rounded-full bg-white border border-slate-200 p-0.5 shadow-sm relative overflow-hidden group-hover:scale-110 transition-transform">
                            @if($acc->profile_picture)
                                <img src="{{ asset('storage/' . $acc->profile_picture) }}" class="w-full h-full rounded-full object-cover">
                            @else
                                <div class="w-full h-full rounded-full bg-slate-100 flex items-center justify-center text-slate-400 font-bold text-sm uppercase">
                                    {{ substr($acc->name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                    </td>

                    <td class="px-4 py-4 text-sm font-mono text-indigo-600 font-bold bg-indigo-50/50 rounded px-2 py-1 inline-block mt-3">{{ $acc->code }}</td>
                    <td class="px-4 py-4 text-sm text-slate-500 font-mono">{{ $acc->manual_code ?? '-' }}</td>
                    
                    <td class="px-4 py-4">
                        <div class="flex flex-col">
                            <span class="font-bold text-slate-800 text-base leading-tight">{{ $acc->name }}</span>
                            <span class="text-xs text-slate-400 mt-0.5">{{ $acc->secondary_name }}</span>
                        </div>
                    </td>
                    
                    <td class="px-4 py-4">
                        <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider border
                            @if($acc->account_type == 'customer') bg-blue-50 text-blue-600 border-blue-200
                            @elseif($acc->account_type == 'vendor') bg-purple-50 text-purple-600 border-purple-200
                            @elseif($acc->account_type == 'buyer_and_seller') bg-orange-50 text-orange-600 border-orange-200
                            @else bg-gray-50 text-gray-600 border-gray-200 @endif">
                            {{ __('account.' . $acc->account_type) }}
                        </span>
                    </td>

                    <td class="px-4 py-4 text-sm font-mono text-slate-600">{{ $acc->mobile_number_1 ?? '-' }}</td>
                    <td class="px-4 py-4 text-sm font-bold text-slate-800">{{ $acc->currency->currency_type ?? '-' }}</td>
                    <td class="px-4 py-4 text-sm text-slate-600">{{ $acc->zone->city ?? '-' }}</td>
                    <td class="px-4 py-4 text-sm font-mono text-slate-700">{{ number_format($acc->debt_limit, 0) }}</td>
                    <td class="px-4 py-4 text-sm text-slate-600">{{ $acc->debt_due_time }}</td>

                    <td class="px-4 py-4 text-center">
                        <div class="relative inline-flex group">
                            <span class="w-3 h-3 rounded-full {{ $acc->is_active ? 'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]' : 'bg-red-500' }}"></span>
                        </div>
                    </td>
                    
                    <td class="px-4 py-4 text-center sticky right-0 bg-white group-hover:bg-indigo-50/40 transition-colors shadow-[-4px_0_8px_-4px_rgba(0,0,0,0.05)]">
                        <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            <button @click="openEdit({{ $acc }})" class="p-2 bg-white border border-slate-200 rounded-lg text-blue-500 hover:bg-blue-50 hover:border-blue-300 transition shadow-sm" title="Edit">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <form action="{{ route('accounts.destroy', $acc->id) }}" method="POST" onsubmit="return confirm('{{ __('account.are_you_sure') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 bg-white border border-slate-200 rounded-lg text-red-500 hover:bg-red-50 hover:border-red-300 transition shadow-sm" title="Delete">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="15" class="px-6 py-20 text-center text-slate-400">
                        <div class="flex flex-col items-center justify-center gap-4">
                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center"><svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></div>
                            <span class="text-sm font-medium">{{ __('No accounts found') }}</span>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="px-4 py-4 border-t border-slate-100 bg-slate-50/50">
    {{ $accounts->links() }}
</div>