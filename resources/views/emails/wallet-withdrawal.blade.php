<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Withdrawal Request {{ ucfirst($transaction->status) }}</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; padding: 20px; }
        .container { max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .header { background: {{ $transaction->status === 'approved' ? '#10b981' : '#ef4444' }}; color: white; padding: 20px; text-align: center; border-radius: 12px 12px 0 0; margin: -30px -30px 30px; }
        .status { font-size: 28px; font-weight: bold; }
        .amount { font-size: 36px; font-weight: bold; color: #1677ff; }
        .note { background: #f3f4f6; padding: 15px; border-radius: 8px; margin: 20px 0; font-style: italic; }
        .button { display: inline-block; background: #1677ff; color: white; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; }
        .footer { text-align: center; margin-top: 40px; color: #666; font-size: 14px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1 class="status">Withdrawal {{ ucfirst($transaction->status) }}</h1>
    </div>

    <p>Hello <strong>{{ $transaction->wallet->employee->first_name }}</strong>,</p>

    <p>Your withdrawal request of <span class="amount">₦{{ number_format($transaction->amount, 2) }}</span> has been <strong>{{ $transaction->status }}</strong>.</p>

    <p><strong>Reason you provided:</strong><br>
        <em>{{ $transaction->description }}</em>
    </p>

    @if($note)
        <div class="note">
            <strong>HR Note:</strong><br>
            {{ $note }}
        </div>
    @endif

    @if($transaction->status === 'approved')
        <p>The funds will be transferred to your account soon.</p>
    @else
        <p>You can submit a new request if needed.</p>
    @endif

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ url('/wallet/my') }}" class="button">View My Wallet</a>
    </div>

    <div class="footer">
        <p>Best regards,<br><strong>Maimalee HR Team</strong></p>
    </div>
</div>
</body>
</html>
