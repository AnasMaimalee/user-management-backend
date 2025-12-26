<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reset Your Password</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background:#f0f9ff; padding:20px; }
        .container { max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); text-align: center; }
        .header { margin-bottom: 30px; }
        .header h1 { color: #1677ff; font-size: 28px; margin: 0; }
        .content { margin: 30px 0; }
        .button { display: inline-block; background: #1677ff; color: white; padding: 16px 32px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 18px; margin: 20px 0; }
        .warning { background: #fffbeb; padding: 15px; border-radius: 8px; border-left: 4px solid #f59e0b; margin: 25px 0; font-size: 14px; color: #92400e; }
        .footer { margin-top: 40px; color: #666; font-size: 14px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Password Reset Request</h1>
    </div>

    <div class="content">
        <p>Hello <strong>{{ $employee->first_name ?? $employee->name }}</strong>,</p>

        <p>We received a request to reset the password for your Maimalee HR Portal account.</p>

        <p>Click the button below to set a new password:</p>

        <a href="{{ $resetUrl }}" class="button">Reset Password</a>

        <p>This link will expire in 60 minutes for security reasons.</p>

        <div class="warning">
            <strong>Important:</strong> If you did not request a password reset, please ignore this email or contact HR immediately. Your account is secure.
        </div>

        <p>Thank you,<br><strong>Maimalee HR Team</strong></p>
    </div>

    <div class="footer">
        <p>This is an automated message. Please do not reply directly.</p>
    </div>
</div>
</body>
</html>
