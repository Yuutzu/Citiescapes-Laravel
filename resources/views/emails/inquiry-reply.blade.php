<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $emailSubject }}</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f9fafb;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            max-width: 560px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
        }

        .header {
            background: #283246;
            padding: 28px 32px;
            text-align: center;
        }

        .header .logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .header .cs {
            background: #fff;
            color: #283246;
            font-weight: 700;
            font-size: 14px;
            border-radius: 8px;
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .header h1 {
            color: #fff;
            font-size: 20px;
            font-weight: 700;
            margin: 0;
            line-height: 1;
        }

        .body {
            padding: 32px;
        }

        .greeting {
            font-size: 15px;
            color: #374151;
            margin-bottom: 16px;
        }

        .message-box {
            background: #f8fafc;
            border-left: 4px solid #283246;
            border-radius: 6px;
            padding: 18px;
            font-size: 14px;
            color: #374151;
            line-height: 1.7;
            white-space: pre-line;
        }

        .signature {
            margin-top: 24px;
            font-size: 14px;
            color: #374151;
        }

        .signature .name {
            font-weight: 700;
            color: #283246;
        }

        .divider {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 28px 0;
        }

        .footer {
            padding: 0 32px 28px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="header">
            <div class="logo">
                <span class="cs">CS</span>
                <h1>Citiescapes</h1>
            </div>
        </div>
        <div class="body">
            <p class="greeting">Hello, <strong>{{ $recipientName }}</strong>,</p>
            <p style="font-size:14px;color:#6b7280;">Thank you for your interest in Citiescapes. Here is our reply to your inquiry:</p>
            <div class="message-box">{{ $body }}</div>
            <div class="signature">
                <p style="margin:0;">Best regards,</p>
                <p style="margin:4px 0 0;" class="name">{{ $senderName }}</p>
                <p style="margin:0;font-size:13px;color:#6b7280;">Citiescapes Apartment Rental</p>
            </div>
            <hr class="divider">
            <p style="font-size:13px;color:#6b7280;">If you have any further questions, simply reply to this email or call us directly.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Citiescapes Apartment Rental &bull; Remedios St., Bajada, Davao City
        </div>
    </div>
</body>

</html>
