<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>

    <div class="flex h-[calc(100vh-65px)] overflow-hidden bg-slate-50 font-sans text-slate-800" dir="rtl">

        {{-- 🟢 MAIN CONTENT AREA (Left/Center) --}}
        <div class="flex-1 overflow-y-auto p-6 scroll-smooth custom-scrollbar">
            
            <div class="mb-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="{{ route('accountant.statement.index') }}" class="p-2 bg-white border border-slate-200 rounded-lg text-slate-500 hover:text-indigo-600 transition">
                        <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </a>
                    <h1 class="text-2xl font-black text-slate-800">{{ __('Account Statement') }}</h1>
                </div>
                <div class="flex gap-2">
                    <button class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-bold text-slate-600 shadow-sm hover:bg-slate-50">Export PDF</button>
                </div>
            </div>

            {{-- Placeholder for Transactions --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 min-h-[500px] flex items-center justify-center text-slate-400">
                <div class="text-center">
                    <svg class="w-16 h-16 mx-auto mb-4 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    <span class="block text-sm font-bold">Transactions will appear here</span>
                </div>
            </div>

        </div>

        {{-- 🟢 RIGHT-SIDE FIXED MENU (Your Requested Sidebar) --}}
        <div class="w-80 flex-shrink-0 bg-white border-r border-slate-200 h-full overflow-y-auto custom-scrollbar shadow-xl z-20">
            
            {{-- 1. Account Image + Edit --}}
            <div class="p-6 flex flex-col items-center border-b border-slate-100 relative">
                <div class="relative group">
                    <img src="{{ $account->profile_picture ? asset('storage/'.$account->profile_picture) : 'https://ui-avatars.com/api/?name='.$account->name.'&background=6366f1&color=fff' }}" 
                         class="w-24 h-24 rounded-full object-cover border-4 border-slate-50 shadow-lg group-hover:border-indigo-50 transition-all">
                    <a href="#" class="absolute bottom-0 right-0 bg-white text-slate-600 p-1.5 rounded-full shadow-md border border-slate-100 hover:text-indigo-600 hover:border-indigo-200 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </a>
                </div>

                {{-- 2. Account Name --}}
                <div class="mt-3 text-center">
                    <h2 class="text-lg font-black text-slate-800 leading-tight">{{ $account->name }}</h2>
                    @if($account->secondary_name)
                        <span class="text-xs font-bold text-slate-400 block mt-0.5">{{ $account->secondary_name }}</span>
                    @endif
                </div>

                {{-- 3. Account Type --}}
                <div class="mt-2">
                    <span class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-600 text-xs font-bold uppercase tracking-wider border border-indigo-100">
                        {{ $account->account_type ?? 'Customer' }}
                    </span>
                </div>
            </div>

            {{-- 4. Account Code + QR --}}
            <div class="p-4 border-b border-slate-100 bg-slate-50/50">
                <div class="flex justify-between items-center mb-3">
                    <div class="flex flex-col">
                        <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Account Code</span>
                        <span class="text-lg font-mono font-bold text-slate-700 tracking-wide">{{ $account->code }}</span>
                    </div>
                    <button class="text-slate-400 hover:text-emerald-500 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </button>
                </div>
                <div class="w-full bg-white border border-slate-200 rounded-lg p-2 flex items-center justify-center h-32 relative overflow-hidden group cursor-pointer">
                    <svg class="w-20 h-20 text-slate-800" fill="currentColor" viewBox="0 0 448 512"><path d="M0 80C0 53.5 21.5 32 48 32h96c26.5 0 48 21.5 48 48v96c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V80zm64 32v64h64v-64H64zM0 336c0-26.5 21.5-48 48-48h96c26.5 0 48 21.5 48 48v96c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V336zm64 32v64h64v-64H64zM304 32h96c26.5 0 48 21.5 48 48v96c0 26.5-21.5 48-48 48h-96c-26.5 0-48-21.5-48-48V80c0-26.5 21.5-48 48-48zm48 64v64h64v-64h-64zM256 304c0-8.8 7.2-16 16-16h64c8.8 0 16 7.2 16 16s7.2 16 16 16h32c8.8 0 16-7.2 16-16s7.2-16 16-16s16 7.2 16 16v96c0 8.8-7.2 16-16 16H368c-8.8 0-16-7.2-16-16s-7.2-16-16-16s-16 7.2-16 16v64c0 8.8-7.2 16-16 16H272c-8.8 0-16-7.2-16-16V304zM368 480a16 16 0 1 1 0-32 16 16 0 1 1 0 32zm64 0a16 16 0 1 1 0-32 16 16 0 1 1 0 32z"/></svg>
                </div>
            </div>

            {{-- Supported Currency Section --}}
            <div class="p-4 border-b border-slate-100">
                <h3 class="text-xs font-black text-slate-400 uppercase mb-3 px-1">Financial Summary</h3>
                <div class="space-y-2">
                    @forelse($supportedCurrencies as $currency)
                        @php
                            $bal = $currency->current_balance ?? 0;
                            $isDebt = $bal < 0;
                            $isLoan = $bal > 0;
                            $color = $isDebt ? 'text-rose-600 bg-rose-50 border-rose-100' : ($isLoan ? 'text-emerald-600 bg-emerald-50 border-emerald-100' : 'text-slate-500 bg-slate-50 border-slate-100');
                            $label = $isDebt ? 'Debt' : ($isLoan ? 'Loan' : 'Balance');
                        @endphp
                        <div class="flex items-center justify-between p-2.5 rounded-lg border {{ $color }}">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold opacity-80">{{ $currency->currency_type }} {{ $label }}</span>
                            </div>
                            <span class="font-mono font-black text-sm" dir="ltr">
                                {{ number_format($bal, 2) }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center text-xs text-slate-400 italic py-2">No currencies configured</div>
                    @endforelse
                </div>
            </div>

            {{-- User Information Section --}}
            <div x-data="{ open: false }" class="border-b border-slate-100">
                <button @click="open = !open" class="w-full flex items-center justify-between p-4 hover:bg-slate-50 transition-colors">
                    <span class="text-sm font-bold text-slate-700">Full Information</span>
                    <svg class="w-4 h-4 text-slate-400 transform transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-collapse class="px-4 pb-4">
                    <div class="space-y-3 bg-white p-3 rounded-lg border border-slate-100 shadow-sm text-xs">
                        <div class="flex justify-between border-b border-slate-50 pb-2"><span class="text-slate-400">Full Name</span><span class="font-bold text-slate-700">{{ $account->name }}</span></div>
                        <div class="flex justify-between border-b border-slate-50 pb-2"><span class="text-slate-400">Mobile 1</span><span class="font-mono font-bold text-slate-700">{{ $account->mobile_number_1 ?? '-' }}</span></div>
                        <div class="flex justify-between border-b border-slate-50 pb-2"><span class="text-slate-400">Mobile 2</span><span class="font-mono font-bold text-slate-700">{{ $account->mobile_number_2 ?? '-' }}</span></div>
                        <div class="flex justify-between border-b border-slate-50 pb-2"><span class="text-slate-400">City</span><span class="font-bold text-slate-700">{{ $account->city->name ?? $account->city->city_name ?? '-' }}</span></div>
                        <div class="flex justify-between border-b border-slate-50 pb-2"><span class="text-slate-400">Neighborhood</span><span class="font-bold text-slate-700">{{ $account->neighborhood->name ?? $account->neighborhood->neighborhood_name ?? '-' }}</span></div>
                        <div class="flex flex-col gap-1"><span class="text-slate-400">Address</span><span class="font-bold text-slate-700 leading-tight">{{ $account->location ?? 'No address provided' }}</span></div>
                    </div>
                </div>
            </div>

            {{-- Last Movement Section --}}
            <div x-data="{ open: true }" class="border-b border-slate-100">
                <button @click="open = !open" class="w-full flex items-center justify-between p-4 hover:bg-slate-50 transition-colors">
                    <span class="text-sm font-bold text-slate-700">Last Move</span>
                    <svg class="w-4 h-4 text-slate-400 transform transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-collapse class="px-4 pb-4">
                    <div class="space-y-2">
                        @foreach($lastMovements as $label => $date)
                            <div class="flex items-center justify-between p-2 rounded hover:bg-slate-50 transition-colors group">
                                <span class="text-xs font-bold text-slate-500 group-hover:text-indigo-600">{{ $label }}</span>
                                <span class="text-xs font-mono font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded">{{ $date }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>