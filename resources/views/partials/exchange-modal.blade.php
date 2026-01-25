<div x-show="showExchangeModal" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" x-cloak>
    <div @click.outside="showExchangeModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden border border-slate-100">
        <div class="bg-gradient-to-r from-emerald-50 to-white px-6 py-4 border-b border-emerald-50 flex justify-between items-center">
            <h3 class="font-bold text-slate-800">{{ __('messages.finance') }}</h3>
            <button @click="showExchangeModal = false" class="text-slate-400 hover:text-red-500"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
        <form action="{{ route('currency.update-rate') }}" method="POST" class="p-6">
            @csrf
            <div class="mb-6"><label class="block text-sm font-bold text-slate-700 mb-2">{{ __('messages.update_exchange_rate') }}</label><input type="text" name="exchange_rate" x-model="exchangeRate" class="w-full pl-4 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl font-mono text-2xl font-bold text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:bg-white text-right"></div>
            <div class="grid grid-cols-2 gap-3"><button type="button" @click="showExchangeModal = false" class="py-3 text-slate-600 font-bold hover:bg-slate-100 rounded-xl">{{ __('messages.cancel') }}</button><button type="submit" class="py-3 bg-emerald-500 text-white font-bold rounded-xl shadow-lg hover:bg-emerald-600">{{ __('messages.save') }}</button></div>
        </form>
    </div>
</div>