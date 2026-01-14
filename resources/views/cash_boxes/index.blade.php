<x-app-layout>
    <x-slot name="header">
        {{ __('cash_box.title') }}
    </x-slot>

    <style>
        @media print {
            @page { size: A4; margin: 10mm; }
            aside, nav, header, .print\:hidden, button, a[href*="export"], input, textarea, select { display: none !important; }
            body, .min-h-screen { background: white !important; color: black !important; font-family: 'serif'; }
            table { width: 100% !important; border-collapse: collapse !important; font-size: 10pt !important; margin-top: 20px !important; }
            th, td { border: 1px solid #333 !important; padding: 6px 8px !important; }
            th { background-color: #f3f4f6 !important; font-weight: bold !important; text-transform: uppercase !important; }
            thead { display: table-header-group; }
            tr { page-break-inside: avoid; }
            th:last-child, td:last-child { display: none !important; }
            #print-header { display: block !important; text-align: center; border-bottom: 2px solid #000; margin-bottom: 20px; padding-bottom: 15px; }
        }
        #print-header { display: none; }
    </style>

    <div x-data="{ 
        openModal: false,
        cols: { id: true, name: true, type: true, currency: true, balance: true, user: true, branch: true, active: true, actions: true },
        toggleAll(value) { for (let key in this.cols) { this.cols[key] = value; } }
    }">

        <div id="print-header">
            <h1 class="uppercase font-bold text-2xl">{{ config('app.name') }}</h1>
            <h2 class="text-lg">{{ __('cash_box.title') }}</h2>
            <p class="text-[10px] text-gray-500 mt-1">
                {{ date('Y-m-d H:i') }} | {{ Auth::user()->name }}
            </p>
        </div>

        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 print:hidden px-2">
            <div class="flex items-center gap-4">
                <h3 class="text-lg font-bold text-slate-700">{{ __('cash_box.title') }}</h3>
                <a href="{{ route('cash-boxes.trash') }}" class="text-xs font-bold text-red-500 hover:text-red-700 flex items-center gap-1 bg-red-50 px-3 py-1.5 rounded-full border border-red-100 transition">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    {{ __('cash_box.trash') }}
                </a>
            </div>
            
            <div class="flex flex-wrap items-center gap-2">
                <div x-data="{ openDropdown: false }" class="relative">
                    <button @click="openDropdown = !openDropdown" @click.away="openDropdown = false" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-600 px-3 py-2 rounded-lg text-xs font-bold hover:bg-slate-50 shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>{{ __('cash_box.manage_view') }}</span>
                    </button>
                    <div x-show="openDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 z-50 p-2" x-cloak>
                        <div class="flex gap-2 mb-2 pb-2 border-b border-slate-100">
                            <button @click="toggleAll(true)" class="flex-1 text-[10px] bg-indigo-50 text-indigo-600 py-1 rounded hover:bg-indigo-100 font-bold">ALL</button>
                            <button @click="toggleAll(false)" class="flex-1 text-[10px] bg-slate-50 text-slate-600 py-1 rounded hover:bg-slate-100 font-bold">NONE</button>
                        </div>
                        <div class="space-y-1 max-h-60 overflow-y-auto">
                            @foreach(['id'=>'#', 'name'=>'Name', 'type'=>'Type', 'currency'=>'Currency', 'balance'=>'Balance', 'user'=>'User', 'branch'=>'Branch', 'active'=>'Active', 'actions'=>'Actions'] as $key => $label)
                            <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 rounded cursor-pointer">
                                <input type="checkbox" x-model="cols.{{ $key }}" class="rounded text-indigo-600 w-4 h-4 border-slate-300">
                                <span class="text-xs text-slate-700 font-medium">{{ __('cash_box.' . $key) }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <button onclick="window.print()" class="flex items-center gap-2 bg-slate-700 text-white px-3 py-2 rounded-lg text-xs font-bold hover:bg-slate-800 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>{{ __('cash_box.print') }}</span>
                </button>

                <button @click="openModal = true" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-lg shadow-blue-500/30 transition-all active:scale-95 text-xs font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>{{ __('cash_box.new_box') }}</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm mx-2">
            <table class="w-full text-sm text-left rtl:text-right text-slate-500">
                <thead class="text-xs text-slate-700 uppercase bg-blue-100/50 border-b border-blue-200">
                    <tr>
                        <th x-show="cols.id" class="px-4 py-3">#</th>
                        <th x-show="cols.name" class="px-4 py-3">{{ __('cash_box.name') }}</th>
                        <th x-show="cols.type" class="px-4 py-3">{{ __('cash_box.type') }}</th>
                        <th x-show="cols.currency" class="px-4 py-3">{{ __('cash_box.currency') }}</th>
                        <th x-show="cols.balance" class="px-4 py-3">{{ __('cash_box.balance') }}</th>
                        <th x-show="cols.user" class="px-4 py-3">{{ __('cash_box.user') }}</th>
                        <th x-show="cols.branch" class="px-4 py-3">{{ __('cash_box.branch') }}</th>
                        <th x-show="cols.active" class="px-4 py-3 text-center">{{ __('cash_box.active') }}</th>
                        <th x-show="cols.actions" class="px-4 py-3 text-center print:hidden">{{ __('cash_box.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($cashBoxes as $box)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td x-show="cols.id" class="px-4 py-3 font-medium text-slate-400">{{ $loop->iteration }}</td>
                        <td x-show="cols.name" class="px-4 py-3 font-bold text-slate-800">{{ $box->name }}</td>
                        <td x-show="cols.type" class="px-4 py-3">{{ $box->type ?? '-' }}</td>
                        <td x-show="cols.currency" class="px-4 py-3">
                            <span class="bg-indigo-50 text-indigo-600 py-1 px-2 rounded text-[10px] font-black uppercase">
                                {{ $box->currency->currency_type ?? 'USD' }}
                            </span>
                        </td>
                        <td x-show="cols.balance" class="px-4 py-3 font-bold text-emerald-600" :class="isBlurred ? 'blur-md select-none' : ''">
                            {{ number_format($box->balance, 2) }}
                        </td>
                        <td x-show="cols.user" class="px-4 py-3 text-[10px] uppercase font-bold">{{ $box->user->name ?? 'System' }}</td>
                        <td x-show="cols.branch" class="px-4 py-3 text-[10px] uppercase font-bold text-slate-400">{{ $box->branch->name ?? 'Main' }}</td>
                        <td x-show="cols.active" class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded-full text-[10px] font-black {{ $box->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                {{ $box->is_active ? 'ACTIVE' : 'INACTIVE' }}
                            </span>
                        </td>
                        <td x-show="cols.actions" class="px-4 py-3 flex justify-center gap-2 print:hidden">
                            <a href="{{ route('cash-boxes.edit', $box->id) }}" class="p-1.5 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                            <form id="delete-form-{{ $box->id }}" action="{{ route('cash-boxes.destroy', $box->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="button" onclick="window.confirmAction('delete-form-{{ $box->id }}', '{{ __('cash_box.delete_text') }}')" class="p-1.5 bg-red-50 text-red-600 rounded hover:bg-red-100 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="px-4 py-8 text-center text-slate-400 italic font-medium">{{ __('messages.no_data') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div x-show="openModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm print:hidden" x-cloak>
            <div @click.away="openModal = false" class="bg-white rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden transform transition-all border border-slate-100">
                <div class="px-8 py-6 flex justify-between items-center border-b border-slate-50 bg-slate-50/50">
                    <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight">{{ __('cash_box.new_box') }}</h3>
                    <button @click="openModal = false" class="text-slate-400 hover:text-red-500 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <form action="{{ route('cash-boxes.store') }}" method="POST" class="p-8 space-y-5">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">{{ __('cash_box.name') }}</label>
                            <input type="text" name="name" required class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all font-bold">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">{{ __('cash_box.type') }}</label>
                            <input type="text" name="type" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all font-bold">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">{{ __('cash_box.currency') }}</label>
                            <select name="currency_id" required class="w-full rounded-xl border-slate-200 focus:border-indigo-500 font-bold">
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->id }}">{{ strtoupper($currency->currency_type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">{{ __('cash_box.branch') }}</label>
                            <select name="branch_id" required class="w-full rounded-xl border-slate-200 focus:border-indigo-500 font-bold">
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">{{ __('cash_box.balance') }}</label>
                        <input type="number" step="0.01" name="balance" required class="w-full rounded-xl border-slate-200 focus:border-indigo-500 text-lg font-black text-emerald-600">
                    </div>
                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" @click="openModal = false" class="px-6 py-3 bg-white border border-slate-200 rounded-xl text-xs font-black uppercase text-slate-400 hover:bg-slate-50 transition tracking-widest">{{ __('cash_box.cancel') }}</button>
                        <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/30">
                            {{ __('cash_box.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>