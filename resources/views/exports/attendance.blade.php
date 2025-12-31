<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: DejaVu Sans; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; }
    </style>
</head>
<body>

<h3>Attendance Report</h3>
<p>From {{ $from }} to {{ $to }}</p>

<table>
    <thead>
    <tr>
        <th>Employee</th>
        <th>Date</th>
        <th>Status</th>
        <th>Worked (min)</th>
        <th>Late (min)</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $row)
        <tr>
            <td>{{ $row->employee->name }}</td>
            <td>{{ $row->attendance_date }}</td>
            <td>{{ ucfirst($row->status) }}</td>
            <td>{{ $row->worked_minutes }}</td>
            <td>{{ $row->late_minutes }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
