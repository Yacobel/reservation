<?php
session_start();

require_once '../config/db.php';

$page_title = 'Manage Rooms';

$page_css = '<link rel="stylesheet" href="css/rooms.css">';

echo '<style>
@import url("css/rooms.css");
</style>';

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $room_id = $_GET['id'];
    
    if ($action === 'delete') {
        try {
            // Only count active reservations (not canceled or completed)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE room_id = :room_id AND status NOT IN ('canceled', 'completed')");
            $stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
            $stmt->execute();
            $reservation_count = $stmt->fetchColumn();
            
            if ($reservation_count > 0) {
                // If there's a force parameter, deactivate the room instead of deleting it
                if (isset($_GET['force']) && $_GET['force'] == 1) {
                    $stmt = $pdo->prepare("UPDATE rooms SET status = 'inactive' WHERE id = :id");
                    $stmt->bindParam(':id', $room_id, PDO::PARAM_INT);
                    $stmt->execute();
                    
                    header("Location: rooms.php?msg=room_deactivated");
                    exit();
                } else {
                    // Redirect to confirmation page for deactivation
                    header("Location: rooms.php?msg=cannot_delete&id=$room_id");
                    exit();
                }
            } else {
                $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = :id");
                $stmt->bindParam(':id', $room_id, PDO::PARAM_INT);
                $stmt->execute();
                
                header("Location: rooms.php?msg=room_deleted");
                exit();
            }
        } catch (PDOException $e) {
            header("Location: rooms.php?msg=error");
            exit();
        }
    } else if ($action === 'activate') {
        try {
            $stmt = $pdo->prepare("UPDATE rooms SET status = 'available' WHERE id = :id");
            $stmt->bindParam(':id', $room_id, PDO::PARAM_INT);
            $stmt->execute();
            
            header("Location: rooms.php?msg=room_activated");
            exit();
        } catch (PDOException $e) {
            header("Location: rooms.php?msg=error");
            exit();
        }
    }
}

// Get rooms with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = '';
$params = [];

if (!empty($search)) {
    $search_condition = "WHERE room_number LIKE :search OR type LIKE :search";
    $params[':search'] = "%$search%";
}

