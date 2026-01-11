<x-app-layout>
    <x-slot name="header">
        {{ __('cash_box.title') }}
    </x-slot>

    <style>
        @media print {
            /* 1. Page Setup for A4 */
            @page {
                size: A4;
                margin: 10mm;
            }

            /* 2. Hide Interface Elements */
            aside, nav, header, 
            .min-h-screen > div:first-child, 
            .print\:hidden,
            button, 
            a[href*="export"], /* Optional: Hide export button if not marked print:hidden */
            input, textarea, select /* Hide forms if they appear in print */
            {
                display: none !important;
            }

            /* 3. Reset Container Layout */
            body, .min-h-screen, .bg-gray-100 {
                background: white !important;
                color: black !important;
                margin: 0 !important;
                padding: 0 !important;
                font-family: 'Times New Roman', serif; /* Professional Serif font for print */
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            main {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                max-width: none !important;
                box-shadow: none !important;
            }

            /* 4. Professional Table Styling */
            .overflow-x-auto {
                overflow: visible !important;
                border: none !important;
                box-shadow: none !important;
            }

            table {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 10pt !important; /* Optimal size for A4 width */
                margin-top: 20px !important;
            }

            th, td {
                border: 1px solid #333 !important;
                padding: 6px 8px !important;
                text-align: left;
                vertical-align: middle;
            }

            th {
                background-color: #f3f4f6 !important; /* Light gray background for headers */
                color: black !important;
                font-weight: bold !important;
                text-transform: uppercase !important;
                font-size: 9pt !important;
            }

            /* 5. Handle Page Breaks */
            thead {
                display: table-header-group; /* Repeats header on every page */
            }
            
            tr {
                page-break-inside: avoid; /* Prevents cutting a row in half */
            }

            /* 6. Hide Actions Column */
            th:last-child, td:last-child {
                display: none !important;
            }

            /* 7. Print Header Styling */
            #print-header {
                display: block !important;
                text-align: center;
                margin-bottom: 20px;
                padding-bottom: 15px;
                border-bottom: 2px solid #000;
            }
            
            #print-header h1 {
                font-size: 24px !important;
                margin: 0;
                font-weight: bold;
            }
            
            #print-header h2 {
                font-size: 16px !important;
                margin: 5px 0 0 0;
                color: #555;
            }
        }

        /* Hide Print Header on Screen */
        #print-header {
            display: none;
        }
    </style>

    <div x-data="{ 
        openModal: false,
        // Column Visibility State
        cols: {
            id: true,
            name: true,
            type: true,
            currency: true,
            balance: true,
            user: true,
            branch: true,
            active: true,
            actions: true
        },
        // Toggle All Helper
        toggleAll(value) {
            for (let key in this.cols) {
                this.cols[key] = value;
            }
        },
        // Print Helper
        printTable() {
            window.print();
        }
    }">

        <div id="print-header">
            <h1 class="uppercase">{{ config('app.name') }}</h1>
            <h2>{{ __('cash_box.title') }}</h2>
            <p style="font-size: 10px; color: #666; margin-top: 5px;">
                {{ __('Date') }}: {{ date('Y-m-d H:i') }} | {{ __('User') }}: {{ Auth::user()->name ?? 'System' }}
            </p>
        </div>

        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 print:hidden">
            
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
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    <div x-show="openDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 z-50 p-2" x-cloak>
                        <div class="flex gap-2 mb-2 pb-2 border-b border-slate-100">
                            <button @click="toggleAll(true)" class="flex-1 text-[10px] bg-indigo-50 text-indigo-600 py-1 rounded hover:bg-indigo-100 font-bold">{{ __('cash_box.show_all') }}</button>
                            <button @click="toggleAll(false)" class="flex-1 text-[10px] bg-slate-50 text-slate-600 py-1 rounded hover:bg-slate-100 font-bold">{{ __('cash_box.hide_all') }}</button>
                        </div>
                        
                        <div class="space-y-1 max-h-60 overflow-y-auto">
                            @foreach(['id'=>'#', 'name'=>'Name', 'type'=>'Type', 'currency'=>'Currency', 'balance'=>'Balance', 'user'=>'User', 'branch'=>'Branch', 'active'=>'Active', 'actions'=>'Actions'] as $key => $label)
                            <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 rounded cursor-pointer">
                                <input type="checkbox" x-model="cols.{{ $key }}" class="rounded text-indigo-600 focus:ring-indigo-500 w-4 h-4 border-slate-300">
                                <span class="text-xs text-slate-700 font-medium">{{ __('cash_box.' . $key) }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <button @click="printTable()" class="flex items-center gap-2 bg-slate-700 text-white px-3 py-2 rounded-lg text-xs font-bold hover:bg-slate-800 shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>{{ __('cash_box.print') }}</span>
                </button>

                <a href="{{ route('cash-boxes.export') }}" class="flex items-center gap-2 bg-emerald-600 text-white px-3 py-2 rounded-lg text-xs font-bold hover:bg-emerald-700 shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>{{ __('cash_box.excel') }}</span>
                </a>

                <button @click="openModal = true" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-lg shadow-blue-500/30 transition-all active:scale-95 text-xs font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>{{ __('cash_box.new_box') }}</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 print:border-none print:shadow-none">
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
                <tbody class="bg-white divide-y divide-slate-100">
                    @forelse($cashBoxes as $box)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td x-show="cols.id" class="px-4 py-3 font-medium">{{ $loop->iteration }}</td>
                        
                        <td x-show="cols.name" class="px-4 py-3 font-bold text-slate-800">
                            <a href="#" class="hover:text-indigo-600 hover:underline transition-colors">{{ $box->name }}</a>
                        </td>

                        <td x-show="cols.type" class="px-4 py-3">{{ $box->type ?? '-' }}</td>
                        
                        <td x-show="cols.currency" class="px-4 py-3">
                            <span class="bg-indigo-50 text-indigo-600 py-1 px-2 rounded text-xs font-bold">
                                {{ strtoupper($box->currency->currency_type ?? 'USD') }}
                            </span>
                        </td>
                        
                        <td x-show="cols.balance" class="px-4 py-3 font-bold text-emerald-600" :class="isBlurred ? 'blur-md select-none' : ''">
                            {{ number_format($box->balance, 2) }}
                        </td>

                        <td x-show="cols.user" class="px-4 py-3 text-xs">{{ $box->user->name ?? 'System' }}</td>
                        <td x-show="cols.branch" class="px-4 py-3 text-xs">{{ $box->branch->name ?? 'Main' }}</td>
                        
                        <td x-show="cols.active" class="px-4 py-3 text-center">
                            @if($box->is_active)
                                <span class="bg-emerald-100 text-emerald-700 py-0.5 px-2 rounded text-[10px] font-bold">✔</span>
                            @else
                                <span class="bg-red-100 text-red-700 py-0.5 px-2 rounded text-[10px] font-bold">✘</span>
                            @endif
                        </td>

                        <td x-show="cols.actions" class="px-4 py-3 flex justify-center gap-2 print:hidden">
                            <a href="{{ route('cash-boxes.edit', $box->id) }}" class="p-1.5 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>

                            <form id="delete-form-{{ $box->id }}" action="{{ route('cash-boxes.destroy', $box->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="button" onclick="confirmDelete({{ $box->id }})" class="p-1.5 bg-red-50 text-red-600 rounded hover:bg-red-100 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-slate-400">
                            {{ __('messages.no_data') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 print:hidden">
            {{ $cashBoxes->links() }}
        </div>

        <div x-show="openModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm print:hidden" x-cloak>
            <div @click.away="openModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800">{{ __('cash_box.new_box') }}</h3>
                    <button @click="openModal = false" class="text-slate-400 hover:text-red-500"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </div>
                <form action="{{ route('cash-boxes.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('cash_box.name') }}</label>
                            <input type="text" name="name" required class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('cash_box.type') }}</label>
                            <input type="text" name="type" placeholder="{{ __('cash_box.type_placeholder') }}" class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('cash_box.currency') }}</label>
                            <select name="currency_id" required class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="" disabled selected>{{ __('cash_box.select_currency') }}</option>
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->id }}">{{ strtoupper($currency->currency_type) }} ({{ $currency->symbol }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('cash_box.branch') }}</label>
                            <select name="branch_id" required class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="" disabled selected>{{ __('cash_box.select_branch') }}</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('cash_box.balance') }}</label>
                        <input type="number" step="0.01" name="balance" required class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-bold">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('cash_box.desc') }}</label>
                        <textarea name="description" rows="2" class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                    </div>
                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" @click="openModal = false" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50 transition">{{ __('cash_box.cancel') }}</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/30">{{ __('cash_box.save') }}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDelete(id) {
        Swal.fire({
            title: "{{ __('cash_box.delete_title') }}",
            text: "{{ __('cash_box.delete_text') }}",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444', 
            cancelButtonColor: '#6b7280', 
            confirmButtonText: "{{ __('cash_box.yes_delete') }}",
            cancelButtonText: "{{ __('cash_box.cancel') }}",
            background: '#fff',
            customClass: {
                popup: 'rounded-2xl shadow-xl border border-slate-100',
                title: 'text-slate-800 font-bold',
                content: 'text-slate-500'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        })
    }
</script>