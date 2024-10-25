<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if (!isset($_GET['list_id'])) {
    echo 'Error: List ID is missing.';
    exit();
}

$list_id = intval($_GET['list_id']);
$user_id = $_SESSION['user_id'];
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$search_query = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';

$list_stmt = $conn->prepare('SELECT title FROM todo_lists WHERE id = ? AND user_id = ?');
$list_stmt->bind_param('ii', $list_id, $user_id);
$list_stmt->execute();
$list_result = $list_stmt->get_result();
$list_title = $list_result->fetch_assoc()['title'] ?? 'Tasks';

$sql = 'SELECT * FROM tasks WHERE list_id = ?';
$params = [$list_id];

if ($status_filter !== 'all') {
    $sql .= ' AND status = ?';
    $params[] = $status_filter;
}

if ($search_query) {
    $sql .= ' AND description LIKE ?';
    $params[] = '%' . $search_query . '%';
}

$sql .= ' ORDER BY status = "completed", created_at DESC';

$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat('s', count($params)), ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($list_title) ?> - Tasks</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .glass-effect {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .task-item:hover {
            transform: translateX(5px);
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        .task-list-container {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .task-item {
            background: white;
            transition: all 0.2s ease;
        }

        .task-item:hover {
            background: #f8f9fa;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="p-8">
        <div class="max-w-5xl mx-auto space-y-6">
            <div class="glass-effect rounded-xl p-6 mb-8">

                <div class="flex items-center justify-between mb-6">

                    <div class="flex items-center gap-4">

                        <a href="dashboard.php"
                            class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-all">

                            <i data-lucide="arrow-left" class="w-5 h-5"></i>

                        </a>

                        <h2 class="text-3xl font-bold text-gray-800"><?= htmlspecialchars($list_title) ?></h2>

                    </div>
                    <div class="flex gap-4">
                        <div class="text-center">
                            <p class="text-sm text-gray-500">Total Tasks</p>
                            <p class="text-2xl font-bold text-blue-600"><?= $result->num_rows ?></p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-500">Completed</p>
                            <p class="text-2xl font-bold text-green-600">
                                <?php
                                $completed = 0;
                                mysqli_data_seek($result, 0);
                                while ($task = $result->fetch_assoc()) {
                                    if ($task['status'] === 'completed') {
                                        $completed++;
                                    }
                                }
                                echo $completed;
                                mysqli_data_seek($result, 0);
                                ?>
                            </p>
                        </div>
                    </div>
                </div>

                <form method="GET" action="todo_list.php" class="flex flex-col md:flex-row gap-4">
                    <input type="hidden" name="list_id" value="<?= $list_id ?>">
                    <div class="flex-1">
                        <div class="relative">
                            <i data-lucide="search"
                                class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="search" placeholder="Search tasks..."
                                value="<?= $search_query ?>"
                                class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <select name="status"
                        class="px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Tasks</option>
                        <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed
                        </option>
                        <option value="incomplete" <?= $status_filter === 'incomplete' ? 'selected' : '' ?>>Incomplete
                        </option>
                    </select>
                    <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                        <i data-lucide="filter" class="w-4 h-4"></i>
                        Apply Filters
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="custom-scrollbar overflow-y-auto h-96">
                    <?php if ($result->num_rows > 0): ?>
                    <ul class="divide-y divide-gray-100">
                        <?php while ($task = $result->fetch_assoc()): ?>
                        <li
                            class="p-4 bg-white hover:bg-gray-50 flex items-center justify-between gap-4 transition-all duration-200">
                            <div class="flex items-center gap-4 flex-1">
                                <a href="toggle_status.php?task_id=<?= $task['id'] ?>&current_status=<?= $task['status'] ?>&list_id=<?= $list_id ?>"
                                    class="p-2 rounded-full hover:bg-gray-100">
                                    <?php if ($task['status'] === 'completed'): ?>
                                    <i data-lucide="check-circle" class="w-6 h-6 text-green-500"></i>
                                    <?php else: ?>
                                    <i data-lucide="circle" class="w-6 h-6 text-gray-300"></i>
                                    <?php endif; ?>
                                </a>
                                <span
                                    class="flex-1 text-gray-700 <?= $task['status'] === 'completed' ? 'line-through text-gray-400' : '' ?>"><?= htmlspecialchars($task['description']) ?></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    onclick="openEditModal(<?= $task['id'] ?>, '<?= htmlspecialchars(addslashes($task['description'])) ?>', '<?= $task['status'] ?>')"
                                    class="p-2 text-gray-400 hover:text-blue-500 rounded-full hover:bg-blue-50 transition-colors">
                                    <i data-lucide="pencil" class="w-5 h-5"></i>
                                </button>
                                <a href="delete_task.php?task_id=<?= $task['id'] ?>&list_id=<?= $list_id ?>"
                                    onclick="return confirm('Are you sure you want to delete this task?');"
                                    class="p-2 text-gray-400 hover:text-red-500 rounded-full hover:bg-red-50 transition-colors">
                                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                                </a>
                            </div>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                    <?php else: ?>
                    <div class="flex flex-col items-center justify-center h-full p-8">
                        <img src="empty-list.svg" alt="Empty list" class="w-64 h-64 mb-6 opacity-70">
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Tasks Yet</h3>
                        <p class="text-gray-500 text-center max-w-md">
                            Your task list is empty. Click the "Add New Task" button below to create your first task!
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div id="editTaskModal"
                class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Edit Task</h3>
                        <form id="editTaskForm" class="mt-2">
                            <input type="hidden" id="editTaskId" name="task_id" />
                            <input type="text" id="editTaskDescription" name="description"
                                placeholder="Task description" required
                                class="w-full p-3 border border-gray-300 rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500" />

                            <select id="editTaskStatus" name="status"
                                class="w-full p-3 border border-gray-300 rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="completed">Completed</option>
                                <option value="incomplete">Incomplete</option>
                            </select>

                            <div class="flex gap-2 justify-end mt-4">
                                <button type="button" onclick="closeEditModal()"
                                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    Update Task
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="flex justify-center">
                <button onclick="openModal()"
                    class="flex items-center gap-2 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                    Add New Task
                </button>
            </div>

            <div id="createTaskModal"
                class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Add New Task</h3>
                        <form id="createTaskForm" class="mt-2">
                            <input type="text" id="taskDescription" name="description"
                                placeholder="Task description" required
                                class="w-full p-3 border border-gray-300 rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            <div class="flex gap-2 justify-end mt-4">
                                <button type="button" onclick="closeModal()"
                                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    Add Task
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <script>
                lucide.createIcons();

                function openModal() {
                    document.getElementById('createTaskModal').classList.remove('hidden');
                }

                function closeModal() {
                    document.getElementById('createTaskModal').classList.add('hidden');
                    document.getElementById('taskDescription').value = '';
                }

                function openEditModal(taskId, description, status) {
                    document.getElementById('editTaskId').value = taskId;
                    document.getElementById('editTaskDescription').value = description;
                    document.getElementById('editTaskStatus').value = status;
                    document.getElementById('editTaskModal').classList.remove('hidden');
                }

                function closeEditModal() {
                    document.getElementById('editTaskModal').classList.add('hidden');
                }

                document.getElementById('editTaskForm').addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData();
                    formData.append('task_id', document.getElementById('editTaskId').value);
                    formData.append('description', document.getElementById('editTaskDescription').value);
                    formData.append('status', document.getElementById('editTaskStatus').value);

                    fetch('update_task.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.text())
                        .then(data => {
                            if (data === 'success') {
                                window.location.reload();
                            } else {
                                alert('Error updating task: ' + data);
                            }
                        })
                        .catch(error => {
                            alert('Error: ' + error);
                        });
                });

                document.getElementById('editTaskModal').addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeEditModal();
                    }
                });

                document.getElementById('createTaskForm').addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData();

                    formData.append('description', document.getElementById('taskDescription').value);

                    fetch('create_task.php?list_id=<?= $list_id ?>', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.text())
                        .then(data => {
                            if (data === 'success') {
                                window.location.reload();
                            } else {
                                alert('Error creating task: ' + data);
                            }
                        })
                        .catch(error => {
                            alert('Error: ' + error);
                        });
                });

                document.getElementById('createTaskModal').addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeModal();
                    }
                });
            </script>
        </div>
    </div>
</body>

</html>
