<div x-data="{ open: false }" class="relative">
    {{-- THE BELL BUTTON --}}
    <button @click="open = !open" class="relative w-10 h-10 flex items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-all shadow-sm">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>

        {{-- Red Dot Counter --}}
        @if(auth()->user()->unreadNotifications->count() > 0)
            <span class="absolute top-2 right-2 w-2.5 h-2.5 bg-red-500 border-2 border-white rounded-full"></span>
        @endif
    </button>

    {{-- THE DROPDOWN --}}
    <div x-show="open" 
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="absolute left-0 mt-3 w-80 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 overflow-hidden"
         style="display: none;">
        
        <div class="px-4 py-3 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <span class="text-xs font-black text-slate-500 uppercase tracking-wider">{{ __('notifications') }}</span>
            @if(auth()->user()->unreadNotifications->count() > 0)
                <a href="{{ route('notifications.readAll') }}" class="text-[10px] font-bold text-blue-600 hover:underline">{{ __('mark_all_read') }}</a>
            @endif
        </div>

        <div class="max-h-80 overflow-y-auto custom-scrollbar">
            @forelse(auth()->user()->unreadNotifications as $notification)
                <div class="p-3 border-b border-slate-50 hover:bg-slate-50 transition-colors cursor-pointer group relative">
                    <div class="flex gap-3">
                        {{-- Icon based on Type --}}
                        <div class="flex-shrink-0 mt-1">
                            @if($notification->data['type'] == 'warning')
                                <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-orange-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                </div>
                            @elseif($notification->data['type'] == 'success')
                                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            @else
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                            @endif
                        </div>
                        
                        {{-- Content --}}
                        <div>
                            <h4 class="text-sm font-bold text-slate-700">{{ $notification->data['title'] }}</h4>
                            <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">{{ $notification->data['message'] }}</p>
                            <span class="text-[10px] text-slate-400 mt-2 block">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center">
                    <svg class="w-12 h-12 text-slate-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                    <p class="text-xs text-slate-400 font-medium">{{ __('no_notifications') }}</p>
                </div>
            @endforelse
        </div>
    </div>
</div>