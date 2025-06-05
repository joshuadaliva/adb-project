<?php
require_once __DIR__ . '/../includes/auth.php';

if (!isAdmin()) {
    redirectWithMessage('../login.php', 'error', 'Unauthorized access');
}

require_once __DIR__ . '/../config/database.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_room'])) {
        // Add new room
        $roomNumber = sanitizeInput($_POST['roomNumber']);
        $roomType = sanitizeInput($_POST['roomType']);
        $description = sanitizeInput($_POST['description']);
        $availableRooms = (int)$_POST['availableRooms'];
        $occupants = (int)$_POST['occupants'];
        $rentFee = (float)$_POST['rentFee'];
        $status = sanitizeInput($_POST['status']);

        // Handle image upload
        $imagePath = '';
        if (isset($_FILES['roomImage']) && $_FILES['roomImage']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/images/rooms/';
            $uploadFile = $uploadDir . basename($_FILES['roomImage']['name']);

            // Ensure directory exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Check if file is an image
            $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($imageFileType, $allowedTypes)) {
                if (move_uploaded_file($_FILES['roomImage']['tmp_name'], $uploadFile)) {
                    $imagePath = 'assets/images/rooms/' . basename($_FILES['roomImage']['name']);
                }
            }
        }

        $stmt = $conn->prepare("INSERT INTO rooms (roomNumber, roomType, description, imagePath, availableRooms, occupants, rentFee, status) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssiids", $roomNumber, $roomType, $description, $imagePath, $availableRooms, $occupants, $rentFee, $status);

        if ($stmt->execute()) {
            redirectWithMessage('rooms.php', 'success', 'Room added successfully');
        } else {
            redirectWithMessage('rooms.php', 'error', 'Failed to add room');
        }
    } elseif (isset($_POST['update_room'])) {
        // Update existing room
        $roomID = (int)$_POST['roomID'];
        $roomNumber = sanitizeInput($_POST['roomNumber']);
        $roomType = sanitizeInput($_POST['roomType']);
        $description = sanitizeInput($_POST['description']);
        $availableRooms = (int)$_POST['availableRooms'];
        $occupants = (int)$_POST['occupants'];
        $rentFee = (float)$_POST['rentFee'];
        $newStatus = sanitizeInput($_POST['status']);

        // Get current room status
        $currentStatusQuery = $conn->prepare("SELECT status FROM rooms WHERE roomID = ?");
        $currentStatusQuery->bind_param("i", $roomID);
        $currentStatusQuery->execute();
        $currentStatusResult = $currentStatusQuery->get_result();
        $currentStatus = $currentStatusResult->fetch_assoc()['status'];

        // Check if changing to unavailable/under maintenance
        if (($newStatus === 'Unavailable' || $newStatus === 'Under Maintenance') && 
            ($currentStatus === 'Available')) {

            // Check if there are any active bookings for this room
            $bookingCheck = $conn->prepare("SELECT COUNT(*) as active_bookings 
                                          FROM bookings 
                                          WHERE roomID = ? AND (status = 'Active' OR status = 'Pending')");
            $bookingCheck->bind_param("i", $roomID);
            $bookingCheck->execute();
            $bookingResult = $bookingCheck->get_result();
            $activeBookings = $bookingResult->fetch_assoc()['active_bookings'];

            if ($activeBookings > 0) {
                redirectWithMessage(
                    'rooms.php',
                    'error',
                    'Cannot mark room as unavailable - there are active or pending bookings for this room'
                );
            }
        }

        // Handle image upload if new image is provided
        $imageUpdate = '';
        $imagePath = '';
        if (isset($_FILES['roomImage']) && $_FILES['roomImage']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/images/rooms/';
            $uploadFile = $uploadDir . basename($_FILES['roomImage']['name']);

            // Check if file is an image
            $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($imageFileType, $allowedTypes)) {
                if (move_uploaded_file($_FILES['roomImage']['tmp_name'], $uploadFile)) {
                    $imagePath = 'assets/images/rooms/' . basename($_FILES['roomImage']['name']);
                    $imageUpdate = ", imagePath = ?";
                }
            }
        }

        if (!empty($imageUpdate)) {
            $stmt = $conn->prepare("UPDATE rooms SET roomNumber = ?, roomType = ?, description = ?, 
                                  availableRooms = ?, occupants = ?, rentFee = ?, status = ? $imageUpdate 
                                  WHERE roomID = ?");
            $stmt->bind_param(
                "issiidsi",
                $roomNumber,
                $roomType,
                $description,
                $availableRooms,
                $occupants,
                $rentFee,
                $newStatus,
                $imagePath,
                $roomID
            );
        } else {
            $stmt = $conn->prepare("UPDATE rooms SET roomNumber = ?, roomType = ?, description = ?, 
                                  availableRooms = ?, occupants = ?, rentFee = ?, status = ? 
                                  WHERE roomID = ?");
            $stmt->bind_param(
                "issiidsi",
                $roomNumber,
                $roomType,
                $description,
                $availableRooms,
                $occupants,
                $rentFee,
                $newStatus,
                $roomID
            );
        }

        if ($stmt->execute()) {
            redirectWithMessage('rooms.php', 'success', 'Room updated successfully');
        } else {
            redirectWithMessage('rooms.php', 'error', 'Failed to update room');
        }
    } elseif (isset($_POST['delete_room'])) {
        // Delete room
        $roomID = (int)$_POST['roomID'];

        // Check if there are any active bookings for this room
        $bookingCheck = $conn->prepare("SELECT COUNT(*) as active_bookings 
                                      FROM bookings 
                                      WHERE roomID = ? AND (status = 'Active' OR status = 'Pending')");
        $bookingCheck->bind_param("i", $roomID);
        $bookingCheck->execute();
        $bookingResult = $bookingCheck->get_result();
        $activeBookings = $bookingResult->fetch_assoc()['active_bookings'];

        if ($activeBookings > 0) {
            redirectWithMessage(
                'rooms.php',
                'error',
                'Cannot delete room - there are active or pending bookings for this room'
            );
        }

        $stmt = $conn->prepare("DELETE FROM rooms WHERE roomID = ?");
        $stmt->bind_param("i", $roomID);

        if ($stmt->execute()) {
            redirectWithMessage('rooms.php', 'success', 'Room deleted successfully');
        } else {
            redirectWithMessage('rooms.php', 'error', 'Failed to delete room');
        }
    }
}

// Get all rooms
$stmt = $conn->prepare("SELECT * FROM rooms ORDER BY roomNumber");
$stmt->execute();
$rooms = $stmt->get_result();

// Include the header that contains the sidebar and topbar
require_once __DIR__ . '/../includes/header.php';
?>


<main class="flex-grow container mx-auto px-6 py-8">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $_SESSION['message_type'] === 'error' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?> shadow">
            <?php echo htmlspecialchars($_SESSION['message']); ?>
            <?php unset($_SESSION['message']);
            unset($_SESSION['message_type']); ?>
        </div>
    <?php endif; ?>

    <h1 class="text-3xl font-extrabold text-gray-900 mb-8 tracking-tight">Manage Rooms</h1>

    <div class="bg-white rounded-xl shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Add New Room</h2>

        <form action="rooms.php" method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="roomNumber" class="block text-gray-700 mb-2">Room Number:</label>
                    <input type="number" id="roomNumber" name="roomNumber" class="w-full px-3 py-2 border rounded" required>
                </div>

                <div>
                    <label for="roomType" class="block text-gray-700 mb-2">Room Type:</label>
                    <select id="roomType" name="roomType" class="w-full px-3 py-2 border rounded" required>
                        <option value="Bed Spacer">Bed Spacer</option>
                        <option value="Single Room">Single Room</option>
                        <option value="Double Room">Double Room</option>
                        <option value="Dormitory">Dormitory</option>
                    </select>
                </div>

                <div>
                    <label for="availableRooms" class="block text-gray-700 mb-2">Available Rooms:</label>
                    <input type="number" id="availableRooms" name="availableRooms" class="w-full px-3 py-2 border rounded" required>
                </div>

                <div>
                    <label for="occupants" class="block text-gray-700 mb-2">Max Occupants:</label>
                    <input type="number" id="occupants" name="occupants" class="w-full px-3 py-2 border rounded" required>
                </div>

                <div>
                    <label for="rentFee" class="block text-gray-700 mb-2">Monthly Rent (₱):</label>
                    <input type="number" id="rentFee" name="rentFee" step="0.01" class="w-full px-3 py-2 border rounded" required>
                </div>

                <div>
                    <label for="status" class="block text-gray-700 mb-2">Status:</label>
                    <select id="status" name="status" class="w-full px-3 py-2 border rounded" required>
                        <option value="Available">Available</option>
                        <option value="Unavailable">Unavailable</option>
                        <option value="Under Maintenance">Under Maintenance</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="block text-gray-700 mb-2">Description:</label>
                    <textarea id="description" name="description" class="w-full px-3 py-2 border rounded"></textarea>
                </div>

                <div class="md:col-span-2">
                    <label for="roomImage" class="block text-gray-700 mb-2">Room Image:</label>
                    <input type="file" id="roomImage" name="roomImage" class="w-full px-3 py-2 border rounded">
                </div>
            </div>

            <button type="submit" name="add_room" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Room</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4">Room List</h2>

        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Room No.</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Available</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Rent Fee</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php while ($room = $rooms->fetch_assoc()): ?>
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-700"><?php echo htmlspecialchars($room['roomNumber']); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600"><?php echo htmlspecialchars($room['roomType']); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600"><?php echo htmlspecialchars($room['availableRooms']); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">₱<?php echo number_format($room['rentFee'], 2); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="<?php
                                                echo $room['status'] === 'Available' ? 'bg-green-100 text-green-800' : ($room['status'] === 'Under Maintenance' ? 'bg-yellow-100 text-yellow-800' :
                                                        'bg-red-100 text-red-800');
                                                ?> px-3 py-1 rounded-full text-xs font-semibold">
                                    <?php echo $room['status']; ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap space-x-2">
                                <button onclick="openEditModal(<?php echo $room['roomID']; ?>)" class="bg-yellow-500 text-white px-3 py-1 rounded text-sm hover:bg-yellow-600">Edit</button>
                                <form action="rooms.php" method="POST" class="inline">
                                    <input type="hidden" name="roomID" value="<?php echo $room['roomID']; ?>">
                                    <button type="submit" name="delete_room" class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600" onclick="return confirm('Are you sure you want to delete this room?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Edit Room Modal -->
<!-- Edit Room Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold text-gray-800">Edit Room Details</h3>
                <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="editForm" action="rooms.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" id="edit_roomID" name="roomID">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_roomNumber" class="block text-sm font-medium text-gray-700 mb-1">Room Number</label>
                        <input type="number" id="edit_roomNumber" name="roomNumber" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>

                    <div>
                        <label for="edit_roomType" class="block text-sm font-medium text-gray-700 mb-1">Room Type</label>
                        <select id="edit_roomType" name="roomType" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="Bed Spacer">Bed Spacer</option>
                            <option value="Single Room">Single Room</option>
                            <option value="Double Room">Double Room</option>
                            <option value="Dormitory">Dormitory</option>
                        </select>
                    </div>

                    <div>
                        <label for="edit_availableRooms" class="block text-sm font-medium text-gray-700 mb-1">Available Rooms</label>
                        <input type="number" id="edit_availableRooms" name="availableRooms" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>

                    <div>
                        <label for="edit_occupants" class="block text-sm font-medium text-gray-700 mb-1">Max Occupants</label>
                        <input type="number" id="edit_occupants" name="occupants" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>

                    <div>
                        <label for="edit_rentFee" class="block text-sm font-medium text-gray-700 mb-1">Monthly Rent (₱)</label>
                        <input type="number" id="edit_rentFee" name="rentFee" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>

                    <div>
                        <label for="edit_status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="edit_status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="Available">Available</option>
                            <option value="Unavailable">Unavailable</option>
                            <option value="Under Maintenance">Under Maintenance</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="edit_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="edit_description" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label for="edit_roomImage" class="block text-sm font-medium text-gray-700 mb-1">Room Image</label>
                        <div class="flex items-center space-x-4">
                            <div class="flex-1">
                                <input type="file" id="edit_roomImage" name="roomImage" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                <p class="mt-1 text-xs text-gray-500">Leave empty to keep current image</p>
                            </div>
                            <div id="currentImagePreview" class="hidden">
                                <span class="text-xs text-gray-500 mr-2">Current:</span>
                                <img id="currentImage" src="" alt="Current room image" class="h-10 w-10 rounded object-cover">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 mt-6">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </button>
                    <button type="submit" name="update_room" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Update Room
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditModal(roomID) {
    // Fetch room data via AJAX
    fetch(`../get_room.php?id=${roomID}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_roomID').value = data.roomID;
            document.getElementById('edit_roomNumber').value = data.roomNumber;
            document.getElementById('edit_roomType').value = data.roomType;
            document.getElementById('edit_availableRooms').value = data.availableRooms;
            document.getElementById('edit_occupants').value = data.occupants;
            document.getElementById('edit_rentFee').value = data.rentFee;
            document.getElementById('edit_status').value = data.status;
            document.getElementById('edit_description').value = data.description;
            
            // Show current image if exists
            const imagePreview = document.getElementById('currentImagePreview');
            const currentImage = document.getElementById('currentImage');
            if (data.imagePath) {
                currentImage.src = '../' + data.imagePath;
                imagePreview.classList.remove('hidden');
            } else {
                imagePreview.classList.add('hidden');
            }
            
            // Also fetch active bookings count
            return fetch(`../check_room_bookings.php?id=${roomID}`);
        })
        .then(response => response.json())
        .then(bookingData => {
            // Store active bookings count in a data attribute
            document.getElementById('editForm').dataset.activeBookings = bookingData.active_bookings;
            
            document.getElementById('editModal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        })
        .catch(error => console.error('Error:', error));
}

// Add form submission handler to check before submitting
document.getElementById('editForm').addEventListener('submit', function(e) {
    const newStatus = document.getElementById('edit_status').value;
    const currentStatus = this.querySelector('[name="status"]').defaultValue;
    const activeBookings = parseInt(this.dataset.activeBookings) || 0;
    
    if ((newStatus === 'Unavailable' || newStatus === 'Under Maintenance') && 
        (currentStatus === 'Available') && activeBookings > 0) {
        e.preventDefault();
        alert('Cannot mark room as unavailable - there are active bookings for this room');
        document.getElementById('edit_status').value = currentStatus;
    }
});

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
</script>

<?php
// Include the footer component
require_once __DIR__ . '/../includes/footer.php';
?>