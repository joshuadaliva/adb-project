<?php
require_once __DIR__ . '/../includes/auth.php';

if (!isStudent()) {
    redirectWithMessage('../login.php', 'error', 'Unauthorized access');
}

require_once __DIR__ . '/../config/database.php';

$studentID = $_SESSION['user_id'];




// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['make_payment'])) {
    $bookingID = (int)$_POST['bookingID'];
    $amount = (float)$_POST['amount'];
    $paymentDate = date('Y-m-d H:i:s');
    
    // Validate that booking belongs to student and is approved
    $stmt = $conn->prepare("SELECT bookingID FROM bookings WHERE bookingID = ? AND studentID = ? AND status = 'Approved'");
    $stmt->bind_param("ii", $bookingID, $studentID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        redirectWithMessage('payments.php', 'error', 'Invalid booking or booking not approved');
    }
    
    // Check if student already has a pending payment for this booking
    $stmt = $conn->prepare("SELECT paymentID FROM payments 
                           WHERE studentID = ? AND bookingID = ? AND status = 'Pending'");
    $stmt->bind_param("ii", $studentID, $bookingID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        redirectWithMessage('payments.php', 'error', 'You already have a pending payment for this booking');
    }
    
    // Create payment
    $stmt = $conn->prepare("INSERT INTO payments (studentID, bookingID, amount, paymentDate, status) 
                           VALUES (?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("iids", $studentID, $bookingID, $amount, $paymentDate);
    
    if ($stmt->execute()) {
        redirectWithMessage('payments.php', 'success', 'Payment submitted. Waiting for admin confirmation.');
    } else {
        redirectWithMessage('payments.php', 'error', 'Failed to submit payment');
    }
}










// Get student's approved booking
$stmt = $conn->prepare("SELECT b.bookingID, r.roomNumber, r.roomType, r.rentFee 
                       FROM bookings b
                       JOIN rooms r ON b.roomID = r.roomID
                       WHERE b.studentID = ? AND b.status = 'Approved'");
$stmt->bind_param("i", $studentID);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

// Check if there's already a pending payment for the booking
$hasPendingPayment = false;
if ($booking) {
    $stmt = $conn->prepare("SELECT paymentID FROM payments 
                           WHERE studentID = ? AND bookingID = ? AND status = 'Pending'");
    $stmt->bind_param("ii", $studentID, $booking['bookingID']);
    $stmt->execute();
    $hasPendingPayment = $stmt->get_result()->num_rows > 0;
}

// Get student's payments with additional details
$stmt = $conn->prepare("SELECT p.*, r.roomNumber, r.roomType
                       FROM payments p
                       LEFT JOIN bookings b ON p.bookingID = b.bookingID
                       LEFT JOIN rooms r ON b.roomID = r.roomID
                       WHERE p.studentID = ?
                       ORDER BY p.paymentDate DESC");
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
            <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
        </div>
    <?php endif; ?>

    <h1 class="text-3xl font-extrabold text-gray-900 mb-8 tracking-tight">My Payments</h1>

    <section aria-label="Payment sections" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <?php if ($booking): ?>
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-3">
                <i class="fas fa-money-bill-wave text-blue-600"></i> Make Payment
            </h2>
            
            <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                <h3 class="font-medium text-gray-800">Room <?php echo htmlspecialchars($booking['roomNumber']); ?> - <?php echo htmlspecialchars($booking['roomType']); ?></h3>
                <p class="text-gray-600">Monthly Rent: ₱<?php echo number_format($booking['rentFee'], 2); ?></p>
            </div>
            
            <?php if ($hasPendingPayment): ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-yellow-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                You already have a pending payment for this booking. Please wait for admin confirmation.
                            </p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <form action="payments.php" method="POST">
                    <input type="hidden" name="bookingID" value="<?php echo $booking['bookingID']; ?>">
                    
                    <div class="mb-6">
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Amount (₱):</label>
                        <input type="number" id="amount" name="amount" step="0.01" min="<?php echo $booking['rentFee']; ?>" 
                               value="<?php echo $booking['rentFee']; ?>" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition" required>
                    </div>
                    
                    <button type="submit" name="make_payment" 
                            class="w-full bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 transition font-medium">
                        Submit Payment
                    </button>
                </form>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-3">
                <i class="fas fa-money-bill-wave text-blue-600"></i> Make Payment
            </h2>
            
            <div class="text-center py-8">
                <i class="fas fa-door-closed text-gray-300 text-4xl mb-3"></i>
                <p class="text-gray-500 mb-4">You don't have an approved room booking to make payments for.</p>
                <a href="bookings.php" class="inline-block bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    Book a Room
                </a>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-3">
                <i class="fas fa-history text-purple-600"></i> Payment History
            </h2>
            
            <?php if ($payments->num_rows > 0): ?>
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Payment ID</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Room</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Amount</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($payment = $payments->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 whitespace-nowrap text-gray-700 font-medium"><?php echo htmlspecialchars($payment['paymentID']); ?></td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <?php if ($payment['roomNumber']): ?>
                                        <span class="font-medium"><?php echo htmlspecialchars($payment['roomNumber']); ?></span>
                                        <span class="block text-xs text-gray-500"><?php echo htmlspecialchars($payment['roomType']); ?></span>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-gray-600">₱<?php echo number_format($payment['amount'], 2); ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-gray-600"><?php echo date('M d, Y', strtotime($payment['paymentDate'])); ?></td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="<?php 
                                        echo $payment['status'] === 'Paid' ? 'bg-green-100 text-green-800' : 
                                        ($payment['status'] === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); 
                                    ?> px-3 py-1 rounded-full text-xs font-semibold">
                                        <?php echo $payment['status']; ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                    <?php if ($payment['status'] === 'Rejected' && !empty($payment['notes'])): ?>
                                        <div class="bg-red-50 p-2 rounded text-xs">
                                            <?php echo htmlspecialchars($payment['notes']); ?>
                                        </div>
                                    <?php elseif ($payment['status'] === 'Paid'): ?>
                                        <span class="text-green-600">✓ Approved</span>
                                    <?php else: ?>
                                        <span class="text-gray-400">Pending review</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-wallet text-gray-300 text-4xl mb-3"></i>
                    <p class="text-gray-500">No payment records found.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php 
// Include the footer component
require_once __DIR__ . '/../includes/footer.php'; 
?>