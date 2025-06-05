<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
if (isAdmin()) {
    redirectWithMessage('admin/dashboard.php', 'error', 'Already Login');
}
if (isStudent()) {
    redirectWithMessage('student/dashboard.php', 'error', 'Unauthorized access');
}

// Initialize message variables
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $department = sanitizeInput($_POST['department']);
    $program = sanitizeInput($_POST['program']);
    $gender = sanitizeInput($_POST['gender']);
    $address = sanitizeInput($_POST['address']);
    $contact = sanitizeInput($_POST['contact']);
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || 
        empty($department) || empty($program) || empty($gender) || empty($address) || empty($contact)) {
        $error_message = 'All fields are required';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match';
    } elseif (strlen($password) < 8) {
        $error_message = 'Password must be at least 8 characters';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Invalid email format';
    }
    
    // Only proceed if no errors so far
    if (empty($error_message)) {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT studentID FROM students WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = 'Email already registered';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new student
            $stmt = $conn->prepare("INSERT INTO students (name, email, password, department, program, gender, address, contact, status) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Active')");
            $stmt->bind_param("ssssssss", $name, $email, $hashed_password, $department, $program, $gender, $address, $contact);
            
            if ($stmt->execute()) {
                $success_message = 'Registration successful. Please login.';
                // Clear form fields
                $_POST = array();
            } else {
                $error_message = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Student Portal</title>
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
                    <h1 class="text-4xl font-bold mb-6 animate__animated animate__fadeIn">Join Our Community!</h1>
                    <p class="text-xl opacity-90 animate__animated animate__fadeIn animate__delay-1s">
                        Create your account with us.
                    </p>
                    <div class="mt-8 animate__animated animate__fadeIn animate__delay-2s">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0 bg-indigo-500 rounded-full p-1">
                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <p class="ml-3 text-lg">Access all room</p>
                        </div>
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0 bg-indigo-500 rounded-full p-1">
                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <p class="ml-3 text-lg">Track your payment progress</p>
                        </div>
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-500 rounded-full p-1">
                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <p class="ml-3 text-lg">Connect with the admin</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right side with signup form -->
        <div class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:flex-none lg:px-20 xl:px-24">
            <div class="mx-auto w-full max-w-md lg:w-96">
                <div class="text-center lg:text-left">
                    <h2 class="text-3xl font-extrabold text-gray-900">Create your account</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Or <a href="login.php" class="font-medium text-indigo-600 hover:text-indigo-500">login to your existing account</a>
                    </p>
                </div>

                <!-- Success Message -->
                <?php if (!empty($success_message)): ?>
                    <div class="mt-8 animate__animated animate__fadeIn bg-green-50 border-l-4 border-green-500 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700">
                                    <?php echo htmlspecialchars($success_message); ?>
                                </p>
                                <p class="mt-1 text-sm text-green-600">
                                    You can now <a href="login.php" class="font-medium text-green-700 hover:text-green-600">login here</a>.
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Error Message -->
                <?php if (!empty($error_message)): ?>
                    <div class="mt-8 animate__animated animate__fadeIn bg-red-50 border-l-4 border-red-500 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700">
                                    <?php echo htmlspecialchars($error_message); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (empty($success_message)): ?>
                <div class="mt-8">
                    <div class="mt-6">
                        <form action="signup.php" method="POST" class="space-y-6">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <!-- Name -->
                                <div class="sm:col-span-2">
                                    <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                                    <div class="mt-1">
                                        <input id="name" name="name" type="text" autocomplete="name" required 
                                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm input-focus">
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="sm:col-span-2">
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                                    <div class="mt-1">
                                        <input id="email" name="email" type="email" autocomplete="email" required 
                                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm input-focus">
                                    </div>
                                </div>

                                <!-- Password -->
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                    <div class="mt-1">
                                        <input id="password" name="password" type="password" autocomplete="new-password" required 
                                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm input-focus">
                                    </div>
                                </div>

                                <!-- Confirm Password -->
                                <div>
                                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                    <div class="mt-1">
                                        <input id="confirm_password" name="confirm_password" type="password" autocomplete="new-password" required 
                                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm input-focus">
                                    </div>
                                </div>

                                <!-- Department -->
                                <div>
                                    <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
                                    <div class="mt-1">
                                        <input id="department" name="department" type="text" required 
                                               value="<?php echo isset($_POST['department']) ? htmlspecialchars($_POST['department']) : ''; ?>"
                                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm input-focus">
                                    </div>
                                </div>

                                <!-- Program -->
                                <div>
                                    <label for="program" class="block text-sm font-medium text-gray-700">Program</label>
                                    <div class="mt-1">
                                        <input id="program" name="program" type="text" required 
                                               value="<?php echo isset($_POST['program']) ? htmlspecialchars($_POST['program']) : ''; ?>"
                                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm input-focus">
                                    </div>
                                </div>

                                <!-- Gender -->
                                <div>
                                    <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
                                    <div class="mt-1">
                                        <select id="gender" name="gender" required class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md input-focus">
                                            <option value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Male' ? 'selected' : ''); ?>>Male</option>
                                            <option value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Female' ? 'selected' : ''); ?>>Female</option>
                                            <option value="Other" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Other' ? 'selected' : ''); ?>>Other</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Contact -->
                                <div>
                                    <label for="contact" class="block text-sm font-medium text-gray-700">Contact Number</label>
                                    <div class="mt-1">
                                        <input id="contact" name="contact" type="text" required 
                                               value="<?php echo isset($_POST['contact']) ? htmlspecialchars($_POST['contact']) : ''; ?>"
                                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm input-focus">
                                    </div>
                                </div>

                                <!-- Address -->
                                <div class="sm:col-span-2">
                                    <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                                    <div class="mt-1">
                                        <textarea id="address" name="address" rows="3" required 
                                                  class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm input-focus"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out transform hover:scale-105">
                                    Register
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
                            <span class="px-2 bg-white text-gray-500">Already have an account?</span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <a href="login.php" class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                            Sign in
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>