<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Leave Request Status</title>
</head>
<body style="font-family: Arial, sans-serif; background-color:#f4f6f8; padding:20px;">

<div style="max-width:600px; margin:auto; background:#ffffff; padding:25px; border-radius:8px;">
    <h2 style="color:#333;">Leave Request Update</h2>

    <p>Hello <strong>{{ $employee->name }}</strong>,</p>

    <p>
        Your leave request has been
        <strong style="color:
                {{ $leave->status === 'approved' ? '#16a34a' : '#dc2626' }}">
            {{ strtoupper($leave->status) }}
        </strong>.
    </p>

    <hr>

    <h4>Leave Details</h4>
    <p>
        <strong>Start Date:</strong> {{ $leave->start_date }} <br>
        <strong>End Date:</strong> {{ $leave->end_date }} <br>
        <strong>Resume Date:</strong> {{ $leave->resume_date }}
    </p>

    <p>
        <strong>Reason:</strong><br>
        {{ $leave->reason }}
    </p>

    @if($leave->admin_note)
        <p>
            <strong>HR/Admin Note:</strong><br>
            {{ $leave->admin_note }}
        </p>
    @endif

    <p>
        <strong>Reviewed By:</strong>
        {{ $reviewer?->name ?? 'HR Department' }}
    </p>

    <hr>

    <p style="font-size:14px; color:#555;">
        You can log in to the HR portal to view more details.
    </p>

    <p>
        Regards,<br>
        <strong>Maimalee HR Team</strong>
    </p>
</div>

</body>
</html>
