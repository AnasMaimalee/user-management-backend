<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Maimalee HR Portal</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .button { background: #1677ff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: bold; }
        code { background: #f0f0f0; padding: 4px 8px; border-radius: 4px; font-size: 1.1em; }
    </style>
</head>
<body>
<div class="container">
    <h1>Welcome, {{ $employee->first_name }} {{ $employee->last_name }}!</h1>

    <p>You've been added to the <strong>Maimalee HR Management System</strong>.</p>

    <p>You can now log in to view your payslips, request leave, and more.</p>

    <div style="background:#f0f9ff;padding:20px;border-left:4px solid #1677ff;margin:20px 0;">
        <h2>Your Login Credentials</h2>
        <p><strong>Email:</strong> {{ $employee->email }}</p>
        <p><strong>Temporary Password:</strong> <code>{{ $password }}</code></p>
    </div>

    <p>
        <a href="{{ $loginUrl }}" class="button">Log In Now</a>
    </p>

    <p><strong>Important:</strong> Please change your password after logging in for the first time.</p>

    <p>Best regards,<br><strong>Maimalee HR Team</strong></p>
</div>
</body>
</html>
