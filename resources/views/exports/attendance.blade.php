<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 6px; font-size: 12px; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>

<h2>Attendance Report</h2>
<p>From: {{ $from }} — To: {{ $to }}</p>

<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Employee</th>
        <th>Date</th>
        <th>Status</th>
        <th>Clock In</th>
        <th>Clock Out</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($data as $i => $row)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $row->employee->first_name }} {{ $row->employee->last_name }}</td>
            <td>{{ $row->attendance_date }}</td>
            <td>{{ strtoupper($row->status) }}</td>
            <td>{{ $row->clock_in }}</td>
            <td>{{ $row->clock_out }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
