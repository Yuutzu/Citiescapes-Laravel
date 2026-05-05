<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New {{ ucfirst($type) }}: {{ $subject }}</title>
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

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .badge-request {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-complaint {
            background: #fee2e2;
            color: #991b1b;
        }

        .subject {
            font-size: 20px;
            font-weight: 700;
            color: #283246;
            margin: 14px 0 8px;
        }

        .message-box {
            background: #f8fafc;
            border-left: 4px solid #283246;
            border-radius: 6px;
            padding: 16px;
            font-size: 14px;
            color: #374151;
            line-height: 1.7;
            white-space: pre-line;
        }

        .meta {
            font-size: 13px;
            color: #6b7280;
            margin-top: 20px;
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
            <p style="font-size:15px;color:#374151;">A tenant has submitted a new <strong>{{ $type }}</strong>.</p>
            <span class="badge badge-{{ $type }}">{{ ucfirst($type) }}</span>
            <h2 class="subject">{{ $subject }}</h2>
            <div class="message-box">{{ $body }}</div>
            <p class="meta">Submitted by: <strong>{{ $tenantName }}</strong></p>
            <hr class="divider">
            <p style="font-size:13px;color:#6b7280;">Log in to the admin panel to respond to this {{ $type }}.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Citiescapes Apartment Rental &bull; Remedios St., Bajada, Davao City
        </div>
    </div>
</body>

</html>