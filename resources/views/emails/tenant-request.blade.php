@extends('emails._layout', ['title' => 'New ' . ucfirst($type) . ': ' . $itemSubject])

@php
    $isComplaint = $type === 'complaint';
    $badgeBg = $isComplaint ? '#fee2e2' : '#faf3e3';
    $badgeFg = $isComplaint ? '#991b1b' : '#7a3b21';
    $badgeBorder = $isComplaint ? '#fca5a5' : '#f5b94e';
@endphp

@section('content')
    <p style="margin:0 0 14px;font-size:15px;color:#3d1d10;">A tenant has submitted a new <strong>{{ $type }}</strong>.</p>

    <span style="display:inline-block;padding:5px 14px;border-radius:999px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;background:{{ $badgeBg }};color:{{ $badgeFg }};border:1px solid {{ $badgeBorder }};">{{ ucfirst($type) }}</span>

    <h2 style="margin:14px 0 10px;font-family:Georgia,serif;font-size:20px;color:#2a140b;">{{ $itemSubject }}</h2>

    <div style="background:#faf7f2;border-left:4px solid #7a3b21;border-radius:8px;padding:16px;font-size:14px;color:#3d1d10;line-height:1.7;white-space:pre-line;">{{ $body }}</div>

    <p style="margin:20px 0 0;font-size:13px;color:#7a3b21;">Submitted by: <strong style="color:#2a140b;">{{ $tenantName }}</strong></p>

    <hr style="border:none;border-top:1px solid #e8d9c8;margin:26px 0;">
    <p style="font-size:13px;color:#9a6a4f;margin:0;">Log in to the admin panel to respond to this {{ $type }}.</p>
@endsection
