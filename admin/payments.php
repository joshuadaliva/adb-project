<?php
require_once __DIR__ . '/../includes/auth.php';

if (!isAdmin()) {
    redirectWithMessage('../login.php', 'error', 'Unauthorized access');
}

require_once __DIR__ . '/../config/database.php';







// Handle payment approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_payment'])) {
        $paymentID = (int)$_POST['paymentID'];
        
        $stmt = $conn->prepare("UPDATE payments SET status = 'Paid' WHERE paymentID = ?");
        $stmt->bind_param("i", $paymentID);
        
        if ($stmt->execute()) {
            redirectWithMessage('payments.php', 'success', 'Payment approved successfully');
        } else {
            redirectWithMessage('payments.php', 'error', 'Failed to approve payment');
        }




    } elseif (isset($_POST['reject_payment'])) {
        $paymentID = (int)$_POST['paymentID'];
        $reason = sanitizeInput($_POST['reject_reason'] ?? 'No reason provided');
        
        // Update payment status and store rejection reason
        $stmt = $conn->prepare("UPDATE payments SET status = 'Rejected', notes = ? WHERE paymentID = ?");
        $stmt->bind_param("si", $reason, $paymentID);
        
        if ($stmt->execute()) {
            redirectWithMessage('payments.php', 'success', 'Payment rejected successfully');
        } else {
            redirectWithMessage('payments.php', 'error', 'Failed to reject payment');
        }
    }
}









// Get pending payments with additional details
$stmt = $conn->prepare("SELECT p.*, s.name as studentName, s.email as studentEmail, r.roomNumber
                       FROM payments p
                       JOIN students s ON p.studentID = s.studentID
                       LEFT JOIN bookings b ON p.bookingID = b.bookingID
                       LEFT JOIN rooms r ON b.roomID = r.roomID
                       WHERE p.status = 'Pending'
                       ORDER BY p.paymentDate");
$stmt->execute();
$pendingPayments = $stmt->get_result();

// Get all payments with rejection reasons if available
$stmt = $conn->prepare("SELECT p.*, s.name as studentName 
                       FROM payments p
                       JOIN students s ON p.studentID = s.studentID
                       ORDER BY p.paymentDate DESC");
$stmt->execute();
$allPayments = $stmt->get_result();

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

    <h1 class="text-3xl font-extrabold text-gray-900 mb-8 tracking-tight">Manage Payments</h1>
    
    <div class="bg-white rounded-xl shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Pending Payments</h2>
        
        <?php if ($pendingPayments->num_rows > 0): ?>
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Payment ID</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Student</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Amount</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Room</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <?php while ($payment = $pendingPayments->fetch_assoc()): ?>
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-700"><?php echo htmlspecialchars($payment['paymentID']); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-700">
                                <?php echo htmlspecialchars($payment['studentName']); ?>
                                <br><small class="text-gray-500"><?php echo htmlspecialchars($payment['studentEmail']); ?></small>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-700">₱<?php echo number_format($payment['amount'], 2); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600"><?php echo $payment['roomNumber'] ? 'Room ' . htmlspecialchars($payment['roomNumber']) : 'N/A'; ?></td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600"><?php echo date('M d, Y', strtotime($payment['paymentDate'])); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap space-x-2">
                                <form action="payments.php" method="POST" class="inline">
                                    <input type="hidden" name="paymentID" value="<?php echo $payment['paymentID']; ?>">
                                    <button type="submit" name="approve_payment" class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600">Approve</button>
                                </form>
                                
                                <button onclick="openRejectModal(<?php echo $payment['paymentID']; ?>)" 
                                        class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">
                                    Reject
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-500">No pending payments.</p>
        <?php endif; ?>
    </div>
    
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4">All Payments</h2>
        
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Payment ID</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Student</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Amount</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Notes</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php while ($payment = $allPayments->fetch_assoc()): ?>
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap text-gray-700"><?php echo htmlspecialchars($payment['paymentID']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-gray-700"><?php echo htmlspecialchars($payment['studentName']); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-gray-700">₱<?php echo number_format($payment['amount'], 2); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-gray-600"><?php echo date('M d, Y', strtotime($payment['paymentDate'])); ?></td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="<?php 
                                echo $payment['status'] === 'Paid' ? 'bg-green-100 text-green-800' : 
                                ($payment['status'] === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); 
                            ?> px-3 py-1 rounded-full text-xs font-semibold">
                                <?php echo $payment['status']; ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600"><?php echo htmlspecialchars($payment['notes'] ?? ''); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Reject Payment Modal -->
<div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
        <h3 class="text-xl font-semibold mb-4">Reject Payment</h3>
        
        <form id="rejectForm" action="payments.php" method="POST">
            <input type="hidden" id="reject_paymentID" name="paymentID">
            
            <div class="mb-4">
                <label for="reject_reason" class="block text-gray-700 mb-2">Reason for Rejection:</label>
                <textarea id="reject_reason" name="reject_reason" class="w-full px-3 py-2 border rounded" required></textarea>
                <p class="text-sm text-gray-500 mt-1">This will be visible to the student.</p>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeRejectModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Cancel</button>
                <button type="submit" name="reject_payment" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Confirm Rejection</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRejectModal(paymentID) {
    document.getElementById('reject_paymentID').value = paymentID;
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('reject_reason').value = '';
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('rejectModal');
    if (event.target === modal) {
        closeRejectModal();
    }
});
</script>

<?php 
// Include the footer component
require_once __DIR__ . '/../includes/footer.php'; 
?>