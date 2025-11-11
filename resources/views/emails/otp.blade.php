<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Verification Code</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">VoteHubPH</h1>
        <p style="color: rgba(255,255,255,0.9); margin: 5px 0 0 0;">Email Verification</p>
    </div>
    
    <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e0e0e0; border-top: none;">
        <h2 style="color: #333; margin-top: 0;">Your Verification Code</h2>
        <p>Thank you for signing up for VoteHubPH! Please use the following code to verify your email address:</p>
        
        <div style="background: white; border: 2px dashed #667eea; border-radius: 8px; padding: 20px; text-align: center; margin: 30px 0;">
            <div style="font-size: 36px; font-weight: bold; color: #667eea; letter-spacing: 8px; font-family: 'Courier New', monospace;">
                {{ $otp }}
            </div>
        </div>
        
        <p style="color: #666; font-size: 14px;">This code will expire in 10 minutes. If you didn't request this code, please ignore this email.</p>
        
        <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0; color: #999; font-size: 12px;">
            Best regards,<br>
            The VoteHubPH Team
        </p>
    </div>
</body>
</html>

