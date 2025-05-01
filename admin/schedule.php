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

// Set timezone to ensure correct time display
date_default_timezone_set('Asia/Manila');

// Fetch admin's full name from the database
$query = "SELECT full_name FROM users WHERE user_id = '$admin_id'";
$result = $conn->query($query);
$admin = $result->fetch_assoc();
$admin_name = $admin['full_name']; // Admin's full name

$query = "SELECT schedule_requests.request_id, users.full_name, schedule_requests.shift_type, schedule_requests.start_time, schedule_requests.end_time, schedule_requests.status
          FROM schedule_requests
          JOIN users ON schedule_requests.user_id = users.user_id
          WHERE schedule_requests.status = 'pending'";

$result = $conn->query($query);
$requests = $result->fetch_all(MYSQLI_ASSOC);

// Handle approval/rejection of requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_request_id'])) {
        $request_id = $_POST['approve_request_id'];
        $status = 'approved';

        // Fetch request details
        $stmt = $conn->prepare("SELECT user_id, shift_type, start_time, end_time FROM schedule_requests WHERE request_id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $request = $stmt->get_result()->fetch_assoc();

        // Store original schedule in users table
        $user_id = $request['user_id'];
        $stmt = $conn->prepare(
            "UPDATE users 
             SET original_shift_type = shift_type, 
                 original_start_time = start_time, 
                 original_end_time = end_time, 
                 shift_type = ?, 
                 start_time = ?, 
                 end_time = ? 
             WHERE user_id = ?"
        );
        $stmt->bind_param(
            "sssi",
            $request['shift_type'],
            $request['start_time'],
            $request['end_time'],
            $user_id
        );
        $stmt->execute();

        // Update schedule request status to approved
        $stmt = $conn->prepare("UPDATE schedule_requests SET status = ? WHERE request_id = ?");
        $stmt->bind_param("si", $status, $request_id);
        $stmt->execute();
    } elseif (isset($_POST['reject_request_id'])) {
        $request_id = $_POST['reject_request_id'];
        $status = 'rejected';

        // Update schedule request status to rejected
        $stmt = $conn->prepare("UPDATE schedule_requests SET status = ? WHERE request_id = ?");
        $stmt->bind_param("si", $status, $request_id);
        $stmt->execute();
    }
}
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
            <h1>ADD SCHEDULE</h1>
        </div>

        <div class="dashboard-content">
            <div class="schedule-container">
                <div class="sched-form">
                    <form action="add_schedule.php" method="POST">
                        <h2>ADD NEW SCHEDULE</h2>
                        <div class="mb-3">
                            <label for="shift_type" class="form-label">Type of Shift</label>
                            <select id="shift_type" name="shift_type" class="form-select" required>
                                <option value="morning">Morning Shift</option>
                                <option value="afternoon">Afternoon Shift</option>
                                <option value="night">Night Shift</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="start_time" class="form-label">Shift Start Time</label>
                            <input type="time" id="start_time" name="start_time" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="end_time" class="form-label">Shift End Time</label>
                            <input type="time" id="end_time" name="end_time" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Schedule</button>
                    </form>
                </div>
                <div class="schedule-request">
                    <h2>Schedule Change Requests</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Shift Type</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($request['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($request['shift_type']); ?></td>
                                    <td><?php echo htmlspecialchars($request['start_time']); ?></td>
                                    <td><?php echo htmlspecialchars($request['end_time']); ?></td>
                                    <td><?php echo htmlspecialchars($request['status']); ?></td>
                                    <td>
                                        <?php if ($request['status'] == 'pending'): ?>
                                            <form method="POST">
                                                <button type="submit" name="approve_request_id" value="<?php echo $request['request_id']; ?>" class="btn btn-success">Approve</button>
                                                <button type="submit" name="reject_request_id" value="<?php echo $request['request_id']; ?>" class="btn btn-danger">Reject</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($request['status']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
       </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="requests.js" defer></script>
</body>
</html>
