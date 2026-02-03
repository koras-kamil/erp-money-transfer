<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('account.print_title') }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; background: white; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #4f46e5; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #1f2937; text-transform: uppercase; letter-spacing: 2px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #f8fafc; color: #475569; font-weight: bold; text-align: inherit; padding: 12px 8px; border: 1px solid #e2e8f0; }
        td { padding: 10px 8px; border: 1px solid #e2e8f0; vertical-align: middle; }
        .text-center { text-align: center; }
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: bold; background: #f1f5f9; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h1>{{ __('account.accounts_report') }}</h1>
        <p>{{ now()->format('Y-m-d H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('account.code') }}</th>
                <th>{{ __('account.name') }}</th>
                <th>{{ __('account.branch') }}</th>
                <th>{{ __('account.type') }}</th>
                <th>{{ __('account.currency') }}</th>
                <th>{{ __('account.debt_limit') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($accounts as $acc)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $acc->code }}</td>
                <td style="font-weight: bold;">{{ $acc->name }}</td>
                <td>{{ $acc->branch->name ?? '-' }}</td>
                <td class="text-center"><span class="badge">{{ __('account.'.$acc->account_type) }}</span></td>
                <td class="text-center">{{ $acc->currency->currency_type ?? '-' }}</td>
                <td class="text-center" style="color: #059669;">{{ number_format($acc->debt_limit, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>