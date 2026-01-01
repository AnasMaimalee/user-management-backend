<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Request {{ ucfirst($status) }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
            color: #334155;
            line-height: 1.6;
        }
        .container {
            max-width: 640px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border-left: 8px solid {{ $status === 'approved' ? '#16a34a' : '#dc2626' }};
        }
        .header {
            background: {{ $status === 'approved' ? '#f0fdf4' : '#fef2f2' }};
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            color: {{ $status === 'approved' ? '#16a34a' : '#dc2626' }};
        }
        .status-badge {
            display: inline-block;
            margin-top: 16px;
            padding: 10px 24px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 50px;
            color: #fff;
            background-color: {{ $status === 'approved' ? '#16a34a' : '#dc2626' }};
        }
        .content {
            padding: 40px 32px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 24px;
        }
        .details-box {
            background-color: #f8fafc;
            border-radius: 12px;
            padding: 28px;
            margin: 28px 0;
            border: 1px solid #e2e8f0;
        }
        .details-box h3 {
            margin: 0 0 20px 0;
            font-size: 20px;
            color: #1e293b;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 16px;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: 600;
            color: #475569;
        }
        .value {
            color: #1e293b;
            text-align: right;
        }
        .admin-note {
            background-color: #fffbeb;
            border-left: 5px solid #f59e0b;
            padding: 20px;
            border-radius: 8px;
            margin: 28px 0;
            font-style: italic;
            color: #92400e;
        }
        .admin-note strong {
            font-style: normal;
            color: #b45309;
        }
        .message {
            font-size: 16px;
            margin: 28px 0;
            padding: 20px;
            background-color: {{ $status === 'approved' ? '#f0fdf4' : '#fef2f2' }};
            border-radius: 12px;
            border-left: 4px solid {{ $status === 'approved' ? '#16a34a' : '#dc2626' }};
        }
        .cta {
            text-align: center;
            margin: 36px 0;
        }
        .button {
            display: inline-block;
            padding: 14px 32px;
            background-color: #4f46e5;
            color: #ffffff;
            font-weight: bold;
            font-size: 16px;
            text-decoration: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
            transition: all 0.3s;
        }
        .footer {
            background-color: #f1f5f9;
            padding: 30px;
            text-align: center;
            font-size: 14px;
            color: #64748b;
        }
        .footer strong {
            color: #1e293b;
        }
        @media (max-width: 600px) {
            .container {
                margin: 20px;
                border-radius: 12px;
            }
            .header, .content {
                padding: 24px;
            }
        }
    </style>
</head>
<body>

<div class="container">

    <!-- Header -->
    <div class="header">
        <h1>Loan Request {{ ucfirst($status) }}</h1>
        <div class="status-badge">
            {{ strtoupper($status) }}
        </div>
    </div>

    <!-- Main Content -->
    <div class="content">
        <p class="greeting">
            Hello <strong>{{ $loan->employee->first_name }} {{ $loan->employee->last_name }}</strong>,
        </p>

        <p>
            We have reviewed your loan application and wanted to update you on the decision.
        </p>

        <!-- Loan Details -->
        <div class="details-box">
            <h3>Loan Application Details</h3>

            <div class="detail-row">
                <span class="label">Requested Amount</span>
                <span class="value">₦{{ number_format($loan->amount, 2) }}</span>
            </div>

            <div class="detail-row">
                <span class="label">Repayment Period</span>
                <span class="value">{{ $loan->months }} months</span>
            </div>

            @if($status === 'approved')
                <div class="detail-row">
                    <span class="label">Monthly Deduction</span>
                    <span class="value">₦{{ number_format($loan->monthly_deduction, 2) }}</span>
                </div>

                <div class="detail-row">
                    <span class="label">Current Balance</span>
                    <span class="value">₦{{ number_format($loan->remaining_amount, 2) }}</span>
                </div>

                @if($loan->approved_by)
                    <div class="detail-row">
                        <span class="label">Approved By</span>
                        <span class="value">{{ $loan->approver?->name ?? 'HR Administrator' }}</span>
                    </div>
                @endif
            @endif

            <div class="detail-row">
                <span class="label">Reason Provided</span>
                <span class="value" style="text-align: left; max-width: 60%;">{{ $loan->reason }}</span>
            </div>
        </div>

        <!-- Admin Note -->
        @if($loan->admin_note)
            <div class="admin-note">
                <p><strong>Message from HR:</strong><br>{{ $loan->admin_note }}</p>
            </div>
        @endif

        <!-- Status Message -->
        <div class="message">
            @if($status === 'approved')
                <strong>Congratulations!</strong> Your loan has been approved. The amount has been credited to your wallet, and monthly deductions will begin accordingly.
            @else
                <strong>We regret to inform you</strong> that your loan request has been declined at this time. Please feel free to contact HR for any clarification or reapply when circumstances improve.
            @endif
        </div>

        <!-- Call to Action -->
        <div class="cta">
            <a href="{{ config('app.frontend_url', 'http://localhost:3001') }}/login" class="button">
                View My Loans in Portal
            </a>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Best regards,</p>
        <p><strong>Maimalee HR & Finance Team</strong></p>
        <p>This is an automated notification • Please do not reply to this email</p>
    </div>

</div>

</body>
</html>
