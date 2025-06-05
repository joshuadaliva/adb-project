<?php
require_once __DIR__ . '/../includes/auth.php';

if (!isStudent()) {
    redirectWithMessage('../login.php', 'error', 'Unauthorized access');
}
require_once __DIR__ . '/../config/database.php';





$studentID = $_SESSION['user_id'];
// Get student info
$stmt = $conn->prepare("SELECT * FROM students WHERE studentID = ?");
$stmt->bind_param("i", $studentID);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();



// Get current booking if exists
$stmt = $conn->prepare("SELECT b.*, r.roomNumber, r.roomType, r.rentFee 
                       FROM bookings b
                       JOIN rooms r ON b.roomID = r.roomID
                       WHERE b.studentID = ? AND b.status = 'Approved'");
$stmt->bind_param("i", $studentID);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();





// Get recent payments
$stmt = $conn->prepare("SELECT * FROM payments 
                       WHERE studentID = ? 
                       ORDER BY paymentDate DESC LIMIT 3");
$stmt->bind_param("i", $studentID);
$stmt->execute();
$payments = $stmt->get_result();





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

    <h1 class="text-3xl font-extrabold text-gray-900 mb-8 tracking-tight">Student Dashboard</h1>

    <section aria-label="Student information" class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
        <div class="bg-white rounded-xl shadow-md p-6 lg:col-span-2">
            <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-3">
                <i class="fas fa-user-circle text-blue-600"></i> My Information
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1">
                    <p class="text-gray-600 text-sm">Name:</p>
                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($student['name']); ?></p>
                </div>
                <div class="space-y-1">
                    <p class="text-gray-600 text-sm">Email:</p>
                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($student['email']); ?></p>
                </div>
                <div class="space-y-1">
                    <p class="text-gray-600 text-sm">Program:</p>
                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($student['program']); ?></p>
                </div>
                <div class="space-y-1">
                    <p class="text-gray-600 text-sm">Contact:</p>
                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($student['contact']); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-3">
                <i class="fas fa-bed text-green-600"></i> My Room
            </h2>

            <?php if ($booking): ?>
                <div class="space-y-4">
                    <div class="space-y-1">
                        <p class="text-gray-600 text-sm">Room Number:</p>
                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($booking['roomNumber']); ?></p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-gray-600 text-sm">Room Type:</p>
                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($booking['roomType']); ?></p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-gray-600 text-sm">Monthly Rent:</p>
                        <p class="font-medium text-gray-900">₱<?php echo number_format($booking['rentFee'], 2); ?></p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-gray-600 text-sm">Booking Date:</p>
                        <p class="font-medium text-gray-900"><?php echo date('M d, Y', strtotime($booking['bookingDate'])); ?></p>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-door-closed text-gray-300 text-4xl mb-3"></i>
                    <p class="text-gray-500 mb-4">You don't have an approved room booking yet.</p>
                    <a href="bookings.php" class="inline-block bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        Book a Room
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section aria-label="Recent payments" class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-3">
            <i class="fas fa-receipt text-purple-600"></i> Recent Payments
        </h2>

        <?php if ($payments->num_rows > 0): ?>
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Payment ID</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Amount</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($payment = $payments->fetch_assoc()): ?>
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-gray-700 font-medium"><?php echo htmlspecialchars($payment['paymentID']); ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-gray-600">₱<?php echo number_format($payment['amount'], 2); ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-gray-600"><?php echo date('M d, Y', strtotime($payment['paymentDate'])); ?></td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="<?php echo $payment['status'] === 'Paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?> px-3 py-1 rounded-full text-xs font-semibold">
                                        <?php echo $payment['status']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                <a href="payments.php" class="text-blue-600 hover:text-blue-800 font-medium transition">
                    View all payments <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        <?php else: ?>
            <div class="text-center py-8">
                <i class="fas fa-wallet text-gray-300 text-4xl mb-3"></i>
                <p class="text-gray-500">No payment records found.</p>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php
// Include the footer component
require_once __DIR__ . '/../includes/footer.php';
?>