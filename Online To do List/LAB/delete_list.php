<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if (isset($_GET['list_id'])) {
    $list_id = intval($_GET['list_id']);

    $stmt1 = $conn->prepare('DELETE FROM tasks WHERE list_id = ?');
    $stmt1->bind_param('i', $list_id);
    $stmt1->execute();

    $stmt2 = $conn->prepare('DELETE FROM todo_lists WHERE id = ?');
    $stmt2->bind_param('i', $list_id);

    if ($stmt2->execute()) {
        header('Location: dashboard.php');
        exit();
    } else {
        echo 'Error deleting to-do list: ' . $stmt2->error;
    }
} else {
    echo 'Error: List ID is missing.';
}
?>
