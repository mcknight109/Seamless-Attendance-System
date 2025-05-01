<?php
include '../db_connection.php';

if (isset($_GET['shift_type'])) {
    $shift_type = $_GET['shift_type'];

    // Query to fetch shift times based on the selected shift type
    $query = "SELECT start_time, end_time FROM shifts WHERE shift_type = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $shift_type);
    $stmt->execute();
    $result = $stmt->get_result();

    $shift_times = [];
    while ($row = $result->fetch_assoc()) {
        $shift_times[] = $row;
    }

    echo json_encode($shift_times);  // Return shift times as JSON
}
?>
