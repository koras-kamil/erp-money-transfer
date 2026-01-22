
@props(['label', 'active' => false, 'icon'])

<div class="relative group" 
     x-data="{ 
        open: @json($active) && window.innerWidth < 768, 
        timer: null,
        isMobile: window.innerWidth < 768,
        position: { top: 0, left: 0 },
        
        show() {
            if(this.isMobile) return;
            clearTimeout(this.timer);
            
            // 1. Calculate position dynamically based on button location
            const button = this.$refs.button;
            const rect = button.getBoundingClientRect();
            const isRtl = document.documentElement.dir === 'rtl';
            
            // Align top of popup with top of button
            this.position.top = rect.top;
            
            // Calculate Left/Right based on direction
            // w-48 is 12rem (192px). We add a small buffer gap.
            if (isRtl) {
                // RTL: Sidebar is on right, popup goes to the left of it
                this.position.left = rect.left - 200; 
            } else {
                // LTR: Sidebar is on left, popup goes to the right of it
                this.position.left = rect.right + 8;
            }

            this.open = true;
        },
        hide() {
            if(this.isMobile) return;
            // Small delay to allow moving mouse to the popup
            this.timer = setTimeout(() => { this.open = false }, 150);
        },
        toggle() {
            if(this.isMobile) this.open = !this.open;
        },
        handleResize() {
            this.isMobile = window.innerWidth < 768;
            if (!this.isMobile) this.open = false; 
        }
     }" 
     x-init="$watch('isMobile', value => { if(!value) open = false })"
     @resize.window="handleResize()"
     @mouseenter="show()" 
     @mouseleave="hide()">
    
    {{-- MAIN BUTTON --}}
    <button x-ref="button" 
            @click="toggle()" 
            type="button"
            class="w-full flex items-center px-2.5 py-2 rounded-xl transition-all duration-200 text-xs font-medium border border-transparent
                   {{ $active 
                      ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20' 
                      : 'text-slate-400 hover:bg-slate-800/50 hover:text-white hover:border-slate-700/50' }}"
            :class="isCollapsed ? 'justify-center' : 'justify-between'">
        
        <div class="flex items-center gap-3">
            {{-- Icon --}}
            <div class="flex-shrink-0 transition-colors duration-200 {{ $active ? 'text-white' : 'text-slate-400 group-hover:text-indigo-400' }}">
                {{ $icon }}
            </div>
            
            {{-- Label (Hidden when Collapsed) --}}
            <span x-show="!isCollapsed" class="whitespace-nowrap transition-opacity duration-200">{{ $label }}</span>
        </div>

        {{-- Chevron (Hidden when Collapsed) --}}
        <svg x-show="!isCollapsed" 
             class="w-3 h-3 transition-transform duration-300 {{ $active ? 'text-white/70' : 'text-slate-600 group-hover:text-slate-400' }}" 
             :class="(open || '{{ $active }}') && !isMobile ? 'rotate-90' : (isMobile && open ? 'rotate-90' : 'rtl:rotate-180')"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
        </svg>
    </button>

    {{-- MOBILE CONTENT (Accordion - Inside Sidebar) --}}
    <div x-show="open && isMobile" 
         x-collapse
         class="mt-1 space-y-1 pl-3 bg-slate-800/30 rounded-lg border border-slate-700/30 overflow-hidden">
         <div class="py-2 space-y-0.5 px-2">
            {{ $slot }}
         </div>
    </div>

    {{-- DESKTOP CONTENT (Teleported Popover - Outside Sidebar) --}}
    <template x-teleport="body">
        <div x-show="open && !isMobile"
             @mouseenter="show()" 
             @mouseleave="hide()"
             :style="`top: ${position.top}px; left: ${position.left}px`"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-x-2"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed z-[9999] w-48 bg-[#1e293b] border border-slate-700 rounded-2xl shadow-2xl p-2"
             style="display: none;" {{-- Prevents FOUC --}}
             x-cloak>
             
             {{-- Arrow Pointer (Decorational) --}}
             <div class="absolute top-4 w-3 h-3 bg-[#1e293b] border-l border-t border-slate-700 rotate-[-45deg] ltr:-left-1.5 rtl:-right-1.5"></div>

             {{-- Header Label --}}
             <div class="px-3 py-2 mb-1 border-b border-slate-700/50">
                 <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">{{ $label }}</span>
             </div>

             {{-- Links Container --}}
             <div class="space-y-0.5">
                {{ $slot }}
             </div>
        </div>
    </template>
</div>

