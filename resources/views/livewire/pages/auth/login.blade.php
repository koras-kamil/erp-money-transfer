<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();
        $this->form->authenticate();
        Session::regenerate();
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="font-sans antialiased selection:bg-indigo-100">
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-5">
        <div class="space-y-2">
            <label for="email" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest ltr:ml-1 rtl:mr-1">
                {{ __('messages.email') }}
            </label>
            <div class="relative group">
                <input wire:model="form.email" id="email" type="email" required autofocus
                    class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-700 text-sm focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all duration-200 outline-none"
                    placeholder="name@company.com">
            </div>
            <x-input-error :messages="$errors->get('form.email')" class="mt-1 text-xs font-bold text-red-500" />
        </div>

        <div class="space-y-2">
            <div class="flex items-center justify-between px-1">
                <label for="password" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest">
                    {{ __('messages.password') }}
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" wire:navigate 
                       class="text-[10px] font-bold text-indigo-600 hover:text-indigo-700 transition-colors uppercase tracking-tighter">
                        {{ __('messages.forgot_password') }}
                    </a>
                @endif
            </div>
            <div class="relative">
                <input wire:model="form.password" id="password" type="password" required
                    class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-700 text-sm focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all duration-200 outline-none"
                    placeholder="••••••••">
            </div>
            <x-input-error :messages="$errors->get('form.password')" class="mt-1 text-xs font-bold text-red-500" />
        </div>

        <div class="flex items-center px-1">
            <label for="remember" class="relative flex items-center cursor-pointer group">
                <input wire:model="form.remember" id="remember" type="checkbox" 
                    class="w-5 h-5 rounded-lg border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500 transition-all cursor-pointer">
                <span class="ltr:ml-3 rtl:mr-3 text-xs font-bold text-slate-500 group-hover:text-slate-700 transition-colors italic">
                    {{ __('messages.remember_me') }}
                </span>
            </label>
        </div>

        <div class="pt-2">
            <button type="submit" wire:loading.attr="disabled"
                class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-600 to-violet-600 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-xl shadow-indigo-200 hover:shadow-indigo-300 hover:translate-y-[-1px] active:translate-y-[1px] transition-all duration-200 group">
                
                <svg wire:loading class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>

                <span wire:loading.remove>{{ __('messages.login_button') }}</span>
                <span wire:loading>{{ __('messages.preview') }}...</span>

                <svg wire:loading.remove class="w-4 h-4 group-hover:translate-x-1 rtl:group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </div>
    </form>
</div>