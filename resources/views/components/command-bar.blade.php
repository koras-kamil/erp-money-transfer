<div class="w-full">

    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between w-full gap-3 sm:gap-4 px-3 sm:px-4 py-3 sm:py-4">
        
        <div class="w-full sm:w-auto order-2 sm:order-1">
            <div class="relative group w-full sm:w-56">
                <select id="branch-select" aria-label="{{ __('messages.select_branch') }}" class="w-full appearance-none bg-gradient-to-br from-slate-50 to-slate-100 border border-slate-200 text-slate-800 text-base sm:text-base rounded-lg px-4 py-3 ltr:pr-10 sm:ltr:pr-10 rtl:pl-10 sm:rtl:pl-10 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all cursor-pointer shadow-sm hover:shadow-md">
                    <option value="" disabled selected>{{ __('messages.select_branch') }}</option>
                    @foreach($branches ?? [] as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 ltr:right-3 sm:ltr:right-3 rtl:left-3 sm:rtl:left-3 flex items-center pointer-events-none text-slate-400 group-hover:text-indigo-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch gap-2 sm:gap-3 w-full sm:w-auto order-1 sm:order-2">
            
            <button type="button" name="finance" aria-label="{{ __('messages.finance') }}" class="w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-3 rounded-lg bg-gradient-to-br from-emerald-50 to-emerald-100 text-emerald-600 border border-emerald-200 hover:from-emerald-100 hover:to-emerald-200 hover:shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1" title="{{ __('messages.finance') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </button>

            <button 
                @click="isBlurred = !isBlurred" 
                type="button" 
                name="preview" 
                aria-label="{{ __('messages.preview') }}" 
                class="w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-3 rounded-lg bg-gradient-to-br from-indigo-50 to-indigo-100 text-indigo-600 border border-indigo-200 hover:from-indigo-100 hover:to-indigo-200 hover:shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1" 
                title="{{ __('messages.preview') }}"
            >
                <svg x-show="!isBlurred" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                <svg x-show="isBlurred" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
            </button>
        </div>
    </div>

    <div class="p-4">
        </div>

</div>