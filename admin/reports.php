<?php
require_once __DIR__ . '/../includes/auth.php';

if (!isAdmin()) {
    redirectWithMessage('../login.php', 'error', 'Unauthorized access');
}

require_once __DIR__ . '/../config/database.php';

// Check if download request
if (isset($_GET['download'])) {
    $type = $_GET['type'] ?? 'pdf';
    $report = $_GET['report'] ?? 'all';
    
    // Get data based on report type
    switch($report) {
        case 'payments':
            $data = getMonthlyPayments($conn);
            $filename = 'monthly_payments_report';
            $title = 'Monthly Payments Report';
            break;
        case 'unpaid':
            $data = getUnpaidStudents($conn);
            $filename = 'unpaid_students_report';
            $title = 'Unpaid Students Report';
            break;
        case 'occupancy':
            $data = getRoomOccupancy($conn);
            $filename = 'room_occupancy_report';
            $title = 'Room Occupancy Report';
            break;
        default:
            // All reports
            $data = [
                'payments' => getMonthlyPayments($conn),
                'unpaid' => getUnpaidStudents($conn),
                'occupancy' => getRoomOccupancy($conn)
            ];
            $filename = 'dormitory_full_report';
            $title = 'Dormitory Full Report';
    }
    
    if ($type === 'excel') {
        downloadExcel($data, $filename, $title, $report);
    } else {
        downloadPDF($data, $filename, $title, $report);
    }
    exit;
}

