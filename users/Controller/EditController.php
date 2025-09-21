<?php
session_start();
include '../../db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'edit_profile') {
    $user_id = $_SESSION['user_id'];
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $gender = $_POST['gender'];

    // Optional: handle profile picture upload
    $profile = null;
    if (isset($_FILES['profile']) && $_FILES['profile']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "../../uploads/profile/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = time() . "_" . basename($_FILES["profile"]["name"]);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES["profile"]["tmp_name"], $targetFile)) {
            $profile = $fileName;
        }
    }

    // Build query dynamically
    $sql = "UPDATE users SET fname=?, lname=?, email=?, gender=?";
    $params = [$fname, $lname, $email, $gender];
    $types = "ssss";

    if (!empty($password)) {
        $sql .= ", password=?";
        $params[] = password_hash($password, PASSWORD_DEFAULT);
        $types .= "s";
    }

    if ($profile) {
        $sql .= ", profile=?";
        $params[] = $profile;
        $types .= "s";
    }

    $sql .= " WHERE user_id=?";
    $params[] = $user_id;
    $types .= "i";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => $stmt->error]);
    }
    $stmt->close();
    exit();
}
