<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meetings & Events - Hilton Tanger City Center Hotel & Residences</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/pages.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Dancing+Script:wght@400;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .space-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .space-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        .space-image img {
            transition: transform 0.5s ease;
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        .space-card:hover .space-image img {
            transform: scale(1.05);
        }
        .type-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 12px;
            padding: 30px;
            background-color: white;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }
        .type-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        .type-icon {
            color: #d4af37;
            margin-bottom: 20px;
        }
        .btn:hover {
            transform: scale(1.05);
        }
        .request-proposal {
            background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1517048676732-d65bc937f952?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1920&h=800&q=80');
            background-size: cover;
            background-position: center;
            padding: 100px 0;
            color: white;
            text-align: center;
            border-radius: 0;
        }
        .request-proposal .section-title {
            color: white;
            font-family: 'Dancing Script', cursive;
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        .request-proposal .section-subtitle {
            color: rgba(255,255,255,0.9);
            font-size: 1.2rem;
            margin-bottom: 30px;
        }
        .request-proposal .btn-primary {
            background-color: #d4af37;
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: transform 0.2s ease, background-color 0.2s ease;
        }
        .request-proposal .btn-primary:hover {
            background-color: #c9a227;
        }
    </style>
</head>
<body>
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
            <a href="meetings.php" class="nav-link active">Meetings</a>
            <a href="contact.php" class="nav-link">Contact</a>
        </nav>
        <a href="auth/login.php" class="btn btn-primary mobile-hide">Book now</a>
    </header>
    <section class="hero meetings-hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <div class="hero-subtitle">Business & Events</div>
                    <h1 class="hero-title">Meetings & Events at Hilton Tanger</h1>
                    <p class="hero-description">Host your next meeting or special event in our elegant venues with stunning views</p>
                </div>
            </div>
        </div>
    </section>
    <section class="meeting-spaces" style="padding: 80px 0;">
        <div class="container">
            <h2 class="section-title" style="font-size: 2.5rem; margin-bottom: 15px; text-align: center; color: #333; font-family: 'Dancing Script', cursive;">Our Meeting Spaces</h2>
            <p class="section-subtitle" style="text-align: center; margin-bottom: 40px; font-size: 1.2rem; color: #666;">Versatile venues for every occasion</p>
            <div class="spaces-grid">
                <div class="space-card">
                    <div class="space-image">
                        <img src="https://images.unsplash.com/photo-1519167758481-83f550bb49b3?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=768&h=512&q=80" alt="Grand Ballroom">
                    </div>
                    <div class="space-content" style="padding: 25px;">
                        <h3 class="space-name">Grand Ballroom</h3>
                        <p class="space-description">Our largest venue, perfect for conferences, galas, and weddings with capacity for up to 500 guests.</p>
                        <div class="space-details">
                            <div class="space-capacity">
                                <strong>Capacity:</strong> Up to 500 guests
                            </div>
                            <div class="space-size">
                                <strong>Size:</strong> 800 m²
                            </div>
                        </div>
                        <a href="auth/login.php" class="btn btn-secondary">Request Information</a>
                    </div>
                </div>
                <div class="space-card">
                    <div class="space-image">
                        <img src="https://images.unsplash.com/photo-1517502884422-41eaead166d4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=768&h=512&q=80" alt="Executive Boardroom">
                    </div>
                    <div class="space-content" style="padding: 25px;">
                        <h3 class="space-name">Executive Boardroom</h3>
                        <p class="space-description">A sophisticated setting for high-level meetings and presentations, equipped with the latest technology.</p>
                        <div class="space-details">
                            <div class="space-capacity">
                                <strong>Capacity:</strong> Up to 20 guests
                            </div>
                            <div class="space-size">
                                <strong>Size:</strong> 75 m²
                            </div>
                        </div>
                        <a href="auth/login.php" class="btn btn-secondary">Request Information</a>
                    </div>
                </div>
                <div class="space-card">
                    <div class="space-image">
                        <img src="https://images.unsplash.com/photo-1562664377-709f2c337eb2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=768&h=512&q=80" alt="Mediterranean Room">
                    </div>
                    <div class="space-content" style="padding: 25px;">
                        <h3 class="space-name">Mediterranean Room</h3>
                        <p class="space-description">A versatile space with panoramic sea views, ideal for medium-sized meetings and social gatherings.</p>
                        <div class="space-details">
                            <div class="space-capacity">
                                <strong>Capacity:</strong> Up to 150 guests
                            </div>
                            <div class="space-size">
                                <strong>Size:</strong> 250 m²
                            </div>
                        </div>
                        <a href="auth/login.php" class="btn btn-secondary">Request Information</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="event-types" style="padding: 80px 0; background-color: #f9f9f9;">
        <div class="container">
            <h2 class="section-title" style="font-size: 2.5rem; margin-bottom: 15px; text-align: center; color: #333; font-family: 'Dancing Script', cursive;">Event Types</h2>
            <p class="section-subtitle" style="text-align: center; margin-bottom: 40px; font-size: 1.2rem; color: #666;">Tailored solutions for every occasion</p>

            <div class="types-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-top: 30px;">
                <div class="type-card">
                    <div class="type-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2" />
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                        </svg>
                    </div>
                    <h3 class="type-title">Corporate Meetings</h3>
                    <p class="type-description">From board meetings to large conferences, our venues provide the perfect setting for your business needs.</p>
                </div>

                <div class="type-card">
                    <div class="type-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                    </div>
                    <h3 class="type-title">Weddings</h3>
                    <p class="type-description">Create unforgettable memories with our elegant wedding venues and expert planning services.</p>
                </div>

                <div class="type-card">
                    <div class="type-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 18v-6a9 9 0 0 1 18 0v6" />
                            <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z" />
                        </svg>
                    </div>
                    <h3 class="type-title">Social Events</h3>
                    <p class="type-description">Host memorable celebrations, galas, and special occasions with our customizable event packages.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="request-proposal">
        <div class="container">
            <div class="proposal-content">
                <h2 class="section-title">Plan Your Next Event</h2>
                <p class="section-subtitle">Let our expert team help you create a memorable event</p>
                <a href="auth/login.php" class="btn btn-primary">Request Proposal</a>
            </div>
        </div>
    </section>

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
    <script src="js/main.js"></script>
</body>

</html>
