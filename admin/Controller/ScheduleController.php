<?php
session_start();
include '../../db_connection.php';

// simple admin check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../index.php");
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

function redirect_with_success($code) {
    header("Location: ../schedule.php?success=" . urlencode($code));
    exit();
}

if ($action === 'add_schedule') {
    // expected POST: shift_name, start_time, end_time
    $shift = isset($_POST['shift_name']) ? trim($_POST['shift_name']) : '';
    $start = isset($_POST['start_time']) ? trim($_POST['start_time']) : '';
    $end = isset($_POST['end_time']) ? trim($_POST['end_time']) : '';

    if ($shift === '' || $start === '' || $end === '') {
        header("Location: ../schedule.php");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO schedules (shift_name, start_time, end_time) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $shift, $start, $end);
    $stmt->execute();
    $stmt->close();

    redirect_with_success('schedule_added');
}

if ($action === 'edit_schedule') {
    // expected POST: schedule_id, shift_name, start_time, end_time
    $id = isset($_POST['schedule_id']) ? (int)$_POST['schedule_id'] : 0;
    $shift = isset($_POST['shift_name']) ? trim($_POST['shift_name']) : '';
    $start = isset($_POST['start_time']) ? trim($_POST['start_time']) : '';
    $end = isset($_POST['end_time']) ? trim($_POST['end_time']) : '';

    if ($id <= 0 || $shift === '' || $start === '' || $end === '') {
        header("Location: ../schedule.php");
        exit();
    }

    $stmt = $conn->prepare("UPDATE schedules SET shift_name = ?, start_time = ?, end_time = ? WHERE schedule_id = ?");
    $stmt->bind_param("sssi", $shift, $start, $end, $id);
    $stmt->execute();
    $stmt->close();

    redirect_with_success('schedule_updated');
}

if ($action === 'delete_schedule') {
    // expected POST: schedule_id
    $id = isset($_POST['schedule_id']) ? (int)$_POST['schedule_id'] : 0;
    if ($id <= 0) { header("Location: ../schedule.php"); exit(); }

    // optional: remove schedule assignments from users referencing this schedule (set to NULL)
    $stmt1 = $conn->prepare("UPDATE users SET schedule_id = NULL WHERE schedule_id = ?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();
    $stmt1->close();

    $stmt = $conn->prepare("DELETE FROM schedules WHERE schedule_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    redirect_with_success('schedule_deleted');
}

if ($action === 'assign_schedule') {
    // expected POST: user_id, schedule_id
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $schedule_id = isset($_POST['schedule_id']) && $_POST['schedule_id'] !== '' ? (int)$_POST['schedule_id'] : NULL;

    if ($user_id <= 0) { header("Location: ../schedule.php"); exit(); }

    if ($schedule_id === NULL) {
        // unassign
        $stmt = $conn->prepare("UPDATE users SET schedule_id = NULL WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET schedule_id = ? WHERE user_id = ?");
        $stmt->bind_param("ii", $schedule_id, $user_id);
    }
    $stmt->execute();
    $stmt->close();

    redirect_with_success('user_assigned');
}

if ($action === 'clear_user_schedule') {
    // expected POST: user_id
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    if ($user_id <= 0) { header("Location: ../schedule.php"); exit(); }

    $stmt = $conn->prepare("UPDATE users SET schedule_id = NULL WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    redirect_with_success('user_cleared');
}

// default: redirect
header("Location: ../schedule.php");
exit();
