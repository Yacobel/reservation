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
$user_email = $_SESSION['user_email'];
$user_role = $_SESSION['user_role'];

// Fetch user's reservations with detailed information
try {
    $stmt = $pdo->prepare("SELECT r.*, rm.room_number, rm.type, rm.price, rm.image 
                          FROM reservations r 
                          JOIN rooms rm ON r.room_id = rm.id 
                          WHERE r.user_id = ? 
                          ORDER BY r.created_at DESC");
    $stmt->execute([$user_id]);
    $user_reservations = $stmt->fetchAll();
} catch (PDOException $e) {
    $reservation_error = "Could not fetch reservations: " . $e->getMessage();
}

// Get user details from database
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_details = $stmt->fetch();
} catch (PDOException $e) {
    $user_error = "Could not fetch user details: " . $e->getMessage();
}

// Calculate some stats
$total_reservations = count($user_reservations);
$active_reservations = 0;
$completed_reservations = 0;
$cancelled_reservations = 0;
$total_spent = 0;

foreach ($user_reservations as $reservation) {
    // Calculate nights and total price
    $check_in = new DateTime($reservation['check_in']);
    $check_out = new DateTime($reservation['check_out']);
    $nights = $check_out->diff($check_in)->days;
    $reservation_total = $nights * $reservation['price'];
    
    // Count by status
    if ($reservation['status'] === 'confirmed') {
        $active_reservations++;
        $total_spent += $reservation_total;
    } elseif ($reservation['status'] === 'completed') {
        $completed_reservations++;
        $total_spent += $reservation_total;
    } elseif ($reservation['status'] === 'cancelled') {
        $cancelled_reservations++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Hilton Tanger City Center</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/booking.css">
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="icon" type="image/x-icon" href="https://www.hilton.com/favicon.ico">
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
                <a href="my-reservations.php" class="dashboard-nav-link">My Reservations</a>
                <a href="profile.php" class="dashboard-nav-link active">My Profile</a>
                <?php if ($user_role === 'admin'): ?>
                <a href="admin/dashboard.php" class="dashboard-nav-link">Admin Panel</a>
                <?php endif; ?>
            </nav>
            
            <div class="dashboard-user">
                <div class="user-avatar <?php echo $user_role === 'admin' ? 'admin-avatar' : 'user-avatar'; ?>">
                    <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                </div>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
                    <div class="user-dropdown">
                        <a href="profile.php" class="dropdown-item active">
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
            
            <div class="hamburger-menu">
                <div class="hamburger-icon">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="profile-header">
                <div class="profile-avatar">
                    <div class="avatar-placeholder <?php echo $user_role === 'admin' ? 'admin-avatar' : 'user-avatar'; ?>">
                        <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                    </div>
                </div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($user_name); ?></h1>
                    <p class="profile-email"><?php echo htmlspecialchars($user_email); ?></p>
                    <p class="profile-member">Member since <?php echo date('F d, Y', strtotime($user_details['created_at'])); ?></p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon stays-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $total_reservations; ?></h3>
                        <p>Total Stays</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon active-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $active_reservations; ?></h3>
                        <p>Active Reservations</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon spent-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($total_spent, 2); ?> MAD</h3>
                        <p>Total Spent</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon status-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $user_role === 'admin' ? 'Admin' : 'Member'; ?></h3>
                        <p>Account Status</p>
                    </div>
                </div>
            </div>

            <!-- Reservations Section -->
            <section class="profile-section">
                <h2>My Reservations</h2>

                <?php if (isset($reservation_error)): ?>
                <div class="alert alert-error">
                    <?php echo $reservation_error; ?>
                </div>
                <?php endif; ?>

                <?php if (empty($user_reservations)): ?>
                <div class="no-reservations-message">
                    <p>You don't have any reservations yet. <a href="booking.php" class="link-primary">Book a room</a> to get started!</p>
                </div>
                <?php else: ?>
                <div class="reservations-cards">
                    <?php foreach ($user_reservations as $reservation): ?>
                    <?php 
                        // Calculate total price
                        $check_in = new DateTime($reservation['check_in']);
                        $check_out = new DateTime($reservation['check_out']);
                        $nights = $check_out->diff($check_in)->days;
                        $total_price = $nights * $reservation['price'];
                    ?>
                    <div class="reservation-card">
                        <div class="reservation-status status-<?php echo strtolower($reservation['status']); ?>">
                            <?php echo ucfirst($reservation['status']); ?>
                        </div>
                        
                        <div class="reservation-image">
                            <?php if (!empty($reservation['image'])): ?>
                                <?php 
                                // Check if image is a URL or a local path
                                $image_src = $reservation['image'];
                                
                                // If it's a URL, use it directly
                                if (filter_var($image_src, FILTER_VALIDATE_URL) || strpos($image_src, 'http') === 0) {
                                    // URL - use as is
                                } else {
                                    // Local path - handle different path formats
                                    
                                    // Remove any leading '../' as we're already at the root
                                    $image_src = preg_replace('/^\.\.\//', '', $image_src);
                                    
                                    // If the path doesn't start with 'uploads/', add it
                                    if (strpos($image_src, 'uploads/') !== 0) {
                                        // Check if it's just the filename in the rooms directory
                                        if (strpos($image_src, 'rooms/') === 0) {
                                            $image_src = 'uploads/' . $image_src;
                                        } else if (strpos($image_src, '/') !== 0) {
                                            $image_src = 'uploads/rooms/' . basename($image_src);
                                        }
                                    }
                                }
                                ?>
                                <img src="<?php echo htmlspecialchars($image_src); ?>" alt="<?php echo htmlspecialchars($reservation['type']); ?>" onerror="this.src='https://www.hilton.com/im/en/NoHotel/15483650/shutterstock-1078953762.jpg?impolicy=crop&cw=5000&ch=3333&gravity=NorthWest&xposition=0&yposition=0&rw=768&rh=512';">
                            <?php else: ?>
                                <img src="https://www.hilton.com/im/en/NoHotel/15483650/shutterstock-1078953762.jpg?impolicy=crop&cw=5000&ch=3333&gravity=NorthWest&xposition=0&yposition=0&rw=768&rh=512'" alt="<?php echo htmlspecialchars($reservation['type']); ?>">
                            <?php endif; ?>
                        </div>
                        
                        <div class="reservation-details">
                            <h3><?php echo htmlspecialchars($reservation['type']); ?></h3>
                            <p class="room-number">Room <?php echo htmlspecialchars($reservation['room_number']); ?></p>
                            
                            <div class="reservation-dates">
                                <div class="date-group">
                                    <span class="date-label">Check In</span>
                                    <span class="date-value"><?php echo date('M d, Y', strtotime($reservation['check_in'])); ?></span>
                                </div>
                                <div class="date-divider"></div>
                                <div class="date-group">
                                    <span class="date-label">Check Out</span>
                                    <span class="date-value"><?php echo date('M d, Y', strtotime($reservation['check_out'])); ?></span>
                                </div>
                            </div>
                            
                            <div class="reservation-price">
                                <div class="price-details">
                                    <div class="price-row">
                                        <span>Price per night</span>
                                        <span><?php echo number_format($reservation['price'], 2); ?> MAD</span>
                                    </div>
                                    <div class="price-row">
                                        <span>Nights</span>
                                        <span><?php echo $nights; ?></span>
                                    </div>
                                    <div class="price-row total">
                                        <span>Total</span>
                                        <span><?php echo number_format($total_price, 2); ?> MAD</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="reservation-actions">
                                <?php if ($reservation['status'] === 'pending'): ?>
                                <a href="cancel-reservation.php?id=<?php echo $reservation['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this reservation?');">Cancel Reservation</a>
                                <?php elseif ($reservation['status'] === 'confirmed'): ?>
                                <a href="view-reservation.php?id=<?php echo $reservation['id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </section>

            <!-- Account Settings Section -->
            <section class="profile-section">
                <h2>Account Settings</h2>
                
                <div class="account-settings">
                    <div class="settings-card">
                        <h3>Personal Information</h3>
                        <form class="settings-form" method="POST" action="update-profile.php">
                            <div class="form-group">
                                <label for="fullName">Full Name</label>
                                <input type="text" id="fullName" name="fullName" class="form-input" value="<?php echo htmlspecialchars($user_name); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($user_email); ?>" required>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="settings-card">
                        <h3>Change Password</h3>
                        <form class="settings-form" method="POST" action="update-password.php">
                            <div class="form-group">
                                <label for="currentPassword">Current Password</label>
                                <input type="password" id="currentPassword" name="currentPassword" class="form-input" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="newPassword">New Password</label>
                                <input type="password" id="newPassword" name="newPassword" class="form-input" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirmPassword">Confirm New Password</label>
                                <input type="password" id="confirmPassword" name="confirmPassword" class="form-input" required>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Update Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section footer-about">
                    <h3 class="footer-title">Hilton Tanger City Center</h3>
                    <p class="footer-text">
                        Located in the heart of Tangier, our hotel offers stunning views of the Mediterranean Sea.
                    </p>
                </div>

                <div class="footer-section">
                    <h4 class="footer-heading">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="booking.php">Rooms</a></li>
                        <li><a href="#">Dining</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4 class="footer-heading">Contact</h4>
                    <p>Place du Maghreb Arabe<br>Tangier, 90000, Morocco</p>
                    <p>Phone: +212 539 34 0100</p>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Hilton Hotels & Resorts</p>
            </div>
        </div>
    </footer>

    <script>
        // User dropdown menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const userMenu = document.querySelector('.user-name');
            const userDropdown = document.querySelector('.user-dropdown');
            
            if (userMenu) {
                userMenu.addEventListener('click', function() {
                    userDropdown.classList.toggle('active');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(event) {
                    if (!event.target.closest('.user-menu')) {
                        userDropdown.classList.remove('active');
                    }
                });
            }
            
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