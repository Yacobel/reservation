<?php
session_start();

require_once '../config/db.php';

$page_title = 'Admin Dashboard';

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM rooms");
    $total_rooms = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM rooms WHERE status = 'available'");
    $available_rooms = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM reservations");
    $total_reservations = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'pending'");
    $pending_reservations = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
    $total_users = $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT SUM(r.price * DATEDIFF(res.check_out, res.check_in)) as total_revenue 
                        FROM reservations res 
                        JOIN rooms r ON res.room_id = r.id 
                        WHERE res.status = 'confirmed'");
    $total_revenue = $stmt->fetchColumn();
    if (!$total_revenue) $total_revenue = 0;
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

$alert_message = '';
$alert_type = '';

if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'room_added':
            $alert_message = 'Room has been added successfully.';
            $alert_type = 'success';
            break;
        case 'room_updated':
            $alert_message = 'Room has been updated successfully.';
            $alert_type = 'success';
            break;
        case 'room_deleted':
            $alert_message = 'Room has been deleted successfully.';
            $alert_type = 'success';
            break;
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

        <div class="admin-container">
            <div class="admin-header">
                <h1>Admin Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($user_name); ?>! Here's an overview of your hotel.</p>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon rooms-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3>Total Rooms</h3>
                        <div class="stat-value"><?php echo $total_rooms; ?></div>
                        <div class="stat-detail"><?php echo $available_rooms; ?> available</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon reservations-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3>Reservations</h3>
                        <div class="stat-value"><?php echo $total_reservations; ?></div>
                        <div class="stat-detail"><?php echo $pending_reservations; ?> pending</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon users-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3>Users</h3>
                        <div class="stat-value"><?php echo $total_users; ?></div>
                        <div class="stat-detail">Registered users</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon revenue-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3>Revenue</h3>
                        <div class="stat-value">$<?php echo number_format($total_revenue, 2); ?></div>
                        <div class="stat-detail">Total earnings</div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="recent-activity">
                <div class="section-header">
                    <h2>Recent Activity</h2>
                    <a href="reservations.php" class="view-all">View All</a>
                </div>
                
                <div class="activity-container">
                    <?php
                    // Get recent reservations
                    try {
                        $stmt = $pdo->query("
                            SELECT r.id, r.check_in, r.check_out, r.status, r.created_at,
                                   u.name as user_name, u.email as user_email,
                                   rm.room_number, rm.type, rm.price
                            FROM reservations r
                            JOIN users u ON r.user_id = u.id
                            JOIN rooms rm ON r.room_id = rm.id
                            ORDER BY r.created_at DESC
                            LIMIT 5
                        ");
                        $recent_reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                        $error = "Database error: " . $e->getMessage();
                    }
                    ?>
                    
                    <?php if (!empty($recent_reservations)): ?>
                        <div class="activity-list">
                            <?php foreach ($recent_reservations as $reservation): ?>
                                <?php
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
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="16" y1="2" x2="16" y2="6"></line>
                                            <line x1="8" y1="2" x2="8" y2="6"></line>
                                            <line x1="3" y1="10" x2="21" y2="10"></line>
                                        </svg>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-header">
                                            <h4><?php echo htmlspecialchars($reservation['user_name']); ?> booked a <?php echo htmlspecialchars($reservation['type']); ?> Room</h4>
                                            <span class="activity-time"><?php echo date('M d, Y', strtotime($reservation['created_at'])); ?></span>
                                        </div>
                                        <div class="activity-details">
                                            <p>Room <?php echo htmlspecialchars($reservation['room_number']); ?> • 
                                               <?php echo date('M d', strtotime($reservation['check_in'])); ?> - <?php echo date('M d, Y', strtotime($reservation['check_out'])); ?></p>
                                            <span class="activity-status <?php echo $status_class; ?>"><?php echo ucfirst($reservation['status']); ?></span>
                                        </div>
                                        <div class="activity-actions">
                                            <a href="reservation-details.php?id=<?php echo $reservation['id']; ?>" class="btn btn-sm">View Details</a>
                                            <?php if ($reservation['status'] === 'pending'): ?>
                                            <form method="post" action="process-reservation.php" style="display:inline;">
                                                <input type="hidden" name="id" value="<?php echo $reservation['id']; ?>">
                                                <input type="hidden" name="action" value="confirm">
                                                <button type="submit" class="btn btn-sm btn-confirm">Confirm</button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-activity">
                            <p>No recent reservations found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <div class="section-header">
                    <h2>Quick Actions</h2>
                </div>
                <div class="actions-grid">
                    <a href="add-room.php" class="action-card">
                        <div class="action-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="12" y1="8" x2="12" y2="16"></line>
                                <line x1="8" y1="12" x2="16" y2="12"></line>
                            </svg>
                        </div>
                        <div class="action-content">
                            <h3>Add New Room</h3>
                            <p>Create a new room listing</p>
                        </div>
                    </a>

                    <a href="reservations.php?filter=pending" class="action-card">
                        <div class="action-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 11 12 14 22 4"></polyline>
                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                            </svg>
                        </div>
                        <div class="action-content">
                            <h3>Pending Reservations</h3>
                            <p>Review and confirm bookings</p>
                        </div>
                    </a>

                    <a href="reports.php" class="action-card">
                        <div class="action-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path>
                                <path d="M22 12A10 10 0 0 0 12 2v10z"></path>
                            </svg>
                        </div>
                        <div class="action-content">
                            <h3>Generate Reports</h3>
                            <p>View booking and revenue reports</p>
                        </div>
                    </a>

                    <a href="settings.php" class="action-card">
                        <div class="action-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="3"></circle>
                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                            </svg>
                        </div>
                        <div class="action-content">
                            <h3>System Settings</h3>
                            <p>Configure hotel settings</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </main>

<?php include 'includes/footer.php'; ?>