ALA WA BALO
#0066E3
#0025B7

// /* Modal Styles */
// .modal {
//     display: none; /* Hidden by default */
//     position: fixed;
//     z-index: 1;
//     left: 0;
//     top: 0;
//     width: 100%;
//     height: 100%;
//     background-color: rgba(0, 0, 0, 0.5);
//     animation: fadeIn 0.3s ease-out;
//     /* Modal Content */
//     .modal-content {
//         position: absolute;
//         top: 50%;
//         left: 50%;
//         transform: translate(-50%, -50%);
//         background-color: white;
//         padding: 20px;
//         border-radius: 10px;
//         width: 300px;
//         box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
//         animation: scaleIn 0.4s ease-out;     
//     }       
// }


// /* Fade In and Scale In Animations */
// @keyframes fadeIn {
//     from { opacity: 0; }
//     to { opacity: 1; }
// }
// @keyframes scaleIn {
//     from { transform: translate(-50%, -50%) scale(0); }
//     to { transform: translate(-50%, -50%) scale(1); }
// }



<?php
session_start();
include '../db_connection.php'; // Database connection

// Fetch admin's ID from the session
$admin_id = $_SESSION['user_id'];

// Check if user is logged in and if the role is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch admin's full name from the database
$query = "SELECT full_name FROM users WHERE user_id = '$admin_id'";
$result = $conn->query($query);
$admin = $result->fetch_assoc();
$admin_name = $admin['full_name']; // Admin's full name

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../admin/scss/admin.scss">
    <link rel="stylesheet" href="../admin/scss/table.scss">    
    <link rel="stylesheet" href="../admin/scss/btn.scss"> 
    <title>Leave Requests</title>
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
                <!-- Display the admin's full name -->
                Welcome, <?php echo htmlspecialchars($admin_name); ?>
            </div>
        </div>

        <div class="page-title">
            <h1>SCHEDULE MANAGEMENT</h1>
        </div>

        <div class="dashboard-content">
            <div class="schedule-container">
                <div class="sched-form">

                </div>
            </div>
       </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="requests.js" defer></script>
</body>
</html>

make a form for adding a new schedule in my sched-form, for should have a Type of shift(Morning shift, afternoon shift or night shift), Shift Start Time, Shift End Time























You said:
time_in.php
<?php
session_start();
include '../db_connection.php';

// Set timezone to ensure correct time display
date_default_timezone_set('Asia/Manila');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT full_name, email, role, shift_type, start_time, end_time FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $email = $user['email'];
    $role = $user['role'];
    $shift_type = $user['shift_type'];
    $start_time = $user['start_time']; // Assume this is stored in "HH:MM:SS" format
    $end_time = $user['end_time'];     // Assume this is stored in "HH:MM:SS" format
} else {
    echo "User not found.";
    exit();
}

// Get today's date
$today_date = date("Y-m-d"); // Format: YYYY-MM-DD for database comparison

// Fetch today's attendance
$query = "SELECT time_in, time_out, status FROM attendance WHERE user_id = ? AND date = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $user_id, $today_date);
$stmt->execute();
$result = $stmt->get_result();
$attendance = $result->fetch_assoc();

$time_in = $attendance['time_in'] ?? null;
$time_out = $attendance['time_out'] ?? null;
$status = $attendance['status'] ?? 'Absent'; // Default status if no record exists

$message = '';

// Calculate status based on the time-in logic
function calculate_status($time_in, $start_time, $end_time) {
    if ($time_in === null) {
        return 'Absent';
    }

    $current_time = strtotime($time_in);
    $shift_start = strtotime($start_time);
    $shift_end = strtotime($end_time);

    // 2 hours grace period after start_time
    $grace_period_end = strtotime("+2 hours", $shift_start);

    if ($current_time <= $grace_period_end) {
        return 'On-time';
    } elseif ($current_time <= $shift_end) {
        return 'Late';
    } else {
        return 'Over-time';
    }
}

