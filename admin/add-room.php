<?php
session_start();

require_once '../config/db.php';

$page_title = 'Add New Room';

$page_css = '<link rel="stylesheet" href="css/room-form.css">';

echo '<style>
@import url("css/room-form.css");
</style>';

$room_number = '';
$room_type = '';
$room_price = '';
$room_image = '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_number = trim($_POST['room_number']);
    $room_type = trim($_POST['room_type']);
    $room_price = trim($_POST['room_price']);
    $room_image = isset($_POST['room_image']) ? trim($_POST['room_image']) : '';
    
    if (empty($room_number)) {
        $error = "Room number is required.";
    } elseif (empty($room_type)) {
        $error = "Room type is required.";
    } elseif (empty($room_price)) {
        $error = "Room price is required.";
    } elseif (!is_numeric($room_price)) {
        $error = "Room price must be a number.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM rooms WHERE room_number = :room_number");
            $stmt->bindParam(':room_number', $room_number, PDO::PARAM_STR);
            $stmt->execute();
            $room_exists = $stmt->fetchColumn();
            
            if ($room_exists) {
                $error = "Room number already exists.";
            } else {
                // Handle image upload if a file is provided
                $image_path = $room_image; // Default to URL if provided
                
                if (isset($_FILES['room_image_file']) && $_FILES['room_image_file']['size'] > 0) {
                    $file_name = $_FILES['room_image_file']['name'];
                    $file_tmp = $_FILES['room_image_file']['tmp_name'];
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    
                    // Check file extension
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                    
                    if (!in_array($file_ext, $allowed_extensions)) {
                        $error = "Invalid file extension. Only JPG, JPEG, PNG, and GIF are allowed.";
                    } else {
                        // Generate unique filename
                        $new_file_name = 'room_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
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
                            $error = "Failed to upload image. Please try again.";
                        }
                    }
                }
                
                if (empty($error)) {
                    // Insert new room
                    $stmt = $pdo->prepare("
                        INSERT INTO rooms (room_number, type, price, status, image)
                        VALUES (:room_number, :type, :price, 'available', :image)
                    ");
                    $stmt->bindParam(':room_number', $room_number, PDO::PARAM_STR);
                    $stmt->bindParam(':type', $room_type, PDO::PARAM_STR);
                    $stmt->bindParam(':price', $room_price, PDO::PARAM_STR);
                    $stmt->bindParam(':image', $image_path, PDO::PARAM_STR);
                    $stmt->execute();
                    
                    // Redirect to rooms page with success message
                    header("Location: rooms.php?msg=room_added");
                    exit();
                }
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

        <div class="admin-container">
            <div class="admin-header">
                <h1>Add New Room</h1>
                <p>Create a new room in the hotel</p>
            </div>

            <div class="room-management">
                <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                    <button class="close-btn">&times;</button>
                </div>
                <?php endif; ?>

                <form method="post" action="" class="room-form" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="room_number">Room Number</label>
                            <input type="text" id="room_number" name="room_number" class="form-control" value="<?php echo htmlspecialchars($room_number); ?>" required>
                            <small>Enter a unique room number (e.g., 101, 102, etc.)</small>
                        </div>

                        <div class="form-group">
                            <label for="room_type">Room Type</label>
                            <select id="room_type" name="room_type" class="form-control" required>
                                <option value="" disabled <?php echo empty($room_type) ? 'selected' : ''; ?>>Select Room Type</option>
                                <option value="Standard" <?php echo $room_type === 'Standard' ? 'selected' : ''; ?>>Standard</option>
                                <option value="Deluxe" <?php echo $room_type === 'Deluxe' ? 'selected' : ''; ?>>Deluxe</option>
                                <option value="Suite" <?php echo $room_type === 'Suite' ? 'selected' : ''; ?>>Suite</option>
                                <option value="Executive" <?php echo $room_type === 'Executive' ? 'selected' : ''; ?>>Executive</option>
                                <option value="Presidential" <?php echo $room_type === 'Presidential' ? 'selected' : ''; ?>>Presidential</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="room_price">Price per Night ($)</label>
                            <input type="number" id="room_price" name="room_price" class="form-control" value="<?php echo htmlspecialchars($room_price); ?>" step="0.01" min="0" required>
                        </div>

                        <div class="form-group">
                            <label for="room_image_file">Room Image</label>
                            <div class="image-upload-container">
                                <input type="file" id="room_image_file" name="room_image_file" class="form-control" accept="image/jpeg,image/png,image/gif">
                                <div class="upload-options">OR</div>
                                <input type="url" id="room_image" name="room_image" class="form-control" value="<?php echo htmlspecialchars($room_image); ?>" placeholder="Enter image URL">
                            </div>
                            <small>Upload an image or enter a URL for the room image</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Room Preview</label>
                        <div class="room-preview">
                            <div class="room-card">
                                <div class="room-image">
                                    <img id="preview-image" src="https://www.hilton.com/im/en/NoHotel/15483650/shutterstock-1078953762.jpg?impolicy=crop&cw=5000&ch=3333&gravity=NorthWest&xposition=0&yposition=0&rw=768&rh=512" alt="Room Preview">
                                </div>
                                <div class="room-details">
                                    <h3 id="preview-type">Room Type</h3>
                                    <p class="room-number" id="preview-number">Room Number</p>
                                    <p class="room-price" id="preview-price">$0.00 <span>per night</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="rooms.php" class="btn-cancel">Cancel</a>
                        <button type="submit" class="btn-save">Add Room</button>
                    </div>
                </form>
            </div>
        </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Live preview
    const roomNumber = document.getElementById('room_number');
    const roomType = document.getElementById('room_type');
    const roomPrice = document.getElementById('room_price');
    const roomImage = document.getElementById('room_image');
    const previewNumber = document.getElementById('preview-number');
    const previewType = document.getElementById('preview-type');
    const previewPrice = document.getElementById('preview-price');
    const previewImage = document.getElementById('preview-image');

    // Get file input element
    const roomImageFile = document.getElementById('room_image_file');

    // Update preview on input change
    roomNumber.addEventListener('input', updatePreview);
    roomType.addEventListener('change', updatePreview);
    roomPrice.addEventListener('input', updatePreview);
    roomImage.addEventListener('input', updatePreview);
    roomImageFile.addEventListener('change', handleFileSelect);

    function updatePreview() {
        // Update room number
        previewNumber.textContent = roomNumber.value ? 'Room ' + roomNumber.value : 'Room Number';
        
        // Update room type
        previewType.textContent = roomType.value ? roomType.value + ' Room' : 'Room Type';
        
        // Update room price
        const price = parseFloat(roomPrice.value) || 0;
        previewPrice.innerHTML = '$' + price.toFixed(2) + ' <span>per night</span>';
        
        // Update room image (only if no file is selected)
        if (!hasFileSelected && roomImage.value && isValidUrl(roomImage.value)) {
            previewImage.src = roomImage.value;
        } else if (!hasFileSelected) {
            previewImage.src = 'https://www.hilton.com/im/en/NoHotel/15483650/shutterstock-1078953762.jpg?impolicy=crop&cw=5000&ch=3333&gravity=NorthWest&xposition=0&yposition=0&rw=768&rh=512';
        }
    }
    
    // Track if a file has been selected
    let hasFileSelected = false;
    
    // Handle file selection for preview
    function handleFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
            hasFileSelected = true;
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
            };
            reader.readAsDataURL(file);
            
            // Clear URL input when file is selected
            roomImage.value = '';
        } else {
            hasFileSelected = false;
            updatePreview();
        }
    }

    function isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }

    // Initial preview update
    updatePreview();
});
</script>

<?php include 'includes/footer.php'; ?>