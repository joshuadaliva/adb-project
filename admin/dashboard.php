<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';



if (!isAdmin()) {
    redirectWithMessage('../login.php', 'error', 'Unauthorized access');
}

// Get counts for dashboard
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM students");
$stmt->execute();
$students = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM rooms");
$stmt->execute();
$rooms = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM bookings WHERE status = 'Pending'");
$stmt->execute();
$pending = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM payments WHERE status = 'Pending'");
$stmt->execute();
$payments = $stmt->get_result()->fetch_assoc();




// Get recent bookings
$stmt = $conn->prepare("SELECT b.bookingID, s.name as studentName, r.roomNumber, b.bookingDate, b.status 
                      FROM bookings b
                      JOIN students s ON b.studentID = s.studentID
                      JOIN rooms r ON b.roomID = r.roomID
                      ORDER BY b.bookingDate DESC LIMIT 5");
$stmt->execute();
$recentBookings = $stmt->get_result();



// Get recent payments
$stmt = $conn->prepare("SELECT p.paymentID, s.name as studentName, p.amount, p.paymentDate, p.status 
                      FROM payments p
                      JOIN students s ON p.studentID = s.studentID
                      ORDER BY p.paymentDate DESC LIMIT 5");
$stmt->execute();
$recentPayments = $stmt->get_result();











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

    <h1 class="text-3xl font-extrabold text-gray-900 mb-8 tracking-tight">Admin Dashboard</h1>

    <section aria-label="Summary cards" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <div class="bg-white rounded-xl shadow-md p-6 flex items-center space-x-4 hover:shadow-lg transition">
            <div class="p-4 bg-blue-100 text-blue-700 rounded-lg">
                <i class="fas fa-user-graduate fa-2x"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-700">Total Students</h3>
                <p class="text-3xl font-extrabold text-blue-600"><?php echo $students['total']; ?></p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 flex items-center space-x-4 hover:shadow-lg transition">
            <div class="p-4 bg-green-100 text-green-700 rounded-lg">
                <i class="fas fa-door-open fa-2x"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-700">Total Rooms</h3>
                <p class="text-3xl font-extrabold text-green-600"><?php echo $rooms['total']; ?></p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 flex items-center space-x-4 hover:shadow-lg transition">
            <div class="p-4 bg-yellow-100 text-yellow-700 rounded-lg">
                <i class="fas fa-calendar-check fa-2x"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-700">Pending Bookings</h3>
                <p class="text-3xl font-extrabold text-yellow-600"><?php echo $pending['total']; ?></p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 flex items-center space-x-4 hover:shadow-lg transition">
            <div class="p-4 bg-red-100 text-red-700 rounded-lg">
                <i class="fas fa-credit-card fa-2x"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-700">Pending Payments</h3>
                <p class="text-3xl font-extrabold text-red-600"><?php echo $payments['total']; ?></p>
            </div>
        </div>
    </section>

    <section aria-label="Recent activity" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-3">
                <i class="fas fa-calendar-alt text-blue-600"></i> Recent Bookings
            </h2>
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Student</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Room</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <?php while ($booking = $recentBookings->fetch_assoc()): ?>
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-700 font-medium"><?php echo htmlspecialchars($booking['studentName']); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600"><?php echo htmlspecialchars($booking['roomNumber']); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600"><?php echo date('M d, Y', strtotime($booking['bookingDate'])); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <?php
                                    $status = $booking['status'];
                                    $statusClasses = 'inline-block px-3 py-1 rounded-full text-xs font-semibold ';
                                    if ($status === 'Approved') {
                                        $statusClasses .= 'bg-green-100 text-green-800';
                                    } elseif ($status === 'Pending') {
                                        $statusClasses .= 'bg-yellow-100 text-yellow-800';
                                    } else {
                                        $statusClasses .= 'bg-red-100 text-red-800';
                                    }
                                ?>
                                <span class="<?php echo $statusClasses; ?>"><?php echo $status; ?></span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-3">
                <i class="fas fa-dollar-sign text-green-600"></i> Recent Payments
            </h2>
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Student</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Amount</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <?php while ($payment = $recentPayments->fetch_assoc()): ?>
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-700 font-medium"><?php echo htmlspecialchars($payment['studentName']); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">₱<?php echo number_format($payment['amount'], 2); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600"><?php echo date('M d, Y', strtotime($payment['paymentDate'])); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <?php
                                    $pstatus = $payment['status'];
                                    $pstatusClasses = 'inline-block px-3 py-1 rounded-full text-xs font-semibold ';
                                    if ($pstatus === 'Paid') {
                                        $pstatusClasses .= 'bg-green-100 text-green-800';
                                    } else {
                                        $pstatusClasses .= 'bg-yellow-100 text-yellow-800';
                                    }
                                ?>
                                <span class="<?php echo $pstatusClasses; ?>"><?php echo $pstatus; ?></span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</main>

<?php 
// Include the footer component
require_once __DIR__ . '/../includes/footer.php'; 
?>