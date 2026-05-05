<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update on your {{ ucfirst($type) }}: {{ $subject }}</title>
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

        .status-pill {
            display: inline-block;
            padding: 5px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-in_progress {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-resolved {
            background: #dcfce7;
            color: #166534;
        }

        .subject {
            font-size: 20px;
            font-weight: 700;
            color: #283246;
            margin: 14px 0 8px;
        }

        .response-box {
            background: #f0f4ff;
            border-left: 4px solid #283246;
            border-radius: 6px;
            padding: 16px;
            font-size: 14px;
            color: #374151;
            line-height: 1.7;
            white-space: pre-line;
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
            <p style="font-size:15px;color:#374151;">Hello, <strong>{{ $tenantName }}</strong>,</p>
            <p style="font-size:14px;color:#6b7280;">The management has responded to your {{ $type }}.</p>
            <h2 class="subject">{{ $subject }}</h2>
            <p style="margin-bottom:8px;">
                Status updated to:
                <span
                    class="status-pill status-{{ str_replace(' ', '_', $status) }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
            </p>
            @if($adminResponse)
                <p style="font-size:13px;font-weight:600;color:#374151;margin-top:20px;margin-bottom:8px;">Response from
                    Management:</p>
                <div class="response-box">{{ $adminResponse }}</div>
            @endif
            <hr class="divider">
            <p style="font-size:13px;color:#6b7280;">Log in to your tenant portal to view the full details.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Citiescapes Apartment Rental &bull; Remedios St., Bajada, Davao City
        </div>
    </div>
</body>

</html>