@props([
    'type' => 'button', // Options: add, print, trash, columns, save, cancel, edit, delete, bulk-delete
])

@php
    // 1. Base Styles (Layout, Animation, Cursor)
    $base = "flex items-center justify-center transition-all duration-200 active:scale-95 cursor-pointer disabled:opacity-50 disabled:pointer-events-none";
    
    // 2. Size & Color Variations
    $styles = [
        // --- Toolbar Buttons (Big: 40px) ---
        'add' => "w-10 h-10 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-200",
        'print' => "w-10 h-10 rounded-xl bg-slate-700 text-white hover:bg-slate-800 shadow-sm",
        'trash' => "w-10 h-10 rounded-xl bg-red-50 text-red-500 border border-red-100 hover:bg-red-100 shadow-sm",
        'columns' => "w-10 h-10 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 shadow-sm",
        
        // --- Row Action Buttons (Small: 28px) ---
        'save' => "w-7 h-7 rounded-lg bg-emerald-500 text-white hover:bg-emerald-600 shadow-sm",
        'cancel' => "w-7 h-7 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50",
        'edit' => "p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50",
        'delete' => "p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50",
        
        // --- Text Buttons ---
        'bulk-delete' => "px-3 py-1.5 bg-red-600 text-white text-xs font-bold rounded shadow-sm hover:bg-red-700",
    ];

    // 3. Icons (SVG Paths)
    $icons = [
        'add' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>',
        'print' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>',
        'trash' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>',
        'columns' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>',
        'save' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>',
        'cancel' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>',
        'edit' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>',
        'delete' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>',
        'bulk-delete' => '',
    ];

    // Merge styles
    $finalClass = $base . ' ' . ($styles[$type] ?? '');
    
    // Determine content (Slot overrides Icon)
    $content = $slot->isNotEmpty() ? $slot : ($icons[$type] ?? '');
@endphp

{{-- Logic: If it has 'href', render <a>, else render <button> --}}
@if($attributes->has('href'))
    <a {{ $attributes->merge(['class' => $finalClass, 'x-cloak' => true]) }}>{!! $content !!}</a>
@else
    <button {{ $attributes->merge(['class' => $finalClass, 'type' => 'button', 'x-cloak' => true]) }}>{!! $content !!}</button>
@endif