// Data fetching functions
function getMonthlyPayments($conn) {
    $stmt = $conn->prepare("SELECT YEAR(paymentDate) as year, MONTH(paymentDate) as month, SUM(amount) as total 
                           FROM payments 
                           WHERE status = 'Paid'
                           GROUP BY YEAR(paymentDate), MONTH(paymentDate)
                           ORDER BY year DESC, month DESC");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getUnpaidStudents($conn) {
    $stmt = $conn->prepare("SELECT s.studentID, s.name, s.email, s.contact, COUNT(p.paymentID) as unpaid_count
                           FROM students s
                           JOIN payments p ON s.studentID = p.studentID
                           WHERE p.status = 'Pending'
                           GROUP BY s.studentID
                           ORDER BY unpaid_count DESC");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getRoomOccupancy($conn) {
    $stmt = $conn->prepare("SELECT r.roomNumber, r.roomType, COUNT(b.bookingID) as occupied, r.occupants as capacity
                           FROM rooms r
                           LEFT JOIN bookings b ON r.roomID = b.roomID AND b.status = 'Approved'
                           GROUP BY r.roomID
                           ORDER BY r.roomNumber");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Download functions
function downloadPDF($data, $filename, $title, $report) {
    require_once __DIR__ . '/../lib/tcpdf.php';
    
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator('Dormitory System');
    $pdf->SetAuthor('Admin');
    $pdf->SetTitle($title);
    $pdf->SetSubject('Dormitory Report');
    
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, $title, 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 10);
    
    if ($report === 'all' || $report === 'payments') {
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Monthly Payment Totals', 0, 1);
        $pdf->SetFont('helvetica', '', 10);
        
        $html = '<table border="1" cellpadding="4">
            <tr>
                <th width="60%"><b>Month</b></th>
                <th width="40%"><b>Total Payments</b></th>
            </tr>';
        
        foreach ($data['payments'] ?? $data as $payment) {
            $html .= '<tr>
                <td>'.date('F Y', mktime(0, 0, 0, $payment['month'], 1, $payment['year'])).'</td>
                <td>₱'.number_format($payment['total'], 2).'</td>
            </tr>';
        }
        $html .= '</table>';
        $pdf->writeHTML($html, true, false, false, false, '');
    }
    
    if ($report === 'all' || $report === 'unpaid') {
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Students with Unpaid Payments', 0, 1);
        $pdf->SetFont('helvetica', '', 10);
        
        $html = '<table border="1" cellpadding="4">
            <tr>
                <th width="40%"><b>Student</b></th>
                <th width="30%"><b>Contact</b></th>
                <th width="30%"><b>Unpaid Count</b></th>
            </tr>';
        
        foreach ($data['unpaid'] ?? $data as $student) {
            $html .= '<tr>
                <td>'.htmlspecialchars($student['name']).'<br><small>'.htmlspecialchars($student['email']).'</small></td>
                <td>'.htmlspecialchars($student['contact']).'</td>
                <td>'.$student['unpaid_count'].'</td>
            </tr>';
        }
        $html .= '</table>';
        $pdf->writeHTML($html, true, false, false, false, '');
    }
    
    if ($report === 'all' || $report === 'occupancy') {
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Room Occupancy', 0, 1);
        $pdf->SetFont('helvetica', '', 10);
        
        $html = '<table border="1" cellpadding="4">
            <tr>
                <th width="20%"><b>Room</b></th>
                <th width="20%"><b>Type</b></th>
                <th width="15%"><b>Occupied</b></th>
                <th width="15%"><b>Capacity</b></th>
                <th width="30%"><b>Occupancy Rate</b></th>
            </tr>';
        
        foreach ($data['occupancy'] ?? $data as $room) {
            $occupancyRate = $room['capacity'] > 0 ? ($room['occupied'] / $room['capacity']) * 100 : 0;
            $html .= '<tr>
                <td>'.$room['roomNumber'].'</td>
                <td>'.$room['roomType'].'</td>
                <td>'.$room['occupied'].'</td>
                <td>'.$room['capacity'].'</td>
                <td>'.number_format($occupancyRate, 1).'%</td>
            </tr>';
        }
        $html .= '</table>';
        $pdf->writeHTML($html, true, false, false, false, '');
    }
    
    $pdf->Output($filename.'.pdf', 'D');
}

function downloadExcel($data, $filename, $title, $report) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
    header('Cache-Control: max-age=0');
    
    echo '<html><body>';
    echo '<h1>'.$title.'</h1>';
    echo '<p>Generated on: '.date('Y-m-d H:i:s').'</p>';
    
    if ($report === 'all' || $report === 'payments') {
        echo '<h2>Monthly Payment Totals</h2>';
        echo '<table border="1">
            <tr>
                <th>Month</th>
                <th>Total Payments</th>
            </tr>';
        
        foreach ($data['payments'] ?? $data as $payment) {
            echo '<tr>
                <td>'.date('F Y', mktime(0, 0, 0, $payment['month'], 1, $payment['year'])).'</td>
                <td>₱'.number_format($payment['total'], 2).'</td>
            </tr>';
        }
        echo '</table><br>';
    }
    
    if ($report === 'all' || $report === 'unpaid') {
        echo '<h2>Students with Unpaid Payments</h2>';
        echo '<table border="1">
            <tr>
                <th>Student</th>
                <th>Email</th>
                <th>Contact</th>
                <th>Unpaid Count</th>
            </tr>';
        
        foreach ($data['unpaid'] ?? $data as $student) {
            echo '<tr>
                <td>'.htmlspecialchars($student['name']).'</td>
                <td>'.htmlspecialchars($student['email']).'</td>
                <td>'.htmlspecialchars($student['contact']).'</td>
                <td>'.$student['unpaid_count'].'</td>
            </tr>';
        }
        echo '</table><br>';
    }
    
    if ($report === 'all' || $report === 'occupancy') {
        echo '<h2>Room Occupancy</h2>';
        echo '<table border="1">
            <tr>
                <th>Room</th>
                <th>Type</th>
                <th>Occupied</th>
                <th>Capacity</th>
                <th>Occupancy Rate</th>
            </tr>';
        
        foreach ($data['occupancy'] ?? $data as $room) {
            $occupancyRate = $room['capacity'] > 0 ? ($room['occupied'] / $room['capacity']) * 100 : 0;
            echo '<tr>
                <td>'.$room['roomNumber'].'</td>
                <td>'.$room['roomType'].'</td>
                <td>'.$room['occupied'].'</td>
                <td>'.$room['capacity'].'</td>
                <td>'.number_format($occupancyRate, 1).'%</td>
            </tr>';
        }
        echo '</table>';
    }
    
    echo '</body></html>';
    exit;
}

// Get data for display
$monthlyPayments = getMonthlyPayments($conn);
$unpaidStudents = getUnpaidStudents($conn);
$roomOccupancy = getRoomOccupancy($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Reports - Student Dormitory Management System</title>
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
                    <li><a href="/adb/admin/bookings.php" class="block px-3 py-2 rounded-md hover:bg-blue-100 transition">Bookings</a></li>
                    <li><a href="/adb/admin/payments.php" class="block px-3 py-2 rounded-md hover:bg-blue-100 transition">Payments</a></li>
                    <li><a href="/adb/admin/rooms.php" class="block px-3 py-2 rounded-md hover:bg-blue-100 transition">Rooms</a></li>
                    <li><a href="/adb/admin/reports.php" class="block px-3 py-2 rounded-md bg-blue-100 text-blue-800 transition">Reports</a></li>
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

        <h1 class="text-3xl font-extrabold text-gray-900 mb-8 tracking-tight">Reports</h1>
        
        <!-- Download buttons -->
        <div class="flex flex-wrap gap-3 mb-6">
            <div class="dropdown relative">
                <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 flex items-center">
                    Download Full Report
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="dropdown-menu absolute hidden bg-white shadow-lg rounded mt-1 py-1 z-10">
                    <a href="?download=1&type=pdf&report=all" class="block px-4 py-2 hover:bg-gray-100">PDF Format</a>
                    <a href="?download=1&type=excel&report=all" class="block px-4 py-2 hover:bg-gray-100">Excel Format</a>
                </div>
            </div>
            
            <div class="dropdown relative">
                <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex items-center">
                    Download Payments
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="dropdown-menu absolute hidden bg-white shadow-lg rounded mt-1 py-1 z-10">
                    <a href="?download=1&type=pdf&report=payments" class="block px-4 py-2 hover:bg-gray-100">PDF Format</a>
                    <a href="?download=1&type=excel&report=payments" class="block px-4 py-2 hover:bg-gray-100">Excel Format</a>
                </div>
            </div>
            
            <div class="dropdown relative">
                <button class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700 flex items-center">
                    Download Unpaid Students
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="dropdown-menu absolute hidden bg-white shadow-lg rounded mt-1 py-1 z-10">
                    <a href="?download=1&type=pdf&report=unpaid" class="block px-4 py-2 hover:bg-gray-100">PDF Format</a>
                    <a href="?download=1&type=excel&report=unpaid" class="block px-4 py-2 hover:bg-gray-100">Excel Format</a>
                </div>
            </div>
            
            <div class="dropdown relative">
                <button class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 flex items-center">
                    Download Occupancy
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div class="dropdown-menu absolute hidden bg-white shadow-lg rounded mt-1 py-1 z-10">
                    <a href="?download=1&type=pdf&report=occupancy" class="block px-4 py-2 hover:bg-gray-100">PDF Format</a>
                    <a href="?download=1&type=excel&report=occupancy" class="block px-4 py-2 hover:bg-gray-100">Excel Format</a>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Monthly Payment Totals</h2>
                    <div class="flex space-x-2">
                        <a href="?download=1&type=pdf&report=payments" class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm hover:bg-blue-200">PDF</a>
                        <a href="?download=1&type=excel&report=payments" class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm hover:bg-green-200">Excel</a>
                    </div>
                </div>
                
                <?php if (!empty($monthlyPayments)): ?>
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Month</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Payments</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <?php foreach ($monthlyPayments as $payment): ?>
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-700"><?php echo date('F Y', mktime(0, 0, 0, $payment['month'], 1, $payment['year'])); ?></td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-700">₱<?php echo number_format($payment['total'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">No payment records found.</p>
                <?php endif; ?>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Students with Unpaid Payments</h2>
                    <div class="flex space-x-2">
                        <a href="?download=1&type=pdf&report=unpaid" class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm hover:bg-blue-200">PDF</a>
                        <a href="?download=1&type=excel&report=unpaid" class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm hover:bg-green-200">Excel</a>
                    </div>
                </div>
                
                <?php if (!empty($unpaidStudents)): ?>
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Student</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contact</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Unpaid Count</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <?php foreach ($unpaidStudents as $student): ?>
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-700"><?php echo htmlspecialchars($student['name']); ?><br><small class="text-gray-500"><?php echo htmlspecialchars($student['email']); ?></small></td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-600"><?php echo htmlspecialchars($student['contact']); ?></td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-600"><?php echo htmlspecialchars($student['unpaid_count']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">No students with unpaid payments.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Room Occupancy</h2>
                <div class="flex space-x-2">
                    <a href="?download=1&type=pdf&report=occupancy" class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm hover:bg-blue-200">PDF</a>
                    <a href="?download=1&type=excel&report=occupancy" class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm hover:bg-green-200">Excel</a>
                </div>
            </div>
            
            <?php if (!empty($roomOccupancy)): ?>
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Room</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Occupied</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Capacity</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Occupancy Rate</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <?php foreach ($roomOccupancy as $room): 
                                $occupancyRate = $room['capacity'] > 0 ? ($room['occupied'] / $room['capacity']) * 100 : 0;
                                ?>
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-700"><?php echo htmlspecialchars($room['roomNumber']); ?></td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-600"><?php echo htmlspecialchars($room['roomType']); ?></td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-600"><?php echo htmlspecialchars($room['occupied']); ?></td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-600"><?php echo htmlspecialchars($room['capacity']); ?></td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?php echo $occupancyRate; ?>%"></div>
                                        </div>
                                        <span><?php echo number_format($occupancyRate, 1); ?>%</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-500">No room data available.</p>
            <?php endif; ?>
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
    // Sidebar functionality
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

    // Dropdown functionality
    document.addEventListener('DOMContentLoaded', function() {
        const dropdowns = document.querySelectorAll('.dropdown');
        
        dropdowns.forEach(dropdown => {
            const button = dropdown.querySelector('button');
            const menu = dropdown.querySelector('.dropdown-menu');
            
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                // Close all other dropdowns
                document.querySelectorAll('.dropdown-menu').forEach(m => {
                    if (m !== menu) m.classList.add('hidden');
                });
                menu.classList.toggle('hidden');
            });
        });
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function() {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.add('hidden');
            });
        });
    });
</script>

</body>
</html>