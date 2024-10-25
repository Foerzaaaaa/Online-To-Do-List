<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT username, email, created_at FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $response = [];

    $new_username = htmlspecialchars($_POST['username']);
    $new_email = htmlspecialchars($_POST['email']);
    $new_password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;

    try {
        if ($new_password) {
            $stmt = $conn->prepare('UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?');
            $stmt->bind_param('sssi', $new_username, $new_email, $new_password, $user_id);
        } else {
            $stmt = $conn->prepare('UPDATE users SET username = ?, email = ? WHERE id = ?');
            $stmt->bind_param('ssi', $new_username, $new_email, $user_id);
        }

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Profile updated successfully!';
        } else {
            $response['success'] = false;
            $response['message'] = 'Error updating profile: ' . $stmt->error;
        }
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = 'Error: ' . $e->getMessage();
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Profile - Todo App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .modal-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 50;
        }

        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 100;
            width: 90%;
            max-width: 500px;
        }

        .modal.active,
        .modal-backdrop.active {
            display: block;
        }

        .fade-in {
            animation: fadeIn 0.2s ease-in;
        }

        .slide-up {
            animation: slideUp 0.3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translate(-50%, -40%);
            }

            to {
                opacity: 1;
                transform: translate(-50%, -50%);
            }
        }

        .toast {
            position: fixed;
            top: 1rem;
            right: 1rem;
            padding: 1rem;
            border-radius: 0.5rem;
            z-index: 1000;
            display: none;
        }

        .toast.active {
            display: block;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-gray-50">
    <div id="toast" class="toast bg-green-500 text-white shadow-lg">
        <span id="toastMessage"></span>
    </div>

    <div class="p-8">
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center gap-4 mb-8">
                <a href="dashboard.php"
                    class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <h2 class="text-3xl font-bold text-gray-800">Your Profile</h2>
            </div>

            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-6 bg-blue-600">
                    <div class="flex items-center justify-center">
                        <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center">
                            <i data-lucide="user" class="w-10 h-10 text-blue-600"></i>
                        </div>
                    </div>
                    <h3 class="text-center text-white text-xl font-semibold mt-4" id="profileUsername">
                        <?= htmlspecialchars($user['username']) ?>
                    </h3>
                </div>

                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex flex-col">
                            <label class="text-sm text-gray-500 mb-1">Username</label>
                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                <i data-lucide="user" class="w-5 h-5 text-gray-400"></i>
                                <span class="text-gray-700"
                                    id="displayUsername"><?= htmlspecialchars($user['username']) ?></span>
                            </div>
                        </div>

                        <div class="flex flex-col">
                            <label class="text-sm text-gray-500 mb-1">Email Address</label>
                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                <i data-lucide="mail" class="w-5 h-5 text-gray-400"></i>
                                <span class="text-gray-700"
                                    id="displayEmail"><?= htmlspecialchars($user['email']) ?></span>
                            </div>
                        </div>

                        <div class="flex flex-col">
                            <label class="text-sm text-gray-500 mb-1">Member Since</label>
                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                <i data-lucide="calendar" class="w-5 h-5 text-gray-400"></i>
                                <span
                                    class="text-gray-700"><?= htmlspecialchars(date('F j, Y', strtotime($user['created_at']))) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6 bg-gray-50 border-t border-gray-100">
                    <button id="editProfileBtn"
                        class="flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors w-full">
                        <i data-lucide="pencil" class="w-4 h-4"></i>
                        Edit Profile
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-backdrop" id="modalBackdrop"></div>

    <div class="modal" id="editProfileModal">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-gray-900">Edit Profile</h3>
                    <button class="text-gray-400 hover:text-gray-600" onclick="closeModal()">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form id="editProfileForm" class="space-y-4">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" id="username" name="username"
                            value="<?= htmlspecialchars($user['username']) ?>"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            required>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email"
                            value="<?= htmlspecialchars($user['email']) ?>"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            required>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">New Password
                            (optional)</label>
                        <input type="password" id="password" name="password"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Leave blank to keep current password">
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="closeModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        const modal = document.getElementById('editProfileModal');
        const modalBackdrop = document.getElementById('modalBackdrop');
        const editProfileBtn = document.getElementById('editProfileBtn');
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');

        function openModal() {
            modalBackdrop.classList.add('active', 'fade-in');
            modal.classList.add('active', 'slide-up');
        }

        function closeModal() {
            modalBackdrop.classList.remove('active', 'fade-in');
            modal.classList.remove('active', 'slide-up');
        }

        function showToast(message, isSuccess = true) {
            toastMessage.textContent = message;
            toast.className = `toast active ${isSuccess ? 'bg-green-500' : 'bg-red-500'} text-white shadow-lg`;

            setTimeout(() => {
                toast.classList.remove('active');
            }, 3000);
        }

        editProfileBtn.addEventListener('click', openModal);
        modalBackdrop.addEventListener('click', closeModal);

        const editProfileForm = document.getElementById('editProfileForm');
        editProfileForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(editProfileForm);
            formData.append('ajax', '1');

            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    document.getElementById('profileUsername').textContent = formData.get('username');
                    document.getElementById('displayUsername').textContent = formData.get('username');
                    document.getElementById('displayEmail').textContent = formData.get('email');

                    showToast(result.message);
                    closeModal();
                } else {
                    showToast(result.message, false);
                }
            } catch (error) {
                showToast('An error occurred while updating the profile', false);
            }
        });
    </script>
</body>

</html>
