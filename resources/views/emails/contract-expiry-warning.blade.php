@extends('emails._layout', ['title' => 'Citiescapes — Lease Expiry Notice'])

@php
    $isUrgent = $daysRemaining === 7;
    $headerLine = $isUrgent ? 'Urgent: Your Lease Expires in 7 Days' : 'Your Lease Expires in 30 Days';
    $accentBg = $isUrgent ? '#fee2e2' : '#faf3e3';
    $accentFg = $isUrgent ? '#991b1b' : '#7a3b21';
    $accentBorder = $isUrgent ? '#fca5a5' : '#f5b94e';
@endphp

@section('content')
    <h2 style="margin:0 0 14px;font-family:Georgia,serif;font-size:22px;color:#2a140b;">{{ $headerLine }}</h2>
    <p style="margin:0 0 14px;">Hello <strong>{{ $tenantName }}</strong>,</p>

    <p style="margin:0 0 18px;color:#5e3322;">
        This is a friendly reminder that your rental contract for <strong>Room {{ $roomNumber }}</strong>
        is scheduled to end on <strong>{{ $endsOn }}</strong>
        (approximately <strong>{{ $daysRemaining }} day{{ $daysRemaining === 1 ? '' : 's' }}</strong> from now).
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background:{{ $accentBg }};border:1px solid {{ $accentBorder }};border-radius:10px;margin:0 0 22px;">
        <tr>
            <td style="padding:16px 20px;">
                <p style="margin:0;font-size:13px;color:{{ $accentFg }};font-weight:600;">
                    @if($isUrgent)
                        Please contact the General Manager as soon as possible to confirm whether you intend to renew, extend, or move out.
                    @else
                        Please reach out to the General Manager if you would like to renew your lease, extend it, or arrange a move-out date.
                    @endif
                </p>
            </td>
        </tr>
    </table>

    <p style="margin:0;font-size:13px;color:#9a6a4f;">
        You can also reply to this email or visit your tenant portal &mdash; <em>My Contract</em> shows the exact end date and lease timer.
    </p>
@endsection
