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
          
            <header>
            @include('components.header')
            </header>

            <main>
                {!! $body !!}
            </main>

            @include('components.footer')
            <!-- <div class="container">
                <div class="footer-logo">
                    <img src="data:image/jpeg;base64,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">
                    
                    <!-- <img src=""> -->
                <!-- </div>
            </div> --> 

            <!-- <p style="text-align: center; color: #666;">
                © {{ date('Y') }}, Transbunnies. All Rights Reserved.
            </p> -->
            </footer>

        </div>
    </div>
</body>

</html>