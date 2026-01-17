<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}"
      class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Smart System') }}</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [v-cloak] { display: none; }
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
        .content-blur { 
            filter: blur(12px) grayscale(30%); 
            pointer-events: none; user-select: none; 
            transition: filter 0.4s cubic-bezier(0.4, 0, 0.2, 1); opacity: 0.7;
        }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-[#f8fafc] font-sans antialiased text-slate-900 h-full overflow-x-hidden" 
      x-data="{ isLoading: false, isBlurred: false }">

    @php
        $globalBranches = \App\Models\Branch::where('is_active', true)->get();
        $currentBranchId = session('current_branch_id', 'all');
    @endphp

    {{-- 1. LOADING OVERLAY --}}
    <div x-show="isLoading"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 backdrop-blur-none"
         x-transition:enter-end="opacity-100 backdrop-blur-sm"
         style="display: none;"
         class="fixed inset-0 z-[200] flex items-center justify-center bg-white/60 backdrop-blur-[2px]">
        <div class="flex flex-col items-center gap-4 p-6 bg-white shadow-2xl rounded-2xl border border-slate-100">
            <svg class="animate-spin h-10 w-10 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-xs font-black text-slate-500 uppercase tracking-[0.2em] animate-pulse">{{ __('messages.switch_success') }}...</span>
        </div>
    </div>

    {{-- 2. FLOATING PRIVACY BUTTON (Fixed Bottom Right/Left) --}}
    <div class="fixed bottom-6 ltr:right-6 rtl:left-6 z-[100] print:hidden">
        <button @click="isBlurred = !isBlurred" 
                type="button" 
                :class="isBlurred ? 'bg-indigo-600 text-white ring-4 ring-indigo-200' : 'bg-white text-slate-500 hover:text-indigo-600 shadow-xl border border-slate-100'"
                class="flex items-center gap-3 px-5 py-3 rounded-full font-bold text-[11px] uppercase tracking-widest transition-all hover:scale-105 active:scale-95 group">
            
            <div :class="isBlurred ? 'text-white' : 'text-indigo-500'">
                <svg x-show="!isBlurred" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                <svg x-show="isBlurred" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
            </div>
            
            <span>{{ __('messages.privacy_mode') }}</span>
        </button>
    </div>

    <x-notification />
    <x-right-menu />

    <div class="transition-all duration-300 min-h-screen flex flex-col ltr:md:ml-56 rtl:md:mr-56">
        
        {{-- HEADER --}}
        <header class="sticky top-0 z-30 px-3 md:px-6 py-3">
            <div class="bg-white/80 backdrop-blur-xl border border-white/40 shadow-sm rounded-2xl px-4 py-3">
                
                {{-- Flex Container --}}
                <div class="flex flex-col md:flex-row items-center gap-y-4">
                    
                    {{-- 1. LEFT SIDE: Sidebar Toggle (Hidden on PC) --}}
                    <div class="w-full md:w-auto flex justify-between md:hidden">
                        <button onclick="toggleSidebar()" class="p-2 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                        </button>
                    </div>

                    {{-- 2. RIGHT SIDE GROUP --}}
                    {{-- 'md:ml-auto' pushes everything to the RIGHT on PC --}}
                    <div class="w-full md:w-auto flex flex-col md:flex-row items-center justify-end gap-3 md:ml-auto">
                        
                        {{-- BUTTONS (Finance + Help) --}}
                        <div class="flex flex-row items-center gap-2">
                             {{-- Finance --}}
                             <button type="button" class="flex-1 md:flex-none inline-flex items-center justify-center h-10 px-4 rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-emerald-600 hover:border-emerald-200 hover:bg-emerald-50 transition-all shadow-sm active:scale-95 group">
                                <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="hidden sm:inline ml-2 text-[10px] font-black uppercase tracking-widest">{{ __('messages.finance') }}</span>
                            </button>

                            {{-- Help --}}
                            <button type="button" class="flex-1 md:flex-none inline-flex items-center justify-center h-10 px-4 rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-sky-600 hover:border-sky-200 hover:bg-sky-50 transition-all shadow-sm active:scale-95 group">
                                <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="hidden sm:inline ml-2 text-[10px] font-black uppercase tracking-widest">{{ __('Help') }}</span>
                            </button>
                        </div>

                        {{-- BRANCH SELECTOR --}}
                        {{-- FIXED WIDTH: w-40 (Mobile) / w-48 (Desktop) --}}
                        {{-- This prevents horizontal scrolling or stretching --}}
                        <div class="relative z-50">
                            <form action="{{ route('branch.switch') }}" method="POST" class="relative group w-40 md:w-48">
                                @csrf
                                
                                {{-- Icon --}}
                                <div class="absolute inset-y-0 ltr:left-0 rtl:right-0 pl-3 rtl:pr-3 flex items-center pointer-events-none z-10">
                                    <svg class="w-4 h-4 {{ $currentBranchId !== 'all' ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>

                                {{-- Active Dot --}}
                                @if($currentBranchId !== 'all')
                                <span class="absolute top-0 ltr:right-0 rtl:left-0 -mt-1 -mr-1 flex h-2.5 w-2.5 z-20">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500 border-2 border-white"></span>
                                </span>
                                @endif

                                {{-- SELECT INPUT --}}
                                <select name="branch_id" 
                                        @change="isLoading = true; $el.form.submit()"
                                        class="block w-full h-10 pl-9 pr-8 rtl:pr-9 rtl:pl-8 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm hover:border-indigo-300 transition-all cursor-pointer appearance-none outline-none truncate">
                                    
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

                                {{-- Chevron --}}
                                <div class="absolute inset-y-0 ltr:right-0 rtl:left-0 flex items-center pr-2.5 rtl:pl-2.5 pointer-events-none text-slate-400 group-hover:text-indigo-600 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path></svg>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </header>

        <main :class="isBlurred ? 'content-blur' : ''" class="p-4 md:p-8 lg:p-12 flex-1 flex flex-col">
            <div class="max-w-7xl w-full mx-auto flex-1 flex flex-col gap-8">
                @if (isset($header))
                <div class="w-full flex justify-center items-center">
                    <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] text-center bg-slate-100/50 px-6 py-2 rounded-full border border-slate-200/50">{{ $header }}</h2>
                </div>
                @endif
                <div class="bg-white rounded-[2rem] md:rounded-[2.5rem] border border-slate-200/60 shadow-sm p-4 md:p-10 flex-1 min-h-[calc(100vh-16rem)]">
                    {{ $slot }}
                </div>
            </div>
        </main>

        <footer class="px-10 py-8 text-center border-t border-slate-100 mt-auto bg-white/50">
             <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">© {{ date('Y') }} Smart System • {{ __('messages.all_rights_reserved') }}</p>
        </footer>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const toggleSidebar = () => {
            const sidebar = document.getElementById('sidebar');
            const isRtl = document.documentElement.dir === 'rtl';
            const hideClass = isRtl ? 'translate-x-full' : '-translate-x-full';
            sidebar.classList.toggle(hideClass); sidebar.classList.toggle('translate-x-0');
        };
        window.confirmAction = function(formId, message = null) {
            Swal.fire({
                title: "{{ __('messages.confirm_action') }}",
                text: message || "{{ __('messages.cannot_undo') }}",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#f1f5f9',
                confirmButtonText: "{{ __('messages.yes_proceed') }}",
                cancelButtonText: "{{ __('messages.cancel') }}",
                customClass: {
                    popup: 'rounded-[1.5rem] border-none shadow-2xl',
                    confirmButton: 'rounded-xl font-black uppercase tracking-widest text-[10px] px-6 py-3',
                    cancelButton: 'rounded-xl font-black uppercase tracking-widest text-[10px] px-6 py-3 text-slate-600'
                }
            }).then((result) => { if (result.isConfirmed) document.getElementById(formId).submit(); });
        }
    </script>
</body>
</html>