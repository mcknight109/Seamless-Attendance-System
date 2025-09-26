<?php
// signup.php
include 'db_connection.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $gender = $_POST['gender'];
    $contact_no = trim($_POST['contact_no']);
    
    // Check if the email already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Email already exists
        $error = "Email is already registered.";
    } else {
        // Hash the password before storing
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user into the database
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, gender, contact_no, role) VALUES (?, ?, ?, ?, ?, 'user')");
        $stmt->bind_param("sssss", $full_name, $email, $hashed_password, $gender, $contact_no);
        if ($stmt->execute()) {
            // Success - redirect to login page or dashboard
            header("Location: index.php");
            exit();
        } else {
            // Error while inserting
            $error = "An error occurred while creating your account. Please try again.";
        }
    }
    $stmt->close();
}
?>
