<?php
session_start();
include '../db_connection.php'; // Database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if the user is not logged in
    exit();
}

// Get the user_id from the session
$user_id = $_SESSION['user_id'];

// Fetch the user data from the database
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit();
}

// Fetch attendance logs only for the logged-in user from the database
$sql = "SELECT id, login_date, time_in, time_out, status
        FROM attendance
        WHERE user_id = ?
        ORDER BY login_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../users/scss/user.scss">
    <link rel="stylesheet" href="../users/scss/table.scss">    
    <link rel="stylesheet" href="../users/scss/btn.scss"> 
    <title>Attendance Log</title>
</head>
<body>
    <div class="sidebar">
        <nav class="nav flex-column">
            <a class="nav-link logo-link" href="user_dashboard.php">
                <span class="icon">
                    <i class="bi bi-cpu"></i>
                </span>
                <span class="description">S.A.S</span>
            </a>
            <span class="category">Profile</span>
            <a class="nav-link" href="user_dashboard.php">
                <span class="icon">
                    <i class="bi bi-bounding-box"></i>
                </span>
                <span class="description">Profile</span>
            </a>
            <hr>
            <span class="category">Attendance</span>
            <a class="nav-link" href="time_in.php">
                <span class="icon">
                    <i class="bi bi-people"></i>
                </span>
                <span class="description">Time-in</span>
            </a>
            <a class="nav-link" href="attendance_log.php">
                <span class="icon">
                    <i class="bi bi-list-check"></i>
                </span>
                <span class="description">Attendance Log</span>
            </a>
            <a class="nav-link" href="request_leave.php">
                <span class="icon">
                    <i class="bi bi-chat-left-text"></i>
                </span>
                <span class="description">Request Leave</span>
            </a>
            <hr>
            <a class="nav-link" href="../logout.php">
                <span class="icon">
                    <i class="bi bi-box-arrow-right"></i>
                </span>
                <span class="description">Logout</span>
            </a>
        </nav>
    </div>

    <main class="main-content">
        <div class="header">
            <div class="welcome-message">
                <span class="icon">
                <i class="bi bi-person-circle"></i>
                </span>
                Welcome, <?php echo htmlspecialchars($user['full_name']); ?>
            </div>
        </div>
        <div class="page-title">
            <h1>ATTENDANCE LOG</h1>
        </div>

        <div class="dashboard-content">
            <div class="table-top">
                <div class="top1"></div>
                <div class="top2"></div>     
            </div>
            <div class="log-container">
                <?php if ($result->num_rows > 0): ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Login Date</th>
                                <th>Time-in</th>
                                <th>Time-out</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php 
                                        echo $row['time_in'] 
                                            ? date('M d, Y', strtotime($row['time_in'])) 
                                            : '--';
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        echo $row['time_in'] ? date('h:i A', strtotime($row['time_in'])) : '--'; 
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        echo $row['time_out'] ? date('h:i A', strtotime($row['time_out'])) : '--'; 
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $status = 'Absent'; // Default status
                                        if ($row['time_in']) {
                                            $time_in = strtotime($row['time_in']);
                                            $expected_time_in = strtotime('09:00:00'); // Example: 9 AM
                                            if ($time_in <= $expected_time_in) {
                                                $status = 'On-Time';
                                            } else {
                                                $status = 'Late';
                                            }
                                        }
                                        if ($row['status'] === 'On-Leave') {
                                            $status = 'On-Leave';
                                        }
                                        echo htmlspecialchars($status); 
                                        ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No attendance records found.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
