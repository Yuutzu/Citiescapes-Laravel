@extends('emails._layout', ['title' => 'Welcome to Citiescapes'])

@section('content')
    <h2 style="margin:0 0 14px;font-family:Georgia,serif;font-size:22px;color:#2a140b;">Welcome to Citiescapes</h2>
    <p style="margin:0 0 12px;">Hello <strong>{{ $name }}</strong>,</p>
    <p style="margin:0 0 18px;color:#5e3322;">Your tenant account has been created. Use the credentials below to log in for the first time:</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background:#faf3e3;border:1px solid #f5b94e;border-radius:10px;margin:0 0 22px;">
        <tr>
            <td style="padding:18px 22px;">
                <p style="margin:0 0 8px;font-size:13px;color:#7a3b21;text-transform:uppercase;letter-spacing:0.08em;">Email</p>
                <p style="margin:0 0 14px;font-weight:600;color:#2a140b;">{{ $email }}</p>
                <p style="margin:0 0 8px;font-size:13px;color:#7a3b21;text-transform:uppercase;letter-spacing:0.08em;">Temporary Password</p>
                <p style="margin:0;"><code style="font-size:18px;color:#a14d2b;background:#ffffff;padding:6px 12px;border-radius:6px;font-family:'Courier New',monospace;">{{ $tempPassword }}</code></p>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 10px;">After logging in, you will need to verify your identity with a One-Time Password (OTP) sent to this email.</p>
    <p style="margin:0;font-size:13px;color:#9a6a4f;">You will then be prompted to change your password.</p>
@endsection
