<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if (isset($_GET['task_id']) && isset($_GET['list_id'])) {
    $task_id = intval($_GET['task_id']);
    $list_id = intval($_GET['list_id']);

    $stmt = $conn->prepare('DELETE FROM tasks WHERE id = ? AND list_id = ?');
    $stmt->bind_param('ii', $task_id, $list_id);

    if ($stmt->execute()) {
        header("Location: todo_list.php?list_id=$list_id");
        exit();
    } else {
        echo 'Error deleting task: ' . $stmt->error;
    }
} else {
    echo 'Error: Task ID or List ID is missing.';
}
?>
