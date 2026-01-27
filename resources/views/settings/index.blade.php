<x-app-layout>
    <div class="max-w-2xl mx-auto">
        {{-- Header --}}
        <div class="flex items-center gap-3 mb-6">
            <div class="p-3 bg-indigo-600 text-white rounded-xl shadow-lg shadow-indigo-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.572 1.065c-1.543.94-3 .888-8 .888s-.888-.888-.888-.888z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <h2 class="text-2xl font-black text-slate-800">{{ __('settings.title') }}</h2>
                <p class="text-sm text-slate-500 font-medium">{{ __('settings.subtitle') }}</p>
            </div>
        </div>

        {{-- Form Container --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
            <form action="{{ route('settings.update') }}" method="POST">
                @csrf
                
                <div class="mb-6">
                    <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">
                        {{ __('settings.base_currency') }}
                    </label>
                    
                    {{-- Note Box --}}
                    <div class="text-xs text-slate-500 mb-4 bg-blue-50 p-3 rounded-lg border border-blue-100 leading-relaxed">
                        <strong class="text-blue-700 block mb-1">{{ __('settings.note_label') }}</strong> 
                        {{ __('settings.note_text') }}
                    </div>
                    
                    {{-- Select Input --}}
                    <div class="relative">
                        <select name="base_currency_id" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none appearance-none cursor-pointer">
                            @foreach($currencies as $currency)
                                <option value="{{ $currency->id }}" {{ $currentBaseId == $currency->id ? 'selected' : '' }}>
                                    {{ $currency->currency_type }} ({{ $currency->symbol }})
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 ltr:right-0 rtl:left-0 flex items-center px-4 pointer-events-none text-slate-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex justify-end pt-6 border-t border-slate-100">
                    <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-200 transition active:scale-95 flex items-center gap-2">
                        <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ __('settings.save_button') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>