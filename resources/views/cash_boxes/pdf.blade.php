<!DOCTYPE html>
<html lang="ku" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        /* 1. Base Font & Direction (Critical for Kurdish) */
        body {
            font-family: 'nrt', sans-serif; /* Must match config/pdf.php */
            direction: rtl;
            text-align: right;
            font-size: 12px;
            color: #1f2937;
        }

        /* 2. Header */
        .header-container {
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
            text-align: center;
        }
        .app-name { font-size: 24px; font-weight: bold; margin-bottom: 5px; }
        .report-title { font-size: 16px; color: #4b5563; margin-bottom: 10px; }
        .meta-info { font-size: 10px; color: #9ca3af; }

        /* 3. Table */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th {
            background-color: #f9fafb;
            font-weight: bold;
            text-transform: uppercase;
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
            text-align: right;
            font-size: 10px;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
            color: #4b5563;
            vertical-align: middle;
        }

        /* 4. Utilities */
        .text-center { text-align: center; }
        .text-emerald { color: #059669; font-weight: bold; }
        .active { color: #059669; }
        .inactive { color: #dc2626; }
        .badge { background-color: #eef2ff; color: #4f46e5; padding: 3px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; }

        /* 5. Footer */
        .footer {
            position: fixed; bottom: 0; width: 100%; text-align: center;
            font-size: 10px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 10px;
        }
    </style>
</head>
<body>

    <div class="header-container">
        <div class="app-name">{{ config('app.name') }}</div>
        <div class="report-title">{{ $title }}</div>
        <div class="meta-info">
            بەروار: {{ $date }} | بەکارهێنەر: {{ $user }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="20%">ناو</th>
                <th width="10%">جۆر</th>
                <th width="10%" class="text-center">دراو</th>
                <th width="15%" class="text-center">باڵانس</th>
                <th width="15%">لقی</th>
                <th width="15%">بەکارهێنەر</th>
                <th width="10%" class="text-center">دۆخ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cashBoxes as $box)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td style="font-weight: bold;">{{ $box->name }}</td>
                <td>{{ $box->type ?? '-' }}</td>
                <td class="text-center"><span class="badge">{{ $box->currency->currency_type ?? '-' }}</span></td>
                <td class="text-center text-emerald" style="direction: ltr;">{{ number_format($box->balance, 2) }}</td>
                <td>{{ $box->branch->name ?? '-' }}</td>
                <td style="font-size: 10px;">{{ $box->user->name ?? '-' }}</td>
                <td class="text-center {{ $box->is_active ? 'active' : 'inactive' }}">
                    {{ $box->is_active ? 'چالاک' : 'ناچالاک' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Page {PAGENO} of {nbpg}
    </div>

</body>
</html>