<?php
session_start();

require_once '../config/db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Check if the reservation ID and action are provided
if (($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['id']) || !isset($_POST['action']))) ||
    ($_SERVER['REQUEST_METHOD'] === 'GET' && (!isset($_GET['id']) || !isset($_GET['action'])))) {
    header("Location: dashboard.php?msg=error");
    exit();
}

// Get the reservation ID and action from either POST or GET
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservation_id = $_POST['id'];
    $action = $_POST['action'];
} else {
    $reservation_id = $_GET['id'];
    $action = $_GET['action'];
}

try {
    // First, check if the reservation exists and get its details
    $stmt = $pdo->prepare("SELECT r.*, rm.room_number, rm.type, u.email 
                          FROM reservations r 
                          JOIN rooms rm ON r.room_id = rm.id 
                          JOIN users u ON r.user_id = u.id 
                          WHERE r.id = :id");
    $stmt->bindParam(':id', $reservation_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reservation) {
        header("Location: dashboard.php?msg=error");
        exit();
    }
    
    // Process the action
    if ($action === 'confirm') {
        // Update reservation status to confirmed
        $stmt = $pdo->prepare("UPDATE reservations SET status = 'confirmed', updated_at = NOW() WHERE id = :id");
        $stmt->bindParam(':id', $reservation_id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Update room status to occupied for the reservation period
        $stmt = $pdo->prepare("UPDATE rooms SET status = 'Occupied' WHERE id = :room_id");
        $stmt->bindParam(':room_id', $reservation['room_id'], PDO::PARAM_INT);
        $stmt->execute();
        
        // Optional: Send confirmation email to the user
        // This would require setting up an email function
        
        header("Location: dashboard.php?msg=reservation_confirmed");
        exit();
    } 
    elseif ($action === 'cancel') {
        // Update reservation status to cancelled
        $stmt = $pdo->prepare("UPDATE reservations SET status = 'cancelled', updated_at = NOW() WHERE id = :id");
        $stmt->bindParam(':id', $reservation_id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Update room status back to available
        $stmt = $pdo->prepare("UPDATE rooms SET status = 'Available' WHERE id = :room_id");
        $stmt->bindParam(':room_id', $reservation['room_id'], PDO::PARAM_INT);
        $stmt->execute();
        
        // Optional: Send cancellation email to the user
        
        header("Location: dashboard.php?msg=reservation_cancelled");
        exit();
    }
    else {
        header("Location: dashboard.php?msg=error");
        exit();
    }
} catch (PDOException $e) {
    // Log the error
    error_log("Error processing reservation: " . $e->getMessage());
    header("Location: dashboard.php?msg=error");
    exit();
}
?>
