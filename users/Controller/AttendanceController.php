<?php
session_start();
include '../../db_connection.php';
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status"=>"Error","message"=>"Not logged in"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? null;

// Get schedule
$stmt = $conn->prepare("
    SELECT s.schedule_id, s.start_time, s.end_time 
    FROM users u 
    JOIN schedules s ON u.schedule_id = s.schedule_id 
    WHERE u.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$schedule = $stmt->get_result()->fetch_assoc();

$now = date("Y-m-d H:i:s");
$today = date("Y-m-d");

if (!$schedule) {
    echo json_encode(["status"=>"Error","message"=>"No schedule assigned"]);
    exit;
}

switch ($action) {
    case "status": // check today’s record
        $stmt = $conn->prepare("SELECT status FROM attendance WHERE user_id=? AND date=?");
        $stmt->bind_param("is", $user_id, $today);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        echo json_encode(["status"=>$res['status'] ?? "none"]);
        break;

    case "clockin":
        $start = strtotime($today." ".$schedule['start_time']);
        $end   = strtotime($today." ".$schedule['end_time']);
        $nowTime = time();

        $status = ($nowTime > $start && $nowTime <= $end) ? "late" : "ontime";

        $stmt = $conn->prepare("INSERT INTO attendance (user_id, schedule_id, date, time_in, status) 
                                VALUES (?,?,?,?,?) 
                                ON DUPLICATE KEY UPDATE time_in=VALUES(time_in), status=VALUES(status)");
        $stmt->bind_param("iisss", $user_id, $schedule['schedule_id'], $today, $now, $status);
        $stmt->execute();

        echo json_encode(["status"=>$status,"message"=>"Clocked in successfully"]);
        break;

    case "clockout":
        $stmt = $conn->prepare("UPDATE attendance SET time_out=?, status=? 
                                WHERE user_id=? AND date=? AND time_in IS NOT NULL");
        $status = "overtime"; // default after clockout
        $stmt->bind_param("ssis", $now, $status, $user_id, $today);
        $stmt->execute();

        echo json_encode(["status"=>$status,"message"=>"Clocked out successfully"]);
        break;

    case "absent":
        $status = "absent";
        $stmt = $conn->prepare("INSERT INTO attendance (user_id, schedule_id, date, status) 
                                VALUES (?,?,?,?) 
                                ON DUPLICATE KEY UPDATE status=VALUES(status)");
        $stmt->bind_param("iiss", $user_id, $schedule['schedule_id'], $today, $status);
        $stmt->execute();

        echo json_encode(["status"=>$status,"message"=>"Marked as absent"]);
        break;

    case "earlyout":
        $status = "earlyout";
        $stmt = $conn->prepare("UPDATE attendance SET time_out=?, status=? 
                                WHERE user_id=? AND date=? AND time_in IS NOT NULL");
        $stmt->bind_param("ssis", $now, $status, $user_id, $today);
        $stmt->execute();

        echo json_encode(["status"=>$status,"message"=>"Clocked out early"]);
        break;

    default:
        echo json_encode(["status"=>"Error","message"=>"Invalid action"]);
}
