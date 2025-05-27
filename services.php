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

// Fetch all active services
try {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE is_active = TRUE ORDER BY name ASC");
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Handle service booking
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_service'])) {
    $service_id = $_POST['service_id'];
    $reservation_id = isset($_POST['reservation_id']) ? $_POST['reservation_id'] : null;
    $booking_date = $_POST['booking_date'];
    $booking_time = $_POST['booking_time'];
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
    
    // Combine date and time
    $booking_datetime = $booking_date . ' ' . $booking_time;
    
    // Basic validation
    $errors = [];
    
    if (empty($service_id)) {
        $errors[] = "Service is required";
    }
    
    if (empty($booking_date)) {
        $errors[] = "Booking date is required";
    }
    
    if (empty($booking_time)) {
        $errors[] = "Booking time is required";
    }
    
    // Validate date
    $today = date('Y-m-d');
    $booking_date_obj = new DateTime($booking_date);
    $today_date = new DateTime($today);
    
    if ($booking_date_obj < $today_date) {
        $errors[] = "Booking date cannot be in the past";
    }
    
    // If no errors, proceed with booking
    if (empty($errors)) {
        try {
            // Insert service booking
            $stmt = $pdo->prepare("INSERT INTO service_bookings (user_id, service_id, reservation_id, booking_date, status, notes, created_at) VALUES (?, ?, ?, ?, 'pending', ?, NOW())");
            $result = $stmt->execute([$user_id, $service_id, $reservation_id, $booking_datetime, $notes]);
            
            if ($result) {
                $success_message = "Service booked successfully!";
            }
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

// Fetch user's active reservations for dropdown
try {
    $stmt = $pdo->prepare("SELECT r.id, r.check_in, r.check_out, rm.room_number, rm.type 
                          FROM reservations r 
                          JOIN rooms rm ON r.room_id = rm.id 
                          WHERE r.user_id = ? AND r.status IN ('pending', 'confirmed') 
                          ORDER BY r.check_in DESC");
    $stmt->execute([$user_id]);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Services - Hilton Hotel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/booking-new.css">
    <link rel="icon" type="image/x-icon" href="https://www.hilton.com/favicon.ico">
    <style>
        .services-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        
        .service-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        
        .service-image {
            height: 200px;
            overflow: hidden;
        }
        
        .service-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .service-card:hover .service-image img {
            transform: scale(1.05);
        }
        
        .service-details {
            padding: 20px;
        }
        
        .service-name {
            font-size: 20px;
            font-weight: 600;
            color: #343a40;
            margin-bottom: 10px;
        }
        
        .service-description {
            color: #6c757d;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .service-price {
            font-size: 18px;
            font-weight: 600;
            color: #b89d5c;
            margin-bottom: 15px;
        }
        
        .service-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .btn-book-service {
            padding: 10px 20px;
            background-color: #b89d5c;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .btn-book-service:hover {
            background-color: #a58a4e;
        }
        
        /* Service Booking Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow: auto;
        }
        
        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            position: relative;
        }
        
        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
        }
        
        .close-modal:hover {
            color: #333;
        }
        
        .modal-header {
            margin-bottom: 20px;
        }
        
        .modal-header h2 {
            margin: 0;
            color: #343a40;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #343a40;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .form-control:focus {
            border-color: #b89d5c;
            outline: none;
        }
        
        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: #b89d5c;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .btn-submit:hover {
            background-color: #a58a4e;
        }
    </style>
</head>

<body>
    <!-- Dashboard Header -->
    <header class="dashboard-header">
        <div class="dashboard-container">
            <div class="dashboard-logo">
                <a href="index.php">
                    <img src="https://www.hilton.com/modules/assets/svgs/logos/HH_logo.svg" alt="Hilton Hotel Logo">
                </a>
            </div>
            <nav class="dashboard-nav">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="booking.php">Book a Room</a></li>
                    <li><a href="services.php" class="active">Services</a></li>
                    <li><a href="my-reservations.php">My Reservations</a></li>
                    <?php if ($user_role === 'admin'): ?>
                    <li><a href="admin/index.php">Admin Dashboard</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="dashboard-user">
                <div class="user-dropdown">
                    <button class="user-dropdown-btn">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span><?php echo htmlspecialchars($user_name); ?></span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <div class="dropdown-content">
                        <a href="profile.php" class="dropdown-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            Profile
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
            
            <div class="page-header">
                <h1>Hotel Services</h1>
                <p>Enhance your stay with our premium hotel services</p>
            </div>
            
            <div class="services-container">
                <?php if (!empty($services)): ?>
                    <?php foreach ($services as $service): ?>
                    <div class="service-card">
                        <div class="service-image">
                            <img src="<?php echo !empty($service['image']) ? htmlspecialchars($service['image']) : 'https://www.hilton.com/im/en/NoHotel/15483650/shutterstock-1078953762.jpg?impolicy=crop&cw=5000&ch=3333&gravity=NorthWest&xposition=0&yposition=0&rw=768&rh=512'; ?>" alt="<?php echo htmlspecialchars($service['name']); ?>">
                        </div>
                        <div class="service-details">
                            <h3 class="service-name"><?php echo htmlspecialchars($service['name']); ?></h3>
                            <p class="service-description"><?php echo htmlspecialchars($service['description']); ?></p>
                            <div class="service-price">$<?php echo number_format($service['price'], 2); ?></div>
                            <div class="service-actions">
                                <button class="btn-book-service" data-service-id="<?php echo $service['id']; ?>" data-service-name="<?php echo htmlspecialchars($service['name']); ?>" data-service-price="<?php echo $service['price']; ?>">Book Now</button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-services">
                        <p>No services available at the moment. Please check back later.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Service Booking Modal -->
    <div id="serviceModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <div class="modal-header">
                <h2>Book Service</h2>
                <p id="modalServiceName"></p>
            </div>
            <form id="serviceBookingForm" method="post" action="">
                <input type="hidden" id="service_id" name="service_id">
                
                <div class="form-group">
                    <label for="reservation_id">Link to Reservation (Optional):</label>
                    <select class="form-control" id="reservation_id" name="reservation_id">
                        <option value="">-- Select a Reservation --</option>
                        <?php if (!empty($reservations)): ?>
                            <?php foreach ($reservations as $reservation): ?>
                                <option value="<?php echo $reservation['id']; ?>">
                                    Room <?php echo htmlspecialchars($reservation['room_number']); ?> (<?php echo htmlspecialchars($reservation['type']); ?>) - 
                                    <?php echo date('M d, Y', strtotime($reservation['check_in'])); ?> to 
                                    <?php echo date('M d, Y', strtotime($reservation['check_out'])); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="booking_date">Date:</label>
                    <input type="date" class="form-control" id="booking_date" name="booking_date" required min="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="booking_time">Time:</label>
                    <input type="time" class="form-control" id="booking_time" name="booking_time" required>
                </div>
                
                <div class="form-group">
                    <label for="notes">Special Requests:</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any special requests or notes for this service?"></textarea>
                </div>
                
                <div class="form-group">
                    <p>Price: <span id="modalServicePrice"></span></p>
                </div>
                
                <button type="submit" name="book_service" class="btn-submit">Confirm Booking</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="https://www.hilton.com/modules/assets/svgs/logos/HH_logo.svg" alt="Hilton Hotel Logo">
                </div>
                <div class="footer-links">
                    <div class="footer-links-column">
                        <h3>About</h3>
                        <ul>
                            <li><a href="#">About Us</a></li>
                            <li><a href="#">Careers</a></li>
                            <li><a href="#">Investor Relations</a></li>
                            <li><a href="#">Press Center</a></li>
                        </ul>
                    </div>
                    <div class="footer-links-column">
                        <h3>Support</h3>
                        <ul>
                            <li><a href="#">Contact Us</a></li>
                            <li><a href="#">FAQs</a></li>
                            <li><a href="#">Terms & Conditions</a></li>
                            <li><a href="#">Privacy Policy</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Hilton Hotel. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal functionality
        const modal = document.getElementById('serviceModal');
        const bookButtons = document.querySelectorAll('.btn-book-service');
        const closeModalBtn = document.querySelector('.close-modal');
        const modalServiceName = document.getElementById('modalServiceName');
        const modalServicePrice = document.getElementById('modalServicePrice');
        const serviceIdInput = document.getElementById('service_id');
        
        // Open modal when book button is clicked
        bookButtons.forEach(button => {
            button.addEventListener('click', function() {
                const serviceId = this.getAttribute('data-service-id');
                const serviceName = this.getAttribute('data-service-name');
                const servicePrice = this.getAttribute('data-service-price');
                
                serviceIdInput.value = serviceId;
                modalServiceName.textContent = serviceName;
                modalServicePrice.textContent = '$' + parseFloat(servicePrice).toFixed(2);
                
                modal.style.display = 'block';
            });
        });
        
        // Close modal when X is clicked
        closeModalBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
        
        // Close modal when clicking outside the modal content
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
        
        // Form validation
        const serviceBookingForm = document.getElementById('serviceBookingForm');
        serviceBookingForm.addEventListener('submit', function(event) {
            const bookingDate = document.getElementById('booking_date').value;
            const bookingTime = document.getElementById('booking_time').value;
            
            if (!bookingDate || !bookingTime) {
                event.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });
    </script>
</body>

</html>
