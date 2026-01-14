<x-app-layout>
    @if(session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" 
             class="fixed top-5 right-5 z-[100] max-w-sm w-full bg-white shadow-[0_20px_50px_rgba(0,0,0,0.1)] rounded-2xl border-l-4 border-emerald-500 p-5 flex items-center gap-4"
             x-transition:enter="transform ease-out duration-300" x-transition:enter-start="translate-x-4 opacity-0" x-transition:enter-end="translate-x-0 opacity-100"
             x-transition:leave="transition ease-in duration-500" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="bg-emerald-100 p-2 rounded-xl text-emerald-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-black text-slate-800 tracking-tight">{{ __('roles.success') }}</h4>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-tighter mt-0.5">{{ session('success') }}</p>
            </div>
            <button @click="show = false" class="text-slate-300 hover:text-slate-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
    @endif

   <x-slot name="header">
    <div class="flex flex-col md:flex-row items-center justify-between w-full gap-6 px-2">
        
        <div class="flex flex-col text-center md:text-start rtl:md:text-right w-full">
            <h1 class="tracking-tight font-black text-slate-800 uppercase text-xl md:text-2xl leading-none">
                {{ __('roles.index_title') }}
            </h1>
            <span class="text-[11px] text-slate-400 font-bold uppercase tracking-widest mt-2 block">
                {{ __('roles.index_subtitle') }}
            </span>
        </div>

        <div class="flex-shrink-0">
            <a href="{{ route('roles.create') }}" 
               class="inline-flex items-center gap-3 px-6 py-3 bg-[#0f172a] hover:bg-indigo-600 hover:shadow-indigo-200 text-white text-[12px] font-black rounded-2xl transition-all duration-300 shadow-xl shadow-slate-200 group">
                
                <div class="bg-white/10 p-1 rounded-lg group-hover:bg-white/20 transition-colors">
                    <svg class="w-4 h-4 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                
                <span class="tracking-wide">{{ __('roles.btn_create_new') }}</span>
            </a>
        </div>

    </div>
</x-slot>

    <div class="py-10 bg-[#f8fafc] min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($roles as $role)
                <div class="bg-white rounded-[2.5rem] border border-slate-200/60 shadow-sm hover:shadow-2xl hover:border-indigo-100 transition-all duration-500 flex flex-col overflow-hidden group">
                    
                    <div class="p-8 pb-4 flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-slate-50 text-slate-400 rounded-2xl flex items-center justify-center font-black group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300 shadow-inner">
                                <span class="text-lg uppercase">{{ substr($role->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <h3 class="font-black text-slate-800 uppercase tracking-tight text-base">{{ $role->name }}</h3>
                                <p class="text-[10px] text-indigo-500 font-black uppercase tracking-widest mt-0.5">
                                    {{ $role->permissions->count() }} {{ __('roles.permissions_count') }}
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <a href="{{ route('roles.edit', $role->id) }}" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </a>
                          @if($role->name !== 'super-admin')
<form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="delete-role-form">
    @csrf @method('DELETE')
    <button type="button" class="delete-btn p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
        </svg>
    </button>
</form>
@endif
                        </div>
                    </div>

                    <div class="px-8 py-5 flex-1">
                        <div class="flex flex-wrap gap-2">
                            @forelse($role->permissions->take(6) as $permission)
                                <span class="px-3 py-1 bg-slate-50 text-slate-500 rounded-lg text-[10px] font-bold uppercase tracking-tight border border-slate-100">
                                    {{ str_replace(['roles.', '-', '.'], ' ', $permission->name) }}
                                </span>
                            @empty
                                <span class="text-[10px] text-slate-400 italic font-medium">{{ __('roles.no_permissions') }}</span>
                            @endforelse
                            
                            @if($role->permissions->count() > 6)
                                <span class="text-[10px] font-black text-indigo-500 bg-indigo-50 px-2 py-1 rounded-lg">
                                    +{{ $role->permissions->count() - 6 }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="p-5 px-8 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between mt-auto">
                        <div class="flex flex-col">
                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ __('roles.guard') }}</span>
                            <span class="text-[10px] font-bold text-slate-600">{{ $role->guard_name }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                             <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('roles.users') }}</span>
                             <div class="w-7 h-7 rounded-full bg-white border border-slate-200 flex items-center justify-center text-[10px] font-black text-indigo-600 shadow-sm">
                                {{ \App\Models\User::role($role->name)->count() }}
                             </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>

<script>
document.querySelectorAll('.delete-btn').forEach(button => {
    button.addEventListener('click', function() {
        const form = this.closest('.delete-role-form');
        
        Swal.fire({
            title: "{{ app()->getLocale() == 'ku' ? 'دڵنیایت؟' : 'Are you sure?' }}",
            text: "{{ app()->getLocale() == 'ku' ? 'ناتوانیت ئەم کارە بگەڕێنیتەوە!' : 'You won\'t be able to revert this!' }}",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5', // Indigo-600
            cancelButtonColor: '#f1f5f9',  // Slate-100
            confirmButtonText: "{{ app()->getLocale() == 'ku' ? 'بەڵێ، بیسڕەوە' : 'Yes, delete it!' }}",
            cancelButtonText: "{{ app()->getLocale() == 'ku' ? 'پاشگەزبوونەوە' : 'Cancel' }}",
            customClass: {
                popup: 'rounded-[2rem] border-none shadow-2xl',
                confirmButton: 'rounded-xl font-black uppercase tracking-widest text-xs px-6 py-3',
                cancelButton: 'rounded-xl font-black uppercase tracking-widest text-xs px-6 py-3 text-slate-600'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>