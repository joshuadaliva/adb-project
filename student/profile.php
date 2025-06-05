<?php
require_once __DIR__ . '/../includes/auth.php';

if (!isStudent()) {
    redirectWithMessage('../login.php', 'error', 'Unauthorized access');
}

require_once __DIR__ . '/../config/database.php';

$studentID = $_SESSION['user_id'];





// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = sanitizeInput($_POST['name']);
    $department = sanitizeInput($_POST['department']);
    $program = sanitizeInput($_POST['program']);
    $gender = sanitizeInput($_POST['gender']);
    $address = sanitizeInput($_POST['address']);
    $contact = sanitizeInput($_POST['contact']);

    // Check if password is being changed
    $passwordUpdate = '';
    if (!empty($_POST['password']) && !empty($_POST['confirm_password'])) {
        if ($_POST['password'] !== $_POST['confirm_password']) {
            redirectWithMessage('profile.php', 'error', 'Passwords do not match');
        }

        if (strlen($_POST['password']) < 8) {
            redirectWithMessage('profile.php', 'error', 'Password must be at least 8 characters');
        }

        $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $passwordUpdate = ", password = ?";
    }

    if (!empty($passwordUpdate)) {
        $stmt = $conn->prepare("UPDATE students SET name = ?, department = ?, program = ?, gender = ?, address = ?, contact = ? $passwordUpdate WHERE studentID = ?");
        $stmt->bind_param("sssssssi", $name, $department, $program, $gender, $address, $contact, $hashed_password, $studentID);
    } else {
        $stmt = $conn->prepare("UPDATE students SET name = ?, department = ?, program = ?, gender = ?, address = ?, contact = ? WHERE studentID = ?");
        $stmt->bind_param("ssssssi", $name, $department, $program, $gender, $address, $contact, $studentID);
    }

    if ($stmt->execute()) {
        // Update session name if changed
        $_SESSION['user_name'] = $name;

        redirectWithMessage('profile.php', 'success', 'Profile updated successfully');
    } else {
        redirectWithMessage('profile.php', 'error', 'Failed to update profile');
    }
}












// Get student data
$stmt = $conn->prepare("SELECT * FROM students WHERE studentID = ?");
$stmt->bind_param("i", $studentID);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    redirectWithMessage('/../login.php', 'error', 'Student not found');
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mx-auto px-4">
    <h1 class="text-2xl font-bold mb-6">My Profile</h1>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl mx-auto">
        <form action="profile.php" method="POST">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-gray-700 mb-2">Full Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" class="w-full px-3 py-2 border rounded" required>
                </div>

                <div>
                    <label for="email" class="block text-gray-700 mb-2">Email:</label>
                    <input type="email" id="email" value="<?php echo htmlspecialchars($student['email']); ?>" class="w-full px-3 py-2 border rounded bg-gray-100" readonly>
                </div>

                <div>
                    <label for="department" class="block text-gray-700 mb-2">Department:</label>
                    <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($student['department']); ?>" class="w-full px-3 py-2 border rounded">
                </div>

                <div>
                    <label for="program" class="block text-gray-700 mb-2">Program:</label>
                    <input type="text" id="program" name="program" value="<?php echo htmlspecialchars($student['program']); ?>" class="w-full px-3 py-2 border rounded">
                </div>

                <div>
                    <label for="gender" class="block text-gray-700 mb-2">Gender:</label>
                    <select id="gender" name="gender" class="w-full px-3 py-2 border rounded">
                        <option value="Male" <?php echo $student['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo $student['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo $student['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>

                <div>
                    <label for="contact" class="block text-gray-700 mb-2">Contact Number:</label>
                    <input type="text" id="contact" name="contact" value="<?php echo htmlspecialchars($student['contact']); ?>" class="w-full px-3 py-2 border rounded">
                </div>

                <div class="md:col-span-2">
                    <label for="address" class="block text-gray-700 mb-2">Address:</label>
                    <textarea id="address" name="address" class="w-full px-3 py-2 border rounded"><?php echo htmlspecialchars($student['address']); ?></textarea>
                </div>

                <div>
                    <label for="password" class="block text-gray-700 mb-2">New Password (leave blank to keep current):</label>
                    <input type="password" id="password" name="password" class="w-full px-3 py-2 border rounded">
                </div>

                <div>
                    <label for="confirm_password" class="block text-gray-700 mb-2">Confirm New Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="w-full px-3 py-2 border rounded">
                </div>
            </div>

            <button type="submit" name="update_profile" class="mt-6 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Update Profile</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>