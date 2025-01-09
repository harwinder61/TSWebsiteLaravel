<div class="container">
    <div class="footer-logo">
        <img src="{{ asset('images/ts-twiter.png') }}" alt="Transbunnies Logo" class="instagram-2" style="height: 55px;">
        <img src="{{ asset('images/ts-instagram.png') }}" alt="Transbunnies Logo" class="instagram-2" style="height: 55px;">
    </div>
</div>

<p style="text-align: center; color: #666;">
    © {{ date('Y') }}, Transbunnies. All Rights Reserved.
</p>
</footer>

<style>
    /* Media query for screens with width 768px or less */
    @media (max-width: 768px) {
        footer {
            display: flex;
            justify-content: center;
            align-items: center;  /* To center content vertically */
            flex-direction: column; /* Ensure items stack vertically */
            text-align: center;
        }

        .footer-logo {
            display: flex;
            justify-content: center;
            gap: 10px; /* Space between logos */
        }
    }
</style>
