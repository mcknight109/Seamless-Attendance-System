<?php
session_start();
include '../db_connection.php';

// Check if user is logged in and admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Set timezone to ensure correct time display
date_default_timezone_set('Asia/Manila');

// Fetch admin's ID and email from the session
$admin_id = $_SESSION['user_id'];
$admin_email = $_SESSION['email'];

// Fetch admin's full name from the database
$query_admin = "SELECT full_name FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query_admin);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($admin_full_name);
$stmt->fetch();
$stmt->close();

// Set a default value if the name is not found
$admin_full_name = $admin_full_name ?? 'Admin';

// Fetch today's date
$today_date = date("Y-m-d");

// Query 1: Count Total Users
$query_users = "SELECT COUNT(*) AS total_users FROM users";
$total_users = $conn->query($query_users)->fetch_assoc()['total_users'] ?? 0;

// Query 2: Users Time-In Today
$query_time_in = "SELECT COUNT(DISTINCT user_id) AS total_time_in 
                  FROM attendance 
                  WHERE date = ? AND time_in IS NOT NULL";
$stmt = $conn->prepare($query_time_in);
$stmt->bind_param("s", $today_date);
$stmt->execute();
$stmt->bind_result($total_time_in);
$stmt->fetch();
$stmt->close();
$total_time_in = $total_time_in ?? 0;

// Query 3: Users Time-Out Today
$query_time_out = "SELECT COUNT(DISTINCT user_id) AS total_time_out 
                   FROM attendance 
                   WHERE date = ? AND time_out IS NOT NULL";
$stmt = $conn->prepare($query_time_out);
$stmt->bind_param("s", $today_date);
$stmt->execute();
$stmt->bind_result($total_time_out);
$stmt->fetch();
$stmt->close();
$total_time_out = $total_time_out ?? 0;

// Query 4: Absent Users (Users with no Time-In Today)
$query_absent = "SELECT COUNT(*) AS total_absent 
                 FROM users 
                 WHERE user_id NOT IN (
                     SELECT DISTINCT user_id 
                     FROM attendance 
                     WHERE date = ? AND time_in IS NOT NULL
                 )";
$stmt = $conn->prepare($query_absent);
$stmt->bind_param("s", $today_date);
$stmt->execute();
$stmt->bind_result($total_absent);
$stmt->fetch();
$stmt->close();
$total_absent = $total_absent ?? 0;

// Query 5: Recently Logged-in Users
$query_recently_logged_in = "SELECT u.full_name, a.time_in, a.time_out
                             FROM attendance a
                             JOIN users u ON a.user_id = u.user_id
                             WHERE a.date = ? AND a.time_in IS NOT NULL
                             ORDER BY a.time_in DESC";
$stmt = $conn->prepare($query_recently_logged_in);
$stmt->bind_param("s", $today_date);
$stmt->execute();
$result_recently_logged_in = $stmt->get_result();
$recent_users = [];
while ($row = $result_recently_logged_in->fetch_assoc()) {
    // Format time_in and time_out to only show hour and minute in AM/PM format
    $row['time_in'] = date("h:i A", strtotime($row['time_in']));
    $row['time_out'] = $row['time_out'] ? date("h:i A", strtotime($row['time_out'])) : null;
    $recent_users[] = $row;
}

// Query to get the count of each status for today
$query_status_counts = "
    SELECT status, COUNT(*) AS count
    FROM attendance
    WHERE date = ?
    GROUP BY status";
$stmt = $conn->prepare($query_status_counts);
$stmt->bind_param("s", $today_date);
$stmt->execute();
$result_status_counts = $stmt->get_result();

// Initialize counts
$Late = 0;
$On_time = 0;
$Over_time = 0;
$Absent = 0;
$On_leave = 0;

