<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Template</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f6f6f6;">
    <div style="width: 100%; max-width: 600px; margin: auto; background-color: #ffffff; border: 1px solid #dddddd; border-radius: 4px; overflow: hidden; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
        @include('emailTemplates.header')
        <div style="padding: 20px; font-family: Arial, sans-serif; line-height: 1.6; color: #333333; text-align: center;">
            <h2 style="color: #d9534f;">Token is invalid!</h2>
            <p>Please check your token or request a new one.</p>
        </div>

    </div>
</body>
</html>
