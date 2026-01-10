@props(['label', 'active' => false, 'icon'])

<div class="relative group">
    <button class="w-full flex items-center justify-between px-3 py-2 rounded-lg transition-all text-sm font-medium
                   {{ $active ? 'text-white bg-slate-800' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100' }}">
        <div class="flex items-center gap-3">
            <div class="{{ $active ? 'text-indigo-400' : 'opacity-70 group-hover:text-indigo-400' }}">
                {{ $icon }}
            </div>
            <span>{{ $label }}</span>
        </div>
        <svg class="w-3 h-3 text-slate-600 transition-transform rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </button>

    <div class="absolute top-0 w-48 py-2 bg-[#0f172a] border border-slate-700 rounded-xl shadow-2xl z-50
                invisible opacity-0 
                ltr:translate-x-2 rtl:-translate-x-2 
                group-hover:translate-x-0 group-hover:opacity-100 group-hover:visible
                transition-all duration-300 ease-out
                ltr:left-[105%] rtl:right-[105%]">
                
        <div class="absolute top-3 w-2 h-2 bg-[#0f172a] border-l border-t border-slate-700 rotate-45 ltr:-left-1.5 rtl:-right-1.5"></div>
        <div class="space-y-1 px-2">
            {{ $slot }}
        </div>
    </div>
</div>