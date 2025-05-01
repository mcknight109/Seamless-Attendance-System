<?php
session_start();
include '../../functions/db_connection.php'; // Include database connection

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// Fetch admin's email
$admin_email = $_SESSION['email'];


$user_id = $_SESSION['user_id'];
$current_time = date('Y-m-d H:i:s');
$current_date = date('Y-m-d');

// Check if an attendance record exists for today
$query = "SELECT attendance_id, time_in, time_out FROM attendance WHERE user_id = ? AND DATE(time_in) = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $user_id, $current_date);
$stmt->execute();
$result = $stmt->get_result();
$record = $result->fetch_assoc();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['time_in'])) {
        // Handle Time In
        if (!$record) {
            // No record for today: Create a new Time In
            $insert_query = "INSERT INTO attendance (user_id, time_in) VALUES (?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("is", $user_id, $current_time);
            if ($stmt->execute()) {
                $message = "Time In recorded successfully.";
            }
        } else {
            $message = "You already timed in for today.";
        }
    } elseif (isset($_POST['time_out'])) {
        // Handle Time Out
        if ($record && !$record['time_out']) {
            // Update Time Out for the existing record
            $update_query = "UPDATE attendance SET time_out = ? WHERE attendance_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("si", $current_time, $record['attendance_id']);
            if ($stmt->execute()) {
                $message = "Time Out recorded successfully.";
            }
        } else {
            $message = $record ? "You already timed out for today." : "You need to Time In first.";
        }
    }
}

// Redirect back to attendance page with a message
header("Location: attendance.php?message=" . urlencode($message));
exit();
?>
