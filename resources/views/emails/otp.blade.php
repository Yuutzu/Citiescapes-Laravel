@extends('emails._layout', ['title' => 'Citiescapes — Account Verification'])

@section('content')
    <h2 style="margin:0 0 14px;font-family:Georgia,serif;font-size:22px;color:#2a140b;">Account Verification</h2>
    <p style="margin:0 0 12px;">Hello <strong>{{ $name }}</strong>,</p>
    <p style="margin:0 0 18px;color:#5e3322;">Your One-Time Password (OTP) for account activation is:</p>

    <div style="text-align:center;margin:24px 0;">
        <span style="display:inline-block;font-size:32px;letter-spacing:10px;font-weight:700;color:#2a140b;background:#faf3e3;border:2px solid #f5b94e;padding:16px 28px;border-radius:10px;font-family:'Courier New',monospace;">{{ $otpCode }}</span>
    </div>

    <p style="margin:0 0 8px;">This code expires in <strong style="color:#7a3b21;">{{ $expiryMinutes }} minutes</strong>.</p>
    <p style="margin:18px 0 0;font-size:13px;color:#9a6a4f;">If you did not request this, please ignore this email.</p>
@endsection
