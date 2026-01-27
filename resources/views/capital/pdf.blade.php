<!DOCTYPE html>
<html dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'nrt', 'dejavu sans', sans-serif; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #333; font-size: 24px; }
        .header p { margin: 5px 0 0; color: #777; font-size: 14px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px 12px; text-align: center; font-size: 12px; }
        th { background-color: #f8f9fa; color: #333; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9fafb; }
        
        .amount { font-family: sans-serif; font-weight: bold; }
        .total-row td { background-color: #f0fdf4; font-weight: bold; border-top: 2px solid #22c55e; color: #15803d; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; font-size: 10px; text-align: center; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $title }}</h1>
        <p>{{ __('capital.date') }}: {{ $date }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 25%; text-align: left;">{{ __('capital.owner') }}</th>
                <th style="width: 10%;">{{ __('capital.share_percent') }}</th>
                <th style="width: 15%;">{{ __('capital.amount') }}</th>
                <th style="width: 10%;">{{ __('capital.currency') }}</th>
                <th style="width: 15%;">{{ __('capital.exchange_rate') }}</th>
                <th style="width: 20%;">{{ __('capital.balance_usd') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($capitals as $capital)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td style="text-align: left; font-weight: bold;">{{ $capital->owner->name ?? 'Unknown' }}</td>
                <td>{{ $capital->share_percentage }}%</td>
                <td class="amount">{{ number_format($capital->amount, 0) }}</td>
                <td>{{ $capital->currency->currency_type ?? '-' }}</td>
                <td class="amount">{{ number_format($capital->exchange_rate, 4) }}</td>
                <td class="amount" style="color: #4f46e5;">${{ number_format($capital->balance_usd, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" style="text-align: right;">{{ __('capital.totals') }}</td>
                <td>{{ $totalShares }}%</td>
                <td colspan="3"></td>
                <td>${{ number_format($totalBalance, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        {{ __('capital.generated_by') }} | {{ date('Y-m-d H:i:s') }}
    </div>

</body>
</html>