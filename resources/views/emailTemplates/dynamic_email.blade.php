<!-- resources/views/emails/dynamic_email.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>{{ $subject }}</title>
</head>
<body>
    <h1>{{ $subject }}</h1>
    <p>{!! $body !!}</p> <!-- This will show the email body, including any HTML content -->
</body>
</html>
