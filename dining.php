<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dining - Hilton Tanger City Center Hotel & Residences</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/pages.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Dancing+Script:wght@400;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .offer-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        .btn:hover {
            transform: scale(1.05);
        }
        .restaurant-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .restaurant-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        .restaurant-image img {
            transition: transform 0.5s ease;
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        .restaurant-card:hover .restaurant-image img {
            transform: scale(1.05);
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
            <a href="dining.php" class="nav-link active">Dining</a>
            <a href="meetings.php" class="nav-link">Meetings</a>
            <a href="contact.php" class="nav-link">Contact</a>
        </nav>

        <a href="auth/login.php" class="btn btn-primary mobile-hide">Book now</a>
    </header>

    <section class="hero dining-hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <div class="hero-subtitle">Culinary Excellence</div>
                    <h1 class="hero-title">Dining at Hilton Tanger</h1>
                    <p class="hero-description">Experience exquisite dining with breathtaking views of the Mediterranean Sea</p>
                </div>
            </div>
        </div>
    </section>

    <section class="restaurants">
        <div class="container">
            <h2 class="section-title" style="font-size: 2.5rem; margin-bottom: 15px; text-align: center; color: #333; font-family: 'Dancing Script', cursive;">Our Restaurants</h2>
            <p class="section-subtitle" style="text-align: center; margin-bottom: 40px; font-size: 1.2rem; color: #666;">Indulge in a variety of culinary experiences</p>

            <div class="restaurants-grid">
                <div class="restaurant-card">
                    <div class="restaurant-image">
                        <img src="https://images.unsplash.com/photo-1559339352-11d035aa65de?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=768&h=512&q=80" alt="Fine Dining Restaurant">
                    </div>
                    <div class="restaurant-content" style="padding: 25px;">
                        <h3 class="restaurant-name">Azure Mediterranean</h3>
                        <p class="restaurant-description">Enjoy Mediterranean cuisine with a modern twist, featuring fresh seafood and local ingredients.</p>
                        <div class="restaurant-details">
                            <div class="restaurant-hours">
                                <strong>Hours:</strong> 12:00 PM - 11:00 PM
                            </div>
                            <div class="restaurant-cuisine">
                                <strong>Cuisine:</strong> Mediterranean
                            </div>
                        </div>
                        <a href="auth/login.php" class="btn btn-secondary">Reserve a Table</a>
                    </div>
                </div>

                <div class="restaurant-card">
                    <div class="restaurant-image">
                        <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=768&h=512&q=80" alt="Casual Dining Restaurant">
                    </div>
                    <div class="restaurant-content" style="padding: 25px;">
                        <h3 class="restaurant-name">Citrus Lounge</h3>
                        <p class="restaurant-description">A casual dining experience offering international cuisine and signature cocktails in a relaxed atmosphere.</p>
                        <div class="restaurant-details">
                            <div class="restaurant-hours">
                                <strong>Hours:</strong> 7:00 AM - 12:00 AM
                            </div>
                            <div class="restaurant-cuisine">
                                <strong>Cuisine:</strong> International
                            </div>
                        </div>
                        <a href="auth/login.php" class="btn btn-secondary">Reserve a Table</a>
                    </div>
                </div>

                <div class="restaurant-card">
                    <div class="restaurant-image">
                        <img src="https://images.unsplash.com/photo-1590523741831-ab7e8b8f9c7f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=768&h=512&q=80" alt="Rooftop Bar">
                    </div>
                    <div class="restaurant-content" style="padding: 25px;">
                        <h3 class="restaurant-name">Skyline Rooftop Bar</h3>
                        <p class="restaurant-description">Enjoy panoramic views of Tangier while savoring premium cocktails and light bites at our rooftop bar.</p>
                        <div class="restaurant-details">
                            <div class="restaurant-hours">
                                <strong>Hours:</strong> 5:00 PM - 1:00 AM
                            </div>
                            <div class="restaurant-cuisine">
                                <strong>Cuisine:</strong> Tapas & Cocktails
                            </div>
                        </div>
                        <a href="auth/login.php" class="btn btn-secondary">Reserve a Table</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="special-offers" style="padding: 80px 0; background-color: #f9f9f9;">
        <div class="container">
            <h2 class="section-title" style="font-size: 2.5rem; margin-bottom: 15px; text-align: center; color: #333; font-family: 'Dancing Script', cursive;">Special Dining Offers</h2>
            <p class="section-subtitle" style="text-align: center; margin-bottom: 40px; font-size: 1.2rem; color: #666;">Exclusive culinary experiences for our guests</p>

            <div class="offers-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-top: 30px;">
                <div class="offer-card" style="position: relative; height: 400px; border-radius: 15px; overflow: hidden; box-shadow: 0 15px 30px rgba(0,0,0,0.1); transition: transform 0.3s ease, box-shadow 0.3s ease;">
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-image: url('https://images.unsplash.com/photo-1482275548304-a58859dc31b7?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=768&h=512&q=80'); background-size: cover; background-position: center; z-index: 1;"></div>
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to top, rgba(0,0,0,0.8) 30%, transparent 100%); z-index: 2;"></div>
                    <div class="offer-content" style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 30px; z-index: 3; color: white;">
                        <div style="display: inline-block; background-color: #d4af37; color: white; font-size: 0.8rem; padding: 5px 15px; border-radius: 20px; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 1px;">Every Sunday</div>
                        <h3 class="offer-title" style="font-size: 1.8rem; margin-bottom: 15px; font-weight: 700;">Sunday Brunch</h3>
                        <p class="offer-description" style="margin-bottom: 20px; line-height: 1.6; opacity: 0.9;">Enjoy our lavish Sunday Brunch featuring international cuisine, live cooking stations, and free-flowing beverages.</p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <p class="offer-price" style="font-size: 1.5rem; font-weight: 700; color: #d4af37;">$45 <span style="font-size: 0.9rem; opacity: 0.8;">per person</span></p>
                            <a href="auth/login.php" class="btn btn-primary" style="background-color: #d4af37; border: none; padding: 10px 20px; border-radius: 30px; font-weight: 600; transition: transform 0.2s ease; text-transform: uppercase; letter-spacing: 1px; font-size: 0.9rem;">Book Now</a>
                        </div>
                    </div>
                </div>

                <div class="offer-card" style="position: relative; height: 400px; border-radius: 15px; overflow: hidden; box-shadow: 0 15px 30px rgba(0,0,0,0.1); transition: transform 0.3s ease, box-shadow 0.3s ease;">
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-image: url('https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=768&h=512&q=80'); background-size: cover; background-position: center; z-index: 1;"></div>
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to top, rgba(0,0,0,0.8) 30%, transparent 100%); z-index: 2;"></div>
                    <div class="offer-content" style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 30px; z-index: 3; color: white;">
                        <div style="display: inline-block; background-color: #8b0000; color: white; font-size: 0.8rem; padding: 5px 15px; border-radius: 20px; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 1px;">Friday & Saturday</div>
                        <h3 class="offer-title" style="font-size: 1.8rem; margin-bottom: 15px; font-weight: 700;">Wine & Dine</h3>
                        <p class="offer-description" style="margin-bottom: 20px; line-height: 1.6; opacity: 0.9;">A special 4-course dinner with wine pairing, featuring the finest selections from our sommelier.</p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <p class="offer-price" style="font-size: 1.5rem; font-weight: 700; color: #8b0000;">$75 <span style="font-size: 0.9rem; opacity: 0.8;">per person</span></p>
                            <a href="auth/login.php" class="btn btn-primary" style="background-color: #8b0000; border: none; padding: 10px 20px; border-radius: 30px; font-weight: 600; transition: transform 0.2s ease; text-transform: uppercase; letter-spacing: 1px; font-size: 0.9rem;">Book Now</a>
                        </div>
                    </div>
                </div>

                <div class="offer-card" style="position: relative; height: 400px; border-radius: 15px; overflow: hidden; box-shadow: 0 15px 30px rgba(0,0,0,0.1); transition: transform 0.3s ease, box-shadow 0.3s ease;">
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-image: url('https://images.unsplash.com/photo-1579631542720-3a87824fff86?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=768&h=512&q=80'); background-size: cover; background-position: center; z-index: 1;"></div>
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to top, rgba(0,0,0,0.8) 30%, transparent 100%); z-index: 2;"></div>
                    <div class="offer-content" style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 30px; z-index: 3; color: white;">
                        <div style="display: inline-block; background-color: #1e6091; color: white; font-size: 0.8rem; padding: 5px 15px; border-radius: 20px; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 1px;">Every Thursday</div>
                        <h3 class="offer-title" style="font-size: 1.8rem; margin-bottom: 15px; font-weight: 700;">Seafood Night</h3>
                        <p class="offer-description" style="margin-bottom: 20px; line-height: 1.6; opacity: 0.9;">Indulge in the freshest seafood from the Mediterranean every Thursday night.</p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <p class="offer-price" style="font-size: 1.5rem; font-weight: 700; color: #1e6091;">$60 <span style="font-size: 0.9rem; opacity: 0.8;">per person</span></p>
                            <a href="auth/login.php" class="btn btn-primary" style="background-color: #1e6091; border: none; padding: 10px 20px; border-radius: 30px; font-weight: 600; transition: transform 0.2s ease; text-transform: uppercase; letter-spacing: 1px; font-size: 0.9rem;">Book Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <
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
                <p>© Hilton Hotels & Resorts 2025</p>
            </div>
        </div>
    </footer>
    
    <script src="js/main.js"></script>
</body>

</html>
