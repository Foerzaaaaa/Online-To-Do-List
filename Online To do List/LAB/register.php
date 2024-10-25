<?php
require 'db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $stmt = $conn->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $username, $email, $password);

    if ($stmt->execute()) {
        header('Location: index.php');
    } else {
        echo 'Error: ' . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Todo List App</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            margin: 0;
            height: 100vh;
            overflow: hidden;
            background: #f0f2f5;
        }

        .register-container {
            display: flex;
            height: 100vh;
        }

        .register-left {
            width: 50%;
            background: linear-gradient(135deg, #4158d0, #c850c0);
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            color: white;
            padding: 40px;
        }

        .todo-illustration {
            position: relative;
            width: 100%;
            max-width: 400px;
        }

        .floating-tasks {
            position: absolute;
            width: 100%;
            height: 100%;
        }

        .task-item {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 10px;
            animation: float 6s infinite;
        }

        .task-item:nth-child(1) {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .task-item:nth-child(2) {
            top: 40%;
            right: 15%;
            animation-delay: 1s;
        }

        .task-item:nth-child(3) {
            bottom: 25%;
            left: 20%;
            animation-delay: 2s;
        }

        .task-checkbox {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .task-done .task-checkbox::after {
            content: '✓';
            color: white;
            font-size: 14px;
        }

        .task-done .task-text {
            text-decoration: line-through;
            opacity: 0.7;
        }

        .welcome-banner {
            text-align: center;
            margin-bottom: 80px;
            animation: fadeInUp 1s ease-out;
        }

        .welcome-banner h1 {
            font-size: 2.5em;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .welcome-banner p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .register-right {
            width: 50%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            background-color: white;
        }

        .register-form {
            width: 100%;
            max-width: 400px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            animation: slideUp 0.6s ease-out;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #4158d0;
        }

        .register-form input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            font-size: 16px;
            border: 2px solid #e1e1e1;
            border-radius: 10px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .register-form input:focus {
            border-color: #4158d0;
            box-shadow: 0 0 0 3px rgba(65, 88, 208, 0.1);
            outline: none;
        }

        .register-form button {
            padding: 15px;
            font-size: 18px;
            background: linear-gradient(135deg, #4158d0, #c850c0);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            font-weight: 600;
        }

        .register-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(65, 88, 208, 0.3);
        }

        .welcome-text {
            margin-bottom: 15px;
            font-size: 16px;
            color: #666;
            text-align: center;
        }

        .form-title {
            margin-bottom: 30px;
            font-size: 32px;
            font-weight: 700;
            text-align: center;
            background: linear-gradient(135deg, #4158d0, #c850c0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .already-have-account {
            margin-top: 20px;
            font-size: 15px;
            text-align: center;
            color: #666;
        }

        .already-have-account a {
            color: #4158d0;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .already-have-account a:hover {
            color: #c850c0;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(2deg);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .register-container {
                flex-direction: column;
            }

            .register-left {
                width: 100%;
                height: 40vh;
                padding: 20px;
            }

            .register-right {
                width: 100%;
                height: 60vh;
                padding: 20px;
            }

            .floating-tasks {
                transform: scale(0.8);
            }

            .welcome-banner h1 {
                font-size: 2em;
            }

            .welcome-banner p {
                font-size: 1em;
            }
        }
    </style>
</head>

<body>
    <div class="register-container">
        <div class="register-left">
            <div class="welcome-banner">
                <h1>Organize Your Life</h1>
                <p>Create, manage, and accomplish your daily tasks with ease</p>
            </div>
            <div class="todo-illustration">
                <div class="floating-tasks">
                    <div class="task-item task-done">
                        <div class="task-checkbox"></div>
                        <div class="task-text">Morning Exercise</div>
                    </div>
                    <div class="task-item">
                        <div class="task-checkbox"></div>
                        <div class="task-text">Team Meeting</div>
                    </div>
                    <div class="task-item">
                        <div class="task-checkbox"></div>
                        <div class="task-text">Project Planning</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="register-right">
            <div class="welcome-text">Welcome to Online To-Do List</div>
            <div class="form-title">Create Your Account</div>
            <form method="POST" action="register.php" class="register-form">
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit">Create Account</button>
            </form>
            <div class="already-have-account">Already have an account? <a href="index.php">Login</a></div>
        </div>
    </div>
</body>

</html>
