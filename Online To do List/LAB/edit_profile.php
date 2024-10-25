<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare('SELECT username, email FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = htmlspecialchars($_POST['username']);
    $new_email = htmlspecialchars($_POST['email']);
    $new_password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;

    if ($new_password) {
        $stmt = $conn->prepare('UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?');
        $stmt->bind_param('sssi', $new_username, $new_email, $new_password, $user_id);
    } else {
        $stmt = $conn->prepare('UPDATE users SET username = ?, email = ? WHERE id = ?');
        $stmt->bind_param('ssi', $new_username, $new_email, $user_id);
    }

    if ($stmt->execute()) {
        echo 'Profile updated successfully!';
        header('Location: view_profile.php');
        exit();
    } else {
        echo 'Error updating profile: ' . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gray-50">
    <div class="p-8">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <h2 class="text-2xl font-bold text-center text-gray-800 p-6">Edit Profile</h2>

            <form method="POST" action="edit_profile.php" class="px-6 py-4">
                <label for="username" class="block mb-1 font-semibold text-gray-700">Username:</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>"
                    required
                    class="w-full p-3 border border-gray-300 rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500" />

                <label for="email" class="block mb-1 font-semibold text-gray-700">Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"
                    required
                    class="w-full p-3 border border-gray-300 rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500" />

                <label for="password" class="block mb-1 font-semibold text-gray-700">New Password (optional):</label>
                <input type="password" id="password" name="password" placeholder="Leave blank to keep current password"
                    class="w-full p-3 border border-gray-300 rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500" />

                <button type="submit"
                    class="w-full bg-blue-600 text-white font-semibold py-2 rounded-lg hover:bg-blue-700 transition-colors">Update
                    Profile</button>
            </form>

            <div class="px-6 py-4">
                <a href="view_profile.php" class="text-blue-600 hover:underline">Back to Profile</a>
                <br>
                <a href="dashboard.php" class="text-blue-600 hover:underline">Back to Dashboard</a>
            </div>
        </div>
    </div>
</body>

</html>
