<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f9fafb; margin: 0; padding: 0; }
        .wrapper { max-width: 560px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #1e3a5f; padding: 28px 32px; text-align: center; }
        .header .logo { display: inline-flex; align-items: center; gap: 10px; }
        .header .cs { background: #fff; color: #1e3a5f; font-weight: 700; font-size: 14px; border-radius: 8px; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; }
        .header h1 { color: #fff; font-size: 20px; font-weight: 700; margin: 0; line-height: 1; }
        .body { padding: 32px; }
        .greeting { font-size: 15px; color: #374151; margin-bottom: 4px; }
        .announcement-title { font-size: 22px; font-weight: 700; color: #1e3a5f; margin: 16px 0 12px; }
        .announcement-body { font-size: 14px; color: #4b5563; line-height: 1.7; white-space: pre-line; }
        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 28px 0; }
        .footer { padding: 0 32px 28px; text-align: center; font-size: 12px; color: #9ca3af; }
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
            <p class="greeting">You have a new announcement from the management:</p>
            <h2 class="announcement-title">{{ $title }}</h2>
            <p class="announcement-body">{{ $body }}</p>
            <hr class="divider">
            <p style="font-size:13px;color:#6b7280;">If you have questions, please contact the management office directly.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Citiescapes Apartment Rental &bull; Remedios St., Bajada, Davao City
        </div>
    </div>
</body>
</html>
