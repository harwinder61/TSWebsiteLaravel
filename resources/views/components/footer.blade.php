<footer>
    <div class="social-links">
        <a href="#" class="social-icon">
            <img src="{{ asset('images/twitter.png') }}" alt="Twitter">
        </a>
        <a href="#" class="social-icon">
            <img src="{{ asset('images/instagram.png') }}" alt="Instagram">
        </a>
    </div>

    <nav>
        <ul style="list-style: none; padding: 0; display: flex; justify-content: center; gap: 20px;">
            <li><a href="{{ url('/login') }}">Log In</a></li>
            <li><a href="{{ url('/contact') }}">Contact Us</a></li>
            <li><a href="{{ url('/') }}">Transbunnies.com</a></li>
        </ul>
    </nav>

    <p style="text-align: center; color: #666;">
        © {{ date('Y') }}, Transbunnies. All Rights Reserved.
    </p>
</footer>