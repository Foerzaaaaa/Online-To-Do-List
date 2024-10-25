<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = htmlspecialchars($_POST['title']);
    $user_id = $_SESSION['user_id'];

    if (!empty($title)) {
        $stmt = $conn->prepare('INSERT INTO todo_lists (user_id, title) VALUES (?, ?)');
        $stmt->bind_param('is', $user_id, $title);

        if ($stmt->execute()) {
            echo 'success';
        } else {
            echo 'Error: ' . $stmt->error;
        }
    } else {
        echo 'Title cannot be empty!';
    }
    exit();
}

header('Location: dashboard.php');
?>
