<?php
include '../db_connection.php';

// Fetch users with temporary schedules
$stmt = $conn->prepare(
    "SELECT user_id, original_shift_type, original_start_time, original_end_time 
     FROM users 
     WHERE original_shift_type IS NOT NULL"
);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

foreach ($users as $user) {
    // Revert schedule to the original
    $stmt = $conn->prepare(
        "UPDATE users 
         SET shift_type = ?, 
             start_time = ?, 
             end_time = ?, 
             original_shift_type = NULL, 
             original_start_time = NULL, 
             original_end_time = NULL 
         WHERE user_id = ?"
    );
    $stmt->bind_param(
        "sssi",
        $user['original_shift_type'],
        $user['original_start_time'],
        $user['original_end_time'],
        $user['user_id']
    );
    $stmt->execute();
}
?>
