<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Request {{ ucfirst($leave->status) }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f3f4f6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding: 40px 20px;
            color: #fff;
        }
        .header.approved { background-color: #16a34a; }
        .header.rejected { background-color: #dc2626; }
        .header h1 { font-size: 28px; margin: 0; }
        .header p { font-size: 16px; margin-top: 8px; opacity: 0.9; }
        .content { padding: 30px 20px; }
        .content p { margin: 16px 0; font-size: 16px; line-height: 1.5; }
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            font-weight: bold;
            color: #fff;
            margin-left: 5px;
        }
        .badge.approved { background-color: #16a34a; }
        .badge.rejected { background-color: #dc2626; }
        .details {
            background-color: #f9fafb;
            border-left: 4px solid #16a34a;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .details.rejected { border-left-color: #dc2626; }
        .details h3 { margin-top: 0; margin-bottom: 12px; font-size: 18px; }
        .details p { margin: 6px 0; font-size: 15px; }
        .admin-note {
            background-color: #fff7ed;
            border-left: 4px solid #f59e0b;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .admin-note p { margin: 0; font-style: italic; color: #b45309; }
        .button {
            display: inline-block;
            background-color: #2563eb;
            color: #fff;
            font-weight: bold;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            transition: background 0.2s;
            margin-top: 20px;
        }
        .button:hover { background-color: #1d4ed8; }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 14px;
            color: #6b7280;
            background-color: #f9fafb;
        }
        .footer p { margin: 4px 0; }
    </style>
</head>
<body>
<div class="container">
    <!-- Header -->
    <div class="header {{ $leave->status }}">
        <h1>Leave Request {{ ucfirst($leave->status) }}</h1>
        <p>Your request has been reviewed</p>
    </div>

    <!-- Content -->
    <div class="content">
        <p>Hello <strong>{{ $employee->first_name ?? $employee->name }}</strong>,</p>

        <p>
            Your leave request has been
            <span class="badge {{ $leave->status }}">{{ strtoupper($leave->status) }}</span>
        </p>

        <!-- Leave Details -->
        <div class="details {{ $leave->status }}">
            <h3>Leave Details</h3>
            <p><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($leave->start_date)->format('l, F j, Y') }}</p>
            <p><strong>End Date:</strong> {{ \Carbon\Carbon::parse($leave->end_date)->format('l, F j, Y') }}</p>
            <p><strong>Resume Date:</strong> {{ $leave->resume_date ? \Carbon\Carbon::parse($leave->resume_date)->format('l, F j, Y') : 'Next working day' }}</p>
            <p><strong>Reason:</strong> {{ $leave->reason }}</p>
        </div>

        <!-- Admin Note -->
        @if($leave->admin_note)
            <div class="admin-note">
                <p><strong>Message from HR:</strong> {{ $leave->admin_note }}</p>
            </div>
        @endif

        <!-- Message -->
        <p>
            @if($leave->status === 'approved')
                Enjoy your well-deserved time off! We look forward to having you back refreshed.
            @else
                If you have any questions regarding this decision, please feel free to contact HR.
            @endif
        </p>

        <!-- Login Button -->
        <p style="text-align:center;">
            <a href="{{ url('/login') }}" class="button">Log in to HR Portal</a>
        </p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Best regards,</p>
        <p><strong>Maimalee HR Team</strong></p>
        <p>This is an automated message — please do not reply directly.</p>
    </div>
</div>
</body>
</html>
