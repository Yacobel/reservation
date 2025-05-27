<?php
session_start();

require_once '../config/db.php';

$page_title = 'Reservation Details';

$page_css = '<link rel="stylesheet" href="css/reservation-details.css">';

echo '<style>
@import url("css/reservation-details.css");
</style>';

if (!isset($_GET['id'])) {
    header("Location: reservations.php");
    exit();
}

$reservation_id = $_GET['id'];

try {
    $stmt = $pdo->prepare("
        SELECT r.*, 
               rm.room_number, rm.type, rm.price, rm.image, rm.capacity, rm.beds, rm.size, rm.description, rm.floor,
               u.id as user_id, u.name as user_name, u.email as user_email, u.phone as user_phone, u.profile_image,
               u.address, u.city, u.country, u.created_at as user_created_at
        FROM reservations r
        JOIN rooms rm ON r.room_id = rm.id
        JOIN users u ON r.user_id = u.id
        WHERE r.id = ?
    ");
    $stmt->execute([$reservation_id]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reservation) {
        header("Location: reservations.php?msg=not_found");
        exit();
    }
    
    $stmt = $pdo->prepare("
        SELECT a.* 
        FROM amenities a 
        JOIN room_amenities ra ON a.id = ra.amenity_id 
        WHERE ra.room_id = ?
    ");
    $stmt->execute([$reservation['room_id']]);
    $amenities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate nights and total price if not set
    $check_in_date = new DateTime($reservation['check_in']);
    $check_out_date = new DateTime($reservation['check_out']);
    $nights = $check_in_date->diff($check_out_date)->days;
    
    if (empty($reservation['total_price'])) {
        $total_price = $nights * $reservation['price'];
    } else {
        $total_price = $reservation['total_price'];
    }
    
    $stmt = $pdo->prepare("
        SELECT r.id, r.check_in, r.check_out, r.status, rm.room_number, rm.type
        FROM reservations r
        JOIN rooms rm ON r.room_id = rm.id
        WHERE r.user_id = ? AND r.id != ?
        ORDER BY r.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$reservation['user_id'], $reservation_id]);
    $other_reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $payment_status = isset($_POST['payment_status']) ? $_POST['payment_status'] : 'pending';
    
    try {
        $stmt = $pdo->prepare("UPDATE reservations SET status = ?, payment_status = ? WHERE id = ?");
        $stmt->execute([$new_status, $payment_status, $reservation_id]);
        
        // Redirect to refresh the page
        header("Location: reservation-details.php?id=" . $reservation_id . "&msg=updated");
        exit();
    } catch (PDOException $e) {
        $error_message = "Error updating reservation: " . $e->getMessage();
    }
}

$alert_message = '';
$alert_type = '';

if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'updated':
            $alert_message = 'Reservation has been updated successfully.';
            $alert_type = 'success';
            break;
        case 'error':
            $alert_message = 'An error occurred. Please try again.';
            $alert_type = 'error';
            break;
    }
}
?>

