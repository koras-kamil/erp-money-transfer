<!DOCTYPE html>
<html lang="ku" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'nrt', sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 12px;
            color: #1f2937;
        }
        .header-container {
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
            text-align: center;
        }
        .app-name { font-size: 24px; font-weight: bold; margin-bottom: 5px; }
        .report-title { font-size: 16px; color: #4b5563; margin-bottom: 10px; }
        .meta-info { font-size: 10px; color: #9ca3af; }
        
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
        .text-center { text-align: center; }
        .text-emerald { color: #059669; font-weight: bold; }
        .text-blue { color: #2563eb; font-weight: bold; }
        .active { color: #059669; }
        .inactive { color: #dc2626; }
        
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
                <th width="15%">جۆر</th>
                <th width="10%" class="text-center">کۆد</th>
                <th width="10%" class="text-center">ڕەقەم</th>
                <th width="15%" class="text-center">نرخی کۆ</th>
                <th width="15%" class="text-center">نرخی تاک</th>
                <th width="20%">لقی</th>
                <th width="10%" class="text-center">دۆخ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($currencies as $currency)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td style="font-weight: bold;">{{ $currency->currency_type }}</td>
                <td class="text-center">{{ $currency->symbol }}</td>
                <td class="text-center">{{ $currency->digit_number }}</td>
                <td class="text-center text-emerald">{{ number_format($currency->price_total, 3) }}</td>
                <td class="text-center text-blue">{{ number_format($currency->price_single, 3) }}</td>
                <td>{{ $currency->branch }}</td>
                <td class="text-center {{ $currency->is_active ? 'active' : 'inactive' }}">
                    {{ $currency->is_active ? 'چالاک' : 'ناچالاک' }}
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