<?php
if (!isset($_SESSION)) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Student Dormitory Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .overflow-x-auto::-webkit-scrollbar {
            height: 8px;
        }
        .overflow-x-auto::-webkit-scrollbar-thumb {
            background-color: rgba(100, 116, 139, 0.5);
            border-radius: 4px;
        }
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
                    <li><a href="/adb/admin/rooms.php" class="block px-3 py-2 rounded-md bg-blue-100 text-blue-800 transition">Rooms</a></li>
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