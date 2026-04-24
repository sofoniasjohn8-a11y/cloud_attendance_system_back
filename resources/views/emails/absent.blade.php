<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 40px auto; padding: 30px; border: 1px solid #ddd; border-radius: 8px; }
        .header { background: #e74c3c; color: white; padding: 15px 20px; border-radius: 6px 6px 0 0; }
        .body { padding: 20px 0; }
        .footer { font-size: 12px; color: #999; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Absence Notification</h2>
        </div>
        <div class="body">
            <p>Dear <strong>{{ $user->name }}</strong>,</p>
            <p>Our records show that you were <strong>absent</strong> from the office on <strong>{{ $date }}</strong>.</p>
            <p>If this is incorrect or you have a valid reason, please contact your manager or HR department.</p>
            <p>Please ensure regular attendance going forward.</p>
        </div>
        <div class="footer">
            <p>This is an automated message from the Attendance Management System.</p>
        </div>
    </div>
</body>
</html>