try {
    // Get total rooms count for pagination
    $count_sql = "SELECT COUNT(*) FROM rooms $search_condition";
    $stmt = $pdo->prepare($count_sql);
    if (!empty($search)) {
        $stmt->bindParam(':search', $params[':search'], PDO::PARAM_STR);
    }
    $stmt->execute();
    $total_rooms = $stmt->fetchColumn();
    
    // Calculate total pages
    $total_pages = ceil($total_rooms / $limit);
    
    // Get rooms for current page
    $sql = "SELECT * FROM rooms $search_condition ORDER BY id DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    if (!empty($search)) {
        $stmt->bindParam(':search', $params[':search'], PDO::PARAM_STR);
    }
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Handle alert messages
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
        case 'cannot_delete':
            $room_id = isset($_GET['id']) ? $_GET['id'] : '';
            $alert_message = 'Cannot delete room because it has active reservations. <a href="rooms.php?action=delete&id=' . $room_id . '&force=1" class="alert-link">Deactivate room instead?</a>';
            $alert_type = 'warning';
            break;
        case 'room_deactivated':
            $alert_message = 'Room has been deactivated successfully. It will no longer be available for new bookings.';
            $alert_type = 'success';
            break;
        case 'room_activated':
            $alert_message = 'Room has been activated successfully. It is now available for bookings.';
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
                <h1>Manage Rooms</h1>
                <p>Add, edit, and delete hotel rooms</p>
            </div>

            <div class="room-management">
                <div class="room-actions">
                    <div class="search-box">
                        <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <form action="" method="get">
                            <input type="text" name="search" placeholder="Search rooms..." value="<?php echo htmlspecialchars($search); ?>">
                        </form>
                    </div>
                    <button type="button" id="openAddRoomModal" class="btn-add">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Add New Room
                    </button>
                </div>

                <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="room-table">
                        <thead>
                            <tr>
                                <th>Room</th>
                                <th>Room Number</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($rooms) > 0): ?>
                                <?php foreach ($rooms as $room): ?>
                                <tr>
                                    <td>
                                        <div class="room-info">
                                            <img src="<?php echo !empty($room['image']) ? htmlspecialchars($room['image']) : 'https://www.hilton.com/im/en/NoHotel/15483650/shutterstock-1078953762.jpg?impolicy=crop&cw=5000&ch=3333&gravity=NorthWest&xposition=0&yposition=0&rw=768&rh=512'; ?>" alt="<?php echo htmlspecialchars($room['type']); ?>" class="room-thumbnail">
                                            <div class="room-details">
                                                <span class="room-type"><?php echo htmlspecialchars($room['type']); ?> Room</span>
                                                <span class="room-number">Room <?php echo htmlspecialchars($room['room_number']); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($room['room_number']); ?></td>
                                    <td><?php echo htmlspecialchars($room['type']); ?></td>
                                    <td class="room-price">$<?php echo number_format($room['price'], 2); ?></td>
                                    <td>
                                        <span class="room-status status-<?php echo strtolower($room['status']); ?>">
                                            <?php echo ucfirst($room['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="room-actions-cell">
                                            <a href="view-room.php?id=<?php echo $room['id']; ?>" class="btn-view">View</a>
                                            <button type="button" class="btn-edit edit-room-btn" data-id="<?php echo $room['id']; ?>">Edit</button>
                                            <?php if (strtolower($room['status']) == 'inactive'): ?>
                                                <a href="rooms.php?action=activate&id=<?php echo $room['id']; ?>" class="btn-activate" onclick="return confirm('Are you sure you want to activate this room?');">Activate</a>
                                            <?php else: ?>
                                                <a href="rooms.php?action=delete&id=<?php echo $room['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this room?');">Delete</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 30px;">No rooms found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="pagination-item pagination-prev">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="pagination-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="pagination-item pagination-next">
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
    </main>

    <!-- Add Room Modal -->
    <div id="addRoomModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Room</h2>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <form id="addRoomForm" method="post" action="" class="room-form" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="room_number">Room Number</label>
                            <input type="text" id="room_number" name="room_number" class="form-control" required>
                            <small>Enter a unique room number (e.g., 101, 102, etc.)</small>
                        </div>

                        <div class="form-group">
                            <label for="room_type">Room Type</label>
                            <select id="room_type" name="room_type" class="form-control" required>
                                <option value="" disabled selected>Select Room Type</option>
                                <option value="Standard">Standard</option>
                                <option value="Deluxe">Deluxe</option>
                                <option value="Suite">Suite</option>
                                <option value="Executive">Executive</option>
                                <option value="Presidential">Presidential</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="room_price">Price per Night ($)</label>
                            <input type="number" id="room_price" name="room_price" class="form-control" step="0.01" min="0" required>
                        </div>

                        <div class="form-group">
                            <label for="room_image_file">Room Image</label>
                            <input type="file" id="room_image_file" name="room_image_file" class="form-control" accept="image/*">
                            <small>Upload an image for the room (JPG, PNG, GIF)</small>
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
                        <button type="button" class="btn-cancel" id="cancelAddRoom">Cancel</button>
                        <button type="submit" class="btn-save">Add Room</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Room Modal -->
    <div id="editRoomModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Room</h2>
                <span class="close-modal" id="closeEditModal">&times;</span>
            </div>
            <div class="modal-body">
                <!-- Left side - Room Image -->
                <div class="modal-image">
                    <img id="modal_room_image" src="" alt="Room Image">
                </div>
                
                <!-- Right side - Edit Form -->
                <div class="modal-form">
                    <form id="editRoomForm" method="post" action="update-room.php" class="room-form" enctype="multipart/form-data">
                        <input type="hidden" id="edit_room_id" name="room_id">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_room_number">Room Number</label>
                                <input type="text" id="edit_room_number" name="room_number" class="form-control" required>
                                <small>Enter a unique room number (e.g., 101, 102, etc.)</small>
                            </div>

                            <div class="form-group">
                                <label for="edit_room_type">Room Type</label>
                                <select id="edit_room_type" name="room_type" class="form-control" required>
                                    <option value="" disabled>Select Room Type</option>
                                    <option value="Standard">Standard</option>
                                    <option value="Deluxe">Deluxe</option>
                                    <option value="Suite">Suite</option>
                                    <option value="Executive">Executive</option>
                                    <option value="Presidential">Presidential</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_room_price">Price per Night ($)</label>
                                <input type="number" id="edit_room_price" name="room_price" class="form-control" step="0.01" min="0" required>
                            </div>

                            <div class="form-group">
                                <label for="edit_room_status">Room Status</label>
                                <select id="edit_room_status" name="room_status" class="form-control" required>
                                    <option value="" disabled>Select Status</option>
                                    <option value="Available">Available</option>
                                    <option value="Occupied">Occupied</option>
                                    <option value="Maintenance">Maintenance</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_room_description">Room Description</label>
                            <textarea id="edit_room_description" name="room_description" class="form-control" rows="4"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="edit_room_image_file">Upload New Image</label>
                            <input type="file" id="edit_room_image_file" name="room_image_file" class="form-control" accept="image/*" onchange="previewImage(this)">
                            <small>Upload a new image for the room (JPG, PNG, GIF)</small>
                            <input type="hidden" id="current_image_path" name="current_image_path">
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-primary">Update Room</button>
                            <button type="button" id="cancelEditRoom" class="btn-secondary">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Function to preview image when a new file is selected
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                document.getElementById('modal_room_image').src = e.target.result;
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Close alert messages
        const alertCloseButtons = document.querySelectorAll('.alert .close-btn');
        alertCloseButtons.forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.display = 'none';
            });
        }, 5000);

        // Modal functionality
        const modal = document.getElementById('addRoomModal');
        const openModalBtn = document.getElementById('openAddRoomModal');
        const closeModalBtn = document.querySelector('.close-modal');
        const cancelBtn = document.getElementById('cancelAddRoom');

        // Open modal when the button is clicked
        openModalBtn.addEventListener('click', function() {
            modal.style.display = 'block';
            document.body.classList.add('modal-open');
        });

        // Close modal when the close button is clicked
        closeModalBtn.addEventListener('click', function() {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
        });

        // Close modal when the cancel button is clicked
        cancelBtn.addEventListener('click', function() {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
        });

        // Close modal when clicking outside of it
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
                document.body.classList.remove('modal-open');
            }
            if (event.target === editModal) {
                editModal.style.display = 'none';
                document.body.classList.remove('modal-open');
            }
        });
        
        // Edit Room Modal functionality
        const editModal = document.getElementById('editRoomModal');
        const closeEditModalBtn = document.getElementById('closeEditModal');
        const cancelEditBtn = document.getElementById('cancelEditRoom');
        const editRoomForm = document.getElementById('editRoomForm');
        const editButtons = document.querySelectorAll('.edit-room-btn');
        
        // Add click event to all edit buttons
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const roomId = this.getAttribute('data-id');
                fetchRoomDetails(roomId);
            });
        });
        
        // Close edit modal when the close button is clicked
        closeEditModalBtn.addEventListener('click', function() {
            editModal.style.display = 'none';
            document.body.classList.remove('modal-open');
        });
        
        // Close edit modal when the cancel button is clicked
        cancelEditBtn.addEventListener('click', function() {
            editModal.style.display = 'none';
            document.body.classList.remove('modal-open');
        });
        
        // Function to fetch room details and populate the edit form
        function fetchRoomDetails(roomId) {
            // Show loading state
            document.body.classList.add('loading');
            
            // Create form data for the AJAX request
            const formData = new FormData();
            formData.append('action', 'get_room');
            formData.append('room_id', roomId);
            
            // Send AJAX request to get room details
            fetch('room-actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Populate the edit form with room data
                    document.getElementById('edit_room_id').value = data.room.id;
                    document.getElementById('edit_room_number').value = data.room.room_number;
                    document.getElementById('edit_room_type').value = data.room.type;
                    document.getElementById('edit_room_price').value = data.room.price;
                    document.getElementById('edit_room_status').value = data.room.status;
                    document.getElementById('edit_room_description').value = data.room.description || '';
                    
                    // Set the current image
                    const defaultImage = 'https://www.hilton.com/im/en/NoHotel/15483650/shutterstock-1078953762.jpg?impolicy=crop&cw=5000&ch=3333&gravity=NorthWest&xposition=0&yposition=0&rw=768&rh=512';
                    const modalRoomImage = document.getElementById('modal_room_image');
                    modalRoomImage.src = data.room.image ? data.room.image : defaultImage;
                    document.getElementById('current_image_path').value = data.room.image || '';
                    
                    // Add click event to enlarge image
                    modalRoomImage.onclick = function() {
                        this.classList.toggle('enlarged');
                    };
                    
                    // Add loading animation for image
                    modalRoomImage.onload = function() {
                        this.classList.add('loaded');
                    };
                    modalRoomImage.onerror = function() {
                        this.src = defaultImage;
                        this.classList.add('loaded');
                    };
                    
                    // Show the edit modal
                    editModal.style.display = 'block';
                    document.body.classList.add('modal-open');
                } else {
                    // Show error message
                    alert('Error: ' + data.message);
                }
                
                // Hide loading state
                document.body.classList.remove('loading');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while fetching room details. Please try again.');
                document.body.classList.remove('loading');
            });
        }

        // Live preview for the add room form
        const roomNumber = document.getElementById('room_number');
        const roomType = document.getElementById('room_type');
        const roomPrice = document.getElementById('room_price');
        const roomImageFile = document.getElementById('room_image_file');
        const previewNumber = document.getElementById('preview-number');
        const previewType = document.getElementById('preview-type');
        const previewPrice = document.getElementById('preview-price');
        const previewImage = document.getElementById('preview-image');

        // Update preview on input change
        roomNumber.addEventListener('input', updatePreview);
        roomType.addEventListener('change', updatePreview);
        roomPrice.addEventListener('input', updatePreview);
        roomImageFile.addEventListener('change', handleImageUpload);

        function updatePreview() {
            // Update room number
            previewNumber.textContent = roomNumber.value ? 'Room ' + roomNumber.value : 'Room Number';
            
            // Update room type
            previewType.textContent = roomType.value ? roomType.value + ' Room' : 'Room Type';
            
            // Update room price
            const price = parseFloat(roomPrice.value) || 0;
            previewPrice.innerHTML = '$' + price.toFixed(2) + ' <span>per night</span>';
        }
        
        function handleImageUpload(e) {
            const file = e.target.files[0];
            if (file) {
                // Check if the file is an image
                if (!file.type.match('image.*')) {
                    alert('Please select an image file (JPG, PNG, GIF)');
                    return;
                }
                
                // Create a FileReader to read the image
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Set the preview image source to the loaded image
                    previewImage.src = e.target.result;
                };
                
                // Read the image file as a data URL
                reader.readAsDataURL(file);
            } else {
                // No file selected, use default image
                previewImage.src = 'https://www.hilton.com/im/en/NoHotel/15483650/shutterstock-1078953762.jpg?impolicy=crop&cw=5000&ch=3333&gravity=NorthWest&xposition=0&yposition=0&rw=768&rh=512';
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

        // Handle form submission
        const addRoomForm = document.getElementById('addRoomForm');
        addRoomForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Create form data object
            const formData = new FormData(addRoomForm);
            
            // Add the file to FormData if selected
            const imageFile = roomImageFile.files[0];
            if (imageFile) {
                formData.append('room_image_file', imageFile);
            }
            
            // Show loading indicator
            const saveButton = addRoomForm.querySelector('.btn-save');
            const originalText = saveButton.textContent;
            saveButton.textContent = 'Saving...';
            saveButton.disabled = true;
            
            // Send AJAX request
            fetch('add-room-ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Reset button
                saveButton.textContent = originalText;
                saveButton.disabled = false;
                
                if (data.success) {
                    // Close modal
                    modal.style.display = 'none';
                    document.body.classList.remove('modal-open');
                    
                    // Show success message
                    alert(data.message);
                    
                    // Reload page to show new room
                    window.location.href = 'rooms.php?msg=room_added';
                } else {
                    // Show error message
                    alert(data.message);
                }
            })
            .catch(error => {
                // Reset button
                saveButton.textContent = originalText;
                saveButton.disabled = false;
                
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
        // Handle edit room form submission
        editRoomForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Create form data object
            const formData = new FormData(editRoomForm);
            formData.append('action', 'update_room');
            
            // Show loading state
            document.body.classList.add('loading');
            
            // Send AJAX request to update room
            fetch('room-actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    alert('Room updated successfully!');
                    
                    // Close the modal
                    editModal.style.display = 'none';
                    document.body.classList.remove('modal-open');
                    
                    // Reload the page to show updated data
                    window.location.reload();
                } else {
                    // Show error message
                    alert('Error: ' + data.message);
                }
                
                // Hide loading state
                document.body.classList.remove('loading');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the room. Please try again.');
                document.body.classList.remove('loading');
            });
        });
    });
    </script>

<?php include 'includes/footer.php'; ?>
