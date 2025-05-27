<?php
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php?error=unauthorized");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
$user_role = $_SESSION['user_role'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Admin Dashboard'; ?> - Hilton Hotel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="css/admin.css">
    <?php if (isset($page_css)): ?>
        <?php echo $page_css; ?>
    <?php endif; ?>
    <link rel="icon" type="image/x-icon" href="https://www.hilton.com/favicon.ico">
    <?php if (isset($page_js)): ?>
        <?php echo $page_js; ?>
    <?php endif; ?>
</head>

<body>
    <header class="dashboard-header">
        <div class="dashboard-container">
            <div class="dashboard-logo">
                <a href="../index.php">
                    <img src="https://www.hilton.com/modules/assets/svgs/logos/HH.svg" alt="Hilton Logo" style="height: 40px;">
                </a>
            </div>
            
            <nav class="dashboard-nav" id="dashboardNav">
                <!-- Mobile User Profile (Only visible in mobile menu) -->
                <div class="mobile-user-profile">
                    <div class="mobile-user-avatar">
                        <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                    </div>
                    <div class="mobile-user-name"><?php echo htmlspecialchars($user_name); ?> <span class="admin-badge">Admin</span></div>
                    <div class="mobile-user-links">
                        <a href="../profile.php" class="mobile-user-link">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            My Profile
                        </a>
                        <a href="../auth/logout.php" class="mobile-user-link">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                            Logout
                        </a>
                    </div>
                </div>
                
                <a href="../index.php" class="dashboard-nav-link">Home</a>
                <a href="dashboard.php" class="dashboard-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">Admin Dashboard</a>
                <a href="rooms.php" class="dashboard-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'rooms.php' ? 'active' : ''; ?>">Manage Rooms</a>
                <a href="reservations.php" class="dashboard-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'reservations.php' ? 'active' : ''; ?>">Manage Reservations</a>
                <a href="users.php" class="dashboard-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">Manage Users</a>
            </nav>
            
            <div class="dashboard-user">
                <div class="user-avatar admin-avatar">
                    <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                </div>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($user_name); ?> <span class="admin-badge">Admin</span></span>
                    <div class="user-dropdown">
                        <a href="../profile.php" class="dropdown-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            My Profile
                        </a>
                        <a href="../auth/logout.php" class="dropdown-item">
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
    
    <div class="menu-overlay" id="menuOverlay"></div>

    <main class="admin-main">
        <?php if (isset($alert_message) && $alert_message): ?>
        <div class="alert alert-<?php echo isset($alert_type) ? $alert_type : 'info'; ?>">
            <?php echo $alert_message; ?>
            <button class="close-btn">&times;</button>
        </div>
        <?php endif; ?>
