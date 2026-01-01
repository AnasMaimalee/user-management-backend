<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payroll Report - {{ $month }} {{ $year }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; margin: 40px; }
        .header { text-align: center; margin-bottom: 40px; }
        .header h1 { color: #1e293b; margin: 0; }
        .header p { color: #64748b; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #e2e8f0; padding: 12px; text-align: left; }
        th { background-color: #f8fafc; color: #334155; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 50px; text-align: center; color: #94a3b8; font-size: 12px; }
    </style>
</head>
<body>
<div class="header">
    <h1>Maimalee HR Payroll Report</h1>
    <p>{{ $month }} {{ $year }}</p>
    <p>Generated on: {{ $generated_at }}</p>
</div>

<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Employee</th>
        <th>Code</th>
        <th>Basic Salary</th>
        <th>Allowances</th>
        <th>Deductions</th>
        <th>Savings</th>
        <th>Net Salary</th>
        <th>Status</th>
    </tr>
    </thead>
    <tbody>
    @foreach($payrolls as $index => $payroll)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>{{ $payroll->employee->first_name }} {{ $payroll->employee->last_name }}</td>
            <td>{{ $payroll->employee->employee_code ?? 'N/A' }}</td>
            <td class="text-right">₦{{ number_format($payroll->basic_salary, 2) }}</td>
            <td class="text-right">₦{{ number_format($payroll->allowances, 2) }}</td>
            <td class="text-right">₦{{ number_format($payroll->deductions, 2) }}</td>
            <td class="text-right">₦{{ number_format($payroll->savings_deduction, 2) }}</td>
            <td class="text-right font-bold">₦{{ number_format($payroll->net_salary, 2) }}</td>
            <td class="text-center">{{ ucfirst($payroll->status) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="footer">
    <p>© {{ date('Y') }} Maimalee HR System • All rights reserved</p>
</div>
</body>
</html>
