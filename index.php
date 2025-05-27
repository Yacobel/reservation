<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hilton Tanger City Center Hotel & Residences</title>
    <link rel="stylesheet" href="css/style.css">
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
            <a href="contact.php" class="nav-link">Contact</a>
        </nav>

        <a href="auth/login.php" class="btn btn-primary mobile-hide">Book now</a>
    </header>

    <!-- Hero Section -->
    <section class="hero" style="background-image: url('./assets/images/6c7b12aa.avif');">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <div class="hero-subtitle">Welcome to</div>
                    <h1 class="hero-title">Hilton Tanger City Center Hotel & Residences</h1>
                    <p class="hero-description">Experience luxury living in the heart of Tangier with breathtaking views of the Mediterranean Sea</p>

                    <div class="hero-buttons">
                        <a href="auth/login.php" class="btn btn-primary">Book now</a>
                        <button class="btn btn-ghost">
                            <div class="play-button">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M8 5v14l11-7z" />
                                </svg>
                            </div>
                            Take a tour
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Booking Form Section -->
    <section class="booking-section">
        <div class="container">
            <h2 class="section-title">Book Your Stay</h2>
            <p class="section-subtitle">Find your perfect room and make a reservation</p>
            
            <form action="auth/login.php" method="get" class="booking-form">
                <div class="booking-grid">
                    <div class="form-group">
                        <label class="form-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />
                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />
                            </svg>
                            Room type
                        </label>
                        <select class="form-select" name="room_type" required>
                            <option value="" disabled selected>Select Room Type</option>
                            <option value="Standard">Standard Room</option>
                            <option value="Deluxe">Deluxe Room</option>
                            <option value="Suite">Suite</option>
                            <option value="Executive">Executive Room</option>
                            <option value="Presidential">Presidential Suite</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            Adults
                        </label>
                        <select class="form-select" name="adults" required>
                            <option value="1" selected>1 Adult</option>
                            <option value="2">2 Adults</option>
                            <option value="3">3 Adults</option>
                            <option value="4">4 Adults</option>
                            <option value="5">5 Adults</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="3" />
                            </svg>
                            Children
                        </label>
                        <select class="form-select" name="children">
                            <option value="0" selected>0 Children</option>
                            <option value="1">1 Child</option>
                            <option value="2">2 Children</option>
                            <option value="3">3 Children</option>
                            <option value="4">4 Children</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                            Check in
                        </label>
                        <input type="date" class="form-input" name="check_in" value="<?php echo date('Y-m-d'); ?>" min="<?php echo date('Y-m-d'); ?>" required />
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                            Check out
                        </label>
                        <input type="date" class="form-input" name="check_out" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required />
                    </div>

                    <div class="form-group booking-submit">
                        <button type="submit" class="btn btn-primary btn-block">Check Availability</button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Facilities Section -->
    <section class="facilities">
        <div class="container">
            <h2 class="section-title">Hotel Amenities</h2>
            <p class="section-subtitle">Experience world-class facilities at Hilton Tanger City Center</p>

            <div class="facilities-grid">
                <div class="facility-item">
                    <div class="facility-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M2 6s1.5-2 5-2 5 2 5 2v14s-1.5-2-5-2-5 2-5 2V6z" />
                            <path d="M12 6s1.5-2 5-2 5 2 5 2v14s-1.5-2-5-2-5 2-5 2V6z" />
                        </svg>
                    </div>
                    <h3 class="facility-name">Rooftop Pool</h3>
                </div>

                <div class="facility-item">
                    <div class="facility-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12.55a11 11 0 0 1 14.08 0" />
                            <path d="M1.42 9a16 16 0 0 1 21.16 0" />
                            <path d="M8.53 16.11a6 6 0 0 1 6.95 0" />
                            <line x1="12" y1="20" x2="12.01" y2="20" />
                        </svg>
                    </div>
                    <h3 class="facility-name">Free High-Speed WiFi</h3>
                </div>

                <div class="facility-item">
                    <div class="facility-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8h1a4 4 0 0 1 0 8h-1" />
                            <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z" />
                            <line x1="6" y1="1" x2="6" y2="4" />
                            <line x1="10" y1="1" x2="10" y2="4" />
                            <line x1="14" y1="1" x2="14" y2="4" />
                        </svg>
                    </div>
                    <h3 class="facility-name">Executive Lounge</h3>
                </div>

                <div class="facility-item">
                    <div class="facility-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6.5 6.5h11" />
                            <path d="M6.5 17.5h11" />
                            <path d="M6.5 12h11" />
                        </svg>
                    </div>
                    <h3 class="facility-name">Fitness Center</h3>
                </div>

                <div class="facility-item">
                    <div class="facility-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2" />
                            <line x1="8" y1="21" x2="16" y2="21" />
                            <line x1="12" y1="17" x2="12" y2="21" />
                        </svg>
                    </div>
                    <h3 class="facility-name">Business Center</h3>
                </div>

                <div class="facility-item">
                    <div class="facility-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="5" />
                            <line x1="12" y1="1" x2="12" y2="3" />
                            <line x1="12" y1="21" x2="12" y2="23" />
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />
                            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
                            <line x1="1" y1="12" x2="3" y2="12" />
                            <line x1="21" y1="12" x2="23" y2="12" />
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" />
                            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
                        </svg>
                    </div>
                    <h3 class="facility-name">Spa Services</h3>
                </div>

                <div class="facility-item">
                    <div class="facility-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 2v20" />
                            <path d="M18 2v20" />
                            <path d="M6 7h12" />
                            <path d="M6 17h12" />
                        </svg>
                    </div>
                    <h3 class="facility-name">Meeting Rooms</h3>
                </div>

                <div class="facility-item">
                    <div class="facility-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 16H9m10 0h3v-3.15a1 1 0 0 0-.84-.99L16 11l-2.7-3.14a1 1 0 0 1 .25-1.4L16 5l.8-2.2a1 1 0 0 0-.25-1.15L15 0H9l-1.55 1.65a1 1 0 0 0-.25 1.15L8 5l2.45 1.46a1 1 0 0 1 .25 1.4L8 11l-5.16 1.86a1 1 0 0 0-.84.99V16h3" />
                            <circle cx="12" cy="16" r="1" />
                        </svg>
                    </div>
                    <h3 class="facility-name">Valet Parking</h3>
                </div>
            </div>
        </div>
    </section>

    <!-- Luxurious Rooms Section -->
    <section class="rooms">
        <div class="container">
            <h2 class="section-title">Luxurious Rooms</h2>
            <p class="section-subtitle">All room are design for your comfort</p>

            <div class="rooms-grid">
                <div class="room-card">
                    <div class="room-image">
                        <img src="https://images.unsplash.com/photo-1611892440504-42a792e24d32?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80" alt="King Guest Room" />
                        <div class="room-badge">Deluxe Room</div>
                    </div>
                    <div class="room-content">
                        <h3>King Guest Room with Sea View</h3>
                        <p>Luxurious room with Mediterranean views, 55-inch HDTV, and premium amenities</p>
                    </div>
                </div>

                <div class="room-card">
                    <div class="room-image">
                        <img src="https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80" alt="Executive Suite" />
                        <div class="room-badge">Executive</div>
                    </div>
                    <div class="room-content">
                        <h3>Executive Suite with Lounge Access</h3>
                        <p>Spacious suite with separate living area, executive lounge access, and premium services</p>
                    </div>
                </div>

                <div class="room-card">
                    <div class="room-image">
                        <img src="https://images.unsplash.com/photo-1578683010236-d716f9a3f461?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80" alt="Presidential Suite" />
                        <div class="room-badge">Premium</div>
                    </div>
                    <div class="room-content">
                        <h3>Presidential Suite</h3>
                        <p>Ultimate luxury with panoramic sea views, private terrace, and exclusive amenities</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container">
            <h2 class="section-title">Testimonies</h2>

            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <span class="testimonial-date">2 Mar 2023</span>
                        <div class="testimonial-stars">
                            <span class="star filled">★</span>
                            <span class="star filled">★</span>
                            <span class="star filled">★</span>
                            <span class="star filled">★</span>
                            <span class="star filled">★</span>
                        </div>
                    </div>
                    <p class="testimonial-text">
                        "The service atmosphere absolutely fantastic! Their are absolutely no issue that was not addressed immediately with a smile, and the view was outstanding and from the terrace amazing Accommodation, specifically coming by the Ocean Room to check with us. Numerous conference attendees commented on the natural beauty of the setting. Everything is so positive attitude toward the conference too. Particularly noteworthy is the helpfully of the team and this sense of being among family. I would specifically like to praise the level of services, by everyone, it is really professional. but there is absolutely nothing that could be improved - you have set the bar very high"
                    </p>
                    <div class="testimonial-author">
                        <div class="author-avatar">AW</div>
                        <span class="author-name">Anthony Word</span>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <span class="testimonial-date">28 Mar 2023</span>
                        <div class="testimonial-stars">
                            <span class="star filled">★</span>
                            <span class="star filled">★</span>
                            <span class="star filled">★</span>
                            <span class="star filled">★</span>
                            <span class="star filled">★</span>
                        </div>
                    </div>
                    <p class="testimonial-text">
                        "The service atmosphere absolutely fantastic! Their are absolutely no issue that was not addressed immediately with a smile, and the view was outstanding and from the terrace amazing Accommodation, specifically coming by the Ocean Room to check with us. Numerous conference attendees commented on the natural beauty of the setting. Everything is so positive attitude toward the conference too. Particularly noteworthy is the helpfully of the team and this sense of being among family. I would specifically like to praise the level of services, by everyone, it is really professional. but there is absolutely nothing that could be improved - you have set the bar very high"
                    </p>
                    <div class="testimonial-author">
                        <div class="author-avatar">RW</div>
                        <span class="author-name">Regina White</span>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <span class="testimonial-date">6 Apr 2023</span>
                        <div class="testimonial-stars">
                            <span class="star filled">★</span>
                            <span class="star filled">★</span>
                            <span class="star filled">★</span>
                            <span class="star filled">★</span>
                            <span class="star">★</span>
                        </div>
                    </div>
                    <p class="testimonial-text">
                        "The service atmosphere absolutely fantastic! Their are absolutely no issue that was not addressed immediately with a smile, and the view was outstanding and from the terrace amazing Accommodation, specifically coming by the Ocean Room to check with us. Numerous conference attendees commented on the natural beauty of the setting. Everything is so positive attitude toward the conference too. Particularly noteworthy is the helpfully of the team and this sense of being among family. I would specifically like to praise the level of services, by everyone, it is really professional. but there is absolutely nothing that could be improved - you have set the bar very high"
                    </p>
                    <div class="testimonial-author">
                        <div class="author-avatar">MD</div>
                        <span class="author-name">Marcus Days</span>
                    </div>
                </div>
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
                    <h4 class="footer-heading">Quick links</h4>
                    <ul class="footer-links">
                        <li><a href="#">Home</a></li>
                        <li><a href="#">Booking</a></li>
                        <li><a href="#">Explore</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4 class="footer-heading">Company</h4>
                    <ul class="footer-links">
                        <li><a href="#">Privacy policy</a></li>
                        <li><a href="#">Refund policy</a></li>
                        <li><a href="#">F.A.Q</a></li>
                        <li><a href="#">About</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4 class="footer-heading">Social media</h4>
                    <div class="social-links">
                        <a href="#" class="social-link">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M24 4.557c-.883.392-1.832.58-2.828.775 1.017-.609 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z" />
                            </svg>
                        </a>
                        <a href="#" class="social-link">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z" />
                            </svg>
                        </a>
                        <a href="#" class="social-link">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24.009c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641.001.012.001z.017.017" />
                            </svg>
                        </a>
                        <a href="#" class="social-link">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                            </svg>
                        </a>
                    </div>

                    <div class="newsletter">
                        <h4 class="footer-heading">Newsletter</h4>
                        <p class="newsletter-text">Kindly subscribe to our newsletter to get latest deals from our offer</p>
                        <div class="newsletter-form">
                            <input type="email" placeholder="Enter your email" class="newsletter-input" />
                            <button class="btn btn-primary newsletter-btn">Subscribe</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>Hotel Hilton Tanger</p>
            </div>
        </div>
    </footer>
    <!-- JavaScript -->
    <script src="js/main.js"></script>
</body>

</html>