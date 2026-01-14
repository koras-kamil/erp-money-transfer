<?php

return [
    'title' => 'تۆماری چالاکییەکان',
    'user' => 'بەکارهێنەر',
    'action' => 'کردار',
    'subject' => 'بابەت',
    'details' => 'وردەکاری',
    'timestamp' => 'کات',
    'system' => 'سیستەم',
    'view' => 'بینین',
    'close' => 'داخستن',
    'empty' => 'هیچ چالاکییەک تۆمار نەکراوە',
    'detail_view' => 'وردەکاری گۆڕانکارییەکان',
    'no_changes' => 'هیچ گۆڕانکارییەک تۆمار نەکراوە',
    
    'new_value' => 'نوێ',
    'old_value' => 'کۆن',
    'deleted_value' => 'زانیاری سڕاوە',
    'current_value' => 'زانیاری زیادکراو',

    // Action Types
    'actions' => [
        'created' => 'زیادکردن',
        'updated' => 'گۆڕانکاری',
        'deleted' => 'سڕینەوە',
    ],

    // Model Names (Subject Types)
    'models' => [
        'user' => 'بەکارهێنەر',
        'branch' => 'لق',
        'role' => 'دەسەڵات',
        'permission' => 'مۆڵەت',
        'currency' => 'دراو',
        'cashbox' => 'سندوق',
    ],

    // Database Column Translations (The most important part)
    'attributes' => [
        'name' => 'ناو',
        'email' => 'ئیمەیڵ',
        'password' => 'وشەی نهێنی',
        'branch_id' => 'لق',
        'role' => 'دەسەڵات',
        'amount' => 'بڕ',
        'symbol' => 'هێما',
        'active' => 'چالاک',
        'address' => 'ناونیشان',
        'phone' => 'ژمارە تەلەفۆن',
    ],
];