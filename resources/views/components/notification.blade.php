<div class="fixed top-6 right-6 z-[200] flex flex-col gap-4 max-w-[360px] w-full px-4 pointer-events-none">
    
    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
    <div x-data="{ show: true, width: '100%' }" 
         x-init="
            // Start the progress bar animation
            setTimeout(() => width = '0%', 100); 
            // Hide the popup after 4 seconds
            setTimeout(() => show = false, 4000);
         "
         x-show="show" 
         x-cloak
         class="pointer-events-auto relative bg-white overflow-hidden rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] border border-slate-100 ring-1 ring-slate-900/5"
         x-transition:enter="transition ease-[cubic-bezier(0.34,1.56,0.64,1)] duration-500"
         x-transition:enter-start="opacity-0 translate-x-full scale-90"
         x-transition:enter-end="opacity-100 translate-x-0 scale-100"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-x-0 scale-100"
         x-transition:leave-end="opacity-0 translate-x-full scale-90">
        
        <div class="p-4 flex items-start gap-4">
            {{-- Animated Icon --}}
            <div class="shrink-0 rounded-full bg-emerald-50 p-2 text-emerald-500 animate-[bounce_1s_infinite]">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <div class="flex-1 pt-0.5">
                <p class="text-[13px] font-black uppercase tracking-wider text-emerald-500 mb-0.5">
                    {{ app()->getLocale() == 'ku' ? 'سەرکەوتوو' : 'Success' }}
                </p>
                <p class="text-[13px] font-medium text-slate-600 leading-relaxed">
                    {{ session('success') }}
                </p>
            </div>

            <button @click="show = false" class="shrink-0 text-slate-400 hover:text-slate-600 transition-colors">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/></svg>
            </button>
        </div>

        {{-- Progress Bar --}}
        <div class="absolute bottom-0 left-0 h-1 bg-emerald-500 transition-all duration-[4000ms] ease-linear"
             :style="'width: ' + width"></div>
    </div>
    @endif

    {{-- ERROR MESSAGE --}}
    @if(session('error'))
    <div x-data="{ show: true, width: '100%' }" 
         x-init="setTimeout(() => width = '0%', 100); setTimeout(() => show = false, 6000);"
         x-show="show" 
         x-cloak
         class="pointer-events-auto relative bg-white overflow-hidden rounded-2xl shadow-[0_10px_40px_-10px_rgba(220,38,38,0.2)] border border-red-100 ring-1 ring-red-900/5"
         x-transition:enter="transition ease-[cubic-bezier(0.34,1.56,0.64,1)] duration-500"
         x-transition:enter-start="opacity-0 translate-x-full scale-90"
         x-transition:enter-end="opacity-100 translate-x-0 scale-100"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-x-0 scale-100"
         x-transition:leave-end="opacity-0 translate-x-full scale-90">
        
        <div class="p-4 flex items-start gap-4">
            <div class="shrink-0 rounded-full bg-red-50 p-2 text-red-500 animate-[pulse_2s_infinite]">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
            </div>

            <div class="flex-1 pt-0.5">
                <p class="text-[13px] font-black uppercase tracking-wider text-red-500 mb-0.5">
                    {{ app()->getLocale() == 'ku' ? 'هەڵە' : 'Error' }}
                </p>
                <p class="text-[13px] font-medium text-slate-600 leading-relaxed">
                    {{ session('error') }}
                </p>
            </div>

            <button @click="show = false" class="shrink-0 text-slate-400 hover:text-slate-600 transition-colors">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/></svg>
            </button>
        </div>

        {{-- Progress Bar --}}
        <div class="absolute bottom-0 left-0 h-1 bg-red-500 transition-all duration-[6000ms] ease-linear"
             :style="'width: ' + width"></div>
    </div>
    @endif

</div>