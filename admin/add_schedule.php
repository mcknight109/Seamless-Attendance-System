<?php
session_start();
include '../db_connection.php'; // Database connection

// Check if user is logged in and if the role is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $shift_type = $_POST['shift_type'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // Insert the shift schedule into the database
    $query = "INSERT INTO shifts (shift_type, start_time, end_time) VALUES ('$shift_type', '$start_time', '$end_time')";
    if ($conn->query($query) === TRUE) {
        // Redirect to the schedule management page after successful insertion
        header("Location: schedule.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
