<?php
require_once __DIR__ . '/../includes/auth.php';

if (!isAdmin()) {
    redirectWithMessage('../login.php', 'error', 'Unauthorized access');
}

require_once __DIR__ . '/../config/database.php';

// Handle booking approval/rejection/checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_booking'])) {
        $bookingID = (int)$_POST['bookingID'];
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Get booking details
            $stmt = $conn->prepare("SELECT roomID FROM bookings WHERE bookingID = ?");
            $stmt->bind_param("i", $bookingID);
            $stmt->execute();
            $booking = $stmt->get_result()->fetch_assoc();
            
            if (!$booking) {
                throw new Exception("Booking not found");
            }
            
            // Update booking status
            $stmt = $conn->prepare("UPDATE bookings SET status = 'Approved' WHERE bookingID = ?");
            $stmt->bind_param("i", $bookingID);
            $stmt->execute();
            
            // Decrease available rooms count
            $stmt = $conn->prepare("UPDATE rooms SET availableRooms = availableRooms - 1 WHERE roomID = ?");
            $stmt->bind_param("i", $booking['roomID']);
            $stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            redirectWithMessage('bookings.php', 'success', 'Booking approved successfully');
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            redirectWithMessage('bookings.php', 'error', 'Failed to approve booking: ' . $e->getMessage());
        }


    } elseif (isset($_POST['reject_booking'])) {
        $bookingID = (int)$_POST['bookingID'];
        
        $stmt = $conn->prepare("UPDATE bookings SET status = 'Rejected' WHERE bookingID = ?");
        $stmt->bind_param("i", $bookingID);
        
        if ($stmt->execute()) {
            redirectWithMessage('bookings.php', 'success', 'Booking rejected successfully');
        } else {
            redirectWithMessage('bookings.php', 'error', 'Failed to reject booking');
        }


        
    } elseif (isset($_POST['checkout_booking'])) {
        $bookingID = (int)$_POST['bookingID'];
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Get booking details
            $stmt = $conn->prepare("SELECT b.roomID, b.studentID FROM bookings b WHERE b.bookingID = ? AND b.status = 'Approved'");
            $stmt->bind_param("i", $bookingID);
            $stmt->execute();
            $booking = $stmt->get_result()->fetch_assoc();
            
            if (!$booking) {
                throw new Exception("Approved booking not found");
            }
            
            // Check for pending payments
            $stmt = $conn->prepare("SELECT COUNT(*) as pending_payments FROM payments 
                                   WHERE studentID = ? AND status = 'Pending'");
            $stmt->bind_param("i", $booking['studentID']);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if ($result['pending_payments'] > 0) {
                throw new Exception("Cannot check out student - there are pending payments");
            }
            
            // Update booking status to "Checked Out"
            $stmt = $conn->prepare("UPDATE bookings SET status = 'Checked Out', checkoutDate = NOW() WHERE bookingID = ?");
            $stmt->bind_param("i", $bookingID);
            $stmt->execute();
            
            // Increase available rooms count
            $stmt = $conn->prepare("UPDATE rooms SET availableRooms = availableRooms + 1 WHERE roomID = ?");
            $stmt->bind_param("i", $booking['roomID']);
            $stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            redirectWithMessage('bookings.php', 'success', 'Student checked out successfully. Room is now available.');
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            redirectWithMessage('bookings.php', 'error', 'Failed to process checkout: ' . $e->getMessage());
        }
    }
}

