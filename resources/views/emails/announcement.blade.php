@extends('emails._layout', ['title' => $title])

@section('content')
    <p style="margin:0 0 8px;">Hello, <strong>{{ $recipientName }}</strong>,</p>
    <p style="margin:0 0 18px;color:#5e3322;">You have a new announcement from the management:</p>

    <h2 style="margin:18px 0 14px;font-family:Georgia,serif;font-size:22px;color:#2a140b;border-left:4px solid #f5b94e;padding-left:14px;">{{ $title }}</h2>

    <div style="font-size:14px;color:#3d1d10;line-height:1.7;white-space:pre-line;background:#faf7f2;border-radius:8px;padding:18px;border:1px solid #e8d9c8;">{{ $body }}</div>

    <hr style="border:none;border-top:1px solid #e8d9c8;margin:26px 0;">
    <p style="font-size:13px;color:#9a6a4f;margin:0;">If you have questions, please contact the management office directly.</p>
@endsection
