<x-app-layout>
    <x-slot name="header">
        <span class="tracking-widest">{{ __('users.edit_user') }}</span>
    </x-slot>

    <div class="max-w-3xl mx-auto py-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-[2rem] border border-slate-200 p-8">
            
            <div class="mb-8">
                <h3 class="text-xl font-black text-slate-800">{{ __('users.edit_info') }}</h3>
                <p class="text-sm text-slate-400 mt-1">{{ $user->name }} • {{ $user->email }}</p>
            </div>

            <form action="{{ route('users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    
                    {{-- 1. Name & Email --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Name --}}
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">
                                {{ __('users.name') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                   class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm focus:ring-indigo-500 focus:border-indigo-500 p-3 transition-all">
                            @error('name') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">
                                {{ __('users.email') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                   class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm focus:ring-indigo-500 focus:border-indigo-500 p-3 transition-all">
                            @error('email') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <hr class="border-slate-100 border-dashed my-6">

                    {{-- 2. Password Change Section --}}
                    <div class="bg-amber-50/50 p-5 rounded-2xl border border-amber-100/50">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <p class="text-[10px] font-bold text-amber-600 uppercase tracking-tight">
                                {{ __('users.leave_blank_password') }}
                            </p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">{{ __('users.new_password') }}</label>
                                <input type="password" name="password" 
                                       class="w-full rounded-xl border-slate-200 bg-white text-sm focus:ring-indigo-500 focus:border-indigo-500 p-3 transition-all placeholder-slate-300"
                                       placeholder="••••••••">
                                @error('password') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">{{ __('users.confirm_password') }}</label>
                                <input type="password" name="password_confirmation" 
                                       class="w-full rounded-xl border-slate-200 bg-white text-sm focus:ring-indigo-500 focus:border-indigo-500 p-3 transition-all"
                                       placeholder="••••••••">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="mt-10 flex gap-3 pt-6 border-t border-slate-100">
                    <a href="{{ route('users.index') }}" class="flex-1 px-4 py-3.5 bg-slate-100 text-slate-600 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-slate-200 transition-all text-center">
                        {{ __('users.btn_cancel') }}
                    </a>
                    
                    <button type="submit" class="flex-[2] px-8 py-3.5 bg-[#0f172a] text-white rounded-xl font-bold text-xs uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 hover:shadow-2xl hover:scale-[1.02] active:scale-95 transition-all">
                        {{ __('users.btn_update_user') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>