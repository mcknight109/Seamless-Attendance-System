<?php
include '../db_connection.php'; // Ensure this file is correct

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $password = $_POST['password']; // The password entered by the user
    $contact_no = $_POST['contact_no'];
    $role = $_POST['role'];
    $shift_type = $_POST['shift_type']; // Get the selected shift type
    $shift_time = $_POST['shift_time']; // Get the selected shift time

    // Hash the password before storing it
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if the email already exists
    $check_sql = "SELECT email FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('s', $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        echo "Error: Email already exists. Please choose a different email.";
    } else {
        // Fetch the shift_id, start_time, and end_time based on selected shift_type and shift_time
        $query = "SELECT shift_id, start_time, end_time FROM shifts WHERE shift_type = ? AND shift_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $shift_type, $shift_time);
        $stmt->execute();
        $result = $stmt->get_result();

        // Get the shift details
        if ($row = $result->fetch_assoc()) {
            $shift_id = $row['shift_id']; // Get the correct shift_id
            $start_time = $row['start_time']; // Get the start_time
            $end_time = $row['end_time']; // Get the end_time
        } else {
            echo "Error: Shift details not found.";
            exit;
        }

        // Insert into the database with the hashed password
        $sql = "INSERT INTO users (full_name, email, gender, password, contact_no, role, shift_id, shift_type, start_time, end_time) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssssisss', $full_name, $email, $gender, $hashed_password, $contact_no, $role, $shift_id, $shift_type, $start_time, $end_time);

        if ($stmt->execute()) {
            // Redirect after successful insertion
            header("Location: users_management.php?");
        } else {
            echo "Error: " . $stmt->error;
        }
    }
}
?>
