@extends('emails._layout', ['title' => $emailSubject])

@section('content')
    <p style="margin:0 0 8px;">Hello, <strong>{{ $recipientName }}</strong>,</p>
    <p style="margin:0 0 20px;color:#5e3322;">Thank you for your interest in Citiescapes. Here is our reply to your inquiry:</p>

    <div style="background:#faf7f2;border-left:4px solid #7a3b21;border-radius:8px;padding:18px;font-size:14px;color:#3d1d10;line-height:1.7;white-space:pre-line;">{{ $body }}</div>

    <div style="margin-top:26px;font-size:14px;color:#3d1d10;">
        <p style="margin:0;">Best regards,</p>
        <p style="margin:4px 0 0;font-weight:700;color:#2a140b;">{{ $senderName }}</p>
        <p style="margin:0;font-size:13px;color:#7a3b21;">Citiescapes Apartment Rental</p>
    </div>

    <hr style="border:none;border-top:1px solid #e8d9c8;margin:26px 0;">
    <p style="font-size:13px;color:#9a6a4f;margin:0;">If you have any further questions, simply reply to this email or call us directly.</p>
@endsection
