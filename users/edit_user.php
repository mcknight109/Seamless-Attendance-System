<?php
session_start();
include '../db_connection.php'; // Database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if the user is not logged in
    exit();
}

// Get the POST data
if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $gender = $_POST['gender'];
    $contact_no = $_POST['contact_no'];

    // Check if password is empty. If it is, keep the existing password
    if (empty($password)) {
        // Get the current password from the database
        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $password = $user['password'];  // Use the old password if new password is empty
    } else {
        // Hash the new password if provided
        $password = password_hash($password, PASSWORD_DEFAULT);
    }

    // Update user details in the database
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, password = ?, gender = ?, contact_no = ? WHERE user_id = ?");
    $stmt->bind_param("sssssi", $full_name, $email, $password, $gender, $contact_no, $user_id);

    if ($stmt->execute()) {
        // Set a success message in session
        $_SESSION['message'] = 'Account details updated successfully!';
        header("Location: user_dashboard.php"); // Redirect back to the dashboard with a success message
        exit();
    } else {
        // Set an error message in session
        $_SESSION['message'] = 'Error updating account details.';
        header("Location: user_dashboard.php"); // Redirect back to the dashboard with an error message
        exit();
    }
} else {
    // Invalid data response
    $_SESSION['message'] = 'Invalid user data.';
    header("Location: user_dashboard.php"); // Redirect back to the dashboard with an error message
    exit();
}
?>
