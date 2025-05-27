<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

require_once '../config/db.php';

if (!isset($_POST['action'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No action specified'
    ]);
    exit();
}

$action = $_POST['action'];

switch ($action) {
    case 'get_room':
        if (!isset($_POST['room_id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Room ID is required'
            ]);
            exit();
        }
        
        $room_id = $_POST['room_id'];
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = :id");
            $stmt->bindParam(':id', $room_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $room = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$room) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Room not found'
                ]);
                exit();
            }
            
            // Return room details
            echo json_encode([
                'success' => true,
                'room' => $room
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'update_room':
        // Update room details
        if (!isset($_POST['room_id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Room ID is required'
            ]);
            exit();
        }
        
        $room_id = $_POST['room_id'];
        $room_number = $_POST['room_number'];
        $room_type = $_POST['room_type'];
        $room_price = $_POST['room_price'];
        $room_status = $_POST['room_status'];
        $room_description = $_POST['room_description'];
        $current_image_path = $_POST['current_image_path'];
        
        // Check if room number already exists (for another room)
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM rooms WHERE room_number = :room_number AND id != :id");
            $stmt->bindParam(':room_number', $room_number, PDO::PARAM_STR);
            $stmt->bindParam(':id', $room_id, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->fetchColumn() > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Room number already exists. Please choose a different one.'
                ]);
                exit();
            }
            
            // Handle image upload if a new image is provided
            $image_path = $current_image_path; // Default to current image
            
            if (isset($_FILES['room_image_file']) && $_FILES['room_image_file']['size'] > 0) {
                $file_name = $_FILES['room_image_file']['name'];
                $file_tmp = $_FILES['room_image_file']['tmp_name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                // Check file extension
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (!in_array($file_ext, $allowed_extensions)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Invalid file extension. Only JPG, PNG, and GIF are allowed.'
                    ]);
                    exit();
                }
                
                // Generate unique filename
                $new_file_name = 'room_' . $room_id . '_' . time() . '.' . $file_ext;
                $upload_dir = '../uploads/rooms/';
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $upload_path = $upload_dir . $new_file_name;
                
                // Move uploaded file
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $image_path = $upload_path;
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to upload image. Please try again.'
                    ]);
                    exit();
                }
            }
            
            // Update room in database
            $stmt = $pdo->prepare("UPDATE rooms SET room_number = :room_number, type = :type, price = :price, status = :status, description = :description, image = :image, updated_at = NOW() WHERE id = :id");
            $stmt->bindParam(':room_number', $room_number, PDO::PARAM_STR);
            $stmt->bindParam(':type', $room_type, PDO::PARAM_STR);
            $stmt->bindParam(':price', $room_price, PDO::PARAM_STR);
            $stmt->bindParam(':status', $room_status, PDO::PARAM_STR);
            $stmt->bindParam(':description', $room_description, PDO::PARAM_STR);
            $stmt->bindParam(':image', $image_path, PDO::PARAM_STR);
            $stmt->bindParam(':id', $room_id, PDO::PARAM_INT);
            $stmt->execute();
            
            // Return success response
            echo json_encode([
                'success' => true,
                'message' => 'Room updated successfully'
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
        break;
}