// Loop through the result and assign counts to variables
while ($row = $result_status_counts->fetch_assoc()) {
    switch ($row['status']) {
        case 'Late':
            $Late = $row['count'];
            break;
        case 'On-Time':
            $On_time = $row['count'];
            break;
        case 'Over-Time':
            $Over_time = $row['count'];
            break;
        case 'Absent':
            $Absent = $row['count'];
            break;
        case 'On-Leave':
            $On_leave = $row['count'];
            break;
    }
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../admin/scss/admin.scss"> 
    <link rel="stylesheet" href="../admin/scss/dashboard.scss"> 

    <link rel="stylesheet" href="../admin/scss/btn.scss">    
    <title>Admin Dashboard</title>
</head>
<body>
    <div class="sidebar">
        <nav class="nav flex-column">
            <a class="nav-link logo-link" href="admin_dashboard.php">
                <span class="icon">
                    <i class="bi bi-cpu"></i>
                </span>
                <span class="description">S.A.S</span>
            </a>
            <span class="category">Admin</span>
            <a class="nav-link" href="admin_dashboard.php">
                <span class="icon">
                    <i class="bi bi-bounding-box"></i>
                </span>
                <span class="description">Dashboard</span>
            </a>
            <hr>
            <span class="category">Management</span>
            <a class="nav-link" href="users_management.php">
                <span class="icon">
                    <i class="bi bi-people"></i>
                </span>
                <span class="description">Users</span>
            </a>
            <a class="nav-link" href="attendance.php">
                <span class="icon">
                    <i class="bi bi-list-check"></i>
                </span>
                <span class="description">Attendance</span>
            </a>
            <a class="nav-link" href="leave_requests.php">
                <span class="icon">
                    <i class="bi bi-chat-left-text"></i>
                </span>
                <span class="description">Leave Requests</span>
            </a>
            <a class="nav-link" href="schedule.php">
                    <span class="icon">
                        <i class="bi bi-clipboard"></i>
                    </span>
                    <span class="description">Schedule</span>
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
                Welcome, <?php echo htmlspecialchars($admin_full_name); ?>
            </div>
        </div>
        <div class="page-title">
            <h1>DASHBOARD</h1>
        </div>
        <div class="box-content">
            <div class="box box1">
                <span class="icon">
                    <i class="bi bi-people-fill"></i>
                </span>
                <p>Total Users: <?php echo $total_users; ?></p>
            </div>
            <div class="box box2">
                <span class="icon">
                    <i class="bi bi-person-fill-check"></i>
                </span>
                <p>Time-In Today: <?php echo $total_time_in; ?></p>
            </div>
            <div class="box box3">
                <span class="icon">
                    <i class="bi bi-person-fill-x"></i>
                </span>
                <p>Time-Out Today: <?php echo $total_time_out; ?></p>
            </div>
            <div class="box box4">
                <span class="icon">
                    <i class="bi bi-person-fill-exclamation"></i>
                </span>
                <p>Absent Today: <?php echo $total_absent; ?></p>
            </div>
        </div>

        <div class="dashboard-content">
            <div class="pie-chart-container">
                <h2>User Status Today</h2>
                <canvas id="userStatusPieChart" style="width: 50%; height: 400px;"></canvas>
            </div>
            <div class="recent-container">
                <h2>Recently Logged-in Users</h2>
                <hr>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recent_users) > 0): ?>
                            <?php foreach ($recent_users as $user): ?>
                                <!-- Time-In Record -->
                                <tr>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['time_in']); ?></td>
                                    <td><span class="time-in-status">Time-In</span></td>
                                </tr>
                                <!-- Time-Out Record (If exists) -->
                                <?php if ($user['time_out']): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['time_out']); ?></td>
                                        <td><span class="time-out-status">Time-Out</span></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">No users logged in today.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Ensure proper data passing from PHP to JS
    var late = <?php echo $Late; ?>;
    var onTime = <?php echo $On_time; ?>;
    var overTime = <?php echo $Over_time; ?>;
    var absent = <?php echo $Absent; ?>;
    var onLeave = <?php echo $On_leave; ?>;

    var ctx = document.getElementById('userStatusPieChart').getContext('2d');
    var userStatusPieChart = new Chart(ctx, {
        type: 'pie',  // Pie chart type
        data: {
            labels: ['Late', 'On Time', 'Over Time', 'Absent', 'On Leave'],  // Labels for the chart
            datasets: [{
                data: [late, onTime, overTime, absent, onLeave],  // Dataset from PHP variables
                backgroundColor: ['#ff6347', '#4caf50', '#ffa500', '#f44336', '#2196f3'],  // Colors for each section
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',  // Position the legend at the top
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return tooltipItem.label + ': ' + tooltipItem.raw;  // Display the count in the tooltip
                        }
                    }
                }
            }
        }
    });
</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
