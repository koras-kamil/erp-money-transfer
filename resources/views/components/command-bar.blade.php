<header class="sticky top-0 z-30 px-3 md:px-6 py-2 bg-[#f8fafc]/90 backdrop-blur-sm">
    <div class="max-w-7xl mx-auto w-full">
        <div class="bg-white border border-slate-200/60 shadow-sm rounded-2xl px-3 py-2">
            
            {{-- TOOLBAR CONTAINER --}}
            <div class="flex flex-nowrap items-center justify-between w-full h-10 gap-2">
                
                {{-- LEFT: Mobile Menu Toggle --}}
                <div class="flex items-center md:hidden shrink-0">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" 
                            class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-600 border border-slate-200 hover:bg-slate-100 hover:text-indigo-600 transition active:scale-95">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                    </button>
                </div>

                {{-- RIGHT: Actions & Branch Selector --}}
                <div class="flex flex-1 flex-row items-center justify-end gap-3 overflow-visible">
                    
                    {{-- Desktop Only: Help Button --}}
                    <div class="hidden md:flex items-center shrink-0">
                        <button @click="isBlurred = !isBlurred" type="button" class="flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl border border-indigo-100 hover:bg-indigo-100 transition active:scale-95 h-10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <span class="text-xs font-bold uppercase tracking-wide">{{ __('Help') }}</span>
                        </button>
                    </div>

                    {{-- 
                        FINANCE BUTTON (Exchange Rate)
                        - Matches Branch Selector Style (Icon Box Right, Text Left)
                    --}}
                    <button @click="showExchangeModal = true" type="button" 
                        class="relative group flex items-center justify-center md:justify-between transition-all duration-200 bg-white border border-slate-200 rounded-xl shadow-sm hover:border-emerald-300 focus:ring-2 focus:ring-emerald-500/20 active:scale-95
                               w-10 h-10 md:w-auto md:h-10 md:pl-4 md:pr-1.5 md:py-1.5"> 
                        
                        <div class="flex items-center gap-3">
                            {{-- Text (Left) --}}
                            <span class="hidden md:block text-xs font-bold text-slate-700 truncate">{{ __('messages.finance') }}</span>
                            
                            {{-- Icon Box (Right) --}}
                            <div class="bg-emerald-50 text-emerald-600 rounded-lg p-1.5 shrink-0 group-hover:bg-emerald-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>
                    </button>

                    {{-- 
                        BRANCH SELECTOR (The Style from Screenshot)
                        - Mobile: Square Icon Button
                        - Desktop: [Chevron + Text] ... [Icon Box]
                    --}}
                    <div x-data="{ 
                            open: false,
                            submitBranch(id) {
                                $refs.branchInput.value = id;
                                $refs.branchForm.submit();
                                isLoading = true; 
                            }
                        }" 
                        class="relative z-50 group"
                        @click.outside="open = false">
                        
                        <form x-ref="branchForm" action="{{ route('branch.switch') }}" method="POST" class="hidden">
                            @csrf
                            <input x-ref="branchInput" type="hidden" name="branch_id">
                        </form>

                        {{-- Trigger Button --}}
                        <button @click="open = !open" type="button" 
                            class="flex items-center justify-center md:justify-between transition-all duration-200 bg-white border border-slate-200 rounded-xl shadow-sm hover:border-indigo-300 focus:ring-2 focus:ring-indigo-500/20 active:scale-95
                                   w-10 h-10 md:w-auto md:h-10 md:pl-3 md:pr-1.5 md:py-1.5"> 
                            
                            {{-- MOBILE VIEW: Just Icon --}}
                            <div class="md:hidden bg-indigo-50 text-indigo-600 rounded-lg p-1.5 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            </div>

                            {{-- DESKTOP VIEW: Matches Screenshot --}}
                            <div class="hidden md:flex items-center gap-4">
                                {{-- Left Side: Chevron + Text --}}
                                <div class="flex items-center gap-2 text-slate-700">
                                    <svg class="w-3 h-3 transition-transform duration-200 text-slate-400" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    <span class="text-xs font-bold truncate max-w-[120px]">
                                        @php
                                            $currentBranchId = session('current_branch_id', 'all');
                                            if($currentBranchId === 'all') {
                                                echo __('messages.all_branches');
                                            } else {
                                                $branch = \App\Models\Branch::find($currentBranchId);
                                                echo $branch ? $branch->name : __('messages.all_branches');
                                            }
                                        @endphp
                                    </span>
                                </div>

                                {{-- Right Side: Icon Box --}}
                                <div class="bg-indigo-50 text-indigo-600 rounded-lg p-1.5 shrink-0 group-hover:bg-indigo-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                </div>
                            </div>
                        </button>

                        {{-- Dropdown Menu --}}
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                             class="absolute top-full ltr:right-0 rtl:left-0 mt-2 w-[240px] bg-white border border-slate-100 rounded-xl shadow-2xl z-50 overflow-hidden ring-1 ring-black/5" 
                             style="display: none;" x-cloak>
                            
                            <ul class="py-2 text-sm font-medium divide-y divide-slate-50 max-h-[300px] overflow-y-auto custom-scrollbar">
                                <li>
                                    <a href="#" @click.prevent="submitBranch('all')" 
                                       class="flex items-center w-full px-4 py-3 gap-3 transition-colors group {{ session('current_branch_id') == 'all' ? 'bg-indigo-50' : 'hover:bg-slate-50' }}">
                                        
                                        <div class="w-5 h-5 flex items-center justify-center shrink-0 {{ session('current_branch_id') == 'all' ? 'text-indigo-600' : 'text-slate-300' }}">
                                            @if(session('current_branch_id') == 'all')
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                            @endif
                                        </div>
                                        
                                        <span class="flex-1 block text-xs font-bold {{ session('current_branch_id') == 'all' ? 'text-indigo-700' : 'text-slate-700' }}">{{ __('messages.all_branches') }}</span>
                                        
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 transition-colors {{ session('current_branch_id') == 'all' ? 'bg-indigo-200 text-indigo-700' : 'bg-slate-100 text-slate-400' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                        </div>
                                    </a>
                                </li>

                                @foreach(\App\Models\Branch::where('is_active', true)->get() as $branch)
                                <li>
                                    <a href="#" @click.prevent="submitBranch('{{ $branch->id }}')" 
                                       class="flex items-center w-full px-4 py-3 gap-3 transition-colors group {{ session('current_branch_id') == $branch->id ? 'bg-indigo-50' : 'hover:bg-slate-50' }}">
                                        
                                        <div class="w-5 h-5 flex items-center justify-center shrink-0 {{ session('current_branch_id') == $branch->id ? 'text-indigo-600' : 'text-slate-300' }}">
                                            @if(session('current_branch_id') == $branch->id)
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                            @endif
                                        </div>

                                        <span class="flex-1 block text-xs font-bold {{ session('current_branch_id') == $branch->id ? 'text-indigo-700' : 'text-slate-700' }}">{{ $branch->name }}</span>

                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 transition-colors {{ session('current_branch_id') == $branch->id ? 'bg-indigo-200 text-indigo-700' : 'bg-slate-100 text-slate-400' }}">
                                            <span class="text-xs font-black">{{ substr($branch->name, 0, 1) }}</span>
                                        </div>
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    {{-- End Branch Selector --}}

                </div>
            </div>
        </div>
    </div>
</header>