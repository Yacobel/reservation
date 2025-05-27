<?php
// Include database connection
require_once '../config/db.php';

try {
    // Read the SQL file
    $sql = file_get_contents('create_settings_table.sql');
    
    // Execute the SQL
    $pdo->exec($sql);
    
    echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background-color: #f8f9fa; border-radius: 10px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);'>
        <h2 style='color: #004990;'>Settings Table Created Successfully</h2>
        <p>The settings table has been created and populated with default values.</p>
        <p>You can now <a href='settings.php' style='color: #004990; text-decoration: none;'>go to the settings page</a>.</p>
    </div>";
} catch (PDOException $e) {
    echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background-color: #f8f9fa; border-radius: 10px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);'>
        <h2 style='color: #dc3545;'>Error Creating Settings Table</h2>
        <p>" . $e->getMessage() . "</p>
        <p>Please check your database configuration and try again.</p>
    </div>";
}
?>
