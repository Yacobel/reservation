<?php
session_start();

require_once '../config/db.php';

$page_title = 'Manage Reservations';

$page_css = '<link rel="stylesheet" href="css/reservations.css">';

echo '<style>
@import url("css/reservations.css");
</style>';

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $reservation_id = $_GET['id'];
    
    try {
        if ($action === 'confirm') {
            $stmt = $pdo->prepare("UPDATE reservations SET status = 'confirmed' WHERE id = :id");
            $stmt->bindParam(':id', $reservation_id, PDO::PARAM_INT);
            $stmt->execute();
            
            header("Location: reservations.php?msg=reservation_confirmed");
            exit();
        } elseif ($action === 'cancel') {
            $stmt = $pdo->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = :id");
            $stmt->bindParam(':id', $reservation_id, PDO::PARAM_INT);
            $stmt->execute();
            
            header("Location: reservations.php?msg=reservation_cancelled");
            exit();
        }
    } catch (PDOException $e) {
        header("Location: reservations.php?msg=error");
        exit();
    }
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filter functionality
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_filter = isset($_GET['date_range']) ? $_GET['date_range'] : '';

$where_conditions = [];
$params = [];

if (!empty($status_filter)) {
    $where_conditions[] = "r.status = :status";
    $params[':status'] = $status_filter;
}

if (!empty($date_filter)) {
    switch ($date_filter) {
        case 'today':
            $where_conditions[] = "(r.check_in = CURDATE() OR r.check_out = CURDATE())";
            break;
        case 'tomorrow':
            $where_conditions[] = "(r.check_in = DATE_ADD(CURDATE(), INTERVAL 1 DAY) OR r.check_out = DATE_ADD(CURDATE(), INTERVAL 1 DAY))";
            break;
        case 'this_week':
            $where_conditions[] = "(r.check_in BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY))";
            break;
        case 'this_month':
            $where_conditions[] = "(MONTH(r.check_in) = MONTH(CURDATE()) AND YEAR(r.check_in) = YEAR(CURDATE()))";
            break;
    }
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(' AND ', $where_conditions);
}

try {
    // Get total reservations count for pagination
    $count_sql = "SELECT COUNT(*) FROM reservations r $where_clause";
    $stmt = $pdo->prepare($count_sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $total_reservations = $stmt->fetchColumn();
    
    // Calculate total pages
    $total_pages = ceil($total_reservations / $limit);
    
    // Get reservations for current page
    $sql = "
        SELECT r.id, r.check_in, r.check_out, r.status, r.created_at,
               u.name as user_name, u.email as user_email,
               rm.room_number, rm.type, rm.price, rm.image
        FROM reservations r
        JOIN users u ON r.user_id = u.id
        JOIN rooms rm ON r.room_id = rm.id
        $where_clause
        ORDER BY r.created_at DESC
        LIMIT :limit OFFSET :offset
    ";
    
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Handle alert messages
$alert_message = '';
$alert_type = '';

if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'reservation_confirmed':
            $alert_message = 'Reservation has been confirmed successfully.';
            $alert_type = 'success';
            break;
        case 'reservation_cancelled':
            $alert_message = 'Reservation has been cancelled successfully.';
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

    <main class="admin-main">
        <!-- Alert Messages -->
        <?php if ($alert_message): ?>
        <div class="alert alert-<?php echo $alert_type; ?>">
            <?php echo $alert_message; ?>
            <button class="close-btn">&times;</button>
        </div>
        <?php endif; ?>

        <div class="admin-container">
            <div class="admin-header">
                <h1>Manage Reservations</h1>
                <p>View, confirm, and manage guest reservations</p>
            </div>

            <div class="reservation-management">
                <form action="" method="get">
                    <div class="filter-row">
                        <div class="filter-item">
                            <span class="filter-label">Status:</span>
                            <select name="status" class="filter-select">
                                <option value="" <?php echo $status_filter === '' ? 'selected' : ''; ?>>All Statuses</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="filter-item">
                            <span class="filter-label">Date Range:</span>
                            <select name="date_range" class="filter-select">
                                <option value="" <?php echo $date_filter === '' ? 'selected' : ''; ?>>All Dates</option>
                                <option value="today" <?php echo $date_filter === 'today' ? 'selected' : ''; ?>>Today</option>
                                <option value="tomorrow" <?php echo $date_filter === 'tomorrow' ? 'selected' : ''; ?>>Tomorrow</option>
                                <option value="this_week" <?php echo $date_filter === 'this_week' ? 'selected' : ''; ?>>This Week</option>
                                <option value="this_month" <?php echo $date_filter === 'this_month' ? 'selected' : ''; ?>>This Month</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-filter">Apply Filters</button>
                    </div>
                </form>

                <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="reservation-table">
                        <thead>
                            <tr>
                                <th>Guest</th>
                                <th>Room</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($reservations) > 0): ?>
                                <?php foreach ($reservations as $reservation): ?>
                                <?php
                                // Calculate total price
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
                                <tr>
                                    <td>
                                        <div class="user-info">
                                            <span class="user-name"><?php echo htmlspecialchars($reservation['user_name']); ?></span>
                                            <span class="user-email"><?php echo htmlspecialchars($reservation['user_email']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="room-info">
                                            <div class="room-details">
                                                <span class="room-type"><?php echo htmlspecialchars($reservation['type']); ?> Room</span>
                                                <span class="room-number">Room <?php echo htmlspecialchars($reservation['room_number']); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="reservation-dates">
                                            <span class="date-value"><?php echo date('M d, Y', strtotime($reservation['check_in'])); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="reservation-dates">
                                            <span class="date-value"><?php echo date('M d, Y', strtotime($reservation['check_out'])); ?></span>
                                        </div>
                                    </td>
                                    <td class="room-price">$<?php echo number_format($total_price, 2); ?></td>
                                    <td>
                                        <span class="activity-status <?php echo $status_class; ?>">
                                            <?php echo ucfirst($reservation['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="room-actions-cell">
                                            <a href="reservation-details.php?id=<?php echo $reservation['id']; ?>" class="btn-view">View</a>
                                            <?php if ($reservation['status'] === 'pending'): ?>
                                            <a href="reservations.php?action=confirm&id=<?php echo $reservation['id']; ?>" class="btn-edit">Confirm</a>
                                            <a href="reservations.php?action=cancel&id=<?php echo $reservation['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to cancel this reservation?');">Cancel</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 30px;">No reservations found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($date_filter) ? '&date_range=' . urlencode($date_filter) : ''; ?>" class="pagination-item pagination-prev">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($date_filter) ? '&date_range=' . urlencode($date_filter) : ''; ?>" class="pagination-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($date_filter) ? '&date_range=' . urlencode($date_filter) : ''; ?>" class="pagination-item pagination-next">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
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
});
</script>

<?php include 'includes/footer.php'; ?>