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
    </style>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-[#f8fafc] font-sans antialiased text-slate-900 h-full overflow-x-hidden" x-data="{ isBlurred: false }">

    <x-right-menu />

    <div class="transition-all duration-300 min-h-screen flex flex-col ltr:md:ml-56 rtl:md:mr-56">
        
   <header class="bg-white/70 backdrop-blur-xl border-b border-slate-200/60 sticky top-0 z-30 px-2 md:px-6 py-2">
    <div class="flex flex-col md:flex-row items-center justify-between gap-y-3">
        
        <button onclick="toggleSidebar()" class="md:hidden self-start p-2 rounded-lg bg-slate-100 text-slate-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
        </button>

        <div class="w-full flex flex-col sm:flex-row items-center justify-between gap-3">
            
            <div class="flex flex-row items-center justify-end gap-2 w-full sm:w-auto order-1 sm:order-2">
                
                <button type="button" class="inline-flex items-center justify-center h-10 w-10 rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 text-emerald-600 border border-emerald-200 hover:shadow-md transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </button>

                <button @click="isBlurred = !isBlurred" type="button" class="inline-flex items-center justify-center h-10 w-10 rounded-xl bg-gradient-to-br from-indigo-50 to-indigo-100 text-indigo-600 border border-indigo-200 hover:shadow-md transition-all active:scale-95">
                    <svg x-show="!isBlurred" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    <svg x-show="isBlurred" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                </button>
            </div>
            
            <div class="w-full sm:w-auto order-2 sm:order-1">
                <div class="relative group w-full sm:w-56">
                    <select id="branch-select" class="w-full appearance-none bg-gradient-to-br from-slate-50 to-slate-100 border border-slate-200 text-slate-800 text-sm rounded-lg px-4 py-3 ltr:pr-10 rtl:pl-10 focus:ring-2 focus:ring-indigo-500 transition-all cursor-pointer">
                        <option value="" disabled selected>{{ __('messages.select_branch') }}</option>
                        @foreach($branches ?? [] as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 ltr:right-3 rtl:left-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

        </div>
    </div>
</header>

        <main class="p-3 md:p-6 lg:p-10 flex-1 flex flex-col">
            <div class="max-w-7xl w-full mx-auto flex-1 flex flex-col gap-6">
                
                @if (isset($header))
                <div class="w-full flex justify-center items-center py-2">
                    <h2 class="text-xs md:text-sm font-black text-slate-400 uppercase tracking-[0.3em] select-none text-center">
                        {{ $header }}
                    </h2>
                </div>
                @endif

                <div class="bg-white rounded-[1.5rem] md:rounded-[2.5rem] border border-slate-200/60 shadow-sm p-4 md:p-8 flex-1 min-h-[calc(100vh-14rem)]">
                    {{ $slot }}
                </div>

            </div>
        </main>

        <footer class="px-10 py-6 text-center lg:text-start border-t border-slate-200/50 mt-auto">
             <p class="text-xs text-slate-400">© {{ date('Y') }} Smart System. {{ __('messages.all_rights_reserved') }}</p>
        </footer>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const Toast = Swal.mixin({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true,
            didOpen: (toast) => { toast.addEventListener('mouseenter', Swal.stopTimer); toast.addEventListener('mouseleave', Swal.resumeTimer); }
        });
        @if(session('success')) Toast.fire({ icon: 'success', title: "{{ session('success') }}" }); @endif
        @if(session('error')) Toast.fire({ icon: 'error', title: "{{ session('error') }}" }); @endif
        @if($errors->any()) Toast.fire({ icon: 'error', title: "{{ __('currency.check_inputs') ?? 'Error' }}" }); @endif
    </script>

    <script>
        const toggleSidebar = () => {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const isRtl = document.documentElement.dir === 'rtl';
            const hideClass = isRtl ? 'translate-x-full' : '-translate-x-full';
            const isOpen = !sidebar.classList.contains(hideClass);
            if (!isOpen) {
                sidebar.classList.remove(hideClass); sidebar.classList.add('translate-x-0'); overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.add('opacity-100'), 10); document.body.style.overflow = 'hidden';
            } else {
                sidebar.classList.add(hideClass); sidebar.classList.remove('translate-x-0'); overlay.classList.remove('opacity-100');
                setTimeout(() => overlay.classList.add('hidden'), 300); document.body.style.overflow = 'auto';
            }
        };
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) { document.body.style.overflow = 'auto'; document.getElementById('sidebar-overlay')?.classList.add('hidden'); }
        });
    </script>
</body>
</html>