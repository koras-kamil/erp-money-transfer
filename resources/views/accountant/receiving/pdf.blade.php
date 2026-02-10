<!DOCTYPE html>
<html lang="ku" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('accountant.receiving_title') }} - PDF</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; padding: 20px; background: #fff; color: #1e293b; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #e2e8f0; padding-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; color: #334155; }
        .header p { margin: 5px 0 0; font-size: 12px; color: #64748b; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 12px; }
        th, td { border: 1px solid #cbd5e1; padding: 8px 12px; text-align: center; }
        th { background-color: #f1f5f9; color: #475569; font-weight: bold; text-transform: uppercase; }
        tr:nth-child(even) { background-color: #f8fafc; }
        
        .badge { padding: 3px 8px; border-radius: 4px; font-weight: bold; font-size: 10px; text-transform: uppercase; }
        .bg-green { background-color: #dcfce7; color: #166534; }
        .bg-red { background-color: #fee2e2; color: #991b1b; }
        
        .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ __('accountant.receiving_title') }}</h1>
        <p>{{ __('accountant.print_date') }}: {{ date('Y-m-d H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 40px;">#</th>
                <th>{{ __('accountant.user') }}</th>
                <th>{{ __('accountant.amount') }}</th>
                <th>{{ __('accountant.type') }}</th>
                <th>{{ __('accountant.cashbox') }}</th>
                <th>{{ __('accountant.date') }}</th>
                <th>{{ __('accountant.note') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $trx)
            <tr>
                <td>{{ $trx->id }}</td>
                <td style="text-align: right;">
                    <strong>{{ $trx->account->name ?? '-' }}</strong><br>
                    <span style="font-size: 10px; color: #64748b;">{{ $trx->account->code ?? '' }}</span>
                </td>
                <td dir="ltr" style="font-family: monospace; font-weight: bold;">
                    {{ number_format($trx->amount, 2) }} {{ $trx->currency->currency_type ?? '' }}
                </td>
                <td>
                    <span class="badge {{ $trx->invoice_type == 'payment' ? 'bg-red' : 'bg-green' }}">
                        {{ $trx->invoice_type }}
                    </span>
                </td>
                <td>{{ $trx->cashbox->name ?? '-' }}</td>
                <td>{{ $trx->created_at->format('Y-m-d H:i') }}</td>
                <td style="text-align: right; max-width: 150px;">{{ $trx->note ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        {{ __('accountant.generated_by_system') }}
    </div>

    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>