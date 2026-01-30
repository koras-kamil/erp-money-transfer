@props(['active'])

<div class="bg-white p-1.5 rounded-xl border border-slate-200 shadow-sm flex items-center w-fit mb-8 no-print">
    
    {{-- 1. Main Accounts Tab --}}
    <a href="{{ route('accounts.index') }}" 
       class="px-4 py-2 text-sm font-bold rounded-lg transition-all {{ $active === 'accounts' ? 'bg-indigo-50 text-indigo-600 shadow-sm border border-indigo-100' : 'text-slate-500 hover:text-indigo-600 hover:bg-slate-50' }}">
       {{ __('account.main_tab') }}
    </a>

    {{-- Separator --}}
    <div class="w-px h-4 bg-slate-200 mx-1"></div>

    {{-- 2. Zones Tab --}}
    <a href="{{ route('zones.index') }}" 
       class="px-4 py-2 text-sm font-bold rounded-lg transition-all {{ $active === 'zones' ? 'bg-indigo-50 text-indigo-600 shadow-sm border border-indigo-100' : 'text-slate-500 hover:text-indigo-600 hover:bg-slate-50' }}">
       {{ __('account.zones_list') }}
    </a>

    {{-- Separator --}}
    <div class="w-px h-4 bg-slate-200 mx-1"></div>

    {{-- 3. Reports Tab --}}
    <a href="#" 
       class="px-4 py-2 text-sm font-bold rounded-lg transition-all {{ $active === 'reports' ? 'bg-indigo-50 text-indigo-600 shadow-sm border border-indigo-100' : 'text-slate-500 hover:text-indigo-600 hover:bg-slate-50' }}">
       {{ __('account.reports_tab') }}
    </a>

</div>