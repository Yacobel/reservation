<?php
session_start();

require_once '../config/db.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $room_id = $_POST['room_id'];
    $room_number = $_POST['room_number'];
    $room_type = $_POST['room_type'];
    $room_price = $_POST['room_price'];
    $room_status = $_POST['room_status'];
    $room_description = $_POST['room_description'];
    $current_image_path = $_POST['current_image_path'];
    
    // Initialize image path variable
    $image_path = $current_image_path;
    
    // Check if a new image was uploaded
    if (isset($_FILES['room_image_file']) && $_FILES['room_image_file']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        $file_type = $_FILES['room_image_file']['type'];
        $file_size = $_FILES['room_image_file']['size'];
        
        // Validate file type and size
        if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
            // Create uploads directory if it doesn't exist
            $upload_dir = '../uploads/rooms/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate a unique filename
            $file_extension = pathinfo($_FILES['room_image_file']['name'], PATHINFO_EXTENSION);
            $unique_filename = 'room_' . $room_id . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $unique_filename;
            
            // Move the uploaded file to the destination
            if (move_uploaded_file($_FILES['room_image_file']['tmp_name'], $upload_path)) {
                // Update the image path
                $image_path = '../uploads/rooms/' . $unique_filename;
            } else {
                $error = "Failed to upload the image. Please try again.";
            }
        } else {
            $error = "Invalid file. Please upload a JPG, PNG, or GIF image under 5MB.";
        }
    }
    
    try {
        // Update the room in the database
        $stmt = $pdo->prepare("UPDATE rooms SET 
                                room_number = :room_number, 
                                type = :type, 
                                price = :price, 
                                status = :status, 
                                description = :description, 
                                image = :image,
                                updated_at = NOW()
                              WHERE id = :id");
        
        $stmt->bindParam(':room_number', $room_number, PDO::PARAM_STR);
        $stmt->bindParam(':type', $room_type, PDO::PARAM_STR);
        $stmt->bindParam(':price', $room_price, PDO::PARAM_STR);
        $stmt->bindParam(':status', $room_status, PDO::PARAM_STR);
        $stmt->bindParam(':description', $room_description, PDO::PARAM_STR);
        $stmt->bindParam(':image', $image_path, PDO::PARAM_STR);
        $stmt->bindParam(':id', $room_id, PDO::PARAM_INT);
        
        $stmt->execute();
        
        // Redirect back to the view room page with a success message
        header("Location: view-room.php?id=$room_id&msg=room_updated");
        exit();
        
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
        header("Location: view-room.php?id=$room_id&msg=error");
        exit();
    }
}

// If we get here, something went wrong
header("Location: rooms.php");
exit();
?>
