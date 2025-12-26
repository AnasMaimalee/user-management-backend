<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Payslip - Maimalee HR</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 640px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        .header {
            background: linear-gradient(135deg, #1677ff, #4096ff);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .logo {
            font-size: 32px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .header h1 {
            margin: 12px 0 0;
            font-size: 28px;
            font-weight: 600;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 24px;
            color: #1e293b;
        }
        .highlight-box {
            background: #f0f9ff;
            border-left: 5px solid #1677ff;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
        }
        .net-salary {
            font-size: 24px;
            font-weight: 700;
            color: #1677ff;
            text-align: center;
            margin: 20px 0;
        }
        .button {
            display: block;
            width: fit-content;
            margin: 30px auto 0;
            background: #1677ff;
            color: white;
            padding: 16px 36px;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 17px;
            box-shadow: 0 4px 12px rgba(22, 119, 255, 0.3);
            transition: all 0.3s;
        }
        .button:hover {
            background: #0d5bdd;
            transform: translateY(-2px);
        }
        .footer {
            background: #f1f5f9;
            padding: 30px;
            text-align: center;
            color: #64748b;
            font-size: 14px;
        }
        .footer strong {
            color: #1e293b;
            font-weight: 600;
        }
        .attachment-note {
            text-align: center;
            margin: 30px 0;
            padding: 16px;
            background: #ecfdf5;
            border-radius: 10px;
            color: #065f46;
            font-size: 15px;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Header with Maimalee HR Branding -->
    <div class="header">
        <div class="logo">Maimalee HR</div>
        <h1>Your Payslip is Ready</h1>
    </div>

    <!-- Main Content -->
    <div class="content">
        <p class="greeting">
            Hello <strong>{{ $payroll->employee->first_name }}</strong>,
        </p>

        <p>
            Great news! Your payslip for <strong>{{ \Carbon\Carbon::createFromDate($payroll->year, $payroll->month, 1)->format('F Y') }}</strong> has been processed.
        </p>

        <!-- Highlight Net Salary -->
        <div class="net-salary">
            Net Salary: ₦{{ number_format($payroll->net_salary, 2) }}
        </div>

        <!-- Attachment Note -->
        <div class="attachment-note">
            <strong>📄 Your detailed payslip is attached as a PDF</strong><br>
            Please open the attachment to view full breakdown including basic salary, allowances, and deductions.
        </div>

        <!-- Call to Action -->
        <a href="{{ url('/payslips/my') }}" class="button">
            View All My Payslips
        </a>

        <p style="margin-top: 40px; color: #64748b;">
            Thank you for your continued dedication and hard work.<br>
            We're proud to have you on the team!
        </p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>Maimalee HR Team</strong></p>
        <p>Empowering better work experiences</p>
        <p style="margin-top: 20px; font-size: 13px;">
            This is an automated notification • Please do not reply to this email
        </p>
    </div>
</div>
</body>
</html>
