<!DOCTYPE html>
<html lang="ku" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'nrt', sans-serif; direction: rtl; text-align: right; font-size: 12px; }
        .header { text-align: center; border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f3f4f6; padding: 8px; border: 1px solid #e5e7eb; font-weight: bold; text-align: center; }
        td { padding: 8px; border: 1px solid #e5e7eb; text-align: center; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $title }}</h2>
        <p>Date: {{ $date }} | User: {{ $user }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="15%">{{ __('spending.code') }}</th>
                <th width="30%">{{ __('spending.name') }}</th>
                <th width="20%">{{ __('spending.accountant_code') }}</th>
                <th width="20%">{{ __('spending.branch') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groups as $group)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td style="font-weight: bold;">{{ $group->code }}</td>
                <td>{{ $group->name }}</td>
                <td>{{ $group->accountant_code }}</td>
                <td>{{ $group->branch->name ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">Page {PAGENO} of {nbpg}</div>
</body>
</html>