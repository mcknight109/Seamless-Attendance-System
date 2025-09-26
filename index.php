<?php
session_start();
include 'db_connection.php'; // Database connection

// Initialize variables
$error = '';

// Set timezone to ensure correct time display
date_default_timezone_set('Asia/Manila');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get email and password from form
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check if inputs are empty
    if (empty($email) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        // Prepare and execute query
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Verify the password using password_verify
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id']; // Correct column name
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role']; // Assuming 'role' column exists in the database

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: ./admin/admin_dashboard.php");
                } elseif ($user['role'] === 'user') {
                    header("Location: ./users/time_in.php");
                } else {
                    $error = "Invalid role assigned to this account.";
                }
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "No user found with this email.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="index.scss">
    <link rel="stylesheet" href="btn.scss">
    <title>Login</title>
</head>
<body>
    <div class="container">
        <div class="content-container">
            <div class="left">
                <div class="sas">S.A.S</div>
                <h1>Seamless</h1>
                <h2>Attendance System</h2>
                <p class="description">Manage your attendance seamlessly with our innovative system designed for efficiency and ease of use.</p>
            <div class="login-option">
                <button class="btn-login">Login</button>
            </div>
            </div>
            <div class="right">
                <div class="image">
                    <img src="./images/login.jpg" alt="Attendance System Image">
                </div>
            </div>
        </div>
    </div>

    <div class="login_container">
        <div class="form-picture">
            <img src="images/login.jpg" alt="Login Illustration">
        </div>
        <div class="login-form">
            <span class="close-btn" id="closeModalBtn">&times;</span>
            <h2>Login</h2>
            <?php if ($error): ?>
                <div class="alert alert-danger" style="color: red; margin-bottom: 10px;"><?= $error ?></div>
            <?php endif; ?>
        <form action="index.php" method="POST" id="loginForm">
            <div class="input">
                <label for="email">Email:</label>
                <div class="input-icon">
                    <i class="bi bi-envelope"></i>
                    <input 
                        type="text" 
                        id="email" 
                        name="email" 
                        placeholder="Enter your email" 
                        maxlength="50" 
                        title="Email can only contain letters, numbers, and @._-" 
                        required>
                </div>
            </div>
            <div class="input">
                <label for="password">Password:</label>
                <div class="input-icon">
                    <i class="bi bi-lock"></i>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter you password" 
                        maxlength="8" 
                        title="Password can only contain letters, numbers, and @" 
                        required>
                </div>
            </div>
            <div class="input">
                <button type="submit" class="login-btn adjust">Login</button>
            </div>
        </div>
    </div>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const loginBtn = document.querySelector(".btn-login");
        const modal = document.querySelector(".login_container");
        const closeModalBtn = document.getElementById("closeModalBtn");
        const container = document.querySelector(".container");

        // Show modal
        loginBtn.addEventListener("click", function () {
            modal.style.display = "flex";
            container.style.filter = "blur(5px)";
        });

        // Close modal when clicking the X button
        closeModalBtn.addEventListener("click", function () {
            modal.style.display = "none";
            container.style.filter = "none";
        });

        // Close modal when clicking outside the login-form (but not the form itself)
        window.addEventListener("click", function (e) {
            if (e.target === modal) {
                modal.style.display = "none";
                container.style.filter = "none";
            }
        });

        // If PHP sends an error, keep modal open automatically
        <?php if ($error): ?>
            modal.style.display = "flex";
            container.style.filter = "blur(5px)";
        <?php endif; ?>
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="index.js"></script>
</body>
</html>
