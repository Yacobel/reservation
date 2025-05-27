<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: auth/login.php");
    exit();
}

// Include database connection
require_once 'config/db.php';

// Get user information
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'user';

// Get user reservations with enhanced details
$stmt = $pdo->prepare("
    SELECT r.id, r.check_in, r.check_out, r.status, r.created_at, r.phone, r.special_requests, r.total_price, r.payment_status,
           rm.room_number, rm.type, rm.price, rm.image, rm.capacity, rm.beds, rm.size, rm.description
    FROM reservations r
    JOIN rooms rm ON r.room_id = rm.id
    WHERE r.user_id = :user_id
    ORDER BY r.created_at DESC
");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate reservation statistics
$total_reservations = count($reservations);
$active_reservations = 0;
$total_spent = 0;

foreach ($reservations as $reservation) {
    // Get total price from the database or calculate if not available
    if (isset($reservation['total_price']) && $reservation['total_price'] > 0) {
        $reservation_total = $reservation['total_price'];
    } else {
        // Calculate days between check-in and check-out
        $check_in = new DateTime($reservation['check_in']);
        $check_out = new DateTime($reservation['check_out']);
        $days = $check_in->diff($check_out)->days;
        
        // Calculate total price for this reservation
        $reservation_total = $days * $reservation['price'];
    }
    
    $total_spent += $reservation_total;
    
    // Count active reservations (pending or confirmed)
    if ($reservation['status'] == 'pending' || $reservation['status'] == 'confirmed') {
        $active_reservations++;
    }
}

// Handle reservation actions (cancel, confirm, etc.)
if (isset($_POST['action']) && isset($_POST['reservation_id'])) {
    $action = $_POST['action'];
    $reservation_id = $_POST['reservation_id'];
    
    // Verify that the reservation belongs to the user and is in pending status
    $stmt = $pdo->prepare("SELECT id FROM reservations WHERE id = :id AND user_id = :user_id AND status = 'pending'");
    $stmt->bindParam(':id', $reservation_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        if ($action === 'cancel') {
            // Get room ID before deleting the reservation
            $stmt = $pdo->prepare("SELECT room_id FROM reservations WHERE id = :id");
            $stmt->bindParam(':id', $reservation_id, PDO::PARAM_INT);
            $stmt->execute();
            $room_id = $stmt->fetchColumn();
            
            // Delete the reservation
            $stmt = $pdo->prepare("DELETE FROM reservations WHERE id = :id");
            $stmt->bindParam(':id', $reservation_id, PDO::PARAM_INT);
            $stmt->execute();
            
            // Update room status to available
            if ($room_id) {
                $stmt = $pdo->prepare("UPDATE rooms SET status = 'available' WHERE id = :room_id");
                $stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
                $stmt->execute();
            }
            
            // Redirect to refresh the page
            header("Location: my-reservations.php?msg=deleted");
            exit();
        } elseif ($action === 'delete') {
            // Delete the reservation
            $stmt = $pdo->prepare("DELETE FROM reservations WHERE id = :id");
            $stmt->bindParam(':id', $reservation_id, PDO::PARAM_INT);
            $stmt->execute();
            
            // Redirect to refresh the page
            header("Location: my-reservations.php?msg=deleted");
            exit();
        }
    }
}

// Handle alert messages
$alert_message = '';
$alert_type = '';

if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'cancelled':
            $alert_message = 'Your reservation has been cancelled successfully.';
            $alert_type = 'success';
            break;
        case 'deleted':
            $alert_message = 'Your reservation has been deleted successfully.';
            $alert_type = 'success';
            break;
        case 'booked':
            $alert_message = 'Your room has been booked successfully.';
            $alert_type = 'success';
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reservations - Hilton Hotel</title>
    
    <!-- Fonts and Styles -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/my-reservations.css">
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
            
            <div class="hamburger-menu">
                <div class="hamburger-icon">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </header>

    <main class="main-content">
        <!-- Alert Messages -->
        <?php if ($alert_message): ?>
        <div class="alert alert-<?php echo $alert_type; ?>">
            <?php echo $alert_message; ?>
            <button class="close-btn">&times;</button>
        </div>
        <?php endif; ?>

        <div class="reservations-container">
            <div class="reservations-header">
                <h1>My Reservations</h1>
                <div class="reservation-stats">
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $total_reservations; ?></span>
                        <span class="stat-label">Total Reservations</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $active_reservations; ?></span>
                        <span class="stat-label">Active Reservations</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">$<?php echo number_format($total_spent, 2); ?></span>
                        <span class="stat-label">Total Spent</span>
                    </div>
                </div>
            </div>

            <div class="reservations-list">
                <?php if (count($reservations) > 0): ?>
                    <?php foreach ($reservations as $reservation): ?>
                        <?php
                        // Calculate days and total price
                        $check_in = new DateTime($reservation['check_in']);
                        $check_out = new DateTime($reservation['check_out']);
                        $days = $check_in->diff($check_out)->days;
                        $total_price = $days * $reservation['price'];
                        
                        // Determine status class for styling
                        $status_class = '';
                        switch ($reservation['status']) {
                            case 'confirmed':
                                $status_class = 'status-confirmed';
                                break;
                            case 'pending':
                                $status_class = 'status-pending';
                                break;
                            case 'cancelled':
                                $status_class = 'status-cancelled';
                                break;
                        }
                        ?>
                        <div class="reservation-card">
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
                                <div class="reservation-status <?php echo $status_class; ?>">
                                    <?php echo ucfirst($reservation['status']); ?>
                                </div>
                            </div>
                            <div class="reservation-details">
                                <h3><?php echo htmlspecialchars($reservation['type']); ?> Room</h3>
                                <p class="room-number">Room <?php echo htmlspecialchars($reservation['room_number']); ?></p>
                                
                                <div class="reservation-dates">
                                    <div class="date-group">
                                        <span class="date-label">Check-in</span>
                                        <span class="date-value"><?php echo date('M d, Y', strtotime($reservation['check_in'])); ?></span>
                                    </div>
                                    <div class="date-separator"></div>
                                    <div class="date-group">
                                        <span class="date-label">Check-out</span>
                                        <span class="date-value"><?php echo date('M d, Y', strtotime($reservation['check_out'])); ?></span>
                                    </div>
                                </div>
                                
                                <div class="reservation-price">
                                    <div class="price-details">
                                        <span class="price-label">$<?php echo number_format($reservation['price'], 2); ?> x <?php echo $days; ?> nights</span>
                                        <span class="price-value">$<?php echo number_format($total_price, 2); ?></span>
                                    </div>
                                </div>
                                
                                <div class="reservation-actions">
                                    <?php if ($reservation['status'] == 'pending'): ?>
                                    <form method="post" onsubmit="return confirm('Are you sure you want to cancel this reservation? This will permanently delete the reservation from your history.');" style="margin-right: 10px;">
                                        <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <button type="submit" class="btn btn-cancel">Cancel Reservation</button>
                                    </form>
                                    
                                    <form method="post" onsubmit="return confirm('Are you sure you want to delete this reservation? This action cannot be undone.');">
                                        <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-delete">Delete</button>
                                    </form>
                                    <?php elseif ($reservation['status'] == 'confirmed'): ?>
                                    <div class="reservation-notice">
                                        <p><i>This reservation has been confirmed by admin. Contact hotel staff for changes.</i></p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-reservations">
                        <div class="no-data-icon">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                        </div>
                        <h3>No Reservations Found</h3>
                        <p>You haven't made any reservations yet.</p>
                        <a href="booking.php" class="btn btn-primary">Book a Room</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Close alert messages
        const alertCloseButtons = document.querySelectorAll('.alert .close-btn');
        alertCloseButtons.forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.display = 'none';
            });
        }, 5000);
        
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