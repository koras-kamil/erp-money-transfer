<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('log.title') }}
        </h2>
    </x-slot>

    <div class="py-6 bg-slate-50 min-h-screen" x-data="{ 
        openModal: false, 
        activeLog: {},
        translations: {{ json_encode(__('log.attributes')) }},
        labelNew: '{{ __('log.new_value') }}',
        labelOld: '{{ __('log.old_value') }}',
        labelDeleted: '{{ __('log.deleted_value') }}',
        
        getProperties() {
            if (!this.activeLog.properties) return {};
            
            // Determine source based on action
            let props = this.activeLog.action === 'deleted' 
                ? { ...this.activeLog.properties.old } 
                : { ...this.activeLog.properties.attributes };
            
            // Clean up: Remove system timestamps from the view
            if (props.updated_at) delete props.updated_at;
            if (props.created_at) delete props.created_at;
            if (props.id) delete props.id;

            return props;
        }
    }">
        <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-4">{{ __('log.user') }}</th>
                                <th class="px-4 py-4 text-center">{{ __('log.action') }}</th>
                                <th class="px-4 py-4 hidden md:table-cell">{{ __('log.subject') }}</th>
                                <th class="px-4 py-4 text-center">{{ __('log.details') }}</th>
                                <th class="px-4 py-4 text-right">{{ __('log.timestamp') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($activities as $activity)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-4 py-4">
                                    <div class="font-bold text-slate-900">{{ $activity->causer->name ?? __('log.system') }}</div>
                                    <div class="text-[10px] text-slate-400 md:hidden">{{ __("log.models." . strtolower(class_basename($activity->subject_type))) }}</div>
                                </td>
                                
                                <td class="px-4 py-4 text-center">
                                    <span class="px-2 py-1 rounded-md text-[10px] font-bold uppercase border 
                                        {{ $activity->description === 'created' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}
                                        {{ $activity->description === 'updated' ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}
                                        {{ $activity->description === 'deleted' ? 'bg-rose-50 text-rose-700 border-rose-200' : '' }}">
                                        {{ __("log.actions." . $activity->description) }}
                                    </span>
                                </td>

                                <td class="px-4 py-4 text-slate-600 hidden md:table-cell">
                                    {{ __("log.models." . strtolower(class_basename($activity->subject_type))) }}
                                </td>

                                <td class="px-4 py-4 text-center">
                                    <button 
                                        @click="openModal = true; activeLog = {{ json_encode([
                                            'user' => $activity->causer->name ?? 'System',
                                            'time' => $activity->created_at->format('j/n/Y H:i'),
                                            'action' => $activity->description,
                                            'properties' => $activity->properties
                                        ]) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition-colors font-semibold text-xs">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        {{ __('log.view') }}
                                    </button>
                                </td>

                                <td class="px-4 py-4 text-right text-slate-500 font-mono text-[11px]">
                                    <span class="font-bold text-slate-700">{{ $activity->created_at->format('j/n/Y') }}</span>
                                    <br>
                                    {{ $activity->created_at->format('H:i') }}
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="p-10 text-center text-slate-400">{{ __('log.empty') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-4">
                {{ $activities->links() }}
            </div>
        </div>

        {{-- POP-UP MODAL --}}
        <div x-show="openModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4 bg-slate-900/60 backdrop-blur-sm" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-cloak>
            
            <div @click.away="openModal = false" class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col">
                
                {{-- Modal Header --}}
                <div class="p-4 sm:p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">{{ __('log.detail_view') }}</h3>
                        <p class="text-xs text-slate-500" x-text="activeLog.user + ' • ' + activeLog.time"></p>
                    </div>
                    <button @click="openModal = false" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
                </div>

                {{-- Modal Body --}}
                <div class="p-4 sm:p-6 overflow-y-auto">
                    <div class="space-y-3">
                        <template x-for="(val, key) in getProperties()" :key="key">
                            <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/50">
                                {{-- Translated Column Name --}}
                                <div class="text-[10px] font-bold text-indigo-600 uppercase mb-2" 
                                     x-text="translations[key] ? translations[key] : key.replace('_', ' ')">
                                </div>

                                <div class="flex items-center gap-2 sm:gap-4">
                                    {{-- If Updated: Show Old vs New --}}
                                    <template x-if="activeLog.action === 'updated'">
                                        <div class="flex-1">
                                            <span class="text-[9px] text-slate-400 uppercase font-bold block mb-1" x-text="labelOld"></span>
                                            <div class="p-2 bg-red-50 text-red-700 rounded border border-red-100 line-through text-xs break-all" 
                                                 x-text="activeLog.properties.old ? activeLog.properties.old[key] : '---'"></div>
                                        </div>
                                    </template>
                                    
                                    <div class="text-slate-300" x-show="activeLog.action === 'updated'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                                    </div>

                                    {{-- Current/New/Deleted Value --}}
                                    <div class="flex-1">
                                        <span class="text-[9px] text-slate-400 uppercase font-bold block mb-1" 
                                              x-text="activeLog.action === 'updated' ? labelNew : (activeLog.action === 'deleted' ? labelDeleted : 'Value')"></span>
                                        <div class="p-2 rounded border text-xs break-all font-bold" 
                                             :class="activeLog.action === 'deleted' ? 'bg-rose-50 text-rose-700 border-rose-100' : 'bg-emerald-50 text-emerald-800 border-emerald-100'"
                                             x-text="val"></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <template x-if="Object.keys(getProperties()).length === 0">
                        <div class="text-center py-10">
                            <p class="text-slate-400 italic text-sm">{{ __('log.no_changes') }}</p>
                        </div>
                    </template>
                </div>

                {{-- Modal Footer --}}
                <div class="p-4 border-t border-slate-100 bg-slate-50 flex justify-end">
                    <button @click="openModal = false" class="px-6 py-2 bg-slate-800 text-white rounded-lg font-bold hover:bg-slate-700 transition-colors shadow-md">
                        {{ __('log.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>