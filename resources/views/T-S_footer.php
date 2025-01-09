

<footer>
    <div class="social-links">
        <a href="#" class="social-icon">
           <h1>hello this is for testing</h1>
        </a>
        <a href="#" class="social-icon">
            <img src="{{ asset('images/instagram.png') }}" alt="Instagram">
        </a>
    </div>

    <nav>
        <ul style="list-style: none; padding: 0; display: flex; justify-content: center; gap: 20px;">
            <li><a href="{{ route('login') }}">Log In</a></li>
            <li><a href="{{ route('contact') }}">Contact Us</a></li>
            <li><a href="{{ route('home') }}">Transbunnies.com</a></li>
        </ul>
    </nav>

    <p style="text-align: center; color: #666;">
        © {{ date('Y') }}, Transbunnies. All Rights Reserved.
    </p>
</footer>   