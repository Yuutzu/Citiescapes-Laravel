@extends('emails._layout', ['title' => 'Update on your ' . ucfirst($type) . ': ' . $itemSubject])

@php
    $statusKey = str_replace(' ', '_', $status);
    $palette = match($statusKey) {
        'pending'     => ['bg' => '#faf3e3', 'fg' => '#7a3b21', 'border' => '#f5b94e'],
        'in_progress' => ['bg' => '#fde8c4', 'fg' => '#92400e', 'border' => '#f59e0b'],
        'resolved'    => ['bg' => '#dcfce7', 'fg' => '#166534', 'border' => '#86efac'],
        default       => ['bg' => '#faf7f2', 'fg' => '#5e3322', 'border' => '#e8d9c8'],
    };
@endphp

@section('content')
    <p style="margin:0 0 8px;font-size:15px;">Hello, <strong>{{ $tenantName }}</strong>,</p>
    <p style="margin:0 0 16px;color:#5e3322;">The management has responded to your {{ $type }}.</p>

    <h2 style="margin:14px 0 12px;font-family:Georgia,serif;font-size:20px;color:#2a140b;">{{ $itemSubject }}</h2>

    <p style="margin:0 0 10px;">
        Status updated to:
        <span style="display:inline-block;padding:5px 14px;border-radius:999px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;background:{{ $palette['bg'] }};color:{{ $palette['fg'] }};border:1px solid {{ $palette['border'] }};">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
    </p>

    @if($adminResponse)
        <p style="font-size:13px;font-weight:600;color:#7a3b21;margin:22px 0 8px;text-transform:uppercase;letter-spacing:0.06em;">Response from Management</p>
        <div style="background:#faf7f2;border-left:4px solid #7a3b21;border-radius:8px;padding:16px;font-size:14px;color:#3d1d10;line-height:1.7;white-space:pre-line;">{{ $adminResponse }}</div>
    @endif

    <hr style="border:none;border-top:1px solid #e8d9c8;margin:26px 0;">
    <p style="font-size:13px;color:#9a6a4f;margin:0;">Log in to your tenant portal to view the full details.</p>
@endsection