// Get pending bookings
$stmt = $conn->prepare("SELECT b.*, s.name as studentName, r.roomNumber, r.roomType 
                       FROM bookings b
                       JOIN students s ON b.studentID = s.studentID
                       JOIN rooms r ON b.roomID = r.roomID
                       WHERE b.status = 'Pending'
                       ORDER BY b.bookingDate");
$stmt->execute();
$pendingBookings = $stmt->get_result();

// Get all bookings
$stmt = $conn->prepare("SELECT b.*, s.name as studentName, r.roomNumber, r.roomType 
                       FROM bookings b
                       JOIN students s ON b.studentID = s.studentID
                       JOIN rooms r ON b.roomID = r.roomID
                       ORDER BY 
                           CASE WHEN b.status = 'Pending' THEN 1
                                WHEN b.status = 'Approved' THEN 2
                                ELSE 3 END,
                           b.bookingDate DESC");
$stmt->execute();
$allBookings = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Bookings - Student Dormitory Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Scrollbar for tables */
        .overflow-x-auto::-webkit-scrollbar {
            height: 8px;
        }
        .overflow-x-auto::-webkit-scrollbar-thumb {
            background-color: rgba(100, 116, 139, 0.5);
            border-radius: 4px;
        }
        /* Hide scrollbar for sidebar nav on desktop */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex">

<!-- Sidebar -->
<div id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
        <a href="<?php echo isAdmin() ? 'admin/dashboard.php' : 'student/dashboard.php'; ?>" class="text-2xl font-extrabold text-blue-700 hover:text-blue-800 flex items-center gap-2">
            <i class="fas fa-building fa-lg"></i>
            Dormitory System
        </a>
        <button id="closeSidebarBtn" class="md:hidden text-gray-600 hover:text-gray-900 focus:outline-none" aria-label="Close sidebar">
            <i class="fas fa-times fa-lg"></i>
        </button>
    </div>
    <nav class="flex-grow overflow-y-auto no-scrollbar px-4 py-6">
        <?php if (isLoggedIn()): ?>
            <div class="mb-6 px-2">
                <span class="block text-gray-700 font-semibold text-center">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            </div>
            <ul class="space-y-3 text-blue-700 font-semibold text-sm sm:text-base">
                <?php if (isAdmin()): ?>
                    <li><a href="/adb/admin/dashboard.php" class="block px-3 py-2 rounded-md hover:bg-blue-100 transition">Dashboard</a></li>
                    <li><a href="/adb/admin/bookings.php" class="block px-3 py-2 rounded-md bg-blue-100 text-blue-800 transition">Bookings</a></li>
                    <li><a href="/adb/admin/payments.php" class="block px-3 py-2 rounded-md hover:bg-blue-100 transition">Payments</a></li>
                    <li><a href="/adb/admin/rooms.php" class="block px-3 py-2 rounded-md hover:bg-blue-100 transition">Rooms</a></li>
                    <li><a href="/adb/admin/reports.php" class="block px-3 py-2 rounded-md hover:bg-blue-100 transition">Reports</a></li>
                <?php else: ?>
                    <li><a href="/adb/student/dashboard.php" class="block px-3 py-2 rounded-md hover:bg-blue-100 transition">Dashboard</a></li>
                    <li><a href="/adb/student/bookings.php" class="block px-3 py-2 rounded-md hover:bg-blue-100 transition">My Bookings</a></li>
                    <li><a href="/adb/student/payments.php" class="block px-3 py-2 rounded-md hover:bg-blue-100 transition">My Payments</a></li>
                    <li><a href="/adb/student/profile.php" class="block px-3 py-2 rounded-md hover:bg-blue-100 transition">Profile</a></li>
                <?php endif; ?>
            </ul>
        <?php else: ?>
            <div class="flex flex-col space-y-3">
                <a href="login.php" class="text-blue-600 hover:underline font-semibold text-center">Login</a>
                <a href="signup.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-semibold transition text-center">Sign Up</a>
            </div>
        <?php endif; ?>
    </nav>
    <?php if (isLoggedIn()): ?>
    <div class="px-6 py-4 border-t border-gray-200">
        <a href="../logout.php" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md transition w-full justify-center">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Overlay for mobile sidebar -->
<div id="overlay" class="fixed inset-0 bg-black bg-opacity-30 z-30 hidden md:hidden"></div>

<!-- Main content wrapper -->
<div class="flex flex-col flex-grow min-h-screen md:pl-64 w-full">

    <!-- Topbar for mobile -->
    <header class="bg-white shadow sticky top-0 z-20 md:hidden flex items-center justify-between px-4 py-3 border-b border-gray-200">
        <button id="openSidebarBtn" class="text-gray-700 hover:text-gray-900 focus:outline-none" aria-label="Open sidebar">
            <i class="fas fa-bars fa-lg"></i>
        </button>
        <a href="<?php echo isAdmin() ? 'admin/dashboard.php' : 'student/dashboard.php'; ?>" class="text-xl font-extrabold text-blue-700 hover:text-blue-800 flex items-center gap-2">
            <i class="fas fa-building fa-lg"></i>
            Dormitory System
        </a>
        <div class="w-8"></div> <!-- placeholder for spacing -->
    </header>

    <main class="flex-grow container mx-auto px-6 py-8">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $_SESSION['message_type'] === 'error' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?> shadow">
                <?php echo htmlspecialchars($_SESSION['message']); ?>
                <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
            </div>
        <?php endif; ?>

        <h1 class="text-3xl font-extrabold text-gray-900 mb-8 tracking-tight">Manage Bookings</h1>

        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Pending Bookings</h2>
            
            <?php if ($pendingBookings->num_rows > 0): ?>
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Booking ID</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Student</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Room</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <?php while ($booking = $pendingBookings->fetch_assoc()): ?>
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-gray-700"><?php echo htmlspecialchars($booking['bookingID']); ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-gray-700"><?php echo htmlspecialchars($booking['studentName']); ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-gray-600"><?php echo htmlspecialchars($booking['roomNumber'] . ' (' . $booking['roomType'] . ')'); ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-gray-600"><?php echo date('M d, Y', strtotime($booking['bookingDate'])); ?></td>
                                <td class="px-4 py-3 whitespace-nowrap space-x-2">
                                    <form action="bookings.php" method="POST" class="inline">
                                        <input type="hidden" name="bookingID" value="<?php echo $booking['bookingID']; ?>">
                                        <button type="submit" name="approve_booking" class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600">Approve</button>
                                    </form>
                                    <form action="bookings.php" method="POST" class="inline">
                                        <input type="hidden" name="bookingID" value="<?php echo $booking['bookingID']; ?>">
                                        <button type="submit" name="reject_booking" class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">Reject</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-500">No pending bookings.</p>
            <?php endif; ?>
        </div>
        
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">All Bookings</h2>
            
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Booking ID</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Student</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Room</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Booking Date</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Checkout Date</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <?php while ($booking = $allBookings->fetch_assoc()): ?>
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-700"><?php echo htmlspecialchars($booking['bookingID']); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-700"><?php echo htmlspecialchars($booking['studentName']); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600"><?php echo htmlspecialchars($booking['roomNumber'] . ' (' . $booking['roomType'] . ')'); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600"><?php echo date('M d, Y', strtotime($booking['bookingDate'])); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                <?php echo !empty($booking['checkoutDate']) ? date('M d, Y', strtotime($booking['checkoutDate'])) : 'N/A'; ?>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="<?php 
                                    echo $booking['status'] === 'Approved' ? 'bg-green-100 text-green-800' : 
                                    ($booking['status'] === 'Pending' ? 'bg-yellow-100 text-yellow-800' : 
                                    ($booking['status'] === 'Checked Out' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800')); 
                                ?> px-3 py-1 rounded-full text-xs font-semibold">
                                    <?php echo $booking['status']; ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <?php if ($booking['status'] === 'Approved'): ?>
                                    <form action="bookings.php" method="POST" class="inline">
                                        <input type="hidden" name="bookingID" value="<?php echo $booking['bookingID']; ?>">
                                        <button type="submit" name="checkout_booking" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600"
                                                onclick="return confirm('Are you sure you want to check out this student? This will make the room available again.')">
                                            Check Out
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-6 mt-12">
        <div class="container mx-auto px-6 text-center text-sm select-none">
            &copy; <?php echo date('Y'); ?> Student Dormitory Management System. All rights reserved.
        </div>
    </footer>
</div>

<script>
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const openBtn = document.getElementById('openSidebarBtn');
    const closeBtn = document.getElementById('closeSidebarBtn');

    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
    }

    openBtn.addEventListener('click', openSidebar);
    closeBtn.addEventListener('click', closeSidebar);
    overlay.addEventListener('click', closeSidebar);

    // Close sidebar on window resize if desktop
    window.addEventListener('resize', () => {
        if(window.innerWidth >= 768) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        } else {
            sidebar.classList.add('-translate-x-full');
        }
    });
</script>

</body>
</html>