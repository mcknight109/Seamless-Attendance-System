<?php
session_start();
include '../../db_connection.php'; // DB connection

// Set timezone
date_default_timezone_set('Asia/Manila');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

// Handle leave request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id    = $_SESSION['user_id'];
    $leave_type = trim($_POST['leave_type'] ?? '');
    $reason     = trim($_POST['reason'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date   = $_POST['end_date'] ?? '';

    // Validate required fields
    if ($leave_type && $reason && $start_date && $end_date) {
        $sql = "INSERT INTO leave_requests (user_id, leave_type, reason, start_date, end_date, status, created_at) 
                VALUES (?, ?, ?, ?, ?, 'pending', NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $user_id, $leave_type, $reason, $start_date, $end_date);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: ../request_leave.php?success=1");
            exit();
        } else {
            $stmt->close();
            header("Location: ../request_leave.php?error=db");
            exit();
        }
    } else {
        // Missing fields
        header("Location: ../request_leave.php?error=missing");
        exit();
    }
} else {
    // Invalid access
    header("Location: ../request_leave.php");
    exit();
}
