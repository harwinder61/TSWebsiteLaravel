<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Welcome' }}</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
    }
    header {
        background-color: red; /* Changed to red */
        color: white;
        padding: 5px 20px; /* Adjusted padding for a smaller height */
        text-align: center;
    }
    footer {
        background-color: #333;
        color: white;
        text-align: center;
        padding: 10px 20px;
        position: fixed;
        bottom: 0;
        width: 100%;
    }
    .container-custom{
        margin: 20px;
    }
    @media (max-width: 600px) {
        header, footer {
            padding: 5px;
            font-size: 14px;
        }
    }

</style>
</head>
<body>
    <div class="container">
        <div class="main_section">
        @include('components.header')
        
        <main>
            @yield('content')
        </main>

        @include('components.footer')
        </div>
    </div>
</body>
</html>