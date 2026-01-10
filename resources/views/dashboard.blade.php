<x-app-layout>
    
    <div class="flex items-center justify-end gap-2 p-4 px-8">
        <a href="{{ route('lang.switch', 'en') }}" 
           class="flex items-center gap-2 px-4 py-2 text-sm font-medium transition-all duration-200 rounded-lg shadow-sm
           {{ app()->getLocale() == 'en' 
             ? 'bg-indigo-600 text-white ring-2 ring-indigo-300' 
             : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' }}">
            <span>🇬🇧</span>
            <span>English</span>
        </a>

        <a href="{{ route('lang.switch', 'ku') }}" 
           class="flex items-center gap-2 px-4 py-2 text-sm font-medium transition-all duration-200 rounded-lg shadow-sm
           {{ app()->getLocale() == 'ku' 
             ? 'bg-emerald-600 text-white ring-2 ring-emerald-300' 
             : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' }}">
            <span>☀️</span>
            <span>کوردی</span>
        </a>
    </div>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('messages.dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" >
                
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl p-6 border-l-4 border-indigo-500 relative group transition hover:shadow-lg" :class="isBlurred ? 'blur-md select-none' : ''">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider" >
                                {{ __('messages.total_currencies') }}
                            </p>
                            <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white"  >
                                {{ $totalCurrencies ?? 0 }}
                            </p>
                        </div>
                        <div class="p-3 bg-indigo-50 dark:bg-indigo-900/30 rounded-full text-indigo-600 dark:text-indigo-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl p-6 border-l-4 border-emerald-500 relative group transition hover:shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                {{ __('messages.active_currencies') }}
                            </p>
                            <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">
                                {{ $activeCurrencies ?? 0 }}
                            </p>
                        </div>
                        <div class="p-3 bg-emerald-50 dark:bg-emerald-900/30 rounded-full text-emerald-600 dark:text-emerald-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

            </div>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("messages.welcome") }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>