<x-app-layout>
    <div class="py-12 bg-[#f8fafc] min-h-screen pb-20">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-black text-slate-800 tracking-tight">{{ __('roles.edit_title') ?? 'Edit Role' }}</h1>
                    <p class="text-xs text-slate-400 font-medium">{{ $role->name }}</p>
                </div>
                <a href="{{ route('roles.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-indigo-600 transition-all group bg-white px-4 py-2 rounded-xl border border-slate-200 shadow-sm">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1 rtl:group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    {{ __('roles.back_to_list') ?? 'Back to List' }}
                </a>
            </div>

            <form action="{{ route('roles.update', $role->id) }}" method="POST">
                @csrf
                @method('PUT') <div class="bg-white rounded-[1.5rem] p-6 shadow-sm border border-slate-200/60 mb-6">
                    <label class="block text-[10px] font-black text-indigo-500 uppercase tracking-[0.2em] mb-3 ml-1">
                        {{ __('roles.role_name') }}
                    </label>
                    <input type="text" name="role_name" value="{{ old('role_name', $role->name) }}" 
                           class="w-full max-w-md rounded-xl border-slate-200 bg-slate-50/50 p-3.5 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-bold text-slate-700">
                </div>

                <div class="bg-white rounded-[1.5rem] shadow-sm border border-slate-200/60 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left rtl:text-right border-separate border-spacing-0">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100">
                                    <th class="p-4 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('roles.module') }}</th>
                                    <th class="p-4 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">{{ __('roles.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @php
                                    $grouped = $permissions->groupBy(function($p) {
                                        $n = strtolower($p->name);
                                        if (str_contains($n, 'currency')) return 'currency';
                                        if (str_contains($n, 'user')) return 'users';
                                        if (str_contains($n, 'cashbox')) return 'cashboxes';
                                        if (str_contains($n, 'report')) return 'reports';
                                        if (str_contains($n, 'log')) return 'logs';
                                        return 'system';
                                    });
                                @endphp

                                @foreach($grouped as $module => $perms)
                                <tr class="group hover:bg-indigo-50/30 transition-all">
                                    <td class="p-4 px-6 align-middle">
                                        <div class="flex items-center gap-3">
                                            <div class="w-1 h-6 bg-indigo-500 rounded-full"></div>
                                            <span class="font-black text-slate-700 text-sm uppercase tracking-tight">{{ __('roles.' . $module) }}</span>
                                        </div>
                                    </td>
                                    <td class="p-4 px-6">
                                        <div class="flex flex-wrap items-center gap-3 justify-end">
                                            <button type="button" @click="$el.closest('tr').querySelectorAll('input').forEach(i => i.checked = !i.checked)" 
                                                    class="text-[9px] font-bold text-indigo-400 hover:text-indigo-600 uppercase border border-indigo-100 px-2 py-1 rounded-md transition-all bg-indigo-50/20 mr-2">
                                                {{ __('roles.toggle_all') }}
                                            </button>
                                            
                                            @foreach($perms as $perm)
                                            <label class="inline-flex items-center gap-2 p-2 px-3 rounded-lg border border-slate-100 bg-white hover:border-indigo-200 cursor-pointer shadow-sm transition-all group/item">
                                                <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" 
                                                       {{ in_array($perm->name, $rolePermissions) ? 'checked' : '' }}
                                                       class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-0">
                                                <span class="text-[11px] font-bold text-slate-500 group-hover/item:text-slate-800 capitalize">
                                                    @php $action = trim(str_replace(['roles.', $module, 'es', 's', '-', '.', ' '], '', strtolower($perm->name))); @endphp
                                                    {{ __('roles.' . ($action ?: 'manage')) }}
                                                </span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button type="submit" class="w-full md:w-auto min-w-[240px] bg-indigo-600 hover:bg-indigo-700 text-white font-black py-3.5 px-8 rounded-xl shadow-lg transition-all flex items-center justify-center gap-3">
                        <span class="uppercase tracking-widest text-xs font-black">{{ __('roles.btn_update') ?? 'Update Role' }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

@if(session('success'))
    <div x-data="{ show: true }" 
         x-init="setTimeout(() => show = false, 5000)"
         x-show="show" 
         x-transition:enter="transform ease-out duration-300 transition"
         x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
         x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
         x-transition:leave="transition ease-in duration-500"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed top-5 right-5 z-[100] max-w-sm w-full bg-white shadow-[0_20px_50px_rgba(0,0,0,0.1)] rounded-2xl border-l-4 border-emerald-500 p-5 flex items-center gap-4">
        
        <div class="bg-emerald-100 p-2 rounded-xl text-emerald-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>

        <div class="flex-1">
            <h4 class="text-sm font-black text-slate-800 tracking-tight">
                {{ app()->getLocale() == 'ku' ? 'سەرکەوتوو بوو' : 'Success' }}
            </h4>
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-tighter mt-0.5">
                {{ session('success') }}
            </p>
        </div>

        <button @click="show = false" class="text-slate-300 hover:text-slate-500 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
@endif