<?php
session_start();
include '../db_connection.php'; // Database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if the user is not logged in
    exit();
}

// Set timezone to ensure correct time display
date_default_timezone_set('Asia/Manila');

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

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the data from the form
    $leave_type = trim($_POST['leave_type']);
    $start_date = trim($_POST['start_date']);
    $end_date = trim($_POST['end_date']);
    $other = trim($_POST['other']); // Reason for leave

    // Check if all fields are filled
    if (empty($leave_type) || empty($start_date) || empty($end_date) || empty($other)) {
        $error = "Please fill in all fields.";
    } else {
        // Prepare the SQL query to insert the leave request into the database
        $stmt = $conn->prepare("INSERT INTO leave_requests (user_id, leave_type, start_date, end_date, other, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
        $stmt->bind_param("issss", $user_id, $leave_type, $start_date, $end_date, $other);
        
        // Execute the query
        if ($stmt->execute()) {
            $success = "Your leave request has been submitted successfully.";
        } else {
            $error = "An error occurred while submitting your request. Please try again.";
        }
        $stmt->close();
    }
}

// Fetch all leave requests for the logged-in user
$leave_requests_stmt = $conn->prepare("SELECT end_date, leave_type, status FROM leave_requests WHERE user_id = ? ORDER BY leave_date DESC");
$leave_requests_stmt->bind_param("i", $user_id);
$leave_requests_stmt->execute();
$leave_requests_result = $leave_requests_stmt->get_result();
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
    <title>Request Leave</title>
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
                <span class="description">Request leave</span>
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
            <h1>REQUEST LEAVE</h1>
        </div>

        <div class="dashboard-content">
            <div class="request-container">
                <div class="submit-container">
                    <?php if (isset($error)): ?>
                        <p class="error"><?php echo $error; ?></p>
                    <?php elseif (isset($success)): ?>
                        <p class="success"><?php echo $success; ?></p>
                    <?php endif; ?>

                    <form action="request_leave.php" method="POST">
                        <div class="form-group">
                            <label for="leave_type">Leave Type</label>
                            <select id="leave_type" name="leave_type" class="input" required>
                                <option value="Sick Leave">Sick Leave</option>
                                <option value="Vacation Leave">Vacation Leave</option>
                                <option value="Emergency Leave">Emergency Leave</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" id="start_date" name="start_date" class="input" required>
                        </div>

                        <div class="form-group">
                            <label for="end_date">End Date</label>
                            <input type="date" id="end_date" name="end_date" class="input" required>
                        </div>

                        <div class="form-group">
                            <label for="other">Reason</label>
                            <textarea id="other" name="other" class="input" rows="4" required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Submit Request</button>
                    </form>
                </div>
                <div class="request-log">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>End Date</th>
                                <th>Leave Type</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($leave_requests_result->num_rows > 0) {
                                while ($row = $leave_requests_result->fetch_assoc()) {
                                    $end_date = date("M j, Y", strtotime($row['end_date']));
                                    $status_class = ""; // Default class

                                    // Determine the CSS class based on status
                                    switch (strtolower($row['status'])) {
                                        case "pending":
                                            $status_class = "status-pending";
                                            break;
                                        case "approved":
                                            $status_class = "status-approved";
                                            break;
                                        case "denied":
                                            $status_class = "status-denied";
                                            break;
                                    }

                                    echo "<tr>
                                            <td>" . $end_date . "</td>
                                            <td>" . htmlspecialchars($row['leave_type']) . "</td>
                                            <td><span class='$status_class'>" . htmlspecialchars($row['status']) . "</span></td>
                                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3'>No leave requests found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>                
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
