<?php
session_start();

require_once '../config/db.php';

$page_title = 'View Room Details';

$page_css = '<link rel="stylesheet" href="css/view-room.css">';

echo '<style>
@import url("css/view-room.css");
</style>';

if (!isset($_GET['id'])) {
    header("Location: rooms.php?msg=no_room_id");
    exit();
}

$room_id = $_GET['id'];

$reservations = [];
$error = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = :id");
    $stmt->bindParam(':id', $room_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $room = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$room) {
        header("Location: rooms.php?msg=room_not_found");
        exit();
    }
    
    $stmt = $pdo->prepare("SELECT r.*, u.name as user_name, u.email as user_email 
                           FROM reservations r 
                           LEFT JOIN users u ON r.user_id = u.id 
                           WHERE r.room_id = :room_id 
                           ORDER BY r.check_in_date DESC");
    $stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Format price
$formatted_price = number_format($room['price'], 2);

$room_image = !empty($room['image']) ? $room['image'] : 'https://www.hilton.com/im/en/NoHotel/15483650/shutterstock-1078953762.jpg?impolicy=crop&cw=5000&ch=3333&gravity=NorthWest&xposition=0&yposition=0&rw=768&rh=512';

$status_class = strtolower($room['status']);
?>

<?php include 'includes/header.php'; ?>
        <div class="admin-container">
            <div class="room-details-header">
                <div class="back-button">
                    <a href="rooms.php" class="btn-back">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        Back to Rooms
                    </a>
                </div>
                <h1>Room Details</h1>
            </div>

            <div class="room-details-container">
                <div class="room-details-card">
                    <div class="room-details-image">
                        <img src="<?php echo htmlspecialchars($room_image); ?>" alt="<?php echo htmlspecialchars($room['type']); ?> Room">
                        <span class="room-status status-<?php echo $status_class; ?>">
                            <?php echo ucfirst($room['status']); ?>
                        </span>
                    </div>
                    
                    <div class="room-details-info">
                        <div class="room-details-header">
                            <h2><?php echo htmlspecialchars($room['type']); ?> Room</h2>
                            <h3>Room <?php echo htmlspecialchars($room['room_number']); ?></h3>
                        </div>
                        
                        <div class="room-details-price">
                            <span class="price-label">Price per Night:</span>
                            <span class="price-value">$<?php echo $formatted_price; ?></span>
                        </div>
                        
                        <div class="room-details-meta">
                            <div class="meta-item">
                                <span class="meta-label">Room ID:</span>
                                <span class="meta-value"><?php echo htmlspecialchars($room['id']); ?></span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Created:</span>
                                <span class="meta-value"><?php echo date('F j, Y', strtotime($room['created_at'])); ?></span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Last Updated:</span>
                                <span class="meta-value"><?php echo date('F j, Y', strtotime($room['updated_at'])); ?></span>
                            </div>
                        </div>
                        
                        <?php if (!empty($room['description'])): ?>
                        <div class="room-details-description">
                            <h3>Description</h3>
                            <p><?php echo nl2br(htmlspecialchars($room['description'])); ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="room-details-actions">
                            <button type="button" class="btn-edit edit-room-btn" data-id="<?php echo $room['id']; ?>">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                                Edit Room
                            </button>
                            <a href="rooms.php?action=delete&id=<?php echo $room['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this room?');">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                                Delete Room
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="room-reservation-history">
                    <h2>Reservation History</h2>
                    
                    <?php if (count($reservations) > 0): ?>
                    <div class="table-responsive">
                        <table class="reservation-table">
                            <thead>
                                <tr>
                                    <th>Reservation ID</th>
                                    <th>Guest</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reservations as $reservation): ?>
                                <tr>
                                    <td>#<?php echo $reservation['id']; ?></td>
                                    <td>
                                        <div class="guest-info">
                                            <span class="guest-name"><?php echo htmlspecialchars($reservation['user_name']); ?></span>
                                            <span class="guest-email"><?php echo htmlspecialchars($reservation['user_email']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($reservation['check_in_date'])); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($reservation['check_out_date'])); ?></td>
                                    <td>
                                        <span class="reservation-status status-<?php echo strtolower($reservation['status']); ?>">
                                            <?php echo ucfirst($reservation['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="reservation-details.php?id=<?php echo $reservation['id']; ?>" class="btn-view">View</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="no-reservations">
                        <p>No reservation history found for this room.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

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
                    <img id="modal_room_image" src="<?php echo htmlspecialchars($room_image); ?>" alt="<?php echo htmlspecialchars($room['type']); ?> Room">
                </div>
                
                <!-- Right side - Edit Form -->
                <div class="modal-form">
                    <form id="editRoomForm" method="post" action="update-room.php" class="room-form" enctype="multipart/form-data">
                        <input type="hidden" id="edit_room_id" name="room_id" value="<?php echo $room['id']; ?>">
                        
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
                            <button type="button" id="cancelEditRoom" class="btn-cancel">Cancel</button>
                            <button type="submit" class="btn-save">Save Changes</button>
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
        
        // Close modal when clicking outside of it
        window.addEventListener('click', function(event) {
            if (event.target === editModal) {
                editModal.style.display = 'none';
                document.body.classList.remove('modal-open');
            }
        });
        
        // Function to fetch room details and populate the edit form
        function fetchRoomDetails(roomId) {
            // Show loading state
            document.body.classList.add('loading');
            
            // For the view-room page, we already have the room data, so we can pre-populate the form
            // without making an AJAX request
            const currentRoomId = <?php echo json_encode($room['id']); ?>;
            const currentRoomNumber = <?php echo json_encode($room['room_number']); ?>;
            const currentRoomType = <?php echo json_encode($room['type']); ?>;
            const currentRoomPrice = <?php echo json_encode($room['price']); ?>;
            const currentRoomStatus = <?php echo json_encode($room['status']); ?>;
            const currentRoomDescription = <?php echo json_encode($room['description'] ?? ''); ?>;
            const currentRoomImage = <?php echo json_encode($room['image'] ?? ''); ?>;
            
            // Populate the edit form with room data
            document.getElementById('edit_room_id').value = currentRoomId;
            document.getElementById('edit_room_number').value = currentRoomNumber;
            document.getElementById('edit_room_type').value = currentRoomType;
            document.getElementById('edit_room_price').value = currentRoomPrice;
            document.getElementById('edit_room_status').value = currentRoomStatus;
            document.getElementById('edit_room_description').value = currentRoomDescription;
            
            // Set the current image
            const modalRoomImage = document.getElementById('modal_room_image');
            const defaultImage = 'https://www.hilton.com/im/en/NoHotel/15483650/shutterstock-1078953762.jpg?impolicy=crop&cw=5000&ch=3333&gravity=NorthWest&xposition=0&yposition=0&rw=768&rh=512';
            modalRoomImage.src = currentRoomImage ? currentRoomImage : defaultImage;
            document.getElementById('current_image_path').value = currentRoomImage || '';
            
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
            
            // Hide loading state
            document.body.classList.remove('loading');
            
            // If we need to fetch room details from server (for other pages), use this code:
            /*
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
                    const currentImage = document.getElementById('current_room_image');
                    const defaultImage = 'https://www.hilton.com/im/en/NoHotel/15483650/shutterstock-1078953762.jpg?impolicy=crop&cw=5000&ch=3333&gravity=NorthWest&xposition=0&yposition=0&rw=768&rh=512';
                    currentImage.src = data.room.image ? data.room.image : defaultImage;
                    document.getElementById('current_image_path').value = data.room.image || '';
                    
                    // Add click event to enlarge image
                    currentImage.onclick = function() {
                        this.classList.toggle('enlarged');
                    };
                    
                    // Add loading animation for image
                    currentImage.onload = function() {
                        this.classList.add('loaded');
                    };
                    currentImage.onerror = function() {
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
            */
        }
        
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
