<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}"
      class="h-full bg-[#f8fafc]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'Smart System') }}</title>

    {{-- Pre-connect CDNs --}}
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://unpkg.com">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>

    <style>
        [v-cloak], [x-cloak] { display: none !important; }
        
        /* 1. CSS-Only Instant Preloader */
        #preloader {
            position: fixed;
            inset: 0;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.5s ease-out, visibility 0.5s;
        }

        .loader-circle {
            width: 50px;
            height: 50px;
            border: 4px solid #e2e8f0;
            border-top: 4px solid #4f46e5;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* Existing Styles */
        .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
        .content-blur { filter: blur(12px) grayscale(30%); pointer-events: none; opacity: 0.7; transition: all 0.3s ease; }
        div.swal2-container { z-index: 9999 !important; }
    </style>
</head>

<body class="font-sans antialiased text-slate-900 h-screen flex bg-[#f8fafc] overflow-hidden"
      x-data="layoutData()"
      x-init="init()">

    <div id="preloader" x-ref="preloader">
        <div class="loader-circle"></div>
        <p class="mt-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] animate-pulse">
            {{ __('messages.loading') ?? 'SYSTEM LOADING' }}
        </p>
    </div>

    {{-- SECONDARY AJAX LOADING SPINNER (For background fetches) --}}
    <div x-show="isLoading" x-cloak x-transition.opacity class="fixed inset-0 z-[200] flex items-center justify-center bg-white/60 backdrop-blur-[2px]">
        <svg class="animate-spin h-10 w-10 text-indigo-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>

    {{-- FLOATING PRIVACY BUTTON --}}
    <div class="fixed bottom-6 z-[90] print:hidden ltr:right-6 rtl:left-6">
        <button @click="toggleBlur()" class="w-12 h-12 flex items-center justify-center rounded-full bg-white text-slate-500 hover:text-indigo-600 shadow-xl border border-slate-100 transition-all hover:scale-110">
            <svg x-show="!isBlurred" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
            <svg x-show="isBlurred" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
        </button>
    </div>

    <x-notification />
    @include('components.right-menu')

    <div class="flex-1 flex flex-col h-full relative min-w-0 overflow-hidden bg-[#f8fafc] transition-all duration-300">
        <x-command-bar />
        <main class="flex-1 overflow-y-auto p-4 md:p-8 custom-scrollbar">
            <div class="max-w-7xl w-full mx-auto flex flex-col gap-8 pb-10">
                @if (isset($header))
                <div class="w-full flex justify-center items-center">
                    <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] text-center bg-white/50 px-6 py-2 rounded-full border border-slate-200/50 select-none">{{ $header }}</h2>
                </div>
                @endif
                <div :class="isBlurred ? 'content-blur' : ''" class="transition-all duration-300 bg-white rounded-[2rem] md:rounded-[2.5rem] border border-slate-200/60 shadow-sm p-4 md:p-10 min-h-[500px]">
                    {{ $slot }}
                </div>
            </div>
            <footer class="px-10 py-8 text-center mt-auto"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">© {{ date('Y') }} Smart System</p></footer>
        </main>
    </div>

    @include('partials.exchange-modal')

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('layoutData', () => ({
                isLoading: false,
                isBlurred: false,
                isCollapsed: Alpine.$persist(false).as('sidebar-collapsed'),

                init() {
                    // 2. Hide Preloader after Alpine and components render
                    window.addEventListener('load', () => {
                        this.$refs.preloader.style.opacity = '0';
                        setTimeout(() => {
                            this.$refs.preloader.style.display = 'none';
                        }, 500);
                    });
                },
                
                toggleBlur() { this.isBlurred = !this.isBlurred; }
            }));
        });

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
                heightAuto: false, 
                customClass: {
                    popup: 'rounded-[1.5rem] border-none shadow-2xl font-sans',
                    confirmButton: 'rounded-xl font-bold uppercase tracking-widest text-[11px] px-6 py-3',
                    cancelButton: 'rounded-xl font-bold uppercase tracking-widest text-[11px] px-6 py-3 text-slate-600 hover:bg-slate-100'
                }
            }).then((result) => { 
                if (result.isConfirmed) {
                    const form = document.getElementById(formId);
                    if(form) form.submit();
                }
            });
        }
    </script>
</body>
</html>