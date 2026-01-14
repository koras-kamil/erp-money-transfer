@props(['label', 'active' => false, 'icon'])

<div class="relative group" 
     x-data="{ 
        open: @json($active) && window.innerWidth < 768, 
        timer: null,
        isMobile: window.innerWidth < 768,
        
        show() {
            if(this.isMobile) return;
            clearTimeout(this.timer);
            this.open = true;
        },
        hide() {
            if(this.isMobile) return;
            // Delay closing by 200ms to allow smooth mouse movement across the gap
            this.timer = setTimeout(() => { this.open = false }, 200);
        },
        toggle() {
            if(this.isMobile) this.open = !this.open;
        },
        handleResize() {
            this.isMobile = window.innerWidth < 768;
            if (!this.isMobile) this.open = false; // Close on desktop default
        }
     }" 
     x-init="$watch('isMobile', value => { if(!value) open = false })"
     @resize.window="handleResize()"
     @mouseenter="show()" 
     @mouseleave="hide()">
    
    {{-- Main Button --}}
    <button @click="toggle()" type="button"
        class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 text-xs font-bold
               {{ $active 
                  ? 'text-white bg-indigo-600/10 ring-1 ring-indigo-500/30' 
                  : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-100' }}">
        
        <div class="flex items-center gap-3">
            <div class="transition-colors duration-200 {{ $active ? 'text-indigo-400' : 'text-slate-500 group-hover:text-indigo-400' }}">
                {{ $icon }}
            </div>
            <span>{{ $label }}</span>
        </div>

        {{-- Fixed SVG Icon (Chevron Right) --}}
        <svg class="w-3.5 h-3.5 text-slate-600 transition-transform duration-300 {{ $active ? 'text-indigo-400' : '' }}" 
             :class="(open || '{{ $active }}') && !isMobile ? 'rotate-90' : (isMobile && open ? 'rotate-90' : 'rtl:rotate-180')"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </button>

    {{-- Dropdown / Popover Content --}}
    <div x-show="open" 
         @mouseenter="show()" 
         @mouseleave="hide()"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         :class="isMobile 
            ? 'static w-full mt-1 bg-slate-800/30 rounded-lg border border-slate-700/30' 
            : 'absolute top-0 w-48 bg-[#1e293b] border border-slate-700/50 rounded-2xl shadow-2xl z-50 ltr:left-[105%] rtl:right-[105%]'"
         style="display: none;"
         x-cloak>
                
        {{-- Little arrow pointer for Desktop Popup --}}
        <div x-show="!isMobile" class="absolute top-4 w-2 h-2 bg-[#1e293b] border-l border-t border-slate-700/50 rotate-[-45deg] ltr:-left-1.5 rtl:-right-1.5"></div>
        
        <div class="py-2 space-y-0.5 px-2">
            {{ $slot }}
        </div>
    </div>
</div>