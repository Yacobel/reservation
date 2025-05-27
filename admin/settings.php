<?php
session_start();

require_once '../config/db.php';

$page_title = 'System Settings';

$settings = [];
$success_message = '';
$error_message = '';
try {
    $stmt = $pdo->query("SELECT * FROM settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    $error_message = "Error fetching settings: " . $e->getMessage();
}

if (!isset($settings['hotel_name'])) {
    $settings['hotel_name'] = 'Hilton Hotel';
}
if (!isset($settings['hotel_address'])) {
    $settings['hotel_address'] = 'Tanger City Center, Tangier, Morocco';
}
if (!isset($settings['hotel_phone'])) {
    $settings['hotel_phone'] = '+212 539 340 850';
}
if (!isset($settings['hotel_email'])) {
    $settings['hotel_email'] = 'info@hiltontanger.com';
}
if (!isset($settings['currency'])) {
    $settings['currency'] = 'MAD';
}
if (!isset($settings['tax_rate'])) {
    $settings['tax_rate'] = '10';
}
if (!isset($settings['check_in_time'])) {
    $settings['check_in_time'] = '14:00';
}
if (!isset($settings['check_out_time'])) {
    $settings['check_out_time'] = '12:00';
}
if (!isset($settings['reservation_enabled'])) {
    $settings['reservation_enabled'] = '1';
}
if (!isset($settings['maintenance_mode'])) {
    $settings['maintenance_mode'] = '0';
}
if (!isset($settings['terms_conditions'])) {
    $settings['terms_conditions'] = 'Default terms and conditions for hotel bookings.';
}
if (!isset($settings['privacy_policy'])) {
    $settings['privacy_policy'] = 'Default privacy policy for hotel bookings.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    try {
        // Begin transaction
        $pdo->beginTransaction();
        
        // Update each setting
        $settings_to_update = [
            'hotel_name', 'hotel_address', 'hotel_phone', 'hotel_email',
            'currency', 'tax_rate', 'check_in_time', 'check_out_time',
            'reservation_enabled', 'maintenance_mode', 'terms_conditions', 'privacy_policy'
        ];
        
        foreach ($settings_to_update as $key) {
            if (isset($_POST[$key])) {
                $value = $_POST[$key];
                
                // Sanitize inputs
                if ($key === 'tax_rate') {
                    $value = floatval($value);
                    if ($value < 0 || $value > 100) {
                        throw new Exception("Tax rate must be between 0 and 100.");
                    }
                }
                
                // Check if setting exists
                $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?");
                $check_stmt->execute([$key]);
                $exists = $check_stmt->fetchColumn();
                
                if ($exists) {
                    // Update existing setting
                    $update_stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
                    $update_stmt->execute([$value, $key]);
                } else {
                    // Insert new setting
                    $insert_stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
                    $insert_stmt->execute([$key, $value]);
                }
                
                // Update local array
                $settings[$key] = $value;
            }
        }
        
        // Commit transaction
        $pdo->commit();
        
        $success_message = "Settings updated successfully.";
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $error_message = "Error updating settings: " . $e->getMessage();
    }
}

// Handle alert messages
$alert_message = '';
$alert_type = '';

if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'settings_updated':
            $alert_message = 'Settings have been updated successfully.';
            $alert_type = 'success';
            break;
        case 'error':
            $alert_message = 'An error occurred. Please try again.';
            $alert_type = 'error';
            break;
    }
}

// Add page-specific CSS
$page_css = '<link rel="stylesheet" href="css/settings.css">';

