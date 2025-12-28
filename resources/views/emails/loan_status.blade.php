<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Request {{ ucfirst($loan->status) }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            color: #374151;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-left: 6px solid #16a34a;
        }
        .container.rejected {
            border-left-color: #dc2626;
        }
        .header {
            text-align: center;
            padding: 32px 20px;
            font-size: 26px;
            font-weight: bold;
        }
        .header.approved {
            color: #16a34a;
        }
        .header.rejected {
            color: #dc2626;
        }
        .content {
            padding: 30px 24px;
        }
        .content p {
            font-size: 16px;
            line-height: 1.6;
            margin: 14px 0;
        }
        .badge {
            display: inline-block;
            padding: 6px 14px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 999px;
            color: #fff;
        }
        .badge.approved {
            background-color: #16a34a;
        }
        .badge.rejected {
            background-color: #dc2626;
        }
        .details {
            background: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            margin: 24px 0;
        }
        .details h3 {
            margin: 0 0 12px;
            font-size: 18px;
            color: #111827;
        }
        .details p {
            margin: 6px 0;
            font-size: 15px;
        }
        .admin-note {
            background-color: #fff7ed;
            border-left: 4px solid #f59e0b;
            padding: 16px;
            border-radius: 6px;
            margin-top: 20px;
        }
        .admin-note p {
            margin: 0;
            font-style: italic;
            color: #92400e;
        }
        .button {
            display: inline-block;
            margin-top: 24px;
            padding: 12px 28px;
            background-color: #2563eb;
            color: #ffffff;
            text-decoration: none;
            font-weight: bold;
            border-radius: 8px;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
        }
    </style>
</head>
<body>

<div class="container {{ $loan->status === 'rejected' ? 'rejected' : '' }}">

    <!-- Header -->
    <div class="header {{ $loan->status }}">
        Loan Request {{ ucfirst($loan->status) }}
    </div>

    <!-- Content -->
    <div class="content">
        <p>Hello <strong>{{ $loan->employee->first_name }}</strong>,</p>

        <p>
            Your loan request has been
            <span class="badge {{ $loan->status }}">{{ strtoupper($loan->status) }}</span>.
        </p>

        <!-- Loan Details -->
        <div class="details">
            <h3>Loan Details</h3>
            <p><strong>Amount:</strong> ₦{{ number_format($loan->amount, 2) }}</p>
            <p><strong>Duration:</strong> {{ $loan->months }} months</p>
            <p><strong>Reason:</strong> {{ $loan->reason }}</p>

            @if($loan->status === 'approved')
                <p><strong>Monthly Deduction:</strong> ₦{{ number_format($loan->monthly_deduction, 2) }}</p>
                <p><strong>Remaining Balance:</strong> ₦{{ number_format($loan->remaining_amount, 2) }}</p>
                <p><strong>Approved By:</strong> {{ $loan->approver->name ?? 'Admin' }}</p>
            @endif
        </div>

        <!-- Admin Note -->
        @if($loan->admin_note)
            <div class="admin-note">
                <p><strong>Message from HR:</strong> {{ $loan->admin_note }}</p>
            </div>
        @endif

        <!-- Message -->
        <p>
            @if($loan->status === 'approved')
                The approved loan amount will be credited to your wallet, and repayments will be deducted monthly.
            @else
                If you believe this decision needs clarification, please contact the HR department.
            @endif
        </p>

        <!-- CTA -->
        <p style="text-align:center;">
            <a href="http://localhost:3001/login" class="button">Log in to HR Portal</a>
        </p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Best regards,</p>
        <p><strong>Maimalee HR Team</strong></p>
        <p>This is an automated email. Please do not reply.</p>
    </div>
</div>

</body>
</html>
