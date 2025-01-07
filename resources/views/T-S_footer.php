<style>
    /* Footer styles */
footer {
    background-color: #f8f8f8; /* Light background color */
    padding: 20px 0; /* Padding at the top and bottom */
    text-align: center;
    font-family: Arial, sans-serif; /* Font style for the footer */
}

/* Social links container */
footer .social-links {
    margin-bottom: 20px;
}

/* Social icon styling */
footer .social-icon img {
    width: 32px; /* Set a fixed size for the icons */
    height: 32px;
    margin: 0 10px; /* Space between icons */
    transition: transform 0.3s ease; /* Smooth transition on hover */
}

footer .social-icon img:hover {
    transform: scale(1.1); /* Slightly enlarge icon on hover */
}

/* Navigation links styling */
footer nav ul {
    list-style: none;
    padding: 0;
    margin-bottom: 20px;
    display: flex;
    justify-content: center;
    gap: 20px;
}

footer nav ul li a {
    color: #333; /* Dark color for text */
    text-decoration: none; /* Remove underline */
    font-size: 14px; /* Set font size */
    font-weight: 500; /* Slightly bolder text */
    transition: color 0.3s ease; /* Smooth color transition on hover */
}

footer nav ul li a:hover {
    color: #007bff; /* Change color on hover */
}

/* Footer copyright styling */
footer p {
    color: #666; /* Gray color for text */
    font-size: 12px; /* Smaller text size */
    margin-top: 20px;
}

/* Responsive styling */
@media (max-width: 768px) {
    footer .social-links {
        margin-bottom: 10px;
    }

    footer nav ul {
        flex-direction: column;
        gap: 10px;
    }

    footer nav ul li a {
        font-size: 16px; /* Larger text on smaller screens */
    }
}

</style>

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