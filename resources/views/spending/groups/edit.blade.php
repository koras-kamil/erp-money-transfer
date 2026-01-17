<x-app-layout>
    <x-slot name="header">
        <span class="tracking-widest">{{ __('spending.edit_group') }}</span>
    </x-slot>

    <div class="max-w-3xl mx-auto py-10 sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-[2rem] border border-slate-200 p-8">
            
            <form action="{{ route('group-spending.update', $groupSpending->id) }}" method="POST" class="space-y-6">
                @csrf @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">{{ __('spending.name') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $groupSpending->name) }}" required class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm focus:ring-indigo-500 focus:border-indigo-500 p-3">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">{{ __('spending.code') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="code" value="{{ old('code', $groupSpending->code) }}" required class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm focus:ring-indigo-500 focus:border-indigo-500 p-3">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">{{ __('spending.accountant_code') }}</label>
                        <input type="text" name="accountant_code" value="{{ old('accountant_code', $groupSpending->accountant_code) }}" class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm focus:ring-indigo-500 focus:border-indigo-500 p-3">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">{{ __('spending.branch') }} <span class="text-red-500">*</span></label>
                        <select name="branch_id" required class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm focus:ring-indigo-500 focus:border-indigo-500 p-3">
                            <option value="">{{ __('spending.select_branch') }}</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id', $groupSpending->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr class="border-slate-100">

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('group-spending.index') }}" class="flex-1 px-4 py-3.5 bg-slate-100 text-slate-600 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-slate-200 transition-all text-center">
                        {{ __('spending.cancel') }}
                    </a>
                    <button type="submit" class="flex-[2] px-8 py-3.5 bg-[#0f172a] text-white rounded-xl font-bold text-xs uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 active:scale-95 transition-all">
                        {{ __('spending.update') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>