<?php include 'includes/header.php'; ?>
        <!-- Alert Messages -->
        <?php if ($alert_message): ?>
        <div class="alert alert-<?php echo $alert_type; ?>">
            <?php echo $alert_message; ?>
            <button class="close-btn">×</button>
        </div>
        <?php endif; ?>

        <div class="admin-container">
            <div class="admin-header">
                <h1>Reservation Details</h1>
                <a href="reservations.php" class="btn-secondary">Back to Reservations</a>
            </div>

            <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
            <?php else: ?>
            <div class="reservation-details-container">
                <div class="detail-header">
                    <div>
                        <div class="reservation-id">Reservation #<?php echo sprintf('HLT%06d', $reservation['id']); ?></div>
                        <h2 class="detail-title"><?php echo htmlspecialchars($reservation['type']); ?> Room</h2>
                    </div>
                    <div>
                        <?php
                        $status_class = '';
                        switch($reservation['status']) {
                            case 'confirmed': $status_class = 'status-confirmed'; break;
                            case 'pending': $status_class = 'status-pending'; break;
                            case 'cancelled': $status_class = 'status-cancelled'; break;
                            case 'completed': $status_class = 'status-completed'; break;
                            default: $status_class = 'status-pending';
                        }
                        ?>
                        <span class="status-badge <?php echo $status_class; ?>"><?php echo ucfirst($reservation['status']); ?></span>
                    </div>
                </div>

                <div class="detail-grid">
                    <div>
                        <div class="detail-section">
                            <h3 class="section-title">Guest Information</h3>
                            <div class="user-profile">
                                <div class="user-avatar">
                                    <img src="<?php echo !empty($reservation['profile_image']) ? htmlspecialchars($reservation['profile_image']) : 'https://ui-avatars.com/api/?name=' . urlencode($reservation['user_name']) . '&background=random'; ?>" alt="User Avatar">
                                </div>
                                <div class="user-info">
                                    <div class="user-name"><?php echo htmlspecialchars($reservation['user_name']); ?></div>
                                    <div class="user-email"><?php echo htmlspecialchars($reservation['user_email']); ?></div>
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">User ID:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($reservation['user_id']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Phone:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($reservation['phone'] ?? $reservation['user_phone'] ?? 'Not provided'); ?></span>
                            </div>
                            <?php if (!empty($reservation['address'])): ?>
                            <div class="detail-row">
                                <span class="detail-label">Address:</span>
                                <span class="detail-value">
                                    <?php echo htmlspecialchars($reservation['address']); ?>
                                    <?php if (!empty($reservation['city']) || !empty($reservation['country'])): ?>
                                    <br>
                                    <?php echo htmlspecialchars(trim($reservation['city'] . ', ' . $reservation['country'], ', ')); ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <?php endif; ?>
                            <div class="detail-row">
                                <span class="detail-label">Member Since:</span>
                                <span class="detail-value"><?php echo date('F d, Y', strtotime($reservation['user_created_at'])); ?></span>
                            </div>
                        </div>

                        <div class="detail-section">
                            <h3 class="section-title">Reservation Details</h3>
                            <div class="detail-row">
                                <span class="detail-label">Check-in:</span>
                                <span class="detail-value"><?php echo date('F d, Y', strtotime($reservation['check_in'])); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Check-out:</span>
                                <span class="detail-value"><?php echo date('F d, Y', strtotime($reservation['check_out'])); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Nights:</span>
                                <span class="detail-value"><?php echo $nights; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Total Price:</span>
                                <span class="detail-value">$<?php echo number_format($total_price, 2); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Payment Status:</span>
                                <span class="detail-value">
                                    <?php
                                    $payment_status_class = '';
                                    switch($reservation['payment_status']) {
                                        case 'paid': $payment_status_class = 'status-confirmed'; break;
                                        case 'pending': $payment_status_class = 'status-pending'; break;
                                        case 'refunded': $payment_status_class = 'status-cancelled'; break;
                                        default: $payment_status_class = 'status-pending';
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $payment_status_class; ?>"><?php echo ucfirst($reservation['payment_status']); ?></span>
                                </span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Booked On:</span>
                                <span class="detail-value"><?php echo date('F d, Y H:i', strtotime($reservation['created_at'])); ?></span>
                            </div>
                            <?php if (!empty($reservation['special_requests'])): ?>
                            <div class="detail-row">
                                <span class="detail-label">Special Requests:</span>
                                <span class="detail-value"><?php echo nl2br(htmlspecialchars($reservation['special_requests'])); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Update Status Form -->
                        <div class="detail-section">
                            <h3 class="section-title">Update Reservation</h3>
                            <form class="status-form" method="post" action="">
                                <div class="form-group">
                                    <label for="status">Reservation Status:</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="pending" <?php echo $reservation['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $reservation['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="cancelled" <?php echo $reservation['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        <option value="completed" <?php echo $reservation['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="payment_status">Payment Status:</label>
                                    <select class="form-control" id="payment_status" name="payment_status">
                                        <option value="pending" <?php echo $reservation['payment_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="paid" <?php echo $reservation['payment_status'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                                        <option value="refunded" <?php echo $reservation['payment_status'] == 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                                    </select>
                                </div>
                                <button type="submit" name="update_status" class="btn-primary">Update Reservation</button>
                            </form>
                        </div>
                    </div>
                    
                    <div>
                        <div class="detail-section">
                            <h3 class="section-title">Room Information</h3>
                            <div class="room-image">
                                <img src="<?php echo !empty($reservation['image']) ? htmlspecialchars($reservation['image']) : 'https://www.hilton.com/im/en/NoHotel/15483650/shutterstock-1078953762.jpg?impolicy=crop&cw=5000&ch=3333&gravity=NorthWest&xposition=0&yposition=0&rw=768&rh=512'; ?>" alt="<?php echo htmlspecialchars($reservation['type']); ?> Room">
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Room Number:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($reservation['room_number']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Room Type:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($reservation['type']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Price per Night:</span>
                                <span class="detail-value">$<?php echo number_format($reservation['price'], 2); ?></span>
                            </div>
                            <?php if (!empty($reservation['capacity'])): ?>
                            <div class="detail-row">
                                <span class="detail-label">Capacity:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($reservation['capacity']); ?> Guests</span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($reservation['beds'])): ?>
                            <div class="detail-row">
                                <span class="detail-label">Beds:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($reservation['beds']); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($reservation['size'])): ?>
                            <div class="detail-row">
                                <span class="detail-label">Room Size:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($reservation['size']); ?> m²</span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($reservation['floor'])): ?>
                            <div class="detail-row">
                                <span class="detail-label">Floor:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($reservation['floor']); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($reservation['description'])): ?>
                            <div class="detail-row">
                                <span class="detail-label">Description:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($reservation['description']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($amenities)): ?>
                            <div class="detail-row">
                                <span class="detail-label">Amenities:</span>
                                <div class="detail-value">
                                    <div class="amenities-list">
                                        <?php foreach ($amenities as $amenity): ?>
                                        <span class="amenity-tag"><?php echo htmlspecialchars($amenity['name']); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Other Reservations by this User -->
                        <?php if (!empty($other_reservations)): ?>
                        <div class="detail-section other-reservations">
                            <h3 class="section-title">Other Reservations by this Guest</h3>
                            <?php foreach ($other_reservations as $other): ?>
                            <div class="reservation-card">
                                <div class="reservation-card-header">
                                    <span class="room-type"><?php echo htmlspecialchars($other['type']); ?> - Room <?php echo htmlspecialchars($other['room_number']); ?></span>
                                    <span class="status-badge <?php echo $other['status'] == 'confirmed' ? 'status-confirmed' : ($other['status'] == 'cancelled' ? 'status-cancelled' : 'status-pending'); ?>">
                                        <?php echo ucfirst($other['status']); ?>
                                    </span>
                                </div>
                                <div class="reservation-dates">
                                    <?php echo date('M d, Y', strtotime($other['check_in'])); ?> to 
                                    <?php echo date('M d, Y', strtotime($other['check_out'])); ?>
                                </div>
                                <div class="action-buttons">
                                    <a href="reservation-details.php?id=<?php echo $other['id']; ?>" class="btn-secondary">View Details</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="room-actions">
                    <?php if ($reservation['status'] === 'pending'): ?>
                    <a href="reservations.php?action=confirm&id=<?php echo $reservation['id']; ?>" class="btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Confirm Reservation
                    </a>
                    <a href="reservations.php?action=cancel&id=<?php echo $reservation['id']; ?>" class="btn-danger" onclick="return confirm('Are you sure you want to cancel this reservation?');">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                        Cancel Reservation
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Close alert messages
    const alertCloseButtons = document.querySelectorAll('.alert .close-btn');
    alertCloseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const alert = this.parentElement;
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>