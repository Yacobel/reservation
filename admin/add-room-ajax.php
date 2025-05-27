<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$room_number = isset($_POST['room_number']) ? trim($_POST['room_number']) : '';
$room_type = isset($_POST['room_type']) ? trim($_POST['room_type']) : '';
$room_price = isset($_POST['room_price']) ? trim($_POST['room_price']) : '';
$room_image = '';
if (isset($_FILES['room_image_file']) && $_FILES['room_image_file']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../uploads/rooms/';
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_name = $_FILES['room_image_file']['name'];
    $file_tmp = $_FILES['room_image_file']['tmp_name'];
    $file_size = $_FILES['room_image_file']['size'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $new_file_name = 'room_' . time() . '_' . uniqid() . '.' . $file_ext;
    $upload_path = $upload_dir . $new_file_name;
    
    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($file_ext, $allowed_exts)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Only JPG, JPEG, PNG, and GIF files are allowed.']);
        exit();
    }
    
    // Check file size (max 5MB)
    if ($file_size > 5 * 1024 * 1024) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'File size should not exceed 5MB.']);
        exit();
    }
    
    // Move uploaded file
    if (move_uploaded_file($file_tmp, $upload_path)) {
        // Set the image path for database
        $room_image = '/reservation/uploads/rooms/' . $new_file_name;
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to upload image. Please try again.']);
        exit();
    }
}

// Validate form data
$response = ['success' => false, 'message' => ''];

if (empty($room_number)) {
    $response['message'] = 'Room number is required.';
} elseif (empty($room_type)) {
    $response['message'] = 'Room type is required.';
} elseif (empty($room_price)) {
    $response['message'] = 'Room price is required.';
} elseif (!is_numeric($room_price)) {
    $response['message'] = 'Room price must be a number.';
} else {
    try {
        // Check if room number already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM rooms WHERE room_number = :room_number");
        $stmt->bindParam(':room_number', $room_number, PDO::PARAM_STR);
        $stmt->execute();
        $room_exists = $stmt->fetchColumn();
        
        if ($room_exists) {
            $response['message'] = 'Room number already exists.';
        } else {
            // Insert new room
            $stmt = $pdo->prepare("
                INSERT INTO rooms (room_number, type, price, status, image)
                VALUES (:room_number, :type, :price, 'available', :image)
            ");
            $stmt->bindParam(':room_number', $room_number, PDO::PARAM_STR);
            $stmt->bindParam(':type', $room_type, PDO::PARAM_STR);
            $stmt->bindParam(':price', $room_price, PDO::PARAM_STR);
            $stmt->bindParam(':image', $room_image, PDO::PARAM_STR);
            $stmt->execute();
            
            $response['success'] = true;
            $response['message'] = 'Room has been added successfully.';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
