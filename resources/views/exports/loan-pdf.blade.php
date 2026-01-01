<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #4f46e5;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #4f46e5;
            margin: 0;
            font-size: 28px;
        }
        .header p {
            margin: 10px 0 0;
            color: #666;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 12px;
        }
        th {
            background-color: #4f46e5;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #ddd;
            vertical-align: top;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .amount {
            font-weight: bold;
            color: #1e40af;
        }
        .status-approved {
            color: #16a34a;
            font-weight: bold;
        }
        .status-rejected {
            color: #dc2626;
            font-weight: bold;
        }
        .status-completed {
            color: #2563eb;
            font-weight: bold;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #888;
            font-size: 11px;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>{{ $title }}</h1>
    <p>Generated on: {{ $generated_at }}</p>
</div>

<table>
    <thead>
    <tr>
        <th width="5%">#</th>
        <th width="20%">Employee</th>
        <th width="15%">Department</th>
        <th width="12%" class="text-right">Amount</th>
        <th width="8%" class="text-center">Months</th>
        <th width="12%" class="text-right">Monthly Deduction</th>
        <th width="12%" class="text-right">Paid</th>
        <th width="12%" class="text-right">Remaining</th>
        <th width="8%" class="text-center">Status</th>
        <th width="15%">Reason</th>
        <th width="11%">Updated At</th>
    </tr>
    </thead>
    <tbody>
    @forelse($loans as $index => $loan)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>
                <strong>{{ $loan->employee->first_name }} {{ $loan->employee->last_name }}</strong><br>
                <small>{{ $loan->employee->employee_code }}</small>
            </td>
            <td>{{ $loan->employee->department->name ?? '—' }}</td>
            <td class="text-right amount">₦{{ number_format($loan->amount) }}</td>
            <td class="text-center">{{ $loan->months }}</td>
            <td class="text-right">₦{{ number_format($loan->monthly_deduction) }}</td>
            <td class="text-right amount">₦{{ number_format($loan->paid_amount) }}</td>
            <td class="text-right">₦{{ number_format($loan->remaining_amount) }}</td>
            <td class="text-center">
                        <span class="status-{{ $loan->status }}">
                            {{ ucfirst($loan->status) }}
                        </span>
            </td>
            <td>{{ $loan->reason ?? '—' }}</td>
            <td>{{ $loan->updated_at->format('d M Y') }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="11" class="text-center">No loan records found.</td>
        </tr>
    @endforelse
    </tbody>
</table>

<div class="footer">
    <p>Loan Management System • Page generated on {{ $generated_at }}</p>
</div>
</body>
</html>
