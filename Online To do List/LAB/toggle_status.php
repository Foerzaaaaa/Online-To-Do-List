<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if (isset($_GET['task_id']) && isset($_GET['current_status']) && isset($_GET['list_id'])) {
    $task_id = intval($_GET['task_id']);
    $current_status = $_GET['current_status'];
    $list_id = intval($_GET['list_id']);

    $new_status = $current_status === 'completed' ? 'incomplete' : 'completed';

    $stmt = $conn->prepare('UPDATE tasks SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $new_status, $task_id);

    if ($stmt->execute()) {
        header("Location: todo_list.php?list_id=$list_id");
        exit();
    } else {
        echo 'Error updating task status: ' . $stmt->error;
    }
} else {
    echo 'Error: Task ID or List ID is missing.';
}
?>
