<?php
session_start();
include '../db_connection.php'; // Database connection

// Check if user is logged in and if the role is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}


// Check if data is received via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_id = intval($_POST['leave_id']);
    $action = $_POST['action']; // 'approved' or 'denied'

    // Validate action
    if ($action === 'approved' || $action === 'denied') {
        $status = $action; // Use 'approved' or 'denied' directly

        // Update leave request status in the database
        $stmt = $conn->prepare("UPDATE leave_requests SET status = ? WHERE leave_id = ?");
        $stmt->bind_param("si", $status, $leave_id);

        if ($stmt->execute()) {
            echo "Leave request has been $status.";
        } else {
            echo "Error updating status: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "Invalid action.";
    }
}

// Update the leave request status in the database
$stmt = $conn->prepare("UPDATE leave_requests SET status = ? WHERE leave_id = ?");
$stmt->bind_param("si", $status, $leave_id);

if ($stmt->execute()) {
    // Redirect back to the leave requests page after updating the status
    header("Location: leave_requests.php");
    exit();
} else {
    echo "Error updating status: " . $conn->error;
}

$conn->close();
?>
