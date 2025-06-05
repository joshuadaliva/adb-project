<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

if (isAdmin()) {
    redirectWithMessage('/admin/dashboard.php', 'error', 'Unauthorized access');
}
if (isStudent()) {
    redirectWithMessage('/student/dashboard.php', 'error', 'Already logged in');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        redirectWithMessage('login.php', 'error', 'Email and password are required');
    }
    
    $stmt = $conn->prepare("SELECT studentID, name, password FROM students WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['studentID'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = 'student';
            $_SESSION['user_email'] = $email;
            
            redirectWithMessage('/adb/student/dashboard.php', 'success', 'Login successful');
        } else {
            redirectWithMessage('login.php', 'error', 'Invalid email or password');
        }
    } else {
        redirectWithMessage('login.php', 'error', 'Invalid email or password');
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login | Student Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        }
        .input-focus:focus {
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3);
        }
    </style>
</head>
<body class="h-full">
    <div class="min-h-full flex">
        <!-- Left side with gradient background (hidden on mobile) -->
        <div class="hidden lg:block relative w-0 flex-1 gradient-bg">
            <div class="absolute inset-0 flex items-center justify-center p-12">
                <div class="text-white max-w-md">
                    <h1 class="text-4xl font-bold mb-6 animate__animated animate__fadeIn">Student Portal</h1>
                    <p class="text-xl opacity-90 animate__animated animate__fadeIn animate__delay-1s">
                        Access your dormitory information
                    </p>
                </div>
            </div>
        </div>

        <!-- Right side with login form -->
        <div class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:flex-none lg:px-20 xl:px-24">
            <div class="mx-auto w-full max-w-md lg:w-96">
                <div class="text-center lg:text-left">
                    <h2 class="text-3xl font-extrabold text-gray-900">Student Sign In</h2>
                </div>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="mt-8 animate__animated animate__fadeIn <?php echo $_SESSION['message_type'] === 'error' ? 'bg-red-50 border-l-4 border-red-500 p-4' : 'bg-green-50 border-l-4 border-green-500 p-4'; ?>">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 <?php echo $_SESSION['message_type'] === 'error' ? 'text-red-500' : 'text-green-500'; ?>" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm <?php echo $_SESSION['message_type'] === 'error' ? 'text-red-700' : 'text-green-700'; ?>">
                                    <?php echo htmlspecialchars($_SESSION['message']); ?>
                                </p>
                            </div>
                        </div>
                        <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
                    </div>
                <?php endif; ?>

                <div class="mt-8">
                    <div class="mt-6">
                        <form action="login.php" method="POST" class="space-y-6">
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                                <div class="mt-1">
                                    <input id="email" name="email" type="email" autocomplete="email" required class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm input-focus">
                                </div>
                            </div>

                            <div class="space-y-1">
                                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                <div class="mt-1">
                                    <input id="password" name="password" type="password" autocomplete="current-password" required class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm input-focus">
                                </div>
                            </div>

                            <div>
                                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out transform hover:scale-105">
                                    Sign in
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">New to our platform?</span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <a href="signup.php" class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                            Create an account
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>