<!DOCTYPE html>
<html>
<head>
    <title>Reservations Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #dddddd; text-align: left; padding: 8px; }
        th { background-color: #f2f2f2; }
        .text-center { text-align: center; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
        .date { font-size: 12px; color: #555; }
    </style>
</head>
<body>
    <div class="title">Reservations Export Report</div>
    <div class="date">Generated on: {{ now()->format('F j, Y, g:i a') }}</div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Room</th>
                <th>Date & Time</th>
                <th>Hrs</th>
                <th>Status</th>
                <th>Returned</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reservations as $res)
                <tr>
                    <td>{{ $res->id }}</td>
                    <td>{{ $res->user->name ?? 'N/A' }}</td>
                    <td>{{ $res->room->name ?? 'N/A' }}</td>
                    <td>{{ $res->usage_date ? $res->usage_date->format('M d, Y H:i') : '' }}</td>
                    <td class="text-center">{{ $res->duration_hours }}</td>
                    <td>{{ strtoupper($res->status) }}</td>
                    <td>{{ $res->returned_at ? $res->returned_at->format('M d, Y') : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>