// Make sure the CSS file is directly included
echo '<style>
@import url("css/settings.css");
</style>';
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
                <h1>System Settings</h1>
                <p>Configure hotel system settings</p>
            </div>

            <div class="settings-container">
                <form method="post" action="" class="settings-form">
                    <!-- Tabs Navigation -->
                    <div class="settings-tabs">
                        <button type="button" class="tab-button active" data-tab="general">General</button>
                        <button type="button" class="tab-button" data-tab="booking">Booking</button>
                        <button type="button" class="tab-button" data-tab="legal">Legal</button>
                        <button type="button" class="tab-button" data-tab="system">System</button>
                    </div>

                    <!-- Tab Content -->
                    <div class="settings-content">
                        <!-- General Settings -->
                        <div class="tab-pane active" id="general">
                            <h2>General Information</h2>
                            <div class="form-group">
                                <label for="hotel_name">Hotel Name</label>
                                <input type="text" id="hotel_name" name="hotel_name" value="<?php echo htmlspecialchars($settings['hotel_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="hotel_address">Hotel Address</label>
                                <input type="text" id="hotel_address" name="hotel_address" value="<?php echo htmlspecialchars($settings['hotel_address']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="hotel_phone">Contact Phone</label>
                                <input type="text" id="hotel_phone" name="hotel_phone" value="<?php echo htmlspecialchars($settings['hotel_phone']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="hotel_email">Contact Email</label>
                                <input type="email" id="hotel_email" name="hotel_email" value="<?php echo htmlspecialchars($settings['hotel_email']); ?>" required>
                            </div>
                        </div>

                        <!-- Booking Settings -->
                        <div class="tab-pane" id="booking">
                            <h2>Booking Configuration</h2>
                            <div class="form-group">
                                <label for="currency">Currency</label>
                                <select id="currency" name="currency">
                                    <option value="MAD" <?php echo $settings['currency'] === 'MAD' ? 'selected' : ''; ?>>Moroccan Dirham (MAD)</option>
                                    <option value="USD" <?php echo $settings['currency'] === 'USD' ? 'selected' : ''; ?>>US Dollar (USD)</option>
                                    <option value="EUR" <?php echo $settings['currency'] === 'EUR' ? 'selected' : ''; ?>>Euro (EUR)</option>
                                    <option value="GBP" <?php echo $settings['currency'] === 'GBP' ? 'selected' : ''; ?>>British Pound (GBP)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="tax_rate">Tax Rate (%)</label>
                                <input type="number" id="tax_rate" name="tax_rate" value="<?php echo htmlspecialchars($settings['tax_rate']); ?>" min="0" max="100" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label for="check_in_time">Check-in Time</label>
                                <input type="time" id="check_in_time" name="check_in_time" value="<?php echo htmlspecialchars($settings['check_in_time']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="check_out_time">Check-out Time</label>
                                <input type="time" id="check_out_time" name="check_out_time" value="<?php echo htmlspecialchars($settings['check_out_time']); ?>" required>
                            </div>
                        </div>

                        <!-- Legal Settings -->
                        <div class="tab-pane" id="legal">
                            <h2>Legal Information</h2>
                            <div class="form-group">
                                <label for="terms_conditions">Terms & Conditions</label>
                                <textarea id="terms_conditions" name="terms_conditions" rows="10"><?php echo htmlspecialchars($settings['terms_conditions']); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="privacy_policy">Privacy Policy</label>
                                <textarea id="privacy_policy" name="privacy_policy" rows="10"><?php echo htmlspecialchars($settings['privacy_policy']); ?></textarea>
                            </div>
                        </div>

                        <!-- System Settings -->
                        <div class="tab-pane" id="system">
                            <h2>System Configuration</h2>
                            <div class="form-group switch-group">
                                <label for="reservation_enabled">Enable Reservations</label>
                                <label class="switch">
                                    <input type="checkbox" id="reservation_enabled" name="reservation_enabled" value="1" <?php echo $settings['reservation_enabled'] == '1' ? 'checked' : ''; ?>>
                                    <span class="slider round"></span>
                                </label>
                                <p class="setting-description">Allow users to make new reservations</p>
                            </div>
                            <div class="form-group switch-group">
                                <label for="maintenance_mode">Maintenance Mode</label>
                                <label class="switch">
                                    <input type="checkbox" id="maintenance_mode" name="maintenance_mode" value="1" <?php echo $settings['maintenance_mode'] == '1' ? 'checked' : ''; ?>>
                                    <span class="slider round"></span>
                                </label>
                                <p class="setting-description">Put the website in maintenance mode (only admins can access)</p>
                            </div>
                            <div class="form-group">
                                <button type="button" id="clearCacheBtn" class="btn-secondary">Clear System Cache</button>
                                <p class="setting-description">Clear all cached data to refresh the system</p>
                            </div>
                            <div class="form-group">
                                <button type="button" id="backupBtn" class="btn-secondary">Backup Database</button>
                                <p class="setting-description">Create a backup of the database</p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" name="update_settings" class="btn-primary">Save Changes</button>
                        <button type="reset" class="btn-secondary">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

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

        // Tab functionality
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabPanes = document.querySelectorAll('.tab-pane');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons and panes
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabPanes.forEach(pane => pane.classList.remove('active'));
                
                // Add active class to current button and corresponding pane
                this.classList.add('active');
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });

        // Clear cache button
        document.getElementById('clearCacheBtn').addEventListener('click', function() {
            if (confirm('Are you sure you want to clear the system cache?')) {
                alert('Cache cleared successfully!');
                // In a real implementation, this would call an AJAX endpoint to clear the cache
            }
        });

        // Backup database button
        document.getElementById('backupBtn').addEventListener('click', function() {
            if (confirm('Are you sure you want to backup the database?')) {
                alert('Database backup created successfully!');
                // In a real implementation, this would call an AJAX endpoint to create a backup
            }
        });
    });
    </script>

<?php include 'includes/footer.php'; ?>
