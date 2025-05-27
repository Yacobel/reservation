<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Hilton Tanger City Center Hotel & Residences</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/pages.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Dancing+Script:wght@400;700&display=swap" rel="stylesheet">
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="logo">
            <img src="https://www.hilton.com/modules/assets/svgs/logos/HH.svg" alt="Hilton Logo" style="height: 40px;">
        </div>

        <div class="hamburger-menu">
            <div class="hamburger-icon">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>

        <nav class="nav">
            <a href="index.php" class="nav-link">Home</a>
            <a href="auth/login.php" class="nav-link">Rooms</a>
            <a href="dining.php" class="nav-link">Dining</a>
            <a href="meetings.php" class="nav-link">Meetings</a>
            <a href="contact.php" class="nav-link active">Contact</a>
        </nav>

        <a href="auth/login.php" class="btn btn-primary mobile-hide">Book now</a>
    </header>

    <!-- Hero Section -->
    <section class="hero contact-hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <div class="hero-subtitle">Get in Touch</div>
                    <h1 class="hero-title">Contact Us</h1>
                    <p class="hero-description">We're here to assist you with any inquiries or special requests</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Information Section -->
    <section class="contact-info">
        <div class="container">
            <div class="contact-grid">
                <div class="contact-details">
                    <h2 class="section-title">Contact Information</h2>
                    <p class="section-subtitle">Reach out to us through any of these channels</p>

                    <div class="contact-methods">
                        <div class="contact-method">
                            <div class="method-icon">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                            </div>
                            <div class="method-content">
                                <h3 class="method-title">Address</h3>
                                <p class="method-text">Place du Maghreb Arabe, Tangier 90000, Morocco</p>
                            </div>
                        </div>

                        <div class="contact-method">
                            <div class="method-icon">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                                </svg>
                            </div>
                            <div class="method-content">
                                <h3 class="method-title">Phone</h3>
                                <p class="method-text">+212 5393-90000</p>
                            </div>
                        </div>

                        <div class="contact-method">
                            <div class="method-icon">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                    <polyline points="22,6 12,13 2,6" />
                                </svg>
                            </div>
                            <div class="method-content">
                                <h3 class="method-title">Email</h3>
                                <p class="method-text">info@hiltontanger.com</p>
                            </div>
                        </div>

                        <div class="contact-method">
                            <div class="method-icon">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10" />
                                    <polyline points="12 6 12 12 16 14" />
                                </svg>
                            </div>
                            <div class="method-content">
                                <h3 class="method-title">Hours</h3>
                                <p class="method-text">24/7 Front Desk<br>Reservations: 8:00 AM - 8:00 PM</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="contact-form-container">
                    <h2 class="form-title">Send us a Message</h2>
                    <form class="contact-form" action="auth/login.php" method="get">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" placeholder="Your name" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" placeholder="Your email address" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" placeholder="Your phone number">
                        </div>

                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <select id="subject" name="subject" required>
                                <option value="">Select a subject</option>
                                <option value="reservation">Reservation Inquiry</option>
                                <option value="feedback">Feedback</option>
                                <option value="services">Services Information</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="5" placeholder="Your message" required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="container">
            <h2 class="section-title">Find Us</h2>
            <p class="section-subtitle">Located in the heart of Tangier City Center</p>
            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3238.2775099425813!2d-5.8133342!3d35.7651089!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd0b875cf1e9d825%3A0x76f7c1d95b45d09d!2sHilton%20Tanger%20City%20Center%20Hotel%20%26%20Residences!5e0!3m2!1sen!2sus!4v1653389167624!5m2!1sen!2sus" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section footer-about">
                    <h3 class="footer-title">Hilton Tanger City Center</h3>
                    <p class="footer-text">
                        Located in the heart of Tangier, Hilton Tanger City Center Hotel & Residences offers stunning views of the Mediterranean Sea. Our modern hotel features spacious rooms, world-class amenities, and exceptional service, making it the perfect choice for both business and leisure travelers.
                    </p>
                </div>

                <div class="footer-section">
                    <h4 class="footer-heading">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="auth/login.php">Rooms</a></li>
                        <li><a href="dining.php">Dining</a></li>
                        <li><a href="meetings.php">Meetings</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4 class="footer-heading">Contact</h4>
                    <ul class="footer-contact">
                        <li>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                            Place du Maghreb Arabe, Tangier 90000, Morocco
                        </li>
                        <li>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                            </svg>
                            +212 5393-90000
                        </li>
                        <li>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                <polyline points="22,6 12,13 2,6" />
                            </svg>
                            info@hiltontanger.com
                        </li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4 class="footer-heading">Social media</h4>
                    <div class="social-links">
                        <a href="#" class="social-link">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
                            </svg>
                        </a>
                        <a href="#" class="social-link">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z" />
                            </svg>
                        </a>
                        <a href="#" class="social-link">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
                                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
                                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>u00a9 Hilton Hotels & Resorts 2025</p>
            </div>
        </div>
    </footer>
    <!-- JavaScript -->
    <script src="js/main.js"></script>
</body>

</html>
