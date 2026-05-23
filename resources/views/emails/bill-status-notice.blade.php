@extends('emails._layout', ['title' => 'Citiescapes — Account Notice'])

@php
    $palette = match($stage) {
        'eviction'   => ['bg' => '#fee2e2', 'fg' => '#991b1b', 'border' => '#fca5a5', 'title' => 'Eviction Notice', 'urgent' => true],
        'delinquent' => ['bg' => '#fee2e2', 'fg' => '#991b1b', 'border' => '#fca5a5', 'title' => 'Delinquent Account', 'urgent' => true],
        'grace'      => ['bg' => '#faf3e3', 'fg' => '#7a3b21', 'border' => '#f5b94e', 'title' => 'Payment Reminder', 'urgent' => false],
        default      => ['bg' => '#faf7f2', 'fg' => '#5e3322', 'border' => '#e8d9c8', 'title' => 'Account Notice', 'urgent' => false],
    };
@endphp

@section('content')
    <h2 style="margin:0 0 14px;font-family:Georgia,serif;font-size:22px;color:#2a140b;">{{ $palette['title'] }}</h2>
    <p style="margin:0 0 14px;">Hello <strong>{{ $tenantName }}</strong>,</p>

    @if($stage === 'eviction')
        <p style="margin:0 0 18px;color:#5e3322;">
            Your bill for <strong>{{ $billingPeriod }}</strong> is now <strong>{{ $daysOverdue }} days overdue</strong>.
            The account has been escalated to <strong>Eviction</strong> status and the General Manager may proceed
            with termination of your lease.
        </p>
    @elseif($stage === 'delinquent')
        <p style="margin:0 0 18px;color:#5e3322;">
            Your bill for <strong>{{ $billingPeriod }}</strong> is now <strong>{{ $daysOverdue }} days overdue</strong>
            and has been marked <strong>Delinquent</strong>. Daily penalties continue to accrue.
        </p>
    @else
        <p style="margin:0 0 18px;color:#5e3322;">
            This is a reminder that your bill for <strong>{{ $billingPeriod }}</strong> is in its grace period
            (<strong>{{ $daysOverdue }} day{{ $daysOverdue === 1 ? '' : 's' }}</strong> overdue).
            Please settle to avoid penalties.
        </p>
    @endif

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background:{{ $palette['bg'] }};border:1px solid {{ $palette['border'] }};border-radius:10px;margin:0 0 22px;">
        <tr>
            <td style="padding:16px 20px;">
                <p style="margin:0 0 6px;font-size:12px;color:{{ $palette['fg'] }};text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">Total due</p>
                <p style="margin:0;font-size:18px;font-weight:700;color:#2a140b;">₱{{ $totalAmount }}</p>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 12px;">
        Please contact the General Manager
        @if($palette['urgent']) <strong>as soon as possible</strong> @else at your earliest convenience @endif
        to settle the outstanding amount or request a payment plan via your tenant portal.
    </p>

    <p style="margin:0;font-size:13px;color:#9a6a4f;">
        You can also log in to your portal and use <em>Request payment plan</em> from the bill row.
    </p>
@endsection
