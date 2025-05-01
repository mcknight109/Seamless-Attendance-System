<?php
session_start();
include '../db_connection.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the logged-in user ID
$user_id = $_SESSION['user_id'];

// Get the data from the form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shift_type = $_POST['shift_type'] ?? null;
    $start_time = $_POST['start_time'] ?? null;
    $end_time = $_POST['end_time'] ?? null;

    // Basic validation
    if (!$shift_type || !$start_time || !$end_time) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit();
    } else {
        // Prepare SQL to insert the request into the schedule_requests table
        $stmt = $conn->prepare("INSERT INTO schedule_requests (user_id, shift_type, start_time, end_time, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param("isss", $user_id, $shift_type, $start_time, $end_time);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Your schedule request has been submitted and is awaiting approval.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'There was an error submitting your request. Please try again.']);
        }
    }
}
?>
