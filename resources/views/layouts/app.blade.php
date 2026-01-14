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
        /* Fixes the 'Flicker' issue */
        [v-cloak] { display: none; }
        [x-cloak] { display: none !important; }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

        /* Privacy Blur Effect */
        .content-blur { 
            filter: blur(12px) grayscale(30%); 
            pointer-events: none; 
            user-select: none; 
            transition: filter 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 0.7;
        }

        /* Better dropdown handling */
        .allow-dropdown { overflow: visible !important; }
    </style>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-[#f8fafc] font-sans antialiased text-slate-900 h-full overflow-x-hidden" 
      x-data="{ isBlurred: false }">

    {{-- 1. NOTIFICATION COMPONENT (Handles Success/Error Animations) --}}
    <x-notification />

    {{-- 2. PRIVACY TOGGLE BUTTON (Floating) --}}
    <div class="fixed bottom-8 ltr:right-8 rtl:left-8 z-[100] print:hidden">
        <button @click="isBlurred = !isBlurred" 
                type="button" 
                :class="isBlurred ? 'bg-slate-900 text-white ring-4 ring-slate-200' : 'bg-white text-indigo-600 shadow-2xl border border-slate-100'"
                class="flex items-center gap-3 px-6 py-3.5 rounded-2xl font-black text-[11px] uppercase tracking-[0.1em] transition-all hover:scale-105 active:scale-95 group">
            
            <div :class="isBlurred ? 'text-emerald-400' : 'text-indigo-600'">
                <svg x-show="!isBlurred" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                <svg x-show="isBlurred" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
            </div>
            
            <span>{{ app()->getLocale() == 'ku' ? 'پاراستنی نهێنی' : 'Privacy Mode' }}</span>
        </button>
    </div>

    {{-- 3. RIGHT MENU (Sidebar) --}}
    <x-right-menu />

    {{-- 4. MAIN CONTENT WRAPPER --}}
    <div class="transition-all duration-300 min-h-screen flex flex-col ltr:md:ml-56 rtl:md:mr-56">
        
        {{-- TOP HEADER --}}
        <header class="bg-white/80 backdrop-blur-xl border-b border-slate-200/60 sticky top-0 z-30 px-2 md:px-6 py-3">
            <div class="flex flex-col md:flex-row items-center justify-between gap-y-3">
                {{-- Mobile Sidebar Toggle --}}
                <button onclick="toggleSidebar()" class="md:hidden self-start p-2 rounded-xl bg-slate-100 text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                </button>

                {{-- Header Content --}}
                <div class="w-full flex flex-col sm:flex-row items-center justify-between gap-4">
                    {{-- Branch Selector --}}
                    <div class="w-full sm:w-auto order-2 sm:order-1">
                        <div class="relative group w-full sm:w-64">
                            <select id="branch-select" class="w-full appearance-none bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold rounded-xl px-4 py-3 ltr:pr-10 rtl:pl-10 focus:ring-4 focus:ring-indigo-500/10 transition-all cursor-pointer">
                                <option value="" disabled selected>{{ __('messages.select_branch') }}</option>
                                @foreach($branches ?? [] as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Empty Spacer (Finance button was here) --}}
                    <div class="flex flex-row items-center justify-end gap-3 w-full sm:w-auto order-1 sm:order-2">
                    </div>
                </div>
            </div>
        </header>

        {{-- MAIN CONTENT AREA --}}
        <main :class="isBlurred ? 'content-blur' : ''" class="p-4 md:p-8 lg:p-12 flex-1 flex flex-col">
            <div class="max-w-7xl w-full mx-auto flex-1 flex flex-col gap-8">
                
                {{-- Page Header (if exists) --}}
                @if (isset($header))
                <div class="w-full flex justify-center items-center">
                    <h2 class="text-[11px] font-black text-slate-400 uppercase tracking-[0.4em] text-center bg-slate-100/50 px-6 py-2 rounded-full">{{ $header }}</h2>
                </div>
                @endif
                
                {{-- Page Content Slot --}}
                <div class="bg-white rounded-[2rem] md:rounded-[3rem] border border-slate-200/60 shadow-sm p-4 md:p-10 flex-1 min-h-[calc(100vh-16rem)]">
                    {{ $slot }}
                </div>
            </div>
        </main>

        {{-- FOOTER --}}
        <footer class="px-10 py-8 text-center border-t border-slate-100 mt-auto bg-white/50">
             <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">© {{ date('Y') }} Smart System • {{ __('messages.all_rights_reserved') }}</p>
        </footer>
    </div>

    {{-- HIDDEN LOGOUT FORM --}}
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>

    {{-- SCRIPTS --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Sidebar Toggle for Mobile
        const toggleSidebar = () => {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const isRtl = document.documentElement.dir === 'rtl';
            const hideClass = isRtl ? 'translate-x-full' : '-translate-x-full';
            if (sidebar.classList.contains(hideClass)) {
                sidebar.classList.remove(hideClass); sidebar.classList.add('translate-x-0'); overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.add('opacity-100'), 10); document.body.style.overflow = 'hidden';
            } else {
                sidebar.classList.add(hideClass); sidebar.classList.remove('translate-x-0'); overlay.classList.remove('opacity-100');
                setTimeout(() => overlay.classList.add('hidden'), 300); document.body.style.overflow = 'auto';
            }
        };

        // Global SweetAlert Confirmation
        window.confirmAction = function(formId, message = null) {
            Swal.fire({
                title: "{{ app()->getLocale() == 'ku' ? 'دڵنیایت؟' : 'Are you sure?' }}",
                text: message || "{{ app()->getLocale() == 'ku' ? 'ئەم کارە ناگەڕێتەوە و دەسڕێتەوە!' : 'This action is permanent!' }}",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#f1f5f9',
                confirmButtonText: "{{ app()->getLocale() == 'ku' ? 'بەڵێ، ئەنجامی بدە' : 'Yes, proceed!' }}",
                cancelButtonText: "{{ app()->getLocale() == 'ku' ? 'پاشگەزبوونەوە' : 'Cancel' }}",
                customClass: {
                    popup: 'rounded-[1.5rem] border-none shadow-2xl',
                    confirmButton: 'rounded-xl font-black uppercase tracking-widest text-[10px] px-6 py-3',
                    cancelButton: 'rounded-xl font-black uppercase tracking-widest text-[10px] px-6 py-3 text-slate-600'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
        }
    </script>
</body>
</html>