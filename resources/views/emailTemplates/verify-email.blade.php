<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Template</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f6f6f6;">
    <div style="width: 100%; max-width: 600px; margin: auto; background-color: #ffffff; border: 1px solid #dddddd; border-radius: 4px; overflow: hidden;">
        @include('emailTemplates.header')
        <div style="padding: 20px; font-family: Arial, sans-serif; line-height: 1.6; color: #333333;">
            <p>Hello, {{ $user->username }}</p>
            <p>Thanks for signing up with us.</p>
            <p>Please click the button below to verify your email.</p>
            <p>
                <a href="{{ $verification_token }}" style="
                    display: inline-block; 
                    padding: 8px 16px; 
                    font-size: 12px; 
                    color: #fff; 
                    background-color: #a50000; 
                    text-decoration: none; 
                    border-radius: 4px; 
                    ">
                    Click to verify email
                </a>
            </p>
        </div>
        @include('emailTemplates.footer') 
    </div>
</body>
</html>
