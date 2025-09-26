<?php
// Controller/RequestsController.php
session_start();

// include DB connection (path tuned to controller located at admin/Controller/RequestsController.php)
include __DIR__ . '/../../db_connection.php';
date_default_timezone_set('Asia/Manila');

// require admin
if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin')) {
    header("Location: ../../index.php");
    exit();
}

// Accept action from POST first (forms use POST), fall back to GET
$action = $_POST['action'] ?? $_GET['action'] ?? '';
// Accept leave id from POST (leave_id) or GET (id)
$id = intval($_POST['leave_id'] ?? $_POST['id'] ?? $_GET['id'] ?? 0);

// only allow approve/reject
$allowed = ['approve' => 'approved', 'reject' => 'rejected'];

if ($id > 0 && isset($allowed[$action])) {
    $status = $allowed[$action];

    // prepare & execute update
    $stmt = $conn->prepare("UPDATE leave_requests SET status = ? WHERE leave_id = ?");
    if ($stmt) {
        $stmt->bind_param("si", $status, $id);
        $ok = $stmt->execute();
        $stmt->close();
        // redirect back with flag
        if ($ok) {
            header("Location: ../leave_requests.php?success=" . urlencode($action));
            exit();
        } else {
            header("Location: ../leave_requests.php?error=db");
            exit();
        }
    } else {
        header("Location: ../leave_requests.php?error=db_prepare");
        exit();
    }
} else {
    // invalid request
    header("Location: ../leave_requests.php?error=invalid_request");
    exit();
}
