<?php
require_once __DIR__ . '/../includes/auth.php';

if (!isStudent()) {
    redirectWithMessage('/../login.php', 'error', 'Unauthorized access');
}

require_once __DIR__ . '/../config/database.php';

$studentID = $_SESSION['user_id'];








// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_room'])) {
    $roomID = (int)$_POST['roomID'];
    


    // Check if student already has an approved booking
    $stmt = $conn->prepare("SELECT bookingID FROM bookings WHERE studentID = ? AND status IN ('Pending', 'Approved')");
    $stmt->bind_param("i", $studentID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        redirectWithMessage('bookings.php', 'error', 'You already have an approved/pending room booking');
    }
    



    // Check if room is available
    $stmt = $conn->prepare("SELECT availableRooms FROM rooms WHERE roomID = ? AND status = 'Available'");
    $stmt->bind_param("i", $roomID);
    $stmt->execute();
    $room = $stmt->get_result()->fetch_assoc();
    
    if (!$room || $room['availableRooms'] <= 0) {
        redirectWithMessage('bookings.php', 'error', 'Selected room is not available');
    }
    




    // Create booking
    $stmt = $conn->prepare("INSERT INTO bookings (studentID, roomID, bookingDate, status) 
                           VALUES (?, ?, NOW(), 'Pending')");
    $stmt->bind_param("ii", $studentID, $roomID);
    
    if ($stmt->execute()) {
        redirectWithMessage('bookings.php', 'success', 'Room booking request submitted. Waiting for admin approval.');
    } else {
        redirectWithMessage('bookings.php', 'error', 'Failed to book room');
    }
}







// Get available rooms
$stmt = $conn->prepare("SELECT * FROM rooms WHERE status = 'Available' AND availableRooms > 0 ORDER BY roomNumber");
$stmt->execute();
$availableRooms = $stmt->get_result();

// Get student's bookings
$stmt = $conn->prepare("SELECT b.*, r.roomNumber, r.roomType, r.rentFee 
                       FROM bookings b
                       JOIN rooms r ON b.roomID = r.roomID
                       WHERE b.studentID = ?
                       ORDER BY b.bookingDate DESC");
$stmt->bind_param("i", $studentID);
$stmt->execute();
$bookings = $stmt->get_result();

// Include the header that contains the sidebar and topbar
require_once __DIR__ . '/../includes/header.php';
?>

<main class="flex-grow container mx-auto px-6 py-8">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $_SESSION['message_type'] === 'error' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?> shadow">
            <?php echo htmlspecialchars($_SESSION['message']); ?>
            <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
        </div>
    <?php endif; ?>

    <h1 class="text-3xl font-extrabold text-gray-900 mb-8 tracking-tight">Room Bookings</h1>

    <section aria-label="Booking sections" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-3">
                <i class="fas fa-door-open text-blue-600"></i> Available Rooms
            </h2>
            
            <?php if ($availableRooms->num_rows > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php while ($room = $availableRooms->fetch_assoc()): ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                        <?php if (!empty($room['imagePath'])): ?>
                            <img src="../<?php echo htmlspecialchars($room['imagePath']); ?>" alt="Room <?php echo htmlspecialchars($room['roomNumber']); ?>" class="w-full h-40 object-cover rounded-lg mb-3">
                        <?php endif; ?>
                        
                        <h3 class="font-semibold text-gray-800">Room <?php echo htmlspecialchars($room['roomNumber']); ?> - <?php echo htmlspecialchars($room['roomType']); ?></h3>
                        <p class="text-gray-600 text-sm mb-2"><?php echo htmlspecialchars($room['description']); ?></p>
                        
                        <div class="flex justify-between items-center mt-3">
                            <div>
                                <span class="font-medium text-gray-900">₱<?php echo number_format($room['rentFee'], 2); ?></span> 
                                <span class="text-sm text-gray-500">/ month</span>
                                <p class="text-sm text-gray-500">Available: <?php echo htmlspecialchars($room['availableRooms']); ?></p>
                            </div>
                            
                            <form action="bookings.php" method="POST">
                                <input type="hidden" name="roomID" value="<?php echo $room['roomID']; ?>">
                                <button type="submit" name="book_room" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">
                                    Book Now
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-door-closed text-gray-300 text-4xl mb-3"></i>
                    <p class="text-gray-500">No available rooms at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-3">
                <i class="fas fa-calendar-check text-green-600"></i> My Bookings
            </h2>
            
            <?php if ($bookings->num_rows > 0): ?>
                <div class="space-y-4">
                    <?php while ($booking = $bookings->fetch_assoc()): ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-semibold text-gray-800">Room <?php echo htmlspecialchars($booking['roomNumber']); ?> - <?php echo htmlspecialchars($booking['roomType']); ?></h3>
                                <p class="text-sm text-gray-500">Booked on: <?php echo date('M d, Y', strtotime($booking['bookingDate'])); ?></p>
                            </div>
                            
                            <span class="<?php echo $booking['status'] === 'Approved' ? 'bg-green-100 text-green-800' : ($booking['status'] === 'Pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?> px-3 py-1 rounded-full text-xs font-semibold">
                                <?php echo $booking['status']; ?>
                            </span>
                        </div>
                        
                        <div class="mt-4">
                            <p class="font-medium text-gray-900">Monthly Rent: ₱<?php echo number_format($booking['rentFee'], 2); ?></p>
                            
                            <?php if ($booking['status'] === 'Approved'): ?>
                                <div class="mt-3">
                                    <a href="payments.php" class="inline-block bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition">
                                        Make Payment
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-calendar-times text-gray-300 text-4xl mb-3"></i>
                    <p class="text-gray-500">You have no room bookings yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php 
// Include the footer component
require_once __DIR__ . '/../includes/footer.php'; 
?>