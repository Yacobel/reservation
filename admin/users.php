<?php
session_start();

// Include database connection
require_once '../config/db.php';

// Set page title
$page_title = 'Manage Users';

// Add page-specific CSS
$page_css = '<link rel="stylesheet" href="css/user-table.css">';

// Make sure the CSS file is directly included
echo '<style>
@import url("css/user-table.css");
</style>';

// Get users with filtering and pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role_filter = isset($_GET['role']) ? trim($_GET['role']) : '';

try {
    $where_clause = '';
    $where_params = [];
    
    if (!empty($search)) {
        $where_clause .= "(name LIKE ? OR email LIKE ?)";
        $where_params[] = "%$search%";
        $where_params[] = "%$search%";
    }
    
    if (!empty($role_filter)) {
        if (!empty($where_clause)) {
            $where_clause .= " AND ";
        }
        $where_clause .= "role = ?";
        $where_params[] = $role_filter;
    }
    
    if (!empty($where_clause)) {
        $where_clause = "WHERE " . $where_clause;
    }
    
    $count_sql = "SELECT COUNT(*) FROM users $where_clause";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($where_params);
    $total_users = $count_stmt->fetchColumn();
    $total_pages = ceil($total_users / $limit);
    
    $sql = "SELECT * FROM users $where_clause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($where_params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $target_user_id = $_GET['id'];
    if ($target_user_id == $user_id) {
        header("Location: users.php?msg=cannot_modify_self");
        exit();
    }
    
    try {
        if ($action === 'delete') {
            // Check if user has any reservations
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE user_id = ?");
            $stmt->execute([$target_user_id]);
            $reservation_count = $stmt->fetchColumn();
            
            if ($reservation_count > 0) {
                header("Location: users.php?msg=user_has_reservations");
                exit();
            } else {
                // Delete the user
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$target_user_id]);
                
                header("Location: users.php?msg=user_deleted");
                exit();
            }
        } elseif ($action === 'make_admin') {
            // Make user an admin
            $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
            $stmt->execute([$target_user_id]);
            
            header("Location: users.php?msg=role_updated");
            exit();
        } elseif ($action === 'make_user') {
            // Make admin a regular user
            $stmt = $pdo->prepare("UPDATE users SET role = 'user' WHERE id = ?");
            $stmt->execute([$target_user_id]);
            
            header("Location: users.php?msg=role_updated");
            exit();
        }
    } catch (PDOException $e) {
        header("Location: users.php?msg=error");
        exit();
    }
}

// Set alert message if any
$alert_message = '';
$alert_type = '';

if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'user_deleted':
            $alert_message = 'User has been deleted successfully.';
            $alert_type = 'success';
            break;
        case 'role_updated':
            $alert_message = 'User role has been updated successfully.';
            $alert_type = 'success';
            break;
        case 'cannot_modify_self':
            $alert_message = 'You cannot modify your own account from this page.';
            $alert_type = 'error';
            break;
        case 'user_has_reservations':
            $alert_message = 'Cannot delete user with active reservations.';
            $alert_type = 'error';
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
                <h1>Manage Users</h1>
                <p>View and manage user accounts</p>
            </div>

            <div class="user-management">
                <form action="" method="get" class="filter-form">
                    <div class="filter-row">
                        <div class="filter-item">
                            <span class="filter-label">Role:</span>
                            <select name="role" class="filter-select">
                                <option value="">All Roles</option>
                                <option value="user" <?php echo $role_filter === 'user' ? 'selected' : ''; ?>>User</option>
                                <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                        <div class="filter-item search-box">
                            <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                            <input type="text" name="search" placeholder="Search by name or email" value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                        </div>
                        <div class="filter-item">
                            <button type="submit" class="btn-filter">Filter</button>
                            <a href="users.php" class="btn-reset">Reset</a>
                        </div>
                    </div>
                </form>

                <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Contact</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($users) > 0): ?>
                                <?php foreach ($users as $user_item): ?>
                                <tr>
                                    <td data-label="User">
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <img src="<?php echo !empty($user_item['profile_image']) ? htmlspecialchars($user_item['profile_image']) : 'https://ui-avatars.com/api/?name=' . urlencode($user_item['name']) . '&background=random'; ?>" alt="User Avatar">
                                            </div>
                                            <div>
                                                <span class="user-name"><?php echo htmlspecialchars($user_item['name']); ?></span>
                                                <span class="user-id">ID: <?php echo $user_item['id']; ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-label="Contact">
                                        <div class="user-contact">
                                            <div class="user-email"><?php echo htmlspecialchars($user_item['email']); ?></div>
                                            <?php if (!empty($user_item['phone'])): ?>
                                            <div class="user-phone"><?php echo htmlspecialchars($user_item['phone']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td data-label="Role">
                                        <span class="role-badge <?php echo $user_item['role'] === 'admin' ? 'role-admin' : 'role-user'; ?>">
                                            <?php echo ucfirst($user_item['role']); ?>
                                        </span>
                                    </td>
                                    <td data-label="Joined">
                                        <?php echo date('M d, Y', strtotime($user_item['created_at'])); ?>
                                    </td>
                                    <td data-label="Actions">
                                        <div class="user-actions">
                                            <a href="../profile.php?id=<?php echo $user_item['id']; ?>" class="btn-view">View</a>
                                            
                                            <?php if ($user_item['id'] != $user_id): ?>
                                                <?php if ($user_item['role'] === 'user'): ?>
                                                <a href="users.php?action=make_admin&id=<?php echo $user_item['id']; ?>" class="btn-edit">Make Admin</a>
                                                <?php else: ?>
                                                <a href="users.php?action=make_user&id=<?php echo $user_item['id']; ?>" class="btn-edit">Make User</a>
                                                <?php endif; ?>
                                                
                                                <a href="users.php?action=delete&id=<?php echo $user_item['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">Delete</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 30px;">No users found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo !empty($role_filter) ? '&role=' . urlencode($role_filter) : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="pagination-item pagination-prev">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </a>
                    <?php endif; ?>
                    
                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $start_page + 4);
                    
                    if ($end_page - $start_page < 4 && $start_page > 1) {
                        $start_page = max(1, $end_page - 4);
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo !empty($role_filter) ? '&role=' . urlencode($role_filter) : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="pagination-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo !empty($role_filter) ? '&role=' . urlencode($role_filter) : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="pagination-item pagination-next">
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

<?php include 'includes/footer.php'; ?>
