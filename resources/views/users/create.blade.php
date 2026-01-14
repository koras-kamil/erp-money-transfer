<x-app-layout>
    <x-slot name="header">
        <span class="tracking-widest">{{ __('users.btn_create') }}</span>
    </x-slot>

    <div class="max-w-3xl mx-auto py-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-[2rem] border border-slate-200 p-8">
            
            <div class="mb-8">
                <h3 class="text-xl font-black text-slate-800">{{ __('users.edit_info') }}</h3>
                <p class="text-sm text-slate-400 mt-1">{{ __('users.modal_title') }}</p>
            </div>

            <form action="{{ route('users.store') }}" method="POST">
                @csrf

                <div class="space-y-6">
                    
                    {{-- 1. Name & Email --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Name --}}
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">
                                {{ __('users.column_user') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name') }}" required autofocus
                                   class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm focus:ring-indigo-500 focus:border-indigo-500 p-3 transition-all placeholder-slate-300"
                                   placeholder="John Doe">
                            @error('name') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">
                                {{ __('users.email') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm focus:ring-indigo-500 focus:border-indigo-500 p-3 transition-all placeholder-slate-300"
                                   placeholder="john@example.com">
                            @error('email') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <hr class="border-slate-100 border-dashed">

                    {{-- 2. Role & Branch --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Branch --}}
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">
                                {{ __('users.label_branch') }}
                            </label>
                            <div class="relative">
                                <select name="branch_id" class="w-full appearance-none rounded-xl border-slate-200 bg-slate-50 text-sm focus:ring-indigo-500 focus:border-indigo-500 p-3 transition-all cursor-pointer">
                                    <option value="">{{ __('users.no_branch') }}</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 ltr:right-0 rtl:left-0 flex items-center px-3 pointer-events-none text-slate-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                            @error('branch_id') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        {{-- Role --}}
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">
                                {{ __('users.label_role') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="role" required class="w-full appearance-none rounded-xl border-slate-200 bg-slate-50 text-sm focus:ring-indigo-500 focus:border-indigo-500 p-3 transition-all cursor-pointer">
                                    <option value="" disabled selected>{{ __('users.label_role') }}</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                            {{ ucwords(str_replace('-', ' ', $role->name)) }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 ltr:right-0 rtl:left-0 flex items-center px-3 pointer-events-none text-slate-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                            @error('role') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <hr class="border-slate-100 border-dashed">

                    {{-- 3. Password Section --}}
                    <div class="bg-indigo-50/50 p-5 rounded-2xl border border-indigo-100/50">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Password --}}
                            <div>
                                <label class="block text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-2">
                                    {{ __('users.new_password') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="password" name="password" required
                                       class="w-full rounded-xl border-slate-200 bg-white text-sm focus:ring-indigo-500 focus:border-indigo-500 p-3 transition-all"
                                       placeholder="••••••••">
                                @error('password') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                            </div>

                            {{-- Confirm Password --}}
                            <div>
                                <label class="block text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-2">
                                    {{ __('users.confirm_password') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="password" name="password_confirmation" required
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
                        {{ __('users.btn_create') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>