<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h2 class="font-black text-xl text-slate-800 tracking-tight">
                {{ __('log.title') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8 min-h-screen" 
         x-data="{ 
            openModal: false, 
            activeLog: {},
            translations: {{ json_encode(__('log.attributes')) }},
            
            // Helper to get readable key name from translation file
            getKeyName(key) {
                if (this.translations[key]) return this.translations[key];
                // Fallback: remove _id and capitalize if no translation found
                return key.replace('_id', '').replace('_', ' ').toUpperCase();
            },

            // Prepare data for the modal loop
            getData() {
                if (!this.activeLog.properties) return {};
                
                let data = {};
                let old = this.activeLog.properties.old || {};
                let attributes = this.activeLog.properties.attributes || {};

                // IF DELETED: Use 'old' or 'attributes'
                if (this.activeLog.action === 'deleted') {
                    data = Object.keys(old).length ? old : attributes;
                } 
                // IF CREATED: Use 'attributes'
                else if (this.activeLog.action === 'created') {
                    data = attributes;
                }
                // IF UPDATED: Merge keys from both to show comparison
                else {
                    data = { ...old, ...attributes };
                }

                // Filter out system columns that users don't need to see
                const ignored = ['id', 'created_at', 'updated_at', 'deleted_at', 'password', 'remember_token'];
                
                return Object.keys(data)
                    .filter(key => !ignored.includes(key))
                    .reduce((obj, key) => {
                        obj[key] = {
                            old: old[key] || null,
                            new: attributes[key] || null
                        };
                        return obj;
                    }, {});
            }
         }">
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50/80 text-slate-500 font-bold uppercase tracking-wider text-[10px] border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-4">{{ __('log.user') }}</th>
                                <th class="px-6 py-4 text-center">{{ __('log.action') }}</th>
                                <th class="px-6 py-4">{{ __('log.subject') }}</th>
                                <th class="px-6 py-4 text-center">{{ __('log.details') }}</th>
                                <th class="px-6 py-4 text-right">{{ __('log.timestamp') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($activities as $activity)
                            <tr class="hover:bg-slate-50/80 transition-all duration-200 group">
                                {{-- USER --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-500 border border-slate-200">
                                            {{ substr($activity->causer->name ?? 'S', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-800">{{ $activity->causer->name ?? __('log.system') }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $activity->causer->email ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                
                                {{-- ACTION BADGE (FIXED LOGIC) --}}
                                <td class="px-6 py-4 text-center">
                                    @php
                                        // Use 'event' for simple keywords (created, updated, deleted)
                                        // Fallback to description only if event is missing
                                        $event = $activity->event ?? $activity->description;
                                        
                                        $color = match($event) {
                                            'created' => 'emerald',
                                            'updated' => 'amber',
                                            'deleted' => 'rose',
                                            default => 'slate'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-{{ $color }}-50 text-{{ $color }}-600 border border-{{ $color }}-100 ring-1 ring-{{ $color }}-500/10">
                                        <span class="w-1.5 h-1.5 rounded-full bg-{{ $color }}-500"></span>
                                        {{ __("log.actions." . $event) }}
                                    </span>
                                </td>

                                {{-- SUBJECT --}}
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-700 text-xs">
                                            {{ __("log.models." . strtolower(class_basename($activity->subject_type))) }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-mono">
                                            ID: #{{ $activity->subject_id }}
                                        </span>
                                    </div>
                                </td>

                                {{-- VIEW BUTTON --}}
                                <td class="px-6 py-4 text-center">
                                    <button 
                                        @click="openModal = true; activeLog = {{ json_encode([
                                            'user' => $activity->causer->name ?? __('log.system'),
                                            'time' => $activity->created_at->format('Y-m-d H:i'),
                                            'action' => $activity->event ?? $activity->description,
                                            'properties' => $activity->properties,
                                            'model' => __("log.models." . strtolower(class_basename($activity->subject_type)))
                                        ]) }}"
                                        class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all active:scale-95">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                </td>

                                {{-- TIMESTAMP --}}
                                <td class="px-6 py-4 text-right">
                                    <div class="flex flex-col items-end">
                                        <span class="text-xs font-bold text-slate-700">{{ $activity->created_at->format('Y/m/d') }}</span>
                                        <span class="text-[10px] text-slate-400 font-medium">{{ $activity->created_at->format('h:i A') }}</span>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-12">
                                    <div class="flex flex-col items-center justify-center text-slate-300">
                                        <svg class="w-12 h-12 mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        <span class="text-sm font-medium">{{ __('log.empty') }}</span>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="mt-6 px-2">
                {{ $activities->links() }}
            </div>
        </div>

        {{-- PROFFESIONAL MODAL --}}
        <div x-show="openModal" 
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-cloak>
            
            <div @click.away="openModal = false" 
                 class="bg-white rounded-[2rem] shadow-2xl max-w-2xl w-full max-h-[85vh] flex flex-col overflow-hidden ring-1 ring-slate-900/5"
                 x-transition:enter="transition ease-[cubic-bezier(0.34,1.56,0.64,1)] duration-500"
                 x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100">
                
                {{-- Modal Header --}}
                <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-start">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase"
                                  :class="{
                                    'bg-emerald-100 text-emerald-700': activeLog.action === 'created',
                                    'bg-amber-100 text-amber-700': activeLog.action === 'updated',
                                    'bg-rose-100 text-rose-700': activeLog.action === 'deleted'
                                  }"
                                  x-text="activeLog.action === 'created' ? '{{ __('log.actions.created') }}' : (activeLog.action === 'updated' ? '{{ __('log.actions.updated') }}' : '{{ __('log.actions.deleted') }}')">
                            </span>
                            <span class="text-xs font-bold text-slate-400" x-text="activeLog.time"></span>
                        </div>
                        <h3 class="text-lg font-black text-slate-800 tracking-tight">{{ __('log.detail_view') }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ __('log.user') }}: <span class="font-bold text-slate-700" x-text="activeLog.user"></span>
                        </p>
                    </div>
                    <button @click="openModal = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-8 overflow-y-auto custom-scrollbar">
                    
                    {{-- 1. UPDATED (Side by Side Diff) --}}
                    <template x-if="activeLog.action === 'updated'">
                        <div class="space-y-4">
                            <template x-for="(val, key) in getData()" :key="key">
                                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                                    <p class="text-[10px] font-black text-indigo-500 uppercase tracking-wider mb-3" x-text="getKeyName(key)"></p>
                                    
                                    <div class="flex items-center gap-4">
                                        {{-- OLD --}}
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[9px] font-bold text-slate-400 uppercase mb-1">{{ __('log.old_value') }}</p>
                                            <div class="text-xs font-medium text-rose-600 bg-rose-50 px-3 py-2 rounded-lg border border-rose-100 break-all" x-text="val.old ?? '---'"></div>
                                        </div>
                                        
                                        <div class="text-slate-300">
                                            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                        </div>

                                        {{-- NEW --}}
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[9px] font-bold text-slate-400 uppercase mb-1">{{ __('log.new_value') }}</p>
                                            <div class="text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-2 rounded-lg border border-emerald-100 break-all shadow-sm" x-text="val.new ?? '---'"></div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- 2. CREATED (Simple List) --}}
                    <template x-if="activeLog.action === 'created'">
                        <div class="grid grid-cols-2 gap-4">
                            <template x-for="(val, key) in getData()" :key="key">
                                <div class="bg-emerald-50/50 p-4 rounded-2xl border border-emerald-100/50">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1" x-text="getKeyName(key)"></p>
                                    <p class="text-sm font-bold text-emerald-800 break-all" x-text="val.new"></p>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- 3. DELETED (Red List) --}}
                    <template x-if="activeLog.action === 'deleted'">
                        <div class="space-y-2">
                            <div class="bg-rose-50 border border-rose-100 rounded-xl p-3 mb-4 text-center">
                                <p class="text-xs font-bold text-rose-600">{{ __('log.deleted_value') }}</p>
                            </div>
                            <template x-for="(val, key) in getData()" :key="key">
                                <div class="flex justify-between items-center py-3 border-b border-slate-100 last:border-0">
                                    <span class="text-xs font-bold text-slate-500 uppercase" x-text="getKeyName(key)"></span>
                                    <span class="text-sm font-medium text-slate-800 break-all" x-text="val.old || val.new"></span>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- EMPTY STATE --}}
                    <template x-if="Object.keys(getData()).length === 0">
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <p class="text-sm font-bold text-slate-500">{{ __('log.no_changes') }}</p>
                            <p class="text-xs text-slate-400 mt-1">This action didn't record specific attribute changes.</p>
                        </div>
                    </template>
                </div>

                {{-- Modal Footer --}}
                <div class="p-6 border-t border-slate-100 bg-slate-50 flex justify-end">
                    <button @click="openModal = false" class="px-6 py-2.5 bg-[#0f172a] text-white rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-slate-800 transition-all shadow-lg shadow-slate-200">
                        {{ __('log.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>