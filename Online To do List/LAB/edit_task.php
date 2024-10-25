<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if (!isset($_GET['task_id'])) {
    echo 'Error: Task ID is missing.';
    exit();
}

$task_id = intval($_GET['task_id']);

$stmt = $conn->prepare("SELECT tasks.*, todo_lists.user_id AS list_user_id FROM tasks 
    JOIN todo_lists ON tasks.list_id = todo_lists.id WHERE tasks.id = ?");
$stmt->bind_param('i', $task_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo 'Error: Task not found.';
    exit();
}

$task = $result->fetch_assoc();

if ($task['list_user_id'] !== $_SESSION['user_id']) {
    echo 'Error: You do not have permission to edit this task.';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = htmlspecialchars($_POST['description']);
    $status = $_POST['status'];

    $stmt = $conn->prepare('UPDATE tasks SET description = ?, status = ? WHERE id = ?');
    $stmt->bind_param('ssi', $description, $status, $task_id);

    if ($stmt->execute()) {
        header('Location: todo_list.php?list_id=' . $task['list_id']);
        exit();
    } else {
        echo 'Error updating task: ' . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gray-50">
    <div class="p-8">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <h2 class="text-2xl font-bold text-center text-gray-800 p-6">Edit Task</h2>

            <form method="POST" action="" class="px-6 py-4">
                <label for="description" class="block mb-1 font-semibold text-gray-700">Task Description:</label>
                <input type="text" id="description" name="description"
                    value="<?= htmlspecialchars($task['description']) ?>" required
                    class="w-full p-3 border border-gray-300 rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500" />

                <label for="status" class="block mb-1 font-semibold text-gray-700">Status:</label>
                <select name="status" id="status"
                    class="w-full p-3 border border-gray-300 rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="completed" <?= $task['status'] === 'completed' ? 'selected' : '' ?>>Completed
                    </option>
                    <option value="incomplete" <?= $task['status'] === 'incomplete' ? 'selected' : '' ?>>Incomplete
                    </option>
                </select>

                <button type="submit"
                    class="w-full bg-blue-600 text-white font-semibold py-2 rounded-lg hover:bg-blue-700 transition-colors">Update
                    Task</button>
            </form>

            <div class="px-6 py-4">
                <a href="todo_list.php?list_id=<?= $task['list_id'] ?>" class="text-blue-600 hover:underline">Back to
                    Task List</a>
            </div>
        </div>
    </div>
</body>

</html>
