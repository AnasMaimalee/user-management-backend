<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payslip - {{ $payroll->employee->first_name }} {{ $payroll->employee->last_name }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f4f4f4; }
        .container { max-width: 700px; margin: 30px auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .header { background: #1677ff; color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; }
        .content { padding: 30px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8fafc; font-weight: bold; }
        .total { font-size: 18px; font-weight: bold; color: #1677ff; }
        .footer { text-align: center; padding: 20px; background: #f1f5f9; color: #666; font-size: 14px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Payslip</h1>
        <p>{{ \Carbon\Carbon::createFromDate($payroll->year, $payroll->month, 1)->format('F Y') }}</p>
    </div>

    <div class="content">
        <p><strong>Employee:</strong> {{ $payroll->employee->first_name }} {{ $payroll->employee->last_name }}</p>
        <p><strong>Employee Code:</strong> {{ $payroll->employee->employee_code ?? 'N/A' }}</p>
        <p><strong>Department:</strong> {{ $payroll->employee->department->name ?? 'N/A' }}</p>

        <table>
            <tr>
                <th>Earnings</th>
                <th>Amount (₦)</th>
            </tr>
            <tr>
                <td>Basic Salary</td>
                <td>{{ number_format($payroll->basic_salary, 2) }}</td>
            </tr>
            <tr>
                <td>Allowances</td>
                <td>{{ number_format($payroll->allowances, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Gross Salary</strong></td>
                <td><strong>{{ number_format($payroll->basic_salary + $payroll->allowances, 2) }}</strong></td>
            </tr>

            <tr>
                <th>Deductions</th>
                <th>Amount (₦)</th>
            </tr>
            <tr>
                <td>Regular Deductions</td>
                <td>{{ number_format($payroll->deductions, 2) }}</td>
            </tr>
            <tr>
                <td>Savings Deduction</td>
                <td>{{ number_format($payroll->savings_deduction, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Total Deductions</strong></td>
                <td><strong>{{ number_format($payroll->deductions + $payroll->savings_deduction, 2) }}</strong></td>
            </tr>

            <tr class="total">
                <td><strong>Net Salary</strong></td>
                <td><strong>₦{{ number_format($payroll->net_salary, 2) }}</strong></td>
            </tr>
        </table>

        <p>Thank you for your hard work this month!</p>
    </div>

    <div class="footer">
        <p><strong>Maimalee HR System</strong></p>
        <p>This is an automated payslip. Please do not reply.</p>
    </div>
</div>
</body>
</html>
