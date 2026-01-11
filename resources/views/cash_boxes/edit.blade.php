<x-app-layout>
    <x-slot name="header">
        {{ __('cash_box.edit_box') }}
    </x-slot>

    <div class="max-w-4xl mx-auto">
        
        <div class="mb-6">
            <a href="{{ route('cash-boxes.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-indigo-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>{{ __('cash_box.back') }}</span>
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
            <h2 class="text-xl font-bold text-slate-800 mb-6">{{ __('cash_box.edit_box') }}: <span class="text-indigo-600">{{ $cashBox->name }}</span></h2>

            <form action="{{ route('cash-boxes.update', $cashBox->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT') <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('cash_box.name') }}</label>
                        <input type="text" name="name" value="{{ $cashBox->name }}" required class="w-full rounded-lg border-slate-200 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('cash_box.type') }}</label>
                        <input type="text" name="type" value="{{ $cashBox->type }}" class="w-full rounded-lg border-slate-200 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('cash_box.currency') }}</label>
                        <select name="currency_id" class="w-full rounded-lg border-slate-200 focus:ring-indigo-500">
                            @foreach($currencies as $currency)
                                <option value="{{ $currency->id }}" {{ $cashBox->currency_id == $currency->id ? 'selected' : '' }}>
                                    {{ strtoupper($currency->currency_type) }} ({{ $currency->symbol }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('cash_box.branch') }}</label>
                        <select name="branch_id" class="w-full rounded-lg border-slate-200 focus:ring-indigo-500">
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ $cashBox->branch_id == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('cash_box.balance') }}</label>
                    <input type="number" step="0.01" name="balance" value="{{ $cashBox->balance }}" required class="w-full rounded-lg border-slate-200 font-bold text-slate-800">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('cash_box.desc') }}</label>
                    <textarea name="description" rows="3" class="w-full rounded-lg border-slate-200">{{ $cashBox->description }}</textarea>
                </div>

                <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-lg border border-slate-100">
                    <input type="checkbox" name="is_active" id="active_check" value="1" {{ $cashBox->is_active ? 'checked' : '' }} class="rounded text-indigo-600 focus:ring-indigo-500 w-5 h-5 cursor-pointer">
                    <label for="active_check" class="text-sm font-bold text-slate-700 cursor-pointer select-none">{{ __('cash_box.active') }}</label>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl shadow-lg shadow-indigo-500/30 transition-all active:scale-95 font-bold flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        {{ __('cash_box.update') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>