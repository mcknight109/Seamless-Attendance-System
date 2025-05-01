<?php
// Include database connection
include '../db_connection.php';

// Check if user is logged in and has admin privileges
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get the user_id from the POST request
if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    // Begin transaction to ensure data integrity
    $conn->begin_transaction();

    try {
        // Fetch the user data
        $query = "SELECT * FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        $stmt->close();

        if (!$user_data) {
            throw new Exception("User not found");
        }

        // Insert the data into the archive table
        $archive_query = "INSERT INTO users_archive (user_id, full_name, gender, contact_no, role, email) 
                          VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($archive_query);
        $stmt->bind_param(
            "isssss",
            $user_data['user_id'],
            $user_data['full_name'],
            $user_data['gender'],
            $user_data['contact_no'],
            $user_data['role'],
            $user_data['email']
        );
        if (!$stmt->execute()) {
            throw new Exception("Failed to archive user data");
        }
        $stmt->close();

        // Delete the user from the main table
        $delete_query = "DELETE FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete user");
        }
        $stmt->close();

        // Commit the transaction
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Rollback the transaction on failure
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'User ID not provided']);
}
