<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2c3a52;">Account Verification</h2>
        <p>Hello <?php echo e($name); ?>,</p>
        <p>Your One-Time Password (OTP) for account activation is:</p>
        <div style="text-align: center; margin: 25px 0;">
            <span style="font-size: 32px; letter-spacing: 8px; font-weight: bold; color: #2c3a52; background: #f5f7fa; padding: 15px 25px; border-radius: 8px;"><?php echo e($otpCode); ?></span>
        </div>
        <p>This code expires in <strong><?php echo e($expiryMinutes); ?> minutes</strong>.</p>
        <p>If you did not request this, please ignore this email.</p>
        <hr style="border: none; border-top: 1px solid #e4e9f1; margin: 20px 0;">
        <p style="font-size: 12px; color: #999;">Citiescapes Apartment Rental &bull; Remedios St., Bajada, Davao City</p>
    </div>
</body>
</html>
<?php /**PATH C:\laragon\www\citiescapes\resources\views/emails/otp.blade.php ENDPATH**/ ?>