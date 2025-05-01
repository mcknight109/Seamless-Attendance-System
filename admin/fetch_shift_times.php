<?php
// Database connection
require '../db_connection.php';

// Check if the request is for fetching shift times by shift type
if (isset($_GET['shift_type'])) {
    $shift_type = $_GET['shift_type'];
    
    $query = "SELECT shift_id, start_time, end_time FROM shifts WHERE shift_type = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $shift_type);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $shifts = [];
    while ($row = $result->fetch_assoc()) {
        $shifts[] = $row;
    }
    
    echo json_encode($shifts);
}

// Check if the request is for fetching user shift details by user ID
if (isset($_GET['user_id'])) {
    $userId = intval($_GET['user_id']);

    $query = "SELECT shift_type, shift_time FROM user_shifts WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($shiftType, $shiftTime);

    if ($stmt->fetch()) {
        echo json_encode([
            'shift_type' => $shiftType,
            'shift_time' => $shiftTime
        ]);
    } else {
        echo json_encode(['error' => 'User shift details not found.']);
    }

    $stmt->close();
}

?>
