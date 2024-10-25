<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare('SELECT * FROM todo_lists WHERE user_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_lists = $result->num_rows;

$stmt_tasks = $conn->prepare("
    SELECT COUNT(DISTINCT t.id) as total_tasks 
    FROM tasks t 
    JOIN todo_lists tl ON t.list_id = tl.id 
    WHERE tl.user_id = ?
");
$stmt_tasks->bind_param('i', $user_id);
$stmt_tasks->execute();
$tasks_result = $stmt_tasks->get_result();
$total_tasks = $tasks_result->fetch_assoc()['total_tasks'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your To-Do Lists</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<style>
    .glass-effect {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .list-card:hover {
        transform: translateY(-5px);
    }
</style>

<body class="bg-gray-100 min-h-screen">
    <div class="fixed left-0 top-0 h-full w-64 glass-effect shadow-lg p-6">
        <div class="flex flex-col h-full">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-blue-800">TaskWarkop</h1>
                <p class="text-sm text-gray-600">Organize your day</p>
            </div>

            <nav class="flex-1">
                <ul class="space-y-4">
                    <li>
                        <a href="#" class="flex items-center gap-3 p-3 rounded-lg bg-blue-100 text-blue-800">
                            <i data-lucide="layout-grid" class="w-5 h-5"></i>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="view_profile.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100">
                            <i data-lucide="user" class="w-5 h-5"></i>
                            Profile
                        </a>
                    </li>
                    <li>
                        <a href="logout.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100">
                            <i data-lucide="log-out" class="w-5 h-5"></i>
                            Logout
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <div class="ml-64 p-8">
        <div class="max-w-6xl mx-auto">
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-800">Welcome back!</h2>
                        <p class="text-gray-600">Here are your to-do lists</p>
                    </div>
                    <button onclick="openModal()"
                        class="flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-all shadow-lg hover:shadow-xl">
                        <i data-lucide="plus" class="w-5 h-5"></i>
                        Create New List
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-6 mb-8">
                <div class="glass-effect rounded-xl p-6">
                    <div class="flex items-center gap-4">
                        <i data-lucide="list" class="w-8 h-8 text-blue-600"></i>
                        <div>
                            <p class="text-lg font-bold text-gray-800">Total Lists</p>
                            <p class="text-sm text-gray-600"><?= $total_lists ?></p>
                        </div>
                    </div>
                </div>
                <div class="glass-effect rounded-xl p-6">
                    <div class="flex items-center gap-4">
                        <i data-lucide="task" class="w-8 h-8 text-blue-600"></i>
                        <div>
                            <p class="text-lg font-bold text-gray-800">Total Tasks</p>
                            <p class="text-sm text-gray-600"><?= $total_tasks ?></p>
                        </div>
                    </div>
                </div>
                <div class="glass-effect rounded-xl p-6">
                    <div class="flex items-center gap-4">
                        <i data-lucide="clock" class="w-8 h-8 text-blue-600"></i>
                        <div>
                            <p class="text-lg font-bold text-gray-800">Last Updated</p>
                            <p class="text-sm text-gray-600"><?= date('d M Y') ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 mb-8">
                <?php 
                mysqli_data_seek($result, 0); 
                while ($list = $result->fetch_assoc()): 
                ?>
                <div class="list-card glass-effect rounded-lg p-4 hover:shadow-lg transition-all">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4 flex-1">
                            <a href="todo_list.php?list_id=<?= $list['id'] ?>"
                                class="text-lg font-medium text-gray-700 hover:text-blue-600">
                                <?= htmlspecialchars($list['title']) ?>
                            </a>
                            <i data-lucide="chevron-right" class="text-gray-400 w-5 h-5"></i>
                        </div>
                        <a href="delete_list.php?list_id=<?= $list['id'] ?>"
                            onclick="return confirm('Are you sure you want to delete this to-do list? This will delete all associated tasks.');"
                            class="p-2 text-gray-400 hover:text-red-500 rounded-full hover:bg-red-50 transition-all">
                            <i data-lucide="trash-2" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <div id="createListModal"
                class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Create a New To-Do List</h3>
                        <form id="createListForm" class="mt-2">
                            <input type="text" id="listTitle" name="title" placeholder="Enter list title" required
                                class="w-full p-3 border border-gray-300 rounded-lg mb-4 focus:outline-none focus:ring-2 focus: ring-blue-500" />

                            <div class="flex gap-2 justify-end mt-4">
                                <button type="button" onclick="closeModal()"
                                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-all">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all">
                                    Create List
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <script>
                lucide.createIcons();

                function openModal() {
                    document.getElementById('createListModal').classList.remove('hidden');
                }

                function closeModal() {
                    document.getElementById('createListModal').classList.add('hidden');
                    document.getElementById('listTitle').value = '';
                }

                document.getElementById('createListForm').addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData();
                    formData.append('title', document.getElementById('listTitle').value);

                    fetch('create_list.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.text())
                        .then(data => {
                            if (data === 'success') {
                                window.location.reload();
                            } else {
                                alert('Error creating list: ' + data);
                            }
                        })
                        .catch(error => {
                            alert('Error: ' + error);
                        });
                });

                document.getElementById('createListModal').addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeModal();
                    }
                });
            </script>
        </div>
    </div>
</body>

</html>

<?php
$stmt->close();
$stmt_tasks->close();
$conn->close();
?>
