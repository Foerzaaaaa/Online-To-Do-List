<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo 'unauthorized';
    exit();
}

if (!isset($_POST['task_id']) || !isset($_POST['description']) || !isset($_POST['status'])) {
    echo 'missing_fields';
    exit();
}

$task_id = intval($_POST['task_id']);
$description = htmlspecialchars($_POST['description']);
$status = $_POST['status'];

$stmt = $conn->prepare("SELECT todo_lists.user_id FROM tasks
    JOIN todo_lists ON tasks.list_id = todo_lists.id
    WHERE tasks.id = ?");
$stmt->bind_param('i', $task_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo 'task_not_found';
    exit();
}

$task = $result->fetch_assoc();
if ($task['user_id'] !== $_SESSION['user_id']) {
    echo 'unauthorized';
    exit();
}

$update_stmt = $conn->prepare('UPDATE tasks SET description = ?, status = ? WHERE id = ?');
$update_stmt->bind_param('ssi', $description, $status, $task_id);

if ($update_stmt->execute()) {
    echo 'success';
} else {
    echo 'error';
}
