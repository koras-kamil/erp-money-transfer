@props(['links'])

<div class="bg-white p-1.5 rounded-xl border border-slate-200 shadow-sm flex items-center w-fit mb-6 no-print">
    @foreach($links as $label => $route)
        @php
            // Check if the current URL matches this route pattern to set 'active' state
            // You can pass specific route names or patterns
            $isActive = request()->routeIs($route['active_pattern'] ?? '');
        @endphp

        <a href="{{ $route['url'] }}" 
           class="px-4 py-2 text-sm font-bold rounded-lg transition-all {{ $isActive ? 'bg-indigo-50 text-indigo-600 shadow-sm border border-indigo-100' : 'text-slate-500 hover:text-indigo-600 hover:bg-slate-50' }}">
           {{ $label }}
        </a>

        @if(!$loop->last)
            <div class="w-px h-4 bg-slate-200 mx-1"></div>
        @endif
    @endforeach
</div>