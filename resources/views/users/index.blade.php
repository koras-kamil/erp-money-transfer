<x-app-layout>
    {{-- 1. Header: Just the Title (Clean & Centered) --}}
    <x-slot name="header">
        {{ __('users.title') }}
    </x-slot>

    <div class="py-6" x-data="{ openModal: false, activeUser: {}, isSaving: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col gap-4">
            
            {{-- 2. Button Moved Here: Creates space and sits nicely on the right --}}
            <div class="flex justify-end">
                <a href="{{ route('users.create') }}" class="px-5 py-2.5 bg-[#0f172a] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl hover:bg-indigo-700 hover:shadow-lg hover:scale-105 transition-all shadow-md shadow-slate-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('users.btn_create') }}
                </a>
            </div>

            {{-- 3. The Table --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-[2rem] border border-slate-200">
                <table class="w-full text-left border-collapse rtl:text-right">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 text-[11px] uppercase tracking-wider font-bold text-slate-500">{{ __('users.column_user') }}</th>
                            <th class="px-6 py-4 text-[11px] uppercase tracking-wider font-bold text-slate-500">{{ __('users.column_branch') }}</th>
                            <th class="px-6 py-4 text-[11px] uppercase tracking-wider font-bold text-slate-500 text-center">{{ __('users.column_role') }}</th>
                            <th class="px-6 py-4 text-[11px] uppercase tracking-wider font-bold text-slate-500 text-center">{{ __('users.column_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($users as $user)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            {{-- User Info --}}
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 leading-tight">{{ $user->name }}</div>
                                <div class="text-xs text-slate-400 font-medium">{{ $user->email }}</div>
                            </td>
                            
                            {{-- Branch --}}
                            <td class="px-6 py-4">
                                <span class="text-xs font-bold {{ $user->branch ? 'text-slate-600 bg-slate-100 px-2 py-1 rounded-md' : 'text-red-400 italic' }}">
                                    {{ $user->branch->name ?? __('users.no_branch') }}
                                </span>
                            </td>

                            {{-- Role --}}
                            <td class="px-6 py-4 text-center">
                                @foreach($user->getRoleNames() as $role)
                                    <span class="inline-flex px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase {{ $role == 'super-admin' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-indigo-50 text-indigo-600 border border-indigo-100' }} shadow-sm">
                                        {{ str_replace('-', ' ', $role) }}
                                    </span>
                                @endforeach
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Quick Access Edit --}}
                                    <button @click="openModal = true; activeUser = {{ json_encode([
                                        'id' => $user->id,
                                        'name' => $user->name,
                                        'role' => $user->roles->first()->name ?? '',
                                        'branch_id' => $user->branch_id
                                    ]) }}" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all" title="{{ __('users.edit_access') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    </button>

                                    {{-- Full Edit --}}
                                    <a href="{{ route('users.edit', $user->id) }}" class="p-2 text-slate-600 hover:bg-slate-100 rounded-xl transition-all" title="{{ __('users.edit_user') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </a>

                                    {{-- Delete --}}
                                    <form id="delete-user-{{ $user->id }}" action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                                onclick="window.confirmAction('delete-user-{{ $user->id }}', '{{ __('users.confirm_delete_text') }}')"
                                                class="p-2 text-red-500 hover:bg-red-50 rounded-xl transition-all" 
                                                title="{{ __('users.delete') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                
                {{-- Pagination --}}
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $users->links() }}
                </div>
            </div>
        </div>

        {{-- ACCESS MODAL (Same as before) --}}
        <div x-show="openModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
            <div x-show="openModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            
            <div x-show="openModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 @click.away="openModal = false" class="bg-white rounded-[2rem] shadow-2xl w-full max-w-md p-8 z-10 border border-slate-100 relative">
                
                <h3 class="text-xl font-black text-slate-800 mb-1">{{ __('users.modal_title') }}</h3>
                <p class="text-sm text-slate-400 mb-6" x-text="activeUser.name"></p>
                
                <form :action="'/users/' + activeUser.id + '/role'" method="POST" @submit="isSaving = true">
                    @csrf
                    @method('PUT')

                    <div class="space-y-5">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">{{ __('users.label_branch') }}</label>
                            <select name="branch_id" x-model="activeUser.branch_id" class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm focus:ring-indigo-500 focus:border-indigo-500 p-3 transition-all">
                                <option value="">{{ __('users.no_branch') }}</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">{{ __('users.label_role') }}</label>
                            <select name="role" x-model="activeUser.role" class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm focus:ring-indigo-500 focus:border-indigo-500 p-3 transition-all">
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}">{{ ucwords(str_replace('-', ' ', $role->name)) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-8 flex gap-3">
                        <button type="button" @click="openModal = false" :disabled="isSaving" class="flex-1 px-4 py-3 bg-slate-100 text-slate-600 rounded-xl font-bold text-sm hover:bg-slate-200 transition-colors">{{ __('users.btn_cancel') }}</button>
                        
                        <button type="submit" :disabled="isSaving" class="flex-1 px-4 py-3 bg-[#0f172a] text-white rounded-xl font-bold text-sm shadow-lg shadow-indigo-200 hover:bg-indigo-700 active:scale-95 transition-all flex items-center justify-center gap-2">
                            <template x-if="isSaving">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </template>
                            <span x-text="isSaving ? '...' : '{{ __('users.btn_save') }}'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>