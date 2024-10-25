<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $list_id = intval($_GET['list_id']);
    $description = htmlspecialchars($_POST['description']);
    $status = 'incomplete';

    if (!empty($description)) {
        $stmt = $conn->prepare('INSERT INTO tasks (list_id, description, status) VALUES (?, ?, ?)');
        $stmt->bind_param('iss', $list_id, $description, $status);

        if ($stmt->execute()) {
            echo 'success';
        } else {
            echo 'Error: ' . $stmt->error;
        }
    } else {
        echo 'Task description cannot be empty!';
    }
    exit();
}

header('Location: todo_list.php?list_id=' . intval($_GET['list_id']));
?>
