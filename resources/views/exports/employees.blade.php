<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; margin: 20mm; }
        h1 { font-size: 18px; text-align: center; margin-bottom: 10mm; }
        .date { text-align: center; font-size: 11px; color: #666; margin-bottom: 15mm; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
<h1>{{ $title }}</h1>
<p class="date">Generated on: {{ $date }}</p>

@if($employees->isEmpty())
    <p style="text-align: center;">No employees found.</p>
@else
    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>Code</th>
            <th>Name</th>
            <th>Email</th>
            <th>Department</th>
            <th>Rank</th>
            <th>Branch</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        @foreach($employees as $index => $employee)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $employee->employee_code ?? '—' }}</td>
                <td>{{ $employee->first_name }} {{ $employee->last_name }}</td>
                <td>{{ $employee->email ?? '—' }}</td>
                <td>{{ $employee->department?->name ?? '—' }}</td>
                <td>{{ $employee->rank?->name ?? '—' }}</td>
                <td>{{ $employee->branch?->name ?? '—' }}</td>
                <td>{{ ucfirst($employee->status) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif
</body>
</html>
