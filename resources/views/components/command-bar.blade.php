<header class="sticky top-0 z-30 px-3 md:px-6 py-2 bg-[#f8fafc]/90 backdrop-blur-sm print:hidden">
    <div class="max-w-7xl mx-auto w-full">
        <div class="bg-white border border-slate-200/60 shadow-sm rounded-2xl px-3 py-2">
            
            {{-- TOOLBAR CONTAINER --}}
            <div class="flex flex-nowrap items-center justify-between w-full h-10 gap-2">
                
                {{-- 
                    🟢 RIGHT SIDE (Start in RTL) 
                    Contains: Mobile Menu & Notification Bell 
                --}}
                <div class="flex items-center gap-3">
                    
                    {{-- Mobile Menu Toggle --}}
                    <div class="md:hidden shrink-0">
                        <button @click="mobileMenuOpen = !mobileMenuOpen" 
                                class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-600 border border-slate-200 hover:bg-slate-100 hover:text-indigo-600 transition active:scale-95">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                        </button>
                    </div>

                    {{-- 🔔 NOTIFICATION BELL (White Style) --}}
                    <div x-data="{ open: false }" class="relative z-50">
                        <button @click="open = !open" type="button" 
                            class="relative flex items-center justify-center w-10 h-10 transition-all duration-200 bg-white border border-slate-200 rounded-xl shadow-sm hover:border-indigo-300 hover:text-indigo-600 focus:ring-2 focus:ring-indigo-500/20 active:scale-95 group text-slate-500">
                            
                            {{-- Icon --}}
                            <svg class="w-5 h-5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>

                            {{-- Red Dot --}}
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <span class="absolute top-2 right-2 flex h-2.5 w-2.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500 border-2 border-white"></span>
                                </span>
                            @endif
                        </button>

                        {{-- Dropdown --}}
                        <div x-show="open" @click.outside="open = false" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute top-full ltr:left-0 rtl:right-0 mt-2 w-80 bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden ring-1 ring-black/5" 
                             style="display: none;" x-cloak>
                            
                            <div class="px-4 py-3 border-b border-slate-50 bg-slate-50/50 flex justify-between items-center">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">{{ __('notifications') }}</span>
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                    <a href="{{ route('notifications.readAll') }}" class="text-[10px] font-bold text-blue-600 hover:underline">{{ __('mark_read') }}</a>
                                @endif
                            </div>

                            <div class="max-h-80 overflow-y-auto custom-scrollbar">
                                @forelse(auth()->user()->unreadNotifications as $notification)
                                    <div class="p-3 border-b border-slate-50 hover:bg-slate-50 transition-colors cursor-pointer relative group">
                                        <div class="flex gap-3">
                                            <div class="mt-1 shrink-0">
                                                @if(($notification->data['type'] ?? 'info') == 'success')
                                                    <div class="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center text-green-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>
                                                @elseif(($notification->data['type'] ?? 'info') == 'warning')
                                                    <div class="w-8 h-8 rounded-full bg-orange-50 flex items-center justify-center text-orange-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></div>
                                                @else
                                                    <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                                                @endif
                                            </div>
                                            <div>
                                                <h4 class="text-xs font-bold text-slate-700">{{ $notification->data['title'] ?? 'Notification' }}</h4>
                                                <p class="text-[10px] text-slate-500 mt-0.5 leading-relaxed">{{ $notification->data['message'] ?? '' }}</p>
                                                <span class="text-[9px] text-slate-400 mt-1 block">{{ $notification->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-8 text-center">
                                        <svg class="w-10 h-10 text-slate-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                        <p class="text-[10px] text-slate-400 font-bold">{{ __('no_notifications') }}</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 
                    🔴 LEFT SIDE (End in RTL) 
                    Contains: Help, Finance, Branch Selector 
                --}}
                <div class="flex flex-1 flex-row items-center justify-end gap-3 overflow-visible">
                    
                    {{-- 1. HELP BUTTON --}}
                    <div class="hidden md:flex items-center shrink-0">
                        <button @click="isBlurred = !isBlurred" type="button" class="flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl border border-indigo-100 hover:bg-indigo-100 transition active:scale-95 h-10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <span class="text-xs font-bold uppercase tracking-wide">{{ __('Help') }}</span>
                        </button>
                    </div>

                    {{-- 2. FINANCE BUTTON --}}
                    <button @click="showExchangeModal = true" type="button" 
                        class="relative group flex items-center justify-center md:justify-between transition-all duration-200 bg-white border border-slate-200 rounded-xl shadow-sm hover:border-emerald-300 focus:ring-2 focus:ring-emerald-500/20 active:scale-95
                               w-10 h-10 md:w-auto md:h-10 md:pl-4 md:pr-1.5 md:py-1.5"> 
                        <div class="flex items-center gap-3">
                            <span class="hidden md:block text-xs font-bold text-slate-700 truncate">{{ __('messages.finance') }}</span>
                            <div class="bg-emerald-50 text-emerald-600 rounded-lg p-1.5 shrink-0 group-hover:bg-emerald-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>
                    </button>

                    {{-- 3. BRANCH SELECTOR --}}
                    <div x-data="{ 
                            open: false,
                            submitBranch(id) { $refs.branchInput.value = id; $refs.branchForm.submit(); isLoading = true; }
                        }" 
                        class="relative z-50 group"
                        @click.outside="open = false">
                        
                        <form x-ref="branchForm" action="{{ route('branch.switch') }}" method="POST" class="hidden">@csrf <input x-ref="branchInput" type="hidden" name="branch_id"></form>

                        <button @click="open = !open" type="button" 
                            class="flex items-center justify-center md:justify-between transition-all duration-200 bg-white border border-slate-200 rounded-xl shadow-sm hover:border-indigo-300 focus:ring-2 focus:ring-indigo-500/20 active:scale-95
                                   w-10 h-10 md:w-auto md:h-10 md:pl-3 md:pr-1.5 md:py-1.5"> 
                            <div class="md:hidden bg-indigo-50 text-indigo-600 rounded-lg p-1.5 shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg></div>
                            <div class="hidden md:flex items-center gap-4">
                                <div class="flex items-center gap-2 text-slate-700">
                                    <svg class="w-3 h-3 transition-transform duration-200 text-slate-400" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    <span class="text-xs font-bold truncate max-w-[120px]">
                                        {{ session('current_branch_id', 'all') === 'all' ? __('messages.all_branches') : \App\Models\Branch::find(session('current_branch_id'))->name ?? __('messages.all_branches') }}
                                    </span>
                                </div>
                                <div class="bg-indigo-50 text-indigo-600 rounded-lg p-1.5 shrink-0 group-hover:bg-indigo-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                </div>
                            </div>
                        </button>

                        <div x-show="open" x-transition class="absolute top-full ltr:right-0 rtl:left-0 mt-2 w-[240px] bg-white border border-slate-100 rounded-xl shadow-2xl z-50 overflow-hidden ring-1 ring-black/5" style="display: none;" x-cloak>
                            <ul class="py-2 text-sm font-medium divide-y divide-slate-50 max-h-[300px] overflow-y-auto custom-scrollbar">
                                <li><a href="#" @click.prevent="submitBranch('all')" class="flex items-center w-full px-4 py-3 gap-3 hover:bg-slate-50"><span class="flex-1 block text-xs font-bold">{{ __('messages.all_branches') }}</span></a></li>
                                @foreach(\App\Models\Branch::where('is_active', true)->get() as $branch)
                                <li><a href="#" @click.prevent="submitBranch('{{ $branch->id }}')" class="flex items-center w-full px-4 py-3 gap-3 hover:bg-slate-50"><span class="flex-1 block text-xs font-bold">{{ $branch->name }}</span></a></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</header>