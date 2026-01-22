<div class="w-full">
    {{-- 1. PREPARE DATA --}}
    @php
        $globalBranches = \App\Models\Branch::where('is_active', true)->get();
        $currentBranchId = session('current_branch_id', 'all');
    @endphp

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between w-full gap-3 px-3 py-3 sm:px-4 sm:py-4">
        
        {{-- 2. BUTTONS GROUP --}}
        <div class="flex flex-row items-center justify-end gap-2 w-full sm:w-auto order-1 sm:order-2">
            
            {{-- Finance Button --}}
            <button type="button" 
                class="inline-flex items-center justify-center h-10 w-10 sm:w-auto sm:px-4 rounded-lg bg-gradient-to-br from-emerald-50 to-emerald-100 text-emerald-600 border border-emerald-200 hover:shadow-md transition-all active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="hidden sm:inline ml-2 text-sm font-bold">{{ __('messages.finance') }}</span>
            </button>

            {{-- Privacy Mode Button --}}
            <button @click="isBlurred = !isBlurred" 
                type="button" 
                class="inline-flex items-center justify-center h-10 w-10 sm:w-auto sm:px-4 rounded-lg bg-gradient-to-br from-indigo-50 to-indigo-100 text-indigo-600 border border-indigo-200 hover:shadow-md transition-all active:scale-95">
                <svg x-show="!isBlurred" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                <svg x-show="isBlurred" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                <span class="hidden sm:inline ml-2 text-sm font-bold">{{ __('messages.privacy_mode') }}</span>
            </button>
        </div>

        {{-- 3. BRANCH SELECTOR --}}
        <div class="w-full sm:w-auto order-2 sm:order-1 relative group sm:w-64">
            <form action="{{ route('branch.switch') }}" method="POST">
                @csrf
                
                {{-- SELECT INPUT --}}
                <select name="branch_id" 
                        onchange="this.form.submit()" 
                        id="branch-select" 
                        class="w-full appearance-none bg-white border border-slate-200 text-slate-800 text-sm font-bold rounded-lg px-4 py-2.5 
                               focus:ring-2 focus:ring-indigo-500 transition-all cursor-pointer shadow-sm
                               ltr:pl-10 ltr:pr-10 ltr:text-left 
                               rtl:pr-10 rtl:pl-10 rtl:text-right">
                    
                    <option value="all" {{ $currentBranchId === 'all' ? 'selected' : '' }}>
                        {{ __('messages.all_branches') }}
                    </option>
                    
                    <option disabled>──────────</option>

                    @foreach($globalBranches as $branch)
                        <option value="{{ $branch->id }}" {{ $currentBranchId == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>

                {{-- BUILDING ICON (Start Side) --}}
                {{-- Left for LTR, Right for RTL --}}
                <div class="absolute inset-y-0 flex items-center pointer-events-none text-slate-400 px-3
                            ltr:left-0 rtl:right-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>

                {{-- ARROW ICON (End Side) --}}
                {{-- Right for LTR, Left for RTL --}}
                <div class="absolute inset-y-0 flex items-center pointer-events-none text-slate-400 px-3 group-hover:text-indigo-500
                            ltr:right-0 rtl:left-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </form>
        </div>

    </div>
</div>