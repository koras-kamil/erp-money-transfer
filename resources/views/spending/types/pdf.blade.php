<!DOCTYPE html>
<html lang="ku" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'nrt', sans-serif; direction: rtl; text-align: right; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f3f4f6; padding: 8px; border: 1px solid #e5e7eb; font-weight: bold; }
        td { padding: 8px; border: 1px solid #e5e7eb; }
        .header { text-align: center; border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-bottom: 20px; }
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
                <th>#</th>
                <th>Code</th>
                <th>Name</th>
                <th>Group</th>
                <th>Branch</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            @foreach($types as $type)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $type->code }}</td>
                <td>{{ $type->name }}</td>
                <td>{{ $type->group->name ?? '-' }}</td>
                <td>{{ $type->branch->name ?? '-' }}</td>
                <td>{{ $type->note }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">Page {PAGENO} of {nbpg}</div>
</body>
</html>