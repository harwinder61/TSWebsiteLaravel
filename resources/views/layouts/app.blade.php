<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{!! $title ?? 'Welcome' !!}}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #FFFFFF;
            /* Changed to red */
            color: white;
            padding: 5px 20px;
            /* Adjusted padding for a smaller height */
            text-align: center;
        }

        footer {
            background-color: #FFFFFF;
            color: white;
            text-align: center;
            padding: 10px 20px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }

        /* Container for flex centering the footer content */
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
            /* Add margin to space it from the footer text */
        }

        .footer-logo {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 50px;
        }

        /* Image styles */
        .footer-logo img {
            height: 80px;
            /* Set image height */
            margin: 0 10px;
            /* Space between the images */
        }

        /* Footer styling with lines before and after content */
        footer {
            position: relative;
            padding: 20px 0;
        }

        /* Line before the footer content */
        footer::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            border-top: 2px solid #ddd;
            margin-bottom: 10px;
        }

        /* Line after the footer content */
        footer::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            border-bottom: 2px solid #ddd;
            margin-top: 10px;
        }

        /* Footer text styling */
        footer p {
            text-align: center;
            color: #666;
            z-index: 1;
            /* Ensure text appears above lines */
        }

        .container-custom {
            margin: 20px;
        }

        @media (max-width: 600px) {

            header,
            footer {
                padding: 5px;
                font-size: 14px;
            }

            .logo-container {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100px;
            }
        }

        @media (max-width: 768px) {
            .logo-container img {
                height: 100px;
                position: fixed;
            }

            .footer-logo img {
                height: 70px;
                margin: 20px 10px;
            }

            .instagram-2 {
                height: 60px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="main_section">
            <!-- @include('components.header') -->
            <header>
                <h1 class="text-red">
                    <!-- Welcome to Transbunnies: TS Escorts Shemales in UK & London -->
                    <div class="logo-container">
                        <img src="{{ asset('images/ts-main-logo.jpeg') }}" alt="Transbunnies Logo" class="w-1/2">
                    </div>

                </h1>
            </header>

            <main>
                <!-- @yield('content') -->
                {!! $body !!}
            </main>

            <!-- @include('components.footer') -->
            <div class="container">
                <div class="footer-logo">
                    <img src="{{ asset('images/ts-twiter.png') }}" alt="Transbunnies Logo" class="twiter-1">
                    <img src="{{ asset('images/ts-instagram.png') }}" alt="Transbunnies Logo" class="instagram-2">
                </div>
            </div>

            <p style="text-align: center; color: #666;">
                © {{ date('Y') }}, Transbunnies. All Rights Reserved.
            </p>
            </footer>

        </div>
    </div>
</body>

</html>