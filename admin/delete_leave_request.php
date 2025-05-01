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

    // Fetch the leave request details before deleting
    $query = "SELECT lr.*, u.full_name 
              FROM leave_requests lr 
              INNER JOIN users u ON lr.user_id = u.user_id 
              WHERE lr.leave_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $leave_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $leave_request = $result->fetch_assoc();

        // Insert the record into the archive table
        $archive_query = "INSERT INTO leave_requests_archive 
                          (leave_id, user_id, full_name, leave_date, start_date, end_date, leave_type, status) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $archive_stmt = $conn->prepare($archive_query);
        $archive_stmt->bind_param(
            "iissssss",
            $leave_request['leave_id'],
            $leave_request['user_id'],
            $leave_request['full_name'],
            $leave_request['leave_date'],
            $leave_request['start_date'],
            $leave_request['end_date'],
            $leave_request['leave_type'],
            $leave_request['status']
        );

        if ($archive_stmt->execute()) {
            // Delete the leave request from the original table
            $delete_query = "DELETE FROM leave_requests WHERE leave_id = ?";
            $delete_stmt = $conn->prepare($delete_query);
            $delete_stmt->bind_param("i", $leave_id);

            if ($delete_stmt->execute()) {
                $_SESSION['message'] = "Leave request archived and deleted successfully.";
                header("Location: leave_requests.php");
                exit();
            } else {
                echo "Error deleting request: " . $conn->error;
            }

            $delete_stmt->close();
        } else {
            echo "Error archiving request: " . $conn->error;
        }

        $archive_stmt->close();
    } else {
        $_SESSION['error'] = "Leave request not found.";
        header("Location: leave_requests.php");
        exit();
    }

    $stmt->close();
}

$conn->close();
?>
