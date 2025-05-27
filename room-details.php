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

// Check if room ID is provided
if (!isset($_GET['id'])) {
    header("Location: booking.php");
    exit();
}

$room_id = $_GET['id'];
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : '';
$check_out = isset($_GET['check_out']) ? $_GET['check_out'] : '';

// Fetch room details with amenities
try {
    // Get room details
    $stmt = $pdo->prepare("SELECT r.*, rc.name as category_name, rc.description as category_description 
                          FROM rooms r 
                          LEFT JOIN room_categories rc ON r.type = rc.name 
                          WHERE r.id = ?");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$room) {
        header("Location: booking.php");
        exit();
    }
    
    // Get room amenities
    $stmt = $pdo->prepare("SELECT a.* FROM amenities a 
                          JOIN room_amenities ra ON a.id = ra.amenity_id 
                          WHERE ra.room_id = ?");
    $stmt->execute([$room_id]);
    $amenities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Handle booking submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_booking'])) {
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $special_requests = isset($_POST['special_requests']) ? trim($_POST['special_requests']) : '';
    
    // Basic validation
    $errors = [];
    
    if (empty($check_in)) {
        $errors[] = "Check-in date is required";
    }
    
    if (empty($check_out)) {
        $errors[] = "Check-out date is required";
    }
    
    if (empty($phone)) {
        $errors[] = "Phone number is required";
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
    
    // Handle profile image upload
    $profile_image = '';
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/profiles/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Get file info
        $file_name = $_FILES['profile_image']['name'];
        $file_tmp = $_FILES['profile_image']['tmp_name'];
        $file_size = $_FILES['profile_image']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Generate unique filename
        $new_file_name = 'profile_' . $user_id . '_' . time() . '.' . $file_ext;
        $upload_path = $upload_dir . $new_file_name;
        
        // Check file extension
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_ext, $allowed_exts)) {
            $errors[] = "Only JPG, JPEG, PNG, and GIF files are allowed for profile image.";
        }
        
        // Check file size (max 2MB)
        if ($file_size > 2 * 1024 * 1024) {
            $errors[] = "Profile image size should not exceed 2MB.";
        }
        
        // Move uploaded file if no errors
        if (empty($errors) && move_uploaded_file($file_tmp, $upload_path)) {
            $profile_image = '/reservation/' . $upload_path;
        } elseif (!empty($_FILES['profile_image']['name'])) {
            $errors[] = "Failed to upload profile image. Please try again.";
        }
    }
    
    // If no errors, proceed with booking
    if (empty($errors)) {
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            // Update user profile with phone and image if provided
            if (!empty($phone) || !empty($profile_image)) {
                $update_fields = [];
                $update_params = [];
                
                if (!empty($phone)) {
                    $update_fields[] = "phone = ?";
                    $update_params[] = $phone;
                }
                
                if (!empty($profile_image)) {
                    $update_fields[] = "profile_image = ?";
                    $update_params[] = $profile_image;
                }
                
                if (!empty($update_fields)) {
                    $update_params[] = $user_id;
                    $stmt = $pdo->prepare("UPDATE users SET " . implode(", ", $update_fields) . " WHERE id = ?");
                    $stmt->execute($update_params);
                }
            }
            
            // Calculate total price
            $check_in_date = new DateTime($check_in);
            $check_out_date = new DateTime($check_out);
            $nights = $check_in_date->diff($check_out_date)->days;
            $total_price = $room['price'] * $nights;
            
            // Insert reservation with total price and payment status
            $stmt = $pdo->prepare("INSERT INTO reservations (user_id, room_id, check_in, check_out, phone, special_requests, total_price, payment_status, status, created_at) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $result = $stmt->execute([$user_id, $room_id, $check_in, $check_out, $phone, $special_requests, $total_price, 'pending', 'pending']);
            $reservation_id = $pdo->lastInsertId();
            
            // Update room status
            if ($result) {
                $stmt = $pdo->prepare("UPDATE rooms SET status = 'booked' WHERE id = ?");
                $stmt->execute([$room_id]);
                
                // Commit transaction
                $pdo->commit();
                
                // Redirect to confirmation page
                header("Location: booking-confirmation.php?id=" . $reservation_id);
                exit();
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

// Calculate total price and nights
$nights = 0;
if (!empty($check_in) && !empty($check_out)) {
    $check_in_date = new DateTime($check_in);
    $check_out_date = new DateTime($check_out);
    $nights = $check_in_date->diff($check_out_date)->days;
}
$total_price = $nights * $room['price'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Details - Hilton Hotel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/booking-new.css">
    <link rel="stylesheet" href="css/room-details.css">
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
            <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>

            <div class="room-details-container">
                <div class="room-details-left">
                    <div class="room-image-large">
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
                            <img src="https://www.hilton.com/im/en/NoHotel/15483650/shutterstock-1078953762.jpg?impolicy=crop&cw=5000&ch=3333&gravity=NorthWest&xposition=0&yposition=0&rw=768&rh=512'" alt="<?php echo htmlspecialchars($room['type']); ?> Room">
                        <?php endif; ?>
                    </div>
                    
                    <div class="room-info-section">
                        <h2><?php echo htmlspecialchars($room['type']); ?> Room</h2>
                        <p class="room-description"><?php echo htmlspecialchars($room['description'] ?? 'A comfortable and well-appointed room for your stay.'); ?></p>
                        
                        <div class="room-specs">
                            <div class="spec-item">
                                <strong>Room Size:</strong> <?php echo htmlspecialchars($room['size'] ?? '25'); ?> m²
                            </div>
                            <div class="spec-item">
                                <strong>Capacity:</strong> <?php echo htmlspecialchars($room['capacity'] ?? '2'); ?> Guests
                            </div>
                            <div class="spec-item">
                                <strong>Beds:</strong> <?php echo htmlspecialchars($room['beds'] ?? '1'); ?> <?php echo ($room['beds'] ?? 1) > 1 ? 'Beds' : 'Bed'; ?>
                            </div>
                            <div class="spec-item">
                                <strong>Floor:</strong> <?php echo htmlspecialchars($room['floor'] ?? '1'); ?>
                            </div>
                        </div>
                        
                        <h3>Room Amenities</h3>
                        <div class="room-features">
                            <?php if (!empty($amenities)): ?>
                                <?php foreach ($amenities as $amenity): ?>
                                <div class="feature">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="8 12 12 16 16 12"></polyline>
                                    </svg>
                                    <span><?php echo htmlspecialchars($amenity['name']); ?></span>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="feature">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                                        <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
                                    </svg>
                                    <span>Free WiFi</span>
                                </div>
                                <div class="feature">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                    </svg>
                                    <span>Air Conditioning</span>
                                </div>
                                <div class="feature">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="2" y="7" width="20" height="15" rx="2" ry="2"></rect>
                                        <polyline points="17 2 12 7 7 2"></polyline>
                                    </svg>
                                    <span>Flat-screen TV</span>
                                </div>
                            <?php endif; ?>
                            <div class="feature">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M5 3v4M3 5h4M6 17v4M4 19h4M13 3l4 4L3 21l4-4L13 3z"></path>
                                </svg>
                                <span>Room Service</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="room-info-section">
                        <h2>Hotel Location</h2>
                        <div class="hotel-map">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3238.2803261156584!2d-5.812079684770594!3d35.76522868017476!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd0b876a1bc6d5c3%3A0x7a05a9738bcb1aca!2sHilton%20Tanger%20City%20Center%20Hotel%20%26%20Residences!5e0!3m2!1sen!2sus!4v1621436433279!5m2!1sen!2sus" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                        </div>
                        <p class="hotel-address">
                            <strong>Hilton Hotel</strong><br>
                            Place du Maghreb Arabe, Tanger 90000, Morocco<br>
                            <a href="tel:+212539309700">+212 5393-09700</a>
                        </p>
                    </div>
                </div>
                
                <div class="room-details-right">
                    <div class="booking-summary">
                        <h2><?php echo htmlspecialchars($room['type']); ?> Room</h2>
                        <p class="room-number">Room <?php echo htmlspecialchars($room['room_number']); ?></p>
                        
                        <div class="price-details">
                            <div class="price-row">
                                <span>Price per night</span>
                                <span>$<?php echo number_format($room['price'], 2); ?></span>
                            </div>
                            <div class="price-row">
                                <span>Number of nights</span>
                                <span><?php echo $nights; ?></span>
                            </div>
                            <div class="price-row total">
                                <span>Total</span>
                                <span>$<?php echo number_format($total_price, 2); ?></span>
                            </div>
                        </div>
                        
                        <button id="openBookingModal" class="btn-book">Confirm Booking</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Booking Modal -->
    <div id="bookingModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Complete Your Booking</h2>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <form id="bookingForm" method="post" action="" enctype="multipart/form-data">
                    <input type="hidden" name="confirm_booking" value="1">
                    <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="check_in">Check-in Date</label>
                            <input type="date" id="check_in" name="check_in" class="form-control" value="<?php echo htmlspecialchars($check_in); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="check_out">Check-out Date</label>
                            <input type="date" id="check_out" name="check_out" class="form-control" value="<?php echo htmlspecialchars($check_out); ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-control" placeholder="+1 (123) 456-7890" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user_email); ?>" readonly>
                            <small>Email from your profile</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="profile_image">Upload Your Photo</label>
                        <input type="file" id="profile_image" name="profile_image" class="form-control" accept="image/*">
                        <small>For identification purposes (JPG, PNG, GIF)</small>
                    </div>

                    <div class="form-group">
                        <label for="special_requests">Special Requests</label>
                        <textarea id="special_requests" name="special_requests" class="form-control" rows="3" placeholder="Any special requests or requirements?"></textarea>
                    </div>

                    <div class="booking-summary-modal">
                        <h3>Booking Summary</h3>
                        <div class="summary-row">
                            <span><?php echo htmlspecialchars($room['type']); ?> Room</span>
                            <span>Room <?php echo htmlspecialchars($room['room_number']); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Price per night</span>
                            <span>$<?php echo number_format($room['price'], 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Number of nights</span>
                            <span id="nights-count"><?php echo $nights; ?></span>
                        </div>
                        <div class="summary-row total">
                            <span>Total</span>
                            <span id="total-price">$<?php echo number_format($total_price, 2); ?></span>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" id="cancelBooking" class="btn-cancel">Cancel</button>
                        <button type="submit" class="btn-book">Complete Booking</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal functionality
        const modal = document.getElementById('bookingModal');
        const openModalBtn = document.getElementById('openBookingModal');
        const closeModalBtn = document.querySelector('.close-modal');
        const cancelBtn = document.getElementById('cancelBooking');

        // Open modal when the button is clicked
        openModalBtn.addEventListener('click', function() {
            modal.style.display = 'block';
            document.body.classList.add('modal-open');
        });

        // Close modal when the close button is clicked
        closeModalBtn.addEventListener('click', function() {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
        });

        // Close modal when the cancel button is clicked
        cancelBtn.addEventListener('click', function() {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
        });

        // Close modal when clicking outside of it
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
                document.body.classList.remove('modal-open');
            }
        });

        // Date validation and price calculation
        const checkInInput = document.getElementById('check_in');
        const checkOutInput = document.getElementById('check_out');
        const nightsCount = document.getElementById('nights-count');
        const totalPrice = document.getElementById('total-price');
        const pricePerNight = <?php echo $room['price']; ?>;

        function updatePriceCalculation() {
            const checkIn = new Date(checkInInput.value);
            const checkOut = new Date(checkOutInput.value);
            
            if (checkIn && checkOut && checkOut > checkIn) {
                const diffTime = Math.abs(checkOut - checkIn);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                
                nightsCount.textContent = diffDays;
                totalPrice.textContent = '$' + (diffDays * pricePerNight).toFixed(2);
            }
        }

        checkInInput.addEventListener('change', function() {
            // Set minimum check-out date to be the day after check-in
            const checkInDate = new Date(this.value);
            checkInDate.setDate(checkInDate.getDate() + 1);
            const minCheckOutDate = checkInDate.toISOString().split('T')[0];
            checkOutInput.min = minCheckOutDate;
            
            // If check-out date is before new check-in date, update it
            if (checkOutInput.value && new Date(checkOutInput.value) <= new Date(this.value)) {
                checkOutInput.value = minCheckOutDate;
            }
            
            updatePriceCalculation();
        });

        checkOutInput.addEventListener('change', updatePriceCalculation);

        // Set minimum check-in date to today
        const today = new Date().toISOString().split('T')[0];
        checkInInput.min = today;
        
        // Preview uploaded profile image
        const profileImageInput = document.getElementById('profile_image');
        profileImageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Check if the file is an image
                if (!file.type.match('image.*')) {
                    alert('Please select an image file (JPG, PNG, GIF)');
                    this.value = '';
                    return;
                }
                
                // Check file size (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('File size should not exceed 2MB');
                    this.value = '';
                    return;
                }
            }
        });
    });
    </script>
</body>

</html>