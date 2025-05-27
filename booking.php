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
$user_role = $_SESSION['user_role'];

// Fetch available rooms from database
try {
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE status = 'available' ORDER BY price ASC");
    $stmt->execute();
    $available_rooms = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Handle room booking
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_room'])) {
    $room_id = $_POST['room_id'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    
    // Basic validation
    $errors = [];
    
    if (empty($check_in)) {
        $errors[] = "Check-in date is required";
    }
    
    if (empty($check_out)) {
        $errors[] = "Check-out date is required";
    }
    
    // Validate dates
    $today = date('Y-m-d');
    $check_in_date = new DateTime($check_in);
    $check_out_date = new DateTime($check_out);
    $today_date = new DateTime($today);
    
    if ($check_in_date < $today_date) {
        $errors[] = "Check-in date cannot be in the past";
    }
    
    if ($check_out_date <= $check_in_date) {
        $errors[] = "Check-out date must be after check-in date";
    }
    
    // If no errors, proceed with booking
    if (empty($errors)) {
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            // Insert reservation
            $stmt = $pdo->prepare("INSERT INTO reservations (user_id, room_id, check_in, check_out, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
            $result = $stmt->execute([$user_id, $room_id, $check_in, $check_out]);
            
            // Update room status
            if ($result) {
                $stmt = $pdo->prepare("UPDATE rooms SET status = 'booked' WHERE id = ?");
                $stmt->execute([$room_id]);
                
                // Commit transaction
                $pdo->commit();
                
                // Set success message
                $success_message = "Room booked successfully! Your reservation is pending confirmation.";
                
                // Refresh available rooms list
                $stmt = $pdo->prepare("SELECT * FROM rooms WHERE status = 'available' ORDER BY price ASC");
                $stmt->execute();
                $available_rooms = $stmt->fetchAll();
            }
        } catch (PDOException $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            $error_message = "Database error: " . $e->getMessage();
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

// Fetch user's reservations
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Stay - Hilton Tanger City Center</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/booking-new.css">
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
                <a href="booking.php" class="dashboard-nav-link active">Book a Room</a>
                <a href="my-reservations.php" class="dashboard-nav-link">My Reservations</a>
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
            <div class="page-header">
                <h1>Book Your Stay</h1>
                <p>Select from our luxurious rooms and suites for your perfect getaway</p>
            </div>

            <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>

            <!-- Room Listings -->
            <section class="rooms-section">
                <div class="section-header">
                    <h1>Available Rooms</h1>
                    <p>Book your stay at our luxurious hotel</p>
                </div>

                <div class="room-filters">
                    <div class="filter-group">
                        <label for="room-type">Room Type</label>
                        <select id="room-type" class="filter-select">
                            <option value="all">All Types</option>
                            <option value="Standard">Standard</option>
                            <option value="Deluxe">Deluxe</option>
                            <option value="Suite">Suite</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="price-range">Price Range</label>
                        <select id="price-range" class="filter-select">
                            <option value="all">All Prices</option>
                            <option value="0-100">$0 - $100</option>
                            <option value="100-200">$100 - $200</option>
                            <option value="200-300">$200 - $300</option>
                            <option value="300+">$300+</option>
                        </select>
                    </div>
                    <button id="apply-filters" class="btn btn-primary">Apply Filters</button>
                </div>

                <div class="rooms-grid">
                    <?php foreach ($available_rooms as $room): ?>
                    <div class="room-card" data-type="<?php echo htmlspecialchars($room['type']); ?>" data-price="<?php echo $room['price']; ?>">
                        <div class="room-image">
                            <?php if (!empty($room['image'])): ?>
                                <?php 
                                // Check if image is a URL or a local path
                                $image_src = $room['image'];
                                
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
                                <img src="<?php echo htmlspecialchars($image_src); ?>" alt="<?php echo htmlspecialchars($room['type']); ?> Room" onerror="this.src='https://www.hilton.com/im/en/NoHotel/15483650/shutterstock-1078953762.jpg?impolicy=crop&cw=5000&ch=3333&gravity=NorthWest&xposition=0&yposition=0&rw=768&rh=512';">
                            <?php else: ?>
                                <img src="https://www.hilton.com/im/en/NoHotel/15483650/shutterstock-1078953762.jpg?impolicy=crop&cw=5000&ch=3333&gravity=NorthWest&xposition=0&yposition=0&rw=768&rh=512" alt="<?php echo htmlspecialchars($room['type']); ?> Room">
                            <?php endif; ?>
                        </div>
                        <div class="room-details">
                            <h3><?php echo htmlspecialchars($room['type']); ?> Room</h3>
                            <p class="room-number">Room <?php echo htmlspecialchars($room['room_number']); ?></p>
                            <p class="room-price">$<?php echo number_format($room['price'], 2); ?> <span>per night</span></p>
                            <a href="room-details.php?id=<?php echo $room['id']; ?>" class="btn btn-book">View Details</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- No Rooms Message -->
                <?php if (count($available_rooms) === 0): ?>
                <div class="no-rooms">
                    <div class="no-data-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                    </div>
                    <h3>No Available Rooms</h3>
                    <p>There are no rooms available at the moment. Please check back later.</p>
                </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <!-- Booking Modal -->
    <div class="modal" id="bookingModal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Book Your Room</h2>
            <p id="modalRoomType"></p>

            <form method="POST" action="" id="bookingForm">
                <input type="hidden" name="room_id" id="roomIdInput">
                <input type="hidden" name="book_room" value="1">

                <div class="form-group">
                    <label for="checkInDate">Check In Date</label>
                    <input type="date" id="checkInDate" name="check_in" class="form-input" required min="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label for="checkOutDate">Check Out Date</label>
                    <input type="date" id="checkOutDate" name="check_out" class="form-input" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancelBooking">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm Booking</button>
                </div>
            </form>
        </div>
    </div>

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
                        <li><a href="#">Rooms</a></li>
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
        // Booking modal functionality
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('bookingModal');
            const bookButtons = document.querySelectorAll('.book-btn');
            const closeModal = document.querySelector('.close-modal');
            const cancelButton = document.getElementById('cancelBooking');
            const roomIdInput = document.getElementById('roomIdInput');
            const modalRoomType = document.getElementById('modalRoomType');
            const userMenu = document.querySelector('.user-name');
            const userDropdown = document.querySelector('.user-dropdown');

            // Open modal when book button is clicked
            bookButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const roomId = this.getAttribute('data-room-id');
                    const roomType = this.getAttribute('data-room-type');
                    
                    roomIdInput.value = roomId;
                    modalRoomType.textContent = roomType;
                    modal.style.display = 'flex';
                });
            });

            // Close modal
            closeModal.addEventListener('click', function() {
                modal.style.display = 'none';
            });

            cancelButton.addEventListener('click', function() {
                modal.style.display = 'none';
            });

            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });

            // User dropdown menu
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

            // Date validation
            const checkInDate = document.getElementById('checkInDate');
            const checkOutDate = document.getElementById('checkOutDate');

            checkInDate.addEventListener('change', function() {
                // Set minimum check-out date to be one day after check-in
                const nextDay = new Date(this.value);
                nextDay.setDate(nextDay.getDate() + 1);
                
                const year = nextDay.getFullYear();
                const month = String(nextDay.getMonth() + 1).padStart(2, '0');
                const day = String(nextDay.getDate()).padStart(2, '0');
                
                checkOutDate.min = `${year}-${month}-${day}`;
                
                // If current check-out date is before new minimum, update it
                if (checkOutDate.value && new Date(checkOutDate.value) <= new Date(this.value)) {
                    checkOutDate.value = `${year}-${month}-${day}`;
                }
            });

            // Room filtering functionality
            const roomTypeFilter = document.getElementById('room-type');
            const priceRangeFilter = document.getElementById('price-range');
            const applyFiltersBtn = document.getElementById('apply-filters');
            const roomCards = document.querySelectorAll('.room-card');
            const noRoomsMessage = document.querySelector('.no-rooms');

            // Function to filter rooms based on selected criteria
            function filterRooms() {
                const selectedType = roomTypeFilter.value;
                const selectedPriceRange = priceRangeFilter.value;
                let visibleCount = 0;

                roomCards.forEach(card => {
                    const roomType = card.getAttribute('data-type');
                    const roomPrice = parseFloat(card.getAttribute('data-price'));
                    let typeMatch = selectedType === 'all' || roomType === selectedType;
                    let priceMatch = false;

                    // Check price range match
                    if (selectedPriceRange === 'all') {
                        priceMatch = true;
                    } else if (selectedPriceRange === '0-100') {
                        priceMatch = roomPrice >= 0 && roomPrice <= 100;
                    } else if (selectedPriceRange === '100-200') {
                        priceMatch = roomPrice > 100 && roomPrice <= 200;
                    } else if (selectedPriceRange === '200-300') {
                        priceMatch = roomPrice > 200 && roomPrice <= 300;
                    } else if (selectedPriceRange === '300+') {
                        priceMatch = roomPrice > 300;
                    }

                    // Show or hide room card based on filter match
                    if (typeMatch && priceMatch) {
                        card.style.display = 'block';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Show or hide the "No Rooms" message based on filter results
                if (noRoomsMessage) {
                    if (visibleCount === 0) {
                        noRoomsMessage.style.display = 'flex';
                        noRoomsMessage.querySelector('h3').textContent = 'No Matching Rooms';
                        noRoomsMessage.querySelector('p').textContent = 'No rooms match your selected filters. Please try different criteria.';
                    } else {
                        noRoomsMessage.style.display = 'none';
                    }
                }
            }

            // Apply filters when button is clicked
            if (applyFiltersBtn) {
                applyFiltersBtn.addEventListener('click', filterRooms);
            }

            // Apply filters when select values change (optional for better UX)
            roomTypeFilter.addEventListener('change', function() {
                filterRooms();
            });

            priceRangeFilter.addEventListener('change', function() {
                filterRooms();
            });
            
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