// Update status if Time In is recorded
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['time_in']) && $time_in === null) {
        $current_time = date('H:i:s'); // Current time in "HH:MM:SS" format
        $status = calculate_status($current_time, $start_time, $end_time);

        $stmt = $conn->prepare("INSERT INTO attendance (user_id, date, time_in, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $today_date, $current_time, $status);

        if ($stmt->execute()) {
            $message = "Time In recorded at $current_time. Status: $status.";
            $time_in = $current_time; // Update locally
        } else {
            $message = "Failed to record Time In.";
        }
    } elseif (isset($_POST['time_out']) && $time_out === null) {
        $current_time = date('H:i:s'); // Current time in "HH:MM:SS" format
        $stmt = $conn->prepare("UPDATE attendance SET time_out = ? WHERE user_id = ? AND date = ?");
        $stmt->bind_param("sis", $current_time, $user_id, $today_date);
        if ($stmt->execute()) {
            $message = "Time Out recorded at $current_time.";
            $time_out = $current_time; // Update locally
        } else {
            $message = "Failed to record Time Out.";
        }
    }
}

// Function to format time to 12-hour format with AM/PM
function format_time($time) {
    return ($time != null && $time != 'N/A') ? date('h:i A', strtotime($time)) : 'Not yet';
}

// Set allowed Time In and Time Out window
$allowed_time_in_start = '07:00 AM';
$allowed_time_in_end = '09:00 AM';
$allowed_time_out_start = '04:00 PM';
$allowed_time_out_end = '06:00 PM';
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
            <!-- <a class="nav-link" href="attendance_log.php">
                <span class="icon">
                    <i class="bi bi-list-check"></i>
                </span>
                <span class="description">Attendance Log</span>
            </a> -->
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
            <h1>TIME-IN</h1>
        </div>

        <div class="dashboard-content">
            <div class="in-container">
                <div class="date-in">
                    <div class="label-display">
                        <h1>ATTENDANCE TURN-IN</h1>
                    </div>
                    <!-- Display the current date -->
                    <div class="date-display">
                        <p>Date: <?php echo date("F, d Y", strtotime($today_date)); ?></p>
                    </div>
                    <!-- Display the current time -->
                    <div class="time-display" id="current-time">
                    <p>Time: <span id="time"></span></p>
                    </div>
                    <div class="status-display">
                        <p>Status: <?php echo htmlspecialchars($status); ?></p>
                    </div>
                </div>
                <div class="status">
                    <div class="in-status">
                        <p><strong>Time In:</strong><br><?php echo format_time($time_in); ?> </p>
                    </div>
                    <div class="out-status">
                        <p><strong>Time Out:</strong><br><?php echo format_time($time_out); ?> </p>
                    </div>
                </div>

            <div class="button-submit">
                <form method="POST">
                    <?php if ($time_in === null): ?>
                        <!-- Display both Time In and Time Out buttons initially -->
                        <div class="in-button">
                            <button type="submit" name="time_in">
                                <i class="bi bi-box-arrow-in-right"></i> Time In
                            </button>
                        </div>
                    <?php elseif ($time_out === null): ?>
                        <!-- Only Time Out button is displayed after Time In is clicked -->
                        <div class="in-button">
                            <button type="submit" name="time_out">
                                <i class="bi bi-box-arrow-in-left"></i>
                                Time Out
                            </button>
                        </div>
                    <?php else: ?>
                        <!-- Message indicating both actions are completed -->
                        <p>You have already completed your Time In and Time Out for today.</p>
                    <?php endif; ?>

                    <?php if ($message): ?>
                        <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
                    <?php endif; ?> 
                </form>
            </div>
                <!-- Display allowed time range for Time In and Time Out -->
                <div class="time-info">
                    <p><strong>Time In: </strong> You can Time In between <?php echo $allowed_time_in_start; ?> and <?php echo $allowed_time_in_end; ?>.</p>
                    <p><strong>Time Out: </strong> You can Time Out between <?php echo $allowed_time_out_start; ?> and <?php echo $allowed_time_out_end; ?>.</p>
                </div>
            </div>    
        </div>
    </main>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="../login/time.js"></script>
</body>
</html>

admin_dashboard.php
<?php
session_start();
include '../db_connection.php';

// Check if user is logged in and admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

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
    <link rel="stylesheet" href="../admin/scss/table.scss">    
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
                                    <td>Time-In</td>
                                </tr>
                                <!-- Time-Out Record (If exists) -->
                                <?php if ($user['time_out']): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['time_out']); ?></td>
                                        <td>Time-Out</td>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

in the users_management.php fix the display of the time-in today and time-out today box and the Recently Logged-in Users and their status


        <div class="pie-chart-container">
    <h2>User Status Today</h2>
    <canvas id="userStatusPieChart" style="width: 50%; height: 400px;"></canvas>
</div>

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