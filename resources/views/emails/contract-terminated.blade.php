@extends('emails._layout', ['title' => 'Citiescapes — Contract Termination Notice'])

@section('content')
    <h2 style="margin:0 0 14px;font-family:Georgia,serif;font-size:22px;color:#2a140b;">Contract Termination Notice</h2>
    <p style="margin:0 0 14px;">Hello <strong>{{ $tenantName }}</strong>,</p>

    <p style="margin:0 0 18px;color:#5e3322;">
        This is an official notice that your rental contract for <strong>Room {{ $roomNumber }}</strong>
        has been terminated, effective <strong>{{ $terminatedOn }}</strong>.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background:#faf7f2;border-left:4px solid #7a3b21;border-radius:8px;margin:0 0 22px;">
        <tr>
            <td style="padding:16px 20px;">
                <p style="margin:0 0 6px;font-size:12px;color:#7a3b21;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">Reason</p>
                <p style="margin:0;font-size:14px;color:#3d1d10;white-space:pre-line;">{{ $reason }}</p>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 18px;">
        Please coordinate with the General Manager regarding key return and your security deposit.
    </p>

    <p style="margin:0;font-size:13px;color:#9a6a4f;">
        If you believe this notice was sent in error, contact the General Manager immediately.
    </p>
@endsection
