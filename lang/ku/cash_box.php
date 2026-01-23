<?php

return [
    'title' => 'سندوقەکانی پارە',
    'new_box' => 'زیادکردنی سندوقی نوێ', // Updated for clarity (New Box)
    'edit_box' => 'دەستکاریکردنی سندوق',
    
    // Table & Form Columns
    'id' => 'ڕیز',
    'name' => 'ناوی سندوق',
    'type' => 'جۆر',
    'currency' => 'دراو',
    'balance' => 'باڵانس', // Changed from "Initial Balance" to just "Balance" for the table
    'user' => 'بەکارهێنەر',
    'branch' => 'لق',
    'date' => 'بەرواری کردنەوە',
    'desc' => 'تێبینی',
    'active' => 'چالاک',
    'actions' => 'کردارەکان',

    // Placeholders
    'select_branch' => 'لق هەڵبژێرە',
    'select_currency' => 'جۆری پارە هەڵبژێرە',
    'type_placeholder' => 'نموونە: سندوقی سەرەکی',

    // Buttons
    'save' => 'تۆمارکردن',
    'save_changes' => 'تۆمارکردنی گۆڕانکارییەکان', // New
    'update' => 'نوێکردنەوە',
    'cancel' => 'پاشگەزبوونەوە',
    'back' => 'گەڕانەوە',
    'print' => 'چاپکردن',
    'excel' => 'ڕاپۆرتی ئێکسڵ',
    'manage_view' => 'ڕێکخستنی ستوون',
    'show_all' => 'هەمووی',
    'hide_all' => 'هیچ',

    // Trash & Restore
    'trash' => 'سەلەی خۆڵ',
    'restore' => 'گەڕاندنەوە',
    'perm_delete' => 'سڕینەوەی یەکجاری',

    // Alerts & Messages
    'created' => 'سندوقەکە بەسەرکەوتویی زیادکرا.',
    'updated' => 'زانیاری سندوقەکە بەسەرکەوتویی نوێکرایەوە.',
    'deleted' => 'سندوقەکە بەسەرکەوتویی سڕایەوە.',
    'restored' => 'سندوقەکە بەسەرکەوتویی گەڕێندرایەوە.',
    'permanently_deleted' => 'سندوقەکە بەیەکجاری سڕایەوە.',
    'saved_successfully' => 'گۆڕانکارییەکان بەسەرکەوتویی تۆمارکران.', // Critical for Bulk Save
    'no_changes' => 'هیچ گۆڕانکارییەک نەکراوە بۆ تۆمارکردن.', // For JS Alert

    // Delete Modal
    'delete_title' => 'دڵنیایت لە سڕینەوە؟',
    'delete_text' => 'ئەم سندوقە دەچێتە ناو سەلەی خۆڵ.',
    'yes_delete' => 'بەڵێ، بیسڕەوە',
    'deleted_at' => 'بەرواری سڕینەوە',
    'no_deleted_data' => 'هیچ سندوقێکی سڕاوە نەدۆزرایەوە',
    'warning_perm_delete' => 'ئاگاداربە: ئەمە بە یەکجاری سندوقەکە دەسڕێتەوە. دڵنیایت؟',
    'deleted_by'   => 'سڕایەوە لەلایەن',
];