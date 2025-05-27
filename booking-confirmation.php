<?php
session_start();

// Include database connection
require_once 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: auth/login.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';
$user_role = $_SESSION['user_role'];

// Check if reservation ID is provided
if (!isset($_GET['id'])) {
    header("Location: booking.php");
    exit();
}

$reservation_id = $_GET['id'];

// Fetch reservation details with enhanced information
try {
    $stmt = $pdo->prepare("
        SELECT r.*, rm.room_number, rm.type, rm.price, rm.image, rm.capacity, rm.beds, rm.size, rm.description, rm.floor,
               u.name as user_name, u.email as user_email, u.phone as user_phone, u.profile_image
        FROM reservations r
        JOIN rooms rm ON r.room_id = rm.id
        JOIN users u ON r.user_id = u.id
        WHERE r.id = ? AND r.user_id = ?
    ");
    $stmt->execute([$reservation_id, $user_id]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reservation) {
        header("Location: booking.php");
        exit();
    }
    
    // Get room amenities
    $stmt = $pdo->prepare("SELECT a.* FROM amenities a 
                          JOIN room_amenities ra ON a.id = ra.amenity_id 
                          WHERE ra.room_id = ?");
    $stmt->execute([$reservation['room_id']]);
    $amenities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate total price if not already set
    $check_in_date = new DateTime($reservation['check_in']);
    $check_out_date = new DateTime($reservation['check_out']);
    $nights = $check_in_date->diff($check_out_date)->days;
    $total_price = isset($reservation['total_price']) && $reservation['total_price'] > 0 ? 
                   $reservation['total_price'] : ($nights * $reservation['price']);
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - Hilton Hotel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/booking-new.css">
    <link rel="stylesheet" href="css/room-details.css">
    <link rel="icon" type="image/x-icon" href="https://www.hilton.com/favicon.ico">
    <style>
        .confirmation-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        .confirmation-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .confirmation-header .success-icon {
            width: 80px;
            height: 80px;
            background-color: #28a745;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        
        .confirmation-header .success-icon svg {
            width: 40px;
            height: 40px;
            color: #fff;
        }
        
        .confirmation-header h1 {
            color: #343a40;
            margin-bottom: 10px;
        }
        
        .confirmation-header p {
            color: #6c757d;
            font-size: 18px;
        }
        
        .confirmation-details {
            margin-top: 30px;
            border-top: 1px solid #e9ecef;
            padding-top: 30px;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
        }
        
        .detail-section {
            margin-bottom: 25px;
        }
        
        .detail-section h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #343a40;
            font-weight: 600;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            color: #6c757d;
            font-weight: 500;
        }
        
        .detail-value {
            color: #343a40;
            font-weight: 400;
        }
        
        .confirmation-actions {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .btn-view-reservations {
            padding: 12px 24px;
            background-color: #b89d5c;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        
        .btn-view-reservations:hover {
            background-color: #a58a4e;
        }
        
        .btn-back-home {
            padding: 12px 24px;
            background-color: #f8f9fa;
            color: #343a40;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        
        .btn-back-home:hover {
            background-color: #e9ecef;
        }
        
        @media (max-width: 768px) {
            .details-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .confirmation-actions {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <!-- Dashboard Header -->
    <header class="dashboard-header">
        <div class="dashboard-container">
            <div class="dashboard-logo">
                <a href="index.php">
                    <img src="https://www.hilton.com/modules/assets/svgs/logos/HH.svg" alt="Hilton Logo" style="height: 40px;">
                </a>
            </div>
            
            <div class="hamburger-menu">
                <div class="hamburger-icon">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
            
            <nav class="dashboard-nav">
                <!-- Mobile User Profile (Only visible in mobile menu) -->
                <div class="mobile-user-profile">
                    <div class="mobile-user-avatar">
                        <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                    </div>
                    <div class="mobile-user-name"><?php echo htmlspecialchars($user_name); ?></div>
                    <div class="mobile-user-links">
                        <a href="profile.php" class="mobile-user-link">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            My Profile
                        </a>
                        <a href="auth/logout.php" class="mobile-user-link">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                            Logout
                        </a>
                    </div>
                </div>
                
                <a href="index.php" class="dashboard-nav-link">Home</a>
                <a href="booking.php" class="dashboard-nav-link">Book a Room</a>
                <a href="my-reservations.php" class="dashboard-nav-link active">My Reservations</a>
                <a href="profile.php" class="dashboard-nav-link">My Profile</a>
                <?php if ($user_role === 'admin'): ?>
                <a href="admin/dashboard.php" class="dashboard-nav-link">Admin Panel</a>
                <?php endif; ?>
            </nav>
            
            <div class="dashboard-user">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                </div>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
                    <div class="user-dropdown">
                        <a href="profile.php" class="dropdown-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            My Profile
                        </a>
                        <a href="auth/logout.php" class="dropdown-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
            <?php else: ?>
            <div class="confirmation-container">
                <div class="confirmation-header">
                    <div class="success-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <h1>Booking Confirmed!</h1>
                    <p>Your reservation has been successfully created and is pending approval.</p>
                    <p>Confirmation #: <strong><?php echo $reservation_id; ?></strong></p>
                </div>
                
                <div class="confirmation-details">
                    <div class="details-grid">
                        <div>
                            <div class="detail-section">
                                <h3>Reservation Details</h3>
                                <div class="detail-row">
                                    <span class="detail-label">Confirmation #</span>
                                    <span class="detail-value"><?php echo sprintf('HLT%06d', $reservation['id']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Check-in Date:</span>
                                    <span class="detail-value"><?php echo date('F d, Y', strtotime($reservation['check_in'])); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Check-out Date:</span>
                                    <span class="detail-value"><?php echo date('F d, Y', strtotime($reservation['check_out'])); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Number of Nights:</span>
                                    <span class="detail-value"><?php echo $nights; ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Status:</span>
                                    <span class="detail-value">
                                        <span class="activity-status status-<?php echo $reservation['status']; ?>">
                                            <?php echo ucfirst($reservation['status']); ?>
                                        </span>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="detail-section">
                                <h3>Guest Information</h3>
                                <div class="detail-row">
                                    <span class="detail-label">Name:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($reservation['user_name']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Email:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($reservation['user_email']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Phone:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($reservation['phone']); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="detail-section">
                                <h3>Room Information</h3>
                                <div class="detail-row">
                                    <span class="detail-label">Room Type:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($reservation['type']); ?> Room</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Room Number:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($reservation['room_number']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Price per Night:</span>
                                    <span class="detail-value">$<?php echo number_format($reservation['price'], 2); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Total Price:</span>
                                    <span class="detail-value">$<?php echo number_format($total_price, 2); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Payment Status:</span>
                                    <span class="detail-value"><?php 
                                        $status_class = '';
                                        switch($reservation['payment_status']) {
                                            case 'paid': $status_class = 'status-success'; break;
                                            case 'pending': $status_class = 'status-warning'; break;
                                            case 'refunded': $status_class = 'status-info'; break;
                                            default: $status_class = 'status-warning';
                                        }
                                        echo '<span class="status-badge ' . $status_class . '">'.ucfirst($reservation['payment_status']).'</span>'; 
                                    ?></span>
                                </div>
                                <?php if (!empty($reservation['special_requests'])): ?>
                                <div class="detail-row">
                                    <span class="detail-label">Special Requests:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($reservation['special_requests']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($reservation['special_requests'])): ?>
                            <div class="detail-section">
                                <h3>Special Requests</h3>
                                <p><?php echo nl2br(htmlspecialchars($reservation['special_requests'])); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="confirmation-actions">
                    <a href="my-reservations.php" class="btn-view-reservations">View All Reservations</a>
                    <a href="index.php" class="btn-back-home">Back to Home</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section footer-about">
                    <h3 class="footer-title">Hilton Hotel</h3>
                    <p class="footer-text">
                        Experience luxury and comfort at Hilton Hotel.
                    </p>
                </div>

                <div class="footer-section">
                    <h4 class="footer-heading">Contact Us</h4>
                    <p class="footer-contact">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        Place du Maghreb Arabe, Tanger 90000, Morocco
                    </p>
                    <p class="footer-contact">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                        +212 5393-09700
                    </p>
                    <p class="footer-contact">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        info@hiltonhotel.com
                    </p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Hilton Hotel. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile Navigation Toggle
        const hamburgerMenu = document.querySelector('.hamburger-icon');
        const nav = document.querySelector('.dashboard-nav');
        
        if (hamburgerMenu) {
            hamburgerMenu.addEventListener('click', function() {
                this.classList.toggle('open');
                nav.classList.toggle('open');
                
                // Prevent scrolling when menu is open
                document.body.classList.toggle('no-scroll');
            });
        }
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (hamburgerMenu && nav) {
                const isClickInsideNav = nav.contains(event.target);
                const isClickInsideHamburger = hamburgerMenu.contains(event.target);
                
                if (!isClickInsideNav && !isClickInsideHamburger && nav.classList.contains('open')) {
                    hamburgerMenu.classList.remove('open');
                    nav.classList.remove('open');
                    document.body.classList.remove('no-scroll');
                }
            }
        });
        
        // Close menu when clicking on a nav link
        const navLinks = document.querySelectorAll('.dashboard-nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (hamburgerMenu && nav) {
                    hamburgerMenu.classList.remove('open');
                    nav.classList.remove('open');
                    document.body.classList.remove('no-scroll');
                }
            });
        });
    });
    </script>
</body>

</html>
