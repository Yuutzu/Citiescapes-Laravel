{{--
  Shared email shell — warm brown / marigold theme matching the web UI.
  Use:
      @extends('emails._layout', ['title' => 'Email subject'])
      @section('content') ...email body html... @endsection
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Citiescapes' }}</title>
</head>
<body style="margin:0;padding:0;background:#faf7f2;font-family:'Segoe UI',Arial,sans-serif;color:#3d1d10;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#faf7f2;padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"
                    style="max-width:600px;width:100%;background:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 4px 18px rgba(94,40,24,0.10);border:1px solid #e8d9c8;">

                    {{-- Header --}}
                    <tr>
                        <td style="background:#2a140b;padding:28px 32px;border-bottom:3px solid #f5b94e;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-right:12px;vertical-align:middle;">
                                        <div style="width:42px;height:42px;border-radius:10px;background:#f5b94e;color:#2a140b;font-weight:700;font-size:15px;text-align:center;line-height:42px;font-family:Georgia,serif;">CS</div>
                                    </td>
                                    <td style="vertical-align:middle;">
                                        <div style="font-family:Georgia,'Times New Roman',serif;font-weight:700;color:#ffffff;font-size:22px;line-height:1;">Citiescapes</div>
                                        <div style="font-size:10px;letter-spacing:0.22em;color:#f5b94e;text-transform:uppercase;margin-top:4px;">Apartment Rental</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:34px 36px;font-size:15px;line-height:1.6;color:#3d1d10;">
                            @yield('content')
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background:#faf7f2;padding:18px 32px;text-align:center;font-size:11px;color:#7a3b21;border-top:1px solid #e8d9c8;">
                            &copy; {{ date('Y') }} Citiescapes Apartment Rental &nbsp;&bull;&nbsp; Remedios St., Bajada, Davao City
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
