<div x-show="showExchangeModal" 
     style="display: none;" 
     class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm" 
     x-cloak>
    
    {{-- Modal Window --}}
    <div @click.outside="showExchangeModal = false" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         class="bg-white rounded-xl shadow-2xl w-full max-w-4xl overflow-hidden border border-slate-100 flex flex-col max-h-[85vh]">
        
        {{-- Header (With Money Icon) --}}
        <div class="bg-white px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <div class="flex items-center gap-3">
                {{-- Money Icon --}}
                <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl border border-emerald-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-slate-800 leading-none">
                        {{ __('messages.update_currency_rates') }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 font-medium">
                        {{ __('messages.base_currency_note') }}
                    </p>
                </div>
            </div>
            
            {{-- Close Button --}}
            <button @click="showExchangeModal = false" class="text-slate-400 hover:text-slate-600 hover:bg-slate-50 p-2 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        {{-- Form --}}
        <form action="{{ route('currency.update-rates') }}" method="POST" class="flex flex-col flex-1 overflow-hidden">
            @csrf
            
            <div class="overflow-y-auto custom-scrollbar flex-1 bg-white p-0">
                <table class="w-full text-sm text-left rtl:text-right text-slate-700">
                    {{-- Table Header --}}
                    <thead class="text-xs font-semibold text-slate-500 uppercase bg-slate-50/80 sticky top-0 z-10 border-b border-slate-200 backdrop-blur-sm">
                        <tr>
                            <th class="px-5 py-3 w-12 text-center">#</th>
                            <th class="px-5 py-3">{{ __('messages.currency_name') }}</th>
                            <th class="px-5 py-3 text-center">{{ __('messages.symbol') }}</th>
                            <th class="px-5 py-3 text-center w-64 bg-emerald-50/30 text-emerald-700 border-x border-emerald-50">
                                {{ __('messages.price_for_100') }}
                            </th>
                            <th class="px-5 py-3 text-center w-48 text-slate-400">
                                {{ __('messages.price_for_1') }}
                            </th>
                        </tr>
                    </thead>
                    
                    {{-- Table Body --}}
                    <tbody class="divide-y divide-slate-100">
                        @foreach(\App\Models\CurrencyConfig::where('is_active', true)->orderBy('id')->get() as $currency)
                        <tr class="hover:bg-slate-50 transition-colors group">
                            {{-- Index --}}
                            <td class="px-5 py-3 text-center text-slate-400 font-normal">
                                {{ $loop->iteration }}
                            </td>
                            
                            {{-- Name (Regular Weight) --}}
                            <td class="px-5 py-3 font-normal text-slate-700">
                                {{ $currency->currency_type }}
                            </td>
                            
                            {{-- Symbol (Mono font for code look) --}}
                            <td class="px-5 py-3 text-center">
                                <span class="font-mono text-xs px-2 py-0.5 bg-slate-100 text-slate-500 rounded border border-slate-200">
                                    {{ $currency->symbol }}
                                </span>
                            </td>
                            
                            {{-- Input (100$) - Highlighted Column --}}
                            <td class="px-4 py-2 bg-emerald-50/10 border-x border-slate-50">
                                <input type="text" 
                                       name="rates[{{ $currency->id }}]" 
                                       value="{{ number_format($currency->price_total, 0, '.', ',') }}"
                                       class="w-full px-3 py-1.5 text-center font-mono text-[15px] font-medium text-slate-700 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all shadow-sm group-hover:border-slate-400"
                                       oninput="calculateRate(this, 'single-{{ $currency->id }}')"
                                       autocomplete="off">
                            </td>

                            {{-- Calculated (1$) --}}
                            <td class="px-5 py-3 text-center">
                                <span class="font-mono font-normal text-slate-500" id="single-{{ $currency->id }}">
                                    {{ number_format($currency->price_single, 2) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Footer (Nicer Buttons) --}}
            <div class="px-6 py-4 bg-white border-t border-slate-100 flex justify-end gap-3 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.02)]">
                
                {{-- Cancel Button --}}
                <button type="button" @click="showExchangeModal = false" 
                        class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-slate-800 hover:border-slate-300 transition-all shadow-sm">
                    {{ __('messages.cancel') }}
                </button>

                {{-- Save Button --}}
                <button type="submit" 
                        class="px-6 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-xl hover:from-emerald-600 hover:to-emerald-700 shadow-lg shadow-emerald-100 active:scale-95 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ __('messages.save_changes') }}
                </button>
            </div>
        </form>
    </div>

    {{-- JS Logic --}}
    <script>
        function calculateRate(input, targetId) {
            let rawValue = input.value.replace(/,/g, '');
            if (!isNaN(rawValue) && rawValue > 0) {
                let singlePrice = rawValue / 100;
                document.getElementById(targetId).innerText = singlePrice.toLocaleString('en-US', {
                    minimumFractionDigits: 2, maximumFractionDigits: 2
                });
            } else {
                document.getElementById(targetId).innerText = '-';
            }
        }
    </script>
</div>