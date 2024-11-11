<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Recovery</title>
</head>

<body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, sans-serif;">
    <div style="width: 100%; max-width: 600px; margin: auto; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <!-- Header Include -->
        @include('emailTemplates.header')

        <div style="padding: 30px; text-align: center;">
            <h2 style="color: #333; font-size: 24px;">Password Recovery</h2>
            <p style="color: #555; font-size: 16px; line-height: 1.5;">We received a request to reset your password. 
                Click the button below to create a new password:</p>
                <p>{{ env('WEBAPP_URL') }}/reset-password?token={{ $recovery_token }} </p>
            <a href="{{ env('WEBAPP_URL') }}/reset-password?token={{ $recovery_token }}" style="display: inline-block; margin-top: 20px;
             padding: 12px 25px; background-color: #a50000; color: white; text-decoration: none;
             border-radius: 5px; font-weight: bold; font-size: 16px;">Reset Password</a>
        </div>

        <div style="padding: 20px; text-align: center; font-size: 14px; color: #777;">
            <p>If you didn’t request a password reset, please ignore this email.</p>
            <p>Thank you for being with us!</p>
        </div>

        <!-- Footer Include -->
        @include('emailTemplates.footer')
    </div>
</body>

</html>

