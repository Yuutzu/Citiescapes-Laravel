<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2c3a52;">Welcome to Citiescapes</h2>
        <p>Hello {{ $name }},</p>
        <p>Your tenant account has been created. Use the credentials below to log in for the first time:</p>
        <div style="background: #f5f7fa; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Email:</strong> {{ $email }}</p>
            <p style="margin: 5px 0;"><strong>Temporary Password:</strong> <code style="font-size: 18px; color: #d97706;">{{ $tempPassword }}</code></p>
        </div>
        <p>After logging in, you will need to verify your identity with a One-Time Password (OTP) sent to this email.</p>
        <p>You will then be prompted to change your password.</p>
        <hr style="border: none; border-top: 1px solid #e4e9f1; margin: 20px 0;">
        <p style="font-size: 12px; color: #999;">Citiescapes Apartment Rental &bull; Remedios St., Bajada, Davao City</p>
    </div>
</body>
</html>
