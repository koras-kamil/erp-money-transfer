{{-- resources/views/partials/command-bar.blade.php --}}
<div class="sticky top-0 z-30 px-3 md:px-6 py-2 bg-[#f8fafc]/90 backdrop-blur-sm">
    <div class="max-w-7xl mx-auto w-full">
        <div class="bg-white border border-slate-200/60 shadow-sm rounded-2xl px-3 py-2">
            <div class="flex flex-nowrap items-center justify-between w-full h-10 gap-2">
                
                {{-- LEFT: Mobile Toggle --}}
                <div class="flex items-center md:hidden shrink-0">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                    </button>
                </div>

                {{-- RIGHT: Actions & Branch --}}
                <div class="flex flex-1 flex-row items-center justify-end gap-2 overflow-hidden">
                    
                    {{-- Desktop Buttons --}}
                    <div class="hidden lg:flex items-center gap-2 shrink-0">
                        <button @click="showExchangeModal = true" type="button" class="flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-600 rounded-xl border border-emerald-100 hover:bg-emerald-100 transition active:scale-95">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="text-xs font-bold uppercase tracking-wide">{{ __('messages.finance') }}</span>
                        </button>
                        <button @click="isBlurred = !isBlurred" type="button" class="flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl border border-indigo-100 hover:bg-indigo-100 transition active:scale-95">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <span class="text-xs font-bold uppercase tracking-wide">{{ __('Help') }}</span>
                        </button>
                    </div>

                    {{-- Mobile Menu (Three Dots) --}}
                    <div class="lg:hidden relative shrink-0">
                        <button @click="mobileMenuOpen = !mobileMenuOpen" @click.outside="mobileMenuOpen = false" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-600 border border-slate-200 hover:bg-slate-100 transition active:scale-95">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                        </button>
                        {{-- Mobile Dropdown --}}
                        <div x-show="mobileMenuOpen" x-transition class="absolute ltr:right-0 rtl:left-0 top-12 w-56 bg-white rounded-xl shadow-2xl border border-slate-100 p-2 z-50" x-cloak>
                            <div class="px-3 py-2 text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('messages.quick_actions') }}</div>
                            <button @click="showExchangeModal = true; mobileMenuOpen = false" class="w-full flex items-center gap-3 px-3 py-2.5 text-sm font-bold text-slate-700 hover:bg-emerald-50 hover:text-emerald-600 rounded-lg transition">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                                <span>{{ __('messages.finance') }}</span>
                            </button>
                            <button @click="isBlurred = !isBlurred; mobileMenuOpen = false" class="w-full flex items-center gap-3 px-3 py-2.5 text-sm font-bold text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition mt-1">
                                <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg></div>
                                <span>{{ __('messages.privacy_mode') }}</span>
                            </button>
                        </div>
                    </div>

                    {{-- BRANCH SELECTOR (Fixed Dropdown) --}}
                    {{-- x-data here ensures it works independently without relying on parent --}}
                    <div x-data="{ branchDropdownOpen: false }" class="relative z-50 group w-auto md:w-48" @click.outside="branchDropdownOpen = false">
                        
                        <form id="branch-switch-form" action="{{ route('branch.switch') }}" method="POST" class="hidden">
                            @csrf
                            <input type="hidden" name="branch_id" id="hidden_branch_id">
                        </form>

                        <button @click="branchDropdownOpen = !branchDropdownOpen" type="button" 
                            class="inline-flex items-center justify-between w-full h-10 px-3 md:px-4 bg-white border border-slate-200 text-slate-700 text-[11px] md:text-sm font-bold rounded-xl shadow-sm hover:bg-slate-50 hover:border-indigo-300 focus:ring-2 focus:ring-indigo-500/20 transition-all truncate">
                            <div class="flex items-center gap-2 truncate">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                <span class="truncate">
                                    @php
                                        $currentBranch = \App\Models\Branch::find(session('current_branch_id'));
                                        echo $currentBranch ? $currentBranch->name : __('messages.all_branches');
                                    @endphp
                                </span>
                            </div>
                            <svg class="w-3 h-3 text-slate-400 ml-2 transition-transform duration-200 shrink-0" :class="branchDropdownOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        {{-- DROPDOWN LIST (Fixed Width & Spacing) --}}
                        <div x-show="branchDropdownOpen" 
                             x-transition.opacity.duration.200ms
                             class="absolute top-full ltr:right-0 rtl:left-0 mt-1 w-full min-w-full bg-white border border-slate-100 rounded-xl shadow-xl z-50 overflow-hidden" 
                             style="display: none;" x-cloak>
                            
                            {{-- Added max-h for scrolling if too many branches --}}
                            <ul class="py-1 text-sm text-slate-700 font-medium divide-y divide-slate-50 max-h-60 overflow-y-auto custom-scrollbar">
                                <li>
                                    <a href="#" onclick="event.preventDefault(); document.getElementById('hidden_branch_id').value='all'; document.getElementById('branch-switch-form').submit();" 
                                       class="flex items-center w-full px-3 py-2 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                        <span class="w-2 h-2 rounded-full bg-slate-300 mr-2 shrink-0"></span>
                                        <span class="truncate">{{ __('messages.all_branches') }}</span>
                                    </a>
                                </li>
                                @foreach(\App\Models\Branch::where('is_active', true)->get() as $branch)
                                <li>
                                    <a href="#" onclick="event.preventDefault(); document.getElementById('hidden_branch_id').value='{{ $branch->id }}'; document.getElementById('branch-switch-form').submit();" 
                                       class="flex items-center w-full px-3 py-2 hover:bg-slate-50 hover:text-indigo-600 transition-colors">
                                        <span class="truncate">{{ $branch->name }}</span>